# Документация по CSS для AMMDr (aMagic Markdown Reader)

## Обзор стилей

Этот CSS-файл обеспечивает стилизацию всех компонентов AMMDr, включая:
- Основную структуру документа
- Навигационное меню
- Контентную область
- Элементы управления
- Адаптивный дизайн

## Структура документа

### 1. Базовые стили
```css
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, 
                sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
}
```
- Сброс стандартных стилей браузера
- Установка box-model
- Определение основного шрифта

### 2. Основная структура документа
```css
body {
    display: grid;
    grid-template-rows: auto 1fr auto;
    grid-template-columns: 300px 1fr;
    grid-template-areas: 
        "header header"
        "nav main"
        "footer footer";
    min-height: 100vh;
}
```
- Использует CSS Grid для макета
- Фиксированный сайдбар (300px) и гибкое основное содержимое
- Минимальная высота - весь экран (100vh)

## Компоненты

### 1. Шапка (Header)
```css
header {
    grid-area: header;
    background: #f8f9fa;
    padding: 1rem;
    border-bottom: 1px solid #e1e4e8;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
```
- Светлый фон с теню
- Фиксированная высота
- Граница снизу

### 2. Навигация (Nav)
```css
nav {
    grid-area: nav;
    background: #f8f9fa;
    padding: 0.5rem;
    border-right: 1px solid #e1e4e8;
    height: calc(100vh - 120px);
}
```
- Светлый фон
- Вертикальный скролл для длинных списков
- Тонкая граница справа

### 3. Основное содержимое (Main)
```css
main {
    grid-area: main;
    padding: 2rem;
    overflow-y: auto;
    height: calc(100vh - 120px);
    background-color: #fff;
}
```
- Белый фон
- Внутренние отступы
- Автоматическая прокрутка при необходимости

### 4. Подвал (Footer)
```css
footer {
    grid-area: footer;
    background: #f8f9fa;
    padding: 1rem;
    border-top: 1px solid #e1e4e8;
}
```
- Светлый фон
- Центрированный текст
- Граница сверху

## Элементы навигации

### 1. Папки
```css
.folder {
    margin-left: -0.5rem;
    padding-left: 0;
}

.folder-name {
    cursor: pointer;
    font-weight: 600;
}
```
- Интерактивные (курсор pointer)
- Жирный текст для названий
- Иконки меняются при раскрытии

### 2. Файлы
```css
.file a {
    position: relative;
    padding-left: 1.2rem;
    display: flex;
    align-items: flex-start;
}
```
- Иконка документа
- Подсветка при наведении
- Особый стиль для активного файла

## Элементы управления

### 1. Кнопки навигации
```css
.nav-btn {
    padding: 1px 16px;
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}
```
- Светлый фон
- Скругленные углы
- Эффект при наведении

### 2. Поле поиска
```css
#search.form-control {
    width: 80%;
    padding: 10px 15px;
    border: 1px solid #ced4da;
    border-radius: 10px;
    height: 30px;
}
```
- Адаптивная ширина
- Закругленные углы
- Изменение цвета при фокусе и вводе

## Адаптивный дизайн

### Мобильная версия (≤600px)
```css
@media (max-width: 600px) {
    body {
        grid-template-columns: 1fr;
        grid-template-areas: 
            "header"
            "main"
            "footer";
    }
    nav {
        position: fixed;
        left: -280px;
        width: 280px;
    }
    nav.active {
        left: 0;
    }
}
```
- Скрытие сайдбара
- Гамбургер-меню
- Полноширинный контент

## Особенности

1. **Скроллбары**:
   - Стилизованные полосы прокрутки
   - Разный дизайн для навигации и контента

2. **Анимации**:
   - Плавные переходы для интерактивных элементов
   - Пульсация индикатора загрузки

3. **Темная тема**:
   - Поддержка системных предпочтений
   - Автоматическое переключение цветов

4. **Markdown-стили**:
   - Специальные стили для рендеринга Markdown
   - Форматирование кодо-блоков

## Использование

1. Подключите файл в head документа:
```html
<link rel="stylesheet" href="ammdr.css">
```

2. Убедитесь, что HTML-структура соответствует ожидаемой:
- Элементы с правильными классами
- Соответствующие grid-areas

3. Для кастомизации:
- Изменяйте цветовые переменные
- Настраивайте размеры в медиа-запросах
- Модифицируйте анимации по необходимости

Этот CSS обеспечивает согласованный и профессиональный внешний вид для всех компонентов AMMDr, с акцентом на удобство использования и адаптивность.