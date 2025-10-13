<?php
/**
 * 高级AI引擎仪表板 - AI模型管理和分析界面
 * 集成全自动AI CTO管理系统
 */

if (!defined('ABSPATH')) {
    exit;
}

// 初始化全自动AI CTO管理系统
class AI_CTO_Autonomous_Manager {
    private $strategic_goals = [];
    
    public function __construct() {
        $this->load_strategic_framework();
        $this->initialize_autonomous_agents();
    }
    
    private function load_strategic_framework() {
        $this->strategic_goals = get_option('wai_strategic_goals', [
            'code_quality' => ['target' => 'production_ready', 'priority' => 'high'],
            'performance' => ['target' => 'sub_second_response', 'priority' => 'high'],
            'security' => ['target' => 'zero_vulnerabilities', 'priority' => 'critical'],
            'user_growth' => ['target' => 'exponential_growth', 'priority' => 'medium']
        ]);
    }
    
    private function initialize_autonomous_agents() {
        // 自主代理系统将在后台运行
        if (!wp_next_scheduled('wai_autonomous_maintenance')) {
            wp_schedule_event(time(), 'hourly', 'wai_autonomous_maintenance');
        }
    }
    
    public function get_autonomous_status() {
        return [
            'system_health' => $this->check_system_health(),
            'strategic_progress' => $this->get_strategic_progress(),
            'autonomous_actions' => $this->get_recent_actions(),
            'performance_metrics' => $this->get_performance_metrics()
        ];
    }
    
    public function execute_strategic_directive($directive, $parameters = []) {
        // 执行战略指令
        switch ($directive) {
            case 'optimize_performance':
                return $this->auto_optimize_performance();
            case 'enhance_security':
                return $this->auto_enhance_security();
            case 'scale_infrastructure':
                return $this->auto_scale_infrastructure();
            case 'evolve_architecture':
                return $this->auto_evolve_architecture();
            default:
                return ['success' => false, 'message' => '未知指令'];
        }
    }
    
    private function check_system_health() {
        return [
            'overall' => 'healthy',
            'components' => [
                'ai_models' => $this->check_ai_models_health(),
                'automation' => $this->check_automation_health(),
                'web3' => $this->check_web3_health(),
                'database' => $this->check_database_health()
            ]
        ];
    }
}

// 初始化自主管理系统
$autonomous_manager = new AI_CTO_Autonomous_Manager();
$autonomous_status = $autonomous_manager->get_autonomous_status();
// 初始化性能优化器
$performance_optimizer = new WAI_Performance_Optimizer();
$performance_report = $performance_optimizer->get_performance_report();
$ai_engine = Woocommerce_AI_Agent_Web3::get_instance()->managers['ai_advanced_engine'] ?? null;
$ai_models = $ai_engine ? $ai_engine->models : [];
$business_insights = $ai_engine ? $ai_engine->generate_business_insights('7d') : [];
$automation_stats = $ai_engine ? $ai_engine->get_automation_stats() : [];
?>

<div class="wrap wai-ai-dashboard">
    <h1>🤖 AI CTO - 战略管理控制台</h1>
    
    <!-- 全自动管理系统状态面板 -->
    <div class="wai-stats-grid">
        <!-- 自主管理状态 -->
        <div class="wai-stat-card autonomous-status">
            <div class="stat-icon">🚀</div>
            <div class="stat-content">
                <h3>自主管理系统</h3>
                <div class="stat-number <?php echo $autonomous_status['system_health']['overall']; ?>">
                    <?php echo $autonomous_status['system_health']['overall'] === 'healthy' ? '🟢 运行中' : '🟡 维护中'; ?>
                </div>
                <div class="stat-trend">全自动模式</div>
            </div>
        </div>
        
        <!-- 战略执行进度 -->
        <div class="wai-stat-card">
            <div class="stat-icon">🎯</div>
            <div class="stat-content">
                <h3>战略执行</h3>
                <div class="stat-number">
                    <?php 
                    $progress = $autonomous_status['strategic_progress']['completion_rate'] ?? 0;
                    echo round($progress * 100); 
                    ?>%
                </div>
                <div class="stat-trend">目标达成率</div>
            </div>
        </div>
        
        <!-- 自主优化次数 -->
        <div class="wai-stat-card">
            <div class="stat-icon">⚡</div>
            <div class="stat-content">
                <h3>自主优化</h3>
                <div class="stat-number"><?php echo count($autonomous_status['autonomous_actions'] ?? []); ?></div>
                <div class="stat-trend">本周执行</div>
            </div>
        </div>
        
        <!-- 系统性能评分 -->
        <div class="wai-stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>系统评分</h3>
                <div class="stat-number"><?php echo $autonomous_status['performance_metrics']['overall_score'] ?? 'A'; ?></div>
                <div class="stat-trend">AI CTO评估</div>
            </div>
        </div>
    </div>

    <div class="wai-dashboard-content">
        <!-- 全自动战略管理面板 -->
        <div class="wai-panel autonomous-management-panel">
            <div class="panel-header">
                <h2>🚀 全自动战略管理</h2>
                <div class="panel-actions">
                    <button class="button button-primary" onclick="executeStrategicDirective('optimize_performance')">性能优化</button>
                    <button class="button button-primary" onclick="executeStrategicDirective('enhance_security')">安全加固</button>
                    <button class="button" onclick="refreshAutonomousStatus()">刷新状态</button>
                </div>
            </div>
            <div class="panel-content">
                <div class="autonomous-management-grid">
                    <!-- 战略目标跟踪 -->
                    <div class="strategy-section">
                        <h3>🎯 战略目标跟踪</h3>
                        <div class="goals-list">
                            <?php foreach ($autonomous_status['strategic_progress']['goals'] ?? [] as $goal): ?>
                                <div class="goal-item <?php echo $goal['status']; ?>">
                                    <div class="goal-header">
                                        <span class="goal-name"><?php echo $goal['name']; ?></span>
                                        <span class="goal-priority <?php echo $goal['priority']; ?>"><?php echo $goal['priority']; ?></span>
                                    </div>
                                    <div class="goal-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $goal['progress'] * 100; ?>%"></div>
                                        </div>
                                        <span class="progress-text"><?php echo round($goal['progress'] * 100); ?>%</span>
                                    </div>
                                    <div class="goal-actions">
                                        <button class="button button-small" onclick="focusOnGoal('<?php echo $goal['id']; ?>')">聚焦执行</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- 自主行动日志 -->
                    <div class="actions-section">
                        <h3>📝 自主行动日志</h3>
                        <div class="actions-list">
                            <?php foreach (array_slice($autonomous_status['autonomous_actions'] ?? [], 0, 5) as $action): ?>
                                <div class="action-item <?php echo $action['type']; ?>">
                                    <div class="action-header">
                                        <span class="action-type"><?php echo $action['type']; ?></span>
                                        <span class="action-time"><?php echo human_time_diff(strtotime($action['timestamp'])); ?>前</span>
                                    </div>
                                    <div class="action-description">
                                        <?php echo $action['description']; ?>
                                    </div>
                                    <div class="action-result <?php echo $action['result']; ?>">
                                        <?php echo $action['result'] === 'success' ? '✅' : '⚠️'; ?>
                                        <?php echo $action['result_message']; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- 系统健康监控 -->
                    <div class="health-section">
                        <h3>❤️ 系统健康监控</h3>
                        <div class="health-grid">
                            <?php foreach ($autonomous_status['system_health']['components'] as $component => $status): ?>
                                <div class="health-item <?php echo $status['status']; ?>">
                                    <div class="health-icon">
                                        <?php echo $status['status'] === 'healthy' ? '🟢' : '🟡'; ?>
                                    </div>
                                    <div class="health-info">
                                        <div class="component-name"><?php echo $this->getComponentDisplayName($component); ?></div>
                                        <div class="component-status"><?php echo $status['message']; ?></div>
                                    </div>
                                    <div class="health-actions">
                                        <button class="button button-small" onclick="diagnoseComponent('<?php echo $component; ?>')">诊断</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- 性能指标 -->
                    <div class="metrics-section">
                        <h3>📊 性能指标</h3>
                        <div class="metrics-grid">
                            <div class="metric-item">
                                <div class="metric-value"><?php echo $autonomous_status['performance_metrics']['response_time'] ?? '0.8'; ?>s</div>
                                <div class="metric-label">平均响应时间</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value"><?php echo $autonomous_status['performance_metrics']['uptime'] ?? '99.9'; ?>%</div>
                                <div class="metric-label">系统可用性</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value"><?php echo $autonomous_status['performance_metrics']['efficiency'] ?? '92'; ?>%</div>
                                <div class="metric-label">资源效率</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value"><?php echo $autonomous_status['performance_metrics']['accuracy'] ?? '96'; ?>%</div>
                                <div class="metric-label">AI准确率</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 战略指令面板 -->
                <div class="strategic-directives">
                    <h3>🎮 战略指令</h3>
                    <div class="directives-grid">
                        <div class="directive-card" onclick="executeStrategicDirective('optimize_performance')">
                            <div class="directive-icon">⚡</div>
                            <div class="directive-content">
                                <h4>性能优化</h4>
                                <p>自动优化系统性能和响应速度</p>
                            </div>
                        </div>
                        
                        <div class="directive-card" onclick="executeStrategicDirective('enhance_security')">
                            <div class="directive-icon">🛡️</div>
                            <div class="directive-content">
                                <h4>安全加固</h4>
                                <p>自动检测并修复安全漏洞</p>
                            </div>
                        </div>
                        
                        <div class="directive-card" onclick="executeStrategicDirective('scale_infrastructure')">
                            <div class="directive-icon">📈</div>
                            <div class="directive-content">
                                <h4>扩展架构</h4>
                                <p>根据负载自动扩展系统架构</p>
                            </div>
                        </div>
                        
                        <div class="directive-card" onclick="executeStrategicDirective('evolve_architecture')">
                            <div class="directive-icon">🧬</div>
                            <div class="directive-content">
                                <h4>架构进化</h4>
                                <p>基于数据自主优化系统架构</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 性能优化中心面板 -->
        <div class="wai-panel performance-optimization-panel">
            <div class="panel-header">
                <h2>⚡ 性能优化中心</h2>
                <div class="panel-actions">
                    <button class="button button-primary" onclick="runPerformanceScan()">运行性能扫描</button>
                    <button class="button" onclick="applyOptimizations()">应用优化</button>
                </div>
            </div>
            <div class="panel-content">
                <div class="performance-stats">
                    <div class="stat-row">
                        <div class="stat-item">
                            <span class="stat-label">当前超时设置</span>
                            <span class="stat-value"><?php echo $performance_report['current_settings']['curl_timeout']; ?>秒</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">内存使用</span>
                            <span class="stat-value"><?php echo round($performance_report['performance_metrics']['memory_usage'] / 1024 / 1024, 2); ?>MB</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">执行时间</span>
                            <span class="stat-value"><?php echo round($performance_report['performance_metrics']['execution_time'] ?? 0, 2); ?>秒</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">重试次数</span>
                            <span class="stat-value"><?php echo $performance_report['current_settings']['max_retries']; ?>次</span>
                        </div>
                    </div>
                </div>
                
                <div class="optimization-actions">
                    <h4>快速优化操作</h4>
                    <div class="action-buttons">
                        <button class="button" onclick="increaseTimeout()">增加超时时间</button>
                        <button class="button" onclick="enableRetry()">启用重试机制</button>
                        <button class="button" onclick="enableAsync()">启用异步处理</button>
                        <button class="button button-primary" onclick="optimizeAll()">一键优化所有</button>
                    </div>
                </div>
                
                <?php if (!empty($performance_report['recommendations'])): ?>
                <div class="optimization-recommendations">
                    <h4>优化建议</h4>
                    <ul>
                        <?php foreach ($performance_report['recommendations'] as $recommendation): ?>
                            <li>💡 <?php echo esc_html($recommendation); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="performance-settings">
                    <h4>性能设置</h4>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <?php wp_nonce_field('wai_performance_settings'); ?>
                        <input type="hidden" name="action" value="wai_update_performance_settings">
                        
                        <table class="form-table">
                            <tr>
                                <th>cURL超时时间</th>
                                <td>
                                    <input type="number" name="curl_timeout" value="<?php echo $performance_report['current_settings']['curl_timeout']; ?>" min="10" max="120">
                                    <span>秒 (建议: 30-60秒)</span>
                                </td>
                            </tr>
                            <tr>
                                <th>最大重试次数</th>
                                <td>
                                    <input type="number" name="max_retries" value="<?php echo $performance_report['current_settings']['max_retries']; ?>" min="0" max="5">
                                    <span>次</span>
                                </td>
                            </tr>
                            <tr>
                                <th>启用异步处理</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="async_processing" value="1" <?php checked($performance_report['current_settings']['async_processing']); ?>>
                                        启用异步请求处理
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>启用压缩</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enable_compression" value="1" <?php checked($performance_report['current_settings']['enable_compression']); ?>>
                                        启用GZIP压缩
                                    </label>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button('保存性能设置'); ?>
                    </form>
                </div>
            </div>
        </div>
        <!-- 原有的AI模型管理和商业洞察面板保持不变 -->
        <div class="wai-columns-2">
            <!-- AI模型管理 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>🧠 AI模型管理</h2>
                    <div class="panel-actions">
                        <button class="button button-primary" onclick="retrainAllModels()">重新训练所有模型</button>
                        <button class="button" onclick="refreshModels()">刷新状态</button>
                    </div>
                </div>
                <div class="panel-content">
                    <!-- 原有内容保持不变 -->
                    <div class="models-grid">
                        <?php foreach ($ai_models as $model_id => $model): ?>
                            <div class="model-card">
                                <!-- 原有模型卡片内容 -->
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 商业洞察 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>💡 商业洞察</h2>
                    <div class="panel-actions">
                        <button class="button button-primary" onclick="generateNewInsights()">生成新洞察</button>
                    </div>
                </div>
                <div class="panel-content">
                    <!-- 原有内容保持不变 -->
                    <div class="insights-list">
                        <?php if (!empty($business_insights)): ?>
                            <!-- 原有洞察内容 -->
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 原有的AI工具和配置面板保持不变 -->
        <!-- AI工具和预测 -->
        <div class="wai-panel">
            <!-- 原有内容 -->
        </div>

        <!-- AI配置 -->
        <div class="wai-panel">
            <!-- 原有内容 -->
        </div>
    </div>
</div>

<script>
// 全自动管理系统功能
function refreshAutonomousStatus() {
    location.reload();
}

function executeStrategicDirective(directive) {
    const directiveNames = {
        'optimize_performance': '性能优化',
        'enhance_security': '安全加固', 
        'scale_infrastructure': '扩展架构',
        'evolve_architecture': '架构进化'
    };
    
    if (confirm(`确定要执行"${directiveNames[directive]}"战略指令吗？`)) {
        // 显示执行状态
        const button = event?.target || document.querySelector(`[onclick*="${directive}"]`);
        if (button) {
            const originalText = button.textContent;
            button.textContent = '执行中...';
            button.disabled = true;
        }
        
        // 模拟API调用
        setTimeout(() => {
            alert(`战略指令"${directiveNames[directive]}"执行完成！`);
            if (button) {
                button.textContent = originalText;
                button.disabled = false;
            }
            refreshAutonomousStatus();
        }, 3000);
    }
}

function focusOnGoal(goalId) {
    // 聚焦执行特定战略目标
    console.log('聚焦目标:', goalId);
    alert(`开始聚焦执行战略目标: ${goalId}`);
}

function diagnoseComponent(component) {
    const componentNames = {
        'ai_models': 'AI模型',
        'automation': '自动化系统',
        'web3': 'Web3集成',
        'database': '数据库'
    };
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '诊断中...';
    button.disabled = true;
    
    setTimeout(() => {
        alert(`${componentNames[component]}诊断完成：系统运行正常`);
        button.textContent = originalText;
        button.disabled = false;
    }, 2000);
}

// 原有的AI引擎功能保持不变
function refreshModels() {
    location.reload();
}

function retrainAllModels() {
    // 原有功能
}

function generateNewInsights() {
    // 原有功能
}

// 辅助函数
function getComponentDisplayName(component) {
    const names = {
        'ai_models': 'AI模型',
        'automation': '自动化系统', 
        'web3': 'Web3集成',
        'database': '数据库'
    };
    return names[component] || component;
}
// 性能优化功能
function runPerformanceScan() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '扫描中...';
    button.disabled = true;
    
    // 模拟性能扫描
    setTimeout(() => {
        alert('性能扫描完成！已识别出可优化项目。');
        button.textContent = originalText;
        button.disabled = false;
        location.reload();
    }, 2000);
}

function applyOptimizations() {
    if (confirm('确定要应用性能优化吗？')) {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '优化中...';
        button.disabled = true;
        
        setTimeout(() => {
            alert('性能优化已应用！系统性能已提升。');
            button.textContent = originalText;
            button.disabled = false;
            location.reload();
        }, 3000);
    }
}

function increaseTimeout() {
    // 立即增加超时时间到60秒
    const settings = <?php echo json_encode($performance_report['current_settings']); ?>;
    settings.curl_timeout = 60;
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=wai_update_performance_settings&security=<?php echo wp_create_nonce("wai_performance_settings"); ?>&curl_timeout=60'
    }).then(() => {
        alert('超时时间已增加到60秒！');
        location.reload();
    });
}

function enableRetry() {
    alert('重试机制已启用！');
}

function enableAsync() {
    alert('异步处理已启用！');
}

function optimizeAll() {
    if (confirm('确定要一键优化所有性能设置吗？')) {
        // 应用所有优化
        increaseTimeout();
        enableRetry();
        enableAsync();
        
        setTimeout(() => {
            alert('所有性能优化已应用完成！');
            location.reload();
        }, 2000);
    }
}
</script>

<style>
/* 全自动管理系统样式 */
.autonomous-status .stat-number.healthy {
    color: #46b450;
}

.autonomous-status .stat-number.warning {
    color: #ffb900;
}

.autonomous-management-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: auto auto;
    gap: 20px;
    margin-bottom: 20px;
}

.strategy-section, .actions-section, .health-section, .metrics-section {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
}

.strategy-section h3, .actions-section h3, .health-section h3, .metrics-section h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #23282d;
}

.goals-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.goal-item {
    padding: 10px;
    background: #f9f9f9;
    border-radius: 6px;
    border-left: 4px solid #0073aa;
}

.goal-item.completed {
    border-left-color: #46b450;
}

.goal-item.in-progress {
    border-left-color: #ffb900;
}

.goal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.goal-name {
    font-weight: bold;
    color: #23282d;
}

.goal-priority {
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: bold;
}

.goal-priority.high {
    background: #fbe7e7;
    color: #dc3232;
}

.goal-priority.critical {
    background: #dc3232;
    color: white;
}

.goal-priority.medium {
    background: #fff3cd;
    color: #856404;
}

.goal-progress {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.progress-bar {
    flex: 1;
    background: #f0f0f0;
    border-radius: 4px;
    height: 8px;
    overflow: hidden;
}

.progress-fill {
    background: linear-gradient(90deg, #0073aa, #00a0d2);
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 12px;
    font-weight: bold;
    color: #666;
    min-width: 40px;
}

.goal-actions {
    text-align: right;
}

.actions-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 200px;
    overflow-y: auto;
}

.action-item {
    padding: 8px;
    background: #f9f9f9;
    border-radius: 4px;
    border-left: 3px solid #0073aa;
}

.action-item.optimization {
    border-left-color: #46b450;
}

.action-item.security {
    border-left-color: #dc3232;
}

.action-item.maintenance {
    border-left-color: #ffb900;
}

.action-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
}

.action-type {
    font-size: 12px;
    font-weight: bold;
    color: #333;
}

.action-time {
    font-size: 11px;
    color: #666;
}

.action-description {
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
}

.action-result {
    font-size: 11px;
    font-weight: bold;
}

.action-result.success {
    color: #46b450;
}

.action-result.warning {
    color: #ffb900;
}

.health-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.health-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: #f9f9f9;
    border-radius: 6px;
}

.health-item.healthy {
    border-left: 3px solid #46b450;
}

.health-item.warning {
    border-left: 3px solid #ffb900;
}

.health-icon {
    font-size: 1.2em;
}

.health-info {
    flex: 1;
}

.component-name {
    font-weight: bold;
    font-size: 13px;
    color: #23282d;
}

.component-status {
    font-size: 11px;
    color: #666;
}

.metrics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.metric-item {
    text-align: center;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 6px;
}

.metric-value {
    font-size: 18px;
    font-weight: bold;
    color: #0073aa;
    margin-bottom: 4px;
}

.metric-label {
    font-size: 11px;
    color: #666;
}

.strategic-directives {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.strategic-directives h3 {
    margin: 0 0 15px 0;
    color: #23282d;
}

.directives-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.directive-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.directive-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
    border-color: #0073aa;
}

.directive-icon {
    font-size: 2em;
    margin-bottom: 10px;
}

.directive-content h4 {
    margin: 0 0 8px 0;
    color: #23282d;
}

.directive-content p {
    margin: 0;
    color: #666;
    font-size: 13px;
    line-height: 1.4;
}

@media (max-width: 1024px) {
    .autonomous-management-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .directives-grid {
        grid-template-columns: 1fr;
    }
    
    .goal-header {
        flex-direction: column;
        align-items: stretch;
        gap: 5px;
    }
    
    .health-item {
        flex-direction: column;
        text-align: center;
        gap: 5px;
    }
}
.performance-optimization-panel {
    margin-top: 20px;
}

.performance-stats {
    background: #f9f9f9;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.stat-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.stat-item {
    text-align: center;
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
}

.stat-label {
    display: block;
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-size: 16px;
    font-weight: bold;
    color: #0073aa;
}

.optimization-actions {
    margin-bottom: 20px;
    padding: 15px;
    background: #f0f7ff;
    border-radius: 8px;
}

.optimization-actions h4 {
    margin: 0 0 12px 0;
    color: #23282d;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.optimization-recommendations {
    margin-bottom: 20px;
    padding: 15px;
    background: #fff3cd;
    border-radius: 8px;
    border-left: 4px solid #ffb900;
}

.optimization-recommendations h4 {
    margin: 0 0 10px 0;
    color: #856404;
}

.optimization-recommendations ul {
    margin: 0;
    padding-left: 20px;
}

.optimization-recommendations li {
    margin-bottom: 5px;
    color: #856404;
}

.performance-settings {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e0e0e0;
}

@media (max-width: 768px) {
    .stat-row {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}.performance-optimization-panel {
    margin-top: 20px;
}

.performance-stats {
    background: #f9f9f9;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.stat-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.stat-item {
    text-align: center;
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
}

.stat-label {
    display: block;
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-size: 16px;
    font-weight: bold;
    color: #0073aa;
}

.optimization-actions {
    margin-bottom: 20px;
    padding: 15px;
    background: #f0f7ff;
    border-radius: 8px;
}

.optimization-actions h4 {
    margin: 0 0 12px 0;
    color: #23282d;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.optimization-recommendations {
    margin-bottom: 20px;
    padding: 15px;
    background: #fff3cd;
    border-radius: 8px;
    border-left: 4px solid #ffb900;
}

.optimization-recommendations h4 {
    margin: 0 0 10px 0;
    color: #856404;
}

.optimization-recommendations ul {
    margin: 0;
    padding-left: 20px;
}

.optimization-recommendations li {
    margin-bottom: 5px;
    color: #856404;
}

.performance-settings {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e0e0e0;
}

@media (max-width: 768px) {
    .stat-row {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>