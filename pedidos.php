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
            <div class="order-header desktop-only">
                <span class="header-col">Pedido</span>
                <span class="header-col">Data</span>
                <span class="header-col">Total</span>
                <span class="header-col">Status</span>
            </div>

            <div class="order-item">
                <div class="order-info">
                    <span class="label mobile-only">Pedido:</span>
                    <span class="order-number">#SPDZ2023-1001</span>
                </div>
                <div class="order-info">
                    <span class="label mobile-only">Data:</span>
                    <span class="order-date">25/09/2025</span>
                </div>
                <div class="order-info">
                    <span class="label mobile-only">Total:</span>
                    <span class="order-total">R$ 159,80</span>
                </div>
                <div class="order-info">
                    <span class="label mobile-only">Status:</span>
                    <span class="order-status delivered">Entregue</span>
                </div>
                </div>
            
            <div class="order-item">
                <div class="order-info">
                    <span class="label mobile-only">Pedido:</span>
                    <span class="order-number">#SPDZ2023-1002</span>
                </div>
                <div class="order-info">
                    <span class="label mobile-only">Data:</span>
                    <span class="order-date">28/09/2025</span>
                </div>
                <div class="order-info">
                    <span class="label mobile-only">Total:</span>
                    <span class="order-total">R$ 299,00</span>
                </div>
                <div class="order-info">
                    <span class="label mobile-only">Status:</span>
                    <span class="order-status processing">Em Processamento</span>
                </div>
                </div>

            <div class="order-item">
                <div class="order-info">
                    <span class="label mobile-only">Pedido:</span>
                    <span class="order-number">#SPDZ2023-1003</span>
                </div>
                <div class="order-info">
                    <span class="label mobile-only">Data:</span>
                    <span class="order-date">29/09/2025</span>
                </div>
                <div class="order-info">
                    <span class="label mobile-only">Total:</span>
                    <span class="order-total">R$ 50,50</span>
                </div>
                <div class="order-info">
                    <span class="label mobile-only">Status:</span>
                    <span class="order-status cancelled">Cancelado</span>
                </div>
                </div>
        </section>
    </div>
</body>
</html>