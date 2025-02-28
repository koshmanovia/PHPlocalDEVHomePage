<?php 
$articlesDir = 'articles';
$uploadsDir = "$articlesDir/uploads";

if (!isset($_GET['file'])) 
{
    die('Не указана статья для редактирования.');
}

$articleFile = "$articlesDir/" . basename($_GET['file']);
if (!file_exists($articleFile)) 
{
    die('Статья не найдена.');
}

$fileContent = file_get_contents($articleFile);
$title = '';
$shortDescription = '';
$fullDescription = '';
$attachedFiles = [];

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
    return preg_replace('/[^a-zA-Zа-яА-Я0-9_\-]/u', '_', $text);//$transliterator->transliterate($text));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $newTitle = trim($_POST['title']);
    $newShortDescription = trim($_POST['short_description']);
    $newDescription = trim($_POST['full_description']);
    
    $oldArticleSlug = translit($title);
    $newArticleSlug = translit($newTitle);

    $oldUploadDir = "$uploadsDir/$oldArticleSlug";
    $newUploadDir = "$uploadsDir/$newArticleSlug";

    $oldUploadFile = "articles/$oldArticleSlug.php"; 
    $newUploadFile = "articles/$newArticleSlug.php"; 

    error_log("newUploadFile - $newUploadFile \n oldUploadFile - $oldUploadFile\n \n \n");
    if($oldArticleSlug !== $newArticleSlug)
    {
        if(is_dir($oldUploadDir))
        {
            rename($oldUploadDir, $newUploadDir);
        }
        if(is_file($oldUploadFile))
        {   
           
            copy($oldUploadFile, $newUploadFile);  
            $articleFile = $newUploadFile;          
        }
    }
    elseif(!is_dir($newUploadDir))
    {
        mkdir($newUploadDir, 0777,true);
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
    $articleSlug = basename($_GET['file'], );
    if(!empty($_POST['title']))
    {
        $articleSlug = translit(trim($_POST['title']));
    }
    if (!empty($_POST['delete_files'])) 
    {
        foreach ($_POST['delete_files'] as $fileToDelete) 
        {
            $decodeFile = rawurlencode($fileToDelete);
            $fullPath = "$newUploadDir/$decodeFile";
            if (file_exists($fullPath)) 
            {
                unlink($fullPath);                
            }
            $encodedPath = "$uploadsDir/$articleSlug" . rawurlencode($decodeFile);
            if (($key = array_search($encodedPath, $attachedFiles)) !== false) 
            {
                unset($attachedFiles[$key]);
            }
        }
    }
    $attachedFiles = [];
    if(is_dir("$uploadsDir/$articleSlug"))
    {
        $files = scandir("$uploadsDir/$articleSlug");
        foreach($files as $file)
        {      
            if($file !== "." && $file !== "..")
            {
                $attachedFiles[] = "$uploadsDir/$articleSlug/" . rawurlencode(basename($file));
            }
        }
    }
    $allFiles = $attachedFiles;

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
            $articleContent .= "        <li><a href=\"" . $file . "\" download>"  .htmlspecialchars(basename(rawurldecode($file))) ."</a></li>\n";
        }
        $articleContent .= "    </ul>\n";
    }
    
    $articleContent .= "</body>\n</html>";    
    
    file_put_contents($articleFile, $articleContent);
    header("Location: edit_articles.php?file=" . urlencode(basename($articleFile))); 
    unlink($oldUploadFile);
    exit;
}
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
    <form action="" method="post" enctype="multipart/form-data">
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
                        <?php $fileName =rawurldecode( basename($file)); $file;?> 
                        <label>
                            <input type="checkbox" name="delete_files[]" value="<?php echo htmlspecialchars(rawurldecode($fileName)); ?>"> 
                            Удалить
                        </label>                        
                        <a href=<?php echo htmlspecialchars(rawurldecode($file)); ?>" download>  
                            <?php echo htmlspecialchars((rawurldecode($fileName))); ?>                    
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
</body>
</html>