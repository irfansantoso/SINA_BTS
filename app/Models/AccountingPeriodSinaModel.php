<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AccountingPeriodSinaModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'accounting_period';
    protected $primaryKey = 'id_period';
    protected $fillable = [
        'year',
        'month',
        'start_date',
        'end_date',
        'code_period',
        'status_period'
    ];
    
    
}
