<?php

namespace CFS\Image;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;

require_once 'vendor/autoload.php';

class CFSImage {
    private $connection     = NULL;
    private $file           = NULL;
    private $extension      = NULL;
    private $height         = 0;
    private $width          = 0;
    private $filename       = NULL;
    
    public function __construct(\SplFileInfo $file) {
        $this->file = $file;
    }
    
    private function getConnection() {
        if ($this->connection === NULL) {
            $config = new \Doctrine\DBAL\Configuration();
            $connectionParams = array(
                'dbname' => DB_NAME,
                'user' => DB_USER_NAME,
                'password' => DB_PASSWORD,
                'host' => DB_HOST,
                'driver' => 'pdo_mysql',
            );
            $connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
            
            $this->connection = $connection;
        }
        
        return $this->connection;
    }
    
    public function validateFileExtension(array $validExtensions = array()) {
        $finfo = new \finfo(FILEINFO_MIME);
        $mimetype = $finfo->file($this->file->getRealPath(), FILEINFO_MIME_TYPE);
        
        // Use mime type to imply real extension
        switch($mimetype) {
            case 'image/jpeg':
                $this->extension = 'jpg';
                break;
            case 'image/png':
                $this->extension = 'png';
                break;
            case 'image/gif':
                $this->extension = 'gif';
                break;
        }
        
        if (!in_array($this->extension, $validExtensions, TRUE)) throw new \Exception('Invalid file type');
    }
    
    public function validateFileSize($size = 0) {
        $filesize = (int) number_format($this->file->getSize() / 1048576, 0);
        
        if ($filesize > (int)$size) throw new \Exception('File too large');
    }
    
    public function validateImageSize($width = 0, $height = 0, $comparison = '>') {
        $imagine        = new Imagine();
        $boxInterface   = $imagine->open($this->file->getRealPath())->getSize();
        
        $this->width    = (int)$boxInterface->getWidth();
        $this->height   = (int)$boxInterface->getHeight();
        
        if ($comparison === '>') {
            if ($this->width < (int)$width) throw new \Exception('Image not wide enough');
            if ($this->height < (int)$height) throw new \Exception('Image not tall enough');
        }
        else if ($comparison === '<') {
            if ($this->width > (int)$width) throw new \Exception('Image is too wide');
            if ($this->height > (int)$height) throw new \Exception('Image is too tall');
        }
        else if ($comparison === '=') {
            if ($this->width < (int)$width) throw new \Exception('Image not the correct width');
            if ($this->height < (int)$height) throw new \Exception('Image not the correct height');
        }
        else {
            throw new \Exception('Image not the correct size');
        }
    }
    
    public function saveBanner() {
        $filename = 'banner_' . md5(rand().time().$_SESSION['u_id']) . '.' . $this->extension;
        $currentPath = $this->file->getRealPath();
        $newPath = __DIR__ . '/../../../images/banners/' . $filename;
        move_uploaded_file($currentPath, $newPath);
        return $filename;
    }
    
    public function scaleAndSave($ad, $type, $width = 0, $height = 0) {
        // Create image in database
        $sql = "INSERT INTO cf_photos SET ad_id = :ad, post_type = :type";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue("ad", $ad);
        $stmt->bindValue("type", $type);
        $stmt->execute();
        $id = $this->getConnection()->lastInsertId();
        
        $this->filename = 'photo-' . $id . '.jpg';
        
        // Scale image
        $imagine = new Imagine();
        
        // Switch for landscape photos
        if ($this->height < $this->width) {
            $new_height = $width;
            $width = $height;
            $height = $new_height;
        }
        
        try {
            $imagine->open($this->file->getRealPath())
                ->thumbnail(new Box($width, $height), ManipulatorInterface::THUMBNAIL_INSET)
                ->save(__DIR__ . '/../../../images/photos/' . $this->filename, array('quality' => 100));
        } catch (\Imagine\Exception\Exception $e) {
            // Remove database entry
            $this->getConnection()->delete('cf_photos', array('photo_id' => $id));
            throw new \Exception('Error processing and saving image');
        }
        
        // Save image with new name
        $sql = "UPDATE cf_photos SET photo_filename = :filename WHERE photo_id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue("filename", $this->filename);
        $stmt->bindValue("id", $id);
        $stmt->execute();
        
        return $this->filename;
    }
    
    public function generateThumbnail($width = 0, $height = 0) {
        $imagine = new Imagine();
        
        $filename = $this->file->getBasename('.jpg') . '-w' . $width . 'h' . $height . '.jpg';
        
        $imagine->open($this->file->getRealPath())
            ->thumbnail(new Box($width, $height), ManipulatorInterface::THUMBNAIL_INSET)
            ->save(__DIR__ . '/../../../images/photos/thumbnails/' . $filename, array('quality' => 100));
        
        return $filename;
    }
    
    public function rotateImage($direction = 'clockwise') {
        $imagine = new Imagine();
        
        switch($direction) {
            case 'anticlockwise':
                $degrees = 90;
                break;
            case 'clockwise':
            default:
                $degrees = -90;
                break;
        }
        
        $imagine->open($this->file->getRealPath())
            ->rotate($degrees)
            ->save($this->file->getRealPath(), array('quality' => 100));
        
        $this->flushThumbnails();
    }
    
    public function removeImage() {
        $id = $this->getFileId();
        
        $this->flushThumbnails();
        
        @unlink($this->file->getRealPath());
        
        $this->getConnection()->delete('cf_photos', array('photo_id' => $id));
    }
    
    protected function getFileId() {
        $basename = $this->file->getBasename('.jpg');
        $parts = explode('-', $basename);
        return $parts[1];
    }
    
    protected function flushThumbnails() {
        $files = array();
        if ($handle = opendir(__DIR__ . '/../../../images/photos/thumbnails/')) {
            while (false !== ($file = readdir($handle)))
            {
                $parts = explode('-', $file);
                if ($file != "." && $file != ".." && $parts[1] == $this->getFileId())
                {
                    @unlink(__DIR__ . '/../../../images/photos/thumbnails/' . $file);
                }
            }
            closedir($handle);
        }
    }
}
