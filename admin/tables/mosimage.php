<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: mosimage.php,v 1.1 2013-09-22 19:10:59 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2008 - 2009 Harry Klein - www.joomla-hklein.de
 * @Copyright (C) 2006 Soner (pisdoktor) Ekici - www.sonerekici.com
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

class JTableMosimage extends JTable {
	public $content_id = null;
	public $images = null;
	
	public function __construct(&$db) {
		parent::__construct('#__mosimage', 'content_id', $db);
	}
	
	public function bind($array, $ignore = '') {
	    return parent::bind($array, $ignore);
	}
	
}


?>