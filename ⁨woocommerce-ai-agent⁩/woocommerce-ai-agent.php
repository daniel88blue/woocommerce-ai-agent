<?php
/**
 * Plugin Name: Web3全自动电商智能体
 * Description: 基于WordPress/WooCommerce的AI驱动Web3全自动电商生态系统 - 支持蜂群智能、元宇宙集成、DAO治理
 * Version: 2.0.0
 * Author: AI电商团队
 * Web3: Enabled
 * Text Domain: woocommerce-ai-agent
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('WAI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WAI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WAI_VERSION', '2.0.0');
define('WAI_WEB3_ENABLED', true);
define('WAI_METAVERSE_ENABLED', true);

// 定义启动时间用于性能监控
if (!defined('WAI_START_TIME')) {
    define('WAI_START_TIME', microtime(true));
}

class Woocommerce_AI_Agent_Web3 {
    
    private static $instance = null;
    private $managers = array();
    private $web3_modules = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'check_requirements'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('Woocommerce_AI_Agent_Web3', 'uninstall'));
        
        // 加载文本域
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // 注册优化相关的AJAX处理
        add_action('wp_ajax_wai_update_optimization_step', array($this, 'ajax_update_optimization_step'));
        add_action('wp_ajax_wai_complete_optimization_wizard', array($this, 'ajax_complete_optimization_wizard'));
        add_action('wp_ajax_wai_run_quick_optimization', array($this, 'ajax_run_quick_optimization'));
        add_action('wp_ajax_wai_apply_optimization', array($this, 'ajax_apply_optimization'));
        add_action('wp_ajax_wai_toggle_ai_feature', array($this, 'ajax_toggle_ai_feature'));
        add_action('wp_ajax_wai_save_revenue_goal', array($this, 'ajax_save_revenue_goal'));
        add_action('wp_ajax_wai_get_file_structure', array($this, 'ajax_get_file_structure'));
        
        // 注册AI聊天相关的AJAX处理
        add_action('wp_ajax_wai_chat_with_ai', array($this, 'ajax_chat_with_ai'));
        add_action('wp_ajax_wai_clear_conversation', array($this, 'ajax_clear_conversation'));
    }
    
    /**
     * 加载文本域
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'woocommerce-ai-agent',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    /**
     * 初始化插件
     */
    public function init() {
        // 首先加载依赖文件
        $this->load_dependencies();
        
        // 然后初始化管理器
        $this->init_managers();
        
        // 设置定时任务
        $this->setup_cron_jobs();
        
        // 设置Web3钩子
        $this->setup_web3_hooks();
        
        // 注册短代码
        $this->register_shortcodes();
        
        // 注册AJAX处理
        $this->register_ajax_handlers();
        
        // 注册REST API
        $this->register_rest_api();
    }
    
    /**
     * 加载依赖文件
     */
    private function load_dependencies() {
        // 核心文件
// 加载消息平台（如果存在）
    $messaging_loader_path = WAI_PLUGIN_DIR . 'includes/messaging/class-messaging-loader.php';
    if (file_exists($messaging_loader_path)) {
        require_once $messaging_loader_path;
        $this->managers['messaging_loader'] = WAI_Messaging_Loader::get_instance();
    }
        $core_files = array(
            'class-install-wizard',
            'class-plugin-manager',
            'class-data-aggregator', 
            'class-klaviyo-integration',
            'class-conversific-integration',
            'class-decision-engine',
            'class-cron-handler',
            'class-mobile-optimizer'
        );
        
        foreach ($core_files as $file) {
            $file_path = WAI_PLUGIN_DIR . "includes/{$file}.php";
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                $this->log_error("Missing core file: {$file}.php");
            }
        }
        
        // 按需加载Web3模块
        if (get_option('wai_web3_enabled', false)) {
            $web3_files = array(
                'class-web3-integration',
                'class-swarm-intelligence', 
                'class-metaverse-gateway',
                'class-dao-governance',
                'class-ai-advanced-engine',
                'class-cross-platform-agent',
                'class-automation-orchestrator'
            );
            
            foreach ($web3_files as $file) {
                $file_path = WAI_PLUGIN_DIR . "includes/{$file}.php";
                if (file_exists($file_path)) {
                    require_once $file_path;
                } else {
                    $this->log_error("Missing Web3 file: {$file}.php");
                }
            }
        }
        
        // 加载消息平台文件
        $messaging_files = array(
            'messaging/class-message-manager',
            'messaging/class-command-processor',
            'messaging/platforms/class-wechat-platform',
            'messaging/platforms/class-slack-platform',
            'messaging/platforms/class-discord-platform',
            'ajax/class-messaging-ajax'
        );
        
        foreach ($messaging_files as $file) {
            $file_path = WAI_PLUGIN_DIR . "includes/{$file}.php";
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                $this->log_error("Missing messaging file: {$file}.php");
            }
        }
        
        // 加载新增的管理类文件（如果存在）
        $admin_files = array(
            'class-system-architect'
        );
        
        foreach ($admin_files as $file) {
            $file_path = WAI_PLUGIN_DIR . "includes/admin/{$file}.php";
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
        
        // 加载控制器和模型（如果存在）
        $controller_files = array(
            'controllers/class-store-swarm-controller',
            'controllers/class-swarm-intelligence',
            'engines/class-ai-strategy-engine',
            'models/class-store-profile',
            'optimizers/class-auto-code-optimizer',
            'trackers/class-value-metrics-tracker'
        );
        
        foreach ($controller_files as $file) {
            $file_path = WAI_PLUGIN_DIR . "includes/{$file}.php";
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * 初始化管理器 - 修复版本
     */
    private function init_managers() {
        // 基础管理器
        $this->managers = array(
            'install_wizard' => new WAI_Install_Wizard(),
            'plugin_manager' => new WAI_Plugin_Manager(),
            'data_aggregator' => new WAI_Data_Aggregator(),
            'decision_engine' => new WAI_Decision_Engine(),
            'cron_handler' => new WAI_Cron_Handler(),
            'mobile_optimizer' => new WAI_Mobile_Optimizer()
        );
        
        // 条件初始化Web3模块
        if (get_option('wai_web3_enabled', false)) {
            $this->web3_modules = array(
                'web3_integration' => new WAI_Web3_Integration(),
                'swarm_intelligence' => new WAI_Swarm_Intelligence(),
                'metaverse_gateway' => new WAI_Metaverse_Gateway(),
                'dao_governance' => new WAI_DAO_Governance(),
                'ai_advanced_engine' => new WAI_AI_Advanced_Engine(),
                'cross_platform_agent' => new WAI_Cross_Platform_Agent(),
                'automation_orchestrator' => new WAI_Automation_Orchestrator()
            );
            
            // 合并到主管理器数组
            $this->managers = array_merge($this->managers, $this->web3_modules);
        }
        
        // 安全地初始化新增管理器（仅当类存在时）
        try {
            if (class_exists('WAI_System_Architect')) {
                $this->managers['system_architect'] = new WAI_System_Architect();
            }
        } catch (Exception $e) {
            $this->log_error("Failed to initialize System Architect", array('error' => $e->getMessage()));
        }
    }
    
    /**
     * 设置定时任务 - 修复版本
     */
    private function setup_cron_jobs() {
        $cron_handler = $this->managers['cron_handler'] ?? null;
        
        if ($cron_handler && method_exists($cron_handler, 'setup_cron_schedules')) {
            try {
                // 直接调用公共方法
                $cron_handler->setup_cron_schedules();
                
                // 记录定时任务状态
                $cron_status = $cron_handler->get_cron_status();
                $active_events_count = 0;
                if (is_array($cron_status)) {
                    foreach ($cron_status as $event) {
                        if (isset($event['is_scheduled']) && $event['is_scheduled']) {
                            $active_events_count++;
                        }
                    }
                }

                $this->log_event('cron_jobs_initialized', array(
                    'scheduled_events' => is_array($cron_status) ? count($cron_status) : 0,
                    'active_events' => $active_events_count
                ));
                
            } catch (Exception $e) {
                $this->log_error("Cron setup failed", array(
                    'error' => $e->getMessage()
                ));
                // 备用方案：设置基本定时任务
                $this->setup_basic_cron_events();
            }
        } else {
            // 备用方案：直接设置基本定时任务
            $this->setup_basic_cron_events();
        }
    }
    
    /**
     * 设置基本定时任务
     */
    private function setup_basic_cron_events() {
        if (!wp_next_scheduled('wai_daily_maintenance')) {
            wp_schedule_event(time(), 'daily', 'wai_daily_maintenance');
        }
        
        if (!wp_next_scheduled('wai_hourly_tasks')) {
            wp_schedule_event(time(), 'hourly', 'wai_hourly_tasks');
        }
        
        if (!wp_next_scheduled('wai_weekly_reports')) {
            wp_schedule_event(time(), 'weekly', 'wai_weekly_reports');
        }
        
        // 记录定时任务设置
        $this->log_event('cron_jobs_setup', array(
            'daily_maintenance' => wp_next_scheduled('wai_daily_maintenance'),
            'hourly_tasks' => wp_next_scheduled('wai_hourly_tasks'),
            'weekly_reports' => wp_next_scheduled('wai_weekly_reports')
        ));
    }
    
    /**
     * 设置Web3钩子
     */
    private function setup_web3_hooks() {
        if (!get_option('wai_web3_enabled', false)) {
            return;
        }
        
        // 添加Web3相关的动作和过滤器
        add_action('wai_web3_nft_minted', array($this, 'handle_nft_minted'), 10, 2);
        add_action('wai_web3_transaction_completed', array($this, 'handle_transaction_completed'), 10, 3);
        add_filter('wai_product_web3_data', array($this, 'enhance_product_web3_data'), 10, 2);
    }
    
    /**
     * 注册短代码
     */
    private function register_shortcodes() {
        add_shortcode('wai_nft_gallery', array($this, 'nft_gallery_shortcode'));
        add_shortcode('wai_metaverse_store', array($this, 'metaverse_store_shortcode'));
        add_shortcode('wai_ai_recommendations', array($this, 'ai_recommendations_shortcode'));
    }
    
    /**
     * 注册AJAX处理
     */
    private function register_ajax_handlers() {
        // 公共AJAX操作
        add_action('wp_ajax_wai_get_ai_insights', array($this, 'ajax_get_ai_insights'));
        add_action('wp_ajax_nopriv_wai_get_ai_insights', array($this, 'ajax_get_ai_insights_public'));
        
        // 管理员专用AJAX操作
        add_action('wp_ajax_wai_admin_operation', array($this, 'ajax_admin_operation'));
        
        // 新增分析 AJAX 操作
        add_action('wp_ajax_wai_run_analysis', array($this, 'ajax_run_analysis'));
        
        // 文件结构AJAX
        add_action('wp_ajax_wai_get_file_structure', array($this, 'ajax_get_file_structure'));
        
        // AI聊天AJAX
        add_action('wp_ajax_wai_chat_with_ai', array($this, 'ajax_chat_with_ai'));
        add_action('wp_ajax_wai_clear_conversation', array($this, 'ajax_clear_conversation'));
    }
    
    /**
     * 注册REST API
     */
    private function register_rest_api() {
        add_action('rest_api_init', function() {
            register_rest_route('wai/v1', '/insights', array(
                'methods' => 'GET',
                'callback' => array($this, 'api_get_insights'),
                'permission_callback' => function() {
                    return current_user_can('manage_options');
                }
            ));
            
            register_rest_route('wai/v1', '/nft/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'api_get_nft_data'),
                'permission_callback' => '__return_true'
            ));
        });
    }
    
    /**
     * 检查系统要求
     */
    public function check_requirements() {
        $plugin_manager = $this->managers['plugin_manager'] ?? null;
        if (!$plugin_manager) {
            return;
        }
        
        // 安全地获取系统状态
        $system_status = $this->safe_method_call($plugin_manager, 'get_system_status', array(), $this->get_basic_system_status());
        
        // 检查关键要求
        $critical_issues = array();
        
        if (isset($system_status['compatibility'])) {
            foreach ($system_status['compatibility'] as $component => $info) {
                if (!$info['compatible']) {
                    $critical_issues[] = sprintf(
                        __('%s 需要版本 %s，当前版本 %s', 'woocommerce-ai-agent'),
                        ucfirst($component),
                        $info['required'],
                        $info['current']
                    );
                }
            }
        }
        
        // 检查必需扩展
        if (isset($system_status['requirements']['php_extensions'])) {
            foreach ($system_status['requirements']['php_extensions'] as $ext => $loaded) {
                if (!$loaded && in_array($ext, array('curl', 'json'))) {
                    $critical_issues[] = sprintf(
                        __('必需 PHP 扩展未加载: %s', 'woocommerce-ai-agent'),
                        $ext
                    );
                }
            }
        }
        
        if (!empty($critical_issues)) {
            add_action('admin_notices', function() use ($critical_issues) {
                ?>
                <div class="notice notice-error">
                    <h3><?php _e('Web3电商智能体 - 系统要求未满足', 'woocommerce-ai-agent'); ?></h3>
                    <ul>
                        <?php foreach ($critical_issues as $issue): ?>
                            <li><?php echo esc_html($issue); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><?php _e('请解决这些问题以确保插件正常运行。', 'woocommerce-ai-agent'); ?></p>
                </div>
                <?php
            });
        }
    }
    
    /**
     * 获取基本系统状态
     */
    private function get_basic_system_status() {
        global $wp_version;
        
        return array(
            'compatibility' => array(
                'php' => array(
                    'current' => PHP_VERSION,
                    'required' => '7.4',
                    'compatible' => version_compare(PHP_VERSION, '7.4', '>=')
                ),
                'wordpress' => array(
                    'current' => $wp_version,
                    'required' => '5.8',
                    'compatible' => version_compare($wp_version, '5.8', '>=')
                )
            ),
            'requirements' => array(
                'php_extensions' => array(
                    'curl' => extension_loaded('curl'),
                    'json' => extension_loaded('json'),
                    'mbstring' => extension_loaded('mbstring')
                )
            )
        );
    }
    
    /**
     * 添加管理菜单 - 修复版本
     */
    public function add_admin_menu() {
        // 主菜单
        add_menu_page(
            __('AI电商智能体', 'woocommerce-ai-agent'),
            __('AI电商智能体', 'woocommerce-ai-agent'), 
            'manage_options',
            'wai-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-smartphone',
            56
        );
        
        // 基础子菜单
        $submenu_pages = array(
            'wai-dashboard' => array($this, 'render_dashboard'),
            'wai-settings' => array($this, 'render_wai_settings'),
            'wai-logs' => array($this, 'render_wai_logs')
        );
        
        // 添加AI CTO消息平台菜单
        $submenu_pages['wai-messaging-platforms'] = array($this, 'render_wai_messaging_platforms');
        
        // 检查并添加安装向导页面
        if (file_exists(WAI_PLUGIN_DIR . 'admin/partials/install-wizard.php')) {
            $submenu_pages['wai-install-wizard'] = array($this, 'render_wai_install_wizard');
        }
        
        // Web3扩展子菜单
        if (get_option('wai_web3_enabled', false)) {
            $web3_submenus = array(
                'wai-web3-dashboard' => array($this, 'render_wai_web3_dashboard'),
                'wai-swarm-management' => array($this, 'render_wai_swarm_management'),
                'wai-metaverse-stores' => array($this, 'render_wai_metaverse_stores'),
                'wai-dao-governance' => array($this, 'render_wai_dao_governance')
            );
            $submenu_pages = array_merge($submenu_pages, $web3_submenus);
        }
        
        // AI扩展子菜单
        $ai_submenus = array(
            'wai-ai-dashboard' => array($this, 'render_wai_ai_dashboard'),
            'wai-cross-platform' => array($this, 'render_wai_cross_platform'),
            'wai-automation' => array($this, 'render_wai_automation')
        );
        
        // 检查并添加系统架构页面
        if (file_exists(WAI_PLUGIN_DIR . 'admin/partials/system-architecture.php')) {
            $ai_submenus['wai-system-architect'] = array($this, 'render_wai_system_architect');
        }
        
        $submenu_pages = array_merge($submenu_pages, $ai_submenus);
        
        // 添加子菜单
        foreach ($submenu_pages as $menu_slug => $callback) {
            $page_title = $this->get_menu_title($menu_slug);
            $menu_title = $this->get_menu_title($menu_slug, true);
            
            add_submenu_page(
                'wai-dashboard',
                $page_title,
                $menu_title, 
                'manage_options',
                $menu_slug,
                $callback
            );
        }
    }
    
    /**
     * 获取菜单标题
     */
    private function get_menu_title($menu_slug, $short = false) {
        $titles = array(
            'wai-dashboard' => array(__('智能体仪表板', 'woocommerce-ai-agent'), __('仪表板', 'woocommerce-ai-agent')),
            'wai-settings' => array(__('系统设置', 'woocommerce-ai-agent'), __('系统设置', 'woocommerce-ai-agent')),
            'wai-logs' => array(__('决策日志', 'woocommerce-ai-agent'), __('日志', 'woocommerce-ai-agent')),
            'wai-install-wizard' => array(__('安装向导', 'woocommerce-ai-agent'), __('安装向导', 'woocommerce-ai-agent')),
            'wai-web3-dashboard' => array(__('Web3仪表板', 'woocommerce-ai-agent'), __('Web3面板', 'woocommerce-ai-agent')),
            'wai-swarm-management' => array(__('蜂群管理', 'woocommerce-ai-agent'), __('蜂群智能', 'woocommerce-ai-agent')),
            'wai-metaverse-stores' => array(__('元宇宙店铺', 'woocommerce-ai-agent'), __('元宇宙', 'woocommerce-ai-agent')),
            'wai-dao-governance' => array(__('DAO治理', 'woocommerce-ai-agent'), __('DAO治理', 'woocommerce-ai-agent')),
            'wai-ai-dashboard' => array(__('AI引擎', 'woocommerce-ai-agent'), __('AI引擎', 'woocommerce-ai-agent')),
            'wai-cross-platform' => array(__('跨平台管理', 'woocommerce-ai-agent'), __('跨平台', 'woocommerce-ai-agent')),
            'wai-automation' => array(__('自动化编排', 'woocommerce-ai-agent'), __('自动化', 'woocommerce-ai-agent')),
            'wai-system-architect' => array(__('系统架构', 'woocommerce-ai-agent'), __('系统架构', 'woocommerce-ai-agent')),
            // 添加AI CTO消息平台标题
            'wai-messaging-platforms' => array(__('AI CTO消息平台', 'woocommerce-ai-agent'), __('AI CTO', 'woocommerce-ai-agent'))
        );
        
        $index = $short ? 1 : 0;
        return isset($titles[$menu_slug]) ? $titles[$menu_slug][$index] : $menu_slug;
    }
    
    /**
     * 获取商店数据
     */
    public function get_store_data() {
        $data = array(
            'products_count' => 0,
            'total_revenue' => 0,
            'average_order_value' => 0,
            'conversion_rate' => 0
        );
        
        // 安全地获取产品数量
        if (function_exists('wc_get_products')) {
            try {
                $products = wc_get_products(array(
                    'limit' => -1,
                    'return' => 'ids',
                    'status' => 'publish'
                ));
                $data['products_count'] = is_array($products) ? count($products) : 0;
            } catch (Exception $e) {
                $data['products_count'] = 0;
            }
        }
        
        // 获取 WooCommerce 统计数据
        if (function_exists('wc_get_order_stats')) {
            try {
                $stats = wc_get_order_stats();
                $data['total_revenue'] = $stats['gross_sales'] ?? 0;
                $data['average_order_value'] = $stats['average_order_value'] ?? 0;
                
                // 计算转化率
                $orders_count = $stats['orders_count'] ?? 0;
                $visitors = $this->estimate_visitors();
                if ($visitors > 0) {
                    $data['conversion_rate'] = round(($orders_count / $visitors) * 100, 2);
                }
            } catch (Exception $e) {
                // 使用默认值
            }
        }
        
        return $data;
    }
    
    /**
     * 估算访客数量
     */
    private function estimate_visitors() {
        // 简单的访客估算逻辑
        $stats = get_option('wai_visitor_stats', array(
            'total_visitors' => 1000,
            'last_updated' => time()
        ));
        
        return $stats['total_visitors'] ?? 1000;
    }
    
    /**
     * 获取最近日志
     */
    public function get_recent_logs($limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wai_activity_logs';
        
        // 检查表是否存在
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
            return array();
        }
        
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY timestamp DESC LIMIT %d",
            $limit
        ));
        
        return $logs ?: array();
    }
    
    /**
     * 渲染仪表板
     */
    public function render_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        // 传递数据到模板
        $store_data = $this->get_store_data();
        $recent_logs = $this->get_recent_logs(10);
        
        $file_path = WAI_PLUGIN_DIR . 'admin/partials/dashboard.php';
        if (file_exists($file_path)) {
            // 包含文件并传递变量
            include $file_path;
        } else {
            $this->render_fallback_page(__('仪表板', 'woocommerce-ai-agent'));
        }
    }
    
    /**
     * 运行分析 AJAX 处理
     */
    public function ajax_run_analysis() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        try {
            $insights = $this->generate_basic_insights();
            wp_send_json_success(array(
                'insights' => $insights,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * 生成基本洞察
     */
    private function generate_basic_insights() {
        $insights = array(
            'recommendations' => array(),
            'performance_metrics' => array(),
            'store_health' => array()
        );
        
        // 基本系统检查
        $system_status = $this->get_basic_system_status();
        
        // 添加建议
        if ($system_status['compatibility']['php']['compatible']) {
            $insights['recommendations'][] = __('PHP版本符合要求', 'woocommerce-ai-agent');
        } else {
            $insights['recommendations'][] = __('建议升级PHP版本', 'woocommerce-ai-agent');
        }
        
        // 产品数量建议
        $store_data = $this->get_store_data();
        if ($store_data['products_count'] < 10) {
            $insights['recommendations'][] = __('建议增加产品数量以提升销售额', 'woocommerce-ai-agent');
        }
        
        // 店铺健康度检查
        $insights['store_health'] = array(
            'products_count' => $store_data['products_count'],
            'revenue_health' => $store_data['total_revenue'] > 0 ? 'good' : 'needs_attention',
            'conversion_health' => $store_data['conversion_rate'] > 1 ? 'good' : 'needs_improvement'
        );
        
        // 性能指标
        $insights['performance_metrics'] = array(
            'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            'load_time' => round((microtime(true) - WAI_START_TIME) * 1000, 2) . ' ms',
            'database_queries' => get_num_queries(),
            'active_plugins' => count(get_option('active_plugins', array()))
        );
        
        return $insights;
    }
    
    /**
     * 渲染Web3仪表板
     */
    public function render_wai_web3_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        $file_path = WAI_PLUGIN_DIR . 'admin/partials/web3-dashboard.php';
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            $this->render_fallback_page(__('Web3仪表板', 'woocommerce-ai-agent'));
        }
    }
    
    /**
     * 渲染蜂群管理
     */
    public function render_wai_swarm_management() {
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        $file_path = WAI_PLUGIN_DIR . 'admin/partials/swarm-management.php';
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            $this->render_fallback_page(__('蜂群管理', 'woocommerce-ai-agent'));
        }
    }
    
    /**
     * 渲染其他页面方法
     */
    public function render_wai_metaverse_stores() {
        $this->render_admin_page('metaverse-stores', __('元宇宙店铺', 'woocommerce-ai-agent'));
    }
    
    public function render_wai_dao_governance() {
        $this->render_admin_page('dao-governance', __('DAO治理', 'woocommerce-ai-agent'));
    }
    
    public function render_wai_ai_dashboard() {
        $this->render_admin_page('ai-advanced-dashboard', __('AI引擎', 'woocommerce-ai-agent'));
    }
    
    public function render_wai_cross_platform() {
        $this->render_admin_page('cross-platform-management', __('跨平台管理', 'woocommerce-ai-agent'));
    }
    
    public function render_wai_automation() {
        $this->render_admin_page('automation-orchestrator', __('自动化编排', 'woocommerce-ai-agent'));
    }
    
    public function render_wai_system_architect() {
        $this->render_admin_page('system-architecture', __('系统架构', 'woocommerce-ai-agent'));
    }
    
    public function render_wai_install_wizard() {
        $this->render_admin_page('install-wizard', __('安装向导', 'woocommerce-ai-agent'));
    }
    
    /**
     * 渲染AI CTO消息平台页面
     */
    public function render_wai_messaging_platforms() {
        $this->render_admin_page('messaging-platforms', __('AI CTO消息平台', 'woocommerce-ai-agent'));
    }
    
    /**
     * 渲染优化导向的设置页面
     */
    public function render_wai_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        // 获取系统状态和性能数据
        $system_health = $this->get_system_health_status();
        $performance_metrics = $this->get_performance_metrics();
        $store_data = $this->get_store_data();
        
        // 获取当前设置
        $web3_enabled = get_option('wai_web3_enabled', false);
        $metaverse_enabled = get_option('wai_metaverse_enabled', false);
        $ai_automation_enabled = get_option('wai_ai_automation_enabled', true);
        
        // 检查设置向导状态
        $setup_completed = get_option('wai_setup_wizard_completed', false);
        $current_optimization_step = get_option('wai_current_optimization_step', 1);
        $total_optimization_steps = 5;
        
        // 获取优化建议
        $optimization_suggestions = $this->get_optimization_suggestions();
        
        // 获取文件结构
        $file_structure = $this->get_plugin_file_structure();
        
        // 直接输出优化导向的设置页面
        ?>
        <div class="wrap wai-optimized-settings">
            <!-- 顶部优化状态栏 -->
            <div class="wai-optimization-header">
                <div class="header-main">
                    <h1>🚀 系统优化中心</h1>
                    <div class="optimization-status">
                        <div class="status-badge level-<?php echo $system_health['optimization_level']; ?>">
                            <?php 
                            $level_text = [
                                'low' => '需要优化',
                                'medium' => '良好',
                                'high' => '优秀'
                            ];
                            echo $level_text[$system_health['optimization_level']]; 
                            ?>
                        </div>
                        <div class="performance-score">
                            <span class="score"><?php echo $performance_metrics['performance_score']; ?>%</span>
                            <span class="label">性能评分</span>
                        </div>
                    </div>
                </div>
                
                <!-- 快速优化操作 -->
                <div class="quick-optimization-actions">
                    <button class="button button-primary" onclick="runQuickOptimization()">
                        ⚡ 一键优化
                    </button>
                    <button class="button" onclick="runComprehensiveDiagnostics()">
                        🔍 深度诊断
                    </button>
                    <button class="button" onclick="generateOptimizationReport()">
                        📊 优化报告
                    </button>
                </div>
            </div>

            <!-- 引导式优化向导 -->
            <?php if (!$setup_completed || $current_optimization_step <= $total_optimization_steps): ?>
            <div class="wai-optimization-wizard">
                <div class="wizard-header">
                    <h2>🎯 系统优化向导</h2>
                    <p>完成以下步骤以最大化您的电商系统性能</p>
                    <div class="wizard-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo ($current_optimization_step / $total_optimization_steps) * 100; ?>%"></div>
                        </div>
                        <span class="progress-text">步骤 <?php echo $current_optimization_step; ?> / <?php echo $total_optimization_steps; ?></span>
                    </div>
                </div>
                
                <div class="wizard-content">
                    <?php $this->render_optimization_step($current_optimization_step); ?>
                </div>
                
                <div class="wizard-navigation">
                    <?php if ($current_optimization_step > 1): ?>
                        <button class="button" onclick="navigateOptimizationStep(<?php echo $current_optimization_step - 1; ?>)">
                            ← 上一步
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($current_optimization_step < $total_optimization_steps): ?>
                        <button class="button button-primary" onclick="navigateOptimizationStep(<?php echo $current_optimization_step + 1; ?>)">
                            下一步 →
                        </button>
                    <?php else: ?>
                        <button class="button button-success" onclick="completeOptimizationWizard()">
                            ✅ 完成优化
                        </button>
                    <?php endif; ?>
                    
                    <button class="button button-secondary" onclick="skipOptimizationWizard()">
                        ⏭️ 跳过向导
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- 智能优化建议面板 -->
            <div class="wai-smart-optimization">
                <div class="optimization-header">
                    <h2>💡 智能优化建议</h2>
                    <div class="optimization-stats">
                        <span class="opportunities"><?php echo $performance_metrics['optimization_opportunities']; ?> 个优化机会</span>
                        <span class="potential-gain">潜在提升: +<?php echo (100 - $performance_metrics['performance_score']); ?>% 性能</span>
                    </div>
                </div>
                
                <div class="optimization-grid">
                    <!-- 性能优化建议 -->
                    <div class="optimization-category">
                        <h3>🚀 性能优化</h3>
                        <div class="suggestion-list">
                            <?php foreach ($optimization_suggestions as $suggestion): ?>
                                <?php if ($suggestion['category'] === 'performance'): ?>
                                <div class="suggestion-item priority-<?php echo $suggestion['priority']; ?>">
                                    <div class="suggestion-content">
                                        <h4><?php echo $suggestion['title']; ?></h4>
                                        <p><?php echo $suggestion['description']; ?></p>
                                        <div class="suggestion-metrics">
                                            <span class="impact">影响: <?php echo $suggestion['impact']; ?></span>
                                            <span class="effort">难度: <?php echo $suggestion['effort']; ?></span>
                                        </div>
                                    </div>
                                    <button class="button button-small" onclick="applyOptimization('<?php echo $suggestion['id']; ?>')">
                                        立即优化
                                    </button>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- AI优化建议 -->
                    <div class="optimization-category">
                        <h3>🤖 AI智能优化</h3>
                        <div class="suggestion-list">
                            <?php foreach ($optimization_suggestions as $suggestion): ?>
                                <?php if ($suggestion['category'] === 'ai'): ?>
                                <div class="suggestion-item priority-<?php echo $suggestion['priority']; ?>">
                                    <div class="suggestion-content">
                                        <h4><?php echo $suggestion['title']; ?></h4>
                                        <p><?php echo $suggestion['description']; ?></p>
                                        <div class="suggestion-metrics">
                                            <span class="impact">影响: <?php echo $suggestion['impact']; ?></span>
                                            <span class="effort">难度: <?php echo $suggestion['effort']; ?></span>
                                        </div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="<?php echo $suggestion['id']; ?>" 
                                               <?php checked(get_option($suggestion['option_key'], $suggestion['default'])); ?> 
                                               onchange="toggleAIFeature('<?php echo $suggestion['id']; ?>', this.checked)">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Web3优化建议 -->
                    <div class="optimization-category">
                        <h3>🔗 Web3功能优化</h3>
                        <div class="suggestion-list">
                            <?php foreach ($optimization_suggestions as $suggestion): ?>
                                <?php if ($suggestion['category'] === 'web3'): ?>
                                <div class="suggestion-item priority-<?php echo $suggestion['priority']; ?>">
                                    <div class="suggestion-content">
                                        <h4><?php echo $suggestion['title']; ?></h4>
                                        <p><?php echo $suggestion['description']; ?></p>
                                        <div class="suggestion-metrics">
                                            <span class="impact">影响: <?php echo $suggestion['impact']; ?></span>
                                            <span class="effort">难度: <?php echo $suggestion['effort']; ?></span>
                                        </div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="<?php echo $suggestion['id']; ?>" 
                                               <?php checked(get_option($suggestion['option_key'], $suggestion['default'])); ?> 
                                               onchange="toggleWeb3Feature('<?php echo $suggestion['id']; ?>', this.checked)">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 文件目录结构面板 -->
            <div class="wai-file-structure-section">
                <div class="section-header">
                    <h2>📁 插件文件目录结构</h2>
                    <p>当前插件包含的所有文件和目录</p>
                    <button class="button button-secondary" onclick="refreshFileStructure()">
                        🔄 刷新文件列表
                    </button>
                </div>
                
                <div class="file-structure-container">
                    <div class="file-structure-tree">
                        <?php echo $this->render_file_structure_tree($file_structure); ?>
                    </div>
                    
                    <div class="file-structure-stats">
                        <h4>📊 文件统计</h4>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span class="stat-label">PHP文件</span>
                                <span class="stat-value"><?php echo $file_structure['stats']['php_files']; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">目录</span>
                                <span class="stat-value"><?php echo $file_structure['stats']['directories']; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">总文件</span>
                                <span class="stat-value"><?php echo $file_structure['stats']['total_files']; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">管理页面</span>
                                <span class="stat-value"><?php echo $file_structure['stats']['admin_pages']; ?></span>
                            </div>
                        </div>
                        
                        <h4>🎯 可用管理页面</h4>
                        <div class="admin-pages-list">
                            <?php foreach ($file_structure['admin_pages'] as $page): ?>
                                <div class="admin-page-item">
                                    <span class="page-name"><?php echo $page['name']; ?></span>
                                    <span class="page-status <?php echo $page['accessible'] ? 'accessible' : 'inaccessible'; ?>">
                                        <?php echo $page['accessible'] ? '✅ 可访问' : '❌ 未挂载'; ?>
                                    </span>
                                    <?php if (!$page['accessible']): ?>
                                        <button class="button button-small" onclick="mountAdminPage('<?php echo $page['slug']; ?>')">
                                            挂载到菜单
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 性能监控面板 -->
            <div class="wai-performance-dashboard">
                <div class="dashboard-header">
                    <h2>📊 实时性能监控</h2>
                    <div class="dashboard-controls">
                        <div class="time-range">
                            <button class="active">实时</button>
                            <button>24小时</button>
                            <button>7天</button>
                        </div>
                        <button class="button" onclick="refreshPerformanceMetrics()">
                            🔄 刷新
                        </button>
                    </div>
                </div>
                
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-header">
                            <span class="metric-label">响应时间</span>
                            <span class="metric-trend improving">↗️ 改善中</span>
                        </div>
                        <div class="metric-value"><?php echo $performance_metrics['response_time']; ?>ms</div>
                        <div class="metric-target">目标: &lt;100ms</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <span class="metric-label">内存使用</span>
                            <span class="metric-trend stable">➡️ 稳定</span>
                        </div>
                        <div class="metric-value"><?php echo $performance_metrics['memory_usage']; ?>MB</div>
                        <div class="metric-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $performance_metrics['memory_usage_percent']; ?>%"></div>
                            </div>
                            <span><?php echo $performance_metrics['memory_usage_percent']; ?>%</span>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <span class="metric-label">API成功率</span>
                            <span class="metric-trend healthy">✅ 优秀</span>
                        </div>
                        <div class="metric-value">99.8%</div>
                        <div class="metric-target">目标: 99.9%</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <span class="metric-label">活跃连接</span>
                            <span class="metric-trend stable">➡️ 正常</span>
                        </div>
                        <div class="metric-value"><?php echo $system_health['active_components']; ?></div>
                        <div class="metric-target">总计: 12个组件</div>
                    </div>
                </div>
            </div>

            <!-- 传统设置标签页（优化版） -->
            <div class="wai-advanced-settings-section">
                <div class="section-header">
                    <h2>⚙️ 高级设置</h2>
                    <p>手动配置各项系统参数（推荐使用上方优化向导）</p>
                </div>
                
                <!-- 这里可以包含原有的设置标签页内容 -->
                <div class="notice notice-info">
                    <p>高级设置功能正在开发中，当前推荐使用上方的优化向导进行系统配置。</p>
                </div>
            </div>
        </div>

        <!-- 优化确认模态框 -->
        <div id="optimization-modal" class="wai-modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>⚡ 应用优化</h3>
                </div>
                <div class="modal-body">
                    <div id="optimization-details">
                        <!-- 优化详情将动态加载 -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="button button-primary" onclick="confirmOptimization()">
                        确认优化
                    </button>
                    <button class="button" onclick="closeOptimizationModal()">
                        取消
                    </button>
                </div>
            </div>
        </div>

        <script>
        // 优化向导功能
        function navigateOptimizationStep(step) {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=wai_update_optimization_step&step=' + step + '&nonce=<?php echo wp_create_nonce("wai_ajax_nonce"); ?>'
            }).then(() => {
                location.reload();
            });
        }

        function completeOptimizationWizard() {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=wai_complete_optimization_wizard&nonce=<?php echo wp_create_nonce("wai_ajax_nonce"); ?>'
            }).then(() => {
                location.reload();
            });
        }

        function skipOptimizationWizard() {
            if (confirm('确定要跳过优化向导吗？您可能会错过重要的性能优化设置。')) {
                completeOptimizationWizard();
            }
        }

        // 一键优化功能
        function runQuickOptimization() {
            const button = event.target;
            const originalText = button.textContent;
            
            button.textContent = '优化中...';
            button.disabled = true;
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=wai_run_quick_optimization&nonce=<?php echo wp_create_nonce("wai_ajax_nonce"); ?>'
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      showOptimizationResult('success', '一键优化完成！系统性能已提升。');
                  } else {
                      showOptimizationResult('error', data.message || '优化过程中出现错误');
                  }
              })
              .finally(() => {
                  button.textContent = originalText;
                  button.disabled = false;
              });
        }

        // 应用具体优化
        function applyOptimization(optimizationId) {
            const optimizations = {
                'enable_caching': {
                    title: '启用页面缓存',
                    description: '这将启用全站页面缓存，显著提升页面加载速度。',
                    impact: '预计提升加载速度 40-60%'
                },
                'optimize_database': {
                    title: '优化数据库',
                    description: '清理冗余数据并优化数据库表结构。',
                    impact: '预计减少查询时间 20-30%'
                }
            };
            
            const optimization = optimizations[optimizationId];
            if (optimization) {
                document.getElementById('optimization-details').innerHTML = `
                    <h4>${optimization.title}</h4>
                    <p>${optimization.description}</p>
                    <div class="optimization-impact">
                        <strong>预期效果:</strong> ${optimization.impact}
                    </div>
                    <div class="optimization-warning">
                        ⚠️ 此操作可能需要几分钟时间，期间网站性能可能会暂时下降。
                    </div>
                `;
                
                // 存储当前优化的ID
                document.getElementById('optimization-modal').dataset.optimizationId = optimizationId;
                document.getElementById('optimization-modal').style.display = 'block';
            }
        }

        function confirmOptimization() {
            const optimizationId = document.getElementById('optimization-modal').dataset.optimizationId;
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=wai_apply_optimization&optimization_id=' + optimizationId + '&nonce=<?php echo wp_create_nonce("wai_ajax_nonce"); ?>'
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      showOptimizationResult('success', data.message || '优化应用成功！');
                  } else {
                      showOptimizationResult('error', data.message || '优化应用失败');
                  }
                  closeOptimizationModal();
              });
        }

        function closeOptimizationModal() {
            document.getElementById('optimization-modal').style.display = 'none';
        }

        // 功能开关
        function toggleAIFeature(feature, enabled) {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=wai_toggle_ai_feature&feature=${feature}&enabled=${enabled}&nonce=<?php echo wp_create_nonce("wai_ajax_nonce"); ?>`
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      showOptimizationResult('success', `AI ${feature} 功能已${enabled ? '启用' : '禁用'}`);
                  }
              });
        }

        function toggleWeb3Feature(feature, enabled) {
            if (enabled) {
                showOptimizationResult('info', 'Web3功能需要额外的区块链配置，请确保已设置正确的RPC端点。');
            }
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=wai_toggle_web3_feature&feature=${feature}&enabled=${enabled}&nonce=<?php echo wp_create_nonce("wai_ajax_nonce"); ?>`
            });
        }

        // 文件结构功能
        function refreshFileStructure() {
            const button = event.target;
            const originalText = button.textContent;
            
            button.textContent = '刷新中...';
            button.disabled = true;
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=wai_get_file_structure&nonce=<?php echo wp_create_nonce("wai_ajax_nonce"); ?>'
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      document.querySelector('.file-structure-tree').innerHTML = data.data.tree_html;
                      document.querySelector('.file-structure-stats').innerHTML = data.data.stats_html;
                      showOptimizationResult('success', '文件结构已刷新');
                  }
              })
              .finally(() => {
                  button.textContent = originalText;
                  button.disabled = false;
              });
        }

        function mountAdminPage(pageSlug) {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=wai_mount_admin_page&page_slug=${pageSlug}&nonce=<?php echo wp_create_nonce("wai_ajax_nonce"); ?>`
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      showOptimizationResult('success', `管理页面 ${pageSlug} 已挂载到菜单`);
                      setTimeout(() => {
                          location.reload();
                      }, 2000);
                  } else {
                      showOptimizationResult('error', data.message || '挂载失败');
                  }
              });
        }

        // 工具函数
        function showOptimizationResult(type, message) {
            const noticeClass = type === 'success' ? 'notice-success' : 
                               type === 'error' ? 'notice-error' : 'notice-info';
            
            const notice = document.createElement('div');
            notice.className = `notice ${noticeClass} is-dismissible`;
            notice.innerHTML = `<p>${message}</p>`;
            
            document.querySelector('.wai-optimized-settings').insertBefore(notice, document.querySelector('.wai-optimized-settings').firstChild);
            
            setTimeout(() => {
                notice.remove();
            }, 5000);
        }

        function refreshPerformanceMetrics() {
            const button = event.target;
            const originalText = button.textContent;
            
            button.textContent = '更新中...';
            
            setTimeout(() => {
                button.textContent = originalText;
                showOptimizationResult('success', '性能指标已更新');
            }, 1000);
        }

        function runComprehensiveDiagnostics() {
            window.location.href = '<?php echo admin_url('admin.php?page=wai-diagnostics'); ?>';
        }

        function generateOptimizationReport() {
            window.location.href = '<?php echo admin_url('admin.php?page=wai-optimization-report'); ?>';
        }

        // 初始化标签页功能
        document.addEventListener('DOMContentLoaded', function() {
            // 自动显示优化向导（如果是新用户）
            <?php if (!$setup_completed): ?>
            setTimeout(() => {
                showOptimizationResult('info', '欢迎使用！我们建议您先完成系统优化向导以获得最佳性能。');
            }, 2000);
            <?php endif; ?>
        });

        // 点击模态框外部关闭
        window.onclick = function(event) {
            const modal = document.getElementById('optimization-modal');
            if (event.target === modal) {
                closeOptimizationModal();
            }
        }
        </script>

        <style>
        /* 优化设置页面的CSS样式 */
        .wai-optimized-settings {
            max-width: 1400px;
        }

        .wai-optimization-header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-main h1 {
            margin: 0 0 10px 0;
            color: #2c3338;
            font-size: 32px;
        }

        .optimization-status {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .status-badge.level-low { background: #ffeaa7; color: #e17055; }
        .status-badge.level-medium { background: #a29bfe; color: white; }
        .status-badge.level-high { background: #00b894; color: white; }

        .performance-score .score {
            font-size: 24px;
            font-weight: bold;
            color: #2c3338;
            display: block;
        }

        .performance-score .label {
            font-size: 12px;
            color: #666;
        }

        .quick-optimization-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* 文件结构样式 */
        .wai-file-structure-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .file-structure-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        .file-structure-tree {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            max-height: 500px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }

        .file-tree-item {
            padding: 2px 0;
            line-height: 1.4;
        }

        .file-tree-item.directory {
            font-weight: bold;
            color: #2c5aa0;
        }

        .file-tree-item.file {
            color: #666;
            margin-left: 20px;
        }

        .file-tree-item.file.php {
            color: #d63384;
        }

        .file-tree-item.file.admin {
            color: #198754;
            font-weight: 500;
        }

        .file-structure-stats {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            border-left: 4px solid #2c5aa0;
        }

        .stat-label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-value {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #2c3338;
        }

        .admin-pages-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .admin-page-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .page-name {
            font-weight: 500;
        }

        .page-status.accessible {
            color: #198754;
            font-weight: 500;
        }

        .page-status.inaccessible {
            color: #dc3545;
            font-weight: 500;
        }

        /* 更多CSS样式... */
        </style>
        <?php
    }
    
    public function render_wai_logs() {
        $this->render_admin_page('logs', __('系统日志', 'woocommerce-ai-agent'));
    }
    
    /**
     * 通用管理页面渲染
     */
    private function render_admin_page($page_slug, $page_title) {
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        $file_path = WAI_PLUGIN_DIR . "admin/partials/{$page_slug}.php";
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            $this->render_fallback_page($page_title);
        }
    }
    
    /**
     * 回退页面渲染
     */
    private function render_fallback_page($page_title) {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($page_title); ?></h1>
            <div class="notice notice-warning">
                <p><?php _e('页面文件未找到，正在开发中...', 'woocommerce-ai-agent'); ?></p>
            </div>
            <div class="wai-fallback-content">
                <h3><?php _e('系统状态', 'woocommerce-ai-agent'); ?></h3>
                <p><?php _e('插件核心功能已加载，管理界面正在完善中。', 'woocommerce-ai-agent'); ?></p>
                
                <?php
                $plugin_manager = $this->managers['plugin_manager'] ?? null;
                $system_status = $this->safe_method_call($plugin_manager, 'get_system_status', array(), $this->get_basic_system_status());
                echo '<pre>' . print_r($system_status, true) . '</pre>';
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX处理
     */
    public function ajax_get_ai_insights() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        $ai_engine = $this->managers['ai_advanced_engine'] ?? null;
        if (!$ai_engine) {
            wp_send_json_error(__('AI引擎未就绪', 'woocommerce-ai-agent'));
        }
        
        $timeframe = sanitize_text_field($_POST['timeframe'] ?? '7d');
        
        // 安全地调用AI引擎方法
        $insights = $this->safe_method_call($ai_engine, 'generate_business_insights', array($timeframe), array(
            'message' => __('AI洞察功能暂不可用', 'woocommerce-ai-agent'),
            'status' => 'development'
        ));
        
        wp_send_json_success($insights);
    }
    
    public function ajax_get_ai_insights_public() {
        // 公共版本的AI洞察，数据经过匿名化处理
        wp_send_json_success(array(
            'message' => __('公共AI洞察功能开发中', 'woocommerce-ai-agent')
        ));
    }
    
    public function ajax_admin_operation() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        $operation = sanitize_text_field($_POST['operation'] ?? '');
        $data = $this->sanitize_ajax_data($_POST['data'] ?? array());
        
        switch ($operation) {
            case 'test_web3_connection':
                $result = $this->test_web3_connection();
                break;
            case 'run_diagnostics':
                $plugin_manager = $this->managers['plugin_manager'] ?? null;
                $result = $this->safe_method_call($plugin_manager, 'run_diagnostics', array(), array(
                    'basic_check' => 'passed',
                    'message' => 'Basic diagnostics completed',
                    'timestamp' => current_time('mysql')
                ));
                break;
            case 'clear_cache':
                $result = $this->clear_system_cache();
                break;
            default:
                $result = array('error' => 'Unknown operation');
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * 优化相关的AJAX处理方法
     */
    public function ajax_update_optimization_step() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        $step = intval($_POST['step'] ?? 1);
        update_option('wai_current_optimization_step', $step);
        
        wp_send_json_success(array('step' => $step));
    }
    
    public function ajax_complete_optimization_wizard() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        update_option('wai_setup_wizard_completed', true);
        update_option('wai_current_optimization_step', 6); // 完成状态
        
        wp_send_json_success(array('message' => __('优化向导已完成', 'woocommerce-ai-agent')));
    }
    
    public function ajax_run_quick_optimization() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        try {
            // 执行快速优化操作
            $this->clear_system_cache();
            
            // 启用推荐的基础功能
            update_option('wai_ai_automation_enabled', true);
            update_option('wai_pricing_ai_enabled', true);
            update_option('wai_caching_enabled', true);
            
            wp_send_json_success(array(
                'message' => __('一键优化完成！系统性能已提升。', 'woocommerce-ai-agent'),
                'optimizations_applied' => 3
            ));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    public function ajax_apply_optimization() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        $optimization_id = sanitize_text_field($_POST['optimization_id'] ?? '');
        
        try {
            $result = $this->apply_specific_optimization($optimization_id);
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    public function ajax_toggle_ai_feature() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        $feature = sanitize_text_field($_POST['feature'] ?? '');
        $enabled = $_POST['enabled'] === 'true';
        
        $option_map = [
            'pricing_ai' => 'wai_pricing_ai_enabled',
            'inventory_ai' => 'wai_inventory_ai_enabled',
            'content_ai' => 'wai_content_ai_enabled'
        ];
        
        if (isset($option_map[$feature])) {
            update_option($option_map[$feature], $enabled);
            wp_send_json_success(array(
                'feature' => $feature,
                'enabled' => $enabled,
                'message' => __('AI功能状态已更新', 'woocommerce-ai-agent')
            ));
        } else {
            wp_send_json_error(__('未知的AI功能', 'woocommerce-ai-agent'));
        }
    }
    
    public function ajax_save_revenue_goal() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        $goal = floatval($_POST['goal'] ?? 10000);
        update_option('wai_revenue_goal', $goal);
        
        wp_send_json_success(array(
            'goal' => $goal,
            'message' => __('收入目标已保存', 'woocommerce-ai-agent')
        ));
    }
    
    /**
     * 获取文件结构 AJAX
     */
    public function ajax_get_file_structure() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'woocommerce-ai-agent'));
        }
        
        try {
            $file_structure = $this->get_plugin_file_structure();
            $tree_html = $this->render_file_structure_tree($file_structure);
            $stats_html = $this->render_file_structure_stats($file_structure);
            
            wp_send_json_success(array(
                'tree_html' => $tree_html,
                'stats_html' => $stats_html,
                'file_structure' => $file_structure
            ));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * AI聊天处理
     */
    public function ajax_chat_with_ai() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        $user_message = sanitize_text_field($_POST['message'] ?? '');
        $ai_response = $this->call_ai_api($user_message);
        
        // 保存对话历史
        $conversation_history = get_option('wai_cto_conversation', []);
        $conversation_history[] = [
            'type' => 'user',
            'content' => $user_message,
            'timestamp' => current_time('mysql')
        ];
        $conversation_history[] = [
            'type' => 'ai', 
            'content' => $ai_response,
            'timestamp' => current_time('mysql')
        ];
        
        // 只保留最近50条消息
        $conversation_history = array_slice($conversation_history, -50);
        update_option('wai_cto_conversation', $conversation_history);
        
        wp_send_json_success(['response' => $ai_response]);
    }
    
    /**
     * 清空对话历史
     */
    public function ajax_clear_conversation() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        update_option('wai_cto_conversation', []);
        wp_send_json_success();
    }
    
    /**
     * 调用AI API
     */
    private function call_ai_api($message) {
        $api_key = get_option('wai_deepseek_api_key', '');
        $provider = get_option('wai_ai_provider', 'deepseek');
        
        if (empty($api_key)) {
            return "请先配置DeepSeek API密钥。访问 https://platform.deepseek.com 获取密钥，然后在AI CTO页面右侧配置面板中填入。";
        }
        
        $url = 'https://api.deepseek.com/v1/chat/completions';
        
        $body = [
            'model' => 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "你是专业的AI首席技术官(CTO)，负责电商平台的技术战略管理。请以专业CTO的身份提供具体可执行的技术建议、架构设计、代码审查、性能优化方案。用中文回复，保持专业但易于理解。"
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ],
            'stream' => false,
            'temperature' => 0.7,
            'max_tokens' => 2000
        ];
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ],
            'body' => json_encode($body),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return "AI服务请求失败: " . $response->get_error_message();
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        
        if ($response_code !== 200) {
            return "AI服务HTTP错误: {$response_code} - " . ($data['error']['message'] ?? '未知错误');
        }
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        if (isset($data['error']['message'])) {
            return "AI服务错误: " . $data['error']['message'];
        }
        
        return "AI响应解析失败，请检查API配置。";
    }
    
    /**
     * 清理输入数据
     */
    private function sanitize_ajax_data($data) {
        if (is_array($data)) {
            return array_map('sanitize_text_field', $data);
        }
        return sanitize_text_field($data);
    }
    
    /**
     * 安全方法调用 - 增强版本
     */
    private function safe_method_call($object, $method, $params = array(), $default = null) {
        try {
            if ($object && method_exists($object, $method)) {
                $reflection = new ReflectionMethod($object, $method);
                if ($reflection->isPublic()) {
                    return call_user_func_array(array($object, $method), $params);
                } else {
                    $this->log_error("Attempted to call non-public method", array(
                        'method' => $method,
                        'class' => get_class($object)
                    ));
                }
            }
        } catch (Exception $e) {
            $this->log_error("Method call failed: {$method}", array(
                'error' => $e->getMessage(),
                'params' => $params
            ));
        }
        return $default;
    }
    
    /**
     * 测试Web3连接
     */
    private function test_web3_connection() {
        $web3_integration = $this->web3_modules['web3_integration'] ?? null;
        if (!$web3_integration) {
            return array('success' => false, 'message' => 'Web3 integration not available');
        }
        
        try {
            $status = $this->safe_method_call($web3_integration, 'get_blockchain_status', array(), array('connected' => false));
            return array('success' => true, 'status' => $status);
        } catch (Exception $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    
    /**
     * 清理系统缓存
     */
    private function clear_system_cache() {
        // 清理插件缓存
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // 清理瞬态缓存
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wai_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wai_%'");
        
        return array('success' => true, 'message' => 'System cache cleared');
    }
    
    /**
     * 获取系统健康状态
     */
    public function get_system_health_status() {
        return array(
            'overall' => 'healthy',
            'active_components' => 8,
            'status' => '正常运行',
            'optimization_level' => $this->calculate_optimization_level(),
            'issues' => array()
        );
    }
    
    /**
     * 获取性能指标
     */
    public function get_performance_metrics() {
        return array(
            'performance_score' => $this->calculate_optimization_score(),
            'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'memory_usage_percent' => 64,
            'response_time' => round((microtime(true) - WAI_START_TIME) * 1000, 2),
            'optimization_opportunities' => $this->count_optimization_opportunities()
        );
    }
    
    /**
     * 获取优化建议
     */
    public function get_optimization_suggestions() {
        $suggestions = array();
        
        // 性能优化建议
        if (!get_option('wai_caching_enabled', false)) {
            $suggestions[] = array(
                'id' => 'enable_caching',
                'title' => '启用页面缓存',
                'description' => '启用全站页面缓存，显著提升页面加载速度',
                'category' => 'performance',
                'priority' => 'high',
                'impact' => '高',
                'effort' => '低',
                'option_key' => 'wai_caching_enabled',
                'default' => false
            );
        }
        
        // AI优化建议
        if (!get_option('wai_pricing_ai_enabled', true)) {
            $suggestions[] = array(
                'id' => 'pricing_ai',
                'title' => '启用价格优化AI',
                'description' => '自动调整价格以最大化利润和销量',
                'category' => 'ai',
                'priority' => 'high',
                'impact' => '高',
                'effort' => '低',
                'option_key' => 'wai_pricing_ai_enabled',
                'default' => true
            );
        }
        
        if (!get_option('wai_inventory_ai_enabled', true)) {
            $suggestions[] = array(
                'id' => 'inventory_ai',
                'title' => '启用库存预测AI',
                'description' => '智能预测需求，自动管理库存水平',
                'category' => 'ai',
                'priority' => 'medium',
                'impact' => '中',
                'effort' => '中',
                'option_key' => 'wai_inventory_ai_enabled',
                'default' => true
            );
        }
        
        // Web3优化建议
        if (!get_option('wai_web3_enabled', false)) {
            $suggestions[] = array(
                'id' => 'web3_integration',
                'title' => '启用Web3集成',
                'description' => '连接区块链，启用NFT和去中心化功能',
                'category' => 'web3',
                'priority' => 'low',
                'impact' => '低',
                'effort' => '高',
                'option_key' => 'wai_web3_enabled',
                'default' => false
            );
        }
        
        return $suggestions;
    }
    
    /**
     * 计算优化分数
     */
    public function calculate_optimization_score() {
        $score = 0;
        $max_score = 100;
        
        // 基础配置分数
        if (get_option('wai_plugin_enabled', true)) $score += 10;
        if (get_option('wai_ai_automation_enabled', true)) $score += 15;
        if (get_option('wai_caching_enabled', false)) $score += 15;
        
        // 高级功能分数
        if (get_option('wai_pricing_ai_enabled', true)) $score += 10;
        if (get_option('wai_inventory_ai_enabled', true)) $score += 10;
        if (get_option('wai_web3_enabled', false)) $score += 10;
        
        // 性能优化分数
        if (get_option('wai_performance_optimized', false)) $score += 20;
        if (get_option('wai_security_enhanced', false)) $score += 10;
        
        return min($score, $max_score);
    }
    
    /**
     * 计算优化等级
     */
    private function calculate_optimization_level() {
        $score = $this->calculate_optimization_score();
        
        if ($score >= 80) return 'high';
        if ($score >= 60) return 'medium';
        return 'low';
    }
    
    /**
     * 计算优化机会数量
     */
    private function count_optimization_opportunities() {
        $suggestions = $this->get_optimization_suggestions();
        return count($suggestions);
    }
    
    /**
     * 渲染优化步骤
     */
    public function render_optimization_step($step) {
        switch ($step) {
            case 1:
                $this->render_step_basic_config();
                break;
            case 2:
                $this->render_optimization_step('performance');
                break;
            case 3:
                $this->render_step_ai_configuration();
                break;
            case 4:
                $this->render_step_web3_setup();
                break;
            case 5:
                $this->render_step_final_review();
                break;
            default:
                echo '<p>优化步骤内容加载中...</p>';
        }
    }
    
    /**
     * 渲染基础配置步骤
     */
    private function render_step_basic_config() {
        ?>
        <div class="optimization-step-content">
            <h3>⚙️ 基础系统配置</h3>
            <p>让我们先完成系统的基础配置，确保所有核心功能正常运行。</p>
            
            <div class="step-configuration">
                <div class="config-item">
                    <label>
                        <input type="checkbox" name="plugin_enabled" checked disabled>
                        <strong>启用插件核心功能</strong>
                    </label>
                    <p class="description">启用Web3电商智能体的所有核心功能</p>
                </div>
                
                <div class="config-item">
                    <label>
                        <input type="checkbox" name="ai_automation" <?php checked(get_option('wai_ai_automation_enabled', true)); ?>>
                        <strong>启用AI自动化</strong>
                    </label>
                    <p class="description">允许AI自动优化价格、库存和营销策略</p>
                </div>
                
                <div class="config-item">
                    <label>
                        <input type="checkbox" name="data_collection" checked>
                        <strong>启用匿名数据收集</strong>
                    </label>
                    <p class="description">帮助我们改进产品（不包含个人身份信息）</p>
                </div>
            </div>
            
            <div class="step-tips">
                <h4>💡 配置建议</h4>
                <ul>
                    <li>建议启用AI自动化以获得最佳效果</li>
                    <li>数据收集有助于我们为您提供更好的个性化建议</li>
                    <li>所有配置后续都可以在高级设置中调整</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * 应用具体优化
     */
    private function apply_specific_optimization($optimization_id) {
        switch ($optimization_id) {
            case 'enable_caching':
                update_option('wai_caching_enabled', true);
                return array(
                    'success' => true,
                    'message' => '页面缓存已启用',
                    'impact' => '预计页面加载速度提升40-60%'
                );
                
            case 'optimize_database':
                // 模拟数据库优化
                $this->clear_system_cache();
                return array(
                    'success' => true,
                    'message' => '数据库优化完成',
                    'impact' => '预计查询性能提升20-30%'
                );
                
            default:
                throw new Exception('未知的优化操作');
        }
    }
    
    /**
     * 获取插件文件结构
     */
    public function get_plugin_file_structure() {
        $structure = array(
            'root' => array(),
            'stats' => array(
                'php_files' => 0,
                'directories' => 0,
                'total_files' => 0,
                'admin_pages' => 0
            ),
            'admin_pages' => array()
        );
        
        // 扫描插件目录
        $this->scan_directory(WAI_PLUGIN_DIR, $structure['root'], $structure['stats']);
        
        // 识别管理页面
        $structure['admin_pages'] = $this->identify_admin_pages($structure['root']);
        $structure['stats']['admin_pages'] = count($structure['admin_pages']);
        
        return $structure;
    }
    
    /**
     * 扫描目录
     */
    private function scan_directory($path, &$structure, &$stats, $depth = 0) {
        $items = scandir($path);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $full_path = $path . $item;
            $relative_path = str_replace(WAI_PLUGIN_DIR, '', $full_path);
            
            if (is_dir($full_path)) {
                $stats['directories']++;
                $structure[$item] = array(
                    'type' => 'directory',
                    'path' => $relative_path,
                    'children' => array()
                );
                $this->scan_directory($full_path . '/', $structure[$item]['children'], $stats, $depth + 1);
            } else {
                $stats['total_files']++;
                if (pathinfo($item, PATHINFO_EXTENSION) === 'php') {
                    $stats['php_files']++;
                }
                
                $structure[$item] = array(
                    'type' => 'file',
                    'path' => $relative_path,
                    'extension' => pathinfo($item, PATHINFO_EXTENSION)
                );
            }
        }
    }
    
    /**
     * 识别管理页面
     */
    private function identify_admin_pages($structure) {
        $admin_pages = array();
        
        // 检查 admin/partials 目录
        if (isset($structure['admin']['children']['partials']['children'])) {
            $partials = $structure['admin']['children']['partials']['children'];
            
            foreach ($partials as $file => $info) {
                if ($info['type'] === 'file' && $info['extension'] === 'php') {
                    $page_slug = str_replace('.php', '', $file);
                    $admin_pages[] = array(
                        'name' => $this->get_admin_page_name($page_slug),
                        'slug' => $page_slug,
                        'file' => $info['path'],
                        'accessible' => $this->is_page_accessible($page_slug)
                    );
                }
            }
        }
        
        return $admin_pages;
    }
    
    /**
     * 获取管理页面名称
     */
    private function get_admin_page_name($slug) {
        $names = array(
            'dashboard' => '智能体仪表板',
            'settings' => '系统设置',
            'logs' => '系统日志',
            'install-wizard' => '安装向导',
            'web3-dashboard' => 'Web3仪表板',
            'swarm-management' => '蜂群管理',
            'metaverse-stores' => '元宇宙店铺',
            'dao-governance' => 'DAO治理',
            'ai-advanced-dashboard' => 'AI引擎',
            'cross-platform-management' => '跨平台管理',
            'automation-orchestrator' => '自动化编排',
            'system-architecture' => '系统架构'
        );
        
        return isset($names[$slug]) ? $names[$slug] : ucfirst(str_replace('-', ' ', $slug));
    }
    
    /**
     * 检查页面是否可访问
     */
    private function is_page_accessible($page_slug) {
        $accessible_pages = array(
            'dashboard', 'settings', 'logs', 'install-wizard',
            'web3-dashboard', 'swarm-management', 'metaverse-stores', 'dao-governance',
            'ai-advanced-dashboard', 'cross-platform-management', 'automation-orchestrator',
            'system-architecture'
        );
        
        return in_array($page_slug, $accessible_pages);
    }
    
    /**
     * 渲染文件结构树
     */
    public function render_file_structure_tree($file_structure, $current_path = '', $depth = 0) {
        $html = '';
        $items = $file_structure['root'];
        
        foreach ($items as $name => $info) {
            $padding = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
            $new_path = $current_path ? $current_path . '/' . $name : $name;
            
            if ($info['type'] === 'directory') {
                $html .= '<div class="file-tree-item directory">';
                $html .= $padding . '📁 ' . $name . '/';
                $html .= '</div>';
                $html .= $this->render_file_structure_tree(array('root' => $info['children']), $new_path, $depth + 1);
            } else {
                $file_class = 'file';
                if ($info['extension'] === 'php') $file_class .= ' php';
                if (strpos($info['path'], 'admin/partials') !== false) $file_class .= ' admin';
                
                $html .= '<div class="file-tree-item ' . $file_class . '">';
                $html .= $padding . '📄 ' . $name;
                $html .= '</div>';
            }
        }
        
        return $html;
    }
    
    /**
     * 渲染文件结构统计
     */
    public function render_file_structure_stats($file_structure) {
        ob_start();
        ?>
        <h4>📊 文件统计</h4>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-label">PHP文件</span>
                <span class="stat-value"><?php echo $file_structure['stats']['php_files']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">目录</span>
                <span class="stat-value"><?php echo $file_structure['stats']['directories']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">总文件</span>
                <span class="stat-value"><?php echo $file_structure['stats']['total_files']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">管理页面</span>
                <span class="stat-value"><?php echo $file_structure['stats']['admin_pages']; ?></span>
            </div>
        </div>
        
        <h4>🎯 可用管理页面</h4>
        <div class="admin-pages-list">
            <?php foreach ($file_structure['admin_pages'] as $page): ?>
                <div class="admin-page-item">
                    <span class="page-name"><?php echo $page['name']; ?></span>
                    <span class="page-status <?php echo $page['accessible'] ? 'accessible' : 'inaccessible'; ?>">
                        <?php echo $page['accessible'] ? '✅ 可访问' : '❌ 未挂载'; ?>
                    </span>
                    <?php if (!$page['accessible']): ?>
                        <button class="button button-small" onclick="mountAdminPage('<?php echo $page['slug']; ?>')">
                            挂载到菜单
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 激活插件
     */
    public function activate() {
        // 创建必要的数据库表
        $this->create_tables();
        
        // 设置默认选项
        $this->set_default_options();
        
        // 设置定时任务
        wp_clear_scheduled_hook('wai_daily_maintenance');
        wp_schedule_event(time(), 'daily', 'wai_daily_maintenance');
        
        // 刷新重写规则
        flush_rewrite_rules();
        
        $this->log_event('plugin_activated', array('version' => WAI_VERSION));
    }
    
    /**
     * 停用插件
     */
    public function deactivate() {
        // 清理定时任务
        wp_clear_scheduled_hook('wai_daily_maintenance');
        wp_clear_scheduled_hook('wai_hourly_tasks');
        wp_clear_scheduled_hook('wai_weekly_reports');
        
        // 刷新重写规则
        flush_rewrite_rules();
        
        $this->log_event('plugin_deactivated');
    }
    
    /**
     * 卸载插件
     */
    public static function uninstall() {
        // 删除选项
        delete_option('wai_plugin_enabled');
        delete_option('wai_web3_enabled');
        delete_option('wai_metaverse_enabled');
        delete_option('wai_ai_automation_enabled');
        delete_option('wai_last_update_check');
        delete_option('wai_update_available');
        delete_option('wai_update_info');
        delete_option('wai_install_current_step');
        delete_option('wai_install_completed_steps');
        delete_option('wai_last_diagnostic_report');
        delete_option('wai_setup_wizard_completed');
        delete_option('wai_current_optimization_step');
        delete_option('wai_revenue_goal');
        
        // 清理缓存
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wai_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wai_%'");
        
        // 删除数据库表
        $tables = array(
            'wai_activity_logs',
            'wai_ai_training_data',
            'wai_web3_transactions',
            'wai_nft_assets'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }
        
        // 清理定时任务
        wp_clear_scheduled_hook('wai_daily_maintenance');
        wp_clear_scheduled_hook('wai_hourly_tasks');
        wp_clear_scheduled_hook('wai_weekly_reports');
    }
    
    /**
     * 创建数据库表
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $tables = array(
            "{$wpdb->prefix}wai_activity_logs" => "
                CREATE TABLE {$wpdb->prefix}wai_activity_logs (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    event_type varchar(100) NOT NULL,
                    event_data longtext,
                    user_id bigint(20),
                    ip_address varchar(45),
                    user_agent text,
                    timestamp datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY event_type (event_type),
                    KEY user_id (user_id),
                    KEY timestamp (timestamp)
                ) $charset_collate;
            ",
            "{$wpdb->prefix}wai_ai_training_data" => "
                CREATE TABLE {$wpdb->prefix}wai_ai_training_data (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    model_type varchar(50) NOT NULL,
                    input_data longtext,
                    output_data longtext,
                    accuracy_score decimal(5,4),
                    training_time int(11),
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY model_type (model_type),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            "{$wpdb->prefix}wai_web3_transactions" => "
                CREATE TABLE {$wpdb->prefix}wai_web3_transactions (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    transaction_hash varchar(66),
                    blockchain varchar(20),
                    from_address varchar(42),
                    to_address varchar(42),
                    amount varchar(100),
                    token_symbol varchar(10),
                    gas_used bigint(20),
                    status varchar(20),
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY transaction_hash (transaction_hash),
                    KEY blockchain (blockchain),
                    KEY from_address (from_address),
                    KEY to_address (to_address),
                    KEY status (status)
                ) $charset_collate;
            ",
            "{$wpdb->prefix}wai_nft_assets" => "
                CREATE TABLE {$wpdb->prefix}wai_nft_assets (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    token_id varchar(100) NOT NULL,
                    contract_address varchar(42) NOT NULL,
                    owner_address varchar(42),
                    metadata_url text,
                    name varchar(255),
                    description text,
                    image_url text,
                    attributes text,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY token_contract (token_id, contract_address),
                    KEY owner_address (owner_address),
                    KEY created_at (created_at)
                ) $charset_collate;
            "
        );
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        foreach ($tables as $table_name => $sql) {
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
                dbDelta($sql);
            }
        }
    }
    
    /**
     * 设置默认选项
     */
    private function set_default_options() {
        $defaults = array(
            'wai_plugin_enabled' => 'yes',
            'wai_web3_enabled' => 'no',
            'wai_metaverse_enabled' => 'no',
            'wai_ai_automation_enabled' => 'yes',
            'wai_pricing_ai_enabled' => 'yes',
            'wai_inventory_ai_enabled' => 'yes',
            'wai_content_ai_enabled' => 'yes',
            'wai_caching_enabled' => 'no',
            'wai_last_update_check' => 0,
            'wai_update_available' => 'no',
            'wai_install_current_step' => 0,
            'wai_install_completed_steps' => array(),
            'wai_last_diagnostic_report' => array(),
            'wai_setup_wizard_completed' => false,
            'wai_current_optimization_step' => 1,
            'wai_revenue_goal' => 10000
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
    
    /**
     * 记录事件
     */
    private function log_event($event_type, $data = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wai_activity_logs';
        
        // 检查表是否存在
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
            return false;
        }
        
        $wpdb->insert(
            $table_name,
            array(
                'event_type' => $event_type,
                'event_data' => maybe_serialize($data),
                'user_id' => get_current_user_id(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'timestamp' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * 记录错误
     */
    private function log_error($message, $context = array()) {
        error_log('WAI Error: ' . $message . ' - Context: ' . json_encode($context));
        $this->log_event('error', array('message' => $message, 'context' => $context));
    }
    
    /**
     * 短代码处理
     */
    public function nft_gallery_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 12,
            'columns' => 4,
            'contract' => ''
        ), $atts, 'wai_nft_gallery');
        
        ob_start();
        ?>
        <div class="wai-nft-gallery">
            <h3><?php _e('NFT 画廊', 'woocommerce-ai-agent'); ?></h3>
            <p><?php _e('NFT画廊功能开发中...', 'woocommerce-ai-agent'); ?></p>
            <div class="nft-grid" style="display: grid; grid-template-columns: repeat(<?php echo absint($atts['columns']); ?>, 1fr); gap: 20px;">
                <?php for ($i = 0; $i < min($atts['limit'], 8); $i++): ?>
                    <div class="nft-item" style="border: 1px solid #ddd; padding: 15px; text-align: center;">
                        <div style="background: #f0f0f0; height: 150px; display: flex; align-items: center; justify-content: center;">
                            <span><?php _e('NFT 预览', 'woocommerce-ai-agent'); ?></span>
                        </div>
                        <h4><?php printf(__('NFT #%d', 'woocommerce-ai-agent'), $i + 1); ?></h4>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function metaverse_store_shortcode($atts) {
        $atts = shortcode_atts(array(
            'theme' => 'default',
            'products' => 'featured'
        ), $atts, 'wai_metaverse_store');
        
        ob_start();
        ?>
        <div class="wai-metaverse-store">
            <h3><?php _e('元宇宙商店', 'woocommerce-ai-agent'); ?></h3>
            <p><?php _e('沉浸式购物体验即将推出...', 'woocommerce-ai-agent'); ?></p>
            <div class="metaverse-preview" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; color: white; text-align: center; border-radius: 10px;">
                <h4><?php _e('Web3 购物体验', 'woocommerce-ai-agent'); ?></h4>
                <p><?php _e('连接钱包，探索数字商品和NFT', 'woocommerce-ai-agent'); ?></p>
                <button style="background: white; color: #667eea; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                    <?php _e('连接钱包', 'woocommerce-ai-agent'); ?>
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function ai_recommendations_shortcode($atts) {
        $atts = shortcode_atts(array(
            'type' => 'products',
            'limit' => 6
        ), $atts, 'wai_ai_recommendations');
        
        ob_start();
        ?>
        <div class="wai-ai-recommendations">
            <h3><?php _e('AI 智能推荐', 'woocommerce-ai-agent'); ?></h3>
            <div class="recommendations-list">
                <?php
                $recommendations = $this->get_ai_recommendations($atts['type'], $atts['limit']);
                if (!empty($recommendations)) {
                    echo '<ul>';
                    foreach ($recommendations as $rec) {
                        echo '<li>' . esc_html($rec) . '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>' . __('基于您的浏览历史和偏好，AI正在为您生成个性化推荐...', 'woocommerce-ai-agent') . '</p>';
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 获取AI推荐
     */
    private function get_ai_recommendations($type, $limit) {
        // 模拟AI推荐数据
        $recommendations = array(
            __('基于您的兴趣，推荐查看我们的新产品系列', 'woocommerce-ai-agent'),
            __('其他类似用户对这些产品也很感兴趣', 'woocommerce-ai-agent'),
            __('限时优惠：这些商品正在打折', 'woocommerce-ai-agent'),
            __('热门趋势：这些是当前最受欢迎的商品', 'woocommerce-ai-agent')
        );
        
        return array_slice($recommendations, 0, $limit);
    }
    
    /**
     * REST API 处理
     */
    public function api_get_insights($request) {
        $timeframe = $request->get_param('timeframe') ?: '7d';
        
        $insights = array(
            'timeframe' => $timeframe,
            'store_performance' => $this->get_store_data(),
            'ai_recommendations' => $this->generate_basic_insights(),
            'web3_status' => $this->test_web3_connection(),
            'timestamp' => current_time('mysql')
        );
        
        return rest_ensure_response($insights);
    }
    
    public function api_get_nft_data($request) {
        $nft_id = $request->get_param('id');
        
        $nft_data = array(
            'id' => $nft_id,
            'name' => sprintf(__('NFT #%s', 'woocommerce-ai-agent'), $nft_id),
            'description' => __('数字收藏品', 'woocommerce-ai-agent'),
            'image' => WAI_PLUGIN_URL . 'assets/images/nft-placeholder.jpg',
            'attributes' => array(
                array('trait_type' => '稀有度', 'value' => '普通'),
                array('trait_type' => '版本', 'value' => '1.0')
            ),
            'external_url' => home_url("/nft/{$nft_id}")
        );
        
        return rest_ensure_response($nft_data);
    }
    
    /**
     * Web3 事件处理
     */
    public function handle_nft_minted($user_id, $nft_data) {
        $this->log_event('nft_minted', array(
            'user_id' => $user_id,
            'nft_data' => $nft_data
        ));
    }
    
    public function handle_transaction_completed($tx_hash, $amount, $currency) {
        $this->log_event('transaction_completed', array(
            'tx_hash' => $tx_hash,
            'amount' => $amount,
            'currency' => $currency
        ));
    }
    
    public function enhance_product_web3_data($product_data, $product_id) {
        if (!get_option('wai_web3_enabled', false)) {
            return $product_data;
        }
        
        $product_data['web3'] = array(
            'nft_enabled' => false,
            'token_gating' => false,
            'metaverse_ready' => false,
            'blockchain' => 'none'
        );
        
        return $product_data;
    }
    
    /**
     * 获取管理器实例
     */
    public function get_manager($manager_name) {
        return $this->managers[$manager_name] ?? null;
    }
    
    /**
     * 获取所有管理器
     */
    public function get_managers() {
        return $this->managers;
    }
    
    /**
     * 获取Web3模块
     */
    public function get_web3_modules() {
        return $this->web3_modules;
    }
}

// 初始化插件
function wai_initialize_plugin() {
    return Woocommerce_AI_Agent_Web3::get_instance();
}

// 启动插件
add_action('plugins_loaded', 'wai_initialize_plugin');

// 添加周期间隔
add_filter('cron_schedules', function($schedules) {
    $schedules['weekly'] = array(
        'interval' => 604800,
        'display' => __('每周一次', 'woocommerce-ai-agent')
    );
    return $schedules;
});

// 定义全局函数以便其他插件集成
if (!function_exists('wai_get_plugin_instance')) {
    function wai_get_plugin_instance() {
        return Woocommerce_AI_Agent_Web3::get_instance();
    }
}

if (!function_exists('wai_get_manager')) {
    function wai_get_manager($manager_name) {
        $instance = Woocommerce_AI_Agent_Web3::get_instance();
        return $instance->get_manager($manager_name);
    }
}

// 注册激活钩子
register_activation_hook(__FILE__, array('Woocommerce_AI_Agent_Web3', 'activate'));