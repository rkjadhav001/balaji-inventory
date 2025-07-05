<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BankTransaction;

class Banks extends Model
{
    use HasFactory;

    protected $table = 'banks';

    protected $fillable = [
        'bank_name',
        'opening_balance',
        'date',
        'total_amount',
        'is_default'
    ];






    public function bankTransaction(){
        return $this->hasMany(BankTransaction::class,'bank_id','id');
    }
}
