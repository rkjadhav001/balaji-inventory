<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasFactory;

    protected $table = 'bank_transaction';

    protected $fillable = [
        'withdraw_from',
        'p_type',
        'balance',
        'date',
        'description',
        'deposit_to',
        'Type'
         
    ];
}
