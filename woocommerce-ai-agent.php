<?php
/**
 * Plugin Name: WooCommerce AI Agent
 * Plugin URI: https://github.com/your-repo/woocommerce-ai-agent
 * Description: 自进化电商智能体 - 自动优化定价、库存和营销策略
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: wc-ai-agent
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('WC_AI_AGENT_VERSION', '1.0.0');
define('WC_AI_AGENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_AI_AGENT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WC_AI_AGENT_PLUGIN_FILE', __FILE__);

// 检查 WooCommerce 是否激活
register_activation_hook(__FILE__, 'wc_ai_agent_check_dependencies');
function wc_ai_agent_check_dependencies() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('WooCommerce AI Agent 需要 WooCommerce 插件。请先安装并激活 WooCommerce。', 'wc-ai-agent'));
    }
}

// 自动加载类
spl_autoload_register('wc_ai_agent_autoloader');
function wc_ai_agent_autoloader($class_name) {
    if (false !== strpos($class_name, 'WC_AI_Agent')) {
        $classes_dir = WC_AI_AGENT_PLUGIN_PATH . 'includes/';
        $class_file = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';
        $file_path = $classes_dir . $class_file;
        
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
}

// 初始化插件
add_action('plugins_loaded', 'wc_ai_agent_init');
function wc_ai_agent_init() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wc_ai_agent_woocommerce_missing_notice');
        return;
    }
    
    // 初始化核心类
    WC_AI_Agent_Main::get_instance();
}

// WooCommerce 缺失通知
function wc_ai_agent_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php _e('WooCommerce AI Agent 需要 WooCommerce 插件。请先安装并激活 WooCommerce。', 'wc-ai-agent'); ?></p>
    </div>
    <?php
}

// 主控制器类
class WC_AI_Agent_Main {
    private static $instance = null;
    
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    private function includes() {
        require_once WC_AI_AGENT_PLUGIN_PATH . 'includes/class-data-collector.php';
        require_once WC_AI_AGENT_PLUGIN_PATH . 'includes/class-decision-engine.php';
        require_once WC_AI_AGENT_PLUGIN_PATH . 'includes/class-action-executor.php';
        require_once WC_AI_AGENT_PLUGIN_PATH . 'includes/class-logger.php';
        require_once WC_AI_AGENT_PLUGIN_PATH . 'includes/class-settings.php';
        require_once WC_AI_AGENT_PLUGIN_PATH . 'includes/class-cron-manager.php';
        
        if (is_admin()) {
            require_once WC_AI_AGENT_PLUGIN_PATH . 'admin/class-admin.php';
            WC_AI_Agent_Admin::get_instance();
        }
    }
    
    private function init_hooks() {
        register_activation_hook(WC_AI_AGENT_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(WC_AI_AGENT_PLUGIN_FILE, array($this, 'deactivate'));
        
        add_action('init', array($this, 'load_textdomain'));
        
        // 初始化管理器
        WC_AI_Agent_Cron_Manager::get_instance();
        WC_AI_Agent_Settings::get_instance();
    }
    
    public function activate() {
        // 创建必要的数据库表
        $this->create_tables();
        
        // 设置默认选项
        $default_settings = array(
            'pricing_optimization' => '1',
            'inventory_management' => '1',
            'auto_discount' => '1',
            'analysis_frequency' => 'daily',
            'max_discount_rate' => '20',
            'min_profit_margin' => '10'
        );
        
        add_option('wc_ai_agent_settings', $default_settings);
        
        // 安排定时任务
        wp_schedule_event(time(), 'daily', 'wc_ai_agent_daily_analysis');
    }
    
    public function deactivate() {
        wp_clear_scheduled_hook('wc_ai_agent_daily_analysis');
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_name = $wpdb->prefix . 'wc_ai_agent_logs';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            action_type varchar(100) NOT NULL,
            product_id bigint(20),
            old_value text,
            new_value text,
            reason text,
            success tinyint(1) DEFAULT 1,
            metadata text,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY action_type (action_type),
            KEY timestamp (timestamp)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('wc-ai-agent', false, dirname(plugin_basename(WC_AI_AGENT_PLUGIN_FILE)) . '/languages');
    }
}
?>
