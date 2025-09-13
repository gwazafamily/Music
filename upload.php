<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = getUserById($_SESSION['user_id']);
$error = '';
$success = '';

if ($_POST) {
    $title = sanitizeInput($_POST['title']);
    $artist = sanitizeInput($_POST['artist']);
    $album = sanitizeInput($_POST['album']);
    $genre = sanitizeInput($_POST['genre']);
    $lyrics = sanitizeInput($_POST['lyrics']);
    
    if (empty($title) || empty($artist)) {
        $error = 'Title and artist are required';
    } elseif (!isset($_FILES['audio_file']) || $_FILES['audio_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a valid audio file';
    } else {
        $file = $_FILES['audio_file'];
        $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            $error = 'Only MP3, WAV, OGG, and M4A files are allowed';
        } elseif ($file['size'] > 50 * 1024 * 1024) { // 50MB limit
            $error = 'File size must be less than 50MB';
        } else {
            // Create uploads directory if it doesn't exist
            $uploadDir = 'uploads/songs/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Get audio duration (simplified - you might want to use a library like getid3)
                $duration = 0; // Placeholder - implement audio duration detection
                
                if (uploadSong($title, $artist, $album, $genre, $filePath, $file['size'], $duration, $lyrics, $user['id'])) {
                    $success = 'Song uploaded successfully!';
                    // Clear form
                    $title = $artist = $album = $genre = $lyrics = '';
                } else {
                    $error = 'Failed to save song information to database';
                    unlink($filePath); // Remove uploaded file
                }
            } else {
                $error = 'Failed to upload file';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Song - MusicHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-blue-400">
                        <i class="fas fa-music mr-2"></i>MusicHub
                    </h1>
                    <div class="hidden md:flex space-x-6">
                        <a href="index.php" class="hover:text-blue-400 transition">Home</a>
                        <a href="browse.php" class="hover:text-blue-400 transition">Browse</a>
                        <a href="upload.php" class="hover:text-blue-400 transition">Upload</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="hover:text-blue-400 transition">
                        <i class="fas fa-user-circle mr-1"></i><?php echo htmlspecialchars($user['username']); ?>
                    </a>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-gray-800 rounded-lg shadow-lg p-8">
            <h2 class="text-3xl font-bold mb-8 text-center">
                <i class="fas fa-upload mr-2"></i>Upload New Song
            </h2>
            
            <?php if ($error): ?>
                <div class="bg-red-600 text-white p-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-600 text-white p-3 rounded mb-4">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium mb-2">Song Title *</label>
                        <input type="text" id="title" name="title" required
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:border-blue-500"
                               value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                    </div>
                    
                    <div>
                        <label for="artist" class="block text-sm font-medium mb-2">Artist *</label>
                        <input type="text" id="artist" name="artist" required
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:border-blue-500"
                               value="<?php echo isset($artist) ? htmlspecialchars($artist) : ''; ?>">
                    </div>
                    
                    <div>
                        <label for="album" class="block text-sm font-medium mb-2">Album</label>
                        <input type="text" id="album" name="album"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:border-blue-500"
                               value="<?php echo isset($album) ? htmlspecialchars($album) : ''; ?>">
                    </div>
                    
                    <div>
                        <label for="genre" class="block text-sm font-medium mb-2">Genre</label>
                        <select id="genre" name="genre" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:border-blue-500">
                            <option value="">Select Genre</option>
                            <option value="Pop" <?php echo (isset($genre) && $genre === 'Pop') ? 'selected' : ''; ?>>Pop</option>
                            <option value="Rock" <?php echo (isset($genre) && $genre === 'Rock') ? 'selected' : ''; ?>>Rock</option>
                            <option value="Hip Hop" <?php echo (isset($genre) && $genre === 'Hip Hop') ? 'selected' : ''; ?>>Hip Hop</option>
                            <option value="Electronic" <?php echo (isset($genre) && $genre === 'Electronic') ? 'selected' : ''; ?>>Electronic</option>
                            <option value="Jazz" <?php echo (isset($genre) && $genre === 'Jazz') ? 'selected' : ''; ?>>Jazz</option>
                            <option value="Classical" <?php echo (isset($genre) && $genre === 'Classical') ? 'selected' : ''; ?>>Classical</option>
                            <option value="Country" <?php echo (isset($genre) && $genre === 'Country') ? 'selected' : ''; ?>>Country</option>
                            <option value="R&B" <?php echo (isset($genre) && $genre === 'R&B') ? 'selected' : ''; ?>>R&B</option>
                            <option value="Other" <?php echo (isset($genre) && $genre === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label for="audio_file" class="block text-sm font-medium mb-2">Audio File *</label>
                    <div class="border-2 border-dashed border-gray-600 rounded-lg p-6 text-center hover:border-blue-500 transition">
                        <input type="file" id="audio_file" name="audio_file" accept="audio/*" required
                               class="hidden" onchange="updateFileName(this)">
                        <label for="audio_file" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <p class="text-lg mb-2">Click to select audio file</p>
                            <p class="text-sm text-gray-400">MP3, WAV, OGG, M4A (Max 50MB)</p>
                        </label>
                        <div id="fileName" class="mt-2 text-blue-400"></div>
                    </div>
                </div>
                
                <div>
                    <label for="lyrics" class="block text-sm font-medium mb-2">Lyrics</label>
                    <textarea id="lyrics" name="lyrics" rows="8"
                              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:border-blue-500"
                              placeholder="Enter song lyrics (optional)"><?php echo isset($lyrics) ? htmlspecialchars($lyrics) : ''; ?></textarea>
                </div>
                
                <div class="flex justify-center">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-8 py-3 rounded-lg text-lg transition">
                        <i class="fas fa-upload mr-2"></i>Upload Song
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateFileName(input) {
            const fileName = document.getElementById('fileName');
            if (input.files && input.files[0]) {
                fileName.textContent = 'Selected: ' + input.files[0].name;
            }
        }
    </script>
</body>
</html>