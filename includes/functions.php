<?php
require_once 'config/database.php';

// User functions
function registerUser($username, $email, $password) {
    $pdo = getConnection();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hashedPassword]);
    } catch(PDOException $e) {
        return false;
    }
}

function loginUser($username, $password) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function getUserById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Song functions
function uploadSong($title, $artist, $album, $genre, $filePath, $fileSize, $duration, $lyrics, $uploaderId) {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO songs (title, artist, album, genre, file_path, file_size, duration, lyrics, uploader_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$title, $artist, $album, $genre, $filePath, $fileSize, $duration, $lyrics, $uploaderId]);
    } catch(PDOException $e) {
        return false;
    }
}

function getSongs($limit = 20, $offset = 0, $search = '') {
    $pdo = getConnection();
    
    if ($search) {
        $stmt = $pdo->prepare("SELECT s.*, u.username as uploader_name FROM songs s 
                              JOIN users u ON s.uploader_id = u.id 
                              WHERE s.title LIKE ? OR s.artist LIKE ? OR s.album LIKE ?
                              ORDER BY s.upload_date DESC LIMIT ? OFFSET ?");
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
    } else {
        $stmt = $pdo->prepare("SELECT s.*, u.username as uploader_name FROM songs s 
                              JOIN users u ON s.uploader_id = u.id 
                              ORDER BY s.upload_date DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSongById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT s.*, u.username as uploader_name FROM songs s 
                          JOIN users u ON s.uploader_id = u.id 
                          WHERE s.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getFeaturedSongs($limit = 8) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT s.*, u.username as uploader_name FROM songs s 
                          JOIN users u ON s.uploader_id = u.id 
                          WHERE s.is_featured = 1 
                          ORDER BY s.likes DESC, s.downloads DESC 
                          LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function incrementDownloads($songId) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE songs SET downloads = downloads + 1 WHERE id = ?");
    return $stmt->execute([$songId]);
}

// Like functions
function likeSong($songId, $userId) {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO likes (song_id, user_id) VALUES (?, ?)");
        $result = $stmt->execute([$songId, $userId]);
        
        if ($result) {
            // Update song likes count
            $stmt = $pdo->prepare("UPDATE songs SET likes = likes + 1 WHERE id = ?");
            $stmt->execute([$songId]);
        }
        
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}

function unlikeSong($songId, $userId) {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("DELETE FROM likes WHERE song_id = ? AND user_id = ?");
        $result = $stmt->execute([$songId, $userId]);
        
        if ($result && $stmt->rowCount() > 0) {
            // Update song likes count
            $stmt = $pdo->prepare("UPDATE songs SET likes = likes - 1 WHERE id = ?");
            $stmt->execute([$songId]);
        }
        
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}

function isLikedByUser($songId, $userId) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE song_id = ? AND user_id = ?");
    $stmt->execute([$songId, $userId]);
    return $stmt->fetch() !== false;
}

// Comment functions
function addComment($songId, $userId, $comment) {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO comments (song_id, user_id, comment) VALUES (?, ?, ?)");
        return $stmt->execute([$songId, $userId, $comment]);
    } catch(PDOException $e) {
        return false;
    }
}

function getComments($songId) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT c.*, u.username FROM comments c 
                          JOIN users u ON c.user_id = u.id 
                          WHERE c.song_id = ? 
                          ORDER BY c.created_at DESC");
    $stmt->execute([$songId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Dashboard functions
function getUserStats($userId) {
    $pdo = getConnection();
    
    // Get user's uploaded songs count
    $stmt = $pdo->prepare("SELECT COUNT(*) as uploaded_songs FROM songs WHERE uploader_id = ?");
    $stmt->execute([$userId]);
    $uploadedSongs = $stmt->fetch(PDO::FETCH_ASSOC)['uploaded_songs'];
    
    // Get total downloads of user's songs
    $stmt = $pdo->prepare("SELECT SUM(downloads) as total_downloads FROM songs WHERE uploader_id = ?");
    $stmt->execute([$userId]);
    $totalDownloads = $stmt->fetch(PDO::FETCH_ASSOC)['total_downloads'] ?? 0;
    
    // Get total likes of user's songs
    $stmt = $pdo->prepare("SELECT SUM(likes) as total_likes FROM songs WHERE uploader_id = ?");
    $stmt->execute([$userId]);
    $totalLikes = $stmt->fetch(PDO::FETCH_ASSOC)['total_likes'] ?? 0;
    
    return [
        'uploaded_songs' => $uploadedSongs,
        'total_downloads' => $totalDownloads,
        'total_likes' => $totalLikes
    ];
}

function getUserSongs($userId, $limit = 10) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM songs WHERE uploader_id = ? ORDER BY upload_date DESC LIMIT ?");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Utility functions
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

function formatDuration($seconds) {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf('%d:%02d', $minutes, $seconds);
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>