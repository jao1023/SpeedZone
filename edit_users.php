<?php require_once __DIR__ . '/session.php'; ?>
<?php

header('Content-Type: application/json');

require_once 'connection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitização e Validação
    $user_id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if (empty($nome) || empty($email) || empty($cargo) || empty($status)) {
        $response['message'] = 'Erro: Os campos Nome, Email, Cargo e Status são obrigatórios.';
        echo json_encode($response);
        exit;
    }

    if ($user_id) {
        $sql = "UPDATE usuario SET 
                    primeiro_nome = ?, 
                    email = ?, 
                    cargo = ?, 
                    status_conta = ? 
                WHERE id_usuario = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $email, $cargo, $status, $user_id);

        if ($stmt->execute()) {
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

        $response['message'] = 'Erro: ID de usuário não fornecido para edição.';
    }


    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Requisição inválida.';
}

echo json_encode($response);
?>