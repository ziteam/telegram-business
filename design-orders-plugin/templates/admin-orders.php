<?php
/**
 * Admin Orders Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$status_labels = array(
    'pending_payment' => __('در انتظار پرداخت', 'design-orders'),
    'processing' => __('در انتظار اجرا', 'design-orders'),
    'completed' => __('تکمیل شده', 'design-orders'),
    'cancelled' => __('لغو شده', 'design-orders')
);

$status_colors = array(
    'pending_payment' => '#e8ad00',
    'processing' => '#0073aa',
    'completed' => '#00a32a',
    'cancelled' => '#d63638'
);
?>

<div class="wrap">
    <h1><?php _e('مدیریت سفارشات طراحی', 'design-orders'); ?></h1>
    
    <!-- Status Filter -->
    <ul class="subsubsub">
        <li>
            <a href="<?php echo admin_url('admin.php?page=design-orders'); ?>" <?php echo empty($status_filter) ? 'class="current"' : ''; ?>>
                <?php _e('همه', 'design-orders'); ?> <span class="count">(<?php echo $total_orders; ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=design-orders&status=pending_payment'); ?>" <?php echo $status_filter === 'pending_payment' ? 'class="current"' : ''; ?>>
                <?php _e('در انتظار پرداخت', 'design-orders'); ?> <span class="count">(<?php echo $pending_count; ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=design-orders&status=processing'); ?>" <?php echo $status_filter === 'processing' ? 'class="current"' : ''; ?>>
                <?php _e('در انتظار اجرا', 'design-orders'); ?> <span class="count">(<?php echo $processing_count; ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=design-orders&status=completed'); ?>" <?php echo $status_filter === 'completed' ? 'class="current"' : ''; ?>>
                <?php _e('تکمیل شده', 'design-orders'); ?> <span class="count">(<?php echo $completed_count; ?>)</span>
            </a>
        </li>
    </ul>
    
    <div class="clear"></div>
    
    <?php if (empty($orders)): ?>
        <div class="notice notice-info">
            <p><?php _e('هیچ سفارش طراحی یافت نشد.', 'design-orders'); ?></p>
        </div>
    <?php else: ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-id"><?php _e('شناسه', 'design-orders'); ?></th>
                    <th scope="col" class="manage-column column-product"><?php _e('محصول', 'design-orders'); ?></th>
                    <th scope="col" class="manage-column column-customer"><?php _e('مشتری', 'design-orders'); ?></th>
                    <th scope="col" class="manage-column column-details"><?php _e('جزئیات', 'design-orders'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e('وضعیت', 'design-orders'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php _e('تاریخ ثبت', 'design-orders'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('عملیات', 'design-orders'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr id="order-<?php echo $order['id']; ?>">
                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                        <td>
                            <strong><?php echo esc_html($order['product_name']); ?></strong>
                            <?php if ($order['wc_order_id']): ?>
                                <br><small>
                                    <a href="<?php echo admin_url('post.php?post=' . $order['wc_order_id'] . '&action=edit'); ?>" target="_blank">
                                        <?php echo sprintf(__('سفارش ووکامرس #%d', 'design-orders'), $order['wc_order_id']); ?>
                                    </a>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="mailto:<?php echo esc_attr($order['customer_email']); ?>">
                                <?php echo esc_html($order['customer_email']); ?>
                            </a>
                        </td>
                        <td>
                            <div class="design-details" style="max-width: 200px; max-height: 100px; overflow: hidden;">
                                <?php echo nl2br(esc_html(wp_trim_words($order['design_details'], 20))); ?>
                                <?php if (strlen($order['design_details']) > 100): ?>
                                    <br><button type="button" class="button-link show-full-details" data-order-id="<?php echo $order['id']; ?>">
                                        <?php _e('نمایش کامل', 'design-orders'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Hidden full details -->
                            <div id="full-details-<?php echo $order['id']; ?>" class="full-details" style="display: none;">
                                <?php echo nl2br(esc_html($order['design_details'])); ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge" style="background-color: <?php echo $status_colors[$order['order_status']]; ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">
                                <?php echo $status_labels[$order['order_status']]; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order['date_created'])); ?>
                        </td>
                        <td>
                            <select class="status-select" data-order-id="<?php echo $order['id']; ?>">
                                <?php foreach ($status_labels as $status => $label): ?>
                                    <option value="<?php echo $status; ?>" <?php selected($order['order_status'], $status); ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <br><br>
                            
                            <button type="button" class="button delete-order" data-order-id="<?php echo $order['id']; ?>">
                                <?php _e('حذف', 'design-orders'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $pagination_args = array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo; قبلی', 'design-orders'),
                        'next_text' => __('بعدی &raquo;', 'design-orders'),
                        'total' => $total_pages,
                        'current' => $current_page,
                        'show_all' => false,
                        'end_size' => 1,
                        'mid_size' => 2,
                        'type' => 'list'
                    );
                    
                    if (!empty($status_filter)) {
                        $pagination_args['base'] = add_query_arg(array('status' => $status_filter, 'paged' => '%#%'));
                    }
                    
                    echo paginate_links($pagination_args);
                    ?>
                </div>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<!-- Full Details Modal -->
<div id="design-details-modal" class="design-details-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('جزئیات کامل سفارش طراحی', 'design-orders'); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modal-details-content"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="button modal-close"><?php _e('بستن', 'design-orders'); ?></button>
        </div>
    </div>
</div>