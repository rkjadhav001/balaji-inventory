<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnInvoice extends Model
{
    use HasFactory;

    public function party()
    {
        return $this->belongsTo(Supplier::class,'party_id', 'id');
    }

    public function purchaseReturnDetails()
    {
        return $this->hasMany(PurchaseReturnProduct::class, 'purchase_return_invoice_id', 'id');
    }
}
