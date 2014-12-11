<?php
/**
 * @version 2.0 $Id: mosimage.php,v 1.1 2013-09-22 19:10:59 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2008 - 20014 Harry Klein - www.joomla-hklein.de
 * @Copyright (C) 2006 Soner (pisdoktor) Ekici - www.sonerekici.com
 * @license GNU/GPL, see LICENSE.txt
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