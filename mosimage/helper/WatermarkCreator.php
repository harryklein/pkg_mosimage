<?php
/**
 * @version 2.0 $Id: WatermarkCreator.php,v 1.3 2014-03-04 22:55:40 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class WatermarkCreator
{

    private $config;

    public function __construct (MosimageConfiguration $config)
    {
        $this->config = $config;
    }

    public function writeWatermarkInfo (&$sourcefileId, $thumbnailMode, CacheFile $cacheFile)
    {
        if (! $this->config->isUsedWatermark() || $thumbnailMode) {
            return;
        }
        static $disable_alpha_warning;
        
        $watermarkfile = $this->config->getWatermarkFile();
        $align = $this->config->getWatermarkLeft();
        $valign = $this->config->getWatermarkTop();
        
        if ($this->config->getWatermarkTransparencyType() == 'alpha') {
            $transcolor = FALSE;
        } else {
            $transcolor = $this->config->getWatermarkTransparentColor();
        }
        $transparency = $this->config->getWatermarkTransparency();
        
        try {
            $watermarkfile_id = $this->loadWatermarkFile($watermarkfile);
        } catch (Exception $e) {
            return false;
        }
        @imageAlphaBlending($watermarkfile_id, false);
        
        $result = @imageSaveAlpha($watermarkfile_id, true);
        if (! $result) {
            if (! $disable_alpha_warning) {
                $msg = "Watermark problem: your server does not support alpha blending (requires GD 2.0.1+)";
                JLog::add($msg, JLog::WARNING);
            }
            $disable_alpha_warning = true;
            imagedestroy($watermarkfile_id);
            return false;
        }
        
        $offset_w = $cacheFile->offsetX();
        $offset_h = $cacheFile->offsetY();
        $w = $cacheFile->displayWidth();
        $h = $cacheFile->displayHeight();
        
        $watermarkfileWidth = imageSX($watermarkfile_id);
        $watermarkfileHeight = imageSY($watermarkfile_id);
        $watermarkOffsetX = $this->calcXOffsetForWatermark($align, $watermarkfileWidth, $offset_w, $w);
        $watermarkOffsetY = $this->calcYOffsetForWatermark($valign, $watermarkfileHeight, $offset_h, $h);
        $fileType = strtolower(pathinfo($watermarkfile, PATHINFO_EXTENSION));
        $sourcefileId = $this->upsampleImageIfNecessary($fileType, $sourcefileId);
        
        if ($transcolor !== false) {
            $transcolAsInt = intval(str_replace('#', '', $transcolor), 16);
            imagecolortransparent($watermarkfile_id, $transcolAsInt); // use transparent color
            imagecopymerge($sourcefileId, $watermarkfile_id, $watermarkOffsetX, $watermarkOffsetY, 0, 0, $watermarkfileWidth, $watermarkfileHeight, 
                    $transparency);
        } else {
            imagecopy($sourcefileId, $watermarkfile_id, $watermarkOffsetX, $watermarkOffsetY, 0, 0, $watermarkfileWidth, $watermarkfileHeight); // True
                                                                                                                                                    // alphablend
        }
        imagedestroy($watermarkfile_id);
        return true;
    }

    private function loadWatermarkFile ($watermarkfile)
    {
        $fileType = strtolower(pathinfo($watermarkfile, PATHINFO_EXTENSION));
        switch ($fileType) {
            case 'png':
                $watermarkfile_id = @imagecreatefrompng($watermarkfile);
                break;
            case 'gif':
                $watermarkfile_id = @imagecreatefromgif($watermarkfile);
                break;
            case 'jpg':
            case 'jpeg':
                $watermarkfile_id = @imagecreatefromjpeg($watermarkfile);
                break;
            default:
                $watermarkfile = basename($watermarkfile);
                $msg = "You cannot use a [.$fileType] file ($watermarkfile) as a watermark";
                static $disable_wm_ext_warning;
                if (! $disable_wm_ext_warning) {
                    JLog::add($msg, JLog::WARNING);
                }
                $disable_wm_ext_warning = true;
                throw new Exception($msg);
        }
        if (! $watermarkfile_id) {
            $msg = "There was a problem loading the watermark [$watermarkfile]";
            static $disable_wm_load_warning;
            if (! $disable_wm_load_warning) {
                JLog::add($msg, JLog::WARNING);
            }
            $disable_wm_load_warning = true;
            throw new Exception($msg);
        }
        return $watermarkfile_id;
    }

    private function calcXOffsetForWatermark ($align, $watermarkfileWidth, $offset_width, $w)
    {
        switch ($align) {
            case 'left':
                $dest_x = $offset_width;
                break;
            case 'right':
                $dest_x = $w - $watermarkfileWidth - $offset_width;
                break;
            case 'center':
            default:
                $dest_x = ($w / 2) - ($watermarkfileWidth / 2) + $offset_width;
                break;
        }
        if ($dest_x < 0) {
            return 0;
        }
        return intval($dest_x);
    }

    private function calcYOffsetForWatermark ($valign, $watermarkfileHeight, $offset_heigth, $h)
    {
        switch ($valign) {
            case 'top':
                $dest_y = $offset_heigth;
                break;
            case 'middle':
                $dest_y = ($h / 2) - ($watermarkfileHeight / 2) + $offset_heigth;
                break;
            case 'bottom':
            default:
                $dest_y = $h - $watermarkfileHeight - $offset_heigth;
                break;
        }
        if ($dest_y < 0) {
            return 0;
        }
        return intval($dest_y);
    }

    private function upsampleImageIfNecessary ($fileType, $sourcefileId)
    {
        if ($fileType == 'gif') {
            $sourcefileWidth = imageSX($sourcefileId);
            $sourcefileHeight = imageSY($sourcefileId);
            $tempimage = imagecreatetruecolor($sourcefileWidth, $sourcefileHeight);
            imagecopy($tempimage, $sourcefileId, 0, 0, 0, 0, $sourcefileWidth, $sourcefileHeight);
            return $tempimage;
        }
        return $sourcefileId;
    }
}

?>