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
	
	private $_data;
	
	public function getTable($type = 'Mosimage', $prefix = 'JTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}	
	
	
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_mosimage.option', 'option', array('control' => 'jform', 'load_data' => $loadData));
	
		if (empty($form))
		{
			return false;
		}
		//$formData = $this->loadFormData();
		//$field = $form->getField('imagelist');
		return $form;
	}
	
	
	protected function loadFormData() {
		$data = JFactory::getApplication()->getUserState('com_users.edit.user.data', array());
		
		if (empty($data))
		{
			$data = $this->getItem();
		}
		/*
		$e = array();
		
		$i = array();
		$i['value'] = 'Value';
		$i['text'] = 'Hallo'; 
		
		$e[] = $i;
		
		$_data = new stdClass();
		$_data->imagelist = $e;
		return $_data;
		*/
		return $data;	
	}
	
	public function getItem($pk = null){
		if ($item = parent::getItem($pk)){
			/*
			// Convert the metadata field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();
	
			// Convert the images field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->images);
			$item->images = $registry->toArray();
	
			if (!empty($item->id))
			{
				$item->tags = new JHelperTags;
				$item->tags->getTagIds($item->id, 'com_weblinks.weblink');
				$item->metadata['tags'] = $item->tags;
			}*/
		}
		return $item;
	}
	
	
	
	public function getData(){
		if (empty($this->_data)){
			//JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_mosimage/tables');
			//$row = JTable::getInstance('mosimage');
			$row = $this->getTable();

			// TODO ist das so OK oder holt man sich die id anders?
			$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
			JArrayHelper::toInteger($cid, array(0));
			$id = JRequest::getVar( 'content_id', $cid[0], '', 'int' );
			
			$row->load($id);
			if (trim( $row->images )) {
				$images = preg_replace("#(\n\r)+#",'',$row->images);
				$images = preg_replace("#(\r)*#",'',$images);
				$row->images = explode( "\n",$images);
			} else {
				$row->images = array();
			}
			$images2 = array();
			foreach( $row->images as $file ) {
				$temp = explode( '|', $file );
				if( strrchr($temp[0], '/') ) {
					$filename = substr( strrchr($temp[0], '/' ), 1 );
				} else {
					$filename = $temp[0];
				}
				$images2[] = JHTML::_('select.option', $file, $filename );
			}
			$this->_data = $images2; 
		}
		return $this->_data;
	}
}



?>