<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class JournalHeaderSinaModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'tb_journal_header';
    protected $primaryKey = 'id_journal_head';
    protected $fillable = [
        'code_jgr',
        'code_jrc',
        'journal_jrc_no',
        'code_period',
        'journal_date',
        'due_date',
        'description',
        'created_by'
    ];
    
    
}
