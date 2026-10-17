<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2008-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Lightbox;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class LightboxFactory
{
    public const LYTEBOX = 'lytebox-3.22';

    public const LYTEBOX5 = 'lytebox-5.5';

    public const SLIMBOX_20 = 'slimbox-2.04';

    public const LIGHTBOX2 = 'lightbox2';

    /**
     * Fancybox 1.3.4 und PiroBox werden nicht mehr unterstützt, da sie mit dem jQuery von Joomla 4+
     * nicht mehr funktionieren. Gespeicherte Einstellungen fallen auf Slimbox 2 zurück.
     */
    public const FANCYBOX = 'fancybox-1.3.4';

    public const PRIOBOX = 'priobox-1.0';

    public static function getInstance(string $lightboxType): LightBox
    {
        switch ($lightboxType) {
            case self::LYTEBOX:
                return new LyteboxLightbox();
            case self::LYTEBOX5:
                return new Lytebox5Lightbox();
            case self::LIGHTBOX2:
                return new Lightbox2Lightbox();
            case self::SLIMBOX_20:
            default:
                return new Slimbox20Lightbox();
        }
    }
}
