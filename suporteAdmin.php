<?php require_once __DIR__ . '/session.php'; ?>
<?php
// Arquivo: suporteAdmin.php (Com a lógica de atualização integrada)
// Inclui o arquivo de conexão. Certifique-se de que 'connection.php' exista e funcione.
require_once 'connection.php'; 

// ----------------------------------------------------------------------
// 1. LÓGICA DE ATUALIZAÇÃO DE STATUS (INTEGRADA)
// ----------------------------------------------------------------------

$update_status = null;
$updated_ticket_id = null;
$error_message = null;

if (isset($_GET['action']) && $_GET['action'] == 'resolve' && isset($_GET['id']) && !empty($_GET['id'])) {
    
    $ticket_id = $_GET['id'];
    $ticket_id_int = (int)$ticket_id;
    $novo_status = "Resolvido";
    
    // Verifica se o ID é válido antes de prosseguir
    if ($ticket_id_int > 0) {
        
        // Prepara a query de atualização com prepared statement para segurança
        $stmt = $conn->prepare("UPDATE suporte SET status_pedido = ? WHERE id_chamado = ?");
        
        // 'si' indica que o primeiro parâmetro é string (status) e o segundo é integer (id)
        if ($stmt) {
            $stmt->bind_param("si", $novo_status, $ticket_id_int); 
        
            if ($stmt->execute()) {
                // Sucesso
                $update_status = 'success';
                $updated_ticket_id = $ticket_id_int;
            } else {
                // Erro na execução da query
                $update_status = 'error';
                $error_message = "Erro ao executar a atualização: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Erro na preparação da query
            $update_status = 'error';
            $error_message = "Erro ao preparar a query: " . $conn->error;
        }
    } else {
        $update_status = 'error';
        $error_message = "ID de chamado inválido.";
    }

    // Redireciona para a mesma página sem os parâmetros 'action' para evitar re-submissão
    if ($update_status) {
        $param = ($update_status == 'success') ? "status=success&ticket={$updated_ticket_id}" : "status=error&msg=" . urlencode($error_message);
        header("Location: suporteAdmin.php?{$param}");
        exit();
    }
}


// ----------------------------------------------------------------------
// 2. CONSULTA SQL PARA EXIBIÇÃO (Após a lógica de atualização)
// ----------------------------------------------------------------------

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
                ORDER BY id_chamado DESC"; // Ordenado pelo mais novo primeiro

$result_suporte = $conn->query($sql_suporte);

if (!$result_suporte) {
    die("Erro na consulta de suporte: " . $conn->error);
}
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
        grid-template-columns: 0.5fr 1.2fr 1.4fr 0.8fr 0.8fr 0.9fr 0.8fr; /* ID, Cliente, Email, Tipo, Status, Data, Ações */
        gap: 10px;
        padding: 10px;
        align-items: center;
    }

    /* 2. CLASSES DE STATUS (Manter para estilos visuais) */
    .status.resolvido { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .status.ativo { background-color: #ffeeba; color: #856404; border: 1px solid #ffc34f; }
    .status.pendente { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    /* Novo estilo para o botão 'Resolver' na tabela */

    
    /* Estilos do Modal (Você deve ajustar 'admin.css' ou usar estes aqui) */
    .modal {
        display: none; 
        position: fixed; 
        z-index: 1000; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0,0,0,0.5); 
    }
    .modal-content {
        background-color: #fefefe;
        margin: 10% auto; 
        padding: 25px;
        border: 1px solid #888;
        width: 80%; 
        max-width: 600px; 
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .close-btn:hover,
    .close-btn:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }
    .modal-body p {
        margin-bottom: 10px;
    }
    .modal-body h4 {
        border-bottom: 2px solid #eee;
        padding-bottom: 5px;
        margin-top: 20px;
        margin-bottom: 15px;
        color: #000000ff; /* Cor primária */
    }
    #ticket-description-detail {
        background-color: #f9f9f9;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        white-space: pre-wrap; /* Mantém quebras de linha */
    }

    /* Estilos para o bloco de alerta (Mensagens de Sucesso/Erro) */
    .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

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
                <a href="cupons.php" class="nav-item"><i class="fas fa-tags"></i> Cupons</a>
            </nav>
            <div class="logout-section">
                <a href="index.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </aside>

        <main class="main-content">
            <h1 class="page-title" id="main-page-title">Gerenciamento de Suporte</h1>
            
            <?php if (isset($_GET['status'])): ?>
                <div class="alert 
                    <?php echo ($_GET['status'] == 'success') ? 'alert-success' : 'alert-error'; ?>"
                    style="padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <?php 
                        if ($_GET['status'] == 'success') {
                            echo "Chamado atualizado com sucesso para 'Resolvido'.";
                        } else {
                            echo "❌ Erro ao atualizar o chamado: " . htmlspecialchars($_GET['msg'] ?? 'Erro desconhecido.');
                        }
                    ?>
                </div>
            <?php endif; ?>

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
                            $ticket_descricao = htmlspecialchars($row['descricao']); // Dados limpos para JS
                            
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
                                    
                                    <a class='action-btn view-details-btn' title='Visualizar Descrição' 
                                        data-id='{$ticket_id}' 
                                        data-cliente='{$ticket_nome}' 
                                        data-email='{$ticket_email}' 
                                        data-tipo='{$ticket_tipo}' 
                                        data-status='{$ticket_status}' 
                                        data-data='{$ticket_data}'
                                        data-descricao='{$ticket_descricao}'
                                    >
                                        <i class='fas fa-eye'></i>
                                    </a>
                                    
                                    <a class='action-btn resolve' title='Mudar Status para Resolvido' href='suporteAdmin.php?action=resolve&id={$ticket_id}'>
                                        <i class='fas fa-check'></i>
                                    </a>
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
    
    <div id="support-details-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modal-title-support">Detalhes do Chamado #<span id="ticket-id-detail"></span></h3>
            
            <div class="modal-body">
                <div class="ticket-info-group">
                    <h4><i class="fas fa-info-circle"></i> Informações Principais</h4>
                    <p><strong>Cliente:</strong> <span id="client-name-detail"></span></p>
                    <p><strong>Email:</strong> <span id="client-email-detail"></span></p>
                    <p><strong>Tipo:</strong> <span id="ticket-type-detail"></span></p>
                    <p><strong>Data de Abertura:</strong> <span id="ticket-date-detail"></span></p>
                    <p><strong>Status:</strong> <span id="ticket-status-detail" class="status"></span></p>
                </div>

                <div class="ticket-info-group">
                    <h4><i class="fas fa-pen"></i> Descrição do Chamado</h4>
                    <div id="ticket-description-detail"></div>
                </div>

                <a id="update-status-link" </a>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        
        // Elementos do Modal de Suporte
        const modal = document.getElementById('support-details-modal');
        const closeBtn = modal.querySelector('.close-btn');
        const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
        
        // Função para preencher e mostrar o modal de suporte
        function showSupportDetails(btn) {
            // Pega os dados dos atributos data-
            const ticketId = btn.getAttribute('data-id');
            const cliente = btn.getAttribute('data-cliente');
            const email = btn.getAttribute('data-email');
            const tipo = btn.getAttribute('data-tipo');
            const status = btn.getAttribute('data-status');
            const data = btn.getAttribute('data-data');
            const descricao = btn.getAttribute('data-descricao');
            
            // 1. Preenche Títulos e Informações Principais
            document.getElementById('ticket-id-detail').textContent = ticketId;
            document.getElementById('client-name-detail').textContent = cliente;
            document.getElementById('client-email-detail').textContent = email;
            document.getElementById('ticket-type-detail').textContent = tipo;
            document.getElementById('ticket-date-detail').textContent = data;
            
            // 2. Preenche Status e aplica a cor
            const statusDetailElement = document.getElementById('ticket-status-detail');
            statusDetailElement.textContent = status;
            const statusClass = status.toLowerCase().replace(/\s|\//g, ''); // Limpa o status para a classe CSS
            statusDetailElement.className = `status ${statusClass}`;

            // 3. Preenche Descrição
            document.getElementById('ticket-description-detail').textContent = descricao;

            // 4. Atualiza o link de Mudar Status no modal (aponta para a própria página)
            document.getElementById('update-status-link').href = `suporteAdmin.php?action=resolve&id=${ticketId}`;
            
            // 5. Mostra o Modal
            modal.style.display = 'block';
        }

        // Event Listeners para abrir o modal
        viewDetailsBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault(); // Evita qualquer ação padrão do link
                showSupportDetails(e.currentTarget);
            });
        });

        // Event Listeners para fechar o modal
        if (closeBtn) closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Fecha o modal ao clicar fora
        window.addEventListener('click', (event) => {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    });
    </script>
    <?php 
    // Fechamento da conexão após o HTML ter sido gerado
    $conn->close();
    ?>
</body>
</html>