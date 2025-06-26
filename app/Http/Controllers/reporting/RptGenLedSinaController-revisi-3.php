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

        // Ambil detail jurnal untuk periode yang diminta
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

        // Ambil saldo akhir bulan sebelumnya
        $previousDateStart = '2000-01-01'; // atau bisa juga dari tanggal awal sistem Anda
        $previousDateEnd = date('Y-m-d', strtotime($s_date . ' -1 day'));

        $beginningBalance = DB::table('tb_journal_detail as jd')
            ->selectRaw("
                jd.account_no,
                jd.code_cost,
                SUM(
                    CASE
                        WHEN jd.account_no LIKE '1%' THEN COALESCE(jd.debit, 0) - COALESCE(jd.kredit, 0)
                        WHEN jd.account_no LIKE '2%' THEN COALESCE(jd.kredit, 0) - COALESCE(jd.debit, 0)
                        WHEN jd.account_no LIKE '3%' THEN COALESCE(jd.kredit, 0) - COALESCE(jd.debit, 0)
                        WHEN jd.account_no LIKE '4%' THEN COALESCE(jd.kredit, 0) - COALESCE(jd.debit, 0)
                        ELSE 0
                    END
                ) as beginning_balance,
                CASE
                    WHEN jd.account_no LIKE '1%' THEN 'D'
                    WHEN jd.account_no LIKE '2%' THEN 'C'
                    WHEN jd.account_no LIKE '3%' THEN 'C'
                    WHEN jd.account_no LIKE '4%' THEN 'C'
                    ELSE 'D'
                END as d_c
            ")
            ->where('jd.journal_date', '<', $s_date)
            ->where('jd.account_no','>=', $acc_no)
            ->where('jd.account_no','<=', $acc_no_end)
            ->when($code_cost != "0", function ($query) use ($code_cost) {
                return $query->where('jd.code_cost', $code_cost);
            })
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('jd.account_no', 'jd.code_cost')
            ->get();


        $balance3201 = $beginningBalance->where('account_no', '3202.0001')->sum('beginning_balance');

        $beginningBalance = $beginningBalance->map(function ($item) use ($balance3201) {
            if ($item->account_no == '3201.0001') {
                $item->beginning_balance += $balance3201;
                // Jika saldo negatif, set d_c menjadi 'D'
                if ($item->beginning_balance < 0) {
                    $item->d_c = 'D';
                }
            }
            return $item;
        });

        // Gabungkan semua akun yang ada di saldo awal dan transaksi
        $allAccounts = $beginningBalance->pluck('account_no')->merge($journalDetails->pluck('account_no'))->unique();

        // Proses data untuk laporan
        $reportData = $allAccounts->map(function ($accountNo) use ($journalDetails, $beginningBalance) {
            $items = $journalDetails->where('account_no', $accountNo);
            $accountName = $items->first()->account_name ?? DB::table('tb_account_list')->where('account_no', $accountNo)->value('account_name') ?? 'Unknown';

            // Default DC - ambil dari beginning balance jika ada
            $beginBalanceInfo = $beginningBalance->where('account_no', $accountNo)->first();
            $d_c = $beginBalanceInfo->d_c ?? 'D'; // Default to 'D' if not found
            
            // Override khusus untuk 3201.0001 jika saldo awal negatif
            if ($accountNo == '3201.0001') {
                $totalBeginBalance = $beginningBalance->where('account_no', $accountNo)->sum('beginning_balance');
                if ($totalBeginBalance < 0) {
                    $d_c = 'D';
                }
            }

            $transactions = [];

            // BEGINNING BALANCE (SALDO AWAL) - ditampilkan sekali di awal
            $transactions[] = (object) [
                'formatted_date' => '',
                'journal_no' => '',
                'code_cost' => '',
                'code_div' => '',
                'description_detail' => 'BEGINNING BALANCE',
                'debit' => 0,
                'kredit' => 0,
                'ending_balance' => 0, // Akan diupdate per kelompok cost
                'dc' => $d_c,
                'is_subtotal' => false,
            ];

            // Kelompokkan transaksi berdasarkan code_cost
            $groupedTransactions = $items->groupBy('code_cost');

            $totalDebit = 0;
            $totalCredit = 0;
            $totalEndingBalance = 0;

            foreach ($groupedTransactions as $codeCost => $group) {
                // Ambil saldo awal untuk kelompok cost center ini
                $beginBalanceForCost = $beginningBalance
                    ->where('account_no', $accountNo)
                    ->where('code_cost', $codeCost)
                    ->sum('beginning_balance');
                
                $currentBalance = $beginBalanceForCost;
                $subTotalDebit = 0;
                $subTotalCredit = 0;

                foreach ($group as $transaction) {
                    // Hitung saldo berjalan
                    if ($d_c == 'D') {
                        $currentBalance += ($transaction->debit ?? 0) - ($transaction->kredit ?? 0);
                    } else {
                        $currentBalance += ($transaction->kredit ?? 0) - ($transaction->debit ?? 0);
                    }
                    
                    $dc_end = ($currentBalance >= 0) ? $d_c : ($d_c == 'D' ? 'C' : 'D');

                    $transactions[] = (object) [
                        'formatted_date' => $transaction->formatted_date,
                        'journal_no' => $transaction->journal_no,
                        'code_cost' => $transaction->code_cost,
                        'code_div' => $transaction->code_div,
                        'description_detail' => $transaction->description_detail,
                        'debit' => $transaction->debit,
                        'kredit' => $transaction->kredit,
                        'ending_balance' => abs($currentBalance),
                        'dc' => $dc_end,
                        'is_subtotal' => false,
                    ];

                    $subTotalDebit += $transaction->debit;
                    $subTotalCredit += $transaction->kredit;
                }

                // Update total
                $totalDebit += $subTotalDebit;
                $totalCredit += $subTotalCredit;
                $totalEndingBalance += $currentBalance;

                // Sub Total per kelompok cost center
                $transactions[] = (object) [
                    'formatted_date' => '',
                    'journal_no' => '',
                    'code_cost' => '',
                    'code_div' => '',
                    'description_detail' => 'Sub Total Cost :',
                    'debit' => $subTotalDebit,
                    'kredit' => $subTotalCredit,
                    'ending_balance' => abs($currentBalance),
                    'dc' => ($currentBalance >= 0) ? $d_c : ($d_c == 'D' ? 'C' : 'D'),
                    'is_subtotal' => true,
                ];
            }

            // Update beginning balance transaction with total beginning balance
            $totalBeginBalance = $beginningBalance->where('account_no', $accountNo)->sum('beginning_balance');
            if (count($transactions) > 0) {
                $transactions[0]->ending_balance = abs($totalBeginBalance);
                // Khusus untuk 3201.0001, jika saldo awal negatif, set dc menjadi 'D'
                if ($accountNo == '3201.0001' && $totalBeginBalance < 0) {
                    $transactions[0]->dc = 'D';
                }
            }

            return [
                'account_no' => $accountNo,
                'account_name' => $accountName,
                'beginning_balance' => $totalBeginBalance,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'ending_balance' => abs($totalEndingBalance),
                'dc' => ($totalEndingBalance >= 0) ? $d_c : ($d_c == 'D' ? 'C' : 'D'),
                'transactions' => $transactions,
            ];
        })->sortBy('account_no');

        // Hitung total untuk footer laporan
        $data['totalDebit'] = $reportData->sum('debit');
        $data['totalCredit'] = $reportData->sum('credit');
        // Perbaikan perhitungan total balance
        $data['totalBalance'] = $reportData->sum(function ($account) {
            $balance = $account['ending_balance'];
            $dc = $account['dc'];
            
            // Penanganan khusus untuk akun 3201.0001
            if ($account['account_no'] == '3201.0001') {
                // Jika beginning balance negatif, perlakukan sebagai debit
                if ($account['beginning_balance'] < 0) {
                    return $balance;
                }
                return -$balance;
            }
            
            // Untuk akun modal lainnya (dimulai dengan 3)
            if (str_starts_with($account['account_no'], '3')) {
                return -$balance; // Normal saldo credit untuk akun modal
            }
            
            // Untuk akun lainnya (aktiva, dll)
            return ($dc == 'D') ? $balance : -$balance;
        });

        // Pastikan total balance adalah 0 jika debit dan credit sudah balance
        if ($data['totalDebit'] == $data['totalCredit']) {
            $data['totalBalance'] = 0;
        }
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

        // Ambil detail jurnal untuk periode yang diminta
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

        // Ambil saldo akhir bulan sebelumnya
        $previousDateStart = '2000-01-01'; // atau bisa juga dari tanggal awal sistem Anda
        $previousDateEnd = date('Y-m-d', strtotime($s_date . ' -1 day'));

        $beginningBalance = DB::table('tb_journal_detail as jd')
            ->selectRaw("
                jd.account_no,
                SUM(
                    CASE
                        WHEN jd.account_no LIKE '1%' THEN COALESCE(jd.debit, 0) - COALESCE(jd.kredit, 0)
                        WHEN jd.account_no LIKE '2%' THEN COALESCE(jd.kredit, 0) - COALESCE(jd.debit, 0)
                        WHEN jd.account_no LIKE '3%' THEN COALESCE(jd.kredit, 0) - COALESCE(jd.debit, 0)
                        WHEN jd.account_no LIKE '4%' THEN COALESCE(jd.kredit, 0) - COALESCE(jd.debit, 0)
                        ELSE 0
                    END
                ) as beginning_balance,
                CASE
                    WHEN jd.account_no LIKE '1%' THEN 'D'
                    WHEN jd.account_no LIKE '2%' THEN 'C'
                    WHEN jd.account_no LIKE '3%' THEN 'C'
                    WHEN jd.account_no LIKE '4%' THEN 'C'
                    ELSE 'D'
                END as d_c
            ")
            ->where('jd.journal_date', '<', $s_date)
            ->where('jd.account_no','>=', $acc_no)
            ->where('jd.account_no','<=', $acc_no_end)
            ->when($code_cost != "0", function ($query) use ($code_cost) {
                return $query->where('jd.code_cost', $code_cost);
            })
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('jd.account_no')
            ->get();

        // Ambil saldo awal untuk 3202.0001
        $balance3202 = $beginningBalance->firstWhere('account_no', '3202.0001')?->beginning_balance ?? 0;

        // Tambahkan saldo 3202.0001 ke 3201.0001
        $beginningBalance = $beginningBalance->map(function ($item) use ($balance3202) {
            if ($item->account_no == '3201.0001') {
                $item->beginning_balance += $balance3202;
            } elseif ($item->account_no == '3202.0001') {
                $item->beginning_balance = 0;
            }
            return $item;
        });

        // Gabungkan semua akun yang ada di saldo awal dan transaksi
        $allAccounts = $beginningBalance->pluck('account_no')->merge($journalDetails->pluck('account_no'))->unique();

        // Proses data untuk laporan
        $reportData = $allAccounts->map(function ($accountNo) use ($journalDetails, $beginningBalance) {
            $items = $journalDetails->where('account_no', $accountNo);
            $beginBalance = $beginningBalance->firstWhere('account_no', $accountNo)?->beginning_balance ?? 0;
            $d_c = $beginningBalance->firstWhere('account_no', $accountNo)?->d_c ?? 'D';

            $accountName = $items->first()->account_name ?? DB::table('tb_account_list')->where('account_no', $accountNo)->value('account_name') ?? 'Unknown';

            $transactions = [];
            $currentBalance = $beginBalance;

            // BEGINNING BALANCE (SALDO AWAL)
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

                foreach ($group as $transaction) {
                    // Hitung saldo berjalan
                    if ($d_c == 'D') {
                        $currentBalance += ($transaction->debit ?? 0) - ($transaction->kredit ?? 0);
                    } else {
                        $currentBalance += ($transaction->kredit ?? 0) - ($transaction->debit ?? 0);
                    }
                    
                    $dc_end = ($currentBalance >= 0) ? $d_c : ($d_c == 'D' ? 'C' : 'D');

                    $transactions[] = (object) [
                        'formatted_date' => $transaction->formatted_date,
                        'journal_no' => $transaction->journal_no,
                        'code_cost' => $transaction->code_cost,
                        'code_div' => $transaction->code_div,
                        'description_detail' => $transaction->description_detail,
                        'debit' => $transaction->debit,
                        'kredit' => $transaction->kredit,
                        'ending_balance' => abs($currentBalance),
                        'dc' => $dc_end,
                        'is_subtotal' => false,
                    ];

                    $subTotalDebit += $transaction->debit;
                    $subTotalCredit += $transaction->kredit;
                }

                // Sub Total per kelompok cost center
                $transactions[] = (object) [
                    'formatted_date' => '',
                    'journal_no' => '',
                    'code_cost' => '',
                    'code_div' => '',
                    'description_detail' => 'Sub Total Cost :',
                    'debit' => $subTotalDebit,
                    'kredit' => $subTotalCredit,
                    'ending_balance' => abs($currentBalance),
                    'dc' => ($currentBalance >= 0) ? $d_c : ($d_c == 'D' ? 'C' : 'D'),
                    'is_subtotal' => true,
                ];
            }

            // Hitung total debit dan kredit
            $totalDebit = $items->sum('debit');
            $totalCredit = $items->sum('kredit');
            
            // Hitung saldo akhir
            $endingBalance = $beginBalance;
            if ($d_c == 'D') {
                $endingBalance += $totalDebit - $totalCredit;
            } else {
                $endingBalance += $totalCredit - $totalDebit;
            }

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
