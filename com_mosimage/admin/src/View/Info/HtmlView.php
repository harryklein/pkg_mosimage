<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Administrator\View\Info;

use HKlein\Component\Mosimage\Administrator\Model\InfoModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class HtmlView extends BaseHtmlView
{
    protected int $amountCacheFile = 0;

    protected array $cacheFileList = [];

    protected int $moreAsMaxFilesExist = 0;

    public function display($tpl = null)
    {
        /** @var InfoModel $model */
        $model = $this->getModel();

        $this->amountCacheFile     = $model->getAmountCacheFiles();
        $this->cacheFileList       = $model->getFileList();
        $this->moreAsMaxFilesExist = $model->getMoreAsMaxFilesExist();

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $toolbar = $this->getDocument()->getToolbar();
        $user    = Factory::getApplication()->getIdentity();

        ToolbarHelper::title(Text::_('COM_MOSIMAGE'), 'images');

        $toolbar->standardButton('delete', 'COM_MOSIMAGE_CLEAR_IMAGE_CACHE', 'info.clearcache')
            ->icon('icon-trash')
            ->listCheck(false);

        $toolbar->linkButton('plugin', 'COM_MOSIMAGE_PLUGIN_SETTINGS')
            ->url('index.php?option=com_mosimage&task=info.plugin')
            ->icon('icon-cog');

        if ($user->authorise('core.admin', 'com_mosimage') || $user->authorise('core.options', 'com_mosimage')) {
            $toolbar->preferences('com_mosimage');
        }
    }
}
