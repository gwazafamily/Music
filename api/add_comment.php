<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to comment']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$songId = isset($input['song_id']) ? (int)$input['song_id'] : 0;
$comment = isset($input['comment']) ? sanitizeInput($input['comment']) : '';

if (!$songId) {
    echo json_encode(['success' => false, 'message' => 'Invalid song ID']);
    exit();
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit();
}

if (strlen($comment) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Comment is too long (max 1000 characters)']);
    exit();
}

// Check if song exists
$song = getSongById($songId);
if (!$song) {
    echo json_encode(['success' => false, 'message' => 'Song not found']);
    exit();
}

$userId = $_SESSION['user_id'];

if (addComment($songId, $userId, $comment)) {
    echo json_encode(['success' => true, 'message' => 'Comment added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
}
?>