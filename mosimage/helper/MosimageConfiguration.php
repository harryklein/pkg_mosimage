<?php
/**
 * @version 2.0 $Id: MosimageConfiguration.php,v 1.5 2014-02-18 23:09:09 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class MosimageConfiguration
{

    const DEFAULT_THUUMNAIL_WIDTH = 150;

    const DEFAULT_THUMBNAIL_HEIGHT = 100;

    const MAX_THUUMNAIL_WIDTH = 500;

    const MAX_THUMBNAIL_HEIGHT = 500;

    public $params;

    public function __construct ($params)
    {
        $this->params = $params;
    }

    public function getJpegQuality ()
    {
        return $this->params->get('quality', 80);
    }

    public function getScrambleFilename ()
    {
        return $this->params->get('scramble', 'off');
    }

    private function getThumbnailSize ()
    {
        $width = intval($this->params->get('thumb_width', self::DEFAULT_THUUMNAIL_WIDTH));
        $height = intval($value = $this->params->get('thumb_height', self::DEFAULT_THUMBNAIL_HEIGHT));
        
        if ($width < 0) {
            $width = self::DEFAULT_THUUMNAIL_WIDTH;
        }
        if ($height < 0) {
            $width = self::DEFAULT_THUMBNAIL_HEIGHT;
        }
        
        if (($width == 0) and ($height == 0)) {
            $width = self::DEFAULT_THUUMNAIL_WIDTH;
            $width = self::DEFAULT_THUMBNAIL_HEIGHT;
        }
        
        $result = new stdClass();
        if ($height < $width) {
            $result->width = $width;
            $result->height = $height;
        } else {
            $result->width = $height;
            $result->height = $width;
        }
        
        $result->width = $width;
        $result->height = $height;
        return $result;
    }

    public function getThumbnailMaxWidth ()
    {
        return $this->getThumbnailSize()->width;
    }

    public function getThumbnailMaxHeight ()
    {
        return $this->getThumbnailSize()->height;
    }

    public function getThumbnailBackgroundColor ()
    {
        $colorAsHtml = $this->params->get('thumb_background_color', '#ffffff');
        return str_replace('#', '0x', $colorAsHtml);
    }

    public function getThumbnailCaptionAlign ()
    {
        return $this->params->get('thumb_caption_align', 'bottom');
    }

    public function getThumbnailBorderWidth ()
    {
        return $this->params->get('thumb_border_width', '1');
    }

    public function getFullsizeMaxWidth ()
    {
        return $this->params->get('full_width', 800);
    }

    public function getFullsizeMaxHeight ()
    {
        return $this->params->get('full_height', 600);
    }

    public function isViewCaptionTextForFullsize ()
    {
        return $this->params->get('full_caption', 1);
    }

    public function getFullsizeBackgroundColor ()
    {
        $colorAsHtml = $this->params->get('image_background_color', '#ffffff');
        return str_replace('#', '0x', $colorAsHtml);
    }

    public function isViewCaptionTextForThumbnail ()
    {
        return $this->params->get('thumb_caption', 0);
    }

    public function getWatermarkTransparencyType ()
    {
        return $this->params->get('transparency_type', 'alpha');
    }

    public function getWatermarkTransparency ()
    {
        $value = $this->params->get('transparency', 25);
        if ($value < 0 || $value > 100) {
            return 100;
        }
        return $value;
    }

    public function getWatermarkTransparentColor ()
    {
        return $this->params->get('transparent_color', '#000000');
    }

    public function getWatermarkTop ()
    {
        return $this->params->get('watermark_top', '');
    }

    public function getWatermarkLeft ()
    {
        return $this->params->get('watermark_left', '');
    }

    public function getWatermarkFilename ()
    {
        return $this->params->get('watermark_file', '');
    }

    public function getWatermarkFile ()
    {
        return JPATH_SITE . '/images/' . $this->getWatermarkFilename();
    }

    public function isUsedWatermark ()
    {
        if (isset($this->getWatermarkFilename) && $this->getWatermarkFilename() . trim() == '') {
            return 0;
        }
        return $this->params->get('watermark', 0);
    }

    public function getImageProportions ()
    {
        return $this->params->get('image_proportions', 'bestfit');
    }

    public function getMaxResizedImagePerRequest ()
    {
        return $this->params->get('maxResizeImagePerRequest', 10);
    }

    public function getLightboxType ()
    {
        return $this->params->get('lightbox-type');
    }

    public function isDebug ()
    {
        if ($this->params->get('debug') == "1") {
            return true;
        }
        return false;
    }
}

?>