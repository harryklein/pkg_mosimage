<?php
/**
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ROOT . '/plugins/content/mosimage/mosimage/fields/lightboxlist.php';

class LightboxHelper
{

    const LIGHTBOX_ROOT = '/plugins/content/mosimage/mosimage/helper/lightbox/';

    public static function getInstance ($lightboxType)
    {
        switch ($lightboxType) {
            case JFormFieldLightboxList::PRIOBOX:
                require_once JPATH_ROOT . self::LIGHTBOX_ROOT . 'PrioboxLightbox.php';
                return new PrioboxLightbox();
            case JFormFieldLightboxList::LYTEBOX:
                require_once JPATH_ROOT . self::LIGHTBOX_ROOT . 'LyteboxLightbox.php';
                return new LyteboxLightbox();
            case JFormFieldLightboxList::LYTEBOX5:
                require_once JPATH_ROOT . self::LIGHTBOX_ROOT . 'Lytebox5Lightbox.php';
                return new Lytebox5Lightbox();
            case JFormFieldLightboxList::LIGHTBOX2:
                require_once JPATH_ROOT . self::LIGHTBOX_ROOT . 'Lightbox2Lightbox.php';
                return new Lightbox2Lightbox();
            case JFormFieldLightboxList::FANCYBOX:
                require_once JPATH_ROOT . self::LIGHTBOX_ROOT . 'FancyLightbox.php';
                return new FancyLightbox();
            case JFormFieldLightboxList::SLIMBOX_20:
            default:
                require_once JPATH_ROOT . self::LIGHTBOX_ROOT . 'Slimbox20Lightbox.php';
                return new Slimbox20Lightbox();
        }
    }
}

interface LightBox
{

    public function getRel ();

    public function getCssClassForImageLink ();

    public function addScriptAndCssToDocument ();
}

?>
