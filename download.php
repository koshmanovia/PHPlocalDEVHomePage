<?php
if (isset($_GET['file'])) 
{
    
    $filePath = urldecode($_GET['file']); // Декодируем путь из URL
    $absolutePath = realpath($filePath); // Получаем абсолютный путь

   

    // Проверяем существование файла
    if (!$absolutePath || !file_exists($absolutePath)) 
    {
        die("Ошибка: Файл не найден.");
    }

    // Очищаем буфер вывода перед отправкой файла
    ob_clean();
    flush();

    // Заголовки для скачивания
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($absolutePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($absolutePath));

    // Читаем файл и отправляем в поток
    readfile($absolutePath);
    exit;
}
?>