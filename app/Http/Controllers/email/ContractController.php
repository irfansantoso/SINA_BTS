<?php
namespace App\Http\Controllers\Email;

use App\Mail\ExpiredContractsMail;
use App\Http\Controllers\Controller;
use App\Models\EmailReceiverHris;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    public function sendExpiredContractsEmail()
    {
        $expiredContracts = DB::table('tb_employee')
            ->leftJoin('tb_site', 'tb_employee.site_id', '=', 'tb_site.id_site')
            ->leftJoin('tb_department', 'tb_employee.dept_id', '=', 'tb_department.id_dept')
            ->leftJoin('tb_position', 'tb_employee.position_id', '=', 'tb_position.id_position')
            ->select('tb_employee.*', 'tb_site.site_name as siteName', 'tb_department.dept_name as deptName', 'tb_position.position_name as positionName')
            ->where('tb_employee.information_status', 'active')
            ->where(function($query) {
                $query->where('tb_employee.end_contract', '<', now())
                      ->orWhereBetween('tb_employee.end_contract', [now(), now()->addDays(30)]);
            })
            ->get();

        if ($expiredContracts->isEmpty()) {
            return 'No expired contracts found. Email not sent.';
        }

        // Fetch email recipients from the EmailReceiverHris model
        $emailRecipients = EmailReceiverHris::pluck('email')->toArray();

        // Check if there are no email recipients
        if (empty($emailRecipients)) {
            return 'No email recipients found. Email not sent.';
        }

        // Define CC recipients
        $ccRecipients = ['irfan.santoso86@gmail.com'];

        Mail::to($emailRecipients)->cc($ccRecipients)->send(new ExpiredContractsMail($expiredContracts));

        // Mail::to(['irfan_savestheday@yahoo.com', 'irfan.santoso86@gmail.com'])->send(new ExpiredContractsMail($expiredContracts));

        // Mail::to($emailRecipients)->send(new ExpiredContractsMail($expiredContracts));

        return 'Email sent successfully!';
    }
}

