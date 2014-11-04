<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: options.php,v 1.2 2014-10-28 23:08:46 harry Exp $
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

class MosimageControllerOptions extends JControllerForm {

	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	public function save($key = null, $urlVar = null){
		$result =  parent::save($key, $urlVar);
		$task = $this->getTask();
		
		switch ( $task){
			case 'save': 
				$document = JFactory::getDocument();
				$document->addScript(JURI::root() .'/administrator/components/com_mosimage/js/mosimage.js');
				?>
				<script type="text/javascript">
					window.parent.SqueezeBox.close();	
				</script> <?php
				// Der Aufruf von parent::save() setzt ein Redirect, der aber wegen
				// dem Modal-Dialog hier  nicht gewüscht ist.
				$this->setRedirect(null);
				break;
		}	
		return $result;
	}


}
