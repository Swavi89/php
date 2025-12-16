<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Calculate subtotal.
     */
    public function calculateSubtotal(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Set subtotal automatically.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orderItem) {
            if (is_null($orderItem->subtotal)) {
                $orderItem->subtotal = $orderItem->calculateSubtotal();
            }
        });

        static::updating(function ($orderItem) {
            $orderItem->subtotal = $orderItem->calculateSubtotal();
        });
    }
}
