    <?php require_once __DIR__ . '/session.php'; ?>
    <?php
    $user_cargo = $_SESSION['cargo'] ?? 'Cliente';
    if (!in_array($user_cargo, ['Funcionario', 'Administrador'])) {
        // Redireciona para a página principal se não tiver permissão
        header("Location: index.php?error=access_denied");
        exit;
    }

    require_once 'connection.php';

    // Buscar todos os pedidos
    $pedidos = array();
    try {
        $sql = "SELECT DISTINCT pf.codigo_pedido as cod_pedido_base,
                    p.status_pedido, pf.total_final, pf.data_pedido, pf.id_usuario,
                    u.primeiro_nome, u.ultimo_nome, u.email
                FROM pedidos_finalizados pf
                LEFT JOIN pedidos p ON SUBSTRING(p.cod_pedido, 1, LOCATE('-', p.cod_pedido, LOCATE('-', p.cod_pedido) + 1) - 1) = pf.codigo_pedido
                LEFT JOIN usuario u ON pf.id_usuario = u.id_usuario
                ORDER BY pf.data_pedido DESC";
        
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        
    } catch (Exception $e) {
        error_log("Erro ao buscar pedidos: " . $e->getMessage());
    }

    // Função para formatar data
    function formatarData($data) {
        return date('d/m/Y', strtotime($data));
    }

    // Função para obter classe CSS do status
    function getStatusClass($status) {
        switch ($status) {
            case 'Entregue':
                return 'delivered';
            case 'Em Processamento':
                return 'processing';
            case 'Cancelado':
                return 'cancelled';
            default:
                return 'processing';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gerenciar Vendas - Painel Admin</title>
        <link rel="stylesheet" href="admin.css"> 
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        
        <style>
            .management-section {
                display: block; /* Garante que a seção principal de vendas apareça */
            }
            
            /* Ajuste do layout de sub-seção para o formato de arquivo único */
            #sales-list-view.sub-section {
                display: block; 
            }
        </style>
    </head>
    <body>
        <div class="admin-layout">
            
            <aside class="sidebar">
                <div class="sidebar-header">
                    <i class="fas fa-tachometer-alt"></i>
                    <h2>Painel Admin</h2>
                </div>
                <nav class="sidebar-nav">
                    <a href="admin.php" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
                    <a href="produtos.php" class="nav-item"><i class="fas fa-box-open"></i> Produtos</a>
                    <a href="vendas.php" class="nav-item"><i class="fas fa-chart-line"></i> Vendas</a>
                    <a href="usuarios.php" class="nav-item sidebar-link active" data-target="usuarios-section"><i class="fas fa-users"></i> Usuários</a> 
                    <a href="suporteAdmin.php" class="nav-item sidebar-link" data-target="suporte-section"><i class="fas fa-headset"></i> Suporte</a>
                    <a href="cupons.php" class="nav-item"><i class="fas fa-tags"></i> Cupons</a>
                </nav>
                <div class="logout-section">
                    <a href="index.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </aside>

            <main class="main-content">
                <h1 class="page-title">Gerenciamento de Vendas e Pedidos</h1>
                
                <section id="vendas" class="management-section">
                    <div id="sales-list-view" class="sub-section active">
                        <div class="action-bar">
                            <div class="search-container">
                                <input type="text" placeholder="Buscar por Pedido ID ou Nome do Cliente..." class="search-input" id="search-input">
                                <button type="button" class="clear-search-btn" id="clear-search-btn" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="search-info">
                                <small>Digite o código do pedido (ex: SPDZ-0002) ou nome do cliente</small>
                            </div>
                        </div>
                        
                        <div class="data-table">
                            <div class="table-header sales-grid-template">
                                <span>Pedido ID</span>
                                <span>Data</span>
                                <span>Cliente</span>
                                <span>Total</span>
                                <span>Status</span>
                                <span>Ações</span>
                            </div>
                            
                            <?php if (!empty($pedidos)): ?>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <div class="table-row sales-grid-template" data-order-id="<?php echo htmlspecialchars($pedido['cod_pedido_base']); ?>">
                                        <span class="cell-data" data-label="ID:">#<?php echo htmlspecialchars($pedido['cod_pedido_base']); ?></span>
                                        <span class="cell-data" data-label="Data:"><?php echo formatarData($pedido['data_pedido']); ?></span>
                                        <span class="cell-data" data-label="Cliente:">
                                            <?php 
                                            $nome_cliente = trim($pedido['primeiro_nome'] . ' ' . $pedido['ultimo_nome']);
                                            echo htmlspecialchars($nome_cliente ?: 'Cliente não encontrado'); 
                                            ?>
                                        </span>
                                        <span class="cell-data" data-label="Total:">R$ <?php echo number_format($pedido['total_final'], 2, ',', '.'); ?></span>
                                        <span class="cell-data status <?php echo getStatusClass($pedido['status_pedido']); ?>" data-label="Status:">
                                            <?php echo htmlspecialchars($pedido['status_pedido']); ?>
                                        </span>
                                        <span class="cell-data actions">
                                            <button class="action-btn view view-details-btn" title="Ver Detalhes" data-id="<?php echo htmlspecialchars($pedido['cod_pedido_base']); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-data">
                                    <p>Nenhum pedido encontrado.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </main>
        </div>
        
        <div id="order-details-modal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3 id="modal-title">Detalhes do Pedido #SZ2025-XXXX</h3>
                
                <div class="modal-body">
                    <div class="order-info-group">
                        <h4><i class="fas fa-user"></i> Cliente & Entrega</h4>
                        <p><strong>Nome:</strong> <span id="client-name"></span></p>
                        <p><strong>Email:</strong> <span id="client-email"></span></p>
                        <p><strong>Endereço:</strong> <span id="delivery-address"></span></p>
                        <p><strong>Status:</strong> <span id="order-status-detail" class="status"></span></p>
                    </div>

                    <div class="order-info-group">
                        <h4><i class="fas fa-box"></i> Itens Adquiridos</h4>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>COD. Prod.</th>
                                    <th>Qtd.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="items-list">
                                </tbody>
                        </table>
                    </div>

                    <div class="order-info-group total-summary">
                        <h4><i class="fas fa-money-bill-wave"></i> Resumo Financeiro</h4>
                        <p>Subtotal: <span id="subtotal"></span></p>
                        <p>Frete: <span id="shipping-cost"></span></p>
                        <p class="total-line">Total Final: <span id="total-amount"></span></p>
                    </div>

                    <button class="update-status-btn">Atualizar Status</button>
                </div>
            </div>
        </div>
        
        <script src="vendas.js"></script>
    </body>
    </html>
