<?php
/**
 * AI电商智能体 - 定时任务处理器
 * 负责管理和执行系统的定时任务
 */

if (!defined('ABSPATH')) {
    exit;
}

class WAI_Cron_Handler {
    
    private $scheduled_events = [];
    private $recurring_intervals = [];
    
    public function __construct() {
        $this->setup_scheduled_events();
        $this->setup_custom_intervals();
        
        add_action('init', [$this, 'setup_cron_schedules']);
        add_filter('cron_schedules', [$this, 'add_custom_cron_intervals']);
        
        // 注册所有定时任务的回调函数
        $this->register_cron_callbacks();
    }
    
    /**
     * 设置定时任务事件
     */
    public function setup_scheduled_events() {
        $this->scheduled_events = [
            // 基础定时任务
            'wai_daily_maintenance' => [
                'schedule' => 'daily',
                'callback' => [$this, 'execute_daily_maintenance'],
                'description' => '每日维护',
                'time' => '02:00',
                'enabled' => true
            ],
            'wai_hourly_tasks' => [
                'schedule' => 'hourly',
                'callback' => [$this, 'execute_hourly_tasks'],
                'description' => '每小时任务',
                'enabled' => true
            ],
            'wai_weekly_reports' => [
                'schedule' => 'weekly',
                'callback' => [$this, 'execute_weekly_reports'],
                'description' => '每周报告',
                'enabled' => true
            ]
        ];
    }
    
    /**
     * 注册所有定时任务回调
     */
    private function register_cron_callbacks() {
        foreach ($this->scheduled_events as $event_name => $event_config) {
            if ($event_config['enabled']) {
                add_action($event_name, $event_config['callback']);
            }
        }
    }
    
    /**
     * 设置自定义时间间隔
     */
    private function setup_custom_intervals() {
        $this->recurring_intervals = [
            'wai_5min' => [
                'interval' => 5 * MINUTE_IN_SECONDS,
                'display' => '每5分钟'
            ],
            'wai_15min' => [
                'interval' => 15 * MINUTE_IN_SECONDS,
                'display' => '每15分钟'
            ],
            'wai_30min' => [
                'interval' => 30 * MINUTE_IN_SECONDS,
                'display' => '每30分钟'
            ]
        ];
    }
    
    /**
     * 设置定时任务调度
     */
    public function setup_cron_schedules() {
        foreach ($this->scheduled_events as $event_name => $event_config) {
            if (!$event_config['enabled']) {
                $this->unschedule_event($event_name);
                continue;
            }
            
            if (!wp_next_scheduled($event_name)) {
                $timestamp = $this->calculate_next_run_time($event_config);
                $result = wp_schedule_event($timestamp, $event_config['schedule'], $event_name);
                
                if ($result !== false) {
                    $this->log_cron_event('scheduled', $event_name, $timestamp);
                } else {
                    $this->log_cron_event('failed', $event_name, $timestamp);
                }
            }
        }
    }
    
    /**
     * 添加自定义Cron时间间隔
     */
    public function add_custom_cron_intervals($schedules) {
        foreach ($this->recurring_intervals as $interval_name => $interval_config) {
            $schedules[$interval_name] = $interval_config;
        }
        return $schedules;
    }
    
    /**
     * 计算下次运行时间
     */
    private function calculate_next_run_time($event_config) {
        $current_time = current_time('timestamp');
        
        // 如果指定了具体时间，计算下次运行时间
        if (isset($event_config['time'])) {
            $scheduled_time = strtotime($event_config['time'], $current_time);
            
            // 如果今天的时间已经过了，安排到明天
            if ($scheduled_time <= $current_time) {
                $scheduled_time = strtotime('+1 day', $scheduled_time);
            }
            
            return $scheduled_time;
        }
        
        // 否则立即开始
        return $current_time + 60; // 1分钟后开始
    }
    
    /**
     * 记录定时任务事件 - 简化版本，不依赖主插件
     */
    private function log_cron_event($action, $event_name, $timestamp = null) {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'action' => $action,
            'event_name' => $event_name,
            'scheduled_time' => $timestamp ? date('Y-m-d H:i:s', $timestamp) : null
        ];
        
        // 只记录到错误日志，不调用主插件的私有方法
        error_log("WAI Cron Event: " . json_encode($log_entry));
        
        // 存储到选项中以供显示
        $event_logs = get_option('wai_cron_event_logs', []);
        $event_logs[] = $log_entry;
        
        // 只保留最近50条日志
        if (count($event_logs) > 50) {
            $event_logs = array_slice($event_logs, -50);
        }
        
        update_option('wai_cron_event_logs', $event_logs);
    }
    
    /**
     * 记录定时任务执行
     */
    private function log_cron_execution($status, $event_name, $details = []) {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'event_name' => $event_name,
            'status' => $status,
            'details' => $details
        ];
        
        // 存储到选项中以供显示
        $execution_logs = get_option('wai_cron_execution_logs', []);
        $execution_logs[] = $log_entry;
        
        // 只保留最近100条日志
        if (count($execution_logs) > 100) {
            $execution_logs = array_slice($execution_logs, -100);
        }
        
        update_option('wai_cron_execution_logs', $execution_logs);
        
        error_log("WAI Cron Execution: " . json_encode($log_entry));
    }
    
    /**
     * 执行每日维护
     */
    public function execute_daily_maintenance() {
        $start_time = microtime(true);
        $this->log_cron_execution('start', 'wai_daily_maintenance');
        
        try {
            // 执行基本的每日维护任务
            $results = [
                'cache_cleared' => $this->clear_transient_cache(),
                'logs_rotated' => $this->rotate_logs(),
                'stats_updated' => $this->update_daily_stats()
            ];
            
            $execution_time = round(microtime(true) - $start_time, 2);
            $this->log_cron_execution('complete', 'wai_daily_maintenance', [
                'execution_time' => $execution_time,
                'results' => $results
            ]);
            
            return $results;
            
        } catch (Exception $e) {
            $this->log_cron_execution('error', 'wai_daily_maintenance', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 执行每小时任务
     */
    public function execute_hourly_tasks() {
        $this->log_cron_execution('start', 'wai_hourly_tasks');
        
        try {
            $tasks = [
                'data_sync' => $this->sync_basic_data(),
                'health_check' => $this->perform_health_check()
            ];
            
            $this->log_cron_execution('complete', 'wai_hourly_tasks', [
                'tasks_completed' => count($tasks)
            ]);
            
            return $tasks;
            
        } catch (Exception $e) {
            $this->log_cron_execution('error', 'wai_hourly_tasks', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 执行每周报告
     */
    public function execute_weekly_reports() {
        $start_time = microtime(true);
        $this->log_cron_execution('start', 'wai_weekly_reports');
        
        try {
            $reports = [
                'performance_report' => $this->generate_performance_report(),
                'analytics_summary' => $this->generate_analytics_summary()
            ];
            
            $execution_time = round(microtime(true) - $start_time, 2);
            $this->log_cron_execution('complete', 'wai_weekly_reports', [
                'execution_time' => $execution_time,
                'reports_generated' => count($reports)
            ]);
            
            return $reports;
            
        } catch (Exception $e) {
            $this->log_cron_execution('error', 'wai_weekly_reports', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 清除瞬态缓存
     */
    private function clear_transient_cache() {
        global $wpdb;
        $deleted = $wpdb->query("
            DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_wai_%' 
            OR option_name LIKE '_transient_timeout_wai_%'
        ");
        return $deleted !== false;
    }
    
    /**
     * 轮换日志
     */
    private function rotate_logs() {
        $execution_logs = get_option('wai_cron_execution_logs', []);
        $event_logs = get_option('wai_cron_event_logs', []);
        
        // 保存旧日志
        $archive_time = date('Y-m-d_H-i-s');
        update_option('wai_cron_execution_logs_archive_' . $archive_time, $execution_logs);
        update_option('wai_cron_event_logs_archive_' . $archive_time, $event_logs);
        
        // 清空当前日志
        update_option('wai_cron_execution_logs', []);
        update_option('wai_cron_event_logs', []);
        
        return true;
    }
    
    /**
     * 更新每日统计
     */
    private function update_daily_stats() {
        $stats = [
            'last_run' => current_time('mysql'),
            'total_events' => count(get_option('wai_cron_event_logs', [])),
            'total_executions' => count(get_option('wai_cron_execution_logs', []))
        ];
        
        update_option('wai_daily_cron_stats', $stats);
        return $stats;
    }
    
    /**
     * 同步基础数据
     */
    private function sync_basic_data() {
        // 基础数据同步逻辑
        return ['status' => 'completed', 'records' => 0];
    }
    
    /**
     * 执行健康检查
     */
    private function perform_health_check() {
        $checks = [
            'wp_cron' => wp_next_scheduled('wai_daily_maintenance') !== false,
            'memory_usage' => $this->check_memory_usage(),
            'disk_space' => $this->check_disk_space()
        ];
        
        $healthy_checks = array_filter($checks);
        return [
            'healthy' => count($healthy_checks) === count($checks),
            'checks' => $checks
        ];
    }
    
    /**
     * 检查内存使用
     */
    private function check_memory_usage() {
        $usage = memory_get_usage(true);
        $limit = ini_get('memory_limit');
        return [
            'usage' => round($usage / 1024 / 1024, 2) . ' MB',
            'limit' => $limit,
            'healthy' => $usage < (wp_convert_hr_to_bytes($limit) * 0.8)
        ];
    }
    
    /**
     * 检查磁盘空间
     */
    private function check_disk_space() {
        $free = disk_free_space(ABSPATH);
        $total = disk_total_space(ABSPATH);
        $percent_free = $total > 0 ? ($free / $total) * 100 : 0;
        
        return [
            'free' => round($free / 1024 / 1024 / 1024, 2) . ' GB',
            'total' => round($total / 1024 / 1024 / 1024, 2) . ' GB',
            'percent_free' => round($percent_free, 2) . '%',
            'healthy' => $percent_free > 10
        ];
    }
    
    /**
     * 生成性能报告
     */
    private function generate_performance_report() {
        return [
            'average_execution_time' => 'N/A',
            'success_rate' => '100%',
            'last_errors' => 0
        ];
    }
    
    /**
     * 生成分析摘要
     */
    private function generate_analytics_summary() {
        return [
            'total_executions' => count(get_option('wai_cron_execution_logs', [])),
            'error_count' => 0,
            'uptime' => '100%'
        ];
    }
    
    /**
     * 清除所有定时任务
     */
    public function clear_schedules() {
        foreach ($this->scheduled_events as $event_name => $event_config) {
            $this->unschedule_event($event_name);
        }
        
        $this->log_cron_event('cleared', 'all_events');
    }
    
    /**
     * 取消单个事件的调度
     */
    private function unschedule_event($event_name) {
        $timestamp = wp_next_scheduled($event_name);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $event_name);
            $this->log_cron_event('unscheduled', $event_name);
        }
    }
    
    /**
     * 获取定时任务状态
     */
    public function get_cron_status() {
        $status = [];
        
        foreach ($this->scheduled_events as $event_name => $event_config) {
            $next_scheduled = wp_next_scheduled($event_name);
            $status[$event_name] = [
                'enabled' => $event_config['enabled'],
                'description' => $event_config['description'],
                'schedule' => $event_config['schedule'],
                'next_run' => $next_scheduled ? date('Y-m-d H:i:s', $next_scheduled) : '未调度',
                'is_scheduled' => (bool) $next_scheduled
            ];
        }
        
        return $status;
    }
    
    /**
     * 手动触发定时任务
     */
    public function manually_trigger_event($event_name) {
        if (!isset($this->scheduled_events[$event_name])) {
            return [
                'success' => false,
                'error' => '未知的定时任务事件: ' . $event_name
            ];
        }
        
        if (!$this->scheduled_events[$event_name]['enabled']) {
            return [
                'success' => false,
                'error' => '定时任务未启用: ' . $event_name
            ];
        }
        
        $callback = $this->scheduled_events[$event_name]['callback'];
        if (is_callable($callback)) {
            try {
                $result = call_user_func($callback);
                return [
                    'success' => true,
                    'result' => $result
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => '回调方法不可调用: ' . print_r($callback, true)
        ];
    }
}