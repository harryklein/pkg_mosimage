<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TextField;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fügt einen Validierungs-Handler hinzu, der dafür sorgt, dass das Feld nur
 * Pixel- (z.B. 800) oder Prozentangaben (z.B. 80%) enthält.
 */
class PercentorabsoluteField extends TextField
{
    protected $type = 'Percentorabsolute';

    protected function getInput()
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('plg_content_mosimage');
        $wa->useScript('plg_content_mosimage.percentorabsolute');

        return parent::getInput();
    }
}
