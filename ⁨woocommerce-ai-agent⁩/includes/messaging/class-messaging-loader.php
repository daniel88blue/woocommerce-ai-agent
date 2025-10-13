<?php
if (!defined('ABSPATH')) {
    exit;
}

class WAI_Messaging_Loader {
    private static $instance = null;
    private $modules = [];
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_modules();
    }
    
    /**
     * 加载所有消息模块
     */
    private function load_modules() {
        // 加载AI CTO平台
        if (file_exists(WAI_PLUGIN_DIR . 'includes/messaging/class-ai-cto-platform.php')) {
            require_once WAI_PLUGIN_DIR . 'includes/messaging/class-ai-cto-platform.php';
            $this->modules['ai_cto'] = WAI_AI_CTO_Platform::get_instance();
        }
        
        // 可以在这里添加其他消息平台模块
        // $this->modules['wechat'] = WAI_WeChat_Platform::get_instance();
        // $this->modules['slack'] = WAI_Slack_Platform::get_instance();
    }
    
    /**
     * 获取所有模块
     */
    public function get_modules() {
        return $this->modules;
    }
    
    /**
     * 获取特定模块
     */
    public function get_module($module_name) {
        return $this->modules[$module_name] ?? null;
    }
}