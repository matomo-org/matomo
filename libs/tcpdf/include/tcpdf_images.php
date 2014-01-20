<?php
//============================================================+
// File name   : tcpdf_images.php
// Version     : 1.0.001
// Begin       : 2002-08-03
// Last Update : 2013-08-05
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2002-2013 Nicola Asuni - Tecnick.com LTD
//
// This file is part of TCPDF software library.
//
// TCPDF is free software: you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// TCPDF is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the License
// along with TCPDF. If not, see
// <http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT>.
//
// See LICENSE.TXT file for more information.
// -------------------------------------------------------------------
//
// Description :
//   Static image methods used by the TCPDF class.
//
//============================================================+

/**
 * @file
 * This is a PHP class that contains static image methods for the TCPDF class.<br>
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni
 * @version 1.0.001
 */

/**
 * @class TCPDF_IMAGES
 * Static image methods used by the TCPDF class.
 * @package com.tecnick.tcpdf
 * @brief PHP class for generating PDF documents without requiring external extensions.
 * @version 1.0.000
 * @author Nicola Asuni - info@tecnick.com
 */
class TCPDF_IMAGES {

	/**
	 * Array of hinheritable SVG properties.
	 * @since 5.0.000 (2010-05-02)
	 * @public static
	 */
	public static $svginheritprop = array('clip-rule', 'color', 'color-interpolation', 'color-interpolation-filters', 'color-profile', 'color-rendering', 'cursor', 'direction', 'fill', 'fill-opacity', 'fill-rule', 'font', 'font-family', 'font-size', 'font-size-adjust', 'font-stretch', 'font-style', 'font-variant', 'font-weight', 'glyph-orientation-horizontal', 'glyph-orientation-vertical', 'image-rendering', 'kerning', 'letter-spacing', 'marker', 'marker-end', 'marker-mid', 'marker-start', 'pointer-events', 'shape-rendering', 'stroke', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'stroke-opacity', 'stroke-width', 'text-anchor', 'text-rendering', 'visibility', 'word-spacing', 'writing-mode');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

	/**
	 * Return the image type given the file name or array returned by getimagesize() function.
	 * @param $imgfile (string) image file name
	 * @param $iminfo (array) array of image information returned by getimagesize() function.
	 * @return string image type
	 * @since 4.8.017 (2009-11-27)
	 * @public static
	 */
	public static function getImageFileType($imgfile, $iminfo=array()) {
		$type = '';
		if (isset($iminfo['mime']) AND !empty($iminfo['mime'])) {
			$mime = explode('/', $iminfo['mime']);
			if ((count($mime) > 1) AND ($mime[0] == 'image') AND (!empty($mime[1]))) {
				$type = strtolower(trim($mime[1]));
			}
		}
		if (empty($type)) {
			$fileinfo = pathinfo($imgfile);
			if (isset($fileinfo['extension']) AND (!TCPDF_STATIC::empty_string($fileinfo['extension']))) {
				$type = strtolower(trim($fileinfo['extension']));
			}
		}
		if ($type == 'jpg') {
			$type = 'jpeg';
		}
		return $type;
	}

	/**
	 * Set the transparency for the given GD image.
	 * @param $new_image (image) GD image object
	 * @param $image (image) GD image object.
	 * return GD image object.
	 * @since 4.9.016 (2010-04-20)
	 * @public static
	 */
	public static function setGDImageTransparency($new_image, $image) {
		// transparency index
		$tid = imagecolortransparent($image);
		// default transparency color
		$tcol = array('red' => 255, 'green' => 255, 'blue' => 255);
		if ($tid >= 0) {
			// get the colors for the transparency index
			$tcol = imagecolorsforindex($image, $tid);
		}
		$tid = imagecolorallocate($new_image, $tcol['red'], $tcol['green'], $tcol['blue']);
		imagefill($new_image, 0, 0, $tid);
		imagecolortransparent($new_image, $tid);
		return $new_image;
	}

	/**
	 * Convert the loaded image to a PNG and then return a structure for the PDF creator.
	 * This function requires GD library and write access to the directory defined on K_PATH_CACHE constant.
	 * @param $image (image) Image object.
	 * return image PNG image object.
	 * @since 4.9.016 (2010-04-20)
	 * @public static
	 */
	public static function _toPNG($image) {
		// set temporary image file name
		$tempname = TCPDF_STATIC::getObjFilename('img');
		// turn off interlaced mode
		imageinterlace($image, 0);
		// create temporary PNG image
		imagepng($image, $tempname);
		// remove image from memory
		imagedestroy($image);
		// get PNG image data
		$retvars = self::_parsepng($tempname);
		// tidy up by removing temporary image
		unlink($tempname);
		return $retvars;
	}

	/**
	 * Convert the loaded image to a JPEG and then return a structure for the PDF creator.
	 * This function requires GD library and write access to the directory defined on K_PATH_CACHE constant.
	 * @param $image (image) Image object.
	 * @param $quality (int) JPEG quality.
	 * return image JPEG image object.
	 * @public static
	 */
	public static function _toJPEG($image, $quality) {
		$tempname = TCPDF_STATIC::getObjFilename('img');
		imagejpeg($image, $tempname, $quality);
		imagedestroy($image);
		$retvars = self::_parsejpeg($tempname);
		// tidy up by removing temporary image
		unlink($tempname);
		return $retvars;
	}

	/**
	 * Extract info from a JPEG file without using the GD library.
	 * @param $file (string) image file to parse
	 * @return array structure containing the image data
	 * @public static
	 */
	public static function _parsejpeg($file) {
		$a = getimagesize($file);
		if (empty($a)) {
			//Missing or incorrect image file
			return false;
		}
		if ($a[2] != 2) {
			// Not a JPEG file
			return false;
		}
		// bits per pixel
		$bpc = isset($a['bits']) ? intval($a['bits']) : 8;
		// number of image channels
		if (!isset($a['channels'])) {
			$channels = 3;
		} else {
			$channels = intval($a['channels']);
		}
		// default colour space
		switch ($channels) {
			case 1: {
				$colspace = 'DeviceGray';
				break;
			}
			case 3: {
				$colspace = 'DeviceRGB';
				break;
			}
			case 4: {
				$colspace = 'DeviceCMYK';
				break;
			}
			default: {
				$channels = 3;
				$colspace = 'DeviceRGB';
				break;
			}
		}
		// get file content
		$data = file_get_contents($file);
		// check for embedded ICC profile
		$icc = array();
		$offset = 0;
		while (($pos = strpos($data, "ICC_PROFILE\0", $offset)) !== false) {
			// get ICC sequence length
			$length = (TCPDF_STATIC::_getUSHORT($data, ($pos - 2)) - 16);
			// marker sequence number
			$msn = max(1, ord($data[($pos + 12)]));
			// number of markers (total of APP2 used)
			$nom = max(1, ord($data[($pos + 13)]));
			// get sequence segment
			$icc[($msn - 1)] = substr($data, ($pos + 14), $length);
			// move forward to next sequence
			$offset = ($pos + 14 + $length);
		}
		// order and compact ICC segments
		if (count($icc) > 0) {
			ksort($icc);
			$icc = implode('', $icc);
			if ((ord($icc{36}) != 0x61) OR (ord($icc{37}) != 0x63) OR (ord($icc{38}) != 0x73) OR (ord($icc{39}) != 0x70)) {
				// invalid ICC profile
				$icc = false;
			}
		} else {
			$icc = false;
		}
		return array('w' => $a[0], 'h' => $a[1], 'ch' => $channels, 'icc' => $icc, 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'DCTDecode', 'data' => $data);
	}

	/**
	 * Extract info from a PNG file without using the GD library.
	 * @param $file (string) image file to parse
	 * @return array structure containing the image data
	 * @public static
	 */
	public static function _parsepng($file) {
		$f = @fopen($file, 'rb');
		if ($f === false) {
			// Can't open image file
			return false;
		}
		//Check signature
		if (fread($f, 8) != chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10)) {
			// Not a PNG file
			return false;
		}
		//Read header chunk
		fread($f, 4);
		if (fread($f, 4) != 'IHDR') {
			//Incorrect PNG file
			return false;
		}
		$w = TCPDF_STATIC::_freadint($f);
		$h = TCPDF_STATIC::_freadint($f);
		$bpc = ord(fread($f, 1));
		$ct = ord(fread($f, 1));
		if ($ct == 0) {
			$colspace = 'DeviceGray';
		} elseif ($ct == 2) {
			$colspace = 'DeviceRGB';
		} elseif ($ct == 3) {
			$colspace = 'Indexed';
		} else {
			// alpha channel
			fclose($f);
			return 'pngalpha';
		}
		if (ord(fread($f, 1)) != 0) {
			// Unknown compression method
			fclose($f);
			return false;
		}
		if (ord(fread($f, 1)) != 0) {
			// Unknown filter method
			fclose($f);
			return false;
		}
		if (ord(fread($f, 1)) != 0) {
			// Interlacing not supported
			fclose($f);
			return false;
		}
		fread($f, 4);
		$channels = ($ct == 2 ? 3 : 1);
		$parms = '/DecodeParms << /Predictor 15 /Colors '.$channels.' /BitsPerComponent '.$bpc.' /Columns '.$w.' >>';
		//Scan chunks looking for palette, transparency and image data
		$pal = '';
		$trns = '';
		$data = '';
		$icc = false;
		do {
			$n = TCPDF_STATIC::_freadint($f);
			$type = fread($f, 4);
			if ($type == 'PLTE') {
				// read palette
				$pal = TCPDF_STATIC::rfread($f, $n);
				fread($f, 4);
			} elseif ($type == 'tRNS') {
				// read transparency info
				$t = TCPDF_STATIC::rfread($f, $n);
				if ($ct == 0) {
					$trns = array(ord($t{1}));
				} elseif ($ct == 2) {
					$trns = array(ord($t{1}), ord($t{3}), ord($t{5}));
				} else {
					$pos = strpos($t, chr(0));
					if ($pos !== false) {
						$trns = array($pos);
					}
				}
				fread($f, 4);
			} elseif ($type == 'IDAT') {
				// read image data block
				$data .= TCPDF_STATIC::rfread($f, $n);
				fread($f, 4);
			} elseif ($type == 'iCCP') {
				// skip profile name
				$len = 0;
				while ((ord(fread($f, 1)) > 0) AND ($len < 80)) {
					++$len;
				}
				// skip null separator
				fread($f, 1);
				// get compression method
				if (ord(fread($f, 1)) != 0) {
					// Unknown filter method
					fclose($f);
					return false;
				}
				// read ICC Color Profile
				$icc = TCPDF_STATIC::rfread($f, ($n - $len - 2));
				// decompress profile
				$icc = gzuncompress($icc);
				fread($f, 4);
			} elseif ($type == 'IEND') {
				break;
			} else {
				TCPDF_STATIC::rfread($f, $n + 4);
			}
		} while ($n);
		if (($colspace == 'Indexed') AND (empty($pal))) {
			// Missing palette
			fclose($f);
			return false;
		}
		fclose($f);
		return array('w' => $w, 'h' => $h, 'ch' => $channels, 'icc' => $icc, 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'FlateDecode', 'parms' => $parms, 'pal' => $pal, 'trns' => $trns, 'data' => $data);
	}

} // END OF TCPDF_IMAGES CLASS

//============================================================+
// END OF FILE
//============================================================+
