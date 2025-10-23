<?php
require_once 'connection.php';

// Array para armazenar mensagens
$erro = '';
$sucesso = '';

// Processar formulário de novo produto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nome'])) {
    try {
        // Verificar se a conexão foi estabelecida
        if ($conn->connect_error) {
            throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
        }

        // Validar dados obrigatórios
        $nome = trim($_POST['nome']);
        $sku = trim($_POST['sku']);
        $preco = floatval($_POST['preco']);
        $estoque = intval($_POST['estoque']);
        $descricao = trim($_POST['descricao']);
        $categoria = $_POST['categoria'];

        if (empty($nome) || empty($sku) || $preco <= 0 || $estoque < 0) {
            throw new Exception("Todos os campos obrigatórios devem ser preenchidos corretamente.");
        }

        // Verificar se o SKU já existe
        $check_sql = "SELECT id_produto FROM produtos WHERE cod_produto = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $sku);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            throw new Exception("Já existe um produto com este código (SKU).");
        }

        // Inserir produto no banco de dados
        $insert_sql = "INSERT INTO produtos (cod_produto, nome_produto, descricao_produto, preco, qtd_estoque, categoria) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        if (!$insert_stmt) {
            throw new Exception("Erro ao preparar statement: " . $conn->error);
        }
        
        $insert_stmt->bind_param("sssdis", $sku, $nome, $descricao, $preco, $estoque, $categoria);

        if ($insert_stmt->execute()) {
            $sucesso = "Produto cadastrado com sucesso!";
        } else {
            throw new Exception("Erro ao cadastrar produto: " . $insert_stmt->error);
        }

        $insert_stmt->close();
        $check_stmt->close();

    } catch (Exception $e) {
        $erro = $e->getMessage();
        error_log("Erro ao cadastrar produto: " . $erro);
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