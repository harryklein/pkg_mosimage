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

class Lytebox5Lightbox implements LightBox
{
    public function getLinkAttributes(): array
    {
        return [
            'class' => 'lytebox',
            'rev'   => 'group:name navType:2 animateOverlay:false doAnimations:false',
        ];
    }

    public function addScriptAndCssToDocument(CMSWebApplicationInterface $app): void
    {
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('plg_content_mosimage');
        $wa->useStyle('plg_content_mosimage.lytebox5');

        // Für Deutsch gibt es eine übersetzte Variante des Skripts
        if (str_starts_with($app->getLanguage()->getTag(), 'de')) {
            $wa->useScript('plg_content_mosimage.lytebox5-de');
        } else {
            $wa->useScript('plg_content_mosimage.lytebox5');
        }
    }
}
