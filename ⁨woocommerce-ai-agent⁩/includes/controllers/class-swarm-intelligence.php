<?php
/**
 * 蜂群智能体控制器 - 基于BuddyPress的分布式智能体管理系统
 * 文件路径: /wp-content/plugins/woocommerce-ai-agent/includes/controllers/class-swarm-intelligence.php
 * 架构说明: 集成BuddyPress群组管理，每个群组代表一个蜂群，会员代表智能体
 */

if (!defined('ABSPATH')) {
    exit;
}

class Swarm_Intelligence_Controller {
    
    private $swarm_groups = [];
    private $agent_members = [];
    private $strategy_discussions = [];
    private $performance_metrics = [];
    
    public function __construct() {
        $this->check_buddypress_dependency();
        $this->initialize_swarm_architecture();
    }
    
    /**
     * 检查BuddyPress依赖
     */
    private function check_buddypress_dependency() {
        if (!function_exists('groups_get_groups')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning"><p>蜂群智能体系统需要BuddyPress插件支持。请安装并激活BuddyPress。</p></div>';
            });
            return;
        }
        $this->initialize_buddypress_integration();
    }
    
    /**
     * 初始化BuddyPress集成
     */
    private function initialize_buddypress_integration() {
        // 注册自定义群组类型
        add_action('bp_groups_register_group_types', function() {
            // 电商策略蜂群
            bp_groups_register_group_type('ecommerce_swarm', [
                'labels' => [
                    'name' => '电商策略蜂群',
                    'singular_name' => '电商蜂群'
                ],
                'has_directory' => true,
                'show_in_create_screen' => true,
                'show_in_list' => true,
                'description' => '专注于电商平台优化策略的智能体蜂群',
                'code' => 'ecommerce_swarm'
            ]);
            
            // 社交媒体蜂群
            bp_groups_register_group_type('social_swarm', [
                'labels' => [
                    'name' => '社交媒体蜂群', 
                    'singular_name' => '社交蜂群'
                ],
                'has_directory' => true,
                'show_in_create_screen' => true,
                'show_in_list' => true,
                'description' => '专注于社交媒体营销策略的智能体蜂群',
                'code' => 'social_swarm'
            ]);
            
            // 数据分析蜂群
            bp_groups_register_group_type('analytics_swarm', [
                'labels' => [
                    'name' => '数据分析蜂群',
                    'singular_name' => '分析蜂群'
                ],
                'has_directory' => true,
                'show_in_create_screen' => true,
                'show_in_list' => true,
                'description' => '专注于数据分析和洞察发现的智能体蜂群',
                'code' => 'analytics_swarm'
            ]);
        });
        
        // 注册成员类型（智能体角色）
        add_action('bp_setup_nav', function() {
            bp_set_member_type_admin_labels([
                'ecommerce_agent' => '电商智能体',
                'social_agent' => '社交智能体', 
                'analytics_agent' => '分析智能体',
                'strategy_agent' => '策略智能体'
            ]);
        });
    }
    
    /**
     * 初始化蜂群架构
     */
    private function initialize_swarm_architecture() {
        $this->swarm_groups = $this->get_or_create_swarm_groups();
        $this->agent_members = $this->get_or_create_agent_members();
        $this->initialize_strategy_forums();
    }
    
    /**
     * 获取或创建蜂群群组
     */
    private function get_or_create_swarm_groups() {
        $groups = [];
        $swarm_configs = [
            [
                'name' => '电商优化蜂群',
                'slug' => 'ecommerce-optimization-swarm',
                'type' => 'ecommerce_swarm',
                'description' => 'Amazon、Shopify、WooCommerce等电商平台策略优化'
            ],
            [
                'name' => '社交媒体蜂群', 
                'slug' => 'social-media-swarm',
                'type' => 'social_swarm',
                'description' => 'Twitter、Facebook、Instagram等社交平台营销策略'
            ],
            [
                'name' => '数据分析蜂群',
                'slug' => 'data-analytics-swarm', 
                'type' => 'analytics_swarm',
                'description' => '销售数据、用户行为、市场趋势分析'
            ],
            [
                'name' => '策略发现蜂群',
                'slug' => 'strategy-discovery-swarm',
                'type' => 'ecommerce_swarm',
                'description' => '全网高性价比策略扫描和评估'
            ]
        ];
        
        foreach ($swarm_configs as $config) {
            $group = $this->find_or_create_group($config);
            if ($group) {
                $groups[$config['type']][] = $group;
                
                // 为群组创建策略讨论论坛（如果bbPress可用）
                $this->create_strategy_forum_for_group($group);
            }
        }
        
        return $groups;
    }
    
    /**
     * 查找或创建群组
     */
    private function find_or_create_group($config) {
        // 查找现有群组
        $existing = groups_get_groups([
            'slug' => $config['slug'],
            'show_hidden' => true
        ]);
        
        if ($existing['total'] > 0) {
            return $existing['groups'][0];
        }
        
        // 创建新群组
        $group_id = groups_create_group([
            'name' => $config['name'],
            'slug' => $config['slug'],
            'description' => $config['description'],
            'status' => 'private',
            'enable_forum' => 1,
            'date_created' => bp_core_current_time(),
            'creator_id' => get_current_user_id()
        ]);
        
        if ($group_id) {
            // 设置群组类型
            bp_groups_set_group_type($group_id, $config['type']);
            
            // 添加群组元数据
            groups_update_groupmeta($group_id, 'swarm_type', $config['type']);
            groups_update_groupmeta($group_id, 'ai_agent_capabilities', $this->get_swarm_capabilities($config['type']));
            groups_update_groupmeta($group_id, 'performance_metrics', $this->initialize_performance_metrics());
            
            return groups_get_group($group_id);
        }
        
        return false;
    }
    
    /**
     * 获取或创建智能体成员
     */
    private function get_or_create_agent_members() {
        $agents = [];
        $agent_configs = [
            // 电商智能体
            [
                'username' => 'amazon_strategy_agent',
                'email' => 'amazon-agent@swarm.system',
                'type' => 'ecommerce_agent',
                'display_name' => 'Amazon策略智能体',
                'capabilities' => ['price_optimization', 'inventory_management', 'review_analysis']
            ],
            [
                'username' => 'shopify_optimization_agent', 
                'email' => 'shopify-agent@swarm.system',
                'type' => 'ecommerce_agent',
                'display_name' => 'Shopify优化智能体',
                'capabilities' => ['product_management', 'sales_analytics', 'customer_segmentation']
            ],
            
            // 社交智能体
            [
                'username' => 'twitter_marketing_agent',
                'email' => 'twitter-agent@swarm.system', 
                'type' => 'social_agent',
                'display_name' => 'Twitter营销智能体',
                'capabilities' => ['content_creation', 'engagement_analysis', 'trend_monitoring']
            ],
            
            // 分析智能体
            [
                'username' => 'data_analytics_agent',
                'email' => 'analytics-agent@swarm.system',
                'type' => 'analytics_agent', 
                'display_name' => '数据分析智能体',
                'capabilities' => ['performance_tracking', 'insight_generation', 'prediction_modeling']
            ]
        ];
        
        foreach ($agent_configs as $config) {
            $agent = $this->find_or_create_agent_member($config);
            if ($agent) {
                $agents[$config['type']][] = $agent;
                
                // 将智能体分配到相应的蜂群群组
                $this->assign_agent_to_swarms($agent, $config['type']);
            }
        }
        
        return $agents;
    }
    
    /**
     * 查找或创建智能体成员
     */
    private function find_or_create_agent_member($config) {
        $user = get_user_by('login', $config['username']);
        
        if (!$user) {
            // 创建智能体用户
            $user_id = wp_create_user(
                $config['username'],
                wp_generate_password(32, true, true),
                $config['email']
            );
            
            if (!is_wp_error($user_id)) {
                // 更新用户信息
                wp_update_user([
                    'ID' => $user_id,
                    'display_name' => $config['display_name'],
                    'role' => 'subscriber'
                ]);
                
                // 设置成员类型
                bp_set_member_type($user_id, $config['type']);
                
                // 存储智能体能力
                update_user_meta($user_id, 'ai_agent_capabilities', $config['capabilities']);
                update_user_meta($user_id, 'agent_status', 'active');
                update_user_meta($user_id, 'last_activity', current_time('mysql'));
                update_user_meta($user_id, 'performance_score', 0.85);
                
                $user = get_user_by('id', $user_id);
            }
        }
        
        return $user;
    }
    
    /**
     * 将智能体分配到蜂群
     */
    private function assign_agent_to_swarms($agent, $agent_type) {
        $swarm_mapping = [
            'ecommerce_agent' => ['ecommerce_swarm', 'strategy_discovery_swarm'],
            'social_agent' => ['social_swarm', 'strategy_discovery_swarm'],
            'analytics_agent' => ['analytics_swarm', 'strategy_discovery_swarm']
        ];
        
        if (isset($swarm_mapping[$agent_type])) {
            foreach ($swarm_mapping[$agent_type] as $swarm_type) {
                if (isset($this->swarm_groups[$swarm_type])) {
                    foreach ($this->swarm_groups[$swarm_type] as $group) {
                        groups_join_group($group->id, $agent->ID);
                        
                        // 设置成员角色（组织者、管理员、成员）
                        groups_promote_member($agent->ID, $group->id, 'admin');
                    }
                }
            }
        }
    }
    
    /**
     * 初始化策略论坛
     */
    private function initialize_strategy_forums() {
        if (!function_exists('bbp_get_forum_post_type')) {
            return; // bbPress不可用
        }
        
        foreach ($this->swarm_groups as $swarm_type => $groups) {
            foreach ($groups as $group) {
                $this->create_strategy_forum_for_group($group);
            }
        }
    }
    
    /**
     * 为群组创建策略论坛
     */
    private function create_strategy_forum_for_group($group) {
        if (!function_exists('bbp_insert_forum')) {
            return;
        }
        
        $forum_id = bbp_insert_forum([
            'post_title' => $group->name . ' - 策略讨论',
            'post_content' => '智能体蜂群策略讨论和优化论坛',
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ]);
        
        if ($forum_id) {
            // 关联论坛和群组
            update_post_meta($forum_id, '_bp_group_ids', [$group->id]);
            groups_update_groupmeta($group->id, 'strategy_forum_id', $forum_id);
        }
    }
    
    /**
     * 获取蜂群性能数据
     */
    public function get_swarm_performance() {
        $performance = [];
        
        foreach ($this->swarm_groups as $swarm_type => $groups) {
            foreach ($groups as $group) {
                $group_performance = $this->calculate_group_performance($group);
                $performance[$group->slug] = $group_performance;
            }
        }
        
        // 总体统计
        $total_agents = 0;
        $active_agents = 0;
        
        foreach ($this->agent_members as $agent_type => $agents) {
            $total_agents += count($agents);
            foreach ($agents as $agent) {
                if (get_user_meta($agent->ID, 'agent_status', true) === 'active') {
                    $active_agents++;
                }
            }
        }
        
        return [
            'total_agents' => $total_agents,
            'active_agents' => $active_agents,
            'swarm_groups' => count($this->swarm_groups, COUNT_RECURSIVE) - count($this->swarm_groups),
            'performance_breakdown' => $performance,
            'overall_score' => $this->calculate_overall_performance($performance)
        ];
    }
    
    /**
     * 计算群组性能
     */
    private function calculate_group_performance($group) {
        $members = groups_get_group_members(['group_id' => $group->id]);
        $total_score = 0;
        $active_count = 0;
        
        foreach ($members['members'] as $member) {
            $status = get_user_meta($member->ID, 'agent_status', true);
            $score = get_user_meta($member->ID, 'performance_score', true);
            
            if ($status === 'active' && $score) {
                $total_score += floatval($score);
                $active_count++;
            }
        }
        
        $avg_score = $active_count > 0 ? $total_score / $active_count : 0;
        
        return [
            'group_name' => $group->name,
            'active_agents' => $active_count,
            'performance_score' => round($avg_score * 100, 1),
            'strategies_active' => $this->get_group_active_strategies($group),
            'last_optimization' => get_group_meta($group->id, 'last_optimization', true)
        ];
    }
    
    /**
     * 部署新策略到蜂群
     */
    public function deploy_strategy_to_swarm($strategy_id, $swarm_type, $parameters = []) {
        if (!isset($this->swarm_groups[$swarm_type])) {
            return [
                'success' => false,
                'message' => '蜂群类型不存在'
            ];
        }
        
        $deployment_results = [];
        
        foreach ($this->swarm_groups[$swarm_type] as $group) {
            $result = $this->deploy_to_group($group, $strategy_id, $parameters);
            $deployment_results[$group->slug] = $result;
            
            // 在群组论坛创建策略讨论主题
            $this->create_strategy_discussion($group, $strategy_id, $parameters);
        }
        
        return [
            'success' => true,
            'deployments' => $deployment_results,
            'total_groups' => count($this->swarm_groups[$swarm_type]),
            'summary' => $this->generate_deployment_summary($deployment_results)
        ];
    }
    
    /**
     * 在群组论坛创建策略讨论
     */
    private function create_strategy_discussion($group, $strategy_id, $parameters) {
        $forum_id = groups_get_groupmeta($group->id, 'strategy_forum_id', true);
        
        if ($forum_id && function_exists('bbp_insert_topic')) {
            $topic_id = bbp_insert_topic([
                'post_parent' => $forum_id,
                'post_title' => '策略部署: ' . $strategy_id,
                'post_content' => $this->generate_strategy_discussion_content($strategy_id, $parameters),
                'post_author' => get_current_user_id()
            ], [
                'forum_id' => $forum_id
            ]);
            
            return $topic_id;
        }
        
        return false;
    }
    
    /**
     * 获取蜂群能力配置
     */
    private function get_swarm_capabilities($swarm_type) {
        $capabilities = [
            'ecommerce_swarm' => [
                'price_optimization',
                'inventory_management', 
                'customer_analytics',
                'sales_forecasting',
                'competitor_tracking'
            ],
            'social_swarm' => [
                'content_creation',
                'engagement_analysis',
                'audience_growth',
                'trend_monitoring',
                'conversion_optimization'
            ],
            'analytics_swarm' => [
                'data_processing',
                'insight_generation',
                'performance_tracking',
                'prediction_modeling',
                'anomaly_detection'
            ]
        ];
        
        return $capabilities[$swarm_type] ?? [];
    }
    
    /**
     * 初始化性能指标
     */
    private function initialize_performance_metrics() {
        return [
            'response_time' => 0,
            'success_rate' => 0,
            'efficiency_score' => 0,
            'strategy_impact' => 0,
            'learning_progress' => 0
        ];
    }
}