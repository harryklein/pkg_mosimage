<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Administrator\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Erlaubt es, einen Button via XML-Beschreibung in ein Formular einzubinden.
 * <ul>
 * <li>label: Text, der auf dem Button stehen soll</li>
 * <li>description: Text, der als Tooltip dargestellt werden soll</li>
 * <li>icon: (optional) CSS-Klasse eines Icons, z.B. icon-arrow-up. Dann ist das Label nur für Screenreader sichtbar.</li>
 * <li>class: CSS-Klasse, default ist btn btn-secondary</li>
 * <li>disabled: Bei true ist der Button nicht klickbar, wird aber dargestellt</li>
 * </ul>
 * Das Verhalten (Klick) wird per JavaScript über die ID (jform_NAME) angebunden.
 */
class MosimagebuttonField extends FormField
{
    protected $type = 'Mosimagebutton';

    protected function getInput()
    {
        $label       = Text::_((string) ($this->element['label'] ?: $this->element['name']));
        $description = $this->description ? Text::_($this->description) : '';
        $icon        = (string) $this->element['icon'];
        $class       = $this->class ?: 'btn btn-secondary';

        $html = '<button type="button" class="' . htmlspecialchars($class) . '" id="' . $this->id . '"';

        if ($description !== '') {
            $html .= ' title="' . htmlspecialchars($description) . '"';
        }

        if ($this->disabled) {
            $html .= ' disabled';
        }

        $html .= '>';

        if ($icon !== '') {
            $html .= '<span class="' . htmlspecialchars($icon) . '" aria-hidden="true"></span>'
                . '<span class="visually-hidden">' . htmlspecialchars($description ?: $label) . '</span>';
        } else {
            $html .= htmlspecialchars($label);
        }

        return $html . '</button>';
    }

    protected function getLabel()
    {
        return '';
    }
}
