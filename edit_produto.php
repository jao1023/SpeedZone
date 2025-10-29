<?php require_once __DIR__ . '/session.php'; ?>
<?php
require_once 'connection.php';

// Array para armazenar mensagens
$erro = '';
$sucesso = '';

// Processar formulário de edição de produto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['nome'])) {
    try {
        // Verificar se a conexão foi estabelecida
        if ($conn->connect_error) {
            throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
        }

        // Validar dados obrigatórios
        $id = intval($_POST['id']);
        $nome = trim($_POST['nome']);
        $sku = trim($_POST['sku']);
        $preco = floatval($_POST['preco']);
        $estoque = intval($_POST['estoque']);
        $descricao = trim($_POST['descricao']);
        $categoria = $_POST['categoria'];

        if (empty($nome) || empty($sku) || $preco <= 0 || $estoque < 0) {
            throw new Exception("Todos os campos obrigatórios devem ser preenchidos corretamente.");
        }

        // Verificar se o produto existe
        $check_sql = "SELECT id_produto FROM produtos WHERE id_produto = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows == 0) {
            throw new Exception("Produto não encontrado.");
        }

        // Verificar se o SKU já existe em outro produto
        $check_sku_sql = "SELECT id_produto FROM produtos WHERE cod_produto = ? AND id_produto != ?";
        $check_sku_stmt = $conn->prepare($check_sku_sql);
        $check_sku_stmt->bind_param("si", $sku, $id);
        $check_sku_stmt->execute();
        $check_sku_result = $check_sku_stmt->get_result();

        if ($check_sku_result->num_rows > 0) {
            throw new Exception("Já existe outro produto com este código (SKU).");
        }

        // Atualizar produto no banco de dados
        $update_sql = "UPDATE produtos SET cod_produto = ?, nome_produto = ?, descricao_produto = ?, preco = ?, qtd_estoque = ?, categoria = ? WHERE id_produto = ?";
        $update_stmt = $conn->prepare($update_sql);
        
        if (!$update_stmt) {
            throw new Exception("Erro ao preparar statement: " . $conn->error);
        }
        
        $update_stmt->bind_param("sssdisi", $sku, $nome, $descricao, $preco, $estoque, $categoria, $id);

        if ($update_stmt->execute()) {
            if ($update_stmt->affected_rows > 0) {
                $sucesso = "Produto '{$nome}' atualizado com sucesso!";
            } else {
                throw new Exception("Nenhuma alteração foi feita no produto.");
            }
        } else {
            throw new Exception("Erro ao atualizar produto: " . $update_stmt->error);
        }

        $update_stmt->close();
        $check_stmt->close();
        $check_sku_stmt->close();

    } catch (Exception $e) {
        $erro = $e->getMessage();
        error_log("Erro ao editar produto: " . $erro);
    }
}

// Fechar conexão
if (isset($conn)) {
    $conn->close();
}

// Redirecionar de volta para produtos.php com mensagens
$redirect_url = "produtos.php";
if (!empty($erro)) {
    $redirect_url .= "?erro=" . urlencode($erro);
} elseif (!empty($sucesso)) {
    $redirect_url .= "?sucesso=" . urlencode($sucesso);
}

header("Location: " . $redirect_url);
exit();
?>
