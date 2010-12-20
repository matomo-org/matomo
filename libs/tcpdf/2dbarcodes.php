<?php
//============================================================+
// File name   : 2dbarcodes.php
// Version     : 1.0.007
// Begin       : 2009-04-07
// Last Update : 2010-12-16
// Author      : Nicola Asuni - Tecnick.com S.r.l - Via Della Pace, 11 - 09044 - Quartucciu (CA) - ITALY - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2009-2010  Nicola Asuni - Tecnick.com S.r.l.
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
// Description : PHP class to creates array representations for
//               2D barcodes to be used with TCPDF.
//
//============================================================+

/**
 * @file
 * PHP class to creates array representations for 2D barcodes to be used with TCPDF.
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni
 * @version 1.0.007
 */

/**
 * @class TCPDF2DBarcode
 * PHP class to creates array representations for 2D barcodes to be used with TCPDF (http://www.tcpdf.org).
 * @package com.tecnick.tcpdf
 * @version 1.0.007
 * @author Nicola Asuni
 */
class TCPDF2DBarcode {

	/**
	 * Array representation of barcode.
	 * @protected
	 */
	protected $barcode_array = false;

	/**
	 * This is the class constructor.
	 * Return an array representations for 2D barcodes:<ul>
	 * <li>$arrcode['code'] code to be printed on text label</li>
	 * <li>$arrcode['num_rows'] required number of rows</li>
	 * <li>$arrcode['num_cols'] required number of columns</li>
	 * <li>$arrcode['bcode'][$r][$c] value of the cell is $r row and $c column (0 = transparent, 1 = black)</li></ul>
	 * @param $code (string) code to print
 	 * @param $type (string) type of barcode: <ul><li>RAW: raw mode - comma-separad list of array rows</li><li>RAW2: raw mode - array rows are surrounded by square parenthesis.</li><li>QRCODE : QR-CODE Low error correction</li><li>QRCODE,L : QR-CODE Low error correction</li><li>QRCODE,M : QR-CODE Medium error correction</li><li>QRCODE,Q : QR-CODE Better error correction</li><li>QRCODE,H : QR-CODE Best error correction</li><li>PDF417 : PDF417 (ISO/IEC 15438:2006)</li><li>PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6 : PDF417 with parameters: a = aspect ratio (width/height); e = error correction level (0-8); t = total number of macro segments; s = macro segment index (0-99998); f = file ID; o0 = File Name (text); o1 = Segment Count (numeric); o2 = Time Stamp (numeric); o3 = Sender (text); o4 = Addressee (text); o5 = File Size (numeric); o6 = Checksum (numeric). NOTES: Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional. To use a comma character ',' on text options, replace it with the character 255: "\xff".</li></ul>
	 */
	public function __construct($code, $type) {
		$this->setBarcode($code, $type);
	}

	/**
	 * Return an array representations of barcode.
 	 * @return array
	 */
	public function getBarcodeArray() {
		return $this->barcode_array;
	}

	/**
	 * Set the barcode.
	 * @param $code (string) code to print
 	 * @param $type (string) type of barcode: <ul><li>RAW: raw mode - comma-separad list of array rows</li><li>RAW2: raw mode - array rows are surrounded by square parenthesis.</li><li>QRCODE : QR-CODE Low error correction</li><li>QRCODE,L : QR-CODE Low error correction</li><li>QRCODE,M : QR-CODE Medium error correction</li><li>QRCODE,Q : QR-CODE Better error correction</li><li>QRCODE,H : QR-CODE Best error correction</li><li>PDF417 : PDF417 (ISO/IEC 15438:2006)</li><li>PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6 : PDF417 with parameters: a = aspect ratio (width/height); e = error correction level (0-8); t = total number of macro segments; s = macro segment index (0-99998); f = file ID; o0 = File Name (text); o1 = Segment Count (numeric); o2 = Time Stamp (numeric); o3 = Sender (text); o4 = Addressee (text); o5 = File Size (numeric); o6 = Checksum (numeric). NOTES: Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional. To use a comma character ',' on text options, replace it with the character 255: "\xff".</li></ul>
 	 * @return array
	 */
	public function setBarcode($code, $type) {
		$mode = explode(',', $type);
		$qrtype = strtoupper($mode[0]);
		switch ($qrtype) {
			case 'QRCODE': { // QR-CODE
				require_once(dirname(__FILE__).'/qrcode.php');
				if (!isset($mode[1]) OR (!in_array($mode[1],array('L','M','Q','H')))) {
					$mode[1] = 'L'; // Ddefault: Low error correction
				}
				$qrcode = new QRcode($code, strtoupper($mode[1]));
				$this->barcode_array = $qrcode->getBarcodeArray();
				break;
			}
			case 'PDF417': { // PDF417 (ISO/IEC 15438:2006)
				require_once(dirname(__FILE__).'/pdf417.php');
				if (!isset($mode[1]) OR ($mode[1] === '')) {
					$aspectratio = 2; // default aspect ratio (width / height)
				} else {
					$aspectratio = floatval($mode[1]);
				}
				if (!isset($mode[2]) OR ($mode[2] === '')) {
					$ecl = -1; // default error correction level (auto)
				} else {
					$ecl = intval($mode[2]);
				}
				// set macro block
				$macro = array();
				if (isset($mode[3]) AND ($mode[3] !== '') AND isset($mode[4]) AND ($mode[4] !== '') AND isset($mode[5]) AND ($mode[5] !== '')) {
					$macro['segment_total'] = intval($mode[3]);
					$macro['segment_index'] = intval($mode[4]);
					$macro['file_id'] = strtr($mode[5], "\xff", ',');
					for ($i = 0; $i < 7; ++$i) {
						$o = $i + 6;
						if (isset($mode[$o]) AND ($mode[$o] !== '')) {
							// add option
							$macro['option_'.$i] = strtr($mode[$o], "\xff", ',');
						}
					}
				}
				$qrcode = new PDF417($code, $ecl, $aspectratio, $macro);
				$this->barcode_array = $qrcode->getBarcodeArray();
				break;
			}
			case 'RAW':
			case 'RAW2': { // RAW MODE
				// remove spaces
				$code = preg_replace('/[\s]*/si', '', $code);
				if (strlen($code) < 3) {
					break;
				}
				if ($qrtype == 'RAW') {
					// comma-separated rows
					$rows = explode(',', $code);
				} else { // RAW2
					// rows enclosed in square parentheses
					$code = substr($code, 1, -1);
					$rows = explode('][', $code);
				}
				$this->barcode_array['num_rows'] = count($rows);
				$this->barcode_array['num_cols'] = strlen($rows[0]);
				$this->barcode_array['bcode'] = array();
				foreach ($rows as $r) {
					$this->barcode_array['bcode'][] = str_split($r, 1);
				}
				break;
			}
			case 'TEST': { // TEST MODE
				$this->barcode_array['num_rows'] = 5;
				$this->barcode_array['num_cols'] = 15;
				$this->barcode_array['bcode'] = array(
					array(1,1,1,0,1,1,1,0,1,1,1,0,1,1,1),
					array(0,1,0,0,1,0,0,0,1,0,0,0,0,1,0),
					array(0,1,0,0,1,1,0,0,1,1,1,0,0,1,0),
					array(0,1,0,0,1,0,0,0,0,0,1,0,0,1,0),
					array(0,1,0,0,1,1,1,0,1,1,1,0,0,1,0));
				break;
			}
			default: {
				$this->barcode_array = false;
			}
		}
	}
} // end of class

//============================================================+
// END OF FILE
//============================================================+
