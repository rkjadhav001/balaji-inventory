<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetial extends Model
{
    use HasFactory;
    protected $fillable = [
        'box',
        'patti',
        'packet',
        'total_qty',
        'total_cost',
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
