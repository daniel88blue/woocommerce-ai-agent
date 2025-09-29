<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>决策日志</h1>
    
    <div class="tablenav top">
        <div class="alignleft actions">
            <button type="button" id="refresh-logs" class="button">刷新日志</button>
        </div>
        <br class="clear">
    </div>
    
    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
            <tr>
                <th>时间</th>
                <th>操作类型</th>
                <th>产品</th>
                <th>原值</th>
                <th>新值</th>
                <th>原因</th>
                <th>状态</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($log->timestamp)); ?></td>
                        <td><?php echo $this->get_action_label($log->action_type); ?></td>
                        <td>
                            <?php 
                            $product = wc_get_product($log->product_id);
                            if ($product) {
                                echo '<a href="' . get_edit_post_link($log->product_id) . '">' . $product->get_name() . '</a>';
                            } else {
                                echo '产品#' . $log->product_id;
                            }
                            ?>
                        </td>
                        <td><?php echo $this->format_log_value($log->old_value, $log->action_type); ?></td>
                        <td><?php echo $this->format_log_value($log->new_value, $log->action_type); ?></td>
                        <td><?php echo esc_html($log->reason); ?></td>
                        <td>
                            <?php if ($log->success): ?>
                                <span class="dashicons dashicons-yes" style="color: #46b450;" title="成功"></span>
                            <?php else: ?>
                                <span class="dashicons dashicons-no" style="color: #dc3232;" title="失败"></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">暂无日志记录</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    $('#refresh-logs').on('click', function() {
        var $button = $(this);
        $button.prop('disabled', true).text('刷新中...');
        
        $.ajax({
            url: wc_ai_agent_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wc_ai_agent_get_logs',
                nonce: wc_ai_agent_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            complete: function() {
                $button.prop('disabled', false).text('刷新日志');
            }
        });
    });
});
</script>
