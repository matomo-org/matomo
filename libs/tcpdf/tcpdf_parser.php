<?php
//============================================================+
// File name   : tcpdf_parser.php
// Version     : 1.0.011
// Begin       : 2011-05-23
// Last Update : 2013-10-13
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT GNU-LGPLv3
// -------------------------------------------------------------------
// Copyright (C) 2011-2013 Nicola Asuni - Tecnick.com LTD
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
// Description : This is a PHP class for parsing PDF documents.
//
//============================================================+

/**
 * @file
 * This is a PHP class for parsing PDF documents.<br>
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni
 * @version 1.0.011
 */

// include class for decoding filters
require_once(dirname(__FILE__).'/include/tcpdf_filters.php');

/**
 * @class TCPDF_PARSER
 * This is a PHP class for parsing PDF documents.<br>
 * @package com.tecnick.tcpdf
 * @brief This is a PHP class for parsing PDF documents..
 * @version 1.0.010
 * @author Nicola Asuni - info@tecnick.com
 */
class TCPDF_PARSER {

	/**
	 * Raw content of the PDF document.
	 * @private
	 */
	private $pdfdata = '';

	/**
	 * XREF data.
	 * @protected
	 */
	protected $xref = array();

	/**
	 * Array of PDF objects.
	 * @protected
	 */
	protected $objects = array();

	/**
	 * Class object for decoding filters.
	 * @private
	 */
	private $FilterDecoders;

	/**
	 * Array of configuration parameters.
	 * @private
	 */
	private $cfg = array(
		'die_for_errors' => false,
		'ignore_filter_decoding_errors' => true,
		'ignore_missing_filter_decoders' => true,
	);

// -----------------------------------------------------------------------------

	/**
	 * Parse a PDF document an return an array of objects.
	 * @param $data (string) PDF data to parse.
	 * @param $cfg (array) Array of configuration parameters:
	 * 			'die_for_errors' : if true termitate the program execution in case of error, otherwise thows an exception;
	 * 			'ignore_filter_decoding_errors' : if true ignore filter decoding errors;
	 * 			'ignore_missing_filter_decoders' : if true ignore missing filter decoding errors.
	 * @public
	 * @since 1.0.000 (2011-05-24)
	 */
	public function __construct($data, $cfg=array()) {
		if (empty($data)) {
			$this->Error('Empty PDF data.');
		}
		// set configuration parameters
		$this->setConfig($cfg);
		// get PDF content string
		$this->pdfdata = $data;
		// get length
		$pdflen = strlen($this->pdfdata);
		// get xref and trailer data
		$this->xref = $this->getXrefData();
		// parse all document objects
		$this->objects = array();
		foreach ($this->xref['xref'] as $obj => $offset) {
			if (!isset($this->objects[$obj]) AND ($offset > 0)) {
				// decode objects with positive offset
				$this->objects[$obj] = $this->getIndirectObject($obj, $offset, true);
			}
		}
		// release some memory
		unset($this->pdfdata);
		$this->pdfdata = '';
	}

	/**
	 * Set the configuration parameters.
	 * @param $cfg (array) Array of configuration parameters:
	 * 			'die_for_errors' : if true termitate the program execution in case of error, otherwise thows an exception;
	 * 			'ignore_filter_decoding_errors' : if true ignore filter decoding errors;
	 * 			'ignore_missing_filter_decoders' : if true ignore missing filter decoding errors.
	 * @public
	 */
	protected function setConfig($cfg) {
		if (isset($cfg['die_for_errors'])) {
			$this->cfg['die_for_errors'] = !!$cfg['die_for_errors'];
		}
		if (isset($cfg['ignore_filter_decoding_errors'])) {
			$this->cfg['ignore_filter_decoding_errors'] = !!$cfg['ignore_filter_decoding_errors'];
		}
		if (isset($cfg['ignore_missing_filter_decoders'])) {
			$this->cfg['ignore_missing_filter_decoders'] = !!$cfg['ignore_missing_filter_decoders'];
		}
	}

	/**
	 * Return an array of parsed PDF document objects.
	 * @return (array) Array of parsed PDF document objects.
	 * @public
	 * @since 1.0.000 (2011-06-26)
	 */
	public function getParsedData() {
		return array($this->xref, $this->objects);
	}

	/**
	 * Get Cross-Reference (xref) table and trailer data from PDF document data.
	 * @param $offset (int) xref offset (if know).
	 * @param $xref (array) previous xref array (if any).
	 * @return Array containing xref and trailer data.
	 * @protected
	 * @since 1.0.000 (2011-05-24)
	 */
	protected function getXrefData($offset=0, $xref=array()) {
		if ($offset == 0) {
			// find last startxref
			if (preg_match_all('/[\r\n]startxref[\s]*[\r\n]+([0-9]+)[\s]*[\r\n]+%%EOF/i', $this->pdfdata, $matches, PREG_SET_ORDER, $offset) == 0) {
				$this->Error('Unable to find startxref');
			}
			$matches = array_pop($matches);
			$startxref = $matches[1];
		} elseif (strpos($this->pdfdata, 'xref', $offset) == $offset) {
			// Already pointing at the xref table
			$startxref = $offset;
		} elseif (preg_match('/([0-9]+[\s][0-9]+[\s]obj)/i', $this->pdfdata, $matches, PREG_OFFSET_CAPTURE, $offset)) {
			// Cross-Reference Stream object
			$startxref = $offset;
		} elseif (preg_match('/[\r\n]startxref[\s]*[\r\n]+([0-9]+)[\s]*[\r\n]+%%EOF/i', $this->pdfdata, $matches, PREG_OFFSET_CAPTURE, $offset)) {
			// startxref found
			$startxref = $matches[1][0];
		} else {
			$this->Error('Unable to find startxref');
		}
		// check xref position
		if (strpos($this->pdfdata, 'xref', $startxref) == $startxref) {
			// Cross-Reference
			$xref = $this->decodeXref($startxref, $xref);
		} else {
			// Cross-Reference Stream
			$xref = $this->decodeXrefStream($startxref, $xref);
		}
		if (empty($xref)) {
			$this->Error('Unable to find xref');
		}
		return $xref;
	}

	/**
	 * Decode the Cross-Reference section
	 * @param $startxref (int) Offset at which the xref section starts (position of the 'xref' keyword).
	 * @param $xref (array) Previous xref array (if any).
	 * @return Array containing xref and trailer data.
	 * @protected
	 * @since 1.0.000 (2011-06-20)
	 */
	protected function decodeXref($startxref, $xref=array()) {
		$startxref += 4; // 4 is the lenght of the word 'xref'
		// skip initial white space chars: \x00 null (NUL), \x09 horizontal tab (HT), \x0A line feed (LF), \x0C form feed (FF), \x0D carriage return (CR), \x20 space (SP)
		$offset = $startxref + strspn($this->pdfdata, "\x00\x09\x0a\x0c\x0d\x20", $startxref);
		// initialize object number
		$obj_num = 0;
		// search for cross-reference entries or subsection
		while (preg_match('/([0-9]+)[\x20]([0-9]+)[\x20]?([nf]?)(\r\n|[\x20]?[\r\n])/', $this->pdfdata, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
			if ($matches[0][1] != $offset) {
				// we are on another section
				break;
			}
			$offset += strlen($matches[0][0]);
			if ($matches[3][0] == 'n') {
				// create unique object index: [object number]_[generation number]
				$index = $obj_num.'_'.intval($matches[2][0]);
				// check if object already exist
				if (!isset($xref['xref'][$index])) {
					// store object offset position
					$xref['xref'][$index] = intval($matches[1][0]);
				}
				++$obj_num;
			} elseif ($matches[3][0] == 'f') {
				++$obj_num;
			} else {
				// object number (index)
				$obj_num = intval($matches[1][0]);
			}
		}
		// get trailer data
		if (preg_match('/trailer[\s]*<<(.*)>>[\s]*[\r\n]+startxref[\s]*[\r\n]+/isU', $this->pdfdata, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
			$trailer_data = $matches[1][0];
			if (!isset($xref['trailer']) OR empty($xref['trailer'])) {
				// get only the last updated version
				$xref['trailer'] = array();
				// parse trailer_data
				if (preg_match('/Size[\s]+([0-9]+)/i', $trailer_data, $matches) > 0) {
					$xref['trailer']['size'] = intval($matches[1]);
				}
				if (preg_match('/Root[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
					$xref['trailer']['root'] = intval($matches[1]).'_'.intval($matches[2]);
				}
				if (preg_match('/Encrypt[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
					$xref['trailer']['encrypt'] = intval($matches[1]).'_'.intval($matches[2]);
				}
				if (preg_match('/Info[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
					$xref['trailer']['info'] = intval($matches[1]).'_'.intval($matches[2]);
				}
				if (preg_match('/ID[\s]*[\[][\s]*[<]([^>]*)[>][\s]*[<]([^>]*)[>]/i', $trailer_data, $matches) > 0) {
					$xref['trailer']['id'] = array();
					$xref['trailer']['id'][0] = $matches[1];
					$xref['trailer']['id'][1] = $matches[2];
				}
			}
			if (preg_match('/Prev[\s]+([0-9]+)/i', $trailer_data, $matches) > 0) {
				// get previous xref
				$xref = $this->getXrefData(intval($matches[1]), $xref);
			}
		} else {
			$this->Error('Unable to find trailer');
		}
		return $xref;
	}

	/**
	 * Decode the Cross-Reference Stream section
	 * @param $startxref (int) Offset at which the xref section starts.
	 * @param $xref (array) Previous xref array (if any).
	 * @return Array containing xref and trailer data.
	 * @protected
	 * @since 1.0.003 (2013-03-16)
	 */
	protected function decodeXrefStream($startxref, $xref=array()) {
		// try to read Cross-Reference Stream
		$xrefobj = $this->getRawObject($startxref);
		$xrefcrs = $this->getIndirectObject($xrefobj[1], $startxref, true);
		if (!isset($xref['trailer']) OR empty($xref['trailer'])) {
			// get only the last updated version
			$xref['trailer'] = array();
			$filltrailer = true;
		} else {
			$filltrailer = false;
		}
		$valid_crs = false;
		$sarr = $xrefcrs[0][1];
		foreach ($sarr as $k => $v) {
			if (($v[0] == '/') AND ($v[1] == 'Type') AND (isset($sarr[($k +1)]) AND ($sarr[($k +1)][0] == '/') AND ($sarr[($k +1)][1] == 'XRef'))) {
				$valid_crs = true;
			} elseif (($v[0] == '/') AND ($v[1] == 'Index') AND (isset($sarr[($k +1)]))) {
				// first object number in the subsection
				$index_first = intval($sarr[($k +1)][1][0][1]);
				// number of entries in the subsection
				$index_entries = intval($sarr[($k +1)][1][1][1]);
			} elseif (($v[0] == '/') AND ($v[1] == 'Prev') AND (isset($sarr[($k +1)]) AND ($sarr[($k +1)][0] == 'numeric'))) {
				// get previous xref offset
				$prevxref = intval($sarr[($k +1)][1]);
			} elseif (($v[0] == '/') AND ($v[1] == 'W') AND (isset($sarr[($k +1)]))) {
				// number of bytes (in the decoded stream) of the corresponding field
				$wb = array();
				$wb[0] = intval($sarr[($k +1)][1][0][1]);
				$wb[1] = intval($sarr[($k +1)][1][1][1]);
				$wb[2] = intval($sarr[($k +1)][1][2][1]);
			} elseif (($v[0] == '/') AND ($v[1] == 'DecodeParms') AND (isset($sarr[($k +1)][1]))) {
				$decpar = $sarr[($k +1)][1];
				foreach ($decpar as $kdc => $vdc) {
					if (($vdc[0] == '/') AND ($vdc[1] == 'Columns') AND (isset($decpar[($kdc +1)]) AND ($decpar[($kdc +1)][0] == 'numeric'))) {
						$columns = intval($decpar[($kdc +1)][1]);
					} elseif (($vdc[0] == '/') AND ($vdc[1] == 'Predictor') AND (isset($decpar[($kdc +1)]) AND ($decpar[($kdc +1)][0] == 'numeric'))) {
						$predictor = intval($decpar[($kdc +1)][1]);
					}
				}
			} elseif ($filltrailer) {
				if (($v[0] == '/') AND ($v[1] == 'Size') AND (isset($sarr[($k +1)]) AND ($sarr[($k +1)][0] == 'numeric'))) {
					$xref['trailer']['size'] = $sarr[($k +1)][1];
				} elseif (($v[0] == '/') AND ($v[1] == 'Root') AND (isset($sarr[($k +1)]) AND ($sarr[($k +1)][0] == 'objref'))) {
					$xref['trailer']['root'] = $sarr[($k +1)][1];
				} elseif (($v[0] == '/') AND ($v[1] == 'Info') AND (isset($sarr[($k +1)]) AND ($sarr[($k +1)][0] == 'objref'))) {
					$xref['trailer']['info'] = $sarr[($k +1)][1];
				} elseif (($v[0] == '/') AND ($v[1] == 'ID') AND (isset($sarr[($k +1)]))) {
					$xref['trailer']['id'] = array();
					$xref['trailer']['id'][0] = $sarr[($k +1)][1][0][1];
					$xref['trailer']['id'][1] = $sarr[($k +1)][1][1][1];
				}
			}
		}
		// decode data
		if ($valid_crs AND isset($xrefcrs[1][3][0])) {
			// number of bytes in a row
			$rowlen = ($columns + 1);
			// convert the stream into an array of integers
			$sdata = unpack('C*', $xrefcrs[1][3][0]);
			// split the rows
			$sdata = array_chunk($sdata, $rowlen);
			// initialize decoded array
			$ddata = array();
			// initialize first row with zeros
			$prev_row = array_fill (0, $rowlen, 0);
			// for each row apply PNG unpredictor
			foreach ($sdata as $k => $row) {
				// initialize new row
				$ddata[$k] = array();
				// get PNG predictor value
				$predictor = (10 + $row[0]);
				// for each byte on the row
				for ($i=1; $i<=$columns; ++$i) {
					// new index
					$j = ($i - 1);
					$row_up = $prev_row[$j];
					if ($i == 1) {
						$row_left = 0;
						$row_upleft = 0;
					} else {
						$row_left = $row[($i - 1)];
						$row_upleft = $prev_row[($j - 1)];
					}
					switch ($predictor) {
						case 10: { // PNG prediction (on encoding, PNG None on all rows)
							$ddata[$k][$j] = $row[$i];
							break;
						}
						case 11: { // PNG prediction (on encoding, PNG Sub on all rows)
							$ddata[$k][$j] = (($row[$i] + $row_left) & 0xff);
							break;
						}
						case 12: { // PNG prediction (on encoding, PNG Up on all rows)
							$ddata[$k][$j] = (($row[$i] + $row_up) & 0xff);
							break;
						}
						case 13: { // PNG prediction (on encoding, PNG Average on all rows)
							$ddata[$k][$j] = (($row[$i] + (($row_left + $row_up) / 2)) & 0xff);
							break;
						}
						case 14: { // PNG prediction (on encoding, PNG Paeth on all rows)
							// initial estimate
							$p = ($row_left + $row_up - $row_upleft);
							// distances
							$pa = abs($p - $row_left);
							$pb = abs($p - $row_up);
							$pc = abs($p - $row_upleft);
							$pmin = min($pa, $pb, $pc);
							// return minumum distance
							switch ($pmin) {
								case $pa: {
									$ddata[$k][$j] = (($row[$i] + $row_left) & 0xff);
									break;
								}
								case $pb: {
									$ddata[$k][$j] = (($row[$i] + $row_up) & 0xff);
									break;
								}
								case $pc: {
									$ddata[$k][$j] = (($row[$i] + $row_upleft) & 0xff);
									break;
								}
							}
							break;
						}
						default: { // PNG prediction (on encoding, PNG optimum)
							$this->Error('Unknown PNG predictor');
							break;
						}
					}
				}
				$prev_row = $ddata[$k];
			} // end for each row
			// complete decoding
			$sdata = array();
			// for every row
			foreach ($ddata as $k => $row) {
				// initialize new row
				$sdata[$k] = array(0, 0, 0);
				if ($wb[0] == 0) {
					// default type field
					$sdata[$k][0] = 1;
				}
				$i = 0; // count bytes on the row
				// for every column
				for ($c = 0; $c < 3; ++$c) {
					// for every byte on the column
					for ($b = 0; $b < $wb[$c]; ++$b) {
						$sdata[$k][$c] += ($row[$i] << (($wb[$c] - 1 - $b) * 8));
						++$i;
					}
				}
			}
			$ddata = array();
			// fill xref
			if (isset($index_first)) {
				$obj_num = $index_first;
			} else {
				$obj_num = 0;
			}
			foreach ($sdata as $k => $row) {
				switch ($row[0]) {
					case 0: { // (f) linked list of free objects
						++$obj_num;
						break;
					}
					case 1: { // (n) objects that are in use but are not compressed
						// create unique object index: [object number]_[generation number]
						$index = $obj_num.'_'.$row[2];
						// check if object already exist
						if (!isset($xref['xref'][$index])) {
							// store object offset position
							$xref['xref'][$index] = $row[1];
						}
						++$obj_num;
						break;
					}
					case 2: { // compressed objects
						// $row[1] = object number of the object stream in which this object is stored
						// $row[2] = index of this object within the object stream
						$index = $row[1].'_0_'.$row[2];
						$xref['xref'][$index] = -1;
						break;
					}
					default: { // null objects
						break;
					}
				}
			}
		} // end decoding data
		if (isset($prevxref)) {
			// get previous xref
			$xref = $this->getXrefData($prevxref, $xref);
		}
		return $xref;
	}

	/**
	 * Get object type, raw value and offset to next object
	 * @param $offset (int) Object offset.
	 * @return array containing object type, raw value and offset to next object
	 * @protected
	 * @since 1.0.000 (2011-06-20)
	 */
	protected function getRawObject($offset=0) {
		$objtype = ''; // object type to be returned
		$objval = ''; // object value to be returned
		// skip initial white space chars: \x00 null (NUL), \x09 horizontal tab (HT), \x0A line feed (LF), \x0C form feed (FF), \x0D carriage return (CR), \x20 space (SP)
		$offset += strspn($this->pdfdata, "\x00\x09\x0a\x0c\x0d\x20", $offset);
		// get first char
		$char = $this->pdfdata[$offset];
		// get object type
		switch ($char) {
			case '%': { // \x25 PERCENT SIGN
				// skip comment and search for next token
				$next = strcspn($this->pdfdata, "\r\n", $offset);
				if ($next > 0) {
					$offset += $next;
					return $this->getRawObject($offset);
				}
				break;
			}
			case '/': { // \x2F SOLIDUS
				// name object
				$objtype = $char;
				++$offset;
				if (preg_match('/^([^\x00\x09\x0a\x0c\x0d\x20\s\x28\x29\x3c\x3e\x5b\x5d\x7b\x7d\x2f\x25]+)/', substr($this->pdfdata, $offset, 256), $matches) == 1) {
					$objval = $matches[1]; // unescaped value
					$offset += strlen($objval);
				}
				break;
			}
			case '(':   // \x28 LEFT PARENTHESIS
			case ')': { // \x29 RIGHT PARENTHESIS
				// literal string object
				$objtype = $char;
				++$offset;
				$strpos = $offset;
				if ($char == '(') {
					$open_bracket = 1;
					while ($open_bracket > 0) {
						if (!isset($this->pdfdata{$strpos})) {
							break;
						}
						$ch = $this->pdfdata{$strpos};
						switch ($ch) {
							case '\\': { // REVERSE SOLIDUS (5Ch) (Backslash)
								// skip next character
								++$strpos;
								break;
							}
							case '(': { // LEFT PARENHESIS (28h)
								++$open_bracket;
								break;
							}
							case ')': { // RIGHT PARENTHESIS (29h)
								--$open_bracket;
								break;
							}
						}
						++$strpos;
					}
					$objval = substr($this->pdfdata, $offset, ($strpos - $offset - 1));
					$offset = $strpos;
				}
				break;
			}
			case '[':   // \x5B LEFT SQUARE BRACKET
			case ']': { // \x5D RIGHT SQUARE BRACKET
				// array object
				$objtype = $char;
				++$offset;
				if ($char == '[') {
					// get array content
					$objval = array();
					do {
						// get element
						$element = $this->getRawObject($offset);
						$offset = $element[2];
						$objval[] = $element;
					} while ($element[0] != ']');
					// remove closing delimiter
					array_pop($objval);
				}
				break;
			}
			case '<':   // \x3C LESS-THAN SIGN
			case '>': { // \x3E GREATER-THAN SIGN
				if (isset($this->pdfdata{($offset + 1)}) AND ($this->pdfdata{($offset + 1)} == $char)) {
					// dictionary object
					$objtype = $char.$char;
					$offset += 2;
					if ($char == '<') {
						// get array content
						$objval = array();
						do {
							// get element
							$element = $this->getRawObject($offset);
							$offset = $element[2];
							$objval[] = $element;
						} while ($element[0] != '>>');
						// remove closing delimiter
						array_pop($objval);
					}
				} else {
					// hexadecimal string object
					$objtype = $char;
					++$offset;
					if (($char == '<') AND (preg_match('/^([0-9A-Fa-f\x09\x0a\x0c\x0d\x20]+)>/iU', substr($this->pdfdata, $offset), $matches) == 1)) {
						// remove white space characters
						$objval = strtr($matches[1], "\x09\x0a\x0c\x0d\x20", '');
						$offset += strlen($matches[0]);
					}
				}
				break;
			}
			default: {
				if (substr($this->pdfdata, $offset, 6) == 'endobj') {
					// indirect object
					$objtype = 'endobj';
					$offset += 6;
				} elseif (substr($this->pdfdata, $offset, 4) == 'null') {
					// null object
					$objtype = 'null';
					$offset += 4;
					$objval = 'null';
				} elseif (substr($this->pdfdata, $offset, 4) == 'true') {
					// boolean true object
					$objtype = 'boolean';
					$offset += 4;
					$objval = 'true';
				} elseif (substr($this->pdfdata, $offset, 5) == 'false') {
					// boolean false object
					$objtype = 'boolean';
					$offset += 5;
					$objval = 'false';
				} elseif (substr($this->pdfdata, $offset, 6) == 'stream') {
					// start stream object
					$objtype = 'stream';
					$offset += 6;
					if (preg_match('/^([\r]?[\n])/isU', substr($this->pdfdata, $offset), $matches) == 1) {
						$offset += strlen($matches[0]);
						if (preg_match('/([\r]?[\n])?(endstream)[\x09\x0a\x0c\x0d\x20]/isU', substr($this->pdfdata, $offset), $matches, PREG_OFFSET_CAPTURE) == 1) {
							$objval = substr($this->pdfdata, $offset, $matches[0][1]);
							$offset += $matches[2][1];
						}
					}
				} elseif (substr($this->pdfdata, $offset, 9) == 'endstream') {
					// end stream object
					$objtype = 'endstream';
					$offset += 9;
				} elseif (preg_match('/^([0-9]+)[\s]+([0-9]+)[\s]+R/iU', substr($this->pdfdata, $offset, 33), $matches) == 1) {
					// indirect object reference
					$objtype = 'objref';
					$offset += strlen($matches[0]);
					$objval = intval($matches[1]).'_'.intval($matches[2]);
				} elseif (preg_match('/^([0-9]+)[\s]+([0-9]+)[\s]+obj/iU', substr($this->pdfdata, $offset, 33), $matches) == 1) {
					// object start
					$objtype = 'obj';
					$objval = intval($matches[1]).'_'.intval($matches[2]);
					$offset += strlen ($matches[0]);
				} elseif (($numlen = strspn($this->pdfdata, '+-.0123456789', $offset)) > 0) {
					// numeric object
					$objtype = 'numeric';
					$objval = substr($this->pdfdata, $offset, $numlen);
					$offset += $numlen;
				}
				break;
			}
		}
		return array($objtype, $objval, $offset);
	}

	/**
	 * Get content of indirect object.
	 * @param $obj_ref (string) Object number and generation number separated by underscore character.
	 * @param $offset (int) Object offset.
	 * @param $decoding (boolean) If true decode streams.
	 * @return array containing object data.
	 * @protected
	 * @since 1.0.000 (2011-05-24)
	 */
	protected function getIndirectObject($obj_ref, $offset=0, $decoding=true) {
		$obj = explode('_', $obj_ref);
		if (($obj === false) OR (count($obj) != 2)) {
			$this->Error('Invalid object reference: '.$obj);
			return;
		}
		$objref = $obj[0].' '.$obj[1].' obj';
		// ignore leading zeros
		$offset += strspn($this->pdfdata, '0', $offset);
		if (strpos($this->pdfdata, $objref, $offset) != $offset) {
			// an indirect reference to an undefined object shall be considered a reference to the null object
			return array('null', 'null', $offset);
		}
		// starting position of object content
		$offset += strlen($objref);
		// get array of object content
		$objdata = array();
		$i = 0; // object main index
		do {
			// get element
			$element = $this->getRawObject($offset);
			$offset = $element[2];
			// decode stream using stream's dictionary information
			if ($decoding AND ($element[0] == 'stream') AND (isset($objdata[($i - 1)][0])) AND ($objdata[($i - 1)][0] == '<<')) {
				$element[3] = $this->decodeStream($objdata[($i - 1)][1], $element[1]);
			}
			$objdata[$i] = $element;
			++$i;
		} while ($element[0] != 'endobj');
		// remove closing delimiter
		array_pop($objdata);
		// return raw object content
		return $objdata;
	}

	/**
	 * Get the content of object, resolving indect object reference if necessary.
	 * @param $obj (string) Object value.
	 * @return array containing object data.
	 * @protected
	 * @since 1.0.000 (2011-06-26)
	 */
	protected function getObjectVal($obj) {
		if ($obj[0] == 'objref') {
			// reference to indirect object
			if (isset($this->objects[$obj[1]])) {
				// this object has been already parsed
				return $this->objects[$obj[1]];
			} elseif (isset($this->xref[$obj[1]])) {
				// parse new object
				$this->objects[$obj[1]] = $this->getIndirectObject($obj[1], $this->xref[$obj[1]], false);
				return $this->objects[$obj[1]];
			}
		}
		return $obj;
	}

	/**
	 * Decode the specified stream.
	 * @param $sdic (array) Stream's dictionary array.
	 * @param $stream (string) Stream to decode.
	 * @return array containing decoded stream data and remaining filters.
	 * @protected
	 * @since 1.0.000 (2011-06-22)
	 */
	protected function decodeStream($sdic, $stream) {
		// get stream lenght and filters
		$slength = strlen($stream);
		if ($slength <= 0) {
			return array('', array());
		}
		$filters = array();
		foreach ($sdic as $k => $v) {
			if ($v[0] == '/') {
				if (($v[1] == 'Length') AND (isset($sdic[($k + 1)])) AND ($sdic[($k + 1)][0] == 'numeric')) {
					// get declared stream lenght
					$declength = intval($sdic[($k + 1)][1]);
					if ($declength < $slength) {
						$stream = substr($stream, 0, $declength);
						$slength = $declength;
					}
				} elseif (($v[1] == 'Filter') AND (isset($sdic[($k + 1)]))) {
					// resolve indirect object
					$objval = $this->getObjectVal($sdic[($k + 1)]);
					if ($objval[0] == '/') {
						// single filter
						$filters[] = $objval[1];
					} elseif ($objval[0] == '[') {
						// array of filters
						foreach ($objval[1] as $flt) {
							if ($flt[0] == '/') {
								$filters[] = $flt[1];
							}
						}
					}
				}
			}
		}
		// decode the stream
		$remaining_filters = array();
		foreach ($filters as $filter) {
			if (in_array($filter, TCPDF_FILTERS::getAvailableFilters())) {
				try {
					$stream = TCPDF_FILTERS::decodeFilter($filter, $stream);
				} catch (Exception $e) {
					$emsg = $e->getMessage();
					if ((($emsg[0] == '~') AND !$this->cfg['ignore_missing_filter_decoders'])
						OR (($emsg[0] != '~') AND !$this->cfg['ignore_filter_decoding_errors'])) {
						$this->Error($e->getMessage());
					}
				}
			} else {
				// add missing filter to array
				$remaining_filters[] = $filter;
			}
		}
		return array($stream, $remaining_filters);
	}

	/**
	 * Throw an exception or print an error message and die if the K_TCPDF_PARSER_THROW_EXCEPTION_ERROR constant is set to true.
	 * @param $msg (string) The error message
	 * @public
	 * @since 1.0.000 (2011-05-23)
	 */
	public function Error($msg) {
		if ($this->cfg['die_for_errors']) {
			die('<strong>TCPDF_PARSER ERROR: </strong>'.$msg);
		} else {
			throw new Exception('TCPDF_PARSER ERROR: '.$msg);
		}
	}

} // END OF TCPDF_PARSER CLASS

//============================================================+
// END OF FILE
//============================================================+
