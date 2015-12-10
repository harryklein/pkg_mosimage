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

class PrioboxLightbox implements LightBox
{

    public function __construct ()
    {}

    public function getRel ()
    {
        return 'gallery';
    }

    public function getCssClassForImageLink ()
    {
        return 'pirobox_gall';
    }

    public function addScriptAndCssToDocument ()
    {
        $document = JFactory::getDocument();
        
        JHtml::_('jquery.framework');
        
        $baseUrl = JURI::base() . 'plugins/content/mosimage/mosimage/priobox';
        
        $document->addScript($baseUrl . '/js/pirobox_extended.js');
        $document->addScript($baseUrl . '/js/priobox_config.js');
        $document->addStyleSheet($baseUrl . '/css_pirobox/style_1/style.css');
    }
}

?>
