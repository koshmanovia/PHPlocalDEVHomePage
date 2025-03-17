<?php 
$linksFile = 'includes/links.json';
$articlesDir = 'articles';
$articles = [];
if (is_dir($articlesDir)) {
    foreach (glob($articlesDir . '/*.php') as $file) {
        $articles[] = $file;
    }
}
if (file_exists($linksFile)) {
    $links = json_decode(file_get_contents($linksFile), true);
} else {
    $links = [];
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url']) && !empty($_POST['description'])) {
    $newLink = [
        'url' => trim($_POST['url']),
        'description' => trim($_POST['description'])
    ];   
    $links[] = $newLink;    
    file_put_contents($linksFile, json_encode($links, JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_index'])) {
    $index = (int)$_POST['delete_index'];
    if (isset($links[$index])) {
        unset($links[$index]);
        $links = array_values($links); // Переиндексация массива
        file_put_contents($linksFile, json_encode($links, JSON_PRETTY_PRINT));
        header('Location: index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_index']) && !empty($_POST['url']) && !empty($_POST['description'])) {
    $index = (int)$_POST['edit_index'];
    if (isset($links[$index])) {
        $links[$index] = [
            'url' => trim($_POST['url']),
            'description' => trim($_POST['description'])
        ];
        file_put_contents($linksFile, json_encode($links, JSON_PRETTY_PRINT));
        header('Location: index.php');
        exit;
    }
}
$editLink = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editIndex = (int)$_GET['edit'];
    if (isset($links[$editIndex])) {
        $editLink = $links[$editIndex];
        $editLink['index'] = $editIndex;
    }
}
?>  


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
    <div class="container">
            <h1>Home page for local dev</h1>            
            <!-- Форма для добавления/редактирования ссылки -->
            <form method="POST" action="index.php">
                <?php if ($editLink): ?>
                    <input type="hidden" name="edit_index" value="<?php echo $editLink['index']; ?>">
                <?php endif; ?>
                <label for="url">URL:</label>
                <input type="text" id="url" name="url" 
                       value="<?php echo $editLink ? htmlspecialchars($editLink['url']) : ''; ?>" 
                       placeholder="http://localhost:port" required>
                <label for="description">Description:</label>
                <input type="text" id="description" name="description" 
                       value="<?php echo $editLink ? htmlspecialchars($editLink['description']) : ''; ?>" 
                       placeholder="Description" required>
                <button type="submit"><?php echo $editLink ? 'Сохранить изменения' : 'Add Link'; ?></button>
                <?php if ($editLink): ?>
                    <a href="index.php" class="button">Отменить редактирование</a>
                <?php endif; ?>
            </form>
            
            <h2>Knowledge Base</h2>
            <ul class="knowledge-base">
                <?php foreach ($links as $index => $link): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank">
                            <?php echo htmlspecialchars($link['url']); ?>
                        </a> - 
                        <?php echo htmlspecialchars($link['description']); ?>
                        <div style="display: inline;">
                            <form method="GET" action="index.php" style="display: inline;">
                                <input type="hidden" name="edit" value="<?php echo $index; ?>">
                                <button type="submit" style="margin-left: 10px; color: blue;">Редактировать</button>
                            </form>
                            <form method="POST" action="index.php" style="display: inline;">
                                <input type="hidden" name="delete_index" value="<?php echo $index; ?>">
                                <button type="submit" 
                                        onclick="return confirm('Вы уверены, что хотите удалить эту ссылку?');" 
                                        style="margin-left: 10px; color: red;">Удалить
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
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
    <?php include 'includes/footer.php'; ?> 
</body>
</html>
