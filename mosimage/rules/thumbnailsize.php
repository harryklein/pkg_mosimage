<?php
/**
 * @version 2.0 $Id: thumbnailsize.php,v 1.3 2015/02/06 00:07:31 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formrule');

class JFormRuleThumbnailsize extends JFormRule
{

    const DEFAULT_MAX_SIZE = 300;

    const DEFAULT_MIN_SIZE = 50;

    public function test (SimpleXMLElement $element, $value, $group = NULL, JRegistry $input = NULL, JForm $form = NULL)
    {
        $max = (int) $element['max'] ? $element['max'] : self::DEFAULT_MAX_SIZE;
        $min = (int) $element['min'] ? $element['min'] : self::DEFAULT_MIN_SIZE;
        
        if (($input->get('params.thumb_height') == 0) && ($input->get('params.thumb_width') == 0)) {
            return new Exception(JText::_('MOSIMAGE_INVALID_THUMB_WIDTH_AND_HEIGHT_ARE_ZERO'));
        }
        
        $name = $element['name'];
        if ((($value < $min) && ($value != 0)) || ($value > $max)) {
            return new Exception(JText::sprintf('MOSIMAGE_INVALID_' . strtoupper($name), $value, $min, $max));
        }
        return true;
    }
}