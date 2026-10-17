<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Mosimage
 * @copyright   (C) 2008-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Plugin\Content\Mosimage\Lightbox;

use Joomla\CMS\Application\CMSWebApplicationInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class Lightbox2Lightbox implements LightBox
{
    public function getLinkAttributes(): array
    {
        return ['rel' => 'lightbox[roadtrip]'];
    }

    public function addScriptAndCssToDocument(CMSWebApplicationInterface $app): void
    {
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('plg_content_mosimage');
        $wa->useStyle('plg_content_mosimage.lightbox2')->useScript('plg_content_mosimage.lightbox2');
    }
}
