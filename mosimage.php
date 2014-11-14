<?php

defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: mosimage.php,v 1.9 2014-03-11 23:38:31 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Plugin
 * @copyright (C) 2010 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 *
 * H2N Mosimage Plugin is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * H2N Mosimage Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/LightboxHelper.php';
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/ImageProperties.php';
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/ThumbnailCreator.php';
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/MosimageConfiguration.php';
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/ImageDisplayProperties.php';
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/CacheFile.php';
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/MosimageFromFolder.php';

class plgContentMosimage extends JPlugin {

	const REGEX     = '/{mosimage\s*.*?}/i';
	const REGEX_DIR = '/{mosimage\s*folder=.*?}/i';
	
	const HTML_FOR_STOP_FLOATING = '<div style="clear:both">&nbsp</div>';
	 
	public function __construct($subject, $config){
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function onContentBeforeDisplay($context, &$row, &$params, $page=0 ) {
		try {
			// Bemerkung zu $row
			// Article
			// introtext: Intro
			// fulltext : Haupttext (ohne Intro)
			// text     : Text, der angezeigt wird (intro + Hauptext bzw. nur Haupttext)

			// Frontpage:
			// introtext: Intro
			// fulltext : nicht vorhanden
			// text     : nicht vorhanden
			if ($this->isNothingToDo($row)){
				return;
			}
			$introCount = 0;

			$registry = new JRegistry;
			$registry->loadString($row->attribs);
			$params->merge($registry);
			
			$replaceMosimagePlaceholder = false;
			
			if ($context =='com_content.featured' || $context == 'com_content.category' || $context == 'com_content.article'){
				// text enthält auch das Intro, wenn es angezeigt werden soll.
				// ohne Anzeige des Intros müssen die Bilder aus Intro übersprungen werden
				// Intro: hier einfach alles ersetzten, auch wenn

				preg_match_all( self::REGEX_DIR, $row->introtext, $matchesInIntro );
				$introCount = count($matchesInIntro[0]);
				preg_match_all( self::REGEX_DIR, $row->text, $matchesInText );
				$count = count( $matchesInText[0] );
				if ($count || $introCount) {
					$replaceMosimagePlaceholder = true;
					$config = new MosimageConfiguration($this->params);
					$this->addMosimageStyleSheet();
					$this->addLightBoxStyleSheetAndScript($config);
					$screenres = $this->getScreenSizeFromCookie();
					$this->addScreenSizeCookie($config);	
					$images = $this->processImagesDir( $row, $config, $matchesInIntro[0], $matchesInText[0], $screenres);

					$pattern=array();
					for ($i=0; $i<$count+$introCount;$i++){
						$pattern[]=self::REGEX_DIR;
					}
					$row->introtext = preg_replace( $pattern, $images->introImages, $row->introtext,1 );
					$row->text = preg_replace( $pattern, $images->images, $row->text,1  );
				}
			}

			if ($context =='com_content.featured' || $context == 'com_content.category' || $context == 'com_content.article'){
				// text enthält auch das Intro, wenn es angezeigt werden soll.
				// ohne Anzeige des Intros müssen die Bilder aus Intro übersprungen werden
				// Intro: hier einfach alles ersetzten, auch wenn

				preg_match_all( self::REGEX, $row->introtext, $matchesInIntro );
				$introCount = count($matchesInIntro[0]);
				preg_match_all( self::REGEX, $row->text, $matchesInText );
				$count = count( $matchesInText[0] );

				if ($count || $introCount) {
					$replaceMosimagePlaceholder = true;
					$config = new MosimageConfiguration($this->params);
					$this->addMosimageStyleSheet();
					$this->addLightBoxStyleSheetAndScript($config);
					$screenres = $this->getScreenSizeFromCookie();
					$this->addScreenSizeCookie($config);
					$images = $this->processImages( $row, $config, $matchesInIntro[0], $matchesInText[0], $screenres, $params->get('show_intro'));

					$pattern=array();
					for ($i=0; $i<$count+$introCount;$i++){
						$pattern[]=self::REGEX;
					}
					// $row->introtext = preg_replace( $pattern, $images->getIntroImages(), $row->introtext,1 );
					// $row->text = preg_replace( $pattern, $images->getImages(), $row->text,1  );
					$row->introtext = preg_replace( $pattern, $images->introImages, $row->introtext,1 );
					$row->text = preg_replace( $pattern, $images->images, $row->text,1  );
				}
			}
			if ($replaceMosimagePlaceholder){
				$row->text = $row->text . self::HTML_FOR_STOP_FLOATING;
			}
		} catch (Exception $e){
			return;
		}
		return;
	}
	
	private function isTextContainIntroText(&$row){
		if (strpos($row->text,$row->introtext) === 0) {
			// im text ist der Intro nicht enthalten, daher beginnt die Anzeige der Bilder wieder bei 0
			$textContainIntro =  true;
		} else {
			$textContainIntro =  false;
		}
		if (empty ($row->fulltext)){
			$textContainIntro =  false;
		}
		return $textContainIntro;
	}

	private function addMosimageStyleSheet(){
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::base().'plugins/content/mosimage/mosimage/css/mosimage.css');
	}

	private function addLightBoxStyleSheetAndScript($config){
		$lightboxHelper = new LightboxHelper($config->getLightboxType());
		$lightboxHelper->addScriptAndCssToDocument();
	}

	private function getScreenSizeFromCookie(){
		if(isset($_COOKIE['botmtscreenres'])){
			return explode('x', $_COOKIE['botmtscreenres']);
		}
		return '';
	}

	private function addScreenSizeCookie($config){
		if(! isset($_COOKIE['botmtscreenres'])){
			if(strpos($config->getFullsizeMaxWidth(), '%') || strpos($config->getFullsizeMaxHeight(), '%')) {
				$document=& JFactory::getDocument();
				$document->addScriptDeclaration('document.cookie= "botmtscreenres=" + screen.width+"x"+screen.height;');
			}
		}
	}

	private function isNothingToDo(&$row){
		if (isset($row->text)){
			$foundMosimageInText = (strpos( $row->text, 'mosimage' ));
		} else {
			$foundMosimageInText = false;
		}
		if (isset($row->introtext)){
			$foundMosimageInIntrotext = (strpos( $row->introtext, 'mosimage' ));
		} else {
			$foundMosimageInIntrotext = false;
		}

		if (( $foundMosimageInText === false ) && ($foundMosimageInIntrotext === false)) {
			return true;
		}
		return false;
	}
	
	private function createImageAndBuildHtmlFromArrayObject($imgagePropertiesAsArrayObject, $config, $screenres, $addHtml = '' ) {
		$html = '<p>';
		if (!is_array($imgagePropertiesAsArrayObject)){
			$value = array();
			$value[] = $imgagePropertiesAsArrayObject;
		} else {
			$value = $imgagePropertiesAsArrayObject;
		}
		foreach ($value as $v){
			$html .= $this->createImageAndBuildHtml($v, $config, $screenres);
		}
		$html .= '</p>';
		return $html;	
	}
	
	private function createImageAndBuildHtml($imgageProperties, $config, $screenres, $addHtml = '' ) {
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
			$images = $this->buildHtmlForImage($lightboxHelper, $mosthumbProperties, $mosimageProperties, $imgProperties, $config, $addHtml);
			return $images;
		} else {
			return '';
		}
	}

	private function getMosimageParameter($mosimage){
		$param = preg_replace('/{mosimage\s*(.*)\s*}/','$1',$mosimage);
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

	private function processImagesDir( &$row, MosimageConfiguration $config, &$matchesInIntro, &$matchesInText, $screenres ){
		$images = array();
		$introImages = array();
		$addHtml = '';
		for ($i = 0; $i < count($matchesInIntro); $i++){
			$imgagePropertiesAsArrayObject  = (new MosimageFromFolder($matchesInIntro[$i]))->getImgagePropertiesAsArrayObject();
			if ($imgagePropertiesAsArrayObject){
				$introImages[] = $this->createImageAndBuildHtmlFromArrayObject($imgagePropertiesAsArrayObject, $config, $screenres, $addHtml);
			} else {
				$intoImages[] = '<span style="display:none;">'.JTEXT::_("PICTURE_INTRO").'['.$i.']</span>';
			}
		}
		for ($i = 0; $i < count($matchesInText); $i++){
			$imgagePropertiesAsArrayObject  = (new MosimageFromFolder($matchesInText[$i]))->getImgagePropertiesAsArrayObject();
			if ($imgagePropertiesAsArrayObject){
				$images[] = $this->createImageAndBuildHtmlFromArrayObject($imgagePropertiesAsArrayObject, $config, $screenres, $addHtml);
			} else {
				$images[] = '<span style="display:none;">'.JTEXT::_("PICTURE_INTRO").'['.$i.']</span>';
			}
		}
		 
		$result = new stdClass();
		$result->introImages = $introImages;
		$result->images = $images;
		return $result;
	}
	
	/**
	 * 
<pre>
	{"source":"stories\/fruit\/cherry.jpg","align":"left","alt":"","border":"0","caption":"","caption_position":"","caption_align":"","width":""},
	{"source":"stories\/fruit\/pears.jpg","align":"left","alt":"","border":"0","caption":"","caption_position":"","caption_align":"","width":""}
</pre>
	*/
	private function convertOldImagelistFormatToJson($images){
		$imageslist = preg_replace("#(\n\r)+#",'',$images);
		$imageslist = preg_replace("#(\r)*#",'',$imageslist);
		$imageslist = explode( "\n",$imageslist);
	
		$jsonList = array();
		foreach ($imageslist as $imageAsString){
			$properties = array_pad(explode('|', $imageAsString),8,'');
			$image = new stdClass();
			$image->source = $properties[0];
			$image->align= $properties[1];
			$image->alt = $properties[2];
			$image->border = $properties[3];
			$image->caption = $properties[4];
			$image->caption_position = $properties[5];
			$image->caption_align =  $properties[6];
			$image->width = $properties[7];
			$jsonList[] = $image;
		}
		$json = json_encode($jsonList, JSON_FORCE_OBJECT);
		return $json;
	}
	
	/**
	 * Konveriert das Json-Format bzw. das alte '|'-Format in ein Array mit PHP-Objekten, die die 
	 * jeweiligen Properties der Bilder enthält.
<pre>
		(
		    [0] => stdClass Object
		        (
		            [source] => stories/fruit/cherry.jpg
		            [align] => left
		            [alt] => 
		            [border] => 0
		            [caption] => 
		            [caption_position] => 
		            [caption_align] => 
		            [width] => 
		        )
		
		    [1] => stdClass Object
		        (
		            [source] => stories/fruit/pears.jpg
		            [align] => left
		            [alt] => 
		            [border] => 0
		            [caption] => 
		            [caption_position] => 
		            [caption_align] => 
		            [width] => 
		        )
		
		)
</pre> 
	 * @param $images Properies im Json bzw. '|'-Format
	 * @return array mit PHP-Objekten, die die jeweiligen Properties der Bilder enthält. 
	 */
	private function convertImagePropertiesToObjectArray($images){
		if (is_null(json_decode($images))){
			$json = $this->convertOldImagelistFormatToJson($images);
		} else {
			$json = $images;
		}
		$jsonObjects = json_decode($json,false);

		if (is_object($jsonObjects)){
			$jsonItemArray = array();
			foreach ($jsonObjects as $jsonItem){
				$jsonItemArray[] = $jsonItem;
			}
			return $jsonItemArray;
		}
		return $jsonObjects;
	}

	private function processImages ( &$row, MosimageConfiguration $config, &$matchesInIntro, &$matchesInText, $screenres, $showIntro ) {
		$introCount=count($matchesInIntro);
		$count = count($matchesInText);

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_mosimage/tables');
		$rowImage = JTable::getInstance('mosimage');
		$rowImage->load($row->id);

		$jsonObjectsList = $this->convertImagePropertiesToObjectArray($rowImage->images);	
		
		$rowImageCount = count($jsonObjectsList);
		$introImages = array();
		for ( $i = 0, $m = 0; $i < $introCount; $i++, $m++ ) {
			$addHtml = $this->getMosimageParameter($matchesInIntro[$m]);
			if ($i < $rowImageCount){
				$introImages[] = $this->createImageAndBuildHtml($jsonObjectsList[$i], $config, $screenres, $addHtml);
			} else {
				$intoImages[] = '<span style="display:none;">'.JTEXT::_("PICTURE_INTRO").'['.$i.']</span>';
			}
		}

		if ($this->isTextContainIntroText($row)== true){
			$start = $introCount;
		} else {
			$start = 0;
		}
		
		if (empty ($row->fulltext) || $showIntro == true){
			$start = 0;
		}

		$images = array();
		for ( $i = $start, $m=0 ; $m < $count; $i++, $m++ ) {
			$addHtml = $this->getMosimageParameter($matchesInText[$m]);
			if ($i < $rowImageCount && ($rowImageCount > 0)){
				$images[] = $this->createImageAndBuildHtml($jsonObjectsList[$i], $config, $screenres, $addHtml);
			} else {
				$images[] = '<span style="display:none;">'.JTEXT::_("PICTURE").'['.$i.']</span>';
			}

		}
		$result = new stdClass();
		$result->introImages = $introImages;
		$result->images = $images;
		return $result;
	}


	private function buildHtmlForImage(&$lightboxHelper, CacheFile &$mosthumbProperties, CacheFile &$mosimageProperties, &$imgProperties, &$config, $addHtml){
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
		 
		return $addHtml . $img;
	}
}

?>