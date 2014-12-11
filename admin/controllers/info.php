<?php
/**
 * @version 2.0 $Id: options.php,v 1.2 2014-10-28 23:08:46 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class MosimageControllerInfo extends JControllerForm
{

    public function __construct ($config = array())
    {
        parent::__construct($config);
    }

    public function clearcache ()
    {
        if (! $this->allowClearCache()) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->setMessage($this->getError(), 'error');
            
            $this->setRedirect(JRoute::_('index.php', false));
            return false;
        }
        
        jimport('joomla.filesystem.folder');
        $cacheDirectory = JPATH_SITE . '/cache/mosimage-cache';
        $result = JFolder::delete($cacheDirectory);
        
        if (! JFolder::exists($cacheDirectory)) {
            $result = JFolder::create($cacheDirectory) && $result;
        }
        
        if ($result) {
            $msg = JText::_('COM_MOSIMAGE_DELETE_CACHE_WAS_SUCCESSFULL');
        } else {
            $msg = JText::_('COM_MOSIMAGE_DELETE_CACHE_FAILED');
        }
        $this->setRedirect(JRoute::_('index.php?option=com_mosimage&view=info'), $msg);
    }

    public function allowClearCache ()
    {
        $user = JFactory::getUser();
        return $user->authorise('core.manage', 'com_mosimage');
    }

    public function plugin ()
    {
        $id = $this->getModel()->getPluginId();
        if ($id == 0) {
            $this->setRedirect(JRoute::_('index.php?option=com_config&view=component&component=com_mosimage', false));
            $this->setMessage(JText::_('COM_MOSIMGAE_MOSIMAGE_PLUGIN_NOT_FOUND_PLEASE_INSTALL_PLUGIN'));
            return true;
        }
        
        $this->setRedirect(JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id=' . $id, false));
        $app = JFactory::getApplication();
        $context = 'com_plugins.edit.plugin';
        $value = array();
        $value[0] = $id;
        $app->setUserState($context . '.id', $value);
        
        return true;
    }
}
