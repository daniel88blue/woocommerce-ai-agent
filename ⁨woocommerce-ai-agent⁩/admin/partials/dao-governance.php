<?php
/**
 * DAO治理面板 - 去中心化自治组织管理界面
 * 
 * @package Woocommerce_AI_Agent
 * @subpackage Web3
 * @file admin/partials/dao-governance.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// 检查Web3功能是否启用
if (!get_option('wai_web3_enabled')) {
    echo '<div class="notice notice-warning"><p>Web3功能未启用。请在设置中启用Web3集成以使用DAO治理。</p></div>';
    return;
}

$dao_governance = Woocommerce_AI_Agent_Web3::get_instance()->managers['dao_governance'] ?? null;

// 使用现有的公共方法获取数据
$dao_stats = $dao_governance ? $dao_governance->get_dao_stats() : [];

// 直接使用proposals属性（如果它是公共的），或者通过其他方法获取
// 这里我们假设可以通过get_proposals方法获取，如果没有就使用空数组
if ($dao_governance && method_exists($dao_governance, 'get_proposals')) {
    $all_proposals = $dao_governance->get_proposals();
} else {
    $all_proposals = [];
}

// 手动过滤活跃提案和最近提案
$active_proposals = array_filter($all_proposals, function($proposal) {
    return isset($proposal['status']) && $proposal['status'] === 'active';
});

$recent_proposals = array_slice(array_filter($all_proposals, function($proposal) {
    return isset($proposal['status']) && $proposal['status'] !== 'active';
}), 0, 5);
?>

<div class="wrap wai-dao-governance">
    <h1>🗳️ DAO 治理面板</h1>
    
    <div class="wai-stats-grid">
        <!-- 提案统计 -->
        <div class="wai-stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <h3>总提案数</h3>
                <div class="stat-number"><?php echo $dao_stats['total_proposals'] ?? 0; ?></div>
                <div class="stat-trend"><?php echo count($active_proposals); ?> 个活跃</div>
            </div>
        </div>
        
        <!-- 投票参与 -->
        <div class="wai-stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>投票参与者</h3>
                <div class="stat-number"><?php echo $dao_stats['total_voters'] ?? 0; ?></div>
                <div class="stat-trend">参与率: <?php echo round(($dao_stats['voting_participation_rate'] ?? 0) * 100, 1); ?>%</div>
            </div>
        </div>
        
        <!-- 国库余额 -->
        <div class="wai-stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3>国库余额</h3>
                <div class="stat-number"><?php echo $dao_stats['treasury_balance'] ?? 0; ?> <?php echo $dao_stats['governance_token'] ?? 'WAI'; ?></div>
                <div class="stat-trend">治理代币</div>
            </div>
        </div>
        
        <!-- 治理代币 -->
        <div class="wai-stat-card">
            <div class="stat-icon">🎫</div>
            <div class="stat-content">
                <h3>治理代币</h3>
                <div class="stat-number"><?php echo $dao_stats['governance_token'] ?? 'WAI'; ?></div>
                <div class="stat-trend">社区治理</div>
            </div>
        </div>
    </div>

    <div class="wai-dashboard-content">
        <div class="wai-columns-2">
            <!-- 活跃提案 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>📢 活跃提案</h2>
                    <div class="panel-actions">
                        <button class="button button-primary" onclick="openCreateProposalModal()">创建提案</button>
                        <button class="button" onclick="refreshProposals()">刷新</button>
                    </div>
                </div>
                <div class="panel-content">
                    <div class="proposals-list">
                        <?php if (!empty($active_proposals)): ?>
                            <?php foreach ($active_proposals as $proposal): ?>
                                <div class="proposal-card">
                                    <div class="proposal-header">
                                        <h3 class="proposal-title"><?php echo esc_html($proposal['title'] ?? '未命名提案'); ?></h3>
                                        <div class="proposal-meta">
                                            <span class="proposal-id">#<?php echo substr($proposal['id'] ?? 'unknown', -6); ?></span>
                                            <span class="proposal-type <?php echo $proposal['proposal_type'] ?? 'general'; ?>"><?php echo $proposal['proposal_type'] ?? '一般'; ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="proposal-body">
                                        <p class="proposal-description"><?php echo esc_html(wp_trim_words($proposal['description'] ?? '无描述', 20)); ?></p>
                                        
                                        <div class="proposal-progress">
                                            <div class="progress-info">
                                                <div class="progress-stats">
                                                    <span class="votes-for">👍 <?php echo $proposal['votes']['for'] ?? 0; ?></span>
                                                    <span class="votes-against">👎 <?php echo $proposal['votes']['against'] ?? 0; ?></span>
                                                    <span class="votes-abstain">🤝 <?php echo $proposal['votes']['abstain'] ?? 0; ?></span>
                                                </div>
                                                <div class="time-remaining">
                                                    剩余: <?php 
                                                    if (isset($proposal['voting_end'])) {
                                                        echo human_time_diff(strtotime($proposal['voting_end']));
                                                    } else {
                                                        echo '未知';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            
                                            <div class="progress-bar">
                                                <?php
                                                $votes = $proposal['votes'] ?? ['for' => 0, 'against' => 0, 'abstain' => 0];
                                                $total_votes = array_sum($votes);
                                                $for_percentage = $total_votes > 0 ? ($votes['for'] / $total_votes) * 100 : 0;
                                                $against_percentage = $total_votes > 0 ? ($votes['against'] / $total_votes) * 100 : 0;
                                                $abstain_percentage = $total_votes > 0 ? ($votes['abstain'] / $total_votes) * 100 : 0;
                                                ?>
                                                <div class="progress-segment for" style="width: <?php echo $for_percentage; ?>%"></div>
                                                <div class="progress-segment against" style="width: <?php echo $against_percentage; ?>%"></div>
                                                <div class="progress-segment abstain" style="width: <?php echo $abstain_percentage; ?>%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="proposer-info">
                                            <span class="proposer-label">提案人:</span>
                                            <span class="proposer-address"><?php 
                                                if (isset($proposal['proposer_address'])) {
                                                    echo substr($proposal['proposer_address'], 0, 8) . '...' . substr($proposal['proposer_address'], -6);
                                                } else {
                                                    echo '未知地址';
                                                }
                                            ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="proposal-actions">
                                        <button class="button button-small button-success" onclick="voteOnProposal('<?php echo $proposal['id'] ?? 'unknown'; ?>', 'for')">赞成</button>
                                        <button class="button button-small button-warning" onclick="voteOnProposal('<?php echo $proposal['id'] ?? 'unknown'; ?>', 'against')">反对</button>
                                        <button class="button button-small" onclick="voteOnProposal('<?php echo $proposal['id'] ?? 'unknown'; ?>', 'abstain')">弃权</button>
                                        <button class="button button-small" onclick="viewProposalDetails('<?php echo $proposal['id'] ?? 'unknown'; ?>')">详情</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-proposals">
                                <div class="no-proposals-icon">📋</div>
                                <h3>暂无活跃提案</h3>
                                <p>创建第一个社区治理提案</p>
                                <button class="button button-primary" onclick="openCreateProposalModal()">创建提案</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 最近提案结果 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>📊 最近提案结果</h2>
                </div>
                <div class="panel-content">
                    <div class="proposals-results">
                        <?php if (!empty($recent_proposals)): ?>
                            <?php foreach ($recent_proposals as $proposal): ?>
                                <div class="result-card status-<?php echo $proposal['status'] ?? 'unknown'; ?>">
                                    <div class="result-header">
                                        <h4 class="result-title"><?php echo esc_html($proposal['title'] ?? '未命名提案'); ?></h4>
                                        <div class="result-status <?php echo $proposal['status'] ?? 'unknown'; ?>">
                                            <?php echo ($proposal['status'] ?? 'unknown') === 'approved' ? '✅ 已通过' : '❌ 未通过'; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="result-details">
                                        <div class="result-meta">
                                            <span class="result-date"><?php 
                                                if (isset($proposal['finalized_at'])) {
                                                    echo date('Y-m-d', strtotime($proposal['finalized_at']));
                                                } else {
                                                    echo '未知日期';
                                                }
                                            ?></span>
                                            <span class="result-type"><?php echo $proposal['proposal_type'] ?? '一般'; ?></span>
                                        </div>
                                        
                                        <div class="result-numbers">
                                            <div class="result-number">
                                                <span class="number-label">总票数</span>
                                                <span class="number-value"><?php echo $proposal['results']['total_votes'] ?? 0; ?></span>
                                            </div>
                                            <div class="result-number">
                                                <span class="number-label">赞成率</span>
                                                <span class="number-value"><?php echo round(($proposal['results']['approval_rate'] ?? 0) * 100, 1); ?>%</span>
                                            </div>
                                            <div class="result-number">
                                                <span class="number-label">法定人数</span>
                                                <span class="number-value <?php echo ($proposal['results']['quorum_achieved'] ?? false) ? 'achieved' : 'not-achieved'; ?>">
                                                    <?php echo ($proposal['results']['quorum_achieved'] ?? false) ? '✅' : '❌'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <?php if (($proposal['status'] ?? '') === 'approved' && !empty($proposal['executed_actions'])): ?>
                                            <div class="executed-actions">
                                                <strong>已执行操作:</strong>
                                                <ul>
                                                    <?php foreach (array_slice($proposal['executed_actions'], 0, 2) as $action): ?>
                                                        <li class="<?php echo $action['success'] ? 'success' : 'failed'; ?>">
                                                            <?php echo $action['action']['type'] ?? '未知操作'; ?>
                                                            <?php echo $action['success'] ? '✅' : '❌'; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                    <?php if (count($proposal['executed_actions']) > 2): ?>
                                                        <li>... 还有 <?php echo count($proposal['executed_actions']) - 2; ?> 个操作</li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="result-actions">
                                        <button class="button button-small" onclick="viewProposalDetails('<?php echo $proposal['id'] ?? 'unknown'; ?>')">查看详情</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-results">
                                <p>暂无提案结果</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- DAO配置 -->
        <div class="wai-panel">
            <div class="panel-header">
                <h2>⚙️ DAO配置</h2>
            </div>
            <div class="panel-content">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('wai_dao_settings'); ?>
                    <input type="hidden" name="action" value="wai_update_dao_settings">
                    
                    <div class="form-sections">
                        <!-- 投票设置 -->
                        <div class="form-section">
                            <h3>投票设置</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">投票持续时间</th>
                                    <td>
                                        <input type="number" name="voting_duration" value="<?php echo esc_attr(get_option('wai_voting_duration', 7)); ?>" min="1" max="30">
                                        <span>天</span>
                                        <p class="description">提案投票开放的持续时间</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">法定人数阈值</th>
                                    <td>
                                        <input type="number" name="quorum_threshold" value="<?php echo esc_attr(get_option('wai_quorum_threshold', 10)); ?>" min="1" max="50" step="0.1">
                                        <span>%</span>
                                        <p class="description">提案通过所需的最小投票参与率</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">批准阈值</th>
                                    <td>
                                        <input type="number" name="approval_threshold" value="<?php echo esc_attr(get_option('wai_approval_threshold', 60)); ?>" min="50" max="100" step="0.1">
                                        <span>%</span>
                                        <p class="description">提案通过所需的赞成票比例</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">最小提案保证金</th>
                                    <td>
                                        <input type="number" name="min_proposal_deposit" value="<?php echo esc_attr(get_option('wai_min_proposal_deposit', 100)); ?>" min="0" step="1">
                                        <span><?php echo $dao_stats['governance_token'] ?? 'WAI'; ?></span>
                                        <p class="description">创建提案所需的最低代币数量</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- 奖励设置 -->
                        <div class="form-section">
                            <h3>奖励设置</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">奖励分发间隔</th>
                                    <td>
                                        <input type="number" name="reward_distribution_interval" value="<?php echo esc_attr(get_option('wai_reward_interval', 30)); ?>" min="7" max="90">
                                        <span>天</span>
                                        <p class="description">治理奖励分发的间隔时间</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">基础奖励池</th>
                                    <td>
                                        <input type="number" name="base_reward_pool" value="<?php echo esc_attr(get_option('wai_base_reward_pool', 1000)); ?>" min="0" step="10">
                                        <span><?php echo $dao_stats['governance_token'] ?? 'WAI'; ?></span>
                                        <p class="description">每次分发的基准奖励数量</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">国库贡献比例</th>
                                    <td>
                                        <input type="number" name="treasury_contribution" value="<?php echo esc_attr(get_option('wai_treasury_contribution', 1)); ?>" min="0" max="10" step="0.1">
                                        <span>%</span>
                                        <p class="description">从国库中贡献给奖励池的比例</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">自动奖励分发</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="auto_reward_distribution" value="1" <?php checked(get_option('wai_auto_reward_distribution'), 1); ?>>
                                            启用自动奖励分发
                                        </label>
                                        <p class="description">自动按计划分发治理奖励</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <?php submit_button('保存配置'); ?>
                        <button type="button" class="button button-secondary" onclick="distributeRewards()">立即分发奖励</button>
                        <button type="button" class="button button-secondary" onclick="processPendingProposals()">处理待定提案</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 创建提案模态框 -->
<div id="create-proposal-modal" class="wai-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📢 创建新提案</h3>
            <span class="close" onclick="closeCreateProposalModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="create-proposal-form">
                <div class="form-group">
                    <label for="proposal_title">提案标题</label>
                    <input type="text" id="proposal_title" name="title" required placeholder="输入提案标题">
                </div>
                
                <div class="form-group">
                    <label for="proposal_description">提案描述</label>
                    <textarea id="proposal_description" name="description" rows="4" required placeholder="详细描述您的提案内容..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="proposal_type">提案类型</label>
                    <select id="proposal_type" name="type" required>
                        <option value="">-- 选择提案类型 --</option>
                        <option value="treasury">国库管理</option>
                        <option value="parameter_change">参数变更</option>
                        <option value="integration">集成管理</option>
                        <option value="community">社区建议</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="proposal_deposit">提案保证金</label>
                    <input type="number" id="proposal_deposit" name="deposit" value="<?php echo get_option('wai_min_proposal_deposit', 100); ?>" min="<?php echo get_option('wai_min_proposal_deposit', 100); ?>" step="1">
                    <p class="description">最小保证金: <?php echo get_option('wai_min_proposal_deposit', 100); ?> <?php echo $dao_stats['governance_token'] ?? 'WAI'; ?></p>
                </div>
                
                <div class="form-group" id="actions-section" style="display: none;">
                    <label>提案动作</label>
                    <div id="proposal-actions">
                        <!-- 动态添加的动作 -->
                    </div>
                    <button type="button" class="button button-small" onclick="addAction()">+ 添加动作</button>
                </div>
                
                <div class="form-group">
                    <label for="proposer_address">提案人地址</label>
                    <input type="text" id="proposer_address" name="proposer_address" required placeholder="输入您的钱包地址">
                    <p class="description">此地址将用于提案创建和保证金锁定</p>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="button" onclick="closeCreateProposalModal()">取消</button>
                    <button type="submit" class="button button-primary">创建提案</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 投票模态框 -->
<div id="vote-modal" class="wai-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🗳️ 投票</h3>
            <span class="close" onclick="closeVoteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="vote-content">
                <!-- 动态加载的投票内容 -->
            </div>
        </div>
    </div>
</div>

<script>
// DAO治理功能
let currentProposalId = null;

function openCreateProposalModal() {
    document.getElementById('create-proposal-modal').style.display = 'block';
}

function closeCreateProposalModal() {
    document.getElementById('create-proposal-modal').style.display = 'none';
}

function closeVoteModal() {
    document.getElementById('vote-modal').style.display = 'none';
    currentProposalId = null;
}

function refreshProposals() {
    location.reload();
}

function viewProposalDetails(proposalId) {
    // 查看提案详情
    alert('查看提案详情: ' + proposalId);
}

function voteOnProposal(proposalId, voteType) {
    currentProposalId = proposalId;
    
    const voteLabels = {
        'for': '赞成',
        'against': '反对', 
        'abstain': '弃权'
    };
    
    const voteContent = `
        <div class="vote-form">
            <h4>对提案 #${proposalId.substr(-6)} 投票</h4>
            <p>您选择投票: <strong>${voteLabels[voteType]}</strong></p>
            
            <div class="form-group">
                <label for="voter_address">投票人地址</label>
                <input type="text" id="voter_address" placeholder="输入您的钱包地址" required>
            </div>
            
            <div class="vote-preview">
                <div class="vote-type ${voteType}">
                    <span class="vote-icon">
                        ${voteType === 'for' ? '👍' : voteType === 'against' ? '👎' : '🤝'}
                    </span>
                    <span class="vote-label">${voteLabels[voteType]}</span>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="button" onclick="closeVoteModal()">取消</button>
                <button type="button" class="button button-primary" onclick="submitVote('${voteType}')">确认投票</button>
            </div>
        </div>
    `;
    
    document.getElementById('vote-content').innerHTML = voteContent;
    document.getElementById('vote-modal').style.display = 'block';
}

function submitVote(voteType) {
    const voterAddress = document.getElementById('voter_address').value;
    
    if (!voterAddress) {
        alert('请输入投票人地址');
        return;
    }
    
    // 显示加载状态
    const submitBtn = document.querySelector('#vote-content .button-primary');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = '投票中...';
    submitBtn.disabled = true;
    
    // 模拟API调用
    setTimeout(() => {
        alert('投票提交成功！');
        closeVoteModal();
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        refreshProposals();
    }, 2000);
}

function distributeRewards() {
    if (confirm('确定要立即分发治理奖励吗？')) {
        // 分发奖励
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '分发中...';
        button.disabled = true;
        
        // 模拟API调用
        setTimeout(() => {
            alert('奖励分发完成！');
            button.textContent = originalText;
            button.disabled = false;
        }, 3000);
    }
}

function processPendingProposals() {
    if (confirm('确定要处理所有待定提案吗？')) {
        // 处理提案
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '处理中...';
        button.disabled = true;
        
        // 模拟API调用
        setTimeout(() => {
            alert('提案处理完成！');
            button.textContent = originalText;
            button.disabled = false;
            refreshProposals();
        }, 2000);
    }
}

// 提案类型变化时显示动作部分
document.getElementById('proposal_type').addEventListener('change', function() {
    const actionsSection = document.getElementById('actions-section');
    if (this.value) {
        actionsSection.style.display = 'block';
        updateActionTemplates(this.value);
    } else {
        actionsSection.style.display = 'none';
    }
});

function updateActionTemplates(proposalType) {
    const actionsContainer = document.getElementById('proposal-actions');
    actionsContainer.innerHTML = '';
    
    const templates = {
        'treasury': [
            { type: 'treasury_transfer', label: '国库转账', fields: ['recipient', 'amount'] }
        ],
        'parameter_change': [
            { type: 'parameter_change', label: '参数变更', fields: ['parameter', 'value'] }
        ],
        'integration': [
            { type: 'integration_toggle', label: '集成开关', fields: ['integration'] }
        ],
        'community': [
            { type: 'community_grant', label: '社区资助', fields: ['recipient', 'amount', 'purpose'] }
        ]
    };
    
    const typeTemplates = templates[proposalType] || [];
    typeTemplates.forEach(template => {
        addActionTemplate(template);
    });
}

function addActionTemplate(template) {
    const actionsContainer = document.getElementById('proposal-actions');
    const actionId = 'action_' + Date.now();
    
    const actionHtml = `
        <div class="action-item" id="${actionId}">
            <div class="action-header">
                <strong>${template.label}</strong>
                <button type="button" class="button button-small button-warning" onclick="removeAction('${actionId}')">删除</button>
            </div>
            <input type="hidden" name="actions[][type]" value="${template.type}">
            ${template.fields.map(field => `
                <div class="action-field">
                    <label>${field}</label>
                    <input type="text" name="actions[][${field}]" placeholder="输入 ${field}" required>
                </div>
            `).join('')}
        </div>
    `;
    
    actionsContainer.innerHTML += actionHtml;
}

function addAction() {
    const proposalType = document.getElementById('proposal_type').value;
    const templates = {
        'treasury': { type: 'treasury_transfer', label: '国库转账', fields: ['recipient', 'amount'] },
        'parameter_change': { type: 'parameter_change', label: '参数变更', fields: ['parameter', 'value'] },
        'integration': { type: 'integration_toggle', label: '集成开关', fields: ['integration'] },
        'community': { type: 'community_grant', label: '社区资助', fields: ['recipient', 'amount', 'purpose'] }
    };
    
    const template = templates[proposalType];
    if (template) {
        addActionTemplate(template);
    }
}

function removeAction(actionId) {
    const actionElement = document.getElementById(actionId);
    if (actionElement) {
        actionElement.remove();
    }
}

// 创建提案表单提交
document.getElementById('create-proposal-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // 显示加载状态
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = '创建中...';
    submitBtn.disabled = true;
    
    // 模拟API调用
    setTimeout(() => {
        alert('提案创建请求已提交！');
        closeCreateProposalModal();
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        refreshProposals();
    }, 2000);
});

// 点击模态框外部关闭
window.onclick = function(event) {
    const modals = document.querySelectorAll('.wai-modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            if (modal.id === 'create-proposal-modal') {
                closeCreateProposalModal();
            } else if (modal.id === 'vote-modal') {
                closeVoteModal();
            }
        }
    });
}
</script>

<style>
.wai-dao-governance {
    max-width: 1200px;
}

.proposals-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.proposal-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s ease;
}

.proposal-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.proposal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.proposal-title {
    margin: 0;
    font-size: 18px;
    color: #23282d;
    flex: 1;
}

.proposal-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
}

.proposal-id {
    font-size: 12px;
    color: #666;
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 4px;
}

.proposal-type {
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 8px;
    font-weight: bold;
}

.proposal-type.treasury {
    background: #fff3cd;
    color: #856404;
}

.proposal-type.parameter_change {
    background: #d1ecf1;
    color: #0c5460;
}

.proposal-type.integration {
    background: #d4edda;
    color: #155724;
}

.proposal-type.community {
    background: #e2e3e5;
    color: #383d41;
}

.proposal-body {
    margin-bottom: 15px;
}

.proposal-description {
    margin: 0 0 15px 0;
    color: #666;
    line-height: 1.5;
}

.proposal-progress {
    margin-bottom: 12px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.progress-stats {
    display: flex;
    gap: 15px;
    font-size: 12px;
}

.votes-for { color: #46b450; }
.votes-against { color: #dc3232; }
.votes-abstain { color: #0073aa; }

.time-remaining {
    font-size: 12px;
    color: #666;
}

.progress-bar {
    display: flex;
    height: 8px;
    background: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;
}

.progress-segment {
    height: 100%;
    transition: width 0.3s ease;
}

.progress-segment.for { background: #46b450; }
.progress-segment.against { background: #dc3232; }
.progress-segment.abstain { background: #0073aa; }

.proposer-info {
    font-size: 12px;
    color: #666;
}

.proposer-label {
    font-weight: bold;
}

.proposal-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.button-success {
    background: #46b450;
    border-color: #46b450;
    color: white;
}

.button-success:hover {
    background: #3a9a43;
    border-color: #3a9a43;
}

.no-proposals {
    text-align: center;
    padding: 40px 20px;
}

.no-proposals-icon {
    font-size: 3em;
    margin-bottom: 15px;
}

.no-proposals h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.no-proposals p {
    margin: 0 0 20px 0;
    color: #666;
}

.proposals-results {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.result-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 15px;
}

.result-card.status-approved {
    border-left: 4px solid #46b450;
}

.result-card.status-rejected {
    border-left: 4px solid #dc3232;
}

.result-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.result-title {
    margin: 0;
    font-size: 14px;
    color: #23282d;
    flex: 1;
}

.result-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}

.result-status.approved {
    background: #e7f4e4;
    color: #46b450;
}

.result-status.rejected {
    background: #fbe7e7;
    color: #dc3232;
}

.result-details {
    margin-bottom: 10px;
}

.result-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 12px;
    color: #666;
}

.result-numbers {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 10px;
}

.result-number {
    text-align: center;
    padding: 8px;
    background: #f9f9f9;
    border-radius: 4px;
}

.number-label {
    display: block;
    font-size: 11px;
    color: #666;
    margin-bottom: 4px;
}

.number-value {
    display: block;
    font-size: 14px;
    font-weight: bold;
    color: #23282d;
}

.number-value.achieved { color: #46b450; }
.number-value.not-achieved { color: #dc3232; }

.executed-actions {
    font-size: 12px;
}

.executed-actions ul {
    margin: 5px 0 0 0;
    padding-left: 15px;
}

.executed-actions li {
    margin-bottom: 2px;
}

.executed-actions li.success { color: #46b450; }
.executed-actions li.failed { color: #dc3232; }

.result-actions {
    text-align: right;
}

.no-results {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.form-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

/* 动作表单样式 */
.action-item {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
}

.action-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e0e0e0;
}

.action-field {
    margin-bottom: 8px;
}

.action-field label {
    display: block;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 4px;
    color: #666;
}

.action-field input {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

/* 投票表单样式 */
.vote-form {
    padding: 10px 0;
}

.vote-preview {
    text-align: center;
    margin: 20px 0;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.vote-type {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    border-radius: 20px;
    font-size: 16px;
    font-weight: bold;
}

.vote-type.for {
    background: #e7f4e4;
    color: #46b450;
}

.vote-type.against {
    background: #fbe7e7;
    color: #dc3232;
}

.vote-type.abstain {
    background: #e7f3ff;
    color: #0073aa;
}

.vote-icon {
    font-size: 1.5em;
}

@media (max-width: 768px) {
    .proposal-header {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    
    .proposal-meta {
        align-items: flex-start;
        flex-direction: row;
        gap: 10px;
    }
    
    .progress-info {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    
    .progress-stats {
        justify-content: space-between;
    }
    
    .proposal-actions {
        justify-content: center;
    }
    
    .result-header {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    
    .result-numbers {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>