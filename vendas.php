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
<<<<<<< HEAD
                <a href="cupons.php" class="nav-item"><i class="fas fa-tags"></i> Cupons</a>
=======
                <a href="promocoes.php" class="nav-item"><i class="fas fa-tags"></i> Promoções</a>
>>>>>>> e7869446688dd7b7c3a1fcc1ad634e023d938e58
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
                        <input type="text" placeholder="Buscar por Pedido ID ou Nome do Cliente..." class="search-input">
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
                        
                        <div class="table-row sales-grid-template" data-order-id="1001">
                            <span class="cell-data" data-label="ID:">#SZ2025-1001</span>
                            <span class="cell-data" data-label="Data:">30/09/2025</span>
                            <span class="cell-data" data-label="Cliente:">João Silva</span>
                            <span class="cell-data" data-label="Total:">R$ 2.559,90</span>
                            <span class="cell-data status processing" data-label="Status:">Em Processamento</span>
                            <span class="cell-data actions">
                                <button class="action-btn view view-details-btn" title="Ver Detalhes" data-id="1001"><i class="fas fa-eye"></i></button>
                            </span>
                        </div>
                        
                        <div class="table-row sales-grid-template" data-order-id="1002">
                            <span class="cell-data" data-label="ID:">#SZ2025-1002</span>
                            <span class="cell-data" data-label="Data:">29/09/2025</span>
                            <span class="cell-data" data-label="Cliente:">Maria Souza</span>
                            <span class="cell-data" data-label="Total:">R$ 159,80</span>
                            <span class="cell-data status delivered" data-label="Status:">Entregue</span>
                            <span class="cell-data actions">
                                <button class="action-btn view view-details-btn" title="Ver Detalhes" data-id="1002"><i class="fas fa-eye"></i></button>
                            </span>
                        </div>

                        <div class="table-row sales-grid-template" data-order-id="1003">
                            <span class="cell-data" data-label="ID:">#SZ2025-1003</span>
                            <span class="cell-data" data-label="Data:">28/09/2025</span>
                            <span class="cell-data" data-label="Cliente:">Pedro Santos</span>
                            <span class="cell-data" data-label="Total:">R$ 4.999,00</span>
                            <span class="cell-data status cancelled" data-label="Status:">Cancelado</span>
                            <span class="cell-data actions">
                                <button class="action-btn view view-details-btn" title="Ver Detalhes" data-id="1003"><i class="fas fa-eye"></i></button>
                            </span>
                        </div>
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
