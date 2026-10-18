<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class ThumbnailCreator
{
    /**
     * Anzahl der in diesem Request bereits erzeugten Bilder
     */
    private static int $countGenerateImage = 0;

    private PluginConfiguration $config;

    private WatermarkCreator $watermarkCreator;

    public function __construct(PluginConfiguration $config)
    {
        $this->config           = $config;
        $this->watermarkCreator = new WatermarkCreator($config);
    }

    /**
     * Erzeugt das PlaceholderImage und liefert dessen URL, Höhe und Breite.
     *
     * @param CacheFile $origCacheFile das ursprüngliche CacheFile, welches durch den Platzhalter ersetzt werden soll
     */
    private function generatePlaceHolder(CacheFile $origCacheFile): CacheFile
    {
        $language      = Factory::getApplication()->getLanguage()->getTag();
        $displayWidth  = max(1, $origCacheFile->displayWidth());
        $displayHeight = max(1, $origCacheFile->displayHeight());

        $cacheFile = $origCacheFile->newCacheFileForPlaceholder($language);

        if (file_exists($cacheFile->getAbsoluteCacheFile())) {
            return $cacheFile;
        }

        $placeholderImage = imagecreatetruecolor($displayWidth, $displayHeight);
        imagefilledrectangle($placeholderImage, 2, 2, $displayWidth - 3, $displayHeight - 3, 0xeeeeee);
        $fontSize  = 2;
        $string    = Text::_('MOSIMAGE_PLACE_HOLDER_IMAGE_TEXT');
        $textWidth = imagefontwidth($fontSize) * \strlen($string);
        $x         = (int) (ceil($displayWidth / 2) - ceil($textWidth / 2));
        $color     = imagecolorallocate($placeholderImage, 0, 0, 0);
        imagestring($placeholderImage, $fontSize, max(0, $x), (int) min(45, $displayHeight / 2), $string, $color);
        imagejpeg($placeholderImage, $cacheFile->getAbsoluteCacheFile(), 80);

        return $cacheFile;
    }

    public function createThumbnailImage(ImageDisplayProperties $imageSizeProperties): CacheFile
    {
        return $this->getResizedImage($imageSizeProperties, true, $this->config->getThumbnailBackgroundColor());
    }

    public function createResizedImage(ImageDisplayProperties $imageSizeProperties): CacheFile
    {
        return $this->getResizedImage($imageSizeProperties, false, $this->config->getFullsizeBackgroundColor());
    }

    /**
     * Erzeugt ein verkleinertes Abbild des Originalbildes.
     */
    private function generateResizedImage(CacheFile $cacheFile, string $proportion, bool $thumbnailMode, int $backgroundColor = 0xff0000): CacheFile
    {
        self::$countGenerateImage++;

        // Die EXIF-Orientierung muss vor der Berechnung bekannt sein, da sie ggf. Breite und Höhe vertauscht
        $rotation = $this->getExifRotation($cacheFile);

        // Werte siehe mosimage.xml => thumb_width => min bzw. full_width => min
        $minWidth = $thumbnailMode ? 50 : 200;

        $origw         = $cacheFile->origWidth();
        $origh         = $cacheFile->origHeight();
        $displayWidth  = $cacheFile->displayWidth();
        $displayHeight = $cacheFile->displayHeight();

        // Behandlung kleiner Bilder
        // - Die original-Höhe ist kleiner als die Zielhöhe. Zielhöhe kann nie 0 sein.
        // - Es bleibt diese Höhe erhalten und wir schneiden ggf. etwas von der Breite weg.
        // - Bei Auto-Breite wird die Breite ggf. auf ein Seitenverhältnis von 2:1 begrenzt, damit
        //   das Bild nicht übermäßig breit wird
        if ($origh < $displayHeight) {
            // Im Auto-Modus wird das Seitenverhältnis aus dem Original genommen
            if ($displayWidth === 0) {
                $displayWidth = (int) ($origw / $origh * $displayHeight);

                if ($displayWidth > $displayHeight * 2) {
                    // Seitenverhältnis ggf. auf 2:1 reduzieren
                    $displayWidth = $displayHeight * 2;
                }
            }

            if ($origw < $displayWidth) {
                // Bild ist auch so schmal, das es reinpasst
                $offsetW = ($displayWidth - $origw) / 2;
                $offsetH = $this->calcProportion($displayHeight, $origh, $proportion);
                $srcW    = $origw;
                $srcH    = $origh;
            } else {
                // ansonsten schneiden wir es rechts ab
                $offsetW = 0;
                $offsetH = $this->calcProportion($displayHeight, $origh, $proportion);
                $srcW    = $displayWidth;
                $srcH    = $origh;
            }

            if ($displayWidth < $minWidth) {
                $this->debug($cacheFile, "[Fix auto-Breite] display: [$displayWidth x $displayHeight] offset: [$offsetW x $offsetH]");
                $offsetW      = ($minWidth - $displayWidth) / 2;
                $displayWidth = $minWidth;
            }

            $cacheFile->setImageOffset($offsetW, $offsetH);
            $cacheFile->setDisplaySize($displayWidth, $displayHeight);

            $this->debug($cacheFile, "[kleine Höhe ] display: [$displayWidth x $displayHeight] offsetW: [$offsetW] offsetH: [$offsetH]");

            if ($this->isLimitReached()) {
                return $this->generatePlaceHolder($cacheFile);
            }

            $srcImg = $this->loadImageFromFile($cacheFile, $rotation);
            $dstImg = $this->createDestinationImage($displayWidth, $displayHeight, $backgroundColor);
            imagecopy($dstImg, $srcImg, (int) $offsetW, (int) $offsetH, 0, 0, (int) $srcW, (int) $srcH);
            $dstImg = $this->watermarkCreator->writeWatermarkInfo($dstImg, $thumbnailMode, $cacheFile);
            $this->writeImageToFile($dstImg, $cacheFile);

            return $cacheFile;
        }

        // Behandlung kleiner Bilder
        // - Die original-Breite ist kleiner als die ZielBreite, Zielbreite ist ungleich 0
        // - Es bleibt diese Breite erhalten und wir schneiden ggf. etwas von der Höhe weg.
        if (($origw < $displayWidth) && ($displayWidth !== 0)) {
            if ($origh < $displayHeight) {
                // Bild ist auch so hoch, das es reinpasst, das Bild wird dabei zentriert
                $offsetW = ($displayWidth - $origw) / 2;
                $offsetH = ($displayHeight - $origh) / 2;
                $srcW    = $origw;
                $srcH    = $origh;
            } else {
                // ansonsten schneiden wir es unten ab
                $offsetW = ($displayWidth - $origw) / 2;
                $offsetH = 0;
                $srcW    = $origw;
                $srcH    = $displayHeight;
            }

            $cacheFile->setImageOffset($offsetW, $offsetH);

            $this->debug($cacheFile, "[schmal] display: [$displayWidth x $displayHeight] offsetW: [$offsetW] offsetH: [$offsetH]");

            if ($this->isLimitReached()) {
                return $this->generatePlaceHolder($cacheFile);
            }

            $srcImg = $this->loadImageFromFile($cacheFile, $rotation);
            $dstImg = $this->createDestinationImage($displayWidth, $displayHeight, $backgroundColor);
            imagecopy($dstImg, $srcImg, (int) $offsetW, (int) $offsetH, 0, 0, (int) $srcW, (int) $srcH);
            $this->writeImageToFile($dstImg, $cacheFile);

            return $cacheFile;
        }

        // Breite automatisch berechnen, Seitenverhältnis beibehalten, wenn sinnvoll.
        if ($displayWidth === 0) {
            $displayWidth = (int) ($origw / $origh * $displayHeight);
            $offsetW      = 0;
            $offsetH      = 0;
            $w            = $displayWidth;
            $h            = $displayHeight;

            if ($displayWidth < $minWidth) {
                $this->debug($cacheFile, "[Fix auto-Breite] display: [$displayWidth x $displayHeight] offset: [$offsetW x $offsetH]");
                $offsetW      = ($minWidth - $displayWidth) / 2;
                $displayWidth = $minWidth;
            }

            $cacheFile->setImageOffset($offsetW, $offsetH);
            $cacheFile->setDisplaySize($displayWidth, $displayHeight);

            $this->debug($cacheFile, "[auto-Breite] display: [$displayWidth x $displayHeight] offset: [$offsetW x $offsetH]");

            if ($this->isLimitReached()) {
                return $this->generatePlaceHolder($cacheFile);
            }

            $srcImg = $this->loadImageFromFile($cacheFile, $rotation);
            $dstImg = $this->createDestinationImage($displayWidth, $displayHeight, $backgroundColor);
            imagecopyresampled($dstImg, $srcImg, (int) $offsetW, (int) $offsetH, 0, 0, (int) $w, (int) $h, $cacheFile->origWidth(), $cacheFile->origHeight());
            $dstImg = $this->watermarkCreator->writeWatermarkInfo($dstImg, $thumbnailMode, $cacheFile);
            $this->writeImageToFile($dstImg, $cacheFile);

            return $cacheFile;
        }

        // - Zielbreite ist bekannt und
        // - Original-Bild ist größer als Zielbild
        if ($origw > $origh) {
            // landscape
            if ($displayWidth > $displayHeight) {
                $w       = $origw / $origh * $displayHeight;
                $h       = $displayHeight;
                $offsetW = ($displayWidth - $w) / 2;
                $offsetH = 0;
            } else {
                $w       = $displayWidth;
                $h       = $origh / $origw * $displayWidth;
                $offsetW = 0;
                $offsetH = $this->calcProportion($displayHeight, $h, $proportion);
            }
        } else {
            // portrait
            if ($displayWidth >= $displayHeight) {
                // Zielbild ist Landscape
                $w       = $origw / $origh * $displayHeight;
                $h       = $displayHeight;
                $offsetW = ($displayWidth - $w) / 2;
                $offsetH = 0;
            } else {
                $w       = $displayWidth;
                $h       = $origh / $origw * $displayWidth;
                $offsetW = 0;
                $offsetH = $this->calcProportion($displayHeight, $h, $proportion);
            }
        }

        $cacheFile->setImageOffset($offsetW, $offsetH);
        $cacheFile->setDisplaySize($displayWidth, $displayHeight);

        $this->debug($cacheFile, "[normal] display: [$displayWidth x $displayHeight]  content [$w x $h] offset: [$offsetW x $offsetH]");

        if ($this->isLimitReached()) {
            return $this->generatePlaceHolder($cacheFile);
        }

        $srcImg = $this->loadImageFromFile($cacheFile, $rotation);
        $dstImg = $this->createDestinationImage($displayWidth, $displayHeight, $backgroundColor);
        imagecopyresampled($dstImg, $srcImg, (int) $offsetW, (int) $offsetH, 0, 0, (int) $w, (int) $h, $cacheFile->origWidth(), $cacheFile->origHeight());
        $dstImg = $this->watermarkCreator->writeWatermarkInfo($dstImg, $thumbnailMode, $cacheFile);
        $this->writeImageToFile($dstImg, $cacheFile);

        return $cacheFile;
    }

    private function isLimitReached(): bool
    {
        return self::$countGenerateImage > $this->config->getMaxResizedImagePerRequest();
    }

    private function createDestinationImage(int $width, int $height, int $backgroundColor): \GdImage
    {
        $dstImg  = imagecreatetruecolor(max(1, $width), max(1, $height));
        $bgcolor = imagecolorallocate($dstImg, 0xFF & ($backgroundColor >> 0x10), 0xFF & ($backgroundColor >> 0x8), 0xFF & $backgroundColor);
        imagefill($dstImg, 0, 0, $bgcolor);

        return $dstImg;
    }

    /**
     * @throws \RuntimeException Wenn das Bild nicht geladen werden kann
     */
    private function loadImageFromFile(CacheFile $cacheFile, int $rotation): \GdImage
    {
        $file = $cacheFile->getAbsoluteFile();

        switch (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            case 'png':
                $srcImg = @imagecreatefrompng($file);
                break;
            case 'gif':
                $srcImg = @imagecreatefromgif($file);
                break;
            case 'webp':
                $srcImg = \function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false;
                break;
            default:
                $srcImg = @imagecreatefromjpeg($file);
        }

        if (!$srcImg instanceof \GdImage) {
            throw new \RuntimeException('There was a problem loading image [' . $file . ']');
        }

        if ($rotation !== 0) {
            $srcImg = imagerotate($srcImg, $rotation, 0);
        }

        return $srcImg;
    }

    private function writeImageToFile(\GdImage $img, CacheFile $cacheFile): void
    {
        $target = $cacheFile->getAbsoluteCacheFile();

        switch (strtolower(pathinfo($cacheFile->getAbsoluteFile(), PATHINFO_EXTENSION))) {
            case 'png':
                @imagepng($img, $target);
                break;
            case 'gif':
                @imagegif($img, $target);
                break;
            case 'webp':
                if (\function_exists('imagewebp')) {
                    @imagewebp($img, $target, $this->config->getJpegQuality());
                    break;
                }
                // no break
            default:
                @imagejpeg($img, $target, $this->config->getJpegQuality());
        }
    }

    private function calcProportion($value, $imageValue, string $proportion)
    {
        switch ($proportion) {
            case 'top':
                return 0;
            case 'bottom':
                return $value - $imageValue;
            case 'none':
            case 'middle':
            default:
                return ($value - $imageValue) / 2;
        }
    }

    private function getResizedImage(ImageDisplayProperties $imageProperties, bool $thumbnailMode, int $bgcolor): CacheFile
    {
        $proportion = $thumbnailMode ? 'none' : $this->config->getImageProportions();

        $cacheFile     = new CacheFile($imageProperties, $this->config, $proportion, $bgcolor);
        $cacheFileName = $cacheFile->getAbsoluteCacheFile();

        if (file_exists($cacheFileName) && filemtime($cacheFileName) > filemtime($cacheFile->getAbsoluteFile())) {
            // Bei auto-Breite wird das Bild ansonsten mit Breite 0 dargestellt
            $size = @getimagesize($cacheFileName);

            if ($size) {
                $cacheFile->setDisplaySize($size[0], $size[1]);

                return $cacheFile;
            }
        }

        return $this->generateResizedImage($cacheFile, $proportion, $thumbnailMode, $bgcolor);
    }

    private function debug(CacheFile $cacheFile, string $msg): void
    {
        if ($this->config->isDebug()) {
            echo '[' . htmlspecialchars($cacheFile->getCacheFilename()) . '] ' . htmlspecialchars($msg) . '<br />';
        }
    }

    /**
     * Liefert den Drehwinkel entsprechend der EXIF-Orientierung. Bei einer Drehung um 90° werden
     * Breite und Höhe des Original-Bildes im CacheFile vertauscht.
     */
    private function getExifRotation(CacheFile $cacheFile): int
    {
        if (!\function_exists('exif_read_data')) {
            return 0;
        }

        $exif = @exif_read_data($cacheFile->getAbsoluteFile());

        switch ($exif['Orientation'] ?? 0) {
            case 8:
                $cacheFile->flipOrigWidthWithHeight();

                return 90;
            case 3:
                return 180;
            case 6:
                if ($cacheFile->origHeight() > $cacheFile->origWidth()) {
                    return 0;
                }

                $cacheFile->flipOrigWidthWithHeight();

                return -90;
        }

        return 0;
    }
}
