<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Helper;

use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class WatermarkCreator
{
    private static bool $warningLogged = false;

    private PluginConfiguration $config;

    public function __construct(PluginConfiguration $config)
    {
        $this->config = $config;
    }

    /**
     * Schreibt das Wasserzeichen in das Bild.
     *
     * @return \GdImage Das Bild mit Wasserzeichen (ggf. eine neue Instanz)
     */
    public function writeWatermarkInfo(\GdImage $sourceImage, bool $thumbnailMode, CacheFile $cacheFile): \GdImage
    {
        if (!$this->config->isUsedWatermark() || $thumbnailMode) {
            return $sourceImage;
        }

        $watermarkfile = $this->config->getWatermarkFile();

        try {
            $watermarkImage = $this->loadWatermarkFile($watermarkfile);
        } catch (\RuntimeException $e) {
            $this->logOnce($e->getMessage());

            return $sourceImage;
        }

        imagealphablending($watermarkImage, false);
        imagesavealpha($watermarkImage, true);

        $watermarkWidth   = imagesx($watermarkImage);
        $watermarkHeight  = imagesy($watermarkImage);
        $watermarkOffsetX = $this->calcXOffsetForWatermark($this->config->getWatermarkLeft(), $watermarkWidth, $cacheFile->offsetX(), $cacheFile->displayWidth());
        $watermarkOffsetY = $this->calcYOffsetForWatermark($this->config->getWatermarkTop(), $watermarkHeight, $cacheFile->offsetY(), $cacheFile->displayHeight());

        $fileType    = strtolower(pathinfo($watermarkfile, PATHINFO_EXTENSION));
        $sourceImage = $this->upsampleImageIfNecessary($fileType, $sourceImage);

        if ($this->config->getWatermarkTransparencyType() === 'alpha') {
            // True alphablend
            imagecopy($sourceImage, $watermarkImage, $watermarkOffsetX, $watermarkOffsetY, 0, 0, $watermarkWidth, $watermarkHeight);
        } else {
            $transparentColor = PluginConfiguration::colorToInt($this->config->getWatermarkTransparentColor());
            imagecolortransparent($watermarkImage, $transparentColor);
            imagecopymerge(
                $sourceImage,
                $watermarkImage,
                $watermarkOffsetX,
                $watermarkOffsetY,
                0,
                0,
                $watermarkWidth,
                $watermarkHeight,
                $this->config->getWatermarkTransparency()
            );
        }

        return $sourceImage;
    }

    /**
     * @throws \RuntimeException
     */
    private function loadWatermarkFile(string $watermarkfile): \GdImage
    {
        $fileType = strtolower(pathinfo($watermarkfile, PATHINFO_EXTENSION));

        switch ($fileType) {
            case 'png':
                $watermarkImage = @imagecreatefrompng($watermarkfile);
                break;
            case 'gif':
                $watermarkImage = @imagecreatefromgif($watermarkfile);
                break;
            case 'jpg':
            case 'jpeg':
                $watermarkImage = @imagecreatefromjpeg($watermarkfile);
                break;
            default:
                throw new \RuntimeException('You cannot use a [.' . $fileType . '] file (' . basename($watermarkfile) . ') as a watermark');
        }

        if (!$watermarkImage instanceof \GdImage) {
            throw new \RuntimeException('There was a problem loading the watermark [' . $watermarkfile . ']');
        }

        return $watermarkImage;
    }

    private function logOnce(string $msg): void
    {
        if (!self::$warningLogged) {
            Log::add('Mosimage: ' . $msg, Log::WARNING, 'mosimage');
            self::$warningLogged = true;
        }
    }

    private function calcXOffsetForWatermark(string $align, int $watermarkWidth, int $offsetWidth, int $w): int
    {
        switch ($align) {
            case 'left':
                $destX = $offsetWidth;
                break;
            case 'right':
                $destX = $w - $watermarkWidth - $offsetWidth;
                break;
            case 'center':
            default:
                $destX = ($w / 2) - ($watermarkWidth / 2) + $offsetWidth;
                break;
        }

        return max(0, (int) $destX);
    }

    private function calcYOffsetForWatermark(string $valign, int $watermarkHeight, int $offsetHeight, int $h): int
    {
        switch ($valign) {
            case 'top':
                $destY = $offsetHeight;
                break;
            case 'middle':
                $destY = ($h / 2) - ($watermarkHeight / 2) + $offsetHeight;
                break;
            case 'bottom':
            default:
                $destY = $h - $watermarkHeight - $offsetHeight;
                break;
        }

        return max(0, (int) $destY);
    }

    private function upsampleImageIfNecessary(string $fileType, \GdImage $sourceImage): \GdImage
    {
        if ($fileType !== 'gif') {
            return $sourceImage;
        }

        $width     = imagesx($sourceImage);
        $height    = imagesy($sourceImage);
        $tempimage = imagecreatetruecolor($width, $height);
        imagecopy($tempimage, $sourceImage, 0, 0, 0, 0, $width, $height);

        return $tempimage;
    }
}
