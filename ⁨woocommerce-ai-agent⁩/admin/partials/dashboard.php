<?php
/**
 * 统一仪表板模板
 * 
 * 整合了仪表板、设置、日志和安装配置功能
 * 
 * @package WC_AI_Agent
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// 调试：检查当前用户权限
global $current_user;
echo '<!-- 调试信息：当前用户ID: ' . $current_user->ID . ' -->';
echo '<!-- 调试信息：用户权限: ' . (current_user_can('manage_woocommerce') ? '有权限' : '无权限') . ' -->';

// 确保所有变量都有默认值
$current_tab = isset($current_tab) ? $current_tab : 'dashboard';
$setup_completed = isset($setup_completed) ? $setup_completed : false;
$revenue_goal = isset($revenue_goal) ? $revenue_goal : 10000;
$current_revenue = isset($current_revenue) ? $current_revenue : 0;
$progress_percent = isset($progress_percent) ? $progress_percent : 0;
$store_data = isset($store_data) ? $store_data : array(
    'products_count' => 0,
    'total_revenue' => 0,
    'average_order_value' => 0,
    'conversion_rate' => 0
);
$logs = isset($logs) ? $logs : array();
$current_settings = isset($current_settings) ? $current_settings : array();

// 确保所有变量都有默认值
$current_tab = isset($current_tab) ? $current_tab : 'dashboard';
$setup_completed = isset($setup_completed) ? $setup_completed : false;
$revenue_goal = isset($revenue_goal) ? $revenue_goal : 10000;
$current_revenue = isset($current_revenue) ? $current_revenue : 0;
$progress_percent = isset($progress_percent) ? $progress_percent : 0;
$store_data = isset($store_data) ? $store_data : array(
    'products_count' => 0,
    'total_revenue' => 0,
    'average_order_value' => 0,
    'conversion_rate' => 0
);
$logs = isset($logs) ? $logs : array();
$current_settings = isset($current_settings) ? $current_settings : array();
?>

<div class="wrap wai-unified-dashboard">
    <!-- 顶部引导进度条 -->
    <div class="wai-master-progress">
        <div class="progress-steps">
            <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=setup'); ?>" class="progress-step <?php echo $setup_completed ? 'completed' : 'active'; ?>">
                <div class="step-icon">⚙️</div>
                <div class="step-label">安装配置</div>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=dashboard'); ?>" class="progress-step <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                <div class="step-icon">📈</div>
                <div class="step-label">收入优化</div>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=settings'); ?>" class="progress-step <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                <div class="step-icon">🔧</div>
                <div class="step-label">系统设置</div>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=logs'); ?>" class="progress-step <?php echo $current_tab === 'logs' ? 'active' : ''; ?>">
                <div class="step-icon">📋</div>
                <div class="step-label">系统日志</div>
            </a>
        </div>
    </div>

    <!-- 主标签导航 -->
    <div class="wai-main-tabs">
        <nav class="main-tab-nav">
            <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=dashboard'); ?>" class="tab-nav-item <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                📈 收入仪表板
            </a>
            <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=setup'); ?>" class="tab-nav-item <?php echo $current_tab === 'setup' ? 'active' : ''; ?>">
                ⚙️ 安装配置
            </a>
            <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=settings'); ?>" class="tab-nav-item <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                🔧 系统设置
            </a>
            <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=logs'); ?>" class="tab-nav-item <?php echo $current_tab === 'logs' ? 'active' : ''; ?>">
                📋 系统日志
            </a>
        </nav>
    </div>

    <!-- 内容区域 -->
    <div class="wai-tab-content">
        <?php switch ($current_tab): 
            case 'setup': ?>
                <!-- 安装配置内容 -->
                <div class="setup-content">
                    <div class="setup-section">
                        <h2>🎯 收入目标设置</h2>
                        <div class="form-group">
                            <label>月收入目标：</label>
                            <div class="input-group">
                                <span class="currency">¥</span>
                                <input type="number" id="revenue-goal-input" value="<?php echo $revenue_goal; ?>" min="1000" step="1000">
                            </div>
                            <button class="button button-primary" onclick="saveRevenueGoal()">保存目标</button>
                        </div>
                    </div>

                    <div class="setup-section">
                        <h2>🛠️ 功能启用</h2>
                        <div class="features-grid">
                            <div class="feature-toggle">
                                <label>
                                    <input type="checkbox" name="ai_automation" <?php checked($current_settings['ai_automation_enabled'] ?? true); ?>>
                                    <span class="toggle-label">🤖 AI自动化优化</span>
                                </label>
                            </div>
                            <div class="feature-toggle">
                                <label>
                                    <input type="checkbox" name="web3_integration" <?php checked($current_settings['web3_enabled'] ?? false); ?>>
                                    <span class="toggle-label">🌐 Web3支付集成</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="setup-actions">
                        <button class="button button-large button-primary" onclick="completeSetup()">
                            ✅ 完成设置并启用系统
                        </button>
                    </div>
                </div>
            <?php break; 
            
            case 'settings': ?>
                <!-- 系统设置内容 -->
                <div class="settings-content">
                    <form method="post" action="">
                        <?php wp_nonce_field('wai_save_settings', 'wai_settings_nonce'); ?>
                        
                        <div class="settings-section">
                            <h3>🔧 基础设置</h3>
                            <div class="form-group">
                                <label>AI自动化：</label>
                                <label class="switch">
                                    <input type="checkbox" name="ai_automation_enabled" value="1" <?php checked($current_settings['ai_automation_enabled'] ?? true); ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>调试模式：</label>
                                <label class="switch">
                                    <input type="checkbox" name="debug_mode" value="1" <?php checked($current_settings['debug_mode'] ?? false); ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3>🌐 高级功能</h3>
                            <div class="form-group">
                                <label>Web3集成：</label>
                                <label class="switch">
                                    <input type="checkbox" name="web3_enabled" value="1" <?php checked($current_settings['web3_enabled'] ?? false); ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="submit_settings" class="button button-primary">保存设置</button>
                        </div>
                    </form>
                </div>
            <?php break; 
            
            case 'logs': ?>
                <!-- 系统日志内容 -->
                <div class="logs-content">
                    <div class="logs-header">
                        <h2>📋 系统日志</h2>
                        <button class="button" onclick="refreshLogs()">刷新日志</button>
                    </div>
                    
                    <div class="logs-list">
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <div class="log-entry">
                                    <div class="log-time"><?php echo date('Y-m-d H:i:s', strtotime($log->timestamp)); ?></div>
                                    <div class="log-level <?php echo strtolower($log->level); ?>"><?php echo $log->level; ?></div>
                                    <div class="log-message"><?php echo esc_html($log->message); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-logs">暂无日志记录</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php break; 
            
            default: ?>
                <!-- 默认显示收入仪表板 -->
                <div class="wai-guided-dashboard">
                    <div class="wai-revenue-goal-tracker">
                        <div class="goal-header">
                            <h1>🎯 您的电商收入目标</h1>
                            <p>让我们共同实现月收入 <strong>¥<?php echo number_format($revenue_goal); ?></strong> 的目标</p>
                        </div>
                        
                        <div class="goal-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%"></div>
                            </div>
                            <div class="progress-stats">
                                <span class="current">¥<?php echo number_format($current_revenue); ?></span>
                                <span class="goal">目标: ¥<?php echo number_format($revenue_goal); ?></span>
                                <span class="percent"><?php echo round($progress_percent); ?>%</span>
                            </div>
                        </div>
                    </div>

                    <!-- 原dashboard.php的其余内容 -->
                    <div class="wai-quick-start-guide">
                        <div class="guide-header">
                            <h2>🚀 快速提升收入的3个步骤</h2>
                        </div>
                        <div class="steps-grid">
                            <div class="step-card <?php echo $store_data['products_count'] >= 10 ? 'completed' : 'active'; ?>">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h3>优化产品目录</h3>
                                    <p>确保您有足够的产品来吸引客户</p>
                                    <div class="step-metrics">
                                        <span class="metric">当前产品: <?php echo $store_data['products_count']; ?>个</span>
                                    </div>
                                </div>
                            </div>
                            <div class="step-card <?php echo $store_data['conversion_rate'] > 1 ? 'completed' : 'active'; ?>">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h3>获取更多流量</h3>
                                    <p>通过营销吸引更多访客到您的店铺</p>
                                </div>
                            </div>
                            <div class="step-card <?php echo $store_data['average_order_value'] > 100 ? 'completed' : 'active'; ?>">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h3>提升客单价</h3>
                                    <p>让每个客户购买更多商品</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php break; 
        endswitch; ?>
    </div>
</div>

<script>
// 收入目标管理
function saveRevenueGoal() {
    const goal = document.getElementById('revenue-goal-input').value;
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=wai_save_revenue_goal&goal=' + goal  // ← 移除了 &nonce=... 部分
    }).then(() => location.reload());
}

function completeSetup() {
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=wai_save_revenue_goal&goal=<?php echo $revenue_goal; ?>'  // ← 移除了 &nonce=... 部分
    }).then(() => {
        alert('设置完成！系统已启用。');
        window.location.href = '<?php echo admin_url('admin.php?page=wc-ai-agent&tab=dashboard'); ?>';
    });
}

function refreshLogs() {
    location.reload();
}

// 新用户自动显示设置
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$setup_completed && $current_tab !== 'setup'): ?>
        // 直接跳转到设置页面，不显示确认弹窗
        setTimeout(() => {
            window.location.href = '<?php echo admin_url('admin.php?page=wc-ai-agent&tab=setup'); ?>';
        }, 500);
    <?php endif; ?>
});
</script>

<style>
.wai-unified-dashboard { max-width: 1200px; }

/* 主进度条 */
.wai-master-progress { background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.progress-steps { display: flex; justify-content: space-between; }
.progress-step { display: flex; flex-direction: column; align-items: center; text-decoration: none; color: inherit; }
.progress-step.active .step-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transform: scale(1.1); }
.progress-step.completed .step-icon { background: #4CD964; color: white; }
.step-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; background: #f0f0f0; transition: all 0.3s ease; margin-bottom: 8px; }
.step-label { font-weight: 600; color: #2c3338; font-size: 13px; }

/* 主标签导航 */
.wai-main-tabs { background: white; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.main-tab-nav { display: flex; padding: 0 20px; }
.tab-nav-item { padding: 15px 20px; text-decoration: none; color: #666; font-weight: 500; border-bottom: 3px solid transparent; transition: all 0.3s ease; }
.tab-nav-item.active { color: #2271b1; border-bottom-color: #2271b1; }
.tab-nav-item:hover { color: #2271b1; background: #f8f9fa; }

/* 内容区域 */
.wai-tab-content { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

/* 安装配置样式 */
.setup-section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e0e0e0; }
.setup-section h2 { margin: 0 0 15px 0; color: #2c3338; }
.features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
.feature-toggle { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e9ecef; }
.feature-toggle label { display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0; }
.toggle-label { font-weight: 500; color: #2c3338; }
.setup-actions { text-align: center; margin-top: 30px; }

/* 系统设置样式 */
.settings-section { margin-bottom: 30px; }
.settings-section h3 { margin: 0 0 15px 0; color: #2c3338; border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; }
.form-group { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; padding: 10px 0; }
.form-group label:first-child { font-weight: 500; color: #2c3338; }

/* 开关样式 */
.switch { position: relative; display: inline-block; width: 50px; height: 24px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
.slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #4CD964; }
input:checked + .slider:before { transform: translateX(26px); }
.form-actions { text-align: center; margin-top: 30px; }

/* 收入目标追踪器 */
.wai-revenue-goal-tracker { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
.goal-header h1 { margin: 0 0 10px 0; font-size: 28px; }
.goal-header p { margin: 0; opacity: 0.9; font-size: 16px; }
.goal-progress { margin-top: 25px; }
.progress-bar { background: rgba(255,255,255,0.2); height: 12px; border-radius: 6px; overflow: hidden; margin-bottom: 15px; }
.progress-fill { background: #4CD964; height: 100%; border-radius: 6px; transition: width 0.5s ease; }
.progress-stats { display: flex; justify-content: space-between; align-items: center; font-weight: 600; }
.progress-stats .current { font-size: 24px; }
.progress-stats .goal { opacity: 0.8; }
.progress-stats .percent { background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px; font-size: 14px; }

/* 快速启动指南 */
.wai-quick-start-guide { background: white; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.guide-header h2 { margin: 0 0 10px 0; color: #2c3338; }
.steps-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
.step-card { background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 10px; padding: 25px; position: relative; transition: all 0.3s ease; }
.step-card.active { border-color: #667eea; background: white; box-shadow: 0 5px 15px rgba(102,126,234,0.1); }
.step-card.completed { border-color: #4CD964; background: #f0f9f0; }
.step-number { position: absolute; top: -15px; left: 20px; background: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
.step-card.completed .step-number { background: #4CD964; }
.step-content h3 { margin: 0 0 10px 0; color: #2c3338; font-size: 18px; }
.step-content p { margin: 0 0 15px 0; color: #666; font-size: 14px; }
.step-metrics { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 13px; }
.step-metrics .metric { color: #2c3338; font-weight: 500; }

/* 响应式设计 */
@media (max-width: 768px) {
    .progress-steps { flex-wrap: wrap; gap: 15px; }
    .progress-step { flex: 0 0 calc(50% - 15px); }
    .main-tab-nav { flex-direction: column; }
    .tab-nav-item { border-bottom: 1px solid #e0e0e0; border-left: 3px solid transparent; }
    .tab-nav-item.active { border-left-color: #2271b1; border-bottom-color: #e0e0e0; }
    .form-group { flex-direction: column; align-items: flex-start; gap: 10px; }
    .steps-grid { grid-template-columns: 1fr; }
}
</style>