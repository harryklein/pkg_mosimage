<?php
/**
 * @package Joomla.Administrator
 * @subpackage com_mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @Copyright (C) 2006 Soner (pisdoktor) Ekici - www.sonerekici.com
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class MosimageHelper extends JHelperContent
{

    public static function addSubmenu ($vName = 'cache')
    {
        JHtmlSidebar::addEntry(JText::_('COM_MOSIMAGE_CACHE_MANAGEMENT'), '#', $vName == 'cache');
    }
}

?>