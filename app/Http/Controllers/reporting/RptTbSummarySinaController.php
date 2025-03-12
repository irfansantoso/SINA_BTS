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
use App\Exports\ExportTbSummaryXls;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Session;

class RptTbSummarySinaController extends Controller
{
    public function rptTbSummarySina_browse()
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
        return view('reporting/rptTbSummarySina', $data, compact('journalGroupSina','syear'));
    }

    

    public function rptTbSummarySina_setPeriode($month,$year)
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

    public function rptTbSummarySina_modal($m_date, $y_date, $acc_no, $acc_no_end, $code_div)
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

        $journalSummary = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'acc.general_account',
                DB::raw('SUM(jd.debit) as debit'),
                DB::raw('SUM(jd.kredit) as kredit')
            )
            ->where('jd.code_period', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('acc.general_account', 'jd.account_no')
            ->orderBy('jd.account_no')
            ->get();

        if ($journalSummary->isEmpty()) {
            $data['reportData'] = [];
            $data['total'] = [
                'beginning_balance' => 0,
                'debit' => 0,
                'kredit' => 0,
                'ending_balance' => 0,
            ];
            return view('reporting.rptTbSummaryModal', $data);
        }

        $beginningBalances = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'acc.general_account',
                DB::raw("
                    SUM(
                        CASE
                            WHEN COALESCE(jd.debit, 0) = 0 THEN COALESCE(jd.kredit, 0)
                            ELSE COALESCE(jd.debit, 0) - COALESCE(jd.kredit, 0)
                        END
                    ) as beginning_balance
                "),
                DB::raw("
                    MAX(
                        CASE
                            WHEN COALESCE(jd.debit, 0) = 0 THEN 'C'
                            ELSE 'D'
                        END
                    ) as d_c
                ")
            )
            ->where('jd.code_period', '<', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('acc.general_account')
            ->get()
            ->keyBy('general_account');

        $groupedData = [];
        foreach ($journalSummary as $summary) {
            $generalAccount = $summary->general_account;
            $beginningBalanceData = $beginningBalances->get(trim($generalAccount));
            $beginningBalance = optional($beginningBalanceData)->beginning_balance ?? 0;
            $d_c = optional($beginningBalanceData)->d_c ?? 'D'; // Nilai 'D' atau 'C'
            $debit = $summary->debit !== null ? (float) $summary->debit : null;
            $kredit = (float) ($summary->kredit ?? 0);
            if ($d_c === 'C' && $debit == null) {
                $endingBalance = $beginningBalance + $kredit;
                $end_dc = 'C';
            }elseif ($d_c === 'C' && $debit != null) {
                $endingBalance = $debit - ($beginningBalance + $kredit);
                $end_dc = 'D';
            }elseif ($d_c === 'D' && $beginningBalance == null && $debit == null) {
                $endingBalance = $kredit;
                $end_dc = 'C';
            }else{
                $endingBalance = ($beginningBalance + $debit) - $kredit;
                $end_dc = 'D';
            }
            $beginningBalanceStatus = $d_c;
            $endingBalanceStatus  = $end_dc;

            $groupedData[] = [
                'general_account' => $generalAccount,
                'general_name' => AccountListSinaModel::where('account_no', $generalAccount)->value('account_name') ?? 'General',
                'beginning_balance' => $beginningBalance,
                'bbs' => $beginningBalanceStatus,
                'debit' => $debit,
                'kredit' => $kredit,
                'ending_balance' => $endingBalance,
                'ebs' => $endingBalanceStatus,
            ];
        }

        $totalkredit = $journalSummary->where(function ($item) {
            return str_starts_with($item->general_account, '4') || str_starts_with($item->general_account, '81');
        })->sum('kredit');

        $totalDebit = $journalSummary->where(function ($item) {
            return str_starts_with($item->general_account, '5') ||
                   str_starts_with($item->general_account, '6') ||
                   str_starts_with($item->general_account, '7') ||
                   str_starts_with($item->general_account, '82') ||
                   str_starts_with($item->general_account, '89') ||
                   str_starts_with($item->general_account, '9');
        })->sum('debit');

        // Hitung currentProfitLosskredit
        $currentProfitLosskredit = $totalkredit - $totalDebit;

        // Tambahkan hasil ke data
        if ($totalkredit > $totalDebit) {
            // Jika totalkredit lebih besar, tampilkan di kolom debit
            $data['currProfitLosskredit'] = [
                'general_account' => 'CurrentProfitLoss',
                'general_name' => 'Current Month Profit/Loss',
                'beginning_balance' => 0,
                'debit' => $currentProfitLosskredit,
                'kredit' => 0,
                'ending_balance' => 0,
                'ebs' => 'D',
            ];
        } else {
            // Jika totalDebit lebih besar atau sama, tampilkan di kolom kredit
            $data['currProfitLosskredit'] = [
                'general_account' => 'CurrentProfitLoss',
                'general_name' => 'Current Month Profit/Loss',
                'beginning_balance' => 0,
                'debit' => 0,
                'kredit' => $currentProfitLosskredit,
                'ending_balance' => 0,
                'ebs' => 'C',
            ];
        }

        // Hitung total
        $data['total'] = [
            'beginning_balance' => array_sum(array_column($groupedData, 'beginning_balance')),
            'debit' => array_sum(array_column($groupedData, 'debit')),
            'kredit' => array_sum(array_column($groupedData, 'kredit')),
            'ending_balance' => array_sum(array_column($groupedData, 'ending_balance')),
        ];

        $data['reportData'] = $groupedData;
        $data['m_date'] = $m_date;
        $data['y_date'] = $y_date;

        return view('reporting.rptTbSummarySinaModal', $data);
    }


    public function rptTbSummarySina_xls($m_date, $y_date, $acc_no, $acc_no_end, $code_div)
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

        $journalSummary = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'acc.general_account',
                DB::raw('SUM(jd.debit) as debit'),
                DB::raw('SUM(jd.kredit) as kredit')
            )
            ->where('jd.code_period', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('acc.general_account', 'jd.account_no')
            ->orderBy('jd.account_no')
            ->get();

        if ($journalSummary->isEmpty()) {
            $data['reportData'] = [];
            $data['total'] = [
                'beginning_balance' => 0,
                'debit' => 0,
                'kredit' => 0,
                'ending_balance' => 0,
            ];
            return view('reporting.rptTbSummaryModal', $data);
        }

        $beginningBalances = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'acc.general_account',
                DB::raw("
                    SUM(
                        CASE
                            WHEN COALESCE(jd.debit, 0) = 0 THEN COALESCE(jd.kredit, 0)
                            ELSE COALESCE(jd.debit, 0) - COALESCE(jd.kredit, 0)
                        END
                    ) as beginning_balance
                "),
                DB::raw("
                    MAX(
                        CASE
                            WHEN COALESCE(jd.debit, 0) = 0 THEN 'C'
                            ELSE 'D'
                        END
                    ) as d_c
                ")
            )
            ->where('jd.code_period', '<', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('acc.general_account')
            ->get()
            ->keyBy('general_account');

        $groupedData = [];
        foreach ($journalSummary as $summary) {
            $generalAccount = $summary->general_account;
            $beginningBalanceData = $beginningBalances->get(trim($generalAccount));
            $beginningBalance = optional($beginningBalanceData)->beginning_balance ?? 0;
            $d_c = optional($beginningBalanceData)->d_c ?? 'D'; // Nilai 'D' atau 'C'
            $debit = $summary->debit !== null ? (float) $summary->debit : null;
            $kredit = (float) ($summary->kredit ?? 0);
            if ($d_c === 'C' && $debit == null) {
                $endingBalance = $beginningBalance + $kredit;
                $end_dc = 'C';
            }elseif ($d_c === 'C' && $debit != null) {
                $endingBalance = $debit - ($beginningBalance + $kredit);
                $end_dc = 'D';
            }elseif ($d_c === 'D' && $beginningBalance == null && $debit == null) {
                $endingBalance = $kredit;
                $end_dc = 'C';
            }else{
                $endingBalance = ($beginningBalance + $debit) - $kredit;
                $end_dc = 'D';
            }
            $beginningBalanceStatus = $d_c;
            $endingBalanceStatus  = $end_dc;

            $groupedData[] = [
                'general_account' => $generalAccount,
                'general_name' => AccountListSinaModel::where('account_no', $generalAccount)->value('account_name') ?? 'General',
                'beginning_balance' => $beginningBalance,
                'bbs' => $beginningBalanceStatus,
                'debit' => $debit,
                'kredit' => $kredit,
                'ending_balance' => $endingBalance,
                'ebs' => $endingBalanceStatus,
            ];
        }

        $totalkredit = $journalSummary->where(function ($item) {
            return str_starts_with($item->general_account, '4') || str_starts_with($item->general_account, '81');
        })->sum('kredit');

        $totalDebit = $journalSummary->where(function ($item) {
            return str_starts_with($item->general_account, '5') ||
                   str_starts_with($item->general_account, '6') ||
                   str_starts_with($item->general_account, '7') ||
                   str_starts_with($item->general_account, '82') ||
                   str_starts_with($item->general_account, '89') ||
                   str_starts_with($item->general_account, '9');
        })->sum('debit');

        // Hitung currentProfitLosskredit
        $currentProfitLosskredit = $totalkredit - $totalDebit;

        // Tambahkan hasil ke data
        if ($totalkredit > $totalDebit) {
            // Jika totalkredit lebih besar, tampilkan di kolom debit
            $data['currProfitLosskredit'] = [
                'general_account' => 'CurrentProfitLoss',
                'general_name' => 'Current Month Profit/Loss',
                'beginning_balance' => 0,
                'debit' => $currentProfitLosskredit,
                'kredit' => 0,
                'ending_balance' => 0,
                'ebs' => 'D',
            ];
        } else {
            // Jika totalDebit lebih besar atau sama, tampilkan di kolom kredit
            $data['currProfitLosskredit'] = [
                'general_account' => 'CurrentProfitLoss',
                'general_name' => 'Current Month Profit/Loss',
                'beginning_balance' => 0,
                'debit' => 0,
                'kredit' => $currentProfitLosskredit,
                'ending_balance' => 0,
                'ebs' => 'C',
            ];
        }

        // Hitung total
        $data['total'] = [
            'beginning_balance' => array_sum(array_column($groupedData, 'beginning_balance')),
            'debit' => array_sum(array_column($groupedData, 'debit')),
            'kredit' => array_sum(array_column($groupedData, 'kredit')),
            'ending_balance' => array_sum(array_column($groupedData, 'ending_balance')),
        ];

        $data['reportData'] = $groupedData;
        $data['m_date'] = $m_date;
        $data['y_date'] = $y_date;

        $tgl = now()->format('Ymd_His');
        try {
            $fileNm = "Trial_Balance_Summary-".$tgl.".xlsx";
            return Excel::download(
                new ExportTbSummaryXls(
                    $data['reportData'],
                    $data['m_date'],
                    $data['y_date'],
                    $data['total'],
                    $data['currProfitLosskredit'],
                ),
                $fileNm
            );
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }


}