<?php defined('_JEXEC') or die('Restricted access');


/**
 * @version 2.0 $Id: percentorabsolute.php,v 1.2 2014-01-18 22:31:38 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Plugin
 * @copyright (C) 2010 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 *
 * H2N Mosimage Plugin is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * H2N Mosimage Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

jimport('joomla.form.formfieldtext');

/** 
 * Fügt ein Javascript-Event-Handler hinzu, der dafür sorgt, dass 
 * bei einem Focus-Lost das Feld auf gültige Zeichen überprüft wird.
 */
class JFormFieldPercentorabsolute extends JFormFieldText {

	protected $type = 'Percentorabsolute';
	
	public function getInput() {	
		$document = JFactory::getDocument();
		$document->addScript(JURI::root() .'plugins/content/mosimage/mosimage/js/mosimage.js');
		return parent::getInput();
	}
}