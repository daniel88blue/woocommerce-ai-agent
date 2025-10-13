<?php
/**
 * AI CTO 管理平台 - 集成真实AI API
 */

if (!defined('ABSPATH')) {
    exit;
}

// 防止直接访问
if (!current_user_can('manage_options')) {
    wp_die(__('权限不足', 'woocommerce-ai-agent'));
}

// 获取AI配置
$deepseek_api_key = get_option('wai_deepseek_api_key', '');
$ai_provider = get_option('wai_ai_provider', 'deepseek');
$conversation_history = get_option('wai_cto_conversation', []);

// 处理AI配置保存
if (isset($_POST['save_ai_config'])) {
    check_admin_referer('wai_ai_config');
    
    $deepseek_api_key = sanitize_text_field($_POST['deepseek_api_key'] ?? '');
    $ai_provider = sanitize_text_field($_POST['ai_provider'] ?? 'deepseek');
    
    update_option('wai_deepseek_api_key', $deepseek_api_key);
    update_option('wai_ai_provider', $ai_provider);
    
    echo '<div class="notice notice-success"><p>AI配置已保存！</p></div>';
}

/**
 * 调用AI API的函数
 */
function call_ai_api($message) {
    $api_key = get_option('wai_deepseek_api_key', '');
    $provider = get_option('wai_ai_provider', 'deepseek');
    
    if (empty($api_key)) {
        return "请先配置DeepSeek API密钥。访问 https://platform.deepseek.com 获取密钥，然后在右侧配置面板中填入。";
    }
    
    $url = 'https://api.deepseek.com/v1/chat/completions';
    
    $body = [
        'model' => 'deepseek-chat',
        'messages' => [
            [
                'role' => 'system',
                'content' => "你是专业的AI首席技术官(CTO)，负责电商平台的技术战略管理。请以专业CTO的身份提供具体可执行的技术建议、架构设计、代码审查、性能优化方案。用中文回复，保持专业但易于理解。"
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'stream' => false,
        'temperature' => 0.7,
        'max_tokens' => 2000
    ];
    
    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ],
        'body' => json_encode($body),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        return "AI服务请求失败: " . $response->get_error_message();
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);
    
    if ($response_code !== 200) {
        return "AI服务HTTP错误: {$response_code} - " . ($data['error']['message'] ?? '未知错误');
    }
    
    if (isset($data['choices'][0]['message']['content'])) {
        return $data['choices'][0]['message']['content'];
    }
    
    if (isset($data['error']['message'])) {
        return "AI服务错误: " . $data['error']['message'];
    }
    
    return "AI响应解析失败，请检查API配置。响应数据: " . substr($response_body, 0, 200);
}

// 强制加载消息平台基础文件
$message_platform_file = WAI_PLUGIN_DIR . 'includes/messaging/class-message-platform.php';
if (file_exists($message_platform_file)) {
    require_once $message_platform_file;
}

$message_manager_file = WAI_PLUGIN_DIR . 'includes/messaging/class-message-manager.php';
if (file_exists($message_manager_file)) {
    require_once $message_manager_file;
}

// 检查类是否存在
if (class_exists('WAI_Message_Manager')) {
    $manager = WAI_Message_Manager::get_instance();
    $status = $manager->get_platforms_status();
} else {
    $status = [
        'wechat' => ['name' => '企业微信机器人', 'connected' => false],
        'slack' => ['name' => 'Slack Webhook', 'connected' => false],
        'discord' => ['name' => 'Discord Webhook', 'connected' => false]
    ];
}
?>

<div class="wrap">
    <h1>🤖 AI CTO - 战略管理控制台</h1>
    
    <div class="wai-cto-dashboard">
        <!-- AI CTO 聊天界面 -->
        <div class="cto-chat-section">
            <div class="chat-header">
                <div class="ai-avatar">
                    <div class="avatar-icon">👨‍💼</div>
                    <div class="ai-info">
                        <h3>AI CTO</h3>
                        <span class="ai-status">
                            <?php echo $deepseek_api_key ? '✅ 已连接DeepSeek AI' : '❌ 未配置AI服务'; ?>
                        </span>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="button button-secondary" onclick="clearConversation()">🗑️ 清空对话</button>
                    <button class="button button-secondary" onclick="testAIConnection()">🧪 测试AI连接</button>
                </div>
            </div>
            
            <div class="chat-container">
                <div class="chat-messages" id="chat-messages">
                    <!-- 欢迎消息 -->
                    <div class="message ai-message">
                        <div class="message-avatar">👨‍💼</div>
                        <div class="message-content">
                            <div class="message-text">
                                <strong>AI CTO:</strong> 
                                <?php if ($deepseek_api_key): ?>
                                    您好！我已连接DeepSeek AI，可以为您提供专业的技术战略管理服务。
                                    <br><br>
                                    <strong>我可以帮助您：</strong>
                                    <ul>
                                        <li>🏗️ 系统架构设计和审查</li>
                                        <li>📅 技术路线图规划</li>
                                        <li>🔍 代码审查和优化建议</li>
                                        <li>🚀 性能优化策略</li>
                                        <li>💡 技术决策支持</li>
                                    </ul>
                                <?php else: ?>
                                    请先配置DeepSeek API密钥以启用真正的AI CTO功能。
                                    <br><br>
                                    <strong>配置步骤：</strong>
                                    <ol>
                                        <li>访问 <a href="https://platform.deepseek.com" target="_blank">DeepSeek平台</a> 获取API密钥</li>
                                        <li>在右侧"AI服务配置"中填入API密钥</li>
                                        <li>保存配置后即可开始对话</li>
                                    </ol>
                                <?php endif; ?>
                            </div>
                            <div class="message-time"><?php echo date('H:i'); ?></div>
                        </div>
                    </div>
                    
                    <!-- 对话历史 -->
                    <?php foreach (array_slice($conversation_history, -10) as $message): ?>
                        <div class="message <?php echo $message['type']; ?>-message">
                            <div class="message-avatar">
                                <?php echo $message['type'] === 'ai' ? '👨‍💼' : '👤'; ?>
                            </div>
                            <div class="message-content">
                                <div class="message-text">
                                    <?php if ($message['type'] === 'ai'): ?>
                                        <strong>AI CTO:</strong> 
                                    <?php else: ?>
                                        <strong>您:</strong> 
                                    <?php endif; ?>
                                    <?php echo nl2br(esc_html($message['content'])); ?>
                                </div>
                                <div class="message-time"><?php echo date('H:i', strtotime($message['timestamp'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="chat-input-container">
                    <div class="quick-actions">
                        <button class="quick-action-btn" onclick="insertQuickQuestion('分析当前技术架构')">🏗️ 架构分析</button>
                        <button class="quick-action-btn" onclick="insertQuickQuestion('制定技术路线图')">📅 技术规划</button>
                        <button class="quick-action-btn" onclick="insertQuickQuestion('代码审查建议')">🔍 代码审查</button>
                        <button class="quick-action-btn" onclick="insertQuickQuestion('性能优化策略')">🚀 性能优化</button>
                    </div>
                    <div class="input-group">
                        <textarea 
                            id="chat-input" 
                            placeholder="<?php echo $deepseek_api_key ? '向AI CTO提问技术战略问题...' : '请先配置DeepSeek API密钥...'; ?>" 
                            rows="3"
                            <?php echo !$deepseek_api_key ? 'disabled' : ''; ?>
                        ></textarea>
                        <button class="button button-primary" onclick="sendMessage()" <?php echo !$deepseek_api_key ? 'disabled' : ''; ?>>
                            <?php echo $deepseek_api_key ? '发送' : '未配置AI'; ?>
                        </button>
                    </div>
                    <?php if (!$deepseek_api_key): ?>
                    <div class="config-notice">
                        <p>⚠️ 请先配置DeepSeek API密钥以启用AI对话功能</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 右侧功能面板 -->
        <div class="cto-sidebar">
            <!-- AI服务配置 -->
            <div class="sidebar-panel">
                <h3>🔧 AI服务配置</h3>
                <form method="post">
                    <?php wp_nonce_field('wai_ai_config'); ?>
                    <div class="form-group">
                        <label for="ai_provider">AI提供商</label>
                        <select id="ai_provider" name="ai_provider">
                            <option value="deepseek" <?php selected($ai_provider, 'deepseek'); ?>>DeepSeek (推荐)</option>
                            <option value="openai" <?php selected($ai_provider, 'openai'); ?>>OpenAI</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="deepseek_api_key">DeepSeek API密钥</label>
                        <input type="password" id="deepseek_api_key" name="deepseek_api_key" 
                               value="<?php echo esc_attr($deepseek_api_key); ?>" 
                               placeholder="输入DeepSeek API密钥">
                        <p class="description">
                            <a href="https://platform.deepseek.com/api_keys" target="_blank">获取API密钥</a>
                        </p>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="save_ai_config" class="button button-primary">保存配置</button>
                        <button type="button" class="button" onclick="testAIConnection()">测试连接</button>
                    </div>
                </form>
            </div>

            <!-- 系统状态 -->
            <div class="sidebar-panel">
                <h3>📈 系统状态</h3>
                <div class="status-grid">
                    <div class="status-item">
                        <span class="status-label">AI服务状态</span>
                        <span class="status-value <?php echo $deepseek_api_key ? 'excellent' : 'error'; ?>">
                            <?php echo $deepseek_api_key ? '已配置' : '未配置'; ?>
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">对话历史</span>
                        <span class="status-value"><?php echo count($conversation_history); ?> 条</span>
                    </div>
                </div>
            </div>

            <!-- 消息平台状态 -->
            <div class="sidebar-panel">
                <h3>🔗 消息平台</h3>
                <div class="platforms-status">
                    <?php foreach ($status as $platform_id => $info): ?>
                    <div class="platform-status-item">
                        <span class="platform-name"><?php echo esc_html($info['name']); ?></span>
                        <span class="platform-status <?php echo $info['connected'] ? 'connected' : 'disconnected'; ?>">
                            <?php echo $info['connected'] ? '✅' : '❌'; ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 快速开始 -->
            <div class="sidebar-panel">
                <h3>🚀 快速开始</h3>
                <div class="quick-start-actions">
                    <button class="button button-small" onclick="insertQuickQuestion('分析当前系统架构并提出改进建议')">架构审查</button>
                    <button class="button button-small" onclick="insertQuickQuestion('制定下个季度的技术路线图')">技术规划</button>
                    <button class="button button-small" onclick="insertQuickQuestion('评估当前技术债务和优化优先级')">技术债务</button>
                    <button class="button button-small" onclick="insertQuickQuestion('设计微服务架构迁移方案')">架构设计</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 生成随机nonce（因为模板文件中无法使用wp_create_nonce）
function generateNonce() {
    return Math.random().toString(36).substring(2) + Date.now().toString(36);
}

// AI CTO 聊天功能
function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    
    if (!message) {
        alert('请输入消息');
        return;
    }
    
    // 添加用户消息到聊天界面
    addMessageToChat('user', message);
    input.value = '';
    
    // 显示AI思考中
    const thinkingMsg = addMessageToChat('ai', '🤔 AI思考中...', true);
    
    // 调用真实的AI API
    const formData = new FormData();
    formData.append('action', 'wai_chat_with_ai');
    formData.append('message', message);
    formData.append('nonce', generateNonce());
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // 移除思考中消息
        thinkingMsg.remove();
        
        if (data.success) {
            addMessageToChat('ai', data.response);
        } else {
            addMessageToChat('ai', '❌ AI服务错误: ' + (data.data || '未知错误'));
        }
        
        // 自动滚动到底部
        scrollToBottom();
    })
    .catch(error => {
        thinkingMsg.remove();
        addMessageToChat('ai', '❌ 网络错误: ' + error.message);
        scrollToBottom();
    });
}

function addMessageToChat(type, content, isThinking = false) {
    const chatMessages = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}-message ${isThinking ? 'thinking' : ''}`;
    
    const avatar = type === 'ai' ? '👨‍💼' : '👤';
    const sender = type === 'ai' ? 'AI CTO:' : '您:';
    
    messageDiv.innerHTML = `
        <div class="message-avatar">${avatar}</div>
        <div class="message-content">
            <div class="message-text">
                <strong>${sender}</strong> ${isThinking ? content : nl2br(escapeHtml(content))}
            </div>
            ${!isThinking ? `<div class="message-time">${new Date().toLocaleTimeString('zh-CN', {hour: '2-digit', minute:'2-digit'})}</div>` : ''}
        </div>
    `;
    
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
    
    return messageDiv;
}

function testAIConnection() {
    const testMsg = addMessageToChat('ai', '🧪 测试AI连接中...', true);
    
    const formData = new FormData();
    formData.append('action', 'wai_chat_with_ai');
    formData.append('message', '测试连接，请回复"AI CTO连接成功"');
    formData.append('nonce', generateNonce());
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        testMsg.remove();
        
        if (data.success) {
            addMessageToChat('ai', '✅ ' + data.response);
        } else {
            addMessageToChat('ai', '❌ 连接测试失败: ' + (data.data || '未知错误'));
        }
    })
    .catch(error => {
        testMsg.remove();
        addMessageToChat('ai', '❌ 测试请求失败: ' + error.message);
    });
}

function clearConversation() {
    if (confirm('确定要清空对话历史吗？')) {
        const formData = new FormData();
        formData.append('action', 'wai_clear_conversation');
        formData.append('nonce', generateNonce());
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        }).then(() => {
            location.reload();
        });
    }
}

// 工具函数
function nl2br(str) {
    return str.replace(/\n/g, '<br>');
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function scrollToBottom() {
    const chatMessages = document.getElementById('chat-messages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function insertQuickQuestion(question) {
    document.getElementById('chat-input').value = question;
}

// 键盘快捷键
document.getElementById('chat-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && e.ctrlKey) {
        sendMessage();
    }
});

// 初始化
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
});
</script>

<style>
/* 保持原有的CSS样式 */
.config-notice {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 12px;
    margin-top: 10px;
    text-align: center;
}

.config-notice p {
    margin: 0;
    color: #856404;
    font-size: 14px;
}

.quick-start-actions {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.status-value.error {
    color: #dc3545;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #2c3338;
    font-size: 14px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group .description {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.form-group .description a {
    color: #0073aa;
}

.form-actions {
    margin-top: 15px;
    display: flex;
    gap: 8px;
}

.wai-cto-dashboard {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.cto-chat-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
}

.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ai-avatar {
    display: flex;
    align-items: center;
    gap: 15px;
}

.avatar-icon {
    font-size: 40px;
}

.ai-info h3 {
    margin: 0;
    font-size: 20px;
}

.ai-status {
    font-size: 14px;
    opacity: 0.9;
}

.chat-container {
    height: 600px;
    display: flex;
    flex-direction: column;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8f9fa;
}

.message {
    display: flex;
    margin-bottom: 20px;
    gap: 12px;
}

.message-avatar {
    font-size: 24px;
    flex-shrink: 0;
}

.message-content {
    flex: 1;
    background: white;
    padding: 12px 16px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.user-message .message-content {
    background: #007cba;
    color: white;
}

.message-text {
    line-height: 1.5;
    margin-bottom: 8px;
}

.message-time {
    font-size: 12px;
    color: #666;
    text-align: right;
}

.user-message .message-time {
    color: rgba(255,255,255,0.8);
}

.chat-input-container {
    border-top: 1px solid #e0e0e0;
    padding: 20px;
    background: white;
}

.quick-actions {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.quick-action-btn {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    background: #007cba;
    color: white;
}

.input-group {
    display: flex;
    gap: 12px;
}

#chat-input {
    flex: 1;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    resize: vertical;
    font-family: inherit;
}

.cto-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-panel {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.sidebar-panel h3 {
    margin: 0 0 15px 0;
    color: #2c3338;
    font-size: 16px;
}

.status-grid {
    display: grid;
    gap: 10px;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.status-item:last-child {
    border-bottom: none;
}

.status-label {
    color: #666;
    font-size: 14px;
}

.status-value {
    font-weight: bold;
    color: #2c3338;
}

.status-value.excellent {
    color: #00d26a;
}

.platforms-status {
    margin-bottom: 15px;
}

.platform-status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.platform-status-item:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .wai-cto-dashboard {
        grid-template-columns: 1fr;
    }
    
    .chat-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .input-group {
        flex-direction: column;
    }
}
</style>