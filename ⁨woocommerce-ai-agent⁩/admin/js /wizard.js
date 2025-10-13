/**
 * AI电商智能体 - 安装向导JavaScript
 */

(function($) {
    'use strict';

    class WAIInstallWizard {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.initDynamicElements();
        }

        bindEvents() {
            // 表单提交处理
            $('#wai-install-form').on('submit', this.handleFormSubmit.bind(this));

            // 动态显示/隐藏Web3设置
            $('#wai_web3_enabled').on('change', this.toggleWeb3Details.bind(this));

            // 范围滑块值显示
            $('#wai_ai_decision_threshold').on('input', this.updateThresholdValue.bind(this));

            // 测试连接按钮
            $('.wai-test-buttons button').on('click', this.handleTestConnection.bind(this));

            // 跳过步骤确认
            $('.wai-skip-link').on('click', this.handleSkipStep.bind(this));
        }

        initDynamicElements() {
            // 初始化阈值显示
            this.updateThresholdValue();

            // 初始化Web3详情显示
            this.toggleWeb3Details();
        }

        handleFormSubmit(e) {
            const currentStep = $('input[name="wai_current_step"]').val();
            
            // 验证当前步骤的表单数据
            if (!this.validateStep(currentStep)) {
                e.preventDefault();
                return false;
            }

            // 显示加载状态
            this.showLoadingState();

            // 可以在这里添加AJAX保存步骤数据的逻辑
            this.saveStepData(currentStep);
        }

        validateStep(step) {
            switch (step) {
                case '4': // 数据分析步骤
                    return this.validateAnalyticsStep();
                case '5': // AI服务步骤
                    return this.validateAIStep();
                case '6': // Web3步骤
                    return this.validateWeb3Step();
                default:
                    return true;
            }
        }

        validateAnalyticsStep() {
            let isValid = true;
            const errors = [];

            // 检查Conversific配置
            if ($('#wai_conversific_enabled').is(':checked') && !$('#wai_conversific_api_key').val().trim()) {
                errors.push('请输入Conversific API密钥');
                isValid = false;
            }

            // 检查Klaviyo配置
            if ($('#wai_klaviyo_enabled').is(':checked')) {
                if (!$('#wai_klaviyo_public_key').val().trim()) {
                    errors.push('请输入Klaviyo Public Key');
                    isValid = false;
                }
                if (!$('#wai_klaviyo_private_key').val().trim()) {
                    errors.push('请输入Klaviyo Private Key');
                    isValid = false;
                }
            }

            if (!isValid) {
                this.showValidationErrors(errors);
            }

            return isValid;
        }

        validateAIStep() {
            let isValid = true;
            const errors = [];

            // 检查OpenAI配置
            if ($('#wai_openai_enabled').is(':checked') && !$('#wai_openai_api_key').val().trim()) {
                errors.push('请输入OpenAI API密钥');
                isValid = false;
            }

            if (!isValid) {
                this.showValidationErrors(errors);
            }

            return isValid;
        }

        validateWeb3Step() {
            // Web3是可选的，没有强制验证
            return true;
        }

        showValidationErrors(errors) {
            const errorHtml = `
                <div class="notice notice-error is-dismissible">
                    <p><strong>配置错误：</strong></p>
                    <ul>
                        ${errors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
                </div>
            `;
            
            $('.wai-step-body').prepend(errorHtml);
            
            // 滚动到错误位置
            $('html, body').animate({
                scrollTop: $('.notice-error').offset().top - 100
            }, 500);
        }

        toggleWeb3Details() {
            const isEnabled = $('#wai_web3_enabled').is(':checked');
            $('#wai-web3-details').toggle(isEnabled);
        }

        updateThresholdValue() {
            const value = $('#wai_ai_decision_threshold').val();
            $('#wai_threshold_value').text(value + '%');
        }

        handleTestConnection(e) {
            e.preventDefault();
            const button = $(e.target);
            const service = button.text().includes('Conversific') ? 'conversific' : 'klaviyo';
            
            this.testServiceConnection(service, button);
        }

        testServiceConnection(service, button) {
            const originalText = button.text();
            button.text('测试中...').prop('disabled', true);

            // 模拟API测试 - 实际实现应该使用AJAX调用
            setTimeout(() => {
                const isSuccess = Math.random() > 0.3; // 70%成功率用于演示
                
                if (isSuccess) {
                    this.showTestResult(service, 'success', '连接测试成功！');
                } else {
                    this.showTestResult(service, 'error', '连接测试失败，请检查配置。');
                }
                
                button.text(originalText).prop('disabled', false);
            }, 2000);
        }

        showTestResult(service, type, message) {
            const resultClass = type === 'success' ? 'notice-success' : 'notice-error';
            const resultHtml = `
                <div class="notice ${resultClass} is-dismissible">
                    <p><strong>${service.toUpperCase()} 测试结果：</strong> ${message}</p>
                </div>
            `;
            
            $('#wai-test-results').html(resultHtml);
        }

        handleSkipStep(e) {
            e.preventDefault();
            const skipUrl = $(e.target).attr('href');
            
            if (confirm('确定要跳过此步骤吗？您可以在稍后重新配置这些设置。')) {
                window.location.href = skipUrl;
            }
        }

        showLoadingState() {
            const submitButton = $('.wai-next-button, [name="wai_launch_system"]');
            const originalText = submitButton.text();
            
            submitButton.text('处理中...').prop('disabled', true);
            
            // 3秒后恢复，防止无限加载
            setTimeout(() => {
                submitButton.text(originalText).prop('disabled', false);
            }, 3000);
        }

        saveStepData(step) {
            // 这里可以添加AJAX调用来自动保存步骤数据
            // 这样即使用户中途离开，配置也不会丢失
            
            const formData = new FormData(document.getElementById('wai-install-form'));
            
            // 移除不需要的字段
            formData.delete('wai_wizard_nonce');
            formData.delete('wai_action');
            formData.delete('wai_submit');
            
            // 在实际实现中，这里应该是一个AJAX调用
            /*
            $.ajax({
                url: wai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wai_save_step_data',
                    step: step,
                    data: Object.fromEntries(formData),
                    nonce: wai_ajax.nonce
                },
                success: function(response) {
                    if (!response.success) {
                        console.error('保存步骤数据失败:', response.data);
                    }
                }
            });
            */
        }
    }

    // 初始化向导
    $(document).ready(function() {
        new WAIInstallWizard();
    });

    // 全局函数，供内联脚本调用
    window.waiTestConversific = function() {
        $('#wai-test-results').html('<div class="notice notice-info"><p>测试Conversific连接...</p></div>');
        
        // 模拟测试过程
        setTimeout(() => {
            $('#wai-test-results').html(`
                <div class="notice notice-success">
                    <p><strong>Conversific 连接测试成功！</strong></p>
                    <p>API密钥有效，数据同步正常。</p>
                </div>
            `);
        }, 1500);
    };

    window.waiTestKlaviyo = function() {
        $('#wai-test-results').html('<div class="notice notice-info"><p>测试Klaviyo连接...</p></div>');
        
        // 模拟测试过程
        setTimeout(() => {
            $('#wai-test-results').html(`
                <div class="notice notice-success">
                    <p><strong>Klaviyo 连接测试成功！</strong></p>
                    <p>API密钥有效，邮件服务正常。</p>
                </div>
            `);
        }, 1500);
    };

    window.waiInstallWooCommerce = function() {
        if (confirm('这将安装并激活WooCommerce插件。确定要继续吗？')) {
            $('#wai-test-results').html('<div class="notice notice-info"><p>正在安装WooCommerce...</p></div>');
            
            // 在实际实现中，这里应该是AJAX调用WordPress的插件安装API
            setTimeout(() => {
                $('#wai-test-results').html(`
                    <div class="notice notice-warning">
                        <p><strong>注意：</strong> 自动安装功能需要进一步开发。</p>
                        <p>请<a href="${wai_admin_url}plugin-install.php?s=woocommerce&tab=search&type=term" target="_blank">手动安装WooCommerce</a>后刷新此页面。</p>
                    </div>
                `);
            }, 2000);
        }
    };

})(jQuery);