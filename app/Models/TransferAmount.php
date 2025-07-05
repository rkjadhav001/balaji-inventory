<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferAmount extends Model
{
    use HasFactory;

    public function debitedParty()
    {
        return $this->belongsTo(Supplier::class, 'from_transfer_id');
    }

    public function creditedParty()
    {
        return $this->belongsTo(Supplier::class, 'to_transfer_id');
    }
}
