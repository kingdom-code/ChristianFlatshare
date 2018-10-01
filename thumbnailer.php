<?php

use CFS\Image\CFSImage;

// Autoloader
require_once 'web/global.php';

$width  = (!isset($_GET['w']) || (int)$_GET['w'] == 0) ? 100 : $_GET['w'];
$height = (!isset($_GET['h']) || (int)$_GET['h'] == 0) ? 100 : $_GET['h'];

if (isset($_GET['img'])) {
    $parts = explode('/', $_GET['img']);
    $file = explode('.', $parts[2]);
    
    $name = __DIR__ . '/' . $parts[0] . '/' . $parts[1] . '/thumbnails/' . $file[0] . '-w' . $width . 'h' . $height . '.jpg';
    
    // Thumbnail already generated?
    if (!is_readable($name)) {
        // Generate
        if (is_readable(__DIR__ . '/' . $_GET['img'])) {
            $file = new SplFileInfo(__DIR__ . '/' . $_GET['img']);
            $image = new CFSImage($file);
    
            $filename = $image->generateThumbnail($width, $height);
            
            $name = __DIR__ . '/images/photos/thumbnails/' . $filename;
        }
    }
    
    $fp = fopen($name, 'rb');

    // send the right headers
    header("Content-Type: image/jpeg");
    header("Content-Length: " . filesize($name));

    // dump the picture and stop the script
    fpassthru($fp);
    exit;
}