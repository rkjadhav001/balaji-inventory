<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" href="https://www.balajiwafers.com/wp-content/themes/custom/img/fav_logo.png">
    <title>Balaji</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://kit.fontawesome.com/177b54f962.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-size: 14px;
            background-image: linear-gradient(to top, #A8EDEA 0%, #FED6E3 100%);
            height: 100%;
        }

        .card-header {
            background-color: white !important;
            border-bottom: 2px solid #403092;
        }

        .card-body {
            padding: 7px;
        }
      
        .scrollable-container {
        display: flex;
        gap: 5px;
        overflow-x: auto;
        white-space: nowrap;
        padding: 5px;
}

        .card {
            background: linear-gradient(90deg, #FF7E5F, #FEB47B);
            border: none;
            /* box-shadow: rgb(0 0 0 / 14%) 0px 3px 8px; */

            border: 1px solid #ddd;
            /* padding: 20px; */
            text-align: center;
            box-shadow: 1px 1px 5px 3px rgba(181, 9, 195, 0.14);
            border-radius: 10px;
            /* background-color: #ffffffb0; */
        }

        label {
            color: white !important;
            font-weight: bold;
            text-align: center;
        }

        input {
            border: 1px solid #c0e6e8;
            background-image: linear-gradient(to top, #a8edeabd 0%, #fed6e321 100%);
        }

        .form-control {
            color: #403092;
            font-weight: 500;
        }

        footer {
            position: fixed;
            z-index: 99999;
            bottom: 0;
            background-color: white;
            width: 100%;
            padding: 10px;
        }

        .container {
            margin-bottom: 92px !important;
        }

        /* #mobile-content {
            display: none;
        } */


        .btn-secondary {
            background-image: linear-gradient(90deg, #403099, #40309994);
        }

        #error-message {
            display: none;
            text-align: center;
            margin-top: 50px;
            color: red;
            font-family: Arial, sans-serif;
        }
        /* @media only screen and (max-width: 767px) {
            .catalogue-cart_delivery-address__hX1Sy {
                margin-right: 15px;
                margin-left: 15px;
            }
        } */
        .catalogue-cart_padding-15__NGjg3 {
            padding: 15px;
        }
        .cart-count {
            position: absolute;
            top: 22px;
            right: 8px;
            background: #403092;
            color: white;
            font-size: 12px;
            font-weight: bold;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div id="mobile-content">
        <form id="orderForm" action="{{ route('Wholesaler.Order') }}" method="POST">
            @csrf
            <div
                style="padding: 0px 10px;width: 100%; display: flex; justify-content: space-between; align-items: center;background-image: linear-gradient(to top, #dbdbdb 0%, #FED6E3 100%);    box-shadow: rgba(149, 157, 165, 0.4) 0px 8px 24px;padding-right: 0px !important;padding-left: 0px !important;">
                <img src="https://www.balajiwafers.com/wp-content/themes/custom/img/BalajiWafers.svg" 
                    alt="Balaji Wafers Logo"
                    style="width: 90px; height: auto; padding: 10px; margin-right: 8px;">

                <div>
                    <a class="btn btn-select-login " href="javascript:void(0)"
                        style="text-transform: capitalize !important;color: #403092;font-weight: bold;     border: 1px solid #403092;font-size: 14px;margin-right: 10px;">
                        {{-- <i class="fa-solid fa-user"></i> {{ $wholesaler->name }} --}}
                        @php
                            $cart = session()->get('cart', []);
                        @endphp
                        @if(count($cart) > 0)
                            <span class="cart-count">{{ count($cart) }}</span>
                        @endif
                        <i class="fa-solid fa-cart-shopping"></i>
                    </a>
                </div>
                {{-- <input type="hidden" name="wholesaler_id" value="{{ $wholesaler->id }}"> --}}
            </div>
            <div class="container">
                <div class="row mt-2">
                    <div class="col-md-12">
                        @php
                            // $customer = session()->get('customer', []);
                            $cookieData = request()->cookie('customer');
                            $customer = [];
                            if ($cookieData) {
                                $customer = json_decode($cookieData, true); 
                            }
                        @endphp
                        <div class="catalogue-cart_delivery-address__hX1Sy bg-white rounded catalogue-cart_padding-15__NGjg3">
                            <div class="catalogue-cart_delivery-address-div__4erb6 catalogue-cart_bold__nEpcf catalogue-cart_fs-16__oSoEd">
                                Name</div>
                                <div class="form-row">
                                    <div class="catalogue-cart_form-group__6D8Ca col-12"><span
                                            class="d-none nameRequired text-danger bold fs-11">Required*</span><input
                                            type="text" class="catalogue-cart_form-control__JHhfU form-control" name="customerName"
                                            id="customerName" placeholder="Name *" required="" minlength="1" maxlength="30"
                                            value="{{ old('customerName', $customer['customerName'] ?? '') }}"></div>
                                    <div class="catalogue-cart_form-group__6D8Ca  col-12"><span
                                            class="d-none phoneRequired text-danger bold fs-11">Required*</span>
                                        <div class=" react-tel-input ">
                                            <div class="special-label">Phone</div><input
                                                class="form-control catalogue-cart_phone-input__I1mGe" name="customerPhone"
                                                placeholder="Contact Number *" type="tel" required=""
                                                value="{{ old('customerPhone', $customer['customerPhone'] ?? '') }}">
                                            <div class="flag-dropdown ">
                                                <div class="selected-flag" title="India: + 91" tabindex="0" role="button"
                                                    aria-haspopup="listbox">
                                                    <div class="flag in">
                                                        <div class="arrow"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="catalogue-cart_form-group__6D8Ca m-0">
                                    <div class="special-label">Address</div>
                                    <textarea name="customerAddress" class="catalogue-cart_form-control__JHhfU catalogue-cart_customer-address__Tpezh form-control"
                                        id="customerAddress" cols="30" rows="2" placeholder="Address" maxlength="100">{{ old('customerAddress', $customer['customerAddress'] ?? '') }}</textarea>
                                </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    @foreach ($products as $key => $product)
                        @php
                            $cartCollection = collect($cart);
                            $cartItem = $cartCollection->firstWhere('product_id', $product->id);
                            // dd($cartItem['box']);
                            $boxQty = $cartItem ? $cartItem['box'] : 0;
                            $pattiQty = $cartItem ? $cartItem['patti'] : 0;
                            $packetQty = $cartItem ? $cartItem['packet'] : 0;
                            $totalQty = $cartItem ? $cartItem['total_qty'] : 0;
                            $productPriceQty = $totalQty * $product->selling_price;
                        @endphp
                        <div class="col-md-12 {{ $key != 0 ? 'mt-3' : '' }}">
                            <div class="card" data-box="{{ $product->box }}" data-patti="{{ $product->patti }}"
                                data-packet="{{ $product->packet }}" data-selling_price="{{ $product->selling_price }}"
                                data-per_patti_paket="{{ $product->per_patti_piece }}">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12 d-flex justify-content-between align-items-center">
                                            <p style="color: #403092; font-weight: 500; margin: 0;">
                                                {{ $product->name }}
                                            </p>
                                            <i class="fa fa-trash text-danger ms-3" style="font-size: 14px; cursor: pointer;"  onclick="removeProduct({{ $product->id }})"></i>
                                        </div>                                        
                                    </div>                                     
                                    <div class="d-flex justify-content-between">
                                        <p style="margin: 0;"><span style="color: #c77642;font-weight: 500;"
                                                class="">Total Qty:
                                            </span>
                                            <span class="productTotalQty">{{ $totalQty }}Pkt</span>
                                        </p>
                                        <input type="hidden" name="products[{{ $product->id }}][productTotalQty]"
                                            class="productTotalFormQty" value="{{ $product->id }}">
                                        <p style="margin: 0;"><span style="color: #507a53;font-weight: 500;"
                                                class="">Total Cost:
                                            </span>
                                            <span class="productTotalCost">₹{{ $productPriceQty }}/INR</span>
                                        </p>
                                        <input type="hidden" name="products[{{ $product->id }}][productTotalCost]"
                                            class="productTotalFormCost" value="{{ $product->id }}">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="products[{{ $product->id }}][product_id]"
                                        value="{{ $product->id }}">
                                    <div class="row">
                                        @php
                                            $unitTypesArray = explode(',', $product->unit_types);

                                        @endphp
                                        @if (in_array('1', $unitTypesArray))
                                            <div class="col-{{ 12 / count($product->unit_type_names) }}">
                                                <label for="box">Box</label>
                                                <div class="input-group">
                                                   
                                                    <button type="button" class="btn btn-secondary btn-sm minus-btn" data-target="productBox">-</button>
                                                    <input type="number" name="products[{{ $product->id }}][box]"
                                                    id="box" class="form-control productBox"
                                                    value="{{ $boxQty }}" placeholder="Box">
                                                     
                                                    <button type="button" class="btn btn-secondary btn-sm plus-btn" data-target="productBox">+</button>
                                                </div>
                                                
                                            </div>
                                        @endif
                                        @if (in_array('2', $unitTypesArray))
                                            <div class="col-{{ 12 / count($product->unit_type_names) }}">
                                                <label for="patti">Patti</label>
                                                
                                                    <div class="input-group">
                                                        <button type="button" class="btn btn-secondary btn-sm minus-btn" data-target="productPatti">-</button>
                                                        <input type="number" name="products[{{ $product->id }}][patti]"
                                                    id="patti" class="form-control productPatti"
                                                    value="{{ $pattiQty }}" placeholder="Patti">
                                                        <button type="button" class="btn btn-secondary btn-sm plus-btn" data-target="productPatti">+</button>
                                                    </div>
                                            </div>
                                        @endif
                                        @if (in_array('3', $unitTypesArray))
                                            <div class="col-{{ 12 / count($product->unit_type_names) }}">
                                                <label for="packet">Packet</label>
                                               
                                                    <div class="input-group">
                                                        <button type="button" class="btn btn-secondary btn-sm minus-btn" data-target="productPacket">-</button>
                                                        <input type="number" name="products[{{ $product->id }}][packet]"
                                                        id="packet" class="form-control productPacket"
                                                        value="{{ $packetQty }}" placeholder="Packet">
                                                        <button type="button" class="btn btn-secondary btn-sm plus-btn" data-target="productPacket">+</button>
                                                    </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <footer>
                <div class="d-flex justify-content-between">
                    <div class="d-flex" style="flex-direction: column;">
                        <span style="font-weight: 600;color: #403092" class="totalProducts">Total Products : <span
                                id="totalProducts" style="color: #fea272">0</span></span>
                        <input type="hidden" name="totalProducts" id="totalProductsHidden" value="0">
                        <span style="font-weight: 600;color: #403092" class="finalTotalAmount">Total Amount : <span
                                id="finalTotalAmount" style="color: #fea272">0</span></span>
                        <input type="hidden" name="totalAmount" id="totalAmountHidden" value="0">
                        <div style="font-weight: 600;color: #403092" class="totalCounts">
                            Total: 
                            <span id="totalBoxCount" style="color: #fea272">0</span> |
                            <span id="totalPattiCount" style="color: #fea272">0</span> |
                            <span id="totalPacketCount" style="color: #fea272">0</span>
                            |
                        </div>

                    </div>
                    <button type="button" class="btn btn-primary mt-2 mb-2" id="add-product"
                    style="background: none;font-weight: 600;border: 1px solid #ffffff;background-image: linear-gradient(90deg, #403099, #40309994);color: #ffffff;">Submit
                        Order</button>
                </div>
            </footer>
        </form>
    </div>
    <h2 id="error-message">This website is only accessible on mobile devices.</h2>
    {{-- <script>
        function detectDevice() {
            const userAgent = navigator.userAgent || navigator.vendor || window.opera;
            const isMobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent);
            if (isMobile) {
                document.getElementById("mobile-content").style.display = "block";
            } else {
                document.getElementById("error-message").style.display = "block";
            }
        }
        window.onload = detectDevice;
    </script> --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>

$(document).ready(function () {
    $("#add-product").click(function () {
        let cartCount = $(".card").length; // Count cart items

        if (cartCount === 0) {
            alert("Your cart is empty! Please add items before submitting the order.");
            return;  
        }

         
        const totalProducts = $('#totalProducts').text();
        const finalTotalAmount = $('#finalTotalAmount').text();

        
        const confirmation = confirm(
            `You are about to place an order with ${totalProducts} products for a total of INR ${finalTotalAmount}. Do you want to proceed?`
        );

        if (confirmation) {
            $("#orderForm").submit(); // Submit the form
        }
    });
});



function addProduct(productId, qty, cost, box, patti, packet) {
    console.log("Adding product:", productId);

    // Increase total values
    updateCartTotals(qty, cost, box, patti, packet);
    updateCartCount();
}

// Function to remove product and decrease values
function removeProduct(productId) {
    console.log("Removing product:", productId);

    $.ajax({
        url: "{{ route('Wholesaler.Cart.Remove') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            product_id: productId
        },
        success: function(response) { 
            console.log("Product removed successfully", response); 

            let productCard = $("input[name='products[" + productId + "][product_id]']").closest(".card");
            let qtyToRemove = parseInt(productCard.find(".productTotalQty").text()) || 0;
            let costToRemove = parseFloat(productCard.find(".productTotalCost").text().replace("₹", "")) || 0;
            

            let boxToRemove = parseInt(productCard.find(".productBox").val()) || 0;
            let pattiToRemove = parseInt(productCard.find(".productPatti").val()) || 0;
            let packetToRemove = parseInt(productCard.find(".productPacket").val()) || 0;

            productCard.fadeOut(300, function () {
                $(this).remove();
                updateCartTotals(-qtyToRemove, -costToRemove, -boxToRemove, -pattiToRemove, -packetToRemove);
                updateCartCount();
            });
        },
        error: function(error) {
            console.error("Error removing product", error);
        }
    });
}

// Function to update cart count dynamically
function updateCartCount() {
    let cartCount = $(".card").length;
    if (cartCount > 0) {
        $(".cart-count").text(cartCount);
    } else {
        $(".cart-count").remove(); // Hide count if cart is empty
    }
}

// Function to update total quantities & cost
function updateCartTotals(qtyChange, costChange, boxChange, pattiChange, packetChange) {
    let totalProducts = $(".card").length;
    
    // Ensure the extracted values are valid numbers
    let totalQty = parseInt($("#totalProducts").text()) || 0;
    let totalCost = parseFloat($("#finalTotalAmount").text().replace(/[^0-9.]/g, "")) || 0;
    let totalBoxCount = parseInt($("#totalBoxCount").text()) || 0;
    let totalPattiCount = parseInt($("#totalPattiCount").text()) || 0;
    let totalPacketCount = parseInt($("#totalPacketCount").text()) || 0;

    // Apply changes
    totalQty += qtyChange;
    totalCost += costChange;
    totalBoxCount += boxChange;
    totalPattiCount += pattiChange;
    totalPacketCount += packetChange;

    // Ensure values do not go negative
    totalQty = Math.max(0, totalQty);
    totalCost = Math.max(0, totalCost);
    totalBoxCount = Math.max(0, totalBoxCount);
    totalPattiCount = Math.max(0, totalPattiCount);
    totalPacketCount = Math.max(0, totalPacketCount);

    // Update the DOM
    $("#totalProducts").text(totalProducts);
    $("#totalProductsHidden").val(totalProducts);

    $("#finalTotalAmount").text(`₹${totalCost.toFixed(2)} /INR`);
    $("#totalAmountHidden").val(totalCost.toFixed(2));

    $("#totalBoxCount").text(totalBoxCount);
    $("#totalPattiCount").text(totalPattiCount);
    $("#totalPacketCount").text(totalPacketCount);

    // Save cart state
    saveCartToSession(totalProducts, totalCost, totalBoxCount, totalPattiCount, totalPacketCount);
}


 
function saveCartToSession(totalProducts, totalCost, totalBoxCount, totalPattiCount, totalPacketCount) {
    
    $.ajax({
        url: "{{ route('Wholesaler.Cart.UpdateSession') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            totalProducts: totalProducts,
            totalCost: totalCost,
            totalBoxCount: totalBoxCount,
            totalPattiCount: totalPattiCount,
            totalPacketCount: totalPacketCount
        },
        success: function(response) {
            console.log("Cart session updated", response);
        },
        error: function(error) {
            console.error("Error updating cart session", error);
        }
    });
}
 
$(document).ready(function() {
      const updateFinalTotals = () => {
        let finalTotalAmount = 0;
        let totalProducts = 0;
        let totalBoxCount = 0;
        let totalPattiCount = 0;
        let totalPacketCount = 0;
        
      
        $('.card').each(function() {
            const card = $(this);
            const totalCostElement = card.find('.productTotalCost');
            const boxInput = card.find('.productBox');
            const pattiInput = card.find('.productPatti');
            const packetInput = card.find('.productPacket');

            // Add up total costs
            const totalCost = parseFloat(totalCostElement.text().replace(/[^0-9.]/g, '')) || 0;
            finalTotalAmount += totalCost;

            // Count products that have any input value greater than 0
            const boxValue = parseFloat(boxInput.val()) || 0;
            const pattiValue = parseFloat(pattiInput.val()) || 0;
            const packetValue = parseFloat(packetInput.val()) || 0;

            if (boxValue > 0 || pattiValue > 0 || packetValue > 0) {
                totalProducts++;
            }

            // Accumulate total counts for Box, Patti, and Packet
            totalBoxCount += boxValue;
            totalPattiCount += pattiValue;
            totalPacketCount += packetValue;
        });

        
        
        $('#finalTotalAmount').html(`₹${finalTotalAmount.toFixed(2)} /INR`);
        $('#totalAmountHidden').val(`${finalTotalAmount.toFixed(2)}`);
        $('#totalProducts').html(`${totalProducts}`);
        $('#totalProductsHidden').val(`${totalProducts}`);

        // Display the total counts for Box, Patti, and Packet
        $('#totalBoxCount').html(`${totalBoxCount}`);
        $('#totalPattiCount').html(`${totalPattiCount}`);
        $('#totalPacketCount').html(`${totalPacketCount}`);
    }; 
    $('.card').each(function() {
        const card = $(this);

        // Extract product details from data attributes
        const boxPacketCount = parseFloat(card.data('box')) || 0;
        const boxPacket = parseFloat(card.data('packet')) || 0;
        const pattiPacketCount = parseFloat(card.data('per_patti_paket')) || 0;
        const pattiCount = parseFloat(card.data('patti')) || 0;
        const sellingPrice = parseFloat(card.data('selling_price')) || 0;

        // Inputs and display elements
        const boxInput = card.find('.productBox');
        const pattiInput = card.find('.productPatti');
        const packetInput = card.find('.productPacket');
        const totalQtyElement = card.find('.productTotalQty');
        const totalCostElement = card.find('.productTotalCost');
        const productTotalQtyElement = card.find('.productTotalFormQty');
        const productTotalCostElement = card.find('.productTotalFormCost');

        // Calculate totals
        const calculateTotals = () => {
            let totalPacketsFromBox = 0;
            let totalPacketsFromPatti = 0;
            let totalDirectPackets = 0;

            // Calculate packets from box
            const boxValue = parseFloat(boxInput.val()) || 0;
            totalPacketsFromBox = boxValue * boxPacket;

            // Calculate packets from patti
            const pattiValue = parseFloat(pattiInput.val()) || 0;
            totalPacketsFromPatti = pattiValue * pattiPacketCount;

            // Get directly entered packet value
            const packetValue = parseFloat(packetInput.val()) || 0;
            totalDirectPackets = packetValue;

            // Total quantity and cost
            const totalPackets = totalPacketsFromBox + totalPacketsFromPatti + totalDirectPackets;
            const totalCost = totalPackets * sellingPrice;

            // Update the display
            totalQtyElement.html(`${totalPackets} Pkt`);
            productTotalQtyElement.val(`${totalPackets}`);
            totalCostElement.html(`₹${totalCost.toFixed(2)} /INR`);
            productTotalCostElement.val(`${totalCost.toFixed(2)}`);

            // Update final totals
            updateFinalTotals();
        };

        // Add event listeners to inputs
        boxInput.on('input', calculateTotals);
        pattiInput.on('input', calculateTotals);
        packetInput.on('input', calculateTotals);
    }); 
    updateFinalTotals();
}); 


    $(document).ready(function() {
                const updateFinalTotals = () => {
                    let finalTotalAmount = 0;
                    let totalProducts = 0;

                    // Loop through each card to calculate the final totals
                    $('.card').each(function() {
                        const card = $(this);
                        const totalCostElement = card.find('.productTotalCost');
                        const boxInput = card.find('.productBox');
                        const pattiInput = card.find('.productPatti');
                        const packetInput = card.find('.productPacket');

                        // Add up total costs
                        const totalCost = parseFloat(totalCostElement.text().replace(/[^0-9.]/g, '')) || 0;
                        finalTotalAmount += totalCost;

                        // Count products that have any input value greater than 0
                        const boxValue = parseFloat(boxInput.val()) || 0;
                        const pattiValue = parseFloat(pattiInput.val()) || 0;
                        const packetValue = parseFloat(packetInput.val()) || 0;
                        if (boxValue > 0 || pattiValue > 0 || packetValue > 0) {
                            totalProducts++;
                        }
                    });

                    // Update the final totals on the page
                    $('#finalTotalAmount').html(`₹${finalTotalAmount.toFixed(2)} /INR`);
                    $('#totalAmountHidden').val(`${finalTotalAmount.toFixed(2)}`);
                    $('#totalProducts').html(`${totalProducts}`);
                    $('#totalProductsHidden').val(`${totalProducts}`);
                };

                // Loop through each card
                $('.card').each(function() {
                    const card = $(this);

                    // Extract product details from data attributes
                    const boxPacketCount = parseFloat(card.data('box')) || 0;
                    const boxPacket = parseFloat(card.data('packet')) || 0;
                    const pattiPacketCount = parseFloat(card.data('per_patti_paket')) || 0;
                    const pattiCount = parseFloat(card.data('patti')) || 0;
                    const sellingPrice = parseFloat(card.data('selling_price')) || 0;

                    // Inputs and display elements
                    const boxInput = card.find('.productBox');
                    const pattiInput = card.find('.productPatti');
                    const packetInput = card.find('.productPacket');
                    const totalQtyElement = card.find('.productTotalQty');
                    const totalCostElement = card.find('.productTotalCost');
                    const productTotalQtyElement = card.find('.productTotalFormQty');
                    const productTotalCostElement = card.find('.productTotalFormCost');

                    // Calculate totals
                    const calculateTotals = () => {
                        let totalPacketsFromBox = 0;
                        let totalPacketsFromPatti = 0;
                        let totalDirectPackets = 0;

                        // Calculate packets from box
                        const boxValue = parseFloat(boxInput.val()) || 0;
                        totalPacketsFromBox = boxValue * boxPacket;

                        // Calculate packets from patti
                        const pattiValue = parseFloat(pattiInput.val()) || 0;
                        totalPacketsFromPatti = pattiValue * pattiPacketCount;

                        // Get directly entered packet value
                        const packetValue = parseFloat(packetInput.val()) || 0;
                        totalDirectPackets = packetValue;

                        // Total quantity and cost
                        const totalPackets = totalPacketsFromBox + totalPacketsFromPatti +
                            totalDirectPackets;
                        const totalCost = totalPackets * sellingPrice;

                        // Update the display
                        totalQtyElement.html(`${totalPackets} Pkt`);
                        productTotalQtyElement.val(`${totalPackets}`);
                        totalCostElement.html(`₹${totalCost.toFixed(2)} /INR`);
                        productTotalCostElement.val(`${totalCost.toFixed(2)}`);

                        // Update final totals
                        updateFinalTotals();
                    };

                    // Add event listeners to inputs
                    boxInput.on('input', calculateTotals);
                    pattiInput.on('input', calculateTotals);
                    packetInput.on('input', calculateTotals);
                });

                // Initialize final totals on page load
                updateFinalTotals();
            });




            document.addEventListener("DOMContentLoaded", function () {
        const categories = document.querySelectorAll(".category-box");

        categories.forEach(category => {
            category.addEventListener("click", function () {
                // Remove "selected" class from all categories
                categories.forEach(cat => cat.classList.remove("selected"));

                // Add "selected" class to the clicked category
                this.classList.add("selected");
            });
        });
    });   
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".plus-btn").forEach(function (button) {
            button.addEventListener("click", function () {
                let card = this.closest(".card");  
                let targetClass = this.getAttribute("data-target");  
                let input = card.querySelector("." + targetClass); 
                if (input) {
                    input.value = parseInt(input.value) + 1;  
                    input.dispatchEvent(new Event("input"));  
                }
            });
        });

        document.querySelectorAll(".minus-btn").forEach(function (button) {
            button.addEventListener("click", function () {
                let card = this.closest(".card"); 
                let targetClass = this.getAttribute("data-target");  
                let input = card.querySelector("." + targetClass);  
                if (input && parseInt(input.value) > 0) {
                    input.value = parseInt(input.value) - 1;  
                    input.dispatchEvent(new Event("input"));  
                }
            });
        });
    }); 
    $(document).ready(function() {
            const updateFinalTotals = () => {
                let finalTotalAmount = 0;
                let totalProducts = 0;
                
                // Loop through each card to calculate the final totals
                $('.card').each(function() {
                    const card = $(this);
                    const totalCostElement = card.find('.productTotalCost');
                    const boxInput = card.find('.productBox');
                    const pattiInput = card.find('.productPatti');
                    const packetInput = card.find('.productPacket');

                    // Add up total costs
                    const totalCost = parseFloat(totalCostElement.text().replace(/[^0-9.]/g, '')) || 0;
                    finalTotalAmount += totalCost;
    
                    // Count products that have any input value greater than 0
                    const boxValue = parseFloat(boxInput.val()) || 0;
                    const pattiValue = parseFloat(pattiInput.val()) || 0;
                    const packetValue = parseFloat(packetInput.val()) || 0;
                    if (boxValue > 0 || pattiValue > 0 || packetValue > 0) {
                        totalProducts++;
                    }
                });
    
                // Update the final totals on the page
                $('#finalTotalAmount').html(`₹${finalTotalAmount.toFixed(2)} /INR`);
                $('#totalAmountHidden').val(`${finalTotalAmount.toFixed(2)}`);
                $('#totalProducts').html(`${totalProducts}`);
                $('#totalProductsHidden').val(`${totalProducts}`);
            };
            $('.card').each(function() {
                const card = $(this);
    
                // Extract product details from data attributes
                const boxPacketCount = parseFloat(card.data('box')) || 0;
                const boxPacket = parseFloat(card.data('packet')) || 0;
                const pattiPacketCount = parseFloat(card.data('per_patti_paket')) || 0;
                const pattiCount = parseFloat(card.data('patti')) || 0;
                const sellingPrice = parseFloat(card.data('selling_price')) || 0;
    
                // Inputs and display elements
                const boxInput = card.find('.productBox');
                const pattiInput = card.find('.productPatti');
                const packetInput = card.find('.productPacket');
                const totalQtyElement = card.find('.productTotalQty');
                const totalCostElement = card.find('.productTotalCost');
                const productTotalQtyElement = card.find('.productTotalFormQty');
                const productTotalCostElement = card.find('.productTotalFormCost');
    
                // Calculate totals
                const calculateTotals = () => {
                    let totalPacketsFromBox = 0;
                    let totalPacketsFromPatti = 0;
                    let totalDirectPackets = 0;
    
                    // Calculate packets from box
                    const boxValue = parseFloat(boxInput.val()) || 0;
                    totalPacketsFromBox = boxValue * boxPacket;
    
                    // Calculate packets from patti
                    const pattiValue = parseFloat(pattiInput.val()) || 0;
                    totalPacketsFromPatti = pattiValue * pattiPacketCount;
    
                    // Get directly entered packet value
                    const packetValue = parseFloat(packetInput.val()) || 0;
                    totalDirectPackets = packetValue;
    
                    // Total quantity and cost
                    const totalPackets = totalPacketsFromBox + totalPacketsFromPatti + totalDirectPackets;
                    const totalCost = totalPackets * sellingPrice;
    
                    // Update the display
                    totalQtyElement.html(`${totalPackets} Pkt`);
                    productTotalQtyElement.val(`${totalPackets}`);
                    totalCostElement.html(`₹${totalCost.toFixed(2)} /INR`);
                    productTotalCostElement.val(`${totalCost.toFixed(2)}`);
    
                    // Update final totals
                    updateFinalTotals();
                };
    
                // Add event listeners to inputs
                boxInput.on('input', calculateTotals);
                pattiInput.on('input', calculateTotals);
                packetInput.on('input', calculateTotals);
            });
            updateFinalTotals();
    });  
    </script>  
    <script>
       
    </script>

    
    <script>
       
    </script>
    
</body>

</html>
