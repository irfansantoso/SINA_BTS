<table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse: collapse; color: #000000;">
    <thead>
        <tr style="border-bottom: 2px solid #000;background-color: #e0c267;">
            <th rowspan="2" style="border: 1px solid #000; text-align: center; background-color: #e0c267;">NO.ACC</th>
            <th rowspan="2" style="border: 1px solid #000; text-align: center; background-color: #e0c267;">URAIAN</th>
            @foreach($months as $key => $month)
                <th colspan="1" style="border: 1px solid #000; text-align: center; background-color: #e0c267;">{{ $month }}</th>
            @endforeach
        </tr>
        <tr style="border-bottom: 2px solid #000; background-color: #e0c267;">
            @foreach($months as $key => $month)
                <th style="border: 1px solid #000; text-align: center; background-color: #e0c267;">{{ $year }}</th>
            @endforeach
        </tr>
    </thead>
    
    <tbody>
        <!-- Bagian Aktiva -->
        <tr style="border-top: 2px solid #000;">
            <td colspan="{{ count($months) + 2 }}" style="border: 1px solid #000; font-weight: bold; background-color: #fae4ac;">AKTIVA</td>
        </tr>
        
        @php $currentGroup = null; @endphp
        @foreach($assetData as $item)
            @if($item['is_group'])
                <tr style="border-top: 1px solid #000;">
                    <td style="border: 1px solid #000; font-weight: bold; background-color: #f2f2f2;"><strong>="{{ $item['account_no'] }}"</strong></td>
                    <td style="border: 1px solid #000; font-weight: bold; background-color: #f2f2f2;"><strong>{{ $item['account_name'] }}</strong></td>
                    @foreach($months as $key => $month)
                        <td style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #f2f2f2;">
                            @if(($key + 1) <= $currentMonth)
                                <strong></strong>
                            @else
                                <strong> - </strong>
                            @endif
                        </td>
                    @endforeach
                </tr>
                @php $currentGroup = $item['account_name']; @endphp
            @else
                <tr>
                    <td style="border: 1px solid #000;">="{{ $item['account_no'] }}"</td>
                    <td style="border: 1px solid #000;">{{ $item['account_name'] }}</td>
                    @foreach($months as $key => $month)
                        <td style="border: 1px solid #000; text-align: right;">
                            @if(($key + 1) <= $currentMonth)
                                {{ $item['balances'][$key+1] }}
                            @else
                                - 
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endif
        @endforeach

        <!-- Baris Total Aktiva -->
        <tr style="border-top: 1px solid #000; border-bottom: 2px solid #000;">
            <td colspan="2" style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #fcf4d9; text-align: center;">TOTAL AKTIVA</td>
            @foreach($months as $key => $month)
                <td style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #fcf4d9;">
                    @if(($key + 1) <= $currentMonth)
                        <strong>{{ number_format($assetTotals[$key+1], 2, ',', '.') }}</strong>
                    @else
                        <strong> - </strong>
                    @endif
                </td>
            @endforeach
        </tr>
        
        <!-- Bagian Pasiva -->
        <tr style="border-top: 2px solid #000;">
            <td colspan="{{ count($months) + 2 }}" style="border: 1px solid #000; font-weight: bold; background-color: #fae4ac;">PASIVA</td>
        </tr>
        
        @php $currentGroup = null; @endphp
        @foreach($liabilityData as $item)
            @if($item['is_group'])
                <tr style="border-top: 1px solid #000;">
                    <td style="border: 1px solid #000; font-weight: bold; background-color: #f2f2f2;"><strong>="{{ $item['account_no'] }}"</strong></td>
                    <td style="border: 1px solid #000; font-weight: bold; background-color: #f2f2f2;"><strong>{{ $item['account_name'] }}</strong></td>
                    @foreach($months as $key => $month)
                        <td style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #f2f2f2;">
                            @if(($key + 1) <= $currentMonth)
                                <strong></strong>
                            @else
                                <strong> - </strong>
                            @endif
                        </td>
                    @endforeach
                </tr>
                @php $currentGroup = $item['account_name']; @endphp
            @else
                <tr>
                    <td style="border: 1px solid #000;">="{{ $item['account_no'] }}"</td>
                    <td style="border: 1px solid #000;">{{ $item['account_name'] }}</td>
                    @foreach($months as $key => $month)
                        <td style="border: 1px solid #000; text-align: right;">
                            @if(($key + 1) <= $currentMonth)
                                {{ $item['balances'][$key+1] }}
                            @else
                                - 
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endif
        @endforeach

        <!-- Baris Total Pasiva -->
        <tr style="border-top: 1px solid #000; border-bottom: 2px solid #000;">
            <td colspan="2" style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #fcf4d9; text-align: center;">TOTAL PASIVA</td>
            @foreach($months as $key => $month)
                <td style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #fcf4d9;">
                    @if(($key + 1) <= $currentMonth)
                        <strong>{{ number_format($liabilityTotals[$key+1], 2, ',', '.') }}</strong>
                    @else
                        <strong> - </strong>
                    @endif
                </td>
            @endforeach
        </tr>
    </tbody>
</table>