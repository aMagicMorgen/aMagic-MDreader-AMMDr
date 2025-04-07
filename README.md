# **aMMDr - aMagic Markdown Reader**  
### САЙТ ЗА 5 СЕКУНД из `*.md` файлов
- К сожалению на хостинге пока работают ver. 1.0, 1.1, 2.0(лучше чем 1.х)

- Версии ver. 3.0, 4.0 работают только на localhost (пока разбираюсь с AJAX запросами)

- Добавил test.php для тестирования
---
#### **Хотите за 5 секунд превратить сои папки с `*.md` файлами в сайт?** 
https://github.com/aMagicMorgen/aMagic-MDreader-AMMDr
Скачать последнюю версию [ammdr-4.0](https://github.com/aMagicMorgen/aMagic-MDreader-AMMDr/tree/main/ammdr-4.0) | [Примеры использования](https://github.com/aMagicMorgen/aMagic-MDreader-AMMDr/tree/main/ammdr-4.0)
- AMMDr ver. 3.0 06.04.2025 Алексей Нечаев, г. Москва, +7(999)003-90-23, nechaev72@list.ru
- Вставьте файл `ammdr.php`  в свою главную папку, где в подпапках есть  файлы `*.md` или в корень сайта,      
- переименуйте его как хотите или в `index.php` и запустите.     
Сайт работает. Ура!!!
- Адаптирован для смартфонов (слегка)...
- ver.3.0 Уже можно комфортно пользоваться...     
✅ кеш в `ammdr-files.json` при пересканировании `ReScan` перезаписывается,  
✅ три вида просмотра 'tree' | 'last-dirs' | 'flat'   
✅ поиск от двух букв пока в режиме 'flat' идет   
- ver.2.0 Выводит меню только по папкам в которых есть файлы `*.md`.   
✅ Предназначена для чтения Документов из больших директорий с большими вложенностями со всех папок.   
✅ Создается cach путей к файлам в файле `ammdr-files.php` при первом сканировании   
✅ Чтобы пересканировать все нужно или удалить файл `ammdr-files.php` или добавить GET в строке браузера `?scan=1`   
✅ Пути к файлам во всплывающих подсказках.    
✅ Поэтому в совпадающих по названиям папкам окажуться файлы с разными путями и возможно с однаковыми названиями.   
- ver.1.1 Выводит только файлы `*.md` а не дерево папок с файлами.
- ver.1.0 Выводит дерево всех папок. которые есть в директории. 
### **🔧 ВАШЕ НАЗВАНИЕ**  
Можете открыть файл и заменить переменные:
```PHP
//ПОЛНОЕ НАЗВАНИЕ
$ammdr = 'AMMDreader - aMagic Markdown Reader';
//Короткое название для мобильного
$ammdr_short = 'AMMDr';
```
### Полное руководство по использованию  

---

## **📌 Краткое описание**  
**aMMDreader** — это удобный PHP-скрипт для автоматического создания навигационного сайта по вашим Markdown-документам (*.md). Просто поместите файл в папку с вашими документами, и система сама:  
✅ Просканирует все подпапки  
✅ Построит древовидное меню  
✅ Преобразует Markdown в красивый HTML  
✅ Адаптируется под мобильные устройства  

**Никакой базы данных, никакой сложной настройки!**  

---

## **🚀 Быстрый старт**  
1. **Скопируйте** файл `index.php` в корень папки с документами (например, `/docs/`).  
2. **Поместите** ваши `.md`-файлы в любые подпапки (например, `/docs/guides/`, `/docs/api/`).  
3. **Откройте** `index.php` в браузере — сайт готов!  

---

## **🛠 Технические особенности**  
### **1. Автоматическое сканирование структуры**  
📂 **Рекурсивный обход папок**  
- Скрипт ищет все `.md`-файлы, включая вложенные директории.  
- Поддерживает любое количество уровней вложенности.  

### **2. Динамическое меню**  
🌲 **Древовидная навигация**  
- Папки раскрываются/закрываются кликом.  
- Файлы отображаются с иконками.  
- Активный файл подсвечивается.  

### **3. Адаптивный дизайн**  
📱 **Мобильная версия**  
- На экранах уже 600px:  
  - Меню скрывается в "гамбургер" (☰).  
  - Длинное название (`AMMDreader - aMagic Markdown Reader`) сокращается до `AMMDr`.  
  - Навигация выезжает по клику.  

### **4. Подсветка синтаксиса Markdown**  
✨ **Интеграция с zero-md**  
- Красивый рендеринг таблиц, кода, заголовков.  
- Поддержка GitHub Flavored Markdown.  

---

## **⚙️ Настройка**  
### **1. Изменение названия**  
Отредактируйте переменные в коде:  
```php
$ammdr = 'AMMDreader - aMagic Markdown Reader';  // Полное название  
$ammdr_short = 'AMMDr';                         // Сокращенное (для мобильных)  
```

### **2. Цвета и стили**  
Все CSS-стили находятся в `<style>` внутри файла. Основные настройки:  
```css
/* Цвета папок и файлов */
.folder-name::before { color: #2ecc71; }      /* Зеленый для закрытых папок */  
.folder.expanded > .folder-name::before { color: #f39c12; } /* Желтый для открытых */  
.file a::before { color: #ca2ecc; }          /* Сиреневый для файлов */  
.file a:hover::before { color: #ff0000; }    /* Красный при наведении */  
```

### **3. Размеры и отступы**  
```css
/* Ширина навигации (десктоп) */  
nav { width: 300px; }  

/* Адаптивность (меню скрывается на 600px) */  
@media (max-width: 600px) { ... }  
```

---

## **🔧 Расширенные возможности**  
### **1. Добавление своих стилей**  
Создайте файл `ammdreader.css` и подключите его в `<head>`:  
```html
<link rel="stylesheet" href="ammdreader.css">  
```

### **2. Кастомизация Markdown-рендеринга**  
Измените параметры zero-md:  
```html
<zero-md src="file.md" no-shadow>  
    <template data-merge="append">  
        <style>  
            h1 { color: red; }  
        </style>  
    </template>  
</zero-md>  
```

### **3. Поддержка темной темы**  
Добавьте в CSS:  
```css
@media (prefers-color-scheme: dark) {  
    body { background: #222; color: #eee; }  
    nav { background: #333; }  
}  
```

---

## **📜 Пример структуры папок**  
```
/docs/  
│── index.php  
├── guides/  
│   ├── installation.md  
│   └── usage.md  
└── api/  
    ├── v1.md  
    └── v2.md  
```

---

## **💡 Советы**  
- **Используйте README.md** — он отобразится на главной странице.  
- **Закрепляйте важные файлы** вверху списка (например, `!_important.md` или `нумерация` 01-..., 02-..., 03-...,).  
- **Обновление в реальном времени** — просто обновите `.md`-файл, изменения появятся сразу.  

---

## **🔄 Альтернативные версии**  
- **Без PHP**: Используйте `md-page.js` (чистый JavaScript).  
- **С Docker**: Соберите образ с Nginx + PHP для развертывания.  

---

## **📜 Лицензия**  
MIT License — свободное использование и модификация.  

**🎉 Теперь ваш Markdown-сайт готов к работе!**
