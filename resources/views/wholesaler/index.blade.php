<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" href="https://www.balajiwafers.com/wp-content/themes/custom/img/fav_logo.png">
    <title>Balaji</title>
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

            padding: 1px 7px 4px 7px;
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
        #error-message {
            display: none !important;
            text-align: center;
            margin-top: 50px;
            color: red;
            font-family: Arial, sans-serif;
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

        .btn-secondary {
            background-image: linear-gradient(90deg, #403099, #40309994);
        }

        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
            appearance: none;
        }

        .scrollable-container {
            display: flex;
            gap: 5px;
            overflow-x: auto;
            white-space: nowrap;
            padding: 5px;
        }

        .category-box {
            font-size: 12px;
            padding: 3px 8px;
            /* Reduced padding for smaller height */
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            background-color: #f8f9fa;
            transition: background 0.3s, color 0.3s;
            text-align: center;
            min-width: fit-content;
            /* Adjusts width based on text */
            max-width: 120px;
            /* Prevents excessive width */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            /* Prevents text wrapping */
            height: 28px;
            /* Reduced height */
            line-height: 22px;
            /* Centers text */
        }

        .category-box:hover,
        .category-box.selected {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }


        .category-box.selected {
            background-color: #403092;
            color: white;
            font-weight: bold;
        }


        .category-header {
            text-align: center;
            margin: 20px 0;
        }


        #error-message {
            display: none;
        }

        @media (max-width: 767px) {

            /* Mobile breakpoint */
            #error-message {
                display: block;
                text-align: center;
                color: red;
                font-family: Arial, sans-serif;
            }
        }
    </style>
</head>

<body>
    <div id="mobile-content">


        <form id="orderForm" action="{{ route('Wholesaler.addToCart') }}" method="POST">
            @csrf
            <div
                style="padding: 2px 8px; width: 100%; display: flex; justify-content: space-between; align-items: center; 
           background-image: linear-gradient(to top, #dbdbdb 0%, #FED6E3 100%);
           box-shadow: rgba(149, 157, 165, 0.4) 0px 4px 12px; height: 70px;">

                <img src="https://www.balajiwafers.com/wp-content/themes/custom/img/BalajiWafers.svg" alt=""
                    style="max-height: 60px; padding: 5px;"> <!-- Reduced height and padding -->
                <div class="search-container" style="flex-grow: 1; margin: 0 10px;">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search products..."
                        style="max-width: 250px; margin: 0 auto;">
                </div>
                <div>
                    <a class="btn btn-select-login" href="{{ route('Wholesaler.Cart') }}"
                        style="text-transform: capitalize !important; color: #403092; font-weight: bold;
                   border: 1px solid #403092; font-size: 14px; margin-right: 8px; padding: 5px 10px;">
                        <i class="fa-solid fa-cart-shopping"></i>
                        @php
                            $cart = session()->get('cart', []);
                        @endphp
                        @if (count($cart) > 0)
                            <span class="cart-count">{{ count($cart) }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <div class="scrollable-container ms-3 md-3">
                <div class="category-box" data-id="0">All</div>
                @if (count($category) > 0)
                    @foreach ($category as $cat)
                        <div class="category-box" data-id="{{ $cat->id }}">{{ $cat->name }}</div>
                    @endforeach
                @endif
            </div>

            <div class="container mb-5">
                <div class="row mt-1">
                    @foreach ($products as $key => $product)
                        <div class="col-md-12 {{ $key != 0 ? 'mt-1' : '' }}">
                            <div class="card" data-category="{{ $product->category_id }}"
                                data-box="{{ $product->box }}" data-patti="{{ $product->patti }}"
                                data-packet="{{ $product->packet }}" data-selling_price="{{ $product->selling_price }}"
                                data-per_patti_paket="{{ $product->per_patti_piece }}">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('product/' . $product->thumbnail) }}" alt=""
                                            style="width: 30px; margin-right: 8px;">
                                        <div class="row" style="width: 100%">
                                            <div class="col-12 text-start"> <!-- Ensures left alignment -->
                                                <p
                                                    style="color: #403092; font-weight: 500; font-size: 11px; margin: 0;">
                                                    {{ $product->name }}
                                                </p>
                                                <div class="mt-1 d-flex justify-content-between">
                                                    <p style="margin: 0; font-size: 12px;">
                                                        <span style="color: #c77642; font-weight: 500;">Total
                                                            Qty:</span>
                                                        <span class="productTotalQty">0Pkt</span>
                                                    </p>
                                                    <input type="hidden"
                                                        name="products[{{ $product->id }}][productTotalQty]"
                                                        class="productTotalFormQty">

                                                    <p style="margin: 0; font-size: 12px;">
                                                        <span style="color: #507a53; font-weight: 500;">Total
                                                            Cost:</span>
                                                        <span class="productTotalCost">0.00/INR</span>
                                                    </p>
                                                    <input type="hidden"
                                                        name="products[{{ $product->id }}][productTotalCost]"
                                                        class="productTotalFormCost">
                                                </div>
                                            </div>
                                        </div>
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
                                                    <!-- Grey color for minus button -->
                                                    <button type="button" class="btn btn-secondary btn-sm minus-btn"
                                                        data-target="productBox">-</button>
                                                    <input type="number" name="products[{{ $product->id }}][box]"
                                                        class="form-control productBox text-center" value="0"
                                                        placeholder="Box" onclick="this.select();">
                                                    <!-- Grey color for plus button -->
                                                    <button type="button" class="btn btn-secondary btn-sm plus-btn"
                                                        data-target="productBox">+</button>
                                                </div>
                                            </div>
                                        @endif
                                        @if (in_array('2', $unitTypesArray))
                                            <div class="col-{{ 12 / count($product->unit_type_names) }}">
                                                <label for="patti">Patti</label>
                                                <div class="input-group">
                                                    <button type="button" class="btn btn-secondary btn-sm minus-btn"
                                                        data-target="productPatti">-</button>
                                                    <input type="number" name="products[{{ $product->id }}][patti]"
                                                        class="form-control productPatti text-center" value="0"
                                                        placeholder="Patti" onclick="this.select();">
                                                    <button type="button" class="btn btn-secondary btn-sm plus-btn"
                                                        data-target="productPatti">+</button>
                                                </div>
                                            </div>
                                        @endif
                                        @if (in_array('3', $unitTypesArray))
                                            <div class="col-{{ 12 / count($product->unit_type_names) }}">
                                                <label for="packet">Packet</label>
                                                <div class="input-group">
                                                    <button type="button" class="btn btn-secondary btn-sm minus-btn"
                                                        data-target="productPacket">-</button>
                                                    <input type="number"
                                                        name="products[{{ $product->id }}][packet]"
                                                        class="form-control productPacket text-center" value="0"
                                                        placeholder="Packet" onclick="this.select();">
                                                    <button type="button" class="btn btn-secondary btn-sm plus-btn"
                                                        data-target="productPacket">+</button>
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
                        style="background: none;font-weight: 600;border: 1px solid #ffffff;background-image: linear-gradient(90deg, #403099, #40309994);color: #ffffff;">Add
                        To Cart</button>
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
        document.addEventListener("DOMContentLoaded", function() {
            const categories = document.querySelectorAll(".category-box");

            categories.forEach(category => {
                category.addEventListener("click", function() {
                    // Remove "selected" class from all categories
                    categories.forEach(cat => cat.classList.remove("selected"));

                    // Add "selected" class to the clicked category
                    this.classList.add("selected");
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".plus-btn").forEach(function(button) {
                button.addEventListener("click", function() {
                    let card = this.closest(".card");
                    let targetClass = this.getAttribute("data-target");
                    let input = card.querySelector("." + targetClass);
                    if (input) {
                        input.value = parseInt(input.value) + 1;
                        input.dispatchEvent(new Event("input"));
                    }
                });
            });

            document.querySelectorAll(".minus-btn").forEach(function(button) {
                button.addEventListener("click", function() {
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
            updateFinalTotals();
        });
    </script>
    <script>
        $(document).ready(function() {
            const totalProductsElement = $('#totalProducts');
            const finalTotalAmountElement = $('#finalTotalAmount');
            $('#add-product').on('click', function() {
                const totalProducts = totalProductsElement.text();
                const finalTotalAmount = finalTotalAmountElement.text();
                const confirmation = confirm(
                    `You are about to place an order with ${totalProducts} products for a total of ${finalTotalAmount}. Do you want to proceed?`
                    );
                if (confirmation) {
                    $('#orderForm').submit();
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#searchInput').on('input', function() {
                const searchText = $(this).val().toLowerCase();

                $('.card').each(function() {
                    const productName = $(this).find('.card-header p').text().toLowerCase();
                    const card = $(this).closest('.col-md-12');

                    if (productName.includes(searchText)) {
                        card.show();
                    } else {
                        card.hide();
                    }
                });
            });
        });

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
            updateFinalTotals();
        });


        $(document).ready(function() {
            const totalProductsElement = $('#totalProducts');
            const finalTotalAmountElement = $('#finalTotalAmount');
            $('#add-product').on('click', function() {
                const totalProducts = totalProductsElement.text();
                const finalTotalAmount = finalTotalAmountElement.text();
                const confirmation = confirm(
                    `You are about to place an order with ${totalProducts} products for a total of ${finalTotalAmount}. Do you want to proceed?`
                    );
                if (confirmation) {
                    $('#orderForm').submit();
                }
            });
        });



        $(document).ready(function() {

            $(".category-box").click(function() {
                let categoryId = $(this).attr('data-id');
                console.log(categoryId);
                if (categoryId == "0") {
                    $(".card").closest('.col-md-12').show(); // Show all products
                } else {
                    // Hide all products first
                    $(".card").closest('.col-md-12').hide();

                    // Show products that match the selected category
                    $(".card").each(function() {
                        let productCategory = $(this).attr(
                        'data-category'); // Assuming you added a data-category attribute to each product card
                        if (productCategory == categoryId) {
                            $(this).closest('.col-md-12').show(); // Show the matching product
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
