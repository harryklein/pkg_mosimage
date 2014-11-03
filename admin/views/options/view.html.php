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
		$this->item	= $this->get('Item');
		$this->form = $this->get('Form');
		$this->data = $this->get('Data');
		
		
		$folders =array();
		$images = array();
		
		//$folders[] = JHTML::_('select.option','/');
		//$imagePath = JPATH_SITE .'/images';
		//$folderPath = '/';
		
		//$this->getFolderAndImageList($imagePath, $folderPath, $folders, $images);
		$this->getFolderAndImageList($folders, $images);
		
		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$id = JRequest::getVar( 'id', $cid[0], '', 'int' );
		//$imageRootDir = JURI::root() .'/images';
		
		$this->assignRef('images', $images);
		$this->assign('id',$id);
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar(){
		JToolbarHelper::title(JText::_('COM_MOSIMAGE_MOSIMAGE_CONTROL'));
		JToolbarHelper::apply('options.apply');
		JToolbarHelper::save('options.save');
		JToolbarHelper::cancel('options.cancel', 'JTOOLBAR_CLOSE');
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