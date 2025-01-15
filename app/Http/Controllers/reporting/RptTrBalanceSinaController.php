<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Models\JournalHeaderSinaModel;
use App\Models\JournalDetailSinaModel;
use App\Models\JournalSourceCodeSinaModel;
use App\Models\JournalGroupSinaModel;
use App\Models\TempAccountingPeriodSinaModel;
use App\Models\AccountingPeriodSinaModel;
use App\Models\AccountListSinaModel;
use App\Exports\ExportTrBalanceXls;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Session;

class RptTrBalanceSinaController extends Controller
{
    public function rptTrBalanceSina_browse()
    {   
        $journalGroupSina= JournalGroupSinaModel::all();

        if (Auth::check()) {
            $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                    ->where('user_acc_period', Auth::user()->username)
                                    ->first(); // Fetch a single record
        } else {
            return redirect()->route('login');
        }
        $syear = $getYearActive->year;

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $syear : '';

        $data['title'] = 'Reporting '.$showYearActive;
        return view('reporting/rptTrBalanceSina', $data, compact('journalGroupSina','syear'));
    }

    

    public function rptTrBalanceSina_setPeriode($month,$year)
    {
        $getPeriode = AccountingPeriodSinaModel::select('start_date','end_date')
                        ->where('year', $year)
                        ->where('month', $month)
                        ->first();

        if (!$getPeriode) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data periode aktif tidak ditemukan.',
            ], 404);
        }
        
        // $cp = $getYearActive->start_date;
        // $dt_periode = $getYearActive->month."/".$getYearActive->year;        

        return response()->json([
            'status' => 'success',
            'data' => $getPeriode
        ]);
            
    }

    public function rptTrBalanceSina_modal($m_date, $y_date, $acc_no, $acc_no_end, $code_div)
    {        
        $data = [
            'acc_no' => $acc_no,
            'acc_no_end' => $acc_no_end,
            'code_div' => $code_div,
        ];

        $getPeriode = AccountingPeriodSinaModel::select('code_period')
            ->where('year', $y_date)
            ->where('month', $m_date)
            ->first();

        if (!$getPeriode) {
            return abort(404, 'Periode tidak ditemukan.');
        }

        $code_period = $getPeriode->code_period;

        $journalDetails = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'acc.general_account',
                'jd.account_no',
                'acc.account_name',
                DB::raw('SUM(jd.debit) as debit'),
                DB::raw('SUM(jd.kredit) as kredit')
            )
            ->where('jd.code_period', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('acc.general_account', 'jd.account_no', 'acc.account_name')
            ->get();

        if ($journalDetails->isEmpty()) {
            $data['reportData'] = [];
            $data['total'] = [
                'beginning_balance' => 0,
                'debit' => 0,
                'credit' => 0,
                'ending_balance' => 0,
            ];
            return view('reporting.rptTrBalanceSinaModal', $data);
        }

        $beginningBalances = DB::table('tb_journal_detail')
            ->select('account_no', DB::raw('SUM(debit - kredit) as beginning_balance'))
            ->where('code_period', '<', $code_period)
            ->whereBetween('account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('code_div', $code_div);
            })
            ->groupBy('account_no')
            ->get()
            ->keyBy('account_no');

        $groupedData = [];
        foreach ($journalDetails as $detail) {
            $generalAccount = $detail->general_account;
            $accountNo = $detail->account_no;
            $beginningBalance = optional($beginningBalances->get(trim($accountNo)))->beginning_balance ?? 0;
            $debit = (float) $detail->debit;
            $credit = (float) $detail->kredit;
            $endingBalance = $beginningBalance + $debit - $credit;

            if (!isset($groupedData[$generalAccount])) {
                $groupedData[$generalAccount] = [
                    'subtotal' => [
                        'beginning_balance' => 0,
                        'debit' => 0,
                        'credit' => 0,
                        'ending_balance' => 0,
                    ],
                    'details' => []
                ];
            }

            $groupedData[$generalAccount]['details'][$accountNo] = [
                'account_name' => $detail->account_name,
                'beginning_balance' => $beginningBalance,
                'debit' => $debit,
                'credit' => $credit,
                'ending_balance' => $endingBalance,
            ];

            $groupedData[$generalAccount]['subtotal']['beginning_balance'] += $beginningBalance;
            $groupedData[$generalAccount]['subtotal']['debit'] += $debit;
            $groupedData[$generalAccount]['subtotal']['credit'] += $credit;
            $groupedData[$generalAccount]['subtotal']['ending_balance'] += $endingBalance;
        }

        $reportData = [];
        $total = [
            'beginning_balance' => 0,
            'debit' => 0,
            'credit' => 0,
            'ending_balance' => 0,
        ];

        foreach ($groupedData as $generalAccount => $group) {
            $getAccName = AccountListSinaModel::select('account_name')
                ->where('account_no', $generalAccount)
                ->first();
            $getAccName_x = $generalAccount." - ".$getAccName->account_name;

            $reportData[] = [
                'is_general_account' => true,
                'general_account' => $getAccName_x,
                'account_no' => '',
                'account_name' => '',
                'beginning_balance' => '',
                'debit' => '',
                'credit' => '',
                'ending_balance' => '',
                'dc' => '',
            ];

            foreach ($group['details'] as $accountNo => $account) {
                $reportData[] = [
                    'account_no' => $accountNo,
                    'account_name' => $account['account_name'],
                    'beginning_balance' => $account['beginning_balance'],
                    'debit' => $account['debit'],
                    'credit' => $account['credit'],
                    'ending_balance' => $account['ending_balance'],
                    'dc' => $account['ending_balance'] >= 0 ? 'D' : 'C',
                ];
            }

            $reportData[] = [
                'account_no' => '',
                'account_name' => 'Subtotal :',
                'beginning_balance' => $group['subtotal']['beginning_balance'],
                'debit' => $group['subtotal']['debit'],
                'credit' => $group['subtotal']['credit'],
                'ending_balance' => $group['subtotal']['ending_balance'],
                'dc' => '',
            ];

            $total['beginning_balance'] += $group['subtotal']['beginning_balance'];
            $total['debit'] += $group['subtotal']['debit'];
            $total['credit'] += $group['subtotal']['credit'];
            $total['ending_balance'] += $group['subtotal']['ending_balance'];

        }


        $data['reportData'] = $reportData;
        $data['total'] = $total;
        $data['m_date'] = $m_date;
        $data['y_date'] = $y_date;

        return view('reporting.rptTrBalanceSinaModal', $data);
    }


    public function rptTrBalanceSina_xls($m_date, $y_date, $acc_no, $acc_no_end, $code_div)
    {
        $data = [
            'acc_no' => $acc_no,
            'acc_no_end' => $acc_no_end,
            'code_div' => $code_div,
        ];

        $getPeriode = AccountingPeriodSinaModel::select('code_period')
            ->where('year', $y_date)
            ->where('month', $m_date)
            ->first();

        if (!$getPeriode) {
            return abort(404, 'Periode tidak ditemukan.');
        }

        $code_period = $getPeriode->code_period;

        $journalDetails = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'acc.general_account',
                'jd.account_no',
                'acc.account_name',
                DB::raw('SUM(jd.debit) as debit'),
                DB::raw('SUM(jd.kredit) as kredit')
            )
            ->where('jd.code_period', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('acc.general_account', 'jd.account_no', 'acc.account_name')
            ->get();

        if ($journalDetails->isEmpty()) {
            $data['reportData'] = [];
            $data['total'] = [
                'beginning_balance' => 0,
                'debit' => 0,
                'credit' => 0,
                'ending_balance' => 0,
            ];
            return view('reporting.rptTrBalanceSinaModal', $data);
        }

        $beginningBalances = DB::table('tb_journal_detail')
            ->select('account_no', DB::raw('SUM(debit - kredit) as beginning_balance'))
            ->where('code_period', '<', $code_period)
            ->whereBetween('account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('code_div', $code_div);
            })
            ->groupBy('account_no')
            ->get()
            ->keyBy('account_no');

        $groupedData = [];
        foreach ($journalDetails as $detail) {
            $generalAccount = $detail->general_account;
            $accountNo = $detail->account_no;
            $beginningBalance = optional($beginningBalances->get(trim($accountNo)))->beginning_balance ?? 0;
            $debit = (float) $detail->debit;
            $credit = (float) $detail->kredit;
            $endingBalance = $beginningBalance + $debit - $credit;

            if (!isset($groupedData[$generalAccount])) {
                $groupedData[$generalAccount] = [
                    'subtotal' => [
                        'beginning_balance' => 0,
                        'debit' => 0,
                        'credit' => 0,
                        'ending_balance' => 0,
                    ],
                    'details' => []
                ];
            }

            $groupedData[$generalAccount]['details'][$accountNo] = [
                'account_name' => $detail->account_name,
                'beginning_balance' => $beginningBalance,
                'debit' => $debit,
                'credit' => $credit,
                'ending_balance' => $endingBalance,
            ];

            $groupedData[$generalAccount]['subtotal']['beginning_balance'] += $beginningBalance;
            $groupedData[$generalAccount]['subtotal']['debit'] += $debit;
            $groupedData[$generalAccount]['subtotal']['credit'] += $credit;
            $groupedData[$generalAccount]['subtotal']['ending_balance'] += $endingBalance;
        }

        $reportData = [];
        $total = [
            'beginning_balance' => 0,
            'debit' => 0,
            'credit' => 0,
            'ending_balance' => 0,
        ];

        foreach ($groupedData as $generalAccount => $group) {
            $getAccName = AccountListSinaModel::select('account_name')
                ->where('account_no', $generalAccount)
                ->first();
            $getAccName_x = $generalAccount." - ".$getAccName->account_name;

            $reportData[] = [
                'is_general_account' => true,
                'general_account' => $getAccName_x,
                'account_no' => '',
                'account_name' => '',
                'beginning_balance' => '',
                'debit' => '',
                'credit' => '',
                'ending_balance' => '',
                'dc' => '',
            ];

            foreach ($group['details'] as $accountNo => $account) {
                $reportData[] = [
                    'account_no' => $accountNo,
                    'account_name' => $account['account_name'],
                    'beginning_balance' => $account['beginning_balance'],
                    'debit' => $account['debit'],
                    'credit' => $account['credit'],
                    'ending_balance' => $account['ending_balance'],
                    'dc' => $account['ending_balance'] >= 0 ? 'D' : 'C',
                ];
            }

            $reportData[] = [
                'account_no' => '',
                'account_name' => 'Sub Total :',
                'beginning_balance' => $group['subtotal']['beginning_balance'],
                'debit' => $group['subtotal']['debit'],
                'credit' => $group['subtotal']['credit'],
                'ending_balance' => $group['subtotal']['ending_balance'],
                'dc' => '',
            ];

            $total['beginning_balance'] += $group['subtotal']['beginning_balance'];
            $total['debit'] += $group['subtotal']['debit'];
            $total['credit'] += $group['subtotal']['credit'];
            $total['ending_balance'] += $group['subtotal']['ending_balance'];

        }


        $data['reportData'] = $reportData;
        $data['total'] = $total;
        $data['m_date'] = $m_date;
        $data['y_date'] = $y_date;        

        $tgl = now()->format('Ymd_His');
        $fileNm = "Trial_Balance-".$tgl.".xlsx";
        return Excel::download(
            new ExportTrBalanceXls(
                $reportData,
                $data['m_date'],
                $data['y_date'],
                $data['total']
            ),
            $fileNm
        );
    }

}
