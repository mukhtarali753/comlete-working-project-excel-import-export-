# Laravel Excel Application

## Performance Optimization for Large Files

### Timeout Issues
If you experience "Maximum execution time exceeded" errors when saving large spreadsheets, the following optimizations have been implemented:

#### Client-Side Optimizations
- **Chunked Saving**: Large files are automatically split into smaller chunks to prevent timeouts
- **Progress Tracking**: Visual progress bar shows save operation status
- **Smart Data Processing**: Only modified data is sent to the server
- **Request Timeout**: 30-second timeout with automatic retry logic

#### Server-Side Configuration
The following files have been configured to handle large file operations:

1. **`.htaccess`** - Apache server configuration
   - Increased PHP execution time to 300 seconds
   - Increased memory limit to 512MB
   - Increased POST size limit to 100MB

2. **`public/php-config.php`** - PHP runtime configuration
   - Sets execution time limits
   - Configures memory limits
   - Enables output buffering

3. **`public/index.php`** - Includes PHP configuration

#### Manual Server Configuration
If you're still experiencing timeout issues, you may need to modify your server's PHP configuration:

```ini
; php.ini settings
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
post_max_size = 100M
upload_max_filesize = 100M
```

#### Alternative Solutions
- **Reduce spreadsheet size**: Limit the number of rows/columns
- **Use incremental saves**: Save smaller portions more frequently
- **Server upgrade**: Consider increasing server resources for very large files

### Usage
1. Open the Excel preview page
2. Make your changes
3. Click "Save Data" - the system will automatically handle large files
4. Monitor progress with the progress bar
5. Large files will be saved in chunks automatically

## Installation and Setup

[Rest of your existing README content...]
