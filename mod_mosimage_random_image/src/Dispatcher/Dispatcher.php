<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_mosimage_random_image
 * @copyright   (C) 2015-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Module\MosimageRandomImage\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['article'] = $this->getHelperFactory()
            ->getHelper('RandomImageHelper')
            ->getRandomImage($data['params'], $this->getApplication());

        return $data;
    }
}
