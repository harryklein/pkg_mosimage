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

interface LightBox
{
    /**
     * Attribute (z.B. rel, class), die der Link auf das Vollbild benötigt.
     *
     * @return array<string, string>
     */
    public function getLinkAttributes(): array;

    public function addScriptAndCssToDocument(CMSWebApplicationInterface $app): void;
}
