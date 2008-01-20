<?php
/*
 * Sparkline PHP Graphing Library
 * Copyright 2004 James Byers <jbyers@users.sf.net>
 * http://sparkline.org
 *
 * Sparkline is distributed under a BSD License.  See LICENSE for details.
 *
 * $Id: Sparkline.php,v 1.8 2005/05/02 20:25:47 jbyers Exp $
 *
 */

define('TEXT_TOP',    1);
define('TEXT_RIGHT',  2);
define('TEXT_BOTTOM', 3);
define('TEXT_LEFT',   4);

define('FONT_1', 1);
define('FONT_2', 2);
define('FONT_3', 3);
define('FONT_4', 4);
define('FONT_5', 5);

require_once('Object.php');

class Sparkline extends Object {

  var $imageX;
  var $imageY;
  var $imageHandle;
  var $graphAreaPx;
  var $graphAreaPt;
  var $colorList;
  var $colorBackground;
  var $lineSize;

  ////////////////////////////////////////////////////////////////////////////
  // constructor
  //
  function Sparkline($catch_errors = true) {
    parent::Object($catch_errors);

    $this->colorList       = array();
    $this->colorBackground = 'white';
    $this->lineSize        = 1;
    $this->graphAreaPx = array(array(0, 0), array(0, 0)); // px(L, B), px(R, T)
  } // function Sparkline

  ////////////////////////////////////////////////////////////////////////////
  // init
  //
  function Init($x, $y) {
    $this->Debug("Sparkline :: Init($x, $y)", DEBUG_CALLS);

    $this->imageX    = $x;
    $this->imageY    = $y;

    // Set functions may have already set graphAreaPx offsets; add image dimensions
    //
    $this->graphAreaPx = array(array($this->graphAreaPx[0][0],
                                   $this->graphAreaPx[0][1]),
                             array($this->graphAreaPx[1][0] + $x - 1,
                                   $this->graphAreaPx[1][1] + $y - 1));
    
    $this->imageHandle = $this->CreateImageHandle($x, $y);

    // load default colors; set all color handles
    //
    $this->SetColorDefaults();
    while (list($k, $v) = each($this->colorList)) {
      $this->SetColorHandle($k, $this->DrawColorAllocate($k, $this->imageHandle));
    }
    reset($this->colorList);

    if ($this->IsError()) {
      return false;
    } else {
      return true;
    }
  } // function Init

  ////////////////////////////////////////////////////////////////////////////
  // color, drawing setup functions
  //
  function SetColor($name, $r, $g, $b) {
    $this->Debug("Sparkline :: SetColor('$name', $r, $g, $b)", DEBUG_SET);
    $name = strtolower($name);
    $this->colorList[$name] = array('rgb' => array($r, $g, $b));
  } // function SetDecColor

  function SetColorHandle($name, $handle) {
    $this->Debug("Sparkline :: SetColorHandle('$name', $handle)", DEBUG_SET);
    $name = strtolower($name);
    if (array_key_exists($name, $this->colorList)) {
      $this->colorList[$name]['handle'] = $handle;
      return true;
    } else {
      return false;
    }
  } // function SetColorHandle

  function SetColorHex($name, $r, $g, $b) {
    $this->Debug("Sparkline :: SetColorHex('$name', $r, $g, $b)", DEBUG_SET);
    $this->SetColor($name, hexdec($r), hexdec($g), hexdec($b));
  } // function SetHexColor

  function SetColorHtml($name, $rgb) {
    $this->Debug("Sparkline :: SetColorHtml('$name', '$rgb')", DEBUG_SET);
    $rgb = trim($rgb, '#');
    $this->SetColor($name, hexdec(substr($rgb, 0, 2)), hexdec(substr($rgb, 2, 2)), hexdec(substr($rgb, 4, 2)));
  } // function SetHexColor

  function SetColorBackground($name) {
    $this->Debug("Sparkline :: SetColorBackground('$name')", DEBUG_SET);
    $this->colorBackground = $name;
  } // function SetColorBackground

  function GetColor($name) {
    if (array_key_exists($name, $this->colorList)) {
      return $this->colorList[$name]['rgb'];
    } else {
      return false;
    }
  } // function GetColor

  function GetColorHandle($name) {
    $name = strtolower($name);
    if (array_key_exists($name, $this->colorList)) {
      return $this->colorList[$name]['handle'];
    } else {
      $this->Debug("Sparkline :: GetColorHandle color '$name' not set", DEBUG_WARNING);
      return false;
    }
  } // function GetColorHandle

  function SetColorDefaults() {
    $this->Debug("Sparkline :: SetColorDefaults()", DEBUG_SET);
    $colorDefaults = array(array('aqua',   '#00FFFF'),
                           array('black',  '#010101'), // TODO failure if 000000?
                           array('blue',   '#0000FF'),
                           array('fuscia', '#FF00FF'),
                           array('gray',   '#808080'),
                           array('grey',   '#808080'),
                           array('green',  '#008000'),
                           array('lime',   '#00FF00'),
                           array('maroon', '#800000'),
                           array('navy',   '#000080'),
                           array('olive',  '#808000'),
                           array('purple', '#800080'),
                           array('red',    '#FF0000'),
                           array('silver', '#C0C0C0'),
                           array('teal',   '#008080'),
                           array('white',  '#FFFFFF'),
                           array('yellow', '#FFFF00'));
    while (list(, $v) = each($colorDefaults)) {
      if (!array_key_exists($v[0], $this->colorList)) {
        $this->SetColorHtml($v[0], $v[1]);
      }
    }
  } // function SetColorDefaults

  function SetLineSize($size) {
    $this->Debug("Sparkline :: SetLineSize($size)", DEBUG_CALLS);

    $this->lineSize = $size;
  } // function SetLineSize

  function GetLineSize() {
    return($this->lineSize);
  } // function GetLineSize

  function SetPadding($T, $R = null, $B = null, $L = null) {
    $this->Debug("Sparkline :: SetPadding($T, $R, $B, $L)", DEBUG_CALLS);

    if (null == $R &&
        null == $B &&
        null == $L) {
      $this->graphAreaPx = array(array($this->graphAreaPx[0][0] + $T,
                                       $this->graphAreaPx[0][1] + $T),
                                 array($this->graphAreaPx[1][0] - $T,
                                       $this->graphAreaPx[1][1] - $T));
    } else {
      $this->graphAreaPx = array(array($this->graphAreaPx[0][0] + $L,
                                       $this->graphAreaPx[0][1] + $B),
                                 array($this->graphAreaPx[1][0] - $R,
                                       $this->graphAreaPx[1][1] - $T));
    }
  } // function SetPadding

  ////////////////////////////////////////////////////////////////////////////
  // canvas setup
  //
  function CreateImageHandle($x, $y) {
    $this->Debug("Sparkline :: CreateImageHandle($x, $y)", DEBUG_CALLS);
	if(function_exists('imagecreatetruecolor'))
	{
	    $handle = imagecreatetruecolor($x, $y);
	}
	elseif(function_exists('imagecreate'))
	{		
      $handle = imagecreate($x, $y);
	}
	else
	{
		echo "You need at least imagecreate()";exit;
	}
    if (!is_resource($handle)) {
      $this->Debug('imagecreatetruecolor unavailable', DEBUG_WARNING);
    }

    if (!is_resource($handle)) {
      $this->Debug('imagecreate unavailable', DEBUG_WARNING);
      $this->Error('could not create image; GD imagecreate functions unavailable');
    }

    return $handle;
  } // function CreateImageHandle

  ////////////////////////////////////////////////////////////////////////////
  // drawing primitives
  //
  // NB: all drawing primitives use the coordinate system where (0,0) 
  //     corresponds to the bottom left of the image, unlike y-inverted 
  //     PHP gd functions
  //
  function DrawBackground($handle = false) {
    $this->Debug("Sparkline :: DrawBackground()", DEBUG_DRAW);

    if (!$this->IsError()) {
      if ($handle === false) $handle = $this->imageHandle;
      return $this->DrawRectangleFilled(0, 
                                        0, 
                                        imagesx($handle) - 1,
                                        imagesy($handle) - 1,
                                        $this->colorBackground,
                                        $handle);
    }
  } // function DrawBackground

  function DrawColorAllocate($color, $handle = false) {
    $this->Debug("Sparkline :: DrawColorAllocate('$color')", DEBUG_DRAW);

    if (!$this->IsError() &&
        $colorRGB = $this->GetColor($color)) {
      if ($handle === false) $handle = $this->imageHandle;
      return imagecolorallocate($handle,
                                $colorRGB[0], 
                                $colorRGB[1], 
                                $colorRGB[2]);
    }
  } // function DrawColorAllocate

  function DrawFill($x, $y, $color, $handle = false) {
    $this->Debug("Sparkline :: DrawFill($x, $y, '$color')", DEBUG_DRAW);

    if (!$this->IsError() &&
        $colorHandle = $this->GetColorHandle($color)) {
      if ($handle === false) $handle = $this->imageHandle;
      return imagefill($handle,
                       $x, 
                       $this->TxGDYToSLY($y, $handle), 
                       $colorHandle);
    }
  } // function DrawFill

  function DrawLine($x1, $y1, $x2, $y2, $color, $thickness = 1, $handle = false) {
    $this->Debug("Sparkline :: DrawLine($x1, $y1, $x2, $y2, '$color', $thickness)", DEBUG_DRAW);

    if (!$this->IsError() &&
        $colorHandle = $this->GetColorHandle($color)) {
      if ($handle === false) $handle = $this->imageHandle;

      imagesetthickness($handle, $thickness);
      $result = imageline($handle, 
                          $x1,
                          $this->TxGDYToSLY($y1, $handle),
                          $x2,
                          $this->TxGDYToSLY($y2, $handle),
                          $colorHandle);
      imagesetthickness($handle, 1);
      return $result;
    }
  } // function DrawLine

  function DrawPoint($x, $y, $color, $handle = false) {
    $this->Debug("Sparkline :: DrawPoint($x, $y, '$color')", DEBUG_DRAW);

    if (!$this->IsError() &&
        $colorHandle = $this->GetColorHandle($color)) {
      if ($handle === false) $handle = $this->imageHandle;
      return imagesetpixel($handle, 
                           $x, 
                           $this->TxGDYToSLY($y, $handle), 
                           $colorHandle);
    }
  } // function DrawPoint

  function DrawRectangle($x1, $y1, $x2, $y2, $color, $handle = false) {
    $this->Debug("Sparkline :: DrawRectangle($x1, $y1, $x2, $y2 '$color')", DEBUG_DRAW);

    if (!$this->IsError() &&
        $colorHandle = $this->GetColorHandle($color)) {
      if ($handle === false) $handle = $this->imageHandle;
      return imagerectangle($handle, 
                            $x1, 
                            $this->TxGDYToSLY($y1, $handle), 
                            $x2, 
                            $this->TxGDYToSLY($y2, $handle), 
                            $colorHandle);
    }
  } // function DrawRectangle

  function DrawRectangleFilled($x1, $y1, $x2, $y2, $color, $handle = false) {
    $this->Debug("Sparkline :: DrawRectangleFilled($x1, $y1, $x2, $y2 '$color')", DEBUG_DRAW);

    if (!$this->IsError() &&
        $colorHandle = $this->GetColorHandle($color)) {
      // NB: switch y1, y2 post conversion
      //
      if ($y1 < $y2) {
        $yt = $y1;
        $y1 = $y2;
        $y2 = $yt;
      }

      if ($handle === false) $handle = $this->imageHandle;
      return imagefilledrectangle($handle, 
                                  $x1,
                                  $this->TxGDYToSLY($y1, $handle),
                                  $x2,
                                  $this->TxGDYToSLY($y2, $handle),
                                  $colorHandle);
    }
  } // function DrawRectangleFilled

  function DrawCircleFilled($x, $y, $diameter, $color, $handle = false) {
    $this->Debug("Sparkline :: DrawCircleFilled($x, $y, $diameter, '$color')", DEBUG_DRAW);

    if (!$this->IsError() &&
        $colorHandle = $this->GetColorHandle($color)) {
      if ($handle === false) $handle = $this->imageHandle;
      return imagefilledellipse($handle, 
                                $x,
                                $this->TxGDYToSLY($y, $handle),
                                $diameter,
                                $diameter,
                                $colorHandle);
    }
  } // function DrawCircleFilled

  function DrawText($string, $x, $y, $color, $font = FONT_1, $handle = false) {
    $this->Debug("Sparkline :: DrawText('$string', $x, $y, '$color', $font)", DEBUG_DRAW);
      
    if (!$this->IsError() &&
        $colorHandle = $this->GetColorHandle($color)) {
      // adjust for font height so x,y corresponds to bottom left of font
      //
      if ($handle === false) $handle = $this->imageHandle;
      return imagestring($handle, 
                         $font, 
                         $x,
                         $this->TxGDYToSLY($y + imagefontheight($font), $handle),
                         $string,
                         $colorHandle);
    }
  } // function DrawText

  function DrawTextRelative($string, $x, $y, $color, $position, $padding = 2, $font = FONT_1, $handle = false) {
    $this->Debug("Sparkline :: DrawTextRelative('$string', $x, $y, '$color', $position, $font, $padding)", DEBUG_DRAW);
      
    if (!$this->IsError() &&
        $colorHandle = $this->GetColorHandle($color)) {
      if ($handle === false) $handle = $this->imageHandle;

      // rendered text width, height
      //
      $textHeight = imagefontheight($font);
      $textWidth  = imagefontwidth($font) * strlen($string);

      // set (pxX, pxY) based on position and point
      //
      switch($position) {
      case TEXT_TOP:
        $x = $x - round($textWidth / 2);
        $y = $y + $padding;
        break;
        
      case TEXT_RIGHT:
        $x = $x + $padding;
        $y = $y - round($textHeight / 2);
        break;
        
      case TEXT_BOTTOM:
        $x = $x - round($textWidth / 2);
        $y = $y - $padding - $textHeight;
        break;
        
      case TEXT_LEFT:
      default:
        $x = $x - $padding - $textWidth;
        $y = $y - round($textHeight / 2);
        break;
      }

      // truncate bounds based on string size in pixels, image bounds
      // order: TRBL
      //
      $y = min($y, $this->GetImageHeight() - $textHeight);
      $x = min($x, $this->GetImageWidth() - $textWidth);
      $y = max($y, 0);
      $x = max($x, 0);

      return $this->DrawText($string,
                             $x,
                             $y,
                             $color,
                             $font,
                             $handle);
    }
  } // function DrawTextRelative

  function DrawImageCopyResampled($dhandle, $shandle, $dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh) {
    $this->Debug("Sparkline :: DrawImageCopyResampled($dhhandle, $shandle, $dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh)", DEBUG_DRAW);
    if (!$this->IsError()) {
      return imagecopyresampled($dhandle,  // dest handle
                                $shandle,  // src  handle
                                $dx, $dy,  // dest x, y
                                $sx, $sy,  // src  x, y
                                $dw, $dh,  // dest w, h
                                $sw, $sh); // src  w, h
    }
  } // function DrawImageCopyResampled
  
  ////////////////////////////////////////////////////////////////////////////
  // coordinate system functions
  //   world coordinates are referenced as points or pt
  //   graph coordinates are referenced as pixels or px
  //   sparkline inverts GD Y pixel coordinates; the bottom left of the 
  //     image rendering area is px(0,0)
  //   all coordinate transformation functions are prefixed with Tx
  //   all coordinate transformation functions depend on a valid image handle
  //     and will only return valid results after all Set* calls are performed
  //
  function TxGDYToSLY($gdY, $handle) {
    return imagesy($handle) - 1 - $gdY;
  } // function TxGDYToSLY

  function TxPxToPt($pxX, $pxY, $handle) {
    // TODO;  must occur after data series conversion
  } // function TxPxToPt

  function TxPtToPx($ptX, $ptY, $handle) {
    // TODO;  must occur after data series conversion
  } // function TxPtToPx

  function GetGraphWidth() {
    return $this->graphAreaPx[1][0] - $this->graphAreaPx[0][0];
  } // function GetGraphWidth

  function GetGraphHeight() {
    return $this->graphAreaPx[1][1] - $this->graphAreaPx[0][1];
  } // function GetGraphHeight

  function GetImageWidth() {
    return $this->imageX;
  } // function GetImageWidth

  function GetImageHeight() {
    return $this->imageY;
  } // function GetImageHeight

  ////////////////////////////////////////////////////////////////////////////
  // image output
  //
  function Output($file = '') {

    $this->Debug("Sparkline :: Output($file)", DEBUG_CALLS);

    if ($this->IsError()) {
      $colorError = imagecolorallocate($this->imageHandle, 0xFF, 0x00, 0x00);
      imagestring($this->imageHandle, 
                  1, 
                  ($this->imageX / 2) - (5 * imagefontwidth(1) / 2), 
                  ($this->imageY / 2) - (imagefontheight(1) / 2), 
                  "ERROR", 
                  $colorError);
    }

    if ($file == '') {
      header('Content-type: image/png');
      imagepng($this->imageHandle);
    } else {
      imagepng($this->imageHandle, $file);
    }

    $this->Debug('Sparkline :: Output - total execution time: ' . round($this->microTimer() - $this->startTime, 4) . ' seconds', DEBUG_STATS);
  } // function Output

  function OutputToFile($file) {
    $this->Output($file);
  } // function OutputToFile

} // class Sparkline

?>
