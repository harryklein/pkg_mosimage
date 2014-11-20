<?php

defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: view.html.php,v 1.2 2014-02-19 22:35:03 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010-2012 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 *
 * H2N Mosimage Component is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * H2N Mosimage Component is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class MosimageViewInfo extends JViewLegacy {
	
    public function display($tpl = null) {
        JToolBarHelper::title(JText::_('COM_MOSIMAGE'));
        $this->amountCacheFile = $this->get('AmountCacheFiles');        
        $this->cacheFileList = $this->get('FileList');
        $this->moreAsMaxFilesExist = $this->get('MoreAsMaxFilesExist');
        
        require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/mosimage.php';
        MosimageHelper::addSubmenu('cache');
        $this->sidebar = JHtmlSidebar::render();
      
        parent::display($tpl);
    }

    
    
        

}