<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class JournalSourceCodeSinaModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'tb_journal_sc';
    protected $primaryKey = 'id_jsc';
    protected $fillable = [
        'code_jgr',
        'deb_cre',
        'year',
        'code_jrc',
        'journal_jrc_no',
        'account_no',
        'account_name',
        'created_by'
    ];
    
    
}
