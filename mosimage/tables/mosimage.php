<?php
/**
 * @version 2.0 $Id: ThumbnailCreator.php,v 1.7 2014-03-04 22:54:39 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class JTableMosimage extends JTable
{

    public $id = null;

    public $images = null;

    public function __construct (& $db)
    {
        parent::__construct('#__mosimage', 'id', $db);
    }

    public function bind ($array, $ignore = '')
    {
        return parent::bind($array, $ignore);
    }
}

?>