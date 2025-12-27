<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('invoice_id');

            $table->string('method', 32)->default('cash'); // cash|bank|card|online|adjustment
            $table->decimal('amount', 12, 2); // allow negative
            $table->string('currency', 8)->default('LKR');

            $table->timestamp('paid_at')->nullable();
            $table->string('reference', 120)->nullable();
            $table->string('note', 255)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_id', 'paid_at'], 'invoice_payments_invoice_paid_at_index');

            $table->foreign('invoice_id', 'invoice_payments_invoice_id_foreign')
                ->references('id')
                ->on('invoices')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'invoice_payments_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // Backfill from existing payment allocations (if any).
        // This keeps invoice totals consistent after introducing invoice_payments.
        $allocations = DB::table('payment_allocations')->get([
            'id',
            'payment_id',
            'invoice_id',
            'amount',
            'created_by',
            'created_at',
        ]);

        if ($allocations->isEmpty()) {
            return;
        }

        $paymentIds = $allocations->pluck('payment_id')->unique()->values()->all();
        $paymentsById = DB::table('payments')
            ->whereIn('id', $paymentIds)
            ->get(['id', 'method', 'currency', 'reference_no', 'received_at'])
            ->keyBy('id');

        $now = now();
        $rows = [];

        foreach ($allocations as $a) {
            $p = $paymentsById->get($a->payment_id);

            $method = 'cash';
            $currency = 'LKR';
            $reference = null;
            $paidAt = null;

            if ($p) {
                $currency = $p->currency ?: $currency;
                $reference = $p->reference_no ?: null;
                $paidAt = $p->received_at ?: null;

                $map = [
                    'cash' => 'cash',
                    'card' => 'card',
                    'bank_transfer' => 'bank',
                    'online_gateway' => 'online',
                ];
                $method = $map[$p->method] ?? 'cash';
            }

            $rows[] = [
                'invoice_id' => $a->invoice_id,
                'method' => $method,
                'amount' => $a->amount,
                'currency' => $currency,
                'paid_at' => $paidAt,
                'reference' => $reference,
                'note' => null,
                'created_by' => $a->created_by,
                'meta' => json_encode([
                    'source' => 'payment_allocation',
                    'payment_id' => $a->payment_id,
                    'payment_allocation_id' => $a->id,
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'created_at' => $a->created_at ?: $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('invoice_payments')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};

