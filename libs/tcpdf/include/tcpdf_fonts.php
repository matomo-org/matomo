<?php
//============================================================+
// File name   : tcpdf_fonts.php
// Version     : 1.0.010
// Begin       : 2008-01-01
// Last Update : 2013-11-10
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2008-2013 Nicola Asuni - Tecnick.com LTD
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
// You should have received a copy of the GNU Lesser General Public License
// along with TCPDF.  If not, see <http://www.gnu.org/licenses/>.
//
// See LICENSE.TXT file for more information.
// -------------------------------------------------------------------
//
// Description :Font methods for TCPDF library.
//
//============================================================+

/**
 * @file
 * Unicode data and font methods for TCPDF library.
 * @author Nicola Asuni
 * @package com.tecnick.tcpdf
 */

/**
 * @class TCPDF_FONTS
 * Font methods for TCPDF library.
 * @package com.tecnick.tcpdf
 * @version 1.0.010
 * @author Nicola Asuni - info@tecnick.com
 */
class TCPDF_FONTS {

	/**
	 * Static cache used for speed up uniord performances
	 * @protected
	 */
	protected static $cache_uniord = array();

	/**
	 * Convert and add the selected TrueType or Type1 font to the fonts folder (that must be writeable).
	 * @param $fontfile (string) Font file (full path).
	 * @param $fonttype (string) Font type. Leave empty for autodetect mode. Valid values are: TrueTypeUnicode, TrueType, Type1, CID0JP = CID-0 Japanese, CID0KR = CID-0 Korean, CID0CS = CID-0 Chinese Simplified, CID0CT = CID-0 Chinese Traditional.
	 * @param $enc (string) Name of the encoding table to use. Leave empty for default mode. Omit this parameter for TrueType Unicode and symbolic fonts like Symbol or ZapfDingBats.
	 * @param $flags (int) Unsigned 32-bit integer containing flags specifying various characteristics of the font (PDF32000:2008 - 9.8.2 Font Descriptor Flags): +1 for fixed font; +4 for symbol or +32 for non-symbol; +64 for italic. Fixed and Italic mode are generally autodetected so you have to set it to 32 = non-symbolic font (default) or 4 = symbolic font.
	 * @param $outpath (string) Output path for generated font files (must be writeable by the web server). Leave empty for default font folder.
	 * @param $platid (int) Platform ID for CMAP table to extract (when building a Unicode font for Windows this value should be 3, for Macintosh should be 1).
	 * @param $encid (int) Encoding ID for CMAP table to extract (when building a Unicode font for Windows this value should be 1, for Macintosh should be 0). When Platform ID is 3, legal values for Encoding ID are: 0=Symbol, 1=Unicode, 2=ShiftJIS, 3=PRC, 4=Big5, 5=Wansung, 6=Johab, 7=Reserved, 8=Reserved, 9=Reserved, 10=UCS-4.
	 * @param $addcbbox (boolean) If true includes the character bounding box information on the php font file.
	 * @param $link (boolean) If true link to system font instead of copying the font data (not transportable) - Note: do not work with Type1 fonts.
	 * @return (string) TCPDF font name or boolean false in case of error.
	 * @author Nicola Asuni
	 * @since 5.9.123 (2010-09-30)
	 * @public static
	 */
	public static function addTTFfont($fontfile, $fonttype='', $enc='', $flags=32, $outpath='', $platid=3, $encid=1, $addcbbox=false, $link=false) {
		if (!file_exists($fontfile)) {
			// Could not find file
			return false;
		}
		// font metrics
		$fmetric = array();
		// build new font name for TCPDF compatibility
		$font_path_parts = pathinfo($fontfile);
		if (!isset($font_path_parts['filename'])) {
			$font_path_parts['filename'] = substr($font_path_parts['basename'], 0, -(strlen($font_path_parts['extension']) + 1));
		}
		$font_name = strtolower($font_path_parts['filename']);
		$font_name = preg_replace('/[^a-z0-9_]/', '', $font_name);
		$search  = array('bold', 'oblique', 'italic', 'regular');
		$replace = array('b', 'i', 'i', '');
		$font_name = str_replace($search, $replace, $font_name);
		if (empty($font_name)) {
			// set generic name
			$font_name = 'tcpdffont';
		}
		// set output path
		if (empty($outpath)) {
			$outpath = self::_getfontpath();
		}
		// check if this font already exist
		if (@file_exists($outpath.$font_name.'.php')) {
			// this font already exist (delete it from fonts folder to rebuild it)
			return $font_name;
		}
		$fmetric['file'] = $font_name;
		$fmetric['ctg'] = $font_name.'.ctg.z';
		// get font data
		$font = file_get_contents($fontfile);
		$fmetric['originalsize'] = strlen($font);
		// autodetect font type
		if (empty($fonttype)) {
			if (TCPDF_STATIC::_getULONG($font, 0) == 0x10000) {
				// True Type (Unicode or not)
				$fonttype = 'TrueTypeUnicode';
			} elseif (substr($font, 0, 4) == 'OTTO') {
				// Open Type (Unicode or not)
				//Unsupported font format: OpenType with CFF data
				return false;
			} else {
				// Type 1
				$fonttype = 'Type1';
			}
		}
		// set font type
		switch ($fonttype) {
			case 'CID0CT':
			case 'CID0CS':
			case 'CID0KR':
			case 'CID0JP': {
				$fmetric['type'] = 'cidfont0';
				break;
			}
			case 'Type1': {
				$fmetric['type'] = 'Type1';
				if (empty($enc) AND (($flags & 4) == 0)) {
					$enc = 'cp1252';
				}
				break;
			}
			case 'TrueType': {
				$fmetric['type'] = 'TrueType';
				break;
			}
			case 'TrueTypeUnicode':
			default: {
				$fmetric['type'] = 'TrueTypeUnicode';
				break;
			}
		}
		// set encoding maps (if any)
		$fmetric['enc'] = preg_replace('/[^A-Za-z0-9_\-]/', '', $enc);
		$fmetric['diff'] = '';
		if (($fmetric['type'] == 'TrueType') OR ($fmetric['type'] == 'Type1')) {
			if (!empty($enc) AND ($enc != 'cp1252') AND isset(TCPDF_FONT_DATA::$encmap[$enc])) {
				// build differences from reference encoding
				$enc_ref = TCPDF_FONT_DATA::$encmap['cp1252'];
				$enc_target = TCPDF_FONT_DATA::$encmap[$enc];
				$last = 0;
				for ($i = 32; $i <= 255; ++$i) {
					if ($enc_target != $enc_ref[$i]) {
						if ($i != ($last + 1)) {
							$fmetric['diff'] .= $i.' ';
						}
						$last = $i;
						$fmetric['diff'] .= '/'.$enc_target[$i].' ';
					}
				}
			}
		}
		// parse the font by type
		if ($fmetric['type'] == 'Type1') {
			// ---------- TYPE 1 ----------
			// read first segment
			$a = unpack('Cmarker/Ctype/Vsize', substr($font, 0, 6));
			if ($a['marker'] != 128) {
				// Font file is not a valid binary Type1
				return false;
			}
			$fmetric['size1'] = $a['size'];
			$data = substr($font, 6, $fmetric['size1']);
			// read second segment
			$a = unpack('Cmarker/Ctype/Vsize', substr($font, (6 + $fmetric['size1']), 6));
			if ($a['marker'] != 128) {
				// Font file is not a valid binary Type1
				return false;
			}
			$fmetric['size2'] = $a['size'];
			$encrypted = substr($font, (12 + $fmetric['size1']), $fmetric['size2']);
			$data .= $encrypted;
			// store compressed font
			$fmetric['file'] .= '.z';
			$fp = fopen($outpath.$fmetric['file'], 'wb');
			fwrite($fp, gzcompress($data));
			fclose($fp);
			// get font info
			$fmetric['Flags'] = $flags;
			preg_match ('#/FullName[\s]*\(([^\)]*)#', $font, $matches);
			$fmetric['name'] = preg_replace('/[^a-zA-Z0-9_\-]/', '', $matches[1]);
			preg_match('#/FontBBox[\s]*{([^}]*)#', $font, $matches);
			$fmetric['bbox'] = trim($matches[1]);
			$bv = explode(' ', $fmetric['bbox']);
			$fmetric['Ascent'] = intval($bv[3]);
			$fmetric['Descent'] = intval($bv[1]);
			preg_match('#/ItalicAngle[\s]*([0-9\+\-]*)#', $font, $matches);
			$fmetric['italicAngle'] = intval($matches[1]);
			if ($fmetric['italicAngle'] != 0) {
				$fmetric['Flags'] |= 64;
			}
			preg_match('#/UnderlinePosition[\s]*([0-9\+\-]*)#', $font, $matches);
			$fmetric['underlinePosition'] = intval($matches[1]);
			preg_match('#/UnderlineThickness[\s]*([0-9\+\-]*)#', $font, $matches);
			$fmetric['underlineThickness'] = intval($matches[1]);
			preg_match('#/isFixedPitch[\s]*([^\s]*)#', $font, $matches);
			if ($matches[1] == 'true') {
				$fmetric['Flags'] |= 1;
			}
			// get internal map
			$imap = array();
			if (preg_match_all('#dup[\s]([0-9]+)[\s]*/([^\s]*)[\s]put#sU', $font, $fmap, PREG_SET_ORDER) > 0) {
				foreach ($fmap as $v) {
					$imap[$v[2]] = $v[1];
				}
			}
			// decrypt eexec encrypted part
			$r = 55665; // eexec encryption constant
			$c1 = 52845;
			$c2 = 22719;
			$elen = strlen($encrypted);
			$eplain = '';
			for ($i = 0; $i < $elen; ++$i) {
				$chr = ord($encrypted[$i]);
				$eplain .= chr($chr ^ ($r >> 8));
				$r = ((($chr + $r) * $c1 + $c2) % 65536);
			}
			if (preg_match('#/ForceBold[\s]*([^\s]*)#', $eplain, $matches) > 0) {
				if ($matches[1] == 'true') {
					$fmetric['Flags'] |= 0x40000;
				}
			}
			if (preg_match('#/StdVW[\s]*\[([^\]]*)#', $eplain, $matches) > 0) {
				$fmetric['StemV'] = intval($matches[1]);
			} else {
				$fmetric['StemV'] = 70;
			}
			if (preg_match('#/StdHW[\s]*\[([^\]]*)#', $eplain, $matches) > 0) {
				$fmetric['StemH'] = intval($matches[1]);
			} else {
				$fmetric['StemH'] = 30;
			}
			if (preg_match('#/BlueValues[\s]*\[([^\]]*)#', $eplain, $matches) > 0) {
				$bv = explode(' ', $matches[1]);
				if (count($bv) >= 6) {
					$v1 = intval($bv[2]);
					$v2 = intval($bv[4]);
					if ($v1 <= $v2) {
						$fmetric['XHeight'] = $v1;
						$fmetric['CapHeight'] = $v2;
					} else {
						$fmetric['XHeight'] = $v2;
						$fmetric['CapHeight'] = $v1;
					}
				} else {
					$fmetric['XHeight'] = 450;
					$fmetric['CapHeight'] = 700;
				}
			} else {
				$fmetric['XHeight'] = 450;
				$fmetric['CapHeight'] = 700;
			}
			// get the number of random bytes at the beginning of charstrings
			if (preg_match('#/lenIV[\s]*([0-9]*)#', $eplain, $matches) > 0) {
				$lenIV = intval($matches[1]);
			} else {
				$lenIV = 4;
			}
			$fmetric['Leading'] = 0;
			// get charstring data
			$eplain = substr($eplain, (strpos($eplain, '/CharStrings') + 1));
			preg_match_all('#/([A-Za-z0-9\.]*)[\s][0-9]+[\s]RD[\s](.*)[\s]ND#sU', $eplain, $matches, PREG_SET_ORDER);
			if (!empty($enc) AND isset(TCPDF_FONT_DATA::$encmap[$enc])) {
				$enc_map = TCPDF_FONT_DATA::$encmap[$enc];
			} else {
				$enc_map = false;
			}
			$fmetric['cw'] = '';
			$fmetric['MaxWidth'] = 0;
			$cwidths = array();
			foreach ($matches as $k => $v) {
				$cid = 0;
				if (isset($imap[$v[1]])) {
					$cid = $imap[$v[1]];
				} elseif ($enc_map !== false) {
					$cid = array_search($v[1], $enc_map);
					if ($cid === false) {
						$cid = 0;
					} elseif ($cid > 1000) {
						$cid -= 1000;
					}
				}
				// decrypt charstring encrypted part
				$r = 4330; // charstring encryption constant
				$c1 = 52845;
				$c2 = 22719;
				$cd = $v[2];
				$clen = strlen($cd);
				$ccom = array();
				for ($i = 0; $i < $clen; ++$i) {
					$chr = ord($cd[$i]);
					$ccom[] = ($chr ^ ($r >> 8));
					$r = ((($chr + $r) * $c1 + $c2) % 65536);
				}
				// decode numbers
				$cdec = array();
				$ck = 0;
				$i = $lenIV;
				while ($i < $clen) {
					if ($ccom[$i] < 32) {
						$cdec[$ck] = $ccom[$i];
						if (($ck > 0) AND ($cdec[$ck] == 13)) {
							// hsbw command: update width
							$cwidths[$cid] = $cdec[($ck - 1)];
						}
						++$i;
					} elseif (($ccom[$i] >= 32) AND ($ccom[$i] <= 246)) {
						$cdec[$ck] = ($ccom[$i] - 139);
						++$i;
					} elseif (($ccom[$i] >= 247) AND ($ccom[$i] <= 250)) {
						$cdec[$ck] = ((($ccom[$i] - 247) * 256) + $ccom[($i + 1)] + 108);
						$i += 2;
					} elseif (($ccom[$i] >= 251) AND ($ccom[$i] <= 254)) {
						$cdec[$ck] = ((-($ccom[$i] - 251) * 256) - $ccom[($i + 1)] - 108);
						$i += 2;
					} elseif ($ccom[$i] == 255) {
						$sval = chr($ccom[($i + 1)]).chr($ccom[($i + 2)]).chr($ccom[($i + 3)]).chr($ccom[($i + 4)]);
						$vsval = unpack('li', $sval);
						$cdec[$ck] = $vsval['i'];
						$i += 5;
					}
					++$ck;
				}
			} // end for each matches
			$fmetric['MissingWidth'] = $cwidths[0];
			$fmetric['MaxWidth'] = $fmetric['MissingWidth'];
			$fmetric['AvgWidth'] = 0;
			// set chars widths
			for ($cid = 0; $cid <= 255; ++$cid) {
				if (isset($cwidths[$cid])) {
					if ($cwidths[$cid] > $fmetric['MaxWidth']) {
						$fmetric['MaxWidth'] = $cwidths[$cid];
					}
					$fmetric['AvgWidth'] += $cwidths[$cid];
					$fmetric['cw'] .= ','.$cid.'=>'.$cwidths[$cid];
				} else {
					$fmetric['cw'] .= ','.$cid.'=>'.$fmetric['MissingWidth'];
				}
			}
			$fmetric['AvgWidth'] = round($fmetric['AvgWidth'] / count($cwidths));
		} else {
			// ---------- TRUE TYPE ----------
			if ($fmetric['type'] != 'cidfont0') {
				if ($link) {
					// creates a symbolic link to the existing font
					symlink($fontfile, $outpath.$fmetric['file']);
				} else {
					// store compressed font
					$fmetric['file'] .= '.z';
					$fp = fopen($outpath.$fmetric['file'], 'wb');
					fwrite($fp, gzcompress($font));
					fclose($fp);
				}
			}
			$offset = 0; // offset position of the font data
			if (TCPDF_STATIC::_getULONG($font, $offset) != 0x10000) {
				// sfnt version must be 0x00010000 for TrueType version 1.0.
				return false;
			}
			$offset += 4;
			// get number of tables
			$numTables = TCPDF_STATIC::_getUSHORT($font, $offset);
			$offset += 2;
			// skip searchRange, entrySelector and rangeShift
			$offset += 6;
			// tables array
			$table = array();
			// ---------- get tables ----------
			for ($i = 0; $i < $numTables; ++$i) {
				// get table info
				$tag = substr($font, $offset, 4);
				$offset += 4;
				$table[$tag] = array();
				$table[$tag]['checkSum'] = TCPDF_STATIC::_getULONG($font, $offset);
				$offset += 4;
				$table[$tag]['offset'] = TCPDF_STATIC::_getULONG($font, $offset);
				$offset += 4;
				$table[$tag]['length'] = TCPDF_STATIC::_getULONG($font, $offset);
				$offset += 4;
			}
			// check magicNumber
			$offset = $table['head']['offset'] + 12;
			if (TCPDF_STATIC::_getULONG($font, $offset) != 0x5F0F3CF5) {
				// magicNumber must be 0x5F0F3CF5
				return false;
			}
			$offset += 4;
			$offset += 2; // skip flags
			// get FUnits
			$fmetric['unitsPerEm'] = TCPDF_STATIC::_getUSHORT($font, $offset);
			$offset += 2;
			// units ratio constant
			$urk = (1000 / $fmetric['unitsPerEm']);
			$offset += 16; // skip created, modified
			$xMin = round(TCPDF_STATIC::_getFWORD($font, $offset) * $urk);
			$offset += 2;
			$yMin = round(TCPDF_STATIC::_getFWORD($font, $offset) * $urk);
			$offset += 2;
			$xMax = round(TCPDF_STATIC::_getFWORD($font, $offset) * $urk);
			$offset += 2;
			$yMax = round(TCPDF_STATIC::_getFWORD($font, $offset) * $urk);
			$offset += 2;
			$fmetric['bbox'] = ''.$xMin.' '.$yMin.' '.$xMax.' '.$yMax.'';
			$macStyle = TCPDF_STATIC::_getUSHORT($font, $offset);
			$offset += 2;
			// PDF font flags
			$fmetric['Flags'] = $flags;
			if (($macStyle & 2) == 2) {
				// italic flag
				$fmetric['Flags'] |= 64;
			}
			// get offset mode (indexToLocFormat : 0 = short, 1 = long)
			$offset = $table['head']['offset'] + 50;
			$short_offset = (TCPDF_STATIC::_getSHORT($font, $offset) == 0);
			$offset += 2;
			// get the offsets to the locations of the glyphs in the font, relative to the beginning of the glyphData table
			$indexToLoc = array();
			$offset = $table['loca']['offset'];
			if ($short_offset) {
				// short version
				$tot_num_glyphs = floor($table['loca']['length'] / 2); // numGlyphs + 1
				for ($i = 0; $i < $tot_num_glyphs; ++$i) {
					$indexToLoc[$i] = TCPDF_STATIC::_getUSHORT($font, $offset) * 2;
					$offset += 2;
				}
			} else {
				// long version
				$tot_num_glyphs = floor($table['loca']['length'] / 4); // numGlyphs + 1
				for ($i = 0; $i < $tot_num_glyphs; ++$i) {
					$indexToLoc[$i] = TCPDF_STATIC::_getULONG($font, $offset);
					$offset += 4;
				}
			}
			// get glyphs indexes of chars from cmap table
			$offset = $table['cmap']['offset'] + 2;
			$numEncodingTables = TCPDF_STATIC::_getUSHORT($font, $offset);
			$offset += 2;
			$encodingTables = array();
			for ($i = 0; $i < $numEncodingTables; ++$i) {
				$encodingTables[$i]['platformID'] = TCPDF_STATIC::_getUSHORT($font, $offset);
				$offset += 2;
				$encodingTables[$i]['encodingID'] = TCPDF_STATIC::_getUSHORT($font, $offset);
				$offset += 2;
				$encodingTables[$i]['offset'] = TCPDF_STATIC::_getULONG($font, $offset);
				$offset += 4;
			}
			// ---------- get os/2 metrics ----------
			$offset = $table['OS/2']['offset'];
			$offset += 2; // skip version
			// xAvgCharWidth
			$fmetric['AvgWidth'] = round(TCPDF_STATIC::_getFWORD($font, $offset) * $urk);
			$offset += 2;
			// usWeightClass
			$usWeightClass = round(TCPDF_STATIC::_getUFWORD($font, $offset) * $urk);
			// estimate StemV and StemH (400 = usWeightClass for Normal - Regular font)
			$fmetric['StemV'] = round((70 * $usWeightClass) / 400);
			$fmetric['StemH'] = round((30 * $usWeightClass) / 400);
			$offset += 2;
			$offset += 2; // usWidthClass
			$fsType = TCPDF_STATIC::_getSHORT($font, $offset);
			$offset += 2;
			if ($fsType == 2) {
				// This Font cannot be modified, embedded or exchanged in any manner without first obtaining permission of the legal owner.
				return false;
			}
			// ---------- get font name ----------
			$fmetric['name'] = '';
			$offset = $table['name']['offset'];
			$offset += 2; // skip Format selector (=0).
			// Number of NameRecords that follow n.
			$numNameRecords = TCPDF_STATIC::_getUSHORT($font, $offset);
			$offset += 2;
			// Offset to start of string storage (from start of table).
			$stringStorageOffset = TCPDF_STATIC::_getUSHORT($font, $offset);
			$offset += 2;
			for ($i = 0; $i < $numNameRecords; ++$i) {
				$offset += 6; // skip Platform ID, Platform-specific encoding ID, Language ID.
				// Name ID.
				$nameID = TCPDF_STATIC::_getUSHORT($font, $offset);
				$offset += 2;
				if ($nameID == 6) {
					// String length (in bytes).
					$stringLength = TCPDF_STATIC::_getUSHORT($font, $offset);
					$offset += 2;
					// String offset from start of storage area (in bytes).
					$stringOffset = TCPDF_STATIC::_getUSHORT($font, $offset);
					$offset += 2;
					$offset = ($table['name']['offset'] + $stringStorageOffset + $stringOffset);
					$fmetric['name'] = substr($font, $offset, $stringLength);
					$fmetric['name'] = preg_replace('/[^a-zA-Z0-9_\-]/', '', $fmetric['name']);
					break;
				} else {
					$offset += 4; // skip String length, String offset
				}
			}
			if (empty($fmetric['name'])) {
				$fmetric['name'] = $font_name;
			}
			// ---------- get post data ----------
			$offset = $table['post']['offset'];
			$offset += 4; // skip Format Type
			$fmetric['italicAngle'] = TCPDF_STATIC::_getFIXED($font, $offset);
			$offset += 4;
			$fmetric['underlinePosition'] = round(TCPDF_STATIC::_getFWORD($font, $offset) * $urk);
			$offset += 2;
			$fmetric['underlineThickness'] = round(TCPDF_STATIC::_getFWORD($font, $offset) * $urk);
			$offset += 2;
			$isFixedPitch = (TCPDF_STATIC::_getULONG($font, $offset) == 0) ? false : true;
			$offset += 2;
			if ($isFixedPitch) {
				$fmetric['Flags'] |= 1;
			}
			// ---------- get hhea data ----------
			$offset = $table['hhea']['offset'];
			$offset += 4; // skip Table version number
			// Ascender
			$fmetric['Ascent'] = round(TCPDF_STATIC::_getFWORD($font, $offset) * $urk);
			$offset += 2;
			// Descender
			$fmetric['Descent'] = round(TCPDF_STATIC::_getFWORD($font, $offset) * $urk);
			$offset += 2;
			// LineGap
			$fmetric['Leading'] = round(TCPDF_STATIC::_getFWORD($font, $offset) * $urk);
			$offset += 2;
			// advanceWidthMax
			$fmetric['MaxWidth'] = round(TCPDF_STATIC::_getUFWORD($font, $offset) * $urk);
			$offset += 2;
			$offset += 22; // skip some values
			// get the number of hMetric entries in hmtx table
			$numberOfHMetrics = TCPDF_STATIC::_getUSHORT($font, $offset);
			// ---------- get maxp data ----------
			$offset = $table['maxp']['offset'];
			$offset += 4; // skip Table version number
			// get the the number of glyphs in the font.
			$numGlyphs = TCPDF_STATIC::_getUSHORT($font, $offset);
			// ---------- get CIDToGIDMap ----------
			$ctg = array();
			foreach ($encodingTables as $enctable) {
				// get only specified Platform ID and Encoding ID
				if (($enctable['platformID'] == $platid) AND ($enctable['encodingID'] == $encid)) {
					$offset = $table['cmap']['offset'] + $enctable['offset'];
					$format = TCPDF_STATIC::_getUSHORT($font, $offset);
					$offset += 2;
					switch ($format) {
						case 0: { // Format 0: Byte encoding table
							$offset += 4; // skip length and version/language
							for ($c = 0; $c < 256; ++$c) {
								$g = TCPDF_STATIC::_getBYTE($font, $offset);
								$ctg[$c] = $g;
								++$offset;
							}
							break;
						}
						case 2: { // Format 2: High-byte mapping through table
							$offset += 4; // skip length and version/language
							$numSubHeaders = 0;
							for ($i = 0; $i < 256; ++$i) {
								// Array that maps high bytes to subHeaders: value is subHeader index * 8.
								$subHeaderKeys[$i] = (TCPDF_STATIC::_getUSHORT($font, $offset) / 8);
								$offset += 2;
								if ($numSubHeaders < $subHeaderKeys[$i]) {
									$numSubHeaders = $subHeaderKeys[$i];
								}
							}
							// the number of subHeaders is equal to the max of subHeaderKeys + 1
							++$numSubHeaders;
							// read subHeader structures
							$subHeaders = array();
							$numGlyphIndexArray = 0;
							for ($k = 0; $k < $numSubHeaders; ++$k) {
								$subHeaders[$k]['firstCode'] = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
								$subHeaders[$k]['entryCount'] = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
								$subHeaders[$k]['idDelta'] = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
								$subHeaders[$k]['idRangeOffset'] = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
								$subHeaders[$k]['idRangeOffset'] -= (2 + (($numSubHeaders - $k - 1) * 8));
								$subHeaders[$k]['idRangeOffset'] /= 2;
								$numGlyphIndexArray += $subHeaders[$k]['entryCount'];
							}
							for ($k = 0; $k < $numGlyphIndexArray; ++$k) {
								$glyphIndexArray[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
							}
							for ($i = 0; $i < 256; ++$i) {
								$k = $subHeaderKeys[$i];
								if ($k == 0) {
									// one byte code
									$c = $i;
									$g = $glyphIndexArray[0];
									$ctg[$c] = $g;
								} else {
									// two bytes code
									$start_byte = $subHeaders[$k]['firstCode'];
									$end_byte = $start_byte + $subHeaders[$k]['entryCount'];
									for ($j = $start_byte; $j < $end_byte; ++$j) {
										// combine high and low bytes
										$c = (($i << 8) + $j);
										$idRangeOffset = ($subHeaders[$k]['idRangeOffset'] + $j - $subHeaders[$k]['firstCode']);
										$g = ($glyphIndexArray[$idRangeOffset] + $subHeaders[$k]['idDelta']) % 65536;
										if ($g < 0) {
											$g = 0;
										}
										$ctg[$c] = $g;
									}
								}
							}
							break;
						}
						case 4: { // Format 4: Segment mapping to delta values
							$length = TCPDF_STATIC::_getUSHORT($font, $offset);
							$offset += 2;
							$offset += 2; // skip version/language
							$segCount = floor(TCPDF_STATIC::_getUSHORT($font, $offset) / 2);
							$offset += 2;
							$offset += 6; // skip searchRange, entrySelector, rangeShift
							$endCount = array(); // array of end character codes for each segment
							for ($k = 0; $k < $segCount; ++$k) {
								$endCount[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
							}
							$offset += 2; // skip reservedPad
							$startCount = array(); // array of start character codes for each segment
							for ($k = 0; $k < $segCount; ++$k) {
								$startCount[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
							}
							$idDelta = array(); // delta for all character codes in segment
							for ($k = 0; $k < $segCount; ++$k) {
								$idDelta[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
							}
							$idRangeOffset = array(); // Offsets into glyphIdArray or 0
							for ($k = 0; $k < $segCount; ++$k) {
								$idRangeOffset[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
							}
							$gidlen = (floor($length / 2) - 8 - (4 * $segCount));
							$glyphIdArray = array(); // glyph index array
							for ($k = 0; $k < $gidlen; ++$k) {
								$glyphIdArray[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
							}
							for ($k = 0; $k < $segCount; ++$k) {
								for ($c = $startCount[$k]; $c <= $endCount[$k]; ++$c) {
									if ($idRangeOffset[$k] == 0) {
										$g = ($idDelta[$k] + $c) % 65536;
									} else {
										$gid = (floor($idRangeOffset[$k] / 2) + ($c - $startCount[$k]) - ($segCount - $k));
										$g = ($glyphIdArray[$gid] + $idDelta[$k]) % 65536;
									}
									if ($g < 0) {
										$g = 0;
									}
									$ctg[$c] = $g;
								}
							}
							break;
						}
						case 6: { // Format 6: Trimmed table mapping
							$offset += 4; // skip length and version/language
							$firstCode = TCPDF_STATIC::_getUSHORT($font, $offset);
							$offset += 2;
							$entryCount = TCPDF_STATIC::_getUSHORT($font, $offset);
							$offset += 2;
							for ($k = 0; $k < $entryCount; ++$k) {
								$c = ($k + $firstCode);
								$g = TCPDF_STATIC::_getUSHORT($font, $offset);
								$offset += 2;
								$ctg[$c] = $g;
							}
							break;
						}
						case 8: { // Format 8: Mixed 16-bit and 32-bit coverage
							$offset += 10; // skip reserved, length and version/language
							for ($k = 0; $k < 8192; ++$k) {
								$is32[$k] = TCPDF_STATIC::_getBYTE($font, $offset);
								++$offset;
							}
							$nGroups = TCPDF_STATIC::_getULONG($font, $offset);
							$offset += 4;
							for ($i = 0; $i < $nGroups; ++$i) {
								$startCharCode = TCPDF_STATIC::_getULONG($font, $offset);
								$offset += 4;
								$endCharCode = TCPDF_STATIC::_getULONG($font, $offset);
								$offset += 4;
								$startGlyphID = TCPDF_STATIC::_getULONG($font, $offset);
								$offset += 4;
								for ($k = $startCharCode; $k <= $endCharCode; ++$k) {
									$is32idx = floor($c / 8);
									if ((isset($is32[$is32idx])) AND (($is32[$is32idx] & (1 << (7 - ($c % 8)))) == 0)) {
										$c = $k;
									} else {
										// 32 bit format
										// convert to decimal (http://www.unicode.org/faq//utf_bom.html#utf16-4)
										//LEAD_OFFSET = (0xD800 - (0x10000 >> 10)) = 55232
										//SURROGATE_OFFSET = (0x10000 - (0xD800 << 10) - 0xDC00) = -56613888
										$c = ((55232 + ($k >> 10)) << 10) + (0xDC00 + ($k & 0x3FF)) -56613888;
									}
									$ctg[$c] = 0;
									++$startGlyphID;
								}
							}
							break;
						}
						case 10: { // Format 10: Trimmed array
							$offset += 10; // skip reserved, length and version/language
							$startCharCode = TCPDF_STATIC::_getULONG($font, $offset);
							$offset += 4;
							$numChars = TCPDF_STATIC::_getULONG($font, $offset);
							$offset += 4;
							for ($k = 0; $k < $numChars; ++$k) {
								$c = ($k + $startCharCode);
								$g = TCPDF_STATIC::_getUSHORT($font, $offset);
								$ctg[$c] = $g;
								$offset += 2;
							}
							break;
						}
						case 12: { // Format 12: Segmented coverage
							$offset += 10; // skip length and version/language
							$nGroups = TCPDF_STATIC::_getULONG($font, $offset);
							$offset += 4;
							for ($k = 0; $k < $nGroups; ++$k) {
								$startCharCode = TCPDF_STATIC::_getULONG($font, $offset);
								$offset += 4;
								$endCharCode = TCPDF_STATIC::_getULONG($font, $offset);
								$offset += 4;
								$startGlyphCode = TCPDF_STATIC::_getULONG($font, $offset);
								$offset += 4;
								for ($c = $startCharCode; $c <= $endCharCode; ++$c) {
									$ctg[$c] = $startGlyphCode;
									++$startGlyphCode;
								}
							}
							break;
						}
						case 13: { // Format 13: Many-to-one range mappings
							// to be implemented ...
							break;
						}
						case 14: { // Format 14: Unicode Variation Sequences
							// to be implemented ...
							break;
						}
					}
				}
			}
			if (!isset($ctg[0])) {
				$ctg[0] = 0;
			}
			// get xHeight (height of x)
			$offset = ($table['glyf']['offset'] + $indexToLoc[$ctg[120]] + 4);
			$yMin = TCPDF_STATIC::_getFWORD($font, $offset);
			$offset += 4;
			$yMax = TCPDF_STATIC::_getFWORD($font, $offset);
			$offset += 2;
			$fmetric['XHeight'] = round(($yMax - $yMin) * $urk);
			// get CapHeight (height of H)
			$offset = ($table['glyf']['offset'] + $indexToLoc[$ctg[72]] + 4);
			$yMin = TCPDF_STATIC::_getFWORD($font, $offset);
			$offset += 4;
			$yMax = TCPDF_STATIC::_getFWORD($font, $offset);
			$offset += 2;
			$fmetric['CapHeight'] = round(($yMax - $yMin) * $urk);
			// ceate widths array
			$cw = array();
			$offset = $table['hmtx']['offset'];
			for ($i = 0 ; $i < $numberOfHMetrics; ++$i) {
				$cw[$i] = round(TCPDF_STATIC::_getUFWORD($font, $offset) * $urk);
				$offset += 4; // skip lsb
			}
			if ($numberOfHMetrics < $numGlyphs) {
				// fill missing widths with the last value
				$cw = array_pad($cw, $numGlyphs, $cw[($numberOfHMetrics - 1)]);
			}
			$fmetric['MissingWidth'] = $cw[0];
			$fmetric['cw'] = '';
			for ($cid = 0; $cid <= 65535; ++$cid) {
				if (isset($ctg[$cid])) {
					if (isset($cw[$ctg[$cid]])) {
						$fmetric['cw'] .= ','.$cid.'=>'.$cw[$ctg[$cid]];
					}
					if ($addcbbox AND isset($indexToLoc[$ctg[$cid]])) {
						$offset = ($table['glyf']['offset'] + $indexToLoc[$ctg[$cid]]);
						$xMin = round(TCPDF_STATIC::_getFWORD($font, $offset + 2)) * $urk;
						$yMin = round(TCPDF_STATIC::_getFWORD($font, $offset + 4)) * $urk;
						$xMax = round(TCPDF_STATIC::_getFWORD($font, $offset + 6)) * $urk;
						$yMax = round(TCPDF_STATIC::_getFWORD($font, $offset + 8)) * $urk;
						$fmetric['cbbox'] .= ','.$cid.'=>array('.$xMin.','.$yMin.','.$xMax.','.$yMax.')';
					}
				}
			}
		} // end of true type
		if (($fmetric['type'] == 'TrueTypeUnicode') AND (count($ctg) == 256)) {
			$fmetric['type'] == 'TrueType';
		}
		// ---------- create php font file ----------
		$pfile = '<'.'?'.'php'."\n";
		$pfile .= '// TCPDF FONT FILE DESCRIPTION'."\n";
		$pfile .= '$type=\''.$fmetric['type'].'\';'."\n";
		$pfile .= '$name=\''.$fmetric['name'].'\';'."\n";
		$pfile .= '$up='.$fmetric['underlinePosition'].';'."\n";
		$pfile .= '$ut='.$fmetric['underlineThickness'].';'."\n";
		if ($fmetric['MissingWidth'] > 0) {
			$pfile .= '$dw='.$fmetric['MissingWidth'].';'."\n";
		} else {
			$pfile .= '$dw='.$fmetric['AvgWidth'].';'."\n";
		}
		$pfile .= '$diff=\''.$fmetric['diff'].'\';'."\n";
		if ($fmetric['type'] == 'Type1') {
			// Type 1
			$pfile .= '$enc=\''.$fmetric['enc'].'\';'."\n";
			$pfile .= '$file=\''.$fmetric['file'].'\';'."\n";
			$pfile .= '$size1='.$fmetric['size1'].';'."\n";
			$pfile .= '$size2='.$fmetric['size2'].';'."\n";
		} else {
			$pfile .= '$originalsize='.$fmetric['originalsize'].';'."\n";
			if ($fmetric['type'] == 'cidfont0') {
				// CID-0
				switch ($fonttype) {
					case 'CID0JP': {
						$pfile .= '// Japanese'."\n";
						$pfile .= '$enc=\'UniJIS-UTF16-H\';'."\n";
						$pfile .= '$cidinfo=array(\'Registry\'=>\'Adobe\', \'Ordering\'=>\'Japan1\',\'Supplement\'=>5);'."\n";
						$pfile .= 'include(dirname(__FILE__).\'/uni2cid_aj16.php\');'."\n";
						break;
					}
					case 'CID0KR': {
						$pfile .= '// Korean'."\n";
						$pfile .= '$enc=\'UniKS-UTF16-H\';'."\n";
						$pfile .= '$cidinfo=array(\'Registry\'=>\'Adobe\', \'Ordering\'=>\'Korea1\',\'Supplement\'=>0);'."\n";
						$pfile .= 'include(dirname(__FILE__).\'/uni2cid_ak12.php\');'."\n";
						break;
					}
					case 'CID0CS': {
						$pfile .= '// Chinese Simplified'."\n";
						$pfile .= '$enc=\'UniGB-UTF16-H\';'."\n";
						$pfile .= '$cidinfo=array(\'Registry\'=>\'Adobe\', \'Ordering\'=>\'GB1\',\'Supplement\'=>2);'."\n";
						$pfile .= 'include(dirname(__FILE__).\'/uni2cid_ag15.php\');'."\n";
						break;
					}
					case 'CID0CT':
					default: {
						$pfile .= '// Chinese Traditional'."\n";
						$pfile .= '$enc=\'UniCNS-UTF16-H\';'."\n";
						$pfile .= '$cidinfo=array(\'Registry\'=>\'Adobe\', \'Ordering\'=>\'CNS1\',\'Supplement\'=>0);'."\n";
						$pfile .= 'include(dirname(__FILE__).\'/uni2cid_aj16.php\');'."\n";
						break;
					}
				}
			} else {
				// TrueType
				$pfile .= '$enc=\''.$fmetric['enc'].'\';'."\n";
				$pfile .= '$file=\''.$fmetric['file'].'\';'."\n";
				$pfile .= '$ctg=\''.$fmetric['ctg'].'\';'."\n";
				// create CIDToGIDMap
				$cidtogidmap = str_pad('', 131072, "\x00"); // (256 * 256 * 2) = 131072
				foreach ($ctg as $cid => $gid) {
					$cidtogidmap = self::updateCIDtoGIDmap($cidtogidmap, $cid, $ctg[$cid]);
				}
				// store compressed CIDToGIDMap
				$fp = fopen($outpath.$fmetric['ctg'], 'wb');
				fwrite($fp, gzcompress($cidtogidmap));
				fclose($fp);
			}
		}
		$pfile .= '$desc=array(';
		$pfile .= '\'Flags\'=>'.$fmetric['Flags'].',';
		$pfile .= '\'FontBBox\'=>\'['.$fmetric['bbox'].']\',';
		$pfile .= '\'ItalicAngle\'=>'.$fmetric['italicAngle'].',';
		$pfile .= '\'Ascent\'=>'.$fmetric['Ascent'].',';
		$pfile .= '\'Descent\'=>'.$fmetric['Descent'].',';
		$pfile .= '\'Leading\'=>'.$fmetric['Leading'].',';
		$pfile .= '\'CapHeight\'=>'.$fmetric['CapHeight'].',';
		$pfile .= '\'XHeight\'=>'.$fmetric['XHeight'].',';
		$pfile .= '\'StemV\'=>'.$fmetric['StemV'].',';
		$pfile .= '\'StemH\'=>'.$fmetric['StemH'].',';
		$pfile .= '\'AvgWidth\'=>'.$fmetric['AvgWidth'].',';
		$pfile .= '\'MaxWidth\'=>'.$fmetric['MaxWidth'].',';
		$pfile .= '\'MissingWidth\'=>'.$fmetric['MissingWidth'].'';
		$pfile .= ');'."\n";
		if (isset($fmetric['cbbox'])) {
			$pfile .= '$cbbox=array('.substr($fmetric['cbbox'], 1).');'."\n";
		}
		$pfile .= '$cw=array('.substr($fmetric['cw'], 1).');'."\n";
		$pfile .= '// --- EOF ---'."\n";
		// store file
		$fp = fopen($outpath.$font_name.'.php', 'w');
		fwrite($fp, $pfile);
		fclose($fp);
		// return TCPDF font name
		return $font_name;
	}

	/**
	 * Returs the checksum of a TTF table.
	 * @param $table (string) table to check
	 * @param $length (int) length of table in bytes
	 * @return int checksum
	 * @author Nicola Asuni
	 * @since 5.2.000 (2010-06-02)
	 * @public static
	 */
	public static function _getTTFtableChecksum($table, $length) {
		$sum = 0;
		$tlen = ($length / 4);
		$offset = 0;
		for ($i = 0; $i < $tlen; ++$i) {
			$v = unpack('Ni', substr($table, $offset, 4));
			$sum += $v['i'];
			$offset += 4;
		}
		$sum = unpack('Ni', pack('N', $sum));
		return $sum['i'];
	}

	/**
	 * Returns a subset of the TrueType font data without the unused glyphs.
	 * @param $font (string) TrueType font data.
	 * @param $subsetchars (array) Array of used characters (the glyphs to keep).
	 * @return (string) A subset of TrueType font data without the unused glyphs.
	 * @author Nicola Asuni
	 * @since 5.2.000 (2010-06-02)
	 * @public static
	 */
	public static function _getTrueTypeFontSubset($font, $subsetchars) {
		ksort($subsetchars);
		$offset = 0; // offset position of the font data
		if (TCPDF_STATIC::_getULONG($font, $offset) != 0x10000) {
			// sfnt version must be 0x00010000 for TrueType version 1.0.
			return $font;
		}
		$offset += 4;
		// get number of tables
		$numTables = TCPDF_STATIC::_getUSHORT($font, $offset);
		$offset += 2;
		// skip searchRange, entrySelector and rangeShift
		$offset += 6;
		// tables array
		$table = array();
		// for each table
		for ($i = 0; $i < $numTables; ++$i) {
			// get table info
			$tag = substr($font, $offset, 4);
			$offset += 4;
			$table[$tag] = array();
			$table[$tag]['checkSum'] = TCPDF_STATIC::_getULONG($font, $offset);
			$offset += 4;
			$table[$tag]['offset'] = TCPDF_STATIC::_getULONG($font, $offset);
			$offset += 4;
			$table[$tag]['length'] = TCPDF_STATIC::_getULONG($font, $offset);
			$offset += 4;
		}
		// check magicNumber
		$offset = $table['head']['offset'] + 12;
		if (TCPDF_STATIC::_getULONG($font, $offset) != 0x5F0F3CF5) {
			// magicNumber must be 0x5F0F3CF5
			return $font;
		}
		$offset += 4;
		// get offset mode (indexToLocFormat : 0 = short, 1 = long)
		$offset = $table['head']['offset'] + 50;
		$short_offset = (TCPDF_STATIC::_getSHORT($font, $offset) == 0);
		$offset += 2;
		// get the offsets to the locations of the glyphs in the font, relative to the beginning of the glyphData table
		$indexToLoc = array();
		$offset = $table['loca']['offset'];
		if ($short_offset) {
			// short version
			$tot_num_glyphs = floor($table['loca']['length'] / 2); // numGlyphs + 1
			for ($i = 0; $i < $tot_num_glyphs; ++$i) {
				$indexToLoc[$i] = TCPDF_STATIC::_getUSHORT($font, $offset) * 2;
				$offset += 2;
			}
		} else {
			// long version
			$tot_num_glyphs = ($table['loca']['length'] / 4); // numGlyphs + 1
			for ($i = 0; $i < $tot_num_glyphs; ++$i) {
				$indexToLoc[$i] = TCPDF_STATIC::_getULONG($font, $offset);
				$offset += 4;
			}
		}
		// get glyphs indexes of chars from cmap table
		$subsetglyphs = array(); // glyph IDs on key
		$subsetglyphs[0] = true; // character codes that do not correspond to any glyph in the font should be mapped to glyph index 0
		$offset = $table['cmap']['offset'] + 2;
		$numEncodingTables = TCPDF_STATIC::_getUSHORT($font, $offset);
		$offset += 2;
		$encodingTables = array();
		for ($i = 0; $i < $numEncodingTables; ++$i) {
			$encodingTables[$i]['platformID'] = TCPDF_STATIC::_getUSHORT($font, $offset);
			$offset += 2;
			$encodingTables[$i]['encodingID'] = TCPDF_STATIC::_getUSHORT($font, $offset);
			$offset += 2;
			$encodingTables[$i]['offset'] = TCPDF_STATIC::_getULONG($font, $offset);
			$offset += 4;
		}
		foreach ($encodingTables as $enctable) {
			// get all platforms and encodings
			$offset = $table['cmap']['offset'] + $enctable['offset'];
			$format = TCPDF_STATIC::_getUSHORT($font, $offset);
			$offset += 2;
			switch ($format) {
				case 0: { // Format 0: Byte encoding table
					$offset += 4; // skip length and version/language
					for ($c = 0; $c < 256; ++$c) {
						if (isset($subsetchars[$c])) {
							$g = TCPDF_STATIC::_getBYTE($font, $offset);
							$subsetglyphs[$g] = true;
						}
						++$offset;
					}
					break;
				}
				case 2: { // Format 2: High-byte mapping through table
					$offset += 4; // skip length and version/language
					$numSubHeaders = 0;
					for ($i = 0; $i < 256; ++$i) {
						// Array that maps high bytes to subHeaders: value is subHeader index * 8.
						$subHeaderKeys[$i] = (TCPDF_STATIC::_getUSHORT($font, $offset) / 8);
						$offset += 2;
						if ($numSubHeaders < $subHeaderKeys[$i]) {
							$numSubHeaders = $subHeaderKeys[$i];
						}
					}
					// the number of subHeaders is equal to the max of subHeaderKeys + 1
					++$numSubHeaders;
					// read subHeader structures
					$subHeaders = array();
					$numGlyphIndexArray = 0;
					for ($k = 0; $k < $numSubHeaders; ++$k) {
						$subHeaders[$k]['firstCode'] = TCPDF_STATIC::_getUSHORT($font, $offset);
						$offset += 2;
						$subHeaders[$k]['entryCount'] = TCPDF_STATIC::_getUSHORT($font, $offset);
						$offset += 2;
						$subHeaders[$k]['idDelta'] = TCPDF_STATIC::_getUSHORT($font, $offset);
						$offset += 2;
						$subHeaders[$k]['idRangeOffset'] = TCPDF_STATIC::_getUSHORT($font, $offset);
						$offset += 2;
						$subHeaders[$k]['idRangeOffset'] -= (2 + (($numSubHeaders - $k - 1) * 8));
						$subHeaders[$k]['idRangeOffset'] /= 2;
						$numGlyphIndexArray += $subHeaders[$k]['entryCount'];
					}
					for ($k = 0; $k < $numGlyphIndexArray; ++$k) {
						$glyphIndexArray[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
						$offset += 2;
					}
					for ($i = 0; $i < 256; ++$i) {
						$k = $subHeaderKeys[$i];
						if ($k == 0) {
							// one byte code
							$c = $i;
							if (isset($subsetchars[$c])) {
								$g = $glyphIndexArray[0];
								$subsetglyphs[$g] = true;
							}
						} else {
							// two bytes code
							$start_byte = $subHeaders[$k]['firstCode'];
							$end_byte = $start_byte + $subHeaders[$k]['entryCount'];
							for ($j = $start_byte; $j < $end_byte; ++$j) {
								// combine high and low bytes
								$c = (($i << 8) + $j);
								if (isset($subsetchars[$c])) {
									$idRangeOffset = ($subHeaders[$k]['idRangeOffset'] + $j - $subHeaders[$k]['firstCode']);
									$g = ($glyphIndexArray[$idRangeOffset] + $subHeaders[$k]['idDelta']) % 65536;
									if ($g < 0) {
										$g = 0;
									}
									$subsetglyphs[$g] = true;
								}
							}
						}
					}
					break;
				}
				case 4: { // Format 4: Segment mapping to delta values
					$length = TCPDF_STATIC::_getUSHORT($font, $offset);
					$offset += 2;
					$offset += 2; // skip version/language
					$segCount = floor(TCPDF_STATIC::_getUSHORT($font, $offset) / 2);
					$offset += 2;
					$offset += 6; // skip searchRange, entrySelector, rangeShift
					$endCount = array(); // array of end character codes for each segment
					for ($k = 0; $k < $segCount; ++$k) {
						$endCount[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
						$offset += 2;
					}
					$offset += 2; // skip reservedPad
					$startCount = array(); // array of start character codes for each segment
					for ($k = 0; $k < $segCount; ++$k) {
						$startCount[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
						$offset += 2;
					}
					$idDelta = array(); // delta for all character codes in segment
					for ($k = 0; $k < $segCount; ++$k) {
						$idDelta[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
						$offset += 2;
					}
					$idRangeOffset = array(); // Offsets into glyphIdArray or 0
					for ($k = 0; $k < $segCount; ++$k) {
						$idRangeOffset[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
						$offset += 2;
					}
					$gidlen = (floor($length / 2) - 8 - (4 * $segCount));
					$glyphIdArray = array(); // glyph index array
					for ($k = 0; $k < $gidlen; ++$k) {
						$glyphIdArray[$k] = TCPDF_STATIC::_getUSHORT($font, $offset);
						$offset += 2;
					}
					for ($k = 0; $k < $segCount; ++$k) {
						for ($c = $startCount[$k]; $c <= $endCount[$k]; ++$c) {
							if (isset($subsetchars[$c])) {
								if ($idRangeOffset[$k] == 0) {
									$g = ($idDelta[$k] + $c) % 65536;
								} else {
									$gid = (floor($idRangeOffset[$k] / 2) + ($c - $startCount[$k]) - ($segCount - $k));
									$g = ($glyphIdArray[$gid] + $idDelta[$k]) % 65536;
								}
								if ($g < 0) {
									$g = 0;
								}
								$subsetglyphs[$g] = true;
							}
						}
					}
					break;
				}
				case 6: { // Format 6: Trimmed table mapping
					$offset += 4; // skip length and version/language
					$firstCode = TCPDF_STATIC::_getUSHORT($font, $offset);
					$offset += 2;
					$entryCount = TCPDF_STATIC::_getUSHORT($font, $offset);
					$offset += 2;
					for ($k = 0; $k < $entryCount; ++$k) {
						$c = ($k + $firstCode);
						if (isset($subsetchars[$c])) {
							$g = TCPDF_STATIC::_getUSHORT($font, $offset);
							$subsetglyphs[$g] = true;
						}
						$offset += 2;
					}
					break;
				}
				case 8: { // Format 8: Mixed 16-bit and 32-bit coverage
					$offset += 10; // skip reserved, length and version/language
					for ($k = 0; $k < 8192; ++$k) {
						$is32[$k] = TCPDF_STATIC::_getBYTE($font, $offset);
						++$offset;
					}
					$nGroups = TCPDF_STATIC::_getULONG($font, $offset);
					$offset += 4;
					for ($i = 0; $i < $nGroups; ++$i) {
						$startCharCode = TCPDF_STATIC::_getULONG($font, $offset);
						$offset += 4;
						$endCharCode = TCPDF_STATIC::_getULONG($font, $offset);
						$offset += 4;
						$startGlyphID = TCPDF_STATIC::_getULONG($font, $offset);
						$offset += 4;
						for ($k = $startCharCode; $k <= $endCharCode; ++$k) {
							$is32idx = floor($c / 8);
							if ((isset($is32[$is32idx])) AND (($is32[$is32idx] & (1 << (7 - ($c % 8)))) == 0)) {
								$c = $k;
							} else {
								// 32 bit format
								// convert to decimal (http://www.unicode.org/faq//utf_bom.html#utf16-4)
								//LEAD_OFFSET = (0xD800 - (0x10000 >> 10)) = 55232
								//SURROGATE_OFFSET = (0x10000 - (0xD800 << 10) - 0xDC00) = -56613888
								$c = ((55232 + ($k >> 10)) << 10) + (0xDC00 + ($k & 0x3FF)) -56613888;
							}
							if (isset($subsetchars[$c])) {
								$subsetglyphs[$startGlyphID] = true;
							}
							++$startGlyphID;
						}
					}
					break;
				}
				case 10: { // Format 10: Trimmed array
					$offset += 10; // skip reserved, length and version/language
					$startCharCode = TCPDF_STATIC::_getULONG($font, $offset);
					$offset += 4;
					$numChars = TCPDF_STATIC::_getULONG($font, $offset);
					$offset += 4;
					for ($k = 0; $k < $numChars; ++$k) {
						$c = ($k + $startCharCode);
						if (isset($subsetchars[$c])) {
							$g = TCPDF_STATIC::_getUSHORT($font, $offset);
							$subsetglyphs[$g] = true;
						}
						$offset += 2;
					}
					break;
				}
				case 12: { // Format 12: Segmented coverage
					$offset += 10; // skip length and version/language
					$nGroups = TCPDF_STATIC::_getULONG($font, $offset);
					$offset += 4;
					for ($k = 0; $k < $nGroups; ++$k) {
						$startCharCode = TCPDF_STATIC::_getULONG($font, $offset);
						$offset += 4;
						$endCharCode = TCPDF_STATIC::_getULONG($font, $offset);
						$offset += 4;
						$startGlyphCode = TCPDF_STATIC::_getULONG($font, $offset);
						$offset += 4;
						for ($c = $startCharCode; $c <= $endCharCode; ++$c) {
							if (isset($subsetchars[$c])) {
								$subsetglyphs[$startGlyphCode] = true;
							}
							++$startGlyphCode;
						}
					}
					break;
				}
				case 13: { // Format 13: Many-to-one range mappings
					// to be implemented ...
					break;
				}
				case 14: { // Format 14: Unicode Variation Sequences
					// to be implemented ...
					break;
				}
			}
		}
		// include all parts of composite glyphs
		$new_sga = $subsetglyphs;
		while (!empty($new_sga)) {
			$sga = $new_sga;
			$new_sga = array();
			foreach ($sga as $key => $val) {
				if (isset($indexToLoc[$key])) {
					$offset = ($table['glyf']['offset'] + $indexToLoc[$key]);
					$numberOfContours = TCPDF_STATIC::_getSHORT($font, $offset);
					$offset += 2;
					if ($numberOfContours < 0) { // composite glyph
						$offset += 8; // skip xMin, yMin, xMax, yMax
						do {
							$flags = TCPDF_STATIC::_getUSHORT($font, $offset);
							$offset += 2;
							$glyphIndex = TCPDF_STATIC::_getUSHORT($font, $offset);
							$offset += 2;
							if (!isset($subsetglyphs[$glyphIndex])) {
								// add missing glyphs
								$new_sga[$glyphIndex] = true;
							}
							// skip some bytes by case
							if ($flags & 1) {
								$offset += 4;
							} else {
								$offset += 2;
							}
							if ($flags & 8) {
								$offset += 2;
							} elseif ($flags & 64) {
								$offset += 4;
							} elseif ($flags & 128) {
								$offset += 8;
							}
						} while ($flags & 32);
					}
				}
			}
			$subsetglyphs += $new_sga;
		}
		// sort glyphs by key (and remove duplicates)
		ksort($subsetglyphs);
		// build new glyf and loca tables
		$glyf = '';
		$loca = '';
		$offset = 0;
		$glyf_offset = $table['glyf']['offset'];
		for ($i = 0; $i < $tot_num_glyphs; ++$i) {
			if (isset($subsetglyphs[$i])) {
				$length = ($indexToLoc[($i + 1)] - $indexToLoc[$i]);
				$glyf .= substr($font, ($glyf_offset + $indexToLoc[$i]), $length);
			} else {
				$length = 0;
			}
			if ($short_offset) {
				$loca .= pack('n', floor($offset / 2));
			} else {
				$loca .= pack('N', $offset);
			}
			$offset += $length;
		}
		// array of table names to preserve (loca and glyf tables will be added later)
		// the cmap table is not needed and shall not be present, since the mapping from character codes to glyph descriptions is provided separately
		$table_names = array ('head', 'hhea', 'hmtx', 'maxp', 'cvt ', 'fpgm', 'prep'); // minimum required table names
		// get the tables to preserve
		$offset = 12;
		foreach ($table as $tag => $val) {
			if (in_array($tag, $table_names)) {
				$table[$tag]['data'] = substr($font, $table[$tag]['offset'], $table[$tag]['length']);
				if ($tag == 'head') {
					// set the checkSumAdjustment to 0
					$table[$tag]['data'] = substr($table[$tag]['data'], 0, 8)."\x0\x0\x0\x0".substr($table[$tag]['data'], 12);
				}
				$pad = 4 - ($table[$tag]['length'] % 4);
				if ($pad != 4) {
					// the length of a table must be a multiple of four bytes
					$table[$tag]['length'] += $pad;
					$table[$tag]['data'] .= str_repeat("\x0", $pad);
				}
				$table[$tag]['offset'] = $offset;
				$offset += $table[$tag]['length'];
				// check sum is not changed (so keep the following line commented)
				//$table[$tag]['checkSum'] = self::_getTTFtableChecksum($table[$tag]['data'], $table[$tag]['length']);
			} else {
				unset($table[$tag]);
			}
		}
		// add loca
		$table['loca']['data'] = $loca;
		$table['loca']['length'] = strlen($loca);
		$pad = 4 - ($table['loca']['length'] % 4);
		if ($pad != 4) {
			// the length of a table must be a multiple of four bytes
			$table['loca']['length'] += $pad;
			$table['loca']['data'] .= str_repeat("\x0", $pad);
		}
		$table['loca']['offset'] = $offset;
		$table['loca']['checkSum'] = self::_getTTFtableChecksum($table['loca']['data'], $table['loca']['length']);
		$offset += $table['loca']['length'];
		// add glyf
		$table['glyf']['data'] = $glyf;
		$table['glyf']['length'] = strlen($glyf);
		$pad = 4 - ($table['glyf']['length'] % 4);
		if ($pad != 4) {
			// the length of a table must be a multiple of four bytes
			$table['glyf']['length'] += $pad;
			$table['glyf']['data'] .= str_repeat("\x0", $pad);
		}
		$table['glyf']['offset'] = $offset;
		$table['glyf']['checkSum'] = self::_getTTFtableChecksum($table['glyf']['data'], $table['glyf']['length']);
		// rebuild font
		$font = '';
		$font .= pack('N', 0x10000); // sfnt version
		$numTables = count($table);
		$font .= pack('n', $numTables); // numTables
		$entrySelector = floor(log($numTables, 2));
		$searchRange = pow(2, $entrySelector) * 16;
		$rangeShift = ($numTables * 16) - $searchRange;
		$font .= pack('n', $searchRange); // searchRange
		$font .= pack('n', $entrySelector); // entrySelector
		$font .= pack('n', $rangeShift); // rangeShift
		$offset = ($numTables * 16);
		foreach ($table as $tag => $data) {
			$font .= $tag; // tag
			$font .= pack('N', $data['checkSum']); // checkSum
			$font .= pack('N', ($data['offset'] + $offset)); // offset
			$font .= pack('N', $data['length']); // length
		}
		foreach ($table as $data) {
			$font .= $data['data'];
		}
		// set checkSumAdjustment on head table
		$checkSumAdjustment = 0xB1B0AFBA - self::_getTTFtableChecksum($font, strlen($font));
		$font = substr($font, 0, $table['head']['offset'] + 8).pack('N', $checkSumAdjustment).substr($font, $table['head']['offset'] + 12);
		return $font;
	}

	/**
	 * Outputs font widths
	 * @param $font (array) font data
	 * @param $cidoffset (int) offset for CID values
	 * @return PDF command string for font widths
	 * @author Nicola Asuni
	 * @since 4.4.000 (2008-12-07)
	 * @public static
	 */
	public static function _putfontwidths($font, $cidoffset=0) {
		ksort($font['cw']);
		$rangeid = 0;
		$range = array();
		$prevcid = -2;
		$prevwidth = -1;
		$interval = false;
		// for each character
		foreach ($font['cw'] as $cid => $width) {
			$cid -= $cidoffset;
			if ($font['subset'] AND (!isset($font['subsetchars'][$cid]))) {
				// ignore the unused characters (font subsetting)
				continue;
			}
			if ($width != $font['dw']) {
				if ($cid == ($prevcid + 1)) {
					// consecutive CID
					if ($width == $prevwidth) {
						if ($width == $range[$rangeid][0]) {
							$range[$rangeid][] = $width;
						} else {
							array_pop($range[$rangeid]);
							// new range
							$rangeid = $prevcid;
							$range[$rangeid] = array();
							$range[$rangeid][] = $prevwidth;
							$range[$rangeid][] = $width;
						}
						$interval = true;
						$range[$rangeid]['interval'] = true;
					} else {
						if ($interval) {
							// new range
							$rangeid = $cid;
							$range[$rangeid] = array();
							$range[$rangeid][] = $width;
						} else {
							$range[$rangeid][] = $width;
						}
						$interval = false;
					}
				} else {
					// new range
					$rangeid = $cid;
					$range[$rangeid] = array();
					$range[$rangeid][] = $width;
					$interval = false;
				}
				$prevcid = $cid;
				$prevwidth = $width;
			}
		}
		// optimize ranges
		$prevk = -1;
		$nextk = -1;
		$prevint = false;
		foreach ($range as $k => $ws) {
			$cws = count($ws);
			if (($k == $nextk) AND (!$prevint) AND ((!isset($ws['interval'])) OR ($cws < 4))) {
				if (isset($range[$k]['interval'])) {
					unset($range[$k]['interval']);
				}
				$range[$prevk] = array_merge($range[$prevk], $range[$k]);
				unset($range[$k]);
			} else {
				$prevk = $k;
			}
			$nextk = $k + $cws;
			if (isset($ws['interval'])) {
				if ($cws > 3) {
					$prevint = true;
				} else {
					$prevint = false;
				}
				if (isset($range[$k]['interval'])) {
					unset($range[$k]['interval']);
				}
				--$nextk;
			} else {
				$prevint = false;
			}
		}
		// output data
		$w = '';
		foreach ($range as $k => $ws) {
			if (count(array_count_values($ws)) == 1) {
				// interval mode is more compact
				$w .= ' '.$k.' '.($k + count($ws) - 1).' '.$ws[0];
			} else {
				// range mode
				$w .= ' '.$k.' [ '.implode(' ', $ws).' ]';
			}
		}
		return '/W ['.$w.' ]';
	}

	/**
	 * Returns the unicode caracter specified by the value
	 * @param $c (int) UTF-8 value
	 * @param $unicode (boolean) True if we are in unicode mode, false otherwise.
	 * @return Returns the specified character.
	 * @since 2.3.000 (2008-03-05)
	 * @public static
	 */
	public static function unichr($c, $unicode=true) {
		if (!$unicode) {
			return chr($c);
		} elseif ($c <= 0x7F) {
			// one byte
			return chr($c);
		} elseif ($c <= 0x7FF) {
			// two bytes
			return chr(0xC0 | $c >> 6).chr(0x80 | $c & 0x3F);
		} elseif ($c <= 0xFFFF) {
			// three bytes
			return chr(0xE0 | $c >> 12).chr(0x80 | $c >> 6 & 0x3F).chr(0x80 | $c & 0x3F);
		} elseif ($c <= 0x10FFFF) {
			// four bytes
			return chr(0xF0 | $c >> 18).chr(0x80 | $c >> 12 & 0x3F).chr(0x80 | $c >> 6 & 0x3F).chr(0x80 | $c & 0x3F);
		} else {
			return '';
		}
	}

	/**
	 * Returns the unicode caracter specified by UTF-8 value
	 * @param $c (int) UTF-8 value
	 * @return Returns the specified character.
	 * @public static
	 */
	public static function unichrUnicode($c) {
		return self::unichr($c, true);
	}

	/**
	 * Returns the unicode caracter specified by ASCII value
	 * @param $c (int) UTF-8 value
	 * @return Returns the specified character.
	 * @public static
	 */
	public static function unichrASCII($c) {
		return self::unichr($c, false);
	}

	/**
	 * Converts array of UTF-8 characters to UTF16-BE string.<br>
	 * Based on: http://www.faqs.org/rfcs/rfc2781.html
	 * <pre>
	 *   Encoding UTF-16:
	 *
	 *   Encoding of a single character from an ISO 10646 character value to
	 *    UTF-16 proceeds as follows. Let U be the character number, no greater
	 *    than 0x10FFFF.
	 *
	 *    1) If U < 0x10000, encode U as a 16-bit unsigned integer and
	 *       terminate.
	 *
	 *    2) Let U' = U - 0x10000. Because U is less than or equal to 0x10FFFF,
	 *       U' must be less than or equal to 0xFFFFF. That is, U' can be
	 *       represented in 20 bits.
	 *
	 *    3) Initialize two 16-bit unsigned integers, W1 and W2, to 0xD800 and
	 *       0xDC00, respectively. These integers each have 10 bits free to
	 *       encode the character value, for a total of 20 bits.
	 *
	 *    4) Assign the 10 high-order bits of the 20-bit U' to the 10 low-order
	 *       bits of W1 and the 10 low-order bits of U' to the 10 low-order
	 *       bits of W2. Terminate.
	 *
	 *    Graphically, steps 2 through 4 look like:
	 *    U' = yyyyyyyyyyxxxxxxxxxx
	 *    W1 = 110110yyyyyyyyyy
	 *    W2 = 110111xxxxxxxxxx
	 * </pre>
	 * @param $unicode (array) array containing UTF-8 unicode values
	 * @param $setbom (boolean) if true set the Byte Order Mark (BOM = 0xFEFF)
	 * @return string
	 * @protected
	 * @author Nicola Asuni
	 * @since 2.1.000 (2008-01-08)
	 * @public static
	 */
	public static function arrUTF8ToUTF16BE($unicode, $setbom=false) {
		$outstr = ''; // string to be returned
		if ($setbom) {
			$outstr .= "\xFE\xFF"; // Byte Order Mark (BOM)
		}
		foreach ($unicode as $char) {
			if ($char == 0x200b) {
				// skip Unicode Character 'ZERO WIDTH SPACE' (DEC:8203, U+200B)
			} elseif ($char == 0xFFFD) {
				$outstr .= "\xFF\xFD"; // replacement character
			} elseif ($char < 0x10000) {
				$outstr .= chr($char >> 0x08);
				$outstr .= chr($char & 0xFF);
			} else {
				$char -= 0x10000;
				$w1 = 0xD800 | ($char >> 0x0a);
				$w2 = 0xDC00 | ($char & 0x3FF);
				$outstr .= chr($w1 >> 0x08);
				$outstr .= chr($w1 & 0xFF);
				$outstr .= chr($w2 >> 0x08);
				$outstr .= chr($w2 & 0xFF);
			}
		}
		return $outstr;
	}

	/**
	 * Convert an array of UTF8 values to array of unicode characters
	 * @param $ta (array) The input array of UTF8 values.
	 * @param $isunicode (boolean) True for Unicode mode, false otherwise.
	 * @return Return array of unicode characters
	 * @since 4.5.037 (2009-04-07)
	 * @public static
	 */
	public static function UTF8ArrayToUniArray($ta, $isunicode=true) {
		if ($isunicode) {
			return array_map(array('self', 'unichrUnicode'), $ta);
		}
		return array_map(array('self', 'unichrASCII'), $ta);
	}

	/**
	 * Extract a slice of the $strarr array and return it as string.
	 * @param $strarr (string) The input array of characters.
	 * @param $start (int) the starting element of $strarr.
	 * @param $end (int) first element that will not be returned.
	 * @param $unicode (boolean) True if we are in unicode mode, false otherwise.
	 * @return Return part of a string
	 * @public static
	 */
	public static function UTF8ArrSubString($strarr, $start='', $end='', $unicode=true) {
		if (strlen($start) == 0) {
			$start = 0;
		}
		if (strlen($end) == 0) {
			$end = count($strarr);
		}
		$string = '';
		for ($i = $start; $i < $end; ++$i) {
			$string .= self::unichr($strarr[$i], $unicode);
		}
		return $string;
	}

	/**
	 * Extract a slice of the $uniarr array and return it as string.
	 * @param $uniarr (string) The input array of characters.
	 * @param $start (int) the starting element of $strarr.
	 * @param $end (int) first element that will not be returned.
	 * @return Return part of a string
	 * @since 4.5.037 (2009-04-07)
	 * @public static
	 */
	public static function UniArrSubString($uniarr, $start='', $end='') {
		if (strlen($start) == 0) {
			$start = 0;
		}
		if (strlen($end) == 0) {
			$end = count($uniarr);
		}
		$string = '';
		for ($i=$start; $i < $end; ++$i) {
			$string .= $uniarr[$i];
		}
		return $string;
	}

	/**
	 * Update the CIDToGIDMap string with a new value.
	 * @param $map (string) CIDToGIDMap.
	 * @param $cid (int) CID value.
	 * @param $gid (int) GID value.
	 * @return (string) CIDToGIDMap.
	 * @author Nicola Asuni
	 * @since 5.9.123 (2011-09-29)
	 * @public static
	 */
	public static function updateCIDtoGIDmap($map, $cid, $gid) {
		if (($cid >= 0) AND ($cid <= 0xFFFF) AND ($gid >= 0)) {
			if ($gid > 0xFFFF) {
				$gid -= 0x10000;
			}
			$map[($cid * 2)] = chr($gid >> 8);
			$map[(($cid * 2) + 1)] = chr($gid & 0xFF);
		}
		return $map;
	}

	/**
	 * Return fonts path
	 * @return string
	 * @public static
	 */
	public static function _getfontpath() {
		if (!defined('K_PATH_FONTS') AND is_dir($fdir = realpath(dirname(__FILE__).'/../fonts'))) {
			if (substr($fdir, -1) != '/') {
				$fdir .= '/';
			}
			define('K_PATH_FONTS', $fdir);
		}
		return defined('K_PATH_FONTS') ? K_PATH_FONTS : '';
	}

	/**
	 * Return font full path
	 * @param $file (string) Font file name.
	 * @param $fontdir (string) Font directory (set to false fto search on default directories)
	 * @return string Font full path or empty string
	 * @author Nicola Asuni
	 * @since 6.0.025
	 * @public static
	 */
	public static function getFontFullPath($file, $fontdir=false) {
		$fontfile = '';
		// search files on various directories
		if (($fontdir !== false) AND @file_exists($fontdir.$file)) {
			$fontfile = $fontdir.$file;
		} elseif (@file_exists(self::_getfontpath().$file)) {
			$fontfile = self::_getfontpath().$file;
		} elseif (@file_exists($file)) {
			$fontfile = $file;
		}
		return $fontfile;
	}

	/**
	 * Converts UTF-8 characters array to array of Latin1 characters array<br>
	 * @param $unicode (array) array containing UTF-8 unicode values
	 * @return array
	 * @author Nicola Asuni
	 * @since 4.8.023 (2010-01-15)
	 * @public static
	 */
	public static function UTF8ArrToLatin1Arr($unicode) {
		$outarr = array(); // array to be returned
		foreach ($unicode as $char) {
			if ($char < 256) {
				$outarr[] = $char;
			} elseif (array_key_exists($char, TCPDF_FONT_DATA::$uni_utf8tolatin)) {
				// map from UTF-8
				$outarr[] = TCPDF_FONT_DATA::$uni_utf8tolatin[$char];
			} elseif ($char == 0xFFFD) {
				// skip
			} else {
				$outarr[] = 63; // '?' character
			}
		}
		return $outarr;
	}

	/**
	 * Converts UTF-8 characters array to array of Latin1 string<br>
	 * @param $unicode (array) array containing UTF-8 unicode values
	 * @return array
	 * @author Nicola Asuni
	 * @since 4.8.023 (2010-01-15)
	 * @public static
	 */
	public static function UTF8ArrToLatin1($unicode) {
		$outstr = ''; // string to be returned
		foreach ($unicode as $char) {
			if ($char < 256) {
				$outstr .= chr($char);
			} elseif (array_key_exists($char, TCPDF_FONT_DATA::$uni_utf8tolatin)) {
				// map from UTF-8
				$outstr .= chr(TCPDF_FONT_DATA::$uni_utf8tolatin[$char]);
			} elseif ($char == 0xFFFD) {
				// skip
			} else {
				$outstr .= '?';
			}
		}
		return $outstr;
	}

	/**
	 * Converts UTF-8 character to integer value.<br>
	 * Uses the getUniord() method if the value is not cached.
	 * @param $uch (string) character string to process.
	 * @return integer Unicode value
	 * @public static
	 */
	public static function uniord($uch) {
		if (!isset(self::$cache_uniord[$uch])) {
			self::$cache_uniord[$uch] = self::getUniord($uch);
		}
		return self::$cache_uniord[$uch];
	}

	/**
	 * Converts UTF-8 character to integer value.<br>
	 * Invalid byte sequences will be replaced with 0xFFFD (replacement character)<br>
	 * Based on: http://www.faqs.org/rfcs/rfc3629.html
	 * <pre>
	 *    Char. number range  |        UTF-8 octet sequence
	 *       (hexadecimal)    |              (binary)
	 *    --------------------+-----------------------------------------------
	 *    0000 0000-0000 007F | 0xxxxxxx
	 *    0000 0080-0000 07FF | 110xxxxx 10xxxxxx
	 *    0000 0800-0000 FFFF | 1110xxxx 10xxxxxx 10xxxxxx
	 *    0001 0000-0010 FFFF | 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
	 *    ---------------------------------------------------------------------
	 *
	 *   ABFN notation:
	 *   ---------------------------------------------------------------------
	 *   UTF8-octets = *( UTF8-char )
	 *   UTF8-char   = UTF8-1 / UTF8-2 / UTF8-3 / UTF8-4
	 *   UTF8-1      = %x00-7F
	 *   UTF8-2      = %xC2-DF UTF8-tail
	 *
	 *   UTF8-3      = %xE0 %xA0-BF UTF8-tail / %xE1-EC 2( UTF8-tail ) /
	 *                 %xED %x80-9F UTF8-tail / %xEE-EF 2( UTF8-tail )
	 *   UTF8-4      = %xF0 %x90-BF 2( UTF8-tail ) / %xF1-F3 3( UTF8-tail ) /
	 *                 %xF4 %x80-8F 2( UTF8-tail )
	 *   UTF8-tail   = %x80-BF
	 *   ---------------------------------------------------------------------
	 * </pre>
	 * @param $uch (string) character string to process.
	 * @return integer Unicode value
	 * @author Nicola Asuni
	 * @public static
	 */
	public static function getUniord($uch) {
		if (function_exists('mb_convert_encoding')) {
			list(, $char) = @unpack('N', mb_convert_encoding($uch, 'UCS-4BE', 'UTF-8'));
			if ($char >= 0) {
				return $char;
			}
		}
		$bytes = array(); // array containing single character byte sequences
		$countbytes = 0;
		$numbytes = 1; // number of octetc needed to represent the UTF-8 character
		$length = strlen($uch);
		for ($i = 0; $i < $length; ++$i) {
			$char = ord($uch[$i]); // get one string character at time
			if ($countbytes == 0) { // get starting octect
				if ($char <= 0x7F) {
					return $char; // use the character "as is" because is ASCII
				} elseif (($char >> 0x05) == 0x06) { // 2 bytes character (0x06 = 110 BIN)
					$bytes[] = ($char - 0xC0) << 0x06;
					++$countbytes;
					$numbytes = 2;
				} elseif (($char >> 0x04) == 0x0E) { // 3 bytes character (0x0E = 1110 BIN)
					$bytes[] = ($char - 0xE0) << 0x0C;
					++$countbytes;
					$numbytes = 3;
				} elseif (($char >> 0x03) == 0x1E) { // 4 bytes character (0x1E = 11110 BIN)
					$bytes[] = ($char - 0xF0) << 0x12;
					++$countbytes;
					$numbytes = 4;
				} else {
					// use replacement character for other invalid sequences
					return 0xFFFD;
				}
			} elseif (($char >> 0x06) == 0x02) { // bytes 2, 3 and 4 must start with 0x02 = 10 BIN
				$bytes[] = $char - 0x80;
				++$countbytes;
				if ($countbytes == $numbytes) {
					// compose UTF-8 bytes to a single unicode value
					$char = $bytes[0];
					for ($j = 1; $j < $numbytes; ++$j) {
						$char += ($bytes[$j] << (($numbytes - $j - 1) * 0x06));
					}
					if ((($char >= 0xD800) AND ($char <= 0xDFFF)) OR ($char >= 0x10FFFF)) {
						// The definition of UTF-8 prohibits encoding character numbers between
						// U+D800 and U+DFFF, which are reserved for use with the UTF-16
						// encoding form (as surrogate pairs) and do not directly represent
						// characters.
						return 0xFFFD; // use replacement character
					} else {
						return $char;
					}
				}
			} else {
				// use replacement character for other invalid sequences
				return 0xFFFD;
			}
		}
		return 0xFFFD;
	}

	/**
	 * Converts UTF-8 strings to codepoints array.<br>
	 * Invalid byte sequences will be replaced with 0xFFFD (replacement character)<br>
	 * @param $str (string) string to process.
	 * @param $isunicode (boolean) True when the documetn is in Unicode mode, false otherwise.
	 * @param $currentfont (array) Reference to current font array.
	 * @return array containing codepoints (UTF-8 characters values)
	 * @author Nicola Asuni
	 * @public static
	 */
	public static function UTF8StringToArray($str, $isunicode=true, &$currentfont) {
		if ($isunicode) {
			// requires PCRE unicode support turned on
			$chars = TCPDF_STATIC::pregSplit('//','u', $str, -1, PREG_SPLIT_NO_EMPTY);
			$carr = array_map(array('self', 'uniord'), $chars);
		} else {
			$chars = str_split($str);
			$carr = array_map('ord', $chars);
		}
		$currentfont['subsetchars'] += array_fill_keys($carr, true);
		return $carr;
	}

	/**
	 * Converts UTF-8 strings to Latin1 when using the standard 14 core fonts.<br>
	 * @param $str (string) string to process.
	 * @param $isunicode (boolean) True when the documetn is in Unicode mode, false otherwise.
	 * @param $currentfont (array) Reference to current font array.
	 * @return string
	 * @since 3.2.000 (2008-06-23)
	 * @public static
	 */
	public static function UTF8ToLatin1($str, $isunicode=true, &$currentfont) {
		$unicode = self::UTF8StringToArray($str, $isunicode, $currentfont); // array containing UTF-8 unicode values
		return self::UTF8ArrToLatin1($unicode);
	}

	/**
	 * Converts UTF-8 strings to UTF16-BE.<br>
	 * @param $str (string) string to process.
	 * @param $setbom (boolean) if true set the Byte Order Mark (BOM = 0xFEFF)
	 * @param $isunicode (boolean) True when the documetn is in Unicode mode, false otherwise.
	 * @param $currentfont (array) Reference to current font array.
	 * @return string
	 * @author Nicola Asuni
	 * @since 1.53.0.TC005 (2005-01-05)
	 * @public static
	 */
	public static function UTF8ToUTF16BE($str, $setbom=false, $isunicode=true, &$currentfont) {
		if (!$isunicode) {
			return $str; // string is not in unicode
		}
		$unicode = self::UTF8StringToArray($str, $isunicode, $currentfont); // array containing UTF-8 unicode values
		return self::arrUTF8ToUTF16BE($unicode, $setbom);
	}

	/**
	 * Reverse the RLT substrings using the Bidirectional Algorithm (http://unicode.org/reports/tr9/).
	 * @param $str (string) string to manipulate.
	 * @param $setbom (bool) if true set the Byte Order Mark (BOM = 0xFEFF)
	 * @param $forcertl (bool) if true forces RTL text direction
	 * @param $isunicode (boolean) True if the document is in Unicode mode, false otherwise.
	 * @param $currentfont (array) Reference to current font array.
	 * @return string
	 * @author Nicola Asuni
	 * @since 2.1.000 (2008-01-08)
	 * @public static
	 */
	public static function utf8StrRev($str, $setbom=false, $forcertl=false, $isunicode=true, &$currentfont) {
		return self::utf8StrArrRev(self::UTF8StringToArray($str, $isunicode, $currentfont), $str, $setbom, $forcertl, $isunicode, $currentfont);
	}

	/**
	 * Reverse the RLT substrings array using the Bidirectional Algorithm (http://unicode.org/reports/tr9/).
	 * @param $arr (array) array of unicode values.
	 * @param $str (string) string to manipulate (or empty value).
	 * @param $setbom (bool) if true set the Byte Order Mark (BOM = 0xFEFF)
	 * @param $forcertl (bool) if true forces RTL text direction
	 * @param $isunicode (boolean) True if the document is in Unicode mode, false otherwise.
	 * @param $currentfont (array) Reference to current font array.
	 * @return string
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-27)
	 * @public static
	 */
	public static function utf8StrArrRev($arr, $str='', $setbom=false, $forcertl=false, $isunicode=true, &$currentfont) {
		return self::arrUTF8ToUTF16BE(self::utf8Bidi($arr, $str, $forcertl, $isunicode, $currentfont), $setbom);
	}

	/**
	 * Reverse the RLT substrings using the Bidirectional Algorithm (http://unicode.org/reports/tr9/).
	 * @param $ta (array) array of characters composing the string.
	 * @param $str (string) string to process
	 * @param $forcertl (bool) if 'R' forces RTL, if 'L' forces LTR
	 * @param $isunicode (boolean) True if the document is in Unicode mode, false otherwise.
	 * @param $currentfont (array) Reference to current font array.
	 * @return array of unicode chars
	 * @author Nicola Asuni
	 * @since 2.4.000 (2008-03-06)
	 * @public static
	 */
	public static function utf8Bidi($ta, $str='', $forcertl=false, $isunicode=true, &$currentfont) {
		// paragraph embedding level
		$pel = 0;
		// max level
		$maxlevel = 0;
		if (TCPDF_STATIC::empty_string($str)) {
			// create string from array
			$str = self::UTF8ArrSubString($ta, '', '', $isunicode);
		}
		// check if string contains arabic text
		if (preg_match(TCPDF_FONT_DATA::$uni_RE_PATTERN_ARABIC, $str)) {
			$arabic = true;
		} else {
			$arabic = false;
		}
		// check if string contains RTL text
		if (!($forcertl OR $arabic OR preg_match(TCPDF_FONT_DATA::$uni_RE_PATTERN_RTL, $str))) {
			return $ta;
		}

		// get number of chars
		$numchars = count($ta);

		if ($forcertl == 'R') {
			$pel = 1;
		} elseif ($forcertl == 'L') {
			$pel = 0;
		} else {
			// P2. In each paragraph, find the first character of type L, AL, or R.
			// P3. If a character is found in P2 and it is of type AL or R, then set the paragraph embedding level to one; otherwise, set it to zero.
			for ($i=0; $i < $numchars; ++$i) {
				$type = TCPDF_FONT_DATA::$uni_type[$ta[$i]];
				if ($type == 'L') {
					$pel = 0;
					break;
				} elseif (($type == 'AL') OR ($type == 'R')) {
					$pel = 1;
					break;
				}
			}
		}

		// Current Embedding Level
		$cel = $pel;
		// directional override status
		$dos = 'N';
		$remember = array();
		// start-of-level-run
		$sor = $pel % 2 ? 'R' : 'L';
		$eor = $sor;

		// Array of characters data
		$chardata = Array();

		// X1. Begin by setting the current embedding level to the paragraph embedding level. Set the directional override status to neutral. Process each character iteratively, applying rules X2 through X9. Only embedding levels from 0 to 61 are valid in this phase.
		// In the resolution of levels in rules I1 and I2, the maximum embedding level of 62 can be reached.
		for ($i=0; $i < $numchars; ++$i) {
			if ($ta[$i] == TCPDF_FONT_DATA::$uni_RLE) {
				// X2. With each RLE, compute the least greater odd embedding level.
				//	a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to neutral.
				//	b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
				$next_level = $cel + ($cel % 2) + 1;
				if ($next_level < 62) {
					$remember[] = array('num' => TCPDF_FONT_DATA::$uni_RLE, 'cel' => $cel, 'dos' => $dos);
					$cel = $next_level;
					$dos = 'N';
					$sor = $eor;
					$eor = $cel % 2 ? 'R' : 'L';
				}
			} elseif ($ta[$i] == TCPDF_FONT_DATA::$uni_LRE) {
				// X3. With each LRE, compute the least greater even embedding level.
				//	a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to neutral.
				//	b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
				$next_level = $cel + 2 - ($cel % 2);
				if ( $next_level < 62 ) {
					$remember[] = array('num' => TCPDF_FONT_DATA::$uni_LRE, 'cel' => $cel, 'dos' => $dos);
					$cel = $next_level;
					$dos = 'N';
					$sor = $eor;
					$eor = $cel % 2 ? 'R' : 'L';
				}
			} elseif ($ta[$i] == TCPDF_FONT_DATA::$uni_RLO) {
				// X4. With each RLO, compute the least greater odd embedding level.
				//	a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to right-to-left.
				//	b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
				$next_level = $cel + ($cel % 2) + 1;
				if ($next_level < 62) {
					$remember[] = array('num' => TCPDF_FONT_DATA::$uni_RLO, 'cel' => $cel, 'dos' => $dos);
					$cel = $next_level;
					$dos = 'R';
					$sor = $eor;
					$eor = $cel % 2 ? 'R' : 'L';
				}
			} elseif ($ta[$i] == TCPDF_FONT_DATA::$uni_LRO) {
				// X5. With each LRO, compute the least greater even embedding level.
				//	a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to left-to-right.
				//	b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
				$next_level = $cel + 2 - ($cel % 2);
				if ( $next_level < 62 ) {
					$remember[] = array('num' => TCPDF_FONT_DATA::$uni_LRO, 'cel' => $cel, 'dos' => $dos);
					$cel = $next_level;
					$dos = 'L';
					$sor = $eor;
					$eor = $cel % 2 ? 'R' : 'L';
				}
			} elseif ($ta[$i] == TCPDF_FONT_DATA::$uni_PDF) {
				// X7. With each PDF, determine the matching embedding or override code. If there was a valid matching code, restore (pop) the last remembered (pushed) embedding level and directional override.
				if (count($remember)) {
					$last = count($remember ) - 1;
					if (($remember[$last]['num'] == TCPDF_FONT_DATA::$uni_RLE) OR
						($remember[$last]['num'] == TCPDF_FONT_DATA::$uni_LRE) OR
						($remember[$last]['num'] == TCPDF_FONT_DATA::$uni_RLO) OR
						($remember[$last]['num'] == TCPDF_FONT_DATA::$uni_LRO)) {
						$match = array_pop($remember);
						$cel = $match['cel'];
						$dos = $match['dos'];
						$sor = $eor;
						$eor = ($cel > $match['cel'] ? $cel : $match['cel']) % 2 ? 'R' : 'L';
					}
				}
			} elseif (($ta[$i] != TCPDF_FONT_DATA::$uni_RLE) AND
							 ($ta[$i] != TCPDF_FONT_DATA::$uni_LRE) AND
							 ($ta[$i] != TCPDF_FONT_DATA::$uni_RLO) AND
							 ($ta[$i] != TCPDF_FONT_DATA::$uni_LRO) AND
							 ($ta[$i] != TCPDF_FONT_DATA::$uni_PDF)) {
				// X6. For all types besides RLE, LRE, RLO, LRO, and PDF:
				//	a. Set the level of the current character to the current embedding level.
				//	b. Whenever the directional override status is not neutral, reset the current character type to the directional override status.
				if ($dos != 'N') {
					$chardir = $dos;
				} else {
					if (isset(TCPDF_FONT_DATA::$uni_type[$ta[$i]])) {
						$chardir = TCPDF_FONT_DATA::$uni_type[$ta[$i]];
					} else {
						$chardir = 'L';
					}
				}
				// stores string characters and other information
				$chardata[] = array('char' => $ta[$i], 'level' => $cel, 'type' => $chardir, 'sor' => $sor, 'eor' => $eor);
			}
		} // end for each char

		// X8. All explicit directional embeddings and overrides are completely terminated at the end of each paragraph. Paragraph separators are not included in the embedding.
		// X9. Remove all RLE, LRE, RLO, LRO, PDF, and BN codes.
		// X10. The remaining rules are applied to each run of characters at the same level. For each run, determine the start-of-level-run (sor) and end-of-level-run (eor) type, either L or R. This depends on the higher of the two levels on either side of the boundary (at the start or end of the paragraph, the level of the 'other' run is the base embedding level). If the higher level is odd, the type is R; otherwise, it is L.

		// 3.3.3 Resolving Weak Types
		// Weak types are now resolved one level run at a time. At level run boundaries where the type of the character on the other side of the boundary is required, the type assigned to sor or eor is used.
		// Nonspacing marks are now resolved based on the previous characters.
		$numchars = count($chardata);

		// W1. Examine each nonspacing mark (NSM) in the level run, and change the type of the NSM to the type of the previous character. If the NSM is at the start of the level run, it will get the type of sor.
		$prevlevel = -1; // track level changes
		$levcount = 0; // counts consecutive chars at the same level
		for ($i=0; $i < $numchars; ++$i) {
			if ($chardata[$i]['type'] == 'NSM') {
				if ($levcount) {
					$chardata[$i]['type'] = $chardata[$i]['sor'];
				} elseif ($i > 0) {
					$chardata[$i]['type'] = $chardata[($i-1)]['type'];
				}
			}
			if ($chardata[$i]['level'] != $prevlevel) {
				$levcount = 0;
			} else {
				++$levcount;
			}
			$prevlevel = $chardata[$i]['level'];
		}

		// W2. Search backward from each instance of a European number until the first strong type (R, L, AL, or sor) is found. If an AL is found, change the type of the European number to Arabic number.
		$prevlevel = -1;
		$levcount = 0;
		for ($i=0; $i < $numchars; ++$i) {
			if ($chardata[$i]['char'] == 'EN') {
				for ($j=$levcount; $j >= 0; $j--) {
					if ($chardata[$j]['type'] == 'AL') {
						$chardata[$i]['type'] = 'AN';
					} elseif (($chardata[$j]['type'] == 'L') OR ($chardata[$j]['type'] == 'R')) {
						break;
					}
				}
			}
			if ($chardata[$i]['level'] != $prevlevel) {
				$levcount = 0;
			} else {
				++$levcount;
			}
			$prevlevel = $chardata[$i]['level'];
		}

		// W3. Change all ALs to R.
		for ($i=0; $i < $numchars; ++$i) {
			if ($chardata[$i]['type'] == 'AL') {
				$chardata[$i]['type'] = 'R';
			}
		}

		// W4. A single European separator between two European numbers changes to a European number. A single common separator between two numbers of the same type changes to that type.
		$prevlevel = -1;
		$levcount = 0;
		for ($i=0; $i < $numchars; ++$i) {
			if (($levcount > 0) AND (($i+1) < $numchars) AND ($chardata[($i+1)]['level'] == $prevlevel)) {
				if (($chardata[$i]['type'] == 'ES') AND ($chardata[($i-1)]['type'] == 'EN') AND ($chardata[($i+1)]['type'] == 'EN')) {
					$chardata[$i]['type'] = 'EN';
				} elseif (($chardata[$i]['type'] == 'CS') AND ($chardata[($i-1)]['type'] == 'EN') AND ($chardata[($i+1)]['type'] == 'EN')) {
					$chardata[$i]['type'] = 'EN';
				} elseif (($chardata[$i]['type'] == 'CS') AND ($chardata[($i-1)]['type'] == 'AN') AND ($chardata[($i+1)]['type'] == 'AN')) {
					$chardata[$i]['type'] = 'AN';
				}
			}
			if ($chardata[$i]['level'] != $prevlevel) {
				$levcount = 0;
			} else {
				++$levcount;
			}
			$prevlevel = $chardata[$i]['level'];
		}

		// W5. A sequence of European terminators adjacent to European numbers changes to all European numbers.
		$prevlevel = -1;
		$levcount = 0;
		for ($i=0; $i < $numchars; ++$i) {
			if ($chardata[$i]['type'] == 'ET') {
				if (($levcount > 0) AND ($chardata[($i-1)]['type'] == 'EN')) {
					$chardata[$i]['type'] = 'EN';
				} else {
					$j = $i+1;
					while (($j < $numchars) AND ($chardata[$j]['level'] == $prevlevel)) {
						if ($chardata[$j]['type'] == 'EN') {
							$chardata[$i]['type'] = 'EN';
							break;
						} elseif ($chardata[$j]['type'] != 'ET') {
							break;
						}
						++$j;
					}
				}
			}
			if ($chardata[$i]['level'] != $prevlevel) {
				$levcount = 0;
			} else {
				++$levcount;
			}
			$prevlevel = $chardata[$i]['level'];
		}

		// W6. Otherwise, separators and terminators change to Other Neutral.
		$prevlevel = -1;
		$levcount = 0;
		for ($i=0; $i < $numchars; ++$i) {
			if (($chardata[$i]['type'] == 'ET') OR ($chardata[$i]['type'] == 'ES') OR ($chardata[$i]['type'] == 'CS')) {
				$chardata[$i]['type'] = 'ON';
			}
			if ($chardata[$i]['level'] != $prevlevel) {
				$levcount = 0;
			} else {
				++$levcount;
			}
			$prevlevel = $chardata[$i]['level'];
		}

		//W7. Search backward from each instance of a European number until the first strong type (R, L, or sor) is found. If an L is found, then change the type of the European number to L.
		$prevlevel = -1;
		$levcount = 0;
		for ($i=0; $i < $numchars; ++$i) {
			if ($chardata[$i]['char'] == 'EN') {
				for ($j=$levcount; $j >= 0; $j--) {
					if ($chardata[$j]['type'] == 'L') {
						$chardata[$i]['type'] = 'L';
					} elseif ($chardata[$j]['type'] == 'R') {
						break;
					}
				}
			}
			if ($chardata[$i]['level'] != $prevlevel) {
				$levcount = 0;
			} else {
				++$levcount;
			}
			$prevlevel = $chardata[$i]['level'];
		}

		// N1. A sequence of neutrals takes the direction of the surrounding strong text if the text on both sides has the same direction. European and Arabic numbers act as if they were R in terms of their influence on neutrals. Start-of-level-run (sor) and end-of-level-run (eor) are used at level run boundaries.
		$prevlevel = -1;
		$levcount = 0;
		for ($i=0; $i < $numchars; ++$i) {
			if (($levcount > 0) AND (($i+1) < $numchars) AND ($chardata[($i+1)]['level'] == $prevlevel)) {
				if (($chardata[$i]['type'] == 'N') AND ($chardata[($i-1)]['type'] == 'L') AND ($chardata[($i+1)]['type'] == 'L')) {
					$chardata[$i]['type'] = 'L';
				} elseif (($chardata[$i]['type'] == 'N') AND
				 (($chardata[($i-1)]['type'] == 'R') OR ($chardata[($i-1)]['type'] == 'EN') OR ($chardata[($i-1)]['type'] == 'AN')) AND
				 (($chardata[($i+1)]['type'] == 'R') OR ($chardata[($i+1)]['type'] == 'EN') OR ($chardata[($i+1)]['type'] == 'AN'))) {
					$chardata[$i]['type'] = 'R';
				} elseif ($chardata[$i]['type'] == 'N') {
					// N2. Any remaining neutrals take the embedding direction
					$chardata[$i]['type'] = $chardata[$i]['sor'];
				}
			} elseif (($levcount == 0) AND (($i+1) < $numchars) AND ($chardata[($i+1)]['level'] == $prevlevel)) {
				// first char
				if (($chardata[$i]['type'] == 'N') AND ($chardata[$i]['sor'] == 'L') AND ($chardata[($i+1)]['type'] == 'L')) {
					$chardata[$i]['type'] = 'L';
				} elseif (($chardata[$i]['type'] == 'N') AND
				 (($chardata[$i]['sor'] == 'R') OR ($chardata[$i]['sor'] == 'EN') OR ($chardata[$i]['sor'] == 'AN')) AND
				 (($chardata[($i+1)]['type'] == 'R') OR ($chardata[($i+1)]['type'] == 'EN') OR ($chardata[($i+1)]['type'] == 'AN'))) {
					$chardata[$i]['type'] = 'R';
				} elseif ($chardata[$i]['type'] == 'N') {
					// N2. Any remaining neutrals take the embedding direction
					$chardata[$i]['type'] = $chardata[$i]['sor'];
				}
			} elseif (($levcount > 0) AND ((($i+1) == $numchars) OR (($i+1) < $numchars) AND ($chardata[($i+1)]['level'] != $prevlevel))) {
				//last char
				if (($chardata[$i]['type'] == 'N') AND ($chardata[($i-1)]['type'] == 'L') AND ($chardata[$i]['eor'] == 'L')) {
					$chardata[$i]['type'] = 'L';
				} elseif (($chardata[$i]['type'] == 'N') AND
				 (($chardata[($i-1)]['type'] == 'R') OR ($chardata[($i-1)]['type'] == 'EN') OR ($chardata[($i-1)]['type'] == 'AN')) AND
				 (($chardata[$i]['eor'] == 'R') OR ($chardata[$i]['eor'] == 'EN') OR ($chardata[$i]['eor'] == 'AN'))) {
					$chardata[$i]['type'] = 'R';
				} elseif ($chardata[$i]['type'] == 'N') {
					// N2. Any remaining neutrals take the embedding direction
					$chardata[$i]['type'] = $chardata[$i]['sor'];
				}
			} elseif ($chardata[$i]['type'] == 'N') {
				// N2. Any remaining neutrals take the embedding direction
				$chardata[$i]['type'] = $chardata[$i]['sor'];
			}
			if ($chardata[$i]['level'] != $prevlevel) {
				$levcount = 0;
			} else {
				++$levcount;
			}
			$prevlevel = $chardata[$i]['level'];
		}

		// I1. For all characters with an even (left-to-right) embedding direction, those of type R go up one level and those of type AN or EN go up two levels.
		// I2. For all characters with an odd (right-to-left) embedding direction, those of type L, EN or AN go up one level.
		for ($i=0; $i < $numchars; ++$i) {
			$odd = $chardata[$i]['level'] % 2;
			if ($odd) {
				if (($chardata[$i]['type'] == 'L') OR ($chardata[$i]['type'] == 'AN') OR ($chardata[$i]['type'] == 'EN')) {
					$chardata[$i]['level'] += 1;
				}
			} else {
				if ($chardata[$i]['type'] == 'R') {
					$chardata[$i]['level'] += 1;
				} elseif (($chardata[$i]['type'] == 'AN') OR ($chardata[$i]['type'] == 'EN')) {
					$chardata[$i]['level'] += 2;
				}
			}
			$maxlevel = max($chardata[$i]['level'],$maxlevel);
		}

		// L1. On each line, reset the embedding level of the following characters to the paragraph embedding level:
		//	1. Segment separators,
		//	2. Paragraph separators,
		//	3. Any sequence of whitespace characters preceding a segment separator or paragraph separator, and
		//	4. Any sequence of white space characters at the end of the line.
		for ($i=0; $i < $numchars; ++$i) {
			if (($chardata[$i]['type'] == 'B') OR ($chardata[$i]['type'] == 'S')) {
				$chardata[$i]['level'] = $pel;
			} elseif ($chardata[$i]['type'] == 'WS') {
				$j = $i+1;
				while ($j < $numchars) {
					if ((($chardata[$j]['type'] == 'B') OR ($chardata[$j]['type'] == 'S')) OR
						(($j == ($numchars-1)) AND ($chardata[$j]['type'] == 'WS'))) {
						$chardata[$i]['level'] = $pel;
						break;
					} elseif ($chardata[$j]['type'] != 'WS') {
						break;
					}
					++$j;
				}
			}
		}

		// Arabic Shaping
		// Cursively connected scripts, such as Arabic or Syriac, require the selection of positional character shapes that depend on adjacent characters. Shaping is logically applied after the Bidirectional Algorithm is used and is limited to characters within the same directional run.
		if ($arabic) {
			$endedletter = array(1569,1570,1571,1572,1573,1575,1577,1583,1584,1585,1586,1608,1688);
			$alfletter = array(1570,1571,1573,1575);
			$chardata2 = $chardata;
			$laaletter = false;
			$charAL = array();
			$x = 0;
			for ($i=0; $i < $numchars; ++$i) {
				if ((TCPDF_FONT_DATA::$uni_type[$chardata[$i]['char']] == 'AL') OR ($chardata[$i]['char'] == 32) OR ($chardata[$i]['char'] == 8204)) {
					$charAL[$x] = $chardata[$i];
					$charAL[$x]['i'] = $i;
					$chardata[$i]['x'] = $x;
					++$x;
				}
			}
			$numAL = $x;
			for ($i=0; $i < $numchars; ++$i) {
				$thischar = $chardata[$i];
				if ($i > 0) {
					$prevchar = $chardata[($i-1)];
				} else {
					$prevchar = false;
				}
				if (($i+1) < $numchars) {
					$nextchar = $chardata[($i+1)];
				} else {
					$nextchar = false;
				}
				if (TCPDF_FONT_DATA::$uni_type[$thischar['char']] == 'AL') {
					$x = $thischar['x'];
					if ($x > 0) {
						$prevchar = $charAL[($x-1)];
					} else {
						$prevchar = false;
					}
					if (($x+1) < $numAL) {
						$nextchar = $charAL[($x+1)];
					} else {
						$nextchar = false;
					}
					// if laa letter
					if (($prevchar !== false) AND ($prevchar['char'] == 1604) AND (in_array($thischar['char'], $alfletter))) {
						$arabicarr = TCPDF_FONT_DATA::$uni_laa_array;
						$laaletter = true;
						if ($x > 1) {
							$prevchar = $charAL[($x-2)];
						} else {
							$prevchar = false;
						}
					} else {
						$arabicarr = TCPDF_FONT_DATA::$uni_arabicsubst;
						$laaletter = false;
					}
					if (($prevchar !== false) AND ($nextchar !== false) AND
						((TCPDF_FONT_DATA::$uni_type[$prevchar['char']] == 'AL') OR (TCPDF_FONT_DATA::$uni_type[$prevchar['char']] == 'NSM')) AND
						((TCPDF_FONT_DATA::$uni_type[$nextchar['char']] == 'AL') OR (TCPDF_FONT_DATA::$uni_type[$nextchar['char']] == 'NSM')) AND
						($prevchar['type'] == $thischar['type']) AND
						($nextchar['type'] == $thischar['type']) AND
						($nextchar['char'] != 1567)) {
						if (in_array($prevchar['char'], $endedletter)) {
							if (isset($arabicarr[$thischar['char']][2])) {
								// initial
								$chardata2[$i]['char'] = $arabicarr[$thischar['char']][2];
							}
						} else {
							if (isset($arabicarr[$thischar['char']][3])) {
								// medial
								$chardata2[$i]['char'] = $arabicarr[$thischar['char']][3];
							}
						}
					} elseif (($nextchar !== false) AND
						((TCPDF_FONT_DATA::$uni_type[$nextchar['char']] == 'AL') OR (TCPDF_FONT_DATA::$uni_type[$nextchar['char']] == 'NSM')) AND
						($nextchar['type'] == $thischar['type']) AND
						($nextchar['char'] != 1567)) {
						if (isset($arabicarr[$chardata[$i]['char']][2])) {
							// initial
							$chardata2[$i]['char'] = $arabicarr[$thischar['char']][2];
						}
					} elseif ((($prevchar !== false) AND
						((TCPDF_FONT_DATA::$uni_type[$prevchar['char']] == 'AL') OR (TCPDF_FONT_DATA::$uni_type[$prevchar['char']] == 'NSM')) AND
						($prevchar['type'] == $thischar['type'])) OR
						(($nextchar !== false) AND ($nextchar['char'] == 1567))) {
						// final
						if (($i > 1) AND ($thischar['char'] == 1607) AND
							($chardata[$i-1]['char'] == 1604) AND
							($chardata[$i-2]['char'] == 1604)) {
							//Allah Word
							// mark characters to delete with false
							$chardata2[$i-2]['char'] = false;
							$chardata2[$i-1]['char'] = false;
							$chardata2[$i]['char'] = 65010;
						} else {
							if (($prevchar !== false) AND in_array($prevchar['char'], $endedletter)) {
								if (isset($arabicarr[$thischar['char']][0])) {
									// isolated
									$chardata2[$i]['char'] = $arabicarr[$thischar['char']][0];
								}
							} else {
								if (isset($arabicarr[$thischar['char']][1])) {
									// final
									$chardata2[$i]['char'] = $arabicarr[$thischar['char']][1];
								}
							}
						}
					} elseif (isset($arabicarr[$thischar['char']][0])) {
						// isolated
						$chardata2[$i]['char'] = $arabicarr[$thischar['char']][0];
					}
					// if laa letter
					if ($laaletter) {
						// mark characters to delete with false
						$chardata2[($charAL[($x-1)]['i'])]['char'] = false;
					}
				} // end if AL (Arabic Letter)
			} // end for each char
			/*
			 * Combining characters that can occur with Arabic Shadda (0651 HEX, 1617 DEC) are replaced.
			 * Putting the combining mark and shadda in the same glyph allows us to avoid the two marks overlapping each other in an illegible manner.
			 */
			for ($i = 0; $i < ($numchars-1); ++$i) {
				if (($chardata2[$i]['char'] == 1617) AND (isset(TCPDF_FONT_DATA::$uni_diacritics[($chardata2[$i+1]['char'])]))) {
					// check if the subtitution font is defined on current font
					if (isset($currentfont['cw'][(TCPDF_FONT_DATA::$uni_diacritics[($chardata2[$i+1]['char'])])])) {
						$chardata2[$i]['char'] = false;
						$chardata2[$i+1]['char'] = TCPDF_FONT_DATA::$uni_diacritics[($chardata2[$i+1]['char'])];
					}
				}
			}
			// remove marked characters
			foreach ($chardata2 as $key => $value) {
				if ($value['char'] === false) {
					unset($chardata2[$key]);
				}
			}
			$chardata = array_values($chardata2);
			$numchars = count($chardata);
			unset($chardata2);
			unset($arabicarr);
			unset($laaletter);
			unset($charAL);
		}

		// L2. From the highest level found in the text to the lowest odd level on each line, including intermediate levels not actually present in the text, reverse any contiguous sequence of characters that are at that level or higher.
		for ($j=$maxlevel; $j > 0; $j--) {
			$ordarray = Array();
			$revarr = Array();
			$onlevel = false;
			for ($i=0; $i < $numchars; ++$i) {
				if ($chardata[$i]['level'] >= $j) {
					$onlevel = true;
					if (isset(TCPDF_FONT_DATA::$uni_mirror[$chardata[$i]['char']])) {
						// L4. A character is depicted by a mirrored glyph if and only if (a) the resolved directionality of that character is R, and (b) the Bidi_Mirrored property value of that character is true.
						$chardata[$i]['char'] = TCPDF_FONT_DATA::$uni_mirror[$chardata[$i]['char']];
					}
					$revarr[] = $chardata[$i];
				} else {
					if ($onlevel) {
						$revarr = array_reverse($revarr);
						$ordarray = array_merge($ordarray, $revarr);
						$revarr = Array();
						$onlevel = false;
					}
					$ordarray[] = $chardata[$i];
				}
			}
			if ($onlevel) {
				$revarr = array_reverse($revarr);
				$ordarray = array_merge($ordarray, $revarr);
			}
			$chardata = $ordarray;
		}
		$ordarray = array();
		foreach ($chardata as $cd) {
			$ordarray[] = $cd['char'];
			// store char values for subsetting
			$currentfont['subsetchars'][$cd['char']] = true;
		}
		return $ordarray;
	}

	/**
	 * Get a reference font size.
	 * @param $size (string) String containing font size value.
	 * @param $refsize (float) Reference font size in points.
	 * @return float value in points
	 * @public static
	 */
	public static function getFontRefSize($size, $refsize=12) {
		switch ($size) {
			case 'xx-small': {
				$size = ($refsize - 4);
				break;
			}
			case 'x-small': {
				$size = ($refsize - 3);
				break;
			}
			case 'small': {
				$size = ($refsize - 2);
				break;
			}
			case 'medium': {
				$size = $refsize;
				break;
			}
			case 'large': {
				$size = ($refsize + 2);
				break;
			}
			case 'x-large': {
				$size = ($refsize + 4);
				break;
			}
			case 'xx-large': {
				$size = ($refsize + 6);
				break;
			}
			case 'smaller': {
				$size = ($refsize - 3);
				break;
			}
			case 'larger': {
				$size = ($refsize + 3);
				break;
			}
		}
		return $size;
	}

} // END OF TCPDF_FONTS CLASS

//============================================================+
// END OF FILE
//============================================================+
