<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseDetail extends Model
{
    use HasFactory;

    public function expense() {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function expensecategory()
    {
        return $this->belongsTo(ExpanseCategory::class, 'category');
    }
}
