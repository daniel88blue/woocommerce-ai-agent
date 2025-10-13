<?php
/**
 * Web3仪表板 - 区块链和NFT管理界面
 */

if (!defined('ABSPATH')) {
    exit;
}

// 检查Web3功能是否启用
if (!get_option('wai_web3_enabled')) {
    echo '<div class="notice notice-warning"><p>Web3功能未启用。请在设置中启用Web3集成。</p></div>';
    return;
}

$web3_integration = Woocommerce_AI_Agent_Web3::get_instance()->managers['web3_integration'] ?? null;
$nft_stats = $web3_integration ? $web3_integration->get_nft_stats() : [];
$blockchain_status = $web3_integration ? $web3_integration->get_blockchain_status() : [];
$recent_transactions = $web3_integration ? $web3_integration->get_recent_transactions() : [];
?>

<div class="wrap wai-web3-dashboard">
    <h1>🌐 Web3 仪表板</h1>
    
    <div class="wai-stats-grid">
        <!-- NFT统计 -->
        <div class="wai-stat-card">
            <div class="stat-icon">🖼️</div>
            <div class="stat-content">
                <h3>NFT总量</h3>
                <div class="stat-number"><?php echo $nft_stats['total_nfts'] ?? 0; ?></div>
                <div class="stat-trend"><?php echo $nft_stats['growth_rate'] ?? '0%'; ?> 增长</div>
            </div>
        </div>
        
        <!-- 区块链状态 -->
        <div class="wai-stat-card">
            <div class="stat-icon">⛓️</div>
            <div class="stat-content">
                <h3>区块链状态</h3>
                <div class="stat-number <?php echo ($blockchain_status['status'] ?? 'disconnected') === 'connected' ? 'status-connected' : 'status-disconnected'; ?>">
                    <?php echo ($blockchain_status['status'] ?? 'disconnected') === 'connected' ? '已连接' : '未连接'; ?>
                </div>
                <div class="stat-trend">区块高度: <?php echo $blockchain_status['block_number'] ?? 'N/A'; ?></div>
            </div>
        </div>
        
        <!-- 交易统计 -->
        <div class="wai-stat-card">
            <div class="stat-icon">💸</div>
            <div class="stat-content">
                <h3>今日交易</h3>
                <div class="stat-number"><?php echo $nft_stats['today_transactions'] ?? 0; ?></div>
                <div class="stat-trend">总交易量: <?php echo $nft_stats['total_volume'] ?? '0 ETH'; ?></div>
            </div>
        </div>
        
        <!-- 钱包连接 -->
        <div class="wai-stat-card">
            <div class="stat-icon">👛</div>
            <div class="stat-content">
                <h3>钱包连接</h3>
                <div class="stat-number"><?php echo $nft_stats['connected_wallets'] ?? 0; ?></div>
                <div class="stat-trend">活跃用户</div>
            </div>
        </div>
    </div>

    <div class="wai-dashboard-content">
        <div class="wai-columns-2">
            <!-- NFT管理 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>🎨 NFT管理</h2>
                    <div class="panel-actions">
                        <button class="button button-primary" onclick="openNFTMintingModal()">铸造NFT</button>
                        <button class="button" onclick="refreshNFTStats()">刷新</button>
                    </div>
                </div>
                <div class="panel-content">
                    <div class="nft-list">
                        <?php if (!empty($nft_stats['recent_nfts'])): ?>
                            <?php foreach (array_slice($nft_stats['recent_nfts'], 0, 5) as $nft): ?>
                                <div class="nft-item">
                                    <div class="nft-image">
                                        <img src="<?php echo esc_url($nft['image']); ?>" alt="<?php echo esc_attr($nft['name']); ?>">
                                    </div>
                                    <div class="nft-details">
                                        <h4><?php echo esc_html($nft['name']); ?></h4>
                                        <p class="nft-id">ID: <?php echo esc_html($nft['token_id']); ?></p>
                                        <p class="nft-price"><?php echo esc_html($nft['price']); ?> ETH</p>
                                        <div class="nft-actions">
                                            <button class="button button-small" onclick="viewNFTDetails('<?php echo $nft['token_id']; ?>')">查看</button>
                                            <button class="button button-small" onclick="transferNFT('<?php echo $nft['token_id']; ?>')">转移</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-data">暂无NFT数据</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 区块链交易 -->
            <div class="wai-panel">
                <div class="panel-header">
                    <h2>🔗 最近交易</h2>
                </div>
                <div class="panel-content">
                    <div class="transactions-list">
                        <?php if (!empty($recent_transactions)): ?>
                            <?php foreach ($recent_transactions as $tx): ?>
                                <div class="transaction-item <?php echo $tx['status']; ?>">
                                    <div class="tx-icon">
                                        <?php echo $tx['type'] === 'mint' ? '🆕' : '🔄'; ?>
                                    </div>
                                    <div class="tx-details">
                                        <div class="tx-header">
                                            <span class="tx-type"><?php echo $tx['type'] === 'mint' ? '铸造' : '转移'; ?></span>
                                            <span class="tx-amount"><?php echo esc_html($tx['amount']); ?> ETH</span>
                                        </div>
                                        <div class="tx-meta">
                                            <span class="tx-hash"><?php echo substr($tx['hash'], 0, 10) . '...'; ?></span>
                                            <span class="tx-time"><?php echo human_time_diff(strtotime($tx['timestamp'])); ?>前</span>
                                        </div>
                                    </div>
                                    <div class="tx-status <?php echo $tx['status']; ?>">
                                        <?php echo $tx['status'] === 'confirmed' ? '已确认' : '待确认'; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-data">暂无交易记录</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Web3设置 -->
        <div class="wai-panel">
            <div class="panel-header">
                <h2>⚙️ Web3设置</h2>
            </div>
            <div class="panel-content">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('wai_web3_settings'); ?>
                    <input type="hidden" name="action" value="wai_update_web3_settings">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">区块链网络</th>
                            <td>
                                <select name="web3_network" id="web3_network">
                                    <option value="ethereum" <?php selected(get_option('wai_web3_network'), 'ethereum'); ?>>Ethereum 主网</option>
                                    <option value="polygon" <?php selected(get_option('wai_web3_network'), 'polygon'); ?>>Polygon 主网</option>
                                    <option value="goerli" <?php selected(get_option('wai_web3_network'), 'goerli'); ?>>Goerli 测试网</option>
                                    <option value="mumbai" <?php selected(get_option('wai_web3_network'), 'mumbai'); ?>>Mumbai 测试网</option>
                                </select>
                                <p class="description">选择要连接的区块链网络</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">智能合约地址</th>
                            <td>
                                <input type="text" name="contract_address" value="<?php echo esc_attr(get_option('wai_contract_address')); ?>" class="regular-text">
                                <p class="description">NFT智能合约地址</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">RPC端点</th>
                            <td>
                                <input type="text" name="rpc_endpoint" value="<?php echo esc_attr(get_option('wai_rpc_endpoint')); ?>" class="regular-text">
                                <p class="description">区块链节点RPC端点</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">自动NFT铸造</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="auto_nft_minting" value="1" <?php checked(get_option('wai_auto_nft_minting'), 1); ?>>
                                    为新商品自动铸造NFT
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('保存设置'); ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- NFT铸造模态框 -->
<div id="nft-minting-modal" class="wai-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🎨 铸造新NFT</h3>
            <span class="close" onclick="closeNFTMintingModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="nft-minting-form">
                <div class="form-group">
                    <label for="nft_product">选择商品</label>
                    <select id="nft_product" name="product_id" required>
                        <option value="">-- 选择商品 --</option>
                        <?php
                        $products = wc_get_products(['status' => 'publish', 'limit' => 50]);
                        foreach ($products as $product) {
                            echo '<option value="' . $product->get_id() . '">' . $product->get_name() . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="nft_edition">版本数量</label>
                    <input type="number" id="nft_edition" name="edition_size" value="1" min="1" max="1000">
                    <p class="description">设置NFT的版本数量（1为唯一版本）</p>
                </div>
                
                <div class="form-group">
                    <label for="nft_royalty">版税比例</label>
                    <input type="number" id="nft_royalty" name="royalty_percentage" value="10" min="0" max="50" step="0.1">
                    <p class="description">设置二级市场销售的版税比例 (%)</p>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="button" onclick="closeNFTMintingModal()">取消</button>
                    <button type="submit" class="button button-primary">开始铸造</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// NFT铸造功能
function openNFTMintingModal() {
    document.getElementById('nft-minting-modal').style.display = 'block';
}

function closeNFTMintingModal() {
    document.getElementById('nft-minting-modal').style.display = 'none';
}

function viewNFTDetails(tokenId) {
    // 查看NFT详情
    alert('查看NFT详情: ' + tokenId);
}

function transferNFT(tokenId) {
    // 转移NFT
    const recipient = prompt('请输入接收方地址:');
    if (recipient) {
        // 执行转移逻辑
        console.log('转移NFT', tokenId, '到', recipient);
    }
}

function refreshNFTStats() {
    // 刷新NFT统计
    location.reload();
}

// NFT铸造表单提交
document.getElementById('nft-minting-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // 显示加载状态
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = '铸造中...';
    submitBtn.disabled = true;
    
    // 模拟API调用
    setTimeout(() => {
        alert('NFT铸造请求已提交！');
        closeNFTMintingModal();
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        refreshNFTStats();
    }, 2000);
});

// 点击模态框外部关闭
window.onclick = function(event) {
    const modal = document.getElementById('nft-minting-modal');
    if (event.target === modal) {
        closeNFTMintingModal();
    }
}
</script>

<style>
.wai-web3-dashboard {
    max-width: 1200px;
}

.wai-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.wai-stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
}

.stat-icon {
    font-size: 2em;
    margin-right: 15px;
}

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #666;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    margin: 5px 0;
}

.stat-trend {
    font-size: 12px;
    color: #888;
}

.status-connected {
    color: #46b450;
}

.status-disconnected {
    color: #dc3232;
}

.wai-dashboard-content {
    margin-top: 30px;
}

.wai-columns-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.wai-panel {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.panel-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.panel-header h2 {
    margin: 0;
}

.panel-actions {
    display: flex;
    gap: 10px;
}

.panel-content {
    padding: 20px;
}

.nft-list, .transactions-list {
    max-height: 400px;
    overflow-y: auto;
}

.nft-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.nft-item:last-child {
    border-bottom: none;
}

.nft-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    margin-right: 15px;
}

.nft-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.nft-details h4 {
    margin: 0 0 5px 0;
}

.nft-id, .nft-price {
    font-size: 12px;
    color: #666;
    margin: 2px 0;
}

.nft-actions {
    margin-top: 8px;
    display: flex;
    gap: 5px;
}

.button-small {
    padding: 4px 8px;
    font-size: 12px;
}

.transaction-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.transaction-item:last-child {
    border-bottom: none;
}

.tx-icon {
    font-size: 1.2em;
    margin-right: 15px;
}

.tx-details {
    flex: 1;
}

.tx-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
}

.tx-type {
    font-weight: bold;
}

.tx-amount {
    color: #46b450;
}

.tx-meta {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #888;
}

.tx-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.tx-status.confirmed {
    background: #e7f4e4;
    color: #46b450;
}

.tx-status.pending {
    background: #fff3cd;
    color: #856404;
}

.no-data {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 40px 20px;
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
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
}

.close {
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.close:hover {
    color: #666;
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .wai-columns-2 {
        grid-template-columns: 1fr;
    }
    
    .wai-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>