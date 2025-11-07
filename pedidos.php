<?php require_once __DIR__ . '/session.php'; ?>
<?php
require_once 'connection.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['user_id'] ?? null;

$pedidos = array();
if ($id_usuario) {
    try {
        $sql = "SELECT DISTINCT pf.codigo_pedido as cod_pedido_base,
                       p.status_pedido, pf.total_final, pf.data_pedido 
                FROM pedidos_finalizados pf
                LEFT JOIN pedidos p ON SUBSTRING(p.cod_pedido, 1, LOCATE('-', p.cod_pedido, LOCATE('-', p.cod_pedido) + 1) - 1) = pf.codigo_pedido
                WHERE pf.id_usuario = ? 
                ORDER BY pf.data_pedido DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Erro ao buscar pedidos: " . $e->getMessage());
    }
}

function formatarData($data)
{
    return date('d/m/Y', strtotime($data));
}

function getStatusClass($status)
{
    switch ($status) {
        case 'Entregue':
            return 'delivered';
        case 'Em Processamento':
            return 'processing';
        case 'Cancelado':
            return 'cancelled';
        default:
            return 'processing';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - SpeedZone</title>
    <link rel="stylesheet" href="pedidos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>

<body>
    <a href="javascript:history.back()" class="back-btn">
        &lt; Voltar
    </a>
    <div class="container">
        <h1 class="page-title">Meus Pedidos</h1>

        <section class="orders-section">
            <?php if (!empty($pedidos)): ?>
                <div class="order-header desktop-only">
                    <span class="header-col">Pedido</span>
                    <span class="header-col">Data</span>
                    <span class="header-col">Total</span>
                    <span class="header-col">Status</span>
                </div>

                <?php foreach ($pedidos as $pedido): ?>
                    <div class="order-item">
                        <div class="order-info">
                            <span class="label mobile-only">Pedido:</span>
                            <span class="order-number">#<?php echo htmlspecialchars($pedido['cod_pedido_base']); ?></span>
                        </div>
                        <div class="order-info">
                            <span class="label mobile-only">Data:</span>
                            <span class="order-date"><?php echo formatarData($pedido['data_pedido']); ?></span>
                        </div>
                        <div class="order-info">
                            <span class="label mobile-only">Total:</span>
                            <span class="order-total">R$ <?php echo number_format($pedido['total_final'], 2, ',', '.'); ?></span>
                        </div>
                        <div class="order-info">
                            <span class="label mobile-only">Status:</span>
                            <span class="order-status <?php echo getStatusClass($pedido['status_pedido']); ?>">
                                <?php echo htmlspecialchars($pedido['status_pedido']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-orders">
                    <h3>Nenhum pedido encontrado</h3>
                    <p>Você ainda não fez nenhum pedido.</p>
                    <a href="index.php" class="continue-shopping-btn">Continuar Comprando</a>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>