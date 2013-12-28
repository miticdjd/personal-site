<?php

/* Setup folders */
define('CACHE_DIR', realpath(dirname(__FILE__)) . '/cache/');
define('CSS_DIR', realpath(dirname(__FILE__)) . '/css/');
define('JS_DIR', realpath(dirname(__FILE__)) . '/js/');

/* Get type of files we will cache and what files we will cache */
$type = $_GET['type'];
$fileNames = $_GET['files'];
$files = explode(',', $fileNames);

/* Let's get base directory */
switch ($type) {
    case 'css':
        $contentType = 'text/css';
        $baseDir = CSS_DIR;
        break;
    case 'js':
        $contentType = 'application/javascript';
        $baseDir = JS_DIR;
        break;
    default:
        header('HTTP/1.0 503 Not Implemented');
        exit;
        break;
}

$lastmodified = 0;
foreach ($files as $file) {
    $path = $baseDir . $file;
    $ext = pathinfo($path, PATHINFO_EXTENSION);

    if (!in_array($ext, array('css', 'js'))) {
        /* We have some file that is not css or js */
        header('HTTP/1.0 403 Forbidden');
        die();
    }

    if (!file_exists($path)) {
        /* Some of the files are not found */
        header('HTTP/1.0 404 Not Found');
        die();
    }

    $lastmodified = max($lastmodified, filemtime($path));
}

$hash = md5($fileNames);
header('Etag: "' . $hash . '"');

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"') {
    /* This is returning visit so don't send anything */
    header('HTTP/1.0 304 Not Modified');
    header('Content-Length: 0');
} else {
    /* This is new visit, let's prepare files */

    /* Let's detect supported methods */
    $gzip = preg_match('~gzip~', $_SERVER['HTTP_ACCEPT_ENCODING']);
    $deflate = preg_match('~deflate~', $_SERVER['HTTP_ACCEPT_ENCODING']);

    /* Determ supported method */
    $encoding = 'none';
    if ($gzip) {
        $encoding = 'gzip';
    } else if ($deflate) {
        $encoding = 'deflate';
    }
    
    $filename = $hash . '.' . $type . ($encoding != 'none' ? '.' . $encoding : '');
    
    if (file_exists(CACHE_DIR . $filename)) {
        /* File already exists */
        $file = fopen(CACHE_DIR . $filename, 'rb');
        if ($file) {

            if ($encoding != 'none') {
                header('Content-Encoding: ' . $encoding);
            }

            header('Content-Type: ' . $contentType);
            header('Content-Length: ' . filesize(CACHE_DIR . $filename));

            fpassthru($file);
            fclose($file);
        }
    } else {
        /* File doesn't exists, so let's get contents */
        $content = '';
        
        foreach ($files as $file) {
            $content .= "\n\n" . file_get_contents($baseDir . $file);
        }
        
        header ('Content-Type: ' . $contentType);
        
        if ($encoding != 'none') {
            /* Send compressed content */
            $content = gzencode($content, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
            header ('Content-Encoding: ' . $encoding);
            header ('Content-Length: ' . strlen($content));
            echo $content;
        } else {
            /* Send regular content */
            header ('Content-Length: ' . strlen($content));
            echo $content;
        }
        
        $file = fopen(CACHE_DIR . $filename, 'wb');
        if ($file) {
            fwrite($file, $content);
            fclose($file);
        }
    }
}
?>