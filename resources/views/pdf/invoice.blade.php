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
        <h5>Order ID: 1236544</h5>
        <h5>01/Jan/2024 10:30 AM</h5>
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
                <tr>
                    <td>1</td>
                    <td>Product A</td>
                    <td>2</td>
                    <td>1</td>
                    <td></td>
                    <td>180 ₹</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Product B</td>
                    <td>1</td>
                    <td></td>
                    <td></td>
                    <td>180 ₹</td>
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
