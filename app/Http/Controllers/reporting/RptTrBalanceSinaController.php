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

        // Ambil semua akun dalam rentang yang diminta
        $allAccountList = DB::table('tb_account_list')
            ->whereBetween('account_no', [$acc_no, $acc_no_end])
            ->select('account_no', 'general_account', 'account_name')
            ->orderBy('account_no')
            ->get()
            ->keyBy('account_no');

        // Validasi jika ada account yang tidak ditemukan
        $missingAccounts = [];
        $start = (float) str_replace('.', '', $acc_no);
        $end = (float) str_replace('.', '', $acc_no_end);
        
        for ($i = $start; $i <= $end; $i++) {
            $accountNo = substr($i, 0, 4) . '.' . substr($i, 4, 4);
            if (!isset($allAccountList[$accountNo])) {
                $missingAccounts[] = $accountNo;
            }
        }

        // Ambil semua general account yang terlibat
        $generalAccounts = DB::table('tb_account_list')
            ->whereIn('account_no', function($query) use ($acc_no, $acc_no_end) {
                $query->select('general_account')
                    ->from('tb_account_list')
                    ->whereBetween('account_no', [$acc_no, $acc_no_end]);
            })
            ->orWhereBetween('account_no', [$acc_no, $acc_no_end])
            ->select('account_no', 'account_name')
            ->orderBy('account_no')
            ->get()
            ->keyBy('account_no');

        // Ambil data journal details
        $journalDetails = DB::table('tb_journal_detail as jd')
            ->join('tb_journal_header as jh', 'jd.journal_head_id', '=', 'jh.id_journal_head')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'acc.general_account',
                'jd.account_no',
                'acc.account_name',
                'jd.code_cost',
                DB::raw('SUM(jd.debit) as debit'),
                DB::raw('SUM(jd.kredit) as kredit')
            )
            ->where('jd.code_period', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('acc.general_account', 'jd.account_no', 'acc.account_name', 'jd.code_cost')
            ->orderBy('jd.account_no')
            ->orderBy('jd.code_cost')
            ->get();

        // Kelompokkan journal details
        $journalDetailsGrouped = [];
        foreach ($journalDetails as $detail) {
            $journalDetailsGrouped[$detail->account_no][$detail->code_cost] = $detail;
        }

        // Ambil saldo awal dari bulan sebelumnya
        $beginningBalances = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'jd.account_no',
                'jd.code_cost',
                DB::raw("SUM(COALESCE(jd.debit, 0)) - SUM(COALESCE(jd.kredit, 0)) AS beginning_balance"),
                DB::raw("
                    CASE
                        WHEN SUM(COALESCE(jd.debit, 0)) - SUM(COALESCE(jd.kredit, 0)) >= 0 THEN 'D'
                        ELSE 'C'
                    END AS d_c
                ")
            )
            ->where('jd.code_period', '<', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('jd.account_no', 'jd.code_cost')
            ->orderBy('jd.account_no')
            ->orderBy('jd.code_cost')
            ->get();


        // Kelompokkan beginning balances
        $beginningBalancesGrouped = [];
        foreach ($beginningBalances as $balance) {
            $beginningBalancesGrouped[$balance->account_no][$balance->code_cost] = $balance;
        }

        // Gabungkan semua data
        $combinedData = [];
        $costCenters = [];
        
        foreach ($allAccountList as $accountNo => $account) {
            $hasJournalDetails = isset($journalDetailsGrouped[$accountNo]);
            $hasBeginningBalances = isset($beginningBalancesGrouped[$accountNo]);
            
            if (!$hasJournalDetails && !$hasBeginningBalances) {
                continue;
            }
            
            $accountCostCenters = [];
            
            if ($hasJournalDetails) {
                $accountCostCenters = array_merge($accountCostCenters, array_keys($journalDetailsGrouped[$accountNo]));
            }
            
            if ($hasBeginningBalances) {
                $accountCostCenters = array_merge($accountCostCenters, array_keys($beginningBalancesGrouped[$accountNo]));
            }
            
            $accountCostCenters = array_unique($accountCostCenters);
            $costCenters = array_merge($costCenters, $accountCostCenters);
            
            if (empty($accountCostCenters)) {
                $accountCostCenters = [null];
            }
            
            foreach ($accountCostCenters as $codeCost) {
                $journalDetail = $hasJournalDetails && isset($journalDetailsGrouped[$accountNo][$codeCost]) 
                    ? $journalDetailsGrouped[$accountNo][$codeCost] 
                    : null;
                    
                $beginningBalance = $hasBeginningBalances && isset($beginningBalancesGrouped[$accountNo][$codeCost]) 
                    ? $beginningBalancesGrouped[$accountNo][$codeCost] 
                    : null;
                
                $debit = $journalDetail ? (float) $journalDetail->debit : 0;
                $credit = $journalDetail ? (float) $journalDetail->kredit : 0;
                
                $beginBalance = $beginningBalance ? $beginningBalance->beginning_balance : 0;

                $firstAccountNo = (int) substr((string) $accountNo, 0, 1);
                if($firstAccountNo == '2'){
                    // $d_c = $beginningBalance ? $beginningBalance->d_c : 'C';
                    $d_c = $beginBalance < 0 ? 'C' : 'D';
                }else{
                    $d_c = $beginningBalance ? $beginningBalance->d_c : 'D';
                }
                
                // Handle khusus untuk akun 3202.0001 dan 3201.0001
                if ($accountNo == '3202.0001') {
                    $balance3202 = $beginBalance;
                    $beginBalance = 0;
                } elseif ($accountNo == '3201.0001') {
                    $balance3202 = ($beginningBalancesGrouped['3202.0001'][$codeCost] ?? null) 
                        ? $beginningBalancesGrouped['3202.0001'][$codeCost]->beginning_balance 
                        : 0;
                    $beginBalance += $balance3202;
                }
                                

                if ($d_c == 'C') {
                    
                    if($firstAccountNo == '2'){
                        $endingBalance = abs($beginBalance) + $credit - $debit;
                        $end_dc = abs($endingBalance) <= 0 ? 'D' : 'C';
                    }else{
                        $endingBalance = abs($beginBalance) + $credit - $debit;
                        $end_dc = abs($endingBalance) >= 0 ? 'C' : 'D';
                    }
                    
                } else {
                    $endingBalance = abs($beginBalance) + $debit - $credit;
                    
                    if($firstAccountNo == '2'){
                        $end_dc = abs($endingBalance) <= 0 ? 'D' : 'C';
                    }else{
                        $end_dc = $endingBalance >= 0 ? 'D' : 'C';
                    }
                }
                
                if ($beginBalance != 0 || $debit != 0 || $credit != 0 || $endingBalance != 0) {
                    $key = $accountNo . ($codeCost ? '|' . $codeCost : '');
                    
                    $combinedData[$key] = [
                        'general_account' => $account->general_account,
                        'account_no' => $accountNo,
                        'account_name' => $account->account_name,
                        'code_cost' => $codeCost,
                        'beginning_balance' => $beginBalance,
                        'bbs' => $d_c,
                        'debit' => $debit,
                        'credit' => $credit,
                        'ending_balance' => $endingBalance,
                        'ebs' => $end_dc,
                    ];
                }
            }
        }
        
        // Hitung laba rugi
        $totalKredit = collect($combinedData)
            ->filter(function ($item) {
                return str_starts_with($item['general_account'], '4') ||
                       str_starts_with($item['general_account'], '5') ||
                       str_starts_with($item['general_account'], '6') ||
                       str_starts_with($item['general_account'], '7') ||
                       str_starts_with($item['general_account'], '8') ||
                       str_starts_with($item['general_account'], '9');
            })
            ->sum('credit');

        $totalDebit = collect($combinedData)
            ->filter(function ($item) {
                return str_starts_with($item['general_account'], '4') ||
                       str_starts_with($item['general_account'], '5') ||
                       str_starts_with($item['general_account'], '6') ||
                       str_starts_with($item['general_account'], '7') ||
                       str_starts_with($item['general_account'], '8') ||
                       str_starts_with($item['general_account'], '9');
            })
            ->sum('debit');

        $currentProfitLosskredit = $totalKredit - $totalDebit;
        $currentProfitLossValue = abs($currentProfitLosskredit);

        $previousProfitLossData = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                DB::raw("SUM(COALESCE(jd.kredit, 0)) - SUM(COALESCE(jd.debit, 0)) as profit_loss")
            )
            ->where('jd.code_period', '<', $code_period)
            ->where(function ($query) {
                $query->where('jd.account_no', 'like', '4%')
                      ->orWhere('jd.account_no', 'like', '5%')
                      ->orWhere('jd.account_no', 'like', '6%')
                      ->orWhere('jd.account_no', 'like', '7%')
                      ->orWhere('jd.account_no', 'like', '8%')
                      ->orWhere('jd.account_no', 'like', '9%');
            })
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->first();

        $previousProfitLoss = $previousProfitLossData ? $previousProfitLossData->profit_loss : 0;

        // Tambahkan akun 3202.0001 secara manual jika ada nilai laba rugi
        if ($currentProfitLossValue != 0 && isset($allAccountList['3202.0001'])) {
            $key = '3202.0001';
            
            // if ($currentProfitLosskredit > 0) {
            //     $endingBalance3202 = $currentProfitLossValue;
            //     $end_dc3202 = 'C';
            //     $debit3202 = 0;
            //     $credit3202 = $endingBalance3202;
            // } else {
            //     $endingBalance3202 = $previousProfitLoss-$currentProfitLossValue;
            //     $end_dc3202 = 'D';
            //     $debit3202 = $currentProfitLossValue;
            //     $credit3202 = 0;
            // }

            if ($currentProfitLosskredit > 0) {
                $endingBalance3202 = $previousProfitLoss+$currentProfitLossValue;
                $end_dc3202 = 'D';
                $debit3202 = 0;
                $credit3202 = $currentProfitLossValue;
            } else {
                $endingBalance3202 = $previousProfitLoss-$currentProfitLossValue;
                $end_dc3202 = 'D';
                $debit3202 = $currentProfitLossValue;
                $credit3202 = 0;
            }

            // $end_dc3202 = $endingBalance3202 >= 0 ? 'D' : 'C';
            
            $combinedData[$key] = [
                'general_account' => $allAccountList['3202.0001']->general_account,
                'account_no' => '3202.0001',
                'account_name' => $allAccountList['3202.0001']->account_name,
                'code_cost' => null,
                'beginning_balance' => $previousProfitLoss,
                'bbs' => 'D',
                'debit' => $debit3202,
                'credit' => $credit3202,
                'ending_balance' => $endingBalance3202,
                'ebs' => $end_dc3202,
            ];
            
            if (!in_array(null, $costCenters)) {
                $costCenters[] = null;
            }
        }

        // Ambil informasi cost center
        $costCenters = array_unique($costCenters);
        $costCenterNames = [];
        
        if (!empty($costCenters)) {
            $costCenterData = DB::table('tb_cost')
                ->whereIn('code_cost', $costCenters)
                ->select('code_cost', 'cost_description')
                ->get();
                
            foreach ($costCenterData as $cost) {
                $costCenterNames[$cost->code_cost] = $cost->cost_description;
            }
        }

        // Kelompokkan data berdasarkan general_account
        $groupedData = [];
        
        uksort($combinedData, function($a, $b) {
            list($aAcc, $aCost) = array_pad(explode('|', $a), 2, null);
            list($bAcc, $bCost) = array_pad(explode('|', $b), 2, null);
            
            if ($aAcc == $bAcc) {
                return strcmp($aCost ?? '', $bCost ?? '');
            }
            return strcmp($aAcc, $bAcc);
        });
        
        foreach ($combinedData as $account) {
            $generalAccount = $account['general_account'];
            
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
            
            $groupedData[$generalAccount]['details'][] = $account;
            
            $groupedData[$generalAccount]['subtotal']['beginning_balance'] += $account['beginning_balance'];
            $groupedData[$generalAccount]['subtotal']['debit'] += $account['debit'];
            $groupedData[$generalAccount]['subtotal']['credit'] += $account['credit'];
            $groupedData[$generalAccount]['subtotal']['ending_balance'] += $account['ending_balance'];
        }

        $groupedData = array_filter($groupedData, function($group) {
            return !empty($group['details']);
        });

        ksort($groupedData);

        // Siapkan data untuk ditampilkan
        $reportData = [];
        $total = [
            'beginning_balance' => 0,
            'debit' => 0,
            'credit' => 0,
            'ending_balance' => 0,
        ];

        foreach ($groupedData as $generalAccount => $group) {
            $generalAccountName = isset($generalAccounts[$generalAccount])
                ? $generalAccount . " - " . $generalAccounts[$generalAccount]->account_name
                : (isset($allAccountList[$generalAccount])
                    ? $generalAccount . " - " . $allAccountList[$generalAccount]->account_name
                    : $generalAccount . " - Unknown Account");

            $reportData[] = [
                'is_general_account' => true,
                'general_account' => $generalAccountName,
                'account_no' => '',
                'account_name' => '',
                'code_cost' => '',
                'cost_center_name' => '',
                'beginning_balance' => '',
                'dc1' => '',
                'debit' => '',
                'credit' => '',
                'ending_balance' => '',
                'dc2' => '',
            ];

            foreach ($group['details'] as $account) {
                $costCenterName = $account['code_cost'] 
                    ? ($costCenterNames[$account['code_cost']] ?? $account['code_cost'])
                    : '';
                    
                $reportData[] = [
                    'account_no' => $account['account_no'],
                    'account_name' => $account['account_name'],
                    'code_cost' => $account['code_cost'],
                    'cost_center_name' => $costCenterName,
                    'beginning_balance' => $account['beginning_balance'],
                    'dc1' => $account['bbs'],
                    'debit' => $account['debit'],
                    'credit' => $account['credit'],
                    'ending_balance' => $account['ending_balance'],
                    'dc2' => $account['ebs'],
                ];
            }

            // Untuk perhitungan Subtotal
            $firstGenAcc = (int) substr((string) $generalAccount, 0, 1);
            $firstTwoDigits = (int) substr((string) $generalAccount, 0, 2);
            $balance = $group['subtotal']['beginning_balance'] ?? null;

            if ($firstTwoDigits == "16") {
                $dc_sub = 'C';
            } elseif ($firstGenAcc == "2") {                
                $dc_sub = is_null($balance) || $balance < 0 ? 'C' : 'D';
            } elseif ($firstTwoDigits == "31") {
                $dc_sub = 'C';
            } elseif ($firstTwoDigits == "32") {
                $dc_sub = 'D';            
            } else {
                $dc_sub = $group['subtotal']['beginning_balance'] < 0 ? 'C' : 'D';
            }

            if ($dc_sub == 'C') {                    
                if($firstGenAcc == '2'){
                    $endBalanceSub = abs($group['subtotal']['beginning_balance']) + $group['subtotal']['credit'] - $group['subtotal']['debit'];
                    $end_dc_sub = abs($group['subtotal']['beginning_balance']) <= 0 ? 'D' : 'C';
                }else{
                    $endBalanceSub = abs($group['subtotal']['beginning_balance']) + $group['subtotal']['credit'] - $group['subtotal']['debit'];
                    $end_dc_sub = abs($group['subtotal']['beginning_balance']) >= 0 ? 'C' : 'D';
                }                
            } else {
                $endBalanceSub = abs($group['subtotal']['beginning_balance']) + $group['subtotal']['debit'] - $group['subtotal']['credit'];
                
                if($firstGenAcc == '2'){
                    $end_dc_sub = abs($endBalanceSub) <= 0 ? 'D' : 'C';
                }else{
                    $end_dc_sub = $endBalanceSub >= 0 ? 'D' : 'C';
                }
            }            

            $reportData[] = [
                'account_no' => '',
                'account_name' => '<strong>Subtotal :</strong>',
                'code_cost' => '',
                'cost_center_name' => '',
                'beginning_balance' => $group['subtotal']['beginning_balance'],
                'dc1' => $dc_sub,
                'debit' => $group['subtotal']['debit'],
                'credit' => $group['subtotal']['credit'],
                'ending_balance' => $endBalanceSub,
                'dc2' => $end_dc_sub,
            ];

            $total['beginning_balance'] += $group['subtotal']['beginning_balance'];
            $total['debit'] += $group['subtotal']['debit'];
            $total['credit'] += $group['subtotal']['credit'];
            // $total['ending_balance'] += $group['subtotal']['ending_balance'];
        }

        // Tambahkan Current Profit/Loss ke data dan total
        if ($currentProfitLosskredit != 0) {
            if ($currentProfitLosskredit > 0) {
                $total['debit'] += $currentProfitLossValue;
                
                $data['currProfitLosskredit'] = [
                    'general_account' => 'CurrentProfitLoss',
                    'general_name' => 'Current Month Profit/Loss',
                    'beginning_balance' => 0,
                    'debit' => $currentProfitLossValue,
                    'kredit' => 0,
                    'ending_balance' => 0,
                    'ebs' => 'D',
                ];
            } else {
                $total['credit'] += $currentProfitLossValue;
                
                $data['currProfitLosskredit'] = [
                    'general_account' => 'CurrentProfitLoss',
                    'general_name' => 'Current Month Profit/Loss',
                    'beginning_balance' => 0,
                    'debit' => 0,
                    'kredit' => $currentProfitLossValue,
                    'ending_balance' => 0,
                    'ebs' => 'C',
                ];
            }
        }

        // $total['beginning_balance'] = $previousProfitLoss;
        $total['ending_balance'] = $total['beginning_balance'] + $total['debit'] - $total['credit'];
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

        // Ambil semua akun dalam rentang yang diminta
        $allAccountList = DB::table('tb_account_list')
            ->whereBetween('account_no', [$acc_no, $acc_no_end])
            ->select('account_no', 'general_account', 'account_name')
            ->orderBy('account_no')
            ->get()
            ->keyBy('account_no');

        // Validasi jika ada account yang tidak ditemukan
        $missingAccounts = [];
        $start = (float) str_replace('.', '', $acc_no);
        $end = (float) str_replace('.', '', $acc_no_end);
        
        for ($i = $start; $i <= $end; $i++) {
            $accountNo = substr($i, 0, 4) . '.' . substr($i, 4, 4);
            if (!isset($allAccountList[$accountNo])) {
                $missingAccounts[] = $accountNo;
            }
        }

        // Ambil semua general account yang terlibat
        $generalAccounts = DB::table('tb_account_list')
            ->whereIn('account_no', function($query) use ($acc_no, $acc_no_end) {
                $query->select('general_account')
                    ->from('tb_account_list')
                    ->whereBetween('account_no', [$acc_no, $acc_no_end]);
            })
            ->orWhereBetween('account_no', [$acc_no, $acc_no_end])
            ->select('account_no', 'account_name')
            ->orderBy('account_no')
            ->get()
            ->keyBy('account_no');

        // Ambil data journal details
        $journalDetails = DB::table('tb_journal_detail as jd')
            ->join('tb_journal_header as jh', 'jd.journal_head_id', '=', 'jh.id_journal_head')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'acc.general_account',
                'jd.account_no',
                'acc.account_name',
                'jd.code_cost',
                DB::raw('SUM(jd.debit) as debit'),
                DB::raw('SUM(jd.kredit) as kredit')
            )
            ->where('jd.code_period', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('acc.general_account', 'jd.account_no', 'acc.account_name', 'jd.code_cost')
            ->orderBy('jd.account_no')
            ->orderBy('jd.code_cost')
            ->get();

        // Kelompokkan journal details
        $journalDetailsGrouped = [];
        foreach ($journalDetails as $detail) {
            $journalDetailsGrouped[$detail->account_no][$detail->code_cost] = $detail;
        }

        // Ambil saldo awal dari bulan sebelumnya
        $beginningBalances = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                'jd.account_no',
                'jd.code_cost',
                DB::raw("SUM(COALESCE(jd.debit, 0)) - SUM(COALESCE(jd.kredit, 0)) AS beginning_balance"),
                DB::raw("
                    CASE
                        WHEN SUM(COALESCE(jd.debit, 0)) - SUM(COALESCE(jd.kredit, 0)) >= 0 THEN 'D'
                        ELSE 'C'
                    END AS d_c
                ")
            )
            ->where('jd.code_period', '<', $code_period)
            ->whereBetween('jd.account_no', [$acc_no, $acc_no_end])
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->groupBy('jd.account_no', 'jd.code_cost')
            ->orderBy('jd.account_no')
            ->orderBy('jd.code_cost')
            ->get();


        // Kelompokkan beginning balances
        $beginningBalancesGrouped = [];
        foreach ($beginningBalances as $balance) {
            $beginningBalancesGrouped[$balance->account_no][$balance->code_cost] = $balance;
        }

        // Gabungkan semua data
        $combinedData = [];
        $costCenters = [];
        
        foreach ($allAccountList as $accountNo => $account) {
            $hasJournalDetails = isset($journalDetailsGrouped[$accountNo]);
            $hasBeginningBalances = isset($beginningBalancesGrouped[$accountNo]);
            
            if (!$hasJournalDetails && !$hasBeginningBalances) {
                continue;
            }
            
            $accountCostCenters = [];
            
            if ($hasJournalDetails) {
                $accountCostCenters = array_merge($accountCostCenters, array_keys($journalDetailsGrouped[$accountNo]));
            }
            
            if ($hasBeginningBalances) {
                $accountCostCenters = array_merge($accountCostCenters, array_keys($beginningBalancesGrouped[$accountNo]));
            }
            
            $accountCostCenters = array_unique($accountCostCenters);
            $costCenters = array_merge($costCenters, $accountCostCenters);
            
            if (empty($accountCostCenters)) {
                $accountCostCenters = [null];
            }
            
            foreach ($accountCostCenters as $codeCost) {
                $journalDetail = $hasJournalDetails && isset($journalDetailsGrouped[$accountNo][$codeCost]) 
                    ? $journalDetailsGrouped[$accountNo][$codeCost] 
                    : null;
                    
                $beginningBalance = $hasBeginningBalances && isset($beginningBalancesGrouped[$accountNo][$codeCost]) 
                    ? $beginningBalancesGrouped[$accountNo][$codeCost] 
                    : null;
                
                $debit = $journalDetail ? (float) $journalDetail->debit : 0;
                $credit = $journalDetail ? (float) $journalDetail->kredit : 0;
                
                $beginBalance = $beginningBalance ? $beginningBalance->beginning_balance : 0;

                $firstAccountNo = (int) substr((string) $accountNo, 0, 1);
                if($firstAccountNo == '2'){
                    // $d_c = $beginningBalance ? $beginningBalance->d_c : 'C';
                    $d_c = $beginBalance < 0 ? 'C' : 'D';
                }else{
                    $d_c = $beginningBalance ? $beginningBalance->d_c : 'D';
                }
                
                // Handle khusus untuk akun 3202.0001 dan 3201.0001
                if ($accountNo == '3202.0001') {
                    $balance3202 = $beginBalance;
                    $beginBalance = 0;
                } elseif ($accountNo == '3201.0001') {
                    $balance3202 = ($beginningBalancesGrouped['3202.0001'][$codeCost] ?? null) 
                        ? $beginningBalancesGrouped['3202.0001'][$codeCost]->beginning_balance 
                        : 0;
                    $beginBalance += $balance3202;
                }
                                

                if ($d_c == 'C') {
                    
                    if($firstAccountNo == '2'){
                        $endingBalance = abs($beginBalance) + $credit - $debit;
                        $end_dc = abs($endingBalance) <= 0 ? 'D' : 'C';
                    }else{
                        $endingBalance = abs($beginBalance) + $credit - $debit;
                        $end_dc = abs($endingBalance) >= 0 ? 'C' : 'D';
                    }
                    
                } else {
                    $endingBalance = abs($beginBalance) + $debit - $credit;
                    
                    if($firstAccountNo == '2'){
                        $end_dc = abs($endingBalance) <= 0 ? 'D' : 'C';
                    }else{
                        $end_dc = $endingBalance >= 0 ? 'D' : 'C';
                    }
                }
                
                if ($beginBalance != 0 || $debit != 0 || $credit != 0 || $endingBalance != 0) {
                    $key = $accountNo . ($codeCost ? '|' . $codeCost : '');
                    
                    $combinedData[$key] = [
                        'general_account' => $account->general_account,
                        'account_no' => $accountNo,
                        'account_name' => $account->account_name,
                        'code_cost' => $codeCost,
                        'beginning_balance' => $beginBalance,
                        'bbs' => $d_c,
                        'debit' => $debit,
                        'credit' => $credit,
                        'ending_balance' => $endingBalance,
                        'ebs' => $end_dc,
                    ];
                }
            }
        }
        
        // Hitung laba rugi
        $totalKredit = collect($combinedData)
            ->filter(function ($item) {
                return str_starts_with($item['general_account'], '4') ||
                       str_starts_with($item['general_account'], '5') ||
                       str_starts_with($item['general_account'], '6') ||
                       str_starts_with($item['general_account'], '7') ||
                       str_starts_with($item['general_account'], '8') ||
                       str_starts_with($item['general_account'], '9');
            })
            ->sum('credit');

        $totalDebit = collect($combinedData)
            ->filter(function ($item) {
                return str_starts_with($item['general_account'], '4') ||
                       str_starts_with($item['general_account'], '5') ||
                       str_starts_with($item['general_account'], '6') ||
                       str_starts_with($item['general_account'], '7') ||
                       str_starts_with($item['general_account'], '8') ||
                       str_starts_with($item['general_account'], '9');
            })
            ->sum('debit');

        $currentProfitLosskredit = $totalKredit - $totalDebit;
        $currentProfitLossValue = abs($currentProfitLosskredit);

        $previousProfitLossData = DB::table('tb_journal_detail as jd')
            ->join('tb_account_list as acc', 'jd.account_no', '=', 'acc.account_no')
            ->select(
                DB::raw("SUM(COALESCE(jd.kredit, 0)) - SUM(COALESCE(jd.debit, 0)) as profit_loss")
            )
            ->where('jd.code_period', '<', $code_period)
            ->where(function ($query) {
                $query->where('jd.account_no', 'like', '4%')
                      ->orWhere('jd.account_no', 'like', '5%')
                      ->orWhere('jd.account_no', 'like', '6%')
                      ->orWhere('jd.account_no', 'like', '7%')
                      ->orWhere('jd.account_no', 'like', '8%')
                      ->orWhere('jd.account_no', 'like', '9%');
            })
            ->when($code_div != "0", function ($query) use ($code_div) {
                return $query->where('jd.code_div', $code_div);
            })
            ->first();

        $previousProfitLoss = $previousProfitLossData ? $previousProfitLossData->profit_loss : 0;

        // Tambahkan akun 3202.0001 secara manual jika ada nilai laba rugi
        if ($currentProfitLossValue != 0 && isset($allAccountList['3202.0001'])) {
            $key = '3202.0001';
            
            // if ($currentProfitLosskredit > 0) {
            //     $endingBalance3202 = $currentProfitLossValue;
            //     $end_dc3202 = 'C';
            //     $debit3202 = 0;
            //     $credit3202 = $endingBalance3202;
            // } else {
            //     $endingBalance3202 = $previousProfitLoss-$currentProfitLossValue;
            //     $end_dc3202 = 'D';
            //     $debit3202 = $currentProfitLossValue;
            //     $credit3202 = 0;
            // }

            if ($currentProfitLosskredit > 0) {
                $endingBalance3202 = $previousProfitLoss+$currentProfitLossValue;
                $end_dc3202 = 'D';
                $debit3202 = 0;
                $credit3202 = $currentProfitLossValue;
            } else {
                $endingBalance3202 = $previousProfitLoss-$currentProfitLossValue;
                $end_dc3202 = 'D';
                $debit3202 = $currentProfitLossValue;
                $credit3202 = 0;
            }
            
            $combinedData[$key] = [
                'general_account' => $allAccountList['3202.0001']->general_account,
                'account_no' => '3202.0001',
                'account_name' => $allAccountList['3202.0001']->account_name,
                'code_cost' => null,
                'beginning_balance' => $previousProfitLoss,
                'bbs' => 'D',
                'debit' => $debit3202,
                'credit' => $credit3202,
                'ending_balance' => $endingBalance3202,
                'ebs' => $end_dc3202,
            ];
            
            if (!in_array(null, $costCenters)) {
                $costCenters[] = null;
            }
        }

        // Ambil informasi cost center
        $costCenters = array_unique($costCenters);
        $costCenterNames = [];
        
        if (!empty($costCenters)) {
            $costCenterData = DB::table('tb_cost')
                ->whereIn('code_cost', $costCenters)
                ->select('code_cost', 'cost_description')
                ->get();
                
            foreach ($costCenterData as $cost) {
                $costCenterNames[$cost->code_cost] = $cost->cost_description;
            }
        }

        // Kelompokkan data berdasarkan general_account
        $groupedData = [];
        
        uksort($combinedData, function($a, $b) {
            list($aAcc, $aCost) = array_pad(explode('|', $a), 2, null);
            list($bAcc, $bCost) = array_pad(explode('|', $b), 2, null);
            
            if ($aAcc == $bAcc) {
                return strcmp($aCost ?? '', $bCost ?? '');
            }
            return strcmp($aAcc, $bAcc);
        });
        
        foreach ($combinedData as $account) {
            $generalAccount = $account['general_account'];
            
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
            
            $groupedData[$generalAccount]['details'][] = $account;
            
            $groupedData[$generalAccount]['subtotal']['beginning_balance'] += $account['beginning_balance'];
            $groupedData[$generalAccount]['subtotal']['debit'] += $account['debit'];
            $groupedData[$generalAccount]['subtotal']['credit'] += $account['credit'];
            $groupedData[$generalAccount]['subtotal']['ending_balance'] += $account['ending_balance'];
        }

        $groupedData = array_filter($groupedData, function($group) {
            return !empty($group['details']);
        });

        ksort($groupedData);

        // Siapkan data untuk ditampilkan
        $reportData = [];
        $total = [
            'beginning_balance' => 0,
            'debit' => 0,
            'credit' => 0,
            'ending_balance' => 0,
        ];

        foreach ($groupedData as $generalAccount => $group) {
            $generalAccountName = isset($generalAccounts[$generalAccount])
                ? $generalAccount . " - " . $generalAccounts[$generalAccount]->account_name
                : (isset($allAccountList[$generalAccount])
                    ? $generalAccount . " - " . $allAccountList[$generalAccount]->account_name
                    : $generalAccount . " - Unknown Account");

            $reportData[] = [
                'is_general_account' => true,
                'general_account' => $generalAccountName,
                'account_no' => '',
                'account_name' => '',
                'code_cost' => '',
                'cost_center_name' => '',
                'beginning_balance' => '',
                'dc1' => '',
                'debit' => '',
                'credit' => '',
                'ending_balance' => '',
                'dc2' => '',
            ];

            foreach ($group['details'] as $account) {
                $costCenterName = $account['code_cost'] 
                    ? ($costCenterNames[$account['code_cost']] ?? $account['code_cost'])
                    : '';
                    
                $reportData[] = [
                    'account_no' => $account['account_no'],
                    'account_name' => $account['account_name'],
                    'code_cost' => $account['code_cost'],
                    'cost_center_name' => $costCenterName,
                    'beginning_balance' => $account['beginning_balance'],
                    'dc1' => $account['bbs'],
                    'debit' => $account['debit'],
                    'credit' => $account['credit'],
                    'ending_balance' => $account['ending_balance'],
                    'dc2' => $account['ebs'],
                ];
            }

            // Untuk perhitungan Subtotal
            $firstGenAcc = (int) substr((string) $generalAccount, 0, 1);
            $firstTwoDigits = (int) substr((string) $generalAccount, 0, 2);
            $balance = $group['subtotal']['beginning_balance'] ?? null;

            if ($firstTwoDigits == "16") {
                $dc_sub = 'C';
            } elseif ($firstGenAcc == "2") {                
                $dc_sub = is_null($balance) || $balance < 0 ? 'C' : 'D';
            } elseif ($firstTwoDigits == "31") {
                $dc_sub = 'C';
            } elseif ($firstTwoDigits == "32") {
                $dc_sub = 'D';            
            } else {
                $dc_sub = $group['subtotal']['beginning_balance'] < 0 ? 'C' : 'D';
            }

            if ($dc_sub == 'C') {                    
                if($firstGenAcc == '2'){
                    $endBalanceSub = abs($group['subtotal']['beginning_balance']) + $group['subtotal']['credit'] - $group['subtotal']['debit'];
                    $end_dc_sub = abs($group['subtotal']['beginning_balance']) <= 0 ? 'D' : 'C';
                }else{
                    $endBalanceSub = abs($group['subtotal']['beginning_balance']) + $group['subtotal']['credit'] - $group['subtotal']['debit'];
                    $end_dc_sub = abs($group['subtotal']['beginning_balance']) >= 0 ? 'C' : 'D';
                }                
            } else {
                $endBalanceSub = abs($group['subtotal']['beginning_balance']) + $group['subtotal']['debit'] - $group['subtotal']['credit'];
                
                if($firstGenAcc == '2'){
                    $end_dc_sub = abs($endBalanceSub) <= 0 ? 'D' : 'C';
                }else{
                    $end_dc_sub = $endBalanceSub >= 0 ? 'D' : 'C';
                }
            }            

            $reportData[] = [
                'account_no' => '',
                'account_name' => '<strong>Subtotal :</strong>',
                'code_cost' => '',
                'cost_center_name' => '',
                'beginning_balance' => $group['subtotal']['beginning_balance'],
                'dc1' => $dc_sub,
                'debit' => $group['subtotal']['debit'],
                'credit' => $group['subtotal']['credit'],
                'ending_balance' => $endBalanceSub,
                'dc2' => $end_dc_sub,
            ];

            $total['beginning_balance'] += $group['subtotal']['beginning_balance'];
            $total['debit'] += $group['subtotal']['debit'];
            $total['credit'] += $group['subtotal']['credit'];
            // $total['ending_balance'] += $group['subtotal']['ending_balance'];
        }

        // Tambahkan Current Profit/Loss ke data dan total
        if ($currentProfitLosskredit != 0) {
            if ($currentProfitLosskredit > 0) {
                $total['debit'] += $currentProfitLossValue;
                
                $data['currProfitLosskredit'] = [
                    'general_account' => 'CurrentProfitLoss',
                    'general_name' => 'Current Month Profit/Loss',
                    'beginning_balance' => 0,
                    'debit' => $currentProfitLossValue,
                    'kredit' => 0,
                    'ending_balance' => 0,
                    'ebs' => 'D',
                ];
            } else {
                $total['credit'] += $currentProfitLossValue;
                
                $data['currProfitLosskredit'] = [
                    'general_account' => 'CurrentProfitLoss',
                    'general_name' => 'Current Month Profit/Loss',
                    'beginning_balance' => 0,
                    'debit' => 0,
                    'kredit' => $currentProfitLossValue,
                    'ending_balance' => 0,
                    'ebs' => 'C',
                ];
            }
        }

        $total['ending_balance'] = $total['beginning_balance'] + $total['debit'] - $total['credit'];
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
