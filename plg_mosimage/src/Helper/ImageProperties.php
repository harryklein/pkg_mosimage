<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class ImageProperties
{
    public const DEFAULT_IMGAGE_ALGIN = 'left';

    private \stdClass $attrib;

    private PluginConfiguration $config;

    public function __construct(\stdClass $imagePropertiesAsStdClass, PluginConfiguration $config)
    {
        $this->attrib = $imagePropertiesAsStdClass;
        $this->config = $config;
    }

    public function getRelativeFileName(): string
    {
        return trim((string) ($this->attrib->source ?? ''));
    }

    public function getImageAlgin(): string
    {
        if (empty($this->attrib->align)) {
            return self::DEFAULT_IMGAGE_ALGIN;
        }

        return (string) $this->attrib->align;
    }

    public function getImageAlignAsHtml(): string
    {
        if ($this->config->isViewCaptionTextForThumbnail()) {
            return '';
        }

        $imageAlign = $this->getImageAlgin();

        switch ($imageAlign) {
            case 'left':
            case 'right':
                return ' style="float: ' . $imageAlign . ';"';
        }

        return ' style="float: none;"';
    }

    public function getOuterCssClass(): string
    {
        $imageAlign = $this->getImageAlgin();

        switch ($imageAlign) {
            case 'left':
            case 'right':
                return 'mosimgage-outer-' . $imageAlign;
        }

        return 'mosimgage-outer-none';
    }

    public function isViewCaptionTextForThumbnail(): bool
    {
        if ($this->getCaptionPosition() === 'hide') {
            return false;
        }

        return $this->config->isViewCaptionTextForThumbnail();
    }

    public function getAltText(): string
    {
        if (empty($this->attrib->alt)) {
            return 'Image';
        }

        return htmlspecialchars((string) $this->attrib->alt, ENT_QUOTES, 'UTF-8');
    }

    public function getBorderWidth(): string
    {
        if (isset($this->attrib->border)) {
            switch ((string) $this->attrib->border) {
                case '0':
                    return $this->config->getThumbnailBorderWidth();
                case '1':
                case '2':
                case '3':
                    return (string) $this->attrib->border;
                case 'hidden':
                    return '0';
            }
        }

        return $this->config->getThumbnailBorderWidth();
    }

    public function getCaptionText(): string
    {
        if (empty($this->attrib->caption)) {
            return '&nbsp;';
        }

        return (string) $this->attrib->caption;
    }

    public function isCaptionTextEmpty(): bool
    {
        return empty($this->attrib->caption);
    }

    public function getCaptionPosition(): string
    {
        $position = (string) ($this->attrib->caption_position ?? '');

        switch ($position) {
            case 'bottom':
            case 'top':
            case 'hide':
                return $position;
        }

        return $this->config->getThumbnailCaptionAlign();
    }

    /**
     * Zugriffsebene (Viewlevel) des Bildes. Default ist 1 (Öffentlich).
     */
    public function getAccessLevel(): int
    {
        $level = (int) ($this->attrib->accesslevel ?? 1);

        return $level > 0 ? $level : 1;
    }

    /**
     * @param int[] $authorisedViewLevels Zugriffsebenen des aktuellen Benutzers
     */
    public function isAccessible(array $authorisedViewLevels): bool
    {
        $level = $this->getAccessLevel();

        return $level === 1 || \in_array($level, array_map('intval', $authorisedViewLevels), true);
    }

    public function getAbsoluteFileName(): string
    {
        return JPATH_SITE . '/images/' . $this->getRelativeFileName();
    }
}
