<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'shipment_id',
        'tracking_number',
        'label_url',
        'provider_response',
        'status',
    ];

    protected $casts = [
        'provider_response' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
