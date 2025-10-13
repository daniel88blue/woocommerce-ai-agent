<?php
/**
 * AI电商智能体 - Conversific集成
 * 负责与Conversific分析平台的API通信和数据同步
 */

if (!defined('ABSPATH')) {
    exit;
}

class WAI_Conversific_Integration {
    
    private $api_key;
    private $api_secret;
    private $api_base_url = 'https://api.conversific.com/v1/';
    private $is_enabled = false;
    private $store_id;
    private $cache_duration = 3600; // 1小时缓存
    
    public function __construct() {
        $this->api_key = get_option('wai_conversific_api_key');
        $this->api_secret = get_option('wai_conversific_api_secret');
        $this->is_enabled = get_option('wai_conversific_enabled', false);
        $this->store_id = get_option('wai_conversific_store_id');
        
        if ($this->is_enabled && $this->api_key && $this->api_secret) {
            $this->setup_hooks();
            $this->auto_register_store();
        }
    }
    
    private function setup_hooks() {
        // 数据同步钩子
        add_action('wai_daily_data_sync', [$this, 'sync_daily_data_to_conversific']);
        add_action('woocommerce_new_order', [$this, 'sync_realtime_order_data']);
        add_action('woocommerce_order_status_changed', [$this, 'sync_order_status_change']);
        
        // 产品同步钩子
        add_action('woocommerce_new_product', [$this, 'sync_product_data']);
        add_action('woocommerce_update_product', [$this, 'sync_product_data']);
        add_action('woocommerce_delete_product', [$this, 'delete_product_data']);
        
        // 客户同步钩子
        add_action('user_register', [$this, 'sync_customer_data']);
        add_action('profile_update', [$this, 'sync_customer_data']);
        
        // 库存同步钩子
        add_action('woocommerce_update_product_stock', [$this, 'sync_inventory_data']);
    }
    
    /**
     * 自动注册店铺到Conversific
     */
    private function auto_register_store() {
        if ($this->store_id) {
            return; // 已经注册过了
        }
        
        try {
            $store_data = $this->register_store();
            if ($store_data && isset($store_data['store']['id'])) {
                $this->store_id = $store_data['store']['id'];
                update_option('wai_conversific_store_id', $this->store_id);
                
                $this->log_activity('store_registration', 'success', '店铺自动注册成功');
            }
        } catch (Exception $e) {
            $this->log_activity('store_registration', 'error', $e->getMessage());
        }
    }
    
    /**
     * 测试Conversific连接
     */
    public function test_connection() {
        try {
            $response = $this->make_api_request('stores/' . $this->store_id, 'GET');
            
            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'message' => '连接测试失败: ' . $response->get_error_message(),
                    'data' => $response
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Conversific连接测试成功',
                'data' => [
                    'store_name' => $response['store']['name'] ?? '未知',
                    'store_url' => $response['store']['url'] ?? '未知',
                    'plan' => $response['store']['plan'] ?? '未知'
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '连接测试异常: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * 注册新店铺
     */
    public function register_store($store_data = null) {
        if (!$store_data) {
            $store_data = [
                'name' => get_bloginfo('name'),
                'url' => get_site_url(),
                'platform' => 'woocommerce',
                'currency' => get_woocommerce_currency(),
                'timezone' => wp_timezone_string(),
                'country' => WC()->countries->get_base_country()
            ];
        }
        
        $payload = ['store' => $store_data];
        $response = $this->make_api_request('stores', 'POST', $payload);
        
        return $response;
    }
    
    /**
     * 同步每日数据到Conversific
     */
    public function sync_daily_data_to_conversific() {
        if (!$this->is_enabled || !$this->store_id) {
            return;
        }
        
        $sync_results = [];
        
        try {
            // 同步订单数据
            $sync_results['orders'] = $this->sync_historical_orders();
            
            // 同步产品数据
            $sync_results['products'] = $this->sync_all_products();
            
            // 同步客户数据
            $sync_results['customers'] = $this->sync_all_customers();
            
            // 同步流量数据
            $sync_results['traffic'] = $this->sync_traffic_data();
            
            $this->log_activity('daily_sync', 'success', '每日数据同步完成', $sync_results);
            
        } catch (Exception $e) {
            $this->log_activity('daily_sync', 'error', $e->getMessage());
        }
        
        return $sync_results;
    }
    
    /**
     * 同步实时订单数据
     */
    public function sync_realtime_order_data($order_id) {
        if (!$this->is_enabled || !$this->store_id) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $order_data = $this->prepare_order_data($order);
        $result = $this->make_api_request('stores/' . $this->store_id . '/orders', 'POST', $order_data);
        
        $this->log_activity('order_sync', 
            is_wp_error($result) ? 'error' : 'success',
            '订单同步: ' . $order_id,
            $order_data
        );
        
        return $result;
    }
    
    /**
     * 同步订单状态变化
     */
    public function sync_order_status_change($order_id, $old_status, $new_status) {
        if (!$this->is_enabled || !$this->store_id) {
            return;
        }
        
        $update_data = [
            'order' => [
                'status' => $new_status,
                'updated_at' => current_time('c')
            ]
        ];
        
        $result = $this->make_api_request(
            'stores/' . $this->store_id . '/orders/' . $order_id, 
            'PATCH', 
            $update_data
        );
        
        $this->log_activity('order_status_update', 
            is_wp_error($result) ? 'error' : 'success',
            "订单状态更新: {$order_id} - {$old_status} → {$new_status}"
        );
        
        return $result;
    }
    
    /**
     * 同步产品数据
     */
    public function sync_product_data($product_id) {
        if (!$this->is_enabled || !$this->store_id) {
            return;
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }
        
        $product_data = $this->prepare_product_data($product);
        $result = $this->make_api_request(
            'stores/' . $this->store_id . '/products', 
            'POST', 
            $product_data
        );
        
        $this->log_activity('product_sync', 
            is_wp_error($result) ? 'error' : 'success',
            '产品同步: ' . $product_id
        );
        
        return $result;
    }
    
    /**
     * 删除产品数据
     */
    public function delete_product_data($product_id) {
        if (!$this->is_enabled || !$this->store_id) {
            return;
        }
        
        $result = $this->make_api_request(
            'stores/' . $this->store_id . '/products/' . $product_id, 
            'DELETE'
        );
        
        $this->log_activity('product_delete', 
            is_wp_error($result) ? 'error' : 'success',
            '产品删除: ' . $product_id
        );
        
        return $result;
    }
    
    /**
     * 同步客户数据
     */
    public function sync_customer_data($user_id) {
        if (!$this->is_enabled || !$this->store_id) {
            return;
        }
        
        $user = get_userdata($user_id);
        if (!$user || $user->roles[0] !== 'customer') {
            return;
        }
        
        $customer_data = $this->prepare_customer_data($user);
        $result = $this->make_api_request(
            'stores/' . $this->store_id . '/customers', 
            'POST', 
            $customer_data
        );
        
        $this->log_activity('customer_sync', 
            is_wp_error($result) ? 'error' : 'success',
            '客户同步: ' . $user_id
        );
        
        return $result;
    }
    
    /**
     * 同步库存数据
     */
    public function sync_inventory_data($product_id) {
        if (!$this->is_enabled || !$this->store_id) {
            return;
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }
        
        $inventory_data = [
            'product' => [
                'id' => (string) $product_id,
                'inventory_quantity' => $product->get_stock_quantity(),
                'inventory_policy' => $product->get_manage_stock() ? 'track' : 'not_track',
                'updated_at' => current_time('c')
            ]
        ];
        
        $result = $this->make_api_request(
            'stores/' . $this->store_id . '/products/' . $product_id . '/inventory', 
            'PATCH', 
            $inventory_data
        );
        
        $this->log_activity('inventory_sync', 
            is_wp_error($result) ? 'error' : 'success',
            '库存同步: ' . $product_id
        );
        
        return $result;
    }
    
    /**
     * 从Conversific获取分析数据
     */
    public function get_analytics_data($metric, $period = '30d', $compare_period = null) {
        $cache_key = "conversific_{$metric}_{$period}" . ($compare_period ? "_vs_{$compare_period}" : '');
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        $params = [
            'period' => $period,
            'metric' => $metric
        ];
        
        if ($compare_period) {
            $params['compare_period'] = $compare_period;
        }
        
        $response = $this->make_api_request(
            'stores/' . $this->store_id . '/analytics', 
            'GET', 
            [], 
            $params
        );
        
        if (!is_wp_error($response)) {
            set_transient($cache_key, $response, $this->cache_duration);
        }
        
        return $response;
    }
    
    /**
     * 获取关键指标概览
     */
    public function get_key_metrics_overview($period = '30d') {
        $metrics = [
            'revenue',
            'orders',
            'average_order_value',
            'conversion_rate',
            'customers',
            'products_sold'
        ];
        
        $overview = [];
        
        foreach ($metrics as $metric) {
            $data = $this->get_analytics_data($metric, $period);
            if (!is_wp_error($data)) {
                $overview[$metric] = $data;
            }
        }
        
        return $overview;
    }
    
    /**
     * 获取销售表现报告
     */
    public function get_sales_performance_report($period = '30d') {
        $report = [
            'overview' => $this->get_analytics_data('revenue', $period, 'previous_period'),
            'daily_trends' => $this->get_analytics_data('revenue', $period, 'daily'),
            'product_performance' => $this->get_product_performance($period),
            'customer_analysis' => $this->get_customer_analysis($period)
        ];
        
        return $report;
    }
    
    /**
     * 获取产品表现数据
     */
    public function get_product_performance($period = '30d') {
        $params = [
            'period' => $period,
            'metric' => 'product_performance'
        ];
        
        return $this->make_api_request(
            'stores/' . $this->store_id . '/analytics/products', 
            'GET', 
            [], 
            $params
        );
    }
    
    /**
     * 获取客户分析数据
     */
    public function get_customer_analysis($period = '30d') {
        $params = [
            'period' => $period,
            'metric' => 'customer_analysis'
        ];
        
        return $this->make_api_request(
            'stores/' . $this->store_id . '/analytics/customers', 
            'GET', 
            [], 
            $params
        );
    }
    
    /**
     * 获取流量分析数据
     */
    public function get_traffic_analysis($period = '30d') {
        $params = [
            'period' => $period,
            'metric' => 'traffic_analysis'
        ];
        
        return $this->make_api_request(
            'stores/' . $this->store_id . '/analytics/traffic', 
            'GET', 
            [], 
            $params
        );
    }
    
    /**
     * 获取营销效果报告
     */
    public function get_marketing_performance_report($period = '30d') {
        $report = [
            'acquisition_channels' => $this->get_acquisition_channels($period),
            'campaign_performance' => $this->get_campaign_performance($period),
            'conversion_funnel' => $this->get_conversion_funnel($period)
        ];
        
        return $report;
    }
    
    /**
     * 获取获客渠道数据
     */
    public function get_acquisition_channels($period = '30d') {
        $params = [
            'period' => $period,
            'metric' => 'acquisition_channels'
        ];
        
        return $this->make_api_request(
            'stores/' . $this->store_id . '/analytics/acquisition', 
            'GET', 
            [], 
            $params
        );
    }
    
    /**
     * 获取营销活动表现
     */
    public function get_campaign_performance($period = '30d') {
        $params = [
            'period' => $period,
            'metric' => 'campaign_performance'
        ];
        
        return $this->make_api_request(
            'stores/' . $this->store_id . '/analytics/campaigns', 
            'GET', 
            [], 
            $params
        );
    }
    
    /**
     * 获取转化漏斗数据
     */
    public function get_conversion_funnel($period = '30d') {
        $params = [
            'period' => $period,
            'metric' => 'conversion_funnel'
        ];
        
        return $this->make_api_request(
            'stores/' . $this->store_id . '/analytics/funnel', 
            'GET', 
            [], 
            $params
        );
    }
    
    /**
     * 获取竞争情报数据
     */
    public function get_competitive_insights($period = '30d') {
        $params = [
            'period' => $period,
            'metric' => 'competitive_insights'
        ];
        
        return $this->make_api_request(
            'stores/' . $this->store_id . '/analytics/competitive', 
            'GET', 
            [], 
            $params
        );
    }
    
    /**
     * 获取AI优化建议
     */
    public function get_ai_recommendations() {
        $cache_key = 'conversific_ai_recommendations';
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        $response = $this->make_api_request(
            'stores/' . $this->store_id . '/recommendations', 
            'GET'
        );
        
        if (!is_wp_error($response)) {
            set_transient($cache_key, $response, $this->cache_duration);
        }
        
        return $response;
    }
    
    /**
     * 同步历史订单数据
     */
    private function sync_historical_orders($days_back = 30) {
        $start_date = date('Y-m-d', strtotime("-{$days_back} days"));
        $end_date = date('Y-m-d');
        
        $orders = wc_get_orders([
            'limit' => -1,
            'status' => ['completed', 'processing', 'cancelled', 'refunded'],
            'date_created' => $start_date . '...' . $end_date,
            'return' => 'objects'
        ]);
        
        $synced_count = 0;
        $error_count = 0;
        
        foreach ($orders as $order) {
            $result = $this->sync_realtime_order_data($order->get_id());
            if (is_wp_error($result)) {
                $error_count++;
            } else {
                $synced_count++;
            }
            
            // 避免API限制，添加小延迟
            usleep(100000); // 0.1秒
        }
        
        return [
            'total_orders' => count($orders),
            'synced_count' => $synced_count,
            'error_count' => $error_count
        ];
    }
    
    /**
     * 同步所有产品
     */
    private function sync_all_products() {
        $products = wc_get_products([
            'limit' => -1,
            'status' => 'publish',
            'return' => 'objects'
        ]);
        
        $synced_count = 0;
        $error_count = 0;
        
        foreach ($products as $product) {
            $result = $this->sync_product_data($product->get_id());
            if (is_wp_error($result)) {
                $error_count++;
            } else {
                $synced_count++;
            }
            
            usleep(50000); // 0.05秒
        }
        
        return [
            'total_products' => count($products),
            'synced_count' => $synced_count,
            'error_count' => $error_count
        ];
    }
    
    /**
     * 同步所有客户
     */
    private function sync_all_customers() {
        $customers = get_users([
            'role' => 'customer',
            'number' => -1
        ]);
        
        $synced_count = 0;
        $error_count = 0;
        
        foreach ($customers as $customer) {
            $result = $this->sync_customer_data($customer->ID);
            if (is_wp_error($result)) {
                $error_count++;
            } else {
                $synced_count++;
            }
            
            usleep(50000); // 0.05秒
        }
        
        return [
            'total_customers' => count($customers),
            'synced_count' => $synced_count,
            'error_count' => $error_count
        ];
    }
    
    /**
     * 同步流量数据
     */
    private function sync_traffic_data() {
        // 这里可以实现Google Analytics或其他流量数据的同步
        // 目前返回模拟数据
        return [
            'sessions' => rand(1000, 5000),
            'pageviews' => rand(5000, 20000),
            'bounce_rate' => rand(30, 70) / 100,
            'avg_session_duration' => rand(60, 300)
        ];
    }
    
    /**
     * 准备订单数据
     */
    private function prepare_order_data($order) {
        $order_items = $order->get_items();
        $items_data = [];
        
        foreach ($order_items as $item) {
            $product = $item->get_product();
            $items_data[] = [
                'product_id' => (string) $product->get_id(),
                'variant_id' => $product->is_type('variation') ? (string) $product->get_id() : null,
                'sku' => $product->get_sku(),
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => (float) $item->get_total(),
                'line_total' => (float) $item->get_total()
            ];
        }
        
        $order_data = [
            'order' => [
                'id' => (string) $order->get_id(),
                'order_number' => $order->get_order_number(),
                'email' => $order->get_billing_email(),
                'created_at' => $order->get_date_created()->format('c'),
                'updated_at' => $order->get_date_modified()->format('c'),
                'completed_at' => $order->get_date_completed() ? $order->get_date_completed()->format('c') : null,
                'currency' => $order->get_currency(),
                'subtotal_price' => (float) $order->get_subtotal(),
                'total_shipping' => (float) $order->get_shipping_total(),
                'total_discounts' => (float) $order->get_discount_total(),
                'total_tax' => (float) $order->get_total_tax(),
                'total_price' => (float) $order->get_total(),
                'financial_status' => $order->get_status(),
                'fulfillment_status' => $this->get_fulfillment_status($order),
                'billing_address' => [
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name(),
                    'company' => $order->get_billing_company(),
                    'address1' => $order->get_billing_address_1(),
                    'address2' => $order->get_billing_address_2(),
                    'city' => $order->get_billing_city(),
                    'province' => $order->get_billing_state(),
                    'country' => $order->get_billing_country(),
                    'zip' => $order->get_billing_postcode(),
                    'phone' => $order->get_billing_phone()
                ],
                'shipping_address' => [
                    'first_name' => $order->get_shipping_first_name(),
                    'last_name' => $order->get_shipping_last_name(),
                    'company' => $order->get_shipping_company(),
                    'address1' => $order->get_shipping_address_1(),
                    'address2' => $order->get_shipping_address_2(),
                    'city' => $order->get_shipping_city(),
                    'province' => $order->get_shipping_state(),
                    'country' => $order->get_shipping_country(),
                    'zip' => $order->get_shipping_postcode()
                ],
                'line_items' => $items_data,
                'customer' => [
                    'id' => (string) $order->get_customer_id(),
                    'email' => $order->get_billing_email(),
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name()
                ]
            ]
        ];
        
        return $order_data;
    }
    
    /**
     * 准备产品数据
     */
    private function prepare_product_data($product) {
        $product_data = [
            'product' => [
                'id' => (string) $product->get_id(),
                'title' => $product->get_name(),
                'body_html' => $product->get_description(),
                'vendor' => $this->get_product_vendor($product),
                'product_type' => $product->get_type(),
                'handle' => $product->get_slug(),
                'published_at' => get_post_time('c', true, $product->get_id()),
                'created_at' => get_post_time('c', true, $product->get_id()),
                'updated_at' => get_post_modified_time('c', true, $product->get_id()),
                'variants' => [
                    [
                        'id' => (string) $product->get_id(),
                        'product_id' => (string) $product->get_id(),
                        'title' => $product->get_name(),
                        'price' => (float) $product->get_price(),
                        'compare_at_price' => (float) $product->get_regular_price(),
                        'sku' => $product->get_sku(),
                        'inventory_quantity' => $product->get_stock_quantity(),
                        'inventory_policy' => $product->get_manage_stock() ? 'track' : 'not_track',
                        'requires_shipping' => true,
                        'taxable' => $product->is_taxable()
                    ]
                ],
                'images' => $this->get_product_images($product)
            ]
        ];
        
        return $product_data;
    }
    
    /**
     * 准备客户数据
     */
    private function prepare_customer_data($user) {
        $customer_data = [
            'customer' => [
                'id' => (string) $user->ID,
                'email' => $user->user_email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'created_at' => $user->user_registered,
                'updated_at' => current_time('c'),
                'orders_count' => $this->get_customer_order_count($user->ID),
                'total_spent' => (float) $this->get_customer_total_spent($user->ID),
                'note' => $user->description,
                'tags' => $this->get_customer_tags($user->ID),
                'addresses' => [
                    [
                        'first_name' => get_user_meta($user->ID, 'billing_first_name', true),
                        'last_name' => get_user_meta($user->ID, 'billing_last_name', true),
                        'company' => get_user_meta($user->ID, 'billing_company', true),
                        'address1' => get_user_meta($user->ID, 'billing_address_1', true),
                        'address2' => get_user_meta($user->ID, 'billing_address_2', true),
                        'city' => get_user_meta($user->ID, 'billing_city', true),
                        'province' => get_user_meta($user->ID, 'billing_state', true),
                        'country' => get_user_meta($user->ID, 'billing_country', true),
                        'zip' => get_user_meta($user->ID, 'billing_postcode', true),
                        'phone' => get_user_meta($user->ID, 'billing_phone', true),
                        'default' => true
                    ]
                ]
            ]
        ];
        
        return $customer_data;
    }
    
    /**
     * 制作API请求
     */
    private function make_api_request($endpoint, $method = 'GET', $data = [], $params = []) {
        $url = $this->api_base_url . $endpoint;
        
        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'X-API-Secret' => $this->api_secret
            ],
            'timeout' => 30
        ];
        
        if (!empty($data)) {
            $args['body'] = json_encode($data);
        }
        
        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code >= 400) {
            return new WP_Error(
                'conversific_api_error',
                'Conversific API错误: ' . $response_code,
                [
                    'status' => $response_code,
                    'response' => $response_body
                ]
            );
        }
        
        return json_decode($response_body, true) ?: [];
    }
    
    /**
     * 获取订单履约状态
     */
    private function get_fulfillment_status($order) {
        $fulfillment_status = 'unfulfilled';
        
        foreach ($order->get_items('shipping') as $shipping_item) {
            if ($shipping_item->get_method_title()) {
                $fulfillment_status = 'fulfilled';
                break;
            }
        }
        
        return $fulfillment_status;
    }
    
    /**
     * 获取产品供应商
     */
    private function get_product_vendor($product) {
        // WooCommerce没有内置供应商字段，可以使用分类或自定义字段
        $vendor_terms = wp_get_post_terms($product->get_id(), 'product_vendor');
        if (!empty($vendor_terms) && !is_wp_error($vendor_terms)) {
            return $vendor_terms[0]->name;
        }
        
        return get_bloginfo('name');
    }
    
    /**
     * 获取产品图片
     */
    private function get_product_images($product) {
        $images = [];
        $product_images = [];
        
        // 主图
        $main_image_id = $product->get_image_id();
        if ($main_image_id) {
            $main_image_url = wp_get_attachment_url($main_image_id);
            $product_images[] = [
                'id' => (string) $main_image_id,
                'product_id' => (string) $product->get_id(),
                'src' => $main_image_url,
                'position' => 1
            ];
        }
        
        // 图库图片
        $gallery_image_ids = $product->get_gallery_image_ids();
        $position = 2;
        foreach ($gallery_image_ids as $image_id) {
            $image_url = wp_get_attachment_url($image_id);
            $product_images[] = [
                'id' => (string) $image_id,
                'product_id' => (string) $product->get_id(),
                'src' => $image_url,
                'position' => $position
            ];
            $position++;
        }
        
        return $product_images;
    }
    
    /**
     * 获取客户订单数量
     */
    private function get_customer_order_count($user_id) {
        $customer = new WC_Customer($user_id);
        return $customer->get_order_count();
    }
    
    /**
     * 获取客户总消费金额
     */
    private function get_customer_total_spent($user_id) {
        $customer = new WC_Customer($user_id);
        return $customer->get_total_spent();
    }
    
    /**
     * 获取客户标签
     */
    private function get_customer_tags($user_id) {
        $tags = [];
        $total_spent = $this->get_customer_total_spent($user_id);
        
        if ($total_spent > 1000) {
            $tags[] = 'VIP客户';
        } elseif ($total_spent > 500) {
            $tags[] = '忠实客户';
        } else {
            $tags[] = '新客户';
        }
        
        return implode(', ', $tags);
    }
    
    /**
     * 记录活动日志
     */
    private function log_activity($type, $status, $message, $data = null) {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'type' => $type,
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
        
        // 这里可以实现日志存储逻辑
        error_log("WAI Conversific: " . json_encode($log_entry));
        
        // 同时存储到WordPress选项中以供显示
        $recent_activities = get_option('wai_conversific_activities', []);
        $recent_activities[] = $log_entry;
        
        // 只保留最近50个活动
        if (count($recent_activities) > 50) {
            $recent_activities = array_slice($recent_activities, -50);
        }
        
        update_option('wai_conversific_activities', $recent_activities);
    }
    
    /**
     * 获取同步状态报告
     */
    public function get_sync_status_report() {
        $report = [
            'store_registered' => (bool) $this->store_id,
            'last_sync' => get_option('wai_conversific_last_sync'),
            'recent_activities' => get_option('wai_conversific_activities', []),
            'data_metrics' => $this->get_data_metrics()
        ];
        
        return $report;
    }
    
    /**
     * 获取数据指标
     */
    private function get_data_metrics() {
        return [
            'orders_synced' => $this->get_synced_orders_count(),
            'products_synced' => $this->get_synced_products_count(),
            'customers_synced' => $this->get_synced_customers_count(),
            'last_analytics_fetch' => get_option('wai_conversific_last_analytics_fetch')
        ];
    }
    
    /**
     * 获取已同步订单数
     */
    private function get_synced_orders_count() {
        // 这里可以实现已同步订单的计数逻辑
        return 0;
    }
    
    /**
     * 获取已同步产品数
     */
    private function get_synced_products_count() {
        // 这里可以实现已同步产品的计数逻辑
        return 0;
    }
    
    /**
     * 获取已同步客户数
     */
    private function get_synced_customers_count() {
        // 这里可以实现已同步客户的计数逻辑
        return 0;
    }
}