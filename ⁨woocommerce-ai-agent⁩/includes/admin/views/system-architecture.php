<?php
/**
 * 系统架构展示页面 - 插件架构和文件目录可视化
 * 文件路径: /wp-content/plugins/woocommerce-ai-agent/includes/admin/views/system-architecture.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// 初始化架构分析器
$architect = Woocommerce_AI_Agent::get_instance()->managers['system_architect'] ?? null;
$architecture_report = $architect ? $architect->get_architecture_report() : $this->get_mock_architecture_data();
?>

<div class="wrap wai-system-architecture">
    <!-- 页面头部 -->
    <div class="architecture-header">
        <h1>🏗️ 系统架构总览 - WooCommerce AI Agent</h1>
        <p class="header-subtitle">全面了解插件架构、文件结构和依赖关系</p>
        <div class="header-actions">
            <button class="button button-primary" onclick="refreshArchitectureAnalysis()">
                <span class="dashicons dashicons-update"></span>
                重新分析
            </button>
            <button class="button" onclick="exportArchitectureReport()">
                <span class="dashicons dashicons-download"></span>
                导出报告
            </button>
        </div>
    </div>

    <!-- 架构概览 -->
    <div class="architecture-overview">
        <div class="overview-cards">
            <div class="overview-card">
                <div class="card-icon">📁</div>
                <div class="card-content">
                    <div class="card-value"><?php echo $architecture_report['plugin_structure']['basic_info']['total_files']; ?></div>
                    <div class="card-label">文件总数</div>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon">📝</div>
                <div class="card-content">
                    <div class="card-value"><?php echo number_format($architecture_report['plugin_structure']['basic_info']['total_lines']); ?></div>
                    <div class="card-label">代码行数</div>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon">🎯</div>
                <div class="card-content">
                    <div class="card-value"><?php echo count($architecture_report['plugin_structure']['class_hierarchy']); ?></div>
                    <div class="card-label">类数量</div>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon">🔗</div>
                <div class="card-content">
                    <div class="card-value"><?php echo count($architecture_report['dependency_map']['wordpress_core']); ?></div>
                    <div class="card-label">WP函数依赖</div>
                </div>
            </div>
        </div>
    </div>

    <div class="architecture-content">
        <!-- 目录结构浏览器 -->
        <div class="architecture-section">
            <div class="section-header">
                <h2>📂 文件目录结构</h2>
                <div class="section-actions">
                    <input type="text" id="fileSearch" placeholder="搜索文件或目录..." class="file-search">
                    <button class="button button-small" onclick="collapseAll()">折叠全部</button>
                    <button class="button button-small" onclick="expandAll()">展开全部</button>
                </div>
            </div>
            
            <div class="file-browser">
                <div class="browser-sidebar">
                    <div class="browser-stats">
                        <h4>文件统计</h4>
                        <div class="stats-grid">
                            <?php foreach ($architecture_report['plugin_structure']['file_system']['file_types'] as $ext => $count): ?>
                            <div class="stat-item">
                                <span class="stat-extension">.<?php echo $ext; ?></span>
                                <span class="stat-count"><?php echo $count; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="size-analysis">
                        <h4>大小分析</h4>
                        <div class="size-metrics">
                            <div class="size-metric">
                                <label>总大小</label>
                                <span><?php echo size_format($architecture_report['plugin_structure']['file_system']['size_analysis']['total_size']); ?></span>
                            </div>
                            <div class="size-metric">
                                <label>平均文件大小</label>
                                <span><?php echo size_format($architecture_report['plugin_structure']['file_system']['size_analysis']['average_file_size']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="browser-main">
                    <div class="directory-tree" id="directoryTree">
                        <?php echo $this->generate_directory_tree($architecture_report['plugin_structure']['file_system']); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 类层次结构 -->
        <div class="architecture-section">
            <div class="section-header">
                <h2>🎯 类层次结构</h2>
                <div class="section-actions">
                    <select id="classFilter" onchange="filterClasses()">
                        <option value="all">所有类</option>
                        <option value="controllers">控制器</option>
                        <option value="models">模型</option>
                        <option value="services">服务</option>
                    </select>
                </div>
            </div>
            
            <div class="class-hierarchy">
                <div class="classes-grid">
                    <?php foreach ($architecture_report['plugin_structure']['class_hierarchy'] as $class_name => $class_info): ?>
                    <div class="class-card" data-class-type="<?php echo $this->detect_class_type($class_name); ?>">
                        <div class="class-header">
                            <h4><?php echo $class_name; ?></h4>
                            <span class="class-location"><?php echo $class_info['file']; ?></span>
                        </div>
                        
                        <div class="class-info">
                            <?php if ($class_info['parent']): ?>
                            <div class="class-inheritance">
                                <span class="label">继承自:</span>
                                <span class="value"><?php echo $class_info['parent']; ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($class_info['interfaces'])): ?>
                            <div class="class-interfaces">
                                <span class="label">接口:</span>
                                <span class="value"><?php echo implode(', ', $class_info['interfaces']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="class-metrics">
                                <div class="metric">
                                    <span class="label">方法:</span>
                                    <span class="value"><?php echo count($class_info['methods']); ?></span>
                                </div>
                                <div class="metric">
                                    <span class="label">属性:</span>
                                    <span class="value"><?php echo count($class_info['properties']); ?></span>
                                </div>
                                <div class="metric">
                                    <span class="label">行数:</span>
                                    <span class="value"><?php echo $class_info['lines']; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="class-actions">
                            <button class="button button-small" onclick="viewClassDetails('<?php echo $class_name; ?>')">查看详情</button>
                            <button class="button button-small" onclick="viewClassFile('<?php echo $class_info['file']; ?>')">打开文件</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 依赖关系图 -->
        <div class="architecture-section">
            <div class="section-header">
                <h2>🔗 依赖关系图</h2>
            </div>
            
            <div class="dependency-map">
                <div class="dependency-tabs">
                    <button class="tab-button active" data-tab="wordpress">WordPress 核心</button>
                    <button class="tab-button" data-tab="woocommerce">WooCommerce</button>
                    <button class="tab-button" data-tab="external">外部 API</button>
                </div>
                
                <div class="dependency-content">
                    <div class="tab-pane active" id="wordpress">
                        <div class="dependency-list">
                            <?php foreach (array_slice($architecture_report['dependency_map']['wordpress_core'], 0, 20) as $function): ?>
                            <span class="dependency-item"><?php echo $function; ?>()</span>
                            <?php endforeach; ?>
                            <?php if (count($architecture_report['dependency_map']['wordpress_core']) > 20): ?>
                            <span class="dependency-more">+<?php echo count($architecture_report['dependency_map']['wordpress_core']) - 20; ?> 更多</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="woocommerce">
                        <div class="dependency-list">
                            <?php foreach ($architecture_report['dependency_map']['woocommerce'] as $function): ?>
                            <span class="dependency-item"><?php echo $function; ?>()</span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="external">
                        <div class="dependency-list">
                            <?php foreach ($architecture_report['dependency_map']['external_apis'] as $api): ?>
                            <span class="dependency-item external"><?php echo $api; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 钩子系统 -->
        <div class="architecture-section">
            <div class="section-header">
                <h2>⚡ 钩子系统</h2>
            </div>
            
            <div class="hook-system">
                <div class="hook-types">
                    <div class="hook-type">
                        <h4>Action 钩子 (<?php echo count($architecture_report['plugin_structure']['hook_system']['actions']); ?>)</h4>
                        <div class="hook-list">
                            <?php foreach (array_slice($architecture_report['plugin_structure']['hook_system']['actions'], 0, 10) as $hook): ?>
                            <div class="hook-item">
                                <span class="hook-name"><?php echo $hook['hook']; ?></span>
                                <span class="hook-type-badge <?php echo $hook['type']; ?>"><?php echo $hook['type']; ?></span>
                                <span class="hook-file"><?php echo $hook['file']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="hook-type">
                        <h4>Filter 钩子 (<?php echo count($architecture_report['plugin_structure']['hook_system']['filters']); ?>)</h4>
                        <div class="hook-list">
                            <?php foreach (array_slice($architecture_report['plugin_structure']['hook_system']['filters'], 0, 10) as $hook): ?>
                            <div class="hook-item">
                                <span class="hook-name"><?php echo $hook['hook']; ?></span>
                                <span class="hook-type-badge <?php echo $hook['type']; ?>"><?php echo $hook['type']; ?></span>
                                <span class="hook-file"><?php echo $hook['file']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 数据库架构 -->
        <div class="architecture-section">
            <div class="section-header">
                <h2>🗄️ 数据库架构</h2>
            </div>
            
            <div class="database-schema">
                <div class="schema-tables">
                    <h4>自定义表</h4>
                    <div class="tables-grid">
                        <?php foreach ($architecture_report['plugin_structure']['database_schema']['custom_tables'] as $table_name => $table_structure): ?>
                        <div class="table-card">
                            <div class="table-header">
                                <h5><?php echo $table_name; ?></h5>
                                <span class="table-columns"><?php echo count($table_structure); ?> 列</span>
                            </div>
                            <div class="table-structure">
                                <?php foreach (array_slice($table_structure, 0, 3) as $column_name => $column_info): ?>
                                <div class="column-info">
                                    <span class="column-name"><?php echo $column_name; ?></span>
                                    <span class="column-type"><?php echo $column_info['type']; ?></span>
                                </div>
                                <?php endforeach; ?>
                                <?php if (count($table_structure) > 3): ?>
                                <div class="column-more">+<?php echo count($table_structure) - 3; ?> 更多列</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 目录树功能
function generateDirectoryTree(filesystem) {
    // 这里实现目录树生成逻辑
    return '<div class="tree-view">目录树内容</div>';
}

// 类过滤
function filterClasses() {
    const filter = document.getElementById('classFilter').value;
    const classCards = document.querySelectorAll('.class-card');
    
    classCards.forEach(card => {
        if (filter === 'all' || card.dataset.classType === filter) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// 标签页切换
document.querySelectorAll('.tab-button').forEach(button => {
    button.addEventListener('click', function() {
        const tabId = this.dataset.tab;
        
        // 更新按钮状态
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        
        // 更新内容
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
    });
});

// 刷新架构分析
function refreshArchitectureAnalysis() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<span class="dashicons dashicons-update spin"></span> 分析中...';
    button.disabled = true;
    
    // AJAX调用重新分析
    jQuery.post(ajaxurl, {
        action: 'wai_refresh_architecture',
        nonce: '<?php echo wp_create_nonce("wai_architecture_nonce"); ?>'
    }, function(response) {
        if (response.success) {
            location.reload();
        } else {
            button.innerHTML = originalText;
            button.disabled = false;
            alert('分析失败，请重试');
        }
    });
}

// 导出报告
function exportArchitectureReport() {
    // 实现导出功能
    alert('架构报告导出功能');
}
</script>

<style>
.wai-system-architecture {
    max-width: 1400px;
    margin: 0 auto;
}

/* 头部样式 */
.architecture-header {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.architecture-header h1 {
    margin: 0 0 8px 0;
    font-size: 32px;
    color: #23282d;
}

.header-subtitle {
    margin: 0;
    color: #666;
    font-size: 16px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

/* 概览卡片 */
.architecture-overview {
    margin-bottom: 30px;
}

.overview-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

.overview-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.3s ease;
}

.overview-card:hover {
    transform: translateY(-2px);
}

.card-icon {
    font-size: 32px;
}

.card-value {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
}

.card-label {
    font-size: 14px;
    color: #666;
}

/* 内容区域 */
.architecture-content {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.architecture-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.section-header h2 {
    margin: 0;
    font-size: 24px;
}

.section-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.file-search {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 200px;
}

/* 文件浏览器 */
.file-browser {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 20px;
}

.browser-stats, .size-analysis {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.browser-stats h4, .size-analysis h4 {
    margin: 0 0 15px 0;
    font-size: 16px;
}

.stats-grid {
    display: grid;
    gap: 8px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
}

.stat-extension {
    font-weight: 500;
}

.stat-count {
    color: #0073aa;
    font-weight: bold;
}

.size-metrics {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.size-metric {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
}

/* 类卡片 */
.classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.class-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #0073aa;
    transition: all 0.3s ease;
}

.class-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.class-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.class-header h4 {
    margin: 0;
    font-size: 18px;
    color: #23282d;
}

.class-location {
    font-size: 12px;
    color: #666;
    background: #e0e0e0;
    padding: 2px 6px;
    border-radius: 8px;
}

.class-info {
    margin-bottom: 15px;
}

.class-inheritance, .class-interfaces {
    font-size: 14px;
    margin-bottom: 8px;
}

.class-inheritance .label, .class-interfaces .label {
    font-weight: 500;
    color: #666;
}

.class-metrics {
    display: flex;
    gap: 15px;
}

.class-metrics .metric {
    font-size: 12px;
}

.class-metrics .label {
    color: #666;
}

.class-metrics .value {
    font-weight: bold;
    color: #0073aa;
}

.class-actions {
    display: flex;
    gap: 6px;
}

/* 依赖关系 */
.dependency-tabs {
    display: flex;
    gap: 0;
    border-bottom: 1px solid #e0e0e0;
    margin-bottom: 20px;
}

.tab-button {
    background: none;
    border: none;
    padding: 12px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

.tab-button.active {
    border-bottom-color: #0073aa;
    color: #0073aa;
    font-weight: 500;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.dependency-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.dependency-item {
    background: #e7f3ff;
    color: #0073aa;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-family: monospace;
}

.dependency-item.external {
    background: #fff3cd;
    color: #856404;
}

.dependency-more {
    color: #666;
    font-size: 12px;
    font-style: italic;
}

/* 钩子系统 */
.hook-types {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.hook-type h4 {
    margin: 0 0 15px 0;
    font-size: 16px;
}

.hook-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.hook-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 4px;
}

.hook-name {
    flex: 1;
    font-family: monospace;
    font-size: 13px;
}

.hook-type-badge {
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: bold;
}

.hook-type-badge.trigger {
    background: #e7f4e4;
    color: #46b450;
}

.hook-type-badge.handler {
    background: #e7f3ff;
    color: #0073aa;
}

.hook-file {
    font-size: 11px;
    color: #666;
}

/* 数据库表 */
.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.table-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e0e0e0;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e0e0e0;
}

.table-header h5 {
    margin: 0;
    font-size: 14px;
    font-family: monospace;
}

.table-columns {
    font-size: 11px;
    color: #666;
    background: #e0e0e0;
    padding: 2px 6px;
    border-radius: 8px;
}

.table-structure {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.column-info {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
}

.column-name {
    font-weight: 500;
}

.column-type {
    color: #666;
    font-family: monospace;
}

.column-more {
    font-size: 11px;
    color: #666;
    text-align: center;
    font-style: italic;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .architecture-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .overview-cards {
        grid-template-columns: 1fr 1fr;
    }
    
    .file-browser {
        grid-template-columns: 1fr;
    }
    
    .hook-types {
        grid-template-columns: 1fr;
    }
    
    .classes-grid {
        grid-template-columns: 1fr;
    }
    
    .tables-grid {
        grid-template-columns: 1fr;
    }
}
</style>