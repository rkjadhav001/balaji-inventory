<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sale Purchase Report</title>
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
            /* border: 1px solid #000; */
            border-bottom: 1px solid #000;
            padding: 5px 8px;
            text-align: left;
        }
        td {
            font-weight: bold;
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
    <p><b>Sale & Purchase Report</b></p>
    @if (request()->input('filter_type') == 'today' && request()->input('date'))
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse(request()->input('date'))->format('d-m-Y') }}</p>
    @elseif(request()->input('filter_type') == 'monthly' && request()->input('month'))
        <p><strong>Month:</strong> {{ \Carbon\Carbon::parse(request()->input('month'))->format('F Y') }}</p>
    @elseif(request()->input('filter_type') == 'yearly' && request()->input('date'))
        <p><strong>Year:</strong> {{ \Carbon\Carbon::parse(request()->input('date'))->format('Y') }}</p>
    @elseif(request()->input('filter_type') == 'custom' && request()->input('from_date') && request()->input('to_date'))
        <p><strong>From:</strong> {{ \Carbon\Carbon::parse(request()->input('from_date'))->format('d-m-Y') }} To {{ \Carbon\Carbon::parse(request()->input('to_date'))->format('d-m-Y') }}</p>
    @elseif(request()->input('filter_type') == 'weekly')
        @php
            $date = request()->input('date') ? Carbon::parse(request()->input('date')) : Carbon::now();
            $startOfWeek = $date->copy()->startOfWeek();
            $endOfWeek = $date->copy()->endOfWeek();
        @endphp
        <p><strong>Week:</strong> {{ $startOfWeek->format('d-m-Y') }} To {{ $endOfWeek->format('d-m-Y') }}</p>
    @else 
        @if (count($transactions) > 0)
            <p><strong>Duration:</strong> {{ \Carbon\Carbon::parse($transactions->first()->date)->format('d/m/Y') }} To {{ \Carbon\Carbon::parse(now())->format('d/m/Y') }}</p>
        @else
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse(now())->format('d/m/Y') }}</p>
        @endif
    @endif
</div>

{{-- <h3 style="margin-bottom: 10px;"><u> Sale</u></h3> --}}
<table>
    <thead>
        <tr class="gray">
            <th>Date</th>
            <th>Order No.</th>
            <th>Ref No.</th>
            <th>Party Name</th>
            <th>Phone No</th>
            <th>Party's GSTIN No.</th>
            <th>Txn Type</th>
            <th>Status</th>
            <th>Total Amount</th>
            <th>Payment Type</th>
            <th>Received/Paid Amount</th>
            <th>Balance Amount</th>
        </tr>
    </thead>
    <tbody>
        <!-- Row 1 -->
        @foreach ($transactions as $transaction)    
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                <td></td>
                <td>{{ $transaction->bill_id }}</td>
                <td>{{ $transaction->party->name }}</td>
                <td>{{ $transaction->party->phone_number }}</td>
                <td></td>
                <td>
                    @if ($transaction->transaction_type == 'sale')
                        Sale
                    @elseif ($transaction->transaction_type == 'purchase')
                        Purchase
                    @elseif ($transaction->transaction_type == 'sale return')
                        Credit Note
                    @elseif ($transaction->transaction_type == 'purchase return')
                        Debit Note
                    @endif
                </td>
                <td>
                    @if ($transaction->type == 'purchase paid' || $transaction->type == 'sale paid')
                        Paid
                    @else
                        Unpaid                        
                    @endif
                </td>
                <td>
                    {{ number_format($transaction->total_amount, 2)}}
                </td>
                <td>-</td>
                <td>
                    @php
                        $revisedAmount = $transaction->total_amount - $transaction->pending_amount;
                    @endphp
                    {{ number_format($revisedAmount, 2) }}
                </td>
                <td>{{ number_format($transaction->pending_amount, 2) }}</td>
            </tr>
        @endforeach
            <tr style="font-size: 15px;">
                <td colspan="8" style="text-align: center">Total</td>
                <td>{{ number_format($transactions->sum('total_amount'), 2) }}</td>
                <td></td>
                <td>
                    @php
                        $totalRevisedAmount = $transactions->sum('total_amount') - $transactions->sum('pending_amount');
                    @endphp
                    {{ number_format($totalRevisedAmount, 2) }}
                </td>
                <td>{{ number_format($transactions->sum('pending_amount'), 2) }}</td>
            </tr>
        <!-- Row 2 -->
    </tbody>
</table>

<script>
    window.print();
</script>
</body>
</html>
