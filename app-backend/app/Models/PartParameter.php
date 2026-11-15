<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'nama_parameter',
        'nominal',
        'batas_min',
        'batas_max',
        'satuan',
    ];

    protected $casts = [
        'nominal' => 'float',
        'batas_min' => 'float',
        'batas_max' => 'float',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'part_id');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class, 'part_parameter_id');
    }

    public function checkTolerance($value): string
    {
        if ($value === null || $value === '') {
            return 'fail';
        }
        $val = (float) $value;
        if ($this->batas_min !== null && $val < $this->batas_min) {
            return 'fail';
        }
        if ($this->batas_max !== null && $val > $this->batas_max) {
            return 'fail';
        }
        return 'pass';
    }
}

