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
            width: 80mm;
            margin: 0 auto;
            text-align: center;
        }

        .invoice-container {
            padding: 5px;
        }

        h2, h5, h3 {
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
                width: 75mm;
            }
        }
        @media print {
            @page {
                size: 80mm 297mm; /* Set custom paper size */
                margin: 0; /* Remove default margin */
            }

            
            body {
                width: 75mm;
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
        <h3>Shyam Agency</h3>
        <h4 style="margin: 0">Sale Bill</h4>

        <hr class="line-dot">

        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
            <div style="flex: 1; min-width: 20px;text-align: left;">
                <h5 style="margin: 4px 0;">Party: {{ $order->supplier->name }}</h5>
                <h5 style="margin: 4px 0;">Phone: {{ $order->supplier->phone_number }}</h5>
                <h5 style="margin: 4px 0;">Address: {{ $order->supplier->address }}</h5>
            </div>
            <div style="text-align: left; flex: 1; min-width: 20px;">
                <h5 style="margin: 4px 0;">Order ID: {{ $order->bill_id }}</h5>
                <h5 style="margin: 4px 0;">Date: {{ $order->date->format('d/M/Y h:i') }}</h5>
            </div>
        </div>

        <hr class="line-dot">

        <table>
            <thead>
                {{-- <tr>
                    <th>SL</th>
                    <th>DESC</th>
                    <th>Box</th>
                    <th>Patti</th>
                    <th>Packet</th>
                    <th>Amount</th>
                </tr> --}}
                <tr>
                    <th>SL</th>
                    <th>DESC</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $serial = 1; @endphp
                @foreach ($order->orderProduct as $orderProduct)
                      @if ($orderProduct->box > 0)
                        @php
                            $totalBoxQty = $orderProduct->box * $orderProduct->product->packet;
                        @endphp
                        <tr>
                            <td>{{ $serial++ }}</td>
                            <td>{{ $orderProduct->product->short_name }}</td>
                            <td style="text-align: center">{{ $orderProduct->box }} box</td>
                            <td style="text-align: center">{{ number_format($orderProduct->product->packet * $orderProduct->price, 2) }}</td>
                            <td>₹{{ number_format($totalBoxQty * $orderProduct->price, 2) }}</td>
                        </tr>
                    @endif

                    @if ($orderProduct->patti > 0)
                        @php
                            $totalPattiQty = $orderProduct->patti * $orderProduct->product->per_patti_piece;
                        @endphp
                        <tr>
                            <td>{{ $serial++ }}</td>
                            <td>{{ $orderProduct->product->short_name }}</td>
                            <td style="text-align: center">{{ $orderProduct->patti }} pti</td>
                            <td style="text-align: center">{{ number_format($orderProduct->product->per_patti_piece * $orderProduct->price, 2) }}</td>
                            <td>₹{{ number_format($totalPattiQty * $orderProduct->price, 2) }}</td>
                        </tr>
                    @endif

                    @if ($orderProduct->packet > 0)
                        @php
                            $totalPacketQty = $orderProduct->packet;
                        @endphp
                        <tr>
                            <td>{{ $serial++ }}</td>
                            <td>{{ $orderProduct->product->short_name }}</td>
                            <td style="text-align: center">{{ $orderProduct->packet }} pkt</td>
                            <td style="text-align: center">{{ number_format($orderProduct->price, 2) }}</td>
                            <td>₹{{ number_format($totalPacketQty * $orderProduct->price, 2) }}</td>
                        </tr>
                    @endif

                    {{-- <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $orderProduct->product->short_name }}</td>
                        <td style="text-align: center">{{ $orderProduct->box }}</td>
                        <td style="text-align: center">{{ $orderProduct->patti }}</td>
                        <td style="text-align: center">{{ $orderProduct->packet }}</td>
                        <td>₹{{ $orderProduct->total_cost }}</td>
                    </tr> --}}
                    
                @endforeach
                {{-- <tr style="border-top: 1px dashed black;">
                    <td></td>
                    <td style="text-align: center;font-weight: bold">Box</td>
                    <td style="text-align: center;font-weight: bold">Patti</td>
                    <td style="text-align: center;font-weight: bold">Pkt</td>
                    <td></td>
                </tr>
                <tr >
                    <td style="font-weight: bold">Total </td>
                    <td style="text-align: center;font-weight: bold">{{ $order->total_box }}</td>
                    <td style="text-align: center;font-weight: bold">{{ $order->total_patti }}</td>
                    <td style="text-align: center;font-weight: bold">{{ $order->total_packet }}</td>
                    <td style="text-align: center;font-weight: bold">₹{{ $order->final_amount }}</td>
                </tr> --}}
            </tbody>
        </table>
     <div class="summary-table" style="margin-top: 10px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
                <tr style="border-top: 1px dashed black;">
                    <td></td>
                    <td style="text-align: center; font-weight: bold">Box</td>
                    <td style="text-align: center; font-weight: bold">Patti</td>
                    <td style="text-align: center; font-weight: bold">Pkt</td>
                    <td></td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Total</td>
                    <td style="text-align: center; font-weight: bold">{{ $order->total_box }}</td>
                    <td style="text-align: center; font-weight: bold">{{ $order->total_patti }}</td>
                    <td style="text-align: center; font-weight: bold">{{ $order->total_packet }}</td>
                    <td style="text-align: center; font-weight: bold">₹{{ $order->final_amount }}</td>
                </tr>
            </tbody>
        </table>
    </div>

        {{-- <hr class="line-dot">
        <p class="total">Total: ₹{{ $order->final_amount }}</p> --}}
        
        {{-- @if ($returnOrder)    
            <hr class="line-dot">
            <p style="color: #312682">Order Return</p>
            <hr class="line-dot">
            <table>
                <thead>
                     <tr>
                        <th>SL</th>
                        <th>DESC</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $serial = 1; @endphp
                    @foreach ($returnOrder->returnOrderProducts as $returnOrderProduct)
                        @if ($returnOrderProduct->box > 0)
                            @php
                                $totalBoxQty = $returnOrderProduct->box * $returnOrderProduct->product->packet;
                            @endphp
                            
                            <tr>
                                <td>{{ $serial++ }}</td>
                                <td>{{ $returnOrderProduct->product->short_name }}</td>
                                <td style="text-align: center">{{ $returnOrderProduct->box }}</td>
                                <td>Box</td>
                                <td>₹{{ number_format($totalBoxQty * $returnOrderProduct->price, 2) }}</td>
                            </tr>
                        @endif
                        @if ($returnOrderProduct->patti > 0)
                            @php
                                $totalPattiQty = $returnOrderProduct->patti * $returnOrderProduct->product->per_patti_piece;
                            @endphp
                            
                            <tr>
                                <td>{{ $serial++ }}</td>
                                <td>{{ $returnOrderProduct->product->short_name }}</td>
                                <td style="text-align: center">{{ $returnOrderProduct->patti }}</td>
                                <td>Patti</td>
                                <td>₹{{ number_format($totalPattiQty * $returnOrderProduct->price, 2) }}</td>
                            </tr>
                        @endif

                        @if ($returnOrderProduct->packet > 0)
                            @php
                                $totalPacketQty = $returnOrderProduct->packet;
                            @endphp
                            
                            <tr>
                                <td>{{ $serial++ }}</td>
                                <td>{{ $returnOrderProduct->product->short_name }}</td>
                                <td style="text-align: center">{{ $returnOrderProduct->packet }}</td>
                                <td>Pkt</td>
                                <td>₹{{ number_format($totalPacketQty * $returnOrderProduct->price, 2) }}</td>
                            </tr>
                            
                        @endif
                        
                    @endforeach
                    <tr style="border-top: 1px dashed black;">
                        <td></td>
                        <td style="text-align: center;font-weight: bold">Box</td>
                        <td style="font-weight: bold">Patti</td>
                        <td style="font-weight: bold">Pkt</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold">Total </td>
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
        @endif --}}
        <hr class="line-dot">

        <p class="total">Total Amount: ₹{{ $order->final_amount }}</p>
        {{-- <p class="total">Return Amount: ₹{{ $returnOrder->final_amount ?? 0}}</p> --}}
        <p class="total">Collection Amount: ₹{{ $collections->sum('amount') }}</p>
        {{-- <p class="total">Expanses Amount: ₹{{ $expanses->sum('total_amount') }}</p> --}}
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
