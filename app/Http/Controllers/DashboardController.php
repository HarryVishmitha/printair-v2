<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function staffData(Request $request)
    {
        $user = $request->user();

        if (! $user?->isStaff()) {
            abort(403);
        }

        $range = (string) $request->query('range', '14d');
        $days = match ($range) {
            '7d' => 7,
            '14d' => 14,
            '30d' => 30,
            '90d' => 90,
            default => 14,
        };

        $cacheKey = "dashboard:staff:data:v2:{$range}";

        $payload = Cache::remember($cacheKey, now()->addSeconds(20), function () use ($days, $range) {
            $now = now();
            $start = $now->copy()->startOfDay()->subDays($days - 1);
            $prevStart = $start->copy()->subDays($days);
            $prevEnd = $start->copy()->subSecond();

            $currency = 'LKR';

            $activeOrderStatuses = ['confirmed', 'in_production', 'ready', 'out_for_delivery'];
            $openEstimateStatuses = ['draft', 'sent', 'viewed'];
            $outstandingInvoiceStatuses = ['issued', 'partial', 'overdue'];

            $usersTotal = User::query()->count();
            $customersTotal = Customer::query()->count();
            $estimatesOpen = Estimate::query()->whereIn('status', $openEstimateStatuses)->count();
            $ordersActive = Order::query()->whereIn('status', $activeOrderStatuses)->count();
            $invoicesOutstanding = Invoice::query()->whereIn('status', $outstandingInvoiceStatuses)->count();

            $revenueCurrent = (float) Payment::query()
                ->where('status', 'confirmed')
                ->where('created_at', '>=', $start)
                ->sum('amount');

            $revenuePrev = (float) Payment::query()
                ->where('status', 'confirmed')
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->sum('amount');

            $revenueDeltaPct = $revenuePrev > 0 ? (($revenueCurrent - $revenuePrev) / $revenuePrev) * 100 : null;
            $revenueDeltaPositive = $revenueDeltaPct === null ? true : $revenueDeltaPct >= 0;
            $revenueDeltaLabel = $revenueDeltaPct === null
                ? '+0% vs prev'
                : sprintf('%+d%% vs prev', (int) round($revenueDeltaPct));

            $slaRisk = Order::query()
                ->whereIn('status', $activeOrderStatuses)
                ->where('created_at', '<=', $now->copy()->subDays(3))
                ->count();

            $estimatesInRange = (int) Estimate::query()
                ->where('created_at', '>=', $start)
                ->count();
            $ordersInRange = (int) Order::query()
                ->where('created_at', '>=', $start)
                ->count();
            $conversionPct = $estimatesInRange > 0 ? (int) round(($ordersInRange / $estimatesInRange) * 100) : 0;

            $avgFulfillmentDays = $this->avgFulfillmentDays($start);

            $overdueInvoices = (int) Invoice::query()->where('status', 'overdue')->count();
            $deliveryRiskLabel = match (true) {
                $slaRisk === 0 => 'Low',
                $slaRisk <= 3 => 'Medium',
                default => 'High',
            };

            // Daily series (last N days)
            $countsOrders = $this->countByDay(Order::query(), 'created_at', $start);
            $countsInvoices = $this->countByDay(Invoice::query(), 'created_at', $start);
            $countsRevenue = $this->countByDay(
                Payment::query()->where('status', 'confirmed'),
                'created_at',
                $start,
                sumColumn: 'amount'
            );
            $countsEstimates = $this->countByDay(Estimate::query(), 'created_at', $start);

            $daily = [
                'labels' => [],
                'orders' => [],
                'invoices' => [],
                'revenue' => [],
                'conversion_pct' => [],
            ];

            for ($i = 0; $i < $days; $i++) {
                $date = $start->copy()->addDays($i);
                $dayKey = $date->toDateString();

                $ordersDay = (int) ($countsOrders[$dayKey] ?? 0);
                $estDay = (int) ($countsEstimates[$dayKey] ?? 0);
                $daily['labels'][] = $date->format('M d');
                $daily['orders'][] = $ordersDay;
                $daily['invoices'][] = (int) ($countsInvoices[$dayKey] ?? 0);
                $daily['revenue'][] = (float) (($countsRevenue[$dayKey]['sum'] ?? 0));
                $daily['conversion_pct'][] = $estDay > 0 ? (int) round(($ordersDay / $estDay) * 100) : 0;
            }

            // Weekly series (group daily into weeks)
            $weekly = [
                'labels' => [],
                'orders' => [],
                'invoices' => [],
                'revenue' => [],
                'conversion_pct' => [],
            ];

            $weeks = (int) ceil($days / 7);
            for ($w = 0; $w < $weeks; $w++) {
                $offset = $w * 7;
                $sliceOrders = array_slice($daily['orders'], $offset, 7);
                $sliceInvoices = array_slice($daily['invoices'], $offset, 7);
                $sliceRevenue = array_slice($daily['revenue'], $offset, 7);
                $sliceConversion = array_slice($daily['conversion_pct'], $offset, 7);

                $weekly['labels'][] = 'W'.($w + 1);
                $weekly['orders'][] = array_sum($sliceOrders);
                $weekly['invoices'][] = array_sum($sliceInvoices);
                $weekly['revenue'][] = array_sum($sliceRevenue);
                $weekly['conversion_pct'][] = count($sliceConversion) ? (int) round(array_sum($sliceConversion) / count($sliceConversion)) : 0;
            }

            // Donut chart: workload summary
            $donut = [
                'labels' => ['Orders Active', 'Estimates Open', 'Invoices Outstanding'],
                'values' => [$ordersActive, $estimatesOpen, $invoicesOutstanding],
            ];

            // Working groups: orders + revenue by group (top 6)
            $wgRows = Order::query()
                ->select([
                    'working_group_id',
                    DB::raw('count(*) as orders'),
                    DB::raw('coalesce(sum(grand_total), 0) as revenue'),
                ])
                ->where('created_at', '>=', $start)
                ->whereNull('deleted_at')
                ->groupBy('working_group_id')
                ->orderByDesc('revenue')
                ->limit(6)
                ->get();

            $wgIds = $wgRows->pluck('working_group_id')->all();
            $wgNames = DB::table('working_groups')->whereIn('id', $wgIds)->pluck('name', 'id');

            $wgChart = [
                'labels' => [],
                'orders' => [],
                'revenue' => [],
            ];
            foreach ($wgRows as $row) {
                $wgChart['labels'][] = (string) ($wgNames[$row->working_group_id] ?? ('WG '.$row->working_group_id));
                $wgChart['orders'][] = (int) $row->orders;
                $wgChart['revenue'][] = (float) $row->revenue;
            }

            // Funnel: estimates -> sent -> accepted -> orders (range based)
            $fEstimates = (int) Estimate::query()->where('created_at', '>=', $start)->count();
            $fSent = (int) Estimate::query()
                ->where('created_at', '>=', $start)
                ->whereIn('status', ['sent', 'viewed'])
                ->count();
            $fAccepted = (int) Estimate::query()
                ->where('created_at', '>=', $start)
                ->whereIn('status', ['accepted', 'converted'])
                ->count();
            $fOrders = (int) Order::query()->where('created_at', '>=', $start)->count();

            $fBase = max(1, $fEstimates);
            $funnel = [
                ['key' => 'estimates', 'label' => 'Estimates', 'value' => $fEstimates, 'pct' => 100],
                ['key' => 'sent', 'label' => 'Sent', 'value' => $fSent, 'pct' => (int) round(($fSent / $fBase) * 100)],
                ['key' => 'accepted', 'label' => 'Accepted', 'value' => $fAccepted, 'pct' => (int) round(($fAccepted / $fBase) * 100)],
                ['key' => 'orders', 'label' => 'Orders', 'value' => $fOrders, 'pct' => (int) round(($fOrders / $fBase) * 100)],
            ];

            // Top products (by orders) in range
            $topProducts = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereNull('order_items.deleted_at')
                ->whereNull('orders.deleted_at')
                ->where('orders.created_at', '>=', $start)
                ->groupBy('order_items.product_id')
                ->selectRaw('order_items.product_id as product_id, count(distinct orders.id) as orders, coalesce(sum(order_items.line_total), 0) as revenue, coalesce(avg(order_items.line_total), 0) as avg_line')
                ->orderByDesc('orders')
                ->limit(5)
                ->get();

            $productNames = Product::query()
                ->whereIn('id', $topProducts->pluck('product_id'))
                ->pluck('name', 'id');

            $topProductsOut = $topProducts->map(fn ($row) => [
                'id' => (int) $row->product_id,
                'name' => (string) ($productNames[$row->product_id] ?? 'Product #'.$row->product_id),
                'orders' => (int) $row->orders,
                'revenue_label' => $currency.' '.number_format((float) $row->revenue, 0),
                'avg_label' => 'Avg '.$currency.' '.number_format((float) $row->avg_line, 0),
            ])->values();

            // Top customers (by orders) in range
            $topCustomers = DB::table('orders')
                ->join('customers', 'customers.id', '=', 'orders.customer_id')
                ->whereNull('orders.deleted_at')
                ->whereNull('customers.deleted_at')
                ->whereNotNull('orders.customer_id')
                ->where('orders.created_at', '>=', $start)
                ->groupBy('orders.customer_id', 'customers.full_name', 'customers.type', 'customers.company_name')
                ->selectRaw("orders.customer_id as customer_id, count(*) as orders, coalesce(sum(orders.grand_total), 0) as total, customers.full_name, customers.company_name, customers.type")
                ->orderByDesc('orders')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'id' => (int) $row->customer_id,
                    'name' => (string) ($row->company_name ?: $row->full_name),
                    'orders' => (int) $row->orders,
                    'total_label' => $currency.' '.number_format((float) $row->total, 0),
                    'type' => Str::headline((string) $row->type),
                ]);

            // Recent
            $recentOrders = Order::query()
                ->select(['id', 'order_no', 'status', 'payment_status', 'grand_total', 'currency', 'created_at'])
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'order_no' => $o->order_no,
                    'subtitle' => "{$o->status} • {$o->payment_status}",
                    'total_label' => ($o->currency ?? $currency).' '.number_format((float) $o->grand_total, 0),
                    'created_at' => optional($o->created_at)->format('M d, H:i'),
                ]);

            $recentInvoices = Invoice::query()
                ->select(['id', 'invoice_no', 'status', 'grand_total', 'amount_due', 'currency', 'due_at', 'created_at'])
                ->latest()
                ->limit(8)
                ->get()
                ->map(function ($inv) use ($currency) {
                    $due = $inv->due_at ? 'Due: '.optional($inv->due_at)->format('M d') : null;
                    return [
                        'id' => $inv->id,
                        'invoice_no' => $inv->invoice_no,
                        'subtitle' => trim($inv->status.($due ? ' • '.$due : '')),
                        'total_label' => ($inv->currency ?? $currency).' '.number_format((float) $inv->grand_total, 0),
                        'created_at' => optional($inv->created_at)->format('M d, H:i'),
                    ];
                });

            $system = [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'queue_connection' => config('queue.default'),
            ];

            return [
                'meta' => [
                    'server_time' => $now->toDateTimeString(),
                    'last_updated_label' => 'Last updated: '.$now->format('h:i A'),
                ],
                'kpis' => [
                    'users_total' => $usersTotal,
                    'customers_total' => $customersTotal,
                    'estimates_open' => $estimatesOpen,
                    'orders_active' => $ordersActive,
                    'invoices_outstanding' => $invoicesOutstanding,
                    'revenue_label' => $currency.' '.number_format($revenueCurrent, 0),
                    'revenue_delta_label' => $revenueDeltaLabel,
                    'revenue_delta_positive' => $revenueDeltaPositive,
                    'sla_risk' => $slaRisk,
                    'conversion_label' => $conversionPct.'%',
                ],
                'metrics' => [
                    'conversion_label' => $conversionPct.'%',
                    'avg_fulfillment_label' => $avgFulfillmentDays === null ? '—' : number_format($avgFulfillmentDays, 1).' days',
                    'overdue_invoices' => $overdueInvoices,
                    'delivery_risk_label' => $deliveryRiskLabel,
                ],
                'series' => [
                    'daily' => $daily,
                    'weekly' => $weekly,
                ],
                'charts' => [
                    'donut' => $donut,
                    'working_groups' => $wgChart,
                ],
                'funnel' => $funnel,
                'top' => [
                    'products' => $topProductsOut,
                    'customers' => $topCustomers,
                ],
                'recent' => [
                    'orders' => $recentOrders,
                    'invoices' => $recentInvoices,
                ],
                'system' => $system + [
                    'range' => $range,
                ],
            ];
        });

        return response()->json($payload);
    }

    public function portalData(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->isStaff()) {
            abort(403);
        }

        $cacheKey = "dashboard:portal:data:v2:user:{$user->id}";

        $payload = Cache::remember($cacheKey, now()->addSeconds(20), function () use ($user) {
            $currency = 'LKR';

            $activeOrderStatuses = ['confirmed', 'in_production', 'ready', 'out_for_delivery'];
            $quoteStatuses = ['draft', 'sent', 'viewed'];

            $ordersTotal = (int) $user->createdOrders()->count();
            $ordersActive = (int) $user->createdOrders()->whereIn('status', $activeOrderStatuses)->count();
            $quotesTotal = (int) $user->createdEstimates()->whereIn('status', $quoteStatuses)->count();

            $customerId = $user->customer?->id;
            $outstanding = 0.0;
            if ($customerId) {
                $outstanding = (float) Invoice::query()
                    ->join('orders', 'orders.id', '=', 'invoices.order_id')
                    ->where('orders.customer_id', $customerId)
                    ->whereIn('invoices.status', ['issued', 'partial', 'overdue'])
                    ->sum('invoices.amount_due');
            }

            $recentOrders = $user->createdOrders()
                ->select(['id', 'order_no', 'status', 'payment_status', 'grand_total', 'currency', 'created_at'])
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'order_no' => $o->order_no,
                    'subtitle' => "{$o->status} • {$o->payment_status}",
                    'total_label' => ($o->currency ?? $currency).' '.number_format((float) $o->grand_total, 0),
                    'created_at' => optional($o->created_at)->format('M d'),
                ]);

            $now = now();

            return [
                'meta' => [
                    'last_updated_label' => 'Last updated: '.$now->format('h:i A'),
                ],
                'kpis' => [
                    'orders_total' => $ordersTotal,
                    'orders_active' => $ordersActive,
                    'quotes_total' => $quotesTotal,
                    'outstanding_label' => $currency.' '.number_format($outstanding, 0),
                ],
                'recent' => [
                    'orders' => $recentOrders,
                ],
            ];
        });

        return response()->json($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function countByDay($query, string $dateColumn, Carbon $start, ?string $sumColumn = null): array
    {
        $driver = DB::connection()->getDriverName();

        $dateExpr = match ($driver) {
            'pgsql' => "DATE({$dateColumn})",
            'sqlsrv' => "CONVERT(date, {$dateColumn})",
            default => "date({$dateColumn})", // mysql/sqlite
        };

        if ($sumColumn) {
            $rows = (clone $query)
                ->where($dateColumn, '>=', $start)
                ->selectRaw("{$dateExpr} as day, count(*) as c, coalesce(sum({$sumColumn}), 0) as s")
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $out = [];
            foreach ($rows as $row) {
                $day = (string) $row->day;
                $out[$day] = [
                    'count' => (int) $row->c,
                    'sum' => (float) $row->s,
                ];
            }

            return $out;
        }

        $rows = (clone $query)
            ->where($dateColumn, '>=', $start)
            ->selectRaw("{$dateExpr} as day, count(*) as c")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row->day] = (int) $row->c;
        }

        return $out;
    }

    private function avgFulfillmentDays(Carbon $start): ?float
    {
        $rows = Order::query()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $start)
            ->select(['created_at', 'ordered_at', 'confirmed_at', 'completed_at'])
            ->latest('completed_at')
            ->limit(200)
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $sum = 0.0;
        $n = 0;

        foreach ($rows as $o) {
            $startAt = $o->confirmed_at ?? $o->ordered_at ?? $o->created_at;
            $endAt = $o->completed_at;

            if (! $startAt || ! $endAt) {
                continue;
            }

            $sum += $startAt->diffInMinutes($endAt) / (60 * 24);
            $n++;
        }

        return $n ? ($sum / $n) : null;
    }
}
