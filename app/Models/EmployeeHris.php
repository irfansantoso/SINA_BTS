<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class EmployeeHris extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'tb_employee';
    protected $primaryKey = 'id_employee';
    protected $fillable = [
        'employee_number',
        'site_id',
        'dept_id',
        'employee_name',
        'nik',
        'bpjs_tk',
        'bpjs_kes',
        'date_in',
        'place_birth',
        'date_birth',
        'position_id',
        'status_marital',
        'gender',
        'fee_status',
        'religion',
        'education',
        'recipient_address',
        'start_contract',
        'end_contract',
        'duration_contract',
        'information_status',
        'additional_information',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'                   
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
