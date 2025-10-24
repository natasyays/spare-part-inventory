<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SparePart extends Model
{
    use HasFactory;
    protected $table = 'spare_parts';
    
    protected $fillable = [
        'part_code',
        'part_name',
        'category',
        'current_stock',
        'minimum_stock',
        'price',
    ];

    public $timestamps = false;
    public function usages(){
        return $this-> hasMany(SparePartUsage::class,"spare_part_id");
    }

    // public function stock_history(){
    //     return $this -> hasMany(StockHistory::class," spare_part_id");
    // }
}
