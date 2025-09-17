<?php
/**
 * Design Order Popup Form Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Design Order Popup -->
<div id="design-order-popup" class="design-order-popup" style="display: none;">
    <div class="popup-overlay"></div>
    <div class="popup-content">
        <div class="popup-header">
            <h3><?php _e('سفارش طراحی', 'design-orders'); ?></h3>
            <button type="button" class="popup-close">&times;</button>
        </div>
        
        <div class="popup-body">
            <form id="design-order-form">
                <?php wp_nonce_field('design_orders_nonce', 'design_orders_nonce'); ?>
                
                <div class="form-group">
                    <label><?php _e('محصول انتخابی:', 'design-orders'); ?></label>
                    <div class="selected-product">
                        <span id="selected-product-name"></span>
                    </div>
                    <input type="hidden" id="product-id" name="product_id">
                    <input type="hidden" id="product-name" name="product_name">
                </div>
                
                <div class="form-group">
                    <label for="customer-email"><?php _e('ایمیل دریافت سفارش:', 'design-orders'); ?> <span class="required">*</span></label>
                    <input type="email" id="customer-email" name="customer_email" class="form-control" required>
                    <small class="form-text"><?php _e('فایل‌های طراحی به این ایمیل ارسال خواهد شد', 'design-orders'); ?></small>
                </div>
                
                <div class="form-group">
                    <label for="design-details"><?php _e('جزئیات سفارش طراحی:', 'design-orders'); ?> <span class="required">*</span></label>
                    <textarea id="design-details" name="design_details" class="form-control" rows="5" required placeholder="<?php esc_attr_e('لطفاً جزئیات سفارش طراحی خود را به طور کامل توضیح دهید...', 'design-orders'); ?>"></textarea>
                    <small class="form-text"><?php _e('هر چه جزئیات بیشتری ارائه دهید، نتیجه نهایی بهتر خواهد بود', 'design-orders'); ?></small>
                </div>
                
                <div class="form-group price-info">
                    <div class="price-display">
                        <?php
                        $design_price = get_option('design_orders_design_order_price', 50000);
                        ?>
                        <span class="price-label"><?php _e('هزینه سفارش طراحی:', 'design-orders'); ?></span>
                        <span class="price-amount"><?php echo number_format($design_price); ?> <?php _e('تومان', 'design-orders'); ?></span>
                    </div>
                </div>
                
                <div class="form-group terms-notice">
                    <div class="notice-box">
                        <p><strong><?php _e('توجه:', 'design-orders'); ?></strong></p>
                        <ul>
                            <li><?php _e('پس از ثبت سفارش، محصول سفارش طراحی به سبد خرید اضافه می‌شود', 'design-orders'); ?></li>
                            <li><?php _e('پروژه پس از تکمیل پرداخت شروع خواهد شد', 'design-orders'); ?></li>
                            <li><?php _e('زمان تحویل معمولاً 3 تا 5 روز کاری می‌باشد', 'design-orders'); ?></li>
                            <li><?php _e('فایل‌های نهایی به ایمیل شما ارسال خواهد شد', 'design-orders'); ?></li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="popup-footer">
            <button type="button" class="btn btn-secondary popup-close"><?php _e('انصراف', 'design-orders'); ?></button>
            <button type="button" id="submit-design-order" class="btn btn-primary">
                <span class="btn-text"><?php _e('ثبت سفارش طراحی', 'design-orders'); ?></span>
                <span class="btn-loading" style="display: none;">
                    <span class="spinner"></span>
                    <?php _e('در حال ثبت...', 'design-orders'); ?>
                </span>
            </button>
        </div>
    </div>
</div>

<!-- Success Message -->
<div id="design-order-success" class="design-order-popup" style="display: none;">
    <div class="popup-overlay"></div>
    <div class="popup-content success-popup">
        <div class="popup-header">
            <h3><?php _e('سفارش با موفقیت ثبت شد', 'design-orders'); ?></h3>
            <button type="button" class="popup-close">&times;</button>
        </div>
        
        <div class="popup-body">
            <div class="success-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#00a32a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <div class="success-message">
                <p><strong><?php _e('سفارش طراحی شما با موفقیت ثبت شد!', 'design-orders'); ?></strong></p>
                <p><?php _e('محصول سفارش طراحی به سبد خرید اضافه شد. لطفاً برای تکمیل فرآیند سفارش، به صفحه پرداخت بروید.', 'design-orders'); ?></p>
            </div>
        </div>
        
        <div class="popup-footer">
            <button type="button" class="btn btn-secondary popup-close"><?php _e('ادامه خرید', 'design-orders'); ?></button>
            <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn btn-primary">
                <?php _e('پرداخت سفارش', 'design-orders'); ?>
            </a>
        </div>
    </div>
</div>