<?php
// index.php Markdown Reader v3.1
//AMMDr ver. 4.0 06.04.2025 Алексей Нечаев, г. Москва, +7(999)003-90-23, nechaev72@list.ru

/////Для вывода ошибок на экран  ini_set('display_errors','on'); on || of
#print_r(function_exists('mb_internal_encoding')); //проверка 1-подключено, 0 - не подключено
error_reporting(E_ALL);
ini_set('display_errors','on');
mb_internal_encoding('UTF-8');

require_once 'MarkdownMenu.php';

// Конфигурация приложения
$ammdr = 'AMMDr ver. 4.0 - aMagic Markdown Reader';
$ammdr_short = 'AMMDr 4.0';

// Инициализация меню
$menu = new MarkdownMenu();

// Обработка AJAX запросов
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: text/html');
    
    $mode = $_POST['v'] ?? 'tree';
    $searchQuery = $_POST['search'] ?? '';
    $shouldScan = isset($_POST['scan']);
    
    // Принудительное сканирование если нужно
    if ($shouldScan) {
        $menu = new MarkdownMenu(); // Автоматически сканирует при создании
    }
    
    // Получаем файлы с учетом поиска
    $files = $searchQuery ? $menu->search($searchQuery) : $menu->getFiles();
    
    // Генерируем и возвращаем меню
    echo $menu->generateMenu($files, $mode);
    exit;
}

// Генерация основной страницы
$menuHtml = $menu->generateMenu($menu->getFiles(), 'tree');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Markdown Reader v3.1</title>
    
    <!-- Подключение CSS -->
    <link rel="stylesheet" href="assets/css/ammdr.css">
    
    <!-- Подключение ZeroMD для рендеринга Markdown -->
    <script type="module" src="https://cdn.jsdelivr.net/gh/zerodevx/zero-md@2/dist/zero-md.min.js"></script>
    
    <!-- Подключение jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Шапка документа -->
    <header>
        <h1>
            <span class="full-title"><?= htmlspecialchars($ammdr) ?></span>
            <span class="short-title"><?= htmlspecialchars($ammdr_short) ?></span>
        </h1>
        <div class="mobile-menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>
    
    <!-- Основная навигация -->
    <nav id="main-nav" class="active">
        <!-- Панель управления навигацией -->
        <div id="nav-controls">
            <!-- Поле поиска -->
            <input type="search" id="search" class="form-control" placeholder="Поиск...">
        </div>
        
        <!-- Вторая панель с кнопками -->
        <div id="nav-controls">
            <!-- Кнопка сканирования -->
            <button class="nav-btn" id="scan-btn" title="Пересканировать всю директорию">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M17 22V20H20V17H22V20.5C22 20.89 21.84 21.24 21.54 21.54C21.24 21.84 20.89 22 20.5 22H17M7 22H3.5C3.11 22 2.76 21.84 2.46 21.54C2.16 21.24 2 20.89 2 20.5V17H4V20H7V22M17 2H20.5C20.89 2 21.24 2.16 21.54 2.46C21.84 2.76 22 3.11 22 3.5V7H20V4H17V2M7 2V4H4V7H2V3.5C2 3.11 2.16 2.76 2.46 2.46C2.76 2.16 3.11 2 3.5 2H7M10.5 6C13 6 15 8 15 10.5C15 11.38 14.75 12.2 14.31 12.9L17.57 16.16L16.16 17.57L12.9 14.31C12.2 14.75 11.38 15 10.5 15C8 15 6 13 6 10.5C6 8 8 6 10.5 6M10.5 8C9.12 8 8 9.12 8 10.5C8 11.88 9.12 13 10.5 13C11.88 13 13 11.88 13 10.5C13 9.12 11.88 8 10.5 8Z"/>
                </svg>
            </button>
            
            <!-- Кнопки переключения вида -->
            <button class="nav-btn active" data-view="tree" title="Древовидный список">
                <?xml version="1.0" ?><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M9 1H1V9H9V6H11V20H15V23H23V15H15V18H13V6H15V9H23V1H15V4H9V1ZM21 3H17V7H21V3ZM17 17H21V21H17V17Z" fill="currentColor" fill-rule="evenodd"/></svg>
            </button>
            
            <button class="nav-btn" data-view="last-dirs" title="По папкам" style="color: #f39c12;">
                📂
            </button>
            
            <button class="nav-btn" data-view="flat" title="Только файлы *.md" style="color: #ca2ecc;">
                📄
            </button>
        </div>
        
        <!-- Контейнер для меню -->
        <div id="menu-container">
            <?= $menuHtml ?>
        </div>
    </nav>
    
    <!-- Основное содержимое -->
    <main>
        <div class="content-wrapper">
            <?php if (isset($_GET['md'])): ?>
                <?php
                $safeFile = basename($_GET['md']);
                if (pathinfo($safeFile, PATHINFO_EXTENSION) === 'md') {
                    echo "<zero-md src='" . htmlspecialchars($safeFile) . "'></zero-md>";
                }?>
				<?php else: ?>
                <h1>Добро пожаловать!</h1>
                <p>Выберите документ из меню слева.</p>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Подвал документа -->
    <footer>
        <p>Generated with PHP Markdown Navigation</p>
    </footer>
    
    <!-- Индикатор загрузки (изначально скрыт) -->
    <div class="loading">Загрузка...</div>
    
    <!-- Подключение основного JavaScript -->
    <script src="assets/js/ammdr.js"></script>
</body>
</html>