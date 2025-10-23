<?php
require_once 'connection.php';

// Queries SQL para criar as tabelas do carrinho
$queries = [
    // Tabela para carrinhos de compras
    "CREATE TABLE IF NOT EXISTS carrinho (
        id_carrinho INT PRIMARY KEY AUTO_INCREMENT,
        id_usuario INT,
        status ENUM('Ativo', 'Finalizado', 'Abandonado') DEFAULT 'Ativo',
        total DECIMAL(10, 2) DEFAULT 0.00,
        frete DECIMAL(10, 2) DEFAULT 0.00,
        total_final DECIMAL(10, 2) DEFAULT 0.00,
        CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
    )",
    
    // Tabela para itens do carrinho
    "CREATE TABLE IF NOT EXISTS carrinho_itens (
        id_item INT PRIMARY KEY AUTO_INCREMENT,
        id_carrinho INT,
        id_produto INT,
        quantidade INT NOT NULL,
        preco_unitario DECIMAL(10, 2) NOT NULL,
        subtotal DECIMAL(10, 2) NOT NULL,
        CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_carrinho) REFERENCES carrinho(id_carrinho) ON DELETE CASCADE,
        FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE,
        UNIQUE KEY unique_carrinho_produto (id_carrinho, id_produto)
    )",
    
    // Tabela para pedidos (quando o carrinho é finalizado)
    "CREATE TABLE IF NOT EXISTS pedidos_finalizados (
        id_pedido INT PRIMARY KEY AUTO_INCREMENT,
        id_carrinho INT,
        id_usuario INT,
        codigo_pedido VARCHAR(20) UNIQUE NOT NULL,
        status_pedido ENUM('Pendente', 'Confirmado', 'Em Preparação', 'Enviado', 'Entregue', 'Cancelado') DEFAULT 'Pendente',
        total_produtos DECIMAL(10, 2) NOT NULL,
        frete DECIMAL(10, 2) NOT NULL,
        total_final DECIMAL(10, 2) NOT NULL,
        data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_carrinho) REFERENCES carrinho(id_carrinho),
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
    )"
];

echo "<h2>Criando Tabelas do Carrinho</h2>";

try {
    // Verificar se a conexão foi estabelecida
    if ($conn->connect_error) {
        throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }

    foreach ($queries as $index => $query) {
        if ($conn->query($query) === TRUE) {
            $tabela = match($index) {
                0 => "carrinho",
                1 => "carrinho_itens", 
                2 => "pedidos_finalizados"
            };
            echo "✅ Tabela '{$tabela}' criada com sucesso!<br>";
        } else {
            echo "❌ Erro ao criar tabela: " . $conn->error . "<br>";
        }
    }

    echo "<br><strong>Todas as tabelas do carrinho foram criadas com sucesso!</strong><br>";
    echo "<a href='index.php'>Voltar para o site</a>";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
} finally {
    // Fechar conexão
    if (isset($conn)) {
        $conn->close();
    }
}
?>

