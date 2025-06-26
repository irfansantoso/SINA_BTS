<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\reporting\ProcessEndingBalanceController;
use App\Models\ProcessEndingBalanceModel;
use App\Models\JournalGroupSinaModel;
use App\Models\TempAccountingPeriodSinaModel;
use App\Models\AccountingPeriodSinaModel;
use App\Exports\ExportNeracaXls;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Session;

class RptNeracaSinaController extends Controller
{
    public function rptNeracaSina_browse()
    {           

        ProcessEndingBalanceModel::truncate();

        $journalGroupSina= JournalGroupSinaModel::all();

        if (Auth::check()) {
            $getYearActive = TempAccountingPeriodSinaModel::select('year','month', 'code_period')
                                    ->where('user_acc_period', Auth::user()->username)
                                    ->first(); // Fetch a single record
        } else {
            return redirect()->route('login');
        }
        $syear = $getYearActive->year;

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $syear : '';

        $ProcessEndingBalanceController = new ProcessEndingBalanceController();
        for ($month = 1; $month <= $getYearActive->month; $month++) {
            $ProcessEndingBalanceController->processEndBalSina($month,$syear,1,9999);
        }

        $data['title'] = 'Reporting '.$showYearActive;
        return view('reporting/rptNeracaSina', $data, compact('journalGroupSina','syear'));
    }

    

    public function rptNeracaSina_setPeriode($month,$year)
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

    public function rptNeracaSina_modal($year)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Ambil periode aktif
        $periodActive = TempAccountingPeriodSinaModel::select('year', 'month', 'code_period')
                            ->where('user_acc_period', Auth::user()->username)
                            ->first();

        if (!$periodActive) {
            abort(404, 'Periode akuntansi tidak ditemukan');
        }

        $months = [
            'JANUARI', 'PEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
            'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'
        ];

        $currentMonth = $periodActive->month;
        
        // Ambil data akun
        $assetAccounts = DB::table('tb_account_list')
            ->where('account_no', 'LIKE', '1%')
            ->orderBy('general_account')
            ->orderBy('account_no')
            ->get();

        $liabilityAccounts = DB::table('tb_account_list')
            ->where(function($query) {
                $query->where('account_no', 'LIKE', '2%')
                      ->orWhere('account_no', 'LIKE', '3%');
            })
            ->orderBy('general_account')
            ->orderBy('account_no')
            ->get();

        // Fungsi untuk mendapatkan saldo per bulan
        $getMonthlyBalance = function($accountNo, $year, $currentMonth) {
            $balances = [];
            $shortYear = substr($year, 2, 2); // Format tahun 2 digit
            
            for ($month = 1; $month <= $currentMonth; $month++) {
                $code_periode = $shortYear . str_pad($month, 2, '0', STR_PAD_LEFT);
                
                $balance = DB::table('temp_tb_ending_balance')
                    ->where('account_no', $accountNo)
                    ->where('code_periode', $code_periode)
                    ->value('nominal');
                    
                $balances[$month] = $balance ?? 0;
            }
            
            return $balances;
        };

        // Format angka
        $formatNumber = function($number) {
            return $number == 0 ? ' - ' : number_format($number, 2, ',', '.');
        };

        // Proses data aktiva
        $assetData = $this->processAccountData($assetAccounts, $year, $currentMonth, $getMonthlyBalance, $formatNumber);
        $assetTotals = $this->calculateGroupTotals($assetData, $currentMonth);
        
        // Proses data pasiva
        $liabilityData = $this->processAccountData($liabilityAccounts, $year, $currentMonth, $getMonthlyBalance, $formatNumber);
        $liabilityTotals = $this->calculateGroupTotals($liabilityData, $currentMonth);

        return view('reporting/rptNeracaSinaModal', [
            'year' => $year,
            'months' => array_slice($months, 0, $currentMonth),
            'assetData' => $assetData,
            'liabilityData' => $liabilityData,
            'currentMonth' => $currentMonth,
            'assetTotals' => $assetTotals,
            'liabilityTotals' => $liabilityTotals
        ]);
    }    


    public function rptNeracaSina_xls($year)
    {        
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Ambil periode aktif
        $periodActive = TempAccountingPeriodSinaModel::select('year', 'month', 'code_period')
                            ->where('user_acc_period', Auth::user()->username)
                            ->first();

        if (!$periodActive) {
            abort(404, 'Periode akuntansi tidak ditemukan');
        }

        $months = [
            'JANUARI', 'PEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
            'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'
        ];

        $currentMonth = $periodActive->month;
        
        // Ambil data akun
        $assetAccounts = DB::table('tb_account_list')
            ->where('account_no', 'LIKE', '1%')
            ->orderBy('general_account')
            ->orderBy('account_no')
            ->get();

        $liabilityAccounts = DB::table('tb_account_list')
            ->where(function($query) {
                $query->where('account_no', 'LIKE', '2%')
                      ->orWhere('account_no', 'LIKE', '3%');
            })
            ->orderBy('general_account')
            ->orderBy('account_no')
            ->get();

        // Fungsi untuk mendapatkan saldo per bulan
        $getMonthlyBalance = function($accountNo, $year, $currentMonth) {
            $balances = [];
            $shortYear = substr($year, 2, 2); // Format tahun 2 digit
            
            for ($month = 1; $month <= $currentMonth; $month++) {
                $code_periode = $shortYear . str_pad($month, 2, '0', STR_PAD_LEFT);
                
                $balance = DB::table('temp_tb_ending_balance')
                    ->where('account_no', $accountNo)
                    ->where('code_periode', $code_periode)
                    ->value('nominal');
                    
                $balances[$month] = $balance ?? 0;
            }
            
            return $balances;
        };

        // Format angka
        $formatNumber = function($number) {
            return $number == 0 ? ' - ' : number_format($number, 2, ',', '.');
        };

        // Proses data aktiva
        $assetData = $this->processAccountData($assetAccounts, $year, $currentMonth, $getMonthlyBalance, $formatNumber);
        $assetTotals = $this->calculateGroupTotals($assetData, $currentMonth);
        
        // Proses data pasiva
        $liabilityData = $this->processAccountData($liabilityAccounts, $year, $currentMonth, $getMonthlyBalance, $formatNumber);
        $liabilityTotals = $this->calculateGroupTotals($liabilityData, $currentMonth);

        $data['year'] = $year;
        $data['months'] = array_slice($months, 0, $currentMonth);
        $data['assetData'] = $assetData;
        $data['liabilityData'] = $liabilityData;
        $data['currentMonth'] = $currentMonth;
        $data['assetTotals'] = $assetTotals;
        $data['liabilityTotals'] = $liabilityTotals;    

        $tgl = now()->format('Ymd_His');
        try {
            $fileNm = "Neraca-BTS_".$tgl.".xlsx";
            return Excel::download(
                new ExportNeracaXls(
                    $data['year'],
                    $data['months'],
                    $data['assetData'],
                    $data['liabilityData'],
                    $data['currentMonth'],
                    $data['assetTotals'],
                    $data['liabilityTotals']
                ),
                $fileNm
            );
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }


    // Fungsi untuk memproses data akun
    protected function processAccountData($accounts, $year, $currentMonth, $getMonthlyBalance, $formatNumber)
    {
        $result = [];
        $currentGroup = null;
        $groupAccounts = []; // Untuk menyimpan account dalam group saat ini

        foreach ($accounts as $account) {
            $balances = $getMonthlyBalance($account->account_no, $year, $currentMonth);
            
            // Cek apakah ada nilai di minimal satu bulan
            $hasValue = false;
            foreach ($balances as $balance) {
                if ($balance != 0) {
                    $hasValue = true;
                    break;
                }
            }

            // Jika berganti group, proses group sebelumnya
            if ($account->general_account != $currentGroup) {
                // Jika group sebelumnya memiliki account dengan nilai, tambahkan ke result
                if (!empty($groupAccounts)) {
                    // Tambahkan group header
                    $generalAccount = DB::table('tb_account_list')
                        ->where('account_no', $currentGroup)
                        ->first();
                    
                    $result[] = [
                        'account_no' => $currentGroup,
                        'account_name' => $generalAccount->account_name ?? $currentGroup,
                        'is_group' => true,
                        'balances' => array_map($formatNumber, array_fill(1, $currentMonth, 0))
                    ];
                    
                    // Tambahkan semua account dalam group
                    $result = array_merge($result, $groupAccounts);
                }
                
                // Reset untuk group baru
                $currentGroup = $account->general_account;
                $groupAccounts = [];
            }

            // Tambahkan account jika memiliki nilai di minimal satu bulan
            if ($hasValue) {
                $groupAccounts[] = [
                    'account_no' => $account->account_no,
                    'account_name' => $account->account_name,
                    'is_group' => false,
                    'group_name' => $currentGroup,
                    'balances' => array_map($formatNumber, $balances)
                ];
            }
        }

        // Proses group terakhir
        if (!empty($groupAccounts)) {
            $generalAccount = DB::table('tb_account_list')
                ->where('account_no', $currentGroup)
                ->first();
            
            $result[] = [
                'account_no' => $currentGroup,
                'account_name' => $generalAccount->account_name ?? $currentGroup,
                'is_group' => true,
                'balances' => array_map($formatNumber, array_fill(1, $currentMonth, 0))
            ];
            
            $result = array_merge($result, $groupAccounts);
        }

        return $result;
    }

    // Fungsi untuk menghitung total kelompok aktiva dan pasiva
    protected function calculateGroupTotals($accountData, $currentMonth)
    {
        $totals = array_fill(1, $currentMonth, 0);
        
        foreach ($accountData as $account) {
            if (!$account['is_group']) {
                for ($month = 1; $month <= $currentMonth; $month++) {
                    // Hapus format angka untuk perhitungan
                    $balance = str_replace(['.', ','], ['', '.'], $account['balances'][$month]);
                    $balance = floatval($balance);
                    $totals[$month] += $balance;
                }
            }
        }
        
        return $totals;
    }

}
