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

        // Ambil periode
        $getPeriode = AccountingPeriodSinaModel::select('code_period')
            ->where('year', $y_date)
            ->where('month', $m_date)
            ->first();

        if (!$getPeriode) {
            return abort(404, 'Periode tidak ditemukan.');
        }

        $code_period = $getPeriode->code_period;

        // Ambil data journal details (transaksi bulan berjalan)
        $journalDetails = DB::table('tb_journal_detail as jd')
            ->join('tb_journal_header as jh', 'jd.journal_head_id', '=', 'jh.id_journal_head')
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
            ->orderBy('jd.account_no')
            ->get();

        // Ambil saldo awal (beginning balance) dari bulan sebelumnya
        $beginningBalances = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'jd.account_no',
                DB::raw("SUM(
                    CASE
                        WHEN COALESCE(jd.debit, 0) = 0 THEN COALESCE(jd.kredit, 0)
                        ELSE COALESCE(jd.debit, 0) - COALESCE(jd.kredit, 0)
                    END
                ) as beginning_balance"),
                DB::raw("MAX(
                    CASE
                        WHEN COALESCE(jd.debit, 0) = 0 THEN 'C'
                        ELSE 'D'
                    END
                ) as d_c")
            )
            ->where('jd.code_period', '<', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('jd.account_no')
            ->havingRaw("SUM(
                CASE
                    WHEN COALESCE(jd.debit, 0) = 0 THEN COALESCE(jd.kredit, 0)
                    ELSE COALESCE(jd.debit, 0) - COALESCE(jd.kredit, 0)
                END
            ) <> 0")
            ->get()
            ->keyBy('account_no');

        // Gabungkan data dari $journalDetails dan $beginningBalances
        $allAccounts = collect();

        // Tambahkan data dari $journalDetails
        foreach ($journalDetails as $detail) {
            $allAccounts->push([
                'general_account' => $detail->general_account,
                'account_no' => $detail->account_no,
                'account_name' => $detail->account_name,
                'debit' => (float) $detail->debit,
                'kredit' => (float) $detail->kredit,
            ]);
        }

        // Ambil semua account_no dari $beginningBalances yang belum ada di $journalDetails
        $missingAccountNos = $beginningBalances->keys()->diff($allAccounts->pluck('account_no'));

        // Ambil general_account dan account_name dari database untuk account_no yang belum ada di $journalDetails
        $missingAccounts = DB::table('tb_account_list')
            ->whereIn('account_no', $missingAccountNos)
            ->select('account_no', 'general_account', 'account_name')
            ->get();

        // Tambahkan data dari $beginningBalances yang belum ada di $journalDetails
        foreach ($missingAccounts as $account) {
            $allAccounts->push([
                'general_account' => $account->general_account, // Ambil general_account dari database
                'account_no' => $account->account_no,
                'account_name' => $account->account_name, // Ambil account_name dari database
                'debit' => 0, // Tidak ada transaksi pada bulan berjalan
                'kredit' => 0, // Tidak ada transaksi pada bulan berjalan
            ]);
        }

        // Proses data yang sudah digabungkan
        $groupedData = [];
        foreach ($allAccounts as $detail) {
            $generalAccount = $detail['general_account'];
            $accountNo = $detail['account_no'];

            // Ambil saldo awal untuk account_no tertentu
            $beginningBalanceData = $beginningBalances->get($accountNo);
            $beginningBalance = $beginningBalanceData ? $beginningBalanceData->beginning_balance : 0;
            $d_c = $beginningBalanceData ? $beginningBalanceData->d_c : 'D';

            $debit = (float) $detail['debit'];
            $credit = (float) $detail['kredit'];

            // Hitung saldo akhir
            if ($d_c === 'C') {
                $endingBalance = $beginningBalance + $credit - $debit;
                $end_dc = $endingBalance >= 0 ? 'C' : 'D';
            } else {
                $endingBalance = $beginningBalance + $debit - $credit;
                $end_dc = $endingBalance >= 0 ? 'D' : 'C';
            }

            // Kelompokkan data berdasarkan general_account
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

            // Tambahkan detail untuk account_no
            $groupedData[$generalAccount]['details'][$accountNo] = [
                'account_name' => $detail['account_name'],
                'beginning_balance' => $beginningBalance,
                'bbs' => $d_c,
                'debit' => $debit,
                'credit' => $credit,
                'ending_balance' => $endingBalance,
                'ebs' => $end_dc,
            ];

            // Hitung subtotal untuk general_account
            $groupedData[$generalAccount]['subtotal']['beginning_balance'] += $beginningBalance;
            $groupedData[$generalAccount]['subtotal']['debit'] += $debit;
            $groupedData[$generalAccount]['subtotal']['credit'] += $credit;
            $groupedData[$generalAccount]['subtotal']['ending_balance'] += $endingBalance;
        }

        // Siapkan data untuk ditampilkan
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
            $getAccName_x = $generalAccount . " - " . $getAccName->account_name;

            // Tambahkan header general account
            $reportData[] = [
                'is_general_account' => true,
                'general_account' => $getAccName_x,
                'account_no' => '',
                'account_name' => '',
                'beginning_balance' => '',
                'dc1' => '',
                'debit' => '',
                'credit' => '',
                'ending_balance' => '',
                'dc2' => '',
            ];

            // Tambahkan detail per account_no
            foreach ($group['details'] as $accountNo => $account) {
                $reportData[] = [
                    'account_no' => $accountNo,
                    'account_name' => $account['account_name'],
                    'beginning_balance' => $account['beginning_balance'],
                    'dc1' => $account['bbs'],
                    'debit' => $account['debit'],
                    'credit' => $account['credit'],
                    'ending_balance' => $account['ending_balance'],
                    'dc2' => $account['ebs'],
                ];
            }

            // Tambahkan subtotal untuk general_account
            $reportData[] = [
                'account_no' => '',
                'account_name' => 'Subtotal :',
                'beginning_balance' => $group['subtotal']['beginning_balance'],
                'dc1' => '',
                'debit' => $group['subtotal']['debit'],
                'credit' => $group['subtotal']['credit'],
                'ending_balance' => $group['subtotal']['ending_balance'],
                'dc2' => '',
            ];

            // Hitung total keseluruhan
            $total['beginning_balance'] += $group['subtotal']['beginning_balance'];
            $total['debit'] += $group['subtotal']['debit'];
            $total['credit'] += $group['subtotal']['credit'];
            $total['ending_balance'] += $group['subtotal']['ending_balance'];
        }

        $totalkredit = $journalDetails->where(function ($item) {
            return str_starts_with($item->general_account, '4') || str_starts_with($item->general_account, '81');
        })->sum('kredit');

        $totalDebit = $journalDetails->where(function ($item) {
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

        // Siapkan data untuk view
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

        // Ambil periode
        $getPeriode = AccountingPeriodSinaModel::select('code_period')
            ->where('year', $y_date)
            ->where('month', $m_date)
            ->first();

        if (!$getPeriode) {
            return abort(404, 'Periode tidak ditemukan.');
        }

        $code_period = $getPeriode->code_period;

        // Ambil data journal details (transaksi bulan berjalan)
        $journalDetails = DB::table('tb_journal_detail as jd')
            ->join('tb_journal_header as jh', 'jd.journal_head_id', '=', 'jh.id_journal_head')
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
            ->orderBy('jd.account_no')
            ->get();

        // Ambil saldo awal (beginning balance) dari bulan sebelumnya
        $beginningBalances = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'jd.account_no',
                DB::raw("SUM(
                    CASE
                        WHEN COALESCE(jd.debit, 0) = 0 THEN COALESCE(jd.kredit, 0)
                        ELSE COALESCE(jd.debit, 0) - COALESCE(jd.kredit, 0)
                    END
                ) as beginning_balance"),
                DB::raw("MAX(
                    CASE
                        WHEN COALESCE(jd.debit, 0) = 0 THEN 'C'
                        ELSE 'D'
                    END
                ) as d_c")
            )
            ->where('jd.code_period', '<', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('jd.account_no')
            ->havingRaw("SUM(
                CASE
                    WHEN COALESCE(jd.debit, 0) = 0 THEN COALESCE(jd.kredit, 0)
                    ELSE COALESCE(jd.debit, 0) - COALESCE(jd.kredit, 0)
                END
            ) <> 0")
            ->get()
            ->keyBy('account_no');

        // Gabungkan data dari $journalDetails dan $beginningBalances
        $allAccounts = collect();

        // Tambahkan data dari $journalDetails
        foreach ($journalDetails as $detail) {
            $allAccounts->push([
                'general_account' => $detail->general_account,
                'account_no' => $detail->account_no,
                'account_name' => $detail->account_name,
                'debit' => (float) $detail->debit,
                'kredit' => (float) $detail->kredit,
            ]);
        }

        // Ambil semua account_no dari $beginningBalances yang belum ada di $journalDetails
        $missingAccountNos = $beginningBalances->keys()->diff($allAccounts->pluck('account_no'));

        // Ambil general_account dan account_name dari database untuk account_no yang belum ada di $journalDetails
        $missingAccounts = DB::table('tb_account_list')
            ->whereIn('account_no', $missingAccountNos)
            ->select('account_no', 'general_account', 'account_name')
            ->get();

        // Tambahkan data dari $beginningBalances yang belum ada di $journalDetails
        foreach ($missingAccounts as $account) {
            $allAccounts->push([
                'general_account' => $account->general_account, // Ambil general_account dari database
                'account_no' => $account->account_no,
                'account_name' => $account->account_name, // Ambil account_name dari database
                'debit' => 0, // Tidak ada transaksi pada bulan berjalan
                'kredit' => 0, // Tidak ada transaksi pada bulan berjalan
            ]);
        }

        // Proses data yang sudah digabungkan
        $groupedData = [];
        foreach ($allAccounts as $detail) {
            $generalAccount = $detail['general_account'];
            $accountNo = $detail['account_no'];

            // Ambil saldo awal untuk account_no tertentu
            $beginningBalanceData = $beginningBalances->get($accountNo);
            $beginningBalance = $beginningBalanceData ? $beginningBalanceData->beginning_balance : 0;
            $d_c = $beginningBalanceData ? $beginningBalanceData->d_c : 'D';

            $debit = (float) $detail['debit'];
            $credit = (float) $detail['kredit'];

            // Hitung saldo akhir
            if ($d_c === 'C') {
                $endingBalance = $beginningBalance + $credit - $debit;
                $end_dc = $endingBalance >= 0 ? 'C' : 'D';
            } else {
                $endingBalance = $beginningBalance + $debit - $credit;
                $end_dc = $endingBalance >= 0 ? 'D' : 'C';
            }

            // Kelompokkan data berdasarkan general_account
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

            // Tambahkan detail untuk account_no
            $groupedData[$generalAccount]['details'][$accountNo] = [
                'account_name' => $detail['account_name'],
                'beginning_balance' => $beginningBalance,
                'bbs' => $d_c,
                'debit' => $debit,
                'credit' => $credit,
                'ending_balance' => $endingBalance,
                'ebs' => $end_dc,
            ];

            // Hitung subtotal untuk general_account
            $groupedData[$generalAccount]['subtotal']['beginning_balance'] += $beginningBalance;
            $groupedData[$generalAccount]['subtotal']['debit'] += $debit;
            $groupedData[$generalAccount]['subtotal']['credit'] += $credit;
            $groupedData[$generalAccount]['subtotal']['ending_balance'] += $endingBalance;
        }

        // Siapkan data untuk ditampilkan
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
            $getAccName_x = $generalAccount . " - " . $getAccName->account_name;

            // Tambahkan header general account
            $reportData[] = [
                'is_general_account' => true,
                'general_account' => $getAccName_x,
                'account_no' => '',
                'account_name' => '',
                'beginning_balance' => '',
                'dc1' => '',
                'debit' => '',
                'credit' => '',
                'ending_balance' => '',
                'dc2' => '',
            ];

            // Tambahkan detail per account_no
            foreach ($group['details'] as $accountNo => $account) {
                $reportData[] = [
                    'account_no' => $accountNo,
                    'account_name' => $account['account_name'],
                    'beginning_balance' => $account['beginning_balance'],
                    'dc1' => $account['bbs'],
                    'debit' => $account['debit'],
                    'credit' => $account['credit'],
                    'ending_balance' => $account['ending_balance'],
                    'dc2' => $account['ebs'],
                ];
            }

            // Tambahkan subtotal untuk general_account
            $reportData[] = [
                'account_no' => '',
                'account_name' => 'Subtotal :',
                'beginning_balance' => $group['subtotal']['beginning_balance'],
                'dc1' => '',
                'debit' => $group['subtotal']['debit'],
                'credit' => $group['subtotal']['credit'],
                'ending_balance' => $group['subtotal']['ending_balance'],
                'dc2' => '',
            ];

            // Hitung total keseluruhan
            $total['beginning_balance'] += $group['subtotal']['beginning_balance'];
            $total['debit'] += $group['subtotal']['debit'];
            $total['credit'] += $group['subtotal']['credit'];
            $total['ending_balance'] += $group['subtotal']['ending_balance'];
        }

        $totalkredit = $journalDetails->where(function ($item) {
            return str_starts_with($item->general_account, '4') || str_starts_with($item->general_account, '81');
        })->sum('kredit');

        $totalDebit = $journalDetails->where(function ($item) {
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


        $data['reportData'] = $reportData;
        $data['total'] = $total;
        $data['m_date'] = $m_date;
        $data['y_date'] = $y_date;       

        $tgl = now()->format('Ymd_His');
        try {
            $fileNm = "Trial_Balance-".$tgl.".xlsx";
            return Excel::download(
                new ExportTrBalanceXls(
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
