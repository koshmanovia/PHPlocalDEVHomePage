<?php
$articlesDir = 'articles';
if (!is_dir($articlesDir)) 
{
    mkdir($articlesDir, 0777, true);
}

$articles = [];
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    mb_internal_encoding("UTF-8");
    setlocale(LC_ALL, "ru_RU.UTF-8");

    $title = trim($_POST['title']);
    $shortDescription = trim($_POST['short_description']);
    $fullDescription = trim($_POST['full_description']);
    
    // Новый функционал: поддержка групп с проверкой на "uploads"
    $group = trim($_POST['group']) === 'new_group' ? trim($_POST['new_group']) : trim($_POST['group']);
    if (strtolower($group) === 'uploads') {
        $errorMessage = 'Имя "uploads" зарезервировано и не может быть использовано для группы.';
    } else {
        $groupDir = $articlesDir . '/' . $group;
        if (!is_dir($groupDir)) 
        {
            mkdir($groupDir, 0777, true);
        }

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
                        $uploadedFiles[] = $destination;
                    }
                }
            }
        }

        $articleFileName = $groupDir . '/' . $title . '.php';
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
        $articleContent .= "    <p class=\"full-description\">" . nl2br(htmlspecialchars($fullDescription)) . "\n\n\n\n\n\n" . $fileTmpPath . "\n\n\n\n\n\n" . "</p>\n";
        if (!empty($uploadedFiles)) 
        {
            $articleContent .= "    <h2>Приложенные файлы:</h2>\n";
            $articleContent .= "    <ul>\n";
            foreach ($uploadedFiles as $file) 
            {
                $fileName = basename($file);
                $articleContent .= "        <li><a href=\"" . htmlspecialchars($file) . "\" download>" . htmlspecialchars($fileName) . "</a></li>\n";
            }
            $articleContent .= "    </ul>\n";
        }
        $articleContent .= "</body>\n";
        $articleContent .= "</html>";
        file_put_contents($articleFileName, $articleContent);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

if (is_dir($articlesDir)) 
{
    foreach (glob($articlesDir . '/*/*.php') as $file) 
    {
        $articles[] = $file;
    }
}

$groups = array_filter(glob($articlesDir . '/*', GLOB_ONLYDIR), function($dir) {
    return is_dir($dir) && basename($dir) !== 'uploads';
});
$groups = array_map('basename', $groups);
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
    <?php if ($errorMessage): ?>
        <p style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>
    <form action="" method="post" enctype="multipart/form-data">
        <label>
            Группа:
            <select name="group" id="group" required>
                <?php foreach ($groups as $group): ?>
                    <option value="<?php echo htmlspecialchars($group); ?>">
                        <?php echo htmlspecialchars($group); ?>
                    </option>
                <?php endforeach; ?>
                <option value="new_group">Новая группа...</option>
            </select>
            <input type="text" name="new_group" id="new_group" placeholder="Имя новой группы" style="display: none;">
        </label>
        <br><br>
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
        <button type="submit" class="button">Сохранить</button>
    </form>
    <script>
        document.getElementById('group').addEventListener('change', function() {
            var newGroupInput = document.getElementById('new_group');
            newGroupInput.style.display = this.value === 'new_group' ? 'block' : 'none';
        });
    </script>
</body>
</html>