<?php require_once __DIR__ . '/session.php'; ?>
<?php

$user_cargo = $_SESSION['cargo'] ?? 'Cliente';
if (!in_array($user_cargo, ['Funcionario', 'Administrador'])) {
    // Redireciona para a página principal se não tiver permissão
    header("Location: index.php?error=access_denied");
    exit;
}


require_once 'connection.php';

// Array para armazenar os produtos
$produtos = array();
$erro = '';
$sucesso = '';
$termo_busca = '';

// Capturar mensagens da URL (vindas do novo_produto.php)
if (isset($_GET['erro'])) {
    $erro = $_GET['erro'];
}
if (isset($_GET['sucesso'])) {
    $sucesso = $_GET['sucesso'];
}

// Capturar termo de busca
if (isset($_GET['busca'])) {
    $termo_busca = trim($_GET['busca']);
}

try {
    // Verificar se a conexão foi estabelecida
    if ($conn->connect_error) {
        throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }

    // Consulta para buscar produtos (com ou sem filtro de busca)
    if (!empty($termo_busca)) {
        $sql = "SELECT * FROM produtos WHERE nome_produto LIKE ? OR cod_produto LIKE ? ORDER BY id_produto ASC";
        $stmt = $conn->prepare($sql);
        $busca_param = "%" . $termo_busca . "%";
        $stmt->bind_param("ss", $busca_param, $busca_param);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT * FROM produtos ORDER BY id_produto ASC";
        $result = $conn->query($sql);
    }

    // Verificar se a consulta foi executada com sucesso
    if ($result === false) {
        throw new Exception("Erro na consulta SQL: " . $conn->error);
    }

    // Processar resultados
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $produtos[] = $row;
        }
    }

} catch (Exception $e) {
    $erro = $e->getMessage();
    error_log("Erro em produtos.php: " . $erro);
} finally {
    // Fechar conexão
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
    <title>Gerenciar Produtos - Painel Admin</title>
    <link rel="stylesheet" href="produtos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="admin-layout">

        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-tachometer-alt"></i>
                <h2>Painel Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="admin.php" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
                <a href="produtos.php" class="nav-item"><i class="fas fa-box-open"></i> Produtos</a>
                <a href="vendas.php" class="nav-item"><i class="fas fa-chart-line"></i> Vendas</a>
                <a href="usuarios.php" class="nav-item sidebar-link active" data-target="usuarios-section"><i class="fas fa-users"></i> Usuários</a>
                <a href="suporteAdmin.php" class="nav-item sidebar-link" data-target="suporte-section"><i class="fas fa-headset"></i> Suporte</a>

                <a href="cupons.php" class="nav-item"><i class="fas fa-tags"></i> Cupons</a>
            </nav>
            <div class="logout-section">
                <a href="index.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </aside>
        <main class="main-content">
            <h1 class="page-title">Gerenciamento de Produtos</h1>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($sucesso)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($sucesso); ?>
                </div>
            <?php endif; ?>

            <section id="product-list-view" class="management-section active">
                <div class="action-bar">
                    <button class="add-btn" id="show-create-form"><i class="fas fa-plus"></i> Adicionar Novo
                        Produto</button>
                    <form method="GET" class="search-form">
                        <input type="text" name="busca" placeholder="Buscar produto por nome ou código..." 
                               class="search-input" value="<?php echo htmlspecialchars($termo_busca); ?>">
                        <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                        <?php if (!empty($termo_busca)): ?>
                            <a href="produtos.php" class="clear-search-btn" title="Limpar busca">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="data-table">
                    <div class="table-header product-grid-template">
                        <span>ID</span>
                        <span>Nome</span>
                        <span>Cod</span>
                        <span>Preço</span>
                        <span>Estoque</span>
                        <span>Ações</span>
                    </div>

                    <?php if (!empty($produtos)): ?>
                        <?php foreach ($produtos as $produto): ?>
                            <div class="table-row product-grid-template" data-product-id="<?php echo $produto['id_produto']; ?>">
                                <span class="cell-data"><?php echo $produto['id_produto']; ?></span>
                                <span class="cell-data"><?php echo htmlspecialchars($produto['nome_produto']); ?></span>
                                <span class="cell-data"><?php echo htmlspecialchars($produto['cod_produto']); ?></span>
                                <span class="cell-data">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                <span class="cell-data <?php echo $produto['qtd_estoque'] > 50 ? 'green' : ($produto['qtd_estoque'] > 10 ? 'orange' : 'red'); ?>">
                                    <?php echo $produto['qtd_estoque']; ?>
                                </span>
                                <span class="cell-data actions">
                                    <button class="action-btn edit edit-btn" title="Editar Produto" data-id="<?php echo $produto['id_produto']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete delete-btn" title="Excluir Produto" data-id="<?php echo $produto['id_produto']; ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="table-row product-grid-template">
                            <span class="cell-data" colspan="6" style="text-align: center; padding: 20px;">
                                <?php if (!empty($termo_busca)): ?>
                                    Nenhum produto encontrado para "<?php echo htmlspecialchars($termo_busca); ?>".
                                    <br><a href="produtos.php" style="color: #1a731d; text-decoration: underline;">Ver todos os produtos</a>
                                <?php else: ?>
                                    Nenhum produto encontrado
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section id="product-form-view" class="management-section form-view">
                <a href="#" class="back-link" id="hide-form-link"><i class="fas fa-arrow-left"></i> Voltar para
                    Lista</a>

                <h2 class="section-heading" id="form-title">Adicionar Novo Produto</h2>

                <div class="form-container">
                    <form action="novo_produto.php" method="POST" class="product-form" enctype="multipart/form-data">

                        <input type="hidden" id="product-id" name="id" value="">

                        <fieldset>
                            <legend>Informações do Produto</legend>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="nome">Nome do Produto *</label>
                                    <input type="text" id="nome" name="nome" required>
                                </div>
                                <div class="form-group">
                                    <label for="sku">SKU/Código *</label>
                                    <input type="text" id="sku" name="sku" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="descricao">Descrição Completa</label>
                                <textarea id="descricao" name="descricao" rows="4"
                                    placeholder="Detalhes técnicos, compatibilidade, etc."></textarea>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Preço e Inventário</legend>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="preco">Preço (R$) *</label>
                                    <input type="number" id="preco" name="preco" step="0.01" min="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="estoque">Quantidade em Estoque *</label>
                                    <input type="number" id="estoque" name="estoque" min="0" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="categoria">Categoria</label>
                                <select id="categoria" name="categoria">
                                    <option value="pistoes">Pistões</option>
                                    <option value="bielas">Bielas</option>
                                    <option value="virabrequins">Virabrequins</option>
                                    <option value="comando_valvulas">Comando de Válvulas</option>
                                    <option value="tuchos_balancins">Tuchos / Balancins</option>
                                    <option value="juntas_motor">Juntas de Motor</option>
                                    <option value="kit_stroker">Kit Stroker</option>
                                    <option value="kit_turbo">Kit Turbo</option>
                                    <option value="turbina">Turbina</option>
                                    <option value="supercharger">Supercharger</option>
                                    <option value="intercooler">Intercooler</option>
                                    <option value="wastegate">Wastegate</option>
                                    <option value="blowoff_valve">Blow-off Valve</option>
                                    <option value="tubulacoes_turbo">Tubulações de Turbo</option>
                                    <option value="filtro_ar_esportivo">Filtro de Ar Esportivo</option>
                                    <option value="kit_admissao_direta">Kit de Admissão Direta</option>
                                    <option value="corpo_borboleta">Corpo de Borboleta</option>
                                    <option value="coletor_admissao">Coletor de Admissão</option>
                                    <option value="bomba_combustivel">Bomba de Combustível</option>
                                    <option value="bico_injetor">Bico Injetor</option>
                                    <option value="regulador_pressao_combustivel">Regulador de Pressão de Combustível
                                    </option>
                                    <option value="fuel_rail">Fuel Rail</option>
                                    <option value="coletor_escape">Coletor de Escape</option>
                                    <option value="downpipe">Downpipe</option>
                                    <option value="midpipe">Midpipe</option>
                                    <option value="catback">Catback</option>
                                    <option value="valvula_escapamento">Válvula de Escapamento</option>
                                    <option value="silencioso_esportivo">Silencioso Esportivo</option>
                                    <option value="ecu_programavel">ECU Programável</option>
                                    <option value="reprogramacao_ecu">Reprogramação de ECU</option>
                                    <option value="piggyback">Piggyback</option>
                                    <option value="boost_controller">Boost Controller</option>
                                    <option value="sensores">Sensores</option>
                                    <option value="wideband">Wideband</option>
                                    <option value="data_logger">Data Logger</option>
                                    <option value="embreagem_esportiva">Embreagem Esportiva</option>
                                    <option value="volante_motor_aliviado">Volante de Motor Aliviado</option>
                                    <option value="short_shifter">Short Shifter</option>
                                    <option value="diferencial_lsd">Diferencial LSD</option>
                                    <option value="kit_cambio">Kit de Câmbio</option>
                                    <option value="coilover">Coilover</option>
                                    <option value="kit_suspensao_ar">Kit de Suspensão a Ar</option>
                                    <option value="molas_esportivas">Molas Esportivas</option>
                                    <option value="barra_estabilizadora">Barra Estabilizadora</option>
                                    <option value="camber_kit">Camber Kit</option>
                                    <option value="bracos_regulaveis">Braços Reguláveis</option>
                                    <option value="buchas_poliuretano">Buchas de Poliuretano</option>
                                    <option value="kit_big_brake">Kit Big Brake</option>
                                    <option value="disco_freio">Disco de Freio</option>
                                    <option value="pastilha_esportiva">Pastilha Esportiva</option>
                                    <option value="mangueira_malha_aco">Mangueira Malha de Aço</option>
                                    <option value="fluido_freio">Fluido de Freio</option>
                                    <option value="radiador">Radiador</option>
                                    <option value="ventoinha">Ventoinha</option>
                                    <option value="mangueira_silicone">Mangueira de Silicone</option>
                                    <option value="termostato_esportivo">Termostato Esportivo</option>
                                    <option value="radiador_oleo">Radiador de Óleo</option>
                                    <option value="reservatorio_expansao">Reservatório de Expansão</option>
                                    <option value="aerofolio">Aerofólio</option>
                                    <option value="splitter_dianteiro">Splitter Dianteiro</option>
                                    <option value="difusor_traseiro">Difusor Traseiro</option>
                                    <option value="saias_laterais">Saias Laterais</option>
                                    <option value="capo_fibra">Capô de Fibra</option>
                                    <option value="grade_frontal">Grade Frontal</option>
                                    <option value="scoop_capo">Scoop de Capô</option>
                                    <option value="roda_esportiva">Roda Esportiva</option>
                                    <option value="roda_beadlock">Roda Beadlock</option>
                                    <option value="pneu_semi_slick">Pneu Semi-Slick</option>
                                    <option value="pneu_slick">Pneu Slick</option>
                                    <option value="espacador_roda">Espaçador de Roda</option>
                                    <option value="porca_esportiva">Porca Esportiva</option>
                                    <option value="farol_led">Farol de LED</option>
                                    <option value="lanterna_traseira">Lanterna Traseira</option>
                                    <option value="drl">DRL</option>
                                    <option value="barra_led">Barra de LED</option>
                                    <option value="kit_xenon">Kit Xenon</option>
                                    <option value="banco_concha">Banco Concha</option>
                                    <option value="volante_esportivo">Volante Esportivo</option>
                                    <option value="pedaleira">Pedaleira</option>
                                    <option value="tapete_personalizado">Tapete Personalizado</option>
                                    <option value="manopla_cambio">Manopla de Câmbio</option>
                                    <option value="painel_digital">Painel Digital</option>
                                    <option value="manometro">Manômetro</option>
                                    <option value="santo_antonio">Santo Antônio</option>
                                    <option value="cinto_4pontos">Cinto 4 Pontos</option>
                                    <option value="extintor_incendio">Extintor de Incêndio</option>
                                    <option value="chave_geral">Chave Geral</option>
                                    <option value="torquimetro">Torquímetro</option>
                                    <option value="scanner_obd2">Scanner OBD2</option>
                                    <option value="ferramenta_montagem">Ferramenta de Montagem</option>
                                    <option value="medidor_compressao">Medidor de Compressão</option>
                                    <option value="kit_stage1">Kit Stage 1</option>
                                    <option value="kit_stage2">Kit Stage 2</option>
                                    <option value="kit_stage3">Kit Stage 3</option>
                                    <option value="kit_swap_motor">Kit Swap de Motor</option>
                                    <option value="kit_track_day">Kit Track Day</option>
                                    <option value="kit_drift">Kit Drift</option>
                                    <option value="wrap_automotivo">Wrap Automotivo</option>
                                    <option value="pelicula">Película</option>
                                    <option value="adesivos">Adesivos</option>
                                    <option value="placa_expositora">Placa Expositora</option>
                                    <option value="capa_automotiva">Capa Automotiva</option>
                                </select>

                            </div>
                        </fieldset>

                                    <div class="form-group">
    <label for="imagem">Imagem do Produto *</label>
    <input type="file" id="imagem" name="imagem" accept="image/*">
</div>

                        <button type="submit" class="submit-btn" id="submit-product-btn">Salvar Produto</button>
                    </form>
                </div>
            </section>

        </main>
    </div>

    <script src="script.js"></script>
</body>

</html>