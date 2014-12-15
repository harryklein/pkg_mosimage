<?php
/**
 * @version 2.0 $Id: button.php,v 1.3 2014-10-30 21:50:08 harry Exp $
 * @package Joomla.Administrator
 * @subpackage com_mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Erlaubt es, eine Button via xml-Beschreibung in ein Form einzubinden.
 * <ul>
 * <li>name: </li>
 * <li>type: button</li>
 * <li>label: Text, der auf dem Button stehen soll</li>
 * <li>description: Text, der als Tooltip dargestellt werden soll
 * <li>onclick: JS, welches beim Kick auf den Button ausgeführt werden soll</li>
 * <li>disable: Bei true ist der Button nicht klickbar, wird aber dargestellt</li>
 * <li>hidden: Bei true wird der Button nicht angezeigt</li>
 * <li>class: CSS-Klasse, default ist btn</li>
 * <li>size: Größe des Buttons in em. Ist das Label länger als das Button, so wird das Label rechts abgeschnitten. Ohne Angabe einer Größe ist der Button so groß wie nötig.
 * </ul>
 * Beispiel XML
 * <pre>
 <field name="up" type="button" label="Up" description="Up desc"
 onclick="Mosimage.moveInList('adminForm','jform_images',-1)"
 disabled="true" class="test" />
 </pre>
 * Als HTML wird ausgegebn:
 <pre>
 <input type="button" disabled="disabled" title="Up desc"
 onclick="Mosimage.moveInList('adminForm','jform_images',-1)" value="Up" class="test" id="jform_up">
 </pre>
 */
class JFormFieldButton extends JFormField
{

    protected $type = 'Button';

    public function __construct ($form = null)
    {
        parent::__construct($form);
    }

    private function prepareAttribute ($attribute, $value)
    {
        $result = sprintf('%s="%s"', $attribute, $value);
        return $result;
    }

    protected function getInput ()
    {
        $attribute = array();
        if ($this->hidden) {
            $type = 'hidden';
        } else {
            $type = 'button';
        }
        
        $attribute['type'] = $this->prepareAttribute('type', $type);
        
        $label = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
        $label = $this->translateLabel ? JText::_($label) : $label;
        $attribute['value'] = $this->prepareAttribute('value', $label);
        
        if (! empty($this->description)) {
            $description = $this->translateDescription ? JText::_($this->description) : $this->description;
            $attribute['title'] = $this->prepareAttribute('title', $description);
        }
        
        if ($this->disabled) {
            $attribute['disable'] = $this->prepareAttribute('disabled', 'disabled');
        }
        
        if ($this->class) {
            $attribute['class'] = $this->prepareAttribute('class', $this->class);
        } else {
            $attribute['class'] = $this->prepareAttribute('class', "btn");
        }
        
        if (isset($this->size) && (((int) $this->size) > 0)) {
            $attribute['size'] = 'style="width:' . $this->size . 'em;"';
        }
        $attribute['onclick'] = $this->prepareAttribute('onclick', $this->onclick);
        $attribute['id'] = $this->prepareAttribute('id', $this->id);
        $attribute['name'] = $this->prepareAttribute('name', $this->name);
        
        $option = '<input ' . join(' ', $attribute) . '/>';
        return $option;
    }

    protected function getLabel ()
    {
        return '&nbsp;';
    }
}

?>