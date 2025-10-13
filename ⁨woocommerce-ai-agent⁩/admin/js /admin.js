(function($) {
    'use strict';
    
    // 仪表板功能
    window.WC_AI_Agent_Admin = {
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // 设置页面实时验证
            $('input[name="max_discount_rate"], input[name="min_profit_margin"]').on('change', function() {
                var value = parseInt($(this).val());
                var min = parseInt($(this).attr('min'));
                var max = parseInt($(this).attr('max'));
                
                if (value < min) {
                    $(this).val(min);
                } else if (value > max) {
                    $(this).val(max);
                }
            });
            
            // 自动执行警告
            $('input[name="auto_execute"]').on('change', function() {
                if ($(this).is(':checked')) {
                    if (!confirm('警告：启用自动执行后，系统将自动修改产品价格、库存和创建优惠券。确定要继续吗？')) {
                        $(this).prop('checked', false);
                    }
                }
            });
        },
        
        // 显示通知
        showNotice: function(message, type) {
            var noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
            var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('.wrap h1').after(notice);
            
            setTimeout(function() {
                notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };
    
    // 初始化
    $(document).ready(function() {
        WC_AI_Agent_Admin.init();
    });
    
})(jQuery);
