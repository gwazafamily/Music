<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_POST && isset($_POST['song_ids'])) {
    $songIds = json_decode($_POST['song_ids'], true);
    
    if (!is_array($songIds) || empty($songIds)) {
        header('Location: browse.php');
        exit();
    }
    
    // Get songs from database
    $pdo = getConnection();
    $placeholders = str_repeat('?,', count($songIds) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM songs WHERE id IN ($placeholders)");
    $stmt->execute($songIds);
    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($songs)) {
        header('Location: browse.php');
        exit();
    }
    
    // Create ZIP file
    $zip = new ZipArchive();
    $zipFileName = 'musichub_bulk_download_' . date('Y-m-d_H-i-s') . '.zip';
    $zipPath = sys_get_temp_dir() . '/' . $zipFileName;
    
    if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
        die('Cannot create ZIP file');
    }
    
    foreach ($songs as $song) {
        if (file_exists($song['file_path'])) {
            $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $song['title']) . '_' . 
                       preg_replace('/[^a-zA-Z0-9_-]/', '_', $song['artist']) . '.mp3';
            $zip->addFile($song['file_path'], $fileName);
            
            // Increment download count
            incrementDownloads($song['id']);
        }
    }
    
    $zip->close();
    
    // Send ZIP file
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    readfile($zipPath);
    unlink($zipPath); // Clean up
    exit();
}

header('Location: browse.php');
exit();
?>