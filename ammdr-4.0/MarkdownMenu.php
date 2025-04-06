<?php
class MarkdownMenu {
    private $files = [];
    private $cacheFile = 'ammdr-files.json';
    
    public function __construct() {
        // Всегда сначала сканируем, потом пытаемся читать кеш
        if ($this->shouldScan()) {
            $this->performFullScan();
        } else {
            $this->loadFromCache();
        }
    }
    
    private function shouldScan(): bool {
        // Сканируем если: явный запрос или нет файла или файл пустой
        return isset($_POST['scan']) || 
               !file_exists($this->cacheFile) || 
               filesize($this->cacheFile) == 0;
    }
    
    private function performFullScan(): void {
        $this->files = $this->scanDirectory();
        $this->saveCache();
    }
    
    private function scanDirectory(string $dir = '.'): array {
        $result = [];
        
        try {
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    $result = array_merge($result, $this->scanDirectory($path));
                } 
                elseif (pathinfo($path, PATHINFO_EXTENSION) === 'md') {
                    $result[] = $path;
                }
            }
        } catch (Exception $e) {
            error_log('Scan error: ' . $e->getMessage());
        }
        
        return $result;
    }
    
    private function loadFromCache(): void {
        try {
            $content = file_get_contents($this->cacheFile);
            $data = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $this->files = $data;
            } else {
                // Если кеш битый - сканируем заново
                $this->performFullScan();
            }
        } catch (Exception $e) {
            error_log('Cache read error: ' . $e->getMessage());
            $this->performFullScan();
        }
    }
    
    private function saveCache(): void {
        try {
            $json = json_encode($this->files, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            if ($json === false) throw new RuntimeException('JSON encode error');
            
            $tmpFile = $this->cacheFile . '.tmp';
            file_put_contents($tmpFile, $json);
            
            // Атомарная запись через временный файл
            rename($tmpFile, $this->cacheFile);
        } catch (Exception $e) {
            error_log('Cache save failed: ' . $e->getMessage());
        }
    }
    
    // Остальные методы без изменений...
    public function getFiles(): array {
        return $this->files;
    }
  
    /**
     * Выполняет поиск по файлам
     */
    public function search(string $query): array {
        if (empty($query)) {
            return $this->files;
        }
        
        $keywords = preg_split('/\s+/', $query);
        $results = [];
        
        foreach ($this->files as $item) {
            $matchAll = true;
            $itemLower = mb_strtolower($item);
            
            foreach ($keywords as $keyword) {
                if (mb_strpos($itemLower, mb_strtolower($keyword)) === false) {
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
    
    /**
     * Генерирует меню в указанном формате
     */
    public function generateMenu(array $files, string $mode = 'tree'): string {
        switch ($mode) {
            case 'tree': 
                return $this->generateTreeMenu($this->buildTreeStructure($files));
            case 'last-dirs': 
                return $this->generateLastDirsMenu($files);
            case 'flat': 
            default: 
                return $this->generateFlatMenu($files);
        }
    }
    
    /**
     * Генерирует плоский список файлов
     */
    protected function generateFlatMenu(array $files): string {
        $html = '<ul class="nav-menu">';
        foreach ($files as $file) {
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $html .= sprintf(
                '<li class="file"><a href="#" data-md="%s" title="%s">%s</a></li>',
                htmlspecialchars($file),
                htmlspecialchars($file),
                htmlspecialchars($fileName)
            );
        }
        $html .= '</ul>';
        return $html;
    }
    
    /**
     * Генерирует древовидное меню
     */
    protected function generateTreeMenu(array $tree, string $basePath = ''): string {
        $html = '<ul class="nav-menu">';
        
        foreach ($tree as $key => $item) {
            if (is_array($item)) {
                // Директория
                $html .= '<li class="folder">';
                $html .= '<span class="folder-name">' . htmlspecialchars($key) . '</span>';
                $html .= $this->generateTreeMenu($item, $basePath . $key . DIRECTORY_SEPARATOR);
                $html .= '</li>';
            } else {
                // Файл
                $fileName = pathinfo($item, PATHINFO_FILENAME);
                $filePath = $basePath . $item;
                $html .= sprintf(
                    '<li class="file"><a href="#" data-md="%s" title="%s">%s</a></li>',
                    htmlspecialchars($filePath),
                    htmlspecialchars($filePath),
                    htmlspecialchars($fileName)
                );
            }
        }
        
        $html .= '</ul>';
        return $html;
    }
    
    /**
     * Генерирует меню с группировкой по последним директориям
     */
    protected function generateLastDirsMenu(array $files): string {
        $scriptDir = __DIR__ . DIRECTORY_SEPARATOR;
        $menuItems = [];
        
        foreach ($files as $fullPath) {
            $relativePath = ltrim(str_replace($scriptDir, '', $fullPath), DIRECTORY_SEPARATOR);
            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
            $fileName = array_pop($parts);
            $folder = !empty($parts) ? end($parts) : '';
            
            if (!isset($menuItems[$folder])) {
                $menuItems[$folder] = [];
            }
            
            $menuItems[$folder][] = [
                'name' => pathinfo($fileName, PATHINFO_FILENAME),
                'path' => $relativePath
            ];
        }
        
        $html = '<ul class="nav-menu">';
        foreach ($menuItems as $folder => $files) {
            $html .= '<li class="folder">';
            $html .= '<span class="folder-name">' . htmlspecialchars($folder ?: 'Корень') . '</span>';
            $html .= '<ul class="file-list">';
            
            foreach ($files as $file) {
                $html .= sprintf(
                    '<li class="file"><a href="#" data-md="%s" title="%s">%s</a></li>',
                    htmlspecialchars($file['path']),
                    htmlspecialchars($file['path']),
                    htmlspecialchars($file['name'])
                );
            }
            
            $html .= '</ul></li>';
        }
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Строит древовидную структуру из массива путей
     */
    protected function buildTreeStructure(array $files): array {
        $tree = [];
        
        foreach ($files as $file) {
            $parts = explode(DIRECTORY_SEPARATOR, $file);
            
            // Удаляем начальные точки и слеши
            $parts = array_filter($parts, function($part) {
                return $part !== '.' && $part !== '';
            });
            
            $filename = array_pop($parts);
            $current = &$tree;
            
            foreach ($parts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
            
            $current[] = $filename;
        }
        
        return $tree;
    }
}