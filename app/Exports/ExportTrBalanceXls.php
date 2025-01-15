<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportTrBalanceXls implements FromView
{
    protected $reportData;
    protected $m_date;
    protected $y_date;
    protected $total;

    public function __construct($reportData, $m_date, $y_date, $total)
    {
        $this->reportData = $reportData;
        $this->m_date = $m_date;
        $this->y_date = $y_date;
        $this->total = $total;
    }

    public function view(): View
    {
        return view('reporting.xls_rptTrBalanceSina', [
            'reportData' => $this->reportData,
            'm_date' => $this->m_date,
            'y_date' => $this->y_date,
            'total' => $this->total,
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
