<?php
/**
 * SimpleProxy - Static Caching Proxy with Domain Rewriting
 * 
 * @author Andreas Kasper <andreas.kasper@goo1.de>
 * @license MIT
 */

// Load configuration from environment
$targetHost = getenv('TARGET_HOST') ?: 'example.com';
$publicHost = getenv('PUBLIC_HOST') ?: $_SERVER['HTTP_HOST'];
$proxyScheme = getenv('PROXY_SCHEME') ?: 'https';
$cacheMaxAge = (int)(getenv('CACHE_MAX_AGE') ?: 3600);
$cacheStaleRevalidate = (int)(getenv('CACHE_STALE_REVALIDATE') ?: 86400);
$cacheStaleError = (int)(getenv('CACHE_STALE_ERROR') ?: 86400);
$enableEtag = filter_var(getenv('ENABLE_ETAG') ?: 'true', FILTER_VALIDATE_BOOLEAN);
$excludedPaths = getenv('EXCLUDED_PATHS') ?: '/wp-admin,/wp-login.php';
$includedPaths = getenv('INCLUDED_PATHS') ?: '.*';
$proxyTimeout = (int)(getenv('PROXY_TIMEOUT') ?: 30);
$enableGzip = filter_var(getenv('ENABLE_GZIP') ?: 'true', FILTER_VALIDATE_BOOLEAN);
$enableSslVerify = filter_var(getenv('ENABLE_SSL_VERIFY') ?: 'true', FILTER_VALIDATE_BOOLEAN);
$allowedMethods = explode(',', getenv('ALLOWED_METHODS') ?: 'GET,HEAD');
$debugMode = filter_var(getenv('DEBUG_MODE') ?: 'false', FILTER_VALIDATE_BOOLEAN);

// Debug logger
function debug_log($message) {
    global $debugMode;
    if ($debugMode) {
        error_log("[SimpleProxy] " . $message, 3, "/var/log/apache2/proxy-debug.log");
    }
}

// Get request details
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

debug_log("Request: {$requestMethod} {$requestUri}");

// Check if method is allowed
if (!in_array($requestMethod, $allowedMethods)) {
    http_response_code(405);
    header('Allow: ' . implode(', ', $allowedMethods));
    debug_log("Method not allowed: {$requestMethod}");
    die("Method Not Allowed");
}

// Check if path is excluded
$excluded = array_filter(explode(',', $excludedPaths));
foreach ($excluded as $pattern) {
    $pattern = trim($pattern);
    if (preg_match('#' . $pattern . '#', $requestPath)) {
        debug_log("Path excluded by pattern: {$pattern}");
        http_response_code(403);
        die("Access to this resource is not cached");
    }
}

// Check if path is included
if (!preg_match('#' . $includedPaths . '#', $requestPath)) {
    debug_log("Path not included: {$requestPath}");
    http_response_code(404);
    die("Not Found");
}

// Build target URL
$targetUrl = $proxyScheme . '://' . $targetHost . $requestUri;
debug_log("Target URL: {$targetUrl}");

// Prepare curl request
$ch = curl_init($targetUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_TIMEOUT => $proxyTimeout,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => $enableSslVerify,
    CURLOPT_SSL_VERIFYHOST => $enableSslVerify ? 2 : 0,
    CURLOPT_HEADER => true,
    CURLOPT_ENCODING => $enableGzip ? 'gzip, deflate' : '',
    CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'] ?? 'SimpleProxy/1.0',
    CURLOPT_HTTPHEADER => [
        'X-Forwarded-For: ' . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
        'X-Forwarded-Host: ' . $publicHost,
        'X-Forwarded-Proto: ' . ($_SERVER['HTTPS'] ?? 'off') === 'on' ? 'https' : 'http',
    ],
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$error = curl_error($ch);
curl_close($ch);

// Handle curl errors
if ($error) {
    debug_log("cURL error: {$error}");
    http_response_code(502);
    die("Bad Gateway: Unable to reach origin server");
}

// Handle non-2xx responses
if ($httpCode < 200 || $httpCode >= 300) {
    debug_log("Origin returned: {$httpCode}");
    http_response_code($httpCode);
    die("Origin Error: HTTP {$httpCode}");
}

// Split headers and body
$headers = substr($response, 0, $headerSize);
$content = substr($response, $headerSize);

debug_log("Response size: " . strlen($content) . " bytes");

// Forward important headers from origin
foreach (explode("\r\n", $headers) as $header) {
    if (preg_match('/^(Content-Type|Content-Language|Set-Cookie):/i', $header)) {
        header($header, false);
    }
}

// Rewrite domains in HTML/CSS/JS content
if (stripos($contentType, 'text/html') !== false || 
    stripos($contentType, 'text/css') !== false || 
    stripos($contentType, 'javascript') !== false) {
    
    $originalLength = strlen($content);
    
    // Replace all occurrences of target domain with public domain
    $content = str_replace([
        "//" . $targetHost . "/",
        "//" . $targetHost,
        "\"" . $targetHost . "/",
        "\"" . $targetHost . "\"",
        "'" . $targetHost . "'",
    ], [
        "//" . $publicHost . "/",
        "//" . $publicHost,
        "\"" . $publicHost . "/",
        "\"" . $publicHost . "\"",
        "'" . $publicHost . "'",
    ], $content);
    
    $replacements = $originalLength - strlen(str_replace($targetHost, '', $content)) / strlen($targetHost);
    debug_log("Domain rewrites: {$replacements} occurrences");
}

// Generate ETag
$etag = null;
if ($enableEtag) {
    $etag = '"' . md5($content) . '"';
    header('ETag: ' . $etag);
}

// Check If-None-Match (ETag)
if ($enableEtag && isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
    $clientEtag = trim($_SERVER['HTTP_IF_NONE_MATCH']);
    if ($clientEtag === $etag) {
        debug_log("ETag match - sending 304");
        http_response_code(304);
        exit();
    }
}

// Generate Last-Modified
$lastModified = gmdate('D, d M Y H:i:s', time()) . ' GMT';
header('Last-Modified: ' . $lastModified);

// Check If-Modified-Since
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $clientTime = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    $serverTime = time();
    if ($clientTime >= $serverTime - $cacheMaxAge) {
        debug_log("Not modified since - sending 304");
        http_response_code(304);
        exit();
    }
}

// Set caching headers
header('Cache-Control: public, max-age=' . $cacheMaxAge . 
       ', stale-while-revalidate=' . $cacheStaleRevalidate . 
       ', stale-if-error=' . $cacheStaleError . 
       ', immutable');
header('X-Proxy-Cache: HIT');
header('X-Proxy-Server: SimpleProxy/1.0');

// Output content
http_response_code($httpCode);
echo $content;

debug_log("Response sent: {$httpCode}");
exit();
