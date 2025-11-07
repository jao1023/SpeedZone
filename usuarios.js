document.addEventListener('DOMContentLoaded', function() {
    const listView = document.getElementById('user-list-view');
    const formView = document.getElementById('user-form-view');
    const hideFormLink = document.getElementById('hide-user-form-link');
    const userForm = document.querySelector('.user-form');
    const formTitle = document.getElementById('user-form-title');
    const submitButton = document.getElementById('submit-user-btn');

    const formFields = {
        id: document.getElementById('user-id'),
        nome: document.getElementById('user-nome'),
        email: document.getElementById('user-email'),
        cargo: document.getElementById('user-cargo'),
        status: document.getElementById('user-status'),
    };

    /**
     * Alterna a visibilidade entre a lista e o formulário.
     * @param {string} view 'list' ou 'form'
     */
    function toggleView(view) {
        if (view === 'form') {
            listView.classList.remove('active');
            formView.classList.add('active');
        } else {
            formView.classList.remove('active');
            listView.classList.add('active');
            userForm.reset(); 
            formFields.id.value = '';
        }
    }

    hideFormLink.addEventListener('click', function(e) {
        e.preventDefault();
        toggleView('list');
    });

    document.querySelectorAll('.edit-user-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('href').split('id=')[1];

            formTitle.textContent = `Editar Usuário (ID: ${userId})`;
            submitButton.textContent = 'Salvar Alterações';
            formFields.id.value = userId;
            const row = document.querySelector(`.table-row[data-user-id='${userId}']`);
            
            if (row) {
                const name = row.children[1].textContent.trim();
                const email = row.children[2].textContent.trim();
                const cargo = row.children[3].textContent.trim();
                const status = row.children[4].textContent.trim();
                
                formFields.nome.value = name;
                formFields.email.value = email;
                formFields.cargo.value = cargo;
                formFields.status.value = status; 
                
                toggleView('form');
            } else {
                alert('Erro: Dados do usuário não encontrados na tabela.');
            }
        });
    });

    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const actionUrl = this.getAttribute('action');
        
        submitButton.disabled = true;
        submitButton.textContent = 'Salvando...';
        
        fetch(actionUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                window.location.href = 'usuarios.php' + (new URLSearchParams(window.location.search).toString() ? '?' + new URLSearchParams(window.location.search).toString() : '');
            } else {
                 submitButton.disabled = false;
                 submitButton.textContent = 'Salvar Alterações';
            }
        })
        .catch(error => {
            console.error('Erro na submissão:', error);
            alert('Ocorreu um erro na comunicação com o servidor.');
            submitButton.disabled = false;
            submitButton.textContent = 'Salvar Alterações';
        });
    });
});


document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const userNameElement = this.closest('.table-row').children[1];
            const userName = userNameElement ? userNameElement.textContent.trim() : 'este usuário';

            if (!confirm(`Tem certeza que deseja DELETAR o usuário: ${userName}? Esta ação é irreversível.`)) {
                e.preventDefault();
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', () => {
o
    const userListView = document.getElementById('user-list-view');
    const userFormView = document.getElementById('user-form-view');
    const formTitle = document.getElementById('user-form-title');
    const hideFormLink = document.getElementById('hide-user-form-link');
    const showCreateFormBtn = document.getElementById('show-create-user-form');
    const editBtns = document.querySelectorAll('.edit-user-btn');
    const userForm = document.querySelector('.user-form');

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

    if (showCreateFormBtn) showCreateFormBtn.addEventListener('click', () => {
        formTitle.textContent = 'Adicionar Novo Usuário';
        document.getElementById('user-id').value = '';
        document.getElementById('submit-user-btn').textContent = 'Criar Usuário';
        document.getElementById('user-registro').value = new Date().toLocaleDateString('pt-BR');
        userForm.reset();
        toggleUserView(true);
    });

    if (hideFormLink) hideFormLink.addEventListener('click', (e) => {
        e.preventDefault();
        toggleUserView(false);
    });


});
