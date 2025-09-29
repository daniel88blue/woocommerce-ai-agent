<?php
// 防止直接访问
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// 清理选项
delete_option('wc_ai_agent_settings');

// 清理数据库表
global $wpdb;
$table_name = $wpdb->prefix . 'wc_ai_agent_logs';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// 清理定时任务
wp_clear_scheduled_hook('wc_ai_agent_daily_analysis');
