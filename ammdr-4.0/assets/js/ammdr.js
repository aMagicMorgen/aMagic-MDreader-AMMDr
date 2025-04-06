/*
Этот код обеспечит всю необходимую функциональность в более организованном и поддерживаемом виде.
Как использовать:
Подключите его в HTML
<script src="ammdr.js"></script>
*/

/**
 * AMMDr - aMagic Markdown Reader
 * Версия 3.1
 */

class AMMDrUI {
    constructor() {
        this.currentSearchQuery = '';
        this.currentView = 'tree';
        this.initElements();
        this.bindEvents();
        this.initState();
        this.bindFolderHandlers(); // Инициализация обработчиков при загрузке
    }

    initElements() {
        this.$body = $('body');
        this.$mainNav = $('#main-nav');
        this.$menuContainer = $('#menu-container');
        this.$search = $('#search');
        this.$contentWrapper = $('.content-wrapper');
        this.$loading = $('.loading');
        this.$mobileMenuBtn = $('.mobile-menu-btn');
        this.$menuBtn = $('.menu-btn');
        this.$navBtns = $('.nav-btn[data-view]');
        this.$scanBtn = $('#scan-btn');
    }

    bindEvents() {
        // Клики по файлам
        this.$mainNav.on('click', 'a[data-md]', (e) => this.handleFileClick(e));

        // Кнопки переключения вида
        this.$navBtns.on('click', (e) => this.handleViewChange(e));

        // Кнопка сканирования
        this.$scanBtn.on('click', () => this.handleScan());

        // Поиск
        this.$search.on('input', () => this.handleSearchInput());

        // Мобильное меню
        this.$mobileMenuBtn.on('click', () => this.toggleMobileMenu());
        this.$menuBtn.on('click', () => this.toggleMobileMenu());

        // История браузера
        window.onpopstate = () => this.handlePopState();
    }

    initState() {
        // Активируем текущий вид
        $(`.nav-btn[data-view="${this.currentView}"]`).addClass('active');

        // Раскрываем папки для текущего файла
        if (location.search.includes('md=')) {
            const mdFile = decodeURIComponent(location.search.split('md=')[1].split('&')[0]);
            $(`a[data-md="${mdFile}"]`)
                .addClass('active')
                .parents('.folder')
                .addClass('expanded');
        }
    }

    // Новый метод: полная перезагрузка меню
    reloadMenu(html) {
        this.$menuContainer.html(html);
        this.bindFolderHandlers();
        
        // Восстанавливаем активный файл
        if (location.search.includes('md=')) {
            const mdFile = decodeURIComponent(location.search.split('md=')[1].split('&')[0]);
            $(`a[data-md="${mdFile}"]`).addClass('active');
        }
    }

    handleFileClick(e) {
        e.preventDefault();
        const mdFile = $(e.currentTarget).data('md');
        
        this.showLoading();
        this.loadMarkdownFile(mdFile);
        this.updateUrl(mdFile);
        this.setActiveFile(e.currentTarget);
        
        if (this.isMobileView()) {
            this.toggleMobileMenu(false);
        }
        
        this.hideLoading();
    }

    handleViewChange(e) {
        this.currentView = $(e.currentTarget).data('view');
        this.$navBtns.removeClass('active');
        $(e.currentTarget).addClass('active');
        this.updateMenu();
    }

    handleScan() {
        this.showLoading();
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: { 'scan': 1 },
            success: (response) => {
                this.reloadMenu(response); // Используем новый метод
                this.hideLoading();
            },
            error: () => {
                alert('Ошибка при сканировании');
                this.hideLoading();
            }
        });
    }

    handleSearchInput() {
        clearTimeout(this.searchTimer);
        this.currentSearchQuery = this.$search.val().trim();
        
        if (this.currentSearchQuery.length >= 2) {
            this.searchTimer = setTimeout(() => this.updateMenu(), 300);
        } else if (this.currentSearchQuery.length === 0) {
            this.updateMenu();
        }
    }

    updateMenu() {
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: { 
                'v': this.currentView,
                'search': this.currentSearchQuery 
            },
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            },
            success: (response) => {
                this.reloadMenu(response); // Используем новый метод
            },
            error: () => {
                console.error('Menu update error');
            }
        });
    }

    // Улучшенный обработчик папок
    bindFolderHandlers() {
        // Удаляем старые обработчики
        $('.folder-name').off('click');
        
        // Добавляем новые
        $('.folder-name').on('click', function() {
            const $folder = $(this).parent();
            $folder.toggleClass('expanded');
            
            // Сохраняем состояние папки в localStorage
            const folderId = $folder.find('> .folder-name').text().trim();
            if ($folder.hasClass('expanded')) {
                localStorage.setItem(`folder-${folderId}`, 'expanded');
            } else {
                localStorage.removeItem(`folder-${folderId}`);
            }
        });
        
        // Восстанавливаем раскрытые папки
        $('.folder').each(function() {
            const folderId = $(this).find('> .folder-name').text().trim();
            if (localStorage.getItem(`folder-${folderId}`)) {
                $(this).addClass('expanded');
            }
        });
    }

    // Остальные методы без изменений...
    loadMarkdownFile(mdFile) {
        let $zeroMd = $('zero-md');
        if ($zeroMd.length === 0) {
            this.$contentWrapper.html(`<zero-md src="${mdFile}"></zero-md>`);
        } else {
            $zeroMd.attr('src', mdFile);
        }
    }

    updateUrl(mdFile) {
        history.pushState(null, null, `?md=${encodeURIComponent(mdFile)}`);
    }

    setActiveFile(element) {
        $('.file a').removeClass('active');
        $(element).addClass('active');
    }

    toggleMobileMenu(force) {
        this.$mainNav.toggleClass('active', force);
        this.$mobileMenuBtn.toggleClass('active', force);
    }

    isMobileView() {
        return window.innerWidth <= 600;
    }

    showLoading() {
        this.$loading.fadeIn();
    }

    hideLoading() {
        this.$loading.fadeOut();
    }

    handlePopState() {
        if (location.search.includes('md=')) {
            const mdFile = decodeURIComponent(location.search.split('md=')[1].split('&')[0]);
            $(`a[data-md="${mdFile}"]`).click();
        } else {
            this.$contentWrapper.html('<h1>Добро пожаловать!</h1><p>Выберите документ из меню слева.</p>');
            $('.file a').removeClass('active');
        }
    }
}

// Инициализация при загрузке
$(document).ready(function() {
    // Несколько проверок для отладки
    if (typeof $ === 'undefined') {
        console.error('jQuery не загружен!');
        return;
    }
    
    try {
        new AMMDrUI();
    } catch (e) {
        console.error('Ошибка инициализации AMMDrUI:', e);
    }
});