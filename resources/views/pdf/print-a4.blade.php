<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>A4 Print</title>
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
            margin-top: 4px;
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
                <h2>TAX INVOICE</h2>
                <p><b>SHYAM AGENCY</b></p>
                {{-- <p>Address: GROUND FLOOR, 1373 GALA NO 3, MARINA, COMPLEX, MILLAT NAGAR,</p>
                <p>BHIWANDI, MH, 421302- BHIWANDI, MAHARASHTRA</p>
                <p>Email: vinodghadia@gmail.com</p> --}}
                <p><b>Contact No.:</b> {{ $appConfig[0]->value }}</p>
                {{-- <p>PAN No.: AJJPP3055B</p> --}}
            </div>
            {{-- <div class="invoice-type">
                <div>[ ] Original for Buyers</div>
                <div>[ ] Duplicate for Transporters</div>
                <div>[ ] Triplicate for Assessee</div>
            </div> --}}
        </div>
        <table class="details-table" style="border: 1px solid black;">
            <tr>
                <td><b>PARTY NAME:</b>  {{ $order->supplier->name }}</td>
                {{-- <td><b>BILLED TO PARTY:</b> <br> BUSHRA KIRANA STORE,<br>BHIWANDI MAHARASHTRA<br>Contact Person : Q Q<br>Contact 1 : 9766165550</td> --}}
                <td><b>Invoice No.:</b> {{ $order->bill_id }} </td>
            </tr>
            <tr>
                <td><b>Contact No. :</b> {{ $order->supplier->phone_number }}</td>
                {{-- <td><b>GSTIN No.:</b></td> --}}
                <td><b>Date/Time of Supply:</b> {{ \Carbon\Carbon::parse($order->date)->format('d/m/Y, h:i A') }}</td>
            </tr>
            <tr>
                <td><b>ADDRESS:</b> {{ $order->supplier->address }}</td>
                {{-- <td><b>State Name:</b> MAHARASHTRA</td> --}}
                <td></td>
            </tr>
        </table>
        <table class="items-table">
            <tr>
                <th>S No.</th>
                <th>Description Of Goods</th>
                <th>HSN Code</th>
                <th>Qty.</th>
                <th>Unit</th>
                <th>Rate</th>
                <th>Taxable Value</th>
                <th>Tax %</th>
                <th>CGST Val.</th>
                <th>SGST/UGST Val.</th>
                <th>Total</th>
            </tr>
            <!-- Example Row, repeat for each item -->
            @foreach ($order->orderProduct as $key => $orderProduct)       
             @php
                $product = \App\Models\Product::where('id', $orderProduct->product_id)->first();
                $getTax = \App\Models\Tax::where('id', $product->tax_id)->first();
                $tax = round($getTax->value, 2);
                $taxableValue = round($orderProduct->total_cost / (1 + ($tax / 100)), 2);
                $cgst = round(($taxableValue * $tax) / 200, 2);
                $sgst = round(($taxableValue * $tax) / 200, 2);
                $total = round($orderProduct->total_cost, 2);
            @endphp

                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $orderProduct->product->name }}</td>
                    <td>{{ $orderProduct->product->hsn }}</td>
                    <td>{{ $orderProduct->total_qty }}</td>
                    <td>PKT</td>
                    <td>{{ $orderProduct->price }}</td>
                    <td>{{ $taxableValue }}</td>
                    <td>{{ $tax }}</td>
                    <td>{{ $cgst }}</td>
                    <td>{{ $sgst }}</td>
                    <td>{{ $orderProduct->total_cost }}</td>
                </tr>
            @endforeach
            <tr style="background: #f0f0f0;">
                <td colspan="3"> INVOICE GRAND TOTAL</td>
                <td></td>
                <td colspan="2"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                @php
                    function convertNumberToWords($number)
                    {
                        $hyphen      = '-';
                        $conjunction = ' and ';
                        $separator   = ', ';
                        $negative    = 'negative ';
                        $decimal     = ' point ';
                        $dictionary  = [
                            0 => 'zero',
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                            4 => 'four',
                            5 => 'five',
                            6 => 'six',
                            7 => 'seven',
                            8 => 'eight',
                            9 => 'nine',
                            10 => 'ten',
                            11 => 'eleven',
                            12 => 'twelve',
                            13 => 'thirteen',
                            14 => 'fourteen',
                            15 => 'fifteen',
                            16 => 'sixteen',
                            17 => 'seventeen',
                            18 => 'eighteen',
                            19 => 'nineteen',
                            20 => 'twenty',
                            30 => 'thirty',
                            40 => 'forty',
                            50 => 'fifty',
                            60 => 'sixty',
                            70 => 'seventy',
                            80 => 'eighty',
                            90 => 'ninety',
                            100 => 'hundred',
                            1000 => 'thousand',
                            100000 => 'lakh',
                            10000000 => 'crore'
                        ];
                        if (!is_numeric($number)) {
                            return false;
                        }
                        if ($number < 0) {
                            return $negative . convertNumberToWords(abs($number));
                        }
                        $string = '';
                        foreach ([10000000 => 'crore', 100000 => 'lakh', 1000 => 'thousand', 100 => 'hundred'] as $value => $name) {
                            if (($number / $value) >= 1) {
                                $count = floor($number / $value);
                                $number %= $value;
                                $string .= convertNumberToWords($count) . ' ' . $name . ' ';
                            }
                        }
                        if ($number > 0) {
                            if ($number < 21) {
                                $string .= $dictionary[$number];
                            } elseif ($number < 100) {
                                $tens = ((int) ($number / 10)) * 10;
                                $units = $number % 10;
                                $string .= $dictionary[$tens];
                                if ($units) {
                                    $string .= $hyphen . $dictionary[$units];
                                }
                            }
                        }
                        return ucfirst(trim($string));
                    }
                @endphp
                <td colspan="8" style="text-align: left"> Invoice Value in Words Rs. : {{ convertNumberToWords($order->final_amount) }}</td>
                <td colspan="2"> <b>Total</b></td>
                <td> {{ $order->final_amount }}</td>
            </tr>
            <tr>
                <td colspan="2">Box/Bunch</td>
                <td colspan="3">Patti</td>
                <td colspan="3"> Packet</td>
                <td colspan="2"> Round off</td>
                <td> 0.00</td>
            </tr>
             <tr>
                <td colspan="2">{{ $order->total_box }}</td>
                <td colspan="3">{{ $order->total_patti }}</td>
                <td colspan="3"> {{ $order->total_packet }}</td>
                <td colspan="2"> Invoice Total</td>
                <td> {{ $order->final_amount }}</td>
            </tr>
            <tr>
                <td colspan="8" style="text-align: left"> Terms & Conditions :</td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="8" >
                    <span> TRANSPORTER SIGN:</span>
                    <span>GOODS CHECK BY SECURITY :</span>
                    <span>CASHIER</span>
                </td>
                <td colspan="3"></td>
            </tr>

            <!-- Add more rows as needed -->
        </table>
        <div class="declaration">
            <b>Declaration:</b>
        </div>
    </div>
</body>
</html>