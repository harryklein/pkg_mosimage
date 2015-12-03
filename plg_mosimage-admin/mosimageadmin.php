<?php
/**
 * @version 2.0 $Id: mosimageadmin.php,v 1.4 2015/02/01 00:33:09 harry Exp $
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.mosimage-admin
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.event.plugin');

class plgButtonMosimageAdmin extends JPlugin
{

    public function __construct ($subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    function onDisplay ($name)
    {
        if (! JFactory::getUser()->authorise('core.edit', 'com_mosimage')) {
            return;
        }
        if (! $this->isEditAnArticle($name)) {
            return;
        }
        JHTML::_('behavior.modal');
        $id = $this->getIdFromRequest();
        if ($id === 0) {
            return false;
        } else {
            $this->addStyleSheet();
            $link = $this->buildUrlToModalDialog($name, $id);
            $button = $this->buildButton($link);
        }
        return $button;
    }

    private function isEditAnArticle ($name)
    {
        if ($name != 'jform_articletext') {
            return false;
        }
        return true;
    }

    private function getIdFromRequest ()
    {
        $app = JFactory::getApplication();
        if ($app->isAdmin()) {
            $id = $app->input->get('id');
            if ($id > 0) {
                return $id;
            }
        } else {
            $a_id = $app->input->get('a_id');
            if ($a_id > 0) {
                return $a_id;
            }
        }
        return 0;
    }

    private function buildUrlToModalDialog ($name, $id)
    {
        $app = JFactory::getApplication();
        if ($app->isAdmin()) {
            $baseUrl = '';
        } else {
            $baseUrl = '';
        }
        $link = $baseUrl . 'index.php?option=com_mosimage&tmpl=component&task=options.edit&content_id=' . $id;
        return $link;
    }

    private function buildButton ($link)
    {
        $button = new JObject();
        $button->class = 'btn';
        $button->modal = true;
        $button->link = $link;
        $button->options = "{ closeable: false , handler: 'iframe', size: {x: 1000, y: 675} , onClose: 'function(){ alert(); return false; }' , onOpen: 'function(){ alert(); return false; }' }";
        $button->text = JText::_('PLG_MOSIMAGE_ADMIN_BUTTON');
        $button->name = 'mosimage';
        // $button->onClose='onClose: function(){Mosimage.revertChanges()}}';
        return $button;
    }

    private function addStyleSheet ()
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'plugins/editors-xtd/mosimageadmin/mosimageadmin.css');
    }
}
?>