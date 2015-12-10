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

class FancyLightbox implements LightBox
{

    public function __construct ()
    {}

    public function getRel ()
    {
        return 'fancybox-group';
    }

    public function getCssClassForImageLink ()
    {
        return 'mosimage';
    }

    public function addScriptAndCssToDocument ()
    {
        JHtml::_('jquery.framework');
        
        $baseUrl = JURI::base() . 'plugins/content/mosimage/mosimage/fancybox';
        $document = JFactory::getDocument();
        $document->addScript($baseUrl . '/fancybox/jquery.fancybox-1.3.4.pack.js');
        $document->addStyleSheet($baseUrl . '/fancybox/jquery.fancybox-1.3.4.css');
        $document->addScriptDeclaration(
                'jQuery(document).ready(function() {


			jQuery("a[rel=fancybox-group]").fancybox({
				\'transitionIn\'		: \'none\',
				\'transitionOut\'		: \'none\',
				\'titlePosition\' 	: \'over\',
				\'titleFormat\'		: function(title, currentArray, currentIndex, currentOpts) {
					return \'<span id="fancybox-title-over">' . JText::_('MOSIMAGE_IMAGE') . ' \' + (currentIndex + 1) + \' / \' + currentArray.length + (title.length ? \' &nbsp; \' + title : \'\') + \'</span>\';
				}
			});});
							');
    }
}
?>
