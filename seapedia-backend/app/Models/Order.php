<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'buyer_id', 'store_id', 'address_id', 'delivery_method',
        'subtotal', 'discount_amount', 'voucher_id', 'promo_id', 'delivery_fee',
        'ppn_amount', 'total_amount', 'status', 'is_refunded', 'is_income_reversed',
        'is_stock_restored', 'sla_due_at', 'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'sla_due_at' => 'datetime',
            'is_refunded' => 'boolean',
            'is_income_reversed' => 'boolean',
            'is_stock_restored' => 'boolean',
            'refunded_at' => 'datetime',
        ];
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }
}