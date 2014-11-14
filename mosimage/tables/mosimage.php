<?php defined('_JEXEC') or die('Restricted access');

class JTableMosimage extends JTable {
	public $id = null;
	public $images = null;
	
	public function __construct(& $db) {
		parent::__construct('#__mosimage', 'id', $db);
	}
	
	public function bind($array, $ignore = '') {
	    return parent::bind($array, $ignore);
	}
	
}


?>