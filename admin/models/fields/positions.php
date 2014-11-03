<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: positions.php,v 1.2 2014-10-28 23:08:46 harry Exp $
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

class JFormFieldPositions extends JFormFieldList {

	protected $type = 'postion';

	public function getOptions() {
		$options = array();
		$tmp = JHTML::_('list.positions', '_align');
		$options[] = $tmp;		
		return $options;
	}
}

?>