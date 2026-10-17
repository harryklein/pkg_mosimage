<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Site\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Im Frontend wird die Komponente nur für die Bearbeitung der Bilder eines Beitrags
 * (Editor-Button "Mosimage") verwendet.
 */
class Dispatcher extends ComponentDispatcher
{
    protected function loadLanguage()
    {
        // Die Sprachdateien liegen nur in der Administration
        $language = $this->app->getLanguage();
        $language->load('com_mosimage', JPATH_ADMINISTRATOR);
        $language->load('com_mosimage', JPATH_ADMINISTRATOR . '/components/com_mosimage');
        $language->load('joomla', JPATH_ADMINISTRATOR);

        parent::loadLanguage();
    }

    protected function checkAccess()
    {
        parent::checkAccess();

        if (!$this->app->getIdentity()->authorise('core.edit', 'com_mosimage')) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
