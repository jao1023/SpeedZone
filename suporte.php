<?php
// Arquivo: suporte.php
session_start();

// Redireciona se o usuário não estiver logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("Location: login.php");
    exit;
}

// Assumindo que 'connection.php' estabelece a variável $conn (MySQLi)
require_once 'connection.php'; 

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$user_data = []; // Inicializa a variável

// --- CARREGAMENTO DOS DADOS DO USUÁRIO ---
$sql_select = "SELECT primeiro_nome, email FROM usuario WHERE id_usuario = ?"; 
if ($stmt_select = $conn->prepare($sql_select)) {
    $stmt_select->bind_param("i", $user_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    } else {
        $error .= "Erro: Dados do usuário (nome/email) não encontrados ou ID inválido. "; 
    }

    $stmt_select->close();
} else {
    $error .= "Erro ao preparar a consulta de dados do usuário: " . $conn->error;
}


// --- PROCESSAMENTO DO FORMULÁRIO DE SUPORTE ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Coleta os dados do usuário logado
    $nome_cliente = $user_data['primeiro_nome'] ?? 'Cliente Desconhecido';
    $email        = $user_data['email'] ?? 'desconhecido@email.com'; 
    
    // 2. Coleta e SANEAMENTO dos campos preenchíveis
    $tipo         = filter_input(INPUT_POST, 'issueType', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $descricao    = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status_inicial = 'Ativo'; 

    // 3. Mapeamento dos valores curtos do HTML para o ENUM longo do SQL
    $tipo_sql = [
        'pedido' => 'Duvida sobre um pedido',
        'produto' => 'Informações sobre produto',
        'pagamento' => 'Problema com Pagamento',
        'tecnico' => 'Problemas Tecnicos/Erros no site',
        'outros' => 'Outros Assuntos'
    ][$tipo] ?? null;

    if ($tipo_sql && $descricao) {
        // 4. Preparação da Consulta SQL para Inserção
        // Assumindo que a coluna na tabela 'suporte' é 'nome_usuario' (ajuste se for diferente, ex: 'nome_cliente' ou 'nome')
        $sql_insert = "INSERT INTO suporte (id_cliente, nome_cliente, email, tipo, status_pedido, descricao) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        
        if ($stmt_insert = $conn->prepare($sql_insert)) {
            
            // CORREÇÃO: Variável $nome_cliente estava sendo chamada incorretamente como $nome_usuario
            $stmt_insert->bind_param("isssss", 
                                $user_id, 
                                $nome_cliente, // CORRIGIDO
                                $email, 
                                $tipo_sql, 
                                $status_inicial, 
                                $descricao);
            
            if ($stmt_insert->execute()) {
                // SUCESSO! IMPLEMENTANDO PRG para evitar duplicidade
                $_SESSION['success_message'] = "Solicitação enviada com sucesso!";
                header("Location: suporte.php"); // Redireciona para a mesma página, mas via GET
                exit; // Encerra o script após o redirecionamento
            } else {
                $error .= "Erro ao enviar solicitação: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        } else {
            $error .= "Erro ao preparar a consulta de inserção: " . $conn->error;
        }
    } else {
        $error .= "Por favor, selecione um tipo de problema e preencha a descrição.";
    }
}

// 5. Verifica se há mensagem de sucesso na sessão (do redirecionamento PRG)
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Remove a mensagem para que não apareça novamente
}

// 6. Fechamento da conexão no final do script
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Suporte - SpeedZone</title>
    <link rel="stylesheet" href="suporte.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        .error-message { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success-message { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <a href="index.php" class="back-btn">
        &lt; Voltar
    </a>
    <div class="container">
        <h1 class="page-title">Solicitar Suporte</h1>

        <section class="support-section">
            <p class="support-intro">
                Preencha o formulário abaixo para abrir um novo chamado. Nossa equipe de suporte responderá o mais rápido possível.
            </p>
            
            <?php
            // Exibe as mensagens de feedback
            if ($error) {
                echo "<div class='error-message'>{$error}</div>";
            }
            if ($success) {
                echo "<div class='success-message'>{$success}</div>";
            }
            ?>
            
            <form action="suporte.php" method="POST" class="support-form">
                
                <div class="form-group">
                    <label for="name">Seu Nome</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user_data['primeiro_nome'] ?? '') ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="email">Seu Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="issueType">Tipo de Problema</label>
                    <select id="issueType" name="issueType" required>
                        <option value="" disabled selected>Selecione um tópico</option>
                        <option value="pedido">Dúvida sobre um Pedido</option>
                        <option value="produto">Informação sobre Produto</option>
                        <option value="pagamento">Problema com Pagamento</option>
                        <option value="tecnico">Problema Técnico/Erro no Site</option>
                        <option value="outros">Outros Assuntos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Descrição do Problema</label>
                    <textarea id="description" name="description" rows="6" placeholder="Descreva seu problema ou dúvida com o máximo de detalhes possível." required></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Enviar Solicitação</button>
            </form>
        </section>
    </div>
</body>
</html>