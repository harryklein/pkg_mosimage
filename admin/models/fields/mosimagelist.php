<?php
/**
 * @package Joomla.Administrator
 * @subpackage com_mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * Die Befüllung des Elements ist nicht offentsichtlich. getItem des Modells liefert ein
 * Objekt item, welches das Attribut imageslist enthält. 
 * 
 * Im Form gibt es das korrespondierende Element:
 * <field id="imageslist" name="imageslist" type="mosimagelist" size="10" ...
 *
 * Beim Befüllen des Form wird value mit dem Wert aus item->imageslist befüllt und 
 * bei getOption entsprechend ausgewertet. Nach des Auwertung wird value auf den selectierten Eintrag (also der oberste) 
 * gesetzt. Nicht schön, abes es geht.
 */
class JFormFieldMosimageList extends JFormFieldList
{

    protected $type = 'MosmageList';

    public function __construct ($form = null)
    {
        parent::__construct($form);
    }

    protected function getInput ()
    {
        $baseURL = JURI::root() . '/images/';
        
        $a1 = ' onkeyup="' . "Mosimage.showImageProps( '" . $baseURL . "' );";
        $a2 = ' onclick="' . "Mosimage.showImageProps( '" . $baseURL . "' );";
        
        // wir misbrauchen hier onchange, da Fieldlist kein onclick und onkeyup unterstützt
        $this->onchange = '" ' . $a1 . '"' . $a2;
        return parent::getInput();
    }

    protected function getOptions ()
    {
        $value = $this->value;
        $options = array();
        $i = 0;
        
        $jsonAsObjects = json_decode($value, false);
        
        foreach ($jsonAsObjects as $obj) {
            $imageAttribuesAsJson = json_encode($obj);
            $filename = $this->stripTrailingSlashFromFilename($obj->source);
            $options[] = JHTML::_('select.option', $imageAttribuesAsJson, $filename);
            // 1. Zeile selektrieren
            if ($i == 0) {
                $this->value = $imageAttribuesAsJson;
            }
            $i ++;
        }
        return $options;
    }

    private function stripTrailingSlashFromFilename ($source)
    {
        if (strrchr($source, '/')) {
            $filename = substr(strrchr($source, '/'), 1);
        } else {
            $filename = $source;
        }
        return $filename;
    }
}
?>