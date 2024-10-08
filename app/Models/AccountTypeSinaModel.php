<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AccountTypeSinaModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'tb_account_type';
    protected $primaryKey = 'id';
    protected $fillable = [
        'acc_no',
        'acc_name',
        'acc_type',
        'acc_desc',
        'created_by'
    ];
    
    
}
