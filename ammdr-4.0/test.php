<?php
// test.php — Диагностический скрипт для проверки работы на хостинге

header('Content-Type: text/plain; charset=utf-8');
echo "=== Диагностика окружения PHP ===\n";

// 1. Проверка версии PHP
echo "\n1. Версия PHP:\n";
echo 'PHP Version: ' . phpversion() . "\n";

// 2. Проверка наличия mbstring
echo "\n2. Проверка расширения mbstring:\n";
if (extension_loaded('mbstring')) {
    echo "mbstring включен.\n";
    echo "Текущая кодировка: " . mb_internal_encoding() . "\n";
} else {
    echo "mbstring НЕ включен. Обратитесь к администратору хостинга.\n";
}

// 3. Проверка прав на запись в текущую директорию
echo "\n3. Проверка прав на запись в текущую директорию:\n";
$testFile = __DIR__ . '/test_write.txt';
if (@file_put_contents($testFile, 'Тест записи') !== false) {
    echo "Запись в текущую директорию разрешена.\n";
    unlink($testFile); // Удаляем тестовый файл
} else {
    echo "Запись в текущую директорию ЗАПРЕЩЕНА. Проверьте права доступа.\n";
}

// 4. Проверка функции scandir()
echo "\n4. Проверка функции scandir():\n";
$dir = __DIR__;
$result = scandir($dir);
if ($result !== false) {
    echo "scandir() работает корректно. Список файлов в текущей директории:\n";
    print_r(array_slice($result, 0, 5)); // Выводим только первые 5 файлов
} else {
    echo "Ошибка при выполнении scandir(). Возможно, недостаточно прав.\n";
}

// 5. Проверка JSON-файла (ammdr-files.json)
echo "\n5. Проверка JSON-файла (ammdr-files.json):\n";
$jsonFile = __DIR__ . '/ammdr-files.json';
if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    if (json_decode($content, true) !== null) {
        echo "Файл ammdr-files.json существует и содержит корректный JSON.\n";
    } else {
        echo "Файл ammdr-files.json существует, но содержит некорректный JSON.\n";
    }
} else {
    echo "Файл ammdr-files.json отсутствует.\n";
}

// 6. Проверка AJAX-запросов
echo "\n6. Проверка заголовков для AJAX-запросов:\n";
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    echo "Этот запрос был выполнен через AJAX.\n";
} else {
    echo "Этот запрос НЕ был выполнен через AJAX.\n";
}

// 7. Проверка времени выполнения скрипта
echo "\n7. Максимальное время выполнения скрипта:\n";
echo ini_get('max_execution_time') . " секунд\n";

// 8. Проверка памяти
echo "\n8. Лимит памяти:\n";
echo ini_get('memory_limit') . "\n";

//9. Тест заголовков
echo "Все заголовки:\n";
print_r($_SERVER);
// Итог
echo "\n=== Диагностика завершена ===\n";

?>
