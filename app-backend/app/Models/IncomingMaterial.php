<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingMaterial extends Model
{
    protected $fillable = [
        'grn_number',
        'part_id',
        'lot_number',
        'quantity',
        'arrived_at',
        'status',
        'inspection_id',
        'registered_by',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function registrar()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
