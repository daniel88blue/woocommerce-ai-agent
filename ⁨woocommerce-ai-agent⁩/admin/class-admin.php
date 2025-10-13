<?php
/**
 * AI电商智能体 - 管理类
 * 
 * @package WC_AI_Agent
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 主管理类
 * 
 * 处理后台菜单、页面渲染和AJAX请求
 */
class WC_AI_Agent_Admin {
    
    private static $instance = null;
    
    /**
     * 获取单例实例
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wc_ai_agent_run_analysis', array($this, 'ajax_run_analysis'));
        add_action('wp_ajax_wc_ai_agent_get_logs', array($this, 'ajax_get_logs'));
        add_action('wp_ajax_wai_save_revenue_goal', array($this, 'ajax_save_revenue_goal'));
    }
    
    /**
     * 添加管理菜单
     */
    public function add_admin_menu() {
        add_menu_page(
            'AI 电商智能体',
            'AI 智能体',
            'manage_woocommerce',
            'wc-ai-agent',
            array($this, 'display_unified_dashboard'),
            'dashicons-admin-generic',
            56
        );
    }
    
    /**
     * 加载管理端脚本和样式
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wc-ai-agent') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wc-ai-agent-admin',
            WC_AI_AGENT_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            WC_AI_AGENT_VERSION
        );
        
        wp_enqueue_script(
            'wc-ai-agent-admin',
            WC_AI_AGENT_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            WC_AI_AGENT_VERSION,
            true
        );
        
        wp_localize_script('wc-ai-agent-admin', 'wc_ai_agent_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc_ai_agent_nonce')
        ));
    }
    
    /**
     * 显示统一仪表板
     */
    public function display_unified_dashboard() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die('您没有权限访问此页面。');
        }
        
        // 获取当前标签页
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
        
        // 收集数据
        $data_collector = WC_AI_Agent_Data_Collector::get_instance();
        $store_data = $data_collector->collect_product_data();
        $logger = WC_AI_Agent_Logger::get_instance();
        $logs = $logger->get_recent_logs(50);
        $settings = WC_AI_Agent_Settings::get_instance();
        $current_settings = $settings->get_settings();
        
        // 处理设置保存
        if (isset($_POST['submit_settings']) && check_admin_referer('wai_save_settings', 'wai_settings_nonce')) {
            $settings->save_settings($_POST);
            echo '<div class="notice notice-success"><p>设置已保存！</p></div>';
            $current_settings = $settings->get_settings();
        }
        
        // 检查设置状态
        $setup_completed = get_option('wai_setup_completed', false);
        $revenue_goal = get_option('wai_revenue_goal', 10000);
        $current_revenue = $store_data['total_revenue'];
        $progress_percent = $revenue_goal > 0 ? min(100, ($current_revenue / $revenue_goal) * 100) : 0;
        
        // 将所有变量传递给模板
        $template_vars = compact(
            'current_tab',
            'store_data', 
            'logs',
            'current_settings',
            'setup_completed',
            'revenue_goal',
            'current_revenue',
            'progress_percent'
        );
        
        // 提取变量，使其在模板中可用
        extract($template_vars);
        
        // 包含仪表板模板
        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/dashboard.php';
    }
    
    /**
     * 保存收入目标 - AJAX处理
     */
    public function ajax_save_revenue_goal() {
        // 简化权限检查，只检查用户是否登录
        if (!is_user_logged_in()) {
            wp_send_json_error('请先登录');
        }
        
        $goal = isset($_POST['goal']) ? floatval($_POST['goal']) : 10000;
        update_option('wai_revenue_goal', $goal);
        
        if (!get_option('wai_setup_completed', false)) {
            update_option('wai_setup_completed', true);
        }
        
        wp_send_json_success(array('goal' => $goal));
    }
    
    /**
     * 运行分析 - AJAX处理
     */
    public function ajax_run_analysis() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die('权限不足');
        }
        
        $data_collector = WC_AI_Agent_Data_Collector::get_instance();
        $decision_engine = new WC_AI_Agent_Decision_Engine();
        $action_executor = new WC_AI_Agent_Action_Executor();
        
        $product_data = $data_collector->collect_product_data();
        $decisions = $decision_engine->analyze_and_decide($product_data);
        $results = $action_executor->execute_decisions($decisions);
        
        wp_send_json_success(array(
            'decisions' => $decisions,
            'results' => $results,
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * 获取日志 - AJAX处理
     */
    public function ajax_get_logs() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die('权限不足');
        }
        
        $logger = WC_AI_Agent_Logger::get_instance();
        $logs = $logger->get_recent_logs(100);
        
        wp_send_json_success($logs);
    }
}
?>