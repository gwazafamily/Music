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
$stats = getUserStats($user['id']);
$userSongs = getUserSongs($user['id'], 10);

// Get recent activity (recent uploads by user)
$recentUploads = getUserSongs($user['id'], 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MusicHub</title>
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

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-900 to-purple-900 rounded-lg p-8 mb-8">
            <h2 class="text-3xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
            <p class="text-gray-300">Manage your music library and track your performance</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Songs Uploaded</p>
                        <p class="text-3xl font-bold text-blue-400"><?php echo $stats['uploaded_songs']; ?></p>
                    </div>
                    <div class="bg-blue-600 p-3 rounded-full">
                        <i class="fas fa-music text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Downloads</p>
                        <p class="text-3xl font-bold text-green-400"><?php echo $stats['total_downloads']; ?></p>
                    </div>
                    <div class="bg-green-600 p-3 rounded-full">
                        <i class="fas fa-download text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Likes</p>
                        <p class="text-3xl font-bold text-red-400"><?php echo $stats['total_likes']; ?></p>
                    </div>
                    <div class="bg-red-600 p-3 rounded-full">
                        <i class="fas fa-heart text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Uploads -->
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold">
                        <i class="fas fa-clock mr-2"></i>Recent Uploads
                    </h3>
                    <a href="upload.php" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded transition">
                        <i class="fas fa-plus mr-2"></i>Upload New
                    </a>
                </div>
                
                <?php if (empty($recentUploads)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-music text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-400 mb-4">You haven't uploaded any songs yet</p>
                        <a href="upload.php" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded transition">
                            <i class="fas fa-upload mr-2"></i>Upload Your First Song
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentUploads as $song): ?>
                            <div class="bg-gray-700 rounded-lg p-4 hover:bg-gray-600 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-lg"><?php echo htmlspecialchars($song['title']); ?></h4>
                                        <p class="text-gray-400">by <?php echo htmlspecialchars($song['artist']); ?></p>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500 mt-2">
                                            <span><i class="fas fa-download mr-1"></i><?php echo $song['downloads']; ?></span>
                                            <span><i class="fas fa-heart mr-1"></i><?php echo $song['likes']; ?></span>
                                            <span><?php echo date('M j, Y', strtotime($song['upload_date'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="play.php?id=<?php echo $song['id']; ?>" 
                                           class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm transition">
                                            <i class="fas fa-play"></i>
                                        </a>
                                        <a href="download.php?id=<?php echo $song['id']; ?>" 
                                           class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-sm transition">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- All Songs Management -->
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold">
                        <i class="fas fa-list mr-2"></i>All Your Songs
                    </h3>
                    <a href="browse.php?user=<?php echo $user['id']; ?>" class="text-blue-400 hover:text-blue-300">
                        View All <i class="fas fa-external-link-alt ml-1"></i>
                    </a>
                </div>
                
                <?php if (empty($userSongs)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-music text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-400">No songs uploaded yet</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($userSongs as $song): ?>
                            <div class="bg-gray-700 rounded-lg p-3 hover:bg-gray-600 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium truncate"><?php echo htmlspecialchars($song['title']); ?></h4>
                                        <p class="text-sm text-gray-400 truncate"><?php echo htmlspecialchars($song['artist']); ?></p>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                                        <span><i class="fas fa-download mr-1"></i><?php echo $song['downloads']; ?></span>
                                        <span><i class="fas fa-heart mr-1"></i><?php echo $song['likes']; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-gray-800 rounded-lg p-6 mt-8">
            <h3 class="text-2xl font-bold mb-6">
                <i class="fas fa-bolt mr-2"></i>Quick Actions
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="upload.php" class="bg-blue-600 hover:bg-blue-700 p-4 rounded-lg text-center transition">
                    <i class="fas fa-upload text-2xl mb-2"></i>
                    <p class="font-semibold">Upload Song</p>
                    <p class="text-sm text-gray-300">Share your music</p>
                </a>
                
                <a href="browse.php" class="bg-green-600 hover:bg-green-700 p-4 rounded-lg text-center transition">
                    <i class="fas fa-search text-2xl mb-2"></i>
                    <p class="font-semibold">Browse Music</p>
                    <p class="text-sm text-gray-300">Discover new songs</p>
                </a>
                
                <a href="index.php" class="bg-purple-600 hover:bg-purple-700 p-4 rounded-lg text-center transition">
                    <i class="fas fa-home text-2xl mb-2"></i>
                    <p class="font-semibold">Go Home</p>
                    <p class="text-sm text-gray-300">Return to homepage</p>
                </a>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>