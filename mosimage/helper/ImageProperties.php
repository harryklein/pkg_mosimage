<?php
/**
 * @version 2.0 $Id: ImageProperties.php,v 1.5 2014-03-12 21:55:54 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class ImageProperties
{

    const IMAGE_FILENAME = 0;

    const IMAGE_ALIGN = 1;

    const ALT_TEXT = 2;

    const BORDER_WIDTH = 3;

    const CAPTION = 4;

    const CAPTION_POSITION = 5;

    const CAPTION_ALIGN = 6;
    // Nicht mehr benutzt, Relikt aus J1.0.x
    const CAPTION_WIDTH = 7;
    // Nicht mehr benutzt, Relikt aus J1.0.x
    const DEFAULT_IMGAGE_ALGIN = 'left';

    private $attrib;

    private $displayThumbCaption;

    private $captionValignFromConfig;

    private $defaultThumbnailBorderWidth;

    /**
     * @param String $imagePropertiesAsLine
     * @param $config
     */
    public function __construct (&$imageProperties, &$config)
    {
        $this->attrib = $imageProperties;
        $this->displayThumbCaption = $config->isViewCaptionTextForThumbnail();
        $this->captionValignFromConfig = $config->getThumbnailCaptionAlign();
        $this->defaultThumbnailBorderWidth = $config->getThumbnailBorderWidth();
    }

    public function getRelativeFileName ()
    {
        return trim($this->attrib->source);
    }

    public function getImageAlgin ()
    {
        if (! isset($this->attrib->align) || ! $this->attrib->align) {
            return self::DEFAULT_IMGAGE_ALGIN;
        }
        return $this->attrib->align;
    }

    public function getImageAlignAsHtml ()
    {
        if ($this->displayThumbCaption) {
            return '';
        }
        $imageAlign = $this->getImageAlgin();
        switch ($imageAlign) {
            case 'left':
            case 'right':
                return ' style="float: ' . $imageAlign . ';"';
        }
        return ' style="float: none;"';
        ;
    }

    public function isViewCaptionTextForThumbnail ()
    {
        if ($this->getCaptionPosition() == 'hide') {
            return false;
        }
        return $this->displayThumbCaption;
    }

    public function getAltText ()
    {
        if (! isset($this->attrib->alt) || ! $this->attrib->alt) {
            return 'Image';
        } else {
            return htmlspecialchars($this->attrib->alt);
        }
    }

    public function getBorderWidth ()
    {
        if (isset($this->attrib->border)) {
            switch ($this->attrib->border) {
                case '0':
                    return $this->defaultThumbnailBorderWidth;
                case '1':
                case '2':
                case '3':
                    return $this->attrib->border;
                case 'hidden':
                    return 0;
            }
        }
        return $this->defaultThumbnailBorderWidth;
    }

    public function getCaptionText ()
    {
        if (! isset($this->attrib->caption) || ! $this->attrib->caption) {
            return '&nbsp;';
        }
        return $this->attrib->caption;
    }

    public function isCaptionTextEmpty ()
    {
        if (! isset($this->attrib->caption) || ! $this->attrib->caption) {
            return true;
        }
        return false;
    }

    public function getCaptionPosition ()
    {
        if (! isset($this->attrib->caption_position) || ! $this->attrib->caption_position) {
            return $this->captionValignFromConfig;
        }
        switch ($this->attrib->caption_position) {
            case 'bottom':
            case 'top':
            case 'hide':
                return $this->attrib->caption_position;
        }
        return $this->captionValignFromConfig;
    }

    public function getAbsoluteFileName ()
    {
        return JPATH_SITE . '/images/' . $this->getRelativeFileName();
    }

    public function getImageSizeAsHtml ()
    {
        if (function_exists('getimagesize')) {
            $size = @getimagesize($this->getAbsoluteFileName());
            if (is_array($size)) {
                return ' width="' . $size[0] . '" height="' . $size[1] . '"';
            }
        }
        return '';
    }
}
?>