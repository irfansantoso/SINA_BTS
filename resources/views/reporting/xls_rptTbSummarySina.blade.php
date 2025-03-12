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
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="8" align="center" valign="bottom" style="font-size: 8;text-align: center; font-weight: bold;">TRIAL BALANCE SUMMARY</td>
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
        <tr>
            <td colspan="7">&nbsp;</td>
        </tr>

        @foreach($reportData as $data)
            <tr>
                <td style="font-size: 8;">="{{ $data['general_account'] }}"</td>
                <td style="font-size: 8;">{{ $data['general_name'] }}</td>
                <td style="font-size: 8; text-align: right;">{{ number_format($data['beginning_balance'], 2, ',', '.') }}</td>
                <td style="font-size: 8; text-align: right;">{{ $data['bbs'] }}</td>
                <td style="font-size: 8; text-align: right;">{{ number_format($data['debit'], 2, ',', '.') }}</td>
                <td style="font-size: 8; text-align: right;">{{ number_format($data['kredit'], 2, ',', '.') }}</td>
                <td style="font-size: 8; text-align: right;">{{ number_format($data['ending_balance'], 2, ',', '.') }}</td>
                <td style="font-size: 8; text-align: right;">{{ $data['ebs'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td></td>
            <td style="font-size: 8;"><strong>CURRENT MONTH PROFIT/LOSS</strong></td>
            <td></td>
            <td></td>
            <td style="font-size: 8; text-align: right;">{{ number_format($currProfitLosskredit['debit'], 2, ',', '.') }}</td>
            <td style="font-size: 8; text-align: right;">{{ number_format($currProfitLosskredit['kredit'], 2, ',', '.') }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 8; text-align: center; border-top: 1px solid black;"><strong>T O T A L :</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format($total['beginning_balance'], 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format($total['debit'], 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format($total['kredit'], 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"><strong>{{ number_format($total['ending_balance'], 2, ',', '.') }}</strong></td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;"></td>
        </tr>
    </tbody>
</table>