<?php require_once __DIR__ . '/session.php'; ?>
<?php

require_once 'connection.php';

$cupom_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT codigo, tipo, valor, data_expiracao, uso_maximo FROM cupons WHERE id_cupom = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $cupom_id);


    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dados_cupom = $result->fetch_assoc();

        $codigo_atual = $dados_cupom['codigo'];
        $tipo_atual = $dados_cupom['tipo'];
        $valor_atual = $dados_cupom['valor'];

        $minimo_atual = "0.00";

        $data_atual = $dados_cupom['data_expiracao'];
        $limite_atual = $dados_cupom['uso_maximo'] !== null ? $dados_cupom['uso_maximo'] : 0;
    } else {
        $codigo_atual = "CUPOM_NAO_ENCONTRADO";
        $tipo_atual = "fixo";
        $valor_atual = "0.00";
        $minimo_atual = "0.00";
        $data_atual = date('Y-m-d', strtotime('+1 year'));
        $limite_atual = 0;
        echo "<script>alert('Aviso: Cupom com ID $cupom_id não encontrado no banco de dados. Exibindo valores padrão.');</script>";
    }

    $stmt->close();
} else {
    die("Erro ao preparar a consulta SQL: " . $conn->error);
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Editar Cupom ID: <?php echo $cupom_id; ?></title>

    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="admin-layout">

        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-tags"></i>
                <h2>Painel Admin</h2>
            </div>

            <nav class="sidebar-nav">
                <a href="admin.php" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
                <a href="cupons.php" class="nav-item active"><i class="fas fa-tags"></i> Cupons</a>
            </nav>

            <div class="logout-section">
                <a href="index.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Editar Cupom de Desconto (ID: <?php echo $cupom_id; ?>)</h1>

            <div class="form-container">
                <form action="processar_edicao_cupom.php" method="POST" id="form-editar-cupom">

                    <input type="hidden" name="id_cupom" value="<?php echo $cupom_id; ?>">

                    <div class="form-group">
                        <label for="codigo">Código do Cupom</label>
                        <input type="text" id="codigo" name="codigo" value="<?php echo $codigo_atual; ?>" required maxlength="20" style="text-transform: uppercase;">
                    </div>

                    <div class="form-group">
                        <label for="tipo_desconto">Tipo de Desconto</label>
                        <select id="tipo_desconto" name="tipo_desconto" required>
                            <option value="percentual" <?php echo ($tipo_atual == 'percentual' ? 'selected' : ''); ?>>Percentual (%)</option>
                            <option value="frete" <?php echo ($tipo_atual == 'frete' ? 'selected' : ''); ?>>Frete Grátis</option>
                        </select>
                    </div>

                    <div class="flex-row">
                        <div class="form-group">
                            <label for="valor_desconto_visual">Valor do Desconto</label>
                            <div class="input-group">
                                <span id="valor_prefix">R$</span>
                                <input type="text" id="valor_desconto_visual" placeholder="Ex: 50,00" value="">
                                <input type="hidden" id="valor_desconto" name="valor_desconto" value="<?php echo $valor_atual; ?>">
                                <span id="valor_suffix">%</span>
                            </div>
                            <small id="valor_help_text" style="color: #777;">(Valor do desconto)</small>
                        </div>

                        <div class="form-group">
                            <label for="minimo_visual">Valor Mínimo do Pedido (R$)</label>
                            <div class="input-group">
                                <span class="input-prefix">R$</span>
                                <input type="text" id="minimo_visual" placeholder="Ex: 100,00" value="">
                                <input type="hidden" id="minimo" name="minimo" value="<?php echo $minimo_atual; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="flex-row">
                        <div class="form-group">
                            <label for="data_expiracao">Data de Expiração</label>
                            <input type="date" id="data_expiracao" name="data_expiracao" value="<?php echo $data_atual; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="limite_usos">Limite de Usos (0 para ilimitado)</label>
                            <input type="number" id="limite_usos" name="limite_usos" value="<?php echo $limite_atual; ?>" min="0">
                        </div>
                    </div>


                    <div class="form-actions">
                        <a href="cupons.php" class="btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-editar-cupom');
            const tipoDesconto = document.getElementById('tipo_desconto');

            const valorDescontoVisual = document.getElementById('valor_desconto_visual');
            const valorDescontoReal = document.getElementById('valor_desconto');
            const valorPrefix = document.getElementById('valor_prefix');
            const valorSuffix = document.getElementById('valor_suffix');
            const valorHelpText = document.getElementById('valor_help_text');

            const minimoVisual = document.getElementById('minimo_visual');
            const minimoReal = document.getElementById('minimo');

            function formatarMoeda(inputElement, realElement) {
                let v = inputElement.value || realElement.value;
                v = v.replace(/\D/g, '');

                if (v.length > 0) {
                    while (v.length < 3) {
                        v = '0' + v;
                    }

                    let inteiros = v.slice(0, -2);
                    let decimais = v.slice(-2);

                    inteiros = inteiros.replace(/^0+(?=\d)/, "");
                    inteiros = inteiros.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                    let formattedValue = inteiros + ',' + decimais;

                    inputElement.value = formattedValue;
                    realElement.value = inteiros.replace(/\./g, '') + '.' + decimais;

                } else {
                    inputElement.value = '';
                    realElement.value = '';
                }
            }

            function updateTipoDesconto() {
                const tipo = tipoDesconto.value;

                valorDescontoVisual.disabled = false;
                valorDescontoVisual.required = true;
                valorPrefix.style.display = 'none';
                valorSuffix.style.display = 'none';
                valorHelpText.style.color = '#777';
                valorDescontoVisual.placeholder = '';

                let valor = valorDescontoReal.value;

                if (tipo === 'percentual') {
                    valorDescontoVisual.placeholder = 'Ex: 20';
                    valorSuffix.style.display = 'inline';
                    valorHelpText.textContent = '(Valor do desconto em percentual, máximo 100)';

                    valorDescontoVisual.value = valor.replace(/\./g, ',');

                } else if (tipo === 'fixo') {
                    valorDescontoVisual.placeholder = 'Ex: 50,00';
                    valorPrefix.style.display = 'inline';
                    valorHelpText.textContent = '(Valor do desconto em Reais - Formato: 1.234,56)';

                    valorDescontoVisual.value = '';
                    formatarMoeda(valorDescontoVisual, valorDescontoReal);

                } else if (tipo === 'frete') {
                    valorDescontoVisual.value = '20,00';
                    valorDescontoReal.value = '20.00';
                    valorDescontoVisual.disabled = true;
                    valorDescontoVisual.required = false;
                    valorPrefix.style.display = 'inline';
                    valorHelpText.textContent = '(Desconto fixo de R$ 20,00, interpretado como Frete Grátis)';
                    valorHelpText.style.color = 'gray';
                }
            }

            minimoVisual.value = '';
            formatarMoeda(minimoVisual, minimoReal);

            valorDescontoVisual.addEventListener('input', function() {
                if (tipoDesconto.value === 'fixo' || tipoDesconto.value === 'frete') {
                    formatarMoeda(this, valorDescontoReal);
                } else if (tipoDesconto.value === 'percentual') {
                    let v = this.value.replace(/[^\d]/g, '');
                    this.value = v;
                    valorDescontoReal.value = v;
                }
            });

            minimoVisual.addEventListener('input', function() {
                formatarMoeda(this, minimoReal);
            });

            tipoDesconto.addEventListener('change', updateTipoDesconto);


            document.getElementById('codigo').addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });

            form.addEventListener('submit', function(e) {
                if (tipoDesconto.value === 'frete') {
                    valorDescontoVisual.disabled = true;
                }
            });

            updateTipoDesconto();
        });
    </script>
</body>

</html>