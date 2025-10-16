<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produto - SpeedZone</title>
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
            <img src="./assets/download.avif" alt="Produto: Kit de Adesivos SpeedZone" class="product-image">
        </div>

        <div class="product-details-section">
            <div class="details-content">
                <h1 class="product-title">Kit de Adesivos 'Velocidade Máxima'</h1>
                <p class="product-category">Acessórios para Veículos</p>
                <div class="product-price">
                    <span class="current-price">R$ 59,90</span>
                    <span class="original-price">R$ 79,90</span>
                </div>
                <p class="product-description">
                    Personalize seu carro com este kit exclusivo de adesivos SpeedZone.
                    Feito com material de alta durabilidade e resistente a raios UV e água.
                    Fácil aplicação e remoção sem danificar a pintura.
                </p>

                <div class="quantity-selector">
                    <label for="quantity">Quantidade:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1">
                </div>

                <button type="button" class="add-to-cart-btn">Adicionar ao Carrinho</button>
            </div>
        </div>
    </div>
</body>

</html>