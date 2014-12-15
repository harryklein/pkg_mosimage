<?php
/**
 * @version 2.0 $Id: percentorabsolute.php,v 1.2 2014-01-18 22:31:38 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfieldtext');

/** 
 * Fügt ein Javascript-Event-Handler hinzu, der dafür sorgt, dass 
 * bei einem Focus-Lost das Feld auf gültige Zeichen überprüft wird.
 */
class JFormFieldPercentorabsolute extends JFormFieldText
{

    protected $type = 'Percentorabsolute';

    public function getInput ()
    {
        $document = JFactory::getDocument();
        $document->addScript(JURI::root() . 'plugins/content/mosimage/mosimage/js/mosimage.js');
        return parent::getInput();
    }
}