<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AccountListSinaModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'tb_account_list';
    protected $primaryKey = 'id_acc_list';
    protected $fillable = [
        'account_no',
        'account_name',
        'type',
        'level',
        'category',
        'report',
        'general_account',
        'created_by'
    ];
    
    
}
