<?php
/**
 * @version 2.0 $Id: lightboxlist.php,v 1.2 2013-12-19 22:57:34 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldLightboxList extends JFormField
{

    protected $type = 'LightboxList';

    const LYTEBOX = 'lytebox-3.22';

    const LYTEBOX5 = 'lytebox-5.5';
    // const SLIMBOX_18 = 'slimbox-1.8';
    const SLIMBOX_20 = 'slimbox-2.04';
    // const SLIMBOX_20_OTHER_JQ = 'slimbox-2.04-other-JQuery';
    const SHADOWBOX = 'shadowbox-3.0.1';

    const LIGHTBOX2 = 'lightbox2';

    const FANCYBOX = 'fancybox-1.3.4';

    protected function getInput ()
    {
        $options = array();
        $options[] = JHtml::_('select.option', self::LYTEBOX, JText::_('lytebox-3.22'));
        $options[] = JHtml::_('select.option', self::LYTEBOX5, JText::_('lytebox-5.5'));
        // $options[] = JHtml::_('select.option',self::SLIMBOX_18, JText::_('slimbox-1.8')); // need Mootools 1.3, Jommla 3.1.5 has 1.4.5
        $options[] = JHtml::_('select.option', self::SLIMBOX_20, JText::_('slimbox-2.04'));
        // $options[] = JHtml::_('select.option',self::SLIMBOX_20_OTHER_JQ, JText::_('slimbox-2.04-other-JQuery'));
        // $options[] = JHtml::_('select.option',self::SHADOWBOX, JText::_('shadowbox-3.0.1'));
        $options[] = JHtml::_('select.option', self::LIGHTBOX2, JText::_('lightbox2'));
        $options[] = JHtml::_('select.option', self::FANCYBOX, JText::_('fancybox-1.3.4'));
        
        $onchange = '';
        $return = JHtml::_('select.genericlist', $options, $this->name, $onchange, 'value', 'text', $this->value, $this->id);
        
        return $return;
    }
}
