<?php defined('_JEXEC') or die( 'Restricted access' );

/**
 * @version 2.0 $Id: thumbnailsize.php,v 1.2 2014-01-18 22:38:44 harry Exp $
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

jimport ( 'joomla.form.formrule' );

class JFormRuleThumbnailsize extends JFormRule {

    const DEFAULT_MAX_SIZE = 300;
    const DEFAULT_MIN_SIZE = 50;

	public function test(SimpleXMLElement $element, $value, $group = NULL, JRegistry $input = NULL, JForm $form = NULL) {
		$max = ( int ) $element ['max'] ? $element ['max'] : self::DEFAULT_MAX_SIZE;
		$min = ( int ) $element ['min'] ? $element ['min'] : self::DEFAULT_MIN_SIZE;
		
		if ( ($input->get('params.thumb_height') == 0) && ($input->get('params.thumb_width') == 0)){
			return new Exception (JText::_('MOSIMAGE_INVALID_THUMB_WIDTH_AND_HEIGHT_ARE_ZERO'));
		}
		
		$name = $element ['name'];
		if ((($value < $min) && ($value != 0)) || ($value > $max)) {
			return new Exception ( JText::sprintf ( 'MOSIMAGE_INVALID_' . strtoupper ( $name ), $value, $min, $max ) );
		}
		return true;
	}
}