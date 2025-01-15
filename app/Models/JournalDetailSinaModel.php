<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class JournalDetailSinaModel extends Model
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'tb_journal_detail';
    protected $primaryKey = 'id_journal_detail';
    protected $fillable = [
        'journal_head_id',
        'code_jgr',
        'code_jrc',
        'code_period',
        'journal_date',
        'due_date',
        'general_account',
        'account_no',
        'code_cost',
        'code_div',
        'invoice_no',
        'code_currency',
        'debit',
        'kredit',
        'kurs',
        'jumlah_total',
        'description_detail',
        'created_by'
    ];
    
    
}
