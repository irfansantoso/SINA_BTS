<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ProcessEndingBalanceModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'temp_tb_ending_balance';
    protected $primaryKey = 'id_temp_tb_eb';
    protected $fillable = [
        'code_periode',
        'general_account',
        'account_no',
        'code_cost',
        'code_div',
        'nominal',
        'posisi'
    ];
    
    
}
