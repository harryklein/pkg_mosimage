<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2008-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Liste aller möglichen Wasserzeichen-Dateien (png/jpg, Dateiname beginnt mit w bzw. W) unterhalb von images/.
 */
class RecursiveimagelistField extends ListField
{
    protected $type = 'Recursiveimagelist';

    private const FILE_FILTER = '^[wW][^\.].*\.([Pp][Nn][Gg]|[Jj][Pp][Ee]?[Gg])$';

    private const MAX_FILES = 500;

    protected function getOptions()
    {
        $rootDir = JPATH_SITE . '/images';
        $options = [HTMLHelper::_('select.option', '-', '- ' . Text::_('JSELECT') . ' -')];

        try {
            $files = Folder::files($rootDir, self::FILE_FILTER, true, true);
        } catch (\Exception $e) {
            $files = [];
        }

        $names = [];

        foreach (\array_slice($files, 0, self::MAX_FILES) as $file) {
            $names[] = str_replace('\\', '/', substr($file, \strlen($rootDir) + 1));
        }

        usort($names, static function (string $a, string $b): int {
            return strnatcasecmp(\dirname($a), \dirname($b)) ?: strnatcasecmp(basename($a), basename($b));
        });

        foreach ($names as $name) {
            $options[] = HTMLHelper::_('select.option', $name, $name);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
