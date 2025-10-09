<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - SpeedZone</title>
    <link rel="stylesheet" href="admin.css">
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
                <a href="promocoes.php" class="nav-item"><i class="fas fa-tags"></i> Promoções</a>
            </nav>
            <div class="logout-section">
                <a href="index.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Bem-vindo, Administrador!</h1>

            <section id="dashboard" class="dashboard-section active">
                <div class="stats-grid">
                    <div class="stat-card total-sales">
                        <i class="fas fa-dollar-sign"></i>
                        <p class="stat-label">Vendas Totais (Mês)</p>
                        <p class="stat-value">R$ 15.450,90</p>
                    </div>
                    <div class="stat-card total-orders">
                        <i class="fas fa-truck"></i>
                        <p class="stat-label">Pedidos Pendentes</p>
                        <p class="stat-value">42</p>
                    </div>
                    <div class="stat-card total-users">
                        <i class="fas fa-user-plus"></i>
                        <p class="stat-label">Novos Usuários (Mês)</p>
                        <p class="stat-value">128</p>
                    </div>
                    <div class="stat-card total-products">
                        <i class="fas fa-boxes"></i>
                        <p class="stat-label">Total de Produtos</p>
                        <p class="stat-value">350</p>
                    </div>
                </div>
            </section>