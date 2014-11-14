<?php

class ImageDisplayProperties{

    private $displayX;
    private $displayY;
    private $file;

    const MAX_X = 1500;
    const MAX_Y = 1000;

    public function __construct($displayX, $displayY, $file, $screenres=array(1024, 768)){
        $xpos=strpos($displayX, '%');
        $ypos=strpos($displayY, '%');
        if($xpos) {
            if(!isset($screenres)) {
                $screenres = array(1024, 768);
            }
            $this->setDisplayX( $screenres[0] * substr($displayX, 0, $xpos) / 100);
        } else {
            $this->setDisplayX($displayX);
        }

        if( $ypos ) {
            if(!isset($screenres)) {
                $screenres = array(1024, 768);
            }
            $this->displayY = $screenres[1] * substr($displayY, 0, $ypos) / 100;
        } else {
            $this->setDisplayY($displayY);
        }
        $this->file = $file;
    }

    private function setDisplayX($value){
        $this->displayX = $this->fixSizeValue($value, self::MAX_X);
    }

    private function setDisplayY($value){
        $this->displayY = $this->fixSizeValue($value, self::MAX_Y);
    }


    private function fixSizeValue($value, $maxValue){
        $result = intval($value);
        if ($result < 0){
            $result = 0;
        }
        if ($result > $maxValue){
            $result = $maxValue;
        }
        return $result;
    }

    public function displayWidth(){
        return $this->displayX;
    }

    public function displayHeight(){
        return $this->displayY;
    }

    public function file(){
        return $this->file;
    }
}