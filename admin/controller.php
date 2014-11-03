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
	
	public function display($cachable = false, $urlparams = array()){
		require_once JPATH_COMPONENT.'/helpers/mosimage.php';
	    $view	= JRequest::getCmd('view', 'info');	    
	    if ($view == 'info'){
	        JRequest::setVar('view','info');
	       	MosimageHelper::addSubmenu($view);
	        parent::display();
	        return $this;
	    }	    
		parent::display();	
		return $this;
	}

	public function saveMosimageOption(){
		$images = JRequest::getString('images');
		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$id = JRequest::getVar( 'id', $cid[0], '', 'int' );
		$db	= JFactory::getDBO();
		if ($id == 0){
			$result = false;
		} else {
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_mosimage/tables');
			$row = JTable::getInstance('mosimage');
			$row->load($id);
			$row->images=$images;
		    if ($row->content_id != 0) {
			    $result = $db->updateObject('#__mosimage', $row, 'content_id');
		    }
		    else {
		        $row->content_id = $id;
			    $result = $db->insertObject('#__mosimage', $row, 'content_id');
		    }
		}
		if ($result === true){
		?>
		<script type="text/javascript">
		window.parent.SqueezeBox.close();
		</script>
		<?php } else {
		    if ($error = $db->getErrorMsg()) {
			    JError::raiseWarning(500, $error);
		    } else { 
			    echo JText::_('COM_MOSIMAGE_ERROR_OCCURRED');
		    }
			?><br />
			<button type="button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE');?></button>
		<?php
		} 
	}
	
	public function clearcache(){
		jimport('joomla.filesystem.folder');
        $cacheDirectory = JPATH_SITE.'/cache/mosimage-cache';
        $result= JFolder::delete($cacheDirectory);
        
        if (!JFolder::exists($cacheDirectory)){
			$result = JFolder::create($cacheDirectory) && $result;
		}
  
		if ($result){
			$msg = JText::_('COM_MOSIMAGE_DELETE_CACHE_WAS_SUCCESSFULL');
		} else {
			$msg = JText::_('COM_MOSIMAGE_DELETE_CACHE_FAILED');			
		}	
	    $this->setRedirect(JRoute::_('index.php?option=com_mosimage&view=info'), $msg);
	}
	
}