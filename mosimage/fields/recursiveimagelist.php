<?php defined('_JEXEC') or die( 'Restricted access' );

/**
 * @version 2.0 $Id: recursiveimagelist.php,v 1.2 2013-12-19 22:58:49 harry Exp $
 * @package Joomla
 * @subpackage H2N Plugin Mosimage
 * @copyright (C) 2008 - 2009 Harry Klein - www.joomla-hklein.de
 * @license GNU/GPL, see LICENSE.php
 * 
 * H2N Plugin Mosimage is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * H2N Plugin Mosimage is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class JFormFieldRecursiveImageList extends JFormField {
	protected $type = 'RecursiveImageList';
	
	private $rootDir;

	public function __construct(){
		$this->rootDir = JPATH_SITE.'/images';
	}

	protected function getInput() {
		$options = $this->readFileList($this->rootDir);
		$onchange	= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
		//return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
		return JHtml::_('select.genericlist', $options, $this->name, $onchange, 'value', 'text', $this->value, $this->id);
	}

	private function readFileList($path ) {
		$filter =  '^[wW][^\.].*\.([Pp][Nn][Gg]|[Jj][Pp][Ee]?[Gg])';
		$recurse = true;
		$excludefiles = array();
		$excludeexts = array();
		jimport('joomla.filesystem.folder');
		$filelist = JFolder::files($path, $filter, $recurse, true, $excludefiles);
		$files = array();
		$files[] = &$this->getFile(null);
		$count = 0;
		while (list($i, $file) = each($filelist)) {
			$count++;
			if ($count > 500) break;
			if (in_array(JFile::getName($file), $excludefiles)) continue;
			if (in_array(JFile::getExt($file), $excludeexts)) continue;
			$file =& $this->getFile($file);
			$files[] = $file;
		}
        $this->fileSort($files);		
		return $files;
	}

	private function &getFile($file){
		if ($file == null){
		    $line = JHtml::_('select.option','-', '- '. JText::_('Select file') .' -');
		    return $line;
		}    
		$fileName = $this->stripPath($file,$this->rootDir);
		$line = JHtml::_('select.option',$fileName,$fileName);
		return $line;
		
	}

	/**
	 * Strip given path from the start of the filename.
	 *
	 * @param String Filename to strip from.
	 * @param String Path to strip from start of filename.
	 * @return String Filename with $path stripped from start.
	 */
	private function stripPath(&$filename, &$path) {
		if (strpos($filename, $path) === 0) {
			return substr($filename, strlen($path) + 1);
		} else {
			return $filename;
		}
	}

	private function fileSort(&$array) {
		usort($array, array('JFormFieldRecursiveImageList', 'compareFiles'));
	}

	private function compareFiles(&$a, &$b){
		$result = strnatcasecmp(dirname($a->value), dirname($b->value));
		if ($result != 0) {
			return $result;
		}
		return strnatcasecmp(basename($a->value), basename($b->value));
	}
}