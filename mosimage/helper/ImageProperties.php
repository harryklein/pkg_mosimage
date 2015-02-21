<?php
/**
 * @version 2.0 $Id: ImageProperties.php,v 1.6 2015/02/06 00:06:47 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class ImageProperties
{
    const DEFAULT_IMGAGE_ALGIN = 'left';

    private $attrib;
    private $config;


    /**
     * @param stdClass $imagePropertiesAsStdClass
     * @param $config
     */
    public function __construct (&$imagePropertiesAsStdClass, PluginConfiguration &$config)
    {
        $this->attrib = $imagePropertiesAsStdClass;
        $this->config = $config;
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
        if ($this->config->isViewCaptionTextForThumbnail()) {
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
        return $this->config->isViewCaptionTextForThumbnail();
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
                    return $this->config->getThumbnailBorderWidth();
                case '1':
                case '2':
                case '3':
                    return $this->attrib->border;
                case 'hidden':
                    return 0;
            }
        }
        return $this->config->getThumbnailBorderWidth();
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
            return $this->config->getThumbnailCaptionAlign();
        }
        switch ($this->attrib->caption_position) {
            case 'bottom':
            case 'top':
            case 'hide':
                return $this->attrib->caption_position;
        }
        return $this->config->getThumbnailCaptionAlign();
    }
    
    public function getAccessLevel(){
    	if (isset($this->attrib->accesslevel)) {
    		switch ($this->attrib->accesslevel){
    			case 1:
    			case 2:
    			case 3:
    				return $this->attrib->accesslevel;
    				break;
    		}
    	}
    	return 1;
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