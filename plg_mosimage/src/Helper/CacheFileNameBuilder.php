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

class CacheFileNameBuilder
{
    private string $filenameInfo;

    private string $proportionInfo = '';

    private string $displayImagesSizeInfo = '';

    private string $bgcolorInfo = '';

    private string $watermarkInfo = '';

    public function __construct(string $filename)
    {
        $relativeFilename   = substr($filename, \strlen(JPATH_SITE . '/images/'));
        $this->filenameInfo = str_replace([':', '/', '\\', ' '], '.', $relativeFilename);
    }

    /**
     * Damit beim späteren Zusammenbau des Names des CacheFile AddBlock das Image nicht unterdückt,
     * wird vom Waterfilename der sha1 gebildet. Nach Versuchen blockiert AddBlock das Bild nicht mehr.
     */
    public function addWatermarkInfo(string $watermarkFilename, bool $isUsedWatermark): self
    {
        $this->watermarkInfo = $isUsedWatermark ? sha1(basename($watermarkFilename)) : 'x';

        return $this;
    }

    public function addDisplaySizeInfo(ImageDisplayProperties $imageProperties): self
    {
        $this->displayImagesSizeInfo = $imageProperties->displayWidth() . 'x' . $imageProperties->displayHeight();

        return $this;
    }

    public function addProportionInfo(string $proportion): self
    {
        $this->proportionInfo = substr($proportion, 0, 1);

        return $this;
    }

    public function addBackgroundColorInfo(int $bgcolor): self
    {
        $this->bgcolorInfo = \sprintf('0x%06x', $bgcolor);

        return $this;
    }

    public function build(): string
    {
        return $this->proportionInfo . '_' . $this->displayImagesSizeInfo . '_' . $this->bgcolorInfo . '_' . $this->watermarkInfo . '_' . $this->filenameInfo;
    }
}
