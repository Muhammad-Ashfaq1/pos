$(document).ready(function() {
    // Click on category card
    $('.category-card').on('click', function() {
        var categoryName = $(this).find('h4').text();
        $('.category-title, .product-name').text(categoryName);
        $('.categories-view').addClass('d-none');
        $('.product-details-view').removeClass('d-none');
    });

    // Click on back button
    $('.btn-back-to-categories').on('click', function() {
        $('.product-details-view').addClass('d-none');
        $('.categories-view').removeClass('d-none');
    });

    // Clear button
    $('.btn-clear').on('click', function() {
        $('.product-details-view input').val('');
    });

    // Function to update item price based on quantity
    function updateItemPrice($btn) {
        var $row = $btn.closest('tr');
        var $qtySpan = $row.find('.qty-value');
        var $priceSpan = $row.find('.item-price');
        var currentQty = parseFloat($qtySpan.text());
        var unitPrice = parseFloat($row.attr('data-unit-price')) || 5.208333;
        var newPrice = (currentQty * unitPrice);
        $priceSpan.text('$' + newPrice);
        updateSummary();
    }

    // Function to update the summary footer
    function updateSummary() {
        var totalQty = 0;
        var totalAmount = 0;

        $('#cart-items-tbody tr:not(.empty-cart-message)').each(function() {
            var qty = parseFloat($(this).find('.qty-value').text());
            var priceText = $(this).find('.item-price').text().replace('$', '');
            var price = parseFloat(priceText);

            if (!isNaN(qty)) totalQty += qty;
            if (!isNaN(price)) totalAmount += price;
        });

        $('.summary-qty').text(totalQty.toFixed(0)); // Show whole number for items count
        $('.summary-subtotal').text('$' + totalAmount.toFixed(3));
        $('.summary-total').text('$' + totalAmount.toFixed(3));

        // Update Pay button text
        $('.btn-pay .text-warning:first').text('$' + totalAmount.toFixed(3));
        if (totalAmount > 0) {
            $('.btn-pay').removeAttr('disabled');
        } else {
            $('.btn-pay').attr('disabled', 'disabled');
        }
    }

    // Quantity Plus
    $(document).on('click', '.btn-qty-plus', function() {
        var $qtySpan = $(this).siblings('.qty-value');
        var currentQty = parseFloat($qtySpan.text());
        $qtySpan.text((currentQty + 1));
        updateItemPrice($(this));
    });

    // Quantity Minus
    $(document).on('click', '.btn-qty-minus', function() {
        var $qtySpan = $(this).siblings('.qty-value');
        var currentQty = parseFloat($qtySpan.text());
        if (currentQty > 0) {
            $qtySpan.text((currentQty - 1));
            updateItemPrice($(this));
        }
    });

    // Add to Cart Logic
    $('.btn-add-to-cart').on('click', function() {
        var productName = $('.product-name').text();
        var qty = parseFloat($('.product-qty-input').val());
        var unitPriceText = $('.product-price').text().replace('$', '');
        var unitPrice = parseFloat(unitPriceText);
        var totalPrice = (qty * unitPrice);

        if (isNaN(qty) || qty <= 0) {
            alert('Please enter a valid quantity');
            return;
        }

        // Remove empty message if exists
        $('.empty-cart-message').remove();

        // Create new row HTML
        var newRow = `
            <tr data-unit-price="${unitPrice}">
                <td class="p-0 text-center" style="width: 40px;">
                    <button class="btn btn-link text-danger p-0 border-0 btn-remove-item align-items-center justify-content-center">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                </td>
                <td class="p-0">
                    <div class="bg-label-primary bg-opacity-10 rounded-pill py-2 px-3 shadow-sm row g-0 align-items-center mb-2">
                        <div class="col-5">
                            <span class="fw-bold text-secondary small">${productName}</span>
                        </div>
                        <div class="col-4 text-center d-flex align-items-center justify-content-center">
                            <button class="btn btn-sm p-0 border-0 text-secondary fw-light btn-qty-minus">—</button>
                            <span class="mx-2 fw-bold text-dark small qty-value">${qty}</span>
                            <button class="btn btn-sm p-0 border-0 text-secondary fw-light btn-qty-plus">+</button>
                        </div>
                        <div class="col-3 text-end">
                            <span class="fw-bold text-primary small item-price">$${totalPrice}</span>
                        </div>
                    </div>
                </td>
            </tr>
        `;

        $('#cart-items-tbody').append(newRow);
        updateSummary();

        // Switch back to categories view
        $('.product-details-view').addClass('d-none');
        $('.categories-view').removeClass('d-none');
        $('.category-title-main').text('Categories');
        $('.product-qty-input').val('1'); // Reset qty
    });

    // Remove item from cart
    $(document).on('click', '.btn-remove-item', function() {
        $(this).closest('tr').remove();
        updateSummary();
        if ($('#cart-items-tbody tr').length === 0) {
            $('#cart-items-tbody').append(`
                <tr class="empty-cart-message">
                    <td colspan="2" class="text-center py-5">
                        <p class="text-muted fw-bold mb-0">No Items Added</p>
                    </td>
                </tr>
            `);
        }
    });
});
