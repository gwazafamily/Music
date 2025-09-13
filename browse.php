<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$isLoggedIn = isset($_SESSION['user_id']);
$user = $isLoggedIn ? getUserById($_SESSION['user_id']) : null;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$songs = getSongs($limit, $offset, $search);
$totalSongs = count(getSongs(1000, 0, $search)); // Get total count
$totalPages = ceil($totalSongs / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Music - MusicHub</title>
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

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Search and Filters -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <form method="GET" class="flex">
                        <input type="text" name="search" placeholder="Search songs, artists, albums..."
                               class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-l-md focus:outline-none focus:border-blue-500"
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-r-md transition">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <?php if ($isLoggedIn): ?>
                    <div class="flex items-center space-x-4">
                        <button onclick="downloadBulk()" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded transition">
                            <i class="fas fa-download mr-2"></i>Bulk Download
                            <span id="bulkDownloadCounter" class="ml-2 bg-red-600 px-2 py-1 rounded text-sm" style="display: none;">0</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Songs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            <?php foreach ($songs as $song): ?>
                <div class="bg-gray-800 rounded-lg overflow-hidden hover:bg-gray-700 transition">
                    <div class="p-4">
                        <h4 class="font-semibold text-lg mb-2 truncate"><?php echo htmlspecialchars($song['title']); ?></h4>
                        <p class="text-gray-400 mb-1">by <?php echo htmlspecialchars($song['artist']); ?></p>
                        <?php if ($song['album']): ?>
                            <p class="text-gray-500 text-sm mb-2">Album: <?php echo htmlspecialchars($song['album']); ?></p>
                        <?php endif; ?>
                        <?php if ($song['genre']): ?>
                            <p class="text-gray-500 text-sm mb-3">Genre: <?php echo htmlspecialchars($song['genre']); ?></p>
                        <?php endif; ?>
                        
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-download mr-1"></i><?php echo $song['downloads']; ?>
                            </span>
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-heart mr-1"></i><?php echo $song['likes']; ?>
                            </span>
                            <span class="text-sm text-gray-500">
                                <?php echo formatFileSize($song['file_size']); ?>
                            </span>
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            <a href="play.php?id=<?php echo $song['id']; ?>" 
                               class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm transition">
                                <i class="fas fa-play mr-1"></i>Play
                            </a>
                            <a href="download.php?id=<?php echo $song['id']; ?>" 
                               class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-sm transition">
                                <i class="fas fa-download mr-1"></i>Download
                            </a>
                            <?php if ($isLoggedIn): ?>
                                <button onclick="toggleLike(<?php echo $song['id']; ?>, this)" 
                                        class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm transition">
                                    <i class="far fa-heart"></i>
                                    <span class="like-count"><?php echo $song['likes']; ?></span>
                                </button>
                                <button onclick="addToBulkDownload(<?php echo $song['id']; ?>, this)" 
                                        class="bg-purple-600 hover:bg-purple-700 px-3 py-1 rounded text-sm transition">
                                    <i class="fas fa-plus"></i> Add to Bulk
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center">
                <nav class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" 
                           class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded transition">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-3 py-2 rounded transition <?php echo $i === $page ? 'bg-blue-600' : 'bg-gray-800 hover:bg-gray-700'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" 
                           class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded transition">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/main.js"></script>
</body>
</html>