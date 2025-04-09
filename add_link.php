<?php 
$linksFile = 'includes/links.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url']) && !empty($_POST['description'])) {
    if (file_exists($linksFile)) {
        $links = json_decode(file_get_contents($linksFile), true);
    } else {
        $links = [];
    }
    $newLink = [
        'url' => trim($_POST['url']),
        'description' => trim($_POST['description'])
    ];   
    $links[] = $newLink;    
    file_put_contents($linksFile, json_encode($links, JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить сайт</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php include 'includes/header.php'; ?>    
    <main>
        <div class="container">
            <h1>Добавить сайт</h1>
            <form method="POST" action="add_link.php">
                <label for="url">URL:</label>
                <input type="text" id="url" name="url" placeholder="http://localhost:port" required>
                <label for="description">Описание:</label>
                <input type="text" id="description" name="description" placeholder="Описание" required>
                <button type="submit" class="button">Добавить</button>
                <a href="index.php" class="button">Отменить</a>
            </form>
        </div>
    </main>
<?php include 'includes/footer.php'; ?> 
</body>
</html>