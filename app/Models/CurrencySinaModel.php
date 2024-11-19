<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class CurrencySinaModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'tb_currency';
    protected $primaryKey = 'id_currency';
    protected $fillable = [
        'code_currency',
        'currency_description',
        'created_by'
    ];
    
    
}
