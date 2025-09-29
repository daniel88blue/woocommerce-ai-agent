<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wc-ai-agent-dashboard">
    <h1>AI 电商智能体仪表板</h1>
    
    <div class="wc-ai-agent-stats">
        <div class="stat-card">
            <h3>总产品数</h3>
            <p class="stat-number"><?php echo count($store_data['products']); ?></p>
        </div>
        <div class="stat-card">
            <h3>总销售额</h3>
            <p class="stat-number">¥<?php echo number_format($store_data['store_metrics']['total_revenue'], 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>平均订单值</h3>
            <p class="stat-number">¥<?php echo number_format($store_data['store_metrics']['average_order_value'], 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>转化率</h3>
            <p class="stat-number"><?php echo $store_data['store_metrics']['conversion_rate']; ?>%</p>
        </div>
    </div>
    
    <div class="wc-ai-agent-actions">
        <button id="run-analysis" class="button button-primary button-large">
            立即运行分析
        </button>
        <span id="analysis-status" style="margin-left: 15px;"></span>
    </div>
    
    <div id="analysis-results" class="wc-ai-agent-results" style="display: none;">
        <h3>分析结果</h3>
        <div id="results-container"></div>
    </div>
    
    <div class="wc-ai-agent-recent-logs">
        <h3>最近决策</h3>
        <?php
        $logger = WC_AI_Agent_Logger::get_instance();
        $recent_logs = $logger->get_recent_logs(10);
        ?>
        
        <?php if (!empty($recent_logs)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>时间</th>
                        <th>操作</th>
                        <th>产品</th>
                        <th>原因</th>
                        <th>状态</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_logs as $log): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i', strtotime($log->timestamp)); ?></td>
                            <td><?php echo $this->get_action_label($log->action_type); ?></td>
                            <td>
                                <?php 
                                $product = wc_get_product($log->product_id);
                                echo $product ? $product->get_name() : '产品#' . $log->product_id;
                                ?>
                            </td>
                            <td><?php echo wp_trim_words($log->reason, 10); ?></td>
                            <td>
                                <span class="dashicons <?php echo $log->success ? 'dashicons-yes success' : 'dashicons-no error'; ?>"></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>暂无决策记录。</p>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#run-analysis').on('click', function() {
        var $button = $(this);
        var $status = $('#analysis-status');
        
        $button.prop('disabled', true);
        $status.html('<span class="spinner is-active"></span> 分析中...');
        
        $.ajax({
            url: wc_ai_agent_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wc_ai_agent_run_analysis',
                nonce: wc_ai_agent_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<span style="color: green;">✓ 分析完成</span>');
                    displayResults(response.data);
                } else {
                    $status.html('<span style="color: red;">✗ 分析失败</span>');
                }
                $button.prop('disabled', false);
            },
            error: function() {
                $status.html('<span style="color: red;">✗ 请求失败</span>');
                $button.prop('disabled', false);
            }
        });
    });
    
    function displayResults(data) {
        var $container = $('#results-container');
        var $results = $('#analysis-results');
        
        $container.empty();
        
        if (data.decisions && data.decisions.length > 0) {
            $.each(data.decisions, function(index, productDecision) {
                var productHtml = '<div class="product-decision">';
                productHtml += '<h4>' + productDecision.product_name + '</h4>';
                productHtml += '<ul>';
                
                $.each(productDecision.decisions, function(i, decision) {
                    productHtml += '<li>' + decision.reason + '</li>';
                });
                
                productHtml += '</ul></div>';
                $container.append(productHtml);
            });
        } else {
            $container.html('<p>本次分析未发现需要优化的产品。</p>');
        }
        
        $results.show();
    }
});
</script>
