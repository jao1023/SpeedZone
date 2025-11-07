<?php
session_start();
require_once 'connection.php';
require_once 'carrinho_functions.php';

$carrinho_itens = obterCarrinhoComDetalhes();
$total_carrinho = calcularTotalCarrinho();
$frete = 20.00; // Frete fixo

// Verificar se há cupom aplicado
$desconto_cupom = 0;
$cupom_aplicado = null;
if (isset($_SESSION['cupom_aplicado'])) {
    $cupom_aplicado = $_SESSION['cupom_aplicado'];
    $desconto_cupom = $cupom_aplicado['desconto'];
}

$total_final = $total_carrinho + $frete - $desconto_cupom;

// Buscar endereço do usuário logado (se houver)
$user_id = $_SESSION['user_id'] ?? null;
$user_address = null;
$endereco_completo = false;
if ($user_id) {
    $sqlUser = "SELECT cep, rua, numero, complemento, bairro, cidade, estado FROM usuario WHERE id_usuario = ?";
    if ($stmt = $conn->prepare($sqlUser)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $user_address = $result->fetch_assoc();
            $cep = preg_replace('/\D/', '', $user_address['cep'] ?? '');
            $endereco_completo = !empty($cep) && strlen($cep) === 8
                && !empty($user_address['rua'])
                && !empty($user_address['numero'])
                && !empty($user_address['bairro'])
                && !empty($user_address['cidade'])
                && !empty($user_address['estado']);
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras - SpeedZone</title>
    <link rel="stylesheet" href="carrinho.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>

<body>
    <a href="javascript:history.back()" class="back-btn">
        &lt; Voltar
    </a>
    <div class="cart-container">
        <h1 class="cart-title">Seu Carrinho</h1>

        <div class="cart-content">
            <div class="cart-items-section">
                <?php if (!empty($carrinho_itens)): ?>
                    <?php foreach ($carrinho_itens as $item): ?>
                        <div class="cart-item" data-produto-id="<?php echo $item['id_produto']; ?>">
                            <img src="<?php echo htmlspecialchars($item['imagem'] ?? 'uploads/sem_imagem.png'); ?>"
                                alt="<?php echo htmlspecialchars($item['nome_produto']); ?>"
                                class="item-image"
                                style="width:120px; height:120px; object-fit:contain;"
                                onerror="this.src='uploads/sem_imagem.png';">
                            <div class="item-details">
                                <p class="item-name"><?php echo htmlspecialchars($item['nome_produto']); ?></p>
                                <p class="item-price">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></p>
                                <div class="item-actions">
                                    <input type="number" value="<?php echo $item['quantidade_carrinho']; ?>" min="1" max="<?php echo $item['qtd_estoque']; ?>" class="item-quantity" onchange="atualizarQuantidade(<?php echo $item['id_produto']; ?>, this.value)">
                                    <button class="remove-btn" onclick="removerItem(<?php echo $item['id_produto']; ?>)">Remover</button>
                                </div>
                                <p class="item-subtotal">Subtotal: R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-cart">
                        <h3>Seu carrinho está vazio</h3>
                        <p>Adicione alguns produtos para começar suas compras!</p>
                        <a href="index.php" class="continue-shopping-btn">Continuar Comprando</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="cart-summary-section">
                <div class="summary-details">
                    <h2 class="summary-title">Resumo do Pedido</h2>

                    <!-- Endereço de Entrega -->
                    <div class="address-section" style="margin-bottom: 16px;">
                        <h3 style="margin: 0 0 8px; font-size: 16px;">Endereço de Entrega</h3>
                        <?php if ($user_id && $user_address): ?>
                            <?php if ($endereco_completo): ?>
                                <div style="font-size: 14px; color: #333;">
                                    <div><?php echo htmlspecialchars($user_address['rua']); ?>, <?php echo htmlspecialchars($user_address['numero']); ?><?php echo !empty($user_address['complemento']) ? ' - ' . htmlspecialchars($user_address['complemento']) : ''; ?></div>
                                    <div><?php echo htmlspecialchars($user_address['bairro']); ?> - <?php echo htmlspecialchars($user_address['cidade']); ?>/<?php echo htmlspecialchars($user_address['estado']); ?></div>
                                    <div>CEP: <?php echo htmlspecialchars(substr(preg_replace('/\D/', '', $user_address['cep']), 0, 5) . '-' . substr(preg_replace('/\D/', '', $user_address['cep']), 5)); ?></div>
                                </div>
                            <?php else: ?>
                                <div style="font-size: 14px; color: #b45309; background: #fffbeb; border: 1px solid #f59e0b; padding: 8px; border-radius: 6px;">Endereço incompleto. Atualize para continuar.</div>
                            <?php endif; ?>
                            <div style="margin-top: 8px;">
                                <a href="config.php" class="cupom-btn" style="text-decoration:none; display:inline-block;">Alterar Endereço</a>
                            </div>
                        <?php else: ?>
                            <div style="font-size: 14px; color: #991b1b; background: #fee2e2; border: 1px solid #ef4444; padding: 8px; border-radius: 6px;">Faça login e cadastre um endereço para continuar.</div>
                            <div style="margin-top: 8px; display: flex; gap: 8px;">
                                <a href="login.php" class="cupom-btn" style="text-decoration:none; display:inline-block;">Entrar</a>
                                <a href="config.php" class="cupom-btn" style="text-decoration:none; display:inline-block;">Cadastrar Endereço</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Campo de Cupom -->
                    <div class="cupom-section">
                        <div class="cupom-input-group">
                            <input type="text" id="cupom-codigo" placeholder="Digite o código do cupom" class="cupom-input">
                            <button type="button" onclick="aplicarCupom()" class="cupom-btn">Aplicar</button>
                        </div>
                        <div id="cupom-message" class="cupom-message"></div>
                    </div>

                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span class="subtotal-price">R$ <?php echo number_format($total_carrinho, 2, ',', '.'); ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Frete</span>
                        <span class="shipping-price">R$ <?php echo number_format($frete, 2, ',', '.'); ?></span>
                    </div>
                    <?php if ($cupom_aplicado): ?>
                        <div id="cupom-discount-line" class="summary-line cupom-discount">
                            <span>Desconto (<?php echo htmlspecialchars($cupom_aplicado['codigo']); ?>)</span>
                            <span class="discount-price">-R$ <?php echo number_format($desconto_cupom, 2, ',', '.'); ?></span>
                        </div>
                    <?php else: ?>
                        <div id="cupom-discount-line" class="summary-line cupom-discount" style="display: none;">
                            <span>Desconto</span>
                            <span class="discount-price">-R$ 0,00</span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-total-line">
                        <span>Total</span>
                        <span class="total-price">R$ <?php echo number_format($total_final, 2, ',', '.'); ?></span>
                    </div>
                </div>
                <?php if (!empty($carrinho_itens)): ?>
                    <?php if ($user_id && $endereco_completo): ?>
                        <button type="button" class="checkout-btn" onclick="finalizarCompra()">Finalizar Compra</button>
                    <?php else: ?>
                        <button type="button" class="checkout-btn" onclick="alert('Cadastre um endereço completo para finalizar a compra.');" disabled style="opacity:0.7; cursor:not-allowed;">Finalizar Compra</button>
                    <?php endif; ?>
                    <button type="button" class="clear-cart-btn" onclick="limparCarrinho()">Limpar Carrinho</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function atualizarQuantidade(produtoId, quantidade) {
            fetch('carrinho_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `acao=atualizar&produto_id=${produtoId}&quantidade=${quantidade}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert('Erro: ' + data.message);
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao atualizar quantidade');
                });
        }

        function removerItem(produtoId) {
            if (confirm('Tem certeza que deseja remover este item do carrinho?')) {
                fetch('carrinho_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `acao=remover&produto_id=${produtoId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert('Erro: ' + data.message);
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao remover item');
                    });
            }
        }

        function limparCarrinho() {
            if (confirm('Tem certeza que deseja limpar todo o carrinho?')) {
                fetch('carrinho_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'acao=limpar'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert('Erro: ' + data.message);
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao limpar carrinho');
                    });
            }
        }

        function aplicarCupom() {
            const codigoCupom = document.getElementById('cupom-codigo').value.trim();
            const messageDiv = document.getElementById('cupom-message');
            if (!codigoCupom) {
                messageDiv.innerHTML = '<span class="error">Digite um código de cupom</span>';
                return;
            }
            fetch('carrinho_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `acao=aplicar_cupom&codigo_cupom=${encodeURIComponent(codigoCupom)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDiv.innerHTML = `<span class="success">${data.message}</span>`;
                        atualizarResumoComCupom(data.desconto, data.total_final);
                    } else {
                        messageDiv.innerHTML = `<span class="error">${data.message}</span>`;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    messageDiv.innerHTML = '<span class="error">Erro ao aplicar cupom</span>';
                });
        }

        function atualizarResumoComCupom(desconto, totalFinal) {
            const discountLine = document.getElementById('cupom-discount-line');
            const discountPrice = discountLine.querySelector('.discount-price');
            discountPrice.textContent = `-R$ ${desconto.toFixed(2).replace('.', ',')}`;
            discountLine.style.display = 'flex';
            const totalPrice = document.querySelector('.total-price');
            totalPrice.textContent = `R$ ${totalFinal.toFixed(2).replace('.', ',')}`;
        }

        function finalizarCompra() {
            if (confirm('Tem certeza que deseja finalizar a compra?')) {
                fetch('carrinho_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'acao=finalizar_compra'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`Compra finalizada com sucesso!\nCódigo do pedido: ${data.codigo_pedido}`);
                            location.reload();
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao finalizar compra');
                    });
            }
        }
    </script>
</body>

</html>