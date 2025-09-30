(function($) {
    'use strict';

    class AIEcommerceWizard {
        constructor() {
            this.currentStep = '';
            this.init();
        }

        init() {
            this.bindEvents();
            this.initializeStep();
        }

        bindEvents() {
            // 表单提交处理
            $('#wizard-form').on('submit', this.handleFormSubmit.bind(this));

            // 连接测试按钮
            $('.test-connection').on('click', this.testConnection.bind(this));

            // 输入框实时验证
            $('.connection-input').on('input', this.validateInput.bind(this));

            // 跳过向导确认
            $('.skip-wizard').on('click', this.confirmSkipWizard.bind(this));
        }

        initializeStep() {
            const currentStep = $('.wizard-content').data('current-step');
            this.currentStep = currentStep;
            
            // 根据步骤初始化特定功能
            switch(currentStep) {
                case 'matomo':
                    this.initMatomoStep();
                    break;
                case 'ai_engine':
                    this.initAIEngineStep();
                    break;
                case 'replit':
                    this.initReplitStep();
                    break;
                case 'github':
                    this.initGitHubStep();
                    break;
            }
        }

        handleFormSubmit(e) {
            const form = $(e.target);
            const submitBtn = form.find('button[type="submit"]');
            const action = submitBtn.val();

            // 显示加载状态
            if (action === 'next_step' || action === 'complete') {
                submitBtn.prop('disabled', true).addClass('loading');
                
                if (action === 'next_step') {
                    submitBtn.text('检查配置中...');
                } else {
                    submitBtn.text('完成安装中...');
                }
            }

            // 在提交前验证必要字段
            if (!this.validateCurrentStep()) {
                e.preventDefault();
                submitBtn.prop('disabled', false).removeClass('loading');
                if (action === 'next_step') {
                    submitBtn.text('下一步 →');
                }
                return false;
            }

            return true;
        }

        validateCurrentStep() {
            let isValid = true;
            const currentStep = this.currentStep;

            switch(currentStep) {
                case 'matomo':
                    isValid = this.validateMatomoStep();
                    break;
                case 'ai_engine':
                    isValid = this.validateAIEngineStep();
                    break;
                case 'replit':
                    isValid = this.validateReplitStep();
                    break;
                case 'github':
                    isValid = this.validateGitHubStep();
                    break;
            }

            if (!isValid) {
                this.showValidationError('请填写所有必填字段');
            }

            return isValid;
        }

        validateMatomoStep() {
            const url = $('#matomo_url').val().trim();
            const token = $('#matomo_token').val().trim();

            if (!url || !token) {
                return false;
            }

            // 验证URL格式
            if (!this.isValidUrl(url)) {
                this.showValidationError('请输入有效的Matomo URL');
                return false;
            }

            return true;
        }

        validateAIEngineStep() {
            const apiKey = $('#ai_api_key').val().trim();
            const provider = $('#ai_provider').val();

            if (!apiKey) {
                return false;
            }

            // 根据提供商验证API密钥格式
            if (provider === 'openai' && !apiKey.startsWith('sk-')) {
                this.showValidationError('OpenAI API密钥格式不正确');
                return false;
            }

            return true;
        }

        validateReplitStep() {
            const url = $('#replit_url').val().trim();
            const secret = $('#replit_secret').val().trim();

            if (!url || !secret) {
                return false;
            }

            if (!this.isValidUrl(url)) {
                this.showValidationError('请输入有效的Replit URL');
                return false;
            }

            return true;
        }

        validateGitHubStep() {
            const token = $('#github_token').val().trim();
            const repo = $('#github_repo').val().trim();

            if (!token || !repo) {
                return false;
            }

            // 验证仓库格式
            if (!this.isValidRepoFormat(repo)) {
                this.showValidationError('仓库名称格式应为: 用户名/仓库名');
                return false;
            }

            return true;
        }

        isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        isValidRepoFormat(repo) {
            return /^[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/.test(repo);
        }

        async testConnection(e) {
            e.preventDefault();
            
            const button = $(e.target);
            const connectionType = button.data('connection-type');
            const formData = this.getConnectionTestData(connectionType);

            if (!formData) {
                return;
            }

            button.prop('disabled', true).text(ai_agent_wizard.testing_connection);

            try {
                const response = await $.ajax({
                    url: ai_agent_wizard.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ai_agent_test_connection',
                        connection_type: connectionType,
                        form_data: formData,
                        nonce: ai_agent_wizard.nonce
                    }
                });

                if (response.success) {
                    this.showConnectionSuccess(button, connectionType);
                } else {
                    this.showConnectionError(button, connectionType, response.data.message);
                }
            } catch (error) {
                this.showConnectionError(button, connectionType, '连接测试失败: ' + error.statusText);
            }
        }

        getConnectionTestData(connectionType) {
            const data = {};

            switch(connectionType) {
                case 'matomo':
                    data.url = $('#matomo_url').val().trim();
                    data.token = $('#matomo_token').val().trim();
                    if (!data.url || !data.token) {
                        this.showValidationError('请填写Matomo URL和Token');
                        return null;
                    }
                    break;

                case 'ai_engine':
                    data.api_key = $('#ai_api_key').val().trim();
                    data.provider = $('#ai_provider').val();
                    if (!data.api_key) {
                        this.showValidationError('请填写AI API密钥');
                        return null;
                    }
                    break;

                case 'replit':
                    data.url = $('#replit_url').val().trim();
                    data.secret = $('#replit_secret').val().trim();
                    if (!data.url || !data.secret) {
                        this.showValidationError('请填写Replit URL和Secret');
                        return null;
                    }
                    break;

                case 'github':
                    data.token = $('#github_token').val().trim();
                    data.repo = $('#github_repo').val().trim();
                    if (!data.token || !data.repo) {
                        this.showValidationError('请填写GitHub Token和仓库名');
                        return null;
                    }
                    break;
            }

            return data;
        }

        showConnectionSuccess(button, connectionType) {
            button.removeClass('button-secondary').addClass('button-success');
            button.text(ai_agent_wizard.connection_success);
            
            // 更新连接状态显示
            $(`.connection-status.${connectionType}`).addClass('connected').removeClass('disconnected');
            
            // 3秒后恢复按钮状态
            setTimeout(() => {
                button.prop('disabled', false).removeClass('button-success').addClass('button-secondary');
                button.text('测试连接');
            }, 3000);
        }

        showConnectionError(button, connectionType, message) {
            button.removeClass('button-secondary').addClass('button-error');
            button.text(ai_agent_wizard.connection_failed);
            
            // 显示详细错误信息
            this.showValidationError(message);
            
            // 3秒后恢复按钮状态
            setTimeout(() => {
                button.prop('disabled', false).removeClass('button-error').addClass('button-secondary');
                button.text('测试连接');
            }, 3000);
        }

        showValidationError(message) {
            // 移除现有的错误消息
            $('.validation-error').remove();
            
            // 创建错误消息元素
            const errorDiv = $('<div class="validation-error"></div>');
            errorDiv.html(`
                <div class="notice notice-error notice-alt">
                    <p>${message}</p>
                </div>
            `);
            
            // 插入到表单顶部
            $('.wizard-content').prepend(errorDiv);
            
            // 5秒后自动移除
            setTimeout(() => {
                errorDiv.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }

        validateInput(e) {
            const input = $(e.target);
            const value = input.val().trim();
            
            // 移除之前的验证状态
            input.removeClass('valid invalid');
            
            if (value) {
                input.addClass('valid');
            } else {
                input.addClass('invalid');
            }
        }

        confirmSkipWizard(e) {
            e.preventDefault();
            
            if (confirm('确定要跳过安装向导吗？您可以在设置页面中手动配置所有选项。')) {
                window.location.href = $(e.target).attr('href');
            }
        }

        initMatomoStep() {
            // Matomo步骤特定初始化
            console.log('初始化Matomo配置步骤');
        }

        initAIEngineStep() {
            // AI引擎步骤特定初始化
            $('#ai_provider').on('change', this.updateAIProviderHelp.bind(this));
            this.updateAIProviderHelp();
        }

        updateAIProviderHelp() {
            const provider = $('#ai_provider').val();
            let helpText = '';
            
            switch(provider) {
                case 'openai':
                    helpText = '在 <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI平台</a> 获取API密钥';
                    break;
                case 'anthropic':
                    helpText = '在 <a href="https://console.anthropic.com/" target="_blank">Anthropic控制台</a> 获取API密钥';
                    break;
                case 'google':
                    helpText = '在 <a href="https://makersuite.google.com/" target="_blank">Google AI Studio</a> 获取API密钥';
                    break;
                case 'custom':
                    helpText = '输入您的自定义AI服务API密钥';
                    break;
            }
            
            $('.ai-provider-help').html(helpText);
        }

        initReplitStep() {
            // Replit步骤特定初始化
            console.log('初始化Replit配置步骤');
        }

        initGitHubStep() {
            // GitHub步骤特定初始化
            console.log('初始化GitHub配置步骤');
        }
    }

    // 初始化向导
    $(document).ready(function() {
        new AIEcommerceWizard();
    });

})(jQuery);
