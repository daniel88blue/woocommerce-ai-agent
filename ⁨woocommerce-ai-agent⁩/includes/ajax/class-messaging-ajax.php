<?php
/**
 * 消息平台AJAX处理
 */

if (!defined('ABSPATH')) {
    exit;
}

class WAI_Messaging_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_wai_test_platform', array($this, 'test_platform'));
        add_action('wp_ajax_wai_test_command', array($this, 'test_command'));
        add_action('wp_ajax_wai_save_platform_config', array($this, 'save_platform_config'));
        add_action('wp_ajax_wai_send_test_message', array($this, 'send_test_message'));
    }
    
    public function test_platform() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        $platform_id = sanitize_text_field($_POST['platform'] ?? '');
        
        try {
            $manager = WAI_Message_Manager::get_instance();
            $result = $manager->send_to_platform(
                $platform_id, 
                '🧪 连接测试消息 - AI CTO已就绪！', 
                'text'
            );
            
            if ($result) {
                wp_send_json_success(array(
                    'message' => '平台连接测试成功',
                    'platform' => $platform_id
                ));
            } else {
                wp_send_json_error('平台连接失败，请检查配置');
            }
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    public function test_command() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        $command = sanitize_text_field($_POST['command'] ?? '');
        
        try {
            // 模拟命令处理
            $response = $this->process_test_command($command);
            
            wp_send_json_success(array(
                'command' => $command,
                'response' => $response,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    private function process_test_command($command) {
        $command = strtolower(trim($command));
        
        switch ($command) {
            case '状态':
            case 'status':
                return array(
                    'type' => 'status_report',
                    'data' => array(
                        'store_status' => '正常运行',
                        'revenue_today' => '¥1,234.56',
                        'orders_today' => 12,
                        'conversion_rate' => '3.2%',
                        'performance_score' => 87
                    )
                );
                
            case '报告':
            case 'report':
                return array(
                    'type' => 'analytics_report',
                    'data' => array(
                        'period' => '今日',
                        'total_visitors' => 375,
                        'total_orders' => 12,
                        'total_revenue' => '¥1,234.56',
                        'top_products' => array('产品A', '产品B', '产品C')
                    )
                );
                
            case '帮助':
            case 'help':
                return array(
                    'type' => 'help',
                    'commands' => array(
                        '状态 - 查看店铺状态',
                        '报告 - 获取分析报告', 
                        '订单 - 查看订单信息',
                        '产品 - 查看产品信息',
                        '帮助 - 显示帮助信息'
                    )
                );
                
            default:
                return array(
                    'type' => 'unknown_command',
                    'message' => '未知命令，请输入"帮助"查看可用命令'
                );
        }
    }
}

new WAI_Messaging_Ajax();