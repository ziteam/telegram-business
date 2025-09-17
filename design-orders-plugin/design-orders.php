<?php
/**
 * Plugin Name: سفارشات طراحی (Design Orders)
 * Plugin URI: https://yourwebsite.com
 * Description: پلاگین سفارش طراحی برای ووکامرس با سازگاری قالب Woodmart
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: design-orders
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DESIGN_ORDERS_VERSION', '1.0.0');
define('DESIGN_ORDERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DESIGN_ORDERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DESIGN_ORDERS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Design Orders Class
 */
class DesignOrders {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('design-orders', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Include required files
        $this->includes();
        
        // Initialize classes
        $this->init_classes();
    }
    
    /**
     * Plugin loaded
     */
    public function plugins_loaded() {
        // Check WooCommerce version compatibility
        if ($this->is_woocommerce_active() && version_compare(WC()->version, '5.0', '<')) {
            add_action('admin_notices', array($this, 'woocommerce_version_notice'));
            return;
        }
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once DESIGN_ORDERS_PLUGIN_DIR . 'includes/class-database.php';
        require_once DESIGN_ORDERS_PLUGIN_DIR . 'includes/class-admin-menu.php';
        require_once DESIGN_ORDERS_PLUGIN_DIR . 'includes/class-frontend.php';
        require_once DESIGN_ORDERS_PLUGIN_DIR . 'includes/class-email.php';
    }
    
    /**
     * Initialize classes
     */
    private function init_classes() {
        // Initialize database
        new Design_Orders_Database();
        
        // Initialize admin
        if (is_admin()) {
            new Design_Orders_Admin_Menu();
        }
        
        // Initialize frontend
        if (!is_admin()) {
            new Design_Orders_Frontend();
        }
        
        // Initialize email
        new Design_Orders_Email();
    }
    
    /**
     * Check if WooCommerce is active
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('پلاگین سفارشات طراحی نیاز به فعال بودن پلاگین ووکامرس دارد.', 'design-orders'); ?></p>
        </div>
        <?php
    }
    
    /**
     * WooCommerce version notice
     */
    public function woocommerce_version_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('پلاگین سفارشات طراحی نیاز به ووکامرس نسخه 5.0 یا بالاتر دارد.', 'design-orders'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        if (class_exists('Design_Orders_Database')) {
            $database = new Design_Orders_Database();
            $database->create_tables();
        }
        
        // Set default options
        $default_options = array(
            'design_order_price' => 50000,
            'notification_email' => get_option('admin_email'),
            'enable_design_orders' => 'yes'
        );
        
        foreach ($default_options as $key => $value) {
            if (!get_option('design_orders_' . $key)) {
                add_option('design_orders_' . $key, $value);
            }
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize plugin
function design_orders_init() {
    return DesignOrders::get_instance();
}

// Start the plugin
design_orders_init();