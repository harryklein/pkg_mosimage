<?php
/**
 * @version 2.0 $Id: ThumbnailCreator.php,v 1.7 2014-03-04 22:54:39 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ROOT . '/plugins/content/mosimage/mosimage/helper/CacheFile.php';
require_once JPATH_ROOT . '/plugins/content/mosimage/mosimage/helper/MosimageConfiguration.php';
require_once JPATH_ROOT . '/plugins/content/mosimage/mosimage/helper/WatermarkCreator.php';

class ThumbnailCreator
{

    private $config;

    private $watermarkCreator;

    public function __construct (MosimageConfiguration $config)
    {
        $this->config = $config;
        $this->watermarkCreator = new WatermarkCreator($config);
    }

    /**
     *
     * Erzeugt das PlaceholderImage und liefert dessen URL, Höhe und Breite.
     * @paramd CacheFile $origCacheFile das ursprünge CacheFile, welches durch den Platzhalter ersetzt werden soll
     * @return CacheFile
     */
    private function generatePlaceHolder (CacheFile $origCacheFile)
    {
        $document = JFactory::getDocument();
        $language = $document->getLanguage();
        $displayWidth = $origCacheFile->displayWidth();
        $displayHeight = $origCacheFile->displayHeight();
        
        $cacheFile = $origCacheFile->newCacheFileForPlaceholder($language);
        
        if (file_exists($cacheFile->getAbsoluteCacheFile())) {
            return $cacheFile;
        }
        $placeholderImage = ImageCreateTrueColor($displayWidth, $displayHeight);
        imagefilledrectangle($placeholderImage, 2, 2, $displayWidth - 3, $displayHeight - 3, 0xeeeeee);
        $fontSize = 2;
        $string = JText::_('MOSIMAGE_PLACE_HOLDER_IMAGE_TEXT');
        $textWidth = imagefontwidth($fontSize) * strlen($string);
        $center = ceil($displayWidth / 2);
        $x = $center - (ceil($textWidth / 2));
        $color = ImageColorAllocate($placeholderImage, 0, 0, 0);
        ImageString($placeholderImage, $fontSize, $x, 45, $string, $color);
        imagejpeg($placeholderImage, $cacheFile->getAbsoluteCacheFile(), 80);
        imagedestroy($placeholderImage);
        return $cacheFile;
    }

    /**
     * @param ImageDisplayProperties $imageSizeProprties
     * @return CacheFile
     */
    public function createThumbnailImage (ImageDisplayProperties &$imageSizeProprties)
    {
        return $this->getResizedImage($imageSizeProprties, true, $this->config->getThumbnailBackgroundColor());
    }

    /**
     * @param ImageDisplayProperties $imageSizeProprties
     * @return CacheFile
     */
    public function createResizedImage (ImageDisplayProperties &$imageSizeProprties)
    {
        return $this->getResizedImage($imageSizeProprties, false, $this->config->getFullsizeBackgroundColor());
    }

    /**
     * Erzeugt ein verkleinertes Abbild des Originalbildes.
     */
    private function generateResizedImage (CacheFile $cacheFile, $proportion, $thumbnailMode, $bgcolor = 0xff0000)
    {
        static $countGenerateImage;
        
        if (! isset($countGenerateImage)) {
            $countGenerateImage = 0;
        }
        $countGenerateImage ++;
        
        if ($thumbnailMode) {
            $minWidth = 50; // Wert siehe mosimage.xml => thumb_width => min
        } else {
            $minWidth = 200; // Wert siehe mosimage.xml => full_width => min
        }
        
        $origw = $cacheFile->origWidth();
        $origh = $cacheFile->origHeight();
        $displayWidth = $cacheFile->displayWidth();
        $displayHeight = $cacheFile->displayHeight();
        
        $src_img = &$this->loadImageFromFile($cacheFile);
        // Behandlung kleiner Bilder
        // - Die original-Höhe ist kleiner als die Zielhöhe. Zielhöhe kann nie 0 sein.
        // - Es bleibt diese Höhe erhalten und wir schneiden ggf. etwas von der Breite weg.
        // - Bei Auto-Breite wird die Breite ggf. auf ein Seitenverhältnis von 2:1 begrenzt, damit
        // das Bild nicht übermäßig breit wird
        if ($origh < $displayHeight) {
            // Im Auto-Modus wird das Seitenverhältnis aus dem Original genmmen
            if ($displayWidth == 0) {
                $displayWidth = intval($origw / $origh * $displayHeight);
                if ($displayWidth > $displayHeight * 2) { // Seitenverhältnis ggf. auf 2:1 reduzieren
                    $displayWidth = $displayHeight * 2;
                }
            }
            // Bild ist auch so schmal, das es reinpasst
            if ($origw < $displayWidth) {
                $offset_w = ($displayWidth - $origw) / 2;
                $offset_h = $this->calcPropertion($displayHeight, $origh, $proportion);
                $src_w = $origw;
                $src_h = $origh;
            } else { // ansonsten schneiden wir es rechts ab
                $offset_w = 0;
                $offset_h = $this->calcPropertion($displayHeight, $origh, $proportion);
                $src_w = $displayWidth;
                $src_h = $origh;
            }
            
            if ($displayWidth < $minWidth) {
                $this->debug($cacheFile, "[Fix auto-Breite] display: [$displayWidth x $displayHeight] offset: [$offset_w x $offset_h]");
                $offset_w = ($minWidth - $displayWidth) / 2;
                $displayWidth = $minWidth;
            }
            
            $cacheFile->setImageOffset($offset_w, $offset_h);
            $cacheFile->setDisplaySize($displayWidth, $displayHeight);
            
            $this->debug($cacheFile, "[kleine Höhe ] display: [$displayWidth x $displayHeight] offsetW: [$offset_w] offsetH: [$offset_h]");
            
            if ($countGenerateImage > $this->config->getMaxResizedImagePerRequest()) {
                return $this->generatePlaceHolder($cacheFile, $this->config->getScrambleFilename());
            }
            
            $dst_img = ImageCreateTrueColor($displayWidth, $displayHeight);
            imagefill($dst_img, 0, 0, $bgcolor);
            imagecopy($dst_img, $src_img, $offset_w, $offset_h, 0, 0, $src_w, $src_h);
            $this->watermarkCreator->writeWatermarkInfo($dst_img, $thumbnailMode, $cacheFile);
            $this->writeImageToFile($dst_img, $cacheFile);
            imagedestroy($src_img);
            imagedestroy($dst_img);
            return $cacheFile;
        }
        
        // Behandlung kleiner Bilder
        // - Die original-Breite ist kleiner als die ZielBreite, Zielbreite ist ungleich 0
        // - Es bleibt diese Breite erhalten und wir schneiden ggf. etwas von der Höhe weg.
        if (($origw < $displayWidth) && ($displayWidth != 0)) {
            // Bild ist auch so hoch, das es reinpasst, das Bild wird dabei zentiert
            if ($origh < $displayHeight) {
                $offset_w = ($displayWidth - $origw) / 2;
                $offset_h = ($displayHeight - $origh) / 2;
                $src_w = $origw;
                $src_h = $origh;
            } else { // ansonsten schneiden wir es unten ab
                $offset_w = ($displayWidth - $origw) / 2;
                $offset_h = 0;
                $src_w = $origw;
                $src_h = $displayHeight;
            }
            $cacheFile->setImageOffset($offset_w, $offset_h);
            
            $this->debug($cacheFile, "[schmal] display: [$displayWidth x $displayHeight] offsetW: [$offset_w] offsetH: [$offset_h]");
            
            if ($countGenerateImage > $this->config->getMaxResizedImagePerRequest()) {
                return $this->generatePlaceHolder($cacheFile, $this->config->getScrambleFilename());
            }
            
            $dst_img = ImageCreateTrueColor($displayWidth, $displayHeight);
            imagefill($dst_img, 0, 0, $bgcolor);
            
            imagecopy($dst_img, $src_img, $offset_w, $offset_h, 0, 0, $src_w, $src_h);
            
            $this->writeImageToFile($dst_img, $cacheFile);
            imagedestroy($src_img);
            imagedestroy($dst_img);
            return $cacheFile;
        }
        
        // Breite automatisch berechnen, Seitenverhältnis beibehalten, wenn sinnvoll.
        if ($displayWidth == 0) {
            $displayWidth = intval($origw / $origh * $displayHeight);
            $offset_w = 0;
            $offset_h = 0;
            $w = $displayWidth;
            $h = $displayHeight;
            
            if ($displayWidth < $minWidth) {
                $this->debug($cacheFile, "[Fix auto-Breite] display: [$displayWidth x $displayHeight] offset: [$offset_w x $offset_h]");
                $offset_w = ($minWidth - $displayWidth) / 2;
                $displayWidth = $minWidth;
            }
            $cacheFile->setImageOffset($offset_w, $offset_h);
            $cacheFile->setDisplaySize($displayWidth, $displayHeight);
            
            $this->debug($cacheFile, "[auto-Breite] display: [$displayWidth x $displayHeight] offset: [$offset_w x $offset_h]");
            
            if ($countGenerateImage > $this->config->getMaxResizedImagePerRequest()) {
                return $this->generatePlaceHolder($cacheFile, $this->config->getScrambleFilename());
            }
            
            $dst_img = ImageCreateTrueColor($displayWidth, $displayHeight);
            imagefill($dst_img, 0, 0, $bgcolor);
            imagecopyresampled($dst_img, $src_img, $offset_w, $offset_h, 0, 0, $w, $h, $origw, $origh);
            $this->watermarkCreator->writeWatermarkInfo($dst_img, $thumbnailMode, $cacheFile);
            
            $this->writeImageToFile($dst_img, $cacheFile);
            imagedestroy($src_img);
            imagedestroy($dst_img);
            return $cacheFile;
        }
        
        // - Zielbreite ist bekannt und
        // - Original-Bilder ist größer als Zielbild
        if ($displayWidth != 0 && $displayHeight != 0) {
            if ($origw > $origh) { // landscape
                if ($displayWidth > $displayHeight) {
                    $w = $origw / $origh * $displayHeight;
                    $h = $displayHeight;
                    $offset_w = ($displayWidth - $w) / 2;
                    $offset_h = 0;
                } else {
                    $w = $displayWidth;
                    $h = $origh / $origw * $displayWidth;
                    $offset_w = 0;
                    $offset_h = $this->calcPropertion($displayHeight, $h, $proportion);
                }
            } else { // portrait
                if ($displayWidth >= $displayHeight) { // Zielbild ist Landcape
                    $w = $origw / $origh * $displayHeight;
                    $h = $displayHeight;
                    $offset_w = ($displayWidth - $w) / 2;
                    $offset_h = 0;
                } else {
                    $w = $displayWidth;
                    $h = $origh / $origw * $displayWidth;
                    $offset_w = 0;
                    $offset_h = $this->calcPropertion($displayHeight, $h, $proportion);
                }
            }
            
            $cacheFile->setImageOffset($offset_w, $offset_h);
            $cacheFile->setDisplaySize($displayWidth, $displayHeight);
            
            $this->debug($cacheFile, "[normal] display: [$displayWidth x $displayHeight]  content [$w x $h] offset: [$offset_w x $offset_h]");
            
            if ($countGenerateImage > $this->config->getMaxResizedImagePerRequest()) {
                return $this->generatePlaceHolder($cacheFile, $this->config->getScrambleFilename());
            }
            
            $dst_img = ImageCreateTrueColor($displayWidth, $displayHeight);
            imagefill($dst_img, 0, 0, $bgcolor);
            imagecopyresampled($dst_img, $src_img, $offset_w, $offset_h, 0, 0, $w, $h, $origw, $origh);
            $this->watermarkCreator->writeWatermarkInfo($dst_img, $thumbnailMode, $cacheFile);
            $this->writeImageToFile($dst_img, $cacheFile);
            imagedestroy($src_img);
            imagedestroy($dst_img);
            return $cacheFile;
        }
    }

    private function &loadImageFromFile (CacheFile $cacheFile)
    {
        $ext = pathinfo($cacheFile->getAbsoluteFile(), PATHINFO_EXTENSION);
        switch (strtolower($ext)) {
            case 'png':
                $src_img = imagecreatefrompng($cacheFile->getAbsoluteFile());
                break;
            case 'gif':
                $src_img = imagecreatefromgif($cacheFile->getAbsoluteFile());
                break;
            default:
                $src_img = imagecreatefromjpeg($cacheFile->getAbsoluteFile());
        }
        return $src_img;
    }

    private function writeImageToFile (&$img, CacheFile $cacheFile)
    {
        $ext = pathinfo($cacheFile->getAbsoluteFile(), PATHINFO_EXTENSION);
        switch (strtolower($ext)) {
            case 'png':
                $imagefunction = "imagepng";
                break;
            case 'gif':
                $imagefunction = "imagegif";
                break;
            default:
                $imagefunction = "imagejpeg";
        }
        if ($imagefunction == 'imagejpeg') {
            $result = @$imagefunction($img, $cacheFile->getAbsoluteCacheFile(), $this->config->getJpegQuality());
        } else {
            $result = @$imagefunction($img, $cacheFile->getAbsoluteCacheFile());
        }
    }

    private function checkJpeg ()
    {
        if (! function_exists("imagecreatefromjpeg")) {
            $msg = 'JPG is not supported on this server.';
            JLog::add($msg, JLog::WARNING);
            throw new Exception($msg);
        }
    }

    private function checkGif ()
    {
        if (! function_exists("imagecreatefromgif")) {
            $msg = 'GIF is not supported on this server.';
            JLog::add($msg, JLog::WARNING);
            throw new Exception($msg);
        }
    }

    private function checkPng ()
    {
        if (! function_exists("imagecreatefrompng")) {
            $msg = 'PNG is not supported on this server.';
            JLog::add($msg, JLog::WARNING);
            throw new Exception($msg);
        }
    }

    private function calcPropertion ($value, $imageValue, $propertion)
    {
        switch ($propertion) {
            case 'bestfit_top':
                return 0;
            case 'bestfit_bottom':
                return ($value - $imageValue);
            case 'bestfit':
            case 'bestfit_middle':
            default:
                return (($value - $imageValue) / 2);
        }
    }

    private function getResizedImage (ImageDisplayProperties $imageProperties, $thumbnailMode = false, $bgcolor = 0xFF0000)
    {
        if ($thumbnailMode) {
            $proportion = 'bestfit';
        } else {
            $proportion = $this->config->getImageProportions();
        }
        $cacheFile = new CacheFile($imageProperties, $this->config, $proportion, $bgcolor);
        if (file_exists($cacheFile->getAbsoluteCacheFile())) {
            // TODO das folgende ist etwas Magie: Bei auto-Breite wird das Bild ansonsten mit Breite 0 dargestellt
            $size = @getimagesize($cacheFile->getAbsoluteCacheFile());
            $cacheFile->setDisplaySize($size[0], $size[1]);
            return $cacheFile;
        } else {
            return $this->generateResizedImage($cacheFile, $proportion, $thumbnailMode, $bgcolor);
        }
    }

    private function debug (CacheFile $cacheFile, $msg)
    {
        if ($this->config->isDebug()) {
            $filename = $cacheFile->getCacheFilename();
            echo "[$filename] $msg<br />";
        }
        return;
    }
}

?>