document.addEventListener('DOMContentLoaded', () => {
    
    // Elementos da Visualização
    const userListView = document.getElementById('user-list-view');
    const userFormView = document.getElementById('user-form-view');
    const formTitle = document.getElementById('user-form-title');
    const hideFormLink = document.getElementById('hide-user-form-link');
    const showCreateFormBtn = document.getElementById('show-create-user-form');
    const editBtns = document.querySelectorAll('.edit-user-btn');
    const userForm = document.querySelector('.user-form');

    // Simulação de Dados de Usuários
    const usersData = {
        '1': { id: '1', nome: 'Admin Master', email: 'admin@speedzone.com', telefone: '(41) 9999-0000', registro: '01/01/2025', cargo: 'admin', status: 'active' },
        '2': { id: '2', nome: 'João Cliente Silva', email: 'joao.silva@cliente.com', telefone: '(11) 9888-1111', registro: '20/09/2025', cargo: 'cliente', status: 'active' },
        '3': { id: '3', nome: 'Usuário Bloqueado', email: 'bloqueado@email.com', telefone: '(99) 9777-2222', registro: '10/08/2025', cargo: 'cliente', status: 'inactive' }
    };

    function toggleUserView(showForm) {
        if (showForm) {
            userListView.classList.remove('active');
            userFormView.classList.add('active');
        } else {
            userFormView.classList.remove('active');
            userListView.classList.add('active');
            userForm.reset(); 
        }
    }

    function loadUserForEdit(id) {
        const user = usersData[id];
        if (user) {
            formTitle.textContent = `Editar Usuário #${id}`;
            document.getElementById('user-id').value = user.id;
            
            // Preenche os campos do formulário
            document.getElementById('user-nome').value = user.nome;
            document.getElementById('user-email').value = user.email;
            document.getElementById('user-telefone').value = user.telefone;
            document.getElementById('user-registro').value = user.registro;
            document.getElementById('user-cargo').value = user.cargo;
            document.getElementById('user-status').value = user.status;
            
            document.getElementById('submit-user-btn').textContent = 'Salvar Alterações';
            toggleUserView(true);
        }
    }

    // Ações de Visualização
    if (showCreateFormBtn) showCreateFormBtn.addEventListener('click', () => {
        formTitle.textContent = 'Adicionar Novo Usuário';
        document.getElementById('user-id').value = '';
        document.getElementById('submit-user-btn').textContent = 'Criar Usuário';
        document.getElementById('user-registro').value = new Date().toLocaleDateString('pt-BR'); // Data atual
        userForm.reset();
        toggleUserView(true);
    });

    if (hideFormLink) hideFormLink.addEventListener('click', (e) => {
        e.preventDefault();
        toggleUserView(false);
    });

    // Ação de Editar
    editBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const userId = e.currentTarget.getAttribute('data-id');
            loadUserForEdit(userId);
        });
    });

    // Ação de Deletar
    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const userId = e.currentTarget.getAttribute('data-id');
            if (confirm(`Tem certeza que deseja DELETAR o usuário #${userId}? Essa ação é irreversível!`)) {
                alert(`Usuário #${userId} deletado (Simulação).`);
            }
        });
    });

    // Ação de Salvar/Criar
    userForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const action = document.getElementById('user-id').value ? 'editado' : 'criado';
        alert(`Usuário ${action} com sucesso! (Simulação)`);
        toggleUserView(false);
    });
});