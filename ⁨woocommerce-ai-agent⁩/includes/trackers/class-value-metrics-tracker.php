<?php
/**
 * 价值指标追踪器 - 策略ROI和性能指标监控系统
 * 文件路径: /wp-content/plugins/woocommerce-ai-agent/includes/trackers/class-value-metrics-tracker.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Value_Metrics_Tracker {
    
    private $metrics_data = [];
    private $strategy_performance = [];
    private $roi_calculations = [];
    private $comparison_benchmarks = [];
    
    public function __construct() {
        $this->load_historical_data();
        $this->initialize_tracking_system();
        $this->setup_benchmarks();
    }
    
    /**
     * 初始化追踪系统
     */
    private function initialize_tracking_system() {
        // 注册自定义数据库表
        $this->create_metrics_tables();
        
        // 设置定时任务
        add_action('wai_daily_metrics_cron', [$this, 'collect_daily_metrics']);
        
        if (!wp_next_scheduled('wai_daily_metrics_cron')) {
            wp_schedule_event(time(), 'daily', 'wai_daily_metrics_cron');
        }
        
        // 注册shutdown钩子用于实时追踪
        add_action('shutdown', [$this, 'track_real_time_metrics']);
    }
    
    /**
     * 创建指标追踪表
     */
    private function create_metrics_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $tables = [
            "{$wpdb->prefix}wai_strategy_metrics" => "
                CREATE TABLE {$wpdb->prefix}wai_strategy_metrics (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    strategy_id varchar(100) NOT NULL,
                    strategy_name text NOT NULL,
                    implementation_date datetime NOT NULL,
                    cost_invested decimal(10,2) DEFAULT 0.00,
                    revenue_generated decimal(10,2) DEFAULT 0.00,
                    roi_percentage decimal(5,2) DEFAULT 0.00,
                    success_rate decimal(5,2) DEFAULT 0.00,
                    performance_score decimal(5,2) DEFAULT 0.00,
                    active_days int(11) DEFAULT 0,
                    status varchar(20) DEFAULT 'active',
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY strategy_id (strategy_id),
                    KEY implementation_date (implementation_date),
                    KEY status (status)
                ) $charset_collate;",
                
            "{$wpdb->prefix}wai_swarm_performance" => "
                CREATE TABLE {$wpdb->prefix}wai_swarm_performance (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    swarm_type varchar(50) NOT NULL,
                    swarm_name varchar(100) NOT NULL,
                    agent_count int(11) DEFAULT 0,
                    active_agents int(11) DEFAULT 0,
                    tasks_completed int(11) DEFAULT 0,
                    success_rate decimal(5,2) DEFAULT 0.00,
                    avg_response_time decimal(8,2) DEFAULT 0.00,
                    efficiency_score decimal(5,2) DEFAULT 0.00,
                    recorded_date date NOT NULL,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY swarm_type (swarm_type),
                    KEY recorded_date (recorded_date)
                ) $charset_collate;",
                
            "{$wpdb->prefix}wai_cost_effectiveness" => "
                CREATE TABLE {$wpdb->prefix}wai_cost_effectiveness (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    metric_date date NOT NULL,
                    category varchar(50) NOT NULL,
                    total_cost decimal(10,2) DEFAULT 0.00,
                    total_value decimal(10,2) DEFAULT 0.00,
                    effectiveness_ratio decimal(5,2) DEFAULT 0.00,
                    comparison_prev_period decimal(5,2) DEFAULT 0.00,
                    trend_direction varchar(10) DEFAULT 'stable',
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY date_category (metric_date, category)
                ) $charset_collate;",
                
            "{$wpdb->prefix}wai_optimization_impact" => "
                CREATE TABLE {$wpdb->prefix}wai_optimization_impact (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    optimization_id varchar(100) NOT NULL,
                    optimization_type varchar(50) NOT NULL,
                    applied_date datetime NOT NULL,
                    before_metrics text NOT NULL,
                    after_metrics text NOT NULL,
                    performance_improvement decimal(5,2) DEFAULT 0.00,
                    cost_savings decimal(10,2) DEFAULT 0.00,
                    impact_score decimal(5,2) DEFAULT 0.00,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY optimization_type (optimization_type),
                    KEY applied_date (applied_date)
                ) $charset_collate;"
        ];
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($tables as $table_name => $sql) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                dbDelta($sql);
            }
        }
    }
    
    /**
     * 设置基准指标
     */
    private function setup_benchmarks() {
        $this->comparison_benchmarks = [
            'roi_targets' => [
                'strategic' => 0.20,  // 20% ROI
                'tactical' => 0.15,   // 15% ROI  
                'operational' => 0.10 // 10% ROI
            ],
            'performance_thresholds' => [
                'excellent' => 0.90,
                'good' => 0.75,
                'acceptable' => 0.60,
                'poor' => 0.50
            ],
            'cost_effectiveness' => [
                'high' => 0.80,
                'medium' => 0.60,
                'low' => 0.40
            ]
        ];
        
        update_option('wai_performance_benchmarks', $this->comparison_benchmarks);
    }
    
    /**
     * 记录策略实施指标
     */
    public function record_strategy_metrics($strategy_data) {
        global $wpdb;
        
        $defaults = [
            'cost_invested' => 0.00,
            'revenue_generated' => 0.00,
            'success_rate' => 0.00,
            'performance_score' => 0.00,
            'active_days' => 1,
            'status' => 'active'
        ];
        
        $data = wp_parse_args($strategy_data, $defaults);
        
        // 计算ROI
        if ($data['cost_invested'] > 0) {
            $data['roi_percentage'] = (($data['revenue_generated'] - $data['cost_invested']) / $data['cost_invested']) * 100;
        } else {
            $data['roi_percentage'] = 0.00;
        }
        
        $result = $wpdb->insert(
            "{$wpdb->prefix}wai_strategy_metrics",
            $data,
            ['%s', '%s', '%s', '%f', '%f', '%f', '%f', '%f', '%d', '%s', '%s', '%s']
        );
        
        if ($result) {
            $this->update_strategy_performance_cache();
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * 记录蜂群性能指标
     */
    public function record_swarm_performance($swarm_data) {
        global $wpdb;
        
        $defaults = [
            'recorded_date' => current_time('mysql'),
            'agent_count' => 0,
            'active_agents' => 0,
            'tasks_completed' => 0,
            'success_rate' => 0.00,
            'avg_response_time' => 0.00,
            'efficiency_score' => 0.00
        ];
        
        $data = wp_parse_args($swarm_data, $defaults);
        
        $result = $wpdb->insert(
            "{$wpdb->prefix}wai_swarm_performance",
            $data,
            ['%s', '%s', '%d', '%d', '%d', '%f', '%f', '%f', '%s', '%s']
        );
        
        return $result !== false;
    }
    
    /**
     * 计算价值指标
     */
    public function calculate_value_metrics($timeframe = '30days') {
        global $wpdb;
        
        $date_range = $this->get_date_range($timeframe);
        
        $metrics = [
            'timeframe' => $timeframe,
            'date_range' => $date_range,
            'strategy_performance' => $this->get_strategy_performance_summary($date_range),
            'swarm_efficiency' => $this->get_swarm_efficiency_summary($date_range),
            'cost_effectiveness' => $this->get_cost_effectiveness_summary($date_range),
            'roi_analysis' => $this->get_roi_analysis($date_range),
            'trends' => $this->calculate_trends($date_range)
        ];
        
        // 计算总体评分
        $metrics['overall_score'] = $this->calculate_overall_score($metrics);
        
        // 缓存结果
        $this->metrics_data = $metrics;
        set_transient('wai_value_metrics_' . $timeframe, $metrics, HOUR_IN_SECONDS);
        
        return $metrics;
    }
    
    /**
     * 获取策略性能摘要
     */
    private function get_strategy_performance_summary($date_range) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                COUNT(*) as total_strategies,
                AVG(roi_percentage) as avg_roi,
                AVG(success_rate) as avg_success_rate,
                AVG(performance_score) as avg_performance,
                SUM(cost_invested) as total_investment,
                SUM(revenue_generated) as total_revenue,
                SUM(revenue_generated - cost_invested) as net_profit
            FROM {$wpdb->prefix}wai_strategy_metrics 
            WHERE implementation_date BETWEEN %s AND %s 
            AND status = 'active'
        ", $date_range['start'], $date_range['end']));
        
        if ($results) {
            $data = (array) $results[0];
            
            // 计算额外指标
            $data['profit_margin'] = $data['total_revenue'] > 0 ? 
                ($data['net_profit'] / $data['total_revenue']) * 100 : 0;
                
            $data['investment_efficiency'] = $data['total_investment'] > 0 ?
                ($data['net_profit'] / $data['total_investment']) * 100 : 0;
                
            return $data;
        }
        
        return [];
    }
    
    /**
     * 获取蜂群效率摘要
     */
    private function get_swarm_efficiency_summary($date_range) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                swarm_type,
                COUNT(*) as record_count,
                AVG(agent_count) as avg_agents,
                AVG(active_agents) as avg_active_agents,
                AVG(success_rate) as avg_success_rate,
                AVG(avg_response_time) as avg_response_time,
                AVG(efficiency_score) as avg_efficiency
            FROM {$wpdb->prefix}wai_swarm_performance 
            WHERE recorded_date BETWEEN %s AND %s
            GROUP BY swarm_type
        ", $date_range['start'], $date_range['end']));
        
        $summary = [
            'total_agents' => 0,
            'active_agents' => 0,
            'overall_efficiency' => 0,
            'by_swarm_type' => []
        ];
        
        foreach ($results as $row) {
            $summary['by_swarm_type'][$row->swarm_type] = (array) $row;
            $summary['total_agents'] += $row->avg_agents;
            $summary['active_agents'] += $row->avg_active_agents;
            $summary['overall_efficiency'] += $row->avg_efficiency;
        }
        
        if (count($results) > 0) {
            $summary['overall_efficiency'] /= count($results);
        }
        
        return $summary;
    }
    
    /**
     * 获取成本效益摘要
     */
    private function get_cost_effectiveness_summary($date_range) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                category,
                AVG(total_cost) as avg_cost,
                AVG(total_value) as avg_value,
                AVG(effectiveness_ratio) as avg_effectiveness,
                trend_direction
            FROM {$wpdb->prefix}wai_cost_effectiveness 
            WHERE metric_date BETWEEN %s AND %s
            GROUP BY category
        ", $date_range['start'], $date_range['end']));
        
        $summary = [
            'total_cost' => 0,
            'total_value' => 0,
            'overall_effectiveness' => 0,
            'by_category' => []
        ];
        
        foreach ($results as $row) {
            $summary['by_category'][$row->category] = (array) $row;
            $summary['total_cost'] += $row->avg_cost;
            $summary['total_value'] += $row->avg_value;
            $summary['overall_effectiveness'] += $row->avg_effectiveness;
        }
        
        if (count($results) > 0) {
            $summary['overall_effectiveness'] /= count($results);
        }
        
        return $summary;
    }
    
    /**
     * ROI分析
     */
    private function get_roi_analysis($date_range) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                CASE 
                    WHEN roi_percentage >= 20 THEN 'high'
                    WHEN roi_percentage >= 10 THEN 'medium' 
                    ELSE 'low'
                END as roi_category,
                COUNT(*) as strategy_count,
                AVG(roi_percentage) as avg_roi,
                SUM(cost_invested) as total_investment,
                SUM(revenue_generated) as total_revenue
            FROM {$wpdb->prefix}wai_strategy_metrics 
            WHERE implementation_date BETWEEN %s AND %s
            AND status = 'active'
            GROUP BY roi_category
            ORDER BY avg_roi DESC
        ", $date_range['start'], $date_range['end']));
        
        $analysis = [
            'high_roi_strategies' => 0,
            'medium_roi_strategies' => 0,
            'low_roi_strategies' => 0,
            'total_investment' => 0,
            'total_return' => 0,
            'overall_roi' => 0
        ];
        
        foreach ($results as $row) {
            $analysis[$row->roi_category . '_roi_strategies'] = $row->strategy_count;
            $analysis['total_investment'] += $row->total_investment;
            $analysis['total_return'] += $row->total_revenue;
        }
        
        if ($analysis['total_investment'] > 0) {
            $analysis['overall_roi'] = (($analysis['total_return'] - $analysis['total_investment']) / $analysis['total_investment']) * 100;
        }
        
        return $analysis;
    }
    
    /**
     * 计算趋势
     */
    private function calculate_trends($date_range) {
        global $wpdb;
        
        // 获取当前期数据
        $current = $this->get_strategy_performance_summary($date_range);
        
        // 获取上一期数据对比
        $previous_range = $this->get_previous_period_range($date_range);
        $previous = $this->get_strategy_performance_summary($previous_range);
        
        $trends = [];
        
        if (!empty($current) && !empty($previous)) {
            $metrics_to_compare = ['avg_roi', 'avg_success_rate', 'net_profit', 'profit_margin'];
            
            foreach ($metrics_to_compare as $metric) {
                if (isset($current[$metric]) && isset($previous[$metric]) && $previous[$metric] != 0) {
                    $change = (($current[$metric] - $previous[$metric]) / abs($previous[$metric])) * 100;
                    $trends[$metric] = [
                        'current' => $current[$metric],
                        'previous' => $previous[$metric],
                        'change' => round($change, 2),
                        'direction' => $change >= 0 ? 'up' : 'down'
                    ];
                }
            }
        }
        
        return $trends;
    }
    
    /**
     * 计算总体评分
     */
    private function calculate_overall_score($metrics) {
        $weights = [
            'roi' => 0.35,
            'efficiency' => 0.25,
            'cost_effectiveness' => 0.20,
            'trend' => 0.20
        ];
        
        $score = 0;
        
        // ROI评分 (0-35分)
        $roi_score = min(35, ($metrics['roi_analysis']['overall_roi'] / 50) * 35);
        $score += $roi_score;
        
        // 效率评分 (0-25分)
        $efficiency_score = $metrics['swarm_efficiency']['overall_efficiency'] * 25;
        $score += $efficiency_score;
        
        // 成本效益评分 (0-20分)
        $cost_effectiveness_score = $metrics['cost_effectiveness']['overall_effectiveness'] * 20;
        $score += $cost_effectiveness_score;
        
        // 趋势评分 (0-20分)
        $trend_score = $this->calculate_trend_score($metrics['trends']);
        $score += $trend_score;
        
        return round($score);
    }
    
    /**
     * 计算趋势评分
     */
    private function calculate_trend_score($trends) {
        $positive_changes = 0;
        $total_metrics = count($trends);
        
        foreach ($trends as $trend) {
            if ($trend['direction'] === 'up') {
                $positive_changes++;
            }
        }
        
        if ($total_metrics > 0) {
            return ($positive_changes / $total_metrics) * 20;
        }
        
        return 10; // 默认分数
    }
    
    /**
     * 获取日期范围
     */
    private function get_date_range($timeframe) {
        $end_date = current_time('mysql');
        
        switch ($timeframe) {
            case '7days':
                $start_date = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;
            case '30days':
                $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case '90days':
                $start_date = date('Y-m-d H:i:s', strtotime('-90 days'));
                break;
            default:
                $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        }
        
        return ['start' => $start_date, 'end' => $end_date];
    }
    
    /**
     * 获取上一期日期范围
     */
    private function get_previous_period_range($current_range) {
        $duration = strtotime($current_range['end']) - strtotime($current_range['start']);
        
        $previous_end = $current_range['start'];
        $previous_start = date('Y-m-d H:i:s', strtotime($previous_end) - $duration);
        
        return ['start' => $previous_start, 'end' => $previous_end];
    }
    
    /**
     * 生成价值报告
     */
    public function generate_value_report($timeframe = '30days') {
        $metrics = $this->calculate_value_metrics($timeframe);
        
        ob_start();
        ?>
        <div class="wai-value-report">
            <div class="report-header">
                <h3>💰 价值实现报告</h3>
                <div class="timeframe">时间段: <?php echo $timeframe; ?></div>
            </div>
            
            <div class="overall-score">
                <div class="score-card">
                    <div class="score-value"><?php echo $metrics['overall_score']; ?>/100</div>
                    <div class="score-label">总体价值评分</div>
                </div>
            </div>
            
            <div class="metrics-grid">
                <div class="metric-card">
                    <h4>📈 ROI分析</h4>
                    <div class="metric-value"><?php echo round($metrics['roi_analysis']['overall_roi'], 1); ?>%</div>
                    <div class="metric-detail">
                        高ROI策略: <?php echo $metrics['roi_analysis']['high_roi_strategies']; ?>个
                    </div>
                </div>
                
                <div class="metric-card">
                    <h4>⚡ 蜂群效率</h4>
                    <div class="metric-value"><?php echo round($metrics['swarm_efficiency']['overall_efficiency'] * 100, 1); ?>%</div>
                    <div class="metric-detail">
                        活跃智能体: <?php echo $metrics['swarm_efficiency']['active_agents']; ?>个
                    </div>
                </div>
                
                <div class="metric-card">
                    <h4>🎯 成本效益</h4>
                    <div class="metric-value"><?php echo round($metrics['cost_effectiveness']['overall_effectiveness'] * 100, 1); ?>%</div>
                    <div class="metric-detail">
                        总价值: $<?php echo number_format($metrics['cost_effectiveness']['total_value'], 2); ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($metrics['trends'])): ?>
            <div class="trends-section">
                <h4>📊 趋势分析</h4>
                <div class="trends-grid">
                    <?php foreach ($metrics['trends'] as $metric => $trend): ?>
                    <div class="trend-item <?php echo $trend['direction']; ?>">
                        <span class="trend-metric"><?php echo $metric; ?></span>
                        <span class="trend-change"><?php echo $trend['change']; ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .wai-value-report {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .overall-score {
            text-align: center;
            margin-bottom: 30px;
        }
        .score-card {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 50px;
            border-radius: 10px;
        }
        .score-value {
            font-size: 48px;
            font-weight: bold;
        }
        .score-label {
            font-size: 16px;
            opacity: 0.9;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .metric-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
            margin: 10px 0;
        }
        .trends-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .trend-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-radius: 4px;
            background: #f8f9fa;
        }
        .trend-item.up {
            border-left: 3px solid #46b450;
        }
        .trend-item.down {
            border-left: 3px solid #dc3232;
        }
        .trend-change {
            font-weight: bold;
        }
        .trend-item.up .trend-change {
            color: #46b450;
        }
        .trend-item.down .trend-change {
            color: #dc3232;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 收集每日指标
     */
    public function collect_daily_metrics() {
        // 收集策略性能指标
        $this->record_daily_strategy_metrics();
        
        // 收集蜂群性能指标  
        $this->record_daily_swarm_metrics();
        
        // 计算成本效益
        $this->calculate_daily_cost_effectiveness();
        
        // 清理旧数据
        $this->cleanup_old_data();
    }
    
    /**
     * 追踪实时指标
     */
    public function track_real_time_metrics() {
        // 在shutdown时记录关键性能指标
        $this->record_performance_snapshot();
    }
    
    /**
     * 加载历史数据
     */
    private function load_historical_data() {
        $this->metrics_data = get_transient('wai_value_metrics_30days');
        
        if (!$this->metrics_data) {
            $this->metrics_data = $this->calculate_value_metrics('30days');
        }
    }
    
    /**
     * 更新策略性能缓存
     */
    private function update_strategy_performance_cache() {
        // 清除缓存，强制下次重新计算
        delete_transient('wai_value_metrics_7days');
        delete_transient('wai_value_metrics_30days');
        delete_transient('wai_value_metrics_90days');
    }
}