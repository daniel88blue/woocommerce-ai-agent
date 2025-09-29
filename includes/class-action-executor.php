<?php
class WC_AI_Agent_Action_Executor {
    
    private $logger;
    
    public function __construct() {
        $this->logger = WC_AI_Agent_Logger::get_instance();
    }
    
    public function execute_decisions($decisions) {
        $results = array();
        
        foreach ($decisions as $product_decision) {
            $product_id = $product_decision['product_id'];
            
            foreach ($product_decision['decisions'] as $decision) {
                $result = $this->execute_decision($product_id, $decision);
                $results[] = $result;
            }
        }
        
        return $results;
    }
    
    private function execute_decision($product_id, $decision) {
        $result = array(
            'product_id' => $product_id,
            'decision_type' => $decision['type'],
            'action' => $decision['action'],
            'timestamp' => current_time('mysql'),
            'success' => false
        );
        
        try {
            switch ($decision['action']) {
                case 'decrease_price':
                case 'increase_price':
                    $success = $this->adjust_price($product_id, $decision['suggested_value']);
                    break;
                    
                case 'restock':
                    $success = $this->adjust_stock($product_id, $decision['suggested_value']);
                    break;
                    
                case 'create_coupon':
                    $success = $this->create_discount_coupon($product_id, $decision['suggested_value']);
                    break;
                    
                default:
                    $success = false;
                    $result['error'] = '未知的操作类型: ' . $decision['action'];
            }
            
            $result['success'] = $success;
            $result['new_value'] = $decision['suggested_value'];
            $result['reason'] = $decision['reason'];
            
            // 记录日志
            $this->logger->log_decision(
                $decision['action'],
                $product_id,
                $decision['current_value'],
                $decision['suggested_value'],
                $decision['reason'],
                $success
            );
            
        } catch (Exception $e) {
            $result['success'] = false;
            $result['error'] = $e->getMessage();
        }
        
        return $result;
    }
    
    private function adjust_price($product_id, $new_price) {
        $product = wc_get_product($product_id);
        if (!$product) {
            throw new Exception('产品不存在');
        }
        
        $product->set_price($new_price);
        $product->set_regular_price($new_price);
        
        return $product->save() > 0;
    }
    
    private function adjust_stock($product_id, $new_quantity) {
        $product = wc_get_product($product_id);
        if (!$product) {
            throw new Exception('产品不存在');
        }
        
        $product->set_manage_stock(true);
        $product->set_stock_quantity($new_quantity);
        
        return $product->save() > 0;
    }
    
    private function create_discount_coupon($product_id, $discount_amount) {
        $product = wc_get_product($product_id);
        if (!$product) {
            throw new Exception('产品不存在');
        }
        
        $coupon_code = 'AI_' . strtoupper(wp_generate_password(6, false)) . '_' . $product_id;
        
        $coupon = array(
            'post_title' => $coupon_code,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'shop_coupon'
        );
        
        $coupon_id = wp_insert_post($coupon);
        
        if (is_wp_error($coupon_id)) {
            throw new Exception('创建优惠券失败: ' . $coupon_id->get_error_message());
        }
        
        // 设置优惠券属性
        update_post_meta($coupon_id, 'discount_type', 'percent');
        update_post_meta($coupon_id, 'coupon_amount', $discount_amount);
        update_post_meta($coupon_id, 'individual_use', 'yes');
        update_post_meta($coupon_id, 'product_ids', array($product_id));
        update_post_meta($coupon_id, 'usage_limit', 100);
        update_post_meta($coupon_id, 'usage_limit_per_user', 1);
        update_post_meta($coupon_id, 'date_expires', strtotime('+30 days'));
        update_post_meta($coupon_id, 'free_shipping', 'no');
        
        return $coupon_id > 0;
    }
}
?>
