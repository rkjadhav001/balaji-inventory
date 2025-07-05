<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Party Item Wise</title>
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
    <table class="items-table" id="items-table">
        <tr>
            <th>Invoice No..</th>
            <th>Reference No.</th>
            <th>Date</th>
            <th>Party Name</th>
            <th>Sales Ledger</th>
            <th>Name of Item</th>
            <th>Quantity</th>
            <th>Item Rate</th>
            <th>Item Rate per</th>
            <th>Amount</th>
            <th>CGST</th>
            <th>SGST</th>
            <th>IGST</th>
            <th>TOTAL AMOUNT</th>
        </tr>
        @foreach ($reportRows as $reportRow)
            <tr>
                <td>{{ $reportRow['Invoice No.'] }}</td>
                <td>{{ $reportRow['Reference No.'] }}</td>
                <td>{{ $reportRow['Date'] }}</td>
                <td>{{ $reportRow['Party Name'] }}</td>
                <td>{{ $reportRow['Sales Ledger'] }}</td>
                <td>{{ $reportRow['Name of Item'] }}</td>
                <td>{{ $reportRow['Quantity'] }}</td>
                <td>{{ $reportRow['Item Rate'] }}</td>
                <td>{{ $reportRow['Item Rate per'] }}</td>
                <td>{{ $reportRow['Amount'] }}</td>
                <td>{{ $reportRow['CGST'] }}</td>
                <td>{{ $reportRow['SGST'] }}</td>
                <td>{{ $reportRow['IGST'] }}</td>
                <td>{{ $reportRow['TOTAL AMOUNT'] }}</td>
            </tr>
        @endforeach
    </table>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        window.onload = function () {
            const table = document.getElementById("items-table");

            // Convert table to SheetJS worksheet
            const ws = XLSX.utils.table_to_sheet(table);

            // Apply bold style to the first row (header)
            const range = XLSX.utils.decode_range(ws['!ref']);
            for (let C = range.s.c; C <= range.e.c; ++C) {
                const cellAddress = XLSX.utils.encode_cell({ r: 0, c: C }); // row 0 = first row
                if (ws[cellAddress]) {
                    ws[cellAddress].s = {
                        font: {
                            bold: true
                        }
                    };
                }
            }

            // Create workbook and add the worksheet
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Report");

            // Export with styles
            XLSX.writeFile(wb, "party_item_wise_report_{{ date('Y-m-d') }}.xlsx", { bookType: "xlsx", cellStyles: true });
        };
    </script>
</body>
</html>