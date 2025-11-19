<?php
// AMMDr Mini - Ultra compact Markdown reader
$files = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.')) as $file) {
    if ($file->getExtension() === 'md') $files[] = $file->getPathname();
}

if ($_POST['search'] ?? '') {
    $query = strtolower($_POST['search']);
    $files = array_filter($files, fn($f) => strpos(strtolower($f), $query) !== false);
}

$menu = '';
foreach ($files as $file) {
    $name = pathinfo($file, PATHINFO_FILENAME);
    $menu .= "<li><a href='#' data-md='$file'>$name</a></li>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MD Reader</title>
    <script src="https://cdn.jsdelivr.net/gh/zerodevx/zero-md@2/dist/zero-md.min.js"></script>
    <style>
        body { margin:0; display:grid; grid-template:"nav main" 1fr / 250px 1fr; height:100vh; }
        nav { background:#f5f5f5; padding:10px; overflow:auto; }
        main { padding:20px; overflow:auto; }
        ul { list-style:none; padding:0; }
        a { display:block; padding:5px; text-decoration:none; color:#333; }
        a:hover { background:#ddd; }
        input { width:100%; padding:5px; margin-bottom:10px; }
    </style>
</head>
<body>
    <nav>
        <input placeholder="Search..." oninput="fetch('?',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:new URLSearchParams({search:this.value})}).then(r=>r.text()).then(html=>document.querySelector('nav').innerHTML=html)">
        <ul><?= $menu ?></ul>
    </nav>
    <main>
        <?= isset($_GET['md']) ? "<zero-md src='{$_GET['md']}'></zero-md>" : "<h2>Select file</h2>" ?>
    </main>
    <script>
        document.addEventListener('click', e => {
            if (e.target.matches('[data-md]')) {
                e.preventDefault();
                document.querySelector('main').innerHTML = `<zero-md src="${e.target.dataset.md}"></zero-md>`;
                history.pushState(null, '', `?md=${encodeURIComponent(e.target.dataset.md)}`);
            }
        });
    </script>
</body>
</html>
