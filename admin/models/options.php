<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: options.php,v 1.2 2014-10-28 23:10:01 harry Exp $
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

class MosimageModelOptions extends JModelAdmin {

	public $typeAlias = 'com_mosimage.options';

	protected $text_prefix = 'COM_MOSIMAGE';
	
	public function getTable($type = 'Mosimage', $prefix = 'JTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}	
	
	
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_mosimage.option', 'option', array('control' => 'jform', 'load_data' => $loadData));
	
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	
	protected function loadFormData() {
		$data = JFactory::getApplication()->getUserState('com_mosimage.edit.data', array());
		
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;	
	}
	
	public function getItem($pk = null){
		$item = parent::getItem($pk);
		// String -> array
		if (trim( $item->images )) {
			$imageslist = preg_replace("#(\n\r)+#",'',$item->images);
			$imageslist = preg_replace("#(\r)*#",'',$imageslist);
			$imageslist = explode( "\n",$imageslist);
		} else {
			$imageslist = array();
		}
		$item->imageslist = $imageslist;
		return $item;
	}
}



?>