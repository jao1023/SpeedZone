<?php require_once __DIR__ . '/session.php'; ?>
<?php
// Arquivo: edit_usuario_handler.php

// Define o cabeçalho para retornar uma resposta JSON
header('Content-Type: application/json');

// Inclui o arquivo de conexão
require_once 'connection.php'; 

// Inicializa a resposta
$response = ['success' => false, 'message' => ''];

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitização e Validação
    $user_id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $status = trim($_POST['status'] ?? '');

    // Verifica campos obrigatórios (ajuste conforme seu BD)
    if (empty($nome) || empty($email) || empty($cargo) || empty($status)) {
        $response['message'] = 'Erro: Os campos Nome, Email, Cargo e Status são obrigatórios.';
        echo json_encode($response);
        exit;
    }

    // Usando Prepared Statements para segurança
    if ($user_id) {
        // Lógica de EDIÇÃO (UPDATE)
        $sql = "UPDATE usuario SET 
                    primeiro_nome = ?, 
                    email = ?, 
                    cargo = ?, 
                    status_conta = ? 
                WHERE id_usuario = ?";
        
        $stmt = $conn->prepare($sql);
        // O tipo de 'telefone' e outros campos deve ser ajustado ('s' para string)
        $stmt->bind_param("ssssi", $nome, $email, $cargo, $status, $user_id);
        
        if ($stmt->execute()) {
            // Verifica se alguma linha foi realmente alterada
            if ($stmt->affected_rows > 0) {
                 $response['success'] = true;
                 $response['message'] = 'Usuário editado com sucesso!';
            } else {
                 $response['success'] = true;
                 $response['message'] = 'Nenhuma alteração foi feita no usuário (dados iguais).';
            }
        } else {
            $response['message'] = "Erro ao editar usuário: " . $stmt->error;
        }
    } else {
        // Lógica de CRIAÇÃO (INSERT) - (Opcional, mas útil ter no mesmo handler)
        // Nota: A criação de usuário deve incluir senha e outros campos obrigatórios do seu 'usuario'
        // Por simplificação, vamos assumir que apenas a edição é necessária agora.
        $response['message'] = 'Erro: ID de usuário não fornecido para edição.';
    }

    // Fecha o statement e a conexão
    $stmt->close();
    $conn->close();

} else {
    $response['message'] = 'Requisição inválida.';
}

echo json_encode($response);
?>