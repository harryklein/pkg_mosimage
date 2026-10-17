<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model für die Bearbeitung der einem Beitrag zugeordneten Bilder.
 */
class OptionsModel extends AdminModel
{
    public $typeAlias = 'com_mosimage.options';

    protected $text_prefix = 'COM_MOSIMAGE';

    private const IMAGE_FILTER = '(?i)\.(gif|jpe?g|png|webp)$';

    private ?array $allAvailableImages = null;

    public function getTable($name = 'Mosimage', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    public function getForm($data = [], $loadData = true)
    {
        // Auch im Frontend wird das Formular der Administration verwendet
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_mosimage/forms');

        $form = $this->loadForm('com_mosimage.option', 'option', ['control' => 'jform', 'load_data' => $loadData]);

        return $form ?: false;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_mosimage.edit.options.data', []);

        return empty($data) ? $this->getItem() : $data;
    }

    /**
     * Zusätzlich zu den Spalten der Tabelle enthält das Item die "Spalte" imageslist, die die
     * Image-Properties als JSON-Array enthält. Existiert noch kein Datensatz zum Beitrag, wird
     * ein leerer Datensatz geliefert.
     */
    public function getItem($pk = null)
    {
        $pk   = (int) ($pk ?: $this->getState($this->getName() . '.id'));
        $item = parent::getItem($pk);

        if (!\is_object($item) || empty($item->content_id)) {
            $item             = new \stdClass();
            $item->content_id = $pk;
            $item->images     = '[]';

            // "Datensatz existiert nicht" ist hier kein Fehler
            $this->setError(null);
        }

        $item->imageslist = $this->prepareJson(trim((string) $item->images));

        return $item;
    }

    /**
     * Liefert die Image-Properties als valides JSON (Array). Das alte '|'-Format aus Joomla 1.0 wird konvertiert.
     */
    public function prepareJson(string $images): string
    {
        if ($images === '') {
            return '[]';
        }

        $decoded = json_decode($images);

        if ($decoded === null) {
            return json_encode($this->convertOldImagelistFormat($images));
        }

        // Altes Format mit JSON_FORCE_OBJECT ({"0":{...},"1":{...}}) in ein Array umwandeln
        return json_encode(array_values(array_filter((array) $decoded, 'is_object')));
    }

    private function convertOldImagelistFormat(string $images): array
    {
        $imageslist = preg_replace("#(\n\r)+#", '', $images);
        $imageslist = preg_replace("#(\r)*#", '', $imageslist);

        $jsonList = [];

        foreach (explode("\n", $imageslist) as $imageAsString) {
            if (trim($imageAsString) === '') {
                continue;
            }

            $properties              = array_pad(explode('|', $imageAsString), 8, '');
            $image                   = new \stdClass();
            $image->source           = $properties[0];
            $image->align            = $properties[1];
            $image->alt              = $properties[2];
            $image->border           = $properties[3];
            $image->caption          = $properties[4];
            $image->caption_position = $properties[5];
            $image->caption_align    = $properties[6];
            $image->width            = $properties[7];
            $jsonList[]              = $image;
        }

        return $jsonList;
    }

    /**
     * Liefert alle Bilder unterhalb von images/, gruppiert nach Ordner:
     * [ '/' => ['a.jpg', ...], '/sub/' => ['sub/b.jpg', ...] ]
     *
     * @return array<string, array<int, array{value: string, text: string}>>
     */
    public function getAllAvailableImages(): array
    {
        if ($this->allAvailableImages === null) {
            $this->allAvailableImages = [];
            $this->readImagesList(JPATH_SITE . '/images', '/');
        }

        return $this->allAvailableImages;
    }

    /**
     * Durchsucht rekursiv den Ordner images/
     */
    private function readImagesList(string $imagePath, string $folderPath): void
    {
        try {
            $files   = Folder::files($imagePath, self::IMAGE_FILTER);
            $folders = Folder::folders($imagePath);
        } catch (\Exception $e) {
            return;
        }

        foreach ($files as $file) {
            // führendes / wird nicht benötigt
            $this->allAvailableImages[$folderPath][] = ['value' => substr($folderPath . $file, 1), 'text' => $file];
        }

        foreach ($folders as $dir) {
            $this->readImagesList($imagePath . '/' . $dir, $folderPath . $dir . '/');
        }
    }
}
