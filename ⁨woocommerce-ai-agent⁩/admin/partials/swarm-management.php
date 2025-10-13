<?php
/**
 * 蜂群管理界面 - 多智能体协调监控
 */

if (!defined('ABSPATH')) {
    exit;
}

$swarm_intelligence = Woocommerce_AI_Agent_Web3::get_instance()->managers['swarm_intelligence'] ?? null;
$swarm_status = $swarm_intelligence ? $swarm_intelligence->get_swarm_status() : [];
$recent_coordinations = get_option('wai_recent_swarm_coordinations', []);
?>

<div class="wrap wai-swarm-management">
    <h1>🐝 蜂群智能管理</h1>
    
    <div class="wai-stats-grid">
        <!-- 智能体统计 -->
        <div class="wai-stat-card">
            <div class="stat-icon">🤖</div>
            <div class="stat-content">
                <h3>智能体总数</h3>
                <div class="stat-number"><?php echo $swarm_status['total_agents'] ?? 0; ?></div>
                <div class="stat-trend"><?php echo $swarm_status['active_agents'] ?? 0; ?> 个活跃</div>
            </div>
        </div>
        
        <!-- 性能指标 -->
        <div class="wai-stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>平均性能</h3>
                <div class="stat-number"><?php echo round(($swarm_status['average_performance'] ?? 0) * 100, 1); ?>%</div>
                <div class="stat-trend">智能体协作效率</div>
            </div>
        </div>
        
        <!-- 任务队列 -->
        <div class="wai-stat-card">
            <div class="stat-icon">⏰</div>
            <div class="stat-content">
                <h3>待处理任务</h3>
                <div class="stat-number"><?php echo $swarm_status['pending_tasks'] ?? 0; ?></div>
                <div class="stat-trend">等待协调执行</div>
            </div>
        </div>
        
        <!-- 最后协调 -->
        <div class="wai-stat-card">
            <div class="stat-icon">🔄</div>
            <div class="stat-content">
                <h3>最后协调</h3>
                <div class="stat-number">
                    <?php 
                    $last_coordination = $swarm_status['last_coordination'] ?? '从未';
                    echo $last_coordination === '从未' ? '从未' : human_time_diff(strtotime($last_coordination)) . '前';
                    ?>
                </div>
                <div class="stat-trend">蜂群活动</div>
            </div>
        </div>
    </div>

    <div class="wai-dashboard-content">
        <div class="wai-columns-2">
            <!-- 智能体状态 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>🤖 智能体状态监控</h2>
                    <div class="panel-actions">
                        <button class="button" onclick="refreshAgentsStatus()">刷新状态</button>
                        <button class="button button-primary" onclick="runCoordination()">执行协调</button>
                    </div>
                </div>
                <div class="panel-content">
                    <div class="agents-grid">
                        <?php
                        $agents = [
                            'pricing_agent' => ['name' => '价格优化智能体', 'icon' => '💰'],
                            'inventory_agent' => ['name' => '库存管理智能体', 'icon' => '📦'],
                            'marketing_agent' => ['name' => '营销策略智能体', 'icon' => '📢'],
                            'web3_agent' => ['name' => 'Web3交互智能体', 'icon' => '🔗'],
                            'metaverse_agent' => ['name' => '元宇宙集成智能体', 'icon' => '🌌']
                        ];
                        
                        foreach ($agents as $agent_id => $agent_info):
                            $status = $swarm_intelligence ? $swarm_intelligence->agents[$agent_id]['status'] ?? 'inactive' : 'inactive';
                            $performance = $swarm_intelligence ? $swarm_intelligence->agents[$agent_id]['performance'] ?? 0 : 0;
                        ?>
                            <div class="agent-card status-<?php echo $status; ?>">
                                <div class="agent-header">
                                    <div class="agent-icon"><?php echo $agent_info['icon']; ?></div>
                                    <div class="agent-info">
                                        <h4><?php echo $agent_info['name']; ?></h4>
                                        <span class="agent-id">ID: <?php echo $agent_id; ?></span>
                                    </div>
                                    <div class="agent-status">
                                        <span class="status-badge <?php echo $status; ?>">
                                            <?php echo $status === 'active' ? '活跃' : '未激活'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="agent-metrics">
                                    <div class="metric">
                                        <label>性能评分</label>
                                        <div class="performance-bar">
                                            <div class="performance-fill" style="width: <?php echo $performance * 100; ?>%"></div>
                                            <span class="performance-text"><?php echo round($performance * 100, 1); ?>%</span>
                                        </div>
                                    </div>
                                    
                                    <div class="metric">
                                        <label>能力</label>
                                        <div class="capabilities">
                                            <?php
                                            $capabilities = $swarm_intelligence ? $swarm_intelligence->agents[$agent_id]['capabilities'] ?? [] : [];
                                            foreach (array_slice($capabilities, 0, 3) as $capability):
                                            ?>
                                                <span class="capability-tag"><?php echo $capability; ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($capabilities) > 3): ?>
                                                <span class="capability-tag">+<?php echo count($capabilities) - 3; ?>更多</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="agent-actions">
                                    <button class="button button-small" onclick="viewAgentDetails('<?php echo $agent_id; ?>')">详情</button>
                                    <?php if ($status === 'active'): ?>
                                        <button class="button button-small" onclick="deactivateAgent('<?php echo $agent_id; ?>')">停用</button>
                                    <?php else: ?>
                                        <button class="button button-small button-primary" onclick="activateAgent('<?php echo $agent_id; ?>')">激活</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 协调历史 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>📋 最近协调记录</h2>
                </div>
                <div class="panel-content">
                    <div class="coordination-history">
                        <?php if (!empty($recent_coordinations)): ?>
                            <?php foreach (array_slice($recent_coordinations, 0, 8) as $coordination): ?>
                                <div class="coordination-item">
                                    <div class="coordination-header">
                                        <span class="coordination-id">协调 #<?php echo substr($coordination['execution_id'], -8); ?></span>
                                        <span class="coordination-time"><?php echo human_time_diff(strtotime($coordination['timestamp'])); ?>前</span>
                                    </div>
                                    
                                    <div class="coordination-details">
                                        <div class="task-info">
                                            <strong>任务类型:</strong> 
                                            <span class="task-type"><?php echo $coordination['task_type'] ?? '未知'; ?></span>
                                        </div>
                                        
                                        <div class="agents-involved">
                                            <strong>参与智能体:</strong>
                                            <div class="agent-tags">
                                                <?php
                                                $agents_involved = $coordination['assigned_agents'] ?? [];
                                                foreach (array_slice($agents_involved, 0, 3) as $agent_id => $agent_data):
                                                ?>
                                                    <span class="agent-tag"><?php echo $agents[$agent_id]['name'] ?? $agent_id; ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($agents_involved) > 3): ?>
                                                    <span class="agent-tag">+<?php echo count($agents_involved) - 3; ?>更多</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="coordination-result">
                                            <strong>结果:</strong>
                                            <span class="result-status <?php echo $coordination['consensus_result']['consensus_achieved'] ? 'success' : 'failed'; ?>">
                                                <?php echo $coordination['consensus_result']['consensus_achieved'] ? '达成共识' : '共识失败'; ?>
                                            </span>
                                            <span class="confidence">置信度: <?php echo round(($coordination['consensus_result']['confidence'] ?? 0) * 100, 1); ?>%</span>
                                        </div>
                                    </div>
                                    
                                    <div class="coordination-actions">
                                        <button class="button button-small" onclick="viewCoordinationDetails('<?php echo $coordination['execution_id']; ?>')">查看详情</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data">
                                <p>暂无协调记录</p>
                                <button class="button button-primary" onclick="runCoordination()">执行首次协调</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 蜂群配置 -->
        <div class="wai-panel">
            <div class="panel-header">
                <h2>⚙️ 蜂群配置</h2>
            </div>
            <div class="panel-content">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('wai_swarm_config'); ?>
                    <input type="hidden" name="action" value="wai_update_swarm_config">
                    
                    <div class="form-sections">
                        <!-- 协调设置 -->
                        <div class="form-section">
                            <h3>协调设置</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">协调频率</th>
                                    <td>
                                        <select name="coordination_frequency">
                                            <option value="realtime" <?php selected(get_option('wai_coordination_frequency'), 'realtime'); ?>>实时协调</option>
                                            <option value="hourly" <?php selected(get_option('wai_coordination_frequency'), 'hourly'); ?>>每小时</option>
                                            <option value="daily" <?php selected(get_option('wai_coordination_frequency'), 'daily'); ?>>每天</option>
                                            <option value="manual" <?php selected(get_option('wai_coordination_frequency'), 'manual'); ?>>手动</option>
                                        </select>
                                        <p class="description">蜂群智能体协调执行的频率</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">共识阈值</th>
                                    <td>
                                        <input type="number" name="consensus_threshold" value="<?php echo esc_attr(get_option('wai_consensus_threshold', 70)); ?>" min="50" max="100" step="1">
                                        <span>%</span>
                                        <p class="description">智能体达成共识所需的最小置信度</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">自动任务生成</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="auto_task_generation" value="1" <?php checked(get_option('wai_auto_task_generation'), 1); ?>>
                                            启用自动任务生成
                                        </label>
                                        <p class="description">系统自动检测并生成优化任务</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- 智能体设置 -->
                        <div class="form-section">
                            <h3>智能体设置</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">最大并发智能体</th>
                                    <td>
                                        <input type="number" name="max_concurrent_agents" value="<?php echo esc_attr(get_option('wai_max_concurrent_agents', 3)); ?>" min="1" max="10">
                                        <p class="description">同时执行任务的智能体最大数量</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">性能监控</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="performance_monitoring" value="1" <?php checked(get_option('wai_performance_monitoring'), 1); ?>>
                                            启用性能监控
                                        </label>
                                        <p class="description">监控智能体性能并自动优化</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">智能体学习</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="agent_learning" value="1" <?php checked(get_option('wai_agent_learning'), 1); ?>>
                                            启用持续学习
                                        </label>
                                        <p class="description">智能体从执行结果中学习并改进</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php submit_button('保存配置'); ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 智能体详情模态框 -->
<div id="agent-details-modal" class="wai-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🤖 智能体详情</h3>
            <span class="close" onclick="closeAgentDetailsModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="agent-details-content">
                <!-- 动态加载的智能体详情 -->
            </div>
        </div>
    </div>
</div>

<script>
// 蜂群管理功能
function refreshAgentsStatus() {
    // 刷新智能体状态
    location.reload();
}

function runCoordination() {
    // 执行协调
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '协调中...';
    button.disabled = true;
    
    // 模拟API调用
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=wai_run_coordination&nonce=<?php echo wp_create_nonce('wai_coordination'); ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('协调执行完成！');
            refreshAgentsStatus();
        } else {
            alert('协调执行失败: ' + data.data);
        }
    })
    .catch(error => {
        alert('协调执行错误: ' + error);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

function viewAgentDetails(agentId) {
    // 查看智能体详情
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=wai_get_agent_details&agent_id=' + agentId + '&nonce=<?php echo wp_create_nonce('wai_agent_details'); ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('agent-details-content').innerHTML = data.data.html;
            document.getElementById('agent-details-modal').style.display = 'block';
        } else {
            alert('获取智能体详情失败: ' + data.data);
        }
    })
    .catch(error => {
        alert('获取智能体详情错误: ' + error);
    });
}

function closeAgentDetailsModal() {
    document.getElementById('agent-details-modal').style.display = 'none';
}

function activateAgent(agentId) {
    if (confirm('确定要激活智能体 ' + agentId + ' 吗？')) {
        // 激活智能体
        console.log('激活智能体:', agentId);
        refreshAgentsStatus();
    }
}

function deactivateAgent(agentId) {
    if (confirm('确定要停用智能体 ' + agentId + ' 吗？')) {
        // 停用智能体
        console.log('停用智能体:', agentId);
        refreshAgentsStatus();
    }
}

function viewCoordinationDetails(executionId) {
    // 查看协调详情
    alert('查看协调详情: ' + executionId);
}

// 点击模态框外部关闭
window.onclick = function(event) {
    const modal = document.getElementById('agent-details-modal');
    if (event.target === modal) {
        closeAgentDetailsModal();
    }
}
</script>

<style>
.wai-swarm-management {
    max-width: 1200px;
}

.agents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.agent-card {
    background: white;
    border: 2px solid #f0f0f0;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
}

.agent-card:hover {
    border-color: #007cba;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.agent-card.status-active {
    border-left: 4px solid #46b450;
}

.agent-card.status-inactive {
    border-left: 4px solid #dc3232;
    opacity: 0.7;
}

.agent-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.agent-icon {
    font-size: 2em;
    margin-right: 12px;
}

.agent-info {
    flex: 1;
}

.agent-info h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
}

.agent-id {
    font-size: 12px;
    color: #666;
}

.agent-status .status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.status-badge.active {
    background: #e7f4e4;
    color: #46b450;
}

.status-badge.inactive {
    background: #fbe7e7;
    color: #dc3232;
}

.agent-metrics {
    margin-bottom: 15px;
}

.metric {
    margin-bottom: 10px;
}

.metric label {
    display: block;
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
}

.performance-bar {
    background: #f0f0f0;
    border-radius: 10px;
    height: 20px;
    position: relative;
    overflow: hidden;
}

.performance-fill {
    background: linear-gradient(90deg, #46b450, #8bc34a);
    height: 100%;
    border-radius: 10px;
    transition: width 0.5s ease;
}

.performance-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 11px;
    font-weight: bold;
    color: #333;
}

.capabilities {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.capability-tag {
    background: #e7f3ff;
    color: #0073aa;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 11px;
}

.agent-actions {
    display: flex;
    gap: 8px;
}

.coordination-history {
    max-height: 500px;
    overflow-y: auto;
}

.coordination-item {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
}

.coordination-item:last-child {
    margin-bottom: 0;
}

.coordination-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.coordination-id {
    font-weight: bold;
    font-size: 14px;
}

.coordination-time {
    font-size: 12px;
    color: #888;
}

.coordination-details {
    margin-bottom: 10px;
}

.task-info, .agents-involved, .coordination-result {
    margin-bottom: 6px;
    font-size: 13px;
}

.task-type {
    background: #e7f3ff;
    color: #0073aa;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
}

.agent-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 4px;
}

.agent-tag {
    background: #f0f0f0;
    color: #666;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 11px;
}

.result-status {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.result-status.success {
    background: #e7f4e4;
    color: #46b450;
}

.result-status.failed {
    background: #fbe7e7;
    color: #dc3232;
}

.confidence {
    margin-left: 8px;
    font-size: 12px;
    color: #666;
}

.coordination-actions {
    text-align: right;
}

.form-sections {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.form-section h3 {
    margin-top: 0;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
    color: #23282d;
}

@media (max-width: 1024px) {
    .form-sections {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .agents-grid {
        grid-template-columns: 1fr;
    }
    
    .wai-columns-2 {
        grid-template-columns: 1fr;
    }
}
</style>