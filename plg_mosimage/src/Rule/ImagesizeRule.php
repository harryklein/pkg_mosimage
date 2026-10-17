<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2008-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Diese Rule stellt sicher, dass full_height und full_width konsistent zueinander sind. Des weiteren wird geprüft, ob
 * der jeweilige Wert im Wertebereich (min, max) bzw. für Prozentwerte im Wertebereich (pmin, pmax) ist.
 */
class ImagesizeRule extends FormRule
{
    public const DEFAULT_MAX_SIZE = 1000;

    public const DEFAULT_MIN_SIZE = 100;

    public const DEFAULT_MAX_PERCENT = 100;

    public const DEFAULT_MIN_PERCENT = 10;

    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        $pmax  = (int) $element['pmax'] ?: self::DEFAULT_MAX_PERCENT;
        $pmin  = (int) $element['pmin'] ?: self::DEFAULT_MIN_PERCENT;
        $max   = (int) $element['max'] ?: self::DEFAULT_MAX_SIZE;
        $min   = (int) $element['min'] ?: self::DEFAULT_MIN_SIZE;
        $value = trim((string) $value);

        if (str_contains($value, '%')) {
            $intValue = (int) str_replace('%', '', $value);
            $maxValue = $pmax;
            $minValue = $pmin;
        } else {
            $intValue = (int) $value;
            $maxValue = $max;
            $minValue = $min;
        }

        if ($input && ((int) $input->get('params.full_height') === 0) && ((int) $input->get('params.full_width') === 0)) {
            throw new \UnexpectedValueException(Text::_('MOSIMAGE_INVALID_FULL_WIDTH_AND_HEIGHT_ARE_ZERO'));
        }

        $name = (string) $element['name'];

        if ($name === 'full_height' && $intValue === 0) {
            throw new \UnexpectedValueException(Text::_('MOSIMAGE_INVALID_FULL_HEIGHT_IS_ZERO'));
        }

        if ((($intValue < $minValue) && ($intValue !== 0)) || ($intValue > $maxValue)) {
            throw new \UnexpectedValueException(Text::sprintf('MOSIMAGE_INVALID_' . strtoupper($name), $value, $min, $max, $pmin, $pmax));
        }

        return true;
    }
}
