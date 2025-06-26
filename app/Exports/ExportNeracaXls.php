<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportNeracaXls implements FromView
{
    protected $year;
    protected $months;
    protected $assetData;
    protected $liabilityData;
    protected $currentMonth;
    protected $assetTotals;
    protected $liabilityTotals;

    public function __construct($year, $months, $assetData, $liabilityData, $currentMonth, $assetTotals, $liabilityTotals)
    {
        $this->year = $year;
        $this->months = $months;
        $this->assetData = $assetData;
        $this->liabilityData = $liabilityData;
        $this->currentMonth = $currentMonth;
        $this->assetTotals = $assetTotals;
        $this->liabilityTotals = $liabilityTotals;
    }

    public function view(): View
    {
        return view('reporting.xls_rptNeracaSina', [
            'year' => $this->year,
            'months' => $this->months,
            'assetData' => $this->assetData,
            'liabilityData' => $this->liabilityData,
            'currentMonth' => $this->currentMonth,
            'assetTotals' => $this->assetTotals,
            'liabilityTotals' => $this->liabilityTotals
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
