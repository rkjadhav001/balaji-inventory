<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Purchase Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 58mm;
            margin: 0 auto;
            text-align: center;
        }

        .invoice-container {
            padding: 5px;
        }

        h2, h5 {
            margin: 3px 0;
        }

        .line-dot {
            border-top: 1px dashed black;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 3px;
            font-size: 11px;
        }

        th {
            border-bottom: 1px solid black;
        }

        .total {
            font-weight: bold;
        }

        .footer-text {
            margin-top: 5px;
            font-size: 11px;
        }

        @media print {
            body {
                width: 58mm;
            }
        }
        @media print {
            @page {
                size: 58mm auto; 
                margin: 0;
            }
            
            body {
                width: 58mm;
                margin: 0;
            }
            
            .invoice-container {
                width: 100%;
                padding: 2px;
            }
            
            table {
                width: 100%;
            }
        }

    </style>
</head>

<body>
    <div class="invoice-container">
        <h2>Balaji</h2>
        <h5>Vadodara Halol Toll Road, At. Kotambi Ta. Waghodia, Vadodara</h5>
        <h5>Phone: 9668555555</h5>
        <h5>Email: balaji@gmail.com</h5>
        <h5>Register No: 646656544</h5>

        <hr class="line-dot">

        {{-- <h5>Party : Neel</h5> --}}
        <h5>Purchase Date</h5>
        <h5>{{ $purchase->date->format('d/M/Y h:i') }}</h5>
        <hr class="line-dot">

        <table>
            <thead>
                <tr>
                    <th>SL</th>
                    <th>DESC</th>
                    <th>Box</th>
                    <th>Patti</th>
                    <th>Packet</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->purchaseDetails as $purchaseDetail)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $purchaseDetail->product->short_name }}</td>
                        <td style="text-align: center">{{ $purchaseDetail->box }}</td>
                        <td style="text-align: center">{{ $purchaseDetail->patti }}</td>
                        <td style="text-align: center">{{ $purchaseDetail->packet }}</td>
                        <td>₹{{ $purchaseDetail->purchase_price }}</td>
                    </tr>
                    
                @endforeach
                <tr>
                    <td colspan="2"></td>
                    <td style="text-align: center;font-weight: bold">{{ $purchase->total_box }}</td>
                    <td style="text-align: center;font-weight: bold">{{ $purchase->total_patti }}</td>
                    <td style="text-align: center;font-weight: bold">{{ $purchase->total_packet }}</td>
                    <td>₹{{ $purchase->total_purchase_amount }}</td>
                </tr>
            </tbody>
        </table>
        <hr class="line-dot">
        
        <p>Items Price: 360 ₹</p>
        <p>Tax / VAT: 40 ₹</p>
        <p>Subtotal: 400 ₹</p>
        <p>Extra Discount: 20 ₹</p>
        <p>Coupon Discount: 10 ₹</p>
        <p class="total">Total: 370 ₹</p>
        
        <hr class="line-dot">
        <p>Paid by: Cash</p>
        <p>Amount: 400 ₹</p>
        <p>Change: 30 ₹</p>
        
        <hr class="line-dot">
        <h5>"THANK YOU"</h5>
        <hr class="line-dot">
    </div>
    
    <script>
        window.onload = function () {
            window.print();
        }
    </script>
</body>

</html>
