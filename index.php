<?php
session_start();

// Redireciona se o usuário não estiver logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("Location: login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpeedZone AutoParts</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
    <header class="navbar">
        <div class="logo">
            <a href="#">SpeedZone AutoParts</a>
        </div>
        <div class="user-menu-container">
            <button class="user-menu-btn">
                Menu <i class="fas fa-chevron-down"></i>
            </button>
            <div class="user-dropdown-content">
                <a href="carrinho.php"><i class="fas fa-shopping-cart"></i> Carrinho</a>
                <a href="pedidos.php"><i class="bi bi-truck-front-fill"></i> Meus Pedidos</a>
                <a href="config.php"><i class="fas fa-cog"></i> Configurações</a>
                <a href="admin.php"><i class="fas fa-cog"></i> Gerenciar Loja</a>
                <a href="suporte.php"><i class="fas fa-cog"></i> Suporte</a>
                <a href="login.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </div>
    </header>

    <section class="carousel-section">
        <div class="carousel-container">
            <div class="carousel-images">
                <img src="./assets/estoque.png" alt="" class="carousel-img active">
                <img src="./assets/faixada.png" alt="" class="carousel-img">
                <img src="./assets/oficina.png" alt="" class="carousel-img">
                <img src="./assets/loja.png" alt="" class="carousel-img">
            </div>
            <button class="carousel-btn prev-btn">&lt;</button>
            <button class="carousel-btn next-btn">&gt;</button>
        </div>
    </section>

    <main class="main-content">
        <div class="banner">
            <div class="banner-text-box">
                <h1 class="banner-title">As melhores peças de alta qualidade para aquele projetinho você encontra aqui na SpeedZone AutoParts</h1>
            </div>
            <img src="https://i.imgur.com/7w3w5Tq.png" alt="Produto de alta qualidade" class="banner-image">
        </div>

        <section class="catalog-section">
            <h2 class="section-title">Catálogo</h2>
            <div class="search-bar-container">
                <input type="text" placeholder="Pesquisa" class="search-input">
                <button class="search-btn">Procurar</button>
            </div>
            
            <div class="product-grid">
                <div class="product-card">
                    <img src="https://i.imgur.com/vHqQ9zG.png" alt="Produto 1" class="product-img">
                    <h3 class="product-name">Fueltech FT450</h3>
                    <p class="product-description">Módulo de controle para sistema de injeção eletrônica em veículos automotores.</p>
                    <a href="produto.php" class="buy-btn">Comprar</a>
                </div>
                <div class="product-card">
                    <img src="https://i.imgur.com/k9b8ZqY.png" alt="Produto 2" class="product-img">
                    <h3 class="product-name">Kit de Adesivos 'Velocidade Máxima'</h3>
                    <p class="product-description">Personalize seu carro com este kit de adesivos exclusivos.</p>
                    <a href="produto.php" class="buy-btn">Comprar</a>
                </div>
                <div class="product-card">
                    <img src="https://i.imgur.com/vHqQ9zG.png" alt="Produto 3" class="product-img">
                    <h3 class="product-name">Fueltech FT450</h3>
                    <p class="product-description">Módulo de controle para sistema de injeção eletrônica em veículos automotores.</p>
                    <a href="produto.php" class="buy-btn">Comprar</a>
                </div>
                <div class="product-card">
                    <img src="https://i.imgur.com/k9b8ZqY.png" alt="Produto 4" class="product-img">
                    <h3 class="product-name">Kit de Adesivos 'Velocidade Máxima'</h3>
                    <p class="product-description">Personalize seu carro com este kit de adesivos exclusivos.</p>
                    <a href="produto.php" class="buy-btn">Comprar</a>
                </div>
                <div class="product-card">
                    <img src="https://i.imgur.com/vHqQ9zG.png" alt="Produto 5" class="product-img">
                    <h3 class="product-name">Fueltech FT450</h3>
                    <p class="product-description">Módulo de controle para sistema de injeção eletrônica em veículos automotores.</p>
                    <a href="produto.php" class="buy-btn">Comprar</a>
                </div>
                <div class="product-card">
                    <img src="https://i.imgur.com/k9b8ZqY.png" alt="Produto 6" class="product-img">
                    <h3 class="product-name">Kit de Adesivos 'Velocidade Máxima'</h3>
                    <p class="product-description">Personalize seu carro com este kit de adesivos exclusivos.</p>
                    <a href="produto.php" class="buy-btn">Comprar</a>
                </div>
            </div>
        </section>
    </main>
    
    <script src="carrossel.js"></script>
</body>
</html>