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

class ThumbnailsizeRule extends FormRule
{
    public const DEFAULT_MAX_SIZE = 300;

    public const DEFAULT_MIN_SIZE = 50;

    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        $max   = (int) $element['max'] ?: self::DEFAULT_MAX_SIZE;
        $min   = (int) $element['min'] ?: self::DEFAULT_MIN_SIZE;
        $value = (int) $value;

        if ($input && ((int) $input->get('params.thumb_height') === 0) && ((int) $input->get('params.thumb_width') === 0)) {
            throw new \UnexpectedValueException(Text::_('MOSIMAGE_INVALID_THUMB_WIDTH_AND_HEIGHT_ARE_ZERO'));
        }

        $name = (string) $element['name'];

        if ((($value < $min) && ($value !== 0)) || ($value > $max)) {
            throw new \UnexpectedValueException(Text::sprintf('MOSIMAGE_INVALID_' . strtoupper($name), $value, $min, $max));
        }

        return true;
    }
}
