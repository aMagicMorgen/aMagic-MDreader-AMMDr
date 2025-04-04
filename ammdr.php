<?php
//ПОЛНОЕ НАЗВАНИЕ
$ammdr = 'AMMDreader - aMagic Markdown Reader';
//Короткое название для мобильного
$ammdr_short = 'AMMDr';

// Функция для рекурсивного сканирования директорий и поиска .md файлов
function scanDirectory($dir) {
    $result = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        
        if (is_dir($path)) {
            $result[$file] = scanDirectory($path);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'md') {
            $result[] = $file;
        }
    }
    
    return $result;
}

// Функция для генерации HTML-меню из структуры директорий
function generateMenu($structure, $basePath = '') {
    $html = '<ul class="nav-menu">';
    
    foreach ($structure as $key => $item) {
        if (is_array($item)) {
            // Это директория
            $html .= '<li class="folder">';
            $html .= '<span class="folder-name">' . htmlspecialchars($key) . '</span>';
            $html .= generateMenu($item, $basePath . $key . '/');
            $html .= '</li>';
        } else {
            // Это файл
            $fileName = pathinfo($item, PATHINFO_FILENAME);
            $filePath = $basePath . $item;
            $html .= '<li class="file">';
            $html .= '<a href="#" data-md="' . htmlspecialchars($filePath) . '">' . htmlspecialchars($fileName) . '</a>';
            $html .= '</li>';
        }
    }
    
    $html .= '</ul>';
    return $html;
}

// Сканируем текущую директорию
$structure = scanDirectory('.');
$menuHtml = generateMenu($structure);

// Генерируем HTML-страницу
header('Content-Type: text/html; charset=utf-8');
echo <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Markdown Documentation</title>
    <!--link rel="stylesheet" href="ammdr.css"-->
    <script type="module" src="https://cdn.jsdelivr.net/gh/zerodevx/zero-md@2/dist/zero-md.min.js"></script>
<style>
 /* ==================== */
/* БАЗОВЫЕ СТИЛИ */
/* ==================== */

/**
 * Сброс стилей и базовые настройки
 * Устанавливает box-model, шрифты и основные параметры документа
 */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, 
                sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
}

/* Основная структура документа */
body {
    display: grid;
    grid-template-rows: auto 1fr auto;
    grid-template-columns: 300px 1fr;
    grid-template-areas: 
        "header header"
        "nav main"
        "footer footer";
    min-height: 100vh;
    color: #24292e;
    line-height: 1.5;
    overflow: hidden;
}

/* ==================== */
/* КОМПОНЕНТЫ МАКЕТА */
/* ==================== */

/* Шапка документа */
header {
    grid-area: header;
    background: #f8f9fa;
    padding: 1rem;
    border-bottom: 1px solid #e1e4e8;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Основная навигация (левая панель) */
nav {
    grid-area: nav;
    background: #f8f9fa;
    padding: 1.5rem;
    border-right: 1px solid #e1e4e8;
    overflow-y: auto;
    height: calc(100vh - 120px);
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

/* Основное содержимое */
main {
    grid-area: main;
    padding: 2rem;
    overflow-y: auto;
    height: calc(100vh - 120px);
    scrollbar-width: thin;
    scrollbar-color: #0366d6 #f1f1f1;
    background-color: #fff;
}

/* Контейнер для ограничения ширины контента */
.content-wrapper {
    max-width: 800px;
    margin: 0 auto;
    width: 100%;
}

/* Подвал документа */
footer {
    grid-area: footer;
    background: #f8f9fa;
    padding: 1rem;
    border-top: 1px solid #e1e4e8;
    text-align: center;
    font-size: 0.9rem;
    color: #6c757d;
}

/* ==================== */
/* ЭЛЕМЕНТЫ НАВИГАЦИИ */
/* ==================== */

/* Список в навигации */
nav ul {
    list-style: none;
    padding-left: 1rem;
}

nav li {
    margin: 0.1rem 0;
}

/* Стили для папок */
.folder {
    margin-left: -0.5rem; /* Уменьшенный отступ для компактности */
    padding-left: 0;
}

.folder-name {
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: flex-start;
    transition: all 0.2s ease;
    color: #24292e;
    border-radius: 4px;
    padding-left: 0.5rem;
    margin-left: -0.5rem;
}

.folder-name:hover {
    background-color: #e1e4e8;
}

/* Иконка закрытой папки (зеленый) */
.folder-name::before {
    content: "📁 ";
    color: #2ecc71;
}

/* Иконка открытой папки (желтый) */
.folder.expanded > .folder-name::before {
    content: "📂 ";
    color: #f39c12;
}

/* Вложенный список в папке */
.folder > ul {
    display: none;
    margin-left: 0.5rem;
    padding-left: 0.5rem;
}

.folder.expanded > ul {
    display: block;
}

/* Стили для файлов */
.file {
    margin-left: -1rem; /* Уменьшенный отступ для компактности */
    padding-left: 0;
}

.file a {
    position: relative;
    padding-left: 1.2rem;
    display: flex;
    align-items: flex-start;
    min-height: 1.5em;
    transition: all 0.2s ease;
    border-radius: 4px;
    color: inherit;
}

/* Иконка файла (сиреневая) */
.file a::before {
    content: "📄";
    position: absolute;
    left: 0.2rem;
    top: 0.4em;
    font-size: 0.9em;
    line-height: 1;
    color: #ca2ecc;
    transition: color 0.2s ease;
}

/* Состояния иконки файла */
.file a:hover::before,
.file a.active::before {
    color: #ff0000; /* Ярко-красный при наведении/активном состоянии */
}

.file a:hover {
    background-color: #e1e4e8;
}

/* Активный файл */
.file a.active {
    color: #0366d6;
    font-weight: 500;
    background-color: #e1e4e8;
}

/* ==================== */
/* ССЫЛКИ */
/* ==================== */

a {
    color: #0366d6;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* ==================== */
/* СКРОЛЛБАРЫ */
/* ==================== */

/* Навигационная панель */
nav::-webkit-scrollbar {
    width: 8px;
}
nav::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
nav::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}
nav::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Основное содержимое */
main::-webkit-scrollbar {
    width: 10px;
}
main::-webkit-scrollbar-track {
    background: #f1f1f1;
}
main::-webkit-scrollbar-thumb {
    background: #0366d6;
    border-radius: 5px;
}
main::-webkit-scrollbar-thumb:hover {
    background: #0252b3;
}

/* ==================== */
/* КОМПОНЕНТЫ ИНТЕРФЕЙСА */
/* ==================== */

/* Индикатор загрузки */
.loading {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    display: none;
    z-index: 1000;
    font-size: 0.9rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { opacity: 0.8; }
    50% { opacity: 1; }
    100% { opacity: 0.8; }
}

/* ==================== */
/* СТИЛИ ДЛЯ MARKDOWN (zero-md) */
/* ==================== */

zero-md {
    width: 100%;
    min-height: 100%;
    background: white;
    border-radius: 6px;
    padding: 1px; /* Необходимо для корректного отображения теней */
}

/* Заголовки */
zero-md h1, 
zero-md h2, 
zero-md h3 {
    scroll-margin-top: 20px; /* Отступ для якорных ссылок */
}

/* Блоки кода */
zero-md pre {
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

zero-md code {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    font-size: 0.9em;
}

/* Мобильное меню - иконка "гамбургер" */
.mobile-menu-btn {
	display: none;
	position: absolute;
	right: 1rem;
	top: 1rem;
	width: 30px;
	height: 24px;
	cursor: pointer;
	z-index: 1001;
}

.mobile-menu-btn span {
	display: block;
	width: 100%;
	height: 3px;
	background: #0366d6;
	margin-bottom: 5px;
	transition: all 0.3s ease;
}

.mobile-menu-btn.active span:nth-child(1) {
	transform: rotate(45deg) translate(5px, 5px);
}

.mobile-menu-btn.active span:nth-child(2) {
	opacity: 0;
}

.mobile-menu-btn.active span:nth-child(3) {
	transform: rotate(-45deg) translate(7px, -7px);
}

/* Адаптивные стили */
@media (max-width: 600px) {
body {
	grid-template-columns: 1fr;
	grid-template-areas: 
		"header"
		"main"
		"footer";
}

.full-title { display: none; }
.short-title { display: inline; }

nav {
	position: fixed;
	top: 0;
	left: -300px;
	width: 280px;
	height: 100vh;
	z-index: 1000;
	transition: left 0.3s ease;
	box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

nav.active {
	left: 0;
}

.mobile-menu-btn {
	display: block;
}

main {
	padding: 1rem;
}
}

@media (min-width: 601px) {
.full-title { display: inline; }
.short-title { display: none; }
}       
</style>
</head>
<body>
    <header>
        <h1><span class="full-title">{$ammdr}</span><span class="short-title">{$ammdr_short}</span></h1>
        <div class="mobile-menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>
    
    <nav id="main-nav">
        {$menuHtml}
    </nav>
    
    <main>
        <div class="content-wrapper">
HTML;

// Отображение приветственного сообщения или выбранного файла
if (isset($_GET['md'])) {
    $safeFile = basename($_GET['md']);
    if (pathinfo($safeFile, PATHINFO_EXTENSION) === 'md') {
        echo "<zero-md src='$safeFile'></zero-md>";
    }
} else {
    echo '<h1>Добро пожаловать!</h1>';
    echo '<p>Выберите документ из меню слева.</p>';
}

echo <<<HTML
        </div>
    </main>
    
    <footer>
        <p>Generated with PHP Markdown Navigation</p>
    </footer>
    
    <!--div class="loading">Загрузка...</div-->
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
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
            
            // Мобильное меню
            $('.mobile-menu-btn').click(function() {
                $(this).toggleClass('active');
                $('#main-nav').toggleClass('active');
            });
            
            // При клике на файл
            $('.file a').click(function() {
                // Удаляем active у всех файлов
                $('.file a').removeClass('active');
                // Добавляем active к текущему
                $(this).addClass('active');
            });
        });
    </script>
</body>
</html>
HTML;