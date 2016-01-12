<?php
/**
 * @package Joomla.Plugin
 * @subpackage Content.Mosimage
 * @copyright (C) 2010-2014 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class MosimageDirProperties
{

    const MAX_AMOUNT_IMAGES = 30;

    const KEY_RANDOM = 'random';

    const KEY_FOLDER = 'folder';

    const KEY_TITLE = 'title';

    const KEY_BORDER = 'border';

    const KEY_IMAGE_ALIGN = 'align';

    const KEY_POS = 'pos';

    const KEY_COUNT_OF_IMAGES = '#';

    const DELEMITER = '=';
    
    const FILE_FILTER = '\.[gG][iI][fF]$|\.[jJ][pP][gG]$|\.[jJ][pP][eE][gG]$|\.[pP][nN][gG]$';

    private $param = array();

    /**
     * Object wird mittels @see MosimageDirProperties#parse($value) erzeugt. 
     */
    private function __construct ($value)
    {
        preg_match_all('/[a-zA-Z0-9#]+' . self::DELEMITER . '\[(.*?)\]/', $value, $matches);
        if (! count($matches)) {
            return false;
        }
        foreach ($matches[0] as $attr) {
            $pieces = explode(self::DELEMITER, $attr);
            $this->param[$pieces[0]] = substr($pieces[1], 1, - 1);
        }
        // if (!array_key_exists(self::KEY_FOLDER,$this->param)){
        // // throw new Exception ('Property "folder" wurde nicht angegeben. Gallerie kann nicht angezeigt werden');
        // }
    }

    /**
     * Liefert die Information aus dem Platzhalter der Form
     * {mosimage folder=[tt-hessen/module/Tanklager/] title=[Dauerleihgabe Tanklager] random=[false] align=[left] border=[1] pos=[] #=[5]}}
     * 
     * folder  Ordner relativ zu images/, der die Bilder enthält.
     * title   (optional) Titel aller Bilder. Default ist der Name der folders.
     * random  (optional) Bei true ist Reihenfolge der Bilder zufällig, ansonten nach Filename aufsteigend sortiert. Default ist true.
     * align
     * border
     * pos
     * #       miximale Anzahl Bilder, default ist 1
     * 
     * @param string Platzhalter inkl. den Parametern, der durch ein Bild oder mehrere Bilder ersetzt werden sollen.
     *
     * @return MosimageDirProperties
     */
    public static function parse ($value)
    {
        $instance = new MosimageDirProperties($value);
        return $instance;
    }

    /**
     * 
     */
    public function getImgagePropertiesAsArrayObject ()
    {
        try {
            $files = &$this->getFileNames();
            $result = array();
            foreach ($files as $file) {
                $item = new stdClass();
                $item->source = $file;
                $item->align = $this->getImageAlign();
                $item->alt = $this->getTitle();
                $item->border = $this->getBorder();
                $item->caption = $this->getTitle();
                $item->caption_position = $this->getCaptionPosition();
                $result[] = $item;
            }
            return $result;
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Name des Folders, aus dem das Image kommen soll
     * @throws Exception Wenn Folder nicht angegeben
     * @return Name des Folder
     */
    private function getRelativeFolderName ()
    {
        if (array_key_exists(self::KEY_FOLDER, $this->param)) {
            return $this->param[self::KEY_FOLDER];
        }
        throw new Exception("Can't found parameter folder");
    }

    /**
     * Name des Titels. Wenn nicht angegeben, so wird als Titel der Name des Folders zurückgegeben.
     */
    private function getTitle ()
    {
        if (array_key_exists(self::KEY_TITLE, $this->param)) {
            return $this->param[self::KEY_TITLE];
        } else {
            return str_replace('_', ' ', basename('images/' . $this->getRelativeFolderName()));
        }
    }

    private function getCaptionPosition ()
    {
        if (array_key_exists(self::KEY_POS, $this->param)) {
            return $this->param[self::KEY_POS];
        }
        return '';
    }

    /**
     * Liefert die Breite der Rahmes um das Bild. Default ist 0 (Wert der der Konfiguration)
     */
    private function getBorder ()
    {
        if (array_key_exists(self::KEY_BORDER, $this->param)) {
            return $this->param[self::KEY_BORDER];
        } else {
            return 0;
        }
    }

    /**
     * Liefert die Ausrichtung des Images. Gültige Werte sind left und right. Defaut ist left.
     */
    private function getImageAlign ()
    {
        if (array_key_exists(self::KEY_IMAGE_ALIGN, $this->param)) {
            switch ($this->param[self::KEY_IMAGE_ALIGN]) {
                case 'left':
                case 'right':
                case 'none':
                    return $this->param[self::KEY_IMAGE_ALIGN];
            }
        }
        return 'left';
    }

    /**
     * Liefert eine oder mehere Dateinahmen (mit den Endungen gif,jpg,jpeg und .png) aus dem Ordner
     * 'images/'. $this->getRelativeFolderName().
     * 
     * random = true
     * random = false
     * 
     *  maxAmount = 0
     *  (a)	random = true	=> alle sortiert
     * 	(b)	random = false  => alle, aber zuällig
     *  maxAmount = 1 
     *  (c)	random = true	=> ein zufälliges Bild
     * 	(d1)random = false  => entwerder cover.jpg oder 
     * 	(d2)                => das 1. Bild
     *  maxAmount > 1
     *  (e)	random = true   => zufällig maxAmount 
     * 	(f)	random = false  => die ersten maxAmount
     *  
     * 
     * @throws Exception Wenn keine Image-Dateien im Ordner vorhanden sind.
     */
    private function getFileNames ()
    {
        $folder = 'images/' . $this->getRelativeFolderName();
        
        jimport('joomla.filesystem.folder');
        if (! JFolder::exists($folder)) {
            throw new Exception("Folder [$folder] don't exist.");
        }
        
        $files = array();
                
        if ($this->getMaxAmountImages() == 1){  // c, d1, d2
            if ( $this->isRandom()) { // c
                $allFiles = JFolder::files($folder,self::FILE_FILTER);
                shuffle($allFiles);
                foreach ($allFiles as $file) {
                    $files[] = $this->getRelativeFolderName() . '/' . $file;
                    return $files;
                }
            } else {                            // d1, d2
                jimport('joomla.filesystem.file');
                if (JFile::exists($folder . '/cover.jpg')) {
                    $files[] = $this->getRelativeFolderName() . '/cover.jpg';
                    return $files;
                }
                foreach (JFolder::files($folder,self::FILE_FILTER) as $file) {
                    $files[] = $this->getRelativeFolderName() . '/' . $file;
                    return $files;
                }
            }
            throw new Exception("Folder [$folder] don't exist.");
        }
        
        // a, b, e, f
        $f = JFolder::files($folder, self::FILE_FILTER);
        if ($this->isRandom()) {
            shuffle($f);
        } 
        $f = array_slice($f,0,$this->getMaxAmountImages());
        
        foreach ($f as $file) {
            $files[] = $this->getRelativeFolderName() . '/' . $file;
        }
        
        if (count($files) == 0) {
            throw  new Exception("Folder [$folder] don't contain any images.");            
        }
        return $files;
    }

    /** 
     * Liefer true, wenn Image zufällig ausgewählt werden soll. Default ist false.
     */
    private function isRandom ()
    {
        if (! array_key_exists(self::KEY_RANDOM, $this->param)) {
            return false;
        } else 
            if (array_key_exists(self::KEY_RANDOM, $this->param) && ($this->param[self::KEY_RANDOM] == 'true')) {
                return true;
            }
        return false;
    }

    /** 
     * Liefert die maximale Anzahl Bilder, die angezeigt werden sollen. Default ist 1. 
     * Der maximale Wert ist self::MAX_AMOUNT_IMAGES  
     *  
     * @return maximale Anzahl Bilder, die angezeigt werden soll.
     */
    private function getMaxAmountImages ()
    {
        if (! array_key_exists(self::KEY_COUNT_OF_IMAGES, $this->param)) {
            return 1;
        } else 
            if (array_key_exists(self::KEY_COUNT_OF_IMAGES, $this->param)) {
                $value = intval($this->param[self::KEY_COUNT_OF_IMAGES]);
            }
        if ($value < 0) {
            return 1;
        }
        if (($value > self::MAX_AMOUNT_IMAGES) || ($value == 0 )) {
            return self::MAX_AMOUNT_IMAGES;
        }
        return $value;
    }
}

?>
