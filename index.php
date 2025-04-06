<?php
//AMMDr ver. 3.0 06.04.2025 Алексей Нечаев, г. Москва, +7(999)003-90-23, nechaev72@list.ru
/*
/////Для вывода ошибок на экран  ini_set('display_errors','on'); on || of
#print_r(function_exists('mb_internal_encoding')); //проверка 1-подключено, 0 - не подключено
error_reporting(E_ALL);
ini_set('display_errors','on');
mb_internal_encoding('UTF-8');
*/
//ПОЛНОЕ НАЗВАНИЕ
$ammdr = 'AMMDr ver. 3.0 - aMagic Markdown Reader';
//Короткое название для мобильного
$ammdr_short = 'AMMDr 3.0';

// Функция для рекурсивного сканирования директорий и поиска .md файлов
function getMarkdownFiles($dir = '.') {
    $cacheFile = 'ammdr-files.json';
    // Загрузка из кеша
    if (file_exists($cacheFile) && !isset($_POST['scan'])) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    // Сканируем директории
    $result = [];
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) $result = array_merge($result, getMarkdownFiles($path));
        elseif (pathinfo($path, PATHINFO_EXTENSION) === 'md') $result[] = $path;
     }
	//Запись в кеш через json
    file_put_contents($cacheFile, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    return $result;
}

/**
 * Генерирует меню в разных форматах
 * 
 * @param array $files Массив путей к .md файлам
 * @param string $mode Режим работы: 'flat' | 'tree' | 'last-dirs'
 * @return string HTML-код меню
 */
 
function generateMenu(array $files, string $mode = 'flat'): string {
    switch ($mode) {
        case 'tree':
            return generateTreeMenu(buildTreeStructure($files));
        case 'last-dirs':
            return generateLastDirsMenu($files);
        case 'flat':
        default:
            return generateFlatMenu($files);
    }
}

// Режим 1: Плоский список всех файлов 'flat' (рабочая)
function generateFlatMenu(array $files): string {
    $html = '<ul class="nav-menu">';
    foreach ($files as $file) {
        // Это файл (в плоском режиме у нас только файлы)
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $html .= '<li class="file">';
        $html .= '<a href="#" data-md="' . htmlspecialchars($file) . '" title="'.htmlspecialchars($file).'">' . 
                 htmlspecialchars($fileName) . '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}


// Режим 2: Древовидное меню  'tree' (работает)
function generateTreeMenu(array $tree, string $basePath = ''): string {
    $html = '<ul class="nav-menu">';
    foreach ($tree as $key => $item) {
        if (is_array($item)) {
			 // Это директория
            $html .= '<li class="folder">';
            $html .= '<span class="folder-name">' . htmlspecialchars($key) . '</span>';
            $html .= generateTreeMenu($item, $basePath . $key . '/');//DIRECTORY_SEPARATOR
            $html .= '</li>';
        } else {
			// Это файл
            $fileName = pathinfo($item, PATHINFO_FILENAME);
			$filePath = $basePath . $item;
            $html .= '<li class="file">';
            $html .= '<a href="#" data-md = "' . htmlspecialchars($filePath) . '" title="'.htmlspecialchars($filePath).'">' . htmlspecialchars($fileName) . '</a>';
            $html .= '</li>';
		}
    }
    $html .= '</ul>';
    return $html;
}

// Режим 3: Список конечных папок 'last-dirs' (рабочая)
function generateLastDirsMenu(array $files): string {
    $scriptDir = __DIR__ . DIRECTORY_SEPARATOR;
    $menuItems = [];
    
    // Создаем упрощенную структуру
    foreach ($files as $fullPath) {
        // Получаем относительный путь
        $relativePath = ltrim(str_replace($scriptDir, '', $fullPath), DIRECTORY_SEPARATOR);
        // Разбиваем на части
        $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
        $fileName = array_pop($parts); // Извлекаем имя файла
      
        // Определяем конечную папку (пустая строка для корня)
        $folder = !empty($parts) ? end($parts) : '';
        
        // Добавляем в структуру
        if (!isset($menuItems[$folder])) {
            $menuItems[$folder] = [];
        }
        $menuItems[$folder][] = [
            'name' => pathinfo($fileName, PATHINFO_FILENAME),
            'path' => $relativePath
        ];
		
    }
    
    // Генерируем HTML меню
    $html = '<ul class="nav-menu">';
    foreach ($menuItems as $folder => $files) {
        $html .= '<li class="folder">';
        $html .= '<span class="folder-name">' . htmlspecialchars($folder ?: 'Корень') . '</span>';
		#$html .= '<span class="folder-name">' . htmlspecialchars($folder) . '</span>';
        $html .= '<ul class="file-list">';
    
         $template = '<li class="file"><a href="#" data-md="%1$s" title="%1$s">%2$s</a></li>';
		foreach ($files as $file) {
			$html .= sprintf($template, htmlspecialchars($file['path']), htmlspecialchars($file['name']));
		}
        $html .= '</ul></li>';
    }
    $html .= '</ul>';
    
    return $html;
}

// Вспомогательная функция для построения древовидной структуры
function buildTreeStructure(array $files): array {
    $tree = [];
    foreach ($files as $file) {
        $parts = explode(DIRECTORY_SEPARATOR, $file);
		// Удаляем начальные точки и слеши (./)
       $parts = array_filter($parts, function($part) {
            return $part !== '.' && $part !== '';
        });
        #$parts = array_filter($parts, fn($part) => $part !== '.' && $part !== '');
        $current = &$tree;
        
        // Обрабатываем все части пути кроме последней (имя файла)
        $filename = array_pop($parts);
        foreach ($parts as $part) {
            if (!isset($current[$part])) {
                $current[$part] = [];
            }
            $current = &$current[$part];
        }
        
        // Добавляем файл в числовом индексе
        $current[] = $filename;
    }
    return $tree;
}
// Сканируем текущую директорию
$structure = getMarkdownFiles();
/// Генерируем меню в зависимости от параметра

$menuHtml = generateMenu($structure, 'tree');

// Если это AJAX-запрос, возвращаем только меню
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
//    header('Content-Type: text/html');
     if (isset($_POST['search'])) {
	 handleAjaxRequest();
	 }
    if (isset($_POST['v'])) {
        print_r (generateMenu($structure, ($_POST['v'])));
    } elseif (isset($_POST['scan'])) {
        print_r (generateMenu($structure, 'tree'));
    }
    
    exit; // Важно! Прекращаем выполнение для AJAX
}

////Реализация поиска слов в массиве

// Функция для обработки AJAX-запросов
function handleAjaxRequest() {
    $query = isset($_POST['search']) ? trim($_POST['search']) : '';
    $results = simpleSearch($query);
    print_r(generateMenu($results, 'flat'));
    exit;
}

// Функция поиска
function simpleSearch($query) {
    $cacheFile = 'ammdr-files.json';
    $array = json_decode(file_get_contents($cacheFile), true);
    
    if (empty($query)) {
        return $array;
    }

    $keywords = preg_split('/\s+/', $query);
    $results = [];
    
    foreach ($array as $item) {
        $matchAll = true;
        /*foreach ($keywords as $keyword) {
            if (stripos($item, $keyword) === false) {
                $matchAll = false;
                break;
            }
        }*/
        foreach ($keywords as $keyword) {
    $keywordLower = mb_strtolower($keyword);
    $itemLower = mb_strtolower($item);
    if (strpos($itemLower, $keywordLower) === false) {
        $matchAll = false;
        break;
    }
}
        if ($matchAll) {
            $results[] = $item;
        }
    }
    
    return $results;
}

// Генерируем HTML-страницу
?>
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
/* ./assets/css/ammdr.css */
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
	padding: 0.5rem;
	border-right: 1px solid #e1e4e8;
	#overflow-y: auto;
	height: calc(100vh - 120px);
	scrollbar-width: thin;
	scrollbar-color: #c1c1c1 #f1f1f1;
}
/*	
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
*/
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
/* Меню - иконка "гамбургер" */
.menu-btn {
	#display: none;
	position: absolute;
	right: 1rem;
	top: 1rem;
	width: 30px;
	height: 24px;
	cursor: pointer;
	z-index: 1001;
}

/* Мобильное меню - иконка "гамбургер" */
.mobile-menu-btn {
	#display: none;
	position: absolute;
	right: 1rem;
	top: 1rem;
	width: 30px;
	height: 24px;
	cursor: pointer;
	z-index: 1001;
}

.menu-btn span {
	display: block;
	width: 100%;
	height: 3px;
	background: #0366d6;
	margin-bottom: 5px;
	transition: all 0.3s ease;
}
.mobile-menu-btn span {
	display: block;
	width: 100%;
	height: 3px;
	background: #0366d6;
	margin-bottom: 5px;
	transition: all 0.3s ease;
}

.menu-btn.active span:nth-child(1) {
	transform: rotate(45deg) translate(5px, 5px);
}

.mobile-menu-btn.active span:nth-child(1) {
	transform: rotate(45deg) translate(5px, 5px);
}

.menu-btn.active span:nth-child(2) {
	opacity: 0;
}

.mobile-menu-btn.active span:nth-child(2) {
	opacity: 0;
}

.menu-btn.active span:nth-child(3) {
	transform: rotate(-45deg) translate(7px, -7px);
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

nav.active {
	left: 0px;
}

.mobile-menu-btn {
	display: block;
}

.menu-btn {
	display: none;
}

main {
	padding: 1rem;
}
}

@media (min-width: 601px) {
.full-title { display: inline; }
.short-title { display: none; }
}  
/* Стили для контейнера элементов управления навигацией */
#nav-controls {
display: flex;
flex-wrap: wrap;
gap: 7px;
margin-top: 0px; /* Поднимаем элемент вверх */
margin-bottom: 5px;
padding-bottom: 10px;
border-bottom: 1px solid #e1e4e8;
}

#nav-controls {
    #padding: 8px 12px;
    #margin-bottom: 10px;
    #width: 100%;
    #box-sizing: border-box;
}
.nav-btn {
	padding: 1px 16px;
	background-color: #f0f0f0;
	border: 1px solid #ddd;
	border-radius: 4px;
	cursor: pointer;
	font-size: 24px;
	transition: background-color 0.2s;
}

.nav-btn:hover {
	background-color: #e0e0e0;
}

#menu-container {
	overflow-y: auto;
	height: calc(100% - 60px);
}

/* Анимация для панели навигации */

#main-nav {
	position: fixed;
	top: 81px;
	left: -280px;
	width: 280px;
	height: 100vh;
	z-index: 1000;
	transition: left 0.3s ease;
	box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

#main-nav.active {
	left: 0px;
} 



/* Стили для поля поиска */
#search.form-control {
    width: 80%;
    padding: 10px 15px;
    font-size: 14px;
    line-height: 1.5;
    color: #333;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 10px; /* Закругленные углы */
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075);
    height: 30px; /* Увеличенная высота */
    box-sizing: border-box;
}

/* Стиль при фокусе */
#search.form-control:focus {
    border-color: #80bdff; /* Голубая рамка при фокусе */
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Стиль при наведении */
#search.form-control:hover {
    border-color: #b3b3b3;
}

/* Стиль для placeholder */
#search.form-control::placeholder {
    color: #6c757d;
    opacity: 1;
}

/* Анимация изменения цвета рамки при вводе */
#search.form-control:not(:placeholder-shown) {
    border-color: #28a745; /* Зеленый цвет при вводе */
}

/* Дополнительные стили для темной темы (опционально) */
@media (prefers-color-scheme: dark) {
    #search.form-control {
        background-color: #343a40;
        border-color: #495057;
        color: #f8f9fa;
    }
    #search.form-control::placeholder {
        color: #adb5bd;
    }
}
</style>
</head>
<body>
    <header>
        <h1><span class="full-title"><?php { echo $ammdr;} ?></span><span class="short-title"><?php { echo $ammdr_short;}?></span></h1>
        <div class="mobile-menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>
	<nav id="main-nav" class="active">
	<div id="nav-controls">
	<input type='search' id='search' class='form-control'> <!--button class="searh-btn" >
	<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-magnify" width="24" height="24" viewBox="0 0 24 24"><path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z" /></svg>
	</button-->
	<div class="menu-btn active">
            <span></span>
            <span></span>
            <span></span>
        </div>
	</div>
	<div id="nav-controls">
		<button class="nav-btn" id="scan-btn" title='Пересканировать всю директорию'>
		<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-magnify-scan" width="24" height="24" viewBox="0 0 24 24"><path d="M17 22V20H20V17H22V20.5C22 20.89 21.84 21.24 21.54 21.54C21.24 21.84 20.89 22 20.5 22H17M7 22H3.5C3.11 22 2.76 21.84 2.46 21.54C2.16 21.24 2 20.89 2 20.5V17H4V20H7V22M17 2H20.5C20.89 2 21.24 2.16 21.54 2.46C21.84 2.76 22 3.11 22 3.5V7H20V4H17V2M7 2V4H4V7H2V3.5C2 3.11 2.16 2.76 2.46 2.46C2.76 2.16 3.11 2 3.5 2H7M10.5 6C13 6 15 8 15 10.5C15 11.38 14.75 12.2 14.31 12.9L17.57 16.16L16.16 17.57L12.9 14.31C12.2 14.75 11.38 15 10.5 15C8 15 6 13 6 10.5C6 8 8 6 10.5 6M10.5 8C9.12 8 8 9.12 8 10.5C8 11.88 9.12 13 10.5 13C11.88 13 13 11.88 13 10.5C13 9.12 11.88 8 10.5 8Z" /></svg>
		</button>
        <button class="nav-btn" data-view="tree" title='Древовидный список'>
		<?xml version="1.0" ?><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M9 1H1V9H9V6H11V20H15V23H23V15H15V18H13V6H15V9H23V1H15V4H9V1ZM21 3H17V7H21V3ZM17 17H21V21H17V17Z" fill="currentColor" fill-rule="evenodd"/></svg>
	</button>
        <button class="nav-btn" data-view="last-dirs" style="color: #f39c12;" title='По папкам'>📂 </button>
        <button class="nav-btn" data-view="flat" style="color: #ca2ecc;" title='Только файлы *.md'>📄</button>
        
		
    </div>
    <div id="menu-container">
        <?php echo $menuHtml;  ?>
    </div>
</nav>
    <main>
        <div class="content-wrapper">
<?php

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

?>
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
    </script>
	
	<script>
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
</script>
	<script>
$(document).ready(function() {
    // Обработка кнопки скрытия/показа панели
    $('#toggle-nav').on('click', function() {
        $('#main-nav').toggleClass('active');
    });
});
</script> 
</body>
</html>

