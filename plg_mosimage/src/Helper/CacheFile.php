<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2008-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Helper;

use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class CacheFile
{
    public const RELATIVE_CACHE_PATH = 'cache/mosimage-cache';

    private string $absoluteFile;

    private string $cacheFilename;

    private int $origWidth;

    private int $origHeight;

    private int $displayWidth;

    private int $displayHeight;

    private ImageDisplayProperties $imageProperties;

    private int $offsetX = 0;

    private int $offsetY = 0;

    private PluginConfiguration $config;

    private int $bgcolor;

    private string $proportion;

    /**
     * @param ImageDisplayProperties $imageProperties Pfad und Name des Original-Bildes inkl. der Größe, wie es angezeigt werden soll
     * @param PluginConfiguration    $config          Plugin-Konfiguration
     * @param string                 $proportion      Art der Einpassung
     * @param int                    $bgcolor         Hintergrundfarbe
     * @param ?int                   $origWidth       Breite des Original-Bildes. Wird, wenn nicht angegeben, zur Laufzeit ermittelt
     * @param ?int                   $origHeight      Höhe des Original-Bildes. Wird, wenn nicht angegeben, zur Laufzeit ermittelt.
     *
     * @throws \RuntimeException Wenn das Original-Bild nicht gelesen werden kann
     */
    public function __construct(ImageDisplayProperties $imageProperties, PluginConfiguration $config, string $proportion, int $bgcolor, ?int $origWidth = null, ?int $origHeight = null)
    {
        $this->absoluteFile = $imageProperties->file();
        $this->config       = $config;

        if ($origWidth === null || $origHeight === null) {
            $size = @getimagesize($imageProperties->file());

            if (!$size || $size[0] <= 0 || $size[1] <= 0) {
                throw new \RuntimeException('There was a problem loading image [' . $imageProperties->file() . ']');
            }

            $this->origWidth  = $size[0];
            $this->origHeight = $size[1];
        } else {
            $this->origWidth  = $origWidth;
            $this->origHeight = $origHeight;
        }

        $this->displayWidth    = $imageProperties->displayWidth();
        $this->displayHeight   = $imageProperties->displayHeight();
        $this->imageProperties = $imageProperties;
        $this->bgcolor         = $bgcolor;
        $this->proportion      = $proportion;
        $this->cacheFilename   = $this->calcCacheFilename($imageProperties, $bgcolor, $proportion);
    }

    public function imageProperties(): ImageDisplayProperties
    {
        return $this->imageProperties;
    }

    /**
     * @param string $language Sprache für den Place-Holder-Text
     */
    public function newCacheFileForPlaceholder(string $language): CacheFile
    {
        $otherCacheFilename = $this->displayWidth . $this->displayHeight . $language . '-placeholder.jpg';
        $cachefile          = new CacheFile($this->imageProperties(), $this->config, $this->proportion, $this->bgcolor, $this->origWidth(), $this->origHeight());
        $cachefile->setDisplaySize($this->displayWidth, $this->displayHeight);
        $cachefile->cacheFilename = $this->scrambleFilename($otherCacheFilename, $this->config->getScrambleFilename());

        return $cachefile;
    }

    /**
     * @return string Name des Cache-Files ohne Pfadangabe
     */
    public function getCacheFilename(): string
    {
        return $this->cacheFilename;
    }

    /**
     * @return string Name des Original-Bildes mit absoluter Pfadangabe
     */
    public function getAbsoluteFile(): string
    {
        return $this->absoluteFile;
    }

    /**
     * @return string Name des Pfades des Caches
     */
    public function getAbsoluteCachePath(): string
    {
        $cachepath = JPATH_SITE . '/' . self::RELATIVE_CACHE_PATH;

        if (!is_dir($cachepath)) {
            Folder::create($cachepath);
        }

        return $cachepath;
    }

    public function getRelativeCachePath(): string
    {
        return self::RELATIVE_CACHE_PATH;
    }

    public function getAbsoluteCacheFile(): string
    {
        return $this->getAbsoluteCachePath() . '/' . $this->getCacheFilename();
    }

    public function getCacheFileUrl(): string
    {
        return Uri::root(true) . '/' . self::RELATIVE_CACHE_PATH . '/' . rawurlencode(basename($this->getCacheFilename()));
    }

    public function displayWidth(): int
    {
        return $this->displayWidth;
    }

    public function displayHeight(): int
    {
        return $this->displayHeight;
    }

    public function getConfig(): PluginConfiguration
    {
        return $this->config;
    }

    public function getProportion(): string
    {
        return $this->proportion;
    }

    public function getBackgroundColor(): int
    {
        return $this->bgcolor;
    }

    public function origWidth(): int
    {
        return $this->origWidth;
    }

    public function origHeight(): int
    {
        return $this->origHeight;
    }

    public function flipOrigWidthWithHeight(): void
    {
        [$this->origWidth, $this->origHeight] = [$this->origHeight, $this->origWidth];
    }

    /**
     * Offset des "Inhaltes"
     */
    public function setImageOffset($x, $y): void
    {
        $this->offsetX = (int) $x;
        $this->offsetY = (int) $y;
    }

    public function offsetX(): int
    {
        return $this->offsetX;
    }

    public function offsetY(): int
    {
        return $this->offsetY;
    }

    /**
     * Größe für die Anzeige des Bildes
     */
    public function setDisplaySize($x, $y): void
    {
        $this->displayWidth  = (int) $x;
        $this->displayHeight = (int) $y;
    }

    private function scrambleFilename(string $filename, string $scramble): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        switch ($scramble) {
            case 'md5':
                return md5($filename) . '.' . $ext;
            case 'crc32':
                return \sprintf('%u', crc32($filename)) . '.' . $ext;
            case 'sha1':
                return sha1($filename) . '.' . $ext;
        }

        return $filename;
    }

    private function calcCacheFilename(ImageDisplayProperties $imageProperties, int $bgcolor, string $proportion): string
    {
        $builder = (new CacheFileNameBuilder($imageProperties->file()))
            ->addWatermarkInfo($this->config->getWatermarkFile(), $this->config->isUsedWatermark())
            ->addBackgroundColorInfo($bgcolor)
            ->addProportionInfo($proportion)
            ->addDisplaySizeInfo($imageProperties);

        return $this->scrambleFilename($builder->build(), $this->config->getScrambleFilename());
    }
}
