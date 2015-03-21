<?php
/**
 * @version 2.0 $Id: controller.php,v 1.4 2015/02/05 22:08:51 harry Exp $
 * @package Joomla.Administrator
 * @subpackage com_mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class MosimageController extends JControllerLegacy
{

    protected $default_view = 'info';

    public function display ($cachable = false, $urlparams = array())
    {
        $view = $this->input->get('view', 'info');
        $task = $this->input->get('task', '');
        if ( ($view == 'info' ) && ($task != 'plugin')) {
            parent::display();
            return $this;
        }
        parent::display();
        return $this;
    }
}