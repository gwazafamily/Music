<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$isLoggedIn = isset($_SESSION['user_id']);
$user = $isLoggedIn ? getUserById($_SESSION['user_id']) : null;

$songId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$song = getSongById($songId);

if (!$song) {
    header('Location: index.php');
    exit();
}

// Get comments for this song
$comments = getComments($songId);

// Check if user has liked this song
$isLiked = $isLoggedIn ? isLikedByUser($songId, $user['id']) : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($song['title']); ?> - MusicHub</title>
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

    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Song Info and Player -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-lg p-6 mb-6">
                    <div class="text-center mb-6">
                        <h2 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($song['title']); ?></h2>
                        <p class="text-xl text-gray-400 mb-4">by <?php echo htmlspecialchars($song['artist']); ?></p>
                        <?php if ($song['album']): ?>
                            <p class="text-gray-500 mb-4">Album: <?php echo htmlspecialchars($song['album']); ?></p>
                        <?php endif; ?>
                        
                        <div class="flex justify-center space-x-6 text-sm text-gray-400">
                            <span><i class="fas fa-download mr-1"></i><?php echo $song['downloads']; ?> downloads</span>
                            <span><i class="fas fa-heart mr-1"></i><?php echo $song['likes']; ?> likes</span>
                            <span><i class="fas fa-file mr-1"></i><?php echo formatFileSize($song['file_size']); ?></span>
                        </div>
                    </div>

                    <!-- Audio Player -->
                    <div id="audioPlayer" class="bg-gray-700 rounded-lg p-6">
                        <audio id="audioElement" preload="metadata">
                            <source src="<?php echo htmlspecialchars($song['file_path']); ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                        
                        <div class="flex items-center space-x-4 mb-4">
                            <button id="playBtn" class="bg-blue-600 hover:bg-blue-700 w-12 h-12 rounded-full flex items-center justify-center transition">
                                <i class="fas fa-play"></i>
                            </button>
                            
                            <div class="flex-1">
                                <div id="progressContainer" class="bg-gray-600 h-2 rounded cursor-pointer">
                                    <div id="progressBar" class="bg-blue-600 h-2 rounded" style="width: 0%"></div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <span id="currentTime">0:00</span>
                                <span>/</span>
                                <span id="duration">0:00</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <button onclick="toggleLike(<?php echo $song['id']; ?>, this)" 
                                        class="flex items-center space-x-2 px-4 py-2 rounded transition <?php echo $isLiked ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-600 hover:bg-gray-500'; ?>">
                                    <i class="<?php echo $isLiked ? 'fas' : 'far'; ?> fa-heart"></i>
                                    <span class="like-count"><?php echo $song['likes']; ?></span>
                                </button>
                                
                                <a href="download.php?id=<?php echo $song['id']; ?>" 
                                   class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded transition">
                                    <i class="fas fa-download mr-2"></i>Download
                                </a>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-volume-up"></i>
                                <input type="range" id="volumeSlider" min="0" max="100" value="100" 
                                       class="w-20">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lyrics -->
                <?php if ($song['lyrics']): ?>
                    <div class="bg-gray-800 rounded-lg p-6">
                        <h3 class="text-2xl font-bold mb-4">
                            <i class="fas fa-music mr-2"></i>Lyrics
                        </h3>
                        <div class="whitespace-pre-wrap text-gray-300 leading-relaxed">
                            <?php echo htmlspecialchars($song['lyrics']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Comments Section -->
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-lg p-6">
                    <h3 class="text-2xl font-bold mb-4">
                        <i class="fas fa-comments mr-2"></i>Comments
                    </h3>
                    
                    <?php if ($isLoggedIn): ?>
                        <form id="commentForm" class="mb-6">
                            <textarea id="commentText" placeholder="Write a comment..." 
                                      class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:border-blue-500 mb-3"
                                      rows="3"></textarea>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded transition">
                                <i class="fas fa-paper-plane mr-2"></i>Post Comment
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-gray-400 mb-4">Please <a href="login.php" class="text-blue-400 hover:text-blue-300">login</a> to comment.</p>
                    <?php endif; ?>
                    
                    <div id="commentsList">
                        <?php foreach ($comments as $comment): ?>
                            <div class="border-b border-gray-700 pb-4 mb-4 last:border-b-0">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold"><?php echo htmlspecialchars($comment['username']); ?></span>
                                    <span class="text-sm text-gray-400"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <p class="text-gray-300"><?php echo htmlspecialchars($comment['comment']); ?></p>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($comments)): ?>
                            <p class="text-gray-400 text-center">No comments yet. Be the first to comment!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize audio player
        const audio = document.getElementById('audioElement');
        const playBtn = document.getElementById('playBtn');
        const progressBar = document.getElementById('progressBar');
        const progressContainer = document.getElementById('progressContainer');
        const currentTimeEl = document.getElementById('currentTime');
        const durationEl = document.getElementById('duration');
        const volumeSlider = document.getElementById('volumeSlider');

        // Load song into global player
        audioPlayer.loadSong('<?php echo htmlspecialchars($song['file_path']); ?>', 
                           '<?php echo htmlspecialchars($song['title']); ?>', 
                           '<?php echo htmlspecialchars($song['artist']); ?>');

        // Audio event listeners
        audio.addEventListener('loadedmetadata', () => {
            durationEl.textContent = audioPlayer.formatTime(audio.duration);
        });

        audio.addEventListener('timeupdate', () => {
            const progress = (audio.currentTime / audio.duration) * 100;
            progressBar.style.width = progress + '%';
            currentTimeEl.textContent = audioPlayer.formatTime(audio.currentTime);
        });

        audio.addEventListener('ended', () => {
            playBtn.innerHTML = '<i class="fas fa-play"></i>';
        });

        // Control event listeners
        playBtn.addEventListener('click', () => {
            if (audio.paused) {
                audio.play();
                playBtn.innerHTML = '<i class="fas fa-pause"></i>';
            } else {
                audio.pause();
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
            }
        });

        progressContainer.addEventListener('click', (e) => {
            const rect = progressContainer.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            audio.currentTime = percent * audio.duration;
        });

        volumeSlider.addEventListener('input', (e) => {
            audio.volume = e.target.value / 100;
        });

        // Comment form submission
        document.getElementById('commentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const commentText = document.getElementById('commentText').value.trim();
            if (!commentText) return;
            
            fetch('api/add_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    song_id: <?php echo $song['id']; ?>,
                    comment: commentText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add comment to the list
                    const commentsList = document.getElementById('commentsList');
                    const newComment = document.createElement('div');
                    newComment.className = 'border-b border-gray-700 pb-4 mb-4';
                    newComment.innerHTML = `
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold"><?php echo htmlspecialchars($user['username']); ?></span>
                            <span class="text-sm text-gray-400">Just now</span>
                        </div>
                        <p class="text-gray-300">${commentText}</p>
                    `;
                    commentsList.insertBefore(newComment, commentsList.firstChild);
                    
                    // Clear form
                    document.getElementById('commentText').value = '';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while posting comment');
            });
        });
    </script>
    <script src="js/main.js"></script>
</body>
</html>