<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$songId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$song = getSongById($songId);

if (!$song || !file_exists($song['file_path'])) {
    header('Location: index.php');
    exit();
}

// Increment download count
incrementDownloads($songId);

// Set headers for file download
$fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $song['title']) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $song['artist']) . '.mp3';

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($song['file_path']));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output file
readfile($song['file_path']);
exit();
?>