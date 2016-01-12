<?php
/**
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class HtmlHelper
{

    public static function createHtmlForNoneIntroImage ($value)
    {
        $result = '<span style="display:none;">' . JTEXT::_('PICTURE_INTRO') . '[' . $value . ']</span>';
        return $result;
    }

    public static function createHtmlForNoneImage ($value)
    {
        $result = '<span style="display:none;">' . JTEXT::_('PICTURE') . '[' . $value . ']</span>';
        return $result;
    }

    public static function createImageAndBuildHtmlFromArrayObject ($imgagePropertiesAsArrayObject, $config, $screenres)
    {
        $html = '<p>';
        if (! is_array($imgagePropertiesAsArrayObject)) {
            $value = array();
            $value[] = $imgagePropertiesAsArrayObject;
        } else {
            $value = $imgagePropertiesAsArrayObject;
        }
        foreach ($value as $v) {
            $html .= self::createImageAndBuildHtml($v, $config, $screenres);
        }
        $html .= '</p>';
        return $html;
    }

    public static function createImageAndBuildHtml ($imgageProperties, $config, $screenres, $param = '')
    {
        if ($imgageProperties) {
            $imgProperties = new ImageProperties($imgageProperties, $config);
            
            $thumb_width = $config->getThumbnailMaxWidth();
            $thumb_heigth = $config->getThumbnailMaxHeight();
            $full_width = $config->getFullsizeMaxWidth();
            $full_height = $config->getFullsizeMaxHeight();
            
            $thumbSizeProperties = new ImageDisplayProperties($thumb_width, $thumb_heigth, $imgProperties->getAbsoluteFileName());
            $imageSizeProperties = new ImageDisplayProperties($full_width, $full_height, $imgProperties->getAbsoluteFileName(), $screenres);
            $thumbnailCreator = new ThumbnailCreator($config);
            $mosthumbProperties = $thumbnailCreator->createThumbnailImage($thumbSizeProperties);
            $mosimageProperties = $thumbnailCreator->createResizedImage($imageSizeProperties);
            
            if (! $mosthumbProperties || ! $mosimageProperties) {
                return '';
            }
            $lightbox = LightboxHelper::getInstance($config->getLightboxType());
            $images = self::buildHtmlForImage($lightbox, $mosthumbProperties, $mosimageProperties, $imgProperties, $config, $param);
            return $images;
        } else {
            return '';
        }
    }

    public static function buildHtmlForImage (&$lightbox, CacheFile &$mosthumbProperties, CacheFile &$mosimageProperties, ImageProperties &$imgProperties, &$config, 
            $param)
    {
    	$accessLevel = $imgProperties->getAccessLevel();
    	
    	$user = JFactory::getUser();
    	if (($user->guest) && ($accessLevel > 1) ){
    		$img = '';
    	} else {
	        $lightboxRel = $lightbox->getRel();
	        $thumbSize = ' width="' . $mosthumbProperties->displayWidth() . '" height="' . $mosthumbProperties->displayHeight() . '"';
	        
	        $image = '<a href="' . $mosimageProperties->getCacheFileUrl() . '"';
	        $image .= ' rel="' . $lightboxRel . '"';
	        $image .= ' class="' . $lightbox->getCssClassForImageLink() . '"';
	        $image .= ' title="' . ($config->isViewCaptionTextForFullsize() ? $imgProperties->getCaptionText() : '') . '"';
	        $image .= '>';
	        $image .= '<img class="mosimgage-inner" src="' . $mosthumbProperties->getCacheFileUrl() . '"';
	        $image .= $thumbSize;
	        $image .= $imgProperties->getImageAlignAsHtml();
	        $image .= ' alt="' . $imgProperties->getAltText() . '"';
	        $image .= ' title="' . $imgProperties->getCaptionText() . '"';
	        $image .= ' border="' . $imgProperties->getBorderWidth() . '"';
	        $image .= ' /></a>';
	        
	        $caption = '';
	        
	        $widthInner = $mosthumbProperties->displayWidth();
	        $widthOuter = $mosthumbProperties->displayWidth(); // + 26 - 12; // -12 img.mosimgage-inner margin- = 0px
	        if ($imgProperties->isCaptionTextEmpty()) {
	            $cssDisplay = " visibility: hidden;";
	        } else {
	            $cssDisplay = '';
	        }
	        
	 
	        $cssClassOuter = $imgProperties->getOuterCssClass();     
	        $img = '<span class="' . $cssClassOuter . '" style="width: ' . $widthOuter . 'px;border-width:' . $imgProperties->getBorderWidth() . 'px;">';
	        
	        if ($imgProperties->isViewCaptionTextForThumbnail()) {
	            $caption_valign = $imgProperties->getCaptionPosition();
	            $cssWidh = 'width: ' . $widthInner . 'px;';
	            $caption = '<span class="mosimgage-inner-' . $caption_valign . '" style="' . $cssWidh . $cssDisplay . '" ';
	            $caption .= '>';
	            $caption .= $imgProperties->getCaptionText();
	            $caption .= '</span>';
	            
	            if ($caption_valign == 'top' && $caption) {
	                $img .= $caption;
	            }
	            $img .= $image;
	            if ($caption_valign == 'bottom' && $caption) {
	                $img .= $caption;
	            }
	        } else {
	            $img .= $image;
	        }
	        $img .= '</span>';
    	}
        $addHtml = self::getHtmlForFloat($param);
        return $addHtml . $img;
    }

    /**
     * 
     * Liefert das HTML zur Steuerung des Flotings.
     * <ul>
     * <li>clear oder clear=all : Unterbricht das Floating komplett</li> 
     * <li>clear=left           : Unterbricht das Floating links</li>
     * <li>clear=right          : Unterbricht das Floating rechts</li>
     * </ul>
     * 
     * Alle anderen Paramter beeinflussen das Floating nicht.
     * 
     * @param Sring $param 
     * @return HTML zur Steuerung des Floatings
     */
    private static function getHtmlForFloat ($param)
    {
        switch ($param) {
            case 'clear':
            case 'clear=all':
                return '<div style="clear:both;"></div>';
            case 'clear=left':
                return '<div style="clear:left;"></div>';
            case 'clear=right':
                return '<div style="clear:right;"></div>';
            default:
                return '';
        }
    }
}