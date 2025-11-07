<?php
require_once 'connection.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nome'])) {
    try {
        if ($conn->connect_error) {
            throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
        }

        $nome = trim($_POST['nome']);
        $sku = trim($_POST['sku']);
        $preco = floatval($_POST['preco']);
        $estoque = intval($_POST['estoque']);
        $descricao = trim($_POST['descricao']);
        $categoria = $_POST['categoria'];

        // Validação básica
        if (empty($nome) || empty($sku) || $preco <= 0 || $estoque < 0) {
            throw new Exception("Todos os campos obrigatórios devem ser preenchidos corretamente.");
        }

        // Verificar SKU duplicado
        $check_sql = "SELECT id_produto FROM produtos WHERE cod_produto = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $sku);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            throw new Exception("Já existe um produto com este código (SKU).");
        }

        $caminho_final = null;

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $pasta = "uploads/";
            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $arquivo = $_FILES['imagem'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($extensao, $permitidas)) {
                throw new Exception("Formato de imagem inválido. Envie JPG, PNG, GIF ou WEBP.");
            }

            $nome_arquivo = uniqid('prod_') . '.' . $extensao;
            $caminho_final = $pasta . $nome_arquivo;

            if (!move_uploaded_file($arquivo['tmp_name'], $caminho_final)) {
                throw new Exception("Erro ao salvar a imagem no servidor.");
            }
        } else {

            $caminho_final = "uploads/sem_imagem.png";
        }

        $insert_sql = "INSERT INTO produtos 
            (cod_produto, nome_produto, descricao_produto, preco, qtd_estoque, categoria, imagem)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception("Erro ao preparar statement: " . $conn->error);
        }

        $insert_stmt->bind_param("sssdiss", $sku, $nome, $descricao, $preco, $estoque, $categoria, $caminho_final);

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
