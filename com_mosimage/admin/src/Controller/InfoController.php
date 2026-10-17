<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Administrator\Controller;

use HKlein\Component\Mosimage\Administrator\Model\InfoModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class InfoController extends BaseController
{
    /**
     * Löscht den Bilder-Cache
     */
    public function clearcache(): void
    {
        $this->checkToken();

        if (!$this->app->getIdentity()->authorise('core.manage', 'com_mosimage')) {
            $this->setRedirect(Route::_('index.php?option=com_mosimage&view=info', false), Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

            return;
        }

        /** @var InfoModel $model */
        $model = $this->getModel('Info', 'Administrator');

        if ($model->clearCache()) {
            $this->setRedirect(Route::_('index.php?option=com_mosimage&view=info', false), Text::_('COM_MOSIMAGE_DELETE_CACHE_WAS_SUCCESSFULL'));
        } else {
            $this->setRedirect(Route::_('index.php?option=com_mosimage&view=info', false), Text::_('COM_MOSIMAGE_DELETE_CACHE_FAILED'), 'error');
        }
    }

    /**
     * Öffnet die Einstellungen des Content-Plugins Mosimage
     */
    public function plugin(): void
    {
        /** @var InfoModel $model */
        $model = $this->getModel('Info', 'Administrator');
        $id    = $model->getPluginId();

        if ($id === 0) {
            $this->setRedirect(
                Route::_('index.php?option=com_mosimage&view=info', false),
                Text::_('COM_MOSIMGAE_MOSIMAGE_PLUGIN_NOT_FOUND_PLEASE_INSTALL_PLUGIN'),
                'warning'
            );

            return;
        }

        $this->setRedirect(Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . $id, false));
    }
}
