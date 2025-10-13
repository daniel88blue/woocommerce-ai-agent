<?php
/**
 * Web3加密货币支付网关
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Web3_Crypto extends WC_Payment_Gateway {
    
    public function __construct() {
        $this->id = 'web3_crypto';
        $this->method_title = 'Web3加密货币支付';
        $this->method_description = '接受加密货币支付的Web3支付网关';
        $this->has_fields = true;
        
        // 初始化设置
        $this->init_form_fields();
        $this->init_settings();
        
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }
    
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => '启用/禁用',
                'type'    => 'checkbox',
                'label'   => '启用Web3加密货币支付',
                'default' => 'no'
            ),
            'title' => array(
                'title'       => '标题',
                'type'        => 'text',
                'description' => '客户在结账时看到的支付方式标题',
                'default'     => '加密货币支付',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => '描述',
                'type'        => 'textarea',
                'description' => '支付方式描述',
                'default'     => '使用加密货币安全支付',
            )
        );
    }
    
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        // 标记订单为待处理
        $order->update_status('pending', __('等待加密货币支付确认', 'woocommerce-ai-agent'));
        
        // 返回成功
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }
    
    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wp_kses_post($this->description));
        }
        
        echo '<div class="web3-payment-info">';
        echo '<p>🔗 连接到您的Web3钱包完成支付</p>';
        echo '</div>';
    }
}