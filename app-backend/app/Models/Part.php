<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_part',
        'nama_part',
        'spesifikasi',
        'kategori_komoditas',
        'standar_aql',
        'level_inspeksi',
    ];

    public function parameters(): HasMany
    {
        return $this->hasMany(PartParameter::class, 'part_id');
    }

    public function receivings(): HasMany
    {
        return $this->hasMany(Receiving::class, 'part_id');
    }

    public function getKomoditasLabelAttribute(): string
    {
        return match ($this->kategori_komoditas) {
            'ee_part' => 'EE Part',
            'automotive' => 'Automotive',
            'wire_harness' => 'Wire Harness',
            default => ucfirst(str_replace('_', ' ', $this->kategori_komoditas)),
        };
    }
}
