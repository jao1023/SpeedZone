document.addEventListener('DOMContentLoaded', () => {
    
    // Elementos do Modal
    const modal = document.getElementById('order-details-modal');
    const closeBtn = document.querySelector('.modal .close-btn');
    const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
    const itemsList = document.getElementById('items-list');

    // Simulação de Dados de Pedidos (Substituiria o Banco de Dados)
    const ordersData = {
        '1001': {
            id: '#SZ2025-1001', date: '30/09/2025', client: 'João Silva', email: 'joao.silva@email.com', 
            address: 'Rua das Peças, 123, Curitiba - PR', status: 'Em Processamento',
            subtotal: 'R$ 2.500,00', shipping: 'R$ 59,90', total: 'R$ 2.559,90',
            items: [
                { name: 'Fueltech FT450', cod: 'FT-450', qty: 1, subtotal: 'R$ 2.500,00' },
                { name: 'Kit Adesivos', cod: 'ADES-M', qty: 1, subtotal: 'R$ 59,90' }
            ]
        },
        '1002': {
            id: '#SZ2025-1002', date: '29/09/2025', client: 'Maria Souza', email: 'maria.souza@email.com', 
            address: 'Av. Velocidade, 400, São Paulo - SP', status: 'Entregue',
            subtotal: 'R$ 139,80', shipping: 'R$ 20,00', total: 'R$ 159,80',
            items: [
                { name: 'Óleo Motor Sintético', cod: 'OLEO-SYNT', qty: 2, subtotal: 'R$ 139,80' }
            ]
        },
        '1003': {
            id: '#SZ2025-1003', date: '28/09/2025', client: 'Pedro Santos', email: 'pedro.santos@email.com', 
            address: 'Rua da Turbina, 50, Rio de Janeiro - RJ', status: 'Cancelado',
            subtotal: 'R$ 4.999,00', shipping: 'R$ 0,00', total: 'R$ 4.999,00',
            items: [
                { name: 'Turbina Garrett GT28RS', cod: 'GT-28RS', qty: 1, subtotal: 'R$ 4.999,00' }
            ]
        }
    };

    // Função para preencher e mostrar o modal
    function showOrderDetails(orderId) {
        const order = ordersData[orderId];
        if (!order) return alert('Pedido não encontrado.');

        // 1. Preenche Títulos e Informações Principais
        document.getElementById('modal-title').textContent = `Detalhes do Pedido ${order.id}`;
        document.getElementById('client-name').textContent = order.client;
        document.getElementById('client-email').textContent = order.email;
        document.getElementById('delivery-address').textContent = order.address;
        
        // 2. Preenche Status e aplica a cor
        const statusDetailElement = document.getElementById('order-status-detail');
        statusDetailElement.textContent = order.status;
        // Ajusta o nome da classe para o CSS: 'Em Processamento' -> 'em-processamento'
        statusDetailElement.className = `status ${order.status.toLowerCase().replace(/\s/g, '-')}`;

        // 3. Preenche Itens Adquiridos
        itemsList.innerHTML = '';
        order.items.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.name}</td>
                <td>${item.cod}</td>
                <td style="text-align: center;">${item.qty}</td>
                <td style="text-align: right;">${item.subtotal}</td>
            `;
            itemsList.appendChild(row);
        });

        // 4. Preenche Resumo Financeiro
        document.getElementById('subtotal').textContent = order.subtotal;
        document.getElementById('shipping-cost').textContent = order.shipping;
        document.getElementById('total-amount').textContent = order.total;

        // 5. Mostra o Modal
        modal.style.display = 'block';
    }

    // Event Listeners para abrir o modal
    viewDetailsBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const orderId = e.currentTarget.getAttribute('data-id');
            showOrderDetails(orderId);
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

    // Simulação do botão de Atualizar Status
    document.querySelector('.update-status-btn').addEventListener('click', () => {
        alert("Simulação: Abrir interface de atualização de status do pedido.");
    });
});
