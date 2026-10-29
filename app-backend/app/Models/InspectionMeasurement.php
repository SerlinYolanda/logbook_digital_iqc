<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'sample_number',
        'parameter_name',
        'measurement_tool',
        'actual_value',
        'nominal_value',
        'tolerance_min',
        'tolerance_max',
        'deviation',
        'is_pass',
        'notes',
    ];

    protected $casts = [
        'is_pass' => 'boolean',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }
}
