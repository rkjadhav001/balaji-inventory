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
        // $remainingStock = $this->available_stock;
        // $box = intdiv($remainingStock, $this->packet);
        // $remainingStock %= $this->packet;
        // $packet = (int)$this->available_stock;
        // $patti = intdiv($packet, $this->per_patti_piece);

        $remainingStock = $this->available_stock;
        $box = $this->packet > 0 ? intdiv($remainingStock, $this->packet) : 0;
        $remainingStock %= $this->packet > 0 ? $this->packet : 1;
        $packet = (int)$remainingStock;
        $patti = $this->per_patti_piece > 0 ? intdiv($packet, $this->per_patti_piece) : 0;
        return [
            'box' => $box,
            'patti' => $patti,
            'packet' => $packet,
        ];
    }
}
