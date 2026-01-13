{{-- Layout SIGEF com botão dentro do enquadramento --}}
<style>
    /* Esconder o título padrão do Filament */
    .fi-header-heading {
        display: none !important;
    }
    
    /* Esconder os breadcrumbs */
    .fi-breadcrumbs {
        display: none !important;
    }
    
    /* ========== SIDEBAR / MENU LATERAL ========== */
    /* Item ativo do menu */
    .fi-sidebar-item-active {
        background-color: #041842 !important;
    }
    .fi-sidebar-item-active .fi-sidebar-item-label {
        color: white !important;
    }
    .fi-sidebar-item-active .fi-sidebar-item-icon {
        color: white !important;
    }
    
    /* Hover nos itens do menu */
    .fi-sidebar-item:hover {
        background-color: rgba(4, 24, 66, 0.1) !important;
    }
    
    /* Grupo ativo do menu */
    .fi-sidebar-group-button[aria-expanded="true"] {
        color: #041842 !important;
    }
    
    /* Ícones do menu */
    .fi-sidebar-item-icon {
        color: #041842 !important;
    }
    .fi-sidebar-item-active .fi-sidebar-item-icon {
        color: white !important;
    }
    
    /* Container principal - SIGEF ficará acima da tabela */
    .sigef-full-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 3rem;
        margin-bottom: 0.5rem;
    }
    
    .sigef-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    /* Esconder o header padrão do Filament completamente */
    .fi-header {
        display: none !important;
    }
    
    /* Reduzir espaçamento da tabela */
    .fi-ta-ctn {
        margin-top: -1rem !important;
    }
    
    /* Reduzir espaçamento entre seções */
    .fi-widgets {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    .fi-wi-stats-overview {
        margin-top: 0 !important;
    }
    .fi-page-content > div {
        gap: 0.5rem !important;
    }
    .filament-widgets-container {
        margin-top: 0 !important;
    }
    
    /* Estilo do botão customizado */
    .sigef-create-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        background-color: #041842;
        color: white;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.5rem;
        text-decoration: none;
        transition: background-color 0.2s;
        gap: 0.5rem;
    }
    .sigef-create-btn:hover {
        background-color: #0a2d5c;
        color: white;
    }
    .sigef-create-btn svg {
        width: 1rem;
        height: 1rem;
    }
</style>

<div class="sigef-full-header">
    {{-- Lado Esquerdo: Ícone + Texto (dinâmico por página) --}}
    <div class="sigef-header-left">
        {{-- Container para o ícone --}}
        <div id="sigef-dynamic-icon" style="flex-shrink: 0; color: #041842; width: 60px; height: 60px; min-width: 60px; min-height: 60px;">
            {{-- Ícone padrão - será substituído pelo JS --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 60px !important; height: 60px !important; min-width: 60px !important; min-height: 60px !important;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
        </div>
        
        {{-- Texto dinâmico --}}
        <div>
            <h1 id="sigef-dynamic-title" style="color: #041842; font-size: 1.75rem; font-weight: 700; margin: 0; line-height: 1.2;">
                Listagem
            </h1>
            <p id="sigef-dynamic-description" style="color: #6b7280; font-size: 1rem; margin: 0; line-height: 1.4;">
                Gestão de registos
            </p>
        </div>
    </div>
    
    {{-- Lado Direito: Botão de criar --}}
    <div id="sigef-actions-placeholder"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Função para obter informações dinâmicas da página
    function updateDynamicHeader() {
        // Tentar obter o título da página do Filament
        const pageTitle = document.querySelector('.fi-header-heading');
        const titleEl = document.getElementById('sigef-dynamic-title');
        const descEl = document.getElementById('sigef-dynamic-description');
        const iconEl = document.getElementById('sigef-dynamic-icon');
        
        // Obter título do item ativo no menu
        const activeMenuItem = document.querySelector('.fi-sidebar-item-active .fi-sidebar-item-label');
        
        if (activeMenuItem && titleEl) {
            const title = activeMenuItem.textContent.trim();
            titleEl.textContent = title;
            
            // Definir descrições baseadas no título
            const descriptions = {
                'Painel de Controlo': 'Visão geral do sistema',
                'Anos Académicos': 'Gestão dos períodos letivos',
                'Tipos de Instituição': 'Classificação das instituições',
                'Proveniências': 'Origem dos candidatos e formandos',
                'Patentes': 'Gestão de patentes militares',
                'Candidatos': 'Gestão de candidatos ao ingresso',
                'Tipos De Recrutamento': 'Modalidades de recrutamento',
                'Provas De Selecção': 'Gestão das provas de seleção',
                'Mapas de Curso': 'Estrutura curricular dos cursos',
                'Fases de Curso': 'Etapas de formação',
                'Planos de Curso': 'Planificação curricular',
                'Cursos': 'Gestão de cursos de formação',
                'Disciplinas': 'Gestão de disciplinas',
                'Formandos': 'Gestão de formandos matriculados',
                'Formadores': 'Gestão de formadores',
                'Turmas': 'Gestão de turmas',
                'Avaliações': 'Gestão de avaliações',
                'Ausências': 'Registo de ausências',
                'Instituições': 'Gestão de instituições',
                'Utilizadores': 'Gestão de utilizadores do sistema'
            };
            
            descEl.textContent = descriptions[title] || 'Gestão de registos do sistema';
        }
        
        // Copiar o ícone do menu ativo - usando múltiplos seletores para Filament 4
        if (iconEl) {
            let activeIcon = null;
            
            // Método 1: Tentar encontrar pelo seletor de item ativo
            activeIcon = document.querySelector('.fi-sidebar-item-active .fi-sidebar-item-icon svg');
            
            // Método 2: Se não encontrou, procurar pelo href que corresponde à URL atual
            if (!activeIcon) {
                const currentUrl = window.location.href;
                const sidebarLinks = document.querySelectorAll('.fi-sidebar-item-btn, .fi-sidebar-item a');
                sidebarLinks.forEach(function(link) {
                    if (link.href === currentUrl) {
                        activeIcon = link.querySelector('.fi-sidebar-item-icon svg') || link.querySelector('svg.fi-icon');
                    }
                });
            }
            
            // Método 3: Procurar por item com aria-current="page"
            if (!activeIcon) {
                activeIcon = document.querySelector('[aria-current="page"] svg.fi-icon');
            }
            
            // Método 4: Procurar pelo item que contém o nome da página no texto
            if (!activeIcon && titleEl) {
                const pageTitle = titleEl.textContent.trim().toLowerCase();
                const allItems = document.querySelectorAll('.fi-sidebar-item');
                allItems.forEach(function(item) {
                    const label = item.querySelector('.fi-sidebar-item-label');
                    if (label && label.textContent.trim().toLowerCase() === pageTitle) {
                        activeIcon = item.querySelector('svg.fi-icon, svg.fi-sidebar-item-icon');
                    }
                });
            }
            
            if (activeIcon) {
                const clonedIcon = activeIcon.cloneNode(true);
                clonedIcon.setAttribute('width', '60');
                clonedIcon.setAttribute('height', '60');
                clonedIcon.style.cssText = 'width: 60px !important; height: 60px !important; min-width: 60px !important; min-height: 60px !important; color: #041842 !important; fill: currentColor !important;';
                // Remover classes que possam conflitar
                clonedIcon.classList.remove('fi-size-lg', 'fi-sidebar-item-icon', 'w-5', 'h-5', 'w-6', 'h-6');
                iconEl.innerHTML = '';
                iconEl.appendChild(clonedIcon);
            }
        }
    }
    
    function moveCreateButton() {
        const fiHeader = document.querySelector('.fi-header');
        const placeholder = document.getElementById('sigef-actions-placeholder');
        
        if (fiHeader && placeholder && placeholder.children.length === 0) {
            const createBtn = fiHeader.querySelector('a[href*="/create"]');
            if (createBtn) {
                const btnText = createBtn.textContent.trim();
                
                const newBtn = document.createElement('a');
                newBtn.href = createBtn.href;
                newBtn.className = 'sigef-create-btn';
                newBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    ${btnText}
                `;
                placeholder.appendChild(newBtn);
            }
        }
    }
    
    // Executar após o carregamento
    setTimeout(function() {
        updateDynamicHeader();
        moveCreateButton();
    }, 100);
    
    setTimeout(function() {
        updateDynamicHeader();
        moveCreateButton();
    }, 300);
    
    setTimeout(function() {
        updateDynamicHeader();
        moveCreateButton();
    }, 600);
});
</script>

