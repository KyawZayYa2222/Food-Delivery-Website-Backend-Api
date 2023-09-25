<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_count',
        'location',
        'total_cost',
        'payment_id',
        'status',
    ];

    // public static function boot()
    // {
    //     parent::boot();

    //     static::saving(function ($model) {
    //         $model->total_cost = $model->total_cost . 'Ks';
    //     });
    // }
}
