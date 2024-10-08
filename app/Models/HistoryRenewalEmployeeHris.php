<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class HistoryRenewalEmployeeHris extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'tb_history_renewal';
    protected $primaryKey = 'id_hist_renew';
    protected $fillable = [
        'nik',
        'start_contract_renew',
        'end_contract_renew',
        'duration_contract_renew',
        'created_by',
        'created_at',
        'updated_at'                   
    ];
}
