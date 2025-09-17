/**
 * Frontend JavaScript for Design Orders Plugin
 */

jQuery(document).ready(function($) {
    'use strict';
    
    var currentProductId = null;
    var currentProductName = null;
    
    // Handle design order checkbox click
    $(document).on('change', '.design-order-checkbox', function() {
        if ($(this).is(':checked')) {
            // Uncheck other checkboxes (only one design order at a time)
            $('.design-order-checkbox').not(this).prop('checked', false);
            
            currentProductId = $(this).data('product-id');
            currentProductName = $(this).data('product-name');
            
            // Populate popup with product info
            $('#product-id').val(currentProductId);
            $('#product-name').val(currentProductName);
            $('#selected-product-name').text(currentProductName);
            
            // Show popup
            showPopup('#design-order-popup');
        }
    });
    
    // Close popup handlers
    $(document).on('click', '.popup-close', function() {
        hidePopup();
        // Uncheck the checkbox
        $('.design-order-checkbox').prop('checked', false);
    });
    
    $(document).on('click', '.popup-overlay', function() {
        hidePopup();
        // Uncheck the checkbox
        $('.design-order-checkbox').prop('checked', false);
    });
    
    // Close popup on escape key
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27) { // Escape key
            hidePopup();
            $('.design-order-checkbox').prop('checked', false);
        }
    });
    
    // Form validation
    function validateForm() {
        var isValid = true;
        var email = $('#customer-email').val();
        var details = $('#design-details').val();
        
        // Clear previous errors
        $('.form-control').removeClass('error');
        $('.error-message').remove();
        
        // Validate email
        if (!email || !isValidEmail(email)) {
            $('#customer-email').addClass('error');
            $('#customer-email').after('<span class="error-message">' + 
                (email ? design_orders_ajax.invalid_email : 'این فیلد الزامی است') + 
                '</span>');
            isValid = false;
        }
        
        // Validate details
        if (!details.trim()) {
            $('#design-details').addClass('error');
            $('#design-details').after('<span class="error-message">این فیلد الزامی است</span>');
            isValid = false;
        }
        
        // Shake invalid form groups
        if (!isValid) {
            $('.form-control.error').closest('.form-group').addClass('shake');
            setTimeout(function() {
                $('.form-group').removeClass('shake');
            }, 500);
        }
        
        return isValid;
    }
    
    // Email validation
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Submit design order
    $(document).on('click', '#submit-design-order', function() {
        if (!validateForm()) {
            return;
        }
        
        var $button = $(this);
        
        // Add loading state
        $button.addClass('loading').prop('disabled', true);
        
        var formData = {
            action: 'submit_design_order',
            nonce: design_orders_ajax.nonce,
            product_id: $('#product-id').val(),
            product_name: $('#product-name').val(),
            customer_email: $('#customer-email').val().trim(),
            design_details: $('#design-details').val().trim()
        };
        
        $.ajax({
            url: design_orders_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Hide form popup
                    hidePopup('#design-order-popup');
                    
                    // Show success popup
                    showPopup('#design-order-success');
                    
                    // Reset form
                    resetForm();
                    
                    // Update mini cart (WooCommerce will handle this automatically)
                    $(document.body).trigger('wc_fragment_refresh');
                    
                } else {
                    showError(response.data || design_orders_ajax.error);
                }
            },
            error: function() {
                showError(design_orders_ajax.error);
            },
            complete: function() {
                // Remove loading state
                $button.removeClass('loading').prop('disabled', false);
            }
        });
    });
    
    // Show popup
    function showPopup(selector) {
        $(selector).fadeIn(300);
        $('body').addClass('popup-open');
        
        // Focus on first input
        setTimeout(function() {
            $(selector).find('input, textarea').first().focus();
        }, 300);
    }
    
    // Hide popup
    function hidePopup(selector) {
        if (selector) {
            $(selector).fadeOut(300);
        } else {
            $('.design-order-popup').fadeOut(300);
        }
        
        $('body').removeClass('popup-open');
    }
    
    // Reset form
    function resetForm() {
        $('#design-order-form')[0].reset();
        $('.form-control').removeClass('error');
        $('.error-message').remove();
        $('.design-order-checkbox').prop('checked', false);
        currentProductId = null;
        currentProductName = null;
    }
    
    // Show error message
    function showError(message) {
        // Remove existing error
        $('.popup-error').remove();
        
        // Add error message
        var $error = $('<div class="popup-error" style="background:#f8d7da;color:#721c24;padding:10px;margin:10px 0;border-radius:4px;border:1px solid #f5c6cb;">' + 
                      message + '</div>');
        $('.popup-body').prepend($error);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $error.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Real-time email validation
    $('#customer-email').on('blur', function() {
        var email = $(this).val();
        var $field = $(this);
        
        $field.removeClass('error');
        $field.next('.error-message').remove();
        
        if (email && !isValidEmail(email)) {
            $field.addClass('error');
            $field.after('<span class="error-message">' + design_orders_ajax.invalid_email + '</span>');
        }
    });
    
    // Character counter for details textarea
    $('#design-details').on('input', function() {
        var length = $(this).val().length;
        var maxLength = 1000;
        
        // Remove existing counter
        $(this).siblings('.char-counter').remove();
        
        // Add counter
        var counterClass = '';
        if (length > maxLength * 0.8) {
            counterClass = length > maxLength ? 'error' : 'warning';
        }
        
        $(this).after('<small class="form-text char-counter ' + counterClass + '">' + 
                     length + ' / ' + maxLength + ' کاراکتر</small>');
        
        // Limit characters
        if (length > maxLength) {
            $(this).val($(this).val().substring(0, maxLength));
        }
    });
    
    // Auto-resize textarea
    function autoResize() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    }
    
    $('#design-details').on('input', autoResize);
    
    // Initialize textarea height
    setTimeout(function() {
        $('#design-details').each(function() {
            autoResize.call(this);
        });
    }, 100);
    
    // Prevent form submission on enter (except in textarea)
    $('#design-order-form').on('keypress', function(e) {
        if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            $('#submit-design-order').click();
        }
    });
    
    // Handle mini cart updates
    $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
        // Re-bind events if mini cart was refreshed
        bindDesignOrderEvents();
    });
    
    function bindDesignOrderEvents() {
        // This function would re-bind events if needed after AJAX updates
        // Currently events are bound with $(document).on() so they persist
    }
    
    // Add CSS to prevent body scroll when popup is open
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            body.popup-open {
                overflow: hidden;
            }
            .char-counter.warning {
                color: #e8ad00;
            }
            .char-counter.error {
                color: #d63638;
            }
        `)
        .appendTo('head');
    
    // Handle page visibility change (pause/resume auto-refresh if implemented)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is now hidden
        } else {
            // Page is now visible
            // Could refresh cart fragments here if needed
        }
    });
    
    // Touch/swipe support for mobile popup closing
    var startY = 0;
    var startX = 0;
    
    $('.popup-content').on('touchstart', function(e) {
        startY = e.originalEvent.touches[0].clientY;
        startX = e.originalEvent.touches[0].clientX;
    });
    
    $('.popup-content').on('touchmove', function(e) {
        e.stopPropagation();
    });
    
    $('.popup-overlay').on('touchend', function(e) {
        var endY = e.originalEvent.changedTouches[0].clientY;
        var endX = e.originalEvent.changedTouches[0].clientX;
        var diffY = Math.abs(endY - startY);
        var diffX = Math.abs(endX - startX);
        
        // If it's a tap (not a swipe)
        if (diffY < 50 && diffX < 50) {
            hidePopup();
            $('.design-order-checkbox').prop('checked', false);
        }
    });
    
    // Analytics tracking (if Google Analytics is available)
    function trackEvent(action, category, label) {
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                event_category: category,
                event_label: label
            });
        } else if (typeof ga !== 'undefined') {
            ga('send', 'event', category, action, label);
        }
    }
    
    // Track design order events
    $(document).on('change', '.design-order-checkbox', function() {
        if ($(this).is(':checked')) {
            trackEvent('design_order_popup_opened', 'Design Orders', $(this).data('product-name'));
        }
    });
    
    $(document).on('click', '#submit-design-order', function() {
        trackEvent('design_order_submitted', 'Design Orders', currentProductName);
    });
});