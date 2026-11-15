<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NestProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'logbook_id',
        'inspector_id',
        'kronologi_masalah',
        'bukti_pendukung',
        'diajukan_pada',
    ];

    protected $casts = [
        'diajukan_pada' => 'datetime',
    ];

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(Logbook::class, 'logbook_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}

