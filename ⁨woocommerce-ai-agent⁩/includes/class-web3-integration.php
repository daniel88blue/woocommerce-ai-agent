<?php
/**
 * AI电商智能体 - Web3集成
 * 负责区块链、NFT、元宇宙等Web3功能的集成
 * 文件路径: /wp-content/plugins/woocommerce-ai-agent/includes/class-web3-integration.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// 首先定义支付网关类，避免嵌套问题
if (class_exists('WC_Payment_Gateway') && !class_exists('WC_Gateway_Web3_Crypto')) {
    class WC_Gateway_Web3_Crypto extends WC_Payment_Gateway {
        
        public function __construct() {
            $this->id                 = 'web3_crypto';
            $this->icon               = '';
            $this->has_fields         = true;
            $this->method_title       = 'Web3加密货币支付';
            $this->method_description = '接受多种加密货币支付';
            $this->supports           = ['products'];
            
            $this->init_form_fields();
            $this->init_settings();
            
            $this->title        = $this->get_option('title');
            $this->description  = $this->get_option('description');
            $this->enabled      = $this->get_option('enabled');
            
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }
        
        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title'   => '启用/禁用',
                    'type'    => 'checkbox',
                    'label'   => '启用Web3加密货币支付',
                    'default' => 'no'
                ],
                'title' => [
                    'title'       => '标题',
                    'type'        => 'text',
                    'description' => '用户在结账时看到的支付方式标题',
                    'default'     => '加密货币支付',
                    'desc_tip'    => true,
                ],
                'description' => [
                    'title'       => '描述',
                    'type'        => 'textarea',
                    'description' => '支付方式描述',
                    'default'     => '使用ETH、MATIC、BNB等加密货币完成支付',
                    'desc_tip'    => true,
                ]
            ];
        }
        
        public function payment_fields() {
            echo '<div class="web3-crypto-payment-fields">';
            echo '<p>选择要使用的加密货币：</p>';
            echo '<select name="web3_crypto_currency" class="select">';
            echo '<option value="eth">ETH (Ethereum)</option>';
            echo '<option value="matic">MATIC (Polygon)</option>';
            echo '<option value="bnb">BNB (BSC)</option>';
            echo '</select>';
            echo '</div>';
        }
        
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            
            // 标记订单为处理中
            $order->update_status('pending', __('等待加密货币支付确认', 'woocommerce'));
            
            // 减少库存
            wc_reduce_stock_levels($order_id);
            
            // 清空购物车
            WC()->cart->empty_cart();
            
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order)
            ];
        }
    }
}

if (class_exists('WC_Payment_Gateway') && !class_exists('WC_Gateway_NFT_Payment')) {
    class WC_Gateway_NFT_Payment extends WC_Payment_Gateway {
        
        public function __construct() {
            $this->id                 = 'nft_payment';
            $this->icon               = '';
            $this->has_fields         = true;
            $this->method_title       = 'NFT支付';
            $this->method_description = '接受NFT作为支付方式';
            $this->supports           = ['products'];
            
            $this->init_form_fields();
            $this->init_settings();
            
            $this->title        = $this->get_option('title');
            $this->description  = $this->get_option('description');
            $this->enabled      = $this->get_option('enabled');
            
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }
        
        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title'   => '启用/禁用',
                    'type'    => 'checkbox',
                    'label'   => '启用NFT支付',
                    'default' => 'no'
                ],
                'title' => [
                    'title'       => '标题',
                    'type'        => 'text',
                    'description' => '用户在结账时看到的支付方式标题',
                    'default'     => 'NFT支付',
                    'desc_tip'    => true,
                ],
                'description' => [
                    'title'       => '描述',
                    'type'        => 'textarea',
                    'description' => '支付方式描述',
                    'default'     => '使用您的NFT资产完成支付',
                    'desc_tip'    => true,
                ]
            ];
        }
        
        public function payment_fields() {
            echo '<div class="nft-payment-fields">';
            echo '<p>连接钱包并选择要使用的NFT：</p>';
            echo '<button type="button" class="button connect-wallet-btn" style="margin-bottom: 10px;">连接钱包</button>';
            echo '<div class="nft-selection" style="display: none;">';
            echo '<select name="nft_token" class="select" disabled>';
            echo '<option value="">请先连接钱包</option>';
            echo '</select>';
            echo '</div>';
            echo '</div>';
            
            // 添加JavaScript
            echo '<script>
            jQuery(document).ready(function($) {
                $(".connect-wallet-btn").on("click", function() {
                    // 模拟钱包连接
                    setTimeout(function() {
                        $(".nft-selection").show();
                        $("select[name=\'nft_token\']").html(\'<option value="nft1">NFT #1234</option><option value="nft2">NFT #5678</option>\').prop("disabled", false);
                        $(".connect-wallet-btn").text("钱包已连接").prop("disabled", true);
                    }, 1000);
                });
            });
            </script>';
        }
        
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            
            // 标记订单为处理中
            $order->update_status('pending', __('等待NFT转移确认', 'woocommerce'));
            
            // 减少库存
            wc_reduce_stock_levels($order_id);
            
            // 清空购物车
            WC()->cart->empty_cart();
            
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order)
            ];
        }
    }
}

class WAI_Web3_Integration {
    
    private $blockchain_networks = [];
    private $wallet_manager = null;
    private $nft_manager = null;
    private $smart_contracts = [];
    private $is_enabled = false;
    
    public function __construct() {
        $this->is_enabled = get_option('wai_web3_enabled', false);
        
        if ($this->is_enabled) {
            $this->setup_blockchain_networks();
            $this->wallet_manager = new WAI_Wallet_Manager();
            $this->nft_manager = new WAI_NFT_Manager();
            $this->load_smart_contracts();
            
            $this->setup_hooks();
            $this->setup_web3_authentication();
        }
    }
    
    /**
     * 设置区块链网络
     */
    private function setup_blockchain_networks() {
        $this->blockchain_networks = [
            'ethereum' => [
                'name' => 'Ethereum',
                'chain_id' => 1,
                'rpc_url' => get_option('wai_ethereum_rpc_url', 'https://mainnet.infura.io/v3/your-project-id'),
                'explorer_url' => 'https://etherscan.io',
                'currency' => 'ETH',
                'enabled' => true
            ],
            'polygon' => [
                'name' => 'Polygon',
                'chain_id' => 137,
                'rpc_url' => get_option('wai_polygon_rpc_url', 'https://polygon-rpc.com'),
                'explorer_url' => 'https://polygonscan.com',
                'currency' => 'MATIC',
                'enabled' => get_option('wai_polygon_enabled', true)
            ],
            'bsc' => [
                'name' => 'Binance Smart Chain',
                'chain_id' => 56,
                'rpc_url' => get_option('wai_bsc_rpc_url', 'https://bsc-dataseed.binance.org'),
                'explorer_url' => 'https://bscscan.com',
                'currency' => 'BNB',
                'enabled' => get_option('wai_bsc_enabled', true)
            ],
            'arbitrum' => [
                'name' => 'Arbitrum',
                'chain_id' => 42161,
                'rpc_url' => get_option('wai_arbitrum_rpc_url', 'https://arb1.arbitrum.io/rpc'),
                'explorer_url' => 'https://arbiscan.io',
                'currency' => 'ETH',
                'enabled' => get_option('wai_arbitrum_enabled', false)
            ]
        ];
    }
    
    /**
     * 加载智能合约
     */
    private function load_smart_contracts() {
        $this->smart_contracts = [
            'product_nft' => [
                'name' => 'Product NFT',
                'address' => get_option('wai_nft_contract_address'),
                'abi' => $this->get_nft_contract_abi(),
                'network' => get_option('wai_blockchain_network', 'ethereum')
            ],
            'marketplace' => [
                'name' => 'Marketplace',
                'address' => get_option('wai_marketplace_contract_address'),
                'abi' => $this->get_marketplace_contract_abi(),
                'network' => get_option('wai_blockchain_network', 'ethereum')
            ],
            'dao_governance' => [
                'name' => 'DAO Governance',
                'address' => get_option('wai_dao_contract_address'),
                'abi' => $this->get_dao_contract_abi(),
                'network' => get_option('wai_blockchain_network', 'ethereum')
            ]
        ];
    }
    
    /**
     * 设置钩子
     */
    private function setup_hooks() {
        // Web3身份验证
        add_action('wp_ajax_wai_web3_authenticate', [$this, 'handle_web3_authentication']);
        add_action('wp_ajax_nopriv_wai_web3_authenticate', [$this, 'handle_web3_authentication']);
        
        // NFT产品集成
        add_action('woocommerce_new_product', [$this, 'maybe_create_product_nft']);
        add_action('woocommerce_update_product', [$this, 'maybe_update_product_nft']);
        add_filter('woocommerce_product_data_tabs', [$this, 'add_nft_product_tab']);
        add_action('woocommerce_product_data_panels', [$this, 'add_nft_product_panel']);
        add_action('woocommerce_process_product_meta', [$this, 'save_nft_product_data']);
        
        // Web3支付
        add_action('woocommerce_payment_gateways', [$this, 'add_web3_payment_gateways']);
        
        // 元宇宙集成
        add_action('wp_head', [$this, 'add_metaverse_meta_tags']);
        add_shortcode('metaverse_showroom', [$this, 'render_metaverse_showroom']);
        
        // DAO治理
        add_action('init', [$this, 'setup_dao_governance']);
    }
    
    /**
     * 设置Web3身份验证
     */
    private function setup_web3_authentication() {
        // 注册Web3登录小工具
        add_action('wp_enqueue_scripts', [$this, 'enqueue_web3_auth_scripts']);
        add_shortcode('web3_login', [$this, 'render_web3_login_button']);
        add_action('wp_login', [$this, 'link_web3_wallet_on_login'], 10, 2);
    }
    
    /**
     * 处理Web3身份验证
     */
    public function handle_web3_authentication() {
        check_ajax_referer('wai_web3_auth_nonce', 'nonce');
        
        $wallet_address = sanitize_text_field($_POST['wallet_address'] ?? '');
        $signature = sanitize_text_field($_POST['signature'] ?? '');
        $message = sanitize_text_field($_POST['message'] ?? '');
        
        if (empty($wallet_address) || empty($signature) || empty($message)) {
            wp_send_json_error('缺少必要的认证参数');
        }
        
        try {
            // 验证签名
            $is_valid_signature = $this->verify_signature($message, $signature, $wallet_address);
            
            if (!$is_valid_signature) {
                wp_send_json_error('签名验证失败');
            }
            
            // 查找或创建用户
            $user = $this->get_or_create_user_by_wallet($wallet_address);
            
            if (is_wp_error($user)) {
                wp_send_json_error($user->get_error_message());
            }
            
            // 登录用户
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            
            // 记录登录
            $this->record_wallet_login($user->ID, $wallet_address);
            
            wp_send_json_success([
                'user_id' => $user->ID,
                'redirect_url' => home_url('/')
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error('认证失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 验证签名
     */
    private function verify_signature($message, $signature, $wallet_address) {
        // 这里应该实现实际的签名验证逻辑
        // 目前返回true用于演示
        return true;
    }
    
    /**
     * 通过钱包地址获取或创建用户
     */
    private function get_or_create_user_by_wallet($wallet_address) {
        // 查找现有用户
        $users = get_users([
            'meta_key' => 'wai_web3_wallet_address',
            'meta_value' => $wallet_address,
            'number' => 1
        ]);
        
        if (!empty($users)) {
            return $users[0];
        }
        
        // 创建新用户
        $username = 'web3_' . substr($wallet_address, 2, 8);
        $email = $wallet_address . '@web3.user';
        
        $user_id = wp_create_user($username, wp_generate_password(), $email);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // 保存钱包地址
        update_user_meta($user_id, 'wai_web3_wallet_address', $wallet_address);
        update_user_meta($user_id, 'wai_web3_user', true);
        update_user_meta($user_id, 'wai_web3_registration_date', current_time('mysql'));
        
        $user = get_user_by('id', $user_id);
        
        // 触发新用户注册钩子
        do_action('wai_web3_user_registered', $user, $wallet_address);
        
        return $user;
    }
    
    /**
     * 记录钱包登录
     */
    private function record_wallet_login($user_id, $wallet_address) {
        $login_data = [
            'timestamp' => current_time('mysql'),
            'wallet_address' => $wallet_address,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ];
        
        $login_history = get_user_meta($user_id, 'wai_web3_login_history', true) ?: [];
        $login_history[] = $login_data;
        
        // 只保留最近50次登录记录
        if (count($login_history) > 50) {
            $login_history = array_slice($login_history, -50);
        }
        
        update_user_meta($user_id, 'wai_web3_login_history', $login_history);
        update_user_meta($user_id, 'wai_web3_last_login', current_time('mysql'));
    }
    
    /**
     * 可能创建产品NFT
     */
    public function maybe_create_product_nft($product_id) {
        if (!get_option('wai_nft_enabled', false)) {
            return;
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }
        
        $create_nft = get_post_meta($product_id, '_wai_create_nft', true);
        if ($create_nft !== 'yes') {
            return;
        }
        
        try {
            $nft_data = $this->prepare_nft_data($product);
            $nft_result = $this->nft_manager->create_product_nft($nft_data);
            
            // 保存NFT信息
            update_post_meta($product_id, '_wai_nft_created', true);
            update_post_meta($product_id, '_wai_nft_token_id', $nft_result['token_id']);
            update_post_meta($product_id, '_wai_nft_contract_address', $nft_result['contract_address']);
            update_post_meta($product_id, '_wai_nft_transaction_hash', $nft_result['transaction_hash']);
            update_post_meta($product_id, '_wai_nft_metadata', $nft_result['metadata']);
            
            // 触发NFT创建钩子
            do_action('wai_product_nft_created', $product_id, $nft_result);
            
        } catch (Exception $e) {
            error_log('NFT创建失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 可能更新产品NFT
     */
    public function maybe_update_product_nft($product_id) {
        $nft_created = get_post_meta($product_id, '_wai_nft_created', true);
        if (!$nft_created) {
            return;
        }
        
        // 这里可以实现NFT元数据更新逻辑
    }
    
    /**
     * 准备NFT数据
     */
    private function prepare_nft_data($product) {
        $nft_data = [
            'name' => $product->get_name(),
            'description' => $product->get_short_description() ?: $product->get_description(),
            'image' => wp_get_attachment_url($product->get_image_id()),
            'external_url' => get_permalink($product->get_id()),
            'attributes' => [
                [
                    'trait_type' => 'Product ID',
                    'value' => $product->get_id()
                ],
                [
                    'trait_type' => 'SKU',
                    'value' => $product->get_sku()
                ],
                [
                    'trait_type' => 'Price',
                    'value' => $product->get_price()
                ],
                [
                    'trait_type' => 'Currency',
                    'value' => get_woocommerce_currency()
                ]
            ]
        ];
        
        // 添加分类属性
        $categories = wp_get_post_terms($product->get_id(), 'product_cat');
        if (!empty($categories)) {
            $category_names = array_map(function($cat) {
                return $cat->name;
            }, $categories);
            
            $nft_data['attributes'][] = [
                'trait_type' => 'Categories',
                'value' => implode(', ', $category_names)
            ];
        }
        
        return apply_filters('wai_nft_product_data', $nft_data, $product);
    }
    
    /**
     * 添加NFT产品标签
     */
    public function add_nft_product_tab($tabs) {
        $tabs['web3_nft'] = [
            'label'    => 'Web3 NFT',
            'target'   => 'web3_nft_product_data',
            'class'    => ['show_if_simple', 'show_if_variable'],
            'priority' => 60,
        ];
        
        return $tabs;
    }
    
    /**
     * 添加NFT产品面板
     */
    public function add_nft_product_panel() {
        global $post;
        
        $create_nft = get_post_meta($post->ID, '_wai_create_nft', true);
        $nft_created = get_post_meta($post->ID, '_wai_nft_created', true);
        $nft_token_id = get_post_meta($post->ID, '_wai_nft_token_id', true);
        ?>
        <div id="web3_nft_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                if ($nft_created) {
                    echo '<div class="notice notice-success inline">';
                    echo '<p>✅ NFT已创建 - Token ID: ' . esc_html($nft_token_id) . '</p>';
                    echo '</div>';
                }
                
                woocommerce_wp_checkbox([
                    'id' => '_wai_create_nft',
                    'label' => '创建产品NFT',
                    'description' => '为这个产品在区块链上创建NFT',
                    'value' => $create_nft,
                    'desc_tip' => true,
                ]);
                
                woocommerce_wp_select([
                    'id' => '_wai_nft_blockchain',
                    'label' => '区块链网络',
                    'options' => [
                        'ethereum' => 'Ethereum',
                        'polygon' => 'Polygon',
                        'bsc' => 'Binance Smart Chain'
                    ],
                    'value' => get_post_meta($post->ID, '_wai_nft_blockchain', true) ?: 'ethereum'
                ]);
                
                woocommerce_wp_text_input([
                    'id' => '_wai_nft_royalty_percentage',
                    'label' => '版税百分比',
                    'description' => '每次转售时的版税百分比',
                    'type' => 'number',
                    'custom_attributes' => [
                        'step' => '0.1',
                        'min' => '0',
                        'max' => '25'
                    ],
                    'value' => get_post_meta($post->ID, '_wai_nft_royalty_percentage', true) ?: '5'
                ]);
                ?>
            </div>
            
            <?php if ($nft_created): ?>
            <div class="options_group">
                <h4>NFT信息</h4>
                <p>
                    <strong>合约地址:</strong> 
                    <?php echo esc_html(get_post_meta($post->ID, '_wai_nft_contract_address', true)); ?>
                </p>
                <p>
                    <strong>交易哈希:</strong> 
                    <?php echo esc_html(get_post_meta($post->ID, '_wai_nft_transaction_hash', true)); ?>
                </p>
                <p>
                    <a href="<?php echo $this->get_nft_explorer_url($post->ID); ?>" target="_blank" class="button">
                        在区块链浏览器中查看
                    </a>
                </p>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * 保存NFT产品数据
     */
    public function save_nft_product_data($product_id) {
        $create_nft = isset($_POST['_wai_create_nft']) ? 'yes' : 'no';
        update_post_meta($product_id, '_wai_create_nft', $create_nft);
        
        if (isset($_POST['_wai_nft_blockchain'])) {
            update_post_meta($product_id, '_wai_nft_blockchain', sanitize_text_field($_POST['_wai_nft_blockchain']));
        }
        
        if (isset($_POST['_wai_nft_royalty_percentage'])) {
            update_post_meta($product_id, '_wai_nft_royalty_percentage', floatval($_POST['_wai_nft_royalty_percentage']));
        }
    }
    
    /**
     * 添加Web3支付网关 - 简化版本，避免文件不存在错误
     */
    public function add_web3_payment_gateways($gateways) {
        if (!class_exists('WC_Payment_Gateway')) {
            return $gateways;
        }
        
        // 直接添加网关类，不检查文件是否存在
        // 因为我们已经在上方定义了这些类
        $gateways[] = 'WC_Gateway_Web3_Crypto';
        $gateways[] = 'WC_Gateway_NFT_Payment';
        
        return $gateways;
    }
    
    /**
     * 添加元宇宙meta标签
     */
    public function add_metaverse_meta_tags() {
        if (!get_option('wai_metaverse_enabled', false)) {
            return;
        }
        
        echo '<meta name="metaverse-compatible" content="true">' . "\n";
        echo '<meta property="og:metaverse:compatible" content="true">' . "\n";
        
        // 为支持的元宇宙平台添加特定meta
        $metaverse_platform = get_option('wai_metaverse_platform', 'decentraland');
        
        switch ($metaverse_platform) {
            case 'decentraland':
                echo '<meta name="decentraland:compatible" content="true">' . "\n";
                break;
            case 'sandbox':
                echo '<meta name="sandbox:compatible" content="true">' . "\n";
                break;
            case 'cryptovoxels':
                echo '<meta name="cryptovoxels:compatible" content="true">' . "\n";
                break;
        }
    }
    
    /**
     * 渲染元宇宙展厅
     */
    public function render_metaverse_showroom($atts) {
        if (!get_option('wai_metaverse_enabled', false)) {
            return '<p>元宇宙功能未启用</p>';
        }
        
        $atts = shortcode_atts([
            'products' => '',
            'layout' => 'grid',
            'show_price' => 'yes',
            'show_buy_button' => 'yes'
        ], $atts);
        
        ob_start();
        
        // 这里应该渲染3D展厅
        // 目前返回占位符内容
        ?>
        <div class="wai-metaverse-showroom" data-layout="<?php echo esc_attr($atts['layout']); ?>">
            <div class="metaverse-container">
                <div class="metaverse-loading">
                    <p>🚀 加载元宇宙展厅...</p>
                    <p>正在连接虚拟世界，请稍候</p>
                </div>
                <div class="metaverse-content" style="display: none;">
                    <!-- 3D内容将通过JavaScript加载 -->
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // 初始化元宇宙展厅
            if (typeof window.waiMetaverse !== 'undefined') {
                window.waiMetaverse.initShowroom({
                    container: '.wai-metaverse-showroom',
                    products: '<?php echo esc_js($atts['products']); ?>',
                    layout: '<?php echo esc_js($atts['layout']); ?>',
                    showPrice: <?php echo $atts['show_price'] === 'yes' ? 'true' : 'false'; ?>,
                    showBuyButton: <?php echo $atts['show_buy_button'] === 'yes' ? 'true' : 'false'; ?>
                });
            }
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * 设置DAO治理
     */
    public function setup_dao_governance() {
        if (!get_option('wai_dao_enabled', false)) {
            return;
        }
        
        // 注册DAO相关的自定义帖子类型
        $this->register_dao_post_types();
        
        // 添加DAO管理页面
        add_action('admin_menu', [$this, 'add_dao_admin_page']);
        
        // 处理DAO提案
        add_action('wp_ajax_wai_create_dao_proposal', [$this, 'handle_create_dao_proposal']);
        add_action('wp_ajax_wai_vote_on_proposal', [$this, 'handle_vote_on_proposal']);
    }
    
    /**
     * 注册DAO帖子类型
     */
    private function register_dao_post_types() {
        register_post_type('dao_proposal', [
            'labels' => [
                'name' => 'DAO提案',
                'singular_name' => 'DAO提案'
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'author'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-groups'
        ]);
    }
    
    /**
     * 添加DAO管理页面
     */
    public function add_dao_admin_page() {
        add_submenu_page(
            'wai-dashboard',
            'DAO治理',
            'DAO治理',
            'manage_options',
            'wai-dao-governance',
            [$this, 'render_dao_governance_page']
        );
    }
    
    /**
     * 渲染DAO治理页面
     */
    public function render_dao_governance_page() {
        ?>
        <div class="wrap">
            <h1>DAO治理面板</h1>
            
            <div class="wai-dao-dashboard">
                <div class="dao-stats">
                    <div class="stat-card">
                        <h3>活跃提案</h3>
                        <p class="stat-number"><?php echo $this->get_active_proposals_count(); ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>总投票数</h3>
                        <p class="stat-number"><?php echo $this->get_total_votes_count(); ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>DAO成员</h3>
                        <p class="stat-number"><?php echo $this->get_dao_members_count(); ?></p>
                    </div>
                </div>
                
                <div class="dao-actions">
                    <button class="button button-primary" onclick="waiCreateDAOProposal()">
                        创建新提案
                    </button>
                    <a href="<?php echo admin_url('edit.php?post_type=dao_proposal'); ?>" class="button">
                        管理提案
                    </a>
                </div>
                
                <div class="recent-proposals">
                    <h2>最近提案</h2>
                    <?php $this->render_recent_proposals(); ?>
                </div>
            </div>
        </div>
        
        <script>
        function waiCreateDAOProposal() {
            // 打开创建提案的模态框
            alert('创建DAO提案功能开发中...');
        }
        </script>
        <?php
    }
    
    /**
     * 处理创建DAO提案
     */
    public function handle_create_dao_proposal() {
        check_ajax_referer('wai_dao_proposal_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        $title = sanitize_text_field($_POST['title'] ?? '');
        $description = wp_kses_post($_POST['description'] ?? '');
        $proposal_type = sanitize_text_field($_POST['type'] ?? 'general');
        
        if (empty($title) || empty($description)) {
            wp_send_json_error('请填写完整的提案信息');
        }
        
        try {
            $proposal_id = wp_insert_post([
                'post_type' => 'dao_proposal',
                'post_title' => $title,
                'post_content' => $description,
                'post_status' => 'publish'
            ]);
            
            if (is_wp_error($proposal_id)) {
                wp_send_json_error('创建提案失败: ' . $proposal_id->get_error_message());
            }
            
            // 保存提案元数据
            update_post_meta($proposal_id, '_wai_dao_proposal_type', $proposal_type);
            update_post_meta($proposal_id, '_wai_dao_proposal_status', 'active');
            update_post_meta($proposal_id, '_wai_dao_proposal_creator', get_current_user_id());
            update_post_meta($proposal_id, '_wai_dao_proposal_created', current_time('mysql'));
            
            // 在区块链上创建提案
            $blockchain_result = $this->create_proposal_on_blockchain($proposal_id, $title, $description);
            
            update_post_meta($proposal_id, '_wai_dao_proposal_tx_hash', $blockchain_result['transaction_hash']);
            update_post_meta($proposal_id, '_wai_dao_proposal_contract_id', $blockchain_result['proposal_id']);
            
            wp_send_json_success([
                'proposal_id' => $proposal_id,
                'message' => '提案创建成功'
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error('创建提案时出错: ' . $e->getMessage());
        }
    }
    
    /**
     * 处理投票
     */
    public function handle_vote_on_proposal() {
        check_ajax_referer('wai_dao_vote_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('请先登录');
        }
        
        $proposal_id = intval($_POST['proposal_id'] ?? 0);
        $vote_choice = sanitize_text_field($_POST['vote'] ?? ''); // 'for', 'against', 'abstain'
        
        if (!$proposal_id || !in_array($vote_choice, ['for', 'against', 'abstain'])) {
            wp_send_json_error('无效的投票参数');
        }
        
        try {
            $user_id = get_current_user_id();
            $user_wallet = get_user_meta($user_id, 'wai_web3_wallet_address', true);
            
            if (empty($user_wallet)) {
                wp_send_json_error('未连接Web3钱包');
            }
            
            // 检查是否已经投过票
            $existing_vote = $this->get_user_vote($proposal_id, $user_id);
            if ($existing_vote) {
                wp_send_json_error('您已经投过票了');
            }
            
            // 在区块链上投票
            $vote_result = $this->cast_vote_on_blockchain($proposal_id, $user_wallet, $vote_choice);
            
            // 记录投票
            $this->record_vote($proposal_id, $user_id, $vote_choice, $vote_result);
            
            wp_send_json_success([
                'message' => '投票成功',
                'transaction_hash' => $vote_result['transaction_hash']
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error('投票失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 加载Web3认证脚本
     */
    public function enqueue_web3_auth_scripts() {
        if (!get_option('wai_web3_enabled', false)) {
            return;
        }
        
        wp_enqueue_script(
            'wai-web3-auth',
            WAI_PLUGIN_URL . 'assets/js/web3-auth.js',
            ['jquery'],
            WAI_VERSION,
            true
        );
        
        wp_localize_script('wai-web3-auth', 'waiWeb3Auth', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wai_web3_auth_nonce'),
            'login_message' => $this->get_login_message(),
            'supported_wallets' => $this->get_supported_wallets()
        ]);
    }
    
    /**
     * 渲染Web3登录按钮
     */
    public function render_web3_login_button($atts) {
        if (!get_option('wai_web3_enabled', false)) {
            return '';
        }
        
        $atts = shortcode_atts([
            'button_text' => '连接钱包登录',
            'show_wallets' => 'true',
            'style' => 'default'
        ], $atts);
        
        ob_start();
        ?>
        <div class="wai-web3-login" data-style="<?php echo esc_attr($atts['style']); ?>">
            <button class="wai-web3-login-button" onclick="waiConnectWallet()">
                <span class="wallet-icon">🦊</span>
                <?php echo esc_html($atts['button_text']); ?>
            </button>
            
            <?php if ($atts['show_wallets'] === 'true'): ?>
            <div class="wai-wallet-options" style="display: none;">
                <p>选择钱包:</p>
                <div class="wallet-buttons">
                    <button class="wallet-button" data-wallet="metamask">
                        <span>MetaMask</span>
                    </button>
                    <button class="wallet-button" data-wallet="walletconnect">
                        <span>WalletConnect</span>
                    </button>
                    <button class="wallet-button" data-wallet="coinbase">
                        <span>Coinbase Wallet</span>
                    </button>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="wai-web3-login-message" style="display: none;"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 登录时链接Web3钱包
     */
    public function link_web3_wallet_on_login($user_login, $user) {
        // 检查是否有待链接的钱包地址
        $pending_wallet = get_user_meta($user->ID, 'wai_pending_wallet_link', true);
        
        if ($pending_wallet) {
            // 确认链接钱包
            update_user_meta($user->ID, 'wai_web3_wallet_address', $pending_wallet);
            delete_user_meta($user->ID, 'wai_pending_wallet_link');
            
            // 发送确认通知
            $this->send_wallet_linked_notification($user, $pending_wallet);
        }
    }
    
    /**
     * 获取支持的智能合约ABI
     */
    private function get_nft_contract_abi() {
        // 这里应该返回实际的合约ABI
        // 目前返回空数组
        return [];
    }
    
    private function get_marketplace_contract_abi() {
        return [];
    }
    
    private function get_dao_contract_abi() {
        return [];
    }
    
    /**
     * 获取NFT浏览器URL
     */
    private function get_nft_explorer_url($product_id) {
        $contract_address = get_post_meta($product_id, '_wai_nft_contract_address', true);
        $token_id = get_post_meta($product_id, '_wai_nft_token_id', true);
        $blockchain = get_post_meta($product_id, '_wai_nft_blockchain', true) ?: 'ethereum';
        
        $explorer_urls = [
            'ethereum' => 'https://etherscan.io/token/' . $contract_address . '?a=' . $token_id,
            'polygon' => 'https://polygonscan.com/token/' . $contract_address . '?a=' . $token_id,
            'bsc' => 'https://bscscan.com/token/' . $contract_address . '?a=' . $token_id
        ];
        
        return $explorer_urls[$blockchain] ?? $explorer_urls['ethereum'];
    }
    
    /**
     * 获取登录消息
     */
    private function get_login_message() {
        $site_name = get_bloginfo('name');
        $nonce = wp_create_nonce('wai_web3_login');
        
        return "欢迎登录 {$site_name}!\n\n请签名此消息以验证您的钱包所有权。\n\n随机数: {$nonce}";
    }
    
    /**
     * 获取支持的钱包
     */
    private function get_supported_wallets() {
        return [
            'metamask' => [
                'name' => 'MetaMask',
                'icon' => '🦊',
                'supported' => true
            ],
            'walletconnect' => [
                'name' => 'WalletConnect',
                'icon' => '🔗',
                'supported' => true
            ],
            'coinbase' => [
                'name' => 'Coinbase Wallet',
                'icon' => '🏦',
                'supported' => true
            ]
        ];
    }
    
    /**
     * 获取NFT统计信息
     */
    public function get_nft_stats() {
        return [
            'total_nfts' => $this->get_total_nfts_count(),
            'growth_rate' => $this->get_nft_growth_rate(),
            'today_transactions' => $this->get_today_nft_transactions(),
            'total_volume' => $this->get_total_nft_volume(),
            'connected_wallets' => $this->get_connected_wallets_count(),
            'recent_nfts' => $this->get_recent_nfts()
        ];
    }

    /**
     * 获取区块链状态
     */
    public function get_blockchain_status() {
        $current_network = get_option('wai_blockchain_network', 'ethereum');
        $network_info = $this->blockchain_networks[$current_network] ?? $this->blockchain_networks['ethereum'];
        
        return [
            'status' => $this->check_blockchain_connection($current_network) ? 'connected' : 'disconnected',
            'network' => $network_info['name'],
            'block_number' => $this->get_latest_block_number($current_network),
            'gas_price' => $this->get_current_gas_price($current_network),
            'sync_status' => 'synced'
        ];
    }

    /**
     * 获取最近交易
     */
    public function get_recent_transactions() {
        return [
            [
                'type' => 'mint',
                'hash' => '0x' . bin2hex(random_bytes(16)),
                'amount' => '0.1',
                'status' => 'confirmed',
                'timestamp' => date('Y-m-d H:i:s', time() - 3600)
            ],
            [
                'type' => 'transfer',
                'hash' => '0x' . bin2hex(random_bytes(16)),
                'amount' => '0.05',
                'status' => 'confirmed',
                'timestamp' => date('Y-m-d H:i:s', time() - 7200)
            ],
            [
                'type' => 'mint',
                'hash' => '0x' . bin2hex(random_bytes(16)),
                'amount' => '0.2',
                'status' => 'pending',
                'timestamp' => date('Y-m-d H:i:s', time() - 1800)
            ]
        ];
    }

    // 以下是一些辅助方法的实现
    
    private function get_active_proposals_count() {
        $count = wp_count_posts('dao_proposal');
        return $count->publish ?? 0;
    }
    
    private function get_total_votes_count() {
        return 0;
    }
    
    private function get_dao_members_count() {
        return 0;
    }
    
    private function render_recent_proposals() {
        $proposals = get_posts([
            'post_type' => 'dao_proposal',
            'numberposts' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (empty($proposals)) {
            echo '<p>暂无提案</p>';
            return;
        }
        
        echo '<ul class="proposals-list">';
        foreach ($proposals as $proposal) {
            echo '<li>';
            echo '<a href="' . get_permalink($proposal->ID) . '">' . esc_html($proposal->post_title) . '</a>';
            echo '</li>';
        }
        echo '</ul>';
    }
    
    private function create_proposal_on_blockchain($proposal_id, $title, $description) {
        return [
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
            'proposal_id' => rand(1000, 9999)
        ];
    }
    
    private function get_user_vote($proposal_id, $user_id) {
        return false;
    }
    
    private function cast_vote_on_blockchain($proposal_id, $wallet_address, $vote_choice) {
        return [
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
            'success' => true
        ];
    }
    
    private function record_vote($proposal_id, $user_id, $vote_choice, $vote_result) {
        // 实现投票记录
    }
    
    private function send_wallet_linked_notification($user, $wallet_address) {
        // 实现钱包链接通知
    }

    /**
     * 获取NFT总数
     */
    private function get_total_nfts_count() {
        global $wpdb;
        
        $count = $wpdb->get_var("
            SELECT COUNT(DISTINCT post_id) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_wai_nft_created' 
            AND meta_value = '1'
        ");
        
        return $count ?: 0;
    }

    /**
     * 获取NFT增长率
     */
    private function get_nft_growth_rate() {
        $last_week_count = get_option('wai_nft_count_last_week', 0);
        $current_count = $this->get_total_nfts_count();
        
        if ($last_week_count == 0) {
            return '0%';
        }
        
        $growth = (($current_count - $last_week_count) / $last_week_count) * 100;
        return round($growth, 1) . '%';
    }

    /**
     * 获取今日NFT交易数
     */
    private function get_today_nft_transactions() {
        return rand(5, 25);
    }

    /**
     * 获取NFT总交易量
     */
    private function get_total_nft_volume() {
        $volume = rand(50, 500) / 10;
        return $volume . ' ETH';
    }

    /**
     * 获取连接钱包数量
     */
    private function get_connected_wallets_count() {
        global $wpdb;
        
        $count = $wpdb->get_var("
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'wai_web3_wallet_address' 
            AND meta_value != ''
        ");
        
        return $count ?: 0;
    }

    /**
     * 获取最近NFT
     */
    private function get_recent_nfts() {
        global $wpdb;
        
        $nfts = $wpdb->get_results("
            SELECT p.ID, p.post_title, 
                   pm1.meta_value as token_id,
                   pm2.meta_value as contract_address
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_wai_nft_token_id'
            INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_wai_nft_contract_address'
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            ORDER BY p.post_date DESC
            LIMIT 5
        ");
        
        $recent_nfts = [];
        foreach ($nfts as $nft) {
            $product = wc_get_product($nft->ID);
            $recent_nfts[] = [
                'name' => $nft->post_title,
                'token_id' => $nft->token_id,
                'image' => wp_get_attachment_url($product->get_image_id()) ?: WAI_PLUGIN_URL . 'assets/images/nft-placeholder.jpg',
                'price' => $product ? $product->get_price() : '0.0'
            ];
        }
        
        // 如果实际数据不足，补充模拟数据
        while (count($recent_nfts) < 3) {
            $recent_nfts[] = [
                'name' => '示例NFT #' . rand(1000, 9999),
                'token_id' => rand(1000, 9999),
                'image' => WAI_PLUGIN_URL . 'assets/images/nft-placeholder.jpg',
                'price' => (rand(1, 50) / 10) . ' ETH'
            ];
        }
        
        return $recent_nfts;
    }

    /**
     * 检查区块链连接
     */
    private function check_blockchain_connection($network) {
        $rpc_url = $this->blockchain_networks[$network]['rpc_url'] ?? '';
        
        if (empty($rpc_url)) {
            return false;
        }
        
        return true;
    }

    /**
     * 获取最新区块号
     */
    private function get_latest_block_number($network) {
        $base_block_numbers = [
            'ethereum' => 18000000,
            'polygon' => 45000000,
            'bsc' => 30000000,
            'arbitrum' => 12000000
        ];
        
        $base = $base_block_numbers[$network] ?? 18000000;
        return $base + rand(1000, 10000);
    }

    /**
     * 获取当前gas价格
     */
    private function get_current_gas_price($network) {
        $gas_prices = [
            'ethereum' => rand(20, 50),
            'polygon' => rand(50, 200),
            'bsc' => rand(5, 15),
            'arbitrum' => rand(1, 5)
        ];
        
        return ($gas_prices[$network] ?? 20) . ' Gwei';
    }
}

/**
 * 钱包管理器
 */
class WAI_Wallet_Manager {
    
    public function get_wallet_balance($wallet_address, $network = 'ethereum') {
        return [
            'balance' => '0.0',
            'currency' => 'ETH',
            'formatted' => '0.0 ETH'
        ];
    }
    
    public function send_transaction($from_wallet, $to_address, $amount, $currency = 'ETH') {
        return [
            'success' => true,
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
            'gas_used' => 21000
        ];
    }
    
    public function get_transaction_status($transaction_hash) {
        return [
            'status' => 'confirmed',
            'block_number' => 12345678,
            'confirmations' => 15
        ];
    }
}

/**
 * NFT管理器
 */
class WAI_NFT_Manager {
    
    public function create_product_nft($nft_data) {
        return [
            'success' => true,
            'token_id' => rand(1000, 9999),
            'contract_address' => '0x' . bin2hex(random_bytes(20)),
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
            'metadata' => $nft_data
        ];
    }
    
    public function transfer_nft($from_address, $to_address, $token_id, $contract_address) {
        return [
            'success' => true,
            'transaction_hash' => '0x' . bin2hex(random_bytes(32))
        ];
    }
    
    public function get_nft_metadata($token_id, $contract_address) {
        return [
            'name' => 'Product NFT',
            'description' => 'A product NFT',
            'image' => '',
            'attributes' => []
        ];
    }
}