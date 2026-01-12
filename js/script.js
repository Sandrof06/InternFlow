// Função para criar pop-up modal
function criarModal(titulo, conteudo, botoes = []) {
    // Remove modal existente se houver
    const modalExistente = document.getElementById('modal-overlay');
    if (modalExistente) {
        modalExistente.remove();
    }

    // Cria overlay
    const overlay = document.createElement('div');
    overlay.id = 'modal-overlay';
    overlay.className = 'modal-overlay';
    
    // Cria modal
    const modal = document.createElement('div');
    modal.className = 'modal';
    
    // Cabeçalho
    const header = document.createElement('div');
    header.className = 'modal-header';
    header.innerHTML = `
        <h2>${titulo}</h2>
        <button class="modal-close">&times;</button>
    `;
    
    // Corpo
    const body = document.createElement('div');
    body.className = 'modal-body';
    body.innerHTML = conteudo;
    
    // Rodapé com botões
    const footer = document.createElement('div');
    footer.className = 'modal-footer';
    
    if (botoes.length === 0) {
        botoes.push({ texto: 'Fechar', acao: 'fechar', classe: 'btn-primary' });
    }
    
    botoes.forEach(btn => {
        const botao = document.createElement('button');
        botao.className = btn.classe || 'btn-primary';
        botao.textContent = btn.texto;
        botao.onclick = () => {
            if (btn.acao === 'fechar') {
                overlay.remove();
            } else if (btn.acao) {
                btn.acao();
            }
        };
        footer.appendChild(botao);
    });
    
    modal.appendChild(header);
    modal.appendChild(body);
    modal.appendChild(footer);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Fechar ao clicar no overlay
    overlay.onclick = (e) => {
        if (e.target === overlay) {
            overlay.remove();
        }
    };
    
    // Fechar ao clicar no X
    header.querySelector('.modal-close').onclick = () => overlay.remove();
}

// CSS para o modal, dropdown, scrollbars e notificações
const modalCSS = `
<style id="modal-styles">
/* Scrollbars Customizadas */
::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
    border: 2px solid #f1f5f9;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Modo Noturno - Scrollbars */
body.night-mode ::-webkit-scrollbar-track {
    background: #1e293b;
}

body.night-mode ::-webkit-scrollbar-thumb {
    background: #475569;
    border: 2px solid #1e293b;
}

body.night-mode ::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Firefox Scrollbars */
* {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

body.night-mode * {
    scrollbar-color: #475569 #1e293b;
}

/* Efeito de Notificações no Bell */
.fa-bell {
    position: relative;
    transition: transform 0.3s ease;
}

.fa-bell:hover {
    transform: scale(1.1);
}

/* Badge de Notificação */
.notification-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    animation: pulse 2s infinite;
}

body.night-mode .notification-badge {
    border-color: #1e293b;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
}

/* Animação de pulso */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.9;
    }
}

/* Animação de shake quando há nova notificação */
@keyframes shake {
    0%, 100% { transform: rotate(0deg); }
    10%, 30%, 50%, 70%, 90% { transform: rotate(-5deg); }
    20%, 40%, 60%, 80% { transform: rotate(5deg); }
}

.fa-bell.has-notifications {
    animation: shake 0.5s ease-in-out;
}

/* Container para o bell com notificação */
.bell-container {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

/* Dropdown de Notificações */
.notifications-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 350px;
    max-height: 400px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    display: none;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

body.night-mode .notifications-dropdown {
    background: #1e293b;
    border-color: #334155;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
}

.notifications-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

body.night-mode .notifications-header {
    border-bottom-color: #334155;
}

.notifications-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

body.night-mode .notifications-header h3 {
    color: #ffffff;
}

.notifications-header .mark-all-read {
    font-size: 12px;
    color: #6366f1;
    cursor: pointer;
    font-weight: 500;
}

body.night-mode .notifications-header .mark-all-read {
    color: #60a5fa;
}

.notifications-list {
    max-height: 320px;
    overflow-y: auto;
}

.notification-item {
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    gap: 12px;
}

body.night-mode .notification-item {
    border-bottom-color: #334155;
}

.notification-item:hover {
    background: #f9fafb;
}

body.night-mode .notification-item:hover {
    background: #334155;
}

.notification-item.unread {
    background: #f0f2ff;
}

body.night-mode .notification-item.unread {
    background: #1e3a5f;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
}

.notification-icon.info {
    background: #dbeafe;
    color: #1e40af;
}

.notification-icon.success {
    background: #d1fae5;
    color: #065f46;
}

.notification-icon.warning {
    background: #fef3c7;
    color: #92400e;
}

body.night-mode .notification-icon.info {
    background: #1e3a5f;
    color: #93c5fd;
}

body.night-mode .notification-icon.success {
    background: #064e3b;
    color: #6ee7b7;
}

body.night-mode .notification-icon.warning {
    background: #78350f;
    color: #fcd34d;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
}

body.night-mode .notification-title {
    color: #ffffff;
}

.notification-text {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.4;
}

body.night-mode .notification-text {
    color: #cbd5e1;
}

.notification-time {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 4px;
}

body.night-mode .notification-time {
    color: #94a3b8;
}

.notification-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #6366f1;
    flex-shrink: 0;
    margin-top: 6px;
}

body.night-mode .notification-dot {
    background: #60a5fa;
}

.notifications-empty {
    padding: 40px 20px;
    text-align: center;
    color: #9ca3af;
}

body.night-mode .notifications-empty {
    color: #64748b;
}

/* Melhorias visuais gerais */
.card {
    transition: box-shadow 0.3s ease, transform 0.2s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

body.night-mode .card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.btn-primary, .btn-outline {
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
}

body.night-mode .btn-primary:hover {
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

/* Melhorias nas tabelas */
.table-container {
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.3s ease;
}

.table-container:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

body.night-mode .table-container:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

/* Melhorias nos inputs */
input:focus, textarea:focus, select:focus {
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    transition: box-shadow 0.2s ease;
}

body.night-mode input:focus,
body.night-mode textarea:focus,
body.night-mode select:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

/* Melhorias nos links de ação */
.action-link {
    transition: color 0.2s ease;
    position: relative;
}

.action-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: currentColor;
    transition: width 0.3s ease;
}

.action-link:hover::after {
    width: 100%;
}

/* Melhorias nos badges de status */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    transition: transform 0.2s ease;
}

.status-badge:hover {
    transform: scale(1.05);
}

/* Melhorias nos cards de estatísticas */
.card-icon-box {
    transition: transform 0.3s ease;
}

.card:hover .card-icon-box {
    transform: scale(1.1) rotate(5deg);
}

/* Melhorias nos ícones de ação */
.icon-edit, .icon-delete {
    transition: all 0.2s ease;
    padding: 4px;
    border-radius: 4px;
}

.icon-edit:hover {
    background-color: rgba(67, 97, 238, 0.1);
    color: #4361ee;
}

.icon-delete:hover {
    background-color: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

body.night-mode .icon-edit:hover {
    background-color: rgba(96, 165, 250, 0.2);
}

body.night-mode .icon-delete:hover {
    background-color: rgba(239, 68, 68, 0.2);
}

/* Melhorias nos filtros */
.filter-tab {
    transition: all 0.3s ease;
    position: relative;
}

.filter-tab::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: #6366f1;
    transition: width 0.3s ease;
}

.filter-tab.active::before {
    width: 100%;
}

body.night-mode .filter-tab.active::before {
    background: #3b82f6;
}

/* Melhorias nos tabs */
.tab-btn {
    transition: all 0.3s ease;
}

.tab-btn:hover:not(.active) {
    background-color: #f3f4f6;
    transform: translateY(-1px);
}

body.night-mode .tab-btn:hover:not(.active) {
    background-color: #334155;
}

/* Melhorias nos inputs com ícone */
.input-with-icon input:focus + i,
.input-group input:focus ~ .input-icon {
    color: #4361ee;
}

body.night-mode .input-with-icon input:focus + i,
body.night-mode .input-group input:focus ~ .input-icon {
    color: #60a5fa;
}

/* Melhorias nas linhas da tabela */
tbody tr {
    transition: background-color 0.2s ease;
}

tbody tr:hover {
    background-color: #f9fafb;
}

body.night-mode tbody tr:hover {
    background-color: #334155;
}

/* Melhorias nos botões outline */
.btn-outline {
    transition: all 0.3s ease;
}

.btn-outline:hover {
    background-color: #f3f4f6;
    border-color: #d1d5db;
    transform: translateY(-1px);
}

body.night-mode .btn-outline:hover {
    background-color: #334155;
    border-color: #475569;
}

/* Melhorias nos avatares */
.user-avatar {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 2px solid transparent;
}

.user-avatar:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: #4361ee;
}

body.night-mode .user-avatar:hover {
    border-color: #60a5fa;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

/* Melhorias nos links de navegação */
.nav-center-links a {
    position: relative;
    padding: 4px 0;
}

.nav-center-links a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: #818cf8;
    transition: width 0.3s ease;
}

.nav-center-links a:hover::after,
.nav-center-links a.active-purple::after {
    width: 100%;
}

body.night-mode .nav-center-links a::after {
    background: #60a5fa;
}

/* Melhorias nos cards de status */
.stat-card {
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), transparent);
    border-radius: 50%;
    transform: translate(30%, -30%);
    transition: transform 0.3s ease;
}

.stat-card:hover::before {
    transform: translate(20%, -20%) scale(1.2);
}

body.night-mode .stat-card::before {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), transparent);
}

/* Melhorias nos formulários */
.form-area {
    background: white;
    padding: 24px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    transition: box-shadow 0.3s ease;
}

.form-area:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

body.night-mode .form-area {
    background: #1e293b;
    border-color: #334155;
}

body.night-mode .form-area:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

/* Melhorias na lista de alunos */
.student-list {
    background: white;
    padding: 16px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

body.night-mode .student-list {
    background: #1e293b;
    border-color: #334155;
}

.student-row {
    transition: all 0.2s ease;
}

.student-row:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

body.night-mode .student-row:hover {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Melhorias nos badges de curso */
.course-badge {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.course-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Melhorias no table-header-group */
.table-header-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e5e7eb;
}

body.night-mode .table-header-group {
    border-bottom-color: #334155;
}

/* Melhorias nos cards do dashboard admin */
.card {
    position: relative;
}

.card-icon-box {
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.card:hover .card-icon-box {
    opacity: 1;
}

/* Melhorias nos pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 30px;
}

.page-numbers {
    display: flex;
    gap: 4px;
}

.page-num, .page-control {
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.page-num:hover:not(.active) {
    background-color: #f3f4f6;
}

body.night-mode .page-num:hover:not(.active) {
    background-color: #334155;
}

/* Melhorias gerais de transição - aplicado apenas a elementos interativos */
button, a, .card, .btn-primary, .btn-outline, .action-link, .status-badge, .filter-tab, .tab-btn {
    transition: all 0.2s ease;
}
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

.modal {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 24px;
    color: #111827;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #6b7280;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #111827;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.modal-footer button {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

/* Settings Dropdown */
.settings-dropdown-container {
    position: relative;
    display: inline-block;
}

.settings-dropdown-container .fa-gear {
    cursor: pointer;
}

.settings-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    z-index: 1000;
    overflow: hidden;
}

.night-mode .settings-dropdown {
    background: #1f2937;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.2s;
    color: #111827;
}

.night-mode .dropdown-item {
    color: #f9fafb;
}

.dropdown-item:hover {
    background: #f3f4f6;
}

.night-mode .dropdown-item:hover {
    background: #374151;
}

.dropdown-item i:first-child {
    width: 20px;
    text-align: center;
}

.dropdown-item span {
    flex: 1;
}

.dropdown-item i:last-child {
    margin-left: auto;
    color: #6b7280;
}

.night-mode .dropdown-item i:last-child {
    color: #9ca3af;
}

.fa-toggle-on {
    color: #2563eb !important;
}

.night-mode .fa-toggle-on {
    color: #60a5fa !important;
}

/* Modo Noturno Global */
body.night-mode {
    background-color: #0f172a;
    color: #ffffff;
}

body.night-mode .navbar {
    background-color: #1e293b;
    border-bottom-color: #334155;
    color: #ffffff;
}

body.night-mode .navbar .logo-text,
body.night-mode .navbar a {
    color: #ffffff;
}

body.night-mode .navbar .action-icon {
    color: #cbd5e1;
}

body.night-mode .navbar .action-icon:hover {
    color: #ffffff;
}

body.night-mode .main-content {
    background-color: #0f172a;
    color: #ffffff;
}

body.night-mode .card {
    background-color: #1e293b;
    border-color: #334155;
    color: #ffffff;
}

body.night-mode .card-label,
body.night-mode .card-value,
body.night-mode .card-sub {
    color: #ffffff;
}

body.night-mode .table-container {
    background-color: #1e293b;
    border-color: #334155;
}

body.night-mode table {
    color: #ffffff;
}

body.night-mode thead {
    background-color: #334155;
    color: #ffffff;
}

body.night-mode thead th {
    color: #ffffff;
}

body.night-mode tbody tr {
    border-color: #334155;
    color: #ffffff;
}

body.night-mode tbody tr:hover {
    background-color: #334155;
}

body.night-mode tbody td {
    color: #e2e8f0;
}

body.night-mode input,
body.night-mode textarea,
body.night-mode select {
    background-color: #334155;
    border-color: #475569;
    color: #ffffff;
}

body.night-mode input::placeholder,
body.night-mode textarea::placeholder {
    color: #94a3b8;
}

body.night-mode label {
    color: #e2e8f0;
}

body.night-mode .btn-primary {
    background-color: #3b82f6;
    color: #ffffff;
}

body.night-mode .btn-primary:hover {
    background-color: #2563eb;
}

body.night-mode .btn-outline {
    border-color: #475569;
    color: #e2e8f0;
}

body.night-mode .btn-outline:hover {
    background-color: #334155;
    border-color: #64748b;
}

body.night-mode .modal {
    background-color: #1e293b;
    color: #ffffff;
}

body.night-mode .modal-header {
    border-bottom-color: #334155;
}

body.night-mode .modal-header h2 {
    color: #ffffff;
}

body.night-mode .modal-body {
    color: #e2e8f0;
}

body.night-mode .modal-footer {
    border-top-color: #334155;
}

body.night-mode .modal-close {
    color: #cbd5e1;
}

body.night-mode .modal-close:hover {
    color: #ffffff;
}

/* Modo Noturno - Login */
body.night-mode .main-container {
    background-color: #0f172a;
}

body.night-mode .login-card {
    background-color: #1e293b;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

body.night-mode .form-side {
    background-color: #1e293b;
}

body.night-mode .image-side {
    background-color: #0f172a;
}

body.night-mode .logo-text {
    color: #60a5fa;
}

body.night-mode .logo-icon {
    background-color: #3b82f6;
    color: #ffffff;
}

body.night-mode .header-text h1 {
    color: #ffffff;
}

body.night-mode .header-text p {
    color: #cbd5e1;
}

body.night-mode .input-wrapper label {
    color: #e2e8f0;
}

body.night-mode .input-group input {
    background-color: #334155;
    border-color: #475569;
    color: #ffffff;
}

body.night-mode .input-group input::placeholder {
    color: #94a3b8;
}

body.night-mode .input-icon {
    color: #94a3b8;
}

body.night-mode .btn-submit {
    background-color: #3b82f6;
    color: #ffffff;
}

body.night-mode .btn-submit:hover {
    background-color: #2563eb;
}

body.night-mode .form-footer p {
    color: #94a3b8;
}

/* Toggle Modo Noturno no Login */
.night-mode-toggle-login {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 45px;
    height: 45px;
    background-color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    transition: all 0.3s;
}

.night-mode-toggle-login:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

.night-mode-toggle-login i {
    color: #4361ee;
    font-size: 20px;
    transition: color 0.3s;
}

body.night-mode .night-mode-toggle-login {
    background-color: #334155;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

body.night-mode .night-mode-toggle-login:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

body.night-mode .night-mode-toggle-login i {
    color: #fbbf24;
}

/* Melhorias gerais modo noturno */
body.night-mode .section-title,
body.night-mode .page-title {
    color: #ffffff;
}

body.night-mode .welcome-title,
body.night-mode .welcome-text {
    color: #ffffff;
}

body.night-mode .welcome-text {
    color: #cbd5e1;
}

body.night-mode .status-badge,
body.night-mode .badge-success,
body.night-mode .badge-green {
    color: #ffffff;
}

body.night-mode .action-link {
    color: #60a5fa;
}

body.night-mode .action-link:hover {
    color: #93c5fd;
}

body.night-mode .nav-center-links a {
    color: #cbd5e1;
}

body.night-mode .nav-center-links a:hover,
body.night-mode .nav-center-links a.active-purple {
    color: #ffffff;
}

body.night-mode .filter-tab {
    color: #cbd5e1;
    border-color: #475569;
}

body.night-mode .filter-tab.active {
    background-color: #334155;
    color: #ffffff;
    border-color: #3b82f6;
}

body.night-mode .page-num,
body.night-mode .page-control {
    color: #cbd5e1;
}

body.night-mode .page-num.active {
    background-color: #3b82f6;
    color: #ffffff;
}

/* Chat - Modo Noturno */
body.night-mode .chat-layout-container {
    background-color: #1e293b;
    border-color: #334155;
}

body.night-mode .chat-sidebar {
    background-color: #1e293b;
    border-right-color: #334155;
}

body.night-mode .sidebar-title {
    color: #ffffff;
    border-bottom-color: #334155;
}

body.night-mode .conv-item {
    color: #e2e8f0;
}

body.night-mode .conv-item:hover {
    background-color: #334155;
}

body.night-mode .conv-item.active {
    background-color: #334155;
    border-left-color: #3b82f6;
}

body.night-mode .conv-name {
    color: #e2e8f0;
}

body.night-mode .chat-window-area {
    background-color: #1e293b;
}

body.night-mode .chat-header {
    background-color: #1e293b;
    border-bottom-color: #334155;
    color: #ffffff;
}

body.night-mode .chat-header h3 {
    color: #ffffff;
}

body.night-mode .chat-messages {
    background-color: #0f172a;
}

body.night-mode .message {
    color: #e2e8f0;
}

body.night-mode .message.received {
    background-color: #334155;
}

body.night-mode .message.sent {
    background-color: #1e40af;
}

body.night-mode .time,
body.night-mode .msg-time {
    color: #94a3b8;
}

body.night-mode .chat-input-wrapper {
    background-color: #1e293b;
    border-top-color: #334155;
}

body.night-mode .chat-input-wrapper input {
    background-color: #334155;
    border-color: #475569;
    color: #ffffff;
}

body.night-mode .btn-send {
    background-color: #3b82f6;
    color: #ffffff;
}

body.night-mode .btn-send:hover {
    background-color: #2563eb;
}

/* Relatório - Modo Noturno */
body.night-mode .report-layout-container {
    background-color: #1e293b;
}

body.night-mode .users-sidebar {
    background-color: #1e293b;
    border-right-color: #334155;
}

body.night-mode .user-item {
    color: #e2e8f0;
}

body.night-mode .user-item:hover {
    background-color: #334155;
}

body.night-mode .user-item.active {
    background-color: #334155;
}

body.night-mode .report-view-area {
    background-color: #1e293b;
}

body.night-mode .report-header-user {
    color: #ffffff;
}

body.night-mode .report-header-user h2 {
    color: #ffffff;
}

body.night-mode .report-body {
    background-color: #1e293b;
    color: #e2e8f0;
}

body.night-mode .report-title {
    color: #ffffff;
}

body.night-mode .evaluation-section {
    background-color: #1e293b;
    border-top-color: #334155;
}

body.night-mode .evaluation-section h3 {
    color: #ffffff;
}

body.night-mode .star-rating .fa-star {
    color: #fbbf24;
}

body.night-mode .feedback-input {
    background-color: #334155;
    border-color: #475569;
    color: #ffffff;
}

body.night-mode .btn-submit-eval {
    background-color: #3b82f6;
    color: #ffffff;
}

body.night-mode .btn-submit-eval:hover {
    background-color: #2563eb;
}

/* Dashboard Professor - Modo Noturno */
body.night-mode .management-section {
    background-color: #1e293b;
}

body.night-mode .tab-btn {
    color: #e2e8f0;
    border-color: #475569;
}

body.night-mode .tab-btn.active {
    background-color: #334155;
    color: #ffffff;
}

body.night-mode .form-area {
    background-color: #1e293b;
}

body.night-mode .form-area h3 {
    color: #ffffff;
}

body.night-mode .add-student-form select {
    background-color: #334155;
    border-color: #475569;
    color: #ffffff;
}

body.night-mode .student-list-section {
    background-color: #1e293b;
}

body.night-mode .list-title {
    color: #e2e8f0;
}

body.night-mode .student-list {
    background-color: #1e293b;
}

body.night-mode .student-row {
    background-color: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}

body.night-mode .student-email {
    color: #e2e8f0;
}

body.night-mode .course-badge {
    color: #ffffff;
}

/* Dashboard Aluno - Modo Noturno */
body.night-mode .stage-card,
body.night-mode .report-card {
    background-color: #1e293b;
    border-color: #334155;
}

body.night-mode .card-header-icon h3 {
    color: #ffffff;
}

body.night-mode .card-subtitle {
    color: #cbd5e1;
}

body.night-mode .stage-info-list .info-item {
    color: #e2e8f0;
}

body.night-mode .status-area {
    color: #e2e8f0;
}

body.night-mode .report-form label {
    color: #e2e8f0;
}

body.night-mode .report-form textarea {
    background-color: #334155;
    border-color: #475569;
    color: #ffffff;
}

body.night-mode .feedback-list {
    background-color: #1e293b;
}

body.night-mode .feedback-item {
    background-color: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}

body.night-mode .feedback-meta {
    color: #cbd5e1;
}

body.night-mode .date {
    color: #94a3b8;
}

body.night-mode .resources-list {
    background-color: #1e293b;
}

body.night-mode .resource-link {
    color: #60a5fa;
}

body.night-mode .resource-link:hover {
    color: #93c5fd;
}

body.night-mode .chat-container-card {
    background-color: #1e293b;
    border-color: #334155;
}

body.night-mode .chat-window {
    background-color: #0f172a;
}

body.night-mode .chat-input-area {
    background-color: #1e293b;
    border-top-color: #334155;
}

body.night-mode .chat-input-area input {
    background-color: #334155;
    border-color: #475569;
    color: #ffffff;
}

/* Estágios - Modo Noturno */
body.night-mode .top-actions {
    background-color: transparent;
}

body.night-mode .filter-bar {
    background-color: transparent;
}

body.night-mode .pagination {
    background-color: transparent;
}

body.night-mode .pagination a {
    color: #cbd5e1;
}

body.night-mode .pagination a:hover {
    color: #ffffff;
}

/* Admin Dashboard - Modo Noturno */
body.night-mode .table-header-group {
    background-color: transparent;
}

body.night-mode .table-header-group h2 {
    color: #ffffff;
}

body.night-mode .actions-cell {
    color: #e2e8f0;
}

body.night-mode .icon-edit,
body.night-mode .icon-delete {
    color: #cbd5e1;
}

body.night-mode .icon-edit:hover {
    color: #60a5fa;
}

body.night-mode .icon-delete:hover {
    color: #ef4444;
}

/* Elementos gerais que podem estar brancos */
body.night-mode h1,
body.night-mode h2,
body.night-mode h3,
body.night-mode h4,
body.night-mode h5,
body.night-mode h6 {
    color: #ffffff;
}

body.night-mode p,
body.night-mode span:not(.badge):not(.status-badge) {
    color: #e2e8f0;
}

body.night-mode .info-text {
    color: #94a3b8;
}

body.night-mode select option {
    background-color: #334155;
    color: #ffffff;
}

body.night-mode .badge-gray {
    background-color: #475569;
    color: #ffffff;
}

body.night-mode .badge-blue {
    background-color: #3b82f6;
    color: #ffffff;
}

body.night-mode .badge-yellow,
body.night-mode .badge-warning {
    background-color: #f59e0b;
    color: #ffffff;
}

body.night-mode .badge-red,
body.night-mode .badge-danger {
    background-color: #ef4444;
    color: #ffffff;
}

body.night-mode .badge-green,
body.night-mode .badge-success {
    background-color: #10b981;
    color: #ffffff;
}
</style>
`;

// Adiciona CSS ao head se não existir
if (!document.getElementById('modal-styles')) {
    document.head.insertAdjacentHTML('beforeend', modalCSS);
}

// Dashboard Admin - Gerenciamento de Professores
document.addEventListener('DOMContentLoaded', () => {
    
    // Login
    const loginForm = document.querySelector('form');
    if (loginForm && window.location.pathname.includes('login.html')) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = document.getElementById('email');
            const emailValue = emailInput.value.toLowerCase();
            
            if (emailValue.includes('admin')) {
                window.location.href = 'dashboard.html';
            } else if (emailValue.includes('aluno')) {
                window.location.href = 'dashboard-aluno.html';
            } else if (emailValue.includes('prof')) {
                window.location.href = 'dashboard-professor.html';
            } else {
                window.location.href = 'dashboard-aluno.html';
            }
        });
    }

    // Dashboard Admin - Botões de ação
    document.querySelectorAll('.icon-edit').forEach(icon => {
        icon.onclick = function() {
            const row = this.closest('tr');
            const id = row.querySelector('td').textContent;
            const nome = row.querySelectorAll('td')[1].textContent;
            const email = row.querySelectorAll('td')[2].textContent;
            const cursos = row.querySelectorAll('td')[3].textContent;
            
            criarModal('Editar Professor', `
                <form id="edit-form">
                    <div style="margin-bottom: 15px;">
                        <label>ID:</label>
                        <input type="text" value="${id}" readonly style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Nome:</label>
                        <input type="text" value="${nome}" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Email:</label>
                        <input type="email" value="${email}" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Cursos:</label>
                        <input type="text" value="${cursos}" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                    </div>
                </form>
            `, [
                { texto: 'Cancelar', acao: 'fechar', classe: 'btn-secondary' },
                { texto: 'Guardar', acao: () => {
                    alert('Alterações guardadas com sucesso!');
                    document.getElementById('modal-overlay').remove();
                }, classe: 'btn-primary' }
            ]);
        };
    });

    document.querySelectorAll('.icon-delete').forEach(icon => {
        icon.onclick = function() {
            const row = this.closest('tr');
            const nome = row.querySelectorAll('td')[1].textContent;
            
            criarModal('Confirmar Eliminação', `
                <p>Tem a certeza que deseja eliminar <strong>${nome}</strong>?</p>
                <p style="color: #dc2626; margin-top: 10px;">Esta ação é irreversível.</p>
            `, [
                { texto: 'Cancelar', acao: 'fechar', classe: 'btn-secondary' },
                { texto: 'Eliminar', acao: () => {
                    row.remove();
                    alert('Item eliminado com sucesso!');
                    document.getElementById('modal-overlay').remove();
                }, classe: 'btn-danger' }
            ]);
        };
    });

    // Botões "Adicionar Professor" e "Criar Curso"
    document.querySelectorAll('.btn-primary').forEach(btn => {
        if (btn.textContent.includes('Adicionar Professor') || btn.textContent.includes('Criar Curso')) {
            btn.onclick = function(e) {
                e.preventDefault();
                const isProfessor = btn.textContent.includes('Professor');
                
                criarModal(
                    isProfessor ? 'Adicionar Professor' : 'Criar Curso',
                    `
                    <form id="add-form">
                        ${isProfessor ? `
                            <div style="margin-bottom: 15px;">
                                <label>Nome:</label>
                                <input type="text" required style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label>Email:</label>
                                <input type="email" required style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label>Cursos Ministrados:</label>
                                <input type="text" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                            </div>
                        ` : `
                            <div style="margin-bottom: 15px;">
                                <label>Nome do Curso:</label>
                                <input type="text" required style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label>Departamento:</label>
                                <input type="text" required style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label>Professor:</label>
                                <input type="text" required style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                            </div>
                        `}
                    </form>
                    `,
                    [
                        { texto: 'Cancelar', acao: 'fechar', classe: 'btn-secondary' },
                        { texto: 'Guardar', acao: () => {
                            alert('Item adicionado com sucesso!');
                            document.getElementById('modal-overlay').remove();
                        }, classe: 'btn-primary' }
                    ]
                );
            };
        }
    });

    // Estagios - Ver Detalhes
    document.querySelectorAll('.action-link').forEach(link => {
        if (link.textContent.includes('Ver Detalhes')) {
            link.onclick = function(e) {
                e.preventDefault();
                const row = this.closest('tr');
                const nome = row.querySelectorAll('td')[0].textContent;
                const curso = row.querySelectorAll('td')[1].textContent;
                const area = row.querySelectorAll('td')[2].textContent;
                const status = row.querySelectorAll('td')[3].textContent;
                
                criarModal('Detalhes do Estágio', `
                    <div style="line-height: 1.8;">
                        <p><strong>Nome do Aluno:</strong> ${nome}</p>
                        <p><strong>Curso:</strong> ${curso}</p>
                        <p><strong>Área de Estágio:</strong> ${area}</p>
                        <p><strong>Estado:</strong> ${status}</p>
                        <hr style="margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;">
                        <p><strong>Data de Início:</strong> 01/09/2023</p>
                        <p><strong>Data de Fim:</strong> 31/12/2023</p>
                        <p><strong>Horas Semanais:</strong> 20 horas</p>
                        <p><strong>Empresa:</strong> Empresa Exemplo Ltda.</p>
                        <p><strong>Supervisor:</strong> João Silva</p>
                    </div>
                `);
            };
        }
        
        if (link.textContent.includes('Editar')) {
            link.onclick = function(e) {
                e.preventDefault();
                const row = this.closest('tr');
                const nome = row.querySelectorAll('td')[0].textContent;
                const curso = row.querySelectorAll('td')[1].textContent;
                const area = row.querySelectorAll('td')[2].textContent;
                
                criarModal('Editar Estágio', `
                    <form id="edit-estagio-form">
                        <div style="margin-bottom: 15px;">
                            <label>Nome do Aluno:</label>
                            <input type="text" value="${nome}" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Curso:</label>
                            <input type="text" value="${curso}" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Área de Estágio:</label>
                            <input type="text" value="${area}" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                        </div>
                    </form>
                `, [
                    { texto: 'Cancelar', acao: 'fechar', classe: 'btn-secondary' },
                    { texto: 'Guardar', acao: () => {
                        alert('Estágio atualizado com sucesso!');
                        document.getElementById('modal-overlay').remove();
                    }, classe: 'btn-primary' }
                ]);
            };
        }
    });

    // Filtros de Estágios
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.onclick = function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filtro = this.textContent.trim();
            const linhas = document.querySelectorAll('tbody tr');
            
            linhas.forEach(linha => {
                const statusBadge = linha.querySelector('.status-badge');
                if (statusBadge) {
                    const status = statusBadge.textContent.trim();
                    let mostrar = false;
                    
                    if (filtro === 'Todos') mostrar = true;
                    else if (filtro === 'Aceites' && status === 'Aceite') mostrar = true;
                    else if (filtro === 'Pendentes' && status === 'Pendente') mostrar = true;
                    else if (filtro === 'Não Aptos' && status === 'Não Apto') mostrar = true;
                    
                    linha.style.display = mostrar ? '' : 'none';
                }
            });
        };
    });

    // Tabs no Dashboard Professor
    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.onclick = function() {
            document.querySelectorAll('.tab-btn').forEach(t => {
                t.classList.remove('active');
                t.classList.add('outline');
            });
            this.classList.add('active');
            this.classList.remove('outline');
        };
    });

    // Adicionar Aluno
    document.querySelectorAll('button[type="button"]').forEach(btn => {
        if (btn.textContent.includes('Adicionar') && btn.closest('form')) {
            btn.onclick = function(e) {
                e.preventDefault();
                const form = this.closest('form');
                const email = form.querySelector('input[type="email"]').value;
                const curso = form.querySelector('select')?.value || 'TSPI';
                
                if (email) {
                    alert(`Aluno ${email} adicionado com sucesso ao curso ${curso}!`);
                    form.querySelector('input[type="email"]').value = '';
                } else {
                    alert('Por favor, preencha o email do aluno.');
                }
            };
        }
    });



    // Avaliar Relatório (Professor)
    document.querySelectorAll('.btn-submit-eval').forEach(btn => {
        btn.onclick = function(e) {
            e.preventDefault();
            const textarea = document.querySelector('.feedback-input');
            const feedback = textarea.value;
            const estrelas = document.querySelectorAll('.star-rating .filled').length;
            
            alert(`Avaliação enviada com sucesso!\n\nNota: ${estrelas}/5\nFeedback: ${feedback || 'Sem feedback adicional'}`);
        };
    });

    // Sistema de estrelas
    document.querySelectorAll('.star-rating i').forEach((star, index, stars) => {
        star.onclick = function() {
            stars.forEach((s, i) => {
                if (i <= index) {
                    s.classList.add('filled');
                    s.classList.remove('fa-regular');
                    s.classList.add('fa-solid');
                } else {
                    s.classList.remove('filled');
                    s.classList.add('fa-regular');
                    s.classList.remove('fa-solid');
                }
            });
        };
    });

    // --- LÓGICA DO CHAT (Real) ---
    const chatWindow = document.getElementById('chat-window');
    if (chatWindow && typeof myId !== 'undefined') {
        let currentChatUserId = null;
        let pollingInterval = null;
        
        const contactsListEl = document.getElementById('contacts-list');
        const emptyStateEl = document.getElementById('empty-state');
        const activeChatContentEl = document.getElementById('active-chat-content');
        const headerAvatarEl = document.getElementById('header-avatar');
        const headerNameEl = document.getElementById('header-name');
        const messagesContainerEl = document.getElementById('messages-container');
        const messageInputEl = document.getElementById('message-input');
        const sendBtnEl = document.getElementById('send-btn');

        // 1. Carregar Contatos
        async function loadContacts() {
            try {
                const res = await fetch('chat.php?api=get_contacts');
                const contacts = await res.json();
                
                contactsListEl.innerHTML = '';
                
                if (contacts.length === 0) {
                    contactsListEl.innerHTML = `
                        <div class="empty-state-container" style="padding: 20px; text-align: center;">
                            <i class="fa-solid fa-user-slash" style="font-size: 24px; color: #9ca3af; margin-bottom: 10px;"></i>
                            <p style="font-size: 14px; color: #6b7280;">Nenhum contacto encontrado.</p>
                        </div>
                    `;
                    return;
                }

                contacts.forEach(user => {
                    const div = document.createElement('div');
                    div.className = `conv-item ${currentChatUserId == user.id ? 'active' : ''}`;
                    div.onclick = () => openChat(user);
                    div.innerHTML = `
                        <img src="${user.avatar_url}" alt="Avatar">
                        <span class="conv-name">${user.nome}</span>
                    `;
                    contactsListEl.appendChild(div);
                });
            } catch (err) {
                console.error("Erro ao carregar contatos:", err);
                contactsListEl.innerHTML = `
                    <div class="error-state" style="padding: 20px; text-align: center; color: #ef4444;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <p>Erro ao carregar contactos.</p>
                    </div>
                `;
            }
        }

        // 2. Abrir Conversa
        window.openChat = function(user) { // Expose to global for onclick if needed, though closure works here
            currentChatUserId = user.id;
            
            // UI Update
            emptyStateEl.style.display = 'none';
            activeChatContentEl.style.display = 'flex';
            headerNameEl.textContent = user.nome;
            headerAvatarEl.src = user.avatar_url;
            
            // Update Active Class
            document.querySelectorAll('.conv-item').forEach(el => el.classList.remove('active'));
            // Re-render to show active state
            loadContacts(); 

            // Load Messages immediately
            loadMessages();
            
            // Start Polling
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(loadMessages, 3000); // Check every 3s
            
            // Focus Input
            messageInputEl.focus();
        };

        // 3. Carregar Mensagens
        async function loadMessages() {
            if (!currentChatUserId) return;

            try {
                const res = await fetch(`chat.php?api=get_messages&user_id=${currentChatUserId}`);
                const messages = await res.json();
                
                messagesContainerEl.innerHTML = '';
                
                if (messages.length === 0) {
                    messagesContainerEl.innerHTML = '<p style="text-align: center; color: #9ca3af; margin-top: 20px;">Nenhuma mensagem. Comece a conversa!</p>';
                } else {
                    messages.forEach(msg => {
                        const isMe = msg.remetente_id == myId;
                        const div = document.createElement('div');
                        div.className = `message ${isMe ? 'sent' : 'received'}`;
                        div.innerHTML = `
                            <p>${msg.conteudo}</p>
                            <span class="time">${msg.hora}</span>
                        `;
                        messagesContainerEl.appendChild(div);
                    });
                }

                messagesContainerEl.scrollTop = messagesContainerEl.scrollHeight;

            } catch (err) {
                console.error("Erro ao carregar mensagens:", err);
            }
        }

        // 4. Enviar Mensagem
        async function sendMessage() {
            const text = messageInputEl.value.trim();
            if (!text || !currentChatUserId) return;

            messageInputEl.value = '';

            try {
                const res = await fetch('chat.php?api=send_message', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        destinatario_id: currentChatUserId,
                        conteudo: text
                    })
                });
                
                const data = await res.json();
                if (data.status === 'success') {
                    loadMessages(); 
                } else {
                    alert('Erro ao enviar: ' + (data.error || 'Desconhecido'));
                }
            } catch (err) {
                console.error("Erro no envio:", err);
                alert("Falha de conexão.");
            }
        }

        // Event Listeners
        if (sendBtnEl) sendBtnEl.onclick = sendMessage;
        if (messageInputEl) {
            messageInputEl.onkeypress = (e) => {
                if (e.key === 'Enter') sendMessage();
            };
        }

        // Inicialização
        loadContacts();
    }


    // Paginação
    document.querySelectorAll('.page-num, .page-control').forEach(link => {
        link.onclick = function(e) {
            e.preventDefault();
            if (this.classList.contains('page-num')) {
                document.querySelectorAll('.page-num').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
            }
        };
    });

    // Settings Dropdown e Modo Noturno
    const settingsIcons = document.querySelectorAll('.fa-gear');
    settingsIcons.forEach(icon => {
        // Envolve o ícone num container
        const iconParent = icon.parentElement;
        if (!iconParent.classList.contains('settings-dropdown-container')) {
            const settingsContainer = document.createElement('div');
            settingsContainer.className = 'settings-dropdown-container';
            settingsContainer.style.position = 'relative';
            settingsContainer.style.display = 'inline-block';
            
            iconParent.insertBefore(settingsContainer, icon);
            settingsContainer.appendChild(icon);
            
            // Cria dropdown
            const dropdown = document.createElement('div');
            dropdown.className = 'settings-dropdown';
            dropdown.innerHTML = `
                <div class="dropdown-item night-mode-toggle">
                    <i class="fa-solid fa-moon"></i>
                    <span>Modo Noturno</span>
                    <i class="fa-solid fa-toggle-off night-mode-icon"></i>
                </div>
            `;
            settingsContainer.appendChild(dropdown);
            
            // Atualiza ícone do toggle baseado no estado atual
            const updateToggleIcon = () => {
                const nightModeIcon = dropdown.querySelector('.night-mode-icon');
                const isActive = document.body.classList.contains('night-mode');
                if (isActive) {
                    nightModeIcon.classList.remove('fa-toggle-off');
                    nightModeIcon.classList.add('fa-toggle-on');
                } else {
                    nightModeIcon.classList.remove('fa-toggle-on');
                    nightModeIcon.classList.add('fa-toggle-off');
                }
            };
            
            // Toggle dropdown
            icon.onclick = function(e) {
                e.stopPropagation();
                const isOpen = dropdown.style.display === 'block';
                document.querySelectorAll('.settings-dropdown').forEach(d => d.style.display = 'none');
                dropdown.style.display = isOpen ? 'none' : 'block';
                updateToggleIcon();
            };
            
            // Toggle modo noturno
            const nightModeToggle = dropdown.querySelector('.night-mode-toggle');
            nightModeToggle.onclick = function(e) {
                e.stopPropagation();
                const isActive = document.body.classList.contains('night-mode');
                
                if (isActive) {
                    document.body.classList.remove('night-mode');
                    localStorage.setItem('nightMode', 'false');
                } else {
                    document.body.classList.add('night-mode');
                    localStorage.setItem('nightMode', 'true');
                }
                updateToggleIcon();
            };
        }
    });
    
    // Aplicar modo noturno ao carregar se estiver ativo
    if (localStorage.getItem('nightMode') === 'true') {
        document.body.classList.add('night-mode');
    }
    
    // Fechar dropdowns ao clicar fora
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.settings-dropdown-container')) {
            document.querySelectorAll('.settings-dropdown').forEach(d => d.style.display = 'none');
        }
    });
    
    // Toggle modo noturno no login
    const loginNightModeToggle = document.getElementById('login-night-mode-icon');
    if (loginNightModeToggle) {
        const updateLoginIcon = () => {
            const isActive = document.body.classList.contains('night-mode');
            if (isActive) {
                loginNightModeToggle.classList.remove('fa-moon');
                loginNightModeToggle.classList.add('fa-sun');
            } else {
                loginNightModeToggle.classList.remove('fa-sun');
                loginNightModeToggle.classList.add('fa-moon');
            }
        };
        
        // Atualiza ícone ao carregar
        updateLoginIcon();
        
        loginNightModeToggle.onclick = function() {
            const isActive = document.body.classList.contains('night-mode');
            
            if (isActive) {
                document.body.classList.remove('night-mode');
                localStorage.setItem('nightMode', 'false');
            } else {
                document.body.classList.add('night-mode');
                localStorage.setItem('nightMode', 'true');
            }
            updateLoginIcon();
        };
    }
    
    // Sistema de Notificações no Bell (Removido)
    /*
    const bellIcons = document.querySelectorAll('.fa-bell');
    bellIcons.forEach(bell => {
        // ... (código de notificações removido)
    });
    */

});
