document.addEventListener('DOMContentLoaded', () => {
    const listView = document.getElementById('product-list-view');
    const formView = document.getElementById('product-form-view');
    const formTitle = document.getElementById('form-title');
    const hideFormLink = document.getElementById('hide-form-link');
    const showCreateFormBtn = document.getElementById('show-create-form');
    const editBtns = document.querySelectorAll('.edit-btn');
    const productForm = document.querySelector('.product-form');
    const searchInput = document.querySelector('.search-input');

    // Mapeamento de Produtos (Simulação de Banco de Dados)
    const productsData = {
        '1': {
            id: '1',
            nome: 'Fueltech FT450',
            sku: 'FT-450',
            preco: '2500.00',
            estoque: '15',
            descricao: 'Módulo de injeção programável de alta performance para motores de competição.',
            categoria: 'injecao'
        },
        '2': {
            id: '2',
            nome: 'Kit Adesivos Max Performance',
            sku: 'ADES-M',
            preco: '59.90',
            estoque: '120',
            descricao: 'Kit com adesivos exclusivos para personalizar seu veículo.',
            categoria: 'acessorios'
        }
    };

    // Função para alternar a visualização
    function toggleView(showForm) {
        if (showForm) {
            listView.classList.remove('active');
            formView.classList.add('active');
        } else {
            formView.classList.remove('active');
            listView.classList.add('active');
            productForm.reset(); // Limpa o formulário ao voltar para a lista
        }
    }

    // Função para preencher o formulário para edição
    function loadProductForEdit(id) {
        // Buscar dados do produto no banco via AJAX
        fetch(`get_produto.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const product = data.produto;
                    formTitle.textContent = `Editar Produto #${id}`;
                    document.getElementById('product-id').value = product.id_produto;
                    document.getElementById('nome').value = product.nome_produto;
                    document.getElementById('sku').value = product.cod_produto;
                    document.getElementById('preco').value = product.preco;
                    document.getElementById('estoque').value = product.qtd_estoque;
                    document.getElementById('descricao').value = product.descricao_produto;
                    document.getElementById('categoria').value = product.categoria;
                    document.getElementById('submit-product-btn').textContent = 'Salvar Alterações';
                    toggleView(true);
                } else {
                    alert('Erro ao carregar dados do produto: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao carregar dados do produto');
            });
    }

    // 1. Mostrar formulário para CRIAÇÃO
    showCreateFormBtn.addEventListener('click', () => {
        formTitle.textContent = 'Adicionar Novo Produto';
        document.getElementById('product-id').value = '';
        document.getElementById('submit-product-btn').textContent = 'Criar Produto';
        productForm.reset();
        toggleView(true);
    });


    // 3. Ocultar formulário (Voltar para Lista)
    hideFormLink.addEventListener('click', (e) => {
        e.preventDefault();
        toggleView(false);
    });

    // Função para configurar botões de ação (edit e delete)
    function setupActionButtons() {
        // Botões de delete
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = e.currentTarget.getAttribute('data-id');
                const productName = e.currentTarget.closest('.table-row').querySelector('.cell-data:nth-child(2)').textContent;
                
                if (confirm(`Tem certeza que deseja DELETAR o produto "${productName}" (ID: ${productId})?\n\nEsta ação não pode ser desfeita!`)) {
                    // Redirecionar para delete_produto.php
                    window.location.href = `delete_produto.php?id=${productId}`;
                }
            });
        });

        // Botões de edição
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = e.currentTarget.getAttribute('data-id');
                loadProductForEdit(productId);
            });
        });
    }

    // 4. Configurar botões de ação
    setupActionButtons();

    // 5. Submissão do Formulário
    productForm.addEventListener('submit', (e) => {
        const productId = document.getElementById('product-id').value;
        
        if (productId) {
            // É edição - enviar para edit_produto.php
            e.preventDefault();
            productForm.action = 'edit_produto.php';
            productForm.submit();
        } else {
            // É criação - enviar para novo_produto.php (já configurado no HTML)
            // Deixar o formulário ser enviado normalmente
        }
    });

    // 6. Busca em tempo real (opcional - busca no servidor)
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const term = e.target.value.trim();
            
            // Se o campo estiver vazio, recarregar a página sem parâmetros
            if (term === '') {
                window.location.href = 'produtos.php';
                return;
            }
            
            // Aguardar 500ms após o usuário parar de digitar
            searchTimeout = setTimeout(() => {
                if (term.length >= 2) { // Buscar apenas com 2+ caracteres
                    window.location.href = `produtos.php?busca=${encodeURIComponent(term)}`;
                }
            }, 500);
        });

        // Buscar ao pressionar Enter
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const term = e.target.value.trim();
                if (term.length > 0) {
                    window.location.href = `produtos.php?busca=${encodeURIComponent(term)}`;
                } else {
                    window.location.href = 'produtos.php';
                }
            }
        });
    }
});
