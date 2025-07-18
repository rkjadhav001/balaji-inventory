<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'datetime', 
    ];

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseProduct::class, 'purchase_invoice_id', 'id');
    }
}
