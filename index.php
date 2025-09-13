<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = $isLoggedIn ? getUserById($_SESSION['user_id']) : null;

// Get featured songs
$featuredSongs = getFeaturedSongs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MusicHub - Your Music Destination</title>
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
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="hover:text-blue-400 transition">
                            <i class="fas fa-user-circle mr-1"></i><?php echo htmlspecialchars($user['username']); ?>
                        </a>
                        <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-blue-400 transition">Login</a>
                        <a href="register.php" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded transition">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-900 to-purple-900 py-20">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h2 class="text-5xl font-bold mb-6">Discover Amazing Music</h2>
            <p class="text-xl mb-8 text-gray-300">Upload, share, and enjoy music from artists around the world</p>
            <div class="flex justify-center space-x-4">
                <a href="browse.php" class="bg-blue-600 hover:bg-blue-700 px-8 py-3 rounded-lg text-lg transition">
                    <i class="fas fa-search mr-2"></i>Browse Music
                </a>
                <?php if ($isLoggedIn): ?>
                    <a href="upload.php" class="bg-green-600 hover:bg-green-700 px-8 py-3 rounded-lg text-lg transition">
                        <i class="fas fa-upload mr-2"></i>Upload Song
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Songs -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4">
            <h3 class="text-3xl font-bold mb-8 text-center">Featured Songs</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($featuredSongs as $song): ?>
                    <div class="bg-gray-800 rounded-lg overflow-hidden hover:bg-gray-700 transition">
                        <div class="p-4">
                            <h4 class="font-semibold text-lg mb-2"><?php echo htmlspecialchars($song['title']); ?></h4>
                            <p class="text-gray-400 mb-3">by <?php echo htmlspecialchars($song['artist']); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-download mr-1"></i><?php echo $song['downloads']; ?>
                                </span>
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-heart mr-1"></i><?php echo $song['likes']; ?>
                                </span>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <a href="play.php?id=<?php echo $song['id']; ?>" 
                                   class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm transition">
                                    <i class="fas fa-play mr-1"></i>Play
                                </a>
                                <a href="download.php?id=<?php echo $song['id']; ?>" 
                                   class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-sm transition">
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-gray-400">&copy; 2024 MusicHub. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>