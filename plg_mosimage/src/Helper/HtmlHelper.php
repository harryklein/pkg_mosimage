<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2008-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Helper;

use HKlein\Plugin\Content\Mosimage\Lightbox\LightBox;
use HKlein\Plugin\Content\Mosimage\Lightbox\LightboxFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class HtmlHelper
{
    public static function createHtmlForNoneIntroImage($value): string
    {
        return '<span style="display:none;">' . Text::_('PICTURE_INTRO') . '[' . $value . ']</span>';
    }

    public static function createHtmlForNoneImage($value): string
    {
        return '<span style="display:none;">' . Text::_('PICTURE') . '[' . $value . ']</span>';
    }

    public static function createImageAndBuildHtmlFromArrayObject($imagePropertiesAsArrayObject, PluginConfiguration $config, ?array $screenres): string
    {
        $value = \is_array($imagePropertiesAsArrayObject) ? $imagePropertiesAsArrayObject : [$imagePropertiesAsArrayObject];
        $html  = '<p>';

        foreach ($value as $v) {
            $html .= self::createImageAndBuildHtml($v, $config, $screenres);
        }

        return $html . '</p>';
    }

    public static function createImageAndBuildHtml($imageProperties, PluginConfiguration $config, ?array $screenres, string $param = ''): string
    {
        if (!$imageProperties) {
            return '';
        }

        $imgProperties = new ImageProperties($imageProperties, $config);

        if (!$imgProperties->isAccessible(Factory::getApplication()->getIdentity()?->getAuthorisedViewLevels() ?? [1])) {
            return self::getHtmlForFloat($param);
        }

        if (!is_file($imgProperties->getAbsoluteFileName())) {
            Log::add('Mosimage: image [' . $imgProperties->getRelativeFileName() . '] not found', Log::WARNING, 'mosimage');

            return self::getHtmlForFloat($param);
        }

        $thumbSizeProperties = new ImageDisplayProperties($config->getThumbnailMaxWidth(), $config->getThumbnailMaxHeight(), $imgProperties->getAbsoluteFileName());
        $imageSizeProperties = new ImageDisplayProperties($config->getFullsizeMaxWidth(), $config->getFullsizeMaxHeight(), $imgProperties->getAbsoluteFileName(), $screenres);

        try {
            $thumbnailCreator   = new ThumbnailCreator($config);
            $mosthumbProperties = $thumbnailCreator->createThumbnailImage($thumbSizeProperties);
            $mosimageProperties = $thumbnailCreator->createResizedImage($imageSizeProperties);
        } catch (\RuntimeException $e) {
            Log::add('Mosimage: ' . $e->getMessage(), Log::WARNING, 'mosimage');

            return self::getHtmlForFloat($param);
        }

        if (!$mosthumbProperties || !$mosimageProperties) {
            return '';
        }

        $lightbox = LightboxFactory::getInstance($config->getLightboxType());

        return self::buildHtmlForImage($lightbox, $mosthumbProperties, $mosimageProperties, $imgProperties, $config, $param);
    }

    public static function buildHtmlForImage(LightBox $lightbox, CacheFile $mosthumbProperties, CacheFile $mosimageProperties, ImageProperties $imgProperties, PluginConfiguration $config, string $param): string
    {
        $captionText      = $imgProperties->getCaptionText();
        $captionAttribute = htmlspecialchars(strip_tags(html_entity_decode($captionText, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8');
        $thumbSize        = ' width="' . (int) $mosthumbProperties->displayWidth() . '" height="' . (int) $mosthumbProperties->displayHeight() . '"';

        $image = '<a href="' . htmlspecialchars($mosimageProperties->getCacheFileUrl()) . '"';

        foreach ($lightbox->getLinkAttributes() as $name => $value) {
            $image .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        }

        $image .= ' title="' . ($config->isViewCaptionTextForFullsize() ? $captionAttribute : '') . '"';
        $image .= '>';
        $image .= '<img class="mosimgage-inner" src="' . htmlspecialchars($mosthumbProperties->getCacheFileUrl()) . '"';
        $image .= $thumbSize;
        $image .= $imgProperties->getImageAlignAsHtml();
        $image .= ' alt="' . $imgProperties->getAltText() . '"';
        $image .= ' title="' . $captionAttribute . '"';
        $image .= ' loading="lazy"';
        $image .= ' /></a>';

        $width     = (int) $mosthumbProperties->displayWidth();
        $cssHidden = $imgProperties->isCaptionTextEmpty() ? ' visibility: hidden;' : '';

        $img = '<span class="' . $imgProperties->getOuterCssClass() . '" style="width: ' . $width . 'px;border-width:' . (int) $imgProperties->getBorderWidth() . 'px;">';

        if ($imgProperties->isViewCaptionTextForThumbnail()) {
            $captionPosition = $imgProperties->getCaptionPosition();
            $caption         = '<span class="mosimgage-inner-' . $captionPosition . '" style="width: ' . $width . 'px;' . $cssHidden . '">'
                . $captionText
                . '</span>';

            if ($captionPosition === 'top') {
                $img .= $caption;
            }

            $img .= $image;

            if ($captionPosition === 'bottom') {
                $img .= $caption;
            }
        } else {
            $img .= $image;
        }

        $img .= '</span>';

        return self::getHtmlForFloat($param) . $img;
    }

    /**
     * Liefert das HTML zur Steuerung des Flotings.
     * <ul>
     * <li>clear oder clear=all : Unterbricht das Floating komplett</li>
     * <li>clear=left           : Unterbricht das Floating links</li>
     * <li>clear=right          : Unterbricht das Floating rechts</li>
     * </ul>
     *
     * Alle anderen Paramter beeinflussen das Floating nicht.
     */
    private static function getHtmlForFloat(string $param): string
    {
        switch ($param) {
            case 'clear':
            case 'clear=all':
                return '<div style="clear:both;"></div>';
            case 'clear=left':
                return '<div style="clear:left;"></div>';
            case 'clear=right':
                return '<div style="clear:right;"></div>';
            default:
                return '';
        }
    }
}
