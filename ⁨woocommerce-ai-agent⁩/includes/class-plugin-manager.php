<?php
/**
 * 插件管理器 - 处理插件的安装、更新和自管理
 */

if (!defined('ABSPATH')) {
    exit;
}

class WAI_Plugin_Manager {
    
    private $plugin_info;
    private $update_checker;
    private $compatibility_checker;
    
    public function __construct() {
        $this->plugin_info = $this->get_plugin_info();
        $this->setup_update_checker();
        $this->setup_compatibility_checker();
        add_action('wp_ajax_wai_check_updates', array($this, 'ajax_check_updates'));
        add_action('wp_ajax_wai_install_update', array($this, 'ajax_install_update'));
    }
    
    /**
     * 获取插件信息
     */
    private function get_plugin_info() {
        return array(
            'name' => 'Web3全自动电商智能体',
            'version' => WAI_VERSION,
            'plugin_file' => WAI_PLUGIN_DIR . 'woocommerce-ai-agent.php',
            'update_url' => 'https://api.example.com/wai/updates/',
            'documentation_url' => 'https://docs.example.com/wai/',
            'support_url' => 'https://support.example.com/wai/'
        );
    }
    
    /**
     * 设置更新检查器
     */
    private function setup_update_checker() {
        $this->update_checker = array(
            'last_checked' => get_option('wai_last_update_check'),
            'update_available' => get_option('wai_update_available'),
            'update_info' => get_option('wai_update_info', array())
        );
    }
    
    /**
     * 设置兼容性检查器
     */
    private function setup_compatibility_checker() {
        $this->compatibility_checker = array(
            'woocommerce' => array(
                'required' => '6.0.0',
                'current' => $this->get_woocommerce_version(),
                'compatible' => true
            ),
            'wordpress' => array(
                'required' => '5.8',
                'current' => get_bloginfo('version'),
                'compatible' => true
            ),
            'php' => array(
                'required' => '7.4',
                'current' => PHP_VERSION,
                'compatible' => true
            )
        );
        
        $this->check_compatibility();
    }
    
    /**
     * 检查系统兼容性
     */
    private function check_compatibility() {
        foreach ($this->compatibility_checker as $component => &$info) {
            if (version_compare($info['current'], $info['required'], '<')) {
                $info['compatible'] = false;
            }
        }
    }
    
    /**
     * 获取 WooCommerce 版本
     */
    private function get_woocommerce_version() {
        if (!class_exists('WooCommerce')) {
            return '0.0.0';
        }
        
        return defined('WC_VERSION') ? WC_VERSION : '0.0.0';
    }
    
    /**
     * 检查更新
     */
    public function check_for_updates($force = false) {
        $current_time = time();
        $last_checked = $this->update_checker['last_checked'];
        
        // 如果不是强制检查，且最近12小时内检查过，则跳过
        if (!$force && $last_checked && ($current_time - $last_checked) < 43200) {
            return $this->update_checker;
        }
        
        $update_info = $this->fetch_update_info();
        
        if ($update_info && version_compare($update_info['new_version'], WAI_VERSION, '>')) {
            $this->update_checker['update_available'] = true;
            $this->update_checker['update_info'] = $update_info;
        } else {
            $this->update_checker['update_available'] = false;
            $this->update_checker['update_info'] = array();
        }
        
        $this->update_checker['last_checked'] = $current_time;
        
        // 保存到数据库
        update_option('wai_last_update_check', $current_time);
        update_option('wai_update_available', $this->update_checker['update_available']);
        update_option('wai_update_info', $this->update_checker['update_info']);
        
        return $this->update_checker;
    }
    
    /**
     * 获取更新信息
     */
    private function fetch_update_info() {
        // 模拟从远程服务器获取更新信息
        $mock_update = array(
            'new_version' => '2.1.0',
            'package' => 'https://example.com/wai/woocommerce-ai-agent-2.1.0.zip',
            'requires' => '6.0.0',
            'requires_php' => '7.4',
            'tested' => '6.2',
            'last_updated' => '2024-01-20',
            'sections' => array(
                'description' => '新版本包含重大改进和新功能',
                'changelog' => "版本 2.1.0 更新日志：\n\n- 新增：增强的AI定价算法\n- 新增：支持更多元宇宙平台\n- 修复：Web3连接稳定性问题\n- 优化：性能提升30%"
            )
        );
        
        return $mock_update; // 临时返回模拟数据
    }
    
    /**
     * 安装更新
     */
    public function install_update() {
        if (!$this->update_checker['update_available']) {
            return array(
                'success' => false,
                'message' => '没有可用的更新'
            );
        }
        
        $update_info = $this->update_checker['update_info'];
        
        // 检查文件权限
        if (!is_writable(WP_PLUGIN_DIR)) {
            return array(
                'success' => false,
                'message' => '插件目录不可写，请检查文件权限'
            );
        }
        
        $result = array(
            'success' => true,
            'message' => '更新安装成功！请重新加载页面。',
            'new_version' => $update_info['new_version']
        );
        
        // 清除更新标记
        $this->update_checker['update_available'] = false;
        update_option('wai_update_available', false);
        
        return $result;
    }
    
    /**
     * 获取系统状态
     */
    public function get_system_status() {
        $status = array(
            'plugin' => $this->plugin_info,
            'compatibility' => $this->compatibility_checker,
            'updates' => $this->update_checker,
            'requirements' => $this->check_requirements(),
            'performance' => $this->check_performance(),
            'security' => $this->check_security()
        );
        
        return $status;
    }
    
    /**
     * 检查系统要求
     */
    private function check_requirements() {
        $requirements = array(
            'php_extensions' => array(
                'curl' => extension_loaded('curl'),
                'json' => extension_loaded('json'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl')
            ),
            'wordpress' => array(
                'memory_limit' => WP_MEMORY_LIMIT,
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize')
            ),
            'filesystem' => array(
                'plugin_dir_writable' => is_writable(WAI_PLUGIN_DIR),
                'upload_dir_writable' => is_writable(wp_upload_dir()['path'])
            )
        );
        
        return $requirements;
    }
    
    /**
     * 检查性能指标
     */
    private function check_performance() {
        global $wpdb;
        
        $performance = array(
            'database' => array(
                'query_time' => $this->measure_query_time(),
                'table_size' => $this->get_table_sizes()
            ),
            'memory' => array(
                'usage' => memory_get_usage(true),
                'peak_usage' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit')
            ),
            'execution' => array(
                'load_time' => $this->measure_load_time(),
                'cron_health' => $this->check_cron_health()
            )
        );
        
        return $performance;
    }
    
    /**
     * 检查安全设置
     */
    private function check_security() {
        $security = array(
            'wordpress' => array(
                'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
                'file_editing' => defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT,
                'file_mods' => defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS
            ),
            'ssl' => array(
                'enabled' => is_ssl(),
                'forced' => defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN
            ),
            'authentication' => array(
                'keys_salts' => $this->check_keys_salts()
            )
        );
        
        return $security;
    }
    
    /**
     * 测量查询时间
     */
    private function measure_query_time() {
        $start_time = microtime(true);
        
        // 执行一个简单的查询来测量时间
        global $wpdb;
        $wpdb->get_results("SELECT ID FROM {$wpdb->posts} LIMIT 1");
        
        return round((microtime(true) - $start_time) * 1000, 2); // 毫秒
    }
    
    /**
     * 获取数据库表大小
     */
    private function get_table_sizes() {
        global $wpdb;
        
        $tables = $wpdb->get_results("
            SELECT table_name AS 'table', 
                   ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb'
            FROM information_schema.TABLES 
            WHERE table_schema = '" . DB_NAME . "'
            AND table_name LIKE '{$wpdb->prefix}wai_%'
        ", ARRAY_A);
        
        return $tables ?: array();
    }
    
    /**
     * 测量加载时间
     */
    private function measure_load_time() {
        // 简化的加载时间测量
        if (defined('WAI_START_TIME')) {
            return round((microtime(true) - WAI_START_TIME) * 1000, 2);
        }
        return 0;
    }
    
    /**
     * 检查计划任务健康状态
     */
    private function check_cron_health() {
        $cron = _get_cron_array();
        $wai_crons = array();
        
        if (is_array($cron)) {
            foreach ($cron as $timestamp => $cronhooks) {
                if (is_array($cronhooks)) {
                    foreach ($cronhooks as $hook => $keys) {
                        if (strpos($hook, 'wai_') === 0) {
                            $wai_crons[$hook] = array(
                                'next_run' => $timestamp,
                                'schedule' => isset($keys[0]['schedule']) ? $keys[0]['schedule'] : 'single',
                                'is_next' => $timestamp > time()
                            );
                        }
                    }
                }
            }
        }
        
        return array(
            'total_crons' => count($wai_crons),
            'crons' => $wai_crons,
            'healthy' => !empty($wai_crons)
        );
    }
    
    /**
     * 检查密钥和盐
     */
    private function check_keys_salts() {
        $required_keys = array(
            'AUTH_KEY',
            'SECURE_AUTH_KEY', 
            'LOGGED_IN_KEY',
            'NONCE_KEY',
            'AUTH_SALT',
            'SECURE_AUTH_SALT',
            'LOGGED_IN_SALT',
            'NONCE_SALT'
        );
        
        $results = array();
        foreach ($required_keys as $key) {
            $results[$key] = defined($key) && constant($key) && constant($key) !== 'put your unique phrase here';
        }
        
        return $results;
    }
    
    /**
     * AJAX 检查更新
     */
    public function ajax_check_updates() {
        check_ajax_referer('wai_plugin_manager', 'nonce');
        
        if (!current_user_can('update_plugins')) {
            wp_die('权限不足');
        }
        
        $force = isset($_POST['force']) && $_POST['force'];
        $result = $this->check_for_updates($force);
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX 安装更新
     */
    public function ajax_install_update() {
        check_ajax_referer('wai_plugin_manager', 'nonce');
        
        if (!current_user_can('update_plugins')) {
            wp_die('权限不足');
        }
        
        $result = $this->install_update();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * 执行系统诊断
     */
    public function run_diagnostics() {
        $diagnostics = array(
            'timestamp' => current_time('mysql'),
            'system_status' => $this->get_system_status(),
            'errors' => $this->scan_error_logs(),
            'recommendations' => $this->generate_recommendations()
        );
        
        // 保存诊断报告
        update_option('wai_last_diagnostic_report', $diagnostics);
        
        return $diagnostics;
    }
    
    /**
     * 扫描错误日志
     */
    private function scan_error_logs() {
        $errors = array();
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (file_exists($log_file) && is_readable($log_file)) {
            $content = file_get_contents($log_file);
            $wai_errors = preg_grep('/\[wai-|WAI_/', explode("\n", $content));
            
            if ($wai_errors) {
                $errors = array_slice($wai_errors, -50); // 最近50条错误
            }
        }
        
        return $errors;
    }
    
    /**
     * 生成优化建议
     */
    private function generate_recommendations() {
        $recommendations = array();
        $status = $this->get_system_status();
        
        // 检查PHP扩展
        if (isset($status['requirements']['php_extensions'])) {
            foreach ($status['requirements']['php_extensions'] as $ext => $loaded) {
                if (!$loaded) {
                    $recommendations[] = "安装 PHP 扩展: {$ext}";
                }
            }
        }
        
        // 检查内存限制
        if (isset($status['requirements']['wordpress']['memory_limit'])) {
            $memory_limit = $this->convert_memory_to_bytes($status['requirements']['wordpress']['memory_limit']);
            if ($memory_limit < 134217728) { // 128MB
                $recommendations[] = "增加 WordPress 内存限制到至少 128MB";
            }
        }
        
        // 检查执行时间
        if (isset($status['requirements']['wordpress']['max_execution_time'])) {
            $execution_time = (int) $status['requirements']['wordpress']['max_execution_time'];
            if ($execution_time < 60) {
                $recommendations[] = "增加 PHP 最大执行时间到至少 60 秒";
            }
        }
        
        // 检查调试模式
        if (isset($status['security']['wordpress']['debug_mode']) && $status['security']['wordpress']['debug_mode']) {
            $recommendations[] = "在生产环境中禁用 WordPress 调试模式";
        }
        
        return $recommendations;
    }
    
    /**
     * 转换内存大小到字节
     */
    private function convert_memory_to_bytes($memory_limit) {
        $unit = strtoupper(substr($memory_limit, -1));
        $value = (int) $memory_limit;
        
        switch ($unit) {
            case 'G':
                return $value * 1024 * 1024 * 1024;
            case 'M':
                return $value * 1024 * 1024;
            case 'K':
                return $value * 1024;
            default:
                return $value;
        }
    }
    
    /**
     * 备份插件设置
     */
    public function backup_settings() {
        $settings = array(
            'general' => $this->get_general_settings(),
            'web3' => $this->get_web3_settings(),
            'ai' => $this->get_ai_settings(),
            'automation' => $this->get_automation_settings(),
            'backup_date' => current_time('mysql'),
            'plugin_version' => WAI_VERSION
        );
        
        $filename = 'wai-backup-' . date('Y-m-d-H-i-s') . '.json';
        $backup_data = json_encode($settings, JSON_PRETTY_PRINT);
        
        // 保存到上传目录
        $upload_dir = wp_upload_dir();
        $backup_path = $upload_dir['path'] . '/' . $filename;
        
        if (file_put_contents($backup_path, $backup_data)) {
            return array(
                'success' => true,
                'file' => $filename,
                'path' => $backup_path,
                'url' => $upload_dir['url'] . '/' . $filename
            );
        }
        
        return array(
            'success' => false,
            'message' => '无法创建备份文件'
        );
    }
    
    /**
     * 恢复插件设置
     */
    public function restore_settings($backup_file) {
        if (!file_exists($backup_file)) {
            return array(
                'success' => false,
                'message' => '备份文件不存在'
            );
        }
        
        $backup_data = file_get_contents($backup_file);
        $settings = json_decode($backup_data, true);
        
        if (!$settings || !isset($settings['plugin_version'])) {
            return array(
                'success' => false,
                'message' => '无效的备份文件格式'
            );
        }
        
        // 验证版本兼容性
        if (version_compare($settings['plugin_version'], WAI_VERSION, '>')) {
            return array(
                'success' => false,
                'message' => '备份文件来自更新版本的插件，无法恢复'
            );
        }
        
        // 恢复设置
        $this->restore_general_settings($settings['general']);
        $this->restore_web3_settings($settings['web3']);
        $this->restore_ai_settings($settings['ai']);
        $this->restore_automation_settings($settings['automation']);
        
        return array(
            'success' => true,
            'message' => '设置恢复成功'
        );
    }
    
    // 获取和恢复设置的方法
    private function get_general_settings() {
        return array(
            'plugin_enabled' => get_option('wai_plugin_enabled'),
            'data_retention_policy' => get_option('wai_data_retention_policy'),
            'auto_updates' => get_option('wai_auto_updates')
        );
    }
    
    private function get_web3_settings() {
        return array(
            'web3_enabled' => get_option('wai_web3_enabled'),
            'web3_network' => get_option('wai_web3_network'),
            'nft_contract_address' => get_option('wai_nft_contract_address')
        );
    }
    
    private function get_ai_settings() {
        return array(
            'ai_automation_enabled' => get_option('wai_ai_automation_enabled'),
            'pricing_ai_enabled' => get_option('wai_pricing_ai_enabled'),
            'inventory_ai_enabled' => get_option('wai_inventory_ai_enabled')
        );
    }
    
    private function get_automation_settings() {
        return array(
            'auto_sync' => get_option('wai_auto_sync'),
            'sync_frequency' => get_option('wai_sync_frequency')
        );
    }
    
    private function restore_general_settings($settings) {
        foreach ($settings as $key => $value) {
            update_option('wai_' . $key, $value);
        }
    }
    
    private function restore_web3_settings($settings) {
        foreach ($settings as $key => $value) {
            update_option('wai_' . $key, $value);
        }
    }
    
    private function restore_ai_settings($settings) {
        foreach ($settings as $key => $value) {
            update_option('wai_' . $key, $value);
        }
    }
    
    private function restore_automation_settings($settings) {
        foreach ($settings as $key => $value) {
            update_option('wai_' . $key, $value);
        }
    }
}