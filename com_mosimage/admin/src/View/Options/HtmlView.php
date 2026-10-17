<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Administrator\View\Options;

use HKlein\Component\Mosimage\Administrator\Model\OptionsModel;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dialog zur Auswahl der Bilder eines Beitrags. Wird im Backend und im Frontend (über den Editor-Button) genutzt.
 */
class HtmlView extends BaseHtmlView
{
    protected ?Form $form = null;

    protected ?\stdClass $item = null;

    public function display($tpl = null)
    {
        $wa = $this->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_mosimage');

        if ($this->getLayout() === 'close') {
            $wa->useScript('com_mosimage.close');
            parent::display($tpl);

            return;
        }

        $this->setLayout('edit');

        /** @var OptionsModel $model */
        $model      = $this->getModel();
        $this->item = $model->getItem();
        $this->form = $model->getForm();

        if (!$this->form) {
            throw new \RuntimeException("Can't load form", 500);
        }

        $wa->useScript('keepalive')
            ->useScript('form.validate')
            ->useScript('com_mosimage.edit')
            ->useStyle('com_mosimage.edit');

        $this->getDocument()->addScriptOptions('com_mosimage.edit', [
            'imagesUrl'   => Uri::root() . 'images/',
            'folderImages' => $model->getAllAvailableImages(),
        ]);

        Text::script('COM_MOSIMAGE_APLAY_CHANGED_VALUES');
        Text::script('COM_MOSIMAGE_DISALLOW_CHANGED_VALUES');

        parent::display($tpl);
    }
}
