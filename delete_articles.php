<?php
// Путь к директории, где хранятся статьи
$articlesDir = 'articles';
function deleteFolder($folder)
{
    if(!is_dir($folder)) 
    {
        return;
    }

    $files = array_diff(scandir($folder),['.','..']);
    foreach($files as $file)
    {
        $filePath = $folder . DIRECTORY_SEPARATOR . $file;
        
        if(strtoupper(substr(PHP_OS,0,3) === 'WIN'))
        {
            $filePath = mb_convert_encoding($filePath, 'Windows-1251', 'UTF-8');
        }

        if(is_dir($filePath))
        {
           deleteFolder($filePath);
        }
        else
        {
            unlink($filePath);
        }


        if(strtoupper(substr(PHP_OS,0,3) === 'WIN'))
        {
            $folder = mb_convert_encoding($folder, 'Windows-1251', 'UTF-8');
        }
        rmdir($folder);
    }

}
// Если передан параметр удаления
if (isset($_GET['file'])) {
    $articleFile = $articlesDir . '/' . basename($_GET['file']);
    $articleFolder = $articlesDir . '/uploads/' . pathinfo($articleFile, PATHINFO_FILENAME);
    
    if(strtoupper(substr(PHP_OS,0,3)==='WIN'))
        {
            $filePath = mb_convert_encoding($articleFolder, 'Windows-1251', 'UTF-8');
        }
    
    // Удаляем папку с файлами, если она существует
    if (is_dir($articleFolder)) {
        array_map('unlink', glob("$articleFolder/*.*")); // Удаляем все файлы в папке
        rmdir($articleFolder); // Удаляем саму папку
    }
    // Удаляем саму статью
    if (file_exists($articleFile)) {
        unlink($articleFile);
    }
    header('Location: index.php');
    exit;
}
// Считываем список всех статей
$articles = [];
if (is_dir($articlesDir)) {
    foreach (glob($articlesDir . '/*.php') as $file) {
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
    <?php include 'includes/header.php'; ?>  
    <h1>Удалить статью</h1>

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


