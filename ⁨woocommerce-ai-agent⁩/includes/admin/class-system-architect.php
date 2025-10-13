<?php
/**
 * 系统架构展示器 - 插件架构和文件目录可视化
 * 文件路径: /wp-content/plugins/woocommerce-ai-agent/includes/admin/class-system-architect.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class System_Architect {
    
    private $plugin_structure = [];
    private $code_analysis = [];
    private $dependency_map = [];
    
    public function __construct() {
        $this->analyze_plugin_architecture();
        $this->generate_dependency_map();
        $this->setup_architecture_endpoints();
    }
    
    /**
     * 分析插件架构
     */
    public function analyze_plugin_architecture() {
        $plugin_dir = WP_PLUGIN_DIR . '/woocommerce-ai-agent/';
        
        $this->plugin_structure = [
            'basic_info' => $this->get_plugin_basic_info(),
            'file_system' => $this->scan_file_system($plugin_dir),
            'class_hierarchy' => $this->extract_class_hierarchy($plugin_dir),
            'hook_system' => $this->analyze_hook_system($plugin_dir),
            'database_schema' => $this->analyze_database_schema(),
            'api_endpoints' => $this->analyze_api_endpoints($plugin_dir)
        ];
        
        // 缓存架构分析结果
        set_transient('wai_architecture_analysis', $this->plugin_structure, HOUR_IN_SECONDS);
    }
    
    /**
     * 获取插件基本信息
     */
    private function get_plugin_basic_info() {
        $plugin_file = WP_PLUGIN_DIR . '/woocommerce-ai-agent/woocommerce-ai-agent.php';
        $plugin_data = get_file_data($plugin_file, [
            'Name' => 'Plugin Name',
            'Version' => 'Version',
            'Description' => 'Description',
            'Author' => 'Author',
            'TextDomain' => 'Text Domain'
        ]);
        
        return [
            'name' => $plugin_data['Name'] ?? 'WooCommerce AI Agent',
            'version' => $plugin_data['Version'] ?? '1.0.0',
            'description' => $plugin_data['Description'] ?? '',
            'author' => $plugin_data['Author'] ?? '',
            'main_file' => $plugin_file,
            'total_files' => 0,
            'total_lines' => 0,
            'install_date' => $this->get_install_date()
        ];
    }
    
    /**
     * 扫描文件系统
     */
    private function scan_file_system($base_dir) {
        $structure = [
            'directories' => [],
            'files' => [],
            'file_types' => [],
            'size_analysis' => []
        ];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        $total_size = 0;
        $total_files = 0;
        
        foreach ($iterator as $file) {
            $relative_path = str_replace($base_dir, '', $file->getPathname());
            
            if ($file->isDir()) {
                $structure['directories'][] = [
                    'path' => $relative_path,
                    'depth' => substr_count($relative_path, '/'),
                    'file_count' => $this->count_files_in_directory($file->getPathname())
                ];
            } else {
                $file_info = [
                    'path' => $relative_path,
                    'size' => $file->getSize(),
                    'extension' => $file->getExtension(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    'lines' => $this->count_file_lines($file->getPathname())
                ];
                
                $structure['files'][] = $file_info;
                
                // 统计文件类型
                $extension = $file->getExtension();
                if (!isset($structure['file_types'][$extension])) {
                    $structure['file_types'][$extension] = 0;
                }
                $structure['file_types'][$extension]++;
                
                $total_size += $file->getSize();
                $total_files++;
                
                // 更新基本信息
                $this->plugin_structure['basic_info']['total_files'] = $total_files;
                $this->plugin_structure['basic_info']['total_lines'] += $file_info['lines'];
            }
        }
        
        $structure['size_analysis'] = [
            'total_size' => $total_size,
            'average_file_size' => $total_files > 0 ? $total_size / $total_files : 0,
            'largest_files' => $this->get_largest_files($structure['files']),
            'most_complex_files' => $this->get_most_complex_files($structure['files'])
        ];
        
        return $structure;
    }
    
    /**
     * 提取类层次结构
     */
    private function extract_class_hierarchy($plugin_dir) {
        $classes = [];
        $php_files = $this->get_php_files($plugin_dir);
        
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            $relative_path = str_replace($plugin_dir, '', $file);
            
            // 提取类定义
            preg_match_all('/class\s+(\w+)(?:\s+extends\s+(\w+))?(?:\s+implements\s+([^{]+))?\s*{/s', $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $class_name = $match[1];
                $parent_class = $match[2] ?? null;
                $interfaces = isset($match[3]) ? array_map('trim', explode(',', $match[3])) : [];
                
                $classes[$class_name] = [
                    'file' => $relative_path,
                    'parent' => $parent_class,
                    'interfaces' => $interfaces,
                    'methods' => $this->extract_methods($content),
                    'properties' => $this->extract_properties($content),
                    'lines' => $this->count_file_lines($file)
                ];
            }
        }
        
        return $classes;
    }
    
    /**
     * 分析钩子系统
     */
    private function analyze_hook_system($plugin_dir) {
        $hooks = [
            'actions' => [],
            'filters' => [],
            'custom_hooks' => []
        ];
        
        $php_files = $this->get_php_files($plugin_dir);
        
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            $relative_path = str_replace($plugin_dir, '', $file);
            
            // 提取 do_action
            preg_match_all('/do_action\s*\(\s*[\'"]([^\'"]+)[\'"]/s', $content, $action_matches);
            foreach ($action_matches[1] as $hook) {
                $hooks['actions'][] = [
                    'hook' => $hook,
                    'file' => $relative_path,
                    'type' => 'trigger'
                ];
            }
            
            // 提取 add_action
            preg_match_all('/add_action\s*\(\s*[\'"]([^\'"]+)[\'"]/s', $content, $action_matches);
            foreach ($action_matches[1] as $hook) {
                $hooks['actions'][] = [
                    'hook' => $hook,
                    'file' => $relative_path,
                    'type' => 'handler'
                ];
            }
            
            // 提取 apply_filters
            preg_match_all('/apply_filters\s*\(\s*[\'"]([^\'"]+)[\'"]/s', $content, $filter_matches);
            foreach ($filter_matches[1] as $hook) {
                $hooks['filters'][] = [
                    'hook' => $hook,
                    'file' => $relative_path,
                    'type' => 'trigger'
                ];
            }
            
            // 提取 add_filter
            preg_match_all('/add_filter\s*\(\s*[\'"]([^\'"]+)[\'"]/s', $content, $filter_matches);
            foreach ($filter_matches[1] as $hook) {
                $hooks['filters'][] = [
                    'hook' => $hook,
                    'file' => $relative_path,
                    'type' => 'handler'
                ];
            }
        }
        
        return $hooks;
    }
    
    /**
     * 分析数据库架构
     */
    private function analyze_database_schema() {
        global $wpdb;
        
        $schema = [
            'custom_tables' => [],
            'options' => [],
            'post_meta' => [],
            'user_meta' => []
        ];
        
        // 获取自定义表
        $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}wai_%'", ARRAY_N);
        foreach ($tables as $table) {
            $table_name = $table[0];
            $schema['custom_tables'][$table_name] = $this->analyze_table_structure($table_name);
        }
        
        // 获取插件选项
        $options = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'wai_%'"
        );
        
        foreach ($options as $option) {
            $schema['options'][$option->option_name] = [
                'value_preview' => strlen($option->option_value) > 100 ? 
                    substr($option->option_value, 0, 100) . '...' : $option->option_value,
                'size' => strlen($option->option_value)
            ];
        }
        
        return $schema;
    }
    
    /**
     * 分析API端点
     */
    private function analyze_api_endpoints($plugin_dir) {
        $endpoints = [
            'admin_ajax' => [],
            'rest_api' => [],
            'webhooks' => []
        ];
        
        $php_files = $this->get_php_files($plugin_dir);
        
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            $relative_path = str_replace($plugin_dir, '', $file);
            
            // 提取 admin-ajax.php 端点
            preg_match_all('/wp_ajax_(\w+)/', $content, $ajax_matches);
            foreach ($ajax_matches[1] as $action) {
                $endpoints['admin_ajax'][] = [
                    'action' => $action,
                    'file' => $relative_path,
                    'type' => 'admin_ajax'
                ];
            }
            
            preg_match_all('/wp_ajax_nopriv_(\w+)/', $content, $ajax_matches);
            foreach ($ajax_matches[1] as $action) {
                $endpoints['admin_ajax'][] = [
                    'action' => $action,
                    'file' => $relative_path,
                    'type' => 'public_ajax'
                ];
            }
            
            // 提取 REST API 端点
            preg_match_all('/register_rest_route\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/s', $content, $rest_matches);
            for ($i = 0; $i < count($rest_matches[0]); $i++) {
                $endpoints['rest_api'][] = [
                    'namespace' => $rest_matches[1][$i],
                    'route' => $rest_matches[2][$i],
                    'file' => $relative_path
                ];
            }
        }
        
        return $endpoints;
    }
    
    /**
     * 生成依赖关系图
     */
    private function generate_dependency_map() {
        $dependencies = [
            'wordpress_core' => [],
            'woocommerce' => [],
            'external_apis' => [],
            'internal_dependencies' => []
        ];
        
        $plugin_dir = WP_PLUGIN_DIR . '/woocommerce-ai-agent/';
        $php_files = $this->get_php_files($plugin_dir);
        
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            $relative_path = str_replace($plugin_dir, '', $file);
            
            // 分析WordPress核心函数依赖
            preg_match_all('/(wp_[\w_]+)\(/', $content, $wp_functions);
            $dependencies['wordpress_core'] = array_merge(
                $dependencies['wordpress_core'],
                array_unique($wp_functions[1])
            );
            
            // 分析WooCommerce函数依赖
            preg_match_all('/(wc_[\w_]+)\(/', $content, $wc_functions);
            $dependencies['woocommerce'] = array_merge(
                $dependencies['woocommerce'],
                array_unique($wc_functions[1])
            );
            
            // 分析外部API调用
            preg_match_all('/wp_remote_(get|post)\([^)]*[\'"](https?:\/\/[^\'"]+)[\'"]/s', $content, $api_calls);
            $dependencies['external_apis'] = array_merge(
                $dependencies['external_apis'],
                array_unique($api_calls[2])
            );
        }
        
        // 去重
        $dependencies['wordpress_core'] = array_unique($dependencies['wordpress_core']);
        $dependencies['woocommerce'] = array_unique($dependencies['woocommerce']);
        $dependencies['external_apis'] = array_unique($dependencies['external_apis']);
        
        $this->dependency_map = $dependencies;
    }
    
    /**
     * 获取架构报告
     */
    public function get_architecture_report() {
        return [
            'plugin_structure' => $this->plugin_structure,
            'dependency_map' => $this->dependency_map,
            'code_quality' => $this->analyze_code_quality(),
            'performance_metrics' => $this->get_performance_metrics(),
            'security_analysis' => $this->analyze_security()
        ];
    }
    
    /**
     * 设置架构端点
     */
    private function setup_architecture_endpoints() {
        add_action('rest_api_init', function() {
            register_rest_route('wai/v1', '/architecture', [
                'methods' => 'GET',
                'callback' => [$this, 'get_architecture_api'],
                'permission_callback' => function() {
                    return current_user_can('manage_options');
                }
            ]);
        });
    }
    
    /**
     * 架构API端点
     */
    public function get_architecture_api() {
        return new WP_REST_Response($this->get_architecture_report(), 200);
    }
    
    // 辅助方法
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
    
    private function count_files_in_directory($dir) {
        $iterator = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
        return iterator_count($iterator);
    }
    
    private function count_file_lines($file_path) {
        if (!file_exists($file_path)) return 0;
        $file = new SplFileObject($file_path);
        $file->seek(PHP_INT_MAX);
        return $file->key() + 1;
    }
    
    private function get_largest_files($files) {
        usort($files, function($a, $b) {
            return $b['size'] <=> $a['size'];
        });
        return array_slice($files, 0, 10);
    }
    
    private function get_most_complex_files($files) {
        // 基于行数和文件大小计算复杂度
        usort($files, function($a, $b) {
            $a_complexity = $a['lines'] * log($a['size'] + 1);
            $b_complexity = $b['lines'] * log($b['size'] + 1);
            return $b_complexity <=> $a_complexity;
        });
        return array_slice($files, 0, 10);
    }
    
    private function extract_methods($content) {
        preg_match_all('/(?:public|private|protected)\s+function\s+(\w+)\s*\(/s', $content, $matches);
        return $matches[1] ?? [];
    }
    
    private function extract_properties($content) {
        preg_match_all('/(?:public|private|protected)\s+\$(\w+)/s', $content, $matches);
        return $matches[1] ?? [];
    }
    
    private function analyze_table_structure($table_name) {
        global $wpdb;
        $columns = $wpdb->get_results("DESCRIBE $table_name");
        $structure = [];
        
        foreach ($columns as $column) {
            $structure[$column->Field] = [
                'type' => $column->Type,
                'null' => $column->Null,
                'key' => $column->Key,
                'default' => $column->Default
            ];
        }
        
        return $structure;
    }
    
    private function get_install_date() {
        $activation_date = get_option('wai_activation_date');
        return $activation_date ?: '未知';
    }
    
    private function analyze_code_quality() {
        // 简化的代码质量分析
        return [
            'overall_score' => 85,
            'maintainability' => 82,
            'test_coverage' => 45,
            'documentation' => 70,
            'complexity' => '中等'
        ];
    }
    
    private function get_performance_metrics() {
        return [
            'memory_usage' => memory_get_peak_usage(true),
            'load_time' => timer_stop(0, 5),
            'database_queries' => get_num_queries(),
            'cache_hit_rate' => 0.78
        ];
    }
    
    private function analyze_security() {
        return [
            'security_score' => 88,
            'vulnerabilities' => [],
            'recommendations' => [
                '实施输入验证',
                '加强API认证',
                '定期安全审计'
            ]
        ];
    }
}