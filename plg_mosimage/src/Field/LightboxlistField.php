<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2008-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Field;

use HKlein\Plugin\Content\Mosimage\Lightbox\LightboxFactory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Auswahl der Lightbox
 */
class LightboxlistField extends ListField
{
    protected $type = 'Lightboxlist';

    protected function getOptions()
    {
        $options   = parent::getOptions();
        $options[] = HTMLHelper::_('select.option', LightboxFactory::LYTEBOX, 'Lytebox 3.22');
        $options[] = HTMLHelper::_('select.option', LightboxFactory::LYTEBOX5, 'Lytebox 5.5');
        $options[] = HTMLHelper::_('select.option', LightboxFactory::SLIMBOX_20, 'Slimbox 2.04');
        $options[] = HTMLHelper::_('select.option', LightboxFactory::LIGHTBOX2, 'Lightbox 2');

        return $options;
    }
}
