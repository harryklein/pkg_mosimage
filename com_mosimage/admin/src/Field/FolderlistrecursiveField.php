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
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Liste aller Ordner (rekursiv) unterhalb des Ordners, der im Attribut directory angegeben ist.
 */
class FolderlistrecursiveField extends ListField
{
    protected $type = 'Folderlistrecursive';

    protected function getOptions()
    {
        $directory = trim((string) ($this->element['directory'] ?: 'images'), '/');
        $root      = JPATH_ROOT . '/' . $directory;
        $options   = [HTMLHelper::_('select.option', '/', '/')];

        $this->readDirectory($root, '', $options);

        return $options;
    }

    private function readDirectory(string $root, string $relativePath, array &$options): void
    {
        try {
            $folders = Folder::folders($root . $relativePath);
        } catch (\Exception $e) {
            return;
        }

        foreach ($folders as $folder) {
            $newFolder = $relativePath . '/' . $folder;
            $options[] = HTMLHelper::_('select.option', $newFolder . '/', $newFolder);
            $this->readDirectory($root, $newFolder, $options);
        }
    }
}
