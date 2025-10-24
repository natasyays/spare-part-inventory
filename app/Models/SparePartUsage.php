<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePartUsage extends Model
{
    use HasFactory;
    protected $table = 'spare_part_usage';
    
    protected $fillable = [
        'machine_id',
        'spare_part_id',
        'quantity_used',
        'notes',
        'recorded_by',
        'usage_date',
    ];
   
    public $timestamps = false;


    public function spare_part (){
        return $this -> belongsTo(SparePart::class,"spare_part_id");
    }

    public function machine(){
        return $this -> belongsTo(Machine::class,"machine_id");
    }
}
