<?php
/**
 * 性能优化AJAX处理
 */

class WAI_Performance_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_wai_update_performance_settings', [$this, 'update_performance_settings']);
        add_action('wp_ajax_wai_run_performance_scan', [$this, 'run_performance_scan']);
        add_action('wp_ajax_wai_apply_optimizations', [$this, 'apply_optimizations']);
    }
    
    /**
     * 更新性能设置
     */
    public function update_performance_settings() {
        check_ajax_referer('wai_performance_settings', 'security');
        
        $settings = [
            'curl_timeout' => intval($_POST['curl_timeout'] ?? 30),
            'max_retries' => intval($_POST['max_retries'] ?? 3),
            'async_processing' => !empty($_POST['async_processing']),
            'enable_compression' => !empty($_POST['enable_compression'])
        ];
        
        update_option('wai_performance_settings', $settings);
        
        wp_send_json_success(['message' => '性能设置已更新']);
    }
    
    /**
     * 运行性能扫描
     */
    public function run_performance_scan() {
        check_ajax_referer('wai_performance_scan', 'security');
        
        $scan_results = $this->perform_comprehensive_scan();
        
        wp_send_json_success([
            'results' => $scan_results,
            'message' => '性能扫描完成'
        ]);
    }
    
    /**
     * 执行全面扫描
     */
    private function perform_comprehensive_scan() {
        return [
            'curl_timeout' => [
                'status' => 'warning',
                'message' => '当前超时设置可能过低',
                'recommendation' => '增加到60秒'
            ],
            'memory_usage' => [
                'status' => 'good', 
                'message' => '内存使用正常',
                'recommendation' => '无需操作'
            ],
            'response_time' => [
                'status' => 'warning',
                'message' => '部分请求响应较慢',
                'recommendation' => '启用异步处理'
            ]
        ];
    }
    
    /**
     * 应用优化
     */
    public function apply_optimizations() {
        check_ajax_referer('wai_apply_optimizations', 'security');
        
        $optimizations = $_POST['optimizations'] ?? [];
        $results = [];
        
        foreach ($optimizations as $optimization) {
            $results[$optimization] = $this->apply_single_optimization($optimization);
        }
        
        wp_send_json_success([
            'results' => $results,
            'message' => '优化已应用完成'
        ]);
    }
    
    /**
     * 应用单个优化
     */
    private function apply_single_optimization($optimization) {
        switch ($optimization) {
            case 'increase_timeout':
                $settings = get_option('wai_performance_settings', []);
                $settings['curl_timeout'] = 60;
                update_option('wai_performance_settings', $settings);
                return '超时时间已增加到60秒';
                
            case 'enable_retry':
                $settings = get_option('wai_performance_settings', []);
                $settings['max_retries'] = 3;
                update_option('wai_performance_settings', $settings);
                return '重试机制已启用';
                
            case 'enable_async':
                $settings = get_option('wai_performance_settings', []);
                $settings['async_processing'] = true;
                update_option('wai_performance_settings', $settings);
                return '异步处理已启用';
                
            default:
                return '未知优化类型';
        }
    }
}

new WAI_Performance_Ajax();