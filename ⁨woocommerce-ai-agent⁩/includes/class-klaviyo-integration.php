<?php
/**
 * AI电商智能体 - Klaviyo集成
 * 负责与Klaviyo平台的API通信和数据同步
 */

if (!defined('ABSPATH')) {
    exit;
}

class WAI_Klaviyo_Integration {
    
    private $public_key;
    private $private_key;
    private $api_base_url = 'https://a.klaviyo.com/api/';
    private $is_enabled = false;
    private $list_mappings = [];
    
    public function __construct() {
        $this->public_key = get_option('wai_klaviyo_public_key');
        $this->private_key = get_option('wai_klaviyo_private_key');
        $this->is_enabled = get_option('wai_klaviyo_enabled', false);
        $this->list_mappings = get_option('wai_klaviyo_list_mappings', []);
        
        if ($this->is_enabled && $this->public_key && $this->private_key) {
            $this->setup_hooks();
        }
    }
    
    private function setup_hooks() {
        // 客户相关钩子
        add_action('user_register', [$this, 'sync_customer_to_klaviyo']);
        add_action('profile_update', [$this, 'sync_customer_to_klaviyo']);
        add_action('woocommerce_new_order', [$this, 'handle_new_order']);
        add_action('woocommerce_order_status_changed', [$this, 'handle_order_status_change'], 10, 3);
        
        // 产品相关钩子
        add_action('woocommerce_new_product', [$this, 'sync_product_to_klaviyo']);
        add_action('woocommerce_update_product', [$this, 'sync_product_to_klaviyo']);
        
        // 购物车相关钩子
        add_action('woocommerce_add_to_cart', [$this, 'track_add_to_cart']);
        add_action('woocommerce_cart_updated', [$this, 'track_cart_activity']);
        
        // 自定义事件
        add_action('wai_customer_segmentation_updated', [$this, 'sync_customer_segments']);
        add_action('wai_ab_test_completed', [$this, 'track_ab_test_results']);
    }
    
    /**
     * 测试Klaviyo连接
     */
    public function test_connection() {
        try {
            $response = $this->make_api_request('lists', 'GET');
            
            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'message' => '连接测试失败: ' . $response->get_error_message(),
                    'data' => $response
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Klaviyo连接测试成功',
                'data' => [
                    'account_name' => $response['data'][0]['account_name'] ?? '未知',
                    'list_count' => count($response['data'] ?? [])
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
     * 同步客户到Klaviyo
     */
    public function sync_customer_to_klaviyo($user_id) {
        if (!$this->is_enabled) {
            return;
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }
        
        $customer_data = $this->prepare_customer_data($user);
        $result = $this->identify_customer($customer_data);
        
        // 添加到默认列表
        if (!empty($this->list_mappings['default_list'])) {
            $this->add_to_list($customer_data['email'], $this->list_mappings['default_list']);
        }
        
        $this->log_sync_activity('customer', $user_id, $result);
        
        return $result;
    }
    
    /**
     * 处理新订单
     */
    public function handle_new_order($order_id) {
        if (!$this->is_enabled) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $this->track_placed_order($order);
        $this->update_customer_profile($order);
    }
    
    /**
     * 处理订单状态变化
     */
    public function handle_order_status_change($order_id, $old_status, $new_status) {
        if (!$this->is_enabled) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $events = [
            'completed' => 'track_fulfilled_order',
            'cancelled' => 'track_cancelled_order',
            'refunded' => 'track_refunded_order'
        ];
        
        if (isset($events[$new_status])) {
            $method = $events[$new_status];
            $this->$method($order);
        }
    }
    
    /**
     * 同步产品到Klaviyo
     */
    public function sync_product_to_klaviyo($product_id) {
        if (!$this->is_enabled) {
            return;
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }
        
        $product_data = $this->prepare_product_data($product);
        $result = $this->catalog_item_request($product_data);
        
        $this->log_sync_activity('product', $product_id, $result);
        
        return $result;
    }
    
    /**
     * 跟踪加入购物车事件
     */
    public function track_add_to_cart() {
        if (!$this->is_enabled || is_admin()) {
            return;
        }
        
        $cart_items = WC()->cart->get_cart();
        $customer_email = $this->get_current_customer_email();
        
        if (!$customer_email || empty($cart_items)) {
            return;
        }
        
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            
            $event_data = [
                'event' => 'Added to Cart',
                'customer_properties' => [
                    '$email' => $customer_email
                ],
                'properties' => [
                    'ProductID' => $product->get_id(),
                    'ProductName' => $product->get_name(),
                    'ProductURL' => get_permalink($product->get_id()),
                    'ProductImage' => wp_get_attachment_url($product->get_image_id()),
                    'Price' => $product->get_price(),
                    'Quantity' => $quantity,
                    'Categories' => $this->get_product_categories($product),
                    'CartTotal' => WC()->cart->get_cart_total()
                ]
            ];
            
            $this->track_event($event_data);
        }
    }
    
    /**
     * 跟踪购物车活动
     */
    public function track_cart_activity() {
        if (!$this->is_enabled || is_admin()) {
            return;
        }
        
        $customer_email = $this->get_current_customer_email();
        if (!$customer_email) {
            return;
        }
        
        $cart_total = WC()->cart->get_total('edit');
        $cart_items_count = WC()->cart->get_cart_contents_count();
        
        $event_data = [
            'event' => 'Cart Updated',
            'customer_properties' => [
                '$email' => $customer_email
            ],
            'properties' => [
                'CartTotal' => $cart_total,
                'ItemsCount' => $cart_items_count,
                'CartItems' => $this->get_cart_items_data()
            ]
        ];
        
        $this->track_event($event_data);
    }
    
    /**
     * 同步客户细分
     */
    public function sync_customer_segments($segment_data) {
        if (!$this->is_enabled) {
            return;
        }
        
        $customer_email = $segment_data['email'] ?? '';
        $segment_name = $segment_data['segment_name'] ?? '';
        $action = $segment_data['action'] ?? 'add'; // add 或 remove
        
        if (!$customer_email || !$segment_name) {
            return;
        }
        
        // 获取或创建细分列表
        $list_id = $this->get_or_create_segment_list($segment_name);
        if (!$list_id) {
            return;
        }
        
        if ($action === 'add') {
            $this->add_to_list($customer_email, $list_id);
        } else {
            $this->remove_from_list($customer_email, $list_id);
        }
    }
    
    /**
     * 跟踪A/B测试结果
     */
    public function track_ab_test_results($test_data) {
        if (!$this->is_enabled) {
            return;
        }
        
        $event_data = [
            'event' => 'A/B Test Completed',
            'customer_properties' => [
                '$email' => $test_data['customer_email'] ?? ''
            ],
            'properties' => [
                'TestID' => $test_data['test_id'] ?? '',
                'TestName' => $test_data['test_name'] ?? '',
                'Variant' => $test_data['variant'] ?? '',
                'ConversionRate' => $test_data['conversion_rate'] ?? 0,
                'RevenueImpact' => $test_data['revenue_impact'] ?? 0,
                'StatisticalSignificance' => $test_data['significance'] ?? 0
            ]
        ];
        
        $this->track_event($event_data);
    }
    
    /**
     * 准备客户数据
     */
    private function prepare_customer_data($user) {
        $customer_data = [
            'email' => $user->user_email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone_number' => get_user_meta($user->ID, 'billing_phone', true),
            'city' => get_user_meta($user->ID, 'billing_city', true),
            'country' => get_user_meta($user->ID, 'billing_country', true),
            'registration_date' => $user->user_registered,
            'orders_count' => $this->get_customer_order_count($user->ID),
            'total_spent' => $this->get_customer_total_spent($user->ID),
            'customer_segment' => $this->get_customer_segment($user->ID)
        ];
        
        // 添加自定义属性
        $custom_properties = apply_filters('wai_klaviyo_customer_properties', [], $user);
        $customer_data = array_merge($customer_data, $custom_properties);
        
        return $customer_data;
    }
    
    /**
     * 准备产品数据
     */
    private function prepare_product_data($product) {
        $product_data = [
            'external_id' => (string) $product->get_id(),
            'title' => $product->get_name(),
            'description' => wp_strip_all_tags($product->get_short_description() ?: $product->get_description()),
            'url' => get_permalink($product->get_id()),
            'image_full_url' => wp_get_attachment_url($product->get_image_id()),
            'price' => (float) $product->get_price(),
            'inventory_quantity' => $product->get_stock_quantity(),
            'categories' => $this->get_product_categories($product),
            'tags' => $this->get_product_tags($product),
            'type' => $product->get_type(),
            'status' => $product->get_status(),
            'created_at' => get_post_time('c', true, $product->get_id()),
            'updated_at' => get_post_modified_time('c', true, $product->get_id())
        ];
        
        // 处理变体产品
        if ($product->is_type('variable')) {
            $variations = $product->get_available_variations();
            $product_data['variants'] = array_map(function($variation) {
                return [
                    'external_id' => (string) $variation['variation_id'],
                    'title' => $variation['variation_description'],
                    'price' => (float) $variation['display_price'],
                    'inventory_quantity' => $variation['max_qty'] ?? null,
                    'attributes' => $variation['attributes']
                ];
            }, $variations);
        }
        
        return $product_data;
    }
    
    /**
     * 识别客户（创建/更新客户档案）
     */
    private function identify_customer($customer_data) {
        $payload = [
            'token' => $this->public_key,
            'properties' => [
                '$email' => $customer_data['email'],
                '$first_name' => $customer_data['first_name'],
                '$last_name' => $customer_data['last_name'],
                '$phone_number' => $customer_data['phone_number'],
                '$city' => $customer_data['city'],
                '$country' => $customer_data['country'],
                'registration_date' => $customer_data['registration_date'],
                'orders_count' => $customer_data['orders_count'],
                'total_spent' => $customer_data['total_spent'],
                'customer_segment' => $customer_data['customer_segment']
            ]
        ];
        
        return $this->make_track_request('identify', $payload);
    }
    
    /**
     * 跟踪下单事件
     */
    private function track_placed_order($order) {
        $customer_email = $order->get_billing_email();
        $order_items = $order->get_items();
        
        $event_data = [
            'event' => 'Placed Order',
            'customer_properties' => [
                '$email' => $customer_email
            ],
            'properties' => [
                'OrderID' => $order->get_id(),
                'OrderNumber' => $order->get_order_number(),
                'Total' => (float) $order->get_total(),
                'Subtotal' => (float) $order->get_subtotal(),
                'Tax' => (float) $order->get_total_tax(),
                'Shipping' => (float) $order->get_shipping_total(),
                'Discount' => (float) $order->get_discount_total(),
                'Currency' => $order->get_currency(),
                'Items' => array_map(function($item) {
                    $product = $item->get_product();
                    return [
                        'ProductID' => $product ? $product->get_id() : 0,
                        'ProductName' => $item->get_name(),
                        'Quantity' => $item->get_quantity(),
                        'Price' => (float) $item->get_total(),
                        'Categories' => $product ? $this->get_product_categories($product) : []
                    ];
                }, $order_items),
                'BillingAddress' => [
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name(),
                    'city' => $order->get_billing_city(),
                    'country' => $order->get_billing_country()
                ]
            ]
        ];
        
        return $this->track_event($event_data);
    }
    
    /**
     * 跟踪订单完成事件
     */
    private function track_fulfilled_order($order) {
        $customer_email = $order->get_billing_email();
        
        $event_data = [
            'event' => 'Fulfilled Order',
            'customer_properties' => [
                '$email' => $customer_email
            ],
            'properties' => [
                'OrderID' => $order->get_id(),
                'OrderNumber' => $order->get_order_number(),
                'Total' => (float) $order->get_total(),
                'FulfillmentDate' => current_time('c')
            ]
        ];
        
        return $this->track_event($event_data);
    }
    
    /**
     * 跟踪取消订单事件
     */
    private function track_cancelled_order($order) {
        $customer_email = $order->get_billing_email();
        
        $event_data = [
            'event' => 'Cancelled Order',
            'customer_properties' => [
                '$email' => $customer_email
            ],
            'properties' => [
                'OrderID' => $order->get_id(),
                'OrderNumber' => $order->get_order_number(),
                'Total' => (float) $order->get_total(),
                'CancellationDate' => current_time('c')
            ]
        ];
        
        return $this->track_event($event_data);
    }
    
    /**
     * 跟踪退款事件
     */
    private function track_refunded_order($order) {
        $customer_email = $order->get_billing_email();
        
        $event_data = [
            'event' => 'Refunded Order',
            'customer_properties' => [
                '$email' => $customer_email
            ],
            'properties' => [
                'OrderID' => $order->get_id(),
                'OrderNumber' => $order->get_order_number(),
                'RefundAmount' => (float) $order->get_total_refunded(),
                'RefundDate' => current_time('c')
            ]
        ];
        
        return $this->track_event($event_data);
    }
    
    /**
     * 更新客户档案
     */
    private function update_customer_profile($order) {
        $customer_id = $order->get_customer_id();
        if (!$customer_id) {
            return;
        }
        
        $user = get_userdata($customer_id);
        if ($user) {
            $this->sync_customer_to_klaviyo($customer_id);
        }
    }
    
    /**
     * 目录项目请求
     */
    private function catalog_item_request($product_data) {
        $endpoint = 'catalog-items';
        $payload = [
            'data' => [
                'type' => 'catalog-item',
                'attributes' => $product_data
            ]
        ];
        
        return $this->make_api_request($endpoint, 'POST', $payload);
    }
    
    /**
     * 跟踪事件
     */
    private function track_event($event_data) {
        $payload = [
            'token' => $this->public_key,
            'event' => $event_data['event'],
            'customer_properties' => $event_data['customer_properties'],
            'properties' => $event_data['properties']
        ];
        
        return $this->make_track_request('track', $payload);
    }
    
    /**
     * 添加到列表
     */
    private function add_to_list($email, $list_id) {
        $endpoint = "list/{$list_id}/members";
        $payload = [
            'profiles' => [
                'data' => [
                    [
                        'type' => 'profile',
                        'attributes' => [
                            'email' => $email
                        ]
                    ]
                ]
            ]
        ];
        
        return $this->make_api_request($endpoint, 'POST', $payload);
    }
    
    /**
     * 从列表移除
     */
    private function remove_from_list($email, $list_id) {
        $endpoint = "list/{$list_id}/members";
        $payload = [
            'emails' => [$email]
        ];
        
        return $this->make_api_request($endpoint, 'DELETE', $payload);
    }
    
    /**
     * 获取或创建细分列表
     */
    private function get_or_create_segment_list($segment_name) {
        // 首先检查是否已存在该细分的列表
        $lists = $this->get_lists();
        
        foreach ($lists as $list) {
            if ($list['name'] === $segment_name) {
                return $list['id'];
            }
        }
        
        // 创建新列表
        return $this->create_list($segment_name);
    }
    
    /**
     * 获取所有列表
     */
    public function get_lists() {
        $response = $this->make_api_request('lists', 'GET');
        
        if (is_wp_error($response)) {
            return [];
        }
        
        return $response['data'] ?? [];
    }
    
    /**
     * 创建新列表
     */
    public function create_list($list_name, $list_type = 'list') {
        $payload = [
            'data' => [
                'type' => 'list',
                'attributes' => [
                    'name' => $list_name,
                    'opt_in_process' => 'single_opt_in'
                ]
            ]
        ];
        
        $response = $this->make_api_request('lists', 'POST', $payload);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        return $response['data']['id'] ?? false;
    }
    
    /**
     * 获取列表成员
     */
    public function get_list_members($list_id, $page_size = 50) {
        $endpoint = "list/{$list_id}/members";
        $params = [
            'page_size' => $page_size
        ];
        
        $response = $this->make_api_request($endpoint, 'GET', [], $params);
        
        if (is_wp_error($response)) {
            return [];
        }
        
        return $response['data'] ?? [];
    }
    
    /**
     * 发送营销活动
     */
    public function send_campaign($campaign_data) {
        $payload = [
            'data' => [
                'type' => 'campaign',
                'attributes' => [
                    'name' => $campaign_data['name'],
                    'subject' => $campaign_data['subject'],
                    'from_email' => $campaign_data['from_email'],
                    'from_name' => $campaign_data['from_name'],
                    'list_id' => $campaign_data['list_id'],
                    'template_id' => $campaign_data['template_id']
                ]
            ]
        ];
        
        $response = $this->make_api_request('campaigns', 'POST', $payload);
        
        if (!is_wp_error($response) && isset($response['data']['id'])) {
            // 立即发送活动
            $campaign_id = $response['data']['id'];
            return $this->send_campaign_immediately($campaign_id);
        }
        
        return $response;
    }
    
    /**
     * 立即发送营销活动
     */
    private function send_campaign_immediately($campaign_id) {
        $endpoint = "campaign/{$campaign_id}/send";
        return $this->make_api_request($endpoint, 'POST');
    }
    
    /**
     * 获取活动分析
     */
    public function get_campaign_analytics($campaign_id) {
        $endpoint = "campaign/{$campaign_id}/statistics";
        return $this->make_api_request($endpoint, 'GET');
    }
    
    /**
     * 制作API请求
     */
    private function make_api_request($endpoint, $method = 'GET', $data = [], $params = []) {
        $url = $this->api_base_url . $endpoint;
        
        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Klaviyo-API-Key ' . $this->private_key,
                'Content-Type' => 'application/json',
                'revision' => '2023-10-15'
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
                'klaviyo_api_error',
                'Klaviyo API错误: ' . $response_code,
                [
                    'status' => $response_code,
                    'response' => $response_body
                ]
            );
        }
        
        return json_decode($response_body, true) ?: [];
    }
    
    /**
     * 制作Track API请求
     */
    private function make_track_request($endpoint, $data) {
        $url = 'https://a.klaviyo.com/api/' . $endpoint;
        
        $args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data),
            'timeout' => 15
        ];
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code >= 400) {
            return new WP_Error(
                'klaviyo_track_error',
                'Klaviyo Track API错误: ' . $response_code
            );
        }
        
        return [
            'success' => true,
            'status' => $response_code
        ];
    }
    
    /**
     * 获取当前客户邮箱
     */
    private function get_current_customer_email() {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            return $user->user_email;
        }
        
        // 从会话或购物车中获取邮箱
        $session_email = WC()->session->get('klaviyo_user_email');
        if ($session_email) {
            return $session_email;
        }
        
        return null;
    }
    
    /**
     * 获取产品分类
     */
    private function get_product_categories($product) {
        $categories = wp_get_post_terms($product->get_id(), 'product_cat');
        return array_map(function($category) {
            return $category->name;
        }, $categories);
    }
    
    /**
     * 获取产品标签
     */
    private function get_product_tags($product) {
        $tags = wp_get_post_terms($product->get_id(), 'product_tag');
        return array_map(function($tag) {
            return $tag->name;
        }, $tags);
    }
    
    /**
     * 获取购物车商品数据
     */
    private function get_cart_items_data() {
        $cart_items = WC()->cart->get_cart();
        $items_data = [];
        
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $items_data[] = [
                'product_id' => $product->get_id(),
                'product_name' => $product->get_name(),
                'quantity' => $cart_item['quantity'],
                'price' => $product->get_price()
            ];
        }
        
        return $items_data;
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
     * 获取客户细分
     */
    private function get_customer_segment($user_id) {
        // 这里可以实现客户细分逻辑
        $total_spent = $this->get_customer_total_spent($user_id);
        
        if ($total_spent > 1000) {
            return 'VIP客户';
        } elseif ($total_spent > 500) {
            return '忠实客户';
        } elseif ($total_spent > 100) {
            return '普通客户';
        } else {
            return '新客户';
        }
    }
    
    /**
     * 记录同步活动
     */
    private function log_sync_activity($type, $id, $result) {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'type' => $type,
            'entity_id' => $id,
            'result' => is_wp_error($result) ? 'error' : 'success',
            'message' => is_wp_error($result) ? $result->get_error_message() : '同步成功'
        ];
        
        // 这里可以实现日志存储逻辑
        error_log("WAI Klaviyo Sync: " . json_encode($log_entry));
    }
    
    /**
     * 获取同步状态报告
     */
    public function get_sync_report() {
        global $wpdb;
        
        $report = [
            'total_customers' => $this->get_total_customers_count(),
            'synced_customers' => $this->get_synced_customers_count(),
            'total_products' => $this->get_total_products_count(),
            'synced_products' => $this->get_synced_products_count(),
            'recent_activities' => $this->get_recent_sync_activities(),
            'list_memberships' => $this->get_list_membership_stats()
        ];
        
        return $report;
    }
    
    /**
     * 获取总客户数
     */
    private function get_total_customers_count() {
        return count(get_users(['role' => 'customer']));
    }
    
    /**
     * 获取已同步客户数
     */
    private function get_synced_customers_count() {
        // 这里可以实现已同步客户的计数逻辑
        return 0;
    }
    
    /**
     * 获取总产品数
     */
    private function get_total_products_count() {
        return wp_count_posts('product')->publish;
    }
    
    /**
     * 获取已同步产品数
     */
    private function get_synced_products_count() {
        // 这里可以实现已同步产品的计数逻辑
        return 0;
    }
    
    /**
     * 获取最近同步活动
     */
    private function get_recent_sync_activities() {
        // 这里可以实现最近同步活动的获取逻辑
        return [];
    }
    
    /**
     * 获取列表成员统计
     */
    private function get_list_membership_stats() {
        $lists = $this->get_lists();
        $stats = [];
        
        foreach ($lists as $list) {
            $members = $this->get_list_members($list['id'], 1); // 只获取第一页来计数
            $stats[] = [
                'list_name' => $list['name'],
                'member_count' => $list['profile_count'] ?? 0
            ];
        }
        
        return $stats;
    }
}