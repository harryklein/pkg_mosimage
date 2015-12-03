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

class Lytebox5Lightbox implements LightBox
{

    public function __construct ()
    {}

    public function getRel ()
    {
        return '';
    }

    public function getCssClassForImageLink ()
    {
        return 'lytebox" rev="group:name navType:2 animateOverlay:false doAnimations:false';
    }

    public function addScriptAndCssToDocument ()
    {
        $document = JFactory::getDocument();
        $baseUrl = JURI::base() . 'plugins/content/mosimage/mosimage/lytebox5';
        $primaryLanguage = substr($document->getLanguage(), 0, 2);
        switch ($primaryLanguage) {
            case 'de':
                $document->addScript($baseUrl . '/lytebox-' . $primaryLanguage . '.js');
                break;
            case 'en':
            default:
                $document->addScript($baseUrl . '/lytebox.js');
        }
        
        $document->addStyleSheet($baseUrl . '/lytebox.css');
    }
}

?>
