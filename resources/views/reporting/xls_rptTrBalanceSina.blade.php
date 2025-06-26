<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody>
        <tr>
            <td align="left" valign="bottom" style="font-size: 8; font-weight: bold;">PT. BTS</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="8" align="center" valign="bottom" style="font-size: 8;text-align: center; font-weight: bold;">TRIAL BALANCE</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="8" align="center" valign="bottom" style="font-size: 8;text-align: center; font-weight: bold;">Periode Date : {{ $m_date }}/{{ $y_date }}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td style="font-size: 8; text-align: left; font-weight: bold;border-bottom: 1px solid black">Account No.</td>
            <td style="font-size: 8; text-align: left; font-weight: bold;border-bottom: 1px solid black">Description</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;border-bottom: 1px solid black">Beginning Balance</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;border-bottom: 1px solid black"></td>
            <td style="font-size: 8; text-align: right; font-weight: bold;border-bottom: 1px solid black">Debit Transaction</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;border-bottom: 1px solid black">Credit Transaction</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;border-bottom: 1px solid black">Ending Balance</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;border-bottom: 1px solid black">D/C</td>
        </tr>

        @php
            $prevAccountNo = null;
        @endphp

        @foreach ($reportData as $account)                
            @if (isset($account['is_general_account']) && $account['is_general_account'])
                <tr>
                    <td colspan="8">&nbsp;</td>
                </tr>
                <tr class="font-weight-bold">
                    <td colspan="8" style="font-size: 8;">{{ $account['general_account'] }}</td>
                </tr>
                <tr>
                    <td colspan="8">&nbsp;</td>
                </tr>
                @php $prevAccountNo = null; @endphp
            @elseif (isset($account['is_subtotal']) && $account['is_subtotal'])
                <tr class="font-weight-bold bg-light">
                    <td colspan="2" style="font-size: 8;">Subtotal General {{ $account['general_account'] }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format(abs($account['subtotal']['beginning_balance']), 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;"></td>
                    <td style="font-size: 8; text-align: right;">{{ number_format(abs($account['subtotal']['debit']), 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format(abs($account['subtotal']['credit']), 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format(abs($account['subtotal']['ending_balance']), 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;"></td>
                </tr>
            @else
                <tr>
                    <td style="font-size: 8;">="
                        @if($account['account_no'] != $prevAccountNo)
                            {{ $account['account_no'] }}
                        @endif"</td>
                    <td style="font-size: 8;">
                        @if(!empty($account['code_cost']))
                            {{ $account['code_cost'] }} - {{ $account['cost_center_name'] }}
                        @elseif($account['account_no'] != $prevAccountNo)
                            {{ strip_tags($account['account_name']) }}
                        @endif</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format(abs($account['beginning_balance']), 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ $account['dc1'] }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format(abs($account['debit']), 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format(abs($account['credit']), 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format(abs($account['ending_balance']), 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ $account['dc2'] }}</td>
                </tr>
                @php $prevAccountNo = $account['account_no']; @endphp
            @endif
        @endforeach
        <tr>
            <td></td>
            <td style="font-size: 8;"><strong>CURRENT MONTH PROFIT/LOSS</strong></td>
            <td></td>
            <td></td>
            <td style="font-size: 8; text-align: right;">{{ number_format(abs($currProfitLosskredit['debit']), 2, ',', '.') }}</td>
            <td style="font-size: 8; text-align: right;">{{ number_format(abs($currProfitLosskredit['kredit']), 2, ',', '.') }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="8">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 8; text-align: center; border-top: 1px solid black;"><strong>T o t a l :</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format(abs($total['beginning_balance']), 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format(abs($total['debit']), 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format(abs($total['credit']), 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format(abs($total['ending_balance']), 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"></td>
        </tr>
    </tbody>
</table>