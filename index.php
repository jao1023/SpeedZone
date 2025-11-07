<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("Location: login.php");
    exit;
}

require_once 'connection.php';

$produtos = array();
$termo_busca = '';

if (isset($_GET['busca'])) {
    $termo_busca = trim($_GET['busca']);
}

$user_cargo = $_SESSION['cargo'] ?? 'Cliente';
$pode_gerenciar = in_array($user_cargo, ['Funcionario', 'Administrador']);

try {
    if ($conn->connect_error) {
        throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }
    if (!empty($termo_busca)) {
        $sql = "SELECT * FROM produtos WHERE nome_produto LIKE ? OR cod_produto LIKE ? ORDER BY id_produto ASC LIMIT 12";
        $stmt = $conn->prepare($sql);
        $busca_param = "%" . $termo_busca . "%";
        $stmt->bind_param("ss", $busca_param, $busca_param);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT * FROM produtos ORDER BY id_produto ASC LIMIT 12";
        $result = $conn->query($sql);
    }

    if ($result === false) {
        throw new Exception("Erro na consulta SQL: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $produtos[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Erro em index.php: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
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
                <?php if ($pode_gerenciar): ?>
                    <a href="admin.php"><i class="fas fa-tools"></i> Gerenciar Loja</a>
                <?php endif; ?>
                <a href="suporte.php"><i class="fas fa-headset"></i> Suporte</a>
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
            <img src="./assets/Design sem nome.png" alt="Produto de alta qualidade" class="banner-image">
        </div>

        <section class="catalog-section">
            <h2 class="section-title">Catálogo</h2>
            <div class="search-bar-container">
                <form method="GET" class="search-form">
                    <input type="text" name="busca" placeholder="Pesquisar produtos..." class="search-input" value="<?php echo htmlspecialchars($termo_busca); ?>">
                    <button type="submit" class="search-btn">Procurar</button>
                    <?php if (!empty($termo_busca)): ?>
                        <a href="index.php" class="clear-search-btn" title="Limpar busca">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="product-grid">
                <?php if (!empty($produtos)): ?>
                    <?php foreach ($produtos as $produto): ?>
                        <div class="product-card">
                            <img
                                src="<?php echo htmlspecialchars($produto['imagem'] ?? 'uploads/sem_imagem.png'); ?>"
                                alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>"
                                class="product-img"
                                style="width:200px; height:200px; object-fit:contain;"
                                onerror="this.src='uploads/sem_imagem.png';">
                            <h3 class="product-name"><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($produto['descricao_produto']); ?></p>
                            <div class="product-price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                            <?php if ($produto['qtd_estoque'] <= 0): ?>
                                <div class="product-stock">
                                    <span class="stock-unavailable">Fora de estoque</span>
                                </div>
                            <?php endif; ?>
                            <a href="produto.php?id=<?php echo $produto['id_produto']; ?>" class="buy-btn">
                                <?php echo $produto['qtd_estoque'] > 0 ? 'Comprar' : 'Indisponível'; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <?php if (!empty($termo_busca)): ?>
                            <h3>Nenhum produto encontrado para "<?php echo htmlspecialchars($termo_busca); ?>"</h3>
                            <p>Tente uma busca diferente ou <a href="index.php">veja todos os produtos</a></p>
                        <?php else: ?>
                            <h3>Nenhum produto disponível no momento</h3>
                            <p>Volte em breve para ver nossos produtos!</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="carrossel.js"></script>
</body>

</html>