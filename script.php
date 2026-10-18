<?php

/**
 * @package     Mosimage
 * @copyright   (C) 2009-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

return new class () implements InstallerScriptInterface {
    private string $minimumJoomla = '6.0.0';

    private string $minimumPhp = '8.3.0';

    public function install(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        if ($type === 'uninstall') {
            return true;
        }

        if (version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
            Log::add(Text::sprintf('JLIB_INSTALLER_MINIMUM_PHP', $this->minimumPhp), Log::WARNING, 'jerror');

            return false;
        }

        if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Log::add(Text::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', $this->minimumJoomla), Log::WARNING, 'jerror');

            return false;
        }

        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        if ($type === 'install' || $type === 'discover_install' || $type === 'update') {
            $this->upgradeTable();

            // Die Cache-Dateien tragen die Einpassung im Namen; nach der Umstellung werden die alten nicht mehr benutzt
            if ($this->migrateImageProportions()) {
                $this->clearImageCache();
            }
        }

        if ($type === 'install' || $type === 'discover_install') {
            $this->enablePlugins();
        }

        return true;
    }

    /**
     * Eine aus Joomla 3 übernommene Tabelle #__mosimage (utf8, Spalte images als text) wird auf
     * utf8mb4 und mediumtext umgestellt. Das Update-SQL der Komponente greift nur bei einem Update,
     * nicht bei einer Neuinstallation über eine bereits vorhandene Tabelle.
     */
    private function upgradeTable(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!\in_array($db->getServerType(), ['mysql'], true)) {
            return;
        }

        try {
            $tableName = $db->replacePrefix('#__mosimage');
            $status    = $db->setQuery('SHOW TABLE STATUS LIKE ' . $db->quote($tableName))->loadObject();

            if (!$status) {
                return;
            }

            if (!str_starts_with((string) $status->Collation, 'utf8mb4')) {
                $db->setQuery('ALTER TABLE ' . $db->quoteName('#__mosimage') . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')
                    ->execute();
            }

            $columns = $db->getTableColumns('#__mosimage');

            if (($columns['images'] ?? '') === 'text') {
                $db->setQuery('ALTER TABLE ' . $db->quoteName('#__mosimage') . ' MODIFY ' . $db->quoteName('images') . ' mediumtext NOT NULL')
                    ->execute();
            }
        } catch (\RuntimeException $e) {
            Log::add('Mosimage: ' . $e->getMessage(), Log::WARNING, 'jerror');
        }
    }

    /**
     * Bis Version 1.x hieß die Einpassung des Content-Plugins (image_proportions) bestfit_top, bestfit_middle
     * und bestfit_bottom, ab Version 2.0.0 top, middle und bottom. Gespeicherte Werte werden umgestellt.
     *
     * @return bool true, wenn ein alter Wert umgestellt wurde
     */
    private function migrateImageProportions(): bool
    {
        $mapping = ['bestfit_top' => 'top', 'bestfit_middle' => 'middle', 'bestfit_bottom' => 'bottom'];
        $db      = Factory::getContainer()->get(DatabaseInterface::class);

        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName(['extension_id', 'params']))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('content'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('mosimage'));
            $plugin = $db->setQuery($query)->loadObject();

            if (!$plugin) {
                return false;
            }

            $params     = new Registry($plugin->params);
            $proportion = (string) $params->get('image_proportions', '');

            if (!isset($mapping[$proportion])) {
                return false;
            }

            $params->set('image_proportions', $mapping[$proportion]);
            $json = $params->toString();
            $id   = (int) $plugin->extension_id;

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('params') . ' = :params')
                ->where($db->quoteName('extension_id') . ' = :id')
                ->bind(':params', $json, ParameterType::STRING)
                ->bind(':id', $id, ParameterType::INTEGER);

            $db->setQuery($query)->execute();

            return true;
        } catch (\RuntimeException $e) {
            Log::add('Mosimage: ' . $e->getMessage(), Log::WARNING, 'jerror');

            return false;
        }
    }

    /**
     * Leert den Bild-Cache des Content-Plugins (wie "Cache leeren" auf der Info-Seite der Komponente).
     */
    private function clearImageCache(): void
    {
        $cacheDirectory = JPATH_SITE . '/cache/mosimage-cache';

        try {
            if (is_dir($cacheDirectory)) {
                Folder::delete($cacheDirectory);
            }

            Folder::create($cacheDirectory);
        } catch (\Exception $e) {
            Log::add('Mosimage: ' . $e->getMessage(), Log::WARNING, 'jerror');
        }
    }

    /**
     * Aktiviert bei der Erstinstallation das Content-Plugin und die beiden Editor-Buttons.
     */
    private function enablePlugins(): void
    {
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $plugins = [['content', 'mosimage'], ['editors-xtd', 'mosimageadmin'], ['editors-xtd', 'viewmosimage']];

        foreach ($plugins as [$folder, $element]) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = :folder')
                ->where($db->quoteName('element') . ' = :element')
                ->bind(':folder', $folder, ParameterType::STRING)
                ->bind(':element', $element, ParameterType::STRING);

            $db->setQuery($query)->execute();
        }
    }
};
