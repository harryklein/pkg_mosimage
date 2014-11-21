<?php
/**
 *
 * @version 2.0 $Id: view.html.php,v 1.2 2014-02-19 22:35:03 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010-2012 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 *         
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