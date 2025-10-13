<?php
/**
 * AI电商智能体 - 安装向导系统
 * 处理插件的初始安装和配置
 */

if (!defined('ABSPATH')) {
    exit;
}

class WAI_Install_Wizard {
    
    private $steps = array();
    private $current_step = 1;
    private $total_steps = 8;
    private $config_data = array();
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_wizard_page'));
        add_action('admin_init', array($this, 'handle_wizard_actions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_wizard_scripts'));
        
        $this->setup_steps();
        $this->load_saved_config();
    }
    
    private function setup_steps() {
        $this->steps = array(
            1 => array(
                'title' => '欢迎使用',
                'description' => '开始设置您的AI电商智能体',
                'handler' => 'step_welcome',
                'button_text' => '开始配置'
            ),
            2 => array(
                'title' => '系统检查',
                'description' => '验证系统环境和依赖',
                'handler' => 'step_system_check',
                'button_text' => '继续'
            ),
            3 => array(
                'title' => 'WooCommerce配置',
                'description' => '连接您的电商店铺',
                'handler' => 'step_woocommerce',
                'button_text' => '保存并继续'
            ),
            4 => array(
                'title' => '数据分析服务',
                'description' => '配置网站分析和数据跟踪',
                'handler' => 'step_analytics',
                'button_text' => '保存并继续'
            ),
            5 => array(
                'title' => 'AI服务配置',
                'description' => '设置AI决策引擎',
                'handler' => 'step_ai_services',
                'button_text' => '保存并继续'
            ),
            6 => array(
                'title' => 'Web3功能设置',
                'description' => '配置区块链和元宇宙集成（可选）',
                'handler' => 'step_web3_setup',
                'button_text' => '保存并继续'
            ),
            7 => array(
                'title' => '自动化规则',
                'description' => '设置自动化运营规则',
                'handler' => 'step_automation_rules',
                'button_text' => '保存并继续'
            ),
            8 => array(
                'title' => '完成设置',
                'description' => '启动智能体学习过程',
                'handler' => 'step_finalize',
                'button_text' => '完成安装'
            )
        );
    }
    
    public function add_wizard_page() {
        add_submenu_page(
            null,
            'AI电商智能体 - 安装向导',
            '安装向导',
            'manage_options',
            'wai-install-wizard',
            array($this, 'render_wizard_page')
        );
    }
    
    public function render_wizard_page() {
        if (!current_user_can('manage_options')) {
            wp_die('您没有权限访问此页面。');
        }
        
        $this->current_step = isset($_GET['step']) ? absint($_GET['step']) : 1;
        if ($this->current_step < 1 || $this->current_step > $this->total_steps) {
            $this->current_step = 1;
        }
        
        $current_step_data = $this->steps[$this->current_step];
        ?>
        <div class="wrap wai-install-wizard">
            <div class="wai-wizard-header">
                <div class="wai-wizard-branding">
                    <h1>🎯 AI电商智能体 - 安装向导</h1>
                    <p>全自动电商运营系统配置</p>
                </div>
                
                <div class="wai-progress-container">
                    <div class="wai-progress-bar">
                        <div class="wai-progress-fill" style="width: <?php echo (($this->current_step - 1) / ($this->total_steps - 1)) * 100; ?>%"></div>
                    </div>
                    <div class="wai-progress-steps">
                        <?php foreach ($this->steps as $step_num => $step): ?>
                            <div class="wai-progress-step <?php echo $step_num == $this->current_step ? 'active' : ($step_num < $this->current_step ? 'completed' : ''); ?>">
                                <span class="wai-step-number"><?php echo $step_num; ?></span>
                                <span class="wai-step-title"><?php echo $step['title']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="wai-wizard-content">
                <form method="post" id="wai-install-form" class="wai-step-form">
                    <?php wp_nonce_field('wai_install_wizard_nonce', 'wai_wizard_nonce'); ?>
                    <input type="hidden" name="wai_current_step" value="<?php echo $this->current_step; ?>">
                    <input type="hidden" name="wai_action" value="process_step">
                    
                    <div class="wai-step-header">
                        <h2><?php echo $current_step_data['title']; ?></h2>
                        <p class="wai-step-description"><?php echo $current_step_data['description']; ?></p>
                    </div>
                    
                    <div class="wai-step-body">
                        <?php $this->render_step_content($this->current_step); ?>
                    </div>
                    
                    <div class="wai-step-footer">
                        <div class="wai-wizard-actions">
                            <?php if ($this->current_step > 1): ?>
                                <a href="<?php echo $this->get_step_url($this->current_step - 1); ?>" class="button button-secondary wai-prev-button">
                                    ← 上一步
                                </a>
                            <?php endif; ?>
                            
                            <button type="submit" name="wai_submit" class="button button-primary wai-next-button">
                                <?php echo $current_step_data['button_text']; ?> →
                            </button>
                        </div>
                        
                        <?php if ($this->current_step < $this->total_steps): ?>
                            <div class="wai-skip-container">
                                <a href="<?php echo $this->get_step_url($this->current_step + 1); ?>" class="wai-skip-link">跳过此步骤</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="wai-wizard-sidebar">
                <div class="wai-sidebar-card">
                    <h3>需要帮助？</h3>
                    <p>如果您在配置过程中遇到问题，请参考我们的文档或联系技术支持。</p>
                    <ul>
                        <li><a href="https://docs.example.com" target="_blank">📚 查看文档</a></li>
                        <li><a href="https://support.example.com" target="_blank">💬 联系支持</a></li>
                    </ul>
                </div>
                
                <div class="wai-sidebar-card">
                    <h3>当前配置</h3>
                    <div class="wai-config-summary">
                        <?php $this->render_config_summary(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_step_content($step) {
        $method_name = $this->steps[$step]['handler'];
        if (method_exists($this, $method_name)) {
            $this->$method_name();
        } else {
            echo '<div class="wai-error-message">步骤处理函数不存在。</div>';
        }
    }
    
    private function step_welcome() {
        ?>
        <div class="wai-welcome-content">
            <div class="wai-welcome-hero">
                <div class="wai-welcome-text">
                    <h3>欢迎使用AI电商智能体！</h3>
                    <p>这个智能系统将自动优化您的电商业务，实现全自动运营：</p>
                    
                    <div class="wai-features-grid">
                        <div class="wai-feature-item">
                            <span class="wai-feature-icon">🤖</span>
                            <h4>智能定价和促销</h4>
                            <p>基于AI算法自动调整价格和促销策略</p>
                        </div>
                        <div class="wai-feature-item">
                            <span class="wai-feature-icon">📊</span>
                            <h4>自动库存管理</h4>
                            <p>智能预测需求，自动补货和库存优化</p>
                        </div>
                        <div class="wai-feature-item">
                            <span class="wai-feature-icon">🎯</span>
                            <h4>个性化营销</h4>
                            <p>基于用户行为的精准营销自动化</p>
                        </div>
                        <div class="wai-feature-item">
                            <span class="wai-feature-icon">📈</span>
                            <h4>数据驱动决策</h4>
                            <p>实时数据分析，智能业务决策</p>
                        </div>
                        <div class="wai-feature-item">
                            <span class="wai-feature-icon">🔄</span>
                            <h4>自我学习和优化</h4>
                            <p>持续学习改进，不断提升运营效果</p>
                        </div>
                        <div class="wai-feature-item">
                            <span class="wai-feature-icon">🔗</span>
                            <h4>Web3集成</h4>
                            <p>区块链、NFT、元宇宙等前沿技术集成</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wai-system-requirements">
                <h4>系统要求检查</h4>
                <div class="wai-requirements-list">
                    <?php
                    $requirements = $this->check_system_requirements();
                    foreach ($requirements as $req): 
                    ?>
                        <div class="wai-requirement-item <?php echo $req['status']; ?>">
                            <span class="wai-req-icon">
                                <?php echo $req['status'] === 'success' ? '✅' : '❌'; ?>
                            </span>
                            <span class="wai-req-name"><?php echo $req['name']; ?></span>
                            <span class="wai-req-value"><?php echo $req['value']; ?></span>
                            <?php if ($req['status'] === 'error' && !empty($req['fix'])): ?>
                                <span class="wai-req-fix"><?php echo $req['fix']; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function step_system_check() {
        $system_checks = $this->perform_system_checks();
        ?>
        <div class="wai-system-checks">
            <div class="wai-check-description">
                <p>我们正在检查您的服务器环境，确保系统能够正常运行。</p>
            </div>
            
            <div class="wai-checks-grid">
                <?php foreach ($system_checks as $check): ?>
                <div class="wai-check-card <?php echo $check['status']; ?>">
                    <div class="wai-check-header">
                        <span class="wai-check-icon">
                            <?php 
                            switch ($check['status']) {
                                case 'success': echo '✅'; break;
                                case 'warning': echo '⚠️'; break;
                                case 'error': echo '❌'; break;
                                default: echo '🔍';
                            }
                            ?>
                        </span>
                        <h4><?php echo $check['name']; ?></h4>
                    </div>
                    <div class="wai-check-body">
                        <p class="wai-check-message"><?php echo $check['message']; ?></p>
                        <?php if (!empty($check['details'])): ?>
                            <div class="wai-check-details">
                                <strong>详情:</strong> <?php echo $check['details']; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($check['recommendation'])): ?>
                            <div class="wai-check-recommendation">
                                <strong>建议:</strong> <?php echo $check['recommendation']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($this->has_critical_errors($system_checks)): ?>
            <div class="wai-critical-error">
                <div class="wai-error-alert">
                    <h4>❌ 存在严重错误</h4>
                    <p>请解决上述问题后再继续安装。</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    private function step_woocommerce() {
        $wc_status = $this->check_woocommerce_status();
        ?>
        <div class="wai-woocommerce-setup">
            <div class="wai-section-description">
                <p>配置WooCommerce集成，让AI智能体能够管理您的电商业务。</p>
            </div>
            
            <div class="wai-wc-status <?php echo $wc_status['status']; ?>">
                <div class="wai-status-header">
                    <h4>WooCommerce状态</h4>
                    <span class="wai-status-badge"><?php echo $wc_status['status_text']; ?></span>
                </div>
                <div class="wai-status-details">
                    <?php if ($wc_status['status'] === 'success'): ?>
                        <p>✅ WooCommerce已安装并激活</p>
                        <ul>
                            <li>版本: <?php echo $wc_status['version']; ?></li>
                            <li>产品数量: <?php echo $wc_status['product_count']; ?></li>
                            <li>订单数量: <?php echo $wc_status['order_count']; ?></li>
                        </ul>
                    <?php else: ?>
                        <p>❌ 需要安装和激活WooCommerce插件</p>
                        <div class="wai-action-buttons">
                            <?php if ($wc_status['can_install']): ?>
                                <button type="button" class="button button-primary" onclick="waiInstallWooCommerce()">
                                    安装WooCommerce
                                </button>
                            <?php else: ?>
                                <a href="<?php echo admin_url('plugin-install.php?s=woocommerce&tab=search&type=term'); ?>" class="button button-primary">
                                    手动安装WooCommerce
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="wai-wc-settings">
                <h4>店铺配置</h4>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wai_store_currency">店铺货币</label>
                        </th>
                        <td>
                            <select name="wai_store_currency" id="wai_store_currency" class="regular-text">
                                <option value="CNY" <?php selected($this->get_config('store_currency'), 'CNY'); ?>>人民币 (CNY)</option>
                                <option value="USD" <?php selected($this->get_config('store_currency'), 'USD'); ?>>美元 (USD)</option>
                                <option value="EUR" <?php selected($this->get_config('store_currency'), 'EUR'); ?>>欧元 (EUR)</option>
                                <option value="GBP" <?php selected($this->get_config('store_currency'), 'GBP'); ?>>英镑 (GBP)</option>
                                <option value="JPY" <?php selected($this->get_config('store_currency'), 'JPY'); ?>>日元 (JPY)</option>
                            </select>
                            <p class="description">选择您店铺的主要交易货币</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wai_auto_sync">自动数据同步</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="wai_auto_sync" id="wai_auto_sync" value="1" <?php checked($this->get_config('auto_sync', 1)); ?>>
                                启用实时数据同步
                            </label>
                            <p class="description">自动同步产品、订单和客户数据</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wai_sync_frequency">同步频率</label>
                        </th>
                        <td>
                            <select name="wai_sync_frequency" id="wai_sync_frequency" class="regular-text">
                                <option value="realtime" <?php selected($this->get_config('sync_frequency'), 'realtime'); ?>>实时同步</option>
                                <option value="hourly" <?php selected($this->get_config('sync_frequency'), 'hourly'); ?>>每小时</option>
                                <option value="twicedaily" <?php selected($this->get_config('sync_frequency'), 'twicedaily'); ?>>每12小时</option>
                                <option value="daily" <?php selected($this->get_config('sync_frequency'), 'daily'); ?>>每天</option>
                            </select>
                            <p class="description">选择数据同步的频率</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <script>
        function waiInstallWooCommerce() {
            // 这里应该实现WooCommerce的自动安装逻辑
            alert('WooCommerce安装功能需要进一步开发');
        }
        </script>
        <?php
    }
    
    private function step_analytics() {
        ?>
        <div class="wai-analytics-setup">
            <div class="wai-section-description">
                <p>配置数据分析服务，让AI能够理解用户行为并做出智能决策。</p>
            </div>
            
            <div class="wai-analytics-services">
                <div class="wai-service-card">
                    <h4>📊 Conversific 集成</h4>
                    <p>高级电商分析平台，提供深入的业务洞察</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wai_conversific_enabled">启用Conversific</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_conversific_enabled" id="wai_conversific_enabled" value="1" <?php checked($this->get_config('conversific_enabled')); ?>>
                                    启用Conversific集成
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_conversific_api_key">API密钥</label>
                            </th>
                            <td>
                                <input type="password" name="wai_conversific_api_key" id="wai_conversific_api_key" 
                                       value="<?php echo esc_attr($this->get_config('conversific_api_key')); ?>" 
                                       class="regular-text" placeholder="输入您的Conversific API密钥">
                                <p class="description">
                                    <a href="https://conversific.com" target="_blank">获取API密钥</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="wai-service-card">
                    <h4>📧 Klaviyo 集成</h4>
                    <p>邮件营销和客户互动平台</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wai_klaviyo_enabled">启用Klaviyo</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_klaviyo_enabled" id="wai_klaviyo_enabled" value="1" <?php checked($this->get_config('klaviyo_enabled')); ?>>
                                    启用Klaviyo集成
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_klaviyo_public_key">Public Key</label>
                            </th>
                            <td>
                                <input type="text" name="wai_klaviyo_public_key" id="wai_klaviyo_public_key" 
                                       value="<?php echo esc_attr($this->get_config('klaviyo_public_key')); ?>" 
                                       class="regular-text" placeholder="输入您的Klaviyo Public Key">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_klaviyo_private_key">Private Key</label>
                            </th>
                            <td>
                                <input type="password" name="wai_klaviyo_private_key" id="wai_klaviyo_private_key" 
                                       value="<?php echo esc_attr($this->get_config('klaviyo_private_key')); ?>" 
                                       class="regular-text" placeholder="输入您的Klaviyo Private Key">
                                <p class="description">
                                    <a href="https://www.klaviyo.com" target="_blank">获取API密钥</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="wai-service-card">
                    <h4>🔍 内置分析</h4>
                    <p>使用WordPress内置数据分析功能</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wai_builtin_analytics">启用内置分析</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_builtin_analytics" id="wai_builtin_analytics" value="1" <?php checked($this->get_config('builtin_analytics', 1)); ?>>
                                    启用内置用户行为分析
                                </label>
                                <p class="description">收集用户浏览、点击和购买行为数据</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_track_user_sessions">用户会话跟踪</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_track_user_sessions" id="wai_track_user_sessions" value="1" <?php checked($this->get_config('track_user_sessions', 1)); ?>>
                                    跟踪用户会话和行为
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="wai-test-connections">
                <h4>测试连接</h4>
                <p>配置完成后，测试与各服务的连接状态：</p>
                <div class="wai-test-buttons">
                    <button type="button" class="button button-secondary" onclick="waiTestConversific()">
                        测试Conversific连接
                    </button>
                    <button type="button" class="button button-secondary" onclick="waiTestKlaviyo()">
                        测试Klaviyo连接
                    </button>
                </div>
                <div id="wai-test-results"></div>
            </div>
        </div>
        
        <script>
        function waiTestConversific() {
            // 测试Conversific连接
            document.getElementById('wai-test-results').innerHTML = '<div class="notice notice-info"><p>测试Conversific连接...</p></div>';
        }
        
        function waiTestKlaviyo() {
            // 测试Klaviyo连接
            document.getElementById('wai-test-results').innerHTML = '<div class="notice notice-info"><p>测试Klaviyo连接...</p></div>';
        }
        </script>
        <?php
    }
    
    private function step_ai_services() {
        ?>
        <div class="wai-ai-services-setup">
            <div class="wai-section-description">
                <p>配置AI服务，让系统能够进行智能决策和自动化运营。</p>
            </div>
            
            <div class="wai-ai-providers">
                <div class="wai-provider-card">
                    <h4>🤖 OpenAI 集成</h4>
                    <p>使用GPT模型进行内容生成和智能分析</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wai_openai_enabled">启用OpenAI</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_openai_enabled" id="wai_openai_enabled" value="1" <?php checked($this->get_config('openai_enabled')); ?>>
                                    启用OpenAI集成
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_openai_api_key">API密钥</label>
                            </th>
                            <td>
                                <input type="password" name="wai_openai_api_key" id="wai_openai_api_key" 
                                       value="<?php echo esc_attr($this->get_config('openai_api_key')); ?>" 
                                       class="regular-text" placeholder="输入您的OpenAI API密钥">
                                <p class="description">
                                    <a href="https://platform.openai.com/api-keys" target="_blank">获取API密钥</a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_openai_model">AI模型</label>
                            </th>
                            <td>
                                <select name="wai_openai_model" id="wai_openai_model" class="regular-text">
                                    <option value="gpt-4" <?php selected($this->get_config('openai_model'), 'gpt-4'); ?>>GPT-4 (推荐)</option>
                                    <option value="gpt-3.5-turbo" <?php selected($this->get_config('openai_model'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="wai-provider-card">
                    <h4>🧠 本地AI引擎</h4>
                    <p>使用内置的机器学习算法进行实时决策</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wai_local_ai_enabled">启用本地AI</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_local_ai_enabled" id="wai_local_ai_enabled" value="1" <?php checked($this->get_config('local_ai_enabled', 1)); ?>>
                                    启用本地AI引擎
                                </label>
                                <p class="description">实时定价、库存优化等基础决策</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_ai_decision_threshold">决策置信度</label>
                            </th>
                            <td>
                                <input type="range" name="wai_ai_decision_threshold" id="wai_ai_decision_threshold" 
                                       min="50" max="95" step="5" 
                                       value="<?php echo esc_attr($this->get_config('ai_decision_threshold', 75)); ?>">
                                <span id="wai_threshold_value"><?php echo $this->get_config('ai_decision_threshold', 75); ?>%</span>
                                <p class="description">AI决策的最小置信度阈值</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="wai-ai-capabilities">
                <h4>AI功能配置</h4>
                <div class="wai-capabilities-grid">
                    <div class="wai-capability-item">
                        <label>
                            <input type="checkbox" name="wai_auto_pricing" value="1" <?php checked($this->get_config('auto_pricing', 1)); ?>>
                            💰 自动定价优化
                        </label>
                        <p class="description">基于竞争和需求自动调整价格</p>
                    </div>
                    <div class="wai-capability-item">
                        <label>
                            <input type="checkbox" name="wai_auto_inventory" value="1" <?php checked($this->get_config('auto_inventory', 1)); ?>>
                            📦 智能库存管理
                        </label>
                        <p class="description">预测需求，自动补货建议</p>
                    </div>
                    <div class="wai-capability-item">
                        <label>
                            <input type="checkbox" name="wai_auto_marketing" value="1" <?php checked($this->get_config('auto_marketing', 1)); ?>>
                            🎯 自动化营销
                        </label>
                        <p class="description">个性化推荐和营销活动</p>
                    </div>
                    <div class="wai-capability-item">
                        <label>
                            <input type="checkbox" name="wai_content_generation" value="1" <?php checked($this->get_config('content_generation', 1)); ?>>
                            📝 智能内容生成
                        </label>
                        <p class="description">自动生成产品描述和营销内容</p>
                    </div>
                    <div class="wai-capability-item">
                        <label>
                            <input type="checkbox" name="wai_customer_insights" value="1" <?php checked($this->get_config('customer_insights', 1)); ?>>
                            👥 客户洞察分析
                        </label>
                        <p class="description">深度分析客户行为和偏好</p>
                    </div>
                    <div class="wai-capability-item">
                        <label>
                            <input type="checkbox" name="wai_ab_testing" value="1" <?php checked($this->get_config('ab_testing', 1)); ?>>
                            🧪 A/B测试优化
                        </label>
                        <p class="description">自动测试和优化策略</p>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        document.getElementById('wai_ai_decision_threshold').addEventListener('input', function() {
            document.getElementById('wai_threshold_value').textContent = this.value + '%';
        });
        </script>
        <?php
    }
    
    private function step_web3_setup() {
        ?>
        <div class="wai-web3-setup">
            <div class="wai-section-description">
                <p>配置Web3功能，集成区块链、NFT和元宇宙技术（可选功能）。</p>
            </div>
            
            <div class="wai-web3-option">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wai_web3_enabled">启用Web3功能</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="wai_web3_enabled" id="wai_web3_enabled" value="1" <?php checked($this->get_config('web3_enabled')); ?>>
                                启用区块链和Web3集成
                            </label>
                            <p class="description">开启后将支持NFT商品、去中心化身份验证等功能</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div id="wai-web3-details" style="<?php echo $this->get_config('web3_enabled') ? '' : 'display: none;'; ?>">
                <div class="wai-web3-services">
                    <div class="wai-service-card">
                        <h4>⛓️ 区块链网络</h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="wai_blockchain_network">主网络</label>
                                </th>
                                <td>
                                    <select name="wai_blockchain_network" id="wai_blockchain_network" class="regular-text">
                                        <option value="ethereum" <?php selected($this->get_config('blockchain_network'), 'ethereum'); ?>>Ethereum 主网</option>
                                        <option value="polygon" <?php selected($this->get_config('blockchain_network'), 'polygon'); ?>>Polygon 主网</option>
                                        <option value="bsc" <?php selected($this->get_config('blockchain_network'), 'bsc'); ?>>BSC 主网</option>
                                        <option value="arbitrum" <?php selected($this->get_config('blockchain_network'), 'arbitrum'); ?>>Arbitrum 主网</option>
                                        <option value="sepolia" <?php selected($this->get_config('blockchain_network'), 'sepolia'); ?>>Sepolia 测试网</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="wai_rpc_url">RPC URL</label>
                                </th>
                                <td>
                                    <input type="text" name="wai_rpc_url" id="wai_rpc_url" 
                                           value="<?php echo esc_attr($this->get_config('rpc_url')); ?>" 
                                           class="regular-text" placeholder="输入自定义RPC端点">
                                    <p class="description">留空使用默认公共节点</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="wai-service-card">
                        <h4>🖼️ NFT 功能</h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="wai_nft_enabled">启用NFT商品</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="wai_nft_enabled" id="wai_nft_enabled" value="1" <?php checked($this->get_config('nft_enabled')); ?>>
                                        支持商品上链为NFT
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="wai_nft_marketplace">NFT市场</label>
                                </th>
                                <td>
                                    <select name="wai_nft_marketplace" id="wai_nft_marketplace" class="regular-text">
                                        <option value="opensea" <?php selected($this->get_config('nft_marketplace'), 'opensea'); ?>>OpenSea</option>
                                        <option value="looksrare" <?php selected($this->get_config('nft_marketplace'), 'looksrare'); ?>>LooksRare</option>
                                        <option value="blur" <?php selected($this->get_config('nft_marketplace'), 'blur'); ?>>Blur</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="wai-service-card">
                        <h4>🌌 元宇宙集成</h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="wai_metaverse_enabled">启用元宇宙</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="wai_metaverse_enabled" id="wai_metaverse_enabled" value="1" <?php checked($this->get_config('metaverse_enabled')); ?>>
                                        集成元宇宙虚拟店铺
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="wai_metaverse_platform">元宇宙平台</label>
                                </th>
                                <td>
                                    <select name="wai_metaverse_platform" id="wai_metaverse_platform" class="regular-text">
                                        <option value="decentraland" <?php selected($this->get_config('metaverse_platform'), 'decentraland'); ?>>Decentraland</option>
                                        <option value="sandbox" <?php selected($this->get_config('metaverse_platform'), 'sandbox'); ?>>The Sandbox</option>
                                        <option value="cryptovoxels" <?php selected($this->get_config('metaverse_platform'), 'cryptovoxels'); ?>>Cryptovoxels</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        document.getElementById('wai_web3_enabled').addEventListener('change', function() {
            document.getElementById('wai-web3-details').style.display = this.checked ? 'block' : 'none';
        });
        </script>
        <?php
    }
    
    private function step_automation_rules() {
        ?>
        <div class="wai-automation-rules">
            <div class="wai-section-description">
                <p>设置自动化运营规则，让系统能够自主管理您的电商业务。</p>
            </div>
            
            <div class="wai-automation-categories">
                <div class="wai-category-card">
                    <h4>💰 定价自动化</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wai_auto_price_adjustment">自动调价</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_auto_price_adjustment" id="wai_auto_price_adjustment" value="1" <?php checked($this->get_config('auto_price_adjustment', 1)); ?>>
                                    启用自动价格调整
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_price_adjustment_range">调价幅度</label>
                            </th>
                            <td>
                                <input type="number" name="wai_price_adjustment_range" id="wai_price_adjustment_range" 
                                       min="1" max="50" value="<?php echo esc_attr($this->get_config('price_adjustment_range', 10)); ?>"> %
                                <p class="description">单次价格调整的最大幅度</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_competitor_tracking">竞争对手跟踪</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_competitor_tracking" id="wai_competitor_tracking" value="1" <?php checked($this->get_config('competitor_tracking', 1)); ?>>
                                    跟踪竞争对手价格
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="wai-category-card">
                    <h4>📦 库存自动化</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wai_auto_restock">自动补货</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_auto_restock" id="wai_auto_restock" value="1" <?php checked($this->get_config('auto_restock', 1)); ?>>
                                    启用自动补货建议
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_low_stock_threshold">低库存阈值</label>
                            </th>
                            <td>
                                <input type="number" name="wai_low_stock_threshold" id="wai_low_stock_threshold" 
                                       min="1" max="100" value="<?php echo esc_attr($this->get_config('low_stock_threshold', 10)); ?>"> 件
                                <p class="description">触发低库存警告的数量</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_demand_forecasting">需求预测</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_demand_forecasting" id="wai_demand_forecasting" value="1" <?php checked($this->get_config('demand_forecasting', 1)); ?>>
                                    启用AI需求预测
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="wai-category-card">
                    <h4>🎯 营销自动化</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wai_auto_campaigns">自动营销活动</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_auto_campaigns" id="wai_auto_campaigns" value="1" <?php checked($this->get_config('auto_campaigns', 1)); ?>>
                                    自动创建营销活动
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_personalized_recommendations">个性化推荐</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_personalized_recommendations" id="wai_personalized_recommendations" value="1" <?php checked($this->get_config('personalized_recommendations', 1)); ?>>
                                    启用个性化产品推荐
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_ab_testing_auto">自动A/B测试</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_ab_testing_auto" id="wai_ab_testing_auto" value="1" <?php checked($this->get_config('ab_testing_auto', 1)); ?>>
                                    自动运行A/B测试优化
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="wai-category-card">
                    <h4>🔄 运营自动化</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wai_auto_reporting">自动报告</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_auto_reporting" id="wai_auto_reporting" value="1" <?php checked($this->get_config('auto_reporting', 1)); ?>>
                                    自动生成运营报告
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_auto_optimization">自动优化</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_auto_optimization" id="wai_auto_optimization" value="1" <?php checked($this->get_config('auto_optimization', 1)); ?>>
                                    自动优化运营策略
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wai_self_healing">自我修复</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wai_self_healing" id="wai_self_healing" value="1" <?php checked($this->get_config('self_healing', 1)); ?>>
                                    自动检测和修复问题
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function step_finalize() {
        $summary = $this->get_config_summary();
        ?>
        <div class="wai-finalize-setup">
            <div class="wai-completion-check">
                <div class="wai-check-icon">✅</div>
                <h3>配置完成！</h3>
                <p>您的AI电商智能体已经配置完成，准备开始自动化运营。</p>
            </div>
            
            <div class="wai-config-summary-final">
                <h4>配置摘要</h4>
                <div class="wai-summary-grid">
                    <?php foreach ($summary as $category => $items): ?>
                    <div class="wai-summary-category">
                        <h5><?php echo $category; ?></h5>
                        <ul>
                            <?php foreach ($items as $item): ?>
                            <li class="<?php echo $item['status']; ?>">
                                <span class="wai-summary-icon">
                                    <?php echo $item['status'] === 'active' ? '✅' : '❌'; ?>
                                </span>
                                <?php echo $item['label']; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="wai-next-steps">
                <h4>接下来</h4>
                <div class="wai-steps-grid">
                    <div class="wai-step-item">
                        <div class="wai-step-number">1</div>
                        <div class="wai-step-content">
                            <h5>数据收集</h5>
                            <p>系统将开始收集历史数据，建立学习基础</p>
                        </div>
                    </div>
                    <div class="wai-step-item">
                        <div class="wai-step-number">2</div>
                        <div class="wai-step-content">
                            <h5>模型训练</h5>
                            <p>AI模型将基于您的业务数据进行训练</p>
                        </div>
                    </div>
                    <div class="wai-step-item">
                        <div class="wai-step-number">3</div>
                        <div class="wai-step-content">
                            <h5>自动化启动</h5>
                            <p>系统将开始自动化运营和优化</p>
                        </div>
                    </div>
                    <div class="wai-step-item">
                        <div class="wai-step-number">4</div>
                        <div class="wai-step-content">
                            <h5>持续优化</h5>
                            <p>系统将持续学习和改进运营策略</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wai-launch-actions">
                <div class="wai-action-buttons">
                    <button type="submit" name="wai_launch_system" class="button button-primary button-hero">
                        🚀 启动AI电商智能体
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=wai-dashboard'); ?>" class="button button-secondary">
                        进入控制面板
                    </a>
                </div>
                <p class="description">点击启动后，系统将开始自动化运营流程</p>
            </div>
        </div>
        <?php
    }
    
    private function check_system_requirements() {
        return array(
            array(
                'name' => 'PHP版本',
                'value' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'success' : 'error',
                'fix' => version_compare(PHP_VERSION, '7.4.0', '<') ? '请升级到PHP 7.4或更高版本' : ''
            ),
            array(
                'name' => 'WordPress版本',
                'value' => get_bloginfo('version'),
                'status' => version_compare(get_bloginfo('version'), '5.8', '>=') ? 'success' : 'warning',
                'fix' => version_compare(get_bloginfo('version'), '5.8', '<') ? '建议升级到WordPress 5.8或更高版本' : ''
            ),
            array(
                'name' => '内存限制',
                'value' => ini_get('memory_limit'),
                'status' => $this->convert_memory_to_bytes(ini_get('memory_limit')) >= 134217728 ? 'success' : 'warning',
                'fix' => $this->convert_memory_to_bytes(ini_get('memory_limit')) < 134217728 ? '建议设置内存限制为128M或更高' : ''
            ),
            array(
                'name' => 'MySQL版本',
                'value' => $this->get_mysql_version(),
                'status' => version_compare($this->get_mysql_version(), '5.6', '>=') ? 'success' : 'warning',
                'fix' => version_compare($this->get_mysql_version(), '5.6', '<') ? '建议升级到MySQL 5.6或更高版本' : ''
            ),
            array(
                'name' => 'cURL支持',
                'value' => function_exists('curl_version') ? '已安装' : '未安装',
                'status' => function_exists('curl_version') ? 'success' : 'error',
                'fix' => !function_exists('curl_version') ? '请安装cURL扩展' : ''
            ),
            array(
                'name' => 'JSON支持',
                'value' => function_exists('json_encode') ? '已安装' : '未安装',
                'status' => function_exists('json_encode') ? 'success' : 'error',
                'fix' => !function_exists('json_encode') ? '请安装JSON扩展' : ''
            )
        );
    }
    
    private function convert_memory_to_bytes($memory_limit) {
        $unit = strtoupper(substr($memory_limit, -1));
        $value = (int) $memory_limit;
        
        switch ($unit) {
            case 'G':
                return $value * 1024 * 1024 * 1024;
            case 'M':
                return $value * 1024 * 1024;
            case 'K':
                return $value * 1024;
            default:
                return $value;
        }
    }
    
    private function perform_system_checks() {
        $checks = array();
        
        // WooCommerce检查
        $wc_activated = class_exists('WooCommerce');
        $checks[] = array(
            'name' => 'WooCommerce',
            'message' => $wc_activated ? '已安装并激活' : '未安装或未激活',
            'status' => $wc_activated ? 'success' : 'error',
            'details' => $wc_activated ? '版本: ' . (defined('WC_VERSION') ? WC_VERSION : '未知') : '',
            'recommendation' => $wc_activated ? '' : '请安装并激活WooCommerce插件'
        );
        
        // 文件权限检查
        $upload_dir = wp_upload_dir();
        $writable = wp_is_writable($upload_dir['basedir']);
        $checks[] = array(
            'name' => '文件权限',
            'message' => $writable ? '上传目录可写' : '上传目录不可写',
            'status' => $writable ? 'success' : 'error',
            'details' => $upload_dir['basedir'],
            'recommendation' => $writable ? '' : '请设置上传目录为可写权限'
        );
        
        // 数据库表检查
        $tables_ok = $this->check_database_tables();
        $checks[] = array(
            'name' => '数据库表',
            'message' => $tables_ok ? '所有表正常' : '缺少必要的数据库表',
            'status' => $tables_ok ? 'success' : 'warning',
            'recommendation' => $tables_ok ? '' : '安装过程中将自动创建所需表'
        );
        
        // Cron系统检查
        $cron_ok = !defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON;
        $checks[] = array(
            'name' => '定时任务系统',
            'message' => $cron_ok ? 'WP-Cron可用' : 'WP-Cron被禁用',
            'status' => $cron_ok ? 'success' : 'warning',
            'recommendation' => $cron_ok ? '' : '建议启用WP-Cron或设置系统Cron'
        );
        
        return $checks;
    }
    
    private function check_woocommerce_status() {
        if (!class_exists('WooCommerce')) {
            return array(
                'status' => 'error',
                'status_text' => '未安装',
                'can_install' => current_user_can('install_plugins')
            );
        }
        
        $product_count = 0;
        $order_count = 0;
        
        try {
            $products = wc_get_products(array('limit' => 1, 'return' => 'ids'));
            $product_count = is_array($products) ? count($products) : 0;
            
            $orders = wc_get_orders(array('limit' => 1, 'return' => 'ids'));
            $order_count = is_array($orders) ? count($orders) : 0;
        } catch (Exception $e) {
            // 忽略错误，使用默认值
        }
        
        return array(
            'status' => 'success',
            'status_text' => '已激活',
            'version' => defined('WC_VERSION') ? WC_VERSION : '未知',
            'product_count' => $product_count,
            'order_count' => $order_count
        );
    }
    
    private function get_config($key, $default = '') {
        return isset($this->config_data[$key]) ? $this->config_data[$key] : $default;
    }
    
    private function load_saved_config() {
        $this->config_data = get_option('wai_install_config', array());
    }
    
    private function save_config() {
        update_option('wai_install_config', $this->config_data);
    }
    
    private function get_step_url($step) {
        return add_query_arg(array('page' => 'wai-install-wizard', 'step' => $step), admin_url('admin.php'));
    }
    
    private function render_config_summary() {
        $summary = $this->get_config_summary();
        foreach ($summary as $category => $items) {
            echo '<div class="wai-summary-category">';
            echo '<strong>' . $category . '</strong>';
            echo '<ul>';
            foreach (array_slice($items, 0, 3) as $item) {
                echo '<li>' . ($item['status'] === 'active' ? '✅' : '❌') . ' ' . $item['label'] . '</li>';
            }
            if (count($items) > 3) {
                echo '<li>... 还有 ' . (count($items) - 3) . ' 项</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }
    
    private function get_config_summary() {
        return array(
            '基础配置' => array(
                array('label' => 'WooCommerce集成', 'status' => $this->get_config('auto_sync') ? 'active' : 'inactive'),
                array('label' => '数据同步', 'status' => $this->get_config('auto_sync') ? 'active' : 'inactive'),
                array('label' => '货币设置', 'status' => 'active')
            ),
            'AI服务' => array(
                array('label' => 'OpenAI集成', 'status' => $this->get_config('openai_enabled') ? 'active' : 'inactive'),
                array('label' => '本地AI引擎', 'status' => $this->get_config('local_ai_enabled') ? 'active' : 'inactive'),
                array('label' => '自动定价', 'status' => $this->get_config('auto_pricing') ? 'active' : 'inactive')
            ),
            '数据分析' => array(
                array('label' => 'Conversific', 'status' => $this->get_config('conversific_enabled') ? 'active' : 'inactive'),
                array('label' => 'Klaviyo', 'status' => $this->get_config('klaviyo_enabled') ? 'active' : 'inactive'),
                array('label' => '内置分析', 'status' => $this->get_config('builtin_analytics') ? 'active' : 'inactive')
            ),
            'Web3功能' => array(
                array('label' => 'Web3集成', 'status' => $this->get_config('web3_enabled') ? 'active' : 'inactive'),
                array('label' => 'NFT功能', 'status' => $this->get_config('nft_enabled') ? 'active' : 'inactive'),
                array('label' => '元宇宙', 'status' => $this->get_config('metaverse_enabled') ? 'active' : 'inactive')
            )
        );
    }
    
    private function get_mysql_version() {
        global $wpdb;
        return $wpdb->db_version();
    }
    
    private function check_database_tables() {
        global $wpdb;
        $tables = array(
            $wpdb->prefix . 'wai_activity_logs',
            $wpdb->prefix . 'wai_ai_training_data'
        );
        
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                return false;
            }
        }
        return true;
    }
    
    private function has_critical_errors($checks) {
        foreach ($checks as $check) {
            if ($check['status'] === 'error') {
                return true;
            }
        }
        return false;
    }
    
    public function enqueue_wizard_scripts($hook) {
        if ($hook !== 'admin_page_wai-install-wizard') {
            return;
        }
        
        wp_enqueue_style(
            'wai-wizard-css',
            WAI_PLUGIN_URL . 'admin/css/wizard.css',
            array(),
            WAI_VERSION
        );
        
        wp_enqueue_script(
            'wai-wizard-js',
            WAI_PLUGIN_URL . 'admin/js/wizard.js',
            array('jquery'),
            WAI_VERSION,
            true
        );
    }
    
    public function handle_wizard_actions() {
        if (!isset($_POST['wai_action']) || $_POST['wai_action'] !== 'process_step') {
            return;
        }
        
        if (!wp_verify_nonce($_POST['wai_wizard_nonce'], 'wai_install_wizard_nonce')) {
            wp_die('安全验证失败，请重试。');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('您没有权限执行此操作。');
        }
        
        $current_step = isset($_POST['wai_current_step']) ? absint($_POST['wai_current_step']) : 1;
        
        // 处理表单数据
        $this->process_step_data($current_step);
        
        // 保存配置
        $this->save_config();
        
        // 如果是最后一步，完成安装
        if ($current_step === $this->total_steps && isset($_POST['wai_launch_system'])) {
            $this->complete_installation();
            wp_redirect(admin_url('admin.php?page=wai-dashboard'));
            exit;
        }
        
        // 重定向到下一步
        $next_step = $current_step + 1;
        if ($next_step <= $this->total_steps) {
            wp_redirect($this->get_step_url($next_step));
            exit;
        }
    }
    
    private function process_step_data($step) {
        switch ($step) {
            case 3: // WooCommerce配置
                $this->config_data['store_currency'] = sanitize_text_field($_POST['wai_store_currency'] ?? 'CNY');
                $this->config_data['auto_sync'] = isset($_POST['wai_auto_sync']) ? 1 : 0;
                $this->config_data['sync_frequency'] = sanitize_text_field($_POST['wai_sync_frequency'] ?? 'hourly');
                break;
                
            case 4: // 数据分析
                $this->config_data['conversific_enabled'] = isset($_POST['wai_conversific_enabled']) ? 1 : 0;
                $this->config_data['conversific_api_key'] = sanitize_text_field($_POST['wai_conversific_api_key'] ?? '');
                $this->config_data['klaviyo_enabled'] = isset($_POST['wai_klaviyo_enabled']) ? 1 : 0;
                $this->config_data['klaviyo_public_key'] = sanitize_text_field($_POST['wai_klaviyo_public_key'] ?? '');
                $this->config_data['klaviyo_private_key'] = sanitize_text_field($_POST['wai_klaviyo_private_key'] ?? '');
                $this->config_data['builtin_analytics'] = isset($_POST['wai_builtin_analytics']) ? 1 : 0;
                $this->config_data['track_user_sessions'] = isset($_POST['wai_track_user_sessions']) ? 1 : 0;
                break;
                
            case 5: // AI服务
                $this->config_data['openai_enabled'] = isset($_POST['wai_openai_enabled']) ? 1 : 0;
                $this->config_data['openai_api_key'] = sanitize_text_field($_POST['wai_openai_api_key'] ?? '');
                $this->config_data['openai_model'] = sanitize_text_field($_POST['wai_openai_model'] ?? 'gpt-3.5-turbo');
                $this->config_data['local_ai_enabled'] = isset($_POST['wai_local_ai_enabled']) ? 1 : 0;
                $this->config_data['ai_decision_threshold'] = intval($_POST['wai_ai_decision_threshold'] ?? 75);
                
                // AI功能
                $this->config_data['auto_pricing'] = isset($_POST['wai_auto_pricing']) ? 1 : 0;
                $this->config_data['auto_inventory'] = isset($_POST['wai_auto_inventory']) ? 1 : 0;
                $this->config_data['auto_marketing'] = isset($_POST['wai_auto_marketing']) ? 1 : 0;
                $this->config_data['content_generation'] = isset($_POST['wai_content_generation']) ? 1 : 0;
                $this->config_data['customer_insights'] = isset($_POST['wai_customer_insights']) ? 1 : 0;
                $this->config_data['ab_testing'] = isset($_POST['wai_ab_testing']) ? 1 : 0;
                break;
                
            case 6: // Web3设置
                $this->config_data['web3_enabled'] = isset($_POST['wai_web3_enabled']) ? 1 : 0;
                $this->config_data['blockchain_network'] = sanitize_text_field($_POST['wai_blockchain_network'] ?? 'ethereum');
                $this->config_data['rpc_url'] = sanitize_text_field($_POST['wai_rpc_url'] ?? '');
                $this->config_data['nft_enabled'] = isset($_POST['wai_nft_enabled']) ? 1 : 0;
                $this->config_data['nft_marketplace'] = sanitize_text_field($_POST['wai_nft_marketplace'] ?? 'opensea');
                $this->config_data['metaverse_enabled'] = isset($_POST['wai_metaverse_enabled']) ? 1 : 0;
                $this->config_data['metaverse_platform'] = sanitize_text_field($_POST['wai_metaverse_platform'] ?? 'decentraland');
                break;
                
            case 7: // 自动化规则
                $this->config_data['auto_price_adjustment'] = isset($_POST['wai_auto_price_adjustment']) ? 1 : 0;
                $this->config_data['price_adjustment_range'] = intval($_POST['wai_price_adjustment_range'] ?? 10);
                $this->config_data['competitor_tracking'] = isset($_POST['wai_competitor_tracking']) ? 1 : 0;
                $this->config_data['auto_restock'] = isset($_POST['wai_auto_restock']) ? 1 : 0;
                $this->config_data['low_stock_threshold'] = intval($_POST['wai_low_stock_threshold'] ?? 10);
                $this->config_data['demand_forecasting'] = isset($_POST['wai_demand_forecasting']) ? 1 : 0;
                $this->config_data['auto_campaigns'] = isset($_POST['wai_auto_campaigns']) ? 1 : 0;
                $this->config_data['personalized_recommendations'] = isset($_POST['wai_personalized_recommendations']) ? 1 : 0;
                $this->config_data['ab_testing_auto'] = isset($_POST['wai_ab_testing_auto']) ? 1 : 0;
                $this->config_data['auto_reporting'] = isset($_POST['wai_auto_reporting']) ? 1 : 0;
                $this->config_data['auto_optimization'] = isset($_POST['wai_auto_optimization']) ? 1 : 0;
                $this->config_data['self_healing'] = isset($_POST['wai_self_healing']) ? 1 : 0;
                break;
        }
    }
    
    private function complete_installation() {
        try {
            // 保存最终配置
            update_option('wai_plugin_configured', true);
            update_option('wai_installation_time', current_time('mysql'));
            update_option('wai_web3_enabled', $this->get_config('web3_enabled', false));
            
            // 设置默认选项
            $default_options = array(
                'wai_auto_pricing' => $this->get_config('auto_pricing', true),
                'wai_auto_inventory' => $this->get_config('auto_inventory', true),
                'wai_auto_marketing' => $this->get_config('auto_marketing', true),
                'wai_learning_enabled' => true,
                'wai_self_healing' => $this->get_config('self_healing', true)
            );
            
            foreach ($default_options as $key => $value) {
                if (get_option($key) === false) {
                    add_option($key, $value);
                }
            }
            
            // 创建必要的数据库表
            $this->create_required_tables();
            
            // 记录安装完成
            $this->log_installation_complete();
        } catch (Exception $e) {
            error_log('WAI Installation Complete Error: ' . $e->getMessage());
        }
    }
    
    private function create_required_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $tables = array(
            "{$wpdb->prefix}wai_decisions" => "
                CREATE TABLE {$wpdb->prefix}wai_decisions (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    decision_type varchar(100) NOT NULL,
                    decision_data longtext NOT NULL,
                    reasoning longtext,
                    result_data longtext,
                    status varchar(50) NOT NULL,
                    created_at datetime NOT NULL,
                    executed_at datetime,
                    PRIMARY KEY (id)
                ) $charset_collate;",
                
            "{$wpdb->prefix}wai_learning_data" => "
                CREATE TABLE {$wpdb->prefix}wai_learning_data (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    data_type varchar(100) NOT NULL,
                    input_data longtext NOT NULL,
                    output_data longtext,
                    performance_metrics longtext,
                    created_at datetime NOT NULL,
                    PRIMARY KEY (id)
                ) $charset_collate;"
        );
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($tables as $table_name => $sql) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                dbDelta($sql);
            }
        }
    }
    
    private function log_installation_complete() {
        // 记录安装日志
        $log_data = array(
            'type' => 'installation_complete',
            'message' => 'AI电商智能体安装完成',
            'config_summary' => $this->get_config_summary(),
            'timestamp' => current_time('mysql')
        );
        
        error_log('WAI Installation Complete: ' . json_encode($log_data));
    }
}