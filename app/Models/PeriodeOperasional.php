<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PeriodeOperasional extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'periode_operasional';
    protected $primaryKey = 'id_periode';
    protected $fillable = [
        'tahun_periode',
        'no_periode',
        'awal_tgl',
        'akhir_tgl',
        'kode_periode',
        'status_periode',
    ];
}
