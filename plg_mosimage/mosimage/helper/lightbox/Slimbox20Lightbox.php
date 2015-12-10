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

class Slimbox20Lightbox implements LightBox
{

    public function __construct ()
    {}

    public function getRel ()
    {
        return 'lightbox-group';
    }

    public function getCssClassForImageLink ()
    {
        return '';
    }

    public function addScriptAndCssToDocument ()
    {
        JHtml::_('jquery.framework');
        
        $baseUrl = JURI::base() . 'plugins/content/mosimage/mosimage/slimbox-2';
        $document = JFactory::getDocument();
        $document->addScript($baseUrl . '/js/slimbox2.js');
        $document->addStyleSheet($baseUrl . '/css/slimbox2.css');
    }
}


?>
