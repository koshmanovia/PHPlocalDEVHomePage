<?php 
$articlesDir = 'articles';
$articles = [];
if (is_dir($articlesDir)) {
    foreach (glob($articlesDir . '/*.php') as $file) {
        $articles[] = $file;
    }
} ?>  


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проекты и база знаний</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php include 'includes/header.php'; ?>    
    <main>
        <section id="projects">
            <h2>Рабочие проекты</h2>
            <ul class="knowledge-base">
                <li><a href="http://localhost:85" target="_blank">http://localhost:85</a> - phpAdmin(XAMPP)</li>
                <li><a href="http://localhost:83" target="_blank">http://localhost:83</a> - nsddata front</li>
                <li><a href="http://localhost:84" target="_blank">http://localhost:84</a> - nsddata back</li>
                <li><a href="http://localhost:81" target="_blank">http://localhost:82</a> - nsd front</li>
                <li><a href="http://localhost:82" target="_blank">http://localhost:81</a> - nsd back</li>                
            </ul>
        </section>
        <section id="knowledge">
            <h2>База знаний</h2>         
                <?php foreach ($articles as $article): ?>    
                <?php 
                    $fileName = basename($article, '.php'); // Убираем расширение
                    $fileContent = file($article); // Читаем содержимое файла построчно
                    $shortDescription = '';
        
                    // Ищем строку с кратким описанием
                    foreach ($fileContent as $line) {
                        if (strpos($line, '// Краткое описание:') === 0) {
                        $shortDescription = trim(str_replace('// Краткое описание:', '', $line));
                        break;
                        }
                    }
                ?>
            <li>
                <a href="<?php echo $article; ?>" target="_blank">
                <?php echo $fileName; ?> </a>
                <small><?php echo htmlspecialchars($shortDescription); ?></small>                
                <a href="edit_articles.php?file=<?php echo urldecode($fileName); echo('.php'); ?>" class="button">Редактировать статью</a>                  
                <br>
                
            </li>
<?php endforeach; ?>





        </section>
    </main>
    <footer>
       
    </footer>
</body>
</html>
