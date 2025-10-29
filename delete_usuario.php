<?php require_once __DIR__ . '/session.php'; ?>
<?php
// Arquivo: delete_usuario.php

// Inclui o arquivo de conexão
require_once 'connection.php'; 

// Verifica se um ID de usuário foi passado
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // 1. Sanitização do ID
    $user_id = $conn->real_escape_string($_GET['id']);
    
    // Converte para inteiro por segurança
    $user_id = (int)$user_id;

    // 2. Consulta SQL de Exclusão usando Prepared Statement para segurança
    $sql = "DELETE FROM usuario WHERE id_usuario = ?";

    // Prepara a instrução
    $stmt = $conn->prepare($sql);
    
    // 'i' significa que a variável é um inteiro
    $stmt->bind_param("i", $user_id); 
    
    // Executa a instrução
    if ($stmt->execute()) {
        // Verifica se alguma linha foi afetada
        if ($stmt->affected_rows > 0) {
            // Redireciona de volta para a lista de usuários com mensagem de sucesso
            header("Location: usuarios.php?success=Usuário excluido com sucesso.");
            exit();
        } else {
            // Redireciona se o usuário não for encontrado
            header("Location: usuarios.php?error=Usuário com ID $user_id não encontrado.");
            exit();
        }
    } else {
        // Redireciona em caso de erro na execução
        header("Location: usuarios.php?error=Erro ao excluir usuário: " . $stmt->error);
        exit();
    }

    // Fecha o statement
    $stmt->close();
} else {
    // Redireciona se nenhum ID foi fornecido
    header("Location: usuarios.php?error=ID de usuário não especificado.");
    exit();
}

// Fecha a conexão
$conn->close();
?>