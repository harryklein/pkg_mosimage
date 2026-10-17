<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Site\Controller;

use HKlein\Component\Mosimage\Administrator\Controller\DisplayController as AdminDisplayController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class DisplayController extends AdminDisplayController
{
    protected $default_view = 'options';
}
