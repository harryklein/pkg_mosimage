<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: mosimagelist.php,v 1.3 2014-10-30 21:50:49 harry Exp $
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

JFormHelper::loadFieldClass('list');

/**
 *
*/
class JFormFieldMosimageList extends JFormFieldList {

	protected $type = 'MosmageList';


	protected function getInput(){
		$baseURL = JURI::root() .'/images/';
		
		$a1 = ' onkeyup="' . "Mosimage.showImageProps( '". $baseURL ."' );";
		$a2 = ' onclick="' . "Mosimage.showImageProps( '". $baseURL ."' );";
		 
		// wir misbrauchen iher onchange, da Fieldlist kein onclick und 
		// onkeyup unterstützt
		$this->onchange = '" ' . $a1 . '"' . $a2;
		return parent::getInput();
	}


	protected function getOptions() {
		$value = $this->value;
		$options= array();
		$i = 0;
		foreach( $value as $file ) {
			$temp = explode( '|', $file );
			if( strrchr($temp[0], '/') ) {
				$filename = substr( strrchr($temp[0], '/' ), 1 );
			} else {
				$filename = $temp[0];
			}
			$tmp = JHTML::_('select.option', $file, $filename );
			$options[] = $tmp;
			// 1. Zeile selektrieren
			if  ($i == 0){
				$this->value = $file;
			}
			$i++;
		}
		return $options;
	}


}
?>