<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: mosimage.php,v 1.1 2014-10-19 19:26:35 harry Exp $
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

class MosimageHelper extends JHelperContent {
		
	public static function addSubmenu($vName = 'cache') {
		JHtmlSidebar::addEntry(JText::_('COM_MOSIMAGE_CACHE_MANAGEMENT'),'#',$vName == 'cache');
	}
}



?>