<?php

define('DOWNLOAD_DIR', __DIR__ . '/articles/uploads/');
define('COUNT_DIR', __DIR__ . '/counts/');
define('DOWNLOAD_LIMIT', 10);

// Check if counts directory exists, create if not
if (!is_dir(COUNT_DIR)) {
    mkdir(COUNT_DIR, 0755, true);
}

if (!isset($_GET['file'])) {
    die('File not specified');
}
$file = $_GET['file'];

// Sanitize the file path to prevent directory traversal
$file_path = realpath(DOWNLOAD_DIR . $file);
if ($file_path === false || strpos($file_path, realpath(DOWNLOAD_DIR)) !== 0 || !is_file($file_path)) {
    die('Invalid file path or file not found');
}

// Create count file name
$count_file_name = str_replace('/', '_', $file) . '.count';
$count_file_path = COUNT_DIR . $count_file_name;

// Open count file with exclusive lock
$fp = fopen($count_file_path, 'a+');
if (!flock($fp, LOCK_EX)) {
    die('Unable to lock count file');
}

// Read current count
$count = (filesize($count_file_path) > 0) ? fread($fp, filesize($count_file_path)) : '0';
$count = intval($count);

// Check if count is less than limit
if ($count < DOWNLOAD_LIMIT) {
    $count++;
    ftruncate($fp, 0);
    fwrite($fp, $count);
    flock($fp, LOCK_UN);
    fclose($fp);
    
    // Force download with correct headers
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($file_path);
    exit;
} else {
    flock($fp, LOCK_UN);
    fclose($fp);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Download limit reached';
    exit;
}
?>