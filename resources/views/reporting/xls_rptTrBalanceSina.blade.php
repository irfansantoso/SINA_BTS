<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody>
        <tr>
            <td align="left" valign="bottom" style="font-size: 8; font-weight: bold;">PT. BTJ</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="7" align="center" valign="bottom" style="font-size: 8;text-align: center; font-weight: bold;">TRIAL BALANCE</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="7" align="center" valign="bottom" style="font-size: 8;text-align: center; font-weight: bold;">Periode Date : {{ $m_date }}/{{ $y_date }}</td>
        </tr>
        <tr>
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
            <td style="font-size: 8; text-align: right; font-weight: bold;border-bottom: 1px solid black">Debit Transaction</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;border-bottom: 1px solid black">Credit Transaction</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;border-bottom: 1px solid black">Ending Balance</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;border-bottom: 1px solid black">D/C</td>
        </tr>

        @foreach ($reportData as $account)                
                
            @if (isset($account['is_general_account']) && $account['is_general_account'])
                <tr>
                    <td colspan="7">&nbsp;</td>
                </tr>
                <tr class="font-weight-bold">
                    <td colspan="7" style="font-size: 8;">{{ $account['general_account'] }}</td>
                </tr>
                <tr>
                    <td colspan="7">&nbsp;</td>
                </tr>
            @elseif (isset($account['is_subtotal']) && $account['is_subtotal'])
                <tr>
                    <td colspan="2" style="font-size: 8;">Subtotal General {{ $account['general_account'] }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format($account['subtotal']['beginning_balance'], 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format($account['subtotal']['debit'], 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format($account['subtotal']['credit'], 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format($account['subtotal']['ending_balance'], 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;"></td>
                </tr>
            @else
                <tr>
                    <!-- CATATAN ="{{ $account['account_no'] }}" kenapa pakai = "{{}}" agar dirubah menjadi string khusus untuk di excel -->
                    <td style="font-size: 8;">="{{ $account['account_no'] }}" </td>
                    <td style="font-size: 8;">{{ $account['account_name'] }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format($account['beginning_balance'], 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format($account['debit'], 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format($account['credit'], 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ number_format($account['ending_balance'], 2, ',', '.') }}</td>
                    <td style="font-size: 8; text-align: right;">{{ $account['dc'] }}</td>
                </tr>
            @endif
        @endforeach
        <tr>
            <td colspan="7">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 8; text-align: center; border-top: 1px solid black;"><strong>T o t a l :</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format($total['beginning_balance'], 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format($total['debit'], 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format($total['credit'], 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format($total['ending_balance'], 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"></td>
        </tr>
    </tbody>
</table>