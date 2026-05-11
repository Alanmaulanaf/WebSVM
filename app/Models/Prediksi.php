<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prediksi extends Model
{
    use HasFactory;

    // Nama tabel (karena bukan plural default Laravel)
    protected $table = 'prediksi';
    public $timestamps = false;
    /**
     * ENUM nilai yang dipakai agar konsisten di Controller/Form.
     */
    public const WARNA = ['tidak berwarna', 'berwarna'];
    public const BAU   = ['tidak berbau', 'berbau'];
    public const RASA  = ['tawar', 'tidak berasa', 'asam', 'manis'];
    public const LABEL = ['ya', 'tidak'];

    protected $fillable = [
        'user_id',
        // input
        'suhu_c',
        'warna',
        'bau',
        'rasa',
        // hasil model
        'label_prediksi',
        'prob_ya',
        'sesuai_permenkes_proxy',
        'versi_model',
        // meta
        'diprediksi_pada',
        'sumber',
    ];

    protected $casts = [
        'suhu_c'                 => 'decimal:1',
        'prob_ya'                => 'float',
        'sesuai_permenkes_proxy' => 'boolean',
        'diprediksi_pada'        => 'datetime',
    ];

    /** Relasi ke user (opsional) */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Scope cepat untuk filter */
    public function scopeLabel($q, string $label)
    {
        return $q->where('label_prediksi', $label);
    }

    public function scopeTanggal($q, $from = null, $to = null)
    {
        if ($from) $q->whereDate('diprediksi_pada', '>=', $from);
        if ($to)   $q->whereDate('diprediksi_pada', '<=', $to);
        return $q;
    }


    /** Accessor tambahan: probabilitas (%) dibulatkan 1 desimal */
    public function getProbYaPercentAttribute(): string
    {
        return number_format(($this->prob_ya ?? 0) * 100, 1) . '%';
    }

    /** Helper daftar opsi untuk form (boleh dipakai di Blade) */
    public static function opsiWarna(): array
    {
        return self::WARNA;
    }
    public static function opsiBau(): array
    {
        return self::BAU;
    }
    public static function opsiRasa(): array
    {
        return self::RASA;
    }
    public static function opsiLabel(): array
    {
        return self::LABEL;
    }
}
