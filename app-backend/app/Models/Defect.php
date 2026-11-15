<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Defect extends Model
{
    use HasFactory;

    protected $fillable = [
        'logbook_id',
        'defect_category_id',
        'qty_defect',
        'tingkat_keparahan',
        'deskripsi',
        'foto_bukti',
    ];

    protected $casts = [
        'qty_defect' => 'integer',
    ];

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(Logbook::class, 'logbook_id');
    }

    public function defectCategory(): BelongsTo
    {
        return $this->belongsTo(DefectCategory::class, 'defect_category_id');
    }

    public function getSeverityBadgeClassAttribute(): string
    {
        return match ($this->tingkat_keparahan) {
            'critical' => 'bg-rose-100 text-rose-800 border-rose-300 font-bold',
            'major' => 'bg-amber-100 text-amber-800 border-amber-300',
            default => 'bg-blue-100 text-blue-800 border-blue-300',
        };
    }
}
