<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Defect extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'defect_type',
        'severity',
        'quantity',
        'description',
        'photo_path',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }
}
