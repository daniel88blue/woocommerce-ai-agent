<?php
/**
 * AI电商智能体 - 安装向导界面
 */
?>
<div class="wrap ai-ecommerce-agent-wizard">
    <div class="wizard-header">
        <h1>🎯 AI电商智能体 - 安装向导</h1>
        <p><?php _e('只需5分钟，配置您的自进化AI电商助手', 'ai-ecommerce-agent'); ?></p>
    </div>
    
    <div class="wizard-progress">
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo $this->get_progress_percentage(); ?>%"></div>
        </div>
        <div class="progress-text">
            <?php printf(__('进度: %d%%', 'ai-ecommerce-agent'), $this->get_progress_percentage()); ?>
        </div>
    </div>
    
    <div class="wizard-container">
        <div class="wizard-sidebar">
            <ul class="wizard-steps">
                <?php foreach ($this->steps as $step_key => $step): ?>
                    <li class="step-item step-<?php echo $step_key; ?> <?php echo $this->get_step_status($step_key); ?>">
                        <span class="step-icon"><?php echo $step['icon']; ?></span>
                        <span class="step-name"><?php echo $step['name']; ?></span>
                        <span class="step-description"><?php echo $step['description']; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="wizard-help">
                <h3>💡 需要帮助？</h3>
                <p>查看 <a href="https://github.com/your-username/ai-ecommerce-agent" target="_blank">详细文档</a></p>
                <p>或 <a href="<?php echo admin_url('admin.php?page=ai-ecommerce-agent-wizard&skip_wizard=1'); ?>">跳过向导，手动配置</a></p>
            </div>
        </div>
        
        <div class="wizard-content">
            <form method="post" class="wizard-form" id="wizard-form">
                <?php wp_nonce_field('ai_agent_wizard_nonce'); ?>
                
                <div class="step-content step-<?php echo $this->current_step; ?>">
                    <?php $this->display_step_content($this->current_step); ?>
                </div>
                
                <div class="wizard-actions">
                    <?php $this->display_step_actions($this->current_step); ?>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
/**
 * 显示步骤内容
 */
public function display_step_content($step) {
    switch ($step) {
        case 'welcome':
            $this->display_welcome_step();
            break;
        case 'requirements':
            $this->display_requirements_step();
            break;
        case 'woocommerce':
            $this->display_woocommerce_step();
            break;
        case 'matomo':
            $this->display_matomo_step();
            break;
        case 'ai_engine':
            $this->display_ai_engine_step();
            break;
        case 'replit':
            $this->display_replit_step();
            break;
        case 'github':
            $this->display_github_step();
            break;
        case 'complete':
            $this->display_complete_step();
            break;
    }
}

/**
 * 显示步骤操作按钮
 */
public function display_step_actions($step) {
    $step_keys = array_keys($this->steps);
    $current_index = array_search($step, $step_keys);
    ?>
    
    <div class="action-buttons">
        <?php if ($current_index > 0): ?>
            <button type="submit" name="wizard_action" value="prev_step" class="button button-secondary">
                ← <?php _e('上一步', 'ai-ecommerce-agent'); ?>
            </button>
        <?php endif; ?>
        
        <?php if ($step === 'complete'): ?>
            <button type="submit" name="wizard_action" value="complete" class="button button-primary button-large">
                🎉 <?php _e('开始使用AI智能体', 'ai-ecommerce-agent'); ?>
            </button>
        <?php else: ?>
            <button type="submit" name="wizard_action" value="next_step" class="button button-primary">
                <?php _e('下一步', 'ai-ecommerce-agent'); ?> →
            </button>
        <?php endif; ?>
    </div>
    
    <?php
}

/**
 * 欢迎步骤
 */
private function display_welcome_step() {
    ?>
    <div class="welcome-step">
        <div class="welcome-hero">
            <h2>🚀 欢迎使用AI电商智能体</h2>
            <p class="welcome-description">
                您的全自动电商优化助手，集成AI决策引擎、数据分析、自动化营销于一体。
            </p>
        </div>
        
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <h3>AI决策引擎</h3>
                <p>基于机器学习的自动定价、库存优化和营销决策</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>数据分析</h3>
                <p>集成Matomo深度分析用户行为和转化路径</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>实时优化</h3>
                <p>基于Replit的云端计算，实现实时决策和优化</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>版本控制</h3>
                <p>通过GitHub管理配置和模型版本，实现自进化</p>
            </div>
        </div>
        
        <div class="setup-time">
            <div class="time-estimate">
                <strong>预计设置时间:</strong> 5-10分钟
            </div>
            <div class="requirements-note">
                <strong>需要准备:</strong> WooCommerce商店、Matomo分析、Replit账户、GitHub账户
            </div>
        </div>
    </div>
    <?php
}

/**
 * 系统要求步骤
 */
private function display_requirements_step() {
    $requirements = $this->check_requirements();
    $all_passed = true;
    
    foreach ($requirements as $req) {
        if (!$req['passed']) {
            $all_passed = false;
            break;
        }
    }
    ?>
    
    <div class="requirements-step">
        <h2>🔍 系统环境检查</h2>
        <p>确保您的服务器环境满足AI智能体的运行要求</p>
        
        <div class="requirements-list">
            <?php foreach ($requirements as $key => $req): ?>
                <div class="requirement-item <?php echo $req['passed'] ? 'passed' : 'failed'; ?>">
                    <span class="requirement-status">
                        <?php echo $req['passed'] ? '✅' : '❌'; ?>
                    </span>
                    <span class="requirement-name"><?php echo $req['name']; ?></span>
                    <span class="requirement-details">
                        要求: <?php echo $req['required']; ?> | 
                        当前: <?php echo $req['current']; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!$all_passed): ?>
            <div class="requirements-warning">
                <h3>⚠️ 需要解决的问题</h3>
                <p>请先解决上述问题，然后再继续安装。</p>
                <ul>
                    <?php foreach ($requirements as $key => $req): ?>
                        <?php if (!$req['passed']): ?>
                            <li>
                                <strong><?php echo $req['name']; ?>:</strong> 
                                需要 <?php echo $req['required']; ?>，当前为 <?php echo $req['current']; ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="requirements-success">
                <h3>✅ 所有要求都满足！</h3>
                <p>您的服务器环境完美支持AI电商智能体的运行。</p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * WooCommerce配置步骤
 */
private function display_woocommerce_step() {
    $woocommerce_active = class_exists('WooCommerce');
    $connected = get_option('ai_agent_woocommerce_connected', false);
    ?>
    
    <div class="woocommerce-step">
        <h2>🛒 WooCommerce配置</h2>
        <p>连接您的电商商店，让AI智能体了解您的产品和销售数据</p>
        
        <div class="connection-status <?php echo $woocommerce_active ? 'connected' : 'disconnected'; ?>">
            <div class="status-indicator"></div>
            <div class="status-text">
                <?php if ($woocommerce_active): ?>
                    <strong>✅ WooCommerce已检测到</strong>
                    <p>版本: <?php echo WC()->version; ?> | 产品数量: <?php echo wp_count_posts('product')->publish; ?></p>
                <?php else: ?>
                    <strong>❌ WooCommerce未安装</strong>
                    <p>请先安装并激活WooCommerce插件</p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($woocommerce_active): ?>
            <div class="configuration-options">
                <h3>数据访问权限</h3>
                
                <div class="option-group">
                    <label class="option-checkbox">
                        <input type="checkbox" name="woocommerce_status" value="1" <?php checked($connected); ?>>
                        <span class="checkmark"></span>
                        <span class="option-label">
                            <strong>允许AI智能体访问WooCommerce数据</strong>
                            <small>包括产品、订单、客户信息和销售数据</small>
                        </span>
                    </label>
                </div>
                
                <div class="permissions-list">
                    <h4>AI智能体将能够：</h4>
                    <ul>
                        <li>📈 分析销售趋势和产品表现</li>
                        <li>💰 优化定价和促销策略</li>
                        <li>📦 监控库存水平和预测需求</li>
                        <li>👥 分析客户行为和价值</li>
                        <li>🎯 个性化产品推荐</li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <div class="installation-guide">
                <h3>如何安装WooCommerce</h3>
                <ol>
                    <li>进入 <strong>插件 → 安装插件</strong></li>
                    <li>搜索 <strong>"WooCommerce"</strong></li>
                    <li>安装并激活插件</li>
                    <li>运行初始设置向导</li>
                    <li>返回此页面继续配置</li>
                </ol>
                
                <div class="action-links">
                    <a href="<?php echo admin_url('plugin-install.php?s=woocommerce&tab=search&type=term'); ?>" 
                       class="button button-primary" target="_blank">
                        安装WooCommerce
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// 由于代码长度限制，其他步骤的显示方法将在下一个回复中继续...
?>
