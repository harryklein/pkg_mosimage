<?php
/**
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class CacheFile
{

    private $absoluteFile;

    private $cacheFilename;

    private $origWidth;

    private $origHeight;

    private $displayWidth;

    private $displayHeight;

    private $imageProperties;

    private $offset_x;

    private $offset_y;

    private $config;

    private $bgcolor;

    private $proportion;

    /**
     *
     * @param ImageDisplayProperties $imageProperties
     *            Pfad und Name des Original-Bildes inkl. der Größe, wie es
     *            angezeigt werden soll
     * @param PluginConfiguration $config
     *            Plugin-Konfiguration
     * @param
     *            $proportion
     * @param
     *            $bgcolor
     * @param $origWidth Breite
     *            des Original-Bildes, Wird, wenn nicht angegeben (bzw. wenn
     *            $origHeight nicht angegeben), zur Laufzeit ermittelt
     * @param $origHeight Höhe
     *            des Original-Bides. Wird, wenn nicht angegeben (bzw. wenn
     *            $origWidth nicht angegeben), zur Laufzeit ermiitelt.
     *            
     */
    public function __construct (ImageDisplayProperties $imageProperties, PluginConfiguration $config, $proportion, $bgcolor, $origWidth = null, $origHeight = null)
    {
        $this->absoluteFile = $imageProperties->file();
        $this->config = $config;
        if (($origWidth == null) || ($origHeight == null)) {
            $size = @getimagesize($imageProperties->file());
            if (! $size) {
                JLog::add('There was a problem loading image [' . $imageProperties->file() . ']', JLog::WARNING);
                $this->origWidth = $imageProperties->displayWidth;
                $this->origHeight = $imageProperties->displayHeight;
            }
            $this->origWidth = $size[0];
            $this->origHeight = $size[1];
        } else {
            $this->origWidth = $origWidth;
            $this->origHeight = $origHeight;
        }
        
        $this->displayWidth = $imageProperties->displayWidth();
        $this->displayHeight = $imageProperties->displayHeight();
        $this->imageProperties = $imageProperties;
        $this->bgcolor = $bgcolor;
        $this->proportion = $proportion;
        $this->cacheFilename = $this->calcCacheFilename($imageProperties, $bgcolor, $proportion, $config);
    }

    public function imageProperties ()
    {
        return $this->imageProperties;
    }

    /**
     *
     * @param $language Sprache
     *            für den Place-Holder-Text
     * @return CacheFile
     */
    public function newCacheFileForPlaceholder ($language)
    {
        $otherCacheFilename = $this->displayWidth . $this->displayHeight . "$language-placeholder.jpg";
        $cachefile = new CacheFile($this->imageProperties(), $this->config, $this->proportion, $this->bgcolor, $this->origWidth(), $this->origHeight());
        $cachefile->setDisplaySize($this->displayWidth, $this->displayHeight);
        $cachefile->cacheFilename = $this->scrambleFilename($otherCacheFilename, $this->config->getScrambleFilename());
        return $cachefile;
    }

    /**
     *
     * @return Name des Cache-Files ohne Pfadangabe
     */
    public function getCacheFilename ()
    {
        return $this->cacheFilename;
    }

    /**
     *
     * @return Name des Cache-Files mit absoluter Pfadangabe
     */
    public function getAbsoluteFile ()
    {
        return $this->absoluteFile;
    }

    /**
     *
     * @return string Name des Pfades des Caches
     */
    public function getAbsoluteCachePath ()
    {
        $cachepath = JPATH_SITE . '/' . $this->getRelativeCachePath();
        jimport('joomla.filesystem.folder');
        if (! JFolder::exists($cachepath)) {
            JFolder::create($cachepath);
        }
        return $cachepath;
    }

    public function getRelativeCachePath ()
    {
        return 'cache/mosimage-cache';
    }

    public function getAbsoluteCacheFile ()
    {
        return $this->getAbsoluteCachePath() . '/' . $this->getCacheFilename();
    }

    public function getCacheFileUrl ()
    {
        return JURI::base() . '/' . $this->getRelativeCachePath() . '/' . rawurlencode(basename($this->getCacheFilename()));
    }

    public function displayWidth ()
    {
        return $this->displayWidth;
    }

    public function displayHeight ()
    {
        return $this->displayHeight;
    }

    public function getConfig ()
    {
        return $this->config;
    }

    public function getProportion ()
    {
        return $this->proportion;
    }

    public function getBackgroundColor ()
    {
        return $this->bgcolor;
    }

    public function origWidth ()
    {
        return $this->origWidth;
    }

    public function origHeight ()
    {
        return $this->origHeight;
    }
    
    public function flipOrigWidthWithHeight(){
    	list($this->origWidth, $this->origHeight) = array($this->origHeight, $this->origWidth);
    }

    /**
     * Offset des "Inhaltes"
     *
     * @param
     *            $x
     * @param
     *            $y
     */
    public function setImageOffset ($x, $y)
    {
        $this->offset_x = $x;
        $this->offset_y = $y;
    }

    public function offsetX ()
    {
        return $this->offset_x;
    }

    public function offsetY ()
    {
        return $this->offset_y;
    }

    /**
     * Größe für die Anzeiges des Bildes
     *
     * @param
     *            $x
     * @param
     *            $y
     */
    public function setDisplaySize ($x, $y)
    {
        $this->displayWidth = $x;
        $this->displayHeight = $y;
    }

    private function scrambleFilename ($filename, $scramble)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        switch ($scramble) {
            case 'md5':
                return md5($filename) . '.' . $ext;
            case 'crc32':
                return sprintf("%u", crc32($filename)) . '.' . $ext;
            case 'sha1':
                return sha1($filename) . '.' . $ext;
        }
        return $filename;
    }

    private function calcCacheFilename (ImageDisplayProperties $imageProperties, $bgcolor, $proportion)
    {
        $builder = new CacheFileNameBuilder($imageProperties->file());
        $builder = $builder->addWatermarkInfo($this->config->getWatermarkFile(), $this->config->isUsedWatermark());
        $builder = $builder->addBackgroundColorInfo($bgcolor);
        $builder = $builder->addProportionInfo($proportion);
        $builder = $builder->addDisplaySizeInfo($imageProperties);
        return $this->scrambleFilename($builder->build(), $this->config->getScrambleFilename());
    }
}

class CacheFileNameBuilder
{

    private $filename;

    private $filenameInfo;

    private $proportionInfo;

    private $displayImagesSizeInfo;

    private $bgcolorInfo;

    private $watermarkInfo;

    private $scramble;

    public function __construct ($filename)
    {
        $this->filename = $filename;
        $retativFilename = substr($filename, strlen(JPATH_SITE . '/images/'));
        $this->filenameInfo = str_replace(array(
                ':',
                '/',
                '\\',
                ' '
        ), '.', $retativFilename);
    }

    /**
     * Damit beim späteren Zusammenbau des Names des CacheFile AddBlock das
     * Image nicht unterdückt,
     * wird vom Waterfilename der sha1 gebildet.
     * Nach Versichen blockiert AddBlock das Bild nicht mehr.
     *
     * @param
     *            $watermarkFilename
     * @param
     *            $isUsedWatermark
     * @return $this
     */
    public function addWatermarkInfo ($watermarkFilename, $isUsedWatermark)
    {
        if ($isUsedWatermark) {
            $this->watermarkInfo = sha1(basename($watermarkFilename));
        } else {
            $this->watermarkInfo = 'x';
        }
        return $this;
    }

    public function addDisplaySizeInfo (ImageDisplayProperties $imageProperties)
    {
        $this->displayImagesSizeInfo = $imageProperties->displayWidth() . 'x' . $imageProperties->displayHeight();
        return $this;
    }

    public function addProportionInfo ($proportion)
    {
        $this->proportionInfo = substr($proportion, 0, 1);
        return $this;
    }

    public function addBackgroundColorInfo ($bgcolor)
    {
        $this->bgcolorInfo = sprintf('0x%06x', $bgcolor);
        return $this;
    }

    public function build ()
    {
        $filenameForCache = $this->proportionInfo . '_' . $this->displayImagesSizeInfo . '_' . $this->bgcolorInfo . '_' . $this->watermarkInfo . '_' .
                 $this->filenameInfo;
        return $filenameForCache;
    }
}

?>