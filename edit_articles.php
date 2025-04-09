<?php 
$articlesDir = 'articles';
$uploadsDir = "$articlesDir/uploads";

if (!isset($_GET['file'])) 
{
    die('Не указана статья для редактирования.');
}

$rawFilePath = urldecode($_GET['file']); // Декодируем путь
$articleFile = "$articlesDir/" . $rawFilePath;
if (!file_exists($articleFile)) 
{
    die('Статья не найдена. Проверяемый путь: ' . htmlspecialchars($articleFile));
}

$fileContent = file_get_contents($articleFile);
$title = '';
$shortDescription = '';
$fullDescription = '';
$attachedFiles = [];
$currentGroup = basename(dirname($articleFile));

if (preg_match('/<title>(.*?)<\/title>/', $fileContent, $matches)) 
{
    $title = htmlspecialchars_decode($matches[1]);
}

if (preg_match('/<p[^>]*style="display:\s*none;"[^>]*>(.*?)<\/p>/', $fileContent, $matches)) 
{    
    $shortDescription = htmlspecialchars_decode($matches[1]);
}
if (preg_match('/<p class="full-description">(.*?)<\/p>/s', $fileContent, $matches)) 
{
    $fullDescription = htmlspecialchars_decode(strip_tags($matches[1]));
}

if (preg_match_all('/<a href="([^"]+)" download>/', $fileContent, $matches)) 
{
    $attachedFiles = $matches[1];    
}

function translit($text) 
{
    $transliterator = Transliterator::create('Any-Latin; Latin-ASCII; Lower');
    return preg_replace('/[^a-z0-9_-]/', '-', $transliterator->transliterate($text));
}

$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $newTitle = trim($_POST['title']);
    $newShortDescription = trim($_POST['short_description']);
    $newDescription = trim($_POST['full_description']);
    
    $newGroup = trim($_POST['group']) === 'new_group' ? trim($_POST['new_group']) : trim($_POST['group']);
    if (strtolower($newGroup) === 'uploads') {
        $errorMessage = 'Имя "uploads" зарезервировано и не может быть использовано для группы.';
    } else {
        $fileName = basename($articleFile);
        $newFilePath = $articleFile;

        if ($newGroup && $newGroup !== $currentGroup) {
            $newDir = $articlesDir . '/' . $newGroup;
            if (!is_dir($newDir)) {
                mkdir($newDir, 0777, true);
            }
            $newFilePath = $newDir . '/' . $fileName;
            if (file_exists($articleFile) && $articleFile !== $newFilePath) {
                rename($articleFile, $newFilePath);
            }
        }

        $articleSlug = translit($newTitle);
        $newUploadDir = "$uploadsDir/$articleSlug";
        if (!is_dir($newUploadDir)) 
        {
            mkdir($newUploadDir, 0777, true);
        }
        
        $uploadedFiles = [];
        if (!empty($_FILES['files']['name'][0])) 
        {
            foreach ($_FILES['files']['name'] as $key => $fileName) 
            {
                if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) 
                {
                    $fileTmpPath = $_FILES['files']['tmp_name'][$key];
                    $safeFileName = basename($fileName);
                    $destination = "$newUploadDir/" . rawurlencode($safeFileName);
                    if (move_uploaded_file($fileTmpPath, $destination)) 
                    {
                        $uploadedFiles[] = $destination;
                    }
                }
            }
        }
        
        if (!empty($_POST['delete_files'])) 
        {
            foreach ($_POST['delete_files'] as $fileToDelete) 
            {
                $fullPath = "$newUploadDir/" . rawurlencode(basename($fileToDelete));
                if (file_exists($fullPath)) 
                {
                    unlink($fullPath);
                    if (($key = array_search($fullPath, $attachedFiles)) !== false) 
                    {
                        unset($attachedFiles[$key]);
                    }
                }
            }
        }
        
        $allFiles = array_merge($attachedFiles, $uploadedFiles);
        
        $articleContent = "<?php \n// Название: " . addslashes($newTitle) . "\n";
        $articleContent .= "// Краткое описание: " . addslashes($newShortDescription) . "\n";
        $articleContent .= "?>";
        $articleContent .= "<!DOCTYPE html>\n";
        $articleContent .= "<html lang=\"ru\">\n";
        $articleContent .= "<head>\n";
        $articleContent .= "    <meta charset=\"UTF-8\">\n";
        $articleContent .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
        $articleContent .= "    <meta name=\"description\" content=\"" . htmlspecialchars($newShortDescription) . "\">\n";
        $articleContent .= "    <title>" . htmlspecialchars($newTitle) . "</title>\n";
        $articleContent .= "    <link rel=\"stylesheet\" href=\"../css/styles.css\">\n";
        $articleContent .= "</head>\n";
        $articleContent .= "<body>\n";
        $articleContent .= "    <?php include '../includes/header.php'; ?> \n";
        $articleContent .= "    <h1>" . htmlspecialchars($newTitle) . "</h1>\n";
        $articleContent .= "    <p style=\"display: none;\">" . nl2br(htmlspecialchars($newShortDescription)) . "</p>\n";
        $articleContent .= "    <p class=\"full-description\">" . nl2br(htmlspecialchars($newDescription)) . "</p>\n";
        
        if (!empty($allFiles)) 
        {
            $articleContent .= "    <h2>Приложенные файлы:</h2>\n<ul>\n";
            foreach ($allFiles as $file) 
            {
                $fileName = basename(rawurldecode($file));
                $articleContent .= "        <li><a href=\"" . htmlspecialchars($file) . "\" download>" . htmlspecialchars($fileName) . "</a></li>\n";
            }
            $articleContent .= "    </ul>\n";
        }
        
        $articleContent .= "</body>\n</html>";    
        
        file_put_contents($newFilePath, $articleContent);
        header("Location: edit_articles.php?file=" . urlencode($newGroup . '/' . basename($newFilePath)));
        exit;
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
    <title>Редактирование статьи</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <h1>Редактировать статью</h1>
    <?php if ($errorMessage): ?>
        <p style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>
    <form action="" method="post" enctype="multipart/form-data">
        <label>
            Группа:
            <select name="group" id="group" required>
                <?php foreach ($groups as $group): ?>
                    <option value="<?php echo htmlspecialchars($group); ?>" 
                            <?php echo $group === $currentGroup ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($group); ?>
                    </option>
                <?php endforeach; ?>
                <option value="new_group">Новая группа...</option>
            </select>
            <input type="text" name="new_group" id="new_group" placeholder="Имя новой группы" 
                   style="display: <?php echo $currentGroup ? 'none' : 'block'; ?>;">
        </label>
        <br><br>
        <label>
            Название:
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
        </label>
        <br><br>
        <label>
            Краткое описание:
            <textarea name="short_description"><?php echo htmlspecialchars($shortDescription); ?></textarea>
        </label>
        <br><br>
        <label>
            Полное описание:
            <textarea name="full_description" required><?php echo htmlspecialchars($fullDescription); ?></textarea>
        </label>
        <br><br>
        <h2>Старые файлы:</h2>
        <?php if (!empty($attachedFiles)): ?>
            <ul>
                <?php foreach ($attachedFiles as $file): ?>
                    <li>
                        <?php $fileName = rawurldecode(basename($file)); ?>
                        <label>
                            <input type="checkbox" name="delete_files[]" value="<?php echo htmlspecialchars(rawurldecode($fileName)); ?>"> 
                            Удалить
                        </label>
                        <a href="<?php echo htmlspecialchars($file); ?>" download>
                            <?php echo htmlspecialchars(rawurldecode($fileName)); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Нет прикреплённых файлов.</p>
        <?php endif; ?>
        <br>
        <label>
            Добавить новые файлы:
            <input type="file" name="files[]" multiple>
        </label>
        <br><br>
        <button type="submit">Сохранить изменения</button>
    </form>
    <script>
        document.getElementById('group').addEventListener('change', function() {
            var newGroupInput = document.getElementById('new_group');
            newGroupInput.style.display = this.value === 'new_group' ? 'block' : 'none';
        });
    </script>
</body>
</html>