<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    public function purchaseOrderDetails()
    {
        return $this->hasMany(PurchaseOrderProduct::class, 'purchase_order_id', 'id');
    }
}
