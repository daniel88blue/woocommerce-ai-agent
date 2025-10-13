<?php
/**
 * 通用消息平台接口
 */

if (!defined('ABSPATH')) {
    exit;
}

interface WAI_Message_Platform_Interface {
    public function send_message($content, $type = 'text', $options = array());
    public function receive_message($input_data);
    public function is_connected();
    public function get_platform_name();
}

abstract class WAI_Abstract_Message_Platform implements WAI_Message_Platform_Interface {
    protected $platform_id;
    protected $platform_name;
    protected $is_connected = false;
    protected $config = array();
    
    public function __construct($platform_id, $config = array()) {
        $this->platform_id = $platform_id;
        $this->config = $config;
        $this->init();
    }
    
    abstract protected function init();
    abstract public function send_message($content, $type = 'text', $options = array());
    abstract public function receive_message($input_data);
    
    public function is_connected() {
        return $this->is_connected;
    }
    
    public function get_platform_name() {
        return $this->platform_name;
    }
    
    public function get_platform_id() {
        return $this->platform_id;
    }
    
    public function get_config() {
        return $this->config;
    }
    
    protected function log_message($direction, $content, $type = 'text') {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'wai_message_logs',
            array(
                'platform_id' => $this->platform_id,
                'direction' => $direction, // 'in' or 'out'
                'message_type' => $type,
                'content' => $content,
                'timestamp' => current_time('mysql')
            )
        );
    }
}