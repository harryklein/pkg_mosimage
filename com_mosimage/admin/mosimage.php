<?php
/**
 * @package Joomla.Administrator
 * @subpackage com_mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @Copyright (C) 2006 Soner (pisdoktor) Ekici - www.sonerekici.com
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
if ($app->isAdmin()) {
    if (! JFactory::getUser()->authorise('core.manage', 'com_mosimage')) {
        return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
    }
} else {
    if (! JFactory::getUser()->authorise('core.edit', 'com_mosimage')) {
        return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
    }
}
$controller = JControllerLegacy::getInstance('Mosimage', array(
        'base_path' => JPATH_COMPONENT_ADMINISTRATOR
));
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

?>