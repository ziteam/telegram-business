<?php
/**
 * Admin Menu Class for Design Orders
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Design_Orders_Admin_Menu {
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->database = new Design_Orders_Database();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_update_design_order_status', array($this, 'ajax_update_order_status'));
        add_action('wp_ajax_delete_design_order', array($this, 'ajax_delete_order'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('سفارشات طراحی', 'design-orders'),
            __('سفارشات طراحی', 'design-orders'),
            'manage_woocommerce',
            'design-orders',
            array($this, 'orders_page'),
            'dashicons-art',
            56
        );
        
        add_submenu_page(
            'design-orders',
            __('مشاهده سفارشات', 'design-orders'),
            __('مشاهده سفارشات', 'design-orders'),
            'manage_woocommerce',
            'design-orders',
            array($this, 'orders_page')
        );
        
        add_submenu_page(
            'design-orders',
            __('تنظیمات', 'design-orders'),
            __('تنظیمات', 'design-orders'),
            'manage_woocommerce',
            'design-orders-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Admin init
     */
    public function admin_init() {
        register_setting('design_orders_settings', 'design_orders_design_order_price');
        register_setting('design_orders_settings', 'design_orders_notification_email');
        register_setting('design_orders_settings', 'design_orders_enable_design_orders');
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'design-orders') !== false) {
            wp_enqueue_style('design-orders-admin', DESIGN_ORDERS_PLUGIN_URL . 'assets/css/admin.css', array(), DESIGN_ORDERS_VERSION);
            wp_enqueue_script('design-orders-admin', DESIGN_ORDERS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), DESIGN_ORDERS_VERSION, true);
            
            wp_localize_script('design-orders-admin', 'design_orders_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('design_orders_nonce'),
                'confirm_delete' => __('آیا از حذف این سفارش اطمینان دارید؟', 'design-orders'),
                'updating' => __('در حال بروزرسانی...', 'design-orders'),
                'updated' => __('بروزرسانی شد', 'design-orders'),
                'error' => __('خطا در بروزرسانی', 'design-orders')
            ));
        }
    }
    
    /**
     * Orders page
     */
    public function orders_page() {
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($current_page - 1) * $per_page;
        
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        
        $args = array(
            'limit' => $per_page,
            'offset' => $offset,
            'status' => $status_filter
        );
        
        $orders = $this->database->get_orders($args);
        $total_orders = $this->database->get_orders_count($status_filter);
        $total_pages = ceil($total_orders / $per_page);
        
        // Status counts
        $pending_count = $this->database->get_orders_count('pending_payment');
        $processing_count = $this->database->get_orders_count('processing');
        $completed_count = $this->database->get_orders_count('completed');
        
        include DESIGN_ORDERS_PLUGIN_DIR . 'templates/admin-orders.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['submit'])) {
            check_admin_referer('design_orders_settings');
            
            update_option('design_orders_design_order_price', floatval($_POST['design_order_price']));
            update_option('design_orders_notification_email', sanitize_email($_POST['notification_email']));
            update_option('design_orders_enable_design_orders', isset($_POST['enable_design_orders']) ? 'yes' : 'no');
            
            echo '<div class="notice notice-success"><p>' . __('تنظیمات ذخیره شد.', 'design-orders') . '</p></div>';
        }
        
        $design_order_price = get_option('design_orders_design_order_price', 50000);
        $notification_email = get_option('design_orders_notification_email', get_option('admin_email'));
        $enable_design_orders = get_option('design_orders_enable_design_orders', 'yes');
        
        include DESIGN_ORDERS_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    /**
     * Ajax update order status
     */
    public function ajax_update_order_status() {
        check_ajax_referer('design_orders_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('شما اجازه انجام این عمل را ندارید.', 'design-orders'));
        }
        
        $order_id = intval($_POST['order_id']);
        $new_status = sanitize_text_field($_POST['status']);
        
        $allowed_statuses = array('pending_payment', 'processing', 'completed', 'cancelled');
        if (!in_array($new_status, $allowed_statuses)) {
            wp_send_json_error(__('وضعیت نامعتبر', 'design-orders'));
        }
        
        $result = $this->database->update_order_status($order_id, $new_status);
        
        if ($result) {
            // Send email notification if status changed to processing
            if ($new_status === 'processing') {
                $order = $this->database->get_order($order_id);
                if ($order) {
                    $email = new Design_Orders_Email();
                    $email->send_order_processing_notification($order);
                }
            }
            
            wp_send_json_success(__('وضعیت سفارش بروزرسانی شد.', 'design-orders'));
        } else {
            wp_send_json_error(__('خطا در بروزرسانی وضعیت سفارش.', 'design-orders'));
        }
    }
    
    /**
     * Ajax delete order
     */
    public function ajax_delete_order() {
        check_ajax_referer('design_orders_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('شما اجازه انجام این عمل را ندارید.', 'design-orders'));
        }
        
        $order_id = intval($_POST['order_id']);
        
        $result = $this->database->delete_order($order_id);
        
        if ($result) {
            wp_send_json_success(__('سفارش حذف شد.', 'design-orders'));
        } else {
            wp_send_json_error(__('خطا در حذف سفارش.', 'design-orders'));
        }
    }
}