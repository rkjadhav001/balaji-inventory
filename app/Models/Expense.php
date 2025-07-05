<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'datetime', 
    ];

    public function expenseDetails()
    {
        return $this->hasMany(ExpenseDetail::class, 'expense_id', 'id');
    }
}
