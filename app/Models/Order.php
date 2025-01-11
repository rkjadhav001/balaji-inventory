<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function wholesaler()
    {
        return $this->belongsTo(Wholesaler::class,'wholesaler_id', 'id');
    }

    public function orderProduct()
    {
        return $this->hasMany(OrderDetial::class, 'order_id',);
    }
}
