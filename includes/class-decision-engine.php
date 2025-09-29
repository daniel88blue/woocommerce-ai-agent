<?php
class WC_AI_Agent_Decision_Engine {
    
    private $settings;
    
    public function __construct() {
        $this->settings = WC_AI_Agent_Settings::get_instance()->get_settings();
    }
    
    public function analyze_and_decide($product_data) {
        $decisions = array();
        
        foreach ($product_data['products'] as $product) {
            $product_decisions = $this->analyze_product($product);
            if (!empty($product_decisions)) {
                $decisions[] = array(
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'decisions' => $product_decisions
                );
            }
        }
        
        return $decisions;
    }
    
    private function analyze_product($product) {
        $decisions = array();
        
        // 定价优化决策
        if ($this->settings['pricing_optimization']) {
            $pricing_decision = $this->analyze_pricing($product);
            if ($pricing_decision) {
                $decisions[] = $pricing_decision;
            }
        }
        
        // 库存管理决策
        if ($this->settings['inventory_management']) {
            $inventory_decision = $this->analyze_inventory($product);
            if ($inventory_decision) {
                $decisions[] = $inventory_decision;
            }
        }
        
        // 自动折扣决策
        if ($this->settings['auto_discount']) {
            $discount_decision = $this->analyze_discount($product);
            if ($discount_decision) {
                $decisions[] = $discount_decision;
            }
        }
        
        return $decisions;
    }
    
    private function analyze_pricing($product) {
        $conversion_rate = $product['conversion_rate'];
        $profit_margin = $product['profit_margin'];
        $days_in_stock = $product['days_in_stock'];
        
        // 简单的定价规则引擎
        if ($days_in_stock > 30 && $conversion_rate < 2) {
            // 库存超过30天且转化率低的产品建议降价
            $current_price = floatval($product['price']);
            $suggested_price = $current_price * 0.9; // 降价10%
            
            if ($this->is_price_change_profitable($current_price, $suggested_price, $profit_margin)) {
                return array(
                    'type' => 'price_adjustment',
                    'action' => 'decrease_price',
                    'current_value' => $current_price,
                    'suggested_value' => round($suggested_price, 2),
                    'reason' => sprintf(
                        '产品已上架 %d 天，转化率较低(%s%%)，建议降价促销',
                        $days_in_stock,
                        $conversion_rate
                    ),
                    'confidence' => 0.8
                );
            }
        }
        
        if ($conversion_rate > 8 && $profit_margin > 25) {
            // 转化率高且利润空间大的产品可以考虑提价
            $current_price = floatval($product['price']);
            $suggested_price = $current_price * 1.05; // 提价5%
            
            return array(
                'type' => 'price_adjustment',
                'action' => 'increase_price',
                'current_value' => $current_price,
                'suggested_value' => round($suggested_price, 2),
                'reason' => sprintf(
                    '产品转化率较高(%s%%)，利润空间充足，可适当提价测试市场接受度',
                    $conversion_rate
                ),
                'confidence' => 0.6
            );
        }
        
        return false;
    }
    
    private function analyze_inventory($product) {
        $stock_quantity = $product['stock_quantity'];
        $sales_count = $product['sales_count'];
        $days_in_stock = $product['days_in_stock'];
        
        if ($stock_quantity === null) {
            return false; // 库存管理未启用
        }
        
        $daily_sales = $days_in_stock > 0 ? $sales_count / $days_in_stock : 0;
        $days_of_supply = $daily_sales > 0 ? $stock_quantity / $daily_sales : 999;
        
        if ($days_of_supply < 7) {
            // 库存不足7天销量，建议补货
            $suggested_quantity = max($stock_quantity * 2, $daily_sales * 30);
            
            return array(
                'type' => 'inventory_management',
                'action' => 'restock',
                'current_value' => $stock_quantity,
                'suggested_value' => intval($suggested_quantity),
                'reason' => sprintf(
                    '当前库存仅够销售 %.1f 天，建议及时补货',
                    $days_of_supply
                ),
                'confidence' => 0.9
            );
        }
        
        if ($days_of_supply > 90 && $daily_sales < 1) {
            // 库存积压严重，建议清仓
            return array(
                'type' => 'inventory_management',
                'action' => 'clearance',
                'current_value' => $stock_quantity,
                'suggested_value' => 0,
                'reason' => '库存积压严重，日均销量低，建议清仓处理',
                'confidence' => 0.7
            );
        }
        
        return false;
    }
    
    private function analyze_discount($product) {
        $conversion_rate = $product['conversion_rate'];
        $days_in_stock = $product['days_in_stock'];
        $sales_count = $product['sales_count'];
        
        $max_discount = floatval($this->settings['max_discount_rate']);
        
        if ($days_in_stock > 60 && $sales_count < 10) {
            // 上架超过60天且销量低的产品建议打折
            $discount_rate = min(30, $max_discount);
            
            return array(
                'type' => 'discount',
                'action' => 'create_coupon',
                'current_value' => 0,
                'suggested_value' => $discount_rate,
                'reason' => sprintf(
                    '产品上架 %d 天销量不佳，建议创建 %d%% 折扣券促销',
                    $days_in_stock,
                    $discount_rate
                ),
                'confidence' => 0.75
            );
        }
        
        return false;
    }
    
    private function is_price_change_profitable($current_price, $suggested_price, $current_margin) {
        $min_margin = floatval($this->settings['min_profit_margin']);
        
        // 假设降价会带来销量增长
        $expected_sales_increase = 1.2; // 20% 销量增长
        $new_margin = (($suggested_price * 0.6) / $suggested_price) * 100; // 简化计算
        
        return $new_margin >= $min_margin;
    }
}
?>
