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

class LyteboxLightbox implements LightBox
{

    public function __construct ()
    {}

    public function getRel ()
    {
        return 'lytebox[vacation]';
    }

    public function getCssClassForImageLink ()
    {
        return '';
    }

    public function addScriptAndCssToDocument ()
    {
        $baseUrl = JURI::base() . 'plugins/content/mosimage/mosimage/lytebox';
        $document = JFactory::getDocument();
        $document->addScript($baseUrl . '/js/lytebox.js');
        $document->addStyleSheet($baseUrl . '/css/lytebox.css');
    }
}

?>
