<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Administrator\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class InfoModel extends BaseDatabaseModel
{
    public const MAX_FILES = 500;

    private const CACHE_DIRECTORY = JPATH_SITE . '/cache/mosimage-cache';

    private ?array $filelist = null;

    /**
     * Liefert die Gesamtanzahl der Dateien im Cache.
     */
    public function getAmountCacheFiles(): int
    {
        if (!is_dir(self::CACHE_DIRECTORY)) {
            return 0;
        }

        $amountFile = 0;

        foreach (new \DirectoryIterator(self::CACHE_DIRECTORY) as $file) {
            if ($file->isFile()) {
                $amountFile++;
            }
        }

        return $amountFile;
    }

    /**
     * Liefert eine Liste mit den ersten self::MAX_FILES Dateien aus dem Cache-Ordner
     *
     * @return string[]
     */
    public function getFileList(): array
    {
        if ($this->filelist === null) {
            $this->filelist = [];

            if (is_dir(self::CACHE_DIRECTORY)) {
                foreach (new \DirectoryIterator(self::CACHE_DIRECTORY) as $file) {
                    if (\count($this->filelist) >= self::MAX_FILES) {
                        break;
                    }

                    if ($file->isFile()) {
                        $this->filelist[] = $file->getFilename();
                    }
                }

                sort($this->filelist);
            }
        }

        return $this->filelist;
    }

    /**
     * Gibt es mehr als self::MAX_FILES Dateien, so wird self::MAX_FILES zurückgegeben, sonst 0;
     */
    public function getMoreAsMaxFilesExist(): int
    {
        return \count($this->getFileList()) >= self::MAX_FILES ? self::MAX_FILES : 0;
    }

    /**
     * Löscht den Cache-Ordner und legt ihn neu an
     */
    public function clearCache(): bool
    {
        try {
            $result = !is_dir(self::CACHE_DIRECTORY) || Folder::delete(self::CACHE_DIRECTORY);

            return Folder::create(self::CACHE_DIRECTORY) && $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Liefert die ID des Content-Plugins Mosimage bzw. 0, wenn das Plugin nicht installiert ist
     */
    public function getPluginId(): int
    {
        $db      = $this->getDatabase();
        $folder  = 'content';
        $element = 'mosimage';
        $query   = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = :folder')
            ->where($db->quoteName('element') . ' = :element')
            ->bind(':folder', $folder, ParameterType::STRING)
            ->bind(':element', $element, ParameterType::STRING);

        return (int) $db->setQuery($query)->loadResult();
    }
}
