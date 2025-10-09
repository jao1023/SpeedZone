document.addEventListener('DOMContentLoaded', () => {
    const listView = document.getElementById('product-list-view');
    const formView = document.getElementById('product-form-view');
    const formTitle = document.getElementById('form-title');
    const hideFormLink = document.getElementById('hide-form-link');
    const showCreateFormBtn = document.getElementById('show-create-form');
    const editBtns = document.querySelectorAll('.edit-btn');
    const productForm = document.querySelector('.product-form');

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
        const product = productsData[id];
        if (product) {
            formTitle.textContent = `Editar Produto #${id}`;
            document.getElementById('product-id').value = product.id;
            document.getElementById('nome').value = product.nome;
            document.getElementById('sku').value = product.sku;
            document.getElementById('preco').value = product.preco;
            document.getElementById('estoque').value = product.estoque;
            document.getElementById('descricao').value = product.descricao;
            document.getElementById('categoria').value = product.categoria;
            document.getElementById('submit-product-btn').textContent = 'Salvar Alterações';
            toggleView(true);
        }
    }

    // 1. Mostrar formulário para CRIAÇÃO
    showCreateFormBtn.addEventListener('click', () => {
        formTitle.textContent = 'Adicionar Novo Produto';
        document.getElementById('product-id').value = '';
        document.getElementById('submit-product-btn').textContent = 'Criar Produto';
        productForm.reset();
        toggleView(true);
    });

    // 2. Mostrar formulário para EDIÇÃO
    editBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const productId = e.currentTarget.getAttribute('data-id');
            loadProductForEdit(productId);
        });
    });

    // 3. Ocultar formulário (Voltar para Lista)
    hideFormLink.addEventListener('click', (e) => {
        e.preventDefault();
        toggleView(false);
    });

    // 4. Deleção (Ação simulada)
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const productId = e.currentTarget.getAttribute('data-id');
            if (confirm(`Tem certeza que deseja DELETAR o produto #${productId}?`)) {
                alert(`Produto #${productId} deletado (Simulação).`);
                // No código real, aqui seria feita uma chamada API para deletar o item.
            }
        });
    });

    // 5. Simulação de Submissão do Formulário
    productForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const action = document.getElementById('product-id').value ? 'editado' : 'criado';
        alert(`Produto ${action} com sucesso! (Simulação)`);
        toggleView(false);
    });
});