<?php
require_once 'connection.php';

// Verificar se foi fornecido um ID de produto
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$produto_id = intval($_GET['id']);
$produto = null;
$erro = '';

try {
    // Verificar se a conexão foi estabelecida
    if ($conn->connect_error) {
        throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }

    // Buscar dados do produto
    $sql = "SELECT * FROM produtos WHERE id_produto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $produto_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $produto = $result->fetch_assoc();
    } else {
        $erro = "Produto não encontrado.";
    }

    $stmt->close();

} catch (Exception $e) {
    $erro = $e->getMessage();
    error_log("Erro em produto.php: " . $erro);
} finally {
    // Fechar conexão
    if (isset($conn)) {
        $conn->close();
    }
}

// Se não encontrou o produto, redirecionar
if (!$produto) {
    header("Location: index.php?erro=" . urlencode($erro));
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produto['nome_produto']); ?> - SpeedZone</title>
    <link rel="stylesheet" href="comprar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>

<body>
    <a href="index.php" class="back-btn">
        &lt; Voltar
    </a>
    <div class="product-container">
        <div class="product-image-section">
            <img src="https://i.imgur.com/vHqQ9zG.png" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>" class="product-image">
        </div>

        <div class="product-details-section">
            <div class="details-content">
                <h1 class="product-title"><?php echo htmlspecialchars($produto['nome_produto']); ?></h1>
                <p class="product-category"><?php echo ucfirst(str_replace('_', ' ', $produto['categoria'])); ?></p>
                <div class="product-price">
                    <span class="current-price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                </div>
                <p class="product-description">
                    <?php echo htmlspecialchars($produto['descricao_produto']); ?>
                </p>

                <div class="product-info">
                    <p><strong>Estoque Disponível:</strong> 
                        <?php if ($produto['qtd_estoque'] > 0): ?>
                            <span class="stock-available"><?php echo $produto['qtd_estoque']; ?> unidades</span>
                        <?php else: ?>
                            <span class="stock-unavailable">Fora de estoque</span>
                        <?php endif; ?>
                    </p>
                </div>

                <?php if ($produto['qtd_estoque'] > 0): ?>
                    <div class="quantity-selector">
                        <label for="quantity">Quantidade:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $produto['qtd_estoque']; ?>">
                    </div>

                    <button type="button" class="add-to-cart-btn" onclick="adicionarAoCarrinho(<?php echo $produto['id_produto']; ?>)">Adicionar ao Carrinho</button>
                <?php else: ?>
                    <button type="button" class="add-to-cart-btn disabled" disabled>Produto Indisponível</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function adicionarAoCarrinho(produtoId) {
            const quantidade = document.getElementById('quantity').value;
            
            fetch('carrinho_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `acao=adicionar&produto_id=${produtoId}&quantidade=${quantidade}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Atualizar contador do carrinho se existir
                    if (typeof atualizarContadorCarrinho === 'function') {
                        atualizarContadorCarrinho();
                    }
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao adicionar ao carrinho');
            });
        }
    </script>
</body>

</html>