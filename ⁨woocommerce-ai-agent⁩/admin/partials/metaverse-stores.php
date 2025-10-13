<?php
/**
 * 元宇宙店铺管理界面
 */

if (!defined('ABSPATH')) {
    exit;
}

// 检查元宇宙功能是否启用
if (!get_option('wai_metaverse_enabled')) {
    echo '<div class="notice notice-warning"><p>元宇宙功能未启用。请在设置中启用元宇宙集成。</p></div>';
    return;
}

$metaverse_gateway = Woocommerce_AI_Agent_Web3::get_instance()->managers['metaverse_gateway'] ?? null;
$virtual_stores = $metaverse_gateway ? $metaverse_gateway->get_virtual_stores() : [];
$metaverse_analytics = $metaverse_gateway ? $metaverse_gateway->get_metaverse_analytics('7d') : [];
$supported_platforms = $metaverse_gateway ? $metaverse_gateway->supported_platforms : [];
?>

<div class="wrap wai-metaverse-stores">
    <h1>🌌 元宇宙店铺管理</h1>
    
    <div class="wai-stats-grid">
        <!-- 平台统计 -->
        <div class="wai-stat-card">
            <div class="stat-icon">🏪</div>
            <div class="stat-content">
                <h3>虚拟店铺</h3>
                <div class="stat-number"><?php echo count($virtual_stores); ?></div>
                <div class="stat-trend">活跃店铺数量</div>
            </div>
        </div>
        
        <!-- 用户参与 -->
        <div class="wai-stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>今日访客</h3>
                <div class="stat-number"><?php echo $metaverse_analytics['user_engagement']['daily_visitors'] ?? 0; ?></div>
                <div class="stat-trend">元宇宙用户参与</div>
            </div>
        </div>
        
        <!-- 交易统计 -->
        <div class="wai-stat-card">
            <div class="stat-icon">💎</div>
            <div class="stat-content">
                <h3>元宇宙收入</h3>
                <div class="stat-number"><?php echo $metaverse_analytics['revenue_attribution']['total_revenue'] ?? 0; ?> ETH</div>
                <div class="stat-trend">虚拟商品销售</div>
            </div>
        </div>
        
        <!-- 平台分布 -->
        <div class="wai-stat-card">
            <div class="stat-icon">🌐</div>
            <div class="stat-content">
                <h3>平台覆盖</h3>
                <div class="stat-number"><?php echo count(array_filter($supported_platforms, function($platform) { return $platform['enabled']; })); ?></div>
                <div class="stat-trend">元宇宙平台</div>
            </div>
        </div>
    </div>

    <div class="wai-dashboard-content">
        <div class="wai-columns-2">
            <!-- 虚拟店铺列表 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>🏪 虚拟店铺列表</h2>
                    <div class="panel-actions">
                        <button class="button button-primary" onclick="openCreateStoreModal()">创建新店铺</button>
                        <button class="button" onclick="syncAllStores()">同步所有店铺</button>
                    </div>
                </div>
                <div class="panel-content">
                    <div class="stores-grid">
                        <?php if (!empty($virtual_stores)): ?>
                            <?php foreach ($virtual_stores as $store): ?>
                                <div class="store-card platform-<?php echo $store['platform']; ?>">
                                    <div class="store-header">
                                        <div class="store-platform">
                                            <span class="platform-icon">
                                                <?php 
                                                $platform_icons = [
                                                    'decentraland' => '🏙️',
                                                    'sandbox' => '🎮', 
                                                    'cryptovoxels' => '⚡',
                                                    'somnium' => '🌠'
                                                ];
                                                echo $platform_icons[$store['platform']] ?? '🌐';
                                                ?>
                                            </span>
                                            <span class="platform-name"><?php echo $supported_platforms[$store['platform']]['name'] ?? $store['platform']; ?></span>
                                        </div>
                                        <div class="store-status <?php echo $store['status']; ?>">
                                            <?php echo $store['status'] === 'active' ? '活跃' : '未激活'; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="store-body">
                                        <h3 class="store-name"><?php echo esc_html($store['name']); ?></h3>
                                        <p class="store-description"><?php echo esc_html($store['description']); ?></p>
                                        
                                        <div class="store-metrics">
                                            <div class="metric">
                                                <span class="metric-value"><?php echo $store['traffic_analytics']['daily_visitors'] ?? 0; ?></span>
                                                <span class="metric-label">日访客</span>
                                            </div>
                                            <div class="metric">
                                                <span class="metric-value"><?php echo $store['traffic_analytics']['conversion_rate'] ?? 0; ?>%</span>
                                                <span class="metric-label">转化率</span>
                                            </div>
                                            <div class="metric">
                                                <span class="metric-value"><?php echo count($store['products_displayed'] ?? []); ?></span>
                                                <span class="metric-label">商品数</span>
                                            </div>
                                        </div>
                                        
                                        <div class="store-location">
                                            <span class="location-icon">📍</span>
                                            <span class="location-text"><?php echo $store['location']; ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="store-actions">
                                        <button class="button button-small" onclick="viewStoreDetails('<?php echo $store['id']; ?>')">查看</button>
                                        <button class="button button-small" onclick="editStore('<?php echo $store['id']; ?>')">编辑</button>
                                        <button class="button button-small" onclick="syncStore('<?php echo $store['id']; ?>')">同步</button>
                                        <?php if ($store['status'] === 'active'): ?>
                                            <button class="button button-small button-warning" onclick="deactivateStore('<?php echo $store['id']; ?>')">停用</button>
                                        <?php else: ?>
                                            <button class="button button-small button-primary" onclick="activateStore('<?php echo $store['id']; ?>')">激活</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-stores">
                                <div class="no-stores-icon">🏪</div>
                                <h3>暂无虚拟店铺</h3>
                                <p>在元宇宙平台创建您的第一个虚拟店铺</p>
                                <button class="button button-primary" onclick="openCreateStoreModal()">创建店铺</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 平台性能分析 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>📊 平台性能分析</h2>
                </div>
                <div class="panel-content">
                    <div class="platform-performance">
                        <?php if (!empty($metaverse_analytics['platform_performance'])): ?>
                            <?php foreach ($metaverse_analytics['platform_performance'] as $platform_id => $performance): ?>
                                <?php if ($supported_platforms[$platform_id]['enabled'] ?? false): ?>
                                    <div class="platform-performance-card">
                                        <div class="platform-header">
                                            <div class="platform-info">
                                                <span class="platform-icon">
                                                    <?php 
                                                    $platform_icons = [
                                                        'decentraland' => '🏙️',
                                                        'sandbox' => '🎮',
                                                        'cryptovoxels' => '⚡',
                                                        'somnium' => '🌠'
                                                    ];
                                                    echo $platform_icons[$platform_id] ?? '🌐';
                                                    ?>
                                                </span>
                                                <span class="platform-name"><?php echo $supported_platforms[$platform_id]['name']; ?></span>
                                            </div>
                                            <div class="platform-score">
                                                <?php echo $performance['engagement_rate'] ?? 'N/A'; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="performance-metrics">
                                            <div class="performance-metric">
                                                <label>活跃用户</label>
                                                <div class="metric-bar">
                                                    <div class="metric-fill" style="width: <?php echo min(($performance['active_users'] ?? 0) / 1000 * 100, 100); ?>%"></div>
                                                </div>
                                                <span class="metric-value"><?php echo $performance['active_users'] ?? 0; ?></span>
                                            </div>
                                            
                                            <div class="performance-metric">
                                                <label>交易数量</label>
                                                <div class="metric-bar">
                                                    <div class="metric-fill" style="width: <?php echo min(($performance['transactions'] ?? 0) / 100 * 100, 100); ?>%"></div>
                                                </div>
                                                <span class="metric-value"><?php echo $performance['transactions'] ?? 0; ?></span>
                                            </div>
                                            
                                            <div class="performance-metric">
                                                <label>收入</label>
                                                <div class="metric-bar">
                                                    <div class="metric-fill" style="width: <?php echo min(($performance['revenue'] ?? 0) / 50000 * 100, 100); ?>%"></div>
                                                </div>
                                                <span class="metric-value"><?php echo $performance['revenue'] ?? 0; ?> ETH</span>
                                            </div>
                                        </div>
                                        
                                        <div class="platform-actions">
                                            <button class="button button-small" onclick="viewPlatformAnalytics('<?php echo $platform_id; ?>')">详细分析</button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data">
                                <p>暂无平台性能数据</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 元宇宙设置 -->
        <div class="wai-panel">
            <div class="panel-header">
                <h2>⚙️ 元宇宙设置</h2>
            </div>
            <div class="panel-content">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('wai_metaverse_settings'); ?>
                    <input type="hidden" name="action" value="wai_update_metaverse_settings">
                    
                    <div class="form-sections">
                        <!-- 平台集成 -->
                        <div class="form-section">
                            <h3>平台集成</h3>
                            <table class="form-table">
                                <?php foreach ($supported_platforms as $platform_id => $platform): ?>
                                    <tr>
                                        <th scope="row"><?php echo $platform['name']; ?></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="platforms[]" value="<?php echo $platform_id; ?>" 
                                                    <?php checked($platform['enabled']); ?>>
                                                启用 <?php echo $platform['name']; ?> 集成
                                            </label>
                                            
                                            <?php if ($platform['enabled']): ?>
                                                <div class="platform-config" style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px;">
                                                    <?php switch ($platform_id):
                                                        case 'decentraland': ?>
                                                            <p><strong>场景坐标:</strong> 
                                                                <input type="text" name="decentraland_coordinates" 
                                                                       value="<?php echo esc_attr($platform['scene_coordinates'] ?? ''); ?>" 
                                                                       placeholder="x,y" class="regular-text">
                                                            </p>
                                                            <?php break;
                                                        case 'sandbox': ?>
                                                            <p><strong>地块ID:</strong> 
                                                                <input type="text" name="sandbox_land_id" 
                                                                       value="<?php echo esc_attr($platform['land_id'] ?? ''); ?>" 
                                                                       class="regular-text">
                                                            </p>
                                                            <?php break;
                                                        case 'cryptovoxels': ?>
                                                            <p><strong>地块ID:</strong> 
                                                                <input type="text" name="cryptovoxels_parcel_id" 
                                                                       value="<?php echo esc_attr($platform['parcel_id'] ?? ''); ?>" 
                                                                       class="regular-text">
                                                            </p>
                                                            <?php break;
                                                    endswitch; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                        
                        <!-- 内容设置 -->
                        <div class="form-section">
                            <h3>内容设置</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">自动3D内容生成</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="auto_3d_generation" value="1" <?php checked(get_option('wai_auto_3d_generation'), 1); ?>>
                                            为商品自动生成3D模型
                                        </label>
                                        <p class="description">使用AI自动将2D商品图片转换为3D模型</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">虚拟试穿</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="virtual_tryon" value="1" <?php checked(get_option('wai_virtual_tryon'), 1); ?>>
                                            启用虚拟试穿功能
                                        </label>
                                        <p class="description">允许用户在元宇宙中试穿虚拟服装</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">AR预览</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="ar_preview" value="1" <?php checked(get_option('wai_ar_preview'), 1); ?>>
                                            启用AR预览功能
                                        </label>
                                        <p class="description">为商品生成AR预览体验</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">同步频率</th>
                                    <td>
                                        <select name="sync_frequency">
                                            <option value="realtime" <?php selected(get_option('wai_metaverse_sync_frequency'), 'realtime'); ?>>实时同步</option>
                                            <option value="hourly" <?php selected(get_option('wai_metaverse_sync_frequency'), 'hourly'); ?>>每小时</option>
                                            <option value="daily" <?php selected(get_option('wai_metaverse_sync_frequency'), 'daily'); ?>>每天</option>
                                        </select>
                                        <p class="description">商品数据同步到元宇宙的频率</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php submit_button('保存设置'); ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 创建店铺模态框 -->
<div id="create-store-modal" class="wai-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🏪 创建虚拟店铺</h3>
            <span class="close" onclick="closeCreateStoreModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="create-store-form">
                <div class="form-group">
                    <label for="store_platform">选择平台</label>
                    <select id="store_platform" name="platform" required>
                        <option value="">-- 选择元宇宙平台 --</option>
                        <?php foreach ($supported_platforms as $platform_id => $platform): ?>
                            <?php if ($platform['enabled']): ?>
                                <option value="<?php echo $platform_id; ?>"><?php echo $platform['name']; ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="store_name">店铺名称</label>
                    <input type="text" id="store_name" name="name" required placeholder="输入店铺名称">
                </div>
                
                <div class="form-group">
                    <label for="store_description">店铺描述</label>
                    <textarea id="store_description" name="description" rows="3" placeholder="输入店铺描述"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="store_location">位置坐标</label>
                    <input type="text" id="store_location" name="location" required placeholder="例如: 10,15">
                    <p class="description">在元宇宙平台中的坐标位置</p>
                </div>
                
                <div class="form-group">
                    <label for="store_design">设计主题</label>
                    <select id="store_design" name="design_theme">
                        <option value="modern">现代风格</option>
                        <option value="futuristic">未来风格</option>
                        <option value="minimalist">极简风格</option>
                        <option value="luxury">奢华风格</option>
                        <option value="natural">自然风格</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="button" onclick="closeCreateStoreModal()">取消</button>
                    <button type="submit" class="button button-primary">创建店铺</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 元宇宙店铺管理功能
function openCreateStoreModal() {
    document.getElementById('create-store-modal').style.display = 'block';
}

function closeCreateStoreModal() {
    document.getElementById('create-store-modal').style.display = 'none';
}

function viewStoreDetails(storeId) {
    // 查看店铺详情
    alert('查看店铺详情: ' + storeId);
}

function editStore(storeId) {
    // 编辑店铺
    alert('编辑店铺: ' + storeId);
}

function syncStore(storeId) {
    // 同步店铺
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '同步中...';
    button.disabled = true;
    
    // 模拟API调用
    setTimeout(() => {
        alert('店铺同步完成！');
        button.textContent = originalText;
        button.disabled = false;
    }, 2000);
}

function syncAllStores() {
    // 同步所有店铺
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '同步中...';
    button.disabled = true;
    
    // 模拟API调用
    setTimeout(() => {
        alert('所有店铺同步完成！');
        button.textContent = originalText;
        button.disabled = false;
    }, 3000);
}

function activateStore(storeId) {
    if (confirm('确定要激活这个店铺吗？')) {
        // 激活店铺
        console.log('激活店铺:', storeId);
        location.reload();
    }
}

function deactivateStore(storeId) {
    if (confirm('确定要停用这个店铺吗？')) {
        // 停用店铺
        console.log('停用店铺:', storeId);
        location.reload();
    }
}

function viewPlatformAnalytics(platformId) {
    // 查看平台分析
    alert('查看平台分析: ' + platformId);
}

// 创建店铺表单提交
document.getElementById('create-store-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // 显示加载状态
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = '创建中...';
    submitBtn.disabled = true;
    
    // 模拟API调用
    setTimeout(() => {
        alert('虚拟店铺创建请求已提交！');
        closeCreateStoreModal();
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        location.reload();
    }, 2000);
});

// 点击模态框外部关闭
window.onclick = function(event) {
    const modal = document.getElementById('create-store-modal');
    if (event.target === modal) {
        closeCreateStoreModal();
    }
}

// 平台配置显示/隐藏
document.querySelectorAll('input[name="platforms[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const configDiv = this.closest('td').querySelector('.platform-config');
        if (configDiv) {
            configDiv.style.display = this.checked ? 'block' : 'none';
        }
    });
});
</script>

<style>
.wai-metaverse-stores {
    max-width: 1200px;
}

.stores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.store-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
}

.store-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.store-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
}

.store-platform {
    display: flex;
    align-items: center;
    gap: 8px;
}

.platform-icon {
    font-size: 1.2em;
}

.platform-name {
    font-size: 14px;
    font-weight: bold;
    color: #333;
}

.store-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.store-status.active {
    background: #e7f4e4;
    color: #46b450;
}

.store-status.inactive {
    background: #fbe7e7;
    color: #dc3232;
}

.store-body {
    margin-bottom: 15px;
}

.store-name {
    margin: 0 0 8px 0;
    font-size: 16px;
    color: #23282d;
}

.store-description {
    margin: 0 0 12px 0;
    font-size: 13px;
    color: #666;
    line-height: 1.4;
}

.store-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 12px;
}

.metric {
    text-align: center;
    padding: 8px;
    background: #f9f9f9;
    border-radius: 6px;
}

.metric-value {
    display: block;
    font-size: 16px;
    font-weight: bold;
    color: #23282d;
}

.metric-label {
    display: block;
    font-size: 11px;
    color: #666;
    margin-top: 2px;
}

.store-location {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #888;
}

.location-icon {
    font-size: 1em;
}

.store-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.button-warning {
    background: #dc3232;
    border-color: #dc3232;
    color: white;
}

.button-warning:hover {
    background: #a00;
    border-color: #a00;
}

.no-stores {
    text-align: center;
    padding: 40px 20px;
}

.no-stores-icon {
    font-size: 3em;
    margin-bottom: 15px;
}

.no-stores h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.no-stores p {
    margin: 0 0 20px 0;
    color: #666;
}

.platform-performance {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.platform-performance-card {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 15px;
}

.platform-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.platform-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.platform-score {
    background: #007cba;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.performance-metrics {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

.performance-metric {
    display: flex;
    align-items: center;
    gap: 10px;
}

.performance-metric label {
    width: 80px;
    font-size: 12px;
    color: #666;
}

.metric-bar {
    flex: 1;
    background: #e0e0e0;
    border-radius: 4px;
    height: 8px;
    overflow: hidden;
}

.metric-fill {
    background: linear-gradient(90deg, #007cba, #00a0d2);
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.metric-value {
    width: 60px;
    font-size: 12px;
    font-weight: bold;
    text-align: right;
}

.platform-actions {
    text-align: right;
}

@media (max-width: 1024px) {
    .form-sections {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stores-grid {
        grid-template-columns: 1fr;
    }
    
    .wai-columns-2 {
        grid-template-columns: 1fr;
    }
    
    .store-metrics {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .performance-metric {
        flex-direction: column;
        align-items: stretch;
        gap: 4px;
    }
    
    .performance-metric label {
        width: auto;
    }
}
</style>