<?php
if (!defined('ABSPATH')) {
    exit;
}

class WAI_AI_CTO_Platform {
    private static $instance = null;
    private $api_key;
    private $api_url = 'https://api.deepseek.com/v1/chat/completions';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->api_key = get_option('wai_deepseek_api_key', '');
        $this->init();
    }
    
    /**
     * 初始化平台
     */
    public function init() {
        // 注册AJAX处理
        add_action('wp_ajax_wai_chat_with_ai', array($this, 'ajax_chat_with_ai'));
        add_action('wp_ajax_wai_clear_conversation', array($this, 'ajax_clear_conversation'));
        add_action('wp_ajax_wai_test_ai_connection', array($this, 'ajax_test_ai_connection'));
        add_action('wp_ajax_wai_save_ai_config', array($this, 'ajax_save_ai_config'));
        
        // 注册管理菜单
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // 注册脚本和样式
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * 添加管理菜单
     */
    public function add_admin_menu() {
        add_submenu_page(
            'wai-dashboard',
            __('AI CTO消息平台', 'woocommerce-ai-agent'),
            __('AI CTO', 'woocommerce-ai-agent'),
            'manage_options',
            'wai-messaging-platforms',
            array($this, 'render_platform_page')
        );
    }
    
    /**
     * 渲染平台页面
     */
    public function render_platform_page() {
        // 获取当前设置
        $deepseek_api_key = get_option('wai_deepseek_api_key', '');
        $ai_provider = get_option('wai_ai_provider', 'deepseek');
        $conversation_history = get_option('wai_cto_conversation', []);
        ?>
        <div class="wrap">
            <h1>🤖 AI CTO消息平台</h1>
            
            <div class="wai-messaging-container">
                <!-- 配置面板 -->
                <div class="wai-config-panel">
                    <div class="config-section">
                        <h3>⚙️ AI服务配置</h3>
                        
                        <div class="config-item">
                            <label for="ai_provider">AI服务提供商</label>
                            <select id="ai_provider" name="ai_provider">
                                <option value="deepseek" <?php selected($ai_provider, 'deepseek'); ?>>DeepSeek</option>
                                <option value="openai" <?php selected($ai_provider, 'openai'); ?>>OpenAI</option>
                            </select>
                        </div>
                        
                        <div class="config-item">
                            <label for="deepseek_api_key">DeepSeek API密钥</label>
                            <input type="password" id="deepseek_api_key" name="deepseek_api_key" 
                                   value="<?php echo esc_attr($deepseek_api_key); ?>" 
                                   placeholder="输入您的DeepSeek API密钥" 
                                   style="width: 100%; max-width: 400px;">
                            <p class="description">
                                🔑 获取API密钥: 
                                <a href="https://platform.deepseek.com/api_keys" target="_blank" rel="noopener">
                                    https://platform.deepseek.com/api_keys
                                </a>
                            </p>
                        </div>
                        
                        <div class="config-actions">
                            <button type="button" class="button button-primary" onclick="saveAIConfig()">
                                💾 保存配置
                            </button>
                            <button type="button" class="button" onclick="testAIConnection()">
                                🔗 测试连接
                            </button>
                            <button type="button" class="button button-secondary" onclick="clearConversation()">
                                🗑️ 清空对话
                            </button>
                        </div>
                        
                        <div id="connection-status" style="margin-top: 15px;"></div>
                    </div>
                </div>

                <!-- 聊天界面 -->
                <div class="wai-chat-interface">
                    <div class="chat-header">
                        <h3>💬 AI CTO对话</h3>
                        <div class="chat-info">
                            <span class="model-info">模型: DeepSeek Chat</span>
                            <span class="message-count">消息: <?php echo count($conversation_history); ?></span>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chat-messages">
                        <?php if (empty($conversation_history)): ?>
                            <div class="empty-chat">
                                <p>👋 欢迎使用AI CTO消息平台！</p>
                                <p>请先配置API密钥，然后开始与技术总监对话。</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversation_history as $message): ?>
                                <div class="message <?php echo $message['type']; ?>">
                                    <div class="message-avatar">
                                        <?php echo $message['type'] === 'user' ? '👤' : '🤖'; ?>
                                    </div>
                                    <div class="message-content">
                                        <div class="message-text"><?php echo nl2br(esc_html($message['content'])); ?></div>
                                        <div class="message-time">
                                            <?php echo date('H:i', strtotime($message['timestamp'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="chat-input">
                        <textarea id="chat-input" placeholder="向AI CTO提问技术问题..." rows="3"></textarea>
                        <button type="button" class="button button-primary" onclick="sendMessage()" id="send-button">
                            📤 发送
                        </button>
                    </div>
                    
                    <div class="chat-suggestions">
                        <h4>💡 快速提问建议</h4>
                        <div class="suggestion-buttons">
                            <button type="button" class="button button-small" onclick="insertSuggestion('如何优化网站性能？')">
                                性能优化
                            </button>
                            <button type="button" class="button button-small" onclick="insertSuggestion('推荐电商技术架构')">
                                技术架构
                            </button>
                            <button type="button" class="button button-small" onclick="insertSuggestion('Web3集成方案')">
                                Web3集成
                            </button>
                            <button type="button" class="button button-small" onclick="insertSuggestion('代码审查建议')">
                                代码审查
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // 保存AI配置
        function saveAIConfig() {
            const apiKey = document.getElementById('deepseek_api_key').value;
            const provider = document.getElementById('ai_provider').value;
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=wai_save_ai_config&api_key=${encodeURIComponent(apiKey)}&provider=${provider}&nonce=<?php echo wp_create_nonce('wai_ajax_nonce'); ?>`
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      showMessage('success', '配置已保存');
                  } else {
                      showMessage('error', data.message || '保存失败');
                  }
              });
        }

        // 测试AI连接
        function testAIConnection() {
            const statusDiv = document.getElementById('connection-status');
            statusDiv.innerHTML = '<div class="notice notice-info"><p>🔗 测试连接中...</p></div>';
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=wai_test_ai_connection&nonce=<?php echo wp_create_nonce('wai_ajax_nonce'); ?>'
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      statusDiv.innerHTML = `<div class="notice notice-success">
                          <p>✅ ${data.message}</p>
                          ${data.response ? `<p><strong>测试回复:</strong> ${data.response}</p>` : ''}
                      </div>`;
                  } else {
                      statusDiv.innerHTML = `<div class="notice notice-error">
                          <p>❌ ${data.message}</p>
                      </div>`;
                  }
              });
        }

        // 发送消息
        function sendMessage() {
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            const sendButton = document.getElementById('send-button');
            
            if (!message) return;
            
            // 禁用发送按钮
            sendButton.disabled = true;
            sendButton.textContent = '发送中...';
            
            // 添加用户消息到界面
            addMessageToChat('user', message);
            input.value = '';
            
            // 滚动到底部
            scrollToBottom();
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=wai_chat_with_ai&message=${encodeURIComponent(message)}&nonce=<?php echo wp_create_nonce('wai_ajax_nonce'); ?>`
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      addMessageToChat('ai', data.response);
                  } else {
                      addMessageToChat('ai', `❌ 错误: ${data.message || '请求失败'}`);
                  }
              })
              .finally(() => {
                  // 重新启用发送按钮
                  sendButton.disabled = false;
                  sendButton.textContent = '📤 发送';
              });
        }

        // 添加消息到聊天界面
        function addMessageToChat(type, content) {
            const messagesContainer = document.getElementById('chat-messages');
            const timestamp = new Date().toLocaleTimeString('zh-CN', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            const messageHTML = `
                <div class="message ${type}">
                    <div class="message-avatar">
                        ${type === 'user' ? '👤' : '🤖'}
                    </div>
                    <div class="message-content">
                        <div class="message-text">${content.replace(/\n/g, '<br>')}</div>
                        <div class="message-time">${timestamp}</div>
                    </div>
                </div>
            `;
            
            // 如果聊天是空的，移除空状态提示
            if (messagesContainer.querySelector('.empty-chat')) {
                messagesContainer.innerHTML = '';
            }
            
            messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
            scrollToBottom();
        }

        // 滚动到底部
        function scrollToBottom() {
            const messagesContainer = document.getElementById('chat-messages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // 清空对话
        function clearConversation() {
            if (confirm('确定要清空所有对话记录吗？此操作不可撤销。')) {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=wai_clear_conversation&nonce=<?php echo wp_create_nonce('wai_ajax_nonce'); ?>'
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          document.getElementById('chat-messages').innerHTML = `
                              <div class="empty-chat">
                                  <p>👋 对话记录已清空</p>
                                  <p>开始新的对话吧！</p>
                              </div>
                          `;
                          showMessage('success', '对话记录已清空');
                      }
                  });
            }
        }

        // 插入建议问题
        function insertSuggestion(question) {
            document.getElementById('chat-input').value = question;
        }

        // 显示消息
        function showMessage(type, message) {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const notice = document.createElement('div');
            notice.className = `notice ${noticeClass} is-dismissible`;
            notice.innerHTML = `<p>${message}</p>`;
            
            document.querySelector('.wrap').insertBefore(notice, document.querySelector('.wai-messaging-container'));
            
            setTimeout(() => {
                notice.remove();
            }, 5000);
        }

        // 回车键发送消息
        document.getElementById('chat-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // 页面加载完成后滚动到底部
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
        });
        </script>

        <style>
        .wai-messaging-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .wai-config-panel {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .wai-chat-interface {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 70vh;
        }

        .config-section h3 {
            margin-top: 0;
            color: #2c3338;
        }

        .config-item {
            margin-bottom: 15px;
        }

        .config-item label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .config-item input,
        .config-item select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .config-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .chat-info {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #666;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .empty-chat {
            text-align: center;
            color: #666;
            padding: 40px 20px;
        }

        .message {
            display: flex;
            margin-bottom: 20px;
            gap: 10px;
        }

        .message.user {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: #e9ecef;
        }

        .message.user .message-avatar {
            background: #007cba;
            color: white;
        }

        .message-content {
            flex: 1;
            max-width: 70%;
        }

        .message.user .message-content {
            text-align: right;
        }

        .message-text {
            background: white;
            padding: 12px 15px;
            border-radius: 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            word-wrap: break-word;
        }

        .message.user .message-text {
            background: #007cba;
            color: white;
        }

        .message-time {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .chat-input {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .chat-input textarea {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: vertical;
            font-family: inherit;
        }

        .chat-suggestions h4 {
            margin-bottom: 10px;
            color: #666;
        }

        .suggestion-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .button-small {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .wai-messaging-container {
                grid-template-columns: 1fr;
            }
            
            .message-content {
                max-width: 85%;
            }
        }
        </style>
        <?php
    }
    
    /**
     * 注册脚本和样式
     */
    public function enqueue_scripts($hook) {
        if ('toplevel_page_wai-messaging-platforms' !== $hook) {
            return;
        }
        
        wp_enqueue_style('wai-messaging-css');
        wp_enqueue_script('wai-messaging-js');
    }
    
    /**
     * AI聊天处理
     */
    public function ajax_chat_with_ai() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        $user_message = sanitize_text_field($_POST['message'] ?? '');
        $ai_response = $this->call_ai_api($user_message);
        
        // 保存对话历史
        $conversation_history = get_option('wai_cto_conversation', []);
        $conversation_history[] = [
            'type' => 'user',
            'content' => $user_message,
            'timestamp' => current_time('mysql')
        ];
        $conversation_history[] = [
            'type' => 'ai', 
            'content' => $ai_response,
            'timestamp' => current_time('mysql')
        ];
        
        // 只保留最近50条消息
        $conversation_history = array_slice($conversation_history, -50);
        update_option('wai_cto_conversation', $conversation_history);
        
        wp_send_json_success(['response' => $ai_response]);
    }
    
    /**
     * 清空对话历史
     */
    public function ajax_clear_conversation() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        update_option('wai_cto_conversation', []);
        wp_send_json_success();
    }
    
    /**
     * 测试AI连接
     */
    public function ajax_test_ai_connection() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        $api_key = get_option('wai_deepseek_api_key', '');
        
        if (empty($api_key)) {
            wp_send_json_error('API密钥未配置');
        }
        
        // 简单的连接测试
        $test_result = $this->call_ai_api('请回复"连接测试成功"');
        
        if (strpos($test_result, '连接测试成功') !== false || strlen($test_result) > 10) {
            wp_send_json_success(array(
                'message' => 'AI连接测试成功',
                'response' => $test_result
            ));
        } else {
            wp_send_json_error('连接测试失败: ' . $test_result);
        }
    }
    
    /**
     * 保存AI配置
     */
    public function ajax_save_ai_config() {
        check_ajax_referer('wai_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        $provider = sanitize_text_field($_POST['provider'] ?? 'deepseek');
        
        update_option('wai_deepseek_api_key', $api_key);
        update_option('wai_ai_provider', $provider);
        
        wp_send_json_success(array(
            'message' => 'AI配置已保存',
            'has_api_key' => !empty($api_key)
        ));
    }
    
    /**
     * 调用AI API
     */
    private function call_ai_api($message) {
        $api_key = get_option('wai_deepseek_api_key', '');
        
        if (empty($api_key)) {
            return "请先配置DeepSeek API密钥。访问 https://platform.deepseek.com 获取密钥，然后在AI CTO页面右侧配置面板中填入。";
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
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'HTTP错误: ' . $response_code;
            return "AI服务HTTP错误: {$error_message}";
        }
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        if (isset($data['error']['message'])) {
            return "AI服务错误: " . $data['error']['message'];
        }
        
        return "AI响应解析失败，请检查API配置。";
    }
}