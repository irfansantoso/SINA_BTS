<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class JournalGroupSinaModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'tb_journal_group';
    protected $primaryKey = 'id_jgr';
    protected $fillable = [
        'code_jgr',
        'description_jgr',
        'deb_cre',
        'created_by'
    ];
    
    
}
