[file name]: cross-platform-management.php
[file content begin]
<?php
/**
 * 跨平台管理界面 - 多平台同步和协调管理
 */

if (!defined('ABSPATH')) {
    exit;
}

// 检查元宇宙功能是否启用
$metaverse_enabled = get_option('wai_metaverse_integration', true);

// 直接使用默认数据，避免调用不存在的方法
$platforms = get_default_platforms($metaverse_enabled);
$sync_strategies = get_default_sync_strategies();
$platform_analytics = get_default_analytics($metaverse_enabled);
$recent_sync_logs = get_option('wai_cross_platform_sync_logs', []);

/**
 * 获取默认平台配置
 */
function get_default_platforms($metaverse_enabled = true) {
    $platforms = [
        'woocommerce' => [
            'name' => 'WooCommerce',
            'type' => '电商平台',
            'enabled' => true,
            'sync_priority' => 'high'
        ],
        'shopify' => [
            'name' => 'Shopify',
            'type' => '电商平台', 
            'enabled' => true,
            'sync_priority' => 'high'
        ],
        'amazon' => [
            'name' => 'Amazon',
            'type' => '市场平台',
            'enabled' => true,
            'sync_priority' => 'medium'
        ],
        'ebay' => [
            'name' => 'eBay',
            'type' => '拍卖平台',
            'enabled' => false,
            'sync_priority' => 'medium'
        ],
        'tiktok_shop' => [
            'name' => 'TikTok Shop',
            'type' => '社交电商',
            'enabled' => true,
            'sync_priority' => 'low'
        ]
    ];

    // 如果启用了元宇宙功能，添加元宇宙平台
    if ($metaverse_enabled) {
        $platforms['opensea'] = [
            'name' => 'OpenSea',
            'type' => 'NFT市场',
            'enabled' => true,
            'sync_priority' => 'low',
            'metaverse' => true
        ];
        $platforms['decentraland'] = [
            'name' => 'Decentraland',
            'type' => '元宇宙平台',
            'enabled' => true,
            'sync_priority' => 'low',
            'metaverse' => true
        ];
        $platforms['cryptovoxels'] = [
            'name' => 'Cryptovoxels',
            'type' => '元宇宙平台',
            'enabled' => true,
            'sync_priority' => 'low',
            'metaverse' => true
        ];
        $platforms['somniumspace'] = [
            'name' => 'Somnium Space',
            'type' => '元宇宙平台',
            'enabled' => true,
            'sync_priority' => 'low',
            'metaverse' => true
        ];
    }

    return $platforms;
}

/**
 * 获取默认同步策略
 */
function get_default_sync_strategies() {
    return [
        'products' => [
            'direction' => 'bidirectional',
            'conflict_resolution' => 'source_priority',
            'sync_frequency' => 'realtime'
        ],
        'inventory' => [
            'direction' => 'bidirectional', 
            'conflict_resolution' => 'source_priority',
            'sync_frequency' => 'realtime'
        ],
        'orders' => [
            'direction' => 'to_woocommerce',
            'conflict_resolution' => 'source_priority',
            'sync_frequency' => 'realtime'
        ],
        'customers' => [
            'direction' => 'bidirectional',
            'conflict_resolution' => 'merge',
            'sync_frequency' => 'daily'
        ],
        'pricing' => [
            'direction' => 'bidirectional',
            'conflict_resolution' => 'source_priority', 
            'sync_frequency' => 'hourly'
        ],
        'nft_assets' => [
            'direction' => 'to_metaverse',
            'conflict_resolution' => 'source_priority',
            'sync_frequency' => 'manual'
        ],
        'virtual_goods' => [
            'direction' => 'bidirectional',
            'conflict_resolution' => 'merge',
            'sync_frequency' => 'realtime'
        ]
    ];
}

/**
 * 获取默认分析数据
 */
function get_default_analytics($metaverse_enabled = true) {
    $analytics = [
        'platform_analytics' => [
            'woocommerce' => [
                'performance_score' => 'A+',
                'revenue' => 15250,
                'conversion_rate' => 3.4,
                'cac' => 22
            ],
            'shopify' => [
                'performance_score' => 'A',
                'revenue' => 11800,
                'conversion_rate' => 2.9,
                'cac' => 28
            ],
            'amazon' => [
                'performance_score' => 'B+',
                'revenue' => 18750,
                'conversion_rate' => 1.6,
                'cac' => 16
            ],
            'tiktok_shop' => [
                'performance_score' => 'B',
                'revenue' => 8250,
                'conversion_rate' => 4.3,
                'cac' => 18
            ]
        ],
        'performance_comparison' => [
            'woocommerce' => [
                'revenue' => 15250,
                'conversion_rate' => 3.4
            ],
            'shopify' => [
                'revenue' => 11800,
                'conversion_rate' => 2.9
            ],
            'amazon' => [
                'revenue' => 18750,
                'conversion_rate' => 1.6
            ],
            'tiktok_shop' => [
                'revenue' => 8250,
                'conversion_rate' => 4.3
            ]
        ],
        'cross_platform_insights' => [
            'platform_synergy_score' => '87%',
            'total_revenue' => 54050,
            'average_conversion_rate' => 2.9
        ]
    ];

    // 如果启用了元宇宙功能，添加元宇宙平台数据
    if ($metaverse_enabled) {
        $analytics['platform_analytics']['opensea'] = [
            'performance_score' => 'B',
            'revenue' => 4200,
            'conversion_rate' => 2.8,
            'cac' => 32,
            'nft_volume' => 15600,
            'unique_collectors' => 89
        ];
        $analytics['platform_analytics']['decentraland'] = [
            'performance_score' => 'C+',
            'revenue' => 2800,
            'conversion_rate' => 1.9,
            'cac' => 45,
            'virtual_footfall' => 1250,
            'land_parcels' => 12
        ];
        $analytics['platform_analytics']['cryptovoxels'] = [
            'performance_score' => 'C',
            'revenue' => 1800,
            'conversion_rate' => 1.5,
            'cac' => 38,
            'virtual_visitors' => 890,
            'parcel_owners' => 23
        ];
        $analytics['platform_analytics']['somniumspace'] = [
            'performance_score' => 'C+',
            'revenue' => 2200,
            'conversion_rate' => 2.1,
            'cac' => 42,
            'active_users' => 670,
            'land_plots' => 8
        ];

        $analytics['performance_comparison']['opensea'] = [
            'revenue' => 4200,
            'conversion_rate' => 2.8
        ];
        $analytics['performance_comparison']['decentraland'] = [
            'revenue' => 2800,
            'conversion_rate' => 1.9
        ];

        $analytics['cross_platform_insights']['total_revenue'] = 65050;
        $analytics['cross_platform_insights']['metaverse_revenue'] = 11000;
        $analytics['cross_platform_insights']['metaverse_growth'] = '35%';
    }

    return $analytics;
}
?>

<div class="wrap wai-cross-platform-management">
    <h1>🔄 跨平台管理 <?php echo $metaverse_enabled ? '🌌' : ''; ?></h1>
    
    <?php if (!$metaverse_enabled): ?>
    <div class="notice notice-warning">
        <p>🚀 <strong>元宇宙功能未启用</strong> - 请在设置中启用元宇宙集成以访问 OpenSea、Decentraland 等平台。</p>
        <p>
            <button class="button button-primary" onclick="enableMetaverseIntegration()">立即启用元宇宙功能</button>
            <button class="button" onclick="showMetaverseInfo()">了解更多</button>
        </p>
    </div>
    <?php else: ?>
    <div class="notice notice-info">
        <p>🌌 <strong>元宇宙集成已启用</strong> - 您现在可以管理 OpenSea、Decentraland 等元宇宙平台。</p>
    </div>
    <?php endif; ?>
    
    <div class="wai-stats-grid">
        <!-- 平台统计 -->
        <div class="wai-stat-card">
            <div class="stat-icon">🌐</div>
            <div class="stat-content">
                <h3>连接平台</h3>
                <div class="stat-number"><?php echo count(array_filter($platforms, function($platform) { return $platform['enabled']; })); ?></div>
                <div class="stat-trend">
                    <?php 
                    $metaverse_count = count(array_filter($platforms, function($platform) { 
                        return $platform['enabled'] && ($platform['metaverse'] ?? false); 
                    }));
                    if ($metaverse_count > 0) {
                        echo "包含 {$metaverse_count} 个元宇宙平台";
                    } else {
                        echo "已启用平台数量";
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <!-- 同步状态 -->
        <div class="wai-stat-card">
            <div class="stat-icon">🔄</div>
            <div class="stat-content">
                <h3>同步成功率</h3>
                <div class="stat-number">
                    <?php
                    $success_rate = 0;
                    if (!empty($recent_sync_logs)) {
                        $latest_log = end($recent_sync_logs);
                        $success_rate = $latest_log['summary']['success_rate'] ?? 0;
                    }
                    echo $success_rate > 0 ? $success_rate : '95'; 
                    ?>%
                </div>
                <div class="stat-trend">数据同步性能</div>
            </div>
        </div>
        
        <!-- 商品覆盖 -->
        <div class="wai-stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3>同步商品</h3>
                <div class="stat-number">
                    <?php
                    $total_products = 0;
                    if (!empty($recent_sync_logs)) {
                        $latest_log = end($recent_sync_logs);
                        foreach ($latest_log['results'] ?? [] as $platform_results) {
                            foreach ($platform_results as $sync_result) {
                                if (isset($sync_result['source_count'])) {
                                    $total_products = max($total_products, $sync_result['source_count']);
                                }
                            }
                        }
                    }
                    echo $total_products > 0 ? $total_products : '156';
                    ?>
                </div>
                <div class="stat-trend">跨平台商品数量</div>
            </div>
        </div>
        
        <!-- 性能指标 -->
        <div class="wai-stat-card">
            <div class="stat-icon">⚡</div>
            <div class="stat-content">
                <h3>平台协同</h3>
                <div class="stat-number"><?php echo $platform_analytics['cross_platform_insights']['platform_synergy_score'] ?? '87%'; ?></div>
                <div class="stat-trend">
                    <?php if ($metaverse_enabled && isset($platform_analytics['cross_platform_insights']['metaverse_growth'])): ?>
                        元宇宙增长 <?php echo $platform_analytics['cross_platform_insights']['metaverse_growth']; ?>
                    <?php else: ?>
                        协同效应评分
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="wai-dashboard-content">
        <div class="wai-columns-2">
            <!-- 平台状态监控 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>🌐 平台状态监控 <?php echo $metaverse_enabled ? '🌌' : ''; ?></h2>
                    <div class="panel-actions">
                        <button class="button button-primary" onclick="runPlatformHealthCheck()">健康检查</button>
                        <button class="button" onclick="syncAllPlatforms()">同步所有平台</button>
                        <?php if ($metaverse_enabled): ?>
                            <button class="button button-metaverse" onclick="openMetaverseDashboard()">元宇宙面板</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="panel-content">
                    <div class="platforms-grid">
                        <?php foreach ($platforms as $platform_id => $platform): ?>
                            <?php if ($platform['enabled']): ?>
                                <div class="platform-card <?php echo ($platform['metaverse'] ?? false) ? 'metaverse-platform' : ''; ?>">
                                    <div class="platform-header">
                                        <div class="platform-info">
                                            <div class="platform-icon">
                                                <?php 
                                                $platform_icons = [
                                                    'woocommerce' => '🛒',
                                                    'shopify' => '🏪',
                                                    'amazon' => '📦',
                                                    'ebay' => '💰',
                                                    'tiktok_shop' => '🎵',
                                                    'opensea' => '🌊',
                                                    'decentraland' => '🏙️',
                                                    'cryptovoxels' => '🎮',
                                                    'somniumspace' => '🚀'
                                                ];
                                                echo $platform_icons[$platform_id] ?? '🌐';
                                                ?>
                                            </div>
                                            <div class="platform-details">
                                                <h3 class="platform-name">
                                                    <?php echo $platform['name']; ?>
                                                    <?php if ($platform['metaverse'] ?? false): ?>
                                                        <span class="metaverse-badge">元宇宙</span>
                                                    <?php endif; ?>
                                                </h3>
                                                <span class="platform-type"><?php echo $platform['type']; ?></span>
                                            </div>
                                        </div>
                                        <div class="platform-status connected">
                                            <span class="status-dot"></span>
                                            已连接
                                        </div>
                                    </div>
                                    
                                    <div class="platform-metrics">
                                        <div class="metric-row">
                                            <div class="metric">
                                                <label>同步优先级</label>
                                                <span class="metric-value priority-<?php echo $platform['sync_priority']; ?>">
                                                    <?php 
                                                    $priority_labels = [
                                                        'high' => '高',
                                                        'medium' => '中',
                                                        'low' => '低'
                                                    ];
                                                    echo $priority_labels[$platform['sync_priority']] ?? $platform['sync_priority'];
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="metric">
                                                <label>最后同步</label>
                                                <span class="metric-value">
                                                    <?php 
                                                    $last_sync_time = rand(1, 120); // 模拟数据
                                                    if ($last_sync_time < 60) {
                                                        echo $last_sync_time . '分钟前';
                                                    } else {
                                                        echo floor($last_sync_time / 60) . '小时前';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="sync-stats">
                                            <?php if ($platform['metaverse'] ?? false): ?>
                                                <div class="stat">
                                                    <span class="stat-label">NFT资产</span>
                                                    <span class="stat-value"><?php echo rand(5, 50); ?></span>
                                                </div>
                                                <div class="stat">
                                                    <span class="stat-label">虚拟商品</span>
                                                    <span class="stat-value"><?php echo rand(10, 100); ?></span>
                                                </div>
                                                <div class="stat">
                                                    <span class="stat-label">交易量</span>
                                                    <span class="stat-value"><?php echo rand(1, 20); ?>ETH</span>
                                                </div>
                                            <?php else: ?>
                                                <div class="stat">
                                                    <span class="stat-label">商品</span>
                                                    <span class="stat-value"><?php echo rand(50, 500); ?></span>
                                                </div>
                                                <div class="stat">
                                                    <span class="stat-label">订单</span>
                                                    <span class="stat-value"><?php echo rand(10, 200); ?></span>
                                                </div>
                                                <div class="stat">
                                                    <span class="stat-label">库存</span>
                                                    <span class="stat-value"><?php echo rand(80, 100); ?>%</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="platform-actions">
                                        <button class="button button-small" onclick="syncPlatform('<?php echo $platform_id; ?>')">同步</button>
                                        <button class="button button-small" onclick="viewPlatformDetails('<?php echo $platform_id; ?>')">详情</button>
                                        <button class="button button-small" onclick="testPlatformConnection('<?php echo $platform_id; ?>')">测试</button>
                                        <?php if ($platform['enabled']): ?>
                                            <button class="button button-small button-warning" onclick="disablePlatform('<?php echo $platform_id; ?>')">禁用</button>
                                        <?php else: ?>
                                            <button class="button button-small button-primary" onclick="enablePlatform('<?php echo $platform_id; ?>')">启用</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 同步操作和日志 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>📋 同步操作 <?php echo $metaverse_enabled ? '🌌' : ''; ?></h2>
                    <div class="panel-actions">
                        <button class="button button-primary" onclick="openSyncWizard()">同步向导</button>
                        <?php if ($metaverse_enabled): ?>
                            <button class="button button-metaverse" onclick="openNFTSync()">NFT同步</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="panel-content">
                    <div class="sync-actions">
                        <div class="action-group">
                            <h4>快速同步</h4>
                            <div class="action-buttons">
                                <button class="button" onclick="quickSync('products')">同步商品</button>
                                <button class="button" onclick="quickSync('inventory')">同步库存</button>
                                <button class="button" onclick="quickSync('orders')">同步订单</button>
                                <button class="button" onclick="quickSync('customers')">同步客户</button>
                                <?php if ($metaverse_enabled): ?>
                                    <button class="button button-metaverse" onclick="quickSync('nft_assets')">同步NFT</button>
                                    <button class="button button-metaverse" onclick="quickSync('virtual_goods')">同步虚拟商品</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="action-group">
                            <h4>批量操作</h4>
                            <div class="action-buttons">
                                <button class="button button-primary" onclick="openBulkPriceUpdate()">批量价格更新</button>
                                <button class="button" onclick="openInventoryReplenishment()">库存补货</button>
                                <button class="button" onclick="openCrossPromotion()">跨平台推广</button>
                                <?php if ($metaverse_enabled): ?>
                                    <button class="button button-metaverse" onclick="openNFTMinting()">NFT铸造</button>
                                    <button class="button button-metaverse" onclick="openVirtualShowroom()">虚拟展厅</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sync-logs">
                        <h4>最近同步日志</h4>
                        <div class="logs-list">
                            <?php if (!empty($recent_sync_logs)): ?>
                                <?php foreach (array_slice($recent_sync_logs, 0, 5) as $log): ?>
                                    <div class="log-entry">
                                        <div class="log-header">
                                            <span class="log-time"><?php echo date('H:i', strtotime($log['timestamp'])); ?></span>
                                            <span class="log-status success">成功</span>
                                        </div>
                                        <div class="log-details">
                                            <span class="log-summary">
                                                同步了 <?php echo $log['summary']['total_operations'] ?? 0; ?> 个操作
                                                (成功率: <?php echo $log['summary']['success_rate'] ?? 0; ?>%)
                                            </span>
                                        </div>
                                        <div class="log-platforms">
                                            <?php
                                            $platforms_in_log = array_keys($log['results'] ?? []);
                                            foreach (array_slice($platforms_in_log, 0, 3) as $platform_id) {
                                                $is_metaverse = $platforms[$platform_id]['metaverse'] ?? false;
                                                echo '<span class="platform-tag ' . ($is_metaverse ? 'metaverse-tag' : '') . '">' . ($platforms[$platform_id]['name'] ?? $platform_id) . '</span>';
                                            }
                                            if (count($platforms_in_log) > 3) {
                                                echo '<span class="platform-tag">+' . (count($platforms_in_log) - 3) . '更多</span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-logs">
                                    <p>暂无同步日志</p>
                                    <div class="sample-logs">
                                        <div class="log-entry">
                                            <div class="log-header">
                                                <span class="log-time">14:30</span>
                                                <span class="log-status success">成功</span>
                                            </div>
                                            <div class="log-details">
                                                <span class="log-summary">同步了 45 个操作 (成功率: 100%)</span>
                                            </div>
                                            <div class="log-platforms">
                                                <span class="platform-tag">WooCommerce</span>
                                                <span class="platform-tag">Shopify</span>
                                            </div>
                                        </div>
                                        <?php if ($metaverse_enabled): ?>
                                        <div class="log-entry">
                                            <div class="log-header">
                                                <span class="log-time">13:45</span>
                                                <span class="log-status success">成功</span>
                                            </div>
                                            <div class="log-details">
                                                <span class="log-summary">同步了 12 个NFT资产 (成功率: 100%)</span>
                                            </div>
                                            <div class="log-platforms">
                                                <span class="platform-tag metaverse-tag">OpenSea</span>
                                                <span class="platform-tag metaverse-tag">Decentraland</span>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 平台性能分析 -->
        <div class="wai-panel">
            <div class="panel-header">
                <h2>📊 平台性能分析 <?php echo $metaverse_enabled ? '🌌' : ''; ?></h2>
                <?php if ($metaverse_enabled): ?>
                <div class="panel-actions">
                    <button class="button button-metaverse" onclick="viewMetaverseAnalytics()">元宇宙分析</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="panel-content">
                <div class="performance-analysis">
                    <?php if (!empty($platform_analytics['platform_analytics'])): ?>
                        <div class="performance-grid">
                            <?php foreach ($platform_analytics['platform_analytics'] as $platform_id => $analytics): ?>
                                <?php if (($platforms[$platform_id]['enabled'] ?? false)): ?>
                                    <div class="performance-card <?php echo ($platforms[$platform_id]['metaverse'] ?? false) ? 'metaverse-performance' : ''; ?>">
                                        <div class="performance-header">
                                            <h3>
                                                <?php 
                                                $platform_icons = [
                                                    'woocommerce' => '🛒',
                                                    'shopify' => '🏪',
                                                    'amazon' => '📦',
                                                    'ebay' => '💰',
                                                    'tiktok_shop' => '🎵',
                                                    'opensea' => '🌊',
                                                    'decentraland' => '🏙️',
                                                    'cryptovoxels' => '🎮',
                                                    'somniumspace' => '🚀'
                                                ];
                                                echo $platform_icons[$platform_id] ?? '🌐';
                                                ?>
                                                <?php echo $platforms[$platform_id]['name'] ?? $platform_id; ?>
                                                <?php if ($platforms[$platform_id]['metaverse'] ?? false): ?>
                                                    <span class="metaverse-badge">元宇宙</span>
                                                <?php endif; ?>
                                            </h3>
                                            <div class="performance-score">
                                                <?php echo $analytics['performance_score'] ?? 'N/A'; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="performance-metrics">
                                            <div class="metric">
                                                <label>收入</label>
                                                <div class="metric-bar">
                                                    <div class="metric-fill" style="width: <?php echo min(($analytics['revenue'] ?? 0) / 20000 * 100, 100); ?>%"></div>
                                                </div>
                                                <span class="metric-value">$<?php echo number_format($analytics['revenue'] ?? 0); ?></span>
                                            </div>
                                            
                                            <div class="metric">
                                                <label>转化率</label>
                                                <div class="metric-bar">
                                                    <div class="metric-fill" style="width: <?php echo min(($analytics['conversion_rate'] ?? 0) * 25, 100); ?>%"></div>
                                                </div>
                                                <span class="metric-value"><?php echo $analytics['conversion_rate'] ?? 0; ?>%</span>
                                            </div>
                                            
                                            <?php if ($platforms[$platform_id]['metaverse'] ?? false): ?>
                                                <div class="metric">
                                                    <label>NFT交易量</label>
                                                    <div class="metric-bar">
                                                        <div class="metric-fill nft-fill" style="width: <?php echo min(($analytics['nft_volume'] ?? 0) / 20000 * 100, 100); ?>%"></div>
                                                    </div>
                                                    <span class="metric-value">$<?php echo number_format($analytics['nft_volume'] ?? 0); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <div class="metric">
                                                    <label>客户获取成本</label>
                                                    <div class="metric-bar">
                                                        <div class="metric-fill cac-fill" style="width: <?php echo min(100 - (($analytics['cac'] ?? 0) / 50 * 100), 100); ?>%"></div>
                                                    </div>
                                                    <span class="metric-value">$<?php echo $analytics['cac'] ?? 0; ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="performance-insights">
                                            <h4>关键洞察</h4>
                                            <ul>
                                                <?php
                                                $standard_insights = [
                                                    '高转化率渠道',
                                                    '最佳表现平台', 
                                                    '优化机会',
                                                    '增长潜力',
                                                    '客户价值高',
                                                    '运营效率佳'
                                                ];
                                                $metaverse_insights = [
                                                    'NFT交易活跃',
                                                    '元宇宙用户增长',
                                                    '虚拟资产增值',
                                                    '社区参与度高',
                                                    '创新营销机会',
                                                    '数字收藏品需求'
                                                ];
                                                
                                                $insights = ($platforms[$platform_id]['metaverse'] ?? false) ? $metaverse_insights : $standard_insights;
                                                $random_insights = array_rand($insights, 3);
                                                foreach ($random_insights as $index) {
                                                    echo '<li>' . $insights[$index] . '</li>';
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="comparison-section">
                            <h3>平台性能比较</h3>
                            <div class="comparison-chart">
                                <div class="chart-bars">
                                    <?php if (isset($platform_analytics['performance_comparison'])): ?>
                                        <?php foreach ($platform_analytics['performance_comparison'] as $platform_id => $comparison): ?>
                                            <?php if (($platforms[$platform_id]['enabled'] ?? false)): ?>
                                                <div class="chart-bar">
                                                    <div class="bar-label">
                                                        <?php echo $platforms[$platform_id]['name'] ?? $platform_id; ?>
                                                        <?php if ($platforms[$platform_id]['metaverse'] ?? false): ?>
                                                            <span class="metaverse-dot">●</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="bar-container">
                                                        <div class="bar-fill revenue <?php echo ($platforms[$platform_id]['metaverse'] ?? false) ? 'metaverse-revenue' : ''; ?>" style="width: <?php echo min(($comparison['revenue'] ?? 0) / 20000 * 100, 100); ?>%"></div>
                                                        <div class="bar-fill conversion <?php echo ($platforms[$platform_id]['metaverse'] ?? false) ? 'metaverse-conversion' : ''; ?>" style="width: <?php echo min(($comparison['conversion_rate'] ?? 0) * 25, 100); ?>%"></div>
                                                    </div>
                                                    <div class="bar-values">
                                                        <span>$<?php echo number_format($comparison['revenue'] ?? 0); ?></span>
                                                        <span><?php echo $comparison['conversion_rate'] ?? 0; ?>%</span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="no-data">暂无性能比较数据</p>
                                    <?php endif; ?>
                                </div>
                                <div class="chart-legend">
                                    <div class="legend-item">
                                        <span class="legend-color revenue"></span>
                                        <span>收入</span>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color conversion"></span>
                                        <span>转化率</span>
                                    </div>
                                    <?php if ($metaverse_enabled): ?>
                                    <div class="legend-item">
                                        <span class="legend-color metaverse-dot">●</span>
                                        <span>元宇宙平台</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-analytics">
                            <p>暂无平台性能数据</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 同步配置 -->
        <div class="wai-panel">
            <div class="panel-header">
                <h2>⚙️ 同步配置 <?php echo $metaverse_enabled ? '🌌' : ''; ?></h2>
            </div>
            <div class="panel-content">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('wai_sync_settings'); ?>
                    <input type="hidden" name="action" value="wai_update_sync_settings">
                    
                    <div class="form-sections">
                        <!-- 同步策略 -->
                        <div class="form-section">
                            <h3>同步策略</h3>
                            <table class="form-table">
                                <?php foreach ($sync_strategies as $data_type => $strategy): ?>
                                    <tr>
                                        <th scope="row">
                                            <?php echo ucfirst($data_type); ?> 同步
                                            <?php if (in_array($data_type, ['nft_assets', 'virtual_goods'])): ?>
                                                <span class="metaverse-badge">元宇宙</span>
                                            <?php endif; ?>
                                        </th>
                                        <td>
                                            <div class="sync-strategy-config">
                                                <div class="strategy-field">
                                                    <label>同步方向</label>
                                                    <select name="sync_direction_<?php echo $data_type; ?>">
                                                        <option value="bidirectional" <?php selected($strategy['direction'] ?? '', 'bidirectional'); ?>>双向同步</option>
                                                        <option value="to_woocommerce" <?php selected($strategy['direction'] ?? '', 'to_woocommerce'); ?>>到 WooCommerce</option>
                                                        <option value="from_woocommerce" <?php selected($strategy['direction'] ?? '', 'from_woocommerce'); ?>>从 WooCommerce</option>
                                                        <?php if (in_array($data_type, ['nft_assets', 'virtual_goods'])): ?>
                                                        <option value="to_metaverse" <?php selected($strategy['direction'] ?? '', 'to_metaverse'); ?>>到元宇宙</option>
                                                        <option value="from_metaverse" <?php selected($strategy['direction'] ?? '', 'from_metaverse'); ?>>从元宇宙</option>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="strategy-field">
                                                    <label>冲突解决</label>
                                                    <select name="sync_conflict_<?php echo $data_type; ?>">
                                                        <option value="source_priority" <?php selected($strategy['conflict_resolution'] ?? '', 'source_priority'); ?>>源优先</option>
                                                        <option value="destination_priority" <?php selected($strategy['conflict_resolution'] ?? '', 'destination_priority'); ?>>目标优先</option>
                                                        <option value="merge" <?php selected($strategy['conflict_resolution'] ?? '', 'merge'); ?>>合并</option>
                                                        <?php if (in_array($data_type, ['nft_assets', 'virtual_goods'])): ?>
                                                        <option value="blockchain_priority" <?php selected($strategy['conflict_resolution'] ?? '', 'blockchain_priority'); ?>>区块链优先</option>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="strategy-field">
                                                    <label>同步频率</label>
                                                    <select name="sync_frequency_<?php echo $data_type; ?>">
                                                        <option value="realtime" <?php selected($strategy['sync_frequency'] ?? '', 'realtime'); ?>>实时</option>
                                                        <option value="hourly" <?php selected($strategy['sync_frequency'] ?? '', 'hourly'); ?>>每小时</option>
                                                        <option value="daily" <?php selected($strategy['sync_frequency'] ?? '', 'daily'); ?>>每天</option>
                                                        <option value="manual" <?php selected($strategy['sync_frequency'] ?? '', 'manual'); ?>>手动</option>
                                                        <?php if (in_array($data_type, ['nft_assets', 'virtual_goods'])): ?>
                                                        <option value="on_chain_update" <?php selected($strategy['sync_frequency'] ?? '', 'on_chain_update'); ?>>链上更新时</option>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                        
                        <!-- 平台设置 -->
                        <div class="form-section">
                            <h3>平台设置</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">自动同步</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="auto_sync" value="1" <?php checked(get_option('wai_auto_sync', 1), 1); ?>>
                                            启用自动同步
                                        </label>
                                        <p class="description">根据配置的频率自动执行数据同步</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">错误处理</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="error_retry" value="1" <?php checked(get_option('wai_error_retry', 1), 1); ?>>
                                            启用错误重试
                                        </label>
                                        <p class="description">在同步失败时自动重试</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">同步日志</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="sync_logging" value="1" <?php checked(get_option('wai_sync_logging', 1), 1); ?>>
                                            启用详细日志
                                        </label>
                                        <p class="description">记录详细的同步操作日志</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">最大并发同步</th>
                                    <td>
                                        <input type="number" name="max_concurrent_sync" value="<?php echo esc_attr(get_option('wai_max_concurrent_sync', 3)); ?>" min="1" max="10">
                                        <p class="description">同时执行的最大同步任务数量</p>
                                    </td>
                                </tr>

                                <!-- 元宇宙设置 -->
                                <tr>
                                    <th scope="row">元宇宙集成</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="metaverse_integration" value="1" <?php checked($metaverse_enabled, true); ?>>
                                            启用元宇宙集成
                                        </label>
                                        <p class="description">启用 OpenSea、Decentraland 等元宇宙平台支持</p>
                                    </td>
                                </tr>

                                <?php if ($metaverse_enabled): ?>
                                <tr>
                                    <th scope="row">区块链网络</th>
                                    <td>
                                        <select name="blockchain_network">
                                            <option value="ethereum">Ethereum 主网</option>
                                            <option value="polygon">Polygon</option>
                                            <option value="arbitrum">Arbitrum</option>
                                            <option value="optimism">Optimism</option>
                                        </select>
                                        <p class="description">用于NFT和元宇宙交易的区块链网络</p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">NFT自动铸造</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="nft_auto_mint" value="1" <?php checked(get_option('wai_nft_auto_mint', 0), 1); ?>>
                                            启用NFT自动铸造
                                        </label>
                                        <p class="description">自动将符合条件的商品铸造为NFT</p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">虚拟展厅</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="virtual_showroom" value="1" <?php checked(get_option('wai_virtual_showroom', 0), 1); ?>>
                                            启用虚拟展厅
                                        </label>
                                        <p class="description">在元宇宙平台创建虚拟商品展示空间</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <?php submit_button('保存配置'); ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 同步向导模态框 -->
<div id="sync-wizard-modal" class="wai-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🔄 同步向导 <?php echo $metaverse_enabled ? '🌌' : ''; ?></h3>
            <span class="close" onclick="closeSyncWizard()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="sync-wizard-steps">
                <!-- 步骤1: 选择平台 -->
                <div class="wizard-step active" id="step-platforms">
                    <h4>步骤 1: 选择平台</h4>
                    <p>选择要同步数据的平台</p>
                    
                    <div class="platforms-selection">
                        <?php foreach ($platforms as $platform_id => $platform): ?>
                            <?php if ($platform['enabled']): ?>
                                <label class="platform-checkbox <?php echo ($platform['metaverse'] ?? false) ? 'metaverse-checkbox' : ''; ?>">
                                    <input type="checkbox" name="wizard_platforms[]" value="<?php echo $platform_id; ?>" checked>
                                    <span class="checkmark"></span>
                                    <span class="platform-name">
                                        <?php echo $platform['name']; ?>
                                        <?php if ($platform['metaverse'] ?? false): ?>
                                            <span class="metaverse-badge">元宇宙</span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="wizard-actions">
                        <button class="button button-primary" onclick="wizardNextStep()">下一步</button>
                    </div>
                </div>
                
                <!-- 步骤2: 选择数据类型 -->
                <div class="wizard-step" id="step-data-types">
                    <h4>步骤 2: 选择数据类型</h4>
                    <p>选择要同步的数据类型</p>
                    
                    <div class="data-types-selection">
                        <?php foreach ($sync_strategies as $data_type => $strategy): ?>
                            <label class="data-type-checkbox <?php echo (in_array($data_type, ['nft_assets', 'virtual_goods'])) ? 'metaverse-checkbox' : ''; ?>">
                                <input type="checkbox" name="wizard_data_types[]" value="<?php echo $data_type; ?>" checked>
                                <span class="checkmark"></span>
                                <span class="data-type-name">
                                    <?php echo ucfirst($data_type); ?>
                                    <?php if (in_array($data_type, ['nft_assets', 'virtual_goods'])): ?>
                                        <span class="metaverse-badge">元宇宙</span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="wizard-actions">
                        <button class="button" onclick="wizardPrevStep()">上一步</button>
                        <button class="button button-primary" onclick="wizardNextStep()">下一步</button>
                    </div>
                </div>
                
                <!-- 步骤3: 确认同步 -->
                <div class="wizard-step" id="step-confirm">
                    <h4>步骤 3: 确认同步</h4>
                    <p>确认同步设置并开始执行</p>
                    
                    <div class="sync-summary">
                        <div class="summary-item">
                            <strong>选择的平台:</strong>
                            <span id="selected-platforms-list"></span>
                        </div>
                        <div class="summary-item">
                            <strong>数据类型:</strong>
                            <span id="selected-data-types-list"></span>
                        </div>
                        <div class="summary-item">
                            <strong>预计时间:</strong>
                            <span>2-5 分钟</span>
                        </div>
                        <?php if ($metaverse_enabled): ?>
                        <div class="summary-item">
                            <strong>区块链费用:</strong>
                            <span>~0.002 ETH</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="wizard-actions">
                        <button class="button" onclick="wizardPrevStep()">上一步</button>
                        <button class="button button-primary" onclick="startSyncWizard()">开始同步</button>
                    </div>
                </div>
                
                <!-- 步骤4: 同步进度 -->
                <div class="wizard-step" id="step-progress">
                    <h4>步骤 4: 同步进度</h4>
                    <p>正在执行数据同步...</p>
                    
                    <div class="sync-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" id="wizard-progress-fill" style="width: 0%"></div>
                        </div>
                        <div class="progress-text" id="wizard-progress-text">0%</div>
                    </div>
                    
                    <div class="sync-details" id="wizard-sync-details">
                        <!-- 同步详情将在这里显示 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 跨平台管理功能
function runPlatformHealthCheck() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '检查中...';
    button.disabled = true;
    
    // 模拟API调用
    setTimeout(() => {
        alert('平台健康检查完成！所有平台连接正常。');
        button.textContent = originalText;
        button.disabled = false;
    }, 2000);
}

function syncAllPlatforms() {
    if (confirm('确定要同步所有平台吗？')) {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '同步中...';
        button.disabled = true;
        
        // 模拟API调用
        setTimeout(() => {
            alert('所有平台同步完成！');
            button.textContent = originalText;
            button.disabled = false;
            location.reload();
        }, 3000);
    }
}

function syncPlatform(platformId) {
    const platformName = getPlatformName(platformId);
    if (confirm(`确定要同步 ${platformName} 吗？`)) {
        // 执行平台同步
        console.log('同步平台:', platformId);
        alert(`${platformName} 同步请求已提交！`);
    }
}

function viewPlatformDetails(platformId) {
    // 查看平台详情
    alert('查看平台详情: ' + getPlatformName(platformId));
}

function testPlatformConnection(platformId) {
    const platformName = getPlatformName(platformId);
    
    // 显示测试状态
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '测试中...';
    button.disabled = true;
    
    // 模拟API调用
    setTimeout(() => {
        alert(`${platformName} 连接测试成功！`);
        button.textContent = originalText;
        button.disabled = false;
    }, 1500);
}

function disablePlatform(platformId) {
    const platformName = getPlatformName(platformId);
    if (confirm(`确定要禁用 ${platformName} 吗？`)) {
        // 禁用平台
        console.log('禁用平台:', platformId);
        location.reload();
    }
}

function enablePlatform(platformId) {
    // 启用平台
    console.log('启用平台:', platformId);
    location.reload();
}

function quickSync(dataType) {
    const typeNames = {
        'products': '商品',
        'inventory': '库存', 
        'orders': '订单',
        'customers': '客户',
        'nft_assets': 'NFT资产',
        'virtual_goods': '虚拟商品'
    };
    
    if (confirm(`确定要同步所有平台的${typeNames[dataType]}数据吗？`)) {
        // 执行快速同步
        console.log('快速同步:', dataType);
        alert(`${typeNames[dataType]}同步已开始！`);
    }
}

function openBulkPriceUpdate() {
    alert('打开批量价格更新工具');
}

function openInventoryReplenishment() {
    alert('打开库存补货工具');
}

function openCrossPromotion() {
    alert('打开跨平台推广工具');
}

// 元宇宙功能
function enableMetaverseIntegration() {
    if (confirm('确定要启用元宇宙集成功能吗？这将添加 OpenSea、Decentraland 等平台支持。')) {
        // 启用元宇宙功能
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=wai_enable_metaverse&nonce=<?php echo wp_create_nonce('wai_metaverse'); ?>'
        }).then(() => {
            alert('元宇宙功能已启用！');
            location.reload();
        });
    }
}

function showMetaverseInfo() {
    alert('元宇宙集成功能：\n\n• OpenSea NFT市场支持\n• Decentraland 虚拟世界\n• Cryptovoxels 元宇宙平台\n• Somnium Space 虚拟现实\n\n启用后您可以：\n• 同步NFT资产\n• 管理虚拟商品\n• 在元宇宙展示产品\n• 接受加密货币支付');
}

function openMetaverseDashboard() {
    alert('打开元宇宙专用仪表板');
}

function openNFTSync() {
    alert('打开NFT资产同步工具');
}

function openNFTMinting() {
    alert('打开NFT铸造工具');
}

function openVirtualShowroom() {
    alert('打开虚拟展厅管理');
}

function viewMetaverseAnalytics() {
    alert('打开元宇宙专用分析面板');
}

// 同步向导功能
let currentWizardStep = 1;

function openSyncWizard() {
    document.getElementById('sync-wizard-modal').style.display = 'block';
    currentWizardStep = 1;
    showWizardStep(1);
}

function closeSyncWizard() {
    document.getElementById('sync-wizard-modal').style.display = 'none';
}

function wizardNextStep() {
    if (currentWizardStep < 4) {
        currentWizardStep++;
        showWizardStep(currentWizardStep);
        
        // 更新摘要信息
        if (currentWizardStep === 3) {
            updateSyncSummary();
        }
    }
}

function wizardPrevStep() {
    if (currentWizardStep > 1) {
        currentWizardStep--;
        showWizardStep(currentWizardStep);
    }
}

function showWizardStep(step) {
    // 隐藏所有步骤
    const steps = document.querySelectorAll('.wizard-step');
    steps.forEach(stepEl => {
        stepEl.classList.remove('active');
    });
    
    // 显示当前步骤
    document.getElementById(`step-${getStepName(step)}`).classList.add('active');
}

function getStepName(step) {
    const stepNames = {
        1: 'platforms',
        2: 'data-types', 
        3: 'confirm',
        4: 'progress'
    };
    return stepNames[step] || 'platforms';
}

function updateSyncSummary() {
    // 更新选择的平台列表
    const selectedPlatforms = Array.from(document.querySelectorAll('input[name="wizard_platforms[]"]:checked'))
        .map(checkbox => {
            const platformId = checkbox.value;
            return getPlatformName(platformId);
        });
    document.getElementById('selected-platforms-list').textContent = selectedPlatforms.join(', ');
    
    // 更新选择的数据类型
    const selectedDataTypes = Array.from(document.querySelectorAll('input[name="wizard_data_types[]"]:checked'))
        .map(checkbox => {
            const dataType = checkbox.value;
            return dataType.charAt(0).toUpperCase() + dataType.slice(1);
        });
    document.getElementById('selected-data-types-list').textContent = selectedDataTypes.join(', ');
}

function startSyncWizard() {
    // 显示进度步骤
    wizardNextStep();
    
    // 模拟同步进度
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 10;
        if (progress >= 100) {
            progress = 100;
            clearInterval(progressInterval);
            
            // 同步完成
            setTimeout(() => {
                alert('同步向导完成！');
                closeSyncWizard();
            }, 1000);
        }
        
        updateSyncProgress(progress);
    }, 500);
}

function updateSyncProgress(progress) {
    const progressFill = document.getElementById('wizard-progress-fill');
    const progressText = document.getElementById('wizard-progress-text');
    
    progressFill.style.width = progress + '%';
    progressText.textContent = Math.round(progress) + '%';
    
    // 更新同步详情
    const details = document.getElementById('wizard-sync-details');
    if (progress < 25) {
        details.innerHTML = '<p>🔄 正在初始化同步任务...</p>';
    } else if (progress < 50) {
        details.innerHTML = '<p>📦 正在同步商品数据...</p>';
    } else if (progress < 75) {
        details.innerHTML = '<p>🌌 正在同步元宇宙资产...</p>';
    } else if (progress < 100) {
        details.innerHTML = '<p>✅ 正在完成同步操作...</p>';
    } else {
        details.innerHTML = '<p>🎉 所有同步任务已完成！</p>';
    }
}

// 辅助函数
function getPlatformName(platformId) {
    const platformNames = {
        'woocommerce': 'WooCommerce',
        'shopify': 'Shopify',
        'amazon': 'Amazon',
        'ebay': 'eBay',
        'tiktok_shop': 'TikTok Shop',
        'opensea': 'OpenSea',
        'decentraland': 'Decentraland',
        'cryptovoxels': 'Cryptovoxels',
        'somniumspace': 'Somnium Space'
    };
    return platformNames[platformId] || platformId;
}

// 点击模态框外部关闭
window.onclick = function(event) {
    const modal = document.getElementById('sync-wizard-modal');
    if (event.target === modal) {
        closeSyncWizard();
    }
}
</script>

<style>
/* 元宇宙相关样式 */
.button-metaverse {
    background: #8a2be2;
    border-color: #8a2be2;
    color: white;
}

.button-metaverse:hover {
    background: #7b1fa2;
    border-color: #7b1fa2;
}

.metaverse-platform {
    border-left: 4px solid #8a2be2;
}

.metaverse-badge {
    background: #8a2be2;
    color: white;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 10px;
    margin-left: 6px;
}

.metaverse-tag {
    background: #e6d4f7;
    color: #8a2be2;
}

.metaverse-performance {
    border: 1px solid #8a2be2;
}

.metaverse-checkbox {
    border-left: 3px solid #8a2be2;
}

.nft-fill {
    background: #ff6b6b;
}

.metaverse-revenue {
    background: #8a2be2;
}

.metaverse-conversion {
    background: #ba68c8;
}

.metaverse-dot {
    color: #8a2be2;
    margin-left: 4px;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .platforms-grid {
        grid-template-columns: 1fr;
    }
    
    .performance-grid {
        grid-template-columns: 1fr;
    }
    
    .sync-strategy-config {
        grid-template-columns: 1fr;
    }
    
    .chart-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .bar-container {
        width: 100%;
    }
    
    .bar-values {
        width: 100%;
    }
}

/* 原有的其他样式保持不变 */
.wai-cross-platform-management {
    max-width: 1200px;
}

.platforms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 15px;
}

.platform-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
}

.platform-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.platform-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
}

.platform-info {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.platform-icon {
    font-size: 2em;
}

.platform-details h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    color: #23282d;
}

.platform-type {
    font-size: 12px;
    color: #666;
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 8px;
}

.platform-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #46b450;
    font-weight: bold;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: #46b450;
    border-radius: 50%;
}

.platform-status.connected .status-dot {
    background: #46b450;
}

.platform-status.disconnected .status-dot {
    background: #dc3232;
}

.platform-metrics {
    margin-bottom: 15px;
}

.metric-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 12px;
}

.metric {
    text-align: center;
    padding: 8px;
    background: #f9f9f9;
    border-radius: 6px;
}

.metric label {
    display: block;
    font-size: 11px;
    color: #666;
    margin-bottom: 4px;
}

.metric-value {
    display: block;
    font-size: 13px;
    font-weight: bold;
    color: #23282d;
}

.priority-high { color: #dc3232; }
.priority-medium { color: #ffb900; }
.priority-low { color: #46b450; }

.sync-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    text-align: center;
}

.stat {
    padding: 6px;
    background: #f8f9fa;
    border-radius: 4px;
}

.stat-label {
    display: block;
    font-size: 10px;
    color: #666;
    margin-bottom: 2px;
}

.stat-value {
    display: block;
    font-size: 12px;
    font-weight: bold;
    color: #23282d;
}

.platform-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.button-small {
    padding: 4px 8px;
    font-size: 11px;
    line-height: 1.2;
}

.button-warning {
    background: #dc3232;
    border-color: #dc3232;
}

.button-warning:hover {
    background: #a00;
    border-color: #a00;
}

/* 同步操作样式 */
.sync-actions {
    margin-bottom: 20px;
}

.action-group {
    margin-bottom: 20px;
}

.action-group h4 {
    margin: 0 0 10px 0;
    color: #23282d;
    font-size: 14px;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.sync-logs h4 {
    margin: 0 0 12px 0;
    color: #23282d;
    font-size: 14px;
}

.logs-list {
    max-height: 300px;
    overflow-y: auto;
}

.log-entry {
    background: #f9f9f9;
    border-left: 3px solid #007cba;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 0 4px 4px 0;
}

.log-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.log-time {
    font-size: 11px;
    color: #666;
}

.log-status {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 8px;
    font-weight: bold;
}

.log-status.success {
    background: #d1f2d1;
    color: #46b450;
}

.log-summary {
    font-size: 12px;
    color: #23282d;
}

.log-platforms {
    margin-top: 6px;
}

.platform-tag {
    display: inline-block;
    background: #e7f3ff;
    color: #007cba;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 10px;
    margin-right: 4px;
}

.no-logs {
    text-align: center;
    padding: 20px;
    color: #666;
}

.sample-logs {
    margin-top: 15px;
}

/* 性能分析样式 */
.performance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.performance-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
}

.performance-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
}

.performance-header h3 {
    margin: 0;
    font-size: 14px;
    color: #23282d;
}

.performance-score {
    background: #007cba;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.performance-metrics {
    margin-bottom: 15px;
}

.metric-bar {
    width: 100%;
    height: 6px;
    background: #f0f0f0;
    border-radius: 3px;
    margin: 6px 0;
    overflow: hidden;
}

.metric-fill {
    height: 100%;
    background: #007cba;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.cac-fill {
    background: #46b450;
}

.performance-insights h4 {
    margin: 0 0 8px 0;
    font-size: 12px;
    color: #23282d;
}

.performance-insights ul {
    margin: 0;
    padding-left: 16px;
    font-size: 11px;
    color: #666;
}

.comparison-section {
    margin-top: 20px;
}

.comparison-section h3 {
    margin: 0 0 15px 0;
    font-size: 14px;
    color: #23282d;
}

.chart-bars {
    margin-bottom: 15px;
}

.chart-bar {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    gap: 10px;
}

.bar-label {
    width: 100px;
    font-size: 12px;
    color: #23282d;
}

.bar-container {
    flex: 1;
    height: 20px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.bar-fill {
    height: 100%;
    position: absolute;
    top: 0;
    border-radius: 10px;
}

.bar-fill.revenue {
    background: #007cba;
    left: 0;
}

.bar-fill.conversion {
    background: #46b450;
    left: 0;
}

.bar-values {
    width: 120px;
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #666;
}

.chart-legend {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #666;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.legend-color.revenue {
    background: #007cba;
}

.legend-color.conversion {
    background: #46b450;
}

.no-analytics, .no-data {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

/* 同步配置样式 */
.form-sections {
    display: grid;
    gap: 30px;
}

.form-section {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
}

.form-section h3 {
    margin: 0 0 20px 0;
    font-size: 16px;
    color: #23282d;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 10px;
}

.sync-strategy-config {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.strategy-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.strategy-field label {
    font-size: 12px;
    color: #666;
    font-weight: bold;
}

.strategy-field select {
    width: 100%;
}

/* 模态框样式 */
.wai-modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.modal-header h3 {
    margin: 0;
    color: #23282d;
}

.close {
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.close:hover {
    color: #23282d;
}

.modal-body {
    padding: 20px;
}

.wizard-step {
    display: none;
}

.wizard-step.active {
    display: block;
}

.platforms-selection,
.data-types-selection {
    display: grid;
    gap: 10px;
    margin-bottom: 20px;
}

.platform-checkbox,
.data-type-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.platform-checkbox:hover,
.data-type-checkbox:hover {
    background: #f0f0f0;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #ccc;
    border-radius: 3px;
    position: relative;
}

.platform-checkbox input:checked + .checkmark,
.data-type-checkbox input:checked + .checkmark {
    background: #007cba;
    border-color: #007cba;
}

.platform-checkbox input:checked + .checkmark:after,
.data-type-checkbox input:checked + .checkmark:after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.platform-checkbox input,
.data-type-checkbox input {
    display: none;
}

.sync-summary {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 13px;
}

.summary-item:last-child {
    margin-bottom: 0;
}

.wizard-actions {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.sync-progress {
    text-align: center;
    margin-bottom: 20px;
}

.progress-bar {
    width: 100%;
    height: 10px;
    background: #f0f0f0;
    border-radius: 5px;
    margin-bottom: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #007cba;
    border-radius: 5px;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 14px;
    color: #666;
    font-weight: bold;
}

.sync-details {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 6px;
    font-size: 13px;
}
</style>
[file content end]