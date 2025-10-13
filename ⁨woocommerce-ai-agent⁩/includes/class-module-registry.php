<?php
/**
 * 模块注册器 - 动态管理插件功能模块
 */

if (!defined('ABSPATH')) {
    exit;
}

class WAI_Module_Registry {
    
    private static $instance = null;
    private $registered_modules = array();
    private $available_pages = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // 注册核心模块
        $this->register_core_modules();
        
        // 扫描并注册可用模块
        $this->scan_available_modules();
        
        // 注册管理页面
        add_action('admin_menu', array($this, 'register_admin_pages'), 20);
    }
    
    /**
     * 注册核心模块
     */
    private function register_core_modules() {
        $core_modules = array(
            'dashboard' => array(
                'name' => __('智能体仪表板', 'woocommerce-ai-agent'),
                'description' => __('整体业务监控和AI洞察', 'woocommerce-ai-agent'),
                'icon' => '📊',
                'category' => 'core',
                'page_slug' => 'wai-dashboard',
                'economic_impact' => __('全面监控可提升业务决策效率40%', 'woocommerce-ai-agent'),
                'setup_required' => false
            ),
            'web3_dashboard' => array(
                'name' => __('Web3仪表板', 'woocommerce-ai-agent'),
                'description' => __('区块链和NFT功能管理', 'woocommerce-ai-agent'),
                'icon' => '🔗',
                'category' => 'web3',
                'page_slug' => 'wai-web3-dashboard',
                'economic_impact' => __('NFT功能可创造额外30%数字资产收入', 'woocommerce-ai-agent'),
                'setup_required' => true,
                'requirement' => 'wai_web3_enabled'
            ),
            'swarm_management' => array(
                'name' => __('蜂群管理', 'woocommerce-ai-agent'),
                'description' => __('多店铺协同和智能网络', 'woocommerce-ai-agent'),
                'icon' => '🐝',
                'category' => 'ai',
                'page_slug' => 'wai-swarm-management',
                'economic_impact' => __('多店铺协同可提升整体销售额28%', 'woocommerce-ai-agent'),
                'setup_required' => true
            )
        );
        
        foreach ($core_modules as $module_id => $module_data) {
            $this->register_module($module_id, $module_data);
        }
    }
    
    /**
     * 扫描可用模块
     */
    private function scan_available_modules() {
        $module_directories = array(
            WAI_PLUGIN_DIR . 'includes/modules/',
            WAI_PLUGIN_DIR . 'admin/modules/'
        );
        
        foreach ($module_directories as $directory) {
            if (file_exists($directory)) {
                $this->scan_directory_for_modules($directory);
            }
        }
        
        // 扫描页面文件
        $this->scan_admin_pages();
    }
    
    /**
     * 扫描目录中的模块
     */
    private function scan_directory_for_modules($directory) {
        $files = scandir($directory);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $file_path = $directory . $file;
            $module_id = pathinfo($file, PATHINFO_FILENAME);
            
            if (is_file($file_path) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $this->register_module_from_file($module_id, $file_path);
            }
        }
    }
    
    /**
     * 扫描管理页面
     */
    private function scan_admin_pages() {
        $pages_dir = WAI_PLUGIN_DIR . 'admin/partials/';
        
        if (!file_exists($pages_dir)) {
            return;
        }
        
        $pages = scandir($pages_dir);
        $page_map = array(
            'dashboard' => 'wai-dashboard',
            'web3-dashboard' => 'wai-web3-dashboard',
            'swarm-management' => 'wai-swarm-management',
            'metaverse-stores' => 'wai-metaverse-stores',
            'dao-governance' => 'wai-dao-governance',
            'ai-dashboard' => 'wai-ai-dashboard',
            'cross-platform' => 'wai-cross-platform',
            'automation' => 'wai-automation',
            'system-architecture' => 'wai-system-architect',
            'settings' => 'wai-settings',
            'logs' => 'wai-logs',
            'install-wizard' => 'wai-install-wizard'
        );
        
        foreach ($pages as $page_file) {
            if (pathinfo($page_file, PATHINFO_EXTENSION) === 'php') {
                $page_slug = pathinfo($page_file, PATHINFO_FILENAME);
                $menu_slug = isset($page_map[$page_slug]) ? $page_map[$page_slug] : 'wai-' . $page_slug;
                
                $this->available_pages[$menu_slug] = array(
                    'file' => $page_file,
                    'slug' => $page_slug,
                    'menu_slug' => $menu_slug
                );
            }
        }
    }
    
    /**
     * 注册模块
     */
    public function register_module($module_id, $module_data) {
        $defaults = array(
            'name' => $module_id,
            'description' => '',
            'icon' => '⚙️',
            'category' => 'general',
            'status' => 'available',
            'economic_impact' => '',
            'setup_required' => false,
            'requirement' => null,
            'dependencies' => array(),
            'performance_impact' => 'medium'
        );
        
        $this->registered_modules[$module_id] = wp_parse_args($module_data, $defaults);
        
        // 如果模块有对应的页面，注册到可用页面
        if (isset($module_data['page_slug'])) {
            $this->available_pages[$module_data['page_slug']] = array(
                'module' => $module_id,
                'menu_slug' => $module_data['page_slug']
            );
        }
    }
    
    /**
     * 从文件注册模块
     */
    private function register_module_from_file($module_id, $file_path) {
        // 这里可以解析文件头信息来获取模块元数据
        $module_data = array(
            'name' => ucfirst(str_replace('-', ' ', $module_id)),
            'description' => __('从文件自动注册的模块', 'woocommerce-ai-agent'),
            'file_path' => $file_path
        );
        
        $this->register_module($module_id, $module_data);
    }
    
    /**
     * 注册管理页面
     */
    public function register_admin_pages() {
        foreach ($this->available_pages as $menu_slug => $page_data) {
            // 检查页面是否已经注册
            global $submenu;
            $is_registered = false;
            
            if (isset($submenu['wai-dashboard'])) {
                foreach ($submenu['wai-dashboard'] as $item) {
                    if ($item[2] === $menu_slug) {
                        $is_registered = true;
                        break;
                    }
                }
            }
            
            if (!$is_registered) {
                $this->add_admin_page($menu_slug, $page_data);
            }
        }
    }
    
    /**
     * 添加管理页面
     */
    private function add_admin_page($menu_slug, $page_data) {
        $module = isset($page_data['module']) ? $this->get_module($page_data['module']) : null;
        
        $page_title = $module ? $module['name'] : $this->get_page_title($menu_slug);
        $menu_title = $module ? $module['name'] : $this->get_menu_title($menu_slug);
        
        add_submenu_page(
            'wai-dashboard',
            $page_title,
            $menu_title,
            'manage_options',
            $menu_slug,
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * 渲染管理页面
     */
    public function render_admin_page() {
        $current_page = $_GET['page'] ?? '';
        
        if (isset($this->available_pages[$current_page])) {
            $page_data = $this->available_pages[$current_page];
            $this->render_page_content($current_page, $page_data);
        } else {
            $this->render_fallback_page($current_page);
        }
    }
    
    /**
     * 渲染页面内容
     */
    private function render_page_content($page_slug, $page_data) {
        // 显示经济性目标引导
        $this->render_economic_goal_banner($page_slug);
        
        $file_path = WAI_PLUGIN_DIR . 'admin/partials/' . $page_data['file'] ?? $page_slug . '.php';
        
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            $this->render_module_content($page_slug, $page_data);
        }
    }
    
    /**
     * 渲染模块内容
     */
    private function render_module_content($page_slug, $page_data) {
        $module = isset($page_data['module']) ? $this->get_module($page_data['module']) : null;
        
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($module ? $module['name'] : $page_slug) . '</h1>';
        
        if ($module && $module['description']) {
            echo '<div class="notice notice-info"><p>' . esc_html($module['description']) . '</p></div>';
        }
        
        echo '<div class="card">';
        echo '<h2>' . __('模块功能预览', 'woocommerce-ai-agent') . '</h2>';
        echo '<p>' . __('此模块的功能界面正在开发中。', 'woocommerce-ai-agent') . '</p>';
        
        if ($module) {
            echo '<div class="module-info">';
            echo '<h3>' . __('模块信息', 'woocommerce-ai-agent') . '</h3>';
            echo '<ul>';
            echo '<li><strong>' . __('经济影响:', 'woocommerce-ai-agent') . '</strong> ' . esc_html($module['economic_impact']) . '</li>';
            echo '<li><strong>' . __('性能影响:', 'woocommerce-ai-agent') . '</strong> ' . esc_html($module['performance_impact']) . '</li>';
            echo '<li><strong>' . __('状态:', 'woocommerce-ai-agent') . '</strong> ' . esc_html($module['status']) . '</li>';
            echo '</ul>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * 渲染经济性目标引导
     */
    private function render_economic_goal_banner($page_slug) {
        $economic_data = $this->get_page_economic_data($page_slug);
        ?>
        <div class="wai-economic-goal-banner">
            <div class="goal-header">
                <h3><?php echo esc_html($economic_data['title']); ?></h3>
                <span class="goal-page"><?php echo esc_html($this->get_page_title($page_slug)); ?></span>
            </div>
            
            <div class="goal-progress">
                <?php $this->render_revenue_progress(); ?>
            </div>
            
            <div class="goal-suggestions">
                <?php foreach ($economic_data['suggestions'] as $suggestion): ?>
                <div class="suggestion-item">
                    <span class="icon"><?php echo $suggestion['icon']; ?></span>
                    <span class="text"><?php echo $suggestion['text']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($economic_data['show_alert']): ?>
            <div class="goal-alert">
                <span class="alert-icon">⚠️</span>
                <span class="alert-text"><?php echo $economic_data['alert_message']; ?></span>
                <?php if (!empty($economic_data['action_button'])): ?>
                <button class="button button-primary" onclick="<?php echo $economic_data['action_button']['action']; ?>">
                    <?php echo $economic_data['action_button']['text']; ?>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * 获取页面经济数据
     */
    private function get_page_economic_data($page_slug) {
        $economic_map = array(
            'wai-dashboard' => array(
                'title' => '🎯 整体业务目标追踪',
                'suggestions' => array(
                    array('icon' => '🚀', 'text' => '启用AI价格优化可提升收入 <strong>15-30%</strong>'),
                    array('icon' => '🤖', 'text' => '自动化营销可减少 <strong>40%</strong> 人工成本'),
                    array('icon' => '🔗', 'text' => 'Web3功能可吸引 <strong>25%</strong> 新客户群体')
                ),
                'show_alert' => true,
                'alert_message' => '建议全面启用AI电商智能体功能以获得最佳经济效果！',
                'action_button' => array('text' => '🚀 开始优化', 'action' => 'startOptimization()')
            ),
            'wai-web3-dashboard' => array(
                'title' => '🔗 Web3经济价值追踪',
                'suggestions' => array(
                    array('icon' => '🪙', 'text' => 'NFT功能可创造 <strong>额外30%</strong> 数字资产收入'),
                    array('icon' => '🌐', 'text' => '元宇宙店铺可覆盖 <strong>全球年轻用户</strong>'),
                    array('icon' => '💎', 'text' => '代币经济可提升 <strong>客户忠诚度45%</strong>')
                ),
                'show_alert' => true,
                'alert_message' => 'Web3功能未完全启用，可能错过数字资产收入机会！',
                'action_button' => array('text' => '🔗 启用Web3', 'action' => 'enableWeb3Features()')
            )
            // 其他页面的经济数据...
        );
        
        return isset($economic_map[$page_slug]) ? $economic_map[$page_slug] : $economic_map['wai-dashboard'];
    }
    
    /**
     * 渲染收入进度
     */
    private function render_revenue_progress() {
        $revenue_goal = get_option('wai_revenue_goal', 10000);
        $current_revenue = $this->get_store_data()['total_revenue'];
        $progress = $current_revenue > 0 ? min(($current_revenue / $revenue_goal) * 100, 100) : 0;
        $days_remaining = date('t') - date('j');
        ?>
        <div class="progress-info">
            <div class="progress-item">
                <span class="label">月度收入目标</span>
                <span class="value">¥<?php echo number_format($revenue_goal, 2); ?></span>
            </div>
            <div class="progress-item">
                <span class="label">当前收入</span>
                <span class="value">¥<?php echo number_format($current_revenue, 2); ?></span>
            </div>
            <div class="progress-item">
                <span class="label">完成进度</span>
                <span class="value"><?php echo number_format($progress, 1); ?>%</span>
            </div>
            <div class="progress-item">
                <span class="label">剩余天数</span>
                <span class="value"><?php echo $days_remaining; ?> 天</span>
            </div>
        </div>
        
        <div class="progress-bar-container">
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
            </div>
            <div class="progress-labels">
                <span>0</span>
                <span>¥<?php echo number_format($revenue_goal, 0); ?></span>
            </div>
        </div>
        <?php
    }
    
    /**
     * 获取商店数据
     */
    private function get_store_data() {
        // 简化的商店数据获取
        return array(
            'total_revenue' => get_option('wai_current_revenue', 0)
        );
    }
    
    /**
     * 获取页面标题
     */
    private function get_page_title($page_slug) {
        $titles = array(
            'wai-dashboard' => __('智能体仪表板', 'woocommerce-ai-agent'),
            'wai-web3-dashboard' => __('Web3仪表板', 'woocommerce-ai-agent'),
            'wai-swarm-management' => __('蜂群管理', 'woocommerce-ai-agent')
            // 其他页面标题...
        );
        
        return isset($titles[$page_slug]) ? $titles[$page_slug] : $page_slug;
    }
    
    /**
     * 获取菜单标题
     */
    private function get_menu_title($page_slug) {
        $titles = array(
            'wai-dashboard' => __('仪表板', 'woocommerce-ai-agent'),
            'wai-web3-dashboard' => __('Web3面板', 'woocommerce-ai-agent'),
            'wai-swarm-management' => __('蜂群智能', 'woocommerce-ai-agent')
            // 其他菜单标题...
        );
        
        return isset($titles[$page_slug]) ? $titles[$page_slug] : $page_slug;
    }
    
    /**
     * 渲染备用页面
     */
    private function render_fallback_page($page_slug) {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->get_page_title($page_slug)) . '</h1>';
        echo '<div class="notice notice-warning">';
        echo '<p>' . __('页面正在开发中，敬请期待！', 'woocommerce-ai-agent') . '</p>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * 获取所有模块
     */
    public function get_modules($category = null) {
        if ($category) {
            return array_filter($this->registered_modules, function($module) use ($category) {
                return $module['category'] === $category;
            });
        }
        
        return $this->registered_modules;
    }
    
    /**
     * 获取单个模块
     */
    public function get_module($module_id) {
        return isset($this->registered_modules[$module_id]) ? $this->registered_modules[$module_id] : null;
    }
    
    /**
     * 获取可用页面
     */
    public function get_available_pages() {
        return $this->available_pages;
    }
    
    /**
     * 检查模块是否可用
     */
    public function is_module_available($module_id) {
        $module = $this->get_module($module_id);
        
        if (!$module) {
            return false;
        }
        
        // 检查要求
        if ($module['requirement'] && !get_option($module['requirement'], false)) {
            return false;
        }
        
        // 检查依赖
        foreach ($module['dependencies'] as $dependency) {
            if (!$this->is_module_available($dependency)) {
                return false;
            }
        }
        
        return true;
    }
}