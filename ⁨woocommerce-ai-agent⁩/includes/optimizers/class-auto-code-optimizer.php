<?php
/**
 * 自动代码优化器 - 基于架构分析的智能代码进化系统
 * 文件路径: /wp-content/plugins/woocommerce-ai-agent/includes/optimizers/class-auto-code-optimizer.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Auto_Code_Optimizer {
    
    private $plugin_structure = [];
    private $database_schema = [];
    private $performance_metrics = [];
    private $optimization_rules = [];
    private $code_analysis_cache = [];
    
    public function __construct() {
        $this->load_optimization_rules();
        $this->scan_plugin_architecture();
        $this->analyze_database_schema();
        $this->initialize_performance_tracking();
    }
    
    /**
     * 扫描插件架构
     */
    public function scan_plugin_architecture() {
        $plugin_dir = WP_PLUGIN_DIR . '/woocommerce-ai-agent/';
        
        $this->plugin_structure = [
            'basic_info' => [
                'plugin_name' => 'WooCommerce AI Agent',
                'version' => $this->get_plugin_version(),
                'main_file' => $plugin_dir . 'woocommerce-ai-agent.php',
                'total_files' => 0,
                'total_lines' => 0
            ],
            'directory_structure' => $this->scan_directory_structure($plugin_dir),
            'file_analysis' => $this->analyze_plugin_files($plugin_dir),
            'class_hierarchy' => $this->extract_class_hierarchy($plugin_dir),
            'function_reference' => $this->extract_functions($plugin_dir),
            'hook_system' => $this->extract_wordpress_hooks($plugin_dir)
        ];
        
        return $this->plugin_structure;
    }
    
    /**
     * 扫描目录结构
     */
    private function scan_directory_structure($base_dir) {
        $structure = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $relative_path = str_replace($base_dir, '', $file->getPathname());
            
            if ($file->isDir()) {
                $structure['directories'][] = [
                    'path' => $relative_path,
                    'file_count' => $this->count_files_in_directory($file->getPathname())
                ];
            } else {
                $structure['files'][] = [
                    'path' => $relative_path,
                    'size' => $file->getSize(),
                    'extension' => $file->getExtension(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime())
                ];
            }
        }
        
        return $structure;
    }
    
    /**
     * 分析插件文件
     */
    private function analyze_plugin_files($plugin_dir) {
        $analysis = [];
        $php_files = $this->get_php_files($plugin_dir);
        
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            $relative_path = str_replace($plugin_dir, '', $file);
            
            $analysis[$relative_path] = [
                'lines' => substr_count($content, "\n") + 1,
                'size' => filesize($file),
                'classes' => $this->extract_classes_from_file($content),
                'functions' => $this->extract_functions_from_file($content),
                'complexity' => $this->calculate_file_complexity($content),
                'dependencies' => $this->analyze_dependencies($content)
            ];
            
            // 更新总体统计
            $this->plugin_structure['basic_info']['total_files']++;
            $this->plugin_structure['basic_info']['total_lines'] += $analysis[$relative_path]['lines'];
        }
        
        return $analysis;
    }
    
    /**
     * 分析数据库架构
     */
    public function analyze_database_schema() {
        global $wpdb;
        
        $this->database_schema = [
            'tables' => [],
            'indexes' => [],
            'relationships' => [],
            'performance' => []
        ];
        
        // 获取所有自定义表
        $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}wai_%'", ARRAY_N);
        
        foreach ($tables as $table) {
            $table_name = $table[0];
            $table_info = $this->analyze_table_structure($table_name);
            $this->database_schema['tables'][$table_name] = $table_info;
        }
        
        // 分析表关系
        $this->database_schema['relationships'] = $this->analyze_table_relationships();
        
        // 性能分析
        $this->database_schema['performance'] = $this->analyze_database_performance();
        
        return $this->database_schema;
    }
    
    /**
     * 分析表结构
     */
    private function analyze_table_structure($table_name) {
        global $wpdb;
        
        $columns = $wpdb->get_results("DESCRIBE $table_name");
        $indexes = $wpdb->get_results("SHOW INDEX FROM $table_name");
        
        $structure = [
            'columns' => [],
            'indexes' => [],
            'row_count' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name"),
            'size' => $this->get_table_size($table_name)
        ];
        
        foreach ($columns as $column) {
            $structure['columns'][$column->Field] = [
                'type' => $column->Type,
                'null' => $column->Null,
                'key' => $column->Key,
                'default' => $column->Default,
                'extra' => $column->Extra
            ];
        }
        
        foreach ($indexes as $index) {
            if (!isset($structure['indexes'][$index->Key_name])) {
                $structure['indexes'][$index->Key_name] = [
                    'columns' => [],
                    'unique' => !$index->Non_unique,
                    'type' => $index->Index_type
                ];
            }
            $structure['indexes'][$index->Key_name]['columns'][] = $index->Column_name;
        }
        
        return $structure;
    }
    
    /**
     * 获取架构报告 - 供AI读取
     */
    public function get_architecture_report() {
        return [
            'plugin_structure' => $this->plugin_structure,
            'database_schema' => $this->database_schema,
            'performance_metrics' => $this->get_performance_metrics(),
            'optimization_opportunities' => $this->identify_optimization_opportunities(),
            'technical_debt' => $this->assess_technical_debt()
        ];
    }
    
    /**
     * 显示架构概览
     */
    public function display_architecture_overview() {
        $report = $this->get_architecture_report();
        
        ob_start();
        ?>
        <div class="wai-architecture-overview">
            <!-- 基本统计 -->
            <div class="architecture-stats">
                <div class="stat-card">
                    <h4>📁 文件统计</h4>
                    <div class="stat-value"><?php echo $report['plugin_structure']['basic_info']['total_files']; ?> 个文件</div>
                    <div class="stat-detail"><?php echo $report['plugin_structure']['basic_info']['total_lines']; ?> 行代码</div>
                </div>
                
                <div class="stat-card">
                    <h4>🗃️ 数据库表</h4>
                    <div class="stat-value"><?php echo count($report['database_schema']['tables']); ?> 个表</div>
                    <div class="stat-detail"><?php echo array_sum(array_column(array_column($report['database_schema']['tables'], 'row_count'), 0)); ?> 行数据</div>
                </div>
                
                <div class="stat-card">
                    <h4>🎯 性能评分</h4>
                    <div class="stat-value"><?php echo $this->calculate_performance_score($report); ?>/100</div>
                    <div class="stat-detail">整体代码质量</div>
                </div>
            </div>
            
            <!-- 目录结构 -->
            <div class="directory-tree">
                <h4>📂 目录结构</h4>
                <div class="tree-view">
                    <?php echo $this->generate_directory_tree($report['plugin_structure']['directory_structure']); ?>
                </div>
            </div>
            
            <!-- 数据库架构 -->
            <div class="database-schema">
                <h4>🗄️ 数据库架构</h4>
                <div class="tables-grid">
                    <?php foreach ($report['database_schema']['tables'] as $table_name => $table_info): ?>
                        <div class="table-card">
                            <h5><?php echo $table_name; ?></h5>
                            <div class="table-meta">
                                <span>列: <?php echo count($table_info['columns']); ?></span>
                                <span>索引: <?php echo count($table_info['indexes']); ?></span>
                                <span>行数: <?php echo $table_info['row_count']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <style>
        .wai-architecture-overview {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .architecture-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        .stat-detail {
            color: #666;
            font-size: 12px;
        }
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .table-card {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border-left: 3px solid #0073aa;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 生成目录树
     */
    private function generate_directory_tree($structure) {
        $html = '<ul class="file-tree">';
        
        foreach ($structure['directories'] as $dir) {
            $html .= '<li class="directory">';
            $html .= '📁 ' . $dir['path'];
            $html .= ' <span class="file-count">(' . $dir['file_count'] . ' files)</span>';
            $html .= '</li>';
        }
        
        foreach ($structure['files'] as $file) {
            if ($file['extension'] === 'php') {
                $icon = '📄';
                $class = 'php-file';
            } else {
                $icon = '📝';
                $class = 'other-file';
            }
            
            $html .= '<li class="' . $class . '">';
            $html .= $icon . ' ' . $file['path'];
            $html .= ' <span class="file-size">(' . size_format($file['size']) . ')</span>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        return $html;
    }
    
    // 以下辅助方法保持不变，但为了完整性包含在这里
    private function get_php_files($directory) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    private function extract_classes_from_file($content) {
        $classes = [];
        preg_match_all('/class\s+(\w+).*?\{/s', $content, $matches);
        return $matches[1] ?? [];
    }
    
    private function extract_functions_from_file($content) {
        $functions = [];
        preg_match_all('/function\s+(\w+)\s*\(/s', $content, $matches);
        return $matches[1] ?? [];
    }
    
    private function calculate_file_complexity($content) {
        // 简化的复杂度计算
        $complexity = substr_count($content, 'if') + substr_count($content, 'for') + 
                     substr_count($content, 'while') + substr_count($content, 'foreach');
        return $complexity;
    }
    
    private function analyze_dependencies($content) {
        $dependencies = [];
        // 分析use语句
        preg_match_all('/use\s+([^;]+);/s', $content, $matches);
        $dependencies['imports'] = $matches[1] ?? [];
        
        // 分析WordPress函数调用
        preg_match_all('/(wp_[\w_]+)\(/', $content, $matches);
        $dependencies['wordpress_functions'] = array_unique($matches[1] ?? []);
        
        return $dependencies;
    }
    
    private function count_files_in_directory($dir) {
        $iterator = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
        return iterator_count($iterator);
    }
    
    private function get_plugin_version() {
        $plugin_data = get_file_data(WP_PLUGIN_DIR . '/woocommerce-ai-agent/woocommerce-ai-agent.php', ['Version' => 'Version']);
        return $plugin_data['Version'] ?? '1.0.0';
    }
    
    // 其他方法（extract_class_hierarchy, extract_functions, extract_wordpress_hooks等）
    // 由于篇幅限制，这里省略具体实现，但实际文件中应该包含
}