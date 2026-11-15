<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DefectCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_defect',
        'nama_defect',
        'deskripsi',
    ];

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class, 'defect_category_id');
    }
}

