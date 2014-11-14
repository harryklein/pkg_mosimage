<?php defined('_JEXEC') or die( 'Restricted access' );

/**
 * @version 2.0 $Id: lightboxlist.php,v 1.2 2013-12-19 22:57:34 harry Exp $
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

jimport('joomla.form.formfield');

class JFormFieldLightboxList extends JFormField {
	
	protected $type = 'LightboxList';

	const LYTEBOX = 'lytebox-3.22';
	const LYTEBOX5 = 'lytebox-5.5';
	//const SLIMBOX_18 = 'slimbox-1.8';
	const SLIMBOX_20 = 'slimbox-2.04';
	//const SLIMBOX_20_OTHER_JQ = 'slimbox-2.04-other-JQuery';
	const SHADOWBOX =  'shadowbox-3.0.1';
	const LIGHTBOX2 = 'lightbox2';
	const FANCYBOX = 'fancybox-1.3.4';
	
	protected function getInput() {
		$options = array();
		$options[] = JHtml::_('select.option',self::LYTEBOX,             JText::_('lytebox-3.22'));
		$options[] = JHtml::_('select.option',self::LYTEBOX5,            JText::_('lytebox-5.5'));
		//$options[] = JHtml::_('select.option',self::SLIMBOX_18,          JText::_('slimbox-1.8')); // need Mootools 1.3, Jommla 3.1.5 has 1.4.5
		$options[] = JHtml::_('select.option',self::SLIMBOX_20,          JText::_('slimbox-2.04'));
		//$options[] = JHtml::_('select.option',self::SLIMBOX_20_OTHER_JQ, JText::_('slimbox-2.04-other-JQuery'));
		//$options[] = JHtml::_('select.option',self::SHADOWBOX,           JText::_('shadowbox-3.0.1'));
		$options[] = JHtml::_('select.option',self::LIGHTBOX2,           JText::_('lightbox2'));
		$options[] = JHtml::_('select.option',self::FANCYBOX,           JText::_('fancybox-1.3.4'));

		$onchange='';
		$return = JHtml::_('select.genericlist', $options, $this->name, $onchange, 'value', 'text', $this->value, $this->id);
		
		return $return;				
	}
}
