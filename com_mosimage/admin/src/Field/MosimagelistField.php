<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Liste der einem Beitrag zugeordneten Bilder.
 *
 * Das Model liefert ein Item, welches das Attribut imageslist (JSON-Array mit den Image-Properties) enthält.
 * Im Formular gibt es das korrespondierende Element:
 * <field name="imageslist" type="mosimagelist" ...
 *
 * Beim Befüllen des Formulars wird value mit dem Wert aus item->imageslist befüllt und bei getOptions()
 * entsprechend ausgewertet. Der Wert jeder Option ist das JSON-Objekt eines Bildes. Die Auswahl des
 * ersten Eintrags erfolgt per JavaScript (mosimage-edit.js).
 */
class MosimagelistField extends ListField
{
    protected $type = 'Mosimagelist';

    /**
     * Liste der Bilder (JSON-String bzw. Array), aus der die Optionen erzeugt werden
     */
    private $images;

    protected function getInput()
    {
        // Der Wert dient nur zum Befüllen der Optionen; keine Option ist vorselektiert.
        $this->images = $this->value;
        $this->value  = [];

        return parent::getInput();
    }

    protected function getOptions()
    {
        $images = $this->images ?? $this->value;

        if (\is_string($images)) {
            $images = json_decode($images);
        }

        $options = [];

        foreach ((array) $images as $image) {
            // Nach einem fehlgeschlagenen Speichern enthält die Liste die JSON-Strings der Optionen
            $obj = \is_string($image) ? json_decode($image) : (object) $image;

            if (!\is_object($obj) || empty($obj->source)) {
                continue;
            }

            $options[] = HTMLHelper::_('select.option', json_encode($obj), basename((string) $obj->source));
        }

        return $options;
    }
}
