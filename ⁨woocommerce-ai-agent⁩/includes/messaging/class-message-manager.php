<?php
/**
 * 消息平台管理器
 */

if (!defined('ABSPATH')) {
    exit;
}

class WAI_Message_Manager {
    
    private static $instance = null;
    private $platforms = array();
    private $command_processor;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_platforms();
        // 先注释掉这行，等创建了Command_Processor类再取消注释
        // $this->command_processor = new WAI_Command_Processor();
    }
    
    /**
     * 加载所有配置的消息平台
     */
    private function load_platforms() {
        $platform_configs = get_option('wai_message_platforms', array());
        
        // 如果没有配置，使用默认配置
        if (empty($platform_configs)) {
            $platform_configs = array(
                'wechat' => array('enabled' => false),
                'slack' => array('enabled' => false),
                'discord' => array('enabled' => false)
            );
        }
        
        foreach ($platform_configs as $platform_id => $config) {
            if ($config['enabled']) {
                $platform_class = "WAI_" . ucfirst($platform_id) . "_Platform";
                if (class_exists($platform_class)) {
                    $this->platforms[$platform_id] = new $platform_class($config);
                }
            }
        }
    }
    
    /**
     * 发送消息到所有平台
     */
    public function broadcast_message($content, $type = 'text', $options = array()) {
        $results = array();
        
        foreach ($this->platforms as $platform_id => $platform) {
            if ($platform->is_connected()) {
                $results[$platform_id] = $platform->send_message($content, $type, $options);
            }
        }
        
        return $results;
    }
    
    /**
     * 发送消息到指定平台
     */
    public function send_to_platform($platform_id, $content, $type = 'text', $options = array()) {
        if (isset($this->platforms[$platform_id])) {
            return $this->platforms[$platform_id]->send_message($content, $type, $options);
        }
        return false;
    }
    
    /**
     * 处理来自平台的消息
     */
    public function handle_incoming_message($platform_id, $input_data) {
        if (!isset($this->platforms[$platform_id])) {
            return false;
        }
        
        $message = $this->platforms[$platform_id]->receive_message($input_data);
        
        // 处理命令
        if ($message['type'] === 'command') {
            return $this->command_processor->process_command($message);
        }
        
        return $message;
    }
    
    /**
     * 发送店铺状态报告
     */
    public function send_store_report($platform_id = null) {
        $report = $this->generate_store_report();
        
        if ($platform_id) {
            return $this->send_to_platform($platform_id, $report, 'markdown');
        } else {
            return $this->broadcast_message($report, 'markdown');
        }
    }
    
    /**
     * 发送系统警报
     */
    public function send_alert($message, $level = 'warning', $platform_id = null) {
        $icons = array(
            'warning' => '⚠️',
            'error' => '❌', 
            'success' => '✅',
            'info' => 'ℹ️'
        );
        
        $icon = $icons[$level] ?? '📢';
        $content = "{$icon} **系统警报**\n\n{$message}";
        
        if ($platform_id) {
            return $this->send_to_platform($platform_id, $content, 'text');
        } else {
            return $this->broadcast_message($content, 'text');
        }
    }
    
    /**
     * 生成店铺报告
     */
    private function generate_store_report() {
        $store_data = $this->get_store_data();
        $performance = $this->get_performance_metrics();
        
        $content = "🏪 **店铺状态报告**\n\n";
        $content .= "📊 **今日数据**\n";
        $content .= "• 销售额: ¥" . number_format($store_data['total_revenue'], 2) . "\n";
        $content .= "• 订单数: " . $store_data['orders_count'] . "\n";
        $content .= "• 转化率: " . $store_data['conversion_rate'] . "%\n\n";
        
        $content .= "🚀 **性能指标**\n";
        $content .= "• 评分: " . $performance['performance_score'] . "/100\n";
        $content .= "• 响应时间: " . $performance['response_time'] . "ms\n";
        $content .= "• 内存使用: " . $performance['memory_usage'] . "MB\n\n";
        
        $content .= "💡 **AI建议**\n";
        $content .= $this->get_ai_recommendations();
        
        return $content;
    }
    
    /**
     * 获取店铺数据
     */
    private function get_store_data() {
        // 实现获取店铺数据的逻辑
        return array(
            'total_revenue' => 12580.50,
            'orders_count' => 23,
            'conversion_rate' => 2.8
        );
    }
    
    /**
     * 获取性能指标
     */
    private function get_performance_metrics() {
        // 实现获取性能指标的逻辑
        return array(
            'performance_score' => 87,
            'response_time' => 156,
            'memory_usage' => 128
        );
    }
    
    /**
     * 获取AI建议
     */
    private function get_ai_recommendations() {
        // 实现获取AI建议的逻辑
        return "建议优化产品描述，提升转化率";
    }
    
    /**
     * 获取所有平台状态
     */
    public function get_platforms_status() {
        $status = array();
        
        // 如果没有平台，返回默认状态
        if (empty($this->platforms)) {
            return array(
                'wechat' => array(
                    'name' => '企业微信机器人',
                    'connected' => false,
                    'config' => array()
                ),
                'slack' => array(
                    'name' => 'Slack Webhook', 
                    'connected' => false,
                    'config' => array()
                ),
                'discord' => array(
                    'name' => 'Discord Webhook',
                    'connected' => false,
                    'config' => array()
                )
            );
        }
        
        foreach ($this->platforms as $platform_id => $platform) {
            $status[$platform_id] = array(
                'name' => $platform->get_platform_name(),
                'connected' => $platform->is_connected(),
                'config' => $platform->get_config()
            );
        }
        
        return $status;
    }
}