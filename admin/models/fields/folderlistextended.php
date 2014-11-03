<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: folderlistextended.php,v 1.3 2014-10-30 21:51:38 harry Exp $
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

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of folder
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
*/
class JFormFieldFolderListExtended extends JFormFieldList {

	protected $type = 'ImageList';

	protected $directory;
	protected $filter;

	public function __get($name) {
		switch ($name) {
			case 'filter':
			// case 'exclude':
			// case 'hideNone':
			// case 'hideDefault':
			case 'directory':
				return $this->$name;
		}
		return parent::__get($name);
	}
	
	public function __set($name, $value) {
		switch ($name) {
			case 'filter':
			case 'directory':
			// case 'exclude':
			 	$this->$name = (string) $value;
				break;
			// case 'hideNone':
			// case 'hideDefault':
			// 	$value = (string) $value;
			// 	$this->$name = ($value === 'true' || $value === $name || $value === '1');
			// 	break;
			default:
				parent::__set($name, $value);
		}
	}
	
	
	

	public function setup(SimpleXMLElement $element, $value, $group = null) {
		$return = parent::setup($element, $value, $group);
		if ($return) {
			$this->filter = (string) $this->element['filter'];
			$this->directory = (string) $this->element['directory'];
		}
		return $return;
	}

	protected function getOptions()
	{
		$options = array();
		$options[] = JHtml::_('select.option', '/', '/');
		$relativePath = '';	
		$root = JPATH_ROOT . '/' . $this->directory ;
		
		$this->readDiretory($root, $relativePath, $options);
		return $options;
	}

	
	private function readDiretory($root, $relativePath, &$options){
		$folders = JFolder::folders($root . $relativePath, $this->filter);
		if (is_array($folders)) {
			foreach ($folders as $folder) {
				$newFolder = $relativePath . '/' . $folder;
				$options[] = JHtml::_('select.option', $newFolder .'/', $newFolder);
				if (is_dir($root . $newFolder)) {
					$this->readDiretory($root, $newFolder, $options);
				}
			}
		}
		
	}
	
	
}
?>