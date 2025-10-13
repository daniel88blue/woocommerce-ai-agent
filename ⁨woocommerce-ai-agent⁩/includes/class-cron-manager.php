<?php
class WC_AI_Agent_Cron_Manager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wc_ai_agent_daily_analysis', array($this, 'run_daily_analysis'));
        add_filter('cron_schedules', array($this, 'add_cron_schedules'));
    }
    
    public function add_cron_schedules($schedules) {
        $schedules['weekly'] = array(
            'interval' => WEEK_IN_SECONDS,
            'display' => __('每周一次')
        );
        
        $schedules['monthly'] = array(
            'interval' => 30 * DAY_IN_SECONDS,
            'display' => __('每月一次')
        );
        
        return $schedules;
    }
    
    public function run_daily_analysis() {
        $settings = WC_AI_Agent_Settings::get_instance()->get_settings();
        
        // 只有在启用自动执行时才运行
        if (!$settings['auto_execute']) {
            return;
        }
        
        $data_collector = WC_AI_Agent_Data_Collector::get_instance();
        $decision_engine = new WC_AI_Agent_Decision_Engine();
        $action_executor = new WC_AI_Agent_Action_Executor();
        
        // 收集数据
        $product_data = $data_collector->collect_product_data();
        
        // 分析决策
        $decisions = $decision_engine->analyze_and_decide($product_data);
        
        // 执行决策
        $results = $action_executor->execute_decisions($decisions);
        
        // 发送通知邮件
        if ($settings['email_notifications'] && !empty($results)) {
            $this->send_analysis_report($results);
        }
    }
    
    private function send_analysis_report($results) {
        $to = get_option('admin_email');
        $subject = 'AI 电商智能体分析报告 - ' . get_bloginfo('name');
        
        $message = "AI 电商智能体分析报告\n\n";
        $message .= "生成时间: " . current_time('mysql') . "\n\n";
        
        $success_count = 0;
        $failed_count = 0;
        
        foreach ($results as $result) {
            if ($result['success']) {
                $success_count++;
                $message .= "✅ " . $result['reason'] . "\n";
            } else {
                $failed_count++;
                $message .= "❌ 操作失败: " . ($result['error'] ?? '未知错误') . "\n";
            }
        }
        
        $message .= "\n总结: 成功 {$success_count} 个操作，失败 {$failed_count} 个操作\n";
        $message .= "\n登录网站后台查看详细报告: " . admin_url('admin.php?page=wc-ai-agent-logs');
        
        wp_mail($to, $subject, $message);
    }
}
?>
