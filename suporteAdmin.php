<?php
// Arquivo: usuarios.php
// Inclui o arquivo de conexão, que define a variável $conn
require_once 'connection.php'; 

// --- 1. CONSULTA SQL PARA SUPORTE ---
// Busca todos os chamados de suporte, ordenados pelo mais novo (ID decrescente)
$sql_suporte = "SELECT 
                    id_chamado, 
                    id_cliente, 
                    nome_cliente, 
                    email, 
                    tipo, 
                    status_pedido, 
                    descricao, 
                    CREATED_AT 
                FROM suporte 
                ORDER BY id_chamado ASC";

$result_suporte = $conn->query($sql_suporte);

if (!$result_suporte) {
    die("Erro na consulta de suporte: " . $conn->error);
}

// Nota: A lógica de pesquisa de usuários e a consulta $sql_usuarios não são mais necessárias
// e foram removidas para simplificar o arquivo e focar apenas no Suporte.

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Suporte - Painel Admin SpeedZone</title>
    <link rel="stylesheet" href="admin.css"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

 <style>
    /* 1. ESTILOS DA TABELA DE SUPORTE */
    .tickets-grid-template {
        display: grid;
        /* ALTERAÇÃO AQUI: Aumenta-se o peso de Cliente e Email (1.2fr e 1.4fr) 
           e reduz-se o peso de Tipo, Status e Data (0.8fr, 0.8fr, 0.9fr). */
        grid-template-columns: 0.5fr 1.2fr 1.4fr 0.8fr 0.8fr 0.9fr 0.8fr; /* ID, Cliente, Email, Tipo, Status, Data, Ações */
        gap: 10px;
        padding: 10px;
        align-items: center;
    }

    /* 2. CLASSES DE STATUS (Manter para estilos visuais) */
    .status.resolvido { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .status.ativo { background-color: #ffeeba; color: #856404; border: 1px solid #ffc34f; }
    .status.pendente { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    
    /* Oculta o container de formulário de usuário, que não é mais necessário */
    #user-form-view { display: none; } 
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
                <a href="usuarios.php" class="nav-item"><i class="fas fa-users"></i> Usuários</a> 
                <a href="suporteAdmin.php" class="nav-item active"><i class="fas fa-headset"></i> Suporte</a>
                <a href="promocoes.php" class="nav-item"><i class="fas fa-tags"></i> Promoções</a>
            </nav>
            <div class="logout-section">
                <a href="index.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </aside>

        <main class="main-content">
            <h1 class="page-title" id="main-page-title">Gerenciamento de Suporte</h1>
            
            <section id="suporte-section" class="management-section active">
                <h2 class="section-heading">Chamados de Suporte Recentes</h2>
                <p>Aqui você pode visualizar todos os chamados abertos pelos clientes.</p>
                
                <div class="data-table">
                    <div class="table-header tickets-grid-template">
                        <span>ID Chamado</span>
                        <span>Cliente</span>
                        <span>Email</span>
                        <span>Tipo</span>
                        <span>Status</span>
                        <span>Data</span>
                        <span>Ações</span>
                    </div>
                    
                    <?php 
                    // Loop para exibir os dados dos chamados de suporte
                    if ($result_suporte->num_rows > 0) {
                        while ($row = $result_suporte->fetch_assoc()) { 
                            $ticket_id = $row['id_chamado'];
                            $ticket_nome = htmlspecialchars($row['nome_cliente']); 
                            $ticket_email = htmlspecialchars($row['email']);
                            $ticket_tipo = htmlspecialchars($row['tipo']); 
                            $ticket_status = htmlspecialchars($row['status_pedido']); 
                            $ticket_data = date('d/m/Y H:i', strtotime($row['CREATED_AT'])); 
                            $ticket_descricao = htmlspecialchars($row['descricao']);
                            
                            // Lógica de classes baseada no status do chamado
                            $status_class = strtolower(str_replace([' ', '/'], '', $ticket_status));
                            
                            echo "
                            <div class='table-row tickets-grid-template' data-ticket-id='{$ticket_id}'>
                                <span class='cell-data'>{$ticket_id}</span>
                                <span class='cell-data' data-label='Cliente:'>{$ticket_nome}</span>
                                <span class='cell-data' data-label='Email:'>{$ticket_email}</span>
                                <span class='cell-data' data-label='Tipo:'>{$ticket_tipo}</span>
                                <span class='cell-data status {$status_class}' data-label='Status:'>{$ticket_status}</span>
                                <span class='cell-data' data-label='Data:'>{$ticket_data}</span>
                                <span class='cell-data actions'>
                                    <a class='action-btn view' title='Visualizar Descrição' onclick=\"alert('Descrição do Chamado #{$ticket_id}:\\n{$ticket_descricao}')\"><i class='fas fa-eye'></i></a>
                                    <a class='action-btn resolve' title='Mudar Status' href='change_status_suporte.php?id={$ticket_id}'><i class='fas fa-check'></i></a>
                                </span>
                            </div>
                            ";
                        }
                    } else {
                        echo "<div class='table-row'><span class='cell-data' style='grid-column: 1 / -1; text-align: center; padding: 20px;'>Nenhum chamado de suporte encontrado.</span></div>";
                    }
                    ?>
                </div>
            </section>
            
        </main>
    </div>
    
    <?php 
    // Fechamento da conexão após o HTML ter sido gerado
    $conn->close();
    ?>
</body>
</html>