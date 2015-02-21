<?php
/**
 * @version 2.0 $Id: imagesize.php,v 1.3 2015/02/06 00:07:32 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formrule');

/**
 * Diese Rule stellt sicher, dass full_height und full_width konsitent zueinander sind. Des weiteren wird gegrüft, ob
 * der  jweilige Wert im Wertebreich (min, max) bzw. für Prozenzwerte im Wertebereich (pmin,pmax) ist. 
 */
class JFormRuleImagesize extends JFormRule
{

    const DEFAULT_MAX_SIZE = 1000;

    const DEFAULT_MIN_SIZE = 100;

    const DEFAULT_MAX_PERCENT = 1000;

    const DEFAULT_MIN_PERCENT = 100;

    public function test (SimpleXMLElement $element, $value, $group = NULL, JRegistry $input = NULL, JForm $form = NULL)
    {
        $pmax = (int) $element['pmax'] ? $element['pmax'] : self::DEFAULT_MAX_PERCENT;
        $pmin = (int) $element['pmin'] ? $element['pmin'] : self::DEFAULT_MIN_PERCENT;
        $max = (int) $element['max'] ? $element['max'] : self::DEFAULT_MAX_SIZE;
        $min = (int) $element['min'] ? $element['min'] : self::DEFAULT_MIN_SIZE;
        
        if (strpos($value, '%')) {
            $intValue = intval(str_replace('%', '', $value));
            $maxValue = $pmax;
            $minValue = $pmin;
        } else {
            $maxValue = $max;
            $minValue = $min;
            $intValue = intval($value);
        }
        
        if (($input->get('params.full_height') == 0) && ($input->get('params.full_width') == 0)) {
            return new Exception(JText::_('MOSIMAGE_INVALID_FULL_WIDTH_AND_HEIGHT_ARE_ZERO'));
        }
        
        $name = $element['name'];
        if ($name == 'full_height') {
            if ($intValue == 0) {
                return new Exception(JText::_('MOSIMAGE_INVALID_FULL_HEIGHT_IS_ZERO'));
            }
        }
        
        if ((($intValue < $minValue) && ($intValue != 0)) || ($intValue > $maxValue)) {
            return new Exception(JText::sprintf('MOSIMAGE_INVALID_' . strtoupper($name), $value, $min, $max, $pmin, $pmax));
        }
        
        return true;
    }

    private function assertValueInRange ($intvalue, $minValue, $maxValue)
    {
    }
}
