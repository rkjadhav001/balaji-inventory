<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillCollection extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'datetime', 
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'party_id', 'id');
    }
}
