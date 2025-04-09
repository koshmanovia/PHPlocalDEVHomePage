<?php 
$articlesDir = 'articles';
$groups = [];
$articles = [];

if (is_dir($articlesDir)) {
    foreach (glob($articlesDir . '/*', GLOB_ONLYDIR) as $groupDir) {
        $groupName = basename($groupDir);
        if ($groupName !== 'uploads') {
            // Проверяем, пуста ли папка
            $contents = array_diff(scandir($groupDir), array('.', '..'));
            if (empty($contents)) {
                rmdir($groupDir); // Удаляем пустую папку
                continue; // Пропускаем добавление в $groups
            }
            
            // Если папка не пуста, добавляем её в $groups
            $groups[$groupName] = [];
            foreach (glob($groupDir . '/*.php') as $file) {
                $groups[$groupName][] = $file;
                $articles[] = $file;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article'])) {
    $articlePath = $_POST['delete_article'];
    if (file_exists($articlePath) && is_file($articlePath)) {
        unlink($articlePath);
        header('Location: knowledge.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>База знаний</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .group-toggle { cursor: pointer; }
        .articles-list { display: none; margin-left: 20px; }
        .articles-list.active { display: block; }
    </style>
    <script>
        function toggleArticles(groupId) {
            var articlesList = document.getElementById('articles-' + groupId);
            articlesList.classList.toggle('active');
        }
    </script>
</head>
<body>
<?php include 'includes/header.php'; ?>    
    <main>
        <section id="knowledge">
            <h1>База знаний</h1>
            <ul class="knowledge-base">
                <?php foreach ($groups as $groupName => $groupArticles): ?>
                    <li>
                        <span class="group-toggle" onclick="toggleArticles('<?php echo htmlspecialchars($groupName); ?>')">
                            <?php echo htmlspecialchars($groupName); ?> (<?php echo count($groupArticles); ?> статей)
                        </span>
                        <ul id="articles-<?php echo htmlspecialchars($groupName); ?>" class="articles-list">
                            <?php foreach ($groupArticles as $article): ?>
                                <?php 
                                    $fileName = basename($article, '.php');
                                    $fileContent = file($article);
                                    $shortDescription = '';
                                    foreach ($fileContent as $line) {
                                        if (strpos($line, '// Краткое описание:') === 0) {
                                            $shortDescription = trim(str_replace('// Краткое описание:', '', $line));
                                            break;
                                        }
                                    }
                                    $articlePath = $groupName . '/' . $fileName . '.php';
                                ?>
                                <li>
                                    <a href="<?php echo $article; ?>" target="_blank">
                                        <?php echo htmlspecialchars($fileName); ?>
                                    </a> - 
                                    <?php echo htmlspecialchars($shortDescription); ?>
                                    <div style="display: inline;">
                                        <form method="GET" action="edit_articles.php" style="display: inline;">
                                            <input type="hidden" name="file" value="<?php echo urlencode($articlePath); ?>">
                                            <button type="submit" class="edit-button">Редактировать</button>
                                        </form>
                                        <form method="POST" action="knowledge.php" style="display: inline;">
                                            <input type="hidden" name="delete_article" value="<?php echo $article; ?>">
                                            <button type="submit" class="delete-button" onclick="return confirm('Вы уверены, что хотите удалить эту статью?');">Удалить</button>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
<?php include 'includes/footer.php'; ?> 
</body>
</html>