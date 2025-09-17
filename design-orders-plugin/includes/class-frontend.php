<?php
/**
 * Frontend Class for Design Orders
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Design_Orders_Frontend {
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->database = new Design_Orders_Database();
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('woocommerce_widget_shopping_cart_buttons', array($this, 'add_design_order_checkboxes'), 5);
        add_action('wp_ajax_submit_design_order', array($this, 'ajax_submit_design_order'));
        add_action('wp_ajax_nopriv_submit_design_order', array($this, 'ajax_submit_design_order'));
        add_action('woocommerce_order_status_completed', array($this, 'update_design_order_status_on_payment'));
        add_action('wp_footer', array($this, 'add_design_order_popup'));
        
        // Replace mini cart with checkout button
        add_filter('woocommerce_widget_shopping_cart_buttons', array($this, 'replace_mini_cart_buttons'), 10, 1);
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        if (get_option('design_orders_enable_design_orders') === 'yes') {
            wp_enqueue_style('design-orders-frontend', DESIGN_ORDERS_PLUGIN_URL . 'assets/css/frontend.css', array(), DESIGN_ORDERS_VERSION);
            wp_enqueue_script('design-orders-frontend', DESIGN_ORDERS_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), DESIGN_ORDERS_VERSION, true);
            
            wp_localize_script('design-orders-frontend', 'design_orders_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('design_orders_nonce'),
                'loading' => __('در حال ارسال...', 'design-orders'),
                'success' => __('سفارش طراحی با موفقیت ثبت شد', 'design-orders'),
                'error' => __('خطا در ثبت سفارش', 'design-orders'),
                'required_fields' => __('لطفاً تمام فیلدهای الزامی را پر کنید', 'design-orders'),
                'invalid_email' => __('ایمیل وارد شده معتبر نمی‌باشد', 'design-orders')
            ));
        }
    }
    
    /**
     * Add design order checkboxes to mini cart
     */
    public function add_design_order_checkboxes() {
        if (get_option('design_orders_enable_design_orders') !== 'yes') {
            return;
        }
        
        $cart_items = WC()->cart->get_cart();
        
        if (empty($cart_items)) {
            return;
        }
        
        echo '<div class="design-orders-section">';
        echo '<h4>' . __('سفارش طراحی', 'design-orders') . '</h4>';
        
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $product_id = $cart_item['product_id'];
            $product_name = $product->get_name();
            
            echo '<div class="design-order-item">';
            echo '<label>';
            echo '<input type="checkbox" class="design-order-checkbox" data-product-id="' . $product_id . '" data-product-name="' . esc_attr($product_name) . '">';
            echo '<span>' . sprintf(__('سفارش طراحی برای %s', 'design-orders'), $product_name) . '</span>';
            echo '</label>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Replace mini cart buttons
     */
    public function replace_mini_cart_buttons() {
        // Remove default buttons
        remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10);
        remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20);
        
        // Add custom checkout button
        echo '<p class="woocommerce-mini-cart__buttons buttons">';
        echo '<a href="' . esc_url(wc_get_checkout_url()) . '" class="button checkout wc-forward">' . __('ثبت سفارش', 'design-orders') . '</a>';
        echo '</p>';
    }
    
    /**
     * Add design order popup
     */
    public function add_design_order_popup() {
        if (get_option('design_orders_enable_design_orders') !== 'yes') {
            return;
        }
        
        include DESIGN_ORDERS_PLUGIN_DIR . 'templates/popup-form.php';
    }
    
    /**
     * Ajax submit design order
     */
    public function ajax_submit_design_order() {
        check_ajax_referer('design_orders_nonce', 'nonce');
        
        $product_id = intval($_POST['product_id']);
        $product_name = sanitize_text_field($_POST['product_name']);
        $customer_email = sanitize_email($_POST['customer_email']);
        $design_details = sanitize_textarea_field($_POST['design_details']);
        
        // Validate required fields
        if (empty($product_id) || empty($customer_email) || empty($design_details)) {
            wp_send_json_error(__('لطفاً تمام فیلدهای الزامی را پر کنید', 'design-orders'));
        }
        
        // Validate email
        if (!is_email($customer_email)) {
            wp_send_json_error(__('ایمیل وارد شده معتبر نمی‌باشد', 'design-orders'));
        }
        
        // Get product
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(__('محصول یافت نشد', 'design-orders'));
        }
        
        // Insert design order
        $order_data = array(
            'product_id' => $product_id,
            'product_name' => $product_name,
            'customer_email' => $customer_email,
            'design_details' => $design_details,
            'order_status' => 'pending_payment'
        );
        
        $design_order_id = $this->database->insert_order($order_data);
        
        if ($design_order_id) {
            // Create design order product
            $design_product_id = $this->create_design_order_product($product_name);
            
            if ($design_product_id) {
                // Add to cart
                WC()->cart->add_to_cart($design_product_id, 1, 0, array(), array(
                    'design_order_id' => $design_order_id,
                    'original_product_id' => $product_id
                ));
                
                // Send notification email
                $email = new Design_Orders_Email();
                $email->send_new_order_notification($order_data);
                
                wp_send_json_success(array(
                    'message' => __('سفارش طراحی با موفقیت ثبت شد و به سبد خرید اضافه شد', 'design-orders'),
                    'design_order_id' => $design_order_id
                ));
            } else {
                wp_send_json_error(__('خطا در ایجاد محصول سفارش طراحی', 'design-orders'));
            }
        } else {
            wp_send_json_error(__('خطا در ثبت سفارش طراحی', 'design-orders'));
        }
    }
    
    /**
     * Create design order product
     */
    private function create_design_order_product($original_product_name) {
        $design_price = get_option('design_orders_design_order_price', 50000);
        
        $product = new WC_Product_Simple();
        $product->set_name(sprintf(__('سفارش طراحی - %s', 'design-orders'), $original_product_name));
        $product->set_description(__('محصول سفارش طراحی', 'design-orders'));
        $product->set_short_description(__('محصول سفارش طراحی', 'design-orders'));
        $product->set_sku('design-order-' . time() . '-' . rand(1000, 9999));
        $product->set_price($design_price);
        $product->set_regular_price($design_price);
        $product->set_stock_status('instock');
        $product->set_catalog_visibility('hidden');
        $product->set_virtual(true);
        
        // Set as design order product
        $product->update_meta_data('_is_design_order_product', 'yes');
        
        return $product->save();
    }
    
    /**
     * Update design order status when WooCommerce order is completed
     */
    public function update_design_order_status_on_payment($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);
            
            if ($product && $product->get_meta('_is_design_order_product') === 'yes') {
                // Find design order by cart item data or other method
                $design_order_id = $item->get_meta('design_order_id');
                
                if ($design_order_id) {
                    // Update design order status
                    $this->database->update_order($design_order_id, array(
                        'order_status' => 'processing',
                        'wc_order_id' => $order_id
                    ));
                    
                    // Send processing notification
                    $design_order = $this->database->get_order($design_order_id);
                    if ($design_order) {
                        $email = new Design_Orders_Email();
                        $email->send_order_processing_notification($design_order);
                    }
                }
            }
        }
    }
}