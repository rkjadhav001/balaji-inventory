<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $appends = ['unit_type_names'];
    protected $fillable = ['sorting'];

    public function getUnitTypeNamesAttribute()
    {
        $unitTypeIds = explode(',', $this->unit_types);
        return DB::table('unit_types')
        ->whereIn('id', $unitTypeIds)
        ->select('id','name')
        ->get();
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id','id');
    }

    public function getStockDetailsAttribute()
    {
        $remainingStock = $this->available_stock;
        // Calculate the number of boxes
        $box = intdiv($remainingStock, $this->packet);
        $remainingStock %= $this->packet;
        $packet = (int)$this->available_stock;
        // Calculate the number of pattis
        $patti = intdiv($packet, $this->per_patti_piece);
        // Total packets remain unchanged
        return [
            'box' => $box,
            'patti' => $patti,
            'packet' => $packet,
        ];
    }
}
