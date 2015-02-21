<?php
/**
 * @version 2.0 $Id: lightboxnote.php,v 1.3 2015/02/05 23:57:53 harry Exp $
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2008-2014 Harry Klein - www.joomla-hklein.de
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('note');

require_once JPATH_ROOT . '/plugins/content/mosimage/mosimage/fields/lightboxlist.php';

class JFormFieldLightboxNote extends JFormFieldNote
{

    public function setup (SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);
        
        $list = array();
        $list[] = new LightboxInfo(JFormFieldLightboxList::LYTEBOX, 'Lytebox 3', '3.25', '-', 'http://lytebox.com/', 'Markus F. Hay', 
                array(
                        'Creative Commons Attribution 3.0 License' => 'http://creativecommons.org/licenses/by/3.0/'
                ));
        $list[] = new LightboxInfo(JFormFieldLightboxList::LYTEBOX5, 'Lytebox 5', '5.5', '-', 'http://lytebox.com/', 'Markus F. Hay', 
                array(
                        'Creative Commons Attribution 3.0 License' => 'http://creativecommons.org/licenses/by/3.0/'
                ));
        $list[] = new LightboxInfo(JFormFieldLightboxList::LIGHTBOX2, 'Lightbox 2', '2.6', 'jQuery >=1.10.2', 'http://lokeshdhakar.com/projects/lightbox2/', 
                'Lokesh Dhakar', 
                array(
                        'Creative Commons Attribution 2.5 License' => 'http://creativecommons.org/licenses/by/2.5/'
                ));
        $list[] = new LightboxInfo(JFormFieldLightboxList::FANCYBOX, 'Famcybox', '1.3.4', 'jQuery', 'http://fancybox.net/blog', 'Janis Skarnelis', 
                array(
                        'MIT License, ' => 'http://www.opensource.org/licenses/mit-license.php',
                        'GPL3' => 'http://www.gnu.org/licenses/gpl.html'
                ));
        $list[] = new LightboxInfo(JFormFieldLightboxList::SLIMBOX_20, 'Slimbox 2', '2.05', 'jQuery >=1.3', 'http://www.digitalia.be/software/slimbox2', 
                'Christophe Beyls', array(
                        'MIT License' => 'http://www.opensource.org/licenses/mit-license.php'
                ));
        
        $list[] = new LightboxInfo(JFormFieldLightboxList::PRIOBOX, 'PiroBox Extended', '1.0', 'jQuery >=1.8', 'http://www.pirolab.it/pirobox/',
                'Diego Valobra', array(
                        'CC-BY-SA' => 'http://creativecommons.org/licenses/by-sa/3/'
                ));
        
        $licenseInfo = '<table width="100%" border="1">';
        $licenseInfo = $licenseInfo . LightboxInfo::writeTableHeader();
        foreach ($list as $l) {
            $licenseInfo = $licenseInfo . $l->writeAsTableRow();
        }
        $licenseInfo = $licenseInfo . '</table>';
        $element['description'] = $licenseInfo;
        return $result;
    }
}

class LightBoxInfo
{

    private $lightboxType;

    private $name;

    private $version;

    private $usedJsLibs;

    private $url;

    private $author;

    private $license;

    public function __construct ($lightboxType, $name, $version = '', $usedJsLibs = '', $url = '', $author = '', $license = array())
    {
        $this->$lightboxType = $lightboxType;
        $this->name = $name;
        $this->version = $version;
        $this->usedJsLibs = $usedJsLibs;
        $this->url = $url;
        $this->author = $author;
        $this->license = $license;
    }

    public function writeAsTableRow ()
    {
        $result = '<tr>' . '<td><a href="' . $this->url . '">' . $this->name . '</a></td>' . '<td>' . $this->version . '</td>' . '<td>' . $this->usedJsLibs .
                 '</td>' . '<td>' . $this->author . '</td>' . '<td>';
        foreach ($this->license as $license => $licenseUrl) {
            $result = $result . '<a href="' . $licenseUrl . '">' . $license . '</a>';
        }
        $result = $result . '</td>';
        $result = $result . '</tr>';
        return $result;
    }

    public static function writeTableHeader ()
    {
        $result = '<tr>' . '<th>' . JText::_('MOSIMAGE_LIGHTBOX_NAME') . '</th>' . '<th>' . JText::_('MOSIMAGE_LIGHTBOX_VERSION') . '</th>' . '<th>' .
                 JText::_('MOSIMAGE_LIGHTBOX_USED_JS_LIBS') . '</th>' . '<th>' . JText::_('MOSIMAGE_LIGHTBOX_AUTHOR') . '</th>' . '<th>' .
                 JText::_('MOSIMAGE_LIGHTBOX_LICENSE') . '</td>' . '<tr>';
        return $result;
    }
}

?>