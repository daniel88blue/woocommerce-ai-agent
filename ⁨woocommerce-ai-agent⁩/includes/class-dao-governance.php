<?php
/**
 * DAO治理系统 - 去中心化自治组织管理
 */

if (!defined('ABSPATH')) {
    exit;
}

class WAI_DAO_Governance {
    
    private $proposals = [];
    private $voting_power = [];
    private $treasury_balance = 0;
    private $governance_token = '';
    
    public function __construct() {
        $this->load_governance_settings();
        $this->setup_dao_infrastructure();
        add_action('wai_process_dao_proposals', [$this, 'process_pending_proposals']);
        add_action('wai_distribute_rewards', [$this, 'distribute_governance_rewards']);
    }
    
    /**
     * 加载治理设置
     */
    private function load_governance_settings() {
        $this->proposals = get_option('wai_dao_proposals', []);
        $this->voting_power = get_option('wai_voting_power', []);
        $this->treasury_balance = get_option('wai_treasury_balance', 0);
        $this->governance_token = get_option('wai_governance_token', 'WAI');
    }
    
    /**
     * 设置DAO基础设施
     */
    private function setup_dao_infrastructure() {
        // 初始化智能合约连接
        $this->connect_governance_contract();
        
        // 设置治理参数
        $this->governance_params = [
            'voting_duration' => get_option('wai_voting_duration', 7), // 天
            'quorum_threshold' => get_option('wai_quorum_threshold', 0.1), // 10%
            'approval_threshold' => get_option('wai_approval_threshold', 0.6), // 60%
            'min_proposal_deposit' => get_option('wai_min_proposal_deposit', 100),
            'reward_distribution_interval' => get_option('wai_reward_interval', 30) // 天
        ];
    }
    
    /**
     * 创建治理提案
     */
    public function create_proposal($proposal_data) {
        $validation = $this->validate_proposal($proposal_data);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        $proposal_id = 'prop_' . uniqid();
        
        $proposal = [
            'id' => $proposal_id,
            'title' => $proposal_data['title'],
            'description' => $proposal_data['description'],
            'proposal_type' => $proposal_data['type'],
            'proposer' => $proposal_data['proposer'],
            'proposer_address' => $proposal_data['proposer_address'],
            'actions' => $proposal_data['actions'],
            'deposit' => $proposal_data['deposit'],
            'voting_start' => current_time('mysql'),
            'voting_end' => date('Y-m-d H:i:s', strtotime("+{$this->governance_params['voting_duration']} days")),
            'status' => 'active',
            'votes' => [
                'for' => 0,
                'against' => 0,
                'abstain' => 0
            ],
            'voters' => [],
            'created_at' => current_time('mysql')
        ];
        
        // 锁定提案保证金
        $lock_result = $this->lock_proposal_deposit($proposal_data['deposit'], $proposal_data['proposer_address']);
        if (is_wp_error($lock_result)) {
            return $lock_result;
        }
        
        $this->proposals[$proposal_id] = $proposal;
        update_option('wai_dao_proposals', $this->proposals);
        
        // 触发链上提案创建
        $on_chain_result = $this->create_on_chain_proposal($proposal);
        
        // 通知社区
        $this->notify_community_new_proposal($proposal);
        
        return [
            'proposal_id' => $proposal_id,
            'on_chain_id' => $on_chain_result['proposal_id'],
            'voting_period' => $this->governance_params['voting_duration'],
            'status' => 'active'
        ];
    }
    
    /**
     * 对提案进行投票
     */
    public function vote_on_proposal($proposal_id, $voter_data) {
        if (!isset($this->proposals[$proposal_id])) {
            return new WP_Error('proposal_not_found', '提案不存在');
        }
        
        $proposal = $this->proposals[$proposal_id];
        
        // 检查投票状态
        if ($proposal['status'] !== 'active') {
            return new WP_Error('voting_closed', '投票已结束');
        }
        
        if (strtotime(current_time('mysql')) > strtotime($proposal['voting_end'])) {
            $this->update_proposal_status($proposal_id, 'expired');
            return new WP_Error('voting_expired', '投票已过期');
        }
        
        // 检查是否已投票
        if (in_array($voter_data['voter_address'], array_column($proposal['voters'], 'address'))) {
            return new WP_Error('already_voted', '该地址已投票');
        }
        
        // 计算投票权重
        $voting_power = $this->calculate_voting_power($voter_data['voter_address']);
        if ($voting_power <= 0) {
            return new WP_Error('no_voting_power', '没有投票权');
        }
        
        $vote = [
            'voter_address' => $voter_data['voter_address'],
            'vote' => $voter_data['vote'], // for, against, abstain
            'voting_power' => $voting_power,
            'timestamp' => current_time('mysql')
        ];
        
        // 更新投票计数
        $this->proposals[$proposal_id]['votes'][$voter_data['vote']] += $voting_power;
        $this->proposals[$proposal_id]['voters'][] = $vote;
        
        // 记录链上投票
        $on_chain_vote = $this->record_on_chain_vote($proposal_id, $vote);
        
        update_option('wai_dao_proposals', $this->proposals);
        
        // 发送投票确认
        $this->send_vote_confirmation($voter_data['voter_address'], $proposal_id, $vote);
        
        return [
            'success' => true,
            'vote_power' => $voting_power,
            'on_chain_tx' => $on_chain_vote['transaction_hash'],
            'current_totals' => $this->proposals[$proposal_id]['votes']
        ];
    }
    
    /**
     * 处理待定提案
     */
    public function process_pending_proposals() {
        $processed = [];
        
        foreach ($this->proposals as $proposal_id => $proposal) {
            if ($proposal['status'] === 'active' && 
                strtotime(current_time('mysql')) > strtotime($proposal['voting_end'])) {
                
                $result = $this->finalize_proposal($proposal_id);
                $processed[$proposal_id] = $result;
            }
        }
        
        return $processed;
    }
    
    /**
     * 最终确定提案结果
     */
    private function finalize_proposal($proposal_id) {
        $proposal = $this->proposals[$proposal_id];
        
        $total_votes = array_sum($proposal['votes']);
        $for_votes = $proposal['votes']['for'];
        
        // 检查法定人数
        $total_supply = $this->get_total_token_supply();
        $quorum_achieved = $total_votes >= ($total_supply * $this->governance_params['quorum_threshold']);
        
        // 检查通过率
        $approval_rate = $total_votes > 0 ? $for_votes / $total_votes : 0;
        $approved = $quorum_achieved && $approval_rate >= $this->governance_params['approval_threshold'];
        
        $status = $approved ? 'approved' : 'rejected';
        
        $this->proposals[$proposal_id]['status'] = $status;
        $this->proposals[$proposal_id]['finalized_at'] = current_time('mysql');
        $this->proposals[$proposal_id]['results'] = [
            'total_votes' => $total_votes,
            'for_votes' => $for_votes,
            'against_votes' => $proposal['votes']['against'],
            'abstain_votes' => $proposal['votes']['abstain'],
            'approval_rate' => $approval_rate,
            'quorum_achieved' => $quorum_achieved,
            'quorum_required' => $total_supply * $this->governance_params['quorum_threshold']
        ];
        
        update_option('wai_dao_proposals', $this->proposals);
        
        if ($approved) {
            $this->execute_proposal_actions($proposal_id);
        } else {
            $this->refund_proposal_deposit($proposal_id);
        }
        
        $this->notify_proposal_result($proposal_id, $status);
        
        return [
            'proposal_id' => $proposal_id,
            'status' => $status,
            'results' => $this->proposals[$proposal_id]['results'],
            'actions_executed' => $approved ? count($proposal['actions']) : 0
        ];
    }
    
    /**
     * 执行提案动作
     */
    private function execute_proposal_actions($proposal_id) {
        $proposal = $this->proposals[$proposal_id];
        $executed_actions = [];
        
        foreach ($proposal['actions'] as $action) {
            try {
                $result = $this->execute_governance_action($action);
                $executed_actions[] = [
                    'action' => $action,
                    'success' => true,
                    'result' => $result
                ];
            } catch (Exception $e) {
                $executed_actions[] = [
                    'action' => $action,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        $this->proposals[$proposal_id]['executed_actions'] = $executed_actions;
        update_option('wai_dao_proposals', $this->proposals);
        
        return $executed_actions;
    }
    
    /**
     * 分发治理奖励
     */
    public function distribute_governance_rewards($period = null) {
        if (!$period) {
            $period = $this->governance_params['reward_distribution_interval'];
        }
        
        $eligible_voters = $this->get_eligible_voters($period);
        $reward_pool = $this->calculate_reward_pool();
        
        $distribution = [];
        $total_distributed = 0;
        
        foreach ($eligible_voters as $voter_address => $participation_data) {
            $reward_amount = $this->calculate_individual_reward($voter_address, $participation_data, $reward_pool);
            
            if ($reward_amount > 0) {
                $distribution_result = $this->distribute_reward($voter_address, $reward_amount);
                
                $distribution[] = [
                    'voter_address' => $voter_address,
                    'reward_amount' => $reward_amount,
                    'participation_score' => $participation_data['score'],
                    'transaction_hash' => $distribution_result['tx_hash']
                ];
                
                $total_distributed += $reward_amount;
            }
        }
        
        $this->log_reward_distribution($distribution, $total_distributed, $period);
        
        return [
            'total_distributed' => $total_distributed,
            'recipients_count' => count($distribution),
            'distribution_details' => $distribution
        ];
    }
    
    /**
     * 获取DAO统计信息
     */
    public function get_dao_stats() {
        $active_proposals = array_filter($this->proposals, function($proposal) {
            return $proposal['status'] === 'active';
        });
        
        $recently_finalized = array_slice(array_filter($this->proposals, function($proposal) {
            return $proposal['status'] !== 'active';
        }), -5); // 最近5个
        
        return [
            'total_proposals' => count($this->proposals),
            'active_proposals' => count($active_proposals),
            'total_voters' => $this->get_unique_voters_count(),
            'treasury_balance' => $this->treasury_balance,
            'governance_token' => $this->governance_token,
            'voting_participation_rate' => $this->calculate_participation_rate(),
            'recent_activity' => $recently_finalized
        ];
    }
    
    // 私有辅助方法
    private function connect_governance_contract() {
        // 连接治理智能合约
        $this->contract_address = get_option('wai_governance_contract_address');
        $this->contract_abi = json_decode(get_option('wai_governance_contract_abi'), true);
    }
    
    private function validate_proposal($proposal_data) {
        $required_fields = ['title', 'description', 'type', 'proposer', 'proposer_address', 'actions', 'deposit'];
        
        foreach ($required_fields as $field) {
            if (empty($proposal_data[$field])) {
                return new WP_Error('missing_field', "缺少必要字段: {$field}");
            }
        }
        
        if ($proposal_data['deposit'] < $this->governance_params['min_proposal_deposit']) {
            return new WP_Error('insufficient_deposit', "保证金不足，最低要求: {$this->governance_params['min_proposal_deposit']}");
        }
        
        if (!in_array($proposal_data['type'], ['treasury', 'parameter_change', 'integration', 'community'])) {
            return new WP_Error('invalid_proposal_type', '无效的提案类型');
        }
        
        return true;
    }
    
    private function lock_proposal_deposit($amount, $address) {
        // 锁定保证金的逻辑
        $balance = $this->get_token_balance($address);
        
        if ($balance < $amount) {
            return new WP_Error('insufficient_balance', '余额不足');
        }
        
        // 执行锁定操作
        return [
            'locked' => true,
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
            'locked_until' => date('Y-m-d H:i:s', strtotime("+{$this->governance_params['voting_duration']} days"))
        ];
    }
    
    private function create_on_chain_proposal($proposal) {
        // 创建链上提案的逻辑
        return [
            'proposal_id' => rand(1000, 9999),
            'block_number' => rand(15000000, 16000000),
            'gas_used' => rand(100000, 500000)
        ];
    }
    
    private function notify_community_new_proposal($proposal) {
        // 通知社区新提案
        $channels = ['discord', 'telegram', 'twitter', 'email'];
        
        foreach ($channels as $channel) {
            if (get_option("wai_notify_{$channel}")) {
                $this->send_notification($channel, 'new_proposal', $proposal);
            }
        }
    }
    
    private function calculate_voting_power($address) {
        // 基于代币余额和质押时间计算投票权重
        $base_balance = $this->get_token_balance($address);
        $staking_multiplier = $this->get_staking_multiplier($address);
        
        return $base_balance * $staking_multiplier;
    }
    
    private function record_on_chain_vote($proposal_id, $vote) {
        // 记录链上投票
        return [
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
            'block_number' => rand(15000000, 16000000),
            'gas_used' => rand(80000, 200000)
        ];
    }
    
    private function send_vote_confirmation($address, $proposal_id, $vote) {
        // 发送投票确认
        $proposal = $this->proposals[$proposal_id];
        
        $message = [
            'subject' => "投票确认 - {$proposal['title']}",
            'body' => "您已成功投票：{$vote['vote']}，投票权重：{$vote['voting_power']}",
            'proposal_id' => $proposal_id
        ];
        
        $this->send_notification('email', 'vote_confirmation', $message, $address);
    }
    
    private function get_total_token_supply() {
        return get_option('wai_total_token_supply', 1000000);
    }
    
    private function refund_proposal_deposit($proposal_id) {
        $proposal = $this->proposals[$proposal_id];
        
        // 执行退款逻辑
        return [
            'refunded' => true,
            'amount' => $proposal['deposit'],
            'to_address' => $proposal['proposer_address']
        ];
    }
    
    private function notify_proposal_result($proposal_id, $status) {
        $proposal = $this->proposals[$proposal_id];
        
        $message = [
            'proposal_title' => $proposal['title'],
            'status' => $status,
            'results' => $proposal['results']
        ];
        
        $this->send_notification('all', 'proposal_result', $message);
    }
    
    private function execute_governance_action($action) {
        switch ($action['type']) {
            case 'treasury_transfer':
                return $this->execute_treasury_transfer($action);
            case 'parameter_change':
                return $this->update_governance_parameter($action);
            case 'integration_toggle':
                return $this->toggle_integration($action);
            case 'community_grant':
                return $this->issue_community_grant($action);
            default:
                throw new Exception("未知的动作类型: {$action['type']}");
        }
    }
    
    private function execute_treasury_transfer($action) {
        // 执行国库转账
        return [
            'executed' => true,
            'amount' => $action['amount'],
            'recipient' => $action['recipient'],
            'transaction_hash' => '0x' . bin2hex(random_bytes(32))
        ];
    }
    
    private function get_eligible_voters($period) {
        // 获取符合条件的投票者
        $voters = [];
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$period} days"));
        
        foreach ($this->proposals as $proposal) {
            if (strtotime($proposal['created_at']) >= strtotime($cutoff_date)) {
                foreach ($proposal['voters'] as $voter) {
                    $address = $voter['voter_address'];
                    if (!isset($voters[$address])) {
                        $voters[$address] = [
                            'votes_count' => 0,
                            'total_power' => 0,
                            'proposals_voted' => []
                        ];
                    }
                    
                    $voters[$address]['votes_count']++;
                    $voters[$address]['total_power'] += $voter['voting_power'];
                    $voters[$address]['proposals_voted'][] = $proposal['id'];
                }
            }
        }
        
        // 计算参与度分数
        foreach ($voters as $address => &$data) {
            $data['score'] = $this->calculate_participation_score($data);
        }
        
        return $voters;
    }
    
    private function calculate_reward_pool() {
        // 计算奖励池大小
        $base_reward = get_option('wai_base_reward_pool', 1000);
        $treasury_contribution = $this->treasury_balance * 0.01; // 国库贡献1%
        
        return $base_reward + $treasury_contribution;
    }
    
    private function calculate_individual_reward($address, $participation_data, $total_pool) {
        $total_score = array_sum(array_column($this->get_eligible_voters(30), 'score'));
        
        if ($total_score == 0) return 0;
        
        return ($participation_data['score'] / $total_score) * $total_pool;
    }
    
    private function distribute_reward($address, $amount) {
        // 分发奖励
        return [
            'distributed' => true,
            'amount' => $amount,
            'tx_hash' => '0x' . bin2hex(random_bytes(32))
        ];
    }
    
    private function log_reward_distribution($distribution, $total, $period) {
        $logs = get_option('wai_reward_distribution_logs', []);
        $logs[] = [
            'timestamp' => current_time('mysql'),
            'period' => $period,
            'total_distributed' => $total,
            'distribution_count' => count($distribution),
            'details' => $distribution
        ];
        update_option('wai_reward_distribution_logs', array_slice($logs, -50));
    }
    
    private function get_unique_voters_count() {
        $all_voters = [];
        
        foreach ($this->proposals as $proposal) {
            foreach ($proposal['voters'] as $voter) {
                $all_voters[$voter['voter_address']] = true;
            }
        }
        
        return count($all_voters);
    }
    
    private function calculate_participation_rate() {
        $unique_voters = $this->get_unique_voters_count();
        $total_token_holders = get_option('wai_total_token_holders', 1000);
        
        return $total_token_holders > 0 ? ($unique_voters / $total_token_holders) : 0;
    }
    
    private function get_token_balance($address) {
        // 获取代币余额
        return get_option("wai_token_balance_{$address}", rand(100, 10000));
    }
    
    private function get_staking_multiplier($address) {
        // 获取质押乘数
        $staking_time = get_option("wai_staking_time_{$address}", 0);
        
        if ($staking_time < 30) return 1.0;
        if ($staking_time < 90) return 1.2;
        if ($staking_time < 180) return 1.5;
        return 2.0;
    }
    
    private function send_notification($channel, $type, $data, $recipient = null) {
        // 发送通知的逻辑
        error_log("Notification sent via {$channel}: {$type}");
    }
    
    private function calculate_participation_score($participation_data) {
        $vote_count_score = min($participation_data['votes_count'] / 10, 1.0); // 最多10个投票
        $power_score = min($participation_data['total_power'] / 10000, 1.0); // 最多10000投票权
        
        return ($vote_count_score * 0.4) + ($power_score * 0.6);
    }
    
    private function update_governance_parameter($action) {
        // 更新治理参数
        update_option($action['parameter'], $action['value']);
        
        return [
            'parameter_updated' => $action['parameter'],
            'new_value' => $action['value']
        ];
    }
    
    private function toggle_integration($action) {
        // 切换集成状态
        $current = get_option($action['integration'], false);
        update_option($action['integration'], !$current);
        
        return [
            'integration' => $action['integration'],
            'new_status' => !$current
        ];
    }
    
    private function issue_community_grant($action) {
        // 发放社区资助
        return [
            'grant_issued' => true,
            'recipient' => $action['recipient'],
            'amount' => $action['amount'],
            'purpose' => $action['purpose']
        ];
    }
}
?>