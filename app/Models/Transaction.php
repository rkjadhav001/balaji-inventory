<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public function party()
    {
        return $this->belongsTo(Supplier::class,'party_id', 'id');
    }

    public function bill()
    {
        return $this->belongsTo(Order::class,'bill_id', 'id');
    }

    public function expanseBill()
    {
        return $this->belongsTo(Expense::class,'bill_id', 'id');
    }
}
