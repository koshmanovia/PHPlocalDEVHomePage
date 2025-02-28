<?php
// Путь для хранения файлов статей
$articlesDir = 'articles';

// Создание папки для статей, если её нет
if (!is_dir($articlesDir)) 
{
    mkdir($articlesDir, 0777, true);
}
// Считывание всех статей
$articles = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    mb_internal_encoding("UTF-8");
    setlocale(LC_ALL, "ru_RU.UTF-8");

    $title = trim($_POST['title']);
    $shortDescription = trim($_POST['short_description']);
    $fullDescription = trim($_POST['full_description']);
    // Генератор папки для статьи
    $articleSlug = preg_replace('/[^a-zA-Zа-яА-Я0-9_\-]/u', '_', $title); 
    $uploadDir = $articlesDir . '/uploads/' . $articleSlug;
    if (!is_dir($uploadDir)) 
    {
        mkdir($uploadDir, 0777, true);
    }
    $uploadedFiles = [];
    
    if (isset($_FILES['files']) && count($_FILES['files']['name']) > 0) 
    {
        foreach ($_FILES['files']['name'] as $key => $fileName)
        {
            if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) 
            {
                $fileTmpPath = $_FILES['files']['tmp_name'][$key];
                $safeFileName = basename($fileName);
                $destination = $uploadDir . '/' . $safeFileName;
                if (move_uploaded_file($fileTmpPath, $destination)) 
                {
                    $uploadedFiles[] = $destination; // Сохраняем пути к загруженным файлам
                }
            }
        }
    }
    // Создание страницы
    $articleFileName = $articlesDir . '/' . $title . '.php';
    $articleContent = "<?php \n// Название: " . addslashes($title) . "\n";
    $articleContent .= "// Краткое описание: " . addslashes($shortDescription) . "\n";
    $articleContent .= "?>";
    $articleContent .= "<!DOCTYPE html>\n";
    $articleContent .= "<html lang=\"ru\">\n";
    $articleContent .= "<head>\n";
    $articleContent .= "    <meta charset=\"UTF-8\">\n";
    $articleContent .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
    $articleContent .= "    <title>" . htmlspecialchars($title) . "</title>\n";
    $articleContent .= "    <link rel=\"stylesheet\" href=\"../css/styles.css\">";
    $articleContent .= "</head>\n";
    $articleContent .= "<body>\n";
    $articleContent .= "    <?php include '../includes/header.php'; ?> "; 
    $articleContent .= "    <h1>" . htmlspecialchars($title) . "</h1>\n";
    $articleContent .= "    <p style=\"display: none;\">" . nl2br(htmlspecialchars($shortDescription)) . "</p>\n";
    $articleContent .= "    <p class=\"full-description\">" . nl2br(htmlspecialchars($fullDescription))  . "</p>\n";
    // Добавляем ссылки на загруженные файлы
    if (!empty($uploadedFiles)) 
    {
        $articleContent .= "    <h2>Приложенные файлы:</h2>\n";
        $articleContent .= "    <ul>\n";
        foreach ($uploadedFiles as $file) 
        {
            $fileName = basename($file);
            $articleContent .= "        <li><a href=\"../download.php?file=" . htmlspecialchars($file) . "\" download>" . htmlspecialchars($fileName) . "</a></li>\n";
        }
        $articleContent .= "    </ul>\n";
    }
    $articleContent .= "</body>\n";
    $articleContent .= "</html>";
    file_put_contents($articleFileName, $articleContent);
    // Перенаправление для предотвращения повторной отправки формы
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
if (is_dir($articlesDir)) 
{
    foreach (glob($articlesDir . '/*.php') as $file) 
    {
        $articles[] = $file;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление статьи</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>  
    <h1>Добавить статью</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <label>
            Название:
            <input type="text" name="title" required>
        </label>
        <br><br>
        <label>
            Краткое описание:
            <textarea name="short_description" required></textarea>
        </label>
        <br><br>
        <label>
            Полное описание:
            <textarea name="full_description" required></textarea>
        </label>
        <br><br>
        <label>
            Приложить файл:
            <input type="file" name="files[]" multiple>
        </label>
        <br><br>
        <button type="submit">Сохранить</button>
    </form>
</body>
</html>
