<?php
/**
 * AI电商智能体 - 安装向导类
 * 提供详细的配置检查和逐步引导
 */

class AI_Ecommerce_Agent_Install_Wizard {
    
    private $steps;
    private $current_step;
    private $completed_steps;
    
    public function __construct() {
        $this->setup_steps();
        $this->current_step = $this->get_current_step();
        $this->completed_steps = $this->get_completed_steps();
        
        add_action('admin_menu', array($this, 'add_wizard_page'));
        add_action('admin_init', array($this, 'handle_wizard_actions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_wizard_scripts'));
    }
    
    /**
     * 设置安装步骤
     */
    private function setup_steps() {
        $this->steps = array(
            'welcome' => array(
                'name' => __('欢迎使用', 'ai-ecommerce-agent'),
                'description' => __('开始设置您的AI电商智能体', 'ai-ecommerce-agent'),
                'icon' => '🎯'
            ),
            'requirements' => array(
                'name' => __('系统检查', 'ai-ecommerce-agent'),
                'description' => __('检查系统环境和依赖', 'ai-ecommerce-agent'),
                'icon' => '🔍'
            ),
            'woocommerce' => array(
                'name' => __('WooCommerce', 'ai-ecommerce-agent'),
                'description' => __('配置电商平台连接', 'ai-ecommerce-agent'),
                'icon' => '🛒'
            ),
            'matomo' => array(
                'name' => __('Matomo分析', 'ai-ecommerce-agent'),
                'description' => __('设置网站分析工具', 'ai-ecommerce-agent'),
                'icon' => '📊'
            ),
            'ai_engine' => array(
                'name' => __('AI引擎', 'ai-ecommerce-agent'),
                'description' => __('配置AI决策引擎', 'ai-ecommerce-agent'),
                'icon' => '🤖'
            ),
            'replit' => array(
                'name' => __('Replit集成', 'ai-ecommerce-agent'),
                'description' => __('设置外部计算服务', 'ai-ecommerce-agent'),
                'icon' => '⚡'
            ),
            'github' => array(
                'name' => __('GitHub集成', 'ai-ecommerce-agent'),
                'description' => __('配置代码版本控制', 'ai-ecommerce-agent'),
                'icon' => '📝'
            ),
            'complete' => array(
                'name' => __('完成设置', 'ai-ecommerce-agent'),
                'description' => __('开始使用智能体', 'ai-ecommerce-agent'),
                'icon' => '🎉'
            )
        );
    }
    
    /**
     * 获取当前步骤
     */
    private function get_current_step() {
        $saved_step = get_option('ai_agent_wizard_current_step', 'welcome');
        return isset($_GET['step']) ? sanitize_text_field($_GET['step']) : $saved_step;
    }
    
    /**
     * 获取已完成步骤
     */
    private function get_completed_steps() {
        return get_option('ai_agent_wizard_completed_steps', array());
    }
    
    /**
     * 添加向导页面
     */
    public function add_wizard_page() {
        // 只在安装未完成时显示向导
        if (!$this->is_installation_complete()) {
            add_submenu_page(
                null, // 不显示在菜单中
                __('AI电商智能体 - 安装向导', 'ai-ecommerce-agent'),
                __('安装向导', 'ai-ecommerce-agent'),
                'manage_options',
                'ai-ecommerce-agent-wizard',
                array($this, 'display_wizard_page')
            );
        }
    }
    
    /**
     * 处理向导操作
     */
    public function handle_wizard_actions() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'ai-ecommerce-agent-wizard') {
            return;
        }
        
        // 处理步骤导航
        if (isset($_POST['wizard_action'])) {
            $this->handle_wizard_submission();
        }
        
        // 处理跳过向导
        if (isset($_GET['skip_wizard'])) {
            $this->complete_installation();
            wp_redirect(admin_url('admin.php?page=ai-ecommerce-agent'));
            exit;
        }
    }
    
    /**
     * 显示向导页面
     */
    public function display_wizard_page() {
        // 如果安装已完成，重定向到主页面
        if ($this->is_installation_complete()) {
            wp_redirect(admin_url('admin.php?page=ai-ecommerce-agent'));
            return;
        }
        
        include AI_ECOMMERCE_AGENT_PLUGIN_DIR . 'admin/partials/install-wizard.php';
    }
    
    /**
     * 处理向导提交
     */
    private function handle_wizard_submission() {
        $action = sanitize_text_field($_POST['wizard_action']);
        $nonce = sanitize_text_field($_POST['_wpnonce']);
        
        if (!wp_verify_nonce($nonce, 'ai_agent_wizard_nonce')) {
            wp_die('安全验证失败');
        }
        
        switch ($action) {
            case 'next_step':
                $this->save_current_step();
                $this->go_to_next_step();
                break;
                
            case 'prev_step':
                $this->go_to_prev_step();
                break;
                
            case 'complete':
                $this->complete_installation();
                wp_redirect(admin_url('admin.php?page=ai-ecommerce-agent'));
                exit;
                break;
        }
    }
    
    /**
     * 保存当前步骤数据
     */
    private function save_current_step() {
        $step_data = array();
        
        switch ($this->current_step) {
            case 'woocommerce':
                if (isset($_POST['woocommerce_status'])) {
                    update_option('ai_agent_woocommerce_connected', true);
                    $step_data['connected'] = true;
                }
                break;
                
            case 'matomo':
                if (!empty($_POST['matomo_url']) && !empty($_POST['matomo_token'])) {
                    update_option('ai_agent_matomo_url', sanitize_text_field($_POST['matomo_url']));
                    update_option('ai_agent_matomo_token', sanitize_text_field($_POST['matomo_token']));
                    $step_data['connected'] = $this->test_matomo_connection();
                }
                break;
                
            case 'ai_engine':
                if (!empty($_POST['ai_api_key'])) {
                    update_option('ai_agent_external_ai_key', sanitize_text_field($_POST['ai_api_key']));
                    update_option('ai_agent_ai_provider', sanitize_text_field($_POST['ai_provider']));
                    $step_data['configured'] = true;
                }
                break;
                
            case 'replit':
                if (!empty($_POST['replit_url']) && !empty($_POST['replit_secret'])) {
                    update_option('ai_agent_replit_url', sanitize_text_field($_POST['replit_url']));
                    update_option('ai_agent_replit_secret', sanitize_text_field($_POST['replit_secret']));
                    $step_data['connected'] = $this->test_replit_connection();
                }
                break;
                
            case 'github':
                if (!empty($_POST['github_token']) && !empty($_POST['github_repo'])) {
                    update_option('ai_agent_github_token', sanitize_text_field($_POST['github_token']));
                    update_option('ai_agent_github_repo', sanitize_text_field($_POST['github_repo']));
                    $step_data['connected'] = $this->test_github_connection();
                }
                break;
        }
        
        // 标记步骤完成
        if (!in_array($this->current_step, $this->completed_steps)) {
            $this->completed_steps[] = $this->current_step;
            update_option('ai_agent_wizard_completed_steps', $this->completed_steps);
        }
        
        update_option('ai_agent_wizard_step_' . $this->current_step . '_data', $step_data);
    }
    
    /**
     * 前往下一步
     */
    private function go_to_next_step() {
        $step_keys = array_keys($this->steps);
        $current_index = array_search($this->current_step, $step_keys);
        
        if ($current_index < count($step_keys) - 1) {
            $next_step = $step_keys[$current_index + 1];
            update_option('ai_agent_wizard_current_step', $next_step);
            wp_redirect($this->get_wizard_url($next_step));
            exit;
        }
    }
    
    /**
     * 前往上一步
     */
    private function go_to_prev_step() {
        $step_keys = array_keys($this->steps);
        $current_index = array_search($this->current_step, $step_keys);
        
        if ($current_index > 0) {
            $prev_step = $step_keys[$current_index - 1];
            update_option('ai_agent_wizard_current_step', $prev_step);
            wp_redirect($this->get_wizard_url($prev_step));
            exit;
        }
    }
    
    /**
     * 完成安装
     */
    private function complete_installation() {
        update_option('ai_agent_installation_complete', true);
        update_option('ai_agent_wizard_completed', true);
        update_option('ai_agent_first_run', true);
        
        // 设置默认的自动化选项
        update_option('ai_agent_auto_optimize', true);
        update_option('ai_agent_optimization_level', 'medium');
        
        // 记录安装时间
        update_option('ai_agent_install_time', current_time('mysql'));
    }
    
    /**
     * 检查安装是否完成
     */
    private function is_installation_complete() {
        return get_option('ai_agent_installation_complete', false);
    }
    
    /**
     * 获取向导URL
     */
    private function get_wizard_url($step = '') {
        $url = admin_url('admin.php?page=ai-ecommerce-agent-wizard');
        if ($step) {
            $url .= '&step=' . $step;
        }
        return $url;
    }
    
    /**
     * 检查系统要求
     */
    public function check_requirements() {
        $requirements = array(
            'php_version' => array(
                'name' => __('PHP版本', 'ai-ecommerce-agent'),
                'required' => '7.4',
                'current' => PHP_VERSION,
                'passed' => version_compare(PHP_VERSION, '7.4', '>=')
            ),
            'wordpress_version' => array(
                'name' => __('WordPress版本', 'ai-ecommerce-agent'),
                'required' => '5.8',
                'current' => get_bloginfo('version'),
                'passed' => version_compare(get_bloginfo('version'), '5.8', '>=')
            ),
            'woocommerce' => array(
                'name' => __('WooCommerce', 'ai-ecommerce-agent'),
                'required' => __('已安装', 'ai-ecommerce-agent'),
                'current' => class_exists('WooCommerce') ? __('已安装', 'ai-ecommerce-agent') : __('未安装', 'ai-ecommerce-agent'),
                'passed' => class_exists('WooCommerce')
            ),
            'curl' => array(
                'name' => __('cURL扩展', 'ai-ecommerce-agent'),
                'required' => __('已启用', 'ai-ecommerce-agent'),
                'current' => function_exists('curl_version') ? __('已启用', 'ai-ecommerce-agent') : __('未启用', 'ai-ecommerce-agent'),
                'passed' => function_exists('curl_version')
            ),
            'json' => array(
                'name' => __('JSON支持', 'ai-ecommerce-agent'),
                'required' => __('已启用', 'ai-ecommerce-agent'),
                'current' => function_exists('json_encode') ? __('已启用', 'ai-ecommerce-agent') : __('未启用', 'ai-ecommerce-agent'),
                'passed' => function_exists('json_encode')
            ),
            'memory_limit' => array(
                'name' => __('内存限制', 'ai-ecommerce-agent'),
                'required' => '128M',
                'current' => ini_get('memory_limit'),
                'passed' => $this->convert_memory_size(ini_get('memory_limit')) >= $this->convert_memory_size('128M')
            )
        );
        
        return $requirements;
    }
    
    /**
     * 转换内存大小
     */
    private function convert_memory_size($size) {
        $unit = strtolower(substr($size, -1));
        $value = intval(substr($size, 0, -1));
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * 测试Matomo连接
     */
    private function test_matomo_connection() {
        // 这里实现Matomo API连接测试
        // 暂时返回true
        return true;
    }
    
    /**
     * 测试Replit连接
     */
    private function test_replit_connection() {
        // 这里实现Replit连接测试
        // 暂时返回true
        return true;
    }
    
    /**
     * 测试GitHub连接
     */
    private function test_github_connection() {
        // 这里实现GitHub API连接测试
        // 暂时返回true
        return true;
    }
    
    /**
     * 加载向导脚本和样式
     */
    public function enqueue_wizard_scripts($hook) {
        if (strpos($hook, 'ai-ecommerce-agent-wizard') === false) {
            return;
        }
        
        wp_enqueue_style('ai-agent-wizard', AI_ECOMMERCE_AGENT_PLUGIN_URL . 'admin/css/wizard.css', array(), AI_ECOMMERCE_AGENT_VERSION);
        wp_enqueue_script('ai-agent-wizard', AI_ECOMMERCE_AGENT_PLUGIN_URL . 'admin/js/wizard.js', array('jquery'), AI_ECOMMERCE_AGENT_VERSION, true);
        
        wp_localize_script('ai-agent-wizard', 'ai_agent_wizard', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_agent_wizard_ajax'),
            'testing_connection' => __('测试连接中...', 'ai-ecommerce-agent'),
            'connection_success' => __('连接成功', 'ai-ecommerce-agent'),
            'connection_failed' => __('连接失败', 'ai-ecommerce-agent')
        ));
    }
    
    /**
     * 获取步骤状态
     */
    public function get_step_status($step) {
        if (in_array($step, $this->completed_steps)) {
            return 'completed';
        } elseif ($step === $this->current_step) {
            return 'current';
        } else {
            return 'pending';
        }
    }
    
    /**
     * 获取进度百分比
     */
    public function get_progress_percentage() {
        $total_steps = count($this->steps);
        $completed_steps = count($this->completed_steps);
        
        // 当前步骤也算部分完成
        if ($this->current_step !== 'welcome' && $this->current_step !== 'complete') {
            $completed_steps += 0.5;
        }
        
        return min(round(($completed_steps / $total_steps) * 100), 100);
    }
}
?>
