<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class TempAccountingPeriodSinaModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'temp_acc_period';
    protected $primaryKey = 'id_tap';
    protected $fillable = [
        'year',
        'month',
        'code_period',
        'user_acc_period'
    ];
    
    
}
