<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/core/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/core/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__.'/core/bootstrap/app.php';

// For API routes and special Laravel routes, handle normally
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$parsedUri = parse_url($requestUri, PHP_URL_PATH);

// Remove query string for route matching
$parsedUri = strtok($parsedUri, '?');

if (str_starts_with($parsedUri, '/api') || 
    str_starts_with($parsedUri, '/ipn') ||
    str_starts_with($parsedUri, '/track') ||
    str_starts_with($parsedUri, '/cron') ||
    str_starts_with($parsedUri, '/clear') ||
    str_starts_with($parsedUri, '/user/social-login') ||
    str_starts_with($parsedUri, '/advertiser/social-login') ||
    str_starts_with($parsedUri, '/admin')) {
    // These routes go to Laravel
    $app->handleRequest(Request::capture());
} else {
    // For all other routes, serve the Vue.js app
    $publicPath = __DIR__ . '/public/index.html';
    if (file_exists($publicPath)) {
        // Set proper content type
        header('Content-Type: text/html; charset=utf-8');
        readfile($publicPath);
        exit;
    } else {
        // If Vue.js app not built, show helpful message
        http_response_code(503);
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Application Not Built</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .container { max-width: 600px; margin: 0 auto; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Vue.js Application Not Built</h1>
        <p>The Vue.js frontend needs to be built before it can be served.</p>
        <p>Please run the following command:</p>
        <p><code>cd frontend && npm install && npm run build</code></p>
        <p>This will create the necessary files in the <code>public/</code> directory.</p>
    </div>
</body>
</html>';
        exit;
    }
}
