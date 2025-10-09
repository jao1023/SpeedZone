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
                <div class="cart-item">
                    <img src="https://i.imgur.com/k9b8ZqY.png" alt="Kit de Adesivos 'Velocidade Máxima'" class="item-image">
                    <div class="item-details">
                        <p class="item-name">Kit de Adesivos 'Velocidade Máxima'</p>
                        <p class="item-price">R$ 59,90</p>
                        <div class="item-actions">
                            <input type="number" value="1" min="1" class="item-quantity">
                            <button class="remove-btn">Remover</button>
                        </div>
                    </div>
                </div>

                <div class="cart-item">
                    <img src="https://i.imgur.com/vHqQ9zG.png" alt="Boné SpeedZone" class="item-image">
                    <div class="item-details">
                        <p class="item-name">Boné SpeedZone - Edição Limitada</p>
                        <p class="item-price">R$ 99,90</p>
                        <div class="item-actions">
                            <input type="number" value="1" min="1" class="item-quantity">
                            <button class="remove-btn">Remover</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cart-summary-section">
                <div class="summary-details">
                    <h2 class="summary-title">Resumo do Pedido</h2>
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span class="subtotal-price">R$ 159,80</span>
                    </div>
                    <div class="summary-line">
                        <span>Frete</span>
                        <span class="shipping-price">R$ 20,00</span>
                    </div>
                    <div class="summary-total-line">
                        <span>Total</span>
                        <span class="total-price">R$ 179,80</span>
                    </div>
                </div>
                <button type="button" class="checkout-btn">Finalizar Compra</button>
            </div>
        </div>
    </div>
</body>
</html>