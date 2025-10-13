<?php
/**
 * AI策略价值管理中枢 - 引导式策略发现与进化控制台
 * 文件路径: /wp-content/plugins/woocommerce-ai-agent/includes/admin/ai-advanced-dashboard.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// 初始化核心引擎
$strategy_engine = Woocommerce_AI_Agent::get_instance()->managers['ai_strategy_engine'] ?? null;
$swarm_controller = Woocommerce_AI_Agent::get_instance()->managers['swarm_intelligence'] ?? null;
$code_optimizer = Woocommerce_AI_Agent::get_instance()->managers['auto_code_optimizer'] ?? null;
$value_tracker = Woocommerce_AI_Agent::get_instance()->managers['value_metrics_tracker'] ?? null;

// 获取引导状态
$onboarding_status = $this->get_onboarding_status();
$current_step = $onboarding_status['current_step'] ?? 1;
$completion_percentage = $onboarding_status['completion_percentage'] ?? 0;
?>

<div class="wrap wai-strategy-hub">
    <!-- 引导进度条 -->
    <div class="wai-onboarding-progress">
        <div class="progress-header">
            <h1>🚀 AI策略价值管理中枢</h1>
            <div class="progress-stats">
                <span class="completion">完成度: <?php echo $completion_percentage; ?>%</span>
                <span class="step">步骤 <?php echo $current_step; ?>/5</span>
            </div>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo $completion_percentage; ?>%"></div>
            <div class="progress-steps">
                <div class="step <?php echo $current_step >= 1 ? 'active' : ''; ?>" data-step="1">
                    <span class="step-number">1</span>
                    <span class="step-label">策略发现</span>
                </div>
                <div class="step <?php echo $current_step >= 2 ? 'active' : ''; ?>" data-step="2">
                    <span class="step-number">2</span>
                    <span class="step-label">价值评估</span>
                </div>
                <div class="step <?php echo $current_step >= 3 ? 'active' : ''; ?>" data-step="3">
                    <span class="step-number">3</span>
                    <span class="step-label">智能体部署</span>
                </div>
                <div class="step <?php echo $current_step >= 4 ? 'active' : ''; ?>" data-step="4">
                    <span class="step-number">4</span>
                    <span class="step-label">性能优化</span>
                </div>
                <div class="step <?php echo $current_step >= 5 ? 'active' : ''; ?>" data-step="5">
                    <span class="step-number">5</span>
                    <span class="step-label">价值追踪</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 引导内容区域 -->
    <div class="wai-onboarding-content">
        <?php if ($current_step == 1): ?>
        <!-- 步骤1: 策略发现 -->
        <div class="onboarding-step active" data-step="1">
            <div class="step-header">
                <h2>🔍 步骤1: 发现高价值策略</h2>
                <p>扫描全网最优性价比策略，为您的业务找到最佳机会</p>
            </div>
            
            <div class="step-content">
                <div class="discovery-wizard">
                    <div class="wizard-sidebar">
                        <h4>策略来源</h4>
                        <div class="source-filters">
                            <label class="source-option active">
                                <input type="checkbox" checked> 电商平台策略
                            </label>
                            <label class="source-option active">
                                <input type="checkbox" checked> 社交媒体策略
                            </label>
                            <label class="source-option">
                                <input type="checkbox"> 竞争分析策略
                            </label>
                            <label class="source-option">
                                <input type="checkbox"> 市场趋势策略
                            </label>
                        </div>
                        
                        <div class="effectiveness-slider">
                            <h4>性价比阈值</h4>
                            <input type="range" min="50" max="95" value="80" class="slider" id="effectivenessRange">
                            <div class="slider-value">≥ <span id="effectivenessValue">80</span>%</div>
                        </div>
                    </div>
                    
                    <div class="wizard-main">
                        <div class="scan-results">
                            <div class="results-header">
                                <h4>🎯 推荐策略</h4>
                                <button class="button button-primary" onclick="startStrategyScan()">
                                    <span class="dashicons dashicons-update"></span>
                                    扫描最新策略
                                </button>
                            </div>
                            
                            <div class="strategies-grid">
                                <!-- 策略卡片将通过AJAX加载 -->
                                <div class="loading-strategies">
                                    <div class="loading-spinner"></div>
                                    <p>正在发现高价值策略...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="quick-actions">
                            <h4>快速操作</h4>
                            <div class="action-buttons">
                                <button class="action-btn" onclick="importPresetStrategies()">
                                    <span class="icon">📦</span>
                                    <span class="label">导入预设策略包</span>
                                </button>
                                <button class="action-btn" onclick="analyzeCompetitors()">
                                    <span class="icon">🔎</span>
                                    <span class="label">分析竞争对手</span>
                                </button>
                                <button class="action-btn" onclick="generateCustomStrategy()">
                                    <span class="icon">🎨</span>
                                    <span class="label">生成定制策略</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="step-footer">
                <button class="button button-secondary" onclick="skipStep(1)">跳过</button>
                <button class="button button-primary" onclick="completeStep(1)">完成策略发现 →</button>
            </div>
        </div>

        <?php elseif ($current_step == 2): ?>
        <!-- 步骤2: 价值评估 -->
        <div class="onboarding-step active" data-step="2">
            <div class="step-header">
                <h2>💰 步骤2: 策略价值评估</h2>
                <p>分析策略的ROI潜力、实施成本和成功概率</p>
            </div>
            
            <div class="step-content">
                <div class="evaluation-dashboard">
                    <div class="evaluation-criteria">
                        <h4>评估维度</h4>
                        <div class="criteria-list">
                            <div class="criterion active" data-criterion="roi">
                                <span class="icon">📈</span>
                                <span class="label">投资回报率</span>
                            </div>
                            <div class="criterion" data-criterion="cost">
                                <span class="icon">💸</span>
                                <span class="label">实施成本</span>
                            </div>
                            <div class="criterion" data-criterion="risk">
                                <span class="icon">⚖️</span>
                                <span class="label">风险评估</span>
                            </div>
                            <div class="criterion" data-criterion="timeline">
                                <span class="icon">⏱️</span>
                                <span class="label">时间线</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="evaluation-results">
                        <div class="strategy-comparison">
                            <h4>策略对比分析</h4>
                            <div class="comparison-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>策略名称</th>
                                            <th>预计ROI</th>
                                            <th>实施成本</th>
                                            <th>成功概率</th>
                                            <th>价值评分</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="strategyComparisonBody">
                                        <!-- 策略对比数据将通过AJAX加载 -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="roi-simulation">
                            <h4>ROI模拟预测</h4>
                            <div class="simulation-chart">
                                <canvas id="roiSimulationChart" width="400" height="200"></canvas>
                            </div>
                            <div class="simulation-controls">
                                <label>投资金额: 
                                    <input type="number" id="investmentAmount" value="1000" min="100" step="100">
                                </label>
                                <label>时间周期: 
                                    <select id="timePeriod">
                                        <option value="30">30天</option>
                                        <option value="90">90天</option>
                                        <option value="180">180天</option>
                                    </select>
                                </label>
                                <button class="button" onclick="runROISimulation()">运行模拟</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="step-footer">
                <button class="button button-secondary" onclick="previousStep(2)">← 上一步</button>
                <button class="button button-primary" onclick="completeStep(2)">完成价值评估 →</button>
            </div>
        </div>

        <?php elseif ($current_step == 3): ?>
        <!-- 步骤3: 智能体部署 -->
        <div class="onboarding-step active" data-step="3">
            <div class="step-header">
                <h2>🤖 步骤3: 智能体蜂群部署</h2>
                <p>配置和部署AI智能体来自动执行选定策略</p>
            </div>
            
            <div class="step-content">
                <div class="deployment-wizard">
                    <div class="platform-selection">
                        <h4>选择目标平台</h4>
                        <div class="platform-grid">
                            <div class="platform-card" data-platform="woocommerce">
                                <div class="platform-icon">🛒</div>
                                <div class="platform-name">WooCommerce</div>
                                <div class="platform-status">已连接</div>
                            </div>
                            <div class="platform-card" data-platform="amazon">
                                <div class="platform-icon">📦</div>
                                <div class="platform-name">Amazon</div>
                                <div class="platform-status">需配置</div>
                            </div>
                            <div class="platform-card" data-platform="shopify">
                                <div class="platform-icon">🏪</div>
                                <div class="platform-name">Shopify</div>
                                <div class="platform-status">需配置</div>
                            </div>
                            <div class="platform-card" data-platform="twitter">
                                <div class="platform-icon">🐦</div>
                                <div class="platform-name">Twitter</div>
                                <div class="platform-status">需配置</div>
                            </div>
                            <div class="platform-card" data-platform="facebook">
                                <div class="platform-icon">👥</div>
                                <div class="platform-name">Facebook</div>
                                <div class="platform-status">需配置</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="agent-configuration">
                        <h4>智能体配置</h4>
                        <div class="config-tabs">
                            <div class="tab-nav">
                                <button class="tab-link active" data-tab="basic">基础配置</button>
                                <button class="tab-link" data-tab="advanced">高级设置</button>
                                <button class="tab-link" data-tab="monitoring">监控配置</button>
                            </div>
                            
                            <div class="tab-content">
                                <div id="basic" class="tab-pane active">
                                    <div class="form-group">
                                        <label>智能体名称</label>
                                        <input type="text" id="agentName" placeholder="例如: 价格优化智能体">
                                    </div>
                                    <div class="form-group">
                                        <label>执行频率</label>
                                        <select id="executionFrequency">
                                            <option value="realtime">实时</option>
                                            <option value="hourly">每小时</option>
                                            <option value="daily" selected>每天</option>
                                            <option value="weekly">每周</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>策略分配</label>
                                        <div class="strategy-assignment">
                                            <select multiple id="assignedStrategies">
                                                <!-- 策略选项将通过AJAX加载 -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="advanced" class="tab-pane">
                                    <!-- 高级配置内容 -->
                                </div>
                                
                                <div id="monitoring" class="tab-pane">
                                    <!-- 监控配置内容 -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="deployment-preview">
                        <h4>部署预览</h4>
                        <div class="preview-card">
                            <div class="preview-header">
                                <h5>智能体部署摘要</h5>
                                <span class="status-badge ready">准备部署</span>
                            </div>
                            <div class="preview-content">
                                <div class="preview-item">
                                    <span class="label">智能体数量:</span>
                                    <span class="value" id="previewAgentCount">0</span>
                                </div>
                                <div class="preview-item">
                                    <span class="label">覆盖平台:</span>
                                    <span class="value" id="previewPlatforms">无</span>
                                </div>
                                <div class="preview-item">
                                    <span class="label">预计成本:</span>
                                    <span class="value" id="previewCost">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="step-footer">
                <button class="button button-secondary" onclick="previousStep(3)">← 上一步</button>
                <button class="button button-primary" onclick="deployAgents()">部署智能体 →</button>
            </div>
        </div>

        <?php elseif ($current_step >= 4): ?>
        <!-- 步骤4+: 主控制台 -->
        <div class="main-dashboard">
            <div class="dashboard-header">
                <h2>🎯 AI策略价值管理中枢</h2>
                <div class="dashboard-actions">
                    <button class="button button-primary" onclick="showQuickStart()">
                        <span class="dashicons dashicons-controls-repeat"></span>
                        快速开始
                    </button>
                    <button class="button" onclick="showTutorial()">
                        <span class="dashicons dashicons-video-alt3"></span>
                        使用教程
                    </button>
                </div>
            </div>
            
            <div class="dashboard-widgets">
                <!-- 策略发现小部件 -->
                <div class="widget strategy-discovery">
                    <div class="widget-header">
                        <h3>🔍 策略发现</h3>
                        <span class="widget-badge new">3个新策略</span>
                    </div>
                    <div class="widget-content">
                        <div class="strategy-preview">
                            <div class="preview-item high-value">
                                <div class="strategy-name">动态定价优化</div>
                                <div class="strategy-meta">
                                    <span class="roi">+23% ROI</span>
                                    <span class="cost">低成本</span>
                                </div>
                            </div>
                            <div class="preview-item medium-value">
                                <div class="strategy-name">社交媒体自动化</div>
                                <div class="strategy-meta">
                                    <span class="roi">+15% ROI</span>
                                    <span class="cost">中成本</span>
                                </div>
                            </div>
                        </div>
                        <button class="button button-small" onclick="showStrategyDiscovery()">查看全部</button>
                    </div>
                </div>
                
                <!-- 价值追踪小部件 -->
                <div class="widget value-tracking">
                    <div class="widget-header">
                        <h3>💰 价值追踪</h3>
                        <span class="widget-badge positive">+12.5%</span>
                    </div>
                    <div class="widget-content">
                        <div class="value-metrics">
                            <div class="metric">
                                <div class="metric-value">$2,847</div>
                                <div class="metric-label">累计价值</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value">18.3%</div>
                                <div class="metric-label">平均ROI</div>
                            </div>
                        </div>
                        <button class="button button-small" onclick="showValueDashboard()">详细报告</button>
                    </div>
                </div>
                
                <!-- 智能体状态小部件 -->
                <div class="widget agent-status">
                    <div class="widget-header">
                        <h3>🤖 智能体状态</h3>
                        <span class="widget-badge active">6个在线</span>
                    </div>
                    <div class="widget-content">
                        <div class="agent-list">
                            <div class="agent online">
                                <span class="agent-name">WooCommerce优化器</span>
                                <span class="agent-status">运行中</span>
                            </div>
                            <div class="agent online">
                                <span class="agent-name">Twitter营销</span>
                                <span class="agent-status">运行中</span>
                            </div>
                        </div>
                        <button class="button button-small" onclick="showSwarmManagement()">管理智能体</button>
                    </div>
                </div>
                
                <!-- 快速操作小部件 -->
                <div class="widget quick-actions">
                    <div class="widget-header">
                        <h3>⚡ 快速操作</h3>
                    </div>
                    <div class="widget-content">
                        <div class="action-grid">
                            <button class="action-btn" onclick="runStrategyScan()">
                                <span class="icon">🔍</span>
                                <span>扫描策略</span>
                            </button>
                            <button class="action-btn" onclick="optimizePerformance()">
                                <span class="icon">⚡</span>
                                <span>性能优化</span>
                            </button>
                            <button class="action-btn" onclick="generateReport()">
                                <span class="icon">📊</span>
                                <span>生成报告</span>
                            </button>
                            <button class="action-btn" onclick="deployNewAgent()">
                                <span class="icon">🤖</span>
                                <span>部署智能体</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 主要内容区域 -->
            <div class="dashboard-main">
                <div class="tabbed-interface">
                    <nav class="tab-navigation">
                        <button class="tab-btn active" data-tab="strategies">策略管理</button>
                        <button class="tab-btn" data-tab="agents">智能体控制</button>
                        <button class="tab-btn" data-tab="optimization">性能优化</button>
                        <button class="tab-btn" data-tab="reports">分析报告</button>
                        <button class="tab-btn" data-tab="settings">系统设置</button>
                    </nav>
                    
                    <div class="tab-content">
                        <div id="strategies" class="tab-pane active">
                            <?php include 'partials/strategy-management.php'; ?>
                        </div>
                        <div id="agents" class="tab-pane">
                            <?php include 'partials/agent-control.php'; ?>
                        </div>
                        <div id="optimization" class="tab-pane">
                            <?php include 'partials/performance-optimization.php'; ?>
                        </div>
                        <div id="reports" class="tab-pane">
                            <?php include 'partials/analytics-reports.php'; ?>
                        </div>
                        <div id="settings" class="tab-pane">
                            <?php include 'partials/system-settings.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// 引导流程管理
function completeStep(step) {
    jQuery.post(ajaxurl, {
        action: 'wai_complete_onboarding_step',
        step: step,
        nonce: '<?php echo wp_create_nonce("wai_onboarding_nonce"); ?>'
    }, function(response) {
        if (response.success) {
            location.reload();
        }
    });
}

function previousStep(step) {
    jQuery.post(ajaxurl, {
        action: 'wai_previous_onboarding_step', 
        step: step,
        nonce: '<?php echo wp_create_nonce("wai_onboarding_nonce"); ?>'
    }, function(response) {
        if (response.success) {
            location.reload();
        }
    });
}

function skipStep(step) {
    completeStep(step);
}

// 策略扫描
function startStrategyScan() {
    const button = jQuery('#startStrategyScan');
    const originalText = button.html();
    
    button.html('<span class="dashicons dashicons-update spin"></span> 扫描中...');
    button.prop('disabled', true);
    
    // 模拟扫描过程
    setTimeout(() => {
        loadStrategyResults();
        button.html(originalText);
        button.prop('disabled', false);
    }, 2000);
}

function loadStrategyResults() {
    // AJAX加载策略结果
    jQuery('.strategies-grid').html('<div class="loading">加载策略数据...</div>');
    
    jQuery.post(ajaxurl, {
        action: 'wai_load_strategy_results',
        nonce: '<?php echo wp_create_nonce("wai_strategy_nonce"); ?>'
    }, function(response) {
        if (response.success) {
            jQuery('.strategies-grid').html(response.data.html);
        }
    });
}

// ROI模拟
function runROISimulation() {
    const investment = jQuery('#investmentAmount').val();
    const period = jQuery('#timePeriod').val();
    
    // 显示模拟结果
    alert(`ROI模拟运行: 投资 $${investment}, 周期 ${period}天`);
}

// 智能体部署
function deployAgents() {
    if (confirm('确定要部署配置的智能体吗？')) {
        jQuery.post(ajaxurl, {
            action: 'wai_deploy_agents',
            nonce: '<?php echo wp_create_nonce("wai_deployment_nonce"); ?>'
        }, function(response) {
            if (response.success) {
                completeStep(3);
            }
        });
    }
}

// 标签页切换
jQuery(document).ready(function($) {
    $('.tab-btn').click(function() {
        const tabId = $(this).data('tab');
        
        // 隐藏所有标签页
        $('.tab-pane').removeClass('active');
        $('.tab-btn').removeClass('active');
        
        // 显示选中标签页
        $(this).addClass('active');
        $('#' + tabId).addClass('active');
    });
    
    // 初始化策略发现
    if ($('.onboarding-step[data-step="1"]').length) {
        loadStrategyResults();
    }
});
</script>

<style>
.wai-strategy-hub {
    max-width: 1400px;
    margin: 0 auto;
}

/* 引导进度条样式 */
.wai-onboarding-progress {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.progress-header h1 {
    margin: 0;
    color: #23282d;
}

.progress-stats {
    display: flex;
    gap: 15px;
    font-size: 14px;
    color: #666;
}

.progress-bar {
    position: relative;
    height: 60px;
    background: #f0f0f0;
    border-radius: 8px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #46b450, #8bc34a);
    transition: width 0.5s ease;
}

.progress-steps {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 2;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background: #46b450;
    color: white;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 5px;
}

.step-label {
    font-size: 12px;
    color: #666;
    font-weight: 500;
}

/* 引导步骤样式 */
.onboarding-step {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.step-header {
    text-align: center;
    margin-bottom: 30px;
}

.step-header h2 {
    margin: 0 0 10px 0;
    color: #23282d;
}

.step-header p {
    margin: 0;
    color: #666;
    font-size: 16px;
}

.step-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

/* 发现向导样式 */
.discovery-wizard {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 30px;
}

.wizard-sidebar {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
}

.source-filters {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.source-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.source-option.active {
    background: #e7f4e4;
    border-left: 3px solid #46b450;
}

.effectiveness-slider {
    margin-top: 20px;
}

.slider {
    width: 100%;
    margin: 10px 0;
}

.slider-value {
    text-align: center;
    font-weight: bold;
    color: #46b450;
}

/* 小部件网格 */
.dashboard-widgets {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.widget {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e0e0;
}

.widget-header h3 {
    margin: 0;
    font-size: 16px;
}

.widget-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}

.widget-badge.new {
    background: #fff3cd;
    color: #856404;
}

.widget-badge.positive {
    background: #e7f4e4;
    color: #46b450;
}

.widget-badge.active {
    background: #e7f3ff;
    color: #0073aa;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .discovery-wizard {
        grid-template-columns: 1fr;
    }
    
    .dashboard-widgets {
        grid-template-columns: 1fr;
    }
    
    .progress-steps {
        padding: 0 10px;
    }
    
    .step-label {
        font-size: 10px;
    }
}
</style>