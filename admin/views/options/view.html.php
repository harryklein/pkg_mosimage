<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: view.html.php,v 1.5 2014-10-30 21:54:14 harry Exp $
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

class MosimageViewOptions extends JViewLegacy {

	public function display($tpl = null) {
	    	    
	    $document = JFactory::getDocument();
	    $document->addScript(JURI::root() .'/administrator/components/com_mosimage/js/mosimage.js');	    
	    
	    JHTML::_('behavior.tooltip');
		$this->setLayout('form');

		$this->state = $this->get('State'); 
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
				
		$this->allAvailableImages = $this->get('AllAvailableImages');
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar(){
		JToolbarHelper::title(JText::_('COM_MOSIMAGE_MOSIMAGE_CONTROL'));
		JToolbarHelper::apply('options.apply');
		JToolbarHelper::save('options.save');
		JToolbarHelper::cancel('options.cancel', 'JTOOLBAR_CLOSE');
	} 
}