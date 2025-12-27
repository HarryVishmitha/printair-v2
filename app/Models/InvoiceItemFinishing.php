<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItemFinishing extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'pricing_snapshot' => 'array',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'meta' => 'array',
    ];

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    public function orderItemFinishing(): BelongsTo
    {
        return $this->belongsTo(OrderItemFinishing::class, 'order_item_finishing_id');
    }

    public function finishingProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'finishing_product_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'option_id');
    }
}

