<?php
/**
 * Database Class for Design Orders
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Design_Orders_Database {
    
    /**
     * Table name
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'design_orders';
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            product_id bigint(20) unsigned NOT NULL,
            product_name varchar(255) NOT NULL,
            customer_email varchar(100) NOT NULL,
            design_details text NOT NULL,
            order_status varchar(50) DEFAULT 'pending_payment',
            wc_order_id bigint(20) unsigned DEFAULT NULL,
            date_created datetime DEFAULT CURRENT_TIMESTAMP,
            date_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY customer_email (customer_email),
            KEY order_status (order_status),
            KEY wc_order_id (wc_order_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Insert new design order
     */
    public function insert_order($data) {
        global $wpdb;
        
        $defaults = array(
            'product_id' => 0,
            'product_name' => '',
            'customer_email' => '',
            'design_details' => '',
            'order_status' => 'pending_payment',
            'wc_order_id' => null,
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'product_id' => $data['product_id'],
                'product_name' => $data['product_name'],
                'customer_email' => $data['customer_email'],
                'design_details' => $data['design_details'],
                'order_status' => $data['order_status'],
                'wc_order_id' => $data['wc_order_id'],
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d'
            )
        );
        
        if ($result !== false) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update design order
     */
    public function update_order($id, $data) {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get design order by ID
     */
    public function get_order($id) {
        global $wpdb;
        
        $query = $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id);
        return $wpdb->get_row($query, ARRAY_A);
    }
    
    /**
     * Get design orders
     */
    public function get_orders($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => '',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'date_created',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = '';
        if (!empty($args['status'])) {
            $where = $wpdb->prepare(" WHERE order_status = %s", $args['status']);
        }
        
        $query = "SELECT * FROM {$this->table_name}{$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT {$args['offset']}, {$args['limit']}";
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Get orders count
     */
    public function get_orders_count($status = '') {
        global $wpdb;
        
        $where = '';
        if (!empty($status)) {
            $where = $wpdb->prepare(" WHERE order_status = %s", $status);
        }
        
        $query = "SELECT COUNT(*) FROM {$this->table_name}{$where}";
        return $wpdb->get_var($query);
    }
    
    /**
     * Delete design order
     */
    public function delete_order($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Update order status
     */
    public function update_order_status($id, $status) {
        return $this->update_order($id, array('order_status' => $status));
    }
    
    /**
     * Get order by WooCommerce order ID
     */
    public function get_order_by_wc_id($wc_order_id) {
        global $wpdb;
        
        $query = $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE wc_order_id = %d", $wc_order_id);
        return $wpdb->get_row($query, ARRAY_A);
    }
    
    /**
     * Get orders by email
     */
    public function get_orders_by_email($email) {
        global $wpdb;
        
        $query = $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE customer_email = %s ORDER BY date_created DESC", $email);
        return $wpdb->get_results($query, ARRAY_A);
    }
}