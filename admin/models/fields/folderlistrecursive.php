<?php
/**
 * @version 2.0 $Id: folderlistextended.php,v 1.3 2014-10-30 21:51:38 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');

/**
 * 
 * 
 *
 */
class JFormFieldFolderListRecursive extends JFormFieldList
{

    protected $type = 'ImageListRecursive';

    protected $directory;

    protected $filter;

    public function __get ($name)
    {
        switch ($name) {
            case 'filter':
            case 'directory':
                return $this->$name;
        }
        return parent::__get($name);
    }

    public function __set ($name, $value)
    {
        switch ($name) {
            case 'filter':
            case 'directory':
                $this->$name = (string) $value;
                break;
            default:
                parent::__set($name, $value);
        }
    }

    public function setup (SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);
        if ($return) {
            $this->filter = (string) $this->element['filter'];
            $this->directory = (string) $this->element['directory'];
        }
        return $return;
    }

    protected function getOptions ()
    {
        $options = array();
        $options[] = JHtml::_('select.option', '/', '/');
        $relativePath = '';
        $root = JPATH_ROOT . '/' . $this->directory;
        
        $this->readDiretory($root, $relativePath, $options, $this->filter);
        return $options;
    }

    private function readDiretory ($root, $relativePath, &$options, $filter = '.')
    {
        $folders = JFolder::folders($root . $relativePath, $filter);
        if (is_array($folders)) {
            foreach ($folders as $folder) {
                $newFolder = $relativePath . '/' . $folder;
                $options[] = JHtml::_('select.option', $newFolder . '/', $newFolder);
                if (is_dir($root . $newFolder)) {
                    $this->readDiretory($root, $newFolder, $options);
                }
            }
        }
    }
}
?>