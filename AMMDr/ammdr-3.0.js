$(document).ready(function() {
            // Обработка кликов по ссылкам в меню
            $('nav').on('click', 'a[data-md]', function(e) {
                e.preventDefault();
                var mdFile = $(this).data('md');
                
                // Показываем индикатор загрузки
                $('.loading').fadeIn();
                
                // Проверяем, существует ли уже элемент zero-md
                var zeroMdElement = $('zero-md');
                
                if (zeroMdElement.length === 0) {
                    // Если элемента нет, создаем его
                    $('.content-wrapper').html('<zero-md src="' + mdFile + '"></zero-md>');
                } else {
                    // Если элемент уже есть, просто обновляем src
                    zeroMdElement.attr('src', mdFile);
                }
                
                // Обновляем URL без перезагрузки страницы
                history.pushState(null, null, '?md=' + encodeURIComponent(mdFile));
                
                // На мобильных устройствах закрываем меню после выбора файла
                if ($(window).width() <= 600) {
                    $('#main-nav').removeClass('active');
                    $('.mobile-menu-btn').removeClass('active');
                }
                
                // Скрываем индикатор загрузки
                $('.loading').fadeOut();
            });
            
            // Обработка нажатия кнопки "назад" в браузере
            window.onpopstate = function(event) {
                if (location.search.includes('md=')) {
                    var mdFile = location.search.split('md=')[1].split('&')[0];
                    $('a[data-md="' + decodeURIComponent(mdFile) + '"]').click();
                } else {
                    $('.content-wrapper').html('<h1>Добро пожаловать!</h1><p>Выберите документ из меню слева.</p>');
                }
            };
            
            // Добавляем обработчики кликов для папок
            $('.folder-name').on('click', function() {
                $(this).parent().toggleClass('expanded');
            });
            
            // Раскрываем папку, если в ней выбран текущий документ
            if (location.search.includes('md=')) {
                var mdFile = location.search.split('md=')[1].split('&')[0];
                $('a[data-md="' + decodeURIComponent(mdFile) + '"]').each(function() {
                    $(this).parents('.folder').addClass('expanded');
                });
            }
			
			// Меню
            $('.menu-btn').click(function() {
                //$(this).toggleClass('active');
				//$('.mobile-menu-btn').removeClass('active');
				//$('.mobile-menu-btn').display('none');
                $('#main-nav').toggleClass('active');
            });
            
            // Мобильное меню
            $('.mobile-menu-btn').click(function() {
                //$(this).toggleClass('active');
                $('#main-nav').toggleClass('active');
            });
            
            // При клике на файл
            $('.file a').click(function() {
                // Удаляем active у всех файлов
                $('.file a').removeClass('active');
                // Добавляем active к текущему
                $(this).addClass('active');
            });

// Функция для Перепривязки обработчиков
function bindFolderHandlers() {
    $('.folder-name').off('click').on('click', function() {
        $(this).parent().toggleClass('expanded');
    });
}
			
	// Обработка кнопок представления
    $('.nav-btn[data-view]').on('click', function() {
        const viewType = $(this).data('view');
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: { 'v': viewType },
            success: function(response) {
                $('#menu-container').html(response);
				bindFolderHandlers(); // Перепривязываем обработчики
			},
            error: function() {
                alert('Ошибка при загрузке данных');
            }
        });
    });

    // Обработка кнопки сканирования
    $('#scan-btn').on('click', function() {
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: { 'scan': 1 },
            success: function(response) {
                $('#menu-container').html(response);
				bindFolderHandlers(); // Перепривязываем обработчики
            },
            error: function() {
                alert('Ошибка при сканировании');
            }
        });
    });
        });

	//Скрипт поиска слов в массиве
$(document).ready(function() {
    // Таймер для задержки запроса
    var searchTimer;
    
    $('#search').on('input', function() {
        var query = $(this).val().trim();
        
        // Очищаем предыдущий таймер
        clearTimeout(searchTimer);
        
        // Запускаем новый таймер только если введено 3+ символа
        if(query.length >= 2) {
            searchTimer = setTimeout(function() {
                performSearch(query);
            }, 300); // Задержка 300мс после окончания ввода
        } else if(query.length === 0) {
            // Если поле очищено - показываем полное меню
            performSearch('');
        }
    });
    
    function performSearch(query) {
        $.ajax({
            type: 'POST',
            url: window.location.href, // Отправляем на текущий URL
            data: { search: query },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            },
            success: function(response) {
                $('#menu-container').html(response);
                bindFolderHandlers1(); // Перепривязываем обработчики
            },
            error: function() {
                console.error('Search error');
            }
        });
    }
    // Функция для Перепривязки обработчиков
function bindFolderHandlers1() {
    $('.folder-name').off('click').on('click', function() {
        $(this).parent().toggleClass('expanded');
    });
}
    // Инициализация при загрузке
    performSearch('');
});

$(document).ready(function() {
    // Обработка кнопки скрытия/показа панели
    $('#toggle-nav').on('click', function() {
        $('#main-nav').toggleClass('active');
    });
});
