<?php require_once __DIR__ . '/session.php'; ?>
<?php

require_once 'connection.php';

// Verifica se o ID foi passado via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: cupons.php?erro=id_nao_fornecido");
    exit();
}

// 1. Recebe e Sanitiza o ID
$cupom_id = (int)$_GET['id'];

// 2. Prepara e Executa a exclusão
if ($cupom_id > 0) {
    // Usando Prepared Statement para segurança
    $sql = "DELETE FROM cupons WHERE id_cupom = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $cupom_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Sucesso na exclusão
                header("Location: cupons.php?sucesso=exclusao&id=" . $cupom_id);
                exit();
            } else {
                // Cupom não encontrado (ID válido, mas não existe)
                header("Location: cupons.php?erro=cupom_nao_existe&id=" . $cupom_id);
                exit();
            }
        } else {
            // Erro na execução
            $erro = "Erro ao excluir o cupom: " . $stmt->error;
            header("Location: cupons.php?erro=" . urlencode($erro));
            exit();
        }
        $stmt->close();
    } else {
        // Erro na preparação da declaração
        $erro = "Erro ao preparar a consulta de DELETE: " . $conn->error;
        header("Location: cupons.php?erro=" . urlencode($erro));
        exit();
    }
} else {
    // ID inválido ou 0
    header("Location: cupons.php?erro=id_invalido");
    exit();
}

// Fecha a conexão
$conn->close();
?>