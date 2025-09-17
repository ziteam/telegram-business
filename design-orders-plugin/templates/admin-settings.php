<?php
/**
 * Admin Settings Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('تنظیمات سفارشات طراحی', 'design-orders'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('design_orders_settings'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="enable_design_orders"><?php _e('فعال‌سازی سفارشات طراحی', 'design-orders'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="enable_design_orders" name="enable_design_orders" value="yes" <?php checked($enable_design_orders, 'yes'); ?>>
                        <label for="enable_design_orders"><?php _e('سیستم سفارش طراحی را فعال کنید', 'design-orders'); ?></label>
                        <p class="description"><?php _e('با فعال کردن این گزینه، چک باکس سفارش طراحی در سبد خرید نمایش داده می‌شود.', 'design-orders'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="design_order_price"><?php _e('قیمت سفارش طراحی', 'design-orders'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="design_order_price" name="design_order_price" value="<?php echo esc_attr($design_order_price); ?>" class="regular-text" min="0" step="1000">
                        <p class="description"><?php _e('قیمت پیش‌فرض برای سفارشات طراحی (به تومان)', 'design-orders'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="notification_email"><?php _e('ایمیل اطلاع‌رسانی', 'design-orders'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="notification_email" name="notification_email" value="<?php echo esc_attr($notification_email); ?>" class="regular-text">
                        <p class="description"><?php _e('ایمیلی که اطلاعیه‌های سفارشات جدید به آن ارسال می‌شود', 'design-orders'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <h2><?php _e('راهنمای استفاده', 'design-orders'); ?></h2>
        <div class="card">
            <h3><?php _e('نحوه عملکرد پلاگین:', 'design-orders'); ?></h3>
            <ol style="padding-right: 20px;">
                <li><?php _e('مشتری محصولات را به سبد خرید اضافه می‌کند', 'design-orders'); ?></li>
                <li><?php _e('در mini cart، چک باکس "سفارش طراحی" برای هر محصول نمایش داده می‌شود', 'design-orders'); ?></li>
                <li><?php _e('با انتخاب چک باکس، پاپ اپ سفارش طراحی باز می‌شود', 'design-orders'); ?></li>
                <li><?php _e('مشتری جزئیات سفارش و ایمیل خود را وارد می‌کند', 'design-orders'); ?></li>
                <li><?php _e('سفارش با وضعیت "در انتظار پرداخت" ثبت می‌شود', 'design-orders'); ?></li>
                <li><?php _e('محصول "سفارش طراحی" به سبد خرید اضافه می‌شود', 'design-orders'); ?></li>
                <li><?php _e('پس از پرداخت، وضعیت به "در انتظار اجرا" تغییر می‌کند', 'design-orders'); ?></li>
                <li><?php _e('ایمیل اطلاع‌رسانی به ادمین ارسال می‌شود', 'design-orders'); ?></li>
            </ol>
        </div>
        
        <div class="card">
            <h3><?php _e('سازگاری با قالب Woodmart:', 'design-orders'); ?></h3>
            <ul style="padding-right: 20px;">
                <li><?php _e('پلاگین با هوک‌های استاندارد ووکامرس توسعه داده شده است', 'design-orders'); ?></li>
                <li><?php _e('استایل‌های CSS با قالب Woodmart سازگار است', 'design-orders'); ?></li>
                <li><?php _e('Mini cart به گونه‌ای تغییر کرده که با طراحی قالب هماهنگ باشد', 'design-orders'); ?></li>
            </ul>
        </div>
        
        <div class="card">
            <h3><?php _e('وضعیت‌های سفارش:', 'design-orders'); ?></h3>
            <ul style="padding-right: 20px;">
                <li><strong><?php _e('در انتظار پرداخت:', 'design-orders'); ?></strong> <?php _e('سفارش ثبت شده اما هنوز پرداخت نشده', 'design-orders'); ?></li>
                <li><strong><?php _e('در انتظار اجرا:', 'design-orders'); ?></strong> <?php _e('سفارش پرداخت شده و آماده اجرا', 'design-orders'); ?></li>
                <li><strong><?php _e('تکمیل شده:', 'design-orders'); ?></strong> <?php _e('سفارش طراحی تکمیل شده', 'design-orders'); ?></li>
                <li><strong><?php _e('لغو شده:', 'design-orders'); ?></strong> <?php _e('سفارش لغو شده است', 'design-orders'); ?></li>
            </ul>
        </div>
        
        <?php submit_button(__('ذخیره تنظیمات', 'design-orders')); ?>
    </form>
    
    <div class="card">
        <h3><?php _e('آمار کلی', 'design-orders'); ?></h3>
        <?php
        $database = new Design_Orders_Database();
        $total_orders = $database->get_orders_count();
        $pending_orders = $database->get_orders_count('pending_payment');
        $processing_orders = $database->get_orders_count('processing');
        $completed_orders = $database->get_orders_count('completed');
        ?>
        <table class="widefat">
            <tbody>
                <tr>
                    <td><strong><?php _e('کل سفارشات:', 'design-orders'); ?></strong></td>
                    <td><?php echo number_format($total_orders); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('در انتظار پرداخت:', 'design-orders'); ?></strong></td>
                    <td><?php echo number_format($pending_orders); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('در انتظار اجرا:', 'design-orders'); ?></strong></td>
                    <td><?php echo number_format($processing_orders); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('تکمیل شده:', 'design-orders'); ?></strong></td>
                    <td><?php echo number_format($completed_orders); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>