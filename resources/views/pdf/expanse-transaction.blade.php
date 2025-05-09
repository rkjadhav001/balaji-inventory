<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Expense Transaction Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 20px;
        }
        h2, h4 {
            text-align: center;
            margin: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px 8px;
            text-align: left;
        }
        .nested-table th, .nested-table td {
            border: 1px solid #666;
            text-align: center;
        }
        .gray {
            background-color: #dcdcdc;
        }
        .subtotal {
            text-align: right;
            font-weight: bold;
            padding-top: 5px;
        }
        .section-header {
            font-weight: bold;
            margin-top: 20px;
        }
        @media print {
            @page {
                margin: 0;
            }
            table {
                width: 100%;
            }
            .gray {
                background-color: #dcdcdc !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h2>Balaji Inventory</h2>
    <p>Phone no.: +919955448877</p>
    <h4><u>Expense Transaction Report</u></h4>
    @php
        $filterType = request('filter_type');
        $date = request('date');
        $month = request('month');
    @endphp

    @if ($filterType == 'today' && $date)
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
    @elseif ($filterType == 'weekly' && $date)
        <p><strong>Week:</strong> {{ \Carbon\Carbon::parse($date)->startOfWeek()->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($date)->endOfWeek()->format('d/m/Y') }}</p>
    @elseif ($filterType == 'monthly' && $month)
        <p><strong>Month:</strong> {{ \Carbon\Carbon::parse($month)->format('F Y') }}</p>
    @elseif ($filterType == 'yearly' && $date)
        <p><strong>Year:</strong> {{ \Carbon\Carbon::parse($date)->format('Y') }}</p>
    @endif
</div>

<table>
    <thead>
        <tr class="gray">
            <th>Date</th>
            <th>Ref No.</th>
            <th>Status</th>
            <th>Total Amount</th>
            <th>Payment Type</th>
            <th>Received/Paid Amount</th>
            <th>Balance Amount</th>
        </tr>
    </thead>
    <tbody>
        <!-- Row 1 -->
        @foreach ($expenses as $expense)
            <tr>
                <td>{{ $expense->date->format('d/m/Y') }}</td>
                <td>{{ $expense->name }}</td>
                <td>Paid</td>
                <td>Rs {{ $expense->total_amount }}</td>
                <td>{{ ucfirst($expense->payment_type) }}</td>
                <td>Rs {{ $expense->total_amount }}</td>
                <td>Rs 0</td>
            </tr>
            @if (count($expense->expense_detail))
                <tr>
                    <td colspan="10">
                        <table class="nested-table" width="100%">
                            <tr class="gray">
                                <th>#</th>
                                <th>Item name</th>
                                <th>Quantity</th>
                                <th>Price/Unit</th>
                                <th>Amount</th>
                            </tr>
                            @foreach ($expense->expense_detail as $key => $detail)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $detail->type }}</td>
                                    <td>{{ $detail->qty }}</td>
                                    <td>Rs {{ $detail->rate }}</td>
                                    <td>Rs {{ $detail->amount }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="4" class="subtotal">Total</td>
                                <td>Rs {{ $expense->total_amount }}</td>
                            </tr>
                        </table>
                        <div class="subtotal">Sub Total: Rs {{ $expense->total_amount }}</div>
                    </td>
                </tr>
            @endif
        @endforeach

        <!-- Row 2 -->
    </tbody>
</table>

<script>
    window.print();
</script>
</body>
</html>
