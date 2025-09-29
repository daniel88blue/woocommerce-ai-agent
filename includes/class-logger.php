<?php
class WC_AI_Agent_Logger {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function log_decision($action_type, $product_id, $old_value, $new_value, $reason, $success = true) {
        global $wpdb;
        
        $data = array(
            'action_type' => $action_type,
            'product_id' => $product_id,
            'old_value' => maybe_serialize($old_value),
            'new_value' => maybe_serialize($new_value),
            'reason' => $reason,
            'success' => $success ? 1 : 0,
            'timestamp' => current_time('mysql')
        );
        
        $format = array('%s', '%d', '%s', '%s', '%s', '%d', '%s');
        
        $wpdb->insert("{$wpdb->prefix}wc_ai_agent_logs", $data, $format);
        
        return $wpdb->insert_id;
    }
    
    public function get_recent_logs($limit = 50) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wc_ai_agent_logs';
        
        $logs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_name 
            ORDER BY timestamp DESC 
            LIMIT %d
        ", $limit));
        
        foreach ($logs as $log) {
            $log->old_value = maybe_unserialize($log->old_value);
            $log->new_value = maybe_unserialize($log->new_value);
        }
        
        return $logs;
    }
    
    public function get_logs_by_product($product_id, $limit = 20) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wc_ai_agent_logs';
        
        $logs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_name 
            WHERE product_id = %d 
            ORDER BY timestamp DESC 
            LIMIT %d
        ", $product_id, $limit));
        
        foreach ($logs as $log) {
            $log->old_value = maybe_unserialize($log->old_value);
            $log->new_value = maybe_unserialize($log->new_value);
        }
        
        return $logs;
    }
}
?>
