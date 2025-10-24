<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_name',
        'location',
        'status'
    ];
    public function usages(){
        return $this -> hasMany(SparePartUsage::class,"machine_id");
    }
}

