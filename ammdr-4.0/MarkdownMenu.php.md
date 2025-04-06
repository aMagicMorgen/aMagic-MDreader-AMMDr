# Документация AMMDr ver. 4.0 - MarkdownMenu

## Обзор класса

`MarkdownMenu` - это PHP-класс для работы с Markdown-документами, который предоставляет функционал для:
- Рекурсивного сканирования директорий
- Кеширования структуры файлов
- Генерации различных видов меню
- Поиска по файлам

## Установка и требования

1. PHP версии 7.4 или выше
2. Расширение mbstring для корректной работы с UTF-8
3. Права на запись в директорию для создания кеш-файла

## Полное описание класса

### Конструктор

```php
public function __construct()
```
Инициализирует объект, автоматически определяя нужно ли сканировать директории или можно использовать кеш.

**Пример:**
```php
$menu = new MarkdownMenu();
```

### Основные методы

#### 1. `getFiles()`
```php
public function getFiles(): array
```
Возвращает массив всех найденных Markdown-файлов.

**Пример:**
```php
$allFiles = $menu->getFiles();
print_r($allFiles);
```

#### 2. `search()`
```php
public function search(string $query): array
```
Выполняет поиск по именам файлов с учетом регистра.

**Параметры:**
- `$query` - строка поиска (может содержать несколько слов)

**Пример:**
```php
$results = $menu->search('документация api');
```

#### 3. `generateMenu()`
```php
public function generateMenu(array $files, string $mode = 'tree'): string
```
Генерирует HTML-меню в указанном формате.

**Параметры:**
- `$files` - массив путей к файлам
- `$mode` - режим отображения:
  - `tree` - древовидное меню
  - `flat` - плоский список
  - `last-dirs` - группировка по последним директориям

**Пример:**
```php
$htmlMenu = $menu->generateMenu($files, 'tree');
echo $htmlMenu;
```

### Вспомогательные методы

#### 1. `scanDirectory()`
```php
private function scanDirectory(string $dir = '.'): array
```
Рекурсивно сканирует директорию на наличие .md файлов.

#### 2. `saveCache()`
```php
private function saveCache(): void
```
Сохраняет текущий список файлов в кеш (с атомарной записью).

#### 3. `loadFromCache()`
```php
private function loadFromCache(): void
```
Загружает список файлов из кеша.

#### 4. `performFullScan()`
```php
private function performFullScan(): void
```
Выполняет полное сканирование и сохраняет результаты.

### Методы генерации меню

#### 1. `generateFlatMenu()`
```php
protected function generateFlatMenu(array $files): string
```
Генерирует плоский список всех файлов.

#### 2. `generateTreeMenu()`
```php
protected function generateTreeMenu(array $tree, string $basePath = ''): string
```
Генерирует древовидное меню.

#### 3. `generateLastDirsMenu()`
```php
protected function generateLastDirsMenu(array $files): string
```
Генерирует меню с группировкой по последним директориям.

#### 4. `buildTreeStructure()`
```php
protected function buildTreeStructure(array $files): array
```
Преобразует плоский массив путей в древовидную структуру.

## Примеры использования

### 1. Базовое использование
```php
$menu = new MarkdownMenu();

// Получить все файлы
$allFiles = $menu->getFiles();

// Сгенерировать меню
$htmlMenu = $menu->generateMenu($allFiles, 'tree');

// Вывести меню
echo $htmlMenu;
```

### 2. Использование с поиском
```php
$menu = new MarkdownMenu();

// Поиск файлов
$foundFiles = $menu->search('установка настройка');

// Генерация меню для результатов поиска
$htmlMenu = $menu->generateMenu($foundFiles, 'flat');

echo $htmlMenu;
```

### 3. Принудительное сканирование
```php
// Принудительное сканирование (например, по нажатию кнопки)
$_POST['scan'] = true;
$menu = new MarkdownMenu();

// Далее обычная работа
```

## Особенности работы

1. **Кеширование**:
   - Результаты сканирования сохраняются в `ammdr-files.json`
   - При следующем запуске используется кеш (если он не пустой и валидный)
   - Для принудительного сканирования установите `$_POST['scan'] = true`

2. **Обработка ошибок**:
   - Все ошибки логируются в error_log
   - При проблемах с кешем автоматически выполняется сканирование

3. **Безопасность**:
   - Все выводимые данные экранируются через `htmlspecialchars()`
   - Атомарная запись в кеш-файл через временный файл

## Интеграция с фронтендом

Для полной интеграции рекомендуется использовать вместе с:
1. `ammdr.js` - обработка кликов и AJAX-запросов
2. `ammdr.css` - стилизация меню

Пример AJAX-обработчика для переключения режимов:
```php
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $mode = $_POST['v'] ?? 'tree';
    $searchQuery = $_POST['search'] ?? '';
    
    $menu = new MarkdownMenu();
    $files = $searchQuery ? $menu->search($searchQuery) : $menu->getFiles();
    
    echo $menu->generateMenu($files, $mode);
    exit;
}
```

## Заключение

`MarkdownMenu` предоставляет удобный API для работы с коллекцией Markdown-файлов, поддерживая различные варианты отображения и поиск. Класс полностью инкапсулирует логику работы с файловой системой и кешированием, что делает его удобным для интеграции в различные проекты.