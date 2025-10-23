document.addEventListener('DOMContentLoaded', () => {
    
    // Elementos do Modal
    const modal = document.getElementById('order-details-modal');
    const closeBtn = document.querySelector('.modal .close-btn');
    const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
    const itemsList = document.getElementById('items-list');
    
    // Elementos da busca
    const searchInput = document.querySelector('.search-input');
    const clearSearchBtn = document.querySelector('.clear-search-btn');
    const tableRows = document.querySelectorAll('.table-row');

    // Função para preencher e mostrar o modal
    function showOrderDetails(codigoPedido) {
        // Mostrar loading
        modal.style.display = 'block';
        document.getElementById('modal-title').textContent = 'Carregando...';
        
        // Buscar dados do pedido via AJAX
        fetch(`get_pedido_details.php?codigo_pedido=${encodeURIComponent(codigoPedido)}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Erro: ' + data.message);
                    modal.style.display = 'none';
                    return;
                }
                
                const order = data.pedido;
                
                // 1. Preenche Títulos e Informações Principais
                document.getElementById('modal-title').textContent = `Detalhes do Pedido ${order.id}`;
                document.getElementById('client-name').textContent = order.cliente;
                document.getElementById('client-email').textContent = order.email;
                document.getElementById('delivery-address').textContent = order.endereco;
                
                // 2. Preenche Status e aplica a cor
                const statusDetailElement = document.getElementById('order-status-detail');
                statusDetailElement.textContent = order.status;
                // Ajusta o nome da classe para o CSS
                statusDetailElement.className = `status ${order.status.toLowerCase().replace(/\s/g, '-')}`;

                // 3. Preenche Itens Adquiridos
                itemsList.innerHTML = '';
                order.itens.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.nome}</td>
                        <td>${item.codigo}</td>
                        <td style="text-align: center;">${item.quantidade}</td>
                        <td style="text-align: right;">${item.subtotal}</td>
                    `;
                    itemsList.appendChild(row);
                });

                // 4. Preenche Resumo Financeiro
                document.getElementById('subtotal').textContent = order.subtotal;
                document.getElementById('shipping-cost').textContent = order.frete;
                document.getElementById('total-amount').textContent = order.total;
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao carregar detalhes do pedido');
                modal.style.display = 'none';
            });
    }

    // Event Listeners para abrir o modal
    viewDetailsBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const codigoPedido = e.currentTarget.getAttribute('data-id');
            showOrderDetails(codigoPedido);
        });
    });

    // Event Listeners para fechar o modal
    if (closeBtn) closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Fecha o modal ao clicar fora
    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });

    // Função de busca
    function filterOrders(searchTerm) {
        const term = searchTerm.toLowerCase().trim();
        let visibleCount = 0;
        
        tableRows.forEach(row => {
            const orderId = row.querySelector('[data-label="ID:"]')?.textContent.toLowerCase() || '';
            const clientName = row.querySelector('[data-label="Cliente:"]')?.textContent.toLowerCase() || '';
            const orderDataId = row.getAttribute('data-order-id')?.toLowerCase() || '';
            
            // Busca por código do pedido (com ou sem #) ou nome do cliente
            const matches = orderId.includes(term) || 
                           clientName.includes(term) || 
                           orderDataId.includes(term);
            
            if (matches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Mostrar mensagem se não houver resultados
        const noDataElement = document.querySelector('.no-data');
        if (visibleCount === 0 && term !== '') {
            if (!noDataElement) {
                const dataTable = document.querySelector('.data-table');
                const noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-data';
                noResultsDiv.innerHTML = `<p>Nenhum pedido encontrado para "${searchTerm}".</p>`;
                dataTable.appendChild(noResultsDiv);
            }
        } else if (noDataElement && term !== '') {
            noDataElement.remove();
        }
    }
    
    // Função para limpar busca
    function clearSearch() {
        searchInput.value = '';
        filterOrders('');
        clearSearchBtn.style.display = 'none';
    }
    
    // Event listener para busca em tempo real
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const value = e.target.value;
            filterOrders(value);
            
            // Mostrar/ocultar botão de limpar
            if (value.trim() !== '') {
                clearSearchBtn.style.display = 'block';
            } else {
                clearSearchBtn.style.display = 'none';
            }
        });
        
        // Limpar busca ao pressionar Escape
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                clearSearch();
            }
        });
    }
    
    // Event listener para botão de limpar
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', clearSearch);
    }

    // Simulação do botão de Atualizar Status
    document.querySelector('.update-status-btn').addEventListener('click', () => {
        alert("Simulação: Abrir interface de atualização de status do pedido.");
    });
});
