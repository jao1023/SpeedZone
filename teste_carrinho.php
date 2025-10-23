<?php
session_start();
require_once 'carrinho_functions.php';

echo "<h2>Teste do Carrinho</h2>";

// Adicionar um produto de teste ao carrinho
adicionarAoCarrinho(1, 2);

echo "✅ Produto adicionado ao carrinho<br>";

// Obter detalhes do carrinho
$carrinho = obterCarrinhoComDetalhes();

echo "📦 Itens no carrinho: " . count($carrinho) . "<br>";

if (!empty($carrinho)) {
    foreach ($carrinho as $item) {
        echo "- " . $item['nome_produto'] . " (Qtd: " . $item['quantidade_carrinho'] . ")<br>";
    }
}

echo "<br><a href='carrinho.php'>Ver Carrinho</a>";
?>

