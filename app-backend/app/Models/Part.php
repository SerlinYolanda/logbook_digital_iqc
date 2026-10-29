<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_number',
        'part_name',
        'supplier_name',
        'sampling_level',
        'aql_level',
        'nominal_dimension',
        'tolerance_min',
        'tolerance_max',
        'unit',
    ];

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function parameters()
    {
        return $this->hasMany(PartInspectionParameter::class);
    }
}
