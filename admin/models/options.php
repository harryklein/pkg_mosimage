<?php
/**
 *
 * @version 2.0 $Id: options.php,v 1.2 2014-10-28 23:10:01 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class MosimageModelOptions extends JModelAdmin
{

    public $typeAlias = 'com_mosimage.options';

    protected $text_prefix = 'COM_MOSIMAGE';

    private $allAvailableImageFolders;

    private $allAvailableImages;

    public function getTable ($type = 'Mosimage', $prefix = 'JTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm ($data = array(), $loadData = true)
    {
        JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/forms');
        JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
        JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/model/form');
        JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/model/field');
        
        $form = $this->loadForm('com_mosimage.option', 'option', 
                array(
                        'control' => 'jform',
                        'load_data' => $loadData
                ));
        
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData ()
    {
        $data = JFactory::getApplication()->getUserState('com_mosimage.edit.data', array());
        
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    private function convertOldImagelistFormatToJson ($images)
    {
        $imageslist = preg_replace("#(\n\r)+#", '', $images);
        $imageslist = preg_replace("#(\r)*#", '', $imageslist);
        $imageslist = explode("\n", $imageslist);
        
        $jsonList = array();
        foreach ($imageslist as $imageAsString) {
            $properties = array_pad(explode('|', $imageAsString), 8, '');
            $image = new stdClass();
            $image->source = $properties[0];
            $image->align = $properties[1];
            $image->alt = $properties[2];
            $image->border = $properties[3];
            $image->caption = $properties[4];
            $image->caption_position = $properties[5];
            $image->caption_align = $properties[6];
            $image->width = $properties[7];
            $jsonList[] = $image;
        }
        $json = json_encode($jsonList);
        return $json;
    }

    /**
     * Zusätzlich einer "Spalte" imageslist, die die Image-Properties als Array mit Json-Objekten enthält.
     */
    public function getItem ($pk = null)
    {
        $item = parent::getItem($pk);
        
        $item->imageslist = $this->prepareJson(trim($item->images));
        return $item;
    }

    /**
     * Konveriert das Json-Format bzw.
     * das alte '|'-Format in ein Array mit PHP-Objekten, die die
     * jeweiligen Properties der Bilder enthält.
     * <pre>
     * (
     * [0] => stdClass Object
     * (
     * [source] => stories/fruit/cherry.jpg
     * [align] => left
     * [alt] =>
     * [border] => 0
     * [caption] =>
     * [caption_position] =>
     * [caption_align] =>
     * [width] =>
     * )
     *
     * [1] => stdClass Object
     * (
     * [source] => stories/fruit/pears.jpg
     * [align] => left
     * [alt] =>
     * [border] => 0
     * [caption] =>
     * [caption_position] =>
     * [caption_align] =>
     * [width] =>
     * )
     * )
     * </pre>
     */
    private function convertJsonToObjectArray ()
    {
    }

    /**
     * Liefert die Imapge-Properties als valides Json.
     *
     * @param $images Properies im Json bzw. '|'-Format
     * @return array mit PHP-Objekten, die die jeweiligen Properties der Bilder enthält.
     */
    public function prepareJson ($images)
    {
        if ($images) {
            if (is_null(json_decode($images))) {
                $json = $this->convertOldImagelistFormatToJson($images);
                return $json;
            } else {
                $json = $images;
                return $json;
            }
        }
        $json = json_encode(array());
        return $json;
    }

    public function getAllAvailableImages ()
    {
        if ($this->allAvailableImages == null) {
            $this->allAvailableImages = array();
            $this->allAvailableImageFolders = array();
            $this->getFolderAndImageList($this->allAvailableImageFolders, $this->allAvailableImages);
        }
        return $this->allAvailableImages;
    }

    public function getAllAvailableImageFolders ()
    {
        if ($this->allAvailableImageFolders == null) {
            $this->allAvailableImages = array();
            $this->allAvailableImageFolders = array();
            $this->getFolderAndImageList($this->allAvailableImageFolders, $this->allAvailableImages);
        }
        return $this->allAvailableImageFolders;
    }

    private function getFolderAndImageList (&$folders, &$images)
    {
        $folders[] = JHTML::_('select.option', '/');
        $imagePath = JPATH_SITE . '/images';
        $folderPath = '/';
        $this->readImagesList($imagePath, $folderPath, $folders, $images);
    }

    /**
     * Internal function to recursive scan the media manager directories
     *
     * @param string Path to scan
     * @param string root path of this folder
     * @param array Value array of all existing folders
     * @param array Value array of all existing images
     */
    private function readImagesList ($imagePath, $folderPath, &$folders, &$images)
    {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        $imgFiles = JFolder::files($imagePath);
        foreach ($imgFiles as $file) {
            $ff = $folderPath . $file;
            $i_f = $imagePath . '/' . $file;
            if (preg_match('/\.gif$|\.jpg$|\.jpeg$|\.png$/i', $file) && is_file($i_f)) {
                // leading / we don't need
                $imageFile = substr($ff, 1);
                $images[$folderPath][] = JHTML::_('select.option', $imageFile, $file);
            }
        }
        
        $imgDirs = JFolder::folders($imagePath);
        foreach ($imgDirs as $dir) {
            $i_f = $imagePath . '/' . $dir;
            $ff_ = $folderPath . $dir . '/';
            if (is_dir($i_f)) {
                $folders[] = JHTML::_('select.option', $ff_);
                $this->readImagesList($i_f, $ff_, $folders, $images);
            }
        }
    }
}

?>