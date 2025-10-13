<?php
if (!defined('ABSPATH')) {
    exit;
}

class WAI_DeepSeek_API {
    private $api_key;
    private $api_url = 'https://api.deepseek.com/v1/chat/completions';
    
    public function __construct() {
        $this->api_key = get_option('wai_deepseek_api_key', '');
    }
    
    /**
     * 测试API连接
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => 'API密钥未配置'
            );
        }
        
        $test_message = array(
            array(
                'role' => 'user',
                'content' => '回复"连接成功"以确认API正常工作'
            )
        );
        
        $response = $this->send_message($test_message);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => '连接失败: ' . $response->get_error_message()
            );
        }
        
        return array(
            'success' => true,
            'message' => 'API连接测试成功',
            'response' => $response
        );
    }
    
    /**
     * 发送消息到DeepSeek API
     */
    public function send_message($messages, $temperature = 0.7, $max_tokens = 2000) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'DeepSeek API密钥未配置');
        }
        
        $body = array(
            'model' => 'deepseek-chat',
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $max_tokens,
            'stream' => false
        );
        
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key
            ),
            'body' => json_encode($body),
            'timeout' => 30
        );
        
        $response = wp_remote_post($this->api_url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        
        if ($response_code !== 200) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'HTTP错误: ' . $response_code;
            return new WP_Error('api_error', $error_message);
        }
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        return new WP_Error('invalid_response', 'API返回了无效的响应格式');
    }
    
    /**
     * 获取可用的模型列表
     */
    public function get_available_models() {
        return array(
            'deepseek-chat' => 'DeepSeek Chat',
            'deepseek-coder' => 'DeepSeek Coder'
        );
    }
}