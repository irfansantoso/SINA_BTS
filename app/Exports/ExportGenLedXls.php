<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportGenLedXls implements FromView
{
    protected $reportData;
    protected $s_date;
    protected $e_date;
    protected $totalDebit;
    protected $totalCredit;
    protected $totalBalance;

    public function __construct($reportData, $s_date, $e_date, $totalDebit, $totalCredit, $totalBalance)
    {
        $this->reportData = $reportData;
        $this->s_date = $s_date;
        $this->e_date = $e_date;
        $this->totalDebit = $totalDebit;
        $this->totalCredit = $totalCredit;
        $this->totalBalance = $totalBalance;
    }

    public function view(): View
    {
        return view('reporting.xls_rptGenLedSina', [
            'reportData' => $this->reportData,
            's_date' => $this->s_date,
            'e_date' => $this->e_date,
            'totalDebit' => $this->totalDebit,
            'totalCredit' => $this->totalCredit,
            'totalBalance' => $this->totalBalance,
        ]);
    }

    public static function afterSheet(AfterSheet $event)
    {
        $default_font_style = [
            'font' => ['name' => 'Calibri', 'size' => 8]
        ];


        // Get Worksheet
        $active_sheet = $event->sheet->getDelegate();

        // $active_sheet->getStyle('A1:F1')->applyFromArray($default_font_style);

    }
}
