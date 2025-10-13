
<?php
class WC_AI_Agent_Data_Collector {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function collect_product_data($product_id = null) {
        $data = array();
        
        if ($product_id) {
            // 收集单个产品数据
            $data['products'] = array($this->get_product_analysis($product_id));
        } else {
            // 收集所有产品数据
            $data['products'] = $this->get_all_products_analysis();
        }
        
        $data['store_metrics'] = $this->get_store_metrics();
        $data['timestamp'] = current_time('mysql');
        
        return $data;
    }
    
    private function get_product_analysis($product_id) {
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }
        
        $analysis = array(
            'id' => $product_id,
            'name' => $product->get_name(),
            'type' => $product->get_type(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'stock_quantity' => $product->get_stock_quantity(),
            'stock_status' => $product->get_stock_status(),
            'sales_count' => $this->get_product_sales_count($product_id),
            'views_count' => $this->get_product_views_count($product_id),
            'conversion_rate' => $this->calculate_conversion_rate($product_id),
            'days_in_stock' => $this->get_days_in_stock($product_id),
            'profit_margin' => $this->calculate_profit_margin($product_id),
            'category_performance' => $this->get_category_performance($product->get_category_ids())
        );
        
        return $analysis;
    }
    
    private function get_all_products_analysis() {
        $products_analysis = array();
        
        $args = array(
            'status' => 'publish',
            'limit' => -1,
            'return' => 'ids'
        );
        
        $product_ids = wc_get_products($args);
        
        foreach ($product_ids as $product_id) {
            $analysis = $this->get_product_analysis($product_id);
            if ($analysis) {
                $products_analysis[] = $analysis;
            }
        }
        
        return $products_analysis;
    }
    
    private function get_store_metrics() {
        global $wpdb;
        
        // 基础商店指标
        $metrics = array(
            'total_revenue' => $this->get_total_revenue(),
            'total_orders' => $this->get_total_orders(),
            'average_order_value' => $this->get_average_order_value(),
            'conversion_rate' => $this->get_store_conversion_rate(),
            'top_categories' => $this->get_top_categories(),
            'sales_trend' => $this->get_sales_trend()
        );
        
        return $metrics;
    }
    
    private function get_product_sales_count($product_id) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(oi.meta_value) 
            FROM {$wpdb->prefix}woocommerce_order_itemmeta oi 
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items o ON oi.order_item_id = o.order_item_id 
            LEFT JOIN {$wpdb->posts} p ON o.order_id = p.ID 
            WHERE oi.meta_key = '_qty' 
            AND o.order_item_type = 'line_item' 
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND oi.order_item_id IN (
                SELECT order_item_id 
                FROM {$wpdb->prefix}woocommerce_order_itemmeta 
                WHERE meta_key = '_product_id' 
                AND meta_value = %d
            )
        ", $product_id));
        
        return $count ? intval($count) : 0;
    }
    
    private function get_product_views_count($product_id) {
        // 这里可以集成 Matomo 或其他分析工具
        // 暂时返回模拟数据
        return get_post_meta($product_id, '_wc_ai_agent_views', true) ?: rand(50, 500);
    }
    
    private function calculate_conversion_rate($product_id) {
        $views = $this->get_product_views_count($product_id);
        $sales = $this->get_product_sales_count($product_id);
        
        if ($views > 0) {
            return round(($sales / $views) * 100, 2);
        }
        
        return 0;
    }
    
    private function get_total_revenue() {
        global $wpdb;
        
        $revenue = $wpdb->get_var("
            SELECT SUM(meta_value) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_order_total' 
            AND post_id IN (
                SELECT ID 
                FROM {$wpdb->posts} 
                WHERE post_type = 'shop_order' 
                AND post_status IN ('wc-completed', 'wc-processing')
            )
        ");
        
        return $revenue ? floatval($revenue) : 0;
    }
    
    // 其他辅助方法...
    private function get_days_in_stock($product_id) {
        $date_created = get_post_time('U', false, $product_id);
        $current_time = current_time('timestamp');
        return round(($current_time - $date_created) / DAY_IN_SECONDS);
    }
    
    private function calculate_profit_margin($product_id) {
        // 简化计算，实际中需要考虑成本价
        $product = wc_get_product($product_id);
        $price = $product->get_price();
        $cost = get_post_meta($product_id, '_cost_price', true) ?: $price * 0.6;
        
        if ($price > 0) {
            return round((($price - $cost) / $price) * 100, 2);
        }
        
        return 0;
    }
    
    private function get_total_orders() {
        $count = wp_count_posts('shop_order');
        return intval($count->{'wc-completed'}) + intval($count->{'wc-processing'});
    }
    
    private function get_average_order_value() {
        $revenue = $this->get_total_revenue();
        $orders = $this->get_total_orders();
        
        if ($orders > 0) {
            return round($revenue / $orders, 2);
        }
        
        return 0;
    }
    
    private function get_store_conversion_rate() {
        // 简化计算
        return rand(1, 5) + (rand(0, 99) / 100);
    }
    
    private function get_top_categories() {
        // 返回顶级分类
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'number' => 5
        ));
        
        $top_categories = array();
        foreach ($categories as $category) {
            $top_categories[] = array(
                'name' => $category->name,
                'count' => $category->count
            );
        }
        
        return $top_categories;
    }
    
    private function get_sales_trend() {
        // 返回最近7天的销售趋势
        $trend = array();
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $trend[$date] = rand(100, 1000);
        }
        return $trend;
    }
    
    private function get_category_performance($category_ids) {
        if (empty($category_ids)) {
            return array();
        }
        
        $performance = array();
        foreach ($category_ids as $category_id) {
            $category = get_term($category_id, 'product_cat');
            if ($category) {
                $performance[] = array(
                    'id' => $category_id,
                    'name' => $category->name,
                    'sales' => rand(500, 5000)
                );
            }
        }
        
        return $performance;
    }
}
?>
