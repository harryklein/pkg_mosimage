<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.mosimage
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


class PlgButtonViewMosimage extends JPlugin
{

	protected $autoloadLanguage = true;

	
	public function onDisplay($name)
	{
		$doc = JFactory::getDocument();
		$js = "
			function insertMosimage(editor)
			{
					jInsertEditorText('{mosimage}', editor);
			}
			";

		$doc->addScriptDeclaration($js);
		$button          = new JObject;
		$button->modal   = false;
		$button->class   = 'btn';
		$button->onclick = 'insertMosimage(\'' . $name . '\');return false;';
		$button->text    = JText::_('PLG_VIEWMOSIMAGE_BUTTON');
		$button->name    = 'pictures';
		$button->link    = '#';
		return $button;
	}
}
