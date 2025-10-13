<?php
/**
 * 统一管理中心 - 整合所有功能的引导式界面
 */

if (!defined('ABSPATH')) {
    exit;
}

// 获取当前页面和标签
$current_page = isset($_GET['page']) ? $_GET['page'] : 'wc-ai-agent';
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// 检查设置完成状态
$setup_completed = get_option('wai_setup_completed', false);
$revenue_goal = get_option('wai_revenue_goal', 10000);

// 获取商店数据
$data_collector = WC_AI_Agent_Data_Collector::get_instance();
$store_data = $data_collector->collect_product_data();
$current_revenue = $store_data['total_revenue'];
$progress_percent = $revenue_goal > 0 ? min(100, ($current_revenue / $revenue_goal) * 100) : 0;
?>

<div class="wrap wai-unified-dashboard">
    <!-- 顶部引导式进度条 -->
    <div class="wai-master-progress">
        <div class="progress-steps">
            <div class="progress-step <?php echo $setup_completed ? 'completed' : 'active'; ?>" data-step="setup">
                <div class="step-icon">⚙️</div>
                <div class="step-label">初始设置</div>
            </div>
            <div class="progress-step <?php echo $setup_completed ? 'active' : ''; ?>" data-step="configuration">
                <div class="step-icon">🔧</div>
                <div class="step-label">系统配置</div>
            </div>
            <div class="progress-step" data-step="optimization">
                <div class="step-icon">🚀</div>
                <div class="step-label">收入优化</div>
            </div>
            <div class="progress-step" data-step="advanced">
                <div class="step-icon">🌟</div>
                <div class="step-label">高级功能</div>
            </div>
        </div>
        <div class="progress-connector">
            <div class="progress-fill" style="width: <?php echo $setup_completed ? '75%' : '25%'; ?>"></div>
        </div>
    </div>

    <!-- 主内容区域 -->
    <div class="wai-main-content">
        <!-- 左侧导航 -->
        <div class="wai-sidebar-nav">
            <div class="nav-section">
                <h3>📊 核心功能</h3>
                <ul class="nav-menu">
                    <li class="<?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=dashboard'); ?>">
                            <span class="nav-icon">📈</span>
                            <span class="nav-label">收入仪表板</span>
                        </a>
                    </li>
                    <li class="<?php echo $current_tab === 'setup-config' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=setup-config'); ?>">
                            <span class="nav-icon">⚙️</span>
                            <span class="nav-label">安装与配置</span>
                        </a>
                    </li>
                    <li class="<?php echo $current_tab === 'web3-metaverse' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=web3-metaverse'); ?>">
                            <span class="nav-icon">🌐</span>
                            <span class="nav-label">Web3 & 元宇宙</span>
                        </a>
                    </li>
                    <li class="<?php echo $current_tab === 'ai-automation' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=ai-automation'); ?>">
                            <span class="nav-icon">🤖</span>
                            <span class="nav-label">AI与自动化</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <h3>🔧 系统管理</h3>
                <ul class="nav-menu">
                    <li class="<?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=settings'); ?>">
                            <span class="nav-icon">⚡</span>
                            <span class="nav-label">系统设置</span>
                        </a>
                    </li>
                    <li class="<?php echo $current_tab === 'optimization' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=optimization'); ?>">
                            <span class="nav-icon">💡</span>
                            <span class="nav-label">优化中心</span>
                        </a>
                    </li>
                    <li class="<?php echo $current_tab === 'logs' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wc-ai-agent&tab=logs'); ?>">
                            <span class="nav-icon">📋</span>
                            <span class="nav-label">系统日志</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 收入目标卡片 -->
            <div class="revenue-goal-card">
                <div class="goal-header">
                    <h4>🎯 收入目标</h4>
                    <span class="goal-percent"><?php echo round($progress_percent); ?>%</span>
                </div>
                <div class="goal-progress-mini">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%"></div>
                    </div>
                </div>
                <div class="goal-stats">
                    <span class="current">¥<?php echo number_format($current_revenue); ?></span>
                    <span class="target">/ ¥<?php echo number_format($revenue_goal); ?></span>
                </div>
                <button class="button button-small" onclick="setRevenueGoal()">调整目标</button>
            </div>
        </div>

        <!-- 右侧内容区域 -->
        <div class="wai-content-area">
            <!-- 页面内标签导航 -->
            <div class="wai-tab-navigation">
                <?php
                // 根据当前页面显示对应的标签导航
                switch ($current_tab) {
                    case 'setup-config':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/tabs/setup-config-tabs.php';
                        break;
                    case 'web3-metaverse':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/tabs/web3-metaverse-tabs.php';
                        break;
                    case 'ai-automation':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/tabs/ai-automation-tabs.php';
                        break;
                    case 'settings':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/tabs/settings-tabs.php';
                        break;
                    case 'optimization':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/tabs/optimization-tabs.php';
                        break;
                    default:
                        // 仪表板不需要额外的标签
                        break;
                }
                ?>
            </div>

            <!-- 动态内容区域 -->
            <div class="wai-tab-content">
                <?php
                // 加载对应的内容模板
                switch ($current_tab) {
                    case 'dashboard':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/dashboard-content.php';
                        break;
                    case 'setup-config':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/setup-config-content.php';
                        break;
                    case 'web3-metaverse':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/web3-metaverse-content.php';
                        break;
                    case 'ai-automation':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/ai-automation-content.php';
                        break;
                    case 'settings':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/settings-content.php';
                        break;
                    case 'optimization':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/optimization-content.php';
                        break;
                    case 'logs':
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/logs-content.php';
                        break;
                    default:
                        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/dashboard-content.php';
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- 收入目标设置模态框 -->
<div id="revenue-goal-modal" class="wai-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🎯 设置收入目标</h3>
        </div>
        <div class="modal-body">
            <p>设定一个明确的月收入目标，帮助我们为您提供更精准的优化建议：</p>
            <div class="goal-input">
                <label>月收入目标：</label>
                <div class="input-group">
                    <span class="currency">¥</span>
                    <input type="number" id="revenue-goal-input" value="<?php echo $revenue_goal; ?>" min="1000" step="1000">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="button button-primary" onclick="saveRevenueGoal()">保存目标</button>
            <button class="button" onclick="closeGoalModal()">取消</button>
        </div>
    </div>
</div>

<script>
// 收入目标管理
function setRevenueGoal() {
    document.getElementById('revenue-goal-modal').style.display = 'block';
}

function saveRevenueGoal() {
    const goal = document.getElementById('revenue-goal-input').value;
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=wai_save_revenue_goal&goal=' + goal + '&nonce=<?php echo wp_create_nonce("wai_ajax_nonce"); ?>'
    }).then(() => {
        location.reload();
    });
    
    closeGoalModal();
}

function closeGoalModal() {
    document.getElementById('revenue-goal-modal').style.display = 'none';
}

// 进度步骤点击事件
document.querySelectorAll('.progress-step').forEach(step => {
    step.addEventListener('click', function() {
        const stepName = this.getAttribute('data-step');
        switch(stepName) {
            case 'setup':
                window.location.href = '<?php echo admin_url('admin.php?page=wc-ai-agent&tab=setup-config'); ?>';
                break;
            case 'configuration':
                window.location.href = '<?php echo admin_url('admin.php?page=wc-ai-agent&tab=settings'); ?>';
                break;
            case 'optimization':
                window.location.href = '<?php echo admin_url('admin.php?page=wc-ai-agent&tab=optimization'); ?>';
                break;
            case 'advanced':
                window.location.href = '<?php echo admin_url('admin.php?page=wc-ai-agent&tab=ai-automation'); ?>';
                break;
        }
    });
});

// 页面加载后检查设置状态
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$setup_completed && $current_tab !== 'setup-config'): ?>
        setTimeout(() => {
            if (confirm('🎯 欢迎使用AI电商智能体！是否要完成初始设置？')) {
                window.location.href = '<?php echo admin_url('admin.php?page=wc-ai-agent&tab=setup-config'); ?>';
            }
        }, 2000);
    <?php endif; ?>
});
</script>

<style>
/* 统一管理中心样式 */
.wai-unified-dashboard {
    max-width: 1400px;
    margin: 0 auto;
}

/* 主进度条样式 */
.wai-master-progress {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    position: relative;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 2;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    z-index: 3;
}

.progress-step.active .step-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: scale(1.1);
}

.progress-step.completed .step-icon {
    background: #4CD964;
    color: white;
}

.step-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: #f0f0f0;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.step-label {
    font-weight: 600;
    color: #2c3338;
    font-size: 14px;
}

.progress-connector {
    position: absolute;
    top: 55px;
    left: 10%;
    right: 10%;
    height: 4px;
    background: #f0f0f0;
    border-radius: 2px;
    z-index: 1;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 2px;
    transition: width 0.5s ease;
}

/* 主布局 */
.wai-main-content {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
}

/* 侧边栏导航 */
.wai-sidebar-nav {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.nav-section {
    margin-bottom: 30px;
}

.nav-section h3 {
    margin: 0 0 15px 0;
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.nav-menu {
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-menu li {
    margin-bottom: 5px;
}

.nav-menu li a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    text-decoration: none;
    color: #2c3338;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-menu li.active a,
.nav-menu li a:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.nav-icon {
    margin-right: 10px;
    font-size: 16px;
}

.nav-label {
    font-weight: 500;
    font-size: 14px;
}

/* 收入目标卡片 */
.revenue-goal-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}

.goal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.goal-header h4 {
    margin: 0;
    font-size: 14px;
}

.goal-percent {
    background: rgba(255,255,255,0.2);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.goal-progress-mini {
    margin-bottom: 10px;
}

.goal-progress-mini .progress-bar {
    background: rgba(255,255,255,0.2);
    height: 6px;
    border-radius: 3px;
    overflow: hidden;
}

.goal-progress-mini .progress-fill {
    background: #4CD964;
    height: 100%;
    border-radius: 3px;
}

.goal-stats {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    margin-bottom: 10px;
    opacity: 0.9;
}

.revenue-goal-card .button {
    width: 100%;
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
    font-size: 12px;
    padding: 6px 12px;
}

/* 内容区域 */
.wai-content-area {
    min-height: 600px;
}

.wai-tab-navigation {
    background: white;
    border-radius: 12px 12px 0 0;
    padding: 20px 25px 0 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.wai-tab-content {
    background: white;
    border-radius: 0 0 12px 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* 响应式设计 */
@media (max-width: 1024px) {
    .wai-main-content {
        grid-template-columns: 1fr;
    }
    
    .wai-sidebar-nav {
        position: static;
        margin-bottom: 20px;
    }
    
    .progress-steps {
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    
    .progress-step {
        flex: 0 0 calc(50% - 20px);
    }
    
    .progress-connector {
        display: none;
    }
}

@media (max-width: 768px) {
    .wai-master-progress {
        padding: 20px;
    }
    
    .step-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .wai-tab-navigation,
    .wai-tab-content {
        padding: 15px;
    }
}
</style>