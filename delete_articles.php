<?php
// Путь к директории, где хранятся статьи
$articlesDir = 'articles';
function deleteFolder($folder)
{
    if (!is_dir($folder)) 
    {
        return;
    }
    $files = glob($folder . '/*', GLOB_BRACE);
    foreach ($files as $file) 
    {
        if (is_dir($file)) 
        {
            deleteFolder($file);
        } 
        else 
        {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
            {
                $file = mb_convert_encoding($file, 'Windows-1251', 'UTF-8');
            }
            unlink($file);
        }
    }
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
    {
        $folder = mb_convert_encoding($folder, 'Windows-1251', 'UTF-8');
    }
    rmdir($folder);
}
// Если передан параметр удаления
if (isset($_GET['file'])) 
{
    $articleFile = $articlesDir . '/' . basename($_GET['file']);
    $articleFolder = $articlesDir . '/uploads/' . str_replace(' ', '_',pathinfo($articleFile, PATHINFO_FILENAME));
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $articleFile = mb_convert_encoding($articleFile, 'Windows-1251', 'UTF-8');
        $articleFolder = mb_convert_encoding(str_replace(' ', '_',$articleFolder), 'Windows-1251', 'UTF-8');
    }
    // Удаляем папку с файлами, если она существует
    if (is_dir($articleFolder)) 
    {
        deleteFolder($articleFolder);
    }
    // Удаляем саму статью
    if (file_exists($articleFile)) 
    {
        unlink($articleFile);
    }
    header('Location: index.php');
    exit;
}
// Считываем список всех статей
$articles = [];
if (is_dir($articlesDir)) 
{
    foreach (glob($articlesDir . '/*.php') as $file) 
    {
        $articles[] = basename($file);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление статьи</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>    <h1>Удалить статью</h1>
    <?php if (!empty($articles)): ?>
        <ul>
            <?php foreach ($articles as $article): ?>
                <li>
                    <?php 
                        $fileName = basename($article, '.php'); // Название статьи без расширения
                    ?>
                    <?php echo htmlspecialchars($fileName); ?>
                    <a href="?file=<?php echo urlencode($article); ?>" onclick="return confirm('Вы уверены, что хотите удалить эту статью?');">Удалить</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Нет статей для удаления.</p>
    <?php endif; ?>
</body>
</html>
