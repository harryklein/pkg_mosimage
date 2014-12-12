<?php
/**
 * @version 2.0 $Id: view.html.php,v 1.5 2014-10-30 21:54:14 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class MosimageViewOptions extends JViewLegacy
{

    public function display ($tpl = null)
    {
        $document = JFactory::getDocument();
        $document->addScript(JURI::root() . '/administrator/components/com_mosimage/js/mosimage.js');
        
        JHTML::_('behavior.tooltip');
        $this->setLayout('form');
        
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');
        
        if ($this->form === false) {
            throw new Exception('Can\'t load Form', 500);
        }
        
        $this->allAvailableImages = $this->get('AllAvailableImages');
        if (JFactory::getApplication()->isAdmin()) {
            $this->addToolbar();
        }
        parent::display($tpl);
    }

    protected function addToolbar ()
    {
        $user = JFactory::getUser();
        
        JToolbarHelper::title(JText::_('COM_MOSIMAGE_MOSIMAGE_CONTROL'));
        if ($user->authorise('core.edit', 'com_mosimage')) {
            JToolbarHelper::apply('options.apply');
            JToolbarHelper::save('options.save');
        }
        JToolbarHelper::cancel('options.cancel', 'JTOOLBAR_CLOSE');
    }
}