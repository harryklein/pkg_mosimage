<?php
/**
 * @package Joomla.Administrator
 * @subpackage com_mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class MosimageViewInfo extends JViewLegacy
{

    public function display ($tpl = null)
    {
        $this->amountCacheFile = $this->get('AmountCacheFiles');
        $this->cacheFileList = $this->get('FileList');
        $this->moreAsMaxFilesExist = $this->get('MoreAsMaxFilesExist');
        
        JToolBarHelper::title(JText::_('COM_MOSIMAGE'));
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/mosimage.php';
        MosimageHelper::addSubmenu('cache');
        $this->sidebar = JHtmlSidebar::render();
        
        parent::display($tpl);
    }
}