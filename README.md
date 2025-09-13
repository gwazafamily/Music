# MusicHub - Music Sharing Platform

A comprehensive music website built with PHP, JavaScript, HTML, CSS, Tailwind CSS, and MySQL that allows users to upload, play, download, and share music.

## Features

### Core Functionality
- **User Authentication**: Register, login, and user management
- **Music Upload**: Upload songs with metadata (title, artist, album, genre, lyrics)
- **Music Player**: HTML5 audio player with play/pause, progress bar, and volume control
- **Download System**: Individual song downloads and bulk download (ZIP)
- **Search & Browse**: Search songs by title, artist, or album with pagination
- **Lyrics Display**: View song lyrics on the player page
- **Like System**: Like/unlike songs with real-time updates
- **Comments**: Add and view comments on songs
- **Dashboard**: User dashboard with statistics and song management

### Technical Features
- **Responsive Design**: Mobile-friendly interface using Tailwind CSS
- **File Validation**: Audio file type and size validation
- **Security**: Input sanitization, SQL injection prevention, file upload security
- **Database Schema**: Comprehensive MySQL database with proper relationships
- **API Endpoints**: RESTful API for likes, comments, and search
- **Bulk Operations**: Select multiple songs for bulk download

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- PHP extensions: PDO, PDO_MySQL, ZipArchive

### Setup Instructions

1. **Clone or download the project files**
   ```bash
   # Place all files in your web server directory
   ```

2. **Create MySQL Database**
   ```sql
   CREATE DATABASE musichub;
   ```

3. **Configure Database Connection**
   Edit `config/database.php` and update the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'musichub');
   ```

4. **Set Directory Permissions**
   ```bash
   chmod 755 uploads/songs/
   chmod 644 config/database.php
   ```

5. **Access the Website**
   - Open your web browser
   - Navigate to your domain/server
   - The database tables will be created automatically on first access

## File Structure

```
musichub/
├── api/                    # API endpoints
│   ├── add_comment.php
│   ├── search.php
│   └── toggle_like.php
├── config/                 # Configuration files
│   └── database.php
├── includes/               # Shared functions
│   └── functions.php
├── uploads/               # File uploads directory
│   └── songs/
├── js/                    # JavaScript files
│   └── main.js
├── index.php              # Homepage
├── login.php              # Login page
├── register.php           # Registration page
├── upload.php             # Song upload page
├── browse.php             # Browse/search songs
├── play.php               # Music player page
├── download.php           # Individual download
├── download_bulk.php      # Bulk download
├── dashboard.php          # User dashboard
├── logout.php             # Logout handler
├── .htaccess              # Apache configuration
└── README.md              # This file
```

## Database Schema

The application automatically creates the following tables:

- **users**: User accounts and authentication
- **songs**: Music files and metadata
- **comments**: Song comments
- **likes**: Song likes
- **playlists**: User playlists (future feature)
- **playlist_songs**: Playlist-song relationships (future feature)

## Usage

### For Users
1. **Register**: Create an account to upload and interact with music
2. **Upload**: Upload your music files with metadata
3. **Browse**: Search and discover music from other users
4. **Play**: Use the built-in audio player to listen to songs
5. **Download**: Download individual songs or create bulk downloads
6. **Interact**: Like songs and leave comments
7. **Dashboard**: View your upload statistics and manage your songs

### For Administrators
- Access the database directly to manage users and content
- Modify `config/database.php` to add admin users
- Use the database to feature songs (set `is_featured = 1`)

## Security Features

- **Input Sanitization**: All user inputs are sanitized
- **SQL Injection Prevention**: Using prepared statements
- **File Upload Security**: File type and size validation
- **Session Management**: Secure user sessions
- **Access Control**: Protected routes and API endpoints

## Customization

### Styling
- Modify Tailwind CSS classes in HTML files
- Update color schemes in the CSS classes
- Customize the navigation and layout

### Features
- Add new genres in the upload form
- Implement playlist functionality
- Add user profiles and social features
- Integrate with external music APIs

### Database
- Add new fields to existing tables
- Create additional tables for new features
- Modify the database schema in `config/database.php`

## Troubleshooting

### Common Issues

1. **File Upload Errors**
   - Check PHP upload limits in `.htaccess`
   - Verify directory permissions on `uploads/songs/`
   - Ensure `ZipArchive` extension is installed

2. **Database Connection Issues**
   - Verify database credentials in `config/database.php`
   - Ensure MySQL server is running
   - Check database exists and user has proper permissions

3. **Audio Player Not Working**
   - Verify audio file paths are correct
   - Check browser console for JavaScript errors
   - Ensure audio files are accessible via web server

### Performance Optimization

1. **File Storage**
   - Consider using cloud storage (AWS S3, etc.)
   - Implement CDN for audio files
   - Optimize audio file formats

2. **Database**
   - Add indexes for frequently queried fields
   - Implement database connection pooling
   - Use database caching

3. **Frontend**
   - Minify CSS and JavaScript
   - Implement lazy loading for images
   - Use browser caching effectively

## License

This project is open source and available under the MIT License.

## Support

For support and questions:
- Check the troubleshooting section
- Review the code comments
- Create an issue in the project repository

## Future Enhancements

- User profiles and social features
- Advanced playlist management
- Music recommendation system
- Mobile app integration
- Advanced analytics and reporting
- Multi-language support
- Advanced search filters
- Music streaming optimization