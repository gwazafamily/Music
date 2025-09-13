<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$search = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';

if (strlen($search) < 2) {
    echo json_encode(['results' => []]);
    exit();
}

$songs = getSongs(10, 0, $search);

// Format results for JSON response
$results = array_map(function($song) {
    return [
        'id' => $song['id'],
        'title' => $song['title'],
        'artist' => $song['artist'],
        'album' => $song['album'],
        'genre' => $song['genre'],
        'downloads' => $song['downloads'],
        'likes' => $song['likes']
    ];
}, $songs);

echo json_encode(['results' => $results]);
?>