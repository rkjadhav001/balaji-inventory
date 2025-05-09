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
                size: 80mm 297mm; /* Set custom paper size */
                margin: 0; /* Remove default margin */
            }

            
            body {
                width: 58mm;
                /* margin: 0; */
            }
            
            .invoice-container {
                width: 100%;
                padding: 2px;
            }
            
            table {
                width: 100%;
            }

            #print {
                display: none;
            }
        }
        .btn-primary {
            background-color: #312682;
            color: white;
            padding: 10px 23px;
            border-radius: 10px;
            margin-top: 10px;
        }
    </style>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
</head>

<body>
    <button onclick="printInvoice()" id="print" class="btn btn-primary">Print</button>
    <div class="invoice-container" id="invoice">
        <h2>Balaji</h2>
        <h5>Vadodara Halol Toll Road, At. Kotambi Ta. Waghodia, Vadodara</h5>
        <h5>Phone: 9668555555</h5>
        <h5>Email: balaji@gmail.com</h5>
        <h5>Register No: 646656544</h5>

        <hr class="line-dot">

        <h5>Party : {{ $order->supplier->name }}</h5>
        <h5>Order ID: {{ $order->order_id }}</h5>
        <h5>{{ $order->created_at->format('d/M/Y h:i') }}</h5>
        <hr class="line-dot">

        <table>
            <thead>
                <tr>
                    <th>SL</th>
                    <th>DESC</th>
                    <th>Box</th>
                    <th>Patti</th>
                    <th>Packet</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderProduct as $orderProduct)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $orderProduct->product->short_name }}</td>
                        <td style="text-align: center">{{ $orderProduct->box }}</td>
                        <td style="text-align: center">{{ $orderProduct->patti }}</td>
                        <td style="text-align: center">{{ $orderProduct->packet }}</td>
                        <td>₹{{ $orderProduct->total_cost }}</td>
                    </tr>
                    
                @endforeach
                <tr style="border-top: 1px dashed black;margin: 5px 0;">
                    <td colspan="2" style="font-weight: bold">Total </td>
                    <td style="text-align: center;font-weight: bold">{{ $order->total_box }}</td>
                    <td style="text-align: center;font-weight: bold">{{ $order->total_patti }}</td>
                    <td style="text-align: center;font-weight: bold">{{ $order->total_packet }}</td>
                    <td style="text-align: center;font-weight: bold">₹{{ $order->final_amount }}</td>
                </tr>
            </tbody>
        </table>
        {{-- <hr class="line-dot">
        <p class="total">Total: ₹{{ $order->final_amount }}</p> --}}
        
        @if ($returnOrder)    
            <hr class="line-dot">
            <p style="color: #312682">Order Return</p>
            <hr class="line-dot">
            <table>
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>DESC</th>
                        <th>Box</th>
                        <th>Patti</th>
                        <th>Packet</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($returnOrder->returnOrderProducts as $returnOrderProduct)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $returnOrderProduct->product->short_name }}</td>
                            <td style="text-align: center">{{ $returnOrderProduct->box }}</td>
                            <td style="text-align: center">{{ $returnOrderProduct->patti }}</td>
                            <td style="text-align: center">{{ $returnOrderProduct->packet }}</td>
                            <td>₹{{ $returnOrderProduct->total_cost }}</td>
                        </tr>
                        
                    @endforeach
                    <tr style="border-top: 1px dashed black;margin: 5px 0;">
                        <td colspan="2" style="font-weight: bold">Total </td>
                        <td style="text-align: center;font-weight: bold">{{ $returnOrder->total_box }}</td>
                        <td style="text-align: center;font-weight: bold">{{ $returnOrder->total_patti }}</td>
                        <td style="text-align: center;font-weight: bold">{{ $returnOrder->total_packet }}</td>
                        <td style="text-align: center;font-weight: bold">₹{{ $returnOrder->final_amount }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
        @if (count($collections) > 0)     
            <hr class="line-dot">
            <p style="color: #312682">Collection</p>
            <hr class="line-dot">
            @foreach ($collections as $collection)    
                <p style="margin: 4px 0;font-weight: bold;text-align: start;">{{ $collection->date->format('d/M/Y') }}</p>
                <table>
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Type</th>
                            <th>Remark</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($collection->collection_type as $orderCollection)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $orderCollection->name }}</td>
                                <td>{{ $orderCollection->remark }}</td>
                                <td>₹{{ $orderCollection->amount }}</td>
                            </tr>
                            
                        @endforeach
                </table>
            @endforeach
        @endif
        @if (count($expanses) > 0)          
            <hr class="line-dot">
            <p style="color: #312682">Expanses</p>
            <hr class="line-dot">
            @foreach ($expanses as $expanse)    
                <p style="margin: 4px 0;font-weight: bold;text-align: start;">{{ $expanse->date->format('d/M/Y') }}</p>
                <table>
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expanse->expanse_type as $orderExpanse)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $orderExpanse->type }}</td>
                                <td>₹{{ $orderExpanse->amount }}</td>
                            </tr>
                            
                        @endforeach
                </table>
                
            @endforeach
        @endif
        <hr class="line-dot">

        <p class="total">Total Amount: ₹{{ $order->final_amount }}</p>
        <p class="total">Return Amount: ₹{{ $returnOrder->final_amount ?? 0}}</p>
        <p class="total">Collection Amount: ₹{{ $collections->sum('amount') }}</p>
        <p class="total">Expanses Amount: ₹{{ $expanses->sum('total_amount') }}</p>
        @php
            $pendingTotal = $order->final_amount - ($returnOrder->final_amount ?? 0) - $collections->sum('amount') - $expanses->sum('total_amount');
        @endphp
        <p class="total" style="color:#858500">Pending Amount: ₹{{ $pendingTotal }}</p>
        
        <hr class="line-dot">
        <h5>"THANK YOU"</h5>
        <hr class="line-dot">
    </div>
    
    {{-- <script>
        function printInvoice() {
          const element = document.getElementById("invoice");
    
          const options = {
            margin: 2,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: [80, 297], orientation: 'portrait' }
          };
    
          html2pdf().set(options).from(element).toPdf().get('pdf').then(function(pdf) {
            pdf.autoPrint(); // Enable auto-print
            window.open(pdf.output('bloburl'), '_blank'); // Open print dialog
          });
        }
      </script> --}}
      {{-- <script>
        function printInvoice() {
            const element = document.getElementById("invoice");
    
            const options = {
                margin: 2,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: [80, 297], orientation: 'portrait' }
            };
    
            html2pdf().set(options).from(element).toPdf().get('pdf').then(function (pdf) {
                const blobUrl = URL.createObjectURL(pdf.output("blob")); // Create Blob URL
                const iframe = document.createElement("iframe"); 
                iframe.style.display = "none"; // Hide the iframe
                document.body.appendChild(iframe);
                iframe.src = blobUrl;
                iframe.onload = function () {
                    iframe.contentWindow.print(); // Open print dialog inside the iframe
                };
            });
        }
    </script> --}}
    
    <script>
        function printInvoice()
        {
            window.print();
        }
    </script>
</body>

</html>
