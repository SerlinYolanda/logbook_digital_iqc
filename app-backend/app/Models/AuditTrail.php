<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'aksi',
        'objek_data',
        'nilai_sebelum',
        'nilai_sesudah',
        'alamat_ip',
        'waktu',
    ];

    protected $casts = [
        'waktu' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper to log immutable system & audit events
     */
    public static function log(string $aksi, string $objek, $nilaiSebelum = null, $nilaiSesudah = null, ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'aksi' => strtoupper($aksi),
            'objek_data' => $objek,
            'nilai_sebelum' => is_array($nilaiSebelum) ? json_encode($nilaiSebelum) : $nilaiSebelum,
            'nilai_sesudah' => is_array($nilaiSesudah) ? json_encode($nilaiSesudah) : $nilaiSesudah,
            'alamat_ip' => request()->ip() ?? '127.0.0.1',
            'waktu' => now(),
        ]);
    }
}

