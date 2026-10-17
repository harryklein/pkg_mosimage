<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2008-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class ImageDisplayProperties
{
    public const MAX_X = 1500;

    public const MAX_Y = 1000;

    private const DEFAULT_SCREEN_RESOLUTION = [1024, 768];

    private int $displayX;

    private int $displayY;

    private string $file;

    /**
     * @param int|string $displayX  Breite in Pixel oder in Prozent der Bildschirmbreite (z.B. "80%")
     * @param int|string $displayY  Höhe in Pixel oder in Prozent der Bildschirmhöhe
     * @param string     $file      Absoluter Pfad des Original-Bildes
     * @param ?array     $screenres Bildschirmauflösung [Breite, Höhe]
     */
    public function __construct($displayX, $displayY, string $file, ?array $screenres = null)
    {
        $screenres ??= self::DEFAULT_SCREEN_RESOLUTION;
        $displayX = (string) $displayX;
        $displayY = (string) $displayY;

        $xpos = strpos($displayX, '%');

        if ($xpos) {
            $this->setDisplayX($screenres[0] * (int) substr($displayX, 0, $xpos) / 100);
        } else {
            $this->setDisplayX($displayX);
        }

        $ypos = strpos($displayY, '%');

        if ($ypos) {
            $this->setDisplayY($screenres[1] * (int) substr($displayY, 0, $ypos) / 100);
        } else {
            $this->setDisplayY($displayY);
        }

        $this->file = $file;
    }

    private function setDisplayX($value): void
    {
        $this->displayX = $this->fixSizeValue($value, self::MAX_X);
    }

    private function setDisplayY($value): void
    {
        $this->displayY = $this->fixSizeValue($value, self::MAX_Y);
    }

    private function fixSizeValue($value, int $maxValue): int
    {
        $result = (int) $value;

        if ($result < 0) {
            $result = 0;
        }

        if ($result > $maxValue) {
            $result = $maxValue;
        }

        return $result;
    }

    public function displayWidth(): int
    {
        return $this->displayX;
    }

    public function displayHeight(): int
    {
        return $this->displayY;
    }

    public function file(): string
    {
        return $this->file;
    }
}
