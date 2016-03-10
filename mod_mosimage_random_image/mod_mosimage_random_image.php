<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mosimage_random_image
 *
 * @copyright (C) 2015-2016 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$catid = intval($params->get('catid'));
$includeSubCategories = $params->get('includeSubCategories');

$article = ModMosimageRandomImageHelper::getRandomImage($catid, $includeSubCategories);

$result = JPluginHelper::importPlugin('content','plg_mosimage');
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_content.article', &$article, &$params, 0));

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_mosimage_random_image', $params->get('layout', 'default'));
