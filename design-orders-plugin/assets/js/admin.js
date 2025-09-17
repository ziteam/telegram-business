/**
 * Admin JavaScript for Design Orders Plugin
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Update order status
    $('.status-select').on('change', function() {
        var $select = $(this);
        var orderId = $select.data('order-id');
        var newStatus = $select.val();
        var $row = $select.closest('tr');
        
        // Add loading state
        $select.addClass('loading').prop('disabled', true);
        $row.addClass('updating');
        
        $.ajax({
            url: design_orders_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'update_design_order_status',
                nonce: design_orders_ajax.nonce,
                order_id: orderId,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    // Update status badge
                    var $statusBadge = $row.find('.status-badge');
                    var statusLabels = {
                        'pending_payment': 'در انتظار پرداخت',
                        'processing': 'در انتظار اجرا',
                        'completed': 'تکمیل شده',
                        'cancelled': 'لغو شده'
                    };
                    var statusColors = {
                        'pending_payment': '#e8ad00',
                        'processing': '#0073aa',
                        'completed': '#00a32a',
                        'cancelled': '#d63638'
                    };
                    
                    $statusBadge.text(statusLabels[newStatus])
                              .css('background-color', statusColors[newStatus]);
                    
                    // Show success message
                    showMessage(response.data, 'success');
                } else {
                    // Revert select value
                    $select.val($select.data('original-value'));
                    showMessage(response.data || design_orders_ajax.error, 'error');
                }
            },
            error: function() {
                // Revert select value
                $select.val($select.data('original-value'));
                showMessage(design_orders_ajax.error, 'error');
            },
            complete: function() {
                // Remove loading state
                $select.removeClass('loading').prop('disabled', false);
                $row.removeClass('updating');
            }
        });
    });
    
    // Store original values for status selects
    $('.status-select').each(function() {
        $(this).data('original-value', $(this).val());
    });
    
    // Delete order
    $('.delete-order').on('click', function() {
        if (!confirm(design_orders_ajax.confirm_delete)) {
            return;
        }
        
        var $button = $(this);
        var orderId = $button.data('order-id');
        var $row = $button.closest('tr');
        
        // Add loading state
        $button.prop('disabled', true).text(design_orders_ajax.updating);
        $row.addClass('updating');
        
        $.ajax({
            url: design_orders_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_design_order',
                nonce: design_orders_ajax.nonce,
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    // Remove row with animation
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        // Update row count if needed
                        updateRowCount();
                    });
                    
                    showMessage(response.data, 'success');
                } else {
                    showMessage(response.data || design_orders_ajax.error, 'error');
                    $button.prop('disabled', false).text('حذف');
                    $row.removeClass('updating');
                }
            },
            error: function() {
                showMessage(design_orders_ajax.error, 'error');
                $button.prop('disabled', false).text('حذف');
                $row.removeClass('updating');
            }
        });
    });
    
    // Show full details
    $('.show-full-details').on('click', function() {
        var orderId = $(this).data('order-id');
        var fullDetails = $('#full-details-' + orderId).html();
        
        $('#modal-details-content').html(fullDetails);
        $('#design-details-modal').show();
    });
    
    // Close modal
    $('.modal-close').on('click', function() {
        $('#design-details-modal').hide();
    });
    
    // Close modal on overlay click
    $('#design-details-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // Close modal on escape key
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27) { // Escape key
            $('#design-details-modal').hide();
        }
    });
    
    // Show message function
    function showMessage(message, type) {
        var $message = $('<div class="design-orders-message ' + type + '">' + message + '</div>');
        $('.wrap > h1').after($message);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $message.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Update row count function
    function updateRowCount() {
        var remainingRows = $('.wp-list-table tbody tr').length;
        if (remainingRows === 0) {
            location.reload(); // Reload to show empty state
        }
    }
    
    // Filter form auto-submit (if needed)
    $('#status-filter').on('change', function() {
        $(this).closest('form').submit();
    });
    
    // Bulk actions (if implemented in future)
    $('#doaction, #doaction2').on('click', function(e) {
        var action = $(this).siblings('select').val();
        if (action === '-1') {
            e.preventDefault();
            return false;
        }
        
        var selectedItems = $('input[name="order[]"]:checked');
        if (selectedItems.length === 0) {
            e.preventDefault();
            alert('لطفاً حداقل یک سفارش را انتخاب کنید.');
            return false;
        }
        
        if (action === 'delete') {
            if (!confirm('آیا از حذف سفارشات انتخابی اطمینان دارید؟')) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Select all checkbox
    $('#cb-select-all-1, #cb-select-all-2').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('input[name="order[]"]').prop('checked', isChecked);
    });
    
    // Individual checkbox change
    $('input[name="order[]"]').on('change', function() {
        var totalBoxes = $('input[name="order[]"]').length;
        var checkedBoxes = $('input[name="order[]"]:checked').length;
        
        $('#cb-select-all-1, #cb-select-all-2').prop('checked', totalBoxes === checkedBoxes);
    });
    
    // Search functionality (if needed)
    var searchTimeout;
    $('#order-search').on('keyup', function() {
        clearTimeout(searchTimeout);
        var searchTerm = $(this).val().toLowerCase();
        
        searchTimeout = setTimeout(function() {
            $('.wp-list-table tbody tr').each(function() {
                var rowText = $(this).text().toLowerCase();
                if (rowText.indexOf(searchTerm) === -1) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        }, 300);
    });
    
    // Export functionality (if needed)
    $('#export-orders').on('click', function() {
        var exportUrl = $(this).data('export-url');
        if (exportUrl) {
            window.open(exportUrl, '_blank');
        }
    });
    
    // Quick edit functionality (if needed)
    $('.quick-edit').on('click', function() {
        var orderId = $(this).data('order-id');
        // Implementation for quick edit
        console.log('Quick edit for order:', orderId);
    });
    
    // Auto-refresh orders (every 30 seconds)
    if ($('.wp-list-table').length > 0) {
        setInterval(function() {
            // Check for new orders without full page reload
            checkForNewOrders();
        }, 30000);
    }
    
    function checkForNewOrders() {
        // This would be implemented to check for new orders via AJAX
        // and show a notification if new orders are available
    }
    
    // Tooltip initialization (if using a tooltip library)
    if (typeof $.fn.tooltip !== 'undefined') {
        $('[data-tooltip]').tooltip();
    }
    
    // Print order details
    $('.print-order').on('click', function() {
        var orderId = $(this).data('order-id');
        var printWindow = window.open('', '_blank');
        var orderRow = $(this).closest('tr');
        
        var printContent = '<html><head><title>Print Order #' + orderId + '</title>';
        printContent += '<style>body{font-family:Arial,sans-serif;direction:rtl;}</style>';
        printContent += '</head><body>';
        printContent += '<h2>سفارش طراحی #' + orderId + '</h2>';
        printContent += '<table border="1" cellpadding="8" cellspacing="0">';
        printContent += '<tr><td>محصول:</td><td>' + orderRow.find('.column-product').text() + '</td></tr>';
        printContent += '<tr><td>مشتری:</td><td>' + orderRow.find('.column-customer').text() + '</td></tr>';
        printContent += '<tr><td>وضعیت:</td><td>' + orderRow.find('.status-badge').text() + '</td></tr>';
        printContent += '<tr><td>تاریخ:</td><td>' + orderRow.find('.column-date').text() + '</td></tr>';
        printContent += '</table>';
        printContent += '</body></html>';
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    });
});