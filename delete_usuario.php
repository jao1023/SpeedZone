<?php require_once __DIR__ . '/session.php'; ?>
<?php

require_once 'connection.php';


if (isset($_GET['id']) && !empty($_GET['id'])) {

    $user_id = $conn->real_escape_string($_GET['id']);

    $user_id = (int)$user_id;

    $sql = "DELETE FROM usuario WHERE id_usuario = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header("Location: usuarios.php?success=Usuário excluido com sucesso.");
            exit();
        } else {
            header("Location: usuarios.php?error=Usuário com ID $user_id não encontrado.");
            exit();
        }
    } else {
        header("Location: usuarios.php?error=Erro ao excluir usuário: " . $stmt->error);
        exit();
    }

    $stmt->close();
} else {

    header("Location: usuarios.php?error=ID de usuário não especificado.");
    exit();
}

$conn->close();
?>