<?php
/**
 * 跨平台店群管理中枢 - 价值导向的蜂群策略管理界面
 * 文件路径: /wp-content/plugins/woocommerce-ai-agent/includes/admin/views/cross-platform-dashboard.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// 初始化管理器
$cross_platform_manager = Woocommerce_AI_Agent::get_instance()->managers['cross_platform'] ?? null;
$swarm_controller = Woocommerce_AI_Agent::get_instance()->managers['store_swarm'] ?? null;

// 获取店群数据
$swarm_overview = $cross_platform_manager ? $cross_platform_manager->get_swarm_overview() : $this->get_mock_swarm_data();
$swarm_performance = $swarm_controller ? $swarm_controller->get_swarm_performance() : $this->get_mock_performance_data();
$value_suggestions = $cross_platform_manager ? $cross_platform_manager->get_value_optimization_suggestions() : [];
$discovered_stores = $cross_platform_manager ? $cross_platform_manager->discover_syncable_stores() : [];
?>

<div class="wrap wai-cross-platform-hub">
    <!-- 价值引导头部 -->
    <div class="wai-value-header">
        <div class="header-main">
            <h1>🏪 店群蜂群管理中枢</h1>
            <p class="header-subtitle">发现 · 同步 · 优化 · 价值实现</p>
        </div>
        <div class="header-actions">
            <button class="button button-primary" onclick="startStoreDiscovery()">
                <span class="dashicons dashicons-search"></span>
                发现新店铺
            </button>
            <button class="button button-secondary" onclick="showQuickDeployment()">
                <span class="dashicons dashicons-migrate"></span>
                快速部署
            </button>
        </div>
    </div>

    <!-- 价值指标仪表板 -->
    <div class="wai-value-dashboard">
        <div class="value-metrics-grid">
            <!-- 总价值指标 -->
            <div class="metric-card primary">
                <div class="metric-icon">💰</div>
                <div class="metric-content">
                    <div class="metric-value">$<?php echo number_format($swarm_overview['total_value_generated']); ?></div>
                    <div class="metric-label">累计创造价值</div>
                    <div class="metric-trend positive">+<?php echo $swarm_overview['avg_roi']; ?>% ROI</div>
                </div>
            </div>

            <!-- 店群规模 -->
            <div class="metric-card">
                <div class="metric-icon">🏪</div>
                <div class="metric-content">
                    <div class="metric-value"><?php echo $swarm_overview['total_stores']; ?></div>
                    <div class="metric-label">店群规模</div>
                    <div class="metric-detail"><?php echo $swarm_overview['active_stores']; ?>个活跃</div>
                </div>
            </div>

            <!-- 同步效率 -->
            <div class="metric-card">
                <div class="metric-icon">⚡</div>
                <div class="metric-content">
                    <div class="metric-value"><?php echo $swarm_overview['efficiency_score']; ?>%</div>
                    <div class="metric-label">同步效率</div>
                    <div class="metric-detail"><?php echo $swarm_overview['active_strategies']; ?>个策略运行中</div>
                </div>
            </div>

            <!-- 蜂群性能 -->
            <div class="metric-card">
                <div class="metric-icon">🤖</div>
                <div class="metric-content">
                    <div class="metric-value"><?php echo $swarm_performance['active_agents']; ?></div>
                    <div class="metric-label">智能体蜂群</div>
                    <div class="metric-detail"><?php echo $swarm_performance['overall']['performance_score']; ?>% 性能</div>
                </div>
            </div>
        </div>
    </div>

    <div class="wai-dashboard-content">
        <!-- 引导式工作流 -->
        <div class="onboarding-workflow">
            <div class="workflow-steps">
                <div class="step active" data-step="discover">
                    <div class="step-number">1</div>
                    <div class="step-info">
                        <div class="step-title">发现店铺</div>
                        <div class="step-description">扫描并连接新的店铺</div>
                    </div>
                </div>
                <div class="step" data-step="analyze">
                    <div class="step-number">2</div>
                    <div class="step-info">
                        <div class="step-title">价值分析</div>
                        <div class="step-description">评估店铺同步价值</div>
                    </div>
                </div>
                <div class="step" data-step="deploy">
                    <div class="step-number">3</div>
                    <div class="step-info">
                        <div class="step-title">策略部署</div>
                        <div class="step-description">部署同步策略</div>
                    </div>
                </div>
                <div class="step" data-step="optimize">
                    <div class="step-number">4</div>
                    <div class="step-info">
                        <div class="step-title">持续优化</div>
                        <div class="step-description">监控和优化性能</div>
                    </div>
                </div>
            </div>

            <div class="workflow-content">
                <!-- 步骤1: 店铺发现 -->
                <div class="step-content active" data-step="discover">
                    <div class="step-header">
                        <h3>🔍 发现可同步店铺</h3>
                        <p>自动扫描您的网络，发现可连接的WooCommerce店铺</p>
                    </div>

                    <div class="discovery-panel">
                        <div class="discovery-methods">
                            <div class="method-card" data-method="subdomain">
                                <div class="method-icon">🌐</div>
                                <div class="method-content">
                                    <h4>子域名扫描</h4>
                                    <p>自动发现子域名店铺</p>
                                    <div class="method-stats">
                                        <span class="count"><?php echo count(array_filter($discovered_stores, fn($store) => $store['type'] === 'subdomain')); ?> 个发现</span>
                                    </div>
                                </div>
                                <button class="button" onclick="scanSubdomainStores()">立即扫描</button>
                            </div>

                            <div class="method-card" data-method="multisite">
                                <div class="method-icon">🔄</div>
                                <div class="method-content">
                                    <h4>多站点网络</h4>
                                    <p>扫描WordPress多站点网络</p>
                                    <div class="method-stats">
                                        <span class="count"><?php echo count(array_filter($discovered_stores, fn($store) => $store['type'] === 'multisite')); ?> 个发现</span>
                                    </div>
                                </div>
                                <button class="button" onclick="scanMultisiteStores()">扫描网络</button>
                            </div>

                            <div class="method-card" data-method="manual">
                                <div class="method-icon">➕</div>
                                <div class="method-content">
                                    <h4>手动添加</h4>
                                    <p>通过API密钥连接外部店铺</p>
                                    <div class="method-stats">
                                        <span class="count">自定义连接</span>
                                    </div>
                                </div>
                                <button class="button" onclick="showManualConnection()">添加店铺</button>
                            </div>
                        </div>

                        <?php if (!empty($discovered_stores)): ?>
                        <div class="discovery-results">
                            <h4>🎯 发现的店铺 (按价值排序)</h4>
                            <div class="stores-grid">
                                <?php foreach (array_slice($discovered_stores, 0, 6) as $store): ?>
                                <div class="store-card" data-store-id="<?php echo $store['url']; ?>">
                                    <div class="store-header">
                                        <h5><?php echo esc_html($store['name']); ?></h5>
                                        <span class="value-badge" style="--score: <?php echo $store['sync_value_score'] * 100; ?>">
                                            <?php echo round($store['sync_value_score'] * 100); ?>%
                                        </span>
                                    </div>
                                    <div class="store-meta">
                                        <span class="type"><?php echo $store['type']; ?></span>
                                        <span class="status"><?php echo $store['status']; ?></span>
                                    </div>
                                    <div class="store-capabilities">
                                        <?php foreach (array_slice($store['sync_capabilities'], 0, 3) as $capability): ?>
                                        <span class="capability"><?php echo $capability; ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($store['sync_capabilities']) > 3): ?>
                                        <span class="capability-more">+<?php echo count($store['sync_capabilities']) - 3; ?>更多</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="store-actions">
                                        <button class="button button-small" onclick="analyzeStore('<?php echo $store['url']; ?>')">分析价值</button>
                                        <button class="button button-small button-primary" onclick="connectStore('<?php echo $store['url']; ?>')">连接</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 步骤2: 价值分析 -->
                <div class="step-content" data-step="analyze">
                    <div class="step-header">
                        <h3>💰 店铺价值分析</h3>
                        <p>基于数据分析和预测模型评估每个店铺的同步价值</p>
                    </div>

                    <div class="analysis-panel">
                        <div class="value-breakdown">
                            <div class="breakdown-header">
                                <h4>价值驱动因素分析</h4>
                                <div class="breakdown-filters">
                                    <select id="valueMetric">
                                        <option value="roi">投资回报率</option>
                                        <option value="efficiency">运营效率</option>
                                        <option value="growth">增长潜力</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="breakdown-content">
                                <div class="value-factors">
                                    <div class="factor high-impact">
                                        <div class="factor-info">
                                            <span class="factor-name">定价优化潜力</span>
                                            <span class="factor-weight">35% 影响</span>
                                        </div>
                                        <div class="factor-bar">
                                            <div class="factor-fill" style="width: 78%"></div>
                                        </div>
                                        <span class="factor-score">78%</span>
                                    </div>
                                    
                                    <div class="factor medium-impact">
                                        <div class="factor-info">
                                            <span class="factor-name">库存周转优化</span>
                                            <span class="factor-weight">25% 影响</span>
                                        </div>
                                        <div class="factor-bar">
                                            <div class="factor-fill" style="width: 65%"></div>
                                        </div>
                                        <span class="factor-score">65%</span>
                                    </div>
                                    
                                    <div class="factor low-impact">
                                        <div class="factor-info">
                                            <span class="factor-name">客户数据价值</span>
                                            <span class="factor-weight">15% 影响</span>
                                        </div>
                                        <div class="factor-bar">
                                            <div class="factor-fill" style="width: 45%"></div>
                                        </div>
                                        <span class="factor-score">45%</span>
                                    </div>
                                </div>
                                
                                <div class="roi-prediction">
                                    <h5>ROI预测模型</h5>
                                    <div class="prediction-chart">
                                        <canvas id="roiPredictionChart" width="400" height="200"></canvas>
                                    </div>
                                    <div class="prediction-summary">
                                        <div class="prediction-item">
                                            <span class="label">3个月预期ROI</span>
                                            <span class="value positive">+18.5%</span>
                                        </div>
                                        <div class="prediction-item">
                                            <span class="label">投资回收期</span>
                                            <span class="value">67天</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 蜂群策略管理 -->
        <div class="swarm-strategy-panel">
            <div class="panel-header">
                <h3>🐝 蜂群策略管理</h3>
                <div class="panel-actions">
                    <button class="button button-primary" onclick="createNewStrategy()">
                        <span class="dashicons dashicons-plus"></span>
                        新建策略
                    </button>
                </div>
            </div>

            <div class="strategies-grid">
                <!-- 定价优化策略 -->
                <div class="strategy-card active" data-strategy="pricing_optimization">
                    <div class="strategy-header">
                        <div class="strategy-icon">💰</div>
                        <div class="strategy-info">
                            <h4>智能定价同步</h4>
                            <div class="strategy-stats">
                                <span class="status active">运行中</span>
                                <span class="stores">8个店铺</span>
                            </div>
                        </div>
                        <div class="strategy-value">
                            <div class="value-score">+23%</div>
                            <div class="value-label">ROI提升</div>
                        </div>
                    </div>
                    <div class="strategy-metrics">
                        <div class="metric">
                            <label>同步成功率</label>
                            <div class="metric-value">94.2%</div>
                        </div>
                        <div class="metric">
                            <label>响应时间</label>
                            <div class="metric-value">1.3s</div>
                        </div>
                        <div class="metric">
                            <label>价值创造</label>
                            <div class="metric-value">$2,847</div>
                        </div>
                    </div>
                    <div class="strategy-actions">
                        <button class="button button-small" onclick="manageStrategy('pricing_optimization')">管理</button>
                        <button class="button button-small" onclick="viewStrategyReport('pricing_optimization')">报告</button>
                        <button class="button button-small button-primary" onclick="deployStrategyToMore('pricing_optimization')">扩展</button>
                    </div>
                </div>

                <!-- 库存智能策略 -->
                <div class="strategy-card" data-strategy="inventory_intelligence">
                    <div class="strategy-header">
                        <div class="strategy-icon">📦</div>
                        <div class="strategy-info">
                            <h4>库存智能分配</h4>
                            <div class="strategy-stats">
                                <span class="status active">运行中</span>
                                <span class="stores">6个店铺</span>
                            </div>
                        </div>
                        <div class="strategy-value">
                            <div class="value-score">+18%</div>
                            <div class="value-label">效率提升</div>
                        </div>
                    </div>
                    <div class="strategy-metrics">
                        <div class="metric">
                            <label>库存周转率</label>
                            <div class="metric-value">+32%</div>
                        </div>
                        <div class="metric">
                            <label>缺货减少</label>
                            <div class="metric-value">-45%</div>
                        </div>
                        <div class="metric">
                            <label>成本节约</label>
                            <div class="metric-value">$1,235</div>
                        </div>
                    </div>
                    <div class="strategy-actions">
                        <button class="button button-small" onclick="manageStrategy('inventory_intelligence')">管理</button>
                        <button class="button button-small" onclick="viewStrategyReport('inventory_intelligence')">报告</button>
                        <button class="button button-small button-primary" onclick="deployStrategyToMore('inventory_intelligence')">扩展</button>
                    </div>
                </div>

                <!-- 添加新策略卡片 -->
                <div class="strategy-card new-strategy">
                    <div class="strategy-placeholder">
                        <div class="placeholder-icon">+</div>
                        <div class="placeholder-text">添加新策略</div>
                        <button class="button button-small" onclick="createNewStrategy()">创建</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 价值优化建议 -->
        <?php if (!empty($value_suggestions)): ?>
        <div class="optimization-suggestions">
            <div class="suggestions-header">
                <h3>💡 价值优化建议</h3>
                <span class="suggestions-count"><?php echo count($value_suggestions); ?> 个机会</span>
            </div>
            
            <div class="suggestions-grid">
                <?php foreach ($value_suggestions as $suggestion): ?>
                <div class="suggestion-card <?php echo $suggestion['priority']; ?>-priority">
                    <div class="suggestion-icon">
                        <?php echo $suggestion['type'] === 'expansion' ? '🚀' : '⚡'; ?>
                    </div>
                    <div class="suggestion-content">
                        <h4><?php echo esc_html($suggestion['title']); ?></h4>
                        <p><?php echo esc_html($suggestion['description']); ?></p>
                        <div class="suggestion-meta">
                            <span class="estimated-value"><?php echo $suggestion['estimated_value']; ?></span>
                            <span class="priority <?php echo $suggestion['priority']; ?>"><?php echo $suggestion['priority'] === 'high' ? '高优先级' : '中优先级'; ?></span>
                        </div>
                    </div>
                    <button class="button button-small button-primary" onclick="implementSuggestion('<?php echo $suggestion['type']; ?>')">立即实施</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 快速操作面板 -->
        <div class="quick-actions-panel">
            <h3>⚡ 快速操作</h3>
            <div class="actions-grid">
                <button class="action-card" onclick="runQuickSync()">
                    <div class="action-icon">🔄</div>
                    <div class="action-content">
                        <div class="action-title">快速同步</div>
                        <div class="action-description">立即执行所有同步任务</div>
                    </div>
                </button>
                
                <button class="action-card" onclick="generateValueReport()">
                    <div class="action-icon">📊</div>
                    <div class="action-content">
                        <div class="action-title">价值报告</div>
                        <div class="action-description">生成详细价值分析报告</div>
                    </div>
                </button>
                
                <button class="action-card" onclick="optimizeAllStrategies()">
                    <div class="action-icon">🎯</div>
                    <div class="action-content">
                        <div class="action-title">策略优化</div>
                        <div class="action-description">自动优化所有运行策略</div>
                    </div>
                </button>
                
                <button class="action-card" onclick="showPerformanceDashboard()">
                    <div class="action-icon">📈</div>
                    <div class="action-content">
                        <div class="action-title">性能监控</div>
                        <div class="action-description">查看实时性能指标</div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// 工作流步骤管理
jQuery(document).ready(function($) {
    $('.workflow-steps .step').click(function() {
        const step = $(this).data('step');
        
        // 更新步骤状态
        $('.workflow-steps .step').removeClass('active');
        $(this).addClass('active');
        
        // 显示对应内容
        $('.step-content').removeClass('active');
        $(`.step-content[data-step="${step}"]`).addClass('active');
    });
});

// 店铺发现功能
function startStoreDiscovery() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<span class="dashicons dashicons-update spin"></span> 扫描中...';
    button.disabled = true;
    
    // 模拟扫描过程
    setTimeout(() => {
        // 这里应该是AJAX调用实际扫描逻辑
        alert('店铺扫描完成！发现3个新店铺');
        button.innerHTML = originalText;
        button.disabled = false;
        
        // 刷新发现结果
        location.reload();
    }, 3000);
}

function scanSubdomainStores() {
    // 子域名扫描逻辑
    console.log('开始子域名扫描...');
}

function scanMultisiteStores() {
    // 多站点扫描逻辑  
    console.log('开始多站点扫描...');
}

// 策略管理功能
function deployStrategyToMore(strategyType) {
    if (confirm(`确定要将 ${strategyType} 策略部署到更多店铺吗？`)) {
        // AJAX调用部署逻辑
        $.post(ajaxurl, {
            action: 'wai_deploy_strategy',
            strategy_type: strategyType,
            nonce: '<?php echo wp_create_nonce("wai_strategy_nonce"); ?>'
        }, function(response) {
            if (response.success) {
                alert('策略部署成功！');
            }
        });
    }
}

// 价值优化实施
function implementSuggestion(suggestionType) {
    // 实施优化建议
    console.log('实施优化建议:', suggestionType);
}
</script>

<style>
.wai-cross-platform-hub {
    max-width: 1400px;
    margin: 0 auto;
}

/* 价值引导头部 */
.wai-value-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.header-main h1 {
    margin: 0 0 8px 0;
    font-size: 32px;
    color: #23282d;
}

.header-subtitle {
    margin: 0;
    color: #666;
    font-size: 16px;
    font-weight: 500;
}

.header-actions {
    display: flex;
    gap: 12px;
}

/* 价值指标仪表板 */
.value-metrics-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 15px;
    margin-bottom: 30px;
}

.metric-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-2px);
}

.metric-card.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.metric-icon {
    font-size: 32px;
}

.metric-content {
    flex: 1;
}

.metric-value {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
}

.metric-label {
    font-size: 14px;
    opacity: 0.8;
    margin-bottom: 5px;
}

.metric-detail, .metric-trend {
    font-size: 12px;
}

.metric-trend.positive {
    color: #46b450;
}

/* 引导式工作流 */
.onboarding-workflow {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.workflow-steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
}

.step {
    display: flex;
    align-items: center;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    border-right: 1px solid #e0e0e0;
}

.step:last-child {
    border-right: none;
}

.step.active {
    background: white;
    border-bottom: 3px solid #0073aa;
}

.step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 12px;
}

.step.active .step-number {
    background: #0073aa;
    color: white;
}

.step-title {
    font-weight: 600;
    margin-bottom: 4px;
}

.step-description {
    font-size: 12px;
    color: #666;
}

.workflow-content {
    padding: 30px;
}

.step-content {
    display: none;
}

.step-content.active {
    display: block;
}

/* 发现面板样式 */
.discovery-methods {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.method-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.method-card:hover {
    border-color: #0073aa;
    transform: translateY(-2px);
}

.method-icon {
    font-size: 24px;
    margin-bottom: 10px;
}

.method-content h4 {
    margin: 0 0 8px 0;
}

.method-content p {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 14px;
}

.method-stats {
    font-size: 12px;
    color: #0073aa;
    font-weight: 500;
}

.stores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.store-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
}

.store-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.store-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.store-header h5 {
    margin: 0;
    font-size: 14px;
}

.value-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    background: hsl(calc(var(--score) * 1.2), 70%, 50%);
    color: white;
}

.store-meta {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
    font-size: 11px;
}

.store-meta .type {
    background: #e7f3ff;
    color: #0073aa;
    padding: 2px 6px;
    border-radius: 8px;
}

.store-meta .status {
    background: #e7f4e4;
    color: #46b450;
    padding: 2px 6px;
    border-radius: 8px;
}

.store-capabilities {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 12px;
}

.capability {
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 10px;
}

.capability-more {
    font-size: 10px;
    color: #666;
}

.store-actions {
    display: flex;
    gap: 6px;
}

/* 蜂群策略管理 */
.swarm-strategy-panel {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.strategies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.strategy-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #0073aa;
    transition: all 0.3s ease;
}

.strategy-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.strategy-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 15px;
}

.strategy-icon {
    font-size: 24px;
}

.strategy-info {
    flex: 1;
}

.strategy-info h4 {
    margin: 0 0 8px 0;
}

.strategy-stats {
    display: flex;
    gap: 10px;
    font-size: 12px;
}

.strategy-stats .status {
    background: #e7f4e4;
    color: #46b450;
    padding: 2px 6px;
    border-radius: 8px;
}

.strategy-stats .stores {
    color: #666;
}

.strategy-value {
    text-align: right;
}

.value-score {
    font-size: 18px;
    font-weight: bold;
    color: #46b450;
}

.value-label {
    font-size: 11px;
    color: #666;
}

.strategy-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 15px;
}

.strategy-metrics .metric {
    text-align: center;
}

.strategy-metrics label {
    display: block;
    font-size: 11px;
    color: #666;
    margin-bottom: 4px;
}

.strategy-metrics .metric-value {
    font-size: 14px;
    font-weight: bold;
}

.strategy-actions {
    display: flex;
    gap: 6px;
}

/* 新策略卡片 */
.new-strategy {
    border: 2px dashed #ccc;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 180px;
}

.strategy-placeholder {
    text-align: center;
}

.placeholder-icon {
    font-size: 32px;
    margin-bottom: 10px;
    color: #ccc;
}

.placeholder-text {
    color: #666;
    margin-bottom: 10px;
}

/* 优化建议 */
.optimization-suggestions {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.suggestions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.suggestions-count {
    background: #0073aa;
    color: white;
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: bold;
}

.suggestions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.suggestion-card {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #ccc;
}

.suggestion-card.high-priority {
    border-left-color: #dc3232;
}

.suggestion-card.medium-priority {
    border-left-color: #ffb900;
}

.suggestion-icon {
    font-size: 20px;
}

.suggestion-content {
    flex: 1;
}

.suggestion-content h4 {
    margin: 0 0 8px 0;
}

.suggestion-content p {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
}

.suggestion-meta {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
}

.estimated-value {
    color: #46b450;
    font-weight: bold;
}

.priority {
    padding: 2px 6px;
    border-radius: 8px;
    font-weight: bold;
}

.priority.high {
    background: #fbe7e7;
    color: #dc3232;
}

.priority.medium {
    background: #fff3cd;
    color: #856404;
}

/* 快速操作面板 */
.quick-actions-panel {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.action-card {
    background: #f8f9fa;
    border: none;
    padding: 20px;
    border-radius: 8px;
    text-align: left;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 15px;
}

.action-card:hover {
    background: #0073aa;
    color: white;
    transform: translateY(-2px);
}

.action-icon {
    font-size: 24px;
}

.action-title {
    font-weight: 600;
    margin-bottom: 4px;
}

.action-description {
    font-size: 12px;
    opacity: 0.8;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .value-metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .discovery-methods {
        grid-template-columns: 1fr;
    }
    
    .workflow-steps {
        grid-template-columns: 1fr;
    }
    
    .strategies-grid {
        grid-template-columns: 1fr;
    }
    
    .suggestions-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .wai-value-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>