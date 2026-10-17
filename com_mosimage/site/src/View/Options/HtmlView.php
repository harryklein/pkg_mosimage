<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_mosimage
 * @copyright   (C) 2010-2026 Harry Klein - www.joomla-hklein.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace HKlein\Component\Mosimage\Site\View\Options;

use HKlein\Component\Mosimage\Administrator\View\Options\HtmlView as AdminHtmlView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Verwendet die Templates der Administration.
 */
class HtmlView extends AdminHtmlView
{
    public function display($tpl = null)
    {
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_mosimage/tmpl/options');

        parent::display($tpl);
    }
}
