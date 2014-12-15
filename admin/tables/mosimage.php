<?php
/**
 * @version 2.0 $Id: mosimage.php,v 1.1 2013-09-22 19:10:59 harry Exp $
 * @package Joomla.Administrator
 * @subpackage com_mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @Copyright (C) 2006 Soner (pisdoktor) Ekici - www.sonerekici.com
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class JTableMosimage extends JTable
{

    public $content_id = null;

    public $images = null;

    public function __construct (&$db)
    {
        parent::__construct('#__mosimage', 'content_id', $db);
    }

    public function bind ($array, $ignore = '')
    {
        return parent::bind($array, $ignore);
    }
}
?>