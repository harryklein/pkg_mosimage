<?php

defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: view.html.php,v 1.2 2014-02-19 22:35:03 harry Exp $
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

class MosimageViewInfo extends JViewLegacy {
	
	const MAX_FILES = 500;

    public function display($tpl = null) {
        JToolBarHelper::title(JText::_('COM_MOSIMAGE'));
        $this->assign('amountCacheFile',$this->amountCacheFiles());
        $fileList = &$this->getFileList();
        $this->assignRef('cacheFileList',$fileList);
    	if (count($fileList) == self::MAX_FILES){
    		$this->assign('moreAsMaxFilesExist', self::MAX_FILES);
    	} else {
    		$this->assign('moreAsMaxFilesExist', 0);
    	}
        parent::display($tpl);
    }

    
    
    private function amountCacheFiles(){
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
    private function &getFileList() {
    	$cacheDirectory = JPATH_SITE.'/cache/mosimage-cache';
    	if (!is_dir($cacheDirectory)){
    		return 0;
    	}
    	
    	$files = array();
    	$fileCounter = 0;
    	$d = opendir($cacheDirectory);
    	while( (false !== ($file = readdir($d))) && ($fileCounter < self::MAX_FILES)){     	
    		if(is_file($cacheDirectory.'/'.$file)) {
    			$fileCounter++;
    			$files[] = $file;
    		}
    	}
    	closedir($d);
    	return $files;
    }
    

}