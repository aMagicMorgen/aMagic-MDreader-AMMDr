/*
Этот код обеспечит всю необходимую функциональность в более организованном и поддерживаемом виде.
Как использовать:
Подключите его в HTML
<script src="ammdr.js"></script>
*/

/**
 * AMMDr - aMagic Markdown Reader
 * JavaScript модуль для работы с интерфейсом
 * Версия 3.0
 */

class AMMDrUI {
    constructor() {
        this.currentSearchQuery = '';
        this.currentView = 'tree';
        this.initElements();
        this.bindEvents();
        this.initState();
    }

    // Инициализация DOM элементов
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

    // Привязка обработчиков событий
    bindEvents() {
        // Клики по ссылкам в меню
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

        // Обработка навигации по истории
        window.onpopstate = () => this.handlePopState();
    }

    // Инициализация начального состояния
    initState() {
        // Раскрываем папки для текущего файла
        if (location.search.includes('md=')) {
            const mdFile = decodeURIComponent(location.search.split('md=')[1].split('&')[0]);
            $(`a[data-md="${mdFile}"]`).each((i, el) => {
                $(el).addClass('active').parents('.folder').addClass('expanded');
            });
        }
    }

    // Обработка клика по файлу
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

    // Загрузка и отображение Markdown файла
    loadMarkdownFile(mdFile) {
        let $zeroMd = $('zero-md');
        
        if ($zeroMd.length === 0) {
            this.$contentWrapper.html(`<zero-md src="${mdFile}"></zero-md>`);
        } else {
            $zeroMd.attr('src', mdFile);
        }
    }

    // Обновление URL без перезагрузки страницы
    updateUrl(mdFile) {
        history.pushState(null, null, `?md=${encodeURIComponent(mdFile)}`);
    }

    // Установка активного файла в меню
    setActiveFile(element) {
        $('.file a').removeClass('active');
        $(element).addClass('active');
    }

    // Обработка изменения вида меню
    handleViewChange(e) {
        this.currentView = $(e.currentTarget).data('view');
        this.$navBtns.removeClass('active');
        $(e.currentTarget).addClass('active');
        this.updateMenu();
    }

    // Обработка сканирования директорий
    handleScan() {
        this.showLoading();
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: { 'scan': 1 },
            success: (response) => {
                this.$menuContainer.html(response);
                this.bindFolderHandlers();
                this.hideLoading();
            },
            error: () => {
                alert('Ошибка при сканировании');
                this.hideLoading();
            }
        });
    }

    // Обработка поискового запроса
    handleSearchInput() {
        clearTimeout(this.searchTimer);
        this.currentSearchQuery = this.$search.val().trim();
        
        if (this.currentSearchQuery.length >= 2) {
            this.searchTimer = setTimeout(() => this.updateMenu(), 300);
        } else if (this.currentSearchQuery.length === 0) {
            this.updateMenu();
        }
    }

    // Обновление меню (с учетом поиска и вида)
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
                this.$menuContainer.html(response);
                this.bindFolderHandlers();
                
                // Восстанавливаем активный файл после обновления
                if (location.search.includes('md=')) {
                    const mdFile = decodeURIComponent(location.search.split('md=')[1].split('&')[0]);
                    $(`a[data-md="${mdFile}"]`).addClass('active');
                }
            },
            error: () => {
                console.error('Menu update error');
            }
        });
    }

    // Обработка навигации по истории
    handlePopState() {
        if (location.search.includes('md=')) {
            const mdFile = decodeURIComponent(location.search.split('md=')[1].split('&')[0]);
            $(`a[data-md="${mdFile}"]`).click();
        } else {
            this.$contentWrapper.html('<h1>Добро пожаловать!</h1><p>Выберите документ из меню слева.</p>');
            $('.file a').removeClass('active');
        }
    }

    // Переключение мобильного меню
    toggleMobileMenu(force) {
        this.$mainNav.toggleClass('active', force);
        this.$mobileMenuBtn.toggleClass('active', force);
    }

    // Привязка обработчиков для папок
    bindFolderHandlers() {
        $('.folder-name').off('click').on('click', function() {
            $(this).parent().toggleClass('expanded');
        });
    }

    // Проверка мобильного вида
    isMobileView() {
        return window.innerWidth <= 600;
    }

    // Показать индикатор загрузки
    showLoading() {
        this.$loading.fadeIn();
    }

    // Скрыть индикатор загрузки
    hideLoading() {
        this.$loading.fadeOut();
    }
}

// Инициализация при загрузке документа
$(document).ready(function() {
    new AMMDrUI();
});