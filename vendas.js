document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('order-details-modal');
    const closeBtn = document.querySelector('.modal .close-btn');
    const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
    const itemsList = document.getElementById('items-list');

    const searchInput = document.querySelector('.search-input');
    const clearSearchBtn = document.querySelector('.clear-search-btn');
    const tableRows = document.querySelectorAll('.table-row');

    let currentOrderCode = null;

    function showOrderDetails(codigoPedido) {
        currentOrderCode = codigoPedido;

        modal.style.display = 'block';
        document.getElementById('modal-title').textContent = 'Carregando...';

        fetch(`get_pedido_details.php?codigo_pedido=${encodeURIComponent(codigoPedido)}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Erro: ' + data.message);
                    modal.style.display = 'none';
                    return;
                }
                
                const order = data.pedido;

                document.getElementById('modal-title').textContent = `Detalhes do Pedido ${order.id}`;
                document.getElementById('client-name').textContent = order.cliente;
                document.getElementById('client-email').textContent = order.email;
                document.getElementById('delivery-address').textContent = order.endereco;

                const statusDetailElement = document.getElementById('order-status-detail');
                statusDetailElement.innerHTML = `
                    <select id="status-select" class="status-select">
                        <option value="Separação do pedido" ${order.status === 'Separação do pedido' ? 'selected' : ''}>Separação do pedido</option>
                        <option value="Em Transporte" ${order.status === 'Em Transporte' ? 'selected' : ''}>Em Transporte</option>
                        <option value="Entregue" ${order.status === 'Entregue' ? 'selected' : ''}>Entregue</option>
                    </select>
                `;

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

                document.getElementById('subtotal').textContent = order.subtotal;
                document.getElementById('shipping-cost').textContent = order.frete;
                document.getElementById('total-amount').textContent = order.total;
            })
            .catch(error => {
                console.error('Erro ao carregar detalhes:', error);
                alert('Erro ao carregar detalhes do pedido');
                modal.style.display = 'none';
            });
    }

    viewDetailsBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const codigoPedido = e.currentTarget.getAttribute('data-id');
            showOrderDetails(codigoPedido);
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });

    function filterOrders(searchTerm) {
        const term = searchTerm.toLowerCase().trim();
        let visibleCount = 0;
        
        tableRows.forEach(row => {
            const orderId = row.querySelector('[data-label="ID:"]')?.textContent.toLowerCase() || '';
            const clientName = row.querySelector('[data-label="Cliente:"]')?.textContent.toLowerCase() || '';
            const orderDataId = row.getAttribute('data-order-id')?.toLowerCase() || '';

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

    function clearSearch() {
        searchInput.value = '';
        filterOrders('');
        clearSearchBtn.style.display = 'none';
    }

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const value = e.target.value;
            filterOrders(value);

            if (value.trim() !== '') {
                clearSearchBtn.style.display = 'block';
            } else {
                clearSearchBtn.style.display = 'none';
            }
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                clearSearch();
            }
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', clearSearch);
    }

    document.querySelector('.update-status-btn').addEventListener('click', () => {
        const statusSelect = document.getElementById('status-select');
        
        if (!statusSelect) {
            alert('Erro: Elemento de status não encontrado.');
            return;
        }

        const newStatus = statusSelect.value;
        
        if (!currentOrderCode) {
            alert('Erro: Código do pedido não encontrado.');
            return;
        }

        console.log('Tentando atualizar:', currentOrderCode, 'para', newStatus);


        if (!confirm(`Deseja realmente alterar o status para "${newStatus}"?`)) {
            return;
        }

        const updateBtn = document.querySelector('.update-status-btn');
        updateBtn.disabled = true;
        updateBtn.textContent = 'Salvando...';

        fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                codigo_pedido: currentOrderCode,
                novo_status: newStatus
            })
        })
        .then(response => {
            console.log('Status da resposta:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Resposta do servidor:', data);
            
            if (data.success) {
                alert('Status atualizado com sucesso!');

                const row = document.querySelector(`[data-order-id="${currentOrderCode}"]`);
                if (row) {
                    const statusCell = row.querySelector('.status');
                    statusCell.textContent = newStatus;

                    statusCell.className = 'cell-data status';
                    if (newStatus === 'Entregue') {
                        statusCell.classList.add('delivered');
                    } else {
                        statusCell.classList.add('processing');
                    }
                }

                modal.style.display = 'none';
            } else {
                alert('Erro ao atualizar status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro ao atualizar status do pedido: ' + error.message);
        })
        .finally(() => {
            updateBtn.disabled = false;
            updateBtn.textContent = 'Atualizar Status';
        });
    });
});