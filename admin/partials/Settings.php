<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>AI 电商智能体设置</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wc_ai_agent_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">功能模块</th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text">功能模块</legend>
                        <label>
                            <input type="checkbox" name="pricing_optimization" value="1" <?php checked($current_settings['pricing_optimization'], '1'); ?>>
                            定价优化
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="inventory_management" value="1" <?php checked($current_settings['inventory_management'], '1'); ?>>
                            库存管理
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="auto_discount" value="1" <?php checked($current_settings['auto_discount'], '1'); ?>>
                            自动折扣
                        </label>
                    </fieldset>
                </td>
            </tr>
            
            <tr>
                <th scope="row">分析频率</th>
                <td>
                    <select name="analysis_frequency">
                        <option value="daily" <?php selected($current_settings['analysis_frequency'], 'daily'); ?>>每日</option>
                        <option value="weekly" <?php selected($current_settings['analysis_frequency'], 'weekly'); ?>>每周</option>
                        <option value="monthly" <?php selected($current_settings['analysis_frequency'], 'monthly'); ?>>每月</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">最大折扣率</th>
                <td>
                    <input type="number" name="max_discount_rate" value="<?php echo esc_attr($current_settings['max_discount_rate']); ?>" min="5" max="50" step="5"> %
                    <p class="description">自动折扣功能的最大折扣百分比</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">最低利润率</th>
                <td>
                    <input type="number" name="min_profit_margin" value="<?php echo esc_attr($current_settings['min_profit_margin']); ?>" min="5" max="50" step="5"> %
                    <p class="description">定价调整时的最低利润率要求</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">自动执行</th>
                <td>
                    <label>
                        <input type="checkbox" name="auto_execute" value="1" <?php checked($current_settings['auto_execute'], '1'); ?>>
                        自动执行优化决策
                    </label>
                    <p class="description">启用后，系统将自动执行分析出的优化决策</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">邮件通知</th>
                <td>
                    <label>
                        <input type="checkbox" name="email_notifications" value="1" <?php checked($current_settings['email_notifications'], '1'); ?>>
                        发送分析报告邮件
                    </label>
                    <p class="description">每次分析完成后发送报告到管理员邮箱</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('保存设置', 'primary', 'submit_settings'); ?>
    </form>
</div>
