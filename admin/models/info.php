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

class MosimageModelInfo extends JModelLegacy {

	public $typeAlias = 'com_mosimage.info';
	protected $text_prefix = 'COM_MOSIMAGE';
	
	const MAX_FILES = 500;
	private $filelist;
	
	
	/**
	 * Liefert die Gesamtanzahl der verfügbaren Dateien.
	 * 
	 * @return Liefert die Anzahl der verfügbaren Dateien
	 */
	public function getAmountCacheFiles(){
		$cacheDirectory = JPATH_SITE.'/cache/mosimage-cache';
		if (!is_dir($cacheDirectory)){
			return 0;
		}
		$d = opendir($cacheDirectory);
		$amountDir = 0;
		$amountFile = 0;
		while(false !== ($file = readdir($d))) {
			if(is_dir($cacheDirectory.'/'.$file)){
				$amountDir++;
			}
	
			if(is_file($cacheDirectory.'/'.$file)) {
				$amountFile++;
			}
		}
		closedir($d);
		return $amountFile;
	}
	
	/**
	 * Liefert eine Liste mit allen Files aus dem Cache-Ordner
	 *
	 * @return number|Ambigous <multitype:, boolean>
	 */
	public function &getFileList() {
		if ($this->filelist == null){
			$files = array();
			$cacheDirectory = JPATH_SITE.'/cache/mosimage-cache';
			
			if (!is_dir($cacheDirectory)){
				return $files;
			}
			 
			$fileCounter = 0;
			
			$d = opendir($cacheDirectory);
			while( (false !== ($file = readdir($d))) && ($fileCounter < self::MAX_FILES)){
				if(is_file($cacheDirectory.'/'.$file)) {
					$fileCounter++;
					$files[] = $file;
				}
			}
			closedir($d);
			$this->filelist = $files;
		}
		return $this->filelist;
	}	
	
	/**
	 * Gibt es mehr als self::MAX_FILES Dateien, so wird self::MAX_FILES zurückgegeben, sonst 0;
	 * 
	 * @return self::MAX_FILES, wen es mehr gibt, sonst 0
	 */
	public function getMoreAsMaxFilesExist(){
		if ($this->filelist == null){
			$this->getFileList();
		}
		if (count($this->filelist) == self::MAX_FILES){
			return  self::MAX_FILES;
		} 
		return 0;
	}	
	
	
	public function getPluginId ()
	{
	    $db = $this->getDBO();
	    JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_mosimage/tables');
	    $row = JTable::getInstance('Extension');
	
	    $keys = array();
	    $keys['folder'] = 'content';
	    $keys['element'] = 'mosimage';
	    $result = $row->load($keys);
	    if ($result === false) {
	        return 0;
	    }
	    $id = $row->extension_id;
	    return $id;
	}
	
}



?>