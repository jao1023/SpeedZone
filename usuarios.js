// Arquivo: usuarios.js

document.addEventListener('DOMContentLoaded', function() {
    const listView = document.getElementById('user-list-view');
    const formView = document.getElementById('user-form-view');
    const hideFormLink = document.getElementById('hide-user-form-link');
    const userForm = document.querySelector('.user-form');
    const formTitle = document.getElementById('user-form-title');
    const submitButton = document.getElementById('submit-user-btn');

    // Mapeamento dos campos do formulário
    const formFields = {
        id: document.getElementById('user-id'),
        nome: document.getElementById('user-nome'),
        email: document.getElementById('user-email'),
        cargo: document.getElementById('user-cargo'),
        status: document.getElementById('user-status'),
        // O campo 'registro' está desabilitado e não será enviado
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
            // Limpa o formulário ao voltar para a lista
            userForm.reset(); 
            formFields.id.value = ''; // Garante que o ID está vazio
        }
    }

    // 1. Evento para voltar à lista
    hideFormLink.addEventListener('click', function(e) {
        e.preventDefault();
        toggleView('list');
    });

    // 2. Evento para carregar e exibir o formulário de edição
    document.querySelectorAll('.edit-user-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('href').split('id=')[1];
            
            // Define o título e o botão para edição
            formTitle.textContent = `Editar Usuário (ID: ${userId})`;
            submitButton.textContent = 'Salvar Alterações';
            formFields.id.value = userId; // Define o ID para o handler PHP

            // Busca os dados atuais do usuário (Simulação, pois você já os tem na tabela)
            // Em uma aplicação real, você faria um AJAX para um endpoint 'fetch_user_data.php?id=...'
            
            // Para simplificar, vamos obter os dados da linha da tabela (menos seguro, mas rápido)
            const row = document.querySelector(`.table-row[data-user-id='${userId}']`);
            
            if (row) {
                // Obtém os dados da linha para preencher o formulário
                const name = row.children[1].textContent.trim();
                const email = row.children[2].textContent.trim();
                const cargo = row.children[3].textContent.trim();
                const status = row.children[4].textContent.trim();
                
                formFields.nome.value = name;
                formFields.email.value = email;
                formFields.cargo.value = cargo;
                formFields.status.value = status; 
                // Telefone não está na linha, precisaria de um endpoint AJAX real.
                
                toggleView('form');
            } else {
                alert('Erro: Dados do usuário não encontrados na tabela.');
            }
        });
    });
    
    // 3. Submissão do Formulário via AJAX (para evitar recarregar a página)
    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const actionUrl = this.getAttribute('action'); // edit_usuario_handler.php
        
        submitButton.disabled = true;
        submitButton.textContent = 'Salvando...';
        
        fetch(actionUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Exibe mensagem de sucesso ou erro
            if (data.success) {
                // Recarrega a página para atualizar a lista ou volta para ela
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

// Arquivo: usuarios.js (Adicione no final, ou dentro do DOMContentLoaded)

document.addEventListener('DOMContentLoaded', function() {
    // ... (Seu código anterior do toggleView, edit-user-btn, e form submit) ...

    // 4. Evento para Confirmação de Exclusão
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const userNameElement = this.closest('.table-row').children[1];
            const userName = userNameElement ? userNameElement.textContent.trim() : 'este usuário';
            
            // Pergunta de confirmação antes de permitir a exclusão
            if (!confirm(`Tem certeza que deseja DELETAR o usuário: ${userName}? Esta ação é irreversível.`)) {
                e.preventDefault(); // Impede o link de ser seguido se o usuário clicar em "Cancelar"
            }
        });
    });
});