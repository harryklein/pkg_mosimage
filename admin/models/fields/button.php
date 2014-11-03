<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: button.php,v 1.3 2014-10-30 21:50:08 harry Exp $
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

/**
 *  
<pre>
<field name="up" type="button" label="Up"   onclick="Mosimage.moveInList('adminForm','jform_images',-1)" disabled="true" class="test" hidden="true"/>
</pre>
*/
class JFormFieldButton extends JFormField {

	protected $type = 'Button';

	protected function getInput(){
		
		if ($this->hidden) {
			return '';
		}
		
		$label = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
		$label = $this->translateLabel ? JText::_($label) : $label;
		
		if (!empty($this->description)){
			$description = $this->translateDescription ? JText::_($this->description) : $this->description;
		} else {
			$description = '';
		}
		
		if ($this->disabled){
			$disabled = ' disabled="disabled" ';
		} else {
			$disabled = '';
		}
		

		if ($this->class){
			$class = ' class="' . $this->class . '" ';
		} else {
			$class = ' class="btn" ';
		}
		
		$value =   ' value="'   . $label .  '" ';
		$onclick = ' onclick="' . $this->onclick  . '" ';
		$title =   ' title="'   . $description . '"';
		$id    =   ' id="' . $this->id . '"'; 
		$name  =   ' name="' . $this->name . '"';
		
		$option = '<input type="button" '
				. $id
				. $class
				. $value 
				. $onclick
				. $title 
				. $disabled		
				. '/>'
				;
		return $option;
		
	}
	
	protected function getLabel(){
		return '&nbsp;';
	}
}



?>