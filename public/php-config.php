<?php
// PHP Configuration for Large File Operations
// This file should be included at the beginning of your application

// Increase execution time limits
ini_set('max_execution_time', 300); // 5 minutes
ini_set('max_input_time', 300);
ini_set('memory_limit', '512M');

// Increase POST and upload limits
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '100M');

// Enable output buffering for large responses
if (!ob_get_level()) {
    ob_start();
}

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Enable session garbage collection
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
?>
