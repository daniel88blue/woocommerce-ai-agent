<?php
/**
 * AI电商智能体 - 移动端优化器
 * 负责移动端用户体验优化和性能提升
 */

if (!defined('ABSPATH')) {
    exit;
}

class WAI_Mobile_Optimizer {
    
    private $optimization_settings = [];
    private $performance_monitor = null;
    private $user_agent_detector = null;
    
    public function __construct() {
        $this->load_optimization_settings();
        $this->performance_monitor = new WAI_Mobile_Performance_Monitor();
        $this->user_agent_detector = new WAI_User_Agent_Detector();
        
        add_action('wp_head', [$this, 'add_mobile_meta_tags']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_mobile_optimized_assets']);
        add_action('wp_footer', [$this, 'add_mobile_optimization_scripts']);
        add_filter('wp_calculate_image_srcset', [$this, 'optimize_image_srcset'], 10, 5);
        add_filter('wp_get_attachment_image_attributes', [$this, 'optimize_image_attributes'], 10, 3);
        add_action('template_redirect', [$this, 'maybe_redirect_to_mobile']);
        
        // AMP集成
        if ($this->optimization_settings['amp_enabled']) {
            add_action('wp', [$this, 'setup_amp_integration']);
        }
        
        // PWA支持
        if ($this->optimization_settings['pwa_enabled']) {
            add_action('init', [$this, 'setup_pwa_support']);
        }
    }
    
    /**
     * 加载优化设置
     */
    private function load_optimization_settings() {
        $this->optimization_settings = get_option('wai_mobile_optimization', [
            'enabled' => true,
            'amp_enabled' => false,
            'pwa_enabled' => false,
            'lazy_loading' => true,
            'image_optimization' => true,
            'critical_css' => true,
            'defer_scripts' => true,
            'cache_strategy' => 'aggressive',
            'performance_threshold' => 3, // 秒
            'mobile_redirect' => false,
            'touch_optimized' => true,
            'responsive_images' => true,
            'minify_assets' => true
        ]);
    }
    
    /**
     * 添加移动端meta标签
     */
    public function add_mobile_meta_tags() {
        if (!$this->is_mobile_request()) {
            return;
        }
        
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . "\n";
        echo '<meta name="HandheldFriendly" content="true">' . "\n";
        echo '<meta name="MobileOptimized" content="width">' . "\n";
        echo '<meta name="theme-color" content="#ffffff">' . "\n";
        
        // 添加到主屏幕的meta
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
        echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        
        // 结构化数据
        $this->add_mobile_structured_data();
    }
    
    /**
     * 添加移动端结构化数据
     */
    private function add_mobile_structured_data() {
        if (!is_singular('product')) {
            return;
        }
        
        global $post;
        $product = wc_get_product($post->ID);
        
        if (!$product) {
            return;
        }
        
        $structured_data = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->get_name(),
            'description' => wp_strip_all_tags($product->get_short_description() ?: $product->get_description()),
            'image' => wp_get_attachment_url($product->get_image_id()),
            'sku' => $product->get_sku(),
            'offers' => [
                '@type' => 'Offer',
                'price' => $product->get_price(),
                'priceCurrency' => get_woocommerce_currency(),
                'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url' => get_permalink($product->get_id())
            ]
        ];
        
        echo '<script type="application/ld+json">' . json_encode($structured_data) . '</script>' . "\n";
    }
    
    /**
     * 加载移动端优化资源
     */
    public function enqueue_mobile_optimized_assets() {
        if (!$this->is_mobile_request()) {
            return;
        }
        
        // 移除不必要的样式和脚本
        $this->remove_unnecessary_assets();
        
        // 加载移动端优化样式
        wp_enqueue_style(
            'wai-mobile-optimized',
            WAI_PLUGIN_URL . 'assets/css/mobile-optimized.css',
            [],
            WAI_VERSION
        );
        
        // 加载触摸优化脚本
        wp_enqueue_script(
            'wai-touch-optimization',
            WAI_PLUGIN_URL . 'assets/js/touch-optimization.js',
            ['jquery'],
            WAI_VERSION,
            true
        );
        
        // 关键CSS内联
        if ($this->optimization_settings['critical_css']) {
            $this->inline_critical_css();
        }
        
        // 延迟加载非关键脚本
        if ($this->optimization_settings['defer_scripts']) {
            $this->defer_non_critical_scripts();
        }
    }
    
    /**
     * 移除不必要的资源
     */
    private function remove_unnecessary_assets() {
        // 移除不必要的WordPress默认样式
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        
        // 移除emoji相关资源
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        
        // 根据设置移除更多资源
        if ($this->optimization_settings['minify_assets']) {
            $this->minify_and_combine_assets();
        }
    }
    
    /**
     * 内联关键CSS
     */
    private function inline_critical_css() {
        $critical_css = $this->generate_critical_css();
        echo '<style id="wai-critical-css">' . $critical_css . '</style>' . "\n";
    }
    
    /**
     * 生成关键CSS
     */
    private function generate_critical_css() {
        $critical_css = '
            /* 关键CSS - 移动端优化 */
            .woocommerce-loop-product__title,
            .price,
            .woocommerce-Price-amount,
            .add_to_cart_button,
            .single_add_to_cart_button {
                opacity: 1 !important;
                transform: none !important;
            }
            
            /* 确保关键元素可见 */
            .site-header,
            .woocommerce-breadcrumb,
            .product_title,
            .woocommerce-product-gallery,
            .woocommerce-tabs,
            .related.products {
                display: block !important;
            }
            
            /* 移动端触摸优化 */
            button, 
            .button,
            .add_to_cart_button,
            input[type="submit"] {
                min-height: 44px;
                min-width: 44px;
                padding: 12px 20px;
            }
            
            /* 响应式调整 */
            @media (max-width: 768px) {
                .woocommerce-products-header {
                    padding: 15px 0;
                }
                
                .products {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 10px;
                }
                
                .product {
                    margin-bottom: 15px;
                }
            }
        ';
        
        return $this->minify_css($critical_css);
    }
    
    /**
     * 延迟加载非关键脚本
     */
    private function defer_non_critical_scripts() {
        add_filter('script_loader_tag', function($tag, $handle) {
            // 需要延迟的脚本
            $defer_scripts = [
                'wai-touch-optimization',
                'wc-add-to-cart',
                'wc-cart-fragments'
            ];
            
            if (in_array($handle, $defer_scripts)) {
                return str_replace(' src', ' defer src', $tag);
            }
            
            return $tag;
        }, 10, 2);
    }
    
    /**
     * 优化图片srcset
     */
    public function optimize_image_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id) {
        if (!$this->is_mobile_request() || !$this->optimization_settings['responsive_images']) {
            return $sources;
        }
        
        // 为移动端优化图片尺寸
        $mobile_sizes = [
            '300w'  => ['width' => 300, 'height' => 0],
            '600w'  => ['width' => 600, 'height' => 0],
            '768w'  => ['width' => 768, 'height' => 0],
            '1024w' => ['width' => 1024, 'height' => 0]
        ];
        
        $optimized_sources = [];
        
        foreach ($mobile_sizes as $size_name => $size_data) {
            if (isset($sources[$size_data['width']])) {
                $optimized_sources[$size_data['width']] = $sources[$size_data['width']];
            }
        }
        
        return !empty($optimized_sources) ? $optimized_sources : $sources;
    }
    
    /**
     * 优化图片属性
     */
    public function optimize_image_attributes($attr, $attachment, $size) {
        if (!$this->is_mobile_request() || !$this->optimization_settings['image_optimization']) {
            return $attr;
        }
        
        // 添加懒加载
        $attr['loading'] = 'lazy';
        
        // 添加decoding属性
        $attr['decoding'] = 'async';
        
        // 优化图片大小
        if (isset($attr['src'])) {
            $attr['src'] = $this->optimize_image_url($attr['src'], $size);
        }
        
        return $attr;
    }
    
    /**
     * 优化图片URL
     */
    private function optimize_image_url($image_url, $size) {
        // 这里可以实现图片CDN优化、WebP转换等
        // 目前返回原URL，实际实现中应该进行优化
        
        return $image_url;
    }
    
    /**
     * 添加移动端优化脚本
     */
    public function add_mobile_optimization_scripts() {
        if (!$this->is_mobile_request()) {
            return;
        }
        
        ?>
        <script>
        (function() {
            'use strict';
            
            // 移动端性能优化
            document.addEventListener('DOMContentLoaded', function() {
                // 延迟加载非关键图片
                if ('IntersectionObserver' in window) {
                    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
                    
                    const imageObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                img.src = img.dataset.src;
                                img.classList.remove('lazy');
                                imageObserver.unobserve(img);
                            }
                        });
                    });
                    
                    lazyImages.forEach(img => {
                        imageObserver.observe(img);
                    });
                }
                
                // 触摸事件优化
                this.optimizeTouchEvents();
                
                // 快速点击优化
                this.optimizeFastClicks();
            });
            
            // 触摸事件优化
            function optimizeTouchEvents() {
                let touchStartY;
                
                document.addEventListener('touchstart', function(e) {
                    touchStartY = e.touches[0].clientY;
                }, { passive: true });
                
                document.addEventListener('touchmove', function(e) {
                    // 防止橡皮筋效果
                    if (e.touches[0].clientY - touchStartY > 50) {
                        e.preventDefault();
                    }
                }, { passive: false });
            }
            
            // 快速点击优化
            function optimizeFastClicks() {
                let lastClickTime = 0;
                const buttons = document.querySelectorAll('button, .button, a.btn');
                
                buttons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        const currentTime = new Date().getTime();
                        
                        // 防止快速连续点击
                        if (currentTime - lastClickTime < 1000) {
                            e.preventDefault();
                            e.stopPropagation();
                            return;
                        }
                        
                        lastClickTime = currentTime;
                        
                        // 添加点击反馈
                        this.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            this.style.transform = 'scale(1)';
                        }, 150);
                    });
                });
            }
            
            // 性能监控
            window.addEventListener('load', function() {
                // 发送性能数据到分析系统
                if ('performance' in window) {
                    const perfData = window.performance.timing;
                    const loadTime = perfData.loadEventEnd - perfData.navigationStart;
                    const domReadyTime = perfData.domContentLoadedEventEnd - perfData.navigationStart;
                    
                    // 如果加载时间过长，记录到分析系统
                    if (loadTime > 3000) {
                        console.warn('页面加载时间过长:', loadTime + 'ms');
                    }
                }
            });
            
        })();
        </script>
        
        <!-- 预加载关键资源 -->
        <link rel="preload" href="<?php echo WAI_PLUGIN_URL . 'assets/css/mobile-optimized.css'; ?>" as="style">
        <link rel="preload" href="<?php echo WAI_PLUGIN_URL . 'assets/js/touch-optimization.js'; ?>" as="script">
        
        <!-- 预连接到重要域名 -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="dns-prefetch" href="//cdn.example.com">
        <?php
    }
    
    /**
     * 可能重定向到移动端版本
     */
    public function maybe_redirect_to_mobile() {
        if (!$this->optimization_settings['mobile_redirect'] || 
            $this->is_mobile_request() || 
            is_admin() || 
            wp_is_mobile()) {
            return;
        }
        
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $is_mobile = $this->user_agent_detector->is_mobile($user_agent);
        
        if ($is_mobile && !$this->is_mobile_domain()) {
            $mobile_url = $this->get_mobile_url();
            wp_redirect($mobile_url, 302);
            exit;
        }
    }
    
    /**
     * 设置AMP集成
     */
    public function setup_amp_integration() {
        if (!$this->optimization_settings['amp_enabled'] || !function_exists('is_amp_endpoint')) {
            return;
        }
        
        // AMP特定优化
        add_action('amp_post_template_head', [$this, 'add_amp_optimizations']);
        add_action('amp_post_template_footer', [$this, 'add_amp_analytics']);
        
        // 优化AMP页面内容
        add_filter('the_content', [$this, 'optimize_content_for_amp'], 20);
    }
    
    /**
     * 添加AMP优化
     */
    public function add_amp_optimizations() {
        ?>
        <!-- AMP优化 -->
        <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
        <script async custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js"></script>
        <style amp-custom>
            /* AMP特定样式优化 */
            .amp-product-image {
                max-width: 100%;
                height: auto;
            }
            
            .amp-add-to-cart {
                background: #007cba;
                color: white;
                border: none;
                padding: 15px 25px;
                border-radius: 5px;
                width: 100%;
                font-size: 16px;
            }
        </style>
        <?php
    }
    
    /**
     * 添加AMP分析
     */
    public function add_amp_analytics() {
        ?>
        <amp-analytics type="googleanalytics">
        <script type="application/json">
        {
            "vars": {
                "account": "UA-XXXXX-Y"
            },
            "triggers": {
                "trackPageview": {
                    "on": "visible",
                    "request": "pageview"
                },
                "trackAddToCart": {
                    "on": "click",
                    "selector": ".amp-add-to-cart",
                    "request": "event",
                    "vars": {
                        "eventCategory": "engagement",
                        "eventAction": "add_to_cart",
                        "eventLabel": "AMP Add to Cart"
                    }
                }
            }
        }
        </script>
        </amp-analytics>
        <?php
    }
    
    /**
     * 为AMP优化内容
     */
    public function optimize_content_for_amp($content) {
        if (!function_exists('is_amp_endpoint') || !is_amp_endpoint()) {
            return $content;
        }
        
        // 移除不兼容AMP的标签
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
        $content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content);
        
        // 优化图片标签
        $content = preg_replace_callback('/<img([^>]+)>/i', function($matches) {
            $attrs = $matches[1];
            
            // 添加AMP图片属性
            $attrs = preg_replace('/\bwidth=(["\'])(\d+)\1/', 'width="$2"', $attrs);
            $attrs = preg_replace('/\bheight=(["\'])(\d+)\1/', 'height="$2"', $attrs);
            $attrs = preg_replace('/\b(src)=(["\'])([^"\']+)\2/', 'src="$3"', $attrs);
            
            return '<amp-img ' . $attrs . ' layout="responsive"></amp-img>';
        }, $content);
        
        return $content;
    }
    
    /**
     * 设置PWA支持
     */
    public function setup_pwa_support() {
        if (!$this->optimization_settings['pwa_enabled']) {
            return;
        }
        
        // 注册Service Worker
        add_action('wp_head', [$this, 'add_pwa_meta_tags']);
        add_action('wp_footer', [$this, 'add_service_worker_registration']);
        
        // 创建manifest文件
        add_action('init', [$this, 'create_web_app_manifest']);
        
        // Service Worker路由
        add_action('template_redirect', [$this, 'maybe_serve_service_worker']);
    }
    
    /**
     * 添加PWA meta标签
     */
    public function add_pwa_meta_tags() {
        ?>
        <link rel="manifest" href="<?php echo home_url('/manifest.json'); ?>">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
        <link rel="apple-touch-icon" href="<?php echo $this->get_pwa_icon_url(); ?>">
        <?php
    }
    
    /**
     * 添加Service Worker注册
     */
    public function add_service_worker_registration() {
        ?>
        <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo home_url('/sw.js'); ?>')
                    .then(function(registration) {
                        console.log('Service Worker 注册成功:', registration);
                    })
                    .catch(function(error) {
                        console.log('Service Worker 注册失败:', error);
                    });
            });
        }
        </script>
        <?php
    }
    
    /**
     * 创建Web App Manifest
     */
    public function create_web_app_manifest() {
        if ($_SERVER['REQUEST_URI'] !== '/manifest.json') {
            return;
        }
        
        $manifest = [
            'name' => get_bloginfo('name'),
            'short_name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'start_url' => home_url('/'),
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#007cba',
            'orientation' => 'portrait',
            'icons' => [
                [
                    'src' => $this->get_pwa_icon_url(192),
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => $this->get_pwa_icon_url(512),
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ]
            ]
        ];
        
        header('Content-Type: application/json');
        echo json_encode($manifest);
        exit;
    }
    
    /**
     * 可能提供Service Worker
     */
    public function maybe_serve_service_worker() {
        if ($_SERVER['REQUEST_URI'] !== '/sw.js') {
            return;
        }
        
        $service_worker_content = $this->generate_service_worker();
        
        header('Content-Type: application/javascript');
        header('Service-Worker-Allowed: /');
        echo $service_worker_content;
        exit;
    }
    
    /**
     * 生成Service Worker
     */
    private function generate_service_worker() {
        $cache_name = 'wai-pwa-cache-v1';
        $assets_to_cache = [
            '/',
            '/wp-content/themes/' . get_stylesheet() . '/style.css',
            WAI_PLUGIN_URL . 'assets/css/mobile-optimized.css',
            WAI_PLUGIN_URL . 'assets/js/touch-optimization.js'
        ];
        
        return "
        const CACHE_NAME = '{$cache_name}';
        const urlsToCache = " . json_encode($assets_to_cache) . ";
        
        self.addEventListener('install', function(event) {
            event.waitUntil(
                caches.open(CACHE_NAME)
                    .then(function(cache) {
                        return cache.addAll(urlsToCache);
                    })
            );
        });
        
        self.addEventListener('fetch', function(event) {
            event.respondWith(
                caches.match(event.request)
                    .then(function(response) {
                        if (response) {
                            return response;
                        }
                        
                        return fetch(event.request).then(function(response) {
                            if (!response || response.status !== 200 || response.type !== 'basic') {
                                return response;
                            }
                            
                            var responseToCache = response.clone();
                            
                            caches.open(CACHE_NAME)
                                .then(function(cache) {
                                    cache.put(event.request, responseToCache);
                                });
                            
                            return response;
                        });
                    })
            );
        });
        ";
    }
    
    /**
     * 获取PWA图标URL
     */
    private function get_pwa_icon_url($size = 192) {
        // 这里应该返回实际的图标URL
        // 目前返回占位符
        return home_url("/wp-content/uploads/pwa-icon-{$size}.png");
    }
    
    /**
     * 检查是否为移动端请求
     */
    private function is_mobile_request() {
        return wp_is_mobile() || $this->user_agent_detector->is_mobile($_SERVER['HTTP_USER_AGENT'] ?? '');
    }
    
    /**
     * 检查是否为移动域名
     */
    private function is_mobile_domain() {
        return strpos($_SERVER['HTTP_HOST'], 'm.') === 0 || 
               strpos($_SERVER['HTTP_HOST'], 'mobile.') === 0;
    }
    
    /**
     * 获取移动端URL
     */
    private function get_mobile_url() {
        $current_url = home_url($_SERVER['REQUEST_URI']);
        
        if ($this->is_mobile_domain()) {
            return $current_url;
        }
        
        // 构建移动端URL
        $mobile_domain = 'm.' . $_SERVER['HTTP_HOST'];
        return str_replace($_SERVER['HTTP_HOST'], $mobile_domain, $current_url);
    }
    
    /**
     * 压缩和合并资源
     */
    private function minify_and_combine_assets() {
        // 这里可以实现CSS和JS的压缩合并
        // 实际实现中应该使用专门的压缩库
        
        add_action('wp_head', function() {
            // 内联关键CSS已经在前面处理了
        });
    }
    
    /**
     * 压缩CSS
     */
    private function minify_css($css) {
        // 简单的CSS压缩
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/;\s*/', ';', $css);
        $css = preg_replace('/:\s+/', ':', $css);
        $css = preg_replace('/\s*{\s*/', '{', $css);
        $css = preg_replace('/;\s*}/', '}', $css);
        
        return trim($css);
    }
    
    /**
     * 获取移动端性能报告
     */
    public function get_mobile_performance_report() {
        return $this->performance_monitor->get_performance_report();
    }
    
    /**
     * 优化WooCommerce移动端体验
     */
    public function optimize_woocommerce_for_mobile() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // 优化产品目录
        add_filter('woocommerce_product_loop_start', [$this, 'optimize_product_loop']);
        add_filter('woocommerce_product_loop_end', [$this, 'close_product_loop_optimization']);
        
        // 优化单个产品页面
        add_action('woocommerce_before_single_product', [$this, 'optimize_single_product_layout']);
        
        // 优化购物车和结账
        add_action('woocommerce_before_cart', [$this, 'optimize_cart_layout']);
        add_action('woocommerce_before_checkout_form', [$this, 'optimize_checkout_layout']);
    }
    
    /**
     * 优化产品循环
     */
    public function optimize_product_loop($loop_start) {
        if (!$this->is_mobile_request()) {
            return $loop_start;
        }
        
        // 为移动端添加特定的CSS类
        $loop_start = str_replace(
            'products columns-', 
            'products mobile-products columns-', 
            $loop_start
        );
        
        return $loop_start;
    }
    
    /**
     * 关闭产品循环优化
     */
    public function close_product_loop_optimization($loop_end) {
        // 清理工作可以在这里进行
        return $loop_end;
    }
    
    /**
     * 优化单个产品页面布局
     */
    public function optimize_single_product_layout() {
        if (!$this->is_mobile_request()) {
            return;
        }
        
        // 重新排列产品页面元素
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
        add_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 25);
        
        // 优化图片库
        add_filter('woocommerce_single_product_carousel_options', [$this, 'optimize_product_gallery']);
    }
    
    /**
     * 优化产品图片库
     */
    public function optimize_product_gallery($options) {
        if (!$this->is_mobile_request()) {
            return $options;
        }
        
        $options['controlNav'] = true;
        $options['directionNav'] = true;
        $options['animation'] = 'fade';
        $options['slideshow'] = false;
        
        return $options;
    }
    
    /**
     * 优化购物车布局
     */
    public function optimize_cart_layout() {
        if (!$this->is_mobile_request()) {
            return;
        }
        
        // 添加移动端特定的购物车样式
        wp_add_inline_style('wai-mobile-optimized', '
            @media (max-width: 768px) {
                .woocommerce-cart-form {
                    font-size: 14px;
                }
                
                .cart_totals {
                    position: sticky;
                    bottom: 0;
                    background: white;
                    padding: 15px;
                    border-top: 2px solid #f0f0f0;
                }
                
                .actions .button {
                    width: 100%;
                    margin-bottom: 10px;
                }
            }
        ');
    }
    
    /**
     * 优化结账布局
     */
    public function optimize_checkout_layout() {
        if (!$this->is_mobile_request()) {
            return;
        }
        
        // 优化结账表单的移动端显示
        wp_add_inline_style('wai-mobile-optimized', '
            @media (max-width: 768px) {
                .woocommerce-checkout {
                    padding: 0 10px;
                }
                
                .col2-set .col-1,
                .col2-set .col-2 {
                    width: 100%;
                    float: none;
                }
                
                #order_review_heading {
                    display: none;
                }
                
                #order_review {
                    width: 100%;
                    position: static;
                }
            }
        ');
    }
}

/**
 * 移动端性能监控器
 */
class WAI_Mobile_Performance_Monitor {
    
    public function get_performance_report() {
        $report = [
            'page_load_time' => $this->get_page_load_time(),
            'first_contentful_paint' => $this->get_first_contentful_paint(),
            'largest_contentful_paint' => $this->get_largest_contentful_paint(),
            'cumulative_layout_shift' => $this->get_cumulative_layout_shift(),
            'first_input_delay' => $this->get_first_input_delay(),
            'mobile_friendly_score' => $this->get_mobile_friendly_score(),
            'recommendations' => $this->get_optimization_recommendations()
        ];
        
        return $report;
    }
    
    private function get_page_load_time() {
        // 实现页面加载时间获取
        return 2.5; // 示例值
    }
    
    private function get_first_contentful_paint() {
        // 实现首次内容绘制时间获取
        return 1.2; // 示例值
    }
    
    private function get_largest_contentful_paint() {
        // 实现最大内容绘制时间获取
        return 2.1; // 示例值
    }
    
    private function get_cumulative_layout_shift() {
        // 实现累积布局偏移获取
        return 0.05; // 示例值
    }
    
    private function get_first_input_delay() {
        // 实现首次输入延迟获取
        return 0.1; // 示例值
    }
    
    private function get_mobile_friendly_score() {
        // 实现移动端友好度评分
        return 85; // 示例值
    }
    
    private function get_optimization_recommendations() {
        $recommendations = [];
        
        // 基于性能数据生成优化建议
        $performance_data = $this->get_performance_report();
        
        if ($performance_data['page_load_time'] > 3) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => '优化页面加载时间',
                'description' => '页面加载时间超过3秒，建议优化图片和脚本加载',
                'action' => 'enable_lazy_loading'
            ];
        }
        
        if ($performance_data['largest_contentful_paint'] > 2.5) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => '优化最大内容绘制',
                'description' => '最大内容绘制时间较长，建议优化关键资源加载',
                'action' => 'optimize_critical_resources'
            ];
        }
        
        if ($performance_data['mobile_friendly_score'] < 80) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => '提高移动端友好度',
                'description' => '移动端友好度评分较低，建议优化触摸交互和响应式设计',
                'action' => 'improve_mobile_ux'
            ];
        }
        
        return $recommendations;
    }
}

/**
 * 用户代理检测器
 */
class WAI_User_Agent_Detector {
    
    private $mobile_user_agents = [
        'android', 'webos', 'iphone', 'ipad', 'ipod', 'blackberry', 
        'windows phone', 'mobile', 'phone', 'tablet'
    ];
    
    public function is_mobile($user_agent) {
        if (empty($user_agent)) {
            return false;
        }
        
        $user_agent = strtolower($user_agent);
        
        foreach ($this->mobile_user_agents as $mobile_agent) {
            if (strpos($user_agent, $mobile_agent) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    public function get_device_type($user_agent) {
        if (strpos($user_agent, 'tablet') !== false || 
            strpos($user_agent, 'ipad') !== false) {
            return 'tablet';
        }
        
        if ($this->is_mobile($user_agent)) {
            return 'mobile';
        }
        
        return 'desktop';
    }
    
    public function get_browser_info($user_agent) {
        $browser_info = [
            'browser' => 'unknown',
            'version' => 'unknown',
            'platform' => 'unknown'
        ];
        
        // 简单的浏览器检测
        if (strpos($user_agent, 'chrome') !== false) {
            $browser_info['browser'] = 'chrome';
        } elseif (strpos($user_agent, 'safari') !== false) {
            $browser_info['browser'] = 'safari';
        } elseif (strpos($user_agent, 'firefox') !== false) {
            $browser_info['browser'] = 'firefox';
        }
        
        // 平台检测
        if (strpos($user_agent, 'android') !== false) {
            $browser_info['platform'] = 'android';
        } elseif (strpos($user_agent, 'iphone') !== false || strpos($user_agent, 'ipad') !== false) {
            $browser_info['platform'] = 'ios';
        } elseif (strpos($user_agent, 'windows') !== false) {
            $browser_info['platform'] = 'windows';
        } elseif (strpos($user_agent, 'mac') !== false) {
            $browser_info['platform'] = 'mac';
        } elseif (strpos($user_agent, 'linux') !== false) {
            $browser_info['platform'] = 'linux';
        }
        
        return $browser_info;
    }
}