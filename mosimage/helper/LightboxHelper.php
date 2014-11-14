<?php defined('_JEXEC') or die( 'Restricted access' );

/**
 * @version 2.0 $Id: LightboxHelper.php,v 1.4 2014-03-04 22:53:20 harry Exp $
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

require_once JPATH_ROOT.'/plugins/content/mosimage/mosimage/fields/lightboxlist.php';

class LightboxHelper {
	
	public $lightboxType;
	
	/**
	 * Default ist JFormFieldLightboxList::SLIMBOX_20.
	 */
	public function __construct($lightboxType){
		switch($lightboxType){
			case JFormFieldLightboxList::LYTEBOX:
			case JFormFieldLightboxList::LYTEBOX5:
			// case JFormFieldLightboxList::SHADOWBOX:
			case JFormFieldLightboxList::LIGHTBOX2:
			case JFormFieldLightboxList::FANCYBOX:
			//case JFormFieldLightboxList::SLIMBOX_18:
			case JFormFieldLightboxList::SLIMBOX_20:
				$this->lightboxType = $lightboxType;
				break;		
			default:
				$this->lightboxType = JFormFieldLightboxList::SLIMBOX_20;
		}
	}
	
	
	public function getRel(){
		switch($this->lightboxType){
			case JFormFieldLightboxList::LYTEBOX5:
				return '';
			case JFormFieldLightboxList::LYTEBOX:
				return 'lytebox[vacation]';
			// case JFormFieldLightboxList::SHADOWBOX:
			//	return 'shadowbox[Vacation]';				
			case JFormFieldLightboxList::LIGHTBOX2:
				return 'lightbox[roadtrip]';
			case JFormFieldLightboxList::FANCYBOX:
				return 'fancybox-group';
			case JFormFieldLightboxList::SLIMBOX_20:
			default:	
				return 'lightbox-group';
		}
	}
	
	public function getCssClassForImageLink(){
		switch($this->lightboxType){
			case JFormFieldLightboxList::FANCYBOX:
				return '';
			case JFormFieldLightboxList::LYTEBOX5:
				return 'lytebox" rev="group:name navType:2 animateOverlay:false doAnimations:false';
				
			default:
				return '';
		}
	}
	
	public function addScriptAndCssToDocument(){
		$document = JFactory::getDocument();
		
		switch($this->lightboxType){
				case JFormFieldLightboxList::LIGHTBOX2:
					$lang=substr($document->getLanguage(),0,2);
					$baseUrl = JURI::base().'plugins/content/mosimage/mosimage/lightbox';
					$baseLangUrl = $baseUrl . '-' . $lang;
					jimport('joomla.filesystem.folder');
					if (JFolder::exists($baseLangUrl)){
						$baseUrl = $baseLangUrl; 
					}
					$document->addScript($baseUrl .'/js/jquery-1.10.2.min.js');
					$document->addScript($baseUrl .'/js/lightbox-2.6.min.js');
					$document->addStyleSheet($baseUrl.'/css/lightbox.css');
					break;
				//case JFormFieldLightboxList::SLIMBOX_20_OTHER_JQ:
				//	$baseUrl = JURI::base().'plugins/content/mosimage/mosimage/slimbox-2';
				//	$document->addScript($baseUrl .'/js/slimbox2.js');
				//	$document->addStyleSheet($baseUrl.'/css/slimbox2.css');
				//	break;
				case JFormFieldLightboxList::LYTEBOX:
					$baseUrl = JURI::base().'plugins/content/mosimage/mosimage/lytebox';
					$document->addScript($baseUrl .'/js/lytebox.js');
					$document->addStyleSheet($baseUrl.'/css/lytebox.css');
					break;
				case JFormFieldLightboxList::LYTEBOX5:
					$baseUrl = JURI::base().'plugins/content/mosimage/mosimage/lytebox5';
					$primaryLanguage = substr($document->getLanguage(),0,2);
					switch ($primaryLanguage){
						case 'de':
							$document->addScript($baseUrl .'/lytebox-'.$primaryLanguage .'.js');
							break;
						case 'en':
						default:
							$document->addScript($baseUrl .'/lytebox.js');
					}
					
					
					$document->addStyleSheet($baseUrl.'/lytebox.css');
					break;
				// case JFormFieldLightboxList::SHADOWBOX:
				// 	$baseUrl = JURI::base().'plugins/content/mosimage/mosimage/shadowbox';
				//	$document->addScript($baseUrl .'/js/shadowbox.js');
				//	$document->addStyleSheet($baseUrl.'/css/shadowbox.css');
				//	$document->addScriptDeclaration('Shadowbox.init()');
				//	break;
				case JFormFieldLightboxList::FANCYBOX:
					JHtml::_('jquery.framework');
					$baseUrl = JURI::base().'plugins/content/mosimage/mosimage/fancybox';
					// $document->addScript(JURI::base().'/media/jui/js/jquery.min.js');
					$document->addScript($baseUrl .'/fancybox/jquery.fancybox-1.3.4.pack.js');
					$document->addStyleSheet($baseUrl.'/fancybox/jquery.fancybox-1.3.4.css');
					$document->addScriptDeclaration('jQuery(document).ready(function() {


			jQuery("a[rel=fancybox-group]").fancybox({
				\'transitionIn\'		: \'none\',
				\'transitionOut\'		: \'none\',
				\'titlePosition\' 	: \'over\',
				\'titleFormat\'		: function(title, currentArray, currentIndex, currentOpts) {
					return \'<span id="fancybox-title-over">' . JText::_('MOSIMAGE_IMAGE') . ' \' + (currentIndex + 1) + \' / \' + currentArray.length + (title.length ? \' &nbsp; \' + title : \'\') + \'</span>\';
				}
			});});
							');
					break;
				case JFormFieldLightboxList::SLIMBOX_20:
				default:
					JHtml::_('jquery.framework');
					$baseUrl = JURI::base().'plugins/content/mosimage/mosimage/slimbox-2';
					$document->addScript($baseUrl .'/js/slimbox2.js');
					$document->addStyleSheet($baseUrl.'/css/slimbox2.css');
					break;
			}
	}	
}


?>
