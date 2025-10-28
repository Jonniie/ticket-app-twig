<?php
// Development server router script
// This handles all requests and routes them through index.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

// If it's a real file or directory, serve it
if ($uri !== '/' && (is_file($file) || is_dir($file))) {
    return false;
}

// Otherwise, route everything through index.php
require_once __DIR__ . '/index.php';
