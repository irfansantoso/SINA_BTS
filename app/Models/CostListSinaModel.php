<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class CostListSinaModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'tb_cost';
    protected $primaryKey = 'id_cost';
    protected $fillable = [
        'code_cost',
        'cost_description',
        'cost_category',
        'created_by'
    ];
    
    
}
