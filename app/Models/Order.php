<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;



    protected $fillable = [
        'total_box',
        'total_patti',
        'total_packet',
        'final_amount',
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    public function wholesaler()
    {
        return $this->belongsTo(Supplier::class,'wholesaler_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id', 'id');
    }

    public function orderProduct()
    {
        return $this->hasMany(OrderDetial::class, 'order_id','id');
    }
}
