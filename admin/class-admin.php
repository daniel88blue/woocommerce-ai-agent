<?php
class WC_AI_Agent_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wc_ai_agent_run_analysis', array($this, 'ajax_run_analysis'));
        add_action('wp_ajax_wc_ai_agent_get_logs', array($this, 'ajax_get_logs'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'AI 电商智能体',
            'AI 智能体',
            'manage_woocommerce',
            'wc-ai-agent',
            array($this, 'display_dashboard'),
            'dashicons-admin-generic',
            56
        );
        
        add_submenu_page(
            'wc-ai-agent',
            '智能体仪表板',
            '仪表板',
            'manage_woocommerce',
            'wc-ai-agent',
            array($this, 'display_dashboard')
        );
        
        add_submenu_page(
            'wc-ai-agent',
            '设置',
            '设置',
            'manage_woocommerce',
            'wc-ai-agent-settings',
            array($this, 'display_settings')
        );
        
        add_submenu_page(
            'wc-ai-agent',
            '决策日志',
            '决策日志',
            'manage_woocommerce',
            'wc-ai-agent-logs',
            array($this, 'display_logs')
        );
    }
    
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
    
    public function display_dashboard() {
        $data_collector = WC_AI_Agent_Data_Collector::get_instance();
        $store_data = $data_collector->collect_product_data();
        
        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/dashboard.php';
    }
    
    public function display_settings() {
        $settings = WC_AI_Agent_Settings::get_instance();
        
        if (isset($_POST['submit_settings'])) {
            $settings->save_settings($_POST);
            echo '<div class="notice notice-success"><p>设置已保存！</p></div>';
        }
        
        $current_settings = $settings->get_settings();
        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/settings.php';
    }
    
    public function display_logs() {
        $logger = WC_AI_Agent_Logger::get_instance();
        $logs = $logger->get_recent_logs(50);
        
        include WC_AI_AGENT_PLUGIN_PATH . 'admin/partials/logs.php';
    }
    
    public function ajax_run_analysis() {
        check_ajax_referer('wc_ai_agent_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die('权限不足');
        }
        
        $data_collector = WC_AI_Agent_Data_Collector::get_instance();
        $decision_engine = new WC_AI_Agent_Decision_Engine();
        $action_executor = new WC_AI_Agent_Action_Executor();
        
        // 收集数据
        $product_data = $data_collector->collect_product_data();
        
        // 分析决策
        $decisions = $decision_engine->analyze_and_decide($product_data);
        
        // 执行决策
        $results = $action_executor->execute_decisions($decisions);
        
        wp_send_json_success(array(
            'decisions' => $decisions,
            'results' => $results,
            'timestamp' => current_time('mysql')
        ));
    }
    
    public function ajax_get_logs() {
        check_ajax_referer('wc_ai_agent_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die('权限不足');
        }
        
        $logger = WC_AI_Agent_Logger::get_instance();
        $logs = $logger->get_recent_logs(100);
        
        wp_send_json_success($logs);
    }
}
?>
