<?php defined('_JEXEC') or die('Restricted access');

/**
 * @version 2.0 $Id: MosimageFromFolder.php,v 1.3 2014-03-12 22:15:07 harry Exp $
 * @package Joomla
 * @subpackage H2N Mosimage Component
 * @copyright (C) 2010-2012 Harry Klein - www.joomla-hklein.de
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 *
 * H2N Mosimage Component is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * H2N Mosimage Component is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class MosimageDirProperties {

	const MAX_AMOUNT_IMAGES = 30;
	
	const KEY_RANDOM = 'random';
	const KEY_FOLDER = 'folder';
	const KEY_TITLE = 'title';
	const KEY_BORDER = 'border';
	const KEY_IMAGE_ALIGN = 'align';
	const KEY_POS = 'pos';
	const KEY_COUNT_OF_IMAGES = '#';
	const DELEMITER = '=';

	private $param = array();

	/**
	 * Liefert die Information aus dem Platzhalter der Form
	 * {mosimage folder=[tt-hessen/module/Tanklager/] title=[Dauerleihgabe Tanklager] rand=[false] align=[left]}}
	 * @param string Platzhalter inkl. den arametern, der durch ein Bild oder mehrere Bilder ersetzt werden sollen.
	 */
	private function __construct($value) {
		preg_match_all('/[a-zA-Z0-9#]+' . self::DELEMITER . '\[(.*?)\]/', $value, $matches);
		if(!count($matches)) {
			return false;
		}
		foreach($matches[0] as $attr){
			$pieces = explode(self::DELEMITER, $attr);
			$this->param[$pieces[0]] = substr($pieces[1],1,-1);
		}
		//if (!array_key_exists(self::KEY_FOLDER,$this->param)){
		//	// throw new Exception ('Property "folder" wurde nicht angegeben. Gallerie kann nicht angezeigt werden');
		//}
	}
	
	/**
	 * 
	 * @return MosimageDirProperties
	 */
	public static function parse($value){
		$instance = new MosimageDirProperties($value);
		return $instance;
	}
	
	/**
	 * 
	 */
	public function getImgagePropertiesAsArrayObject(){
		try {
			$files = &$this->getFileNames();
			$result = array();
			foreach ($files as $file){
				$item = new stdClass();
				$item->source = $file;
				$item->align = $this->getImageAlign();
				$item->alt = $this->getTitle();
				$item->border = $this->getBorder();
				$item->caption = $this->getTitle();
				$item->caption_position = $this->getCaptionPosition();
				$result[] =  $item;
			}
			return $result;
	
		} catch (Exception $e){
			return false;
		}
		return false;
	}
	
	
	/**
	 * Name des Folders, aus dem das Image kommen soll
	 * @throws Exception Wenn Folder nicht angegeben
	 * @return Name des Folder
	 */
	private function getRelativeFolderName(){
		if (array_key_exists(self::KEY_FOLDER,$this->param)){
			return $this->param[self::KEY_FOLDER];
		}
		throw new Exception("Can't found parameter folder");
	}

	/**
	 * Name des Titels. Wenn nicht angegeben, so wird als Titel der Name des Folders zurückgegeben.
	 */
	private function getTitle(){
		if (array_key_exists(self::KEY_TITLE,$this->param)){
			return $this->param[self::KEY_TITLE];
		} else {
			return str_replace('_',' ', basename('images/'.$this->getRelativeFolderName()));
		}
	}
	
	private function getCaptionPosition(){
		if (array_key_exists(self::KEY_POS,$this->param)){
			return $this->param[self::KEY_POS];
		} 
		return '';
	}	
		
	/**
	 * Liefert die Breite der Rahmes um das Bild. Default ist 0 (Wert der der Konfiguration)
	 */
	private function getBorder(){
		if (array_key_exists(self::KEY_BORDER,$this->param)){
			return $this->param[self::KEY_BORDER];
		} else {
			return 0;
		}
	} 

	/**
	 * Liefert die Ausrichtung des Images. Gültige Werte sind left und right. Defaut ist left.
	 */
	private function getImageAlign(){
		if (array_key_exists(self::KEY_IMAGE_ALIGN,$this->param)){
			switch ($this->param[self::KEY_IMAGE_ALIGN]) {
				case 'left':
				case 'right':
				case 'none':
					return $this->param[self::KEY_IMAGE_ALIGN];				
			}
		} 
		return 'none';
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
	 *  (c)	random = true	=> ein zufääligs Bild
	 * 	(d1)	random = false  => entwerder cover.jpg oder 
	 * 	(d2)                       das 1. Bild
	 *  maxAmount > 1
	 *  (e)	random = true   => zufällig maxAmount 
	 * 	(f)	random = false  => die ersten maxAmount
	 *  
	 * 
	 * @throws Exception Wenn keine Image-Dateien im Ordner vorhanden sind.
	 */
	private function  getFileNames(){
		$folder = 'images/'.$this->getRelativeFolderName();
		
		jimport('joomla.filesystem.folder');
		if (!JFolder::exists($folder)){
			throw new Exception("Folder [$folder] don't exist.");
		}
		
		$files = array();
		
		// Abkürzung für d1
		if ( (!$this->isRandom()) && ($this->getMaxAmountImages() == 1) ){
			jimport('joomla.filesystem.file');
			if (JFile::exists($folder.'/cover.jpg')) {
				$files[] = $this->getRelativeFolderName().'/cover.jpg';
				return $files;
			}
		}
		
		// Abkürzung für d2
		if ( ($this->isRandom()) && ($this->getMaxAmountImages() == 1) ){
			if (JFolder::exists($folder)){
				foreach (JFolder::files($folder) as $file) {
					if ( preg_match( '/\.gif$|.\jpg$|\.jpeg$|\.png$/i', $file )) {
						$files[]= $this->getRelativeFolderName() . '/' . $file;
						return $files;
					}
				}
			}
		}
		
		if (JFolder::exists($folder)){
			$counter = 0;
			foreach (JFolder::files($folder) as $file) {
				if ( preg_match( '/\.gif$|.\jpg$|\.jpeg$|\.png$/i', $file )) {
					$files[]= $this->getRelativeFolderName() . '/' . $file;
					$counter++;
					// Vorzeiges Aussteigen bei f, wenn genügend Daten da
					if ( !$this->isRandom() && $counter == $this->getMaxAmountImages()){
						return $files;
					}
				}
			} 
		}
		
		if (count($files) == 0){
			$e = new Exception("Folder [$folder] don't contain any images.");
			throw $e;
		}
		
		// Ergebis von a und e
		if ($this->isRandom()){
			if ($this->getMaxAmountImages() == 0){
				$max = count($files);
			} else {
				$max = $this->getMaxAmountImages();
			}
			shuffle($files);
			return array_slice($files,0,$max);
		}
		// Ergebnis von b (wenn weniger Bilder da als gefordert) und f
		return $files;	
	}

	/** 
	 * Liefer true, wenn Image zufällig ausgewählt werden soll. Default ist false.
	 */
	private function isRandom(){
		if (!array_key_exists(self::KEY_RANDOM,$this->param)){
			return false;
		} else if ( array_key_exists(self::KEY_RANDOM,$this->param) && ( $this->param[self::KEY_RANDOM] == 'true') ){
			return  true;
		}
		return false;
	}
	
	/** 
	 * Liefert die maximale Anzahl Bilder, die angezeigt werden sollen. Default ist 1. Der Wert 0 steht für alle Bilder. Wenn der 
	 * Wert größer als MAX_AMOUNT_IMAGES ist, wird MAX_AMOUNT_IMAGES zurückgegeben.
	 *  
	 * @return maximale Anzahl Bilder, die angezeigt werden soll.
	 */
	private function getMaxAmountImages(){
		if (!array_key_exists(self::KEY_COUNT_OF_IMAGES,$this->param)){
			return 1;
		} else if ( array_key_exists(self::KEY_COUNT_OF_IMAGES,$this->param)){
			$value = intval( $this->param[self::KEY_COUNT_OF_IMAGES]);
		}
		if ($value < 0 ) { 
			return 1;
		}
		if ($value > self::MAX_AMOUNT_IMAGES){
			return self::MAX_AMOUNT_IMAGES;
		}
		return $value;
	}
	
	
}

?>
