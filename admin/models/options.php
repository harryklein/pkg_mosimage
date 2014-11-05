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
	
	private $allAvailableImageFolders;
	private $allAvailableImages;
	
	
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
	
	public function getAllAvailableImages(){
		if ( $this->allAvailableImages == null) {
			$this->allAvailableImages = array();
			$this->allAvailableImageFolders = array();
			$this->getFolderAndImageList($this->allAvailableImageFolders , $this->allAvailableImages);
		}
		return $this->allAvailableImages;
	}
	
	public function getAllAvailableImageFolders(){
		if ( $this->allAvailableImageFolders == null) {
			$this->allAvailableImages = array();
			$this->allAvailableImageFolders = array();
			$this->getFolderAndImageList($this->allAvailableImageFolders , $this->allAvailableImages);
		}
		return $this->allAvailableImageFolders;
	}
	
	
	private function getFolderAndImageList( &$folders, &$images){
		$folders[] = JHTML::_('select.option','/');
		$imagePath = JPATH_SITE .'/images';
		$folderPath = '/';
		$this->readImagesList($imagePath, $folderPath, $folders, $images);
	}
	
	/**
	 * Internal function to recursive scan the media manager directories
	 * @param string Path to scan
	 * @param string root path of this folder
	 * @param array  Value array of all existing folders
	 * @param array  Value array of all existing images
	 */
	private function readImagesList( $imagePath, $folderPath, &$folders, &$images ) {
		 
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$imgFiles=JFolder::files($imagePath);
		foreach ($imgFiles as $file) {
			$ff 	= $folderPath . $file;
			$i_f 	= $imagePath .'/'. $file;
			if ( preg_match( '/\.gif$|\.jpg$|\.jpeg$|\.png$/i', $file ) && is_file( $i_f ) ) {
				// leading / we don't need
				$imageFile = substr( $ff, 1 );
				$images[$folderPath][] = JHTML::_('select.option',$imageFile, $file );
			}
		}
	
		$imgDirs=JFolder::folders($imagePath);
		foreach ($imgDirs as $dir) {
			$i_f 	= $imagePath .'/'. $dir;
			$ff_ 	= $folderPath . $dir .'/';
			if ( is_dir( $i_f )) {
				$folders[] = JHTML::_('select.option',$ff_);
				$this->readImagesList( $i_f, $ff_, $folders, $images );
			}
		}
	}
	
	
	
}



?>