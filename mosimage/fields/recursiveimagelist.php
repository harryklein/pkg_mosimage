<?php
/**
 * @version 2.0 $Id: recursiveimagelist.php,v 1.3 2015/02/05 23:57:53 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class JFormFieldRecursiveImageList extends JFormField
{

    protected $type = 'RecursiveImageList';

    private $rootDir;

    public function __construct ()
    {
        $this->rootDir = JPATH_SITE . '/images';
    }

    protected function getInput ()
    {
        $options = $this->readFileList($this->rootDir);
        $onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
        // return JHTML::_('select.genericlist', $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
        return JHtml::_('select.genericlist', $options, $this->name, $onchange, 'value', 'text', $this->value, $this->id);
    }

    private function readFileList ($path)
    {
        $filter = '^[wW][^\.].*\.([Pp][Nn][Gg]|[Jj][Pp][Ee]?[Gg])';
        $recurse = true;
        $excludefiles = array();
        $excludeexts = array();
        jimport('joomla.filesystem.folder');
        $filelist = JFolder::files($path, $filter, $recurse, true, $excludefiles);
        $files = array();
        $files[] = &$this->getFile(null);
        $count = 0;
        while (list ($i, $file) = each($filelist)) {
            $count ++;
            if ($count > 500)
                break;
            if (in_array(JFile::getName($file), $excludefiles))
                continue;
            if (in_array(JFile::getExt($file), $excludeexts))
                continue;
            $file = & $this->getFile($file);
            $files[] = $file;
        }
        $this->fileSort($files);
        return $files;
    }

    private function &getFile ($file)
    {
        if ($file == null) {
            $line = JHtml::_('select.option', '-', '- ' . JText::_('Select file') . ' -');
            return $line;
        }
        $fileName = $this->stripPath($file, $this->rootDir);
        $line = JHtml::_('select.option', $fileName, $fileName);
        return $line;
    }

    /**
     * Strip given path from the start of the filename.
     *
     * @param String Filename to strip from.
     * @param String Path to strip from start of filename.
     * @return String Filename with $path stripped from start.
     */
    private function stripPath (&$filename, &$path)
    {
        if (strpos($filename, $path) === 0) {
            return substr($filename, strlen($path) + 1);
        } else {
            return $filename;
        }
    }

    private function fileSort (&$array)
    {
        usort($array, array(
                'JFormFieldRecursiveImageList',
                'compareFiles'
        ));
    }

    private function compareFiles (&$a, &$b)
    {
        $result = strnatcasecmp(dirname($a->value), dirname($b->value));
        if ($result != 0) {
            return $result;
        }
        return strnatcasecmp(basename($a->value), basename($b->value));
    }
}