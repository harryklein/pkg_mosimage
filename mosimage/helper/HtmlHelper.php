<?php defined('_JEXEC') or die( 'Restricted access' );

/**
 * @version 2.0 $Id: CacheFile.php,v 1.5 2014-03-04 22:53:56 harry Exp $
 * @package Joomla
 * @subpackage H2N Plugin Mosimage
 * @copyright (C) 2008 - 2009 Harry Klein - www.joomla-hklein.de
 * @license GNU/GPL, see LICENSE.php
 *
 * H2N Plugin Mosimage is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * H2N Plugin Mosimage is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class HtmlHelper{
	
	public static function createHtmlForNoneIntroImage($value){
		$result = '<span style="display:none;">'.JTEXT::_('PICTURE_INTRO').'['.$value.']</span>';
		return $result;
	}
	
	public static function createHtmlForNoneImage($value){
		$result = '<span style="display:none;">'.JTEXT::_('PICTURE').'['.$value.']</span>';
		return $result;
	}
	
	
	public static function createImageAndBuildHtmlFromArrayObject($imgagePropertiesAsArrayObject, $config, $screenres ) {
		$html = '<p>';
		if (!is_array($imgagePropertiesAsArrayObject)){
			$value = array();
			$value[] = $imgagePropertiesAsArrayObject;
		} else {
			$value = $imgagePropertiesAsArrayObject;
		}
		foreach ($value as $v){
			$html .= self::createImageAndBuildHtml($v, $config, $screenres);
		}
		$html .= '</p>';
		return $html;
	}
	
	public static function createImageAndBuildHtml($imgageProperties, $config, $screenres, $param = '' ) {
		if ( $imgageProperties ) {
			$imgProperties = new ImageProperties($imgageProperties,$config);
	
			$thumb_width = $config->getThumbnailMaxWidth();
			$thumb_heigth = $config->getThumbnailMaxHeight();
			$full_width = $config->getFullsizeMaxWidth();
			$full_height = $config->getFullsizeMaxHeight();
	
			$thumbSizeProperties = new ImageDisplayProperties($thumb_width,$thumb_heigth, $imgProperties->getAbsoluteFileName());
			$imageSizeProperties = new ImageDisplayProperties($full_width,$full_height, $imgProperties->getAbsoluteFileName(), $screenres);
			$thumbnailCreator = new ThumbnailCreator($config);
			$mosthumbProperties = $thumbnailCreator->createThumbnailImage($thumbSizeProperties);
			$mosimageProperties = $thumbnailCreator->createResizedImage  ($imageSizeProperties);
	
			if (!$mosthumbProperties || !$mosimageProperties){
				return '';
			}
			$lightboxHelper = new LightboxHelper($config->getLightboxType());
			$images = self::buildHtmlForImage($lightboxHelper, $mosthumbProperties, $mosimageProperties, $imgProperties, $config, $param);
			return $images;
		} else {
			return '';
		}
	}
	
	
	
	public static function buildHtmlForImage(&$lightboxHelper, CacheFile &$mosthumbProperties, CacheFile &$mosimageProperties, &$imgProperties, &$config, $param){
		$lightboxRel   = $lightboxHelper->getRel();
		$thumbSize = ' width="'. $mosthumbProperties->displayWidth() .'" height="'. $mosthumbProperties->displayHeight() .'"';
	
		$image = '<a href="'. $mosimageProperties->getCacheFileUrl() .'"';
		$image.= ' rel="'.$lightboxRel.'"';
		$image.= ' class="'. $lightboxHelper->getCssClassForImageLink() .'"';
		$image.= ' title="' . ($config->isViewCaptionTextForFullsize() ? $imgProperties->getCaptionText():'') .'"';
		$image.= '>';
		$image.= '<img class="mosimgage-inner" src="'. $mosthumbProperties->getCacheFileUrl()  .'"';
		$image.= $thumbSize;
		$image.= $imgProperties->getImageAlignAsHtml();
		$image.=' alt="'. $imgProperties->getAltText() .'"';
		$image.=' title="'. $imgProperties->getCaptionText() .'"';
		$image.=' border="'. $imgProperties->getBorderWidth() .'"';
		$image.=' /></a>';
	
		$caption = '';
	
		$widthInner = $mosthumbProperties->displayWidth();
		$widthOuter = $mosthumbProperties->displayWidth() ;//+ 26 - 12; // -12 img.mosimgage-inner margin- = 0px
		if ($imgProperties->isCaptionTextEmpty()){
			$cssDisplay =" visibility: hidden;";
		} else {
			$cssDisplay = '';
		}
		$cssWidh='width: '. $widthInner . 'px;';
	
		$caption_valign = $imgProperties->getCaptionPosition();
	
		if ($imgProperties->getImageAlgin() == ""){
			$cssClassOuter 		= 'mosimgage-outer-none';
		} else {
			$cssClassOuter 		= 'mosimgage-outer-'. $imgProperties->getImageAlgin();
		}
		$img = '<span class="' . $cssClassOuter . '" style="width: '. $widthOuter . 'px;border-width:'.$imgProperties->getBorderWidth().'px;">';
	
		if ( $imgProperties->isViewCaptionTextForThumbnail()) {
			$caption = '<span class="mosimgage-inner-'.$caption_valign.'" style="'.$cssWidh . $cssDisplay.'" ';
			$caption .= '>';
			$caption .= $imgProperties->getCaptionText();
			$caption .= '</span>';
	
			if ($caption_valign == 'top' && $caption){
				$img .= $caption;
			}
			$img .= $image;
			if ($caption_valign == 'bottom' && $caption){
				$img .= $caption;
			}
		} else {
			$img .= $image;
		}
		$img .='</span>';
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
	public static function getHtmlForFloat($param){
		switch($param){
			case 'clear':
			case 'clear=all':
				return '<br clear="all">';
				//return '<div style="clear:both;"/>';
			case 'clear=left':
				return '<br clear="left">';
			case 'clear=right':
				return '<br clear="right">';
			default:
				return '';
		}
	
	
	}
	
	
}