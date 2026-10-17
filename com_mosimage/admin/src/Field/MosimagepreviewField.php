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
 * Zeigt ein Vorschaubild an. Folgende Attribute werden unterstützt:
 * <ul>
 * <li>alt: Text, der als Alternativtext angezeigt werden soll</li>
 * <li>class: Name der CSS-Klasse des img-Elements</li>
 * </ul>
 * Das angezeigte Bild wird per JavaScript gesetzt.
 */
class MosimagepreviewField extends FormField
{
    /**
     * Transparentes 1x1-GIF als Platzhalter
     */
    public const BLANK_IMAGE = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    protected $type = 'Mosimagepreview';

    protected function getInput()
    {
        $alt = Text::_((string) ($this->element['alt'] ?: $this->element['name']));
        $src = $this->value ?: self::BLANK_IMAGE;

        return '<img id="' . $this->id . '" class="' . htmlspecialchars((string) $this->class) . '"'
            . ' src="' . htmlspecialchars((string) $src) . '" alt="' . htmlspecialchars($alt) . '">';
    }
}
