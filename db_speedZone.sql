CREATE DATABASE IF NOT EXISTS speedZone;
USE speedZone;

CREATE TABLE IF NOT EXISTS usuario(
id_usuario INT PRIMARY KEY AUTO_INCREMENT,
primeiro_nome VARCHAR(50),
ultimo_nome VARCHAR(50),
email VARCHAR(150) UNIQUE,
cpf VARCHAR(14) UNIQUE,
cargo ENUM('Funcionario', 'Administrador', 'Cliente'),
status_conta ENUM('Ativo','Bloqueado','Desativado'),
cep VARCHAR(9),
rua VARCHAR(150),
numero INT,
complemento VARCHAR(100),
bairro VARCHAR(100),
cidade VARCHAR(150),
estado VARCHAR(3),
senha VARCHAR(255),
CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS suporte(
id_chamado INT PRIMARY KEY AUTO_INCREMENT,
id_cliente INT,
nome_cliente VARCHAR(150),
email VARCHAR(150),
tipo ENUM('Duvida sobre um pedido','Informações sobre produto','Problema com Pagamento','Problemas Tecnicos/Erros no site', 'Outros Assuntos'),
status_pedido ENUM('Ativo','Resolvido'),
descricao VARCHAR(255),
CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SELECT * FROM suporte;

CREATE TABLE IF NOT EXISTS pedidos(
id_pedido INT PRIMARY KEY AUTO_INCREMENT,
cod_pedido VARCHAR(20) UNIQUE,
id_cliente INT,
id_produto INT,
valor_total DECIMAL(10,2), 
status_pedido ENUM('Entregue','Em Processo de entrega','Cancelado')	,
CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS produtos(
id_produto INT PRIMARY KEY AUTO_INCREMENT,
cod_produto VARCHAR(10),
nome_produto VARCHAR(150),
descricao_produto VARCHAR(255),
preco DECIMAL(10,2),
qtd_estoque INT,
categoria ENUM(
    'pistoes',
    'bielas',
    'virabrequins',
    'comando_valvulas',
    'tuchos_balancins',
    'juntas_motor',
    'kit_stroker',
    'kit_turbo',
    'turbina',
    'supercharger',
    'intercooler',
    'wastegate',
    'blowoff_valve',
    'tubulacoes_turbo',
    'filtro_ar_esportivo',
    'kit_admissao_direta',
    'corpo_borboleta',
    'coletor_admissao',
    'bomba_combustivel',
    'bico_injetor',
    'regulador_pressao_combustivel',
    'fuel_rail',
    'coletor_escape',
    'downpipe',
    'midpipe',
    'catback',
    'valvula_escapamento',
    'silencioso_esportivo',
    'ecu_programavel',
    'reprogramacao_ecu',
    'piggyback',
    'boost_controller',
    'sensores',
    'wideband',
    'data_logger',
    'embreagem_esportiva',
    'volante_motor_aliviado',
    'short_shifter',
    'diferencial_lsd',
    'kit_cambio',
    'coilover',
    'kit_suspensao_ar',
    'molas_esportivas',
    'barra_estabilizadora',
    'camber_kit',
    'bracos_regulaveis',
    'buchas_poliuretano',
    'kit_big_brake',
    'disco_freio',
    'pastilha_esportiva',
    'mangueira_malha_aco',
    'fluido_freio',
    'radiador',
    'ventoinha',
    'mangueira_silicone',
    'termostato_esportivo',
    'radiador_oleo',
    'reservatorio_expansao',
    'aerofolio',
    'splitter_dianteiro',
    'difusor_traseiro',
    'saias_laterais',
    'capo_fibra',
    'grade_frontal',
    'scoop_capo',
    'roda_esportiva',
    'roda_beadlock',
    'pneu_semi_slick',
    'pneu_slick',
    'espacador_roda',
    'porca_esportiva',
    'farol_led',
    'lanterna_traseira',
    'drl',
    'barra_led',
    'kit_xenon',
    'banco_concha',
    'volante_esportivo',
    'pedaleira',
    'tapete_personalizado',
    'manopla_cambio',
    'painel_digital',
    'manometro',
    'santo_antonio',
    'cinto_4pontos',
    'extintor_incendio',
    'chave_geral',
    'torquimetro',
    'scanner_obd2',
    'ferramenta_montagem',
    'medidor_compressao',
    'kit_stage1',
    'kit_stage2',
    'kit_stage3',
    'kit_swap_motor',
    'kit_track_day',
    'kit_drift',
    'wrap_automotivo',
    'pelicula',
    'adesivos',
    'placa_expositora',
    'capa_automotiva'
  ) NOT NULL,
  CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SELECT * FROM pedidos;
SELECT * FROM usuario;

CREATE TABLE IF NOT EXISTS cupons (
    id_cupom INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    tipo ENUM('percentual', 'fixo') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_expiracao DATE,
    uso_maximo INT DEFAULT NULL,
    usos_atuais INT DEFAULT 0,
    status ENUM('Ativo', 'Inativo') DEFAULT 'Ativo',
    CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela para carrinhos de compras
CREATE TABLE IF NOT EXISTS carrinho (
    id_carrinho INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT,
    status ENUM('Ativo', 'Finalizado', 'Abandonado') DEFAULT 'Ativo',
    total DECIMAL(10, 2) DEFAULT 0.00,
    frete DECIMAL(10, 2) DEFAULT 0.00,
    total_final DECIMAL(10, 2) DEFAULT 0.00,
    CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS carrinho_itens (
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
);

CREATE TABLE IF NOT EXISTS pedidos_finalizados (
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
);

SELECT * FROM pedidos_finalizados;

-- Reinserindo os iniciais (1-5) e adicionando mais 30 clientes (6-35)
INSERT INTO usuario (primeiro_nome, ultimo_nome, email, cpf, cargo, status_conta, cep, rua, numero, complemento, bairro, cidade, estado, senha) VALUES
('Ana', 'Souza', 'ana.admin@speedzone.com', '111.111.111-11', 'Administrador', 'Ativo', '80000-000', 'Rua Principal', 100, 'Sala 1', 'Centro', 'Curitiba', 'PR', 'hash_admin1'),
('Bruno', 'Lima', 'bruno.func@speedzone.com', '222.222.222-22', 'Funcionario', 'Ativo', '13000-000', 'Av. Secundária', 250, NULL, 'Cambuí', 'Campinas', 'SP', 'hash_func1'),
('Carlos', 'Ferreira', 'carlos.c@email.com', '333.333.333-33', 'Cliente', 'Ativo', '90000-000', 'Rua das Flores', 30, 'Ap. 101', 'Moinhos de Vento', 'Porto Alegre', 'RS', 'hash_cliente1'),
('Denise', 'Gomes', 'denise.c@email.com', '444.444.444-44', 'Cliente', 'Bloqueado', '70000-000', 'Setor Leste', 55, NULL, 'Asa Sul', 'Brasília', 'DF', 'hash_cliente2'),
('Eduardo', 'Alves', 'eduardo.c@email.com', '555.555.555-55', 'Cliente', 'Ativo', '20000-000', 'Rua da Alfândega', 120, 'Loja 5', 'Centro', 'Rio de Janeiro', 'RJ', 'hash_cliente3'),
('Fernanda', 'Rocha', 'fernanda.c@email.com', '666.666.666-66', 'Cliente', 'Ativo', '01000-000', 'Av. Paulista', 1500, 'Conj. 10', 'Bela Vista', 'São Paulo', 'SP', 'hash_cliente4'),
('Gustavo', 'Martins', 'gustavo.c@email.com', '777.777.777-77', 'Cliente', 'Ativo', '40000-000', 'Rua Bahia', 300, NULL, 'Comércio', 'Salvador', 'BA', 'hash_cliente5'),
('Helena', 'Pereira', 'helena.c@email.com', '888.888.888-88', 'Cliente', 'Desativado', '30000-000', 'Praça da Liberdade', 45, NULL, 'Funcionários', 'Belo Horizonte', 'MG', 'hash_cliente6'),
('Igor', 'Santos', 'igor.c@email.com', '999.999.999-99', 'Cliente', 'Ativo', '50000-000', 'Av. Boa Viagem', 10, 'Apt. 202', 'Boa Viagem', 'Recife', 'PE', 'hash_cliente7'),
('Julia', 'Oliveira', 'julia.c@email.com', '012.012.012-12', 'Cliente', 'Ativo', '60000-000', 'Av. Beira Mar', 700, NULL, 'Meireles', 'Fortaleza', 'CE', 'hash_cliente8'),
('Lucas', 'Ribeiro', 'lucas.c@email.com', '023.023.023-23', 'Cliente', 'Ativo', '74000-000', 'Rua 10', 88, NULL, 'Centro', 'Goiânia', 'GO', 'hash_cliente9'),
('Mariana', 'Costa', 'mariana.c@email.com', '034.034.034-34', 'Cliente', 'Ativo', '88000-000', 'Av. Atlântica', 220, 'Casa 1', 'Centro', 'Florianópolis', 'SC', 'hash_cliente10'),
('Nelson', 'Almeida', 'nelson.c@email.com', '045.045.045-45', 'Cliente', 'Bloqueado', '69000-000', 'Rua Amazonas', 500, NULL, 'Centro', 'Manaus', 'AM', 'hash_cliente11'),
('Otávio', 'Guedes', 'otavio.c@email.com', '056.056.056-56', 'Cliente', 'Ativo', '49000-000', 'Av. Santos Dumont', 150, NULL, 'Atalaia', 'Aracaju', 'SE', 'hash_cliente12'),
('Priscila', 'Nunes', 'priscila.c@email.com', '067.067.067-67', 'Cliente', 'Ativo', '57000-000', 'Av. Maceió', 320, NULL, 'Pajuçara', 'Maceió', 'AL', 'hash_cliente13'),
('Quentin', 'Viana', 'quentin.c@email.com', '078.078.078-78', 'Cliente', 'Ativo', '65000-000', 'Rua Maranhão', 120, 'Apt. 50', 'Centro', 'São Luís', 'MA', 'hash_cliente14'),
('Rafael', 'Xavier', 'rafael.c@email.com', '089.089.089-89', 'Cliente', 'Ativo', '68900-000', 'Av. Rio Branco', 90, NULL, 'Central', 'Macapá', 'AP', 'hash_cliente15'),
('Sofia', 'Zanella', 'sofia.c@email.com', '090.090.090-90', 'Cliente', 'Ativo', '76800-000', 'Av. Sete de Setembro', 210, NULL, 'Centro', 'Porto Velho', 'RO', 'hash_cliente16'),
('Thiago', 'Abreu', 'thiago.c@email.com', '101.101.101-01', 'Cliente', 'Ativo', '77000-000', 'Quadra 104 Sul', 10, NULL, 'Plano Diretor Sul', 'Palmas', 'TO', 'hash_cliente17'),
('Ursula', 'Bastos', 'ursula.c@email.com', '112.112.112-12', 'Cliente', 'Ativo', '66000-000', 'Travessa Piedade', 55, NULL, 'Umarizal', 'Belém', 'PA', 'hash_cliente18'),
('Vinicius', 'Castro', 'vinicius.c@email.com', '123.123.123-23', 'Cliente', 'Ativo', '58000-000', 'Av. Epitácio Pessoa', 400, NULL, 'Tambaú', 'João Pessoa', 'PB', 'hash_cliente19'),
('Wanda', 'Dantas', 'wanda.c@email.com', '134.134.134-34', 'Cliente', 'Ativo', '80000-000', 'Rua Comendador', 110, NULL, 'Centro', 'Curitiba', 'PR', 'hash_cliente20'),
('Xavier', 'Elias', 'xavier.c@email.com', '145.145.145-45', 'Cliente', 'Ativo', '01000-000', 'Rua Boa Vista', 900, NULL, 'Centro', 'São Paulo', 'SP', 'hash_cliente21'),
('Yara', 'Fogaça', 'yara.c@email.com', '156.156.156-56', 'Cliente', 'Ativo', '13000-000', 'Rua Conceição', 85, NULL, 'Centro', 'Campinas', 'SP', 'hash_cliente22'),
('Zeca', 'Gama', 'zeca.c@email.com', '167.167.167-67', 'Cliente', 'Ativo', '90000-000', 'Av. Goethe', 140, NULL, 'Moinhos de Vento', 'Porto Alegre', 'RS', 'hash_cliente23'),
('Alexandre', 'Horta', 'alexandre.c@email.com', '178.178.178-78', 'Cliente', 'Ativo', '70000-000', 'Setor Hoteleiro', 15, NULL, 'Asa Norte', 'Brasília', 'DF', 'hash_cliente24'),
('Bianca', 'Inacio', 'bianca.c@email.com', '189.189.189-89', 'Cliente', 'Ativo', '20000-000', 'Rua do Ouvidor', 10, NULL, 'Centro', 'Rio de Janeiro', 'RJ', 'hash_cliente25'),
('Cesar', 'Junqueira', 'cesar.c@email.com', '190.190.190-90', 'Cliente', 'Ativo', '40000-000', 'Av. Oceânica', 1000, NULL, 'Barra', 'Salvador', 'BA', 'hash_cliente26'),
('Diana', 'Klima', 'diana.c@email.com', '201.201.201-01', 'Cliente', 'Ativo', '30000-000', 'Rua Rio Grande do Sul', 20, NULL, 'Savassi', 'Belo Horizonte', 'MG', 'hash_cliente27'),
('Elias', 'Lemos', 'elias.c@email.com', '212.212.212-12', 'Cliente', 'Ativo', '50000-000', 'Rua dos Navegantes', 50, NULL, 'Pina', 'Recife', 'PE', 'hash_cliente28'),
('Felipe', 'Motta', 'felipe.c@email.com', '223.223.223-23', 'Cliente', 'Ativo', '60000-000', 'Av. Senador Virgílio Távora', 300, NULL, 'Aldeota', 'Fortaleza', 'CE', 'hash_cliente29'),
('Gisele', 'Novaes', 'gisele.c@email.com', '234.234.234-34', 'Cliente', 'Ativo', '74000-000', 'Rua T-55', 40, NULL, 'Setor Bueno', 'Goiânia', 'GO', 'hash_cliente30'),
('Hector', 'Pinheiro', 'hector.admin@email.com', '245.245.245-45', 'Administrador', 'Ativo', '88000-000', 'Rua Almirante Lamego', 10, NULL, 'Centro', 'Florianópolis', 'SC', 'hash_admin2'),
('Igor', 'Quintino', 'igor.func2@email.com', '256.256.256-56', 'Funcionario', 'Ativo', '69000-000', 'Av. Djalma Batista', 100, NULL, 'Nossa Senhora das Graças', 'Manaus', 'AM', 'hash_func2'),
('Jessica', 'Rosa', 'jessica.func3@email.com', '267.267.267-67', 'Funcionario', 'Ativo', '49000-000', 'Rua Laranjeiras', 200, NULL, 'Centro', 'Aracaju', 'SE', 'hash_func3');

INSERT INTO produtos (cod_produto, nome_produto, descricao_produto, preco, qtd_estoque, categoria) VALUES
('PSTN001', 'Pistão Forjado 86mm', 'Pistão Forjado de Alta Performance 86mm', 1250.00, 50, 'pistoes'),
('TRB002', 'Turbina GT30R', 'Turbina Garrett GT30R para até 600cv', 7500.00, 10, 'turbina'),
('BCIN003', 'Bico Injetor 80lb/h', 'Bico injetor de alta vazão 80 libras/hora', 450.00, 120, 'bico_injetor'),
('FRAR004', 'Filtro K&N Cônico', 'Filtro de Ar Esportivo K&N lavável', 180.00, 300, 'filtro_ar_esportivo'),
('COIL005', 'Kit Coilover Track', 'Kit de suspensão Coilover ajustável para Track Day', 3200.00, 15, 'coilover'),
('OLED006', 'Manômetro de Óleo Digital', 'Manômetro de pressão de óleo digital com sensor', 350.00, 80, 'manometro'),
('BLWS007', 'Blow-off GFB DV+', 'Válvula Blow-off GFB DV+ para turbos OEM', 890.00, 45, 'blowoff_valve'),
('EMBR008', 'Embreagem Cerâmica 6 Pastilhas', 'Embreagem de cerâmica com 6 pastilhas para 500cv', 1800.00, 20, 'embreagem_esportiva'),
('BCOM009', 'Bomba Combustível Walbro 450', 'Bomba de combustível in-tank Walbro 450 lph', 700.00, 75, 'bomba_combustivel'),
('JMOT010', 'Kit Juntas Motor AP', 'Kit completo de juntas para motor AP Turbo', 280.00, 150, 'juntas_motor'),
('KITC011', 'Kit Big Brake 4 Pistões', 'Kit de freios Big Brake com pinças de 4 pistões', 5500.00, 12, 'kit_big_brake'),
('EPRG012', 'ECU FuelTech FT550', 'Módulo de injeção programável FuelTech FT550', 8990.00, 5, 'ecu_programavel'),
('BILA013', 'Biela Forjada Scat', 'Bielas forjadas Scat para alta potência', 2900.00, 30, 'bielas'),
('AERO014', 'Aerofólio GT Carbono', 'Aerofólio estilo GT em fibra de carbono', 4200.00, 8, 'aerofolio'),
('PNEU015', 'Pneu Semi Slick R888', 'Pneu Toyo R888 semi slick', 1100.00, 100, 'pneu_semi_slick'),
('WSTG016', 'Wastegate Tial 44mm', 'Válvula Wastegate externa Tial MVS 44mm', 2100.00, 25, 'wastegate'),
('WDBD017', 'Wideband FuelTech Nano', 'Sensor de banda larga (wideband) FT Nano', 650.00, 90, 'wideband'),
('FRAC018', 'Fluido Freio Motul RBF600', 'Fluido de freio de competição Motul RBF600', 120.00, 200, 'fluido_freio'),
('RDOI019', 'Radiador Óleo Universal', 'Kit radiador de óleo universal 19 linhas', 880.00, 40, 'radiador_oleo'),
('CPB020', 'Corpo Borboleta 70mm', 'Corpo de borboleta em alumínio 70mm', 550.00, 60, 'corpo_borboleta'),
('KITSTK21', 'Kit Stroker 2.1 AP', 'Kit Stroker para motor AP 2.1 Litros', 7200.00, 7, 'kit_stroker'),
('SHST022', 'Short Shifter Engate Rápido', 'Alavanca de câmbio de engate rápido', 480.00, 55, 'short_shifter'),
('MANG023', 'Kit Mangueiras Silicone AP', 'Kit de mangueiras do radiador em silicone', 390.00, 95, 'mangueira_silicone'),
('VIBR024', 'Virabrequim Forjado', 'Virabrequim forjado 92.8mm', 6100.00, 18, 'virabrequins'),
('RDES025', 'Roda Enkei RPF1 17"', 'Roda Enkei RPF1 17x8 (unidade)', 1500.00, 60, 'roda_esportiva'),
('KITTS26', 'Kit Stage 2 Remap', 'Serviço de reprogramação ECU Stage 2', 2500.00, 0, 'reprogramacao_ecu'), -- Serviço
('COLA027', 'Coletor de Admissão Fluxo Cruzado', 'Coletor de Admissão de alto fluxo', 950.00, 22, 'coletor_admissao'),
('BARR028', 'Barra Estabilizadora Traseira', 'Barra estabilizadora traseira ajustável', 1150.00, 28, 'barra_estabilizadora'),
('SNTN029', 'Santo Antônio Aço Cromoly', 'Santo Antônio para pista em Aço Cromoly', 3800.00, 9, 'santo_antonio'),
('PEDL030', 'Pedaleira Esportiva Sparco', 'Conjunto de pedaleiras esportivas Sparco', 150.00, 180, 'pedaleira'),
('FRLD031', 'Farol Full LED Projector', 'Kit de faróis com projetor e Full LED', 990.00, 40, 'farol_led'),
('BPLS032', 'Buchas PU Bandeja Dianteira', 'Kit de buchas em poliuretano para suspensão', 290.00, 110, 'buchas_poliuretano'),
('DLGR033', 'Data Logger AIM Solo 2', 'Data Logger GPS para tempo de volta', 2300.00, 15, 'data_logger'),
('CINT034', 'Cinto 4 Pontos Sabelt', 'Cinto de segurança de 4 pontos Sabelt', 850.00, 30, 'cinto_4pontos'),
('RADT035', 'Radiador de Alumínio 3 Colmeias', 'Radiador de alta performance em alumínio', 1500.00, 20, 'radiador');

INSERT INTO cupons (codigo, tipo, valor, data_expiracao, uso_maximo, usos_atuais, status) VALUES
('PRIMEIRACOMPRA10', 'percentual', 10.00, '2026-12-31', 500, 50, 'Ativo'),
('FRETEZERO', 'fixo', 30.00, '2025-11-30', 100, 85, 'Ativo'),
('FIMDEESTOQUE', 'percentual', 25.00, '2024-10-01', NULL, 0, 'Inativo'),
('DESCONTO50', 'fixo', 50.00, '2025-12-31', 50, 10, 'Ativo'),
('NOVOUSER20', 'percentual', 20.00, '2026-06-30', 200, 150, 'Ativo'),
('TURBOS15', 'percentual', 15.00, '2025-08-15', 80, 5, 'Ativo'),
('FRETEGRATIS1000', 'fixo', 50.00, '2025-07-01', NULL, 300, 'Ativo'),
('SUSP10', 'percentual', 10.00, '2025-05-20', 100, 99, 'Ativo'), -- Quase esgotado
('FLASH30', 'percentual', 30.00, '2024-12-25', 10, 10, 'Inativo'), -- Esgotado
('FIXO100', 'fixo', 100.00, '2026-03-01', 20, 1, 'Ativo'),
('ESPECIAL40', 'percentual', 40.00, '2025-04-01', 30, 0, 'Ativo'),
('FERRAMENTA5', 'fixo', 5.00, '2026-01-01', 500, 250, 'Ativo'),
('FIMANO2024', 'percentual', 18.00, '2024-12-31', NULL, 0, 'Ativo'),
('CLIENTE1', 'fixo', 20.00, '2024-11-01', 1, 0, 'Ativo'), -- Uso único
('MOTOR20', 'percentual', 20.00, '2025-03-31', 60, 15, 'Ativo'),
('BODYKIT10', 'percentual', 10.00, '2025-09-01', 40, 5, 'Ativo'),
('LIGHTING15', 'percentual', 15.00, '2025-10-01', 70, 0, 'Ativo'),
('FIXO25', 'fixo', 25.00, '2026-02-01', 150, 45, 'Ativo'),
('BLACKFRIDAY50', 'percentual', 50.00, '2024-11-29', 1000, 0, 'Inativo'), -- Data no passado
('ESGOTADO', 'fixo', 10.00, '2026-12-31', 5, 5, 'Inativo'), -- Uso máximo atingido
('DESCONTO35', 'percentual', 35.00, '2025-07-20', 20, 0, 'Ativo'),
('FIXO75', 'fixo', 75.00, '2026-04-15', 10, 0, 'Ativo'),
('PNEU12', 'percentual', 12.00, '2025-06-01', 50, 0, 'Ativo'),
('INTERIOR10', 'percentual', 10.00, '2025-05-01', 120, 0, 'Ativo'),
('ECUFIXO', 'fixo', 150.00, '2025-10-31', 5, 0, 'Ativo'),
('ESPORTIVO5', 'percentual', 5.00, '2026-05-01', 1000, 50, 'Ativo'),
('FIXO10', 'fixo', 10.00, '2026-06-01', 200, 10, 'Ativo'),
('MAISDESCONTO', 'percentual', 17.50, '2025-03-01', 90, 0, 'Ativo'),
('EXPIRADO', 'fixo', 5.00, '2024-01-01', 100, 0, 'Inativo'), -- Data no passado
('SEMANAL20', 'percentual', 20.00, '2025-11-04', 50, 0, 'Ativo');

-- ID_CUPOM vai de 1 a 30

INSERT INTO carrinho (id_usuario, status, total, frete, total_final) VALUES
(3, 'Finalizado', 1700.00, 25.00, 1725.00), -- 1: Carlos (Finalizado)
(3, 'Ativo', 350.00, 0.00, 350.00), -- 2: Carlos (Ativo)
(5, 'Abandonado', 7500.00, 50.00, 7550.00), -- 3: Eduardo (Abandonado)
(6, 'Finalizado', 180.00, 15.00, 195.00), -- 4: Fernanda
(7, 'Finalizado', 3200.00, 40.00, 3240.00), -- 5: Gustavo
(8, 'Ativo', 1250.00, 20.00, 1270.00), -- 6: Helena
(9, 'Abandonado', 700.00, 0.00, 700.00), -- 7: Igor
(10, 'Finalizado', 1800.00, 30.00, 1830.00), -- 8: Julia
(11, 'Finalizado', 8990.00, 100.00, 9090.00), -- 9: Lucas
(12, 'Ativo', 5500.00, 55.00, 5555.00), -- 10: Mariana
(13, 'Abandonado', 4200.00, 60.00, 4260.00), -- 11: Nelson
(14, 'Finalizado', 1100.00, 15.00, 1115.00), -- 12: Otávio
(15, 'Finalizado', 2100.00, 25.00, 2125.00), -- 13: Priscila
(16, 'Ativo', 650.00, 10.00, 660.00), -- 14: Quentin
(17, 'Abandonado', 120.00, 5.00, 125.00), -- 15: Rafael
(18, 'Finalizado', 880.00, 20.00, 900.00), -- 16: Sofia
(19, 'Finalizado', 550.00, 15.00, 565.00), -- 17: Thiago
(20, 'Ativo', 7200.00, 80.00, 7280.00), -- 18: Ursula
(21, 'Abandonado', 480.00, 10.00, 490.00), -- 19: Vinicius
(22, 'Finalizado', 390.00, 15.00, 405.00), -- 20: Wanda
(23, 'Finalizado', 6100.00, 70.00, 6170.00), -- 21: Xavier
(24, 'Ativo', 1500.00, 25.00, 1525.00), -- 22: Yara
(25, 'Abandonado', 2500.00, 0.00, 2500.00), -- 23: Zeca
(26, 'Finalizado', 950.00, 15.00, 965.00), -- 24: Alexandre
(27, 'Finalizado', 1150.00, 20.00, 1170.00), -- 25: Bianca
(28, 'Ativo', 3800.00, 50.00, 3850.00), -- 26: Cesar
(29, 'Abandonado', 150.00, 8.00, 158.00), -- 27: Diana
(30, 'Finalizado', 990.00, 25.00, 1015.00), -- 28: Elias
(31, 'Finalizado', 290.00, 10.00, 300.00), -- 29: Felipe
(32, 'Ativo', 2300.00, 35.00, 2335.00), -- 30: Gisele
(3, 'Finalizado', 300.00, 15.00, 315.00), -- 31: Carlos (2º pedido finalizado)
(5, 'Finalizado', 890.00, 15.00, 905.00), -- 32: Eduardo (1º pedido finalizado)
(1, 'Finalizado', 1000.00, 25.00, 1025.00), -- 33: Ana (Admin - Compra)
(2, 'Ativo', 500.00, 10.00, 510.00), -- 34: Bruno (Func - Carrinho Ativo)
(35, 'Abandonado', 850.00, 15.00, 865.00); -- 35: Jessica (Abandonado)

-- ID_CARRINHO vai de 1 a 35

-- REQUER LIMPEZA PRÉVIA: DELETE FROM pedidos_finalizados;

INSERT INTO pedidos_finalizados (id_carrinho, id_usuario, codigo_pedido, status_pedido, total_produtos, frete, total_final, data_pedido) VALUES
-- Pedidos da primeira quinzena
(1, 3, 'SPDZ-0001', 'Entregue', 1700.00, 25.00, 1725.00, '2025-10-01 10:15:00'),       -- Carlos
(4, 6, 'SPDZ-0002', 'Em Preparação', 180.00, 15.00, 195.00, '2025-10-02 14:30:00'),    -- Fernanda
(5, 7, 'SPDZ-0003', 'Enviado', 3200.00, 40.00, 3240.00, '2025-10-03 09:00:00'),        -- Gustavo
(8, 10, 'SPDZ-0004', 'Entregue', 1800.00, 30.00, 1830.00, '2025-10-04 11:45:00'),      -- Julia
(9, 11, 'SPDZ-0005', 'Entregue', 8990.00, 100.00, 9090.00, '2025-10-05 16:20:00'),     -- Lucas
(12, 14, 'SPDZ-0006', 'Entregue', 1100.00, 15.00, 1115.00, '2025-10-06 08:35:00'),     -- Otávio
(13, 15, 'SPDZ-0007', 'Cancelado', 2100.00, 25.00, 2125.00, '2025-10-07 13:50:00'),    -- Priscila
(16, 18, 'SPDZ-0008', 'Confirmado', 880.00, 20.00, 900.00, '2025-10-08 17:05:00'),     -- Sofia
(17, 19, 'SPDZ-0009', 'Em Preparação', 550.00, 15.00, 565.00, '2025-10-09 10:25:00'),  -- Thiago
(20, 22, 'SPDZ-0010', 'Enviado', 390.00, 15.00, 405.00, '2025-10-10 15:10:00'),        -- Wanda
(21, 23, 'SPDZ-0011', 'Entregue', 6100.00, 70.00, 6170.00, '2025-10-11 09:55:00'),     -- Xavier
(24, 26, 'SPDZ-0012', 'Confirmado', 950.00, 15.00, 965.00, '2025-10-12 12:40:00'),     -- Alexandre
(25, 27, 'SPDZ-0013', 'Cancelado', 1150.00, 20.00, 1170.00, '2025-10-13 18:15:00'),    -- Bianca
(28, 30, 'SPDZ-0014', 'Em Preparação', 990.00, 25.00, 1015.00, '2025-10-14 11:00:00'), -- Elias
(29, 31, 'SPDZ-0015', 'Entregue', 290.00, 10.00, 300.00, '2025-10-15 13:25:00'),       -- Felipe

-- Pedidos da segunda quinzena
(31, 3, 'SPDZ-0016', 'Entregue', 300.00, 15.00, 315.00, '2025-10-16 10:05:00'),        -- Carlos (2º pedido)
(32, 5, 'SPDZ-0017', 'Enviado', 890.00, 15.00, 905.00, '2025-10-17 14:40:00'),         -- Eduardo
(33, 1, 'SPDZ-0018', 'Confirmado', 1000.00, 25.00, 1025.00, '2025-10-18 09:10:00'),    -- Ana (Admin)
(NULL, 10, 'SPDZ-0019', 'Entregue', 450.00, 10.00, 460.00, '2025-10-19 11:55:00'),
(NULL, 12, 'SPDZ-0020', 'Entregue', 5500.00, 60.00, 5560.00, '2025-10-20 16:30:00'),
(NULL, 16, 'SPDZ-0021', 'Em Preparação', 3200.00, 40.00, 3240.00, '2025-10-21 08:45:00'),
(NULL, 20, 'SPDZ-0022', 'Enviado', 7500.00, 85.00, 7585.00, '2025-10-22 13:00:00'),
(NULL, 24, 'SPDZ-0023', 'Confirmado', 180.00, 10.00, 190.00, '2025-10-23 17:15:00'),
(NULL, 28, 'SPDZ-0024', 'Cancelado', 1250.00, 20.00, 1270.00, '2025-10-24 10:35:00'),
(NULL, 32, 'SPDZ-0025', 'Entregue', 2500.00, 35.00, 2535.00, '2025-10-25 15:50:00'),
(NULL, 35, 'SPDZ-0026', 'Pendente', 890.00, 15.00, 905.00, '2025-10-26 09:20:00'),
(NULL, 3, 'SPDZ-0027', 'Entregue', 1100.00, 10.00, 1110.00, '2025-10-27 12:05:00'),
(NULL, 5, 'SPDZ-0028', 'Em Preparação', 390.00, 15.00, 405.00, '2025-10-27 15:15:00'), -- Dois pedidos no dia 27
(NULL, 7, 'SPDZ-0029', 'Confirmado', 5500.00, 70.00, 5570.00, '2025-10-28 09:40:00'), -- Hoje, manhã
(NULL, 9, 'SPDZ-0030', 'Enviado', 990.00, 25.00, 1015.00, '2025-10-28 12:20:00'); -- Hoje, almoço

-- ID_PEDIDO_FINALIZADO vai de 1 a 30

INSERT INTO pedidos (cod_pedido, id_cliente, id_produto, valor_total, status_pedido) VALUES
('PE-0001', 3, 1, 1250.00, 'Entregue'),
('PE-0002', 5, 2, 7500.00, 'Em Processo de entrega'),
('PE-0003', 6, 4, 180.00, 'Cancelado'),
('PE-0004', 7, 5, 3200.00, 'Entregue'),
('PE-0005', 10, 8, 1800.00, 'Em Processo de entrega'),
('PE-0006', 11, 12, 8990.00, 'Entregue'),
('PE-0007', 14, 15, 1100.00, 'Entregue'),
('PE-0008', 15, 16, 2100.00, 'Cancelado'),
('PE-0009', 18, 19, 880.00, 'Entregue'),
('PE-0010', 19, 20, 550.00, 'Em Processo de entrega'),
('PE-0011', 22, 23, 390.00, 'Entregue'),
('PE-0012', 23, 24, 6100.00, 'Em Processo de entrega'),
('PE-0013', 26, 27, 950.00, 'Entregue'),
('PE-0014', 27, 28, 1150.00, 'Cancelado'),
('PE-0015', 30, 31, 990.00, 'Em Processo de entrega'),
('PE-0016', 31, 32, 290.00, 'Entregue'),
('PE-0017', 3, 33, 2300.00, 'Entregue'),
('PE-0018', 5, 34, 850.00, 'Em Processo de entrega'),
('PE-0019', 6, 35, 1500.00, 'Entregue'),
('PE-0020', 7, 1, 1250.00, 'Entregue'),
('PE-0021', 10, 2, 7500.00, 'Cancelado'),
('PE-0022', 11, 3, 450.00, 'Em Processo de entrega'),
('PE-0023', 14, 4, 180.00, 'Entregue'),
('PE-0024', 15, 5, 3200.00, 'Entregue'),
('PE-0025', 18, 6, 350.00, 'Em Processo de entrega'),
('PE-0026', 19, 7, 890.00, 'Cancelado'),
('PE-0027', 22, 8, 1800.00, 'Entregue'),
('PE-0028', 23, 9, 700.00, 'Em Processo de entrega'),
('PE-0029', 26, 10, 280.00, 'Entregue'),
('PE-0030', 27, 11, 5500.00, 'Entregue');

INSERT INTO suporte (id_cliente, nome_cliente, email, tipo, status_pedido, descricao) VALUES
(3, 'Carlos Ferreira', 'carlos.c@email.com', 'Duvida sobre um pedido', 'Resolvido', 'O pedido SPDZ-0001 já foi enviado?'),
(5, 'Eduardo Alves', 'eduardo.c@email.com', 'Problema com Pagamento', 'Ativo', 'Meu cartão não está sendo aceito na finalização.'),
(6, 'Fernanda Rocha', 'fernanda.c@email.com', 'Informações sobre produto', 'Resolvido', 'Qual a diferença entre o pneu semi slick e o slick?'),
(7, 'Gustavo Martins', 'gustavo.c@email.com', 'Problemas Tecnicos/Erros no site', 'Ativo', 'Erro 404 ao tentar adicionar item ao carrinho.'),
(8, 'Helena Pereira', 'helena.c@email.com', 'Outros Assuntos', 'Resolvido', 'Gostaria de mudar meu endereço de entrega.'),
(9, 'Igor Santos', 'igor.c@email.com', 'Duvida sobre um pedido', 'Ativo', 'Queria saber o prazo de entrega do meu pedido.'),
(10, 'Julia Oliveira', 'julia.c@email.com', 'Problema com Pagamento', 'Resolvido', 'Boleto bancário não foi compensado.'),
(11, 'Lucas Ribeiro', 'lucas.c@email.com', 'Informações sobre produto', 'Ativo', 'A ECU FuelTech FT550 precisa de chicote extra?'),
(12, 'Mariana Costa', 'mariana.c@email.com', 'Problemas Tecnicos/Erros no site', 'Resolvido', 'Não consigo redefinir minha senha, o link não chega.'),
(13, 'Nelson Almeida', 'nelson.c@email.com', 'Outros Assuntos', 'Ativo', 'Como faço para desbloquear minha conta?'),
(14, 'Otávio Guedes', 'otavio.c@email.com', 'Duvida sobre um pedido', 'Resolvido', 'Recebi o item errado, como proceder com a troca?'),
(15, 'Priscila Nunes', 'priscila.c@email.com', 'Problema com Pagamento', 'Ativo', 'O valor cobrado na fatura está incorreto.'),
(16, 'Quentin Viana', 'quentin.c@email.com', 'Informações sobre produto', 'Resolvido', 'Qual a medida máxima de roda para o kit big brake?'),
(17, 'Rafael Xavier', 'rafael.c@email.com', 'Problemas Tecnicos/Erros no site', 'Ativo', 'O filtro de busca de produtos está lento.'),
(18, 'Sofia Zanella', 'sofia.c@email.com', 'Outros Assuntos', 'Resolvido', 'Gostaria de um orçamento para loja parceira.'),
(19, 'Thiago Abreu', 'thiago.c@email.com', 'Duvida sobre um pedido', 'Ativo', 'O status do meu pedido não atualiza há 3 dias.'),
(20, 'Ursula Bastos', 'ursula.c@email.com', 'Problema com Pagamento', 'Resolvido', 'Não consegui usar meu cupom de desconto.'),
(21, 'Vinicius Castro', 'vinicius.c@email.com', 'Informações sobre produto', 'Ativo', 'Vocês têm volante aliviado para Honda Civic?'),
(22, 'Wanda Dantas', 'wanda.c@email.com', 'Problemas Tecnicos/Erros no site', 'Resolvido', 'A imagem do produto está com erro de carregamento.'),
(23, 'Xavier Elias', 'xavier.c@email.com', 'Outros Assuntos', 'Ativo', 'Qual o prazo de garantia para virabrequim?'),
(24, 'Yara Fogaça', 'yara.c@email.com', 'Duvida sobre um pedido', 'Resolvido', 'A data de entrega prevista foi ultrapassada.'),
(25, 'Zeca Gama', 'zeca.c@email.com', 'Problema com Pagamento', 'Ativo', 'Minha compra foi estornada sem motivo.'),
(26, 'Alexandre Horta', 'alexandre.c@email.com', 'Informações sobre produto', 'Resolvido', 'O coletor de admissão serve no meu carro?'),
(27, 'Bianca Inacio', 'bianca.c@email.com', 'Problemas Tecnicos/Erros no site', 'Ativo', 'A página de checkout está travando no celular.'),
(28, 'Cesar Junqueira', 'cesar.c@email.com', 'Outros Assuntos', 'Resolvido', 'Preciso de uma segunda via da nota fiscal.'),
(29, 'Diana Klima', 'diana.c@email.com', 'Duvida sobre um pedido', 'Ativo', 'Posso alterar a cor do meu produto no pedido já confirmado?'),
(30, 'Elias Lemos', 'elias.c@email.com', 'Problema com Pagamento', 'Resolvido', 'Pagamento duplicado no cartão.'),
(31, 'Felipe Motta', 'felipe.c@email.com', 'Informações sobre produto', 'Ativo', 'Qual o material da barra estabilizadora?'),
(32, 'Gisele Novaes', 'gisele.c@email.com', 'Problemas Tecnicos/Erros no site', 'Resolvido', 'Os filtros da categoria não estão funcionando corretamente.'),
(33, 'Hector Pinheiro', 'hector.admin@email.com', 'Outros Assuntos', 'Ativo', 'Solicitação de credenciais de acesso para novo admin.'),
(34, 'Igor Quintino', 'igor.func2@email.com', 'Informações sobre produto', 'Resolvido', 'Queria saber se o volante esportivo tem cubo.');
