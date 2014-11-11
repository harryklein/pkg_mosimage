<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: controller.php,v 1.3 2014-10-19 19:26:35 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010 Harry Klein - www.joomla-hklein.de
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

class MosimageController extends JControllerLegacy {
	
	protected $default_view = 'info';
	
	public function display($cachable = false, $urlparams = array()){
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/mosimage.php';
		$view   = $this->input->get('view', 'info');
	    
	    if ($view == 'info'){
	       	MosimageHelper::addSubmenu($view);
	        parent::display();
	        return $this;
	    }	    
		parent::display();	
		return $this;
	}	
}