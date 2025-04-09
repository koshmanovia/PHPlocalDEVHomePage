<?php 
$linksFile = 'includes/links.json';

if (file_exists($linksFile)) {
    $links = json_decode(file_get_contents($linksFile), true);
} else {
    $links = [];
}

// Обработка удаления ссылки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_index'])) {
    $index = (int)$_POST['delete_index'];
    if (isset($links[$index])) {
        unset($links[$index]);
        $links = array_values($links);
        file_put_contents($linksFile, json_encode($links, JSON_PRETTY_PRINT));
        header('Location: index.php');
        exit;
    }
}

// Обработка редактирования ссылки
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
    <title>Проекты</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php include 'includes/header.php'; ?>    
    <main>
        <div class="container">
            <h1>Список сайтов</h1>
            
            <ul class="knowledge-base">
                <?php foreach ($links as $index => $link): ?>
                    <li>
                        <div style="display: flex; align-items: center;">
                            <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank">
                                <?php echo htmlspecialchars($link['url']); ?>
                            </a> - 
                            <?php echo htmlspecialchars($link['description']); ?>
                            <form method="GET" action="index.php" style="margin-left: 10px;">
                                <input type="hidden" name="edit" value="<?php echo $index; ?>">
                                <button type="submit" class="edit-button">Редактировать</button>
                            </form>
                            <form method="POST" action="index.php" style="margin-left: 10px;">
                                <input type="hidden" Ascendancy" name="delete_index" value="<?php echo $index; ?>">
                                <button type="submit" class="delete-button" onclick="return confirm('Вы уверены, что хотите удалить эту ссылку?');">Удалить</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($editLink): ?>
                <h2>Редактировать сайт</h2>
                <form method="POST" action="index.php">
                    <input type="hidden" name="edit_index" value="<?php echo $editLink['index']; ?>">
                    <label for="url">URL:</label>
                    <input type="text" id="url" name="url" value="<?php echo htmlspecialchars($editLink['url']); ?>" required>
                    <label for="description">Описание:</label>
                    <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($editLink['description']); ?>" required>
                    <button type="submit">Сохранить изменения</button>
                    <a href="index.php" class="button">Отменить</a>
                </form>
            <?php else: ?>
                <a href="add_link.php" class="button">Добавить сайт</a>
            <?php endif; ?>
        </div>
    </main>
<?php include 'includes/footer.php'; ?> 
</body>
</html>