<?php require_once __DIR__ . '/session.php'; ?>
<?php
// Verificar se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("Location: login.php");
    exit;
}

// Verificar se o usuário tem permissão (Funcionario ou Administrador)
$user_cargo = $_SESSION['cargo'] ?? 'Cliente';
if (!in_array($user_cargo, ['Funcionario', 'Administrador'])) {
    // Redireciona para a página principal se não tiver permissão
    header("Location: index.php?error=access_denied");
    exit;
}

require_once 'connection.php';

$total_vendas_mes = 0.0;
$total_pedidos = 0;
$total_usuarios_ativos = 0;
$total_itens_estoque = 0;
$chart_labels = [];
$chart_values = [];
try {
    $sqlTotal = "SELECT COALESCE(SUM(total_final), 0) AS total
                 FROM pedidos_finalizados
                 WHERE status_pedido != 'Cancelado'";
    $result = $conn->query($sqlTotal);
    if ($result && $row = $result->fetch_assoc()) {
        $total_vendas_mes = (float)$row['total'];
    }
    // Total de pedidos feitos (todas as datas)
    $sqlCount = "SELECT COUNT(*) AS qtd FROM pedidos_finalizados";
    $result2 = $conn->query($sqlCount);
    if ($result2 && $row2 = $result2->fetch_assoc()) {
        $total_pedidos = (int)$row2['qtd'];
    }

    // Total de usuários ativos
    $sqlUsers = "SELECT COUNT(*) AS qtd FROM usuario WHERE status_conta = 'Ativo'";
    $result3 = $conn->query($sqlUsers);
    if ($result3 && $row3 = $result3->fetch_assoc()) {
        $total_usuarios_ativos = (int)$row3['qtd'];
    }

    // Total de itens em estoque (soma das quantidades)
    $sqlStock = "SELECT COALESCE(SUM(qtd_estoque), 0) AS total_itens FROM produtos";
    $result5 = $conn->query($sqlStock);
    if ($result5 && $row5 = $result5->fetch_assoc()) {
        $total_itens_estoque = (int)$row5['total_itens'];
    }

    // Vendas por dia (últimos 30 dias)
    $sqlChart = "SELECT DATE(data_pedido) AS dia, COALESCE(SUM(total_final), 0) AS total
                 FROM pedidos_finalizados
                 WHERE status_pedido != 'Cancelado'
                   AND data_pedido >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
                 GROUP BY DATE(data_pedido)
                 ORDER BY dia ASC";
    $result4 = $conn->query($sqlChart);

    $labels = [];
    $values = [];
    if ($result4) {
        while ($r = $result4->fetch_assoc()) {
            $labels[] = date('d/m', strtotime($r['dia']));
            $values[] = (float)$r['total'];
        }
    }
    $chart_labels = $labels;
    $chart_values = $values;

    // Configuração dinâmica do eixo Y: até 150k -> passos de 10k; acima -> 100k
    $chart_max_value = 0.0;
    foreach ($chart_values as $v) { if ($v > $chart_max_value) { $chart_max_value = $v; } }
    $y_step = ($chart_max_value <= 150000) ? 10000 : 100000;
    $y_max  = max($y_step, ceil(($chart_max_value > 0 ? $chart_max_value : $y_step) / $y_step) * $y_step);
} catch (Exception $e) {
    error_log('Erro ao calcular total de vendas: ' . $e->getMessage());
}
?>
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
                <a href="cupons.php" class="nav-item"><i class="fas fa-tags"></i> Cupons</a>
            </nav>
            <div class="logout-section">
                <a href="index.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Bem-vindo, <?php echo htmlspecialchars($user_cargo); ?>!</h1>

            <section id="dashboard" class="dashboard-section active">
                <div class="stats-grid">
                    <div class="stat-card total-sales">
                        <i class="fas fa-dollar-sign"></i>
                        <p class="stat-label">Vendas Totais (Mês)</p>
                        <p class="stat-value">R$ <?php echo number_format($total_vendas_mes, 2, ',', '.'); ?></p>
                    </div>
                    <div class="stat-card total-orders">
                        <i class="fas fa-truck"></i>
                        <p class="stat-label">Pedidos Totais</p>
                        <p class="stat-value"><?php echo number_format($total_pedidos, 0, ',', '.'); ?></p>
                    </div>
                    <div class="stat-card total-users">
                        <i class="fas fa-user-plus"></i>
                        <p class="stat-label">Usuários Ativos</p>
                        <p class="stat-value"><?php echo number_format($total_usuarios_ativos, 0, ',', '.'); ?></p>
                    </div>
                    <div class="stat-card total-products">
                        <i class="fas fa-boxes"></i>
                        <p class="stat-label">Itens em Estoque</p>
                        <p class="stat-value"><?php echo number_format($total_itens_estoque, 0, ',', '.'); ?></p>
                    </div>
                </div>
                <div class="chart-card" style="background:#fff; border-radius:12px; padding:16px; margin-top:16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="margin:0 0 12px;">Vendas por Dia (últimos 30 dias)</h3>
                    <canvas id="salesByDayChart" style="width:100%; height:320px;"></canvas>
                </div>
            </section>
            
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function(){
            const ctx = document.getElementById('salesByDayChart');
            if (!ctx) return;
            let labels = <?php echo json_encode($chart_labels); ?>;
            let values = <?php echo json_encode($chart_values); ?>;
            let yMax = <?php echo json_encode($y_max ?? 0); ?>;
            let yStep = <?php echo json_encode($y_step ?? 10000); ?>;
 
             // Guardar contra dados vazios
             if (!Array.isArray(labels) || labels.length === 0) {
                 labels = ['—'];
                 values = [0];
                 yMax = yMax && yMax > 0 ? yMax : 10000;
                 yStep = yStep && yStep > 0 ? yStep : 10000;
             }
 
             // Evitar grande número de ticks: limitar a ~10 divisões
             if (yMax > 0 && yStep > 0) {
                 const steps = Math.ceil(yMax / yStep);
                 if (steps > 10) {
                     const targetStep = Math.ceil((yMax / 10));
                     // arredonda para múltiplos de 1000
                     const rounded = Math.ceil(targetStep / 1000) * 1000;
                     yStep = Math.max(rounded, 1000);
                 }
             }
             // Garantir yMax coerente com yStep
             if (yStep > 0) {
                 yMax = Math.max(yStep, Math.ceil((yMax || yStep) / yStep) * yStep);
             }

            // Evitar múltiplas instâncias
            if (window.__salesByDayChartInstance) {
                try { window.__salesByDayChartInstance.destroy(); } catch(e) {}
            }

            window.__salesByDayChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Vendas (R$)',
                        data: values,
                        borderColor: 'rgba(37, 99, 235, 1)',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: false,
                    aspectRatio: 2.5,
                    animation: false,
                    scales: {
                        y: {
                            suggestedMin: 0,
                            suggestedMax: yMax,
                            ticks: {
                                stepSize: yStep,
                                maxTicksLimit: 10,
                                callback: function(value){
                                    return 'R$ ' + Number(value).toLocaleString('pt-BR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context){
                                    const v = context.parsed.y || 0;
                                    return 'R$ ' + Number(v).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
        })();
    </script>
</body>

</html>