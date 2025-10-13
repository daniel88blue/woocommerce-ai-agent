<?php
class WC_AI_Agent_Settings {
    
    private static $instance = null;
    private $settings;
    
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->settings = get_option('wc_ai_agent_settings', array());
    }
    
    public function get_settings() {
        return wp_parse_args($this->settings, $this->get_default_settings());
    }
    
    public function save_settings($new_settings) {
        $sanitized_settings = $this->sanitize_settings($new_settings);
        update_option('wc_ai_agent_settings', $sanitized_settings);
        $this->settings = $sanitized_settings;
    }
    
    private function get_default_settings() {
        return array(
            'pricing_optimization' => '1',
            'inventory_management' => '1',
            'auto_discount' => '1',
            'analysis_frequency' => 'daily',
            'max_discount_rate' => '20',
            'min_profit_margin' => '10',
            'auto_execute' => '0',
            'email_notifications' => '1'
        );
    }
    
    private function sanitize_settings($settings) {
        $sanitized = array();
        
        $sanitized['pricing_optimization'] = isset($settings['pricing_optimization']) ? '1' : '0';
        $sanitized['inventory_management'] = isset($settings['inventory_management']) ? '1' : '0';
        $sanitized['auto_discount'] = isset($settings['auto_discount']) ? '1' : '0';
        $sanitized['auto_execute'] = isset($settings['auto_execute']) ? '1' : '0';
        $sanitized['email_notifications'] = isset($settings['email_notifications']) ? '1' : '0';
        
        $sanitized['analysis_frequency'] = sanitize_text_field($settings['analysis_frequency']);
        $sanitized['max_discount_rate'] = floatval($settings['max_discount_rate']);
        $sanitized['min_profit_margin'] = floatval($settings['min_profit_margin']);
        
        return $sanitized;
    }
}
?>
