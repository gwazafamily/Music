<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to like songs']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$songId = isset($input['song_id']) ? (int)$input['song_id'] : 0;

if (!$songId) {
    echo json_encode(['success' => false, 'message' => 'Invalid song ID']);
    exit();
}

$userId = $_SESSION['user_id'];

// Check if song exists
$song = getSongById($songId);
if (!$song) {
    echo json_encode(['success' => false, 'message' => 'Song not found']);
    exit();
}

// Check if user has already liked this song
$isLiked = isLikedByUser($songId, $userId);

if ($isLiked) {
    // Unlike the song
    if (unlikeSong($songId, $userId)) {
        echo json_encode(['success' => true, 'liked' => false, 'message' => 'Song unliked']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to unlike song']);
    }
} else {
    // Like the song
    if (likeSong($songId, $userId)) {
        echo json_encode(['success' => true, 'liked' => true, 'message' => 'Song liked']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to like song']);
    }
}
?>