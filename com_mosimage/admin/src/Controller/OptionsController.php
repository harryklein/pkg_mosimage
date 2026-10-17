<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Administrator\Controller;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Bearbeitung der einem Beitrag zugeordneten Bilder. Die Bearbeitung erfolgt in einem
 * Dialog (iframe) des Editors; nach "Speichern & Schließen" bzw. "Schließen" wird der Dialog geschlossen.
 */
class OptionsController extends FormController
{
    protected $view_item = 'options';

    protected $view_list = 'options';

    protected $text_prefix = 'COM_MOSIMAGE';

    public function save($key = null, $urlVar = null)
    {
        $result = parent::save($key, $urlVar);

        // Der Aufruf von parent::save() setzt einen Redirect auf die Listenansicht, der wegen
        // des Dialogs hier nicht gewünscht ist.
        if ($result && $this->getTask() === 'save') {
            $this->setRedirect(Route::_($this->getCloseUrl(), false), $this->message, $this->messageType);
        }

        return $result;
    }

    public function cancel($key = null)
    {
        $result = parent::cancel($key);
        $this->setRedirect(Route::_($this->getCloseUrl(), false));

        return $result;
    }

    protected function allowEdit($data = [], $key = 'content_id')
    {
        return $this->app->getIdentity()->authorise('core.edit', $this->option);
    }

    protected function allowAdd($data = [])
    {
        return $this->app->getIdentity()->authorise('core.edit', $this->option);
    }

    private function getCloseUrl(): string
    {
        return 'index.php?option=' . $this->option . '&view=options&layout=close&tmpl=component';
    }
}
