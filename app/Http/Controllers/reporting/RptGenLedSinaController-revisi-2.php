<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Models\JournalHeaderSinaModel;
use App\Models\JournalDetailSinaModel;
use App\Models\JournalSourceCodeSinaModel;
use App\Models\JournalGroupSinaModel;
use App\Models\TempAccountingPeriodSinaModel;
use App\Models\AccountingPeriodSinaModel;
use App\Exports\ExportGenLedXls;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Session;

class RptGenLedSinaController extends Controller
{
    public function rptGenLedSina_browse()
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
        return view('reporting/rptGenLedSina', $data, compact('journalGroupSina','syear'));
    }

    

    public function rptGenLedSina_setPeriode($month,$year)
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

    public function rptGenLedSina_modal($s_date, $e_date, $acc_no, $acc_no_end, $code_cost, $code_div)
    {
        $data['s_date'] = date('d/m/Y', strtotime($s_date));
        $data['e_date'] = date('d/m/Y', strtotime($e_date));
        $data['acc_no'] = $acc_no;
        $data['acc_no_end'] = $acc_no_end;
        $data['code_cost'] = $code_cost;
        $data['code_div'] = $code_div;

        // Ambil detail jurnal
        $journalDetails = DB::table('tb_journal_detail as jd')
            ->join('tb_journal_header as jh', 'jd.journal_head_id', '=', 'jh.id_journal_head')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->selectRaw("
                jd.account_no,
                acc.account_name,
                DATE_FORMAT(jd.journal_date, '%d/%m/%Y') as formatted_date,
                CONCAT(jh.code_jrc, jh.journal_jrc_no) as journal_no,
                jd.code_cost,
                jd.code_div,
                jd.description_detail,
                jd.debit,
                jd.kredit,
                CASE
                    WHEN COALESCE(jd.debit, 0) = 0 THEN 'C'
                    ELSE 'D'
                END as d_c
            ")
            ->whereBetween('jd.journal_date', [$s_date, $e_date])
            ->where('jd.account_no','>=', $acc_no)
            ->where('jd.account_no','<=', $acc_no_end)
            ->when($code_cost != "0", function ($query) use ($code_cost) {
                return $query->where('jd.code_cost', $code_cost);
            })
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->orderBy('jd.account_no')
            ->orderBy('jd.journal_date')
            ->orderBy('jd.code_cost')
            ->orderBy('jd.id_journal_detail')            
            ->get();

        // Ambil saldo awal untuk semua akun
        $beginningBalance = DB::table('tb_journal_detail')
            ->where('journal_date', '<', $s_date)
            ->where(function($query) {
                $query->where('account_no', 'like', '1%')
                      ->orWhere('account_no', 'like', '2%')
                      ->orWhere('account_no', 'like', '3%')
                      ->orWhere('account_no', 'like', '4%');
            })
            ->selectRaw("
                account_no,
                SUM(
                    CASE
                        WHEN COALESCE(debit, 0) = 0 THEN COALESCE(kredit, 0)
                        ELSE COALESCE(debit, 0) - COALESCE(kredit, 0)
                    END
                ) as beginning_balance,
                MAX(
                    CASE
                        WHEN COALESCE(debit, 0) = 0 THEN 'C'
                        ELSE 'D'
                    END
                ) as d_c
            ")
            ->groupBy('account_no')
            ->get();

        // Ambil saldo awal untuk 3202.0001
        $balance3202 = $beginningBalance->firstWhere('account_no', '3202.0001')?->beginning_balance ?? 0;

        // Tambahkan saldo 3202.0001 ke 3201.0001
        $beginningBalance = $beginningBalance->map(function ($item) use ($balance3202) {
            if ($item->account_no == '3201.0001') {
                $item->beginning_balance += $balance3202; // Tambahkan saldo 3202.0001 ke 3201.0001
            } elseif ($item->account_no == '3202.0001') {
                $item->beginning_balance = 0; // Set saldo 3202.0001 menjadi 0
            }
            return $item;
        });

        $allAccounts = $beginningBalance->pluck('account_no')->merge($journalDetails->pluck('account_no'))->unique();

        $reportData = $allAccounts->map(function ($accountNo) use ($journalDetails, $beginningBalance) {
            $items = $journalDetails->where('account_no', $accountNo);
            $beginBalance = $beginningBalance->firstWhere('account_no', $accountNo)?->beginning_balance ?? 0;
            $d_c = $beginningBalance->firstWhere('account_no', $accountNo)?->d_c ?? 'D';

            $accountName = $items->first()->account_name ?? 'Unknown';
            if ($accountName == 'Unknown') {
                $accountName = DB::table('tb_account_list')->where('account_no', $accountNo)->value('account_name');
            }

            $transactions = [];
            $currentBalance = $beginBalance;

            // BEGINNING BALANCE
            $transactions[] = (object) [
                'formatted_date' => '',
                'journal_no' => '',
                'code_cost' => '',
                'code_div' => '',
                'description_detail' => 'BEGINNING BALANCE',
                'debit' => 0,
                'kredit' => 0,
                'ending_balance' => abs($currentBalance),
                'dc' => $d_c,
                'is_subtotal' => false,
            ];

            // Kelompokkan transaksi berdasarkan code_cost
            $groupedTransactions = $items->groupBy('code_cost');

            foreach ($groupedTransactions as $codeCost => $group) {
                $subTotalDebit = 0;
                $subTotalCredit = 0;
                $groupBalance = $beginBalance; // Reset saldo per kelompok

                foreach ($group as $transaction) {
                    if ($d_c == 'C') {
                        $groupBalance = $groupBalance + ($transaction->kredit ?? 0) - ($transaction->debit ?? 0);
                        $dc_end = ($groupBalance >= 0) ? 'C' : 'D';
                    } else {
                        $groupBalance = $groupBalance + ($transaction->debit ?? 0) - ($transaction->kredit ?? 0);
                        $dc_end = ($groupBalance >= 0) ? 'D' : 'C';
                    }

                    $transactions[] = (object) [
                        'formatted_date' => $transaction->formatted_date,
                        'journal_no' => $transaction->journal_no,
                        'code_cost' => $transaction->code_cost,
                        'code_div' => $transaction->code_div,
                        'description_detail' => $transaction->description_detail,
                        'debit' => $transaction->debit,
                        'kredit' => $transaction->kredit,
                        'ending_balance' => abs($groupBalance),
                        'dc' => $dc_end,
                        'is_subtotal' => false,
                    ];

                    $subTotalDebit += $transaction->debit;
                    $subTotalCredit += $transaction->kredit;
                }

                // Sub Total Cost per kelompok
                $transactions[] = (object) [
                    'formatted_date' => '',
                    'journal_no' => '',
                    'code_cost' => '',
                    'code_div' => '',
                    'description_detail' => 'Sub Total Cost :',
                    'debit' => $subTotalDebit,
                    'kredit' => $subTotalCredit,
                    'ending_balance' => abs($groupBalance),
                    'dc' => ($groupBalance >= 0) ? $d_c : ($d_c == 'D' ? 'C' : 'D'),
                    'is_subtotal' => true,
                ];

                // Update saldo global untuk perhitungan akhir
                $currentBalance = $groupBalance;
            }

            // Hitung total debit, kredit, dan ending balance untuk akun
            $totalDebit = $items->sum('debit');
            $totalCredit = $items->sum('kredit');
            $endingBalance = $beginBalance + $totalDebit - $totalCredit;

            return [
                'account_no' => $accountNo,
                'account_name' => $accountName,
                'beginning_balance' => $beginBalance,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'ending_balance' => abs($endingBalance),
                'dc' => ($endingBalance >= 0) ? $d_c : ($d_c == 'D' ? 'C' : 'D'),
                'transactions' => $transactions,
            ];
        })->sortBy('account_no');

        // Hitung total debit, kredit, dan saldo akhir untuk semua akun
        $data['totalDebit'] = $reportData->sum('debit');
        $data['totalCredit'] = $reportData->sum('credit');
        $data['totalBalance'] = $reportData->sum(function ($account) {
            return ($account['dc'] == 'D' ? 1 : -1) * $account['ending_balance'];
        });
        $data['reportData'] = $reportData;

        return view('reporting/rptGenLedSinaModal', $data);
    }


    public function rptGenLedSina_xls($s_date, $e_date, $acc_no, $acc_no_end, $code_cost, $code_div)
    {
        $data['s_date'] = date('d/m/Y', strtotime($s_date));
        $data['e_date'] = date('d/m/Y', strtotime($e_date));
        $data['acc_no'] = $acc_no;
        $data['acc_no_end'] = $acc_no_end;
        $data['code_cost'] = $code_cost;
        $data['code_div'] = $code_div;

        // Ambil detail jurnal
        $journalDetails = DB::table('tb_journal_detail as jd')
            ->join('tb_journal_header as jh', 'jd.journal_head_id', '=', 'jh.id_journal_head')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->selectRaw("
                jd.account_no,
                acc.account_name,
                DATE_FORMAT(jd.journal_date, '%d/%m/%Y') as formatted_date,
                CONCAT(jh.code_jrc, jh.journal_jrc_no) as journal_no,
                jd.code_cost,
                jd.code_div,
                jd.description_detail,
                jd.debit,
                jd.kredit,
                CASE
                    WHEN COALESCE(jd.debit, 0) = 0 THEN 'C'
                    ELSE 'D'
                END as d_c
            ")
            ->whereBetween('jd.journal_date', [$s_date, $e_date])
            ->where('jd.account_no','>=', $acc_no)
            ->where('jd.account_no','<=', $acc_no_end)
            ->when($code_cost != "0", function ($query) use ($code_cost) {
                return $query->where('jd.code_cost', $code_cost);
            })
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->orderBy('jd.account_no')
            ->orderBy('jd.journal_date')
            ->orderBy('jd.code_cost')
            ->orderBy('jd.id_journal_detail')            
            ->get();

        // Ambil saldo awal untuk semua akun
        $beginningBalance = DB::table('tb_journal_detail')
            ->where('journal_date', '<', $s_date)
            ->where(function($query) {
                $query->where('account_no', 'like', '1%')
                      ->orWhere('account_no', 'like', '2%')
                      ->orWhere('account_no', 'like', '3%')
                      ->orWhere('account_no', 'like', '4%');
            })
            ->selectRaw("
                account_no,
                SUM(
                    CASE
                        WHEN COALESCE(debit, 0) = 0 THEN COALESCE(kredit, 0)
                        ELSE COALESCE(debit, 0) - COALESCE(kredit, 0)
                    END
                ) as beginning_balance,
                MAX(
                    CASE
                        WHEN COALESCE(debit, 0) = 0 THEN 'C'
                        ELSE 'D'
                    END
                ) as d_c
            ")
            ->groupBy('account_no')
            ->get();

        // Ambil saldo awal untuk 3202.0001
        $balance3202 = $beginningBalance->firstWhere('account_no', '3202.0001')?->beginning_balance ?? 0;

        // Tambahkan saldo 3202.0001 ke 3201.0001
        $beginningBalance = $beginningBalance->map(function ($item) use ($balance3202) {
            if ($item->account_no == '3201.0001') {
                $item->beginning_balance += $balance3202; // Tambahkan saldo 3202.0001 ke 3201.0001
            } elseif ($item->account_no == '3202.0001') {
                $item->beginning_balance = 0; // Set saldo 3202.0001 menjadi 0
            }
            return $item;
        });

        $allAccounts = $beginningBalance->pluck('account_no')->merge($journalDetails->pluck('account_no'))->unique();

        $reportData = $allAccounts->map(function ($accountNo) use ($journalDetails, $beginningBalance) {
            $items = $journalDetails->where('account_no', $accountNo);
            $beginBalance = $beginningBalance->firstWhere('account_no', $accountNo)?->beginning_balance ?? 0;
            $d_c = $beginningBalance->firstWhere('account_no', $accountNo)?->d_c ?? 'D';

            $accountName = $items->first()->account_name ?? 'Unknown';
            if ($accountName == 'Unknown') {
                $accountName = DB::table('tb_account_list')->where('account_no', $accountNo)->value('account_name');
            }

            $transactions = [];
            $currentBalance = $beginBalance;

            // BEGINNING BALANCE
            $transactions[] = (object) [
                'formatted_date' => '',
                'journal_no' => '',
                'code_cost' => '',
                'code_div' => '',
                'description_detail' => 'BEGINNING BALANCE',
                'debit' => 0,
                'kredit' => 0,
                'ending_balance' => abs($currentBalance),
                'dc' => $d_c,
                'is_subtotal' => false,
            ];

            // Kelompokkan transaksi berdasarkan code_cost
            $groupedTransactions = $items->groupBy('code_cost');

            foreach ($groupedTransactions as $codeCost => $group) {
                $subTotalDebit = 0;
                $subTotalCredit = 0;
                $groupBalance = $beginBalance; // Reset saldo per kelompok

                foreach ($group as $transaction) {
                    if ($d_c == 'C') {
                        $groupBalance = $groupBalance + ($transaction->kredit ?? 0) - ($transaction->debit ?? 0);
                        $dc_end = ($groupBalance >= 0) ? 'C' : 'D';
                    } else {
                        $groupBalance = $groupBalance + ($transaction->debit ?? 0) - ($transaction->kredit ?? 0);
                        $dc_end = ($groupBalance >= 0) ? 'D' : 'C';
                    }

                    $transactions[] = (object) [
                        'formatted_date' => $transaction->formatted_date,
                        'journal_no' => $transaction->journal_no,
                        'code_cost' => $transaction->code_cost,
                        'code_div' => $transaction->code_div,
                        'description_detail' => $transaction->description_detail,
                        'debit' => $transaction->debit,
                        'kredit' => $transaction->kredit,
                        'ending_balance' => abs($groupBalance),
                        'dc' => $dc_end,
                        'is_subtotal' => false,
                    ];

                    $subTotalDebit += $transaction->debit;
                    $subTotalCredit += $transaction->kredit;
                }

                // Sub Total Cost per kelompok
                $transactions[] = (object) [
                    'formatted_date' => '',
                    'journal_no' => '',
                    'code_cost' => '',
                    'code_div' => '',
                    'description_detail' => 'Sub Total Cost :',
                    'debit' => $subTotalDebit,
                    'kredit' => $subTotalCredit,
                    'ending_balance' => abs($groupBalance),
                    'dc' => ($groupBalance >= 0) ? $d_c : ($d_c == 'D' ? 'C' : 'D'),
                    'is_subtotal' => true,
                ];

                // Update saldo global untuk perhitungan akhir
                $currentBalance = $groupBalance;
            }

            // Hitung total debit, kredit, dan ending balance untuk akun
            $totalDebit = $items->sum('debit');
            $totalCredit = $items->sum('kredit');
            $endingBalance = $beginBalance + $totalDebit - $totalCredit;

            return [
                'account_no' => $accountNo,
                'account_name' => $accountName,
                'beginning_balance' => $beginBalance,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'ending_balance' => abs($endingBalance),
                'dc' => ($endingBalance >= 0) ? $d_c : ($d_c == 'D' ? 'C' : 'D'),
                'transactions' => $transactions,
            ];
        })->sortBy('account_no');

        // Hitung total debit, kredit, dan saldo akhir untuk semua akun
        $data['totalDebit'] = $reportData->sum('debit');
        $data['totalCredit'] = $reportData->sum('credit');
        $data['totalBalance'] = $reportData->sum(function ($account) {
            return ($account['dc'] == 'D' ? 1 : -1) * $account['ending_balance'];
        });

        // $data['totalBalance'] = (($data['totalDebit'] - $data['totalCredit']) + $reportData->sum('beginning_balance'));
        $data['reportData'] = $reportData;

        $tgl = now()->format('Ymd_His');
        $fileNm = "general_ledger-".$tgl.".xlsx";
        return Excel::download(
            new ExportGenLedXls(
                $reportData,
                $data['s_date'],
                $data['e_date'],
                $data['totalDebit'], 
                $data['totalCredit'], 
                $data['totalBalance']
            ),
            $fileNm
        );
    }

}
