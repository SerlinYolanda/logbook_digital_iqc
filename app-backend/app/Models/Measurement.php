<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'logbook_id',
        'part_parameter_id',
        'sample_number',
        'nilai_ukur',
        'status_toleransi',
    ];

    protected $casts = [
        'sample_number' => 'integer',
        'nilai_ukur' => 'float',
    ];

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(Logbook::class, 'logbook_id');
    }

    public function partParameter(): BelongsTo
    {
        return $this->belongsTo(PartParameter::class, 'part_parameter_id');
    }
}

