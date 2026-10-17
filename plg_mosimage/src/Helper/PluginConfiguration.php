<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Helper;

use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class PluginConfiguration
{
    public const DEFAULT_THUUMNAIL_WIDTH = 150;

    public const DEFAULT_THUMBNAIL_HEIGHT = 100;

    public const MAX_THUUMNAIL_WIDTH = 500;

    public const MAX_THUMBNAIL_HEIGHT = 500;

    public Registry $params;

    public function __construct(Registry $params)
    {
        $this->params = $params;
    }

    public function getJpegQuality(): int
    {
        return (int) $this->params->get('quality', 80);
    }

    public function getScrambleFilename(): string
    {
        return (string) $this->params->get('scramble', 'off');
    }

    private function getThumbnailSize(): \stdClass
    {
        $width  = (int) $this->params->get('thumb_width', self::DEFAULT_THUUMNAIL_WIDTH);
        $height = (int) $this->params->get('thumb_height', self::DEFAULT_THUMBNAIL_HEIGHT);

        if ($width < 0) {
            $width = self::DEFAULT_THUUMNAIL_WIDTH;
        }

        if ($height < 0) {
            $height = self::DEFAULT_THUMBNAIL_HEIGHT;
        }

        if ($width === 0 && $height === 0) {
            $width  = self::DEFAULT_THUUMNAIL_WIDTH;
            $height = self::DEFAULT_THUMBNAIL_HEIGHT;
        }

        $result         = new \stdClass();
        $result->width  = $width;
        $result->height = $height;

        return $result;
    }

    public function getThumbnailMaxWidth(): int
    {
        return $this->getThumbnailSize()->width;
    }

    public function getThumbnailMaxHeight(): int
    {
        return $this->getThumbnailSize()->height;
    }

    public function getThumbnailBackgroundColor(): int
    {
        return self::colorToInt((string) $this->params->get('thumb_background_color', '#ffffff'));
    }

    public function getThumbnailCaptionAlign(): string
    {
        return (string) $this->params->get('thumb_caption_align', 'bottom');
    }

    public function getThumbnailBorderWidth(): string
    {
        return (string) $this->params->get('thumb_border_width', '1');
    }

    public function getFullsizeMaxWidth(): string
    {
        return (string) $this->params->get('full_width', 800);
    }

    public function getFullsizeMaxHeight(): string
    {
        return (string) $this->params->get('full_height', 600);
    }

    public function isViewCaptionTextForFullsize(): bool
    {
        return (bool) $this->params->get('full_caption', 1);
    }

    public function getFullsizeBackgroundColor(): int
    {
        return self::colorToInt((string) $this->params->get('image_background_color', '#000000'));
    }

    public function isViewCaptionTextForThumbnail(): bool
    {
        return (bool) $this->params->get('thumb_caption', 0);
    }

    public function getWatermarkTransparencyType(): string
    {
        return (string) $this->params->get('transparency_type', 'alpha');
    }

    public function getWatermarkTransparency(): int
    {
        $value = (int) $this->params->get('transparency', 25);

        if ($value < 0 || $value > 100) {
            return 100;
        }

        return $value;
    }

    public function getWatermarkTransparentColor(): string
    {
        return (string) $this->params->get('transparent_color', '#000000');
    }

    public function getWatermarkTop(): string
    {
        return (string) $this->params->get('watermark_top', '');
    }

    public function getWatermarkLeft(): string
    {
        return (string) $this->params->get('watermark_left', '');
    }

    public function getWatermarkFilename(): string
    {
        return trim((string) $this->params->get('watermark_file', ''));
    }

    public function getWatermarkFile(): string
    {
        return JPATH_SITE . '/images/' . $this->getWatermarkFilename();
    }

    public function isUsedWatermark(): bool
    {
        $filename = $this->getWatermarkFilename();

        if ($filename === '' || $filename === '-') {
            return false;
        }

        return (bool) $this->params->get('watermark', 0);
    }

    public function getImageProportions(): string
    {
        return (string) $this->params->get('image_proportions', 'bestfit');
    }

    public function getMaxResizedImagePerRequest(): int
    {
        return (int) $this->params->get('maxResizeImagePerRequest', 10);
    }

    public function getLightboxType(): string
    {
        return (string) $this->params->get('lightbox-type', '');
    }

    public function isDebug(): bool
    {
        return $this->params->get('debug') == '1';
    }

    /**
     * Wandelt eine HTML-Farbe (#rrggbb bzw. #rgb) in einen Integer um.
     */
    public static function colorToInt(string $color): int
    {
        $hex = ltrim(trim($color), '#');

        if (\strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return 0;
        }

        return (int) hexdec($hex);
    }
}
