<?php
/**
 * @package Joomla.Administrator
 * @subpackage com_mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Erlaubt es, eine Bild via xml-Beschreibung in ein Form einzubinden. Folgende Attribute werden dabei unterstützt:
 * <ul>
 * <li>name: Name des Elements</li>
 * <li>type: muss image sein</li>
 * <li>alt: Text, als alterniv-Text angezeigt werden soll</li>
 * <li>description: Wenn angegeben, so wird description als Tooltip ausgegeben</li>
 * <li>label: </li>
 * <li>hidden: Bei true wird des Bild nicht sichtbar ('visibility' wird auf hidden gesetzt)</li>
 * <li>class: Name der CSS-Klasse für das HTML-Element src</li>
 * <li>default: Pfad zum Bild, default ist ../media/system/images/blank.png</li>
 * </ul>
 * Beispiel XML
 * <pre>
 <field id="view_imagefiles" name="view_imagefiles" type="image" class="preview" 
 default="../media/system/images/blank.png" alt="COM_MOSIMAGE_PREVIEW" label="COM_MOSIMAGE_PREVIEW" />
 </pre>
 * Als HTML wird ausgegebn:
 <pre>
 <img id="jform_view_imagefiles" class="preview" name="jform[view_imagefiles]" 
 onclick="" src="http://localhost/cms/images/apply_f2.png" style="visibility: hidden;"/>
 </pre>
 */
class JFormFieldImage extends JFormField
{

    protected $type = 'Image';

    public function __construct ($form = null)
    {
        parent::__construct($form);
    }

    private function prepareAttribute ($attribute, $value)
    {
        $result = sprintf('%s="%s"', $attribute, $value);
        return $result;
    }

    protected function getLabel ()
    {
        return parent::getLabel();
    }

    protected function getInput ()
    {
        $attribute = array();
        if ($this->hidden) {
            $attribute[''] = $this->prepareAttribute('style', 'visibility: hidden;');
        }
        
        $alt = $this->element['alt'] ? (string) $this->element['alt'] : (string) $this->element['name'];
        $alt = $this->translateLabel ? JText::_($alt) : $alt;
        $attribute['alt'] = $this->prepareAttribute('alt', $alt);
        
        if (! empty($this->description)) {
            $attribute['title'] = $this->prepareAttribute('title', $this->translateDescription ? JText::_($this->description) : $this->description);
        }
        
        if (! empty($this->description)) {
            $attribute['title'] = $this->prepareAttribute('title', $this->translateDescription ? JText::_($this->description) : $this->description);
        }
        
        if (empty($this->value)) {
            $attribute['src'] = $this->prepareAttribute('src', '../media/system/images/blank.png');
        } else {
            $attribute['src'] = $this->prepareAttribute('src', $this->value);
        }
        
        if ($this->disabled) {
            $attribute['disabled'] = $this->prepareAttribute('disabled', 'disabled');
        }
        
        if ($this->class) {
            $attribute['class'] = $this->prepareAttribute('class', $this->class);
        }
        
        if (! empty($this->onclick)) {
            $attribute['onclick'] = $this->prepareAttribute('onclick', $this->onclick);
        }
        $attribute['id'] = $this->prepareAttribute('id', $this->id);
        $attribute['name'] = $this->prepareAttribute('name', $this->name);
        $option = '<img ' . join(' ', $attribute) . '/>';
        return $option;
    }
}

?>