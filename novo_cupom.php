<?php require_once __DIR__ . '/session.php'; ?>
<?php
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Criar Novo Cupom</title>

    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-submit,
        .btn-cancel {
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease;
        }


        .btn-submit {
            background-color: #28a745;
        }

        .btn-submit:hover {
            background-color: #1e7e34;
        }

        .btn-cancel {
            background-color: #4CAF50;
        }

        .btn-cancel:hover {
            background-color: #45a049;
        }
    </style>
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
                <a href="produtos.php" class="nav-item"><i class="fas fa-box-open"></i> Produtos</a>
                <a href="vendas.php" class="nav-item"><i class="fas fa-chart-line"></i> Vendas</a>
                <a href="usuarios.php" class="nav-item"><i class="fas fa-users"></i> Usuários</a>
                <a href="suporteAdmin.php" class="nav-item"><i class="fas fa-headset"></i> Suporte</a>

                <a href="cupons.php" class="nav-item active"><i class="fas fa-tags"></i> Cupons</a>
            </nav>

            <div class="logout-section">
                <a href="index.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Criar Novo Cupom de Desconto</h1>

            <div class="form-container">
                <form action="cupons.php" method="POST" id="form-novo-cupom">

                    <div class="form-group">
                        <label for="codigo">Código do Cupom</label>
                        <input type="text" id="codigo" name="codigo" placeholder="Ex: DESCONTO20" required maxlength="20" style="text-transform: uppercase;">
                    </div>

                    <div class="form-group">
                        <label for="tipo_desconto">Tipo de Desconto</label>
                        <select id="tipo_desconto" name="tipo_desconto" required>
                            <option value="percentual">Percentual (%)</option>
                            <option value="frete">Frete Grátis</option>
                        </select>
                    </div>

                    <div class="flex-row">
                        <div class="form-group">
                            <label for="valor_desconto_visual">Valor do Desconto</label>
                            <div class="input-group">
                                <span id="valor_prefix">R$</span>
                                <input type="text" id="valor_desconto_visual" placeholder="Ex: 50,00" value="">
                                <input type="hidden" id="valor_desconto" name="valor_desconto">
                                <span id="valor_suffix">%</span>
                            </div>
                            <small id="valor_help_text" style="color: #777;">(Valor do desconto em Reais)</small>
                        </div>

                        <div class="form-group">
                            <label for="minimo_visual">Valor Mínimo do Pedido (R$)</label>
                            <div class="input-group">
                                <span class="input-prefix">R$</span>
                                <input type="text" id="minimo_visual" placeholder="Ex: 100,00" value="0,00">
                                <input type="hidden" id="minimo" name="minimo" value="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="flex-row">
                        <div class="form-group">
                            <label for="data_expiracao">Data de Expiração</label>
                            <input type="date" id="data_expiracao" name="data_expiracao" required>
                        </div>

                        <div class="form-group">
                            <label for="limite_usos">Limite de Usos (0 para ilimitado)</label>
                            <input type="number" id="limite_usos" name="limite_usos" value="0" min="0">
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="cupons.php" class="btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Salvar Cupom
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-novo-cupom');
            const tipoDesconto = document.getElementById('tipo_desconto');

            const valorDescontoVisual = document.getElementById('valor_desconto_visual');
            const valorDescontoReal = document.getElementById('valor_desconto');
            const valorPrefix = document.getElementById('valor_prefix');
            const valorSuffix = document.getElementById('valor_suffix');
            const valorHelpText = document.getElementById('valor_help_text');

            const minimoVisual = document.getElementById('minimo_visual');
            const minimoReal = document.getElementById('minimo');


            function formatarMoeda(inputElement, realElement) {
                let v = inputElement.value.replace(/\D/g, '');
                if (v.length > 0) {
                    // Preenche com zeros à esquerda se for menos de 3 dígitos
                    while (v.length < 3) {
                        v = '0' + v;
                    }

                    // Separa centavos (últimos 2 dígitos)
                    let inteiros = v.slice(0, -2);
                    let decimais = v.slice(-2);

                    // Remove zeros à esquerda (exceto se for só zeros)
                    inteiros = inteiros.replace(/^0+/, '') || '0';

                    // Adiciona separador de milhar (ponto)
                    inteiros = inteiros.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                    // Constrói a string formatada para visualização
                    let formattedValue = inteiros + ',' + decimais;
                    inputElement.value = formattedValue;

                    let valorLimpo = v.slice(0, -2) + '.' + decimais;

                    valorLimpo = valorLimpo.replace(/^0+/, '0');
                    realElement.value = valorLimpo;

                } else {
                    inputElement.value = '';
                    realElement.value = '';
                }
            }

            function updateTipoDesconto() {
                const tipo = tipoDesconto.value;

                valorDescontoVisual.disabled = false;
                valorDescontoVisual.required = false;
                valorDescontoVisual.style.display = 'block';

                const prefixSpan = document.getElementById('valor_prefix');
                const suffixSpan = document.getElementById('valor_suffix');
                prefixSpan.style.display = 'none';
                suffixSpan.style.display = 'none';

                valorDescontoVisual.placeholder = '';

                if (tipo === 'percentual') {
                    valorDescontoVisual.placeholder = 'Ex: 20';
                    suffixSpan.style.display = 'inline';
                    valorHelpText.textContent = '(Valor do desconto em percentual, máximo 100)';
                    valorDescontoVisual.required = true;


                    let valorAtual = valorDescontoReal.value;
                    if (valorAtual) {
                        valorDescontoVisual.value = valorAtual.replace('.', ',');
                    }

                } else if (tipo === 'frete') {
                    valorDescontoVisual.value = '0,00';
                    valorDescontoReal.value = '0.00';
                    valorDescontoVisual.disabled = true;
                    valorDescontoVisual.required = false;
                    prefixSpan.style.display = 'inline';
                    valorHelpText.textContent = '(Frete Grátis - valor fixo R$ 0,00)';
                }
            }


            valorDescontoVisual.addEventListener('input', function() {
                if (tipoDesconto.value === 'fixo') {
                    formatarMoeda(this, valorDescontoReal);
                } else if (tipoDesconto.value === 'percentual') {

                    let v = this.value.replace(/[^\d.,]/g, '');
                    v = v.replace(',', '.');

                    let num = parseFloat(v);
                    if (num > 100) {
                        v = '100';
                    }

                    this.value = v.replace('.', ',');
                    valorDescontoReal.value = v.replace(',', '.');
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
                if (tipoDesconto.value === 'percentual') {
                    let valor = parseFloat(valorDescontoReal.value);
                    if (valor > 100 || valor <= 0) {
                        e.preventDefault();
                        alert('O desconto percentual deve ser entre 0.01 e 100');
                        return false;
                    }
                }



                console.log('Enviando valores:', {
                    tipo: tipoDesconto.value,
                    valorVisual: valorDescontoVisual.value,
                    valorReal: valorDescontoReal.value,
                    minimoVisual: minimoVisual.value,
                    minimoReal: minimoReal.value
                });
            });

            updateTipoDesconto();

            if (minimoVisual.value) {
                formatarMoeda(minimoVisual, minimoReal);
            }
        });
    </script>
</body>

</html>