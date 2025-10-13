<?php
/**
 * 店铺配置模型 - 店群成员配置和价值优化
 * 文件路径: /wp-content/plugins/woocommerce-ai-agent/includes/models/class-store-profile.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Store_Profile {
    
    private $store_id;
    private $profile_data;
    private $sync_capabilities;
    private $performance_metrics;
    
    public function __construct($store_id) {
        $this->store_id = $store_id;
        $this->load_profile_data();
        $this->analyze_capabilities();
        $this->initialize_metrics();
    }
    
    /**
     * 加载店铺配置数据
     */
    private function load_profile_data() {
        $default_profile = [
            'store_type' => 'woocommerce',
            'status' => 'active',
            'sync_strategies' => [],
            'value_score' => 0.0,
            'optimization_opportunities' => [],
            'last_sync' => null,
            'performance_history' => []
        ];
        
        $saved_profile = get_option("wai_store_profile_{$this->store_id}", []);
        $this->profile_data = wp_parse_args($saved_profile, $default_profile);
    }
    
    /**
     * 分析店铺能力
     */
    private function analyze_capabilities() {
        $this->sync_capabilities = [
            'pricing' => $this->can_sync_pricing(),
            'inventory' => $this->can_sync_inventory(),
            'products' => $this->can_sync_products(),
            'customers' => $this->can_sync_customers(),
            'orders' => $this->can_sync_orders()
        ];
        
        // 计算总体能力评分
        $capability_score = array_sum($this->sync_capabilities) / count($this->sync_capabilities);
        $this->profile_data['capability_score'] = $capability_score;
    }
    
    /**
     * 检查定价同步能力
     */
    private function can_sync_pricing() {
        // 检查是否支持动态定价
        if (function_exists('wc_dynamic_pricing')) {
            return true;
        }
        
        // 检查是否有定价相关插件
        $pricing_plugins = ['woocommerce-dynamic-pricing', 'woocommerce-tiered-pricing'];
        foreach ($pricing_plugins as $plugin) {
            if (is_plugin_active("{$plugin}/{$plugin}.php")) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 检查库存同步能力
     */
    private function can_sync_inventory() {
        // 检查库存管理功能
        if (!function_exists('wc_get_product')) {
            return false;
        }
        
        // 检查是否有高级库存管理
        $inventory_plugins = ['woocommerce-advanced-inventory', 'woocommerce-stock-manager'];
        foreach ($inventory_plugins as $plugin) {
            if (is_plugin_active("{$plugin}/{$plugin}.php")) {
                return true;
            }
        }
        
        return true; // WooCommerce基础库存管理
    }
    
    /**
     * 检查产品同步能力
     */
    private function can_sync_products() {
        // 基础产品管理能力
        if (!function_exists('wc_get_products')) {
            return false;
        }
        
        // 检查产品导入导出能力
        return true;
    }
    
    /**
     * 检查客户同步能力
     */
    private function can_sync_customers() {
        // 检查客户管理功能
        if (!function_exists('wc_get_customers')) {
            return false;
        }
        
        // 检查是否有客户数据插件
        $customer_plugins = ['woocommerce-customer-history', 'woocommerce-loyalty-points'];
        foreach ($customer_plugins as $plugin) {
            if (is_plugin_active("{$plugin}/{$plugin}.php")) {
                return true;
            }
        }
        
        return true;
    }
    
    /**
     * 检查订单同步能力
     */
    private function can_sync_orders() {
        return function_exists('wc_get_orders');
    }
    
    /**
     * 初始化性能指标
     */
    private function initialize_metrics() {
        $this->performance_metrics = [
            'sync_success_rate' => $this->calculate_sync_success_rate(),
            'average_response_time' => $this->get_average_response_time(),
            'error_rate' => $this->calculate_error_rate(),
            'value_contribution' => $this->calculate_value_contribution(),
            'efficiency_score' => $this->calculate_efficiency_score()
        ];
    }
    
    /**
     * 计算同步成功率
     */
    private function calculate_sync_success_rate() {
        $sync_history = $this->profile_data['performance_history'] ?? [];
        
        if (empty($sync_history)) {
            return 0.95; // 默认成功率
        }
        
        $successful_syncs = 0;
        $total_syncs = count($sync_history);
        
        foreach ($sync_history as $sync) {
            if ($sync['status'] === 'success') {
                $successful_syncs++;
            }
        }
        
        return $total_syncs > 0 ? $successful_syncs / $total_syncs : 0.95;
    }
    
    /**
     * 获取平均响应时间
     */
    private function get_average_response_time() {
        $sync_history = $this->profile_data['performance_history'] ?? [];
        
        if (empty($sync_history)) {
            return 1.5; // 默认1.5秒
        }
        
        $total_time = 0;
        $count = 0;
        
        foreach ($sync_history as $sync) {
            if (isset($sync['response_time'])) {
                $total_time += $sync['response_time'];
                $count++;
            }
        }
        
        return $count > 0 ? $total_time / $count : 1.5;
    }
    
    /**
     * 计算错误率
     */
    private function calculate_error_rate() {
        $success_rate = $this->performance_metrics['sync_success_rate'];
        return 1 - $success_rate;
    }
    
    /**
     * 计算价值贡献
     */
    private function calculate_value_contribution() {
        // 基于店铺销售数据和同步贡献计算
        $store_sales = $this->get_store_sales_data();
        $sync_impact = $this->calculate_sync_impact();
        
        return $store_sales * $sync_impact;
    }
    
    /**
     * 计算效率评分
     */
    private function calculate_efficiency_score() {
        $weights = [
            'success_rate' => 0.4,
            'response_time' => 0.3,
            'error_rate' => 0.2,
            'value_contribution' => 0.1
        ];
        
        $score = 0;
        $score += $this->performance_metrics['sync_success_rate'] * $weights['success_rate'] * 100;
        $score += (1 / $this->performance_metrics['average_response_time']) * $weights['response_time'] * 50;
        $score += (1 - $this->performance_metrics['error_rate']) * $weights['error_rate'] * 100;
        $score += min($this->performance_metrics['value_contribution'] / 1000, 1) * $weights['value_contribution'] * 100;
        
        return round($score, 1);
    }
    
    /**
     * 获取店铺销售数据
     */
    private function get_store_sales_data() {
        // 这里可以连接WooCommerce API获取实际销售数据
        // 暂时返回模拟数据
        return rand(5000, 50000);
    }
    
    /**
     * 计算同步影响
     */
    private function calculate_sync_impact() {
        // 基于同步策略的数量和效果计算影响系数
        $strategy_count = count($this->profile_data['sync_strategies']);
        $base_impact = 0.1; // 基础影响系数
        $strategy_multiplier = 0.05; // 每个策略的增量影响
        
        return $base_impact + ($strategy_count * $strategy_multiplier);
    }
    
    /**
     * 获取店铺价值评分
     */
    public function get_value_score() {
        $capability_weight = 0.3;
        $performance_weight = 0.4;
        $sync_activity_weight = 0.3;
        
        $capability_score = $this->profile_data['capability_score'];
        $performance_score = $this->performance_metrics['efficiency_score'] / 100;
        $sync_activity = $this->get_sync_activity_level();
        
        $value_score = ($capability_score * $capability_weight) +
                      ($performance_score * $performance_weight) +
                      ($sync_activity * $sync_activity_weight);
        
        return round($value_score, 3);
    }
    
    /**
     * 获取同步活动水平
     */
    private function get_sync_activity_level() {
        $sync_history = $this->profile_data['performance_history'] ?? [];
        $recent_syncs = 0;
        $thirty_days_ago = strtotime('-30 days');
        
        foreach ($sync_history as $sync) {
            if (strtotime($sync['timestamp']) >= $thirty_days_ago) {
                $recent_syncs++;
            }
        }
        
        // 标准化到0-1范围
        return min($recent_syncs / 30, 1);
    }
    
    /**
     * 获取优化建议
     */
    public function get_optimization_suggestions() {
        $suggestions = [];
        
        // 基于能力分析的建议
        if (!$this->sync_capabilities['pricing']) {
            $suggestions[] = [
                'type' => 'capability',
                'title' => '启用动态定价',
                'description' => '安装动态定价插件以支持价格优化策略',
                'impact' => 'high',
                'estimated_roi' => '15-25%'
            ];
        }
        
        if ($this->performance_metrics['sync_success_rate'] < 0.9) {
            $suggestions[] = [
                'type' => 'performance',
                'title' => '优化同步成功率',
                'description' => '当前同步成功率较低，建议检查网络连接和API配置',
                'impact' => 'medium',
                'estimated_roi' => '5-10%'
            ];
        }
        
        if ($this->performance_metrics['average_response_time'] > 2.0) {
            $suggestions[] = [
                'type' => 'performance',
                'title' => '改善响应时间',
                'description' => '同步响应时间较慢，可能影响实时策略执行',
                'impact' => 'medium',
                'estimated_roi' => '8-12%'
            ];
        }
        
        // 基于价值评分的建议
        $value_score = $this->get_value_score();
        if ($value_score < 0.6) {
            $suggestions[] = [
                'type' => 'strategy',
                'title' => '增加同步策略',
                'description' => '当前同步策略较少，建议部署更多自动化策略',
                'impact' => 'high',
                'estimated_roi' => '20-30%'
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * 更新店铺配置
     */
    public function update_profile($new_data) {
        $this->profile_data = wp_parse_args($new_data, $this->profile_data);
        $this->save_profile();
        $this->analyze_capabilities(); // 重新分析能力
        $this->initialize_metrics(); // 重新计算指标
    }
    
    /**
     * 保存店铺配置
     */
    private function save_profile() {
        update_option("wai_store_profile_{$this->store_id}", $this->profile_data);
    }
    
    /**
     * 记录同步活动
     */
    public function record_sync_activity($sync_data) {
        $activity = [
            'timestamp' => current_time('mysql'),
            'strategy_type' => $sync_data['strategy_type'],
            'status' => $sync_data['status'],
            'response_time' => $sync_data['response_time'] ?? 0,
            'items_processed' => $sync_data['items_processed'] ?? 0,
            'errors' => $sync_data['errors'] ?? []
        ];
        
        // 添加到性能历史
        $this->profile_data['performance_history'][] = $activity;
        
        // 保持历史记录在合理范围内
        if (count($this->profile_data['performance_history']) > 100) {
            array_shift($this->profile_data['performance_history']);
        }
        
        // 更新最后同步时间
        $this->profile_data['last_sync'] = current_time('mysql');
        
        $this->save_profile();
    }
    
    /**
     * 获取店铺配置摘要
     */
    public function get_profile_summary() {
        return [
            'store_id' => $this->store_id,
            'value_score' => $this->get_value_score(),
            'capabilities' => $this->sync_capabilities,
            'performance_metrics' => $this->performance_metrics,
            'active_strategies' => count($this->profile_data['sync_strategies']),
            'optimization_suggestions' => $this->get_optimization_suggestions(),
            'last_activity' => $this->profile_data['last_sync']
        ];
    }
    
    /**
     * 检查是否支持特定策略
     */
    public function supports_strategy($strategy_type) {
        $strategy_capabilities = [
            'pricing_optimization' => 'pricing',
            'inventory_intelligence' => 'inventory',
            'product_syndication' => 'products',
            'customer_unification' => 'customers'
        ];
        
        if (isset($strategy_capabilities[$strategy_type])) {
            return $this->sync_capabilities[$strategy_capabilities[$strategy_type]];
        }
        
        return false;
    }
}