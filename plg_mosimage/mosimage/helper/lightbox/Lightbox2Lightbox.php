<?php
/**
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ROOT . '/plugins/content/mosimage/mosimage/fields/lightboxlist.php';
require_once JPATH_ROOT . '/plugins/content/mosimage/mosimage/helper/LightboxHelper.php';

class Lightbox2Lightbox implements LightBox
{

    public function __construct ()
    {}

    public function getRel ()
    {
        return 'lightbox[roadtrip]';
    }

    public function getCssClassForImageLink ()
    {
        return '';
    }

    public function addScriptAndCssToDocument ()
    {
        $document = JFactory::getDocument();
        
        $lang = substr($document->getLanguage(), 0, 2);
        $baseUrl = JURI::base() . 'plugins/content/mosimage/mosimage/lightbox';
        $baseLangUrl = $baseUrl . '-' . $lang;
        jimport('joomla.filesystem.folder');
        if (JFolder::exists($baseLangUrl)) {
            $baseUrl = $baseLangUrl;
        }
        $document->addScript($baseUrl . '/js/jquery-1.10.2.min.js');
        $document->addScript($baseUrl . '/js/lightbox-2.6.min.js');
        $document->addStyleSheet($baseUrl . '/css/lightbox.css');
    }
}

?>
