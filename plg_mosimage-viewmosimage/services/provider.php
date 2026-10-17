<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.viewmosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use HKlein\Plugin\EditorsXtd\ViewMosimage\Extension\ViewMosimage;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new ViewMosimage((array) PluginHelper::getPlugin('editors-xtd', 'viewmosimage'));
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
