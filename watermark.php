

<?php

function getTypographicHeights ($pFont, $pSize) {
	$bBox = imagettfbbox($pSize, 0, $pFont, "x");
	$xLine = $bBox[1] - $bBox[7];
	$bBox = imagettfbbox($pSize, 0, $pFont, "p");
	$pLine = $xLine - $bBox[1] + $bBox[7];
	$bBox = imagettfbbox($pSize, 0, $pFont, "k");
	$kLine = $bBox[1] - $bBox[7];
	$bBox = imagettfbbox($pSize, 0, $pFont, "H");
	$hLine = $bBox[1] - $bBox[7];
	$bBox = imagettfbbox($pSize, 0, $pFont, "ÁÂÃÄÅĂČ");
	$accLine = $bBox[1] - $bBox[7];
	return array($pLine, 0, $xLine, $hLine, $kLine, $accLine);
}
function getKerningOffset ($pFont, $pSize, $pText) {
	$bBox = imagettfbbox($pSize, 0, $pFont, "  ");
	$sWidth = $bBox[2] - $bBox[0];
	$bBox = imagettfbbox($pSize, 0, $pFont, "  " . $pText);
	$width = $bBox[2] - $bBox[0] - $sWidth;
	$bBox = imagettfbbox($pSize, 0, $pFont, $pText);
	$kerning = $bBox[2] - $bBox[0] - $width;
	return $kerning;
}




$size = 24;
$fontFile='/usr/share/fonts/truetype/msttcorefonts/arial.ttf';
$text = 'Ein Test mit y ß ü Ö Ä';
$textwerte = imagettfbbox ( $size, 0, $fontFile, $text );

$textwerte[2] += 8;

$textwerte[5] = abs ( $textwerte[5] );

$textwerte[5] += 4;

$image=imagecreate ( $textwerte[2], $textwerte[5] );

$farbe_body=imagecolorallocate ( $image, 222, 222, 222);

$farbe_b = imagecolorallocate ( $image, 10, 36, 106);

$textwerte[5] -= 2;

imagettftext ( $image, $size, 0, 4, $textwerte[5], $farbe_b,
               $fontFile, $text);
 
imagepng ( $image, 'image.png' );

imagedestroy ( $image );


/*

$bBox = imagettfbbox($size, 0, $font, $text);
$lines = getTypographicHeights($font, $size);
$kerning = getKerningOffset($font, $size, $text);
$accentLine = $lines[5];
$pLine = $lines[0];
$img = imagecreatetruecolor($bBox[2] - $bBox[0], $accentLine - $pLine);
$backgnd = imagecolorallocate($img, 255, 255, 160);
$color = imagecolorallocate($img, 128, 0, 0);

imagefill($img, 0, 0, $backgnd);
imagettftext($img, $size, 0, $kerning, $accentLine, $color, $font, $text);
*/
$ext = pathinfo('/tmp/resr.txt', PATHINFO_EXTENSION);
echo $ext;

?> 
