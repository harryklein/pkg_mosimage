<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_mosimage_random_image
 * @copyright   (C) 2015-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\HKlein\\Module\\MosimageRandomImage'));
        $container->registerServiceProvider(new HelperFactory('\\HKlein\\Module\\MosimageRandomImage\\Site\\Helper'));
        $container->registerServiceProvider(new Module());
    }
};
