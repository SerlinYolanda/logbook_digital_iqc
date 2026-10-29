<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartInspectionParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'parameter_name',
        'instrument_id',
        'nominal_dimension',
        'tolerance_min',
        'tolerance_max',
        'unit',
        'is_critical',
    ];

    protected $casts = [
        'nominal_dimension' => 'float',
        'tolerance_min' => 'float',
        'tolerance_max' => 'float',
        'is_critical' => 'boolean',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function instrument()
    {
        return $this->belongsTo(Instrument::class);
    }
}
