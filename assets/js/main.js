// Main JavaScript untuk ShoeBrand Store

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Product image gallery
    $('.thumbnail').click(function() {
        var newSrc = $(this).attr('src');
        $('.main-image').attr('src', newSrc);
        $('.thumbnail').removeClass('active');
        $(this).addClass('active');
    });

    // Add to cart functionality
    $('.add-to-cart').click(function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        var quantity = $('#quantity').val() || 1;
        var button = $(this);
        var originalText = button.html();

        button.html('<span class="loading"></span> Menambahkan...');
        button.prop('disabled', true);

        $.ajax({
            url: 'ajax/add_to_cart.php',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update cart count
                    $('#cart-count').text(response.cart_count);
                    
                    // Show success message
                    showAlert('success', 'Produk berhasil ditambahkan ke keranjang!');
                    
                    // Reset button
                    button.html('<i class="fas fa-check"></i> Ditambahkan');
                    setTimeout(function() {
                        button.html(originalText);
                        button.prop('disabled', false);
                    }, 2000);
                } else {
                    showAlert('danger', response.message || 'Gagal menambahkan produk ke keranjang');
                    button.html(originalText);
                    button.prop('disabled', false);
                }
            },
            error: function() {
                showAlert('danger', 'Terjadi kesalahan. Silakan coba lagi.');
                button.html(originalText);
                button.prop('disabled', false);
            }
        });
    });

    // Update cart quantity
    $('.quantity-input').change(function() {
        var cartId = $(this).data('cart-id');
        var quantity = $(this).val();
        var row = $(this).closest('.cart-item');

        if (quantity < 1) {
            $(this).val(1);
            return;
        }

        $.ajax({
            url: 'ajax/update_cart.php',
            method: 'POST',
            data: {
                cart_id: cartId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update item total
                    row.find('.item-total').text(response.item_total);
                    // Update cart total
                    $('.cart-total').text(response.cart_total);
                    // Update cart count
                    $('#cart-count').text(response.cart_count);
                } else {
                    showAlert('danger', response.message || 'Gagal mengupdate keranjang');
                }
            },
            error: function() {
                showAlert('danger', 'Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    });

    // Remove from cart
    $('.remove-from-cart').click(function(e) {
        e.preventDefault();
        var cartId = $(this).data('cart-id');
        var row = $(this).closest('.cart-item');

        if (confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
            $.ajax({
                url: 'ajax/remove_from_cart.php',
                method: 'POST',
                data: {
                    cart_id: cartId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                            // Update cart total
                            $('.cart-total').text(response.cart_total);
                            // Update cart count
                            $('#cart-count').text(response.cart_count);
                            
                            if (response.cart_count == 0) {
                                location.reload();
                            }
                        });
                        showAlert('success', 'Produk berhasil dihapus dari keranjang');
                    } else {
                        showAlert('danger', response.message || 'Gagal menghapus produk dari keranjang');
                    }
                },
                error: function() {
                    showAlert('danger', 'Terjadi kesalahan. Silakan coba lagi.');
                }
            });
        }
    });

    // Search functionality
    $('#search-form').submit(function(e) {
        var searchTerm = $('#search-input').val().trim();
        if (searchTerm === '') {
            e.preventDefault();
            showAlert('warning', 'Masukkan kata kunci pencarian');
        }
    });

    // Filter products
    $('.filter-checkbox').change(function() {
        filterProducts();
    });

    $('#price-range').change(function() {
        filterProducts();
    });

    $('#sort-select').change(function() {
        filterProducts();
    });

    // Product rating
    $('.rating-star').click(function() {
        var rating = $(this).data('rating');
        var productId = $(this).data('product-id');
        
        $('.rating-star').removeClass('active');
        for (var i = 1; i <= rating; i++) {
            $('.rating-star[data-rating="' + i + '"]').addClass('active');
        }

        // Submit rating (if implemented)
        // submitRating(productId, rating);
    });

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});

// Helper functions
function showAlert(type, message) {
    var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
    
    $('.main-content').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

function filterProducts() {
    var categories = [];
    var priceRange = $('#price-range').val();
    var sortBy = $('#sort-select').val();

    $('.filter-checkbox:checked').each(function() {
        categories.push($(this).val());
    });

    var params = {
        categories: categories,
        price_range: priceRange,
        sort: sortBy
    };

    // Update URL with filters
    var url = new URL(window.location);
    Object.keys(params).forEach(key => {
        if (params[key] && params[key].length > 0) {
            url.searchParams.set(key, params[key]);
        } else {
            url.searchParams.delete(key);
        }
    });

    window.location.href = url.toString();
}

function formatPrice(price) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
}

// Admin functions
function deleteItem(type, id, name) {
    if (confirm('Apakah Anda yakin ingin menghapus ' + type + ' "' + name + '"?')) {
        $.ajax({
            url: 'ajax/delete_' + type + '.php',
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showAlert('danger', response.message || 'Gagal menghapus ' + type);
                }
            },
            error: function() {
                showAlert('danger', 'Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    }
}

function updateStatus(transactionId, status) {
    $.ajax({
        url: 'ajax/update_transaction_status.php',
        method: 'POST',
        data: {
            transaction_id: transactionId,
            status: status
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                showAlert('danger', response.message || 'Gagal mengupdate status');
            }
        },
        error: function() {
            showAlert('danger', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    });
}

// Image preview for file uploads
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#image-preview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Form validation
function validateForm(formId) {
    var isValid = true;
    $('#' + formId + ' [required]').each(function() {
        if ($(this).val() === '') {
            $(this).addClass('is-invalid');
            isValid = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    return isValid;
}

// Loading state
function showLoading(element) {
    $(element).html('<span class="loading"></span> Loading...');
    $(element).prop('disabled', true);
}

function hideLoading(element, originalText) {
    $(element).html(originalText);
    $(element).prop('disabled', false);
}