<?php require_once __DIR__ . '/session.php'; ?>
<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cupons.php?erro=metodo_invalido");
    exit();
}

require_once 'connection.php';

$id_cupom = isset($_POST['id_cupom']) ? (int)$_POST['id_cupom'] : 0;
$codigo = isset($_POST['codigo']) ? strtoupper(trim($_POST['codigo'])) : '';
$tipo = isset($_POST['tipo_desconto']) ? trim($_POST['tipo_desconto']) : '';
$valor_enviado = isset($_POST['valor_desconto']) ? $_POST['valor_desconto'] : '0.00';
$data_expiracao = isset($_POST['data_expiracao']) ? $_POST['data_expiracao'] : NULL;
$limite_usos = isset($_POST['limite_usos']) ? (int)$_POST['limite_usos'] : 0;

$valor = (float)$valor_enviado;

$uso_maximo_bd = ($limite_usos > 0) ? $limite_usos : NULL;

if (!in_array($tipo, ['percentual', 'fixo', 'frete'])) {
    $tipo = 'fixo';
}

if ($tipo === 'frete') {
    $valor = 0.00;
    $tipo_para_bd = 'fixo';
} else {
    $tipo_para_bd = $tipo;
}



if ($id_cupom > 0 && !empty($codigo)) {

    $sql = "UPDATE cupons 
            SET codigo = ?, tipo = ?, valor = ?, data_expiracao = ?, uso_maximo = ? 
            WHERE id_cupom = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param(
            "ssdsii",
            $codigo,
            $tipo_para_bd,
            $valor,
            $data_expiracao,
            $uso_maximo_bd,
            $id_cupom
        );

        if ($stmt->execute()) {

            header("Location: cupons.php?sucesso=edicao&id=" . $id_cupom);
            exit();
        } else {

            $erro = "Erro ao atualizar cupom: " . $stmt->error;
            header("Location: cupons.php?erro=" . urlencode($erro));
            exit();
        }

        $stmt->close();
    } else {

        $erro = "Erro ao preparar a consulta de UPDATE: " . $conn->error;
        header("Location: cupons.php?erro=" . urlencode($erro));
        exit();
    }
} else {

    header("Location: cupons.php?erro=dados_faltando");
    exit();
}


$conn->close();
?>