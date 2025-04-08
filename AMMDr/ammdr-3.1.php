<?php
//Ваше ПОЛНОЕ НАЗВАНИЕ
$ammdr = '';
//Ваше Короткое название для мобильного
$ammdr_short = '';

//AMMDr ver. 3.1 08.04.2025 Алексей Нечаев, г. Москва, +7(999)003-90-23, nechaev72@list.ru
/*
/////Для вывода ошибок на экран  ini_set('display_errors','on'); on || of
#print_r(function_exists('mb_internal_encoding')); //проверка 1-подключено, 0 - не подключено
error_reporting(E_ALL);
ini_set('display_errors','on');
mb_internal_encoding('UTF-8');
*/

if(empty ($ammdr)){
//ПОЛНОЕ НАЗВАНИЕ
$ammdr = 'AMMDr ver. 3.1 - aMagic Markdown Reader';
}
if(empty ($ammdr_short)){
//Короткое название для мобильного
$ammdr_short = 'AMMDr 3.1';
}
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
 
function generateMenu(array $files, string $mode = 'tree'): string {
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
	<link href="https://cdn.jsdelivr.net/gh/aMagicMorgen/aMagic-MDreader-AMMDr@main/ammdr-4.0/assets/css/ammdr.css" rel="stylesheet" >
	<script src="https://cdn.jsdelivr.net/npm/cssbed@1.0.5/dist/cssbed.min.js"></script>
    <script type="module" src="https://cdn.jsdelivr.net/gh/zerodevx/zero-md@2/dist/zero-md.min.js"></script>
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
	<input type='search' id='search' class='form-control'>
	<div class="menu-btn active">
            <span></span>
            <span></span>
            <span></span>
        </div>
	</div>
	<div id="nav-controls">
		<button class="nav-btn" id="scan-btn" title='Пересканировать всю директорию'>
		<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-magnify-scan" width="24" height="24" viewBox="0 0 24 24"><path d="M17 22V20H20V17H22V20.5C22 20.89 21.84 21.24 21.54 21.54C21.24 21.84 20.89 22 20.5 22H17M7 22H3.5C3.11 22 2.76 21.84 2.46 21.54C2.16 21.24 2 20.89 2 20.5V17H4V20H7V22M17 2H20.5C20.89 2 21.24 2.16 21.54 2.46C21.84 2.76 22 3.11 22 3.5V7H20V4H17V2M7 2V4H4V7H2V3.5C2 3.11 2.16 2.76 2.46 2.46C2.76 2.16 3.11 2 3.5 2H7M10.5 6C13 6 15 8 15 10.5C15 11.38 14.75 12.2 14.31 12.9L17.57 16.16L16.16 17.57L12.9 14.31C12.2 14.75 11.38 15 10.5 15C8 15 6 13 6 10.5C6 8 8 6 10.5 6M10.5 8C9.12 8 8 9.12 8 10.5C8 11.88 9.12 13 10.5 13C11.88 13 13 11.88 13 10.5C13 9.12 11.88 8 10.5 8Z" /></svg>
		</button>
        <button class="nav-btn" data-view="tree" title='Древовидный список'>
		<svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M9 1H1V9H9V6H11V20H15V23H23V15H15V18H13V6H15V9H23V1H15V4H9V1ZM21 3H17V7H21V3ZM17 17H21V21H17V17Z" fill="currentColor" fill-rule="evenodd"/></svg>
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
    <script src="https://cdn.jsdelivr.net/gh/aMagicMorgen/aMagic-MDreader-AMMDr@main/AMMDr/ammdr-3.0.js"></script>
    
</body>
</html>
