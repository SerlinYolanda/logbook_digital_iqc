<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'lot_number',
        'part_id',
        'inspector_id',
        'supervisor_id',
        'lot_quantity',
        'sample_size',
        'status',
        'disposition',
        'disposition_notes',
        'inspection_date',
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function measurements()
    {
        return $this->hasMany(InspectionMeasurement::class);
    }

    public function defects()
    {
        return $this->hasMany(Defect::class);
    }

    public function photos()
    {
        return $this->hasMany(InspectionPhoto::class);
    }

    public function passPhotos()
    {
        return $this->hasMany(InspectionPhoto::class)->where('photo_type', 'pass');
    }

    public function defectPhotos()
    {
        return $this->hasMany(InspectionPhoto::class)->where('photo_type', 'defect');
    }
}
