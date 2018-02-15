<?php

defined('_JEXEC') or die('Restricted access');

/**
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
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
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/PluginConfiguration.php';
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/ImageDisplayProperties.php';
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/CacheFile.php';
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/MosimageDirProperties.php';
require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/helper/HtmlHelper.php';

class plgContentMosimage extends JPlugin {

	const REGEX     = '/{mosimage\s*.*?}/i';
	const REGEX_DIR = '/{mosimage\s*folder=.*?}/i';
	const REGEX_RANDOM = '/{mosimage\s*random}/i';
	
	const HTML_FOR_STOP_FLOATING = '<div style="clear:both">&nbsp</div>';
	 
	public function __construct($subject, $config){
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function onContentBeforeDisplay($context, &$row, $params, $page=0 ) {
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
			
		    // com_tags
		    // introtext: nicht vorhanden
		    // fulltext: nicht vorhannden
		    // text: Intro oder Hauptext, wenn Intro nicht vorhanden
		    
		    
		    if ($context =='com_content.featured' || $context == 'com_content.category' || $context == 'com_content.article' || $context == 'com_tags.tag'){
		        // nur bei unterstützen contexten später etwas tun
		    } else {
		        return;
		    }
		    
			if ($this->isNothingToDo($row)){
				return;
			}
			
			$introCount = 0;
			
			if ($context == 'com_tags.tag') {
			    $registry = new Registry;
			    $registry->loadString($params);
			    $params = $registry;
			    $row->introtext = $row->text;
			    
			} else {
			     $registry = new JRegistry;
			     $registry->loadString($row->attribs);
			     $params->merge($registry);
			}
			
			if ($context =='com_content.featured' || $context == 'com_content.category' || $context == 'com_content.article' || $context == 'com_tags.tag'){
			    $isReplacePlaceholderRandom = $this->replacePlaceHolder($row, $params, self::REGEX_RANDOM, 'processImagesRandom');
			    $isReplacePlaceholderDir = $this->replacePlaceHolder($row, $params, self::REGEX_DIR, 'processImagesDir');
			    $isReplacePlaceholder = $this->replacePlaceHolder($row, $params, self::REGEX, 'processImages');
			    $this->replaceClearFlotingPlaceHolder($row);
			}
			if ($isReplacePlaceholderDir || $isReplacePlaceholder || $isReplacePlaceholderRandom){
			    if (!empty($row->text)){
				    $row->text = $row->text . self::HTML_FOR_STOP_FLOATING;
			    }
			}
		} catch (Exception $e){
			return;
		}
		return;
	}
	
	private function replaceClearFlotingPlaceHolder(&$row){
	    $regex = '/{clear-floting}/i';
	    $row->introtext = preg_replace($regex, self::HTML_FOR_STOP_FLOATING, $row->introtext);
	    if (!empty($row->text)){
	        $row->text =  preg_replace($regex, self::HTML_FOR_STOP_FLOATING, $row->text);
	    }
	}
	
	/** 
	 * 
	 * @return liefert true, wenn Platzhalter ersetzt worden sind, sonst false
	 */
	private function replacePlaceHolder(&$row, &$params, $regex, $processImagesFunction){
	    // text enthält auch das Intro, wenn es angezeigt werden soll.
	    // ohne Anzeige des Intros müssen die Bilder aus Intro übersprungen werden
	    // Intro: hier einfach alles ersetzten, auch wenn
	    
	    preg_match_all( $regex, $row->introtext, $matchesInIntro );
	    $introCount = count($matchesInIntro[0]);
	    if (empty($row->text)){
	        $text='';
	    } else {
	        $text=$row->text;
	    }
	    preg_match_all( $regex, $text, $matchesInText );
	    $count = count( $matchesInText[0] );
	    
	    if ($count || $introCount) {
	        $config = new PluginConfiguration($this->params);
	        $this->addMosimageStyleSheet();
	        $this->addLightBoxStyleSheetAndScript($config);
	        $screenres = $this->getScreenSizeFromCookie();
	        $this->addScreenSizeCookie($config);
	        $images = $this->$processImagesFunction( $row, $config, $matchesInIntro[0], $matchesInText[0], $screenres, $params->get('show_intro'));
	    
	        $pattern=array();
	        for ($i=0; $i<$count+$introCount;$i++){
	            $pattern[]=$regex;
	        }
	        $row->introtext = preg_replace( $pattern, $images->introImages, $row->introtext,1 );
	        if (!empty($row->text)){
	            $row->text = preg_replace( $pattern, $images->images, $row->text,1  );
	        }
	        return true;
	    }
	    return false;
	}
	
	
	private function isTextContainIntroText(&$row){
	    if (empty($row->text) || empty($row->introtext)) {
	        $textContainIntro =  false;
	    } else {
    		if (strpos($row->text,$row->introtext) === 0) {
    			// im text ist der Intro nicht enthalten, daher beginnt die Anzeige der Bilder wieder bei 0
    			$textContainIntro =  true;
    		} else {
    			$textContainIntro =  false;
    		}
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
		$lightbox = LightboxHelper::getInstance($config->getLightboxType());
		$lightbox->addScriptAndCssToDocument();
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

	public function getMosimageParameter($mosimage){
		$param = preg_replace('/{mosimage\s*(.*)\s*}/','$1',$mosimage);
		return $param;
	}

	private function processImagesRandom( &$row, PluginConfiguration $config, &$matchesInIntro, &$matchesInText, $screenres, $showIntro = false ){
	    $images = array();
	    $introImages = array();
	   
	    jimport('joomla.application.component.model');
	    JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_mosimage/models');
	    JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_mosimage/tables');
	    $model = JModelLegacy::getInstance('Options', 'MosimageModel');
	    
	    JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_content/models');
	    $modelContent = JModelLegacy::getInstance('Article', 'ContentModel');
	    
	    $article = $modelContent->getItem($row->id);
	    preg_match_all( self::REGEX, $article->introtext, $mIntro );
	    preg_match_all( self::REGEX, $article->fulltext, $mFull );
	    $full = count($mFull[0]);
	    $intro = count($mIntro[0]);
	    
	    
	    $rowImage = $model->getItem($row->id);
	    $jsonObjectsList = $this->convertJsonToObjectArray($rowImage->imageslist);
	    
	    $rowImageCount = min( count($jsonObjectsList), max($intro, $full));
	    if ($rowImageCount == 0){
	        $intoImages[] = HtmlHelper::createHtmlForNoneIntroImage(0);
	        $images[] = HtmlHelper::createHtmlForNoneIntroImage(0);
	    } else {
	       $randIndex = rand(0,$rowImageCount-1);
	       $imageData = $jsonObjectsList[$randIndex];
	       $imageData->align = '';
	       $randIndex = rand(0,$rowImageCount);
	       $introImages[] = HtmlHelper::createImageAndBuildHtml($imageData, $config, $screenres);
	       $images[] = HtmlHelper::createImageAndBuildHtml($imageData, $config, $screenres);
	    }
	    $result = new stdClass();
	    $result->introImages = $introImages;
	    $result->images = $images;
	    return $result;
	}
	
	
	private function processImagesDir( &$row, PluginConfiguration $config, &$matchesInIntro, &$matchesInText, $screenres, $showIntro = false ){
		$images = array();
		$introImages = array();

		for ($i = 0; $i < count($matchesInIntro); $i++){
			try {
				$properies = MosimageDirProperties::parse($matchesInIntro[$i]);
				$imgagePropertiesAsArrayObject  = $properies->getImgagePropertiesAsArrayObject();
				if ($imgagePropertiesAsArrayObject){
					$introImages[] = HtmlHelper::createImageAndBuildHtmlFromArrayObject($imgagePropertiesAsArrayObject, $config, $screenres);
				} else {
					$intoImages[] = HtmlHelper::createHtmlForNoneIntroImage($i);
				}
			} catch (Exception $e){
				$introImages[] = '<p>' . $e->getMessage() . '</p>';
			}
		}
		for ($i = 0; $i < count($matchesInText); $i++){
			try {
				$properies = MosimageDirProperties::parse($matchesInText[$i]);
				$properies->align = 'left';
				$imgagePropertiesAsArrayObject  = $properies->getImgagePropertiesAsArrayObject();
				if ($imgagePropertiesAsArrayObject){
					$images[] = HtmlHelper::createImageAndBuildHtmlFromArrayObject($imgagePropertiesAsArrayObject, $config, $screenres);
				} else {
					$images[] = HtmlHelper::createHtmlForNoneImage($i);
				}
			} catch (Exception $e){
				$images[] = '<p>' . $e->getMessage() . '</p>';
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
	
	public function convertJsonToObjectArray($json){
		$jsonObjects = json_decode($json, false);
		 
		if (is_null($jsonObjects )) {
			$emptyArray = array();
			return $emptyArray;
		}
		
		if (is_object($jsonObjects)){
			$jsonItemArray = array();
			foreach ($jsonObjects as $jsonItem){
				$jsonItemArray[] = $jsonItem;
			}
			return $jsonItemArray;
		}
		return $jsonObjects;
	}
	

	private function processImages ( &$row, PluginConfiguration $config, &$matchesInIntro, &$matchesInText, $screenres, $showIntro ) {
		$introCount=count($matchesInIntro);
		$count = count($matchesInText);

		
		jimport('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_mosimage/models');
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_mosimage/tables');
		$model = JModelLegacy::getInstance('Options', 'MosimageModel');

		$rowImage = $model->getItem($row->id);
		$jsonObjectsList = $this->convertJsonToObjectArray($rowImage->imageslist);	
		
		$rowImageCount = count($jsonObjectsList);
		$introImages = array();
		for ( $i = 0, $m = 0; $i < $introCount; $i++, $m++ ) {
			$param = $this->getMosimageParameter($matchesInIntro[$m]);
			if ($i < $rowImageCount){
				$introImages[] = HtmlHelper::createImageAndBuildHtml($jsonObjectsList[$i], $config, $screenres, $param);
			} else {
				$intoImages[] = HtmlHelper::createHtmlForNoneIntroImage($i);
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
			$param = $this->getMosimageParameter($matchesInText[$m]);
			if ($i < $rowImageCount && ($rowImageCount > 0)){
				$images[] = HtmlHelper::createImageAndBuildHtml($jsonObjectsList[$i], $config, $screenres, $param);
			} else {
				$images[] = HtmlHelper::createHtmlForNoneImage($i);
			}

		}
		$result = new stdClass();
		$result->introImages = $introImages;
		$result->images = $images;
		return $result;
	}
}

?>