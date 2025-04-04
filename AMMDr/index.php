<?php
/*
/////Для вывода ошибок на экран  ini_set('display_errors','on'); on || of
#print_r(function_exists('mb_internal_encoding')); //проверка 1-подключено, 0 - не подключено
error_reporting(E_ALL);
ini_set('display_errors','on');
mb_internal_encoding('UTF-8');
*/
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
            $subResult = scanDirectory($path);
            if (!empty($subResult)) {
                $result = array_merge($result, $subResult);
            }
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'md') {
            $result[] = $path; // Возвращаем полный путь к файлу
        }
    }
    
    return $result;
}
/*
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
*/
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
    <link rel="stylesheet" href="ammdr.css">
    <script type="module" src="https://cdn.jsdelivr.net/gh/zerodevx/zero-md@2/dist/zero-md.min.js"></script>
    <style></style>
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