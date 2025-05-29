<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>HSN Wise</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .invoice-box {
            width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #eee;
            background: #fff;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            width: 120px;
            height: 90px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ffffff;
            margin-right: 20px;
        }
        .company-details {
            flex: 1;
            text-align: center;
        }
        .company-details h2 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }
        .company-details p {
            margin: 2px 0;
            font-size: 13px;
        }
        .invoice-type {
            text-align: right;
            font-size: 12px;
        }
        .section {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }
        .section .box {
            width: 48%;
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 12px;
        }
        .details-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0px;
        }
        .details-table td {
            padding: 5px 6px;
            font-size: 12px;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            font-size: 12px;
        }
        .items-table th {
            background: #f0f0f0;
        }
        .totals {
            margin-top: 10px;
            width: 100%;
            font-size: 13px;
        }
        .totals td {
            padding: 4px 6px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
        }
        .footer .left, .footer .right {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .footer .right {
            text-align: right;
        }
        .declaration {
            margin-top: 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="invoice-box" style="border: 1px solid black;">
        <div class="header" style="border: 1px solid black;padding: 10px;">
            <div class="logo">
                <img src="https://www.balajiwafers.com/wp-content/themes/custom/img/BalajiWafers.svg" alt="">
            </div>
            <div class="company-details">
                <h2 style="text-decoration: underline; color: #403092;">HSN Report</h2>
                <p><b>{{ $appName }}</b></p>
                <p><b>Contact No.:</b> {{ $appContact }}</p>
            </div>
        </div>
        <table class="items-table">
            <tr>
                <th>S No.</th>
                <th>HSN Code</th>
                <th>Qty.</th>
                <th>Taxable Value</th>
                <th>GST %</th>
                <th>CGST Val.</th>
                <th>SGST Val.</th>
                <th>IGST Val.</th>
                <th>Total</th>
            </tr>
           
            @php 
                $i = 1; 
                $totalQty = array_sum(array_column($hsnReport, 'qty'));
                $totalTaxable = array_sum(array_column($hsnReport, 'taxable'));
                $totalCGST = array_sum(array_column($hsnReport, 'cgst'));
                $totalSGST = array_sum(array_column($hsnReport, 'sgst'));
                $totalIGST = array_sum(array_column($hsnReport, 'igst'));
                $totalAmount = array_sum(array_column($hsnReport, 'total'));
            @endphp
            @foreach($hsnReport as $item)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $item['hsn_code'] }}</td>
                <td>{{ $item['qty'] }}</td>
                <td>{{ number_format($item['taxable'], 2) }}</td>
                <td>{{ $item['gst'] }}%</td>
                <td>{{ number_format($item['cgst'], 2) }}</td>
                <td>{{ number_format($item['sgst'], 2) }}</td>
                <td>{{ number_format($item['igst'], 2) }}</td>
                <td>{{ number_format($item['total'], 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="2"><strong>Total</strong></td>
                <td><strong>{{ $totalQty }}</strong></td>
                <td><strong>{{ number_format($totalTaxable, 2) }}</strong></td>
                <td></td>
                <td><strong>{{ number_format($totalCGST, 2) }}</strong></td>
                <td><strong>{{ number_format($totalSGST, 2) }}</strong></td>
                <td><strong>{{ number_format($totalIGST, 2) }}</strong></td>
                <td><strong>{{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </table>
    </div>
</body>
</html>