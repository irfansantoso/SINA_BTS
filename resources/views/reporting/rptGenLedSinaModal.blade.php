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
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="10" align="center" valign="bottom" style="font-size: 8;text-align: center; font-weight: bold;">GENERAL LEDGER</td>
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
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="10" align="center" valign="bottom" style="font-size: 8;text-align: center; font-weight: bold;">Periode Date : {{ $s_date }} - {{ $e_date }}</td>
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
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>

        @foreach ($reportData as $account)
        <tr>
            <td colspan="10" style="font-size: 8; text-align: left; border-top: 1px solid black;">
                <strong>{{ $account['account_no'] }} {{ $account['account_name'] }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="10">
                <strong>&nbsp;</strong>
            </td>
        </tr>
        <tr>
            <td style="font-size: 8; text-align: center; font-weight: bold;">Date</td>
            <td style="font-size: 8; text-align: left; font-weight: bold;">Journal No.</td>
            <td style="font-size: 8; text-align: center; font-weight: bold;">Cost</td>
            <td style="font-size: 8; text-align: center; font-weight: bold;">Div.</td>
            <td style="font-size: 8; text-align: left; font-weight: bold;">Description</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;">Debit Transaction</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;">Credit Transaction</td>
            <td style="font-size: 8; text-align: right; font-weight: bold;">Ending Balance</td>
            <td style="font-size: 8; text-align: center; font-weight: bold;">D/C</td>
        </tr>
        <tr>
            <td colspan="10">
                <strong>&nbsp;</strong>
            </td>
        </tr>

        @foreach ($account['transactions'] as $transaction)
        <tr>
            <td style="font-size: 8; text-align: center;">{{ $transaction->formatted_date }}</td>
            <td style="font-size: 8; text-align: left;">{{ $transaction->journal_no }}</td>
            <td style="font-size: 8; text-align: center;">{{ $transaction->code_cost }}</td>
            <td style="font-size: 8; text-align: center;">{{ $transaction->code_div }}</td>
            <td style="font-size: 8; text-align: left;">{{ $transaction->description_detail }}</td>
            <td style="font-size: 8; text-align: right;">{{ number_format($transaction->debit, 2, ',', '.') }}</td>
            <td style="font-size: 8; text-align: right;">{{ number_format($transaction->kredit, 2, ',', '.') }}</td>
            <td style="font-size: 8; text-align: right;">{{ number_format($transaction->ending_balance, 2, ',', '.') }}</td>
            <td style="font-size: 8; text-align: center;">{{ $transaction->dc }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="10">
                <strong>&nbsp;</strong>
            </td>
        </tr>
        <tr>
            <td colspan="5" style="font-size: 8; text-align: right;">Sub Total :</td>
            <td style="font-size: 8; text-align: right;">{{ number_format($account['debit'], 2, ',', '.') }}</td>
            <td style="font-size: 8; text-align: right;">{{ number_format($account['credit'], 2, ',', '.') }}</td>
            <td style="font-size: 8; text-align: right;">{{ number_format($account['ending_balance'], 2, ',', '.') }}</td>
            <td>&nbsp;</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5" style="font-size: 8; text-align: right; border-top: 1px solid black;">
                <strong>T o t a l :</strong>
            </td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;">
                <strong>{{ number_format($totalDebit, 2, ',', '.') }}</strong>
            </td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;">
                <strong>{{ number_format($totalCredit, 2, ',', '.') }}</strong>
            </td>
            <td style="font-size: 8; text-align: right; border-top: 1px solid black;">
                <strong>{{ number_format($totalBalance, 2, ',', '.') }}</strong>
            </td>
            <td>&nbsp;</td>
        </tr>

    </tbody>
</table>
