// Main JavaScript file for MusicHub

// Audio player functionality
class AudioPlayer {
    constructor() {
        this.audio = null;
        this.currentSong = null;
        this.isPlaying = false;
        this.currentTime = 0;
        this.duration = 0;
        this.volume = 1;
        this.init();
    }

    init() {
        this.audio = new Audio();
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.audio.addEventListener('loadedmetadata', () => {
            this.duration = this.audio.duration;
            this.updateDurationDisplay();
        });

        this.audio.addEventListener('timeupdate', () => {
            this.currentTime = this.audio.currentTime;
            this.updateProgressBar();
        });

        this.audio.addEventListener('ended', () => {
            this.isPlaying = false;
            this.updatePlayButton();
        });

        this.audio.addEventListener('error', (e) => {
            console.error('Audio error:', e);
            this.showError('Error loading audio file');
        });
    }

    loadSong(songUrl, songTitle, songArtist) {
        this.currentSong = { url: songUrl, title: songTitle, artist: songArtist };
        this.audio.src = songUrl;
        this.audio.load();
    }

    play() {
        if (this.audio.src) {
            this.audio.play().then(() => {
                this.isPlaying = true;
                this.updatePlayButton();
            }).catch(e => {
                console.error('Playback failed:', e);
                this.showError('Playback failed');
            });
        }
    }

    pause() {
        this.audio.pause();
        this.isPlaying = false;
        this.updatePlayButton();
    }

    togglePlay() {
        if (this.isPlaying) {
            this.pause();
        } else {
            this.play();
        }
    }

    setVolume(volume) {
        this.volume = Math.max(0, Math.min(1, volume));
        this.audio.volume = this.volume;
    }

    seek(time) {
        this.audio.currentTime = time;
    }

    updatePlayButton() {
        const playBtn = document.getElementById('playBtn');
        if (playBtn) {
            playBtn.innerHTML = this.isPlaying ? 
                '<i class="fas fa-pause"></i>' : 
                '<i class="fas fa-play"></i>';
        }
    }

    updateProgressBar() {
        const progressBar = document.getElementById('progressBar');
        const currentTimeEl = document.getElementById('currentTime');
        
        if (progressBar && this.duration > 0) {
            const progress = (this.currentTime / this.duration) * 100;
            progressBar.style.width = progress + '%';
        }
        
        if (currentTimeEl) {
            currentTimeEl.textContent = this.formatTime(this.currentTime);
        }
    }

    updateDurationDisplay() {
        const durationEl = document.getElementById('duration');
        if (durationEl) {
            durationEl.textContent = this.formatTime(this.duration);
        }
    }

    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${minutes}:${secs.toString().padStart(2, '0')}`;
    }

    showError(message) {
        // Create or update error message
        let errorEl = document.getElementById('audioError');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.id = 'audioError';
            errorEl.className = 'bg-red-600 text-white p-3 rounded mb-4';
            const playerEl = document.getElementById('audioPlayer');
            if (playerEl) {
                playerEl.insertBefore(errorEl, playerEl.firstChild);
            }
        }
        errorEl.textContent = message;
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (errorEl) {
                errorEl.remove();
            }
        }, 5000);
    }
}

// Initialize global audio player
const audioPlayer = new AudioPlayer();

// Like functionality
function toggleLike(songId, button) {
    fetch('api/toggle_like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ song_id: songId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = button.querySelector('i');
            const countEl = button.querySelector('.like-count');
            
            if (data.liked) {
                icon.className = 'fas fa-heart text-red-500';
                countEl.textContent = parseInt(countEl.textContent) + 1;
            } else {
                icon.className = 'far fa-heart';
                countEl.textContent = parseInt(countEl.textContent) - 1;
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating like status');
    });
}

// Bulk download functionality
function addToBulkDownload(songId, button) {
    let bulkDownloads = JSON.parse(localStorage.getItem('bulkDownloads') || '[]');
    
    if (bulkDownloads.includes(songId)) {
        // Remove from bulk downloads
        bulkDownloads = bulkDownloads.filter(id => id !== songId);
        button.innerHTML = '<i class="fas fa-plus"></i> Add to Bulk';
        button.className = button.className.replace('bg-red-600', 'bg-blue-600');
    } else {
        // Add to bulk downloads
        bulkDownloads.push(songId);
        button.innerHTML = '<i class="fas fa-minus"></i> Remove';
        button.className = button.className.replace('bg-blue-600', 'bg-red-600');
    }
    
    localStorage.setItem('bulkDownloads', JSON.stringify(bulkDownloads));
    updateBulkDownloadCounter();
}

function updateBulkDownloadCounter() {
    const bulkDownloads = JSON.parse(localStorage.getItem('bulkDownloads') || '[]');
    const counter = document.getElementById('bulkDownloadCounter');
    if (counter) {
        counter.textContent = bulkDownloads.length;
        counter.style.display = bulkDownloads.length > 0 ? 'inline' : 'none';
    }
}

function downloadBulk() {
    const bulkDownloads = JSON.parse(localStorage.getItem('bulkDownloads') || '[]');
    
    if (bulkDownloads.length === 0) {
        alert('No songs selected for bulk download');
        return;
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'download_bulk.php';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'song_ids';
    input.value = JSON.stringify(bulkDownloads);
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Search functionality
function performSearch(query) {
    if (query.length < 2) return;
    
    fetch(`api/search.php?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
        displaySearchResults(data.results);
    })
    .catch(error => {
        console.error('Search error:', error);
    });
}

function displaySearchResults(results) {
    const resultsContainer = document.getElementById('searchResults');
    if (!resultsContainer) return;
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<p class="text-gray-400">No results found</p>';
        return;
    }
    
    resultsContainer.innerHTML = results.map(song => `
        <div class="bg-gray-800 rounded-lg p-4 hover:bg-gray-700 transition">
            <h4 class="font-semibold text-lg mb-2">${song.title}</h4>
            <p class="text-gray-400 mb-3">by ${song.artist}</p>
            <div class="flex space-x-2">
                <a href="play.php?id=${song.id}" class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm transition">
                    <i class="fas fa-play mr-1"></i>Play
                </a>
                <a href="download.php?id=${song.id}" class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-sm transition">
                    <i class="fas fa-download mr-1"></i>Download
                </a>
            </div>
        </div>
    `).join('');
}

// File upload progress
function uploadProgress(fileInput, progressBar) {
    const file = fileInput.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('audio_file', file);
    formData.append('title', document.getElementById('title').value);
    formData.append('artist', document.getElementById('artist').value);
    formData.append('album', document.getElementById('album').value);
    formData.append('genre', document.getElementById('genre').value);
    formData.append('lyrics', document.getElementById('lyrics').value);
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progressBar.style.width = percentComplete + '%';
            progressBar.textContent = Math.round(percentComplete) + '%';
        }
    });
    
    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert('Song uploaded successfully!');
                window.location.href = 'dashboard.php';
            } else {
                alert('Upload failed: ' + response.message);
            }
        } else {
            alert('Upload failed');
        }
    });
    
    xhr.open('POST', 'api/upload.php');
    xhr.send(formData);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateBulkDownloadCounter();
    
    // Setup search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(e.target.value);
            }, 300);
        });
    }
    
    // Setup audio player controls
    const playBtn = document.getElementById('playBtn');
    if (playBtn) {
        playBtn.addEventListener('click', () => audioPlayer.togglePlay());
    }
    
    const volumeSlider = document.getElementById('volumeSlider');
    if (volumeSlider) {
        volumeSlider.addEventListener('input', (e) => {
            audioPlayer.setVolume(e.target.value / 100);
        });
    }
    
    const progressContainer = document.getElementById('progressContainer');
    if (progressContainer) {
        progressContainer.addEventListener('click', (e) => {
            const rect = progressContainer.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            audioPlayer.seek(audioPlayer.duration * percent);
        });
    }
});