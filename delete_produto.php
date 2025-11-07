<?php require_once __DIR__ . '/session.php'; ?>
<?php
require_once 'connection.php';

$erro = '';
$sucesso = '';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $produto_id = intval($_GET['id']);

    try {
        if ($conn->connect_error) {
            throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
        }

        $check_sql = "SELECT id_produto, nome_produto FROM produtos WHERE id_produto = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $produto_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows == 0) {
            throw new Exception("Produto não encontrado.");
        }

        $produto = $check_result->fetch_assoc();
        $nome_produto = $produto['nome_produto'];

        $delete_sql = "DELETE FROM produtos WHERE id_produto = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $produto_id);

        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                $sucesso = "Produto '{$nome_produto}' deletado com sucesso!";
            } else {
                throw new Exception("Nenhum produto foi deletado.");
            }
        } else {
            throw new Exception("Erro ao deletar produto: " . $delete_stmt->error);
        }

        $delete_stmt->close();
        $check_stmt->close();
    } catch (Exception $e) {
        $erro = $e->getMessage();
        error_log("Erro ao deletar produto: " . $erro);
    }
} else {
    $erro = "ID do produto não fornecido.";
}

if (isset($conn)) {
    $conn->close();
}

$redirect_url = "produtos.php";
if (!empty($erro)) {
    $redirect_url .= "?erro=" . urlencode($erro);
} elseif (!empty($sucesso)) {
    $redirect_url .= "?sucesso=" . urlencode($sucesso);
}

header("Location: " . $redirect_url);
exit();
?>
