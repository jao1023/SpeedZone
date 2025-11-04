<?php require_once __DIR__ . '/session.php'; ?>
<?php

require_once 'connection.php';

function getLoggedInUserId() {
	return $_SESSION['user_id'] ?? null;
}

function getOrCreateActiveCartIdForUser(int $userId) {
	global $conn;
	$sql = "SELECT id_carrinho FROM carrinho WHERE id_usuario = ? AND status = 'Ativo' LIMIT 1";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$stmt->bind_result($idCarrinho);
	if ($stmt->fetch()) {
		$stmt->close();
		return (int)$idCarrinho;
	}
	$stmt->close();
	$insert = $conn->prepare("INSERT INTO carrinho (id_usuario, status, total, frete, total_final) VALUES (?, 'Ativo', 0, 0, 0)");
	$insert->bind_param("i", $userId);
	$insert->execute();
	$newId = (int)$conn->insert_id;
	$insert->close();
	return $newId;
}

function getUserCartItemsFromDb(int $userId) {
	global $conn;
	$idCarrinho = getOrCreateActiveCartIdForUser($userId);
	$sql = "SELECT ci.id_produto, ci.quantidade, p.* FROM carrinho_itens ci JOIN produtos p ON p.id_produto = ci.id_produto WHERE ci.id_carrinho = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $idCarrinho);
	$stmt->execute();
	$result = $stmt->get_result();
	$items = [];
	while ($row = $result->fetch_assoc()) {
		$row['quantidade_carrinho'] = (int)$row['quantidade'];
		$row['subtotal'] = (float)$row['preco'] * (int)$row['quantidade'];
		$items[] = $row;
	}
	$stmt->close();
	return $items;
}

/**
 * Adicionar item ao carrinho com validação de estoque
 */
function adicionarAoCarrinho($produto_id, $quantidade = 1) {
	global $conn;

	if ($quantidade <= 0) {
		return array('success' => false, 'message' => 'Quantidade inválida.');
	}

	// Verificar estoque atual do produto
	$sql = "SELECT qtd_estoque, nome_produto FROM produtos WHERE id_produto = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $produto_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows === 0) {
		$stmt->close();
		return array('success' => false, 'message' => 'Produto não encontrado.');
	}
	$produto = $result->fetch_assoc();
	$stmt->close();

	$estoque_disponivel = intval($produto['qtd_estoque']);
	if ($estoque_disponivel <= 0) {
		return array('success' => false, 'message' => 'Produto sem estoque.');
	}

	$userId = getLoggedInUserId();
	if ($userId) {
		// DB-backed cart
		$idCarrinho = getOrCreateActiveCartIdForUser($userId);
		// Quantidade existente
		$qStmt = $conn->prepare("SELECT quantidade FROM carrinho_itens WHERE id_carrinho = ? AND id_produto = ?");
		$qStmt->bind_param("ii", $idCarrinho, $produto_id);
		$qStmt->execute();
		$qStmt->bind_result($qtdExistente);
		$tem = $qStmt->fetch();
		$qStmt->close();
		$quantidade_desejada = (int)($tem ? $qtdExistente : 0) + (int)$quantidade;
		if ($quantidade_desejada > $estoque_disponivel) {
			return array('success' => false, 'message' => 'Quantidade indisponível. Estoque atual: ' . $estoque_disponivel);
		}
		if ($tem) {
			$u = $conn->prepare("UPDATE carrinho_itens SET quantidade = ? WHERE id_carrinho = ? AND id_produto = ?");
			$u->bind_param("iii", $quantidade_desejada, $idCarrinho, $produto_id);
			$u->execute();
			$u->close();
		} else {
			// inserir com preco_unitario e subtotal coerentes ao momento
			$preco = 0.0;
			$p2 = $conn->prepare("SELECT preco FROM produtos WHERE id_produto = ?");
			$p2->bind_param("i", $produto_id);
			$p2->execute();
			$p2->bind_result($preco);
			$p2->fetch();
			$p2->close();
			$subtotal = (float)$preco * (int)$quantidade;
			$ins = $conn->prepare("INSERT INTO carrinho_itens (id_carrinho, id_produto, quantidade, preco_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
			$ins->bind_param("iiidd", $idCarrinho, $produto_id, $quantidade, $preco, $subtotal);
			$ins->execute();
			$ins->close();
		}
		return array('success' => true, 'message' => 'Produto adicionado ao carrinho!');
	}

	// Session-backed cart (guest)
	$quantidade_atual_no_carrinho = isset($_SESSION['carrinho'][$produto_id]) ? intval($_SESSION['carrinho'][$produto_id]) : 0;
	$quantidade_desejada = $quantidade_atual_no_carrinho + intval($quantidade);
	if ($quantidade_desejada > $estoque_disponivel) {
		return array('success' => false, 'message' => 'Quantidade indisponível. Estoque atual: ' . $estoque_disponivel);
	}
	$_SESSION['carrinho'][$produto_id] = $quantidade_desejada;
	return array('success' => true, 'message' => 'Produto adicionado ao carrinho!');
}

/**
 * Remover item do carrinho
 */
function removerDoCarrinho($produto_id) {
	global $conn;
	$userId = getLoggedInUserId();
	if ($userId) {
		$idCarrinho = getOrCreateActiveCartIdForUser($userId);
		$del = $conn->prepare("DELETE FROM carrinho_itens WHERE id_carrinho = ? AND id_produto = ?");
		$del->bind_param("ii", $idCarrinho, $produto_id);
		$ok = $del->execute();
		$del->close();
		return (bool)$ok;
	}
	if (isset($_SESSION['carrinho'][$produto_id])) {
		unset($_SESSION['carrinho'][$produto_id]);
		return true;
	}
	return false;
}

/**
 * Atualizar quantidade de um item no carrinho com validação de estoque
 */
function atualizarQuantidade($produto_id, $quantidade) {
	global $conn;

	if ($quantidade <= 0) {
		$removido = removerDoCarrinho($produto_id);
		return array('success' => $removido, 'message' => $removido ? 'Item removido do carrinho.' : 'Falha ao remover item.');
	}

	// Verificar estoque atual do produto
	$sql = "SELECT qtd_estoque FROM produtos WHERE id_produto = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $produto_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows === 0) {
		$stmt->close();
		return array('success' => false, 'message' => 'Produto não encontrado.');
	}
	$produto = $result->fetch_assoc();
	$stmt->close();

	$estoque_disponivel = intval($produto['qtd_estoque']);
	if ($quantidade > $estoque_disponivel) {
		return array('success' => false, 'message' => 'Quantidade indisponível. Estoque atual: ' . $estoque_disponivel);
	}

	$userId = getLoggedInUserId();
	if ($userId) {
		$idCarrinho = getOrCreateActiveCartIdForUser($userId);
		$u = $conn->prepare("UPDATE carrinho_itens SET quantidade = ? WHERE id_carrinho = ? AND id_produto = ?");
		$u->bind_param("iii", $quantidade, $idCarrinho, $produto_id);
		$ok = $u->execute();
		$u->close();
		return array('success' => (bool)$ok, 'message' => $ok ? 'Quantidade atualizada!' : 'Falha ao atualizar.');
	}

	if (!isset($_SESSION['carrinho'][$produto_id])) {
		return array('success' => false, 'message' => 'Produto não está no carrinho.');
	}
	$_SESSION['carrinho'][$produto_id] = intval($quantidade);
	return array('success' => true, 'message' => 'Quantidade atualizada!');
}

/**
 * Obter todos os itens do carrinho com detalhes do produto
 */
function obterCarrinhoComDetalhes() {
	global $conn;
	$userId = getLoggedInUserId();
	if ($userId) {
		return getUserCartItemsFromDb($userId);
	}
	if (empty($_SESSION['carrinho'])) {
		return array();
	}
	$carrinho_detalhado = array();
	try {
		if ($conn->connect_error) {
			throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
		}
		foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
			$sql = "SELECT * FROM produtos WHERE id_produto = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $produto_id);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result->num_rows > 0) {
				$produto = $result->fetch_assoc();
				$produto['quantidade_carrinho'] = $quantidade;
				$produto['subtotal'] = $produto['preco'] * $quantidade;
				$carrinho_detalhado[] = $produto;
			}
			$stmt->close();
		}
	} catch (Exception $e) {
		error_log("Erro ao obter detalhes do carrinho: " . $e->getMessage());
	}
	return $carrinho_detalhado;
}

/**
 * Calcular total do carrinho
 */
function calcularTotalCarrinho() {
	$carrinho = obterCarrinhoComDetalhes();
	$total = 0;
	foreach ($carrinho as $item) {
		$total += $item['subtotal'];
	}
	return $total;
}

/**
 * Contar total de itens no carrinho
 */
function contarItensCarrinho() {
	$userId = getLoggedInUserId();
	if ($userId) {
		global $conn;
		$idCarrinho = getOrCreateActiveCartIdForUser($userId);
		$sql = "SELECT COALESCE(SUM(quantidade),0) FROM carrinho_itens WHERE id_carrinho = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $idCarrinho);
		$stmt->execute();
		$stmt->bind_result($soma);
		$stmt->fetch();
		$stmt->close();
		return (int)$soma;
	}
	$total = 0;
	foreach ($_SESSION['carrinho'] as $quantidade) {
		$total += $quantidade;
	}
	return $total;
}

/**
 * Limpar carrinho
 */
function limparCarrinho() {
	$userId = getLoggedInUserId();
	if ($userId) {
		global $conn;
		$idCarrinho = getOrCreateActiveCartIdForUser($userId);
		$del = $conn->prepare("DELETE FROM carrinho_itens WHERE id_carrinho = ?");
		$del->bind_param("i", $idCarrinho);
		$del->execute();
		$del->close();
		return true;
	}
	$_SESSION['carrinho'] = array();
	return true;
}

/**
 * Validar e aplicar cupom
 */
function validarCupom($codigo_cupom) {
	global $conn;
	if (empty($codigo_cupom)) {
		return array('success' => false, 'message' => 'Código do cupom não informado');
	}
	try {
		$sql = "SELECT * FROM cupons WHERE codigo = ? AND status = 'Ativo'";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $codigo_cupom);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows === 0) {
			return array('success' => false, 'message' => 'Cupom não encontrado ou inativo');
		}
		$cupom = $result->fetch_assoc();
		// Verificar se o cupom expirou
		if ($cupom['data_expiracao'] && strtotime($cupom['data_expiracao']) < time()) {
			return array('success' => false, 'message' => 'Cupom expirado');
		}
		// Verificar limite de uso
		if ($cupom['uso_maximo'] && $cupom['usos_atuais'] >= $cupom['uso_maximo']) {
			return array('success' => false, 'message' => 'Cupom esgotado');
		}
		return array('success' => true, 'cupom' => $cupom);
	} catch (Exception $e) {
		error_log("Erro ao validar cupom: " . $e->getMessage());
		return array('success' => false, 'message' => 'Erro interno do servidor');
	}
}

/**
 * Calcular desconto do cupom (sobre subtotal)
 */
function calcularDescontoCupom($cupom, $subtotal) {
	if ($cupom['tipo'] === 'percentual') {
		return ($subtotal * $cupom['valor']) / 100;
	} elseif ($cupom['tipo'] === 'fixo') {
		return min($cupom['valor'], $subtotal);
	}
	return 0;
}

/**
 * Aplicar cupom ao carrinho: retorna total incluindo frete (considerando frete grátis)
 */
function aplicarCupomAoCarrinho($codigo_cupom) {
	$validacao = validarCupom($codigo_cupom);
	if (!$validacao['success']) {
		return $validacao;
	}
	$cupom = $validacao['cupom'];
	$subtotal = calcularTotalCarrinho();
	$freteBase = 20.00;
	// Detectar frete grátis: tipo 'frete' OU (tipo 'fixo' e valor 0.00)
	$isFreeShipping = (isset($cupom['tipo']) && $cupom['tipo'] === 'frete') || (isset($cupom['tipo']) && $cupom['tipo'] === 'fixo' && (float)$cupom['valor'] == 0.0);
	$desconto = $isFreeShipping ? 0.0 : calcularDescontoCupom($cupom, $subtotal);
	$freteAplicado = $isFreeShipping ? 0.0 : $freteBase;
	$totalFinal = $subtotal + $freteAplicado - $desconto;

	// Salvar cupom na sessão (só um por compra): substitui qualquer cupom anterior
	if (isset($_SESSION['cupom_aplicado'])) {
		unset($_SESSION['cupom_aplicado']);
	}
	$_SESSION['cupom_aplicado'] = array(
		'codigo' => $cupom['codigo'],
		'tipo' => $cupom['tipo'],
		'valor' => $cupom['valor'],
		'desconto' => $desconto,
		'frete_gratis' => $isFreeShipping
	);

	return array(
		'success' => true,
		'message' => 'Cupom aplicado com sucesso!',
		'desconto' => $desconto,
		'free_shipping' => $isFreeShipping,
		'total_final' => $totalFinal
	);
}

/**
 * Gerar código único do pedido
 */
function gerarCodigoPedido() {
	global $conn;
	try {
		$sql = "SELECT codigo_pedido FROM pedidos_finalizados WHERE codigo_pedido LIKE 'SPDZ-%' ORDER BY id_pedido DESC LIMIT 1";
		$result = $conn->query($sql);
		$proximo_numero = 1;
		if ($result && $result->num_rows > 0) {
			$ultimo_codigo = $result->fetch_assoc()['codigo_pedido'];
			$numero_atual = (int) substr($ultimo_codigo, 5);
			$proximo_numero = $numero_atual + 1;
		}
		return 'SPDZ-' . str_pad($proximo_numero, 4, '0', STR_PAD_LEFT);
	} catch (Exception $e) {
		error_log("Erro ao gerar código do pedido: " . $e->getMessage());
		return 'SPDZ-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
	}
}

/**
 * Finalizar compra - criar pedido
 */
function finalizarCompra($id_usuario = null) {
	global $conn;
	if (!$id_usuario) {
		return array('success' => false, 'message' => 'Usuário não identificado. Faça login.');
	}

	// Itens: usa DB para logado; fallback para sessão se necessário
	$itens = getUserCartItemsFromDb($id_usuario);
	if (empty($itens) && !empty($_SESSION['carrinho'])) {
		foreach (obterCarrinhoComDetalhes() as $item) {
			$itens[] = $item;
		}
	}
	if (empty($itens)) {
		return array('success' => false, 'message' => 'Carrinho vazio');
	}

	try {
		$conn->begin_transaction();
		$codigo_pedido = gerarCodigoPedido();
		$total_produtos = 0.0;
		foreach ($itens as $it) { $total_produtos += (float)$it['subtotal']; }
		$freteBase = 20.00;
		$isFreeShipping = isset($_SESSION['cupom_aplicado']['frete_gratis']) && $_SESSION['cupom_aplicado']['frete_gratis'] === true;
		$frete = $isFreeShipping ? 0.0 : $freteBase;
		$desconto_cupom = isset($_SESSION['cupom_aplicado']) ? (float)$_SESSION['cupom_aplicado']['desconto'] : 0.0;
		$total_final = $total_produtos + $frete - $desconto_cupom;

		$sql_pedido = "INSERT INTO pedidos_finalizados (id_usuario, codigo_pedido, status_pedido, total_produtos, frete, total_final) VALUES (?, ?, 'Pendente', ?, ?, ?)";
		$stmt_pedido = $conn->prepare($sql_pedido);
		$stmt_pedido->bind_param("isddd", $id_usuario, $codigo_pedido, $total_produtos, $frete, $total_final);
		$stmt_pedido->execute();
		$id_pedido = $conn->insert_id;
		$stmt_pedido->close();

		$item_counter = 1;
		foreach ($itens as $it) {
			$produto_id = (int)$it['id_produto'];
			$quantidade_item = (int)$it['quantidade_carrinho'];

			// Bloqueio e validação de estoque
			$sql_produto = "SELECT qtd_estoque, preco FROM produtos WHERE id_produto = ? FOR UPDATE";
			$stmt_produto = $conn->prepare($sql_produto);
			$stmt_produto->bind_param("i", $produto_id);
			$stmt_produto->execute();
			$res_p = $stmt_produto->get_result();
			if ($res_p->num_rows === 0) {
				$stmt_produto->close();
				throw new Exception('Produto não encontrado durante a finalização.');
			}
			$pRow = $res_p->fetch_assoc();
			$stmt_produto->close();
			$estoque_disponivel = (int)$pRow['qtd_estoque'];
			if ($quantidade_item > $estoque_disponivel) {
				throw new Exception('Estoque insuficiente para um dos itens do carrinho. Disponível: ' . $estoque_disponivel);
			}
			$valor_total_item = (float)$pRow['preco'] * $quantidade_item;
			$codigo_item = $codigo_pedido . '-' . $item_counter;

			$sql_item = "INSERT INTO pedidos (cod_pedido, id_cliente, id_produto, valor_total, status_pedido) VALUES (?, ?, ?, ?, 'Separação do pedido')";
			$stmt_item = $conn->prepare($sql_item);
			$stmt_item->bind_param("siid", $codigo_item, $id_usuario, $produto_id, $valor_total_item);
			$stmt_item->execute();
			$stmt_item->close();

			$sql_update_estoque = "UPDATE produtos SET qtd_estoque = qtd_estoque - ? WHERE id_produto = ?";
			$stmt_update = $conn->prepare($sql_update_estoque);
			$stmt_update->bind_param("ii", $quantidade_item, $produto_id);
			$stmt_update->execute();
			$stmt_update->close();

			$item_counter++;
		}

		if (isset($_SESSION['cupom_aplicado']) && !empty($_SESSION['cupom_aplicado']['codigo'])) {
			$codigo_cupom_aplicado = $_SESSION['cupom_aplicado']['codigo'];
			$sql_update_cupom = "UPDATE cupons SET usos_atuais = usos_atuais + 1 WHERE codigo = ? AND status = 'Ativo' AND (uso_maximo IS NULL OR usos_atuais < uso_maximo)";
			$stmt_cupom = $conn->prepare($sql_update_cupom);
			$stmt_cupom->bind_param("s", $codigo_cupom_aplicado);
			$stmt_cupom->execute();
			if ($stmt_cupom->affected_rows === 0) {
				$stmt_cupom->close();
				throw new Exception('Cupom inválido ou limite de uso atingido durante a finalização.');
			}
			$stmt_cupom->close();
		}

		$conn->commit();

		// Limpar carrinho (DB e sessão)
		limparCarrinho();
		unset($_SESSION['cupom_aplicado']);

		return array(
			'success' => true,
			'message' => 'Pedido finalizado com sucesso!',
			'codigo_pedido' => $codigo_pedido,
			'id_pedido' => $id_pedido
		);
	} catch (Exception $e) {
		$conn->rollback();
		error_log("Erro ao finalizar compra: " . $e->getMessage());
		return array('success' => false, 'message' => 'Erro ao finalizar compra: ' . $e->getMessage());
	}
}
?>
