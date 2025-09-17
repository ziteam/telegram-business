<?php
/**
 * Email Class for Design Orders
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Design_Orders_Email {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Email actions can be added here if needed
    }
    
    /**
     * Send new order notification to admin
     */
    public function send_new_order_notification($order_data) {
        $notification_email = get_option('design_orders_notification_email', get_option('admin_email'));
        
        if (empty($notification_email)) {
            return false;
        }
        
        $subject = __('سفارش طراحی جدید دریافت شد', 'design-orders');
        
        $message = $this->get_new_order_email_template($order_data);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        return wp_mail($notification_email, $subject, $message, $headers);
    }
    
    /**
     * Send order processing notification to admin
     */
    public function send_order_processing_notification($order_data) {
        $notification_email = get_option('design_orders_notification_email', get_option('admin_email'));
        
        if (empty($notification_email)) {
            return false;
        }
        
        $subject = __('سفارش طراحی در انتظار اجرا', 'design-orders');
        
        $message = $this->get_processing_order_email_template($order_data);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        return wp_mail($notification_email, $subject, $message, $headers);
    }
    
    /**
     * Send order completion notification to customer
     */
    public function send_order_completion_notification($order_data) {
        $customer_email = $order_data['customer_email'];
        
        if (empty($customer_email)) {
            return false;
        }
        
        $subject = __('سفارش طراحی شما تکمیل شد', 'design-orders');
        
        $message = $this->get_completion_email_template($order_data);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        return wp_mail($customer_email, $subject, $message, $headers);
    }
    
    /**
     * Get new order email template
     */
    private function get_new_order_email_template($order_data) {
        $site_name = get_bloginfo('name');
        $admin_url = admin_url('admin.php?page=design-orders');
        
        $message = '<html><body dir="rtl" style="font-family: Tahoma, Arial, sans-serif;">';
        $message .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd;">';
        $message .= '<h2 style="color: #333; text-align: center;">' . sprintf(__('سفارش طراحی جدید در %s', 'design-orders'), $site_name) . '</h2>';
        
        $message .= '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; font-weight: bold;">' . __('محصول:', 'design-orders') . '</td>';
        $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($order_data['product_name']) . '</td></tr>';
        
        $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; font-weight: bold;">' . __('ایمیل مشتری:', 'design-orders') . '</td>';
        $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($order_data['customer_email']) . '</td></tr>';
        
        $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; font-weight: bold;">' . __('جزئیات سفارش:', 'design-orders') . '</td>';
        $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . nl2br(esc_html($order_data['design_details'])) . '</td></tr>';
        
        $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; font-weight: bold;">' . __('وضعیت:', 'design-orders') . '</td>';
        $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . __('در انتظار پرداخت', 'design-orders') . '</td></tr>';
        $message .= '</table>';
        
        $message .= '<p style="text-align: center; margin: 20px 0;">';
        $message .= '<a href="' . $admin_url . '" style="background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">' . __('مشاهده سفارشات', 'design-orders') . '</a>';
        $message .= '</p>';
        
        $message .= '<p style="color: #666; font-size: 12px; text-align: center;">' . sprintf(__('این ایمیل از %s ارسال شده است.', 'design-orders'), $site_name) . '</p>';
        $message .= '</div></body></html>';
        
        return $message;
    }
    
    /**
     * Get processing order email template
     */
    private function get_processing_order_email_template($order_data) {
        $site_name = get_bloginfo('name');
        $admin_url = admin_url('admin.php?page=design-orders');
        
        $message = '<html><body dir="rtl" style="font-family: Tahoma, Arial, sans-serif;">';
        $message .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd;">';
        $message .= '<h2 style="color: #333; text-align: center;">' . __('سفارش طراحی پرداخت شد', 'design-orders') . '</h2>';
        
        $message .= '<p>' . __('سفارش طراحی زیر پرداخت شده و آماده اجرا می‌باشد:', 'design-orders') . '</p>';
        
        $message .= '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; font-weight: bold;">' . __('شناسه سفارش:', 'design-orders') . '</td>';
        $message .= '<td style="padding: 10px; border: 1px solid #ddd;">#' . $order_data['id'] . '</td></tr>';
        
        $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; font-weight: bold;">' . __('محصول:', 'design-orders') . '</td>';
        $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($order_data['product_name']) . '</td></tr>';
        
        $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; font-weight: bold;">' . __('ایمیل مشتری:', 'design-orders') . '</td>';
        $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($order_data['customer_email']) . '</td></tr>';
        
        $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; font-weight: bold;">' . __('جزئیات سفارش:', 'design-orders') . '</td>';
        $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . nl2br(esc_html($order_data['design_details'])) . '</td></tr>';
        $message .= '</table>';
        
        $message .= '<p style="text-align: center; margin: 20px 0;">';
        $message .= '<a href="' . $admin_url . '" style="background: #00a32a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">' . __('شروع اجرای پروژه', 'design-orders') . '</a>';
        $message .= '</p>';
        
        $message .= '<p style="color: #666; font-size: 12px; text-align: center;">' . sprintf(__('این ایمیل از %s ارسال شده است.', 'design-orders'), $site_name) . '</p>';
        $message .= '</div></body></html>';
        
        return $message;
    }
    
    /**
     * Get completion email template
     */
    private function get_completion_email_template($order_data) {
        $site_name = get_bloginfo('name');
        
        $message = '<html><body dir="rtl" style="font-family: Tahoma, Arial, sans-serif;">';
        $message .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd;">';
        $message .= '<h2 style="color: #333; text-align: center;">' . __('سفارش طراحی شما تکمیل شد', 'design-orders') . '</h2>';
        
        $message .= '<p>' . sprintf(__('با سلام، سفارش طراحی شما برای محصول "%s" با موفقیت تکمیل شد.', 'design-orders'), $order_data['product_name']) . '</p>';
        
        $message .= '<p>' . __('جهت دریافت فایل‌های نهایی با ما تماس بگیرید.', 'design-orders') . '</p>';
        
        $message .= '<p style="color: #666; font-size: 12px; text-align: center;">' . sprintf(__('با تشکر، تیم %s', 'design-orders'), $site_name) . '</p>';
        $message .= '</div></body></html>';
        
        return $message;
    }
}