<?php
//============================================================+
// File name   : tcpdf.php
// Version     : 6.0.044
// Begin       : 2002-08-03
// Last Update : 2013-10-29
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2002-2013 Nicola Asuni - Tecnick.com LTD
//
// This file is part of TCPDF software library.
//
// TCPDF is free software: you can ioredistribute it and/or modify it
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
//   This is a PHP class for generating PDF documents without requiring external extensions.
//
// NOTE:
//   This class was originally derived in 2002 from the Public
//   Domain FPDF class by Olivier Plathey (http://www.fpdf.org),
//   but now is almost entirely rewritten and contains thousands of
//   new lines of code and hundreds new features.
//
// Main features:
//  * no external libraries are required for the basic functions;
//  * all standard page formats, custom page formats, custom margins and units of measure;
//  * UTF-8 Unicode and Right-To-Left languages;
//  * TrueTypeUnicode, TrueType, Type1 and CID-0 fonts;
//  * font subsetting;
//  * methods to publish some XHTML + CSS code, Javascript and Forms;
//  * images, graphic (geometric figures) and transformation methods;
//  * supports JPEG, PNG and SVG images natively, all images supported by GD (GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM) and all images supported via ImagMagick (http://www.imagemagick.org/www/formats.html)
//  * 1D and 2D barcodes: CODE 39, ANSI MH10.8M-1983, USD-3, 3 of 9, CODE 93, USS-93, Standard 2 of 5, Interleaved 2 of 5, CODE 128 A/B/C, 2 and 5 Digits UPC-Based Extention, EAN 8, EAN 13, UPC-A, UPC-E, MSI, POSTNET, PLANET, RMS4CC (Royal Mail 4-state Customer Code), CBC (Customer Bar Code), KIX (Klant index - Customer index), Intelligent Mail Barcode, Onecode, USPS-B-3200, CODABAR, CODE 11, PHARMACODE, PHARMACODE TWO-TRACKS, Datamatrix, QR-Code, PDF417;
//  * JPEG and PNG ICC profiles, Grayscale, RGB, CMYK, Spot Colors and Transparencies;
//  * automatic page header and footer management;
//  * document encryption up to 256 bit and digital signature certifications;
//  * transactions to UNDO commands;
//  * PDF annotations, including links, text and file attachments;
//  * text rendering modes (fill, stroke and clipping);
//  * multiple columns mode;
//  * no-write page regions;
//  * bookmarks, named destinations and table of content;
//  * text hyphenation;
//  * text stretching and spacing (tracking);
//  * automatic page break, line break and text alignments including justification;
//  * automatic page numbering and page groups;
//  * move and delete pages;
//  * page compression (requires php-zlib extension);
//  * XOBject Templates;
//  * Layers and object visibility.
//	* PDF/A-1b support
//============================================================+

/**
 * @file
 * This is a PHP class for generating PDF documents without requiring external extensions.<br>
 * TCPDF project (http://www.tcpdf.org) was originally derived in 2002 from the Public Domain FPDF class by Olivier Plathey (http://www.fpdf.org), but now is almost entirely rewritten.<br>
 * <h3>TCPDF main features are:</h3>
 * <ul>
 * <li>no external libraries are required for the basic functions;</li>
 * <li>all standard page formats, custom page formats, custom margins and units of measure;</li>
 * <li>UTF-8 Unicode and Right-To-Left languages;</li>
 * <li>TrueTypeUnicode, TrueType, Type1 and CID-0 fonts;</li>
 * <li>font subsetting;</li>
 * <li>methods to publish some XHTML + CSS code, Javascript and Forms;</li>
 * <li>images, graphic (geometric figures) and transformation methods;
 * <li>supports JPEG, PNG and SVG images natively, all images supported by GD (GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM) and all images supported via ImagMagick (http://www.imagemagick.org/www/formats.html)</li>
 * <li>1D and 2D barcodes: CODE 39, ANSI MH10.8M-1983, USD-3, 3 of 9, CODE 93, USS-93, Standard 2 of 5, Interleaved 2 of 5, CODE 128 A/B/C, 2 and 5 Digits UPC-Based Extention, EAN 8, EAN 13, UPC-A, UPC-E, MSI, POSTNET, PLANET, RMS4CC (Royal Mail 4-state Customer Code), CBC (Customer Bar Code), KIX (Klant index - Customer index), Intelligent Mail Barcode, Onecode, USPS-B-3200, CODABAR, CODE 11, PHARMACODE, PHARMACODE TWO-TRACKS, Datamatrix, QR-Code, PDF417;</li>
 * <li>JPEG and PNG ICC profiles, Grayscale, RGB, CMYK, Spot Colors and Transparencies;</li>
 * <li>automatic page header and footer management;</li>
 * <li>document encryption up to 256 bit and digital signature certifications;</li>
 * <li>transactions to UNDO commands;</li>
 * <li>PDF annotations, including links, text and file attachments;</li>
 * <li>text rendering modes (fill, stroke and clipping);</li>
 * <li>multiple columns mode;</li>
 * <li>no-write page regions;</li>
 * <li>bookmarks, named destinations and table of content;</li>
 * <li>text hyphenation;</li>
 * <li>text stretching and spacing (tracking);</li>
 * <li>automatic page break, line break and text alignments including justification;</li>
 * <li>automatic page numbering and page groups;</li>
 * <li>move and delete pages;</li>
 * <li>page compression (requires php-zlib extension);</li>
 * <li>XOBject Templates;</li>
 * <li>Layers and object visibility;</li>
 * <li>PDF/A-1b support.</li>
 * </ul>
 * Tools to encode your unicode fonts are on fonts/utils directory.</p>
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni
 * @version 6.0.044
 */

// TCPDF configuration
require_once(dirname(__FILE__).'/tcpdf_autoconfig.php');
// TCPDF static font methods and data
require_once(dirname(__FILE__).'/include/tcpdf_font_data.php');
// TCPDF static font methods and data
require_once(dirname(__FILE__).'/include/tcpdf_fonts.php');
// TCPDF static color methods and data
require_once(dirname(__FILE__).'/include/tcpdf_colors.php');
// TCPDF static image methods and data
require_once(dirname(__FILE__).'/include/tcpdf_images.php');
// TCPDF static methods and data
require_once(dirname(__FILE__).'/include/tcpdf_static.php');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

/**
 * @class TCPDF
 * PHP class for generating PDF documents without requiring external extensions.
 * TCPDF project (http://www.tcpdf.org) has been originally derived in 2002 from the Public Domain FPDF class by Olivier Plathey (http://www.fpdf.org), but now is almost entirely rewritten.<br>
 * @package com.tecnick.tcpdf
 * @brief PHP class for generating PDF documents without requiring external extensions.
 * @version 6.0.044
 * @author Nicola Asuni - info@tecnick.com
 */
class TCPDF {

	// Protected properties

	/**
	 * Current page number.
	 * @protected
	 */
	protected $page;

	/**
	 * Current object number.
	 * @protected
	 */
	protected $n;

	/**
	 * Array of object offsets.
	 * @protected
	 */
	protected $offsets = array();

	/**
	 * Array of object IDs for each page.
	 * @protected
	 */
	protected $pageobjects = array();

	/**
	 * Buffer holding in-memory PDF.
	 * @protected
	 */
	protected $buffer;

	/**
	 * Array containing pages.
	 * @protected
	 */
	protected $pages = array();

	/**
	 * Current document state.
	 * @protected
	 */
	protected $state;

	/**
	 * Compression flag.
	 * @protected
	 */
	protected $compress;

	/**
	 * Current page orientation (P = Portrait, L = Landscape).
	 * @protected
	 */
	protected $CurOrientation;

	/**
	 * Page dimensions.
	 * @protected
	 */
	protected $pagedim = array();

	/**
	 * Scale factor (number of points in user unit).
	 * @protected
	 */
	protected $k;

	/**
	 * Width of page format in points.
	 * @protected
	 */
	protected $fwPt;

	/**
	 * Height of page format in points.
	 * @protected
	 */
	protected $fhPt;

	/**
	 * Current width of page in points.
	 * @protected
	 */
	protected $wPt;

	/**
	 * Current height of page in points.
	 * @protected
	 */
	protected $hPt;

	/**
	 * Current width of page in user unit.
	 * @protected
	 */
	protected $w;

	/**
	 * Current height of page in user unit.
	 * @protected
	 */
	protected $h;

	/**
	 * Left margin.
	 * @protected
	 */
	protected $lMargin;

	/**
	 * Right margin.
	 * @protected
	 */
	protected $rMargin;

	/**
	 * Cell left margin (used by regions).
	 * @protected
	 */
	protected $clMargin;

	/**
	 * Cell right margin (used by regions).
	 * @protected
	 */
	protected $crMargin;

	/**
	 * Top margin.
	 * @protected
	 */
	protected $tMargin;

	/**
	 * Page break margin.
	 * @protected
	 */
	protected $bMargin;

	/**
	 * Array of cell internal paddings ('T' => top, 'R' => right, 'B' => bottom, 'L' => left).
	 * @since 5.9.000 (2010-10-03)
	 * @protected
	 */
	protected $cell_padding = array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0);

	/**
	 * Array of cell margins ('T' => top, 'R' => right, 'B' => bottom, 'L' => left).
	 * @since 5.9.000 (2010-10-04)
	 * @protected
	 */
	protected $cell_margin = array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0);

	/**
	 * Current horizontal position in user unit for cell positioning.
	 * @protected
	 */
	protected $x;

	/**
	 * Current vertical position in user unit for cell positioning.
	 * @protected
	 */
	protected $y;

	/**
	 * Height of last cell printed.
	 * @protected
	 */
	protected $lasth;

	/**
	 * Line width in user unit.
	 * @protected
	 */
	protected $LineWidth;

	/**
	 * Array of standard font names.
	 * @protected
	 */
	protected $CoreFonts;

	/**
	 * Array of used fonts.
	 * @protected
	 */
	protected $fonts = array();

	/**
	 * Array of font files.
	 * @protected
	 */
	protected $FontFiles = array();

	/**
	 * Array of encoding differences.
	 * @protected
	 */
	protected $diffs = array();

	/**
	 * Array of used images.
	 * @protected
	 */
	protected $images = array();

	/**
	 * Array of cached files.
	 * @protected
	 */
	protected $cached_files = array();

	/**
	 * Array of Annotations in pages.
	 * @protected
	 */
	protected $PageAnnots = array();

	/**
	 * Array of internal links.
	 * @protected
	 */
	protected $links = array();

	/**
	 * Current font family.
	 * @protected
	 */
	protected $FontFamily;

	/**
	 * Current font style.
	 * @protected
	 */
	protected $FontStyle;

	/**
	 * Current font ascent (distance between font top and baseline).
	 * @protected
	 * @since 2.8.000 (2007-03-29)
	 */
	protected $FontAscent;

	/**
	 * Current font descent (distance between font bottom and baseline).
	 * @protected
	 * @since 2.8.000 (2007-03-29)
	 */
	protected $FontDescent;

	/**
	 * Underlining flag.
	 * @protected
	 */
	protected $underline;

	/**
	 * Overlining flag.
	 * @protected
	 */
	protected $overline;

	/**
	 * Current font info.
	 * @protected
	 */
	protected $CurrentFont;

	/**
	 * Current font size in points.
	 * @protected
	 */
	protected $FontSizePt;

	/**
	 * Current font size in user unit.
	 * @protected
	 */
	protected $FontSize;

	/**
	 * Commands for drawing color.
	 * @protected
	 */
	protected $DrawColor;

	/**
	 * Commands for filling color.
	 * @protected
	 */
	protected $FillColor;

	/**
	 * Commands for text color.
	 * @protected
	 */
	protected $TextColor;

	/**
	 * Indicates whether fill and text colors are different.
	 * @protected
	 */
	protected $ColorFlag;

	/**
	 * Automatic page breaking.
	 * @protected
	 */
	protected $AutoPageBreak;

	/**
	 * Threshold used to trigger page breaks.
	 * @protected
	 */
	protected $PageBreakTrigger;

	/**
	 * Flag set when processing page header.
	 * @protected
	 */
	protected $InHeader = false;

	/**
	 * Flag set when processing page footer.
	 * @protected
	 */
	protected $InFooter = false;

	/**
	 * Zoom display mode.
	 * @protected
	 */
	protected $ZoomMode;

	/**
	 * Layout display mode.
	 * @protected
	 */
	protected $LayoutMode;

	/**
	 * If true set the document information dictionary in Unicode.
	 * @protected
	 */
	protected $docinfounicode = true;

	/**
	 * Document title.
	 * @protected
	 */
	protected $title = '';

	/**
	 * Document subject.
	 * @protected
	 */
	protected $subject = '';

	/**
	 * Document author.
	 * @protected
	 */
	protected $author = '';

	/**
	 * Document keywords.
	 * @protected
	 */
	protected $keywords = '';

	/**
	 * Document creator.
	 * @protected
	 */
	protected $creator = '';

	/**
	 * Starting page number.
	 * @protected
	 */
	protected $starting_page_number = 1;

	/**
	 * The right-bottom (or left-bottom for RTL) corner X coordinate of last inserted image.
	 * @since 2002-07-31
	 * @author Nicola Asuni
	 * @protected
	 */
	protected $img_rb_x;

	/**
	 * The right-bottom corner Y coordinate of last inserted image.
	 * @since 2002-07-31
	 * @author Nicola Asuni
	 * @protected
	 */
	protected $img_rb_y;

	/**
	 * Adjusting factor to convert pixels to user units.
	 * @since 2004-06-14
	 * @author Nicola Asuni
	 * @protected
	 */
	protected $imgscale = 1;

	/**
	 * Boolean flag set to true when the input text is unicode (require unicode fonts).
	 * @since 2005-01-02
	 * @author Nicola Asuni
	 * @protected
	 */
	protected $isunicode = false;

	/**
	 * PDF version.
	 * @since 1.5.3
	 * @protected
	 */
	protected $PDFVersion = '1.7';

	/**
	 * ID of the stored default header template (-1 = not set).
	 * @protected
	 */
	protected $header_xobjid = -1;

	/**
	 * If true reset the Header Xobject template at each page
	 * @protected
	 */
	protected $header_xobj_autoreset = false;

	/**
	 * Minimum distance between header and top page margin.
	 * @protected
	 */
	protected $header_margin;

	/**
	 * Minimum distance between footer and bottom page margin.
	 * @protected
	 */
	protected $footer_margin;

	/**
	 * Original left margin value.
	 * @protected
	 * @since 1.53.0.TC013
	 */
	protected $original_lMargin;

	/**
	 * Original right margin value.
	 * @protected
	 * @since 1.53.0.TC013
	 */
	protected $original_rMargin;

	/**
	 * Default font used on page header.
	 * @protected
	 */
	protected $header_font;

	/**
	 * Default font used on page footer.
	 * @protected
	 */
	protected $footer_font;

	/**
	 * Language templates.
	 * @protected
	 */
	protected $l;

	/**
	 * Barcode to print on page footer (only if set).
	 * @protected
	 */
	protected $barcode = false;

	/**
	 * Boolean flag to print/hide page header.
	 * @protected
	 */
	protected $print_header = true;

	/**
	 * Boolean flag to print/hide page footer.
	 * @protected
	 */
	protected $print_footer = true;

	/**
	 * Header image logo.
	 * @protected
	 */
	protected $header_logo = '';

	/**
	 * Width of header image logo in user units.
	 * @protected
	 */
	protected $header_logo_width = 30;

	/**
	 * Title to be printed on default page header.
	 * @protected
	 */
	protected $header_title = '';

	/**
	 * String to pring on page header after title.
	 * @protected
	 */
	protected $header_string = '';

	/**
	 * Color for header text (RGB array).
	 * @since 5.9.174 (2012-07-25)
	 * @protected
	 */
	protected $header_text_color = array(0,0,0);

	/**
	 * Color for header line (RGB array).
	 * @since 5.9.174 (2012-07-25)
	 * @protected
	 */
	protected $header_line_color = array(0,0,0);

	/**
	 * Color for footer text (RGB array).
	 * @since 5.9.174 (2012-07-25)
	 * @protected
	 */
	protected $footer_text_color = array(0,0,0);

	/**
	 * Color for footer line (RGB array).
	 * @since 5.9.174 (2012-07-25)
	 * @protected
	 */
	protected $footer_line_color = array(0,0,0);

	/**
	 * Text shadow data array.
	 * @since 5.9.174 (2012-07-25)
	 * @protected
	 */
	protected $txtshadow = array('enabled'=>false, 'depth_w'=>0, 'depth_h'=>0, 'color'=>false, 'opacity'=>1, 'blend_mode'=>'Normal');

	/**
	 * Default number of columns for html table.
	 * @protected
	 */
	protected $default_table_columns = 4;

	// variables for html parser

	/**
	 * HTML PARSER: array to store current link and rendering styles.
	 * @protected
	 */
	protected $HREF = array();

	/**
	 * List of available fonts on filesystem.
	 * @protected
	 */
	protected $fontlist = array();

	/**
	 * Current foreground color.
	 * @protected
	 */
	protected $fgcolor;

	/**
	 * HTML PARSER: array of boolean values, true in case of ordered list (OL), false otherwise.
	 * @protected
	 */
	protected $listordered = array();

	/**
	 * HTML PARSER: array count list items on nested lists.
	 * @protected
	 */
	protected $listcount = array();

	/**
	 * HTML PARSER: current list nesting level.
	 * @protected
	 */
	protected $listnum = 0;

	/**
	 * HTML PARSER: indent amount for lists.
	 * @protected
	 */
	protected $listindent = 0;

	/**
	 * HTML PARSER: current list indententation level.
	 * @protected
	 */
	protected $listindentlevel = 0;

	/**
	 * Current background color.
	 * @protected
	 */
	protected $bgcolor;

	/**
	 * Temporary font size in points.
	 * @protected
	 */
	protected $tempfontsize = 10;

	/**
	 * Spacer string for LI tags.
	 * @protected
	 */
	protected $lispacer = '';

	/**
	 * Default encoding.
	 * @protected
	 * @since 1.53.0.TC010
	 */
	protected $encoding = 'UTF-8';

	/**
	 * PHP internal encoding.
	 * @protected
	 * @since 1.53.0.TC016
	 */
	protected $internal_encoding;

	/**
	 * Boolean flag to indicate if the document language is Right-To-Left.
	 * @protected
	 * @since 2.0.000
	 */
	protected $rtl = false;

	/**
	 * Boolean flag used to force RTL or LTR string direction.
	 * @protected
	 * @since 2.0.000
	 */
	protected $tmprtl = false;

	// --- Variables used for document encryption:

	/**
	 * IBoolean flag indicating whether document is protected.
	 * @protected
	 * @since 2.0.000 (2008-01-02)
	 */
	protected $encrypted;

	/**
	 * Array containing encryption settings.
	 * @protected
	 * @since 5.0.005 (2010-05-11)
	 */
	protected $encryptdata = array();

	/**
	 * Last RC4 key encrypted (cached for optimisation).
	 * @protected
	 * @since 2.0.000 (2008-01-02)
	 */
	protected $last_enc_key;

	/**
	 * Last RC4 computed key.
	 * @protected
	 * @since 2.0.000 (2008-01-02)
	 */
	protected $last_enc_key_c;

	/**
	 * File ID (used on document trailer).
	 * @protected
	 * @since 5.0.005 (2010-05-12)
	 */
	protected $file_id;

	// --- bookmark ---

	/**
	 * Outlines for bookmark.
	 * @protected
	 * @since 2.1.002 (2008-02-12)
	 */
	protected $outlines = array();

	/**
	 * Outline root for bookmark.
	 * @protected
	 * @since 2.1.002 (2008-02-12)
	 */
	protected $OutlineRoot;

	// --- javascript and form ---

	/**
	 * Javascript code.
	 * @protected
	 * @since 2.1.002 (2008-02-12)
	 */
	protected $javascript = '';

	/**
	 * Javascript counter.
	 * @protected
	 * @since 2.1.002 (2008-02-12)
	 */
	protected $n_js;

	/**
	 * line through state
	 * @protected
	 * @since 2.8.000 (2008-03-19)
	 */
	protected $linethrough;

	/**
	 * Array with additional document-wide usage rights for the document.
	 * @protected
	 * @since 5.8.014 (2010-08-23)
	 */
	protected $ur = array();

	/**
	 * DPI (Dot Per Inch) Document Resolution (do not change).
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $dpi = 72;

	/**
	 * Array of page numbers were a new page group was started (the page numbers are the keys of the array).
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $newpagegroup = array();

	/**
	 * Array that contains the number of pages in each page group.
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $pagegroups = array();

	/**
	 * Current page group number.
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $currpagegroup = 0;

	/**
	 * Array of transparency objects and parameters.
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $extgstates;

	/**
	 * Set the default JPEG compression quality (1-100).
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $jpeg_quality;

	/**
	 * Default cell height ratio.
	 * @protected
	 * @since 3.0.014 (2008-05-23)
	 */
	protected $cell_height_ratio = K_CELL_HEIGHT_RATIO;

	/**
	 * PDF viewer preferences.
	 * @protected
	 * @since 3.1.000 (2008-06-09)
	 */
	protected $viewer_preferences;

	/**
	 * A name object specifying how the document should be displayed when opened.
	 * @protected
	 * @since 3.1.000 (2008-06-09)
	 */
	protected $PageMode;

	/**
	 * Array for storing gradient information.
	 * @protected
	 * @since 3.1.000 (2008-06-09)
	 */
	protected $gradients = array();

	/**
	 * Array used to store positions inside the pages buffer (keys are the page numbers).
	 * @protected
	 * @since 3.2.000 (2008-06-26)
	 */
	protected $intmrk = array();

	/**
	 * Array used to store positions inside the pages buffer (keys are the page numbers).
	 * @protected
	 * @since 5.7.000 (2010-08-03)
	 */
	protected $bordermrk = array();

	/**
	 * Array used to store page positions to track empty pages (keys are the page numbers).
	 * @protected
	 * @since 5.8.007 (2010-08-18)
	 */
	protected $emptypagemrk = array();

	/**
	 * Array used to store content positions inside the pages buffer (keys are the page numbers).
	 * @protected
	 * @since 4.6.021 (2009-07-20)
	 */
	protected $cntmrk = array();

	/**
	 * Array used to store footer positions of each page.
	 * @protected
	 * @since 3.2.000 (2008-07-01)
	 */
	protected $footerpos = array();

	/**
	 * Array used to store footer length of each page.
	 * @protected
	 * @since 4.0.014 (2008-07-29)
	 */
	protected $footerlen = array();

	/**
	 * Boolean flag to indicate if a new line is created.
	 * @protected
	 * @since 3.2.000 (2008-07-01)
	 */
	protected $newline = true;

	/**
	 * End position of the latest inserted line.
	 * @protected
	 * @since 3.2.000 (2008-07-01)
	 */
	protected $endlinex = 0;

	/**
	 * PDF string for width value of the last line.
	 * @protected
	 * @since 4.0.006 (2008-07-16)
	 */
	protected $linestyleWidth = '';

	/**
	 * PDF string for CAP value of the last line.
	 * @protected
	 * @since 4.0.006 (2008-07-16)
	 */
	protected $linestyleCap = '0 J';

	/**
	 * PDF string for join value of the last line.
	 * @protected
	 * @since 4.0.006 (2008-07-16)
	 */
	protected $linestyleJoin = '0 j';

	/**
	 * PDF string for dash value of the last line.
	 * @protected
	 * @since 4.0.006 (2008-07-16)
	 */
	protected $linestyleDash = '[] 0 d';

	/**
	 * Boolean flag to indicate if marked-content sequence is open.
	 * @protected
	 * @since 4.0.013 (2008-07-28)
	 */
	protected $openMarkedContent = false;

	/**
	 * Count the latest inserted vertical spaces on HTML.
	 * @protected
	 * @since 4.0.021 (2008-08-24)
	 */
	protected $htmlvspace = 0;

	/**
	 * Array of Spot colors.
	 * @protected
	 * @since 4.0.024 (2008-09-12)
	 */
	protected $spot_colors = array();

	/**
	 * Symbol used for HTML unordered list items.
	 * @protected
	 * @since 4.0.028 (2008-09-26)
	 */
	protected $lisymbol = '';

	/**
	 * String used to mark the beginning and end of EPS image blocks.
	 * @protected
	 * @since 4.1.000 (2008-10-18)
	 */
	protected $epsmarker = 'x#!#EPS#!#x';

	/**
	 * Array of transformation matrix.
	 * @protected
	 * @since 4.2.000 (2008-10-29)
	 */
	protected $transfmatrix = array();

	/**
	 * Current key for transformation matrix.
	 * @protected
	 * @since 4.8.005 (2009-09-17)
	 */
	protected $transfmatrix_key = 0;

	/**
	 * Booklet mode for double-sided pages.
	 * @protected
	 * @since 4.2.000 (2008-10-29)
	 */
	protected $booklet = false;

	/**
	 * Epsilon value used for float calculations.
	 * @protected
	 * @since 4.2.000 (2008-10-29)
	 */
	protected $feps = 0.005;

	/**
	 * Array used for custom vertical spaces for HTML tags.
	 * @protected
	 * @since 4.2.001 (2008-10-30)
	 */
	protected $tagvspaces = array();

	/**
	 * HTML PARSER: custom indent amount for lists. Negative value means disabled.
	 * @protected
	 * @since 4.2.007 (2008-11-12)
	 */
	protected $customlistindent = -1;

	/**
	 * Boolean flag to indicate if the border of the cell sides that cross the page should be removed.
	 * @protected
	 * @since 4.2.010 (2008-11-14)
	 */
	protected $opencell = true;

	/**
	 * Array of files to embedd.
	 * @protected
	 * @since 4.4.000 (2008-12-07)
	 */
	protected $embeddedfiles = array();

	/**
	 * Boolean flag to indicate if we are inside a PRE tag.
	 * @protected
	 * @since 4.4.001 (2008-12-08)
	 */
	protected $premode = false;

	/**
	 * Array used to store positions of graphics transformation blocks inside the page buffer.
	 * keys are the page numbers
	 * @protected
	 * @since 4.4.002 (2008-12-09)
	 */
	protected $transfmrk = array();

	/**
	 * Default color for html links.
	 * @protected
	 * @since 4.4.003 (2008-12-09)
	 */
	protected $htmlLinkColorArray = array(0, 0, 255);

	/**
	 * Default font style to add to html links.
	 * @protected
	 * @since 4.4.003 (2008-12-09)
	 */
	protected $htmlLinkFontStyle = 'U';

	/**
	 * Counts the number of pages.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $numpages = 0;

	/**
	 * Array containing page lengths in bytes.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $pagelen = array();

	/**
	 * Counts the number of pages.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $numimages = 0;

	/**
	 * Store the image keys.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $imagekeys = array();

	/**
	 * Length of the buffer in bytes.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $bufferlen = 0;

	/**
	 * If true enables disk caching.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $diskcache = false;

	/**
	 * Counts the number of fonts.
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected $numfonts = 0;

	/**
	 * Store the font keys.
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected $fontkeys = array();

	/**
	 * Store the font object IDs.
	 * @protected
	 * @since 4.8.001 (2009-09-09)
	 */
	protected $font_obj_ids = array();

	/**
	 * Store the fage status (true when opened, false when closed).
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected $pageopen = array();

	/**
	 * Default monospace font.
	 * @protected
	 * @since 4.5.025 (2009-03-10)
	 */
	protected $default_monospaced_font = 'courier';

	/**
	 * Cloned copy of the current class object.
	 * @protected
	 * @since 4.5.029 (2009-03-19)
	 */
	protected $objcopy;

	/**
	 * Array used to store the lengths of cache files.
	 * @protected
	 * @since 4.5.029 (2009-03-19)
	 */
	protected $cache_file_length = array();

	/**
	 * Table header content to be repeated on each new page.
	 * @protected
	 * @since 4.5.030 (2009-03-20)
	 */
	protected $thead = '';

	/**
	 * Margins used for table header.
	 * @protected
	 * @since 4.5.030 (2009-03-20)
	 */
	protected $theadMargins = array();

	/**
	 * Boolean flag to enable document digital signature.
	 * @protected
	 * @since 4.6.005 (2009-04-24)
	 */
	protected $sign = false;

	/**
	 * Digital signature data.
	 * @protected
	 * @since 4.6.005 (2009-04-24)
	 */
	protected $signature_data = array();

	/**
	 * Digital signature max length.
	 * @protected
	 * @since 4.6.005 (2009-04-24)
	 */
	protected $signature_max_length = 11742;

	/**
	 * Data for digital signature appearance.
	 * @protected
	 * @since 5.3.011 (2010-06-16)
	 */
	protected $signature_appearance = array('page' => 1, 'rect' => '0 0 0 0');

	/**
	 * Array of empty digital signature appearances.
	 * @protected
	 * @since 5.9.101 (2011-07-06)
	 */
	protected $empty_signature_appearance = array();

	/**
	 * Regular expression used to find blank characters (required for word-wrapping).
	 * @protected
	 * @since 4.6.006 (2009-04-28)
	 */
	protected $re_spaces = '/[^\S\xa0]/';

	/**
	 * Array of $re_spaces parts.
	 * @protected
	 * @since 5.5.011 (2010-07-09)
	 */
	protected $re_space = array('p' => '[^\S\xa0]', 'm' => '');

	/**
	 * Digital signature object ID.
	 * @protected
	 * @since 4.6.022 (2009-06-23)
	 */
	protected $sig_obj_id = 0;

	/**
	 * ID of page objects.
	 * @protected
	 * @since 4.7.000 (2009-08-29)
	 */
	protected $page_obj_id = array();

	/**
	 * List of form annotations IDs.
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $form_obj_id = array();

	/**
	 * Deafult Javascript field properties. Possible values are described on official Javascript for Acrobat API reference. Annotation options can be directly specified using the 'aopt' entry.
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $default_form_prop = array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 255), 'strokeColor'=>array(128, 128, 128));

	/**
	 * Javascript objects array.
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $js_objects = array();

	/**
	 * Current form action (used during XHTML rendering).
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $form_action = '';

	/**
	 * Current form encryption type (used during XHTML rendering).
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $form_enctype = 'application/x-www-form-urlencoded';

	/**
	 * Current method to submit forms.
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $form_mode = 'post';

	/**
	 * List of fonts used on form fields (fontname => fontkey).
	 * @protected
	 * @since 4.8.001 (2009-09-09)
	 */
	protected $annotation_fonts = array();

	/**
	 * List of radio buttons parent objects.
	 * @protected
	 * @since 4.8.001 (2009-09-09)
	 */
	protected $radiobutton_groups = array();

	/**
	 * List of radio group objects IDs.
	 * @protected
	 * @since 4.8.001 (2009-09-09)
	 */
	protected $radio_groups = array();

	/**
	 * Text indentation value (used for text-indent CSS attribute).
	 * @protected
	 * @since 4.8.006 (2009-09-23)
	 */
	protected $textindent = 0;

	/**
	 * Store page number when startTransaction() is called.
	 * @protected
	 * @since 4.8.006 (2009-09-23)
	 */
	protected $start_transaction_page = 0;

	/**
	 * Store Y position when startTransaction() is called.
	 * @protected
	 * @since 4.9.001 (2010-03-28)
	 */
	protected $start_transaction_y = 0;

	/**
	 * True when we are printing the thead section on a new page.
	 * @protected
	 * @since 4.8.027 (2010-01-25)
	 */
	protected $inthead = false;

	/**
	 * Array of column measures (width, space, starting Y position).
	 * @protected
	 * @since 4.9.001 (2010-03-28)
	 */
	protected $columns = array();

	/**
	 * Number of colums.
	 * @protected
	 * @since 4.9.001 (2010-03-28)
	 */
	protected $num_columns = 1;

	/**
	 * Current column number.
	 * @protected
	 * @since 4.9.001 (2010-03-28)
	 */
	protected $current_column = 0;

	/**
	 * Starting page for columns.
	 * @protected
	 * @since 4.9.001 (2010-03-28)
	 */
	protected $column_start_page = 0;

	/**
	 * Maximum page and column selected.
	 * @protected
	 * @since 5.8.000 (2010-08-11)
	 */
	protected $maxselcol = array('page' => 0, 'column' => 0);

	/**
	 * Array of: X difference between table cell x start and starting page margin, cellspacing, cellpadding.
	 * @protected
	 * @since 5.8.000 (2010-08-11)
	 */
	protected $colxshift = array('x' => 0, 's' => array('H' => 0, 'V' => 0), 'p' => array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0));

	/**
	 * Text rendering mode: 0 = Fill text; 1 = Stroke text; 2 = Fill, then stroke text; 3 = Neither fill nor stroke text (invisible); 4 = Fill text and add to path for clipping; 5 = Stroke text and add to path for clipping; 6 = Fill, then stroke text and add to path for clipping; 7 = Add text to path for clipping.
	 * @protected
	 * @since 4.9.008 (2010-04-03)
	 */
	protected $textrendermode = 0;

	/**
	 * Text stroke width in doc units.
	 * @protected
	 * @since 4.9.008 (2010-04-03)
	 */
	protected $textstrokewidth = 0;

	/**
	 * Current stroke color.
	 * @protected
	 * @since 4.9.008 (2010-04-03)
	 */
	protected $strokecolor;

	/**
	 * Default unit of measure for document.
	 * @protected
	 * @since 5.0.000 (2010-04-22)
	 */
	protected $pdfunit = 'mm';

	/**
	 * Boolean flag true when we are on TOC (Table Of Content) page.
	 * @protected
	 */
	protected $tocpage = false;

	/**
	 * Boolean flag: if true convert vector images (SVG, EPS) to raster image using GD or ImageMagick library.
	 * @protected
	 * @since 5.0.000 (2010-04-26)
	 */
	protected $rasterize_vector_images = false;

	/**
	 * Boolean flag: if true enables font subsetting by default.
	 * @protected
	 * @since 5.3.002 (2010-06-07)
	 */
	protected $font_subsetting = true;

	/**
	 * Array of default graphic settings.
	 * @protected
	 * @since 5.5.008 (2010-07-02)
	 */
	protected $default_graphic_vars = array();

	/**
	 * Array of XObjects.
	 * @protected
	 * @since 5.8.014 (2010-08-23)
	 */
	protected $xobjects = array();

	/**
	 * Boolean value true when we are inside an XObject.
	 * @protected
	 * @since 5.8.017 (2010-08-24)
	 */
	protected $inxobj = false;

	/**
	 * Current XObject ID.
	 * @protected
	 * @since 5.8.017 (2010-08-24)
	 */
	protected $xobjid = '';

	/**
	 * Percentage of character stretching.
	 * @protected
	 * @since 5.9.000 (2010-09-29)
	 */
	protected $font_stretching = 100;

	/**
	 * Increases or decreases the space between characters in a text by the specified amount (tracking).
	 * @protected
	 * @since 5.9.000 (2010-09-29)
	 */
	protected $font_spacing = 0;

	/**
	 * Array of no-write regions.
	 * ('page' => page number or empy for current page, 'xt' => X top, 'yt' => Y top, 'xb' => X bottom, 'yb' => Y bottom, 'side' => page side 'L' = left or 'R' = right)
	 * @protected
	 * @since 5.9.003 (2010-10-14)
	 */
	protected $page_regions = array();

	/**
	 * Boolean value true when page region check is active.
	 * @protected
	 */
	protected $check_page_regions = true;

	/**
	 * Array of PDF layers data.
	 * @protected
	 * @since 5.9.102 (2011-07-13)
	 */
	protected $pdflayers = array();

	/**
	 * A dictionary of names and corresponding destinations (Dests key on document Catalog).
	 * @protected
	 * @since 5.9.097 (2011-06-23)
	 */
	protected $dests = array();

	/**
	 * Object ID for Named Destinations
	 * @protected
	 * @since 5.9.097 (2011-06-23)
	 */
	protected $n_dests;

	/**
	 * Embedded Files Names
	 * @protected
	 * @since 5.9.204 (2013-01-23)
	 */
	protected $efnames = array();

	/**
	 * Directory used for the last SVG image.
	 * @protected
	 * @since 5.0.000 (2010-05-05)
	 */
	protected $svgdir = '';

	/**
	 *  Deafult unit of measure for SVG.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgunit = 'px';

	/**
	 * Array of SVG gradients.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svggradients = array();

	/**
	 * ID of last SVG gradient.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svggradientid = 0;

	/**
	 * Boolean value true when in SVG defs group.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgdefsmode = false;

	/**
	 * Array of SVG defs.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgdefs = array();

	/**
	 * Boolean value true when in SVG clipPath tag.
	 * @protected
	 * @since 5.0.000 (2010-04-26)
	 */
	protected $svgclipmode = false;

	/**
	 * Array of SVG clipPath commands.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgclippaths = array();

	/**
	 * Array of SVG clipPath tranformation matrix.
	 * @protected
	 * @since 5.8.022 (2010-08-31)
	 */
	protected $svgcliptm = array();

	/**
	 * ID of last SVG clipPath.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgclipid = 0;

	/**
	 * SVG text.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgtext = '';

	/**
	 * SVG text properties.
	 * @protected
	 * @since 5.8.013 (2010-08-23)
	 */
	protected $svgtextmode = array();

	/**
	 * Array of SVG properties.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgstyles = array(array(
		'alignment-baseline' => 'auto',
		'baseline-shift' => 'baseline',
		'clip' => 'auto',
		'clip-path' => 'none',
		'clip-rule' => 'nonzero',
		'color' => 'black',
		'color-interpolation' => 'sRGB',
		'color-interpolation-filters' => 'linearRGB',
		'color-profile' => 'auto',
		'color-rendering' => 'auto',
		'cursor' => 'auto',
		'direction' => 'ltr',
		'display' => 'inline',
		'dominant-baseline' => 'auto',
		'enable-background' => 'accumulate',
		'fill' => 'black',
		'fill-opacity' => 1,
		'fill-rule' => 'nonzero',
		'filter' => 'none',
		'flood-color' => 'black',
		'flood-opacity' => 1,
		'font' => '',
		'font-family' => 'helvetica',
		'font-size' => 'medium',
		'font-size-adjust' => 'none',
		'font-stretch' => 'normal',
		'font-style' => 'normal',
		'font-variant' => 'normal',
		'font-weight' => 'normal',
		'glyph-orientation-horizontal' => '0deg',
		'glyph-orientation-vertical' => 'auto',
		'image-rendering' => 'auto',
		'kerning' => 'auto',
		'letter-spacing' => 'normal',
		'lighting-color' => 'white',
		'marker' => '',
		'marker-end' => 'none',
		'marker-mid' => 'none',
		'marker-start' => 'none',
		'mask' => 'none',
		'opacity' => 1,
		'overflow' => 'auto',
		'pointer-events' => 'visiblePainted',
		'shape-rendering' => 'auto',
		'stop-color' => 'black',
		'stop-opacity' => 1,
		'stroke' => 'none',
		'stroke-dasharray' => 'none',
		'stroke-dashoffset' => 0,
		'stroke-linecap' => 'butt',
		'stroke-linejoin' => 'miter',
		'stroke-miterlimit' => 4,
		'stroke-opacity' => 1,
		'stroke-width' => 1,
		'text-anchor' => 'start',
		'text-decoration' => 'none',
		'text-rendering' => 'auto',
		'unicode-bidi' => 'normal',
		'visibility' => 'visible',
		'word-spacing' => 'normal',
		'writing-mode' => 'lr-tb',
		'text-color' => 'black',
		'transfmatrix' => array(1, 0, 0, 1, 0, 0)
		));

	/**
	 * If true force sRGB color profile for all document.
	 * @protected
	 * @since 5.9.121 (2011-09-28)
	 */
	protected $force_srgb = false;

	/**
	 * If true set the document to PDF/A mode.
	 * @protected
	 * @since 5.9.121 (2011-09-27)
	 */
	protected $pdfa_mode = false;

	/**
	 * Document creation date-time
	 * @protected
	 * @since 5.9.152 (2012-03-22)
	 */
	protected $doc_creation_timestamp;

	/**
	 * Document modification date-time
	 * @protected
	 * @since 5.9.152 (2012-03-22)
	 */
	protected $doc_modification_timestamp;

	/**
	 * Custom XMP data.
	 * @protected
	 * @since 5.9.128 (2011-10-06)
	 */
	protected $custom_xmp = '';

	/**
	 * Overprint mode array.
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @protected
	 * @since 5.9.152 (2012-03-23)
	 */
	protected $overprint = array('OP' => false, 'op' => false, 'OPM' => 0);

	/**
	 * Alpha mode array.
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @protected
	 * @since 5.9.152 (2012-03-23)
	 */
	protected $alpha = array('CA' => 1, 'ca' => 1, 'BM' => '/Normal', 'AIS' => false);

	/**
	 * Define the page boundaries boxes to be set on document.
	 * @protected
	 * @since 5.9.152 (2012-03-23)
	 */
	protected $page_boxes = array('MediaBox', 'CropBox', 'BleedBox', 'TrimBox', 'ArtBox');

	/**
	 * If true print TCPDF meta link.
	 * @protected
	 * @since 5.9.152 (2012-03-23)
	 */
	protected $tcpdflink = true;

	/**
	 * Cache array for computed GD gamma values.
	 * @protected
	 * @since 5.9.1632 (2012-06-05)
	 */
	protected $gdgammacache = array();

	//------------------------------------------------------------
	// METHODS
	//------------------------------------------------------------

	/**
	 * This is the class constructor.
	 * It allows to set up the page format, the orientation and the measure unit used in all the methods (except for the font sizes).
	 * 
	 * IMPORTANT: Please note that this method sets the mb_internal_encoding to ASCII, so if you are using the mbstring module functions with TCPDF you need to correctly set/unset the mb_internal_encoding when needed.
	 * 
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or Portrait (default)</li><li>L or Landscape</li><li>'' (empty string) for automatic orientation</li></ul>
	 * @param $unit (string) User measure unit. Possible values are:<ul><li>pt: point</li><li>mm: millimeter (default)</li><li>cm: centimeter</li><li>in: inch</li></ul><br />A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param $unicode (boolean) TRUE means that the input text is unicode (default = true)
	 * @param $encoding (string) Charset encoding (used only when converting back html entities); default is UTF-8.
	 * @param $diskcache (boolean) If TRUE reduce the RAM memory usage by caching temporary data on filesystem (slower).
	 * @param $pdfa (boolean) If TRUE set the document to PDF/A mode.
	 * @public
	 * @see getPageSizeFromFormat(), setPageFormat()
	 */
	public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
		/* Set internal character encoding to ASCII */
		if (function_exists('mb_internal_encoding') AND mb_internal_encoding()) {
			$this->internal_encoding = mb_internal_encoding();
			mb_internal_encoding('ASCII');
		}
		$this->font_obj_ids = array();
		$this->page_obj_id = array();
		$this->form_obj_id = array();
		// set pdf/a mode
		$this->pdfa_mode = $pdfa;
		$this->force_srgb = false;
		// set disk caching
		$this->diskcache = $diskcache ? true : false;
		// set language direction
		$this->rtl = false;
		$this->tmprtl = false;
		// some checks
		$this->_dochecks();
		// initialization of properties
		$this->isunicode = $unicode;
		$this->page = 0;
		$this->transfmrk[0] = array();
		$this->pagedim = array();
		$this->n = 2;
		$this->buffer = '';
		$this->pages = array();
		$this->state = 0;
		$this->fonts = array();
		$this->FontFiles = array();
		$this->diffs = array();
		$this->images = array();
		$this->links = array();
		$this->gradients = array();
		$this->InFooter = false;
		$this->lasth = 0;
		$this->FontFamily = defined('PDF_FONT_NAME_MAIN')?PDF_FONT_NAME_MAIN:'helvetica';
		$this->FontStyle = '';
		$this->FontSizePt = 12;
		$this->underline = false;
		$this->overline = false;
		$this->linethrough = false;
		$this->DrawColor = '0 G';
		$this->FillColor = '0 g';
		$this->TextColor = '0 g';
		$this->ColorFlag = false;
		$this->pdflayers = array();
		// encryption values
		$this->encrypted = false;
		$this->last_enc_key = '';
		// standard Unicode fonts
		$this->CoreFonts = array(
			'courier'=>'Courier',
			'courierB'=>'Courier-Bold',
			'courierI'=>'Courier-Oblique',
			'courierBI'=>'Courier-BoldOblique',
			'helvetica'=>'Helvetica',
			'helveticaB'=>'Helvetica-Bold',
			'helveticaI'=>'Helvetica-Oblique',
			'helveticaBI'=>'Helvetica-BoldOblique',
			'times'=>'Times-Roman',
			'timesB'=>'Times-Bold',
			'timesI'=>'Times-Italic',
			'timesBI'=>'Times-BoldItalic',
			'symbol'=>'Symbol',
			'zapfdingbats'=>'ZapfDingbats'
		);
		// set scale factor
		$this->setPageUnit($unit);
		// set page format and orientation
		$this->setPageFormat($format, $orientation);
		// page margins (1 cm)
		$margin = 28.35 / $this->k;
		$this->SetMargins($margin, $margin);
		$this->clMargin = $this->lMargin;
		$this->crMargin = $this->rMargin;
		// internal cell padding
		$cpadding = $margin / 10;
		$this->setCellPaddings($cpadding, 0, $cpadding, 0);
		// cell margins
		$this->setCellMargins(0, 0, 0, 0);
		// line width (0.2 mm)
		$this->LineWidth = 0.57 / $this->k;
		$this->linestyleWidth = sprintf('%F w', ($this->LineWidth * $this->k));
		$this->linestyleCap = '0 J';
		$this->linestyleJoin = '0 j';
		$this->linestyleDash = '[] 0 d';
		// automatic page break
		$this->SetAutoPageBreak(true, (2 * $margin));
		// full width display mode
		$this->SetDisplayMode('fullwidth');
		// compression
		$this->SetCompression();
		// set default PDF version number
		$this->setPDFVersion();
		$this->tcpdflink = true;
		$this->encoding = $encoding;
		$this->HREF = array();
		$this->getFontsList();
		$this->fgcolor = array('R' => 0, 'G' => 0, 'B' => 0);
		$this->strokecolor = array('R' => 0, 'G' => 0, 'B' => 0);
		$this->bgcolor = array('R' => 255, 'G' => 255, 'B' => 255);
		$this->extgstates = array();
		$this->setTextShadow();
		// user's rights
		$this->sign = false;
		$this->ur['enabled'] = false;
		$this->ur['document'] = '/FullSave';
		$this->ur['annots'] = '/Create/Delete/Modify/Copy/Import/Export';
		$this->ur['form'] = '/Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate';
		$this->ur['signature'] = '/Modify';
		$this->ur['ef'] = '/Create/Delete/Modify/Import';
		$this->ur['formex'] = '';
		$this->signature_appearance = array('page' => 1, 'rect' => '0 0 0 0', 'name' => 'Signature');
		$this->empty_signature_appearance = array();
		// set default JPEG quality
		$this->jpeg_quality = 75;
		// initialize some settings
		TCPDF_FONTS::utf8Bidi(array(''), '', false, $this->isunicode, $this->CurrentFont);
		// set default font
		$this->SetFont($this->FontFamily, $this->FontStyle, $this->FontSizePt);
		// check if PCRE Unicode support is enabled
		if ($this->isunicode AND (@preg_match('/\pL/u', 'a') == 1)) {
			// PCRE unicode support is turned ON
			// \p{Z} or \p{Separator}: any kind of Unicode whitespace or invisible separator.
			// \p{Lo} or \p{Other_Letter}: a Unicode letter or ideograph that does not have lowercase and uppercase variants.
			// \p{Lo} is needed because Chinese characters are packed next to each other without spaces in between.
			//$this->setSpacesRE('/[^\S\P{Z}\P{Lo}\xa0]/u');
			$this->setSpacesRE('/[^\S\P{Z}\xa0]/u');
		} else {
			// PCRE unicode support is turned OFF
			$this->setSpacesRE('/[^\S\xa0]/');
		}
		$this->default_form_prop = array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 255), 'strokeColor'=>array(128, 128, 128));
		// set file ID for trailer
		$serformat = (is_array($format) ? serialize($format) : $format);
		$this->file_id = md5(TCPDF_STATIC::getRandomSeed('TCPDF'.$orientation.$unit.$serformat.$encoding));
		// set document creation and modification timestamp
		$this->doc_creation_timestamp = time();
		$this->doc_modification_timestamp = $this->doc_creation_timestamp;
		// get default graphic vars
		$this->default_graphic_vars = $this->getGraphicVars();
		$this->header_xobj_autoreset = false;
		$this->custom_xmp = '';
	}

	/**
	 * Default destructor.
	 * @public
	 * @since 1.53.0.TC016
	 */
	public function __destruct() {
		// restore internal encoding
		if (isset($this->internal_encoding) AND !empty($this->internal_encoding)) {
			mb_internal_encoding($this->internal_encoding);
		}
		// unset all class variables
		$this->_destroy(true);
	}

	/**
	 * Set the units of measure for the document.
	 * @param $unit (string) User measure unit. Possible values are:<ul><li>pt: point</li><li>mm: millimeter (default)</li><li>cm: centimeter</li><li>in: inch</li></ul><br />A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.
	 * @public
	 * @since 3.0.015 (2008-06-06)
	 */
	public function setPageUnit($unit) {
		$unit = strtolower($unit);
		//Set scale factor
		switch ($unit) {
			// points
			case 'px':
			case 'pt': {
				$this->k = 1;
				break;
			}
			// millimeters
			case 'mm': {
				$this->k = $this->dpi / 25.4;
				break;
			}
			// centimeters
			case 'cm': {
				$this->k = $this->dpi / 2.54;
				break;
			}
			// inches
			case 'in': {
				$this->k = $this->dpi;
				break;
			}
			// unsupported unit
			default : {
				$this->Error('Incorrect unit: '.$unit);
				break;
			}
		}
		$this->pdfunit = $unit;
		if (isset($this->CurOrientation)) {
			$this->setPageOrientation($this->CurOrientation);
		}
	}

	/**
	 * Change the format of the current page
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() documentation or an array of two numners (width, height) or an array containing the following measures and options:<ul>
	 * <li>['format'] = page format name (one of the above);</li>
	 * <li>['Rotate'] : The number of degrees by which the page shall be rotated clockwise when displayed or printed. The value shall be a multiple of 90.</li>
	 * <li>['PZ'] : The page's preferred zoom (magnification) factor.</li>
	 * <li>['MediaBox'] : the boundaries of the physical medium on which the page shall be displayed or printed:</li>
	 * <li>['MediaBox']['llx'] : lower-left x coordinate in points</li>
	 * <li>['MediaBox']['lly'] : lower-left y coordinate in points</li>
	 * <li>['MediaBox']['urx'] : upper-right x coordinate in points</li>
	 * <li>['MediaBox']['ury'] : upper-right y coordinate in points</li>
	 * <li>['CropBox'] : the visible region of default user space:</li>
	 * <li>['CropBox']['llx'] : lower-left x coordinate in points</li>
	 * <li>['CropBox']['lly'] : lower-left y coordinate in points</li>
	 * <li>['CropBox']['urx'] : upper-right x coordinate in points</li>
	 * <li>['CropBox']['ury'] : upper-right y coordinate in points</li>
	 * <li>['BleedBox'] : the region to which the contents of the page shall be clipped when output in a production environment:</li>
	 * <li>['BleedBox']['llx'] : lower-left x coordinate in points</li>
	 * <li>['BleedBox']['lly'] : lower-left y coordinate in points</li>
	 * <li>['BleedBox']['urx'] : upper-right x coordinate in points</li>
	 * <li>['BleedBox']['ury'] : upper-right y coordinate in points</li>
	 * <li>['TrimBox'] : the intended dimensions of the finished page after trimming:</li>
	 * <li>['TrimBox']['llx'] : lower-left x coordinate in points</li>
	 * <li>['TrimBox']['lly'] : lower-left y coordinate in points</li>
	 * <li>['TrimBox']['urx'] : upper-right x coordinate in points</li>
	 * <li>['TrimBox']['ury'] : upper-right y coordinate in points</li>
	 * <li>['ArtBox'] : the extent of the page's meaningful content:</li>
	 * <li>['ArtBox']['llx'] : lower-left x coordinate in points</li>
	 * <li>['ArtBox']['lly'] : lower-left y coordinate in points</li>
	 * <li>['ArtBox']['urx'] : upper-right x coordinate in points</li>
	 * <li>['ArtBox']['ury'] : upper-right y coordinate in points</li>
	 * <li>['BoxColorInfo'] :specify the colours and other visual characteristics that should be used in displaying guidelines on the screen for each of the possible page boundaries other than the MediaBox:</li>
	 * <li>['BoxColorInfo'][BOXTYPE]['C'] : an array of three numbers in the range 0-255, representing the components in the DeviceRGB colour space.</li>
	 * <li>['BoxColorInfo'][BOXTYPE]['W'] : the guideline width in default user units</li>
	 * <li>['BoxColorInfo'][BOXTYPE]['S'] : the guideline style: S = Solid; D = Dashed</li>
	 * <li>['BoxColorInfo'][BOXTYPE]['D'] : dash array defining a pattern of dashes and gaps to be used in drawing dashed guidelines</li>
	 * <li>['trans'] : the style and duration of the visual transition to use when moving from another page to the given page during a presentation</li>
	 * <li>['trans']['Dur'] : The page's display duration (also called its advance timing): the maximum length of time, in seconds, that the page shall be displayed during presentations before the viewer application shall automatically advance to the next page.</li>
	 * <li>['trans']['S'] : transition style : Split, Blinds, Box, Wipe, Dissolve, Glitter, R, Fly, Push, Cover, Uncover, Fade</li>
	 * <li>['trans']['D'] : The duration of the transition effect, in seconds.</li>
	 * <li>['trans']['Dm'] : (Split and Blinds transition styles only) The dimension in which the specified transition effect shall occur: H = Horizontal, V = Vertical. Default value: H.</li>
	 * <li>['trans']['M'] : (Split, Box and Fly transition styles only) The direction of motion for the specified transition effect: I = Inward from the edges of the page, O = Outward from the center of the pageDefault value: I.</li>
	 * <li>['trans']['Di'] : (Wipe, Glitter, Fly, Cover, Uncover and Push transition styles only) The direction in which the specified transition effect shall moves, expressed in degrees counterclockwise starting from a left-to-right direction. If the value is a number, it shall be one of: 0 = Left to right, 90 = Bottom to top (Wipe only), 180 = Right to left (Wipe only), 270 = Top to bottom, 315 = Top-left to bottom-right (Glitter only). If the value is a name, it shall be None, which is relevant only for the Fly transition when the value of SS is not 1.0. Default value: 0.</li>
	 * <li>['trans']['SS'] : (Fly transition style only) The starting or ending scale at which the changes shall be drawn. If M specifies an inward transition, the scale of the changes drawn shall progress from SS to 1.0 over the course of the transition. If M specifies an outward transition, the scale of the changes drawn shall progress from 1.0 to SS over the course of the transition. Default: 1.0.</li>
	 * <li>['trans']['B'] : (Fly transition style only) If true, the area that shall be flown in is rectangular and opaque. Default: false.</li>
	 * </ul>
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul>
	 * <li>P or Portrait (default)</li>
	 * <li>L or Landscape</li>
	 * <li>'' (empty string) for automatic orientation</li>
	 * </ul>
	 * @protected
	 * @since 3.0.015 (2008-06-06)
	 * @see getPageSizeFromFormat()
	 */
	protected function setPageFormat($format, $orientation='P') {
		if (!empty($format) AND isset($this->pagedim[$this->page])) {
			// remove inherited values
			unset($this->pagedim[$this->page]);
		}
		if (is_string($format)) {
			// get page measures from format name
			$pf = TCPDF_STATIC::getPageSizeFromFormat($format);
			$this->fwPt = $pf[0];
			$this->fhPt = $pf[1];
		} else {
			// the boundaries of the physical medium on which the page shall be displayed or printed
			if (isset($format['MediaBox'])) {
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'MediaBox', $format['MediaBox']['llx'], $format['MediaBox']['lly'], $format['MediaBox']['urx'], $format['MediaBox']['ury'], false, $this->k, $this->pagedim);
				$this->fwPt = (($format['MediaBox']['urx'] - $format['MediaBox']['llx']) * $this->k);
				$this->fhPt = (($format['MediaBox']['ury'] - $format['MediaBox']['lly']) * $this->k);
			} else {
				if (isset($format[0]) AND is_numeric($format[0]) AND isset($format[1]) AND is_numeric($format[1])) {
					$pf = array(($format[0] * $this->k), ($format[1] * $this->k));
				} else {
					if (!isset($format['format'])) {
						// default value
						$format['format'] = 'A4';
					}
					$pf = TCPDF_STATIC::getPageSizeFromFormat($format['format']);
				}
				$this->fwPt = $pf[0];
				$this->fhPt = $pf[1];
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'MediaBox', 0, 0, $this->fwPt, $this->fhPt, true, $this->k, $this->pagedim);
			}
			// the visible region of default user space
			if (isset($format['CropBox'])) {
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'CropBox', $format['CropBox']['llx'], $format['CropBox']['lly'], $format['CropBox']['urx'], $format['CropBox']['ury'], false, $this->k, $this->pagedim);
			}
			// the region to which the contents of the page shall be clipped when output in a production environment
			if (isset($format['BleedBox'])) {
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'BleedBox', $format['BleedBox']['llx'], $format['BleedBox']['lly'], $format['BleedBox']['urx'], $format['BleedBox']['ury'], false, $this->k, $this->pagedim);
			}
			// the intended dimensions of the finished page after trimming
			if (isset($format['TrimBox'])) {
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'TrimBox', $format['TrimBox']['llx'], $format['TrimBox']['lly'], $format['TrimBox']['urx'], $format['TrimBox']['ury'], false, $this->k, $this->pagedim);
			}
			// the page's meaningful content (including potential white space)
			if (isset($format['ArtBox'])) {
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'ArtBox', $format['ArtBox']['llx'], $format['ArtBox']['lly'], $format['ArtBox']['urx'], $format['ArtBox']['ury'], false, $this->k, $this->pagedim);
			}
			// specify the colours and other visual characteristics that should be used in displaying guidelines on the screen for the various page boundaries
			if (isset($format['BoxColorInfo'])) {
				$this->pagedim[$this->page]['BoxColorInfo'] = $format['BoxColorInfo'];
			}
			if (isset($format['Rotate']) AND (($format['Rotate'] % 90) == 0)) {
				// The number of degrees by which the page shall be rotated clockwise when displayed or printed. The value shall be a multiple of 90.
				$this->pagedim[$this->page]['Rotate'] = intval($format['Rotate']);
			}
			if (isset($format['PZ'])) {
				// The page's preferred zoom (magnification) factor
				$this->pagedim[$this->page]['PZ'] = floatval($format['PZ']);
			}
			if (isset($format['trans'])) {
				// The style and duration of the visual transition to use when moving from another page to the given page during a presentation
				if (isset($format['trans']['Dur'])) {
					// The page's display duration
					$this->pagedim[$this->page]['trans']['Dur'] = floatval($format['trans']['Dur']);
				}
				$stansition_styles = array('Split', 'Blinds', 'Box', 'Wipe', 'Dissolve', 'Glitter', 'R', 'Fly', 'Push', 'Cover', 'Uncover', 'Fade');
				if (isset($format['trans']['S']) AND in_array($format['trans']['S'], $stansition_styles)) {
					// The transition style that shall be used when moving to this page from another during a presentation
					$this->pagedim[$this->page]['trans']['S'] = $format['trans']['S'];
					$valid_effect = array('Split', 'Blinds');
					$valid_vals = array('H', 'V');
					if (isset($format['trans']['Dm']) AND in_array($format['trans']['S'], $valid_effect) AND in_array($format['trans']['Dm'], $valid_vals)) {
						$this->pagedim[$this->page]['trans']['Dm'] = $format['trans']['Dm'];
					}
					$valid_effect = array('Split', 'Box', 'Fly');
					$valid_vals = array('I', 'O');
					if (isset($format['trans']['M']) AND in_array($format['trans']['S'], $valid_effect) AND in_array($format['trans']['M'], $valid_vals)) {
						$this->pagedim[$this->page]['trans']['M'] = $format['trans']['M'];
					}
					$valid_effect = array('Wipe', 'Glitter', 'Fly', 'Cover', 'Uncover', 'Push');
					if (isset($format['trans']['Di']) AND in_array($format['trans']['S'], $valid_effect)) {
						if (((($format['trans']['Di'] == 90) OR ($format['trans']['Di'] == 180)) AND ($format['trans']['S'] == 'Wipe'))
							OR (($format['trans']['Di'] == 315) AND ($format['trans']['S'] == 'Glitter'))
							OR (($format['trans']['Di'] == 0) OR ($format['trans']['Di'] == 270))) {
							$this->pagedim[$this->page]['trans']['Di'] = intval($format['trans']['Di']);
						}
					}
					if (isset($format['trans']['SS']) AND ($format['trans']['S'] == 'Fly')) {
						$this->pagedim[$this->page]['trans']['SS'] = floatval($format['trans']['SS']);
					}
					if (isset($format['trans']['B']) AND ($format['trans']['B'] === true) AND ($format['trans']['S'] == 'Fly')) {
						$this->pagedim[$this->page]['trans']['B'] = 'true';
					}
				} else {
					$this->pagedim[$this->page]['trans']['S'] = 'R';
				}
				if (isset($format['trans']['D'])) {
					// The duration of the transition effect, in seconds
					$this->pagedim[$this->page]['trans']['D'] = floatval($format['trans']['D']);
				} else {
					$this->pagedim[$this->page]['trans']['D'] = 1;
				}
			}
		}
		$this->setPageOrientation($orientation);
	}

	/**
	 * Set page orientation.
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or Portrait (default)</li><li>L or Landscape</li><li>'' (empty string) for automatic orientation</li></ul>
	 * @param $autopagebreak (boolean) Boolean indicating if auto-page-break mode should be on or off.
	 * @param $bottommargin (float) bottom margin of the page.
	 * @public
	 * @since 3.0.015 (2008-06-06)
	 */
	public function setPageOrientation($orientation, $autopagebreak='', $bottommargin='') {
		if (!isset($this->pagedim[$this->page]['MediaBox'])) {
			// the boundaries of the physical medium on which the page shall be displayed or printed
			$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'MediaBox', 0, 0, $this->fwPt, $this->fhPt, true, $this->k, $this->pagedim);
		}
		if (!isset($this->pagedim[$this->page]['CropBox'])) {
			// the visible region of default user space
			$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'CropBox', $this->pagedim[$this->page]['MediaBox']['llx'], $this->pagedim[$this->page]['MediaBox']['lly'], $this->pagedim[$this->page]['MediaBox']['urx'], $this->pagedim[$this->page]['MediaBox']['ury'], true, $this->k, $this->pagedim);
		}
		if (!isset($this->pagedim[$this->page]['BleedBox'])) {
			// the region to which the contents of the page shall be clipped when output in a production environment
			$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'BleedBox', $this->pagedim[$this->page]['CropBox']['llx'], $this->pagedim[$this->page]['CropBox']['lly'], $this->pagedim[$this->page]['CropBox']['urx'], $this->pagedim[$this->page]['CropBox']['ury'], true, $this->k, $this->pagedim);
		}
		if (!isset($this->pagedim[$this->page]['TrimBox'])) {
			// the intended dimensions of the finished page after trimming
			$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'TrimBox', $this->pagedim[$this->page]['CropBox']['llx'], $this->pagedim[$this->page]['CropBox']['lly'], $this->pagedim[$this->page]['CropBox']['urx'], $this->pagedim[$this->page]['CropBox']['ury'], true, $this->k, $this->pagedim);
		}
		if (!isset($this->pagedim[$this->page]['ArtBox'])) {
			// the page's meaningful content (including potential white space)
			$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'ArtBox', $this->pagedim[$this->page]['CropBox']['llx'], $this->pagedim[$this->page]['CropBox']['lly'], $this->pagedim[$this->page]['CropBox']['urx'], $this->pagedim[$this->page]['CropBox']['ury'], true, $this->k, $this->pagedim);
		}
		if (!isset($this->pagedim[$this->page]['Rotate'])) {
			// The number of degrees by which the page shall be rotated clockwise when displayed or printed. The value shall be a multiple of 90.
			$this->pagedim[$this->page]['Rotate'] = 0;
		}
		if (!isset($this->pagedim[$this->page]['PZ'])) {
			// The page's preferred zoom (magnification) factor
			$this->pagedim[$this->page]['PZ'] = 1;
		}
		if ($this->fwPt > $this->fhPt) {
			// landscape
			$default_orientation = 'L';
		} else {
			// portrait
			$default_orientation = 'P';
		}
		$valid_orientations = array('P', 'L');
		if (empty($orientation)) {
			$orientation = $default_orientation;
		} else {
			$orientation = strtoupper($orientation{0});
		}
		if (in_array($orientation, $valid_orientations) AND ($orientation != $default_orientation)) {
			$this->CurOrientation = $orientation;
			$this->wPt = $this->fhPt;
			$this->hPt = $this->fwPt;
		} else {
			$this->CurOrientation = $default_orientation;
			$this->wPt = $this->fwPt;
			$this->hPt = $this->fhPt;
		}
		if ((abs($this->pagedim[$this->page]['MediaBox']['urx'] - $this->hPt) < $this->feps) AND (abs($this->pagedim[$this->page]['MediaBox']['ury'] - $this->wPt) < $this->feps)){
			// swap X and Y coordinates (change page orientation)
			$this->pagedim = TCPDF_STATIC::swapPageBoxCoordinates($this->page, $this->pagedim);
		}
		$this->w = ($this->wPt / $this->k);
		$this->h = ($this->hPt / $this->k);
		if (TCPDF_STATIC::empty_string($autopagebreak)) {
			if (isset($this->AutoPageBreak)) {
				$autopagebreak = $this->AutoPageBreak;
			} else {
				$autopagebreak = true;
			}
		}
		if (TCPDF_STATIC::empty_string($bottommargin)) {
			if (isset($this->bMargin)) {
				$bottommargin = $this->bMargin;
			} else {
				// default value = 2 cm
				$bottommargin = 2 * 28.35 / $this->k;
			}
		}
		$this->SetAutoPageBreak($autopagebreak, $bottommargin);
		// store page dimensions
		$this->pagedim[$this->page]['w'] = $this->wPt;
		$this->pagedim[$this->page]['h'] = $this->hPt;
		$this->pagedim[$this->page]['wk'] = $this->w;
		$this->pagedim[$this->page]['hk'] = $this->h;
		$this->pagedim[$this->page]['tm'] = $this->tMargin;
		$this->pagedim[$this->page]['bm'] = $bottommargin;
		$this->pagedim[$this->page]['lm'] = $this->lMargin;
		$this->pagedim[$this->page]['rm'] = $this->rMargin;
		$this->pagedim[$this->page]['pb'] = $autopagebreak;
		$this->pagedim[$this->page]['or'] = $this->CurOrientation;
		$this->pagedim[$this->page]['olm'] = $this->original_lMargin;
		$this->pagedim[$this->page]['orm'] = $this->original_rMargin;
	}

	/**
	 * Set regular expression to detect withespaces or word separators.
	 * The pattern delimiter must be the forward-slash character "/".
	 * Some example patterns are:
	 * <pre>
	 * Non-Unicode or missing PCRE unicode support: "/[^\S\xa0]/"
	 * Unicode and PCRE unicode support: "/[^\S\P{Z}\xa0]/u"
	 * Unicode and PCRE unicode support in Chinese mode: "/[^\S\P{Z}\P{Lo}\xa0]/u"
	 * if PCRE unicode support is turned ON ("\P" is the negate class of "\p"):
	 * "\p{Z}" or "\p{Separator}": any kind of Unicode whitespace or invisible separator.
	 * "\p{Lo}" or "\p{Other_Letter}": a Unicode letter or ideograph that does not have lowercase and uppercase variants.
	 * "\p{Lo}" is needed for Chinese characters because are packed next to each other without spaces in between.
	 * </pre>
	 * @param $re (string) regular expression (leave empty for default).
	 * @public
	 * @since 4.6.016 (2009-06-15)
	 */
	public function setSpacesRE($re='/[^\S\xa0]/') {
		$this->re_spaces = $re;
		$re_parts = explode('/', $re);
		// get pattern parts
		$this->re_space = array();
		if (isset($re_parts[1]) AND !empty($re_parts[1])) {
			$this->re_space['p'] = $re_parts[1];
		} else {
			$this->re_space['p'] = '[\s]';
		}
		// set pattern modifiers
		if (isset($re_parts[2]) AND !empty($re_parts[2])) {
			$this->re_space['m'] = $re_parts[2];
		} else {
			$this->re_space['m'] = '';
		}
	}

	/**
	 * Enable or disable Right-To-Left language mode
	 * @param $enable (Boolean) if true enable Right-To-Left language mode.
	 * @param $resetx (Boolean) if true reset the X position on direction change.
	 * @public
	 * @since 2.0.000 (2008-01-03)
	 */
	public function setRTL($enable, $resetx=true) {
		$enable = $enable ? true : false;
		$resetx = ($resetx AND ($enable != $this->rtl));
		$this->rtl = $enable;
		$this->tmprtl = false;
		if ($resetx) {
			$this->Ln(0);
		}
	}

	/**
	 * Return the RTL status
	 * @return boolean
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getRTL() {
		return $this->rtl;
	}

	/**
	 * Force temporary RTL language direction
	 * @param $mode (mixed) can be false, 'L' for LTR or 'R' for RTL
	 * @public
	 * @since 2.1.000 (2008-01-09)
	 */
	public function setTempRTL($mode) {
		$newmode = false;
		switch (strtoupper($mode)) {
			case 'LTR':
			case 'L': {
				if ($this->rtl) {
					$newmode = 'L';
				}
				break;
			}
			case 'RTL':
			case 'R': {
				if (!$this->rtl) {
					$newmode = 'R';
				}
				break;
			}
			case false:
			default: {
				$newmode = false;
				break;
			}
		}
		$this->tmprtl = $newmode;
	}

	/**
	 * Return the current temporary RTL status
	 * @return boolean
	 * @public
	 * @since 4.8.014 (2009-11-04)
	 */
	public function isRTLTextDir() {
		return ($this->rtl OR ($this->tmprtl == 'R'));
	}

	/**
	 * Set the last cell height.
	 * @param $h (float) cell height.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.53.0.TC034
	 */
	public function setLastH($h) {
		$this->lasth = $h;
	}

	/**
	 * Reset the last cell height.
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 */
	public function resetLastH() {
		$this->lasth = ($this->FontSize * $this->cell_height_ratio) + $this->cell_padding['T'] + $this->cell_padding['B'];
	}

	/**
	 * Get the last cell height.
	 * @return last cell height
	 * @public
	 * @since 4.0.017 (2008-08-05)
	 */
	public function getLastH() {
		return $this->lasth;
	}

	/**
	 * Set the adjusting factor to convert pixels to user units.
	 * @param $scale (float) adjusting factor to convert pixels to user units.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 */
	public function setImageScale($scale) {
		$this->imgscale = $scale;
	}

	/**
	 * Returns the adjusting factor to convert pixels to user units.
	 * @return float adjusting factor to convert pixels to user units.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 */
	public function getImageScale() {
		return $this->imgscale;
	}

	/**
	 * Returns an array of page dimensions:
	 * <ul><li>$this->pagedim[$this->page]['w'] = page width in points</li><li>$this->pagedim[$this->page]['h'] = height in points</li><li>$this->pagedim[$this->page]['wk'] = page width in user units</li><li>$this->pagedim[$this->page]['hk'] = page height in user units</li><li>$this->pagedim[$this->page]['tm'] = top margin</li><li>$this->pagedim[$this->page]['bm'] = bottom margin</li><li>$this->pagedim[$this->page]['lm'] = left margin</li><li>$this->pagedim[$this->page]['rm'] = right margin</li><li>$this->pagedim[$this->page]['pb'] = auto page break</li><li>$this->pagedim[$this->page]['or'] = page orientation</li><li>$this->pagedim[$this->page]['olm'] = original left margin</li><li>$this->pagedim[$this->page]['orm'] = original right margin</li><li>$this->pagedim[$this->page]['Rotate'] = The number of degrees by which the page shall be rotated clockwise when displayed or printed. The value shall be a multiple of 90.</li><li>$this->pagedim[$this->page]['PZ'] = The page's preferred zoom (magnification) factor.</li><li>$this->pagedim[$this->page]['trans'] : the style and duration of the visual transition to use when moving from another page to the given page during a presentation<ul><li>$this->pagedim[$this->page]['trans']['Dur'] = The page's display duration (also called its advance timing): the maximum length of time, in seconds, that the page shall be displayed during presentations before the viewer application shall automatically advance to the next page.</li><li>$this->pagedim[$this->page]['trans']['S'] = transition style : Split, Blinds, Box, Wipe, Dissolve, Glitter, R, Fly, Push, Cover, Uncover, Fade</li><li>$this->pagedim[$this->page]['trans']['D'] = The duration of the transition effect, in seconds.</li><li>$this->pagedim[$this->page]['trans']['Dm'] = (Split and Blinds transition styles only) The dimension in which the specified transition effect shall occur: H = Horizontal, V = Vertical. Default value: H.</li><li>$this->pagedim[$this->page]['trans']['M'] = (Split, Box and Fly transition styles only) The direction of motion for the specified transition effect: I = Inward from the edges of the page, O = Outward from the center of the pageDefault value: I.</li><li>$this->pagedim[$this->page]['trans']['Di'] = (Wipe, Glitter, Fly, Cover, Uncover and Push transition styles only) The direction in which the specified transition effect shall moves, expressed in degrees counterclockwise starting from a left-to-right direction. If the value is a number, it shall be one of: 0 = Left to right, 90 = Bottom to top (Wipe only), 180 = Right to left (Wipe only), 270 = Top to bottom, 315 = Top-left to bottom-right (Glitter only). If the value is a name, it shall be None, which is relevant only for the Fly transition when the value of SS is not 1.0. Default value: 0.</li><li>$this->pagedim[$this->page]['trans']['SS'] = (Fly transition style only) The starting or ending scale at which the changes shall be drawn. If M specifies an inward transition, the scale of the changes drawn shall progress from SS to 1.0 over the course of the transition. If M specifies an outward transition, the scale of the changes drawn shall progress from 1.0 to SS over the course of the transition. Default: 1.0. </li><li>$this->pagedim[$this->page]['trans']['B'] = (Fly transition style only) If true, the area that shall be flown in is rectangular and opaque. Default: false.</li></ul></li><li>$this->pagedim[$this->page]['MediaBox'] : the boundaries of the physical medium on which the page shall be displayed or printed<ul><li>$this->pagedim[$this->page]['MediaBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['MediaBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['MediaBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['MediaBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['CropBox'] : the visible region of default user space<ul><li>$this->pagedim[$this->page]['CropBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['CropBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['CropBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['CropBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['BleedBox'] : the region to which the contents of the page shall be clipped when output in a production environment<ul><li>$this->pagedim[$this->page]['BleedBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['BleedBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['BleedBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['BleedBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['TrimBox'] : the intended dimensions of the finished page after trimming<ul><li>$this->pagedim[$this->page]['TrimBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['TrimBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['TrimBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['TrimBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['ArtBox'] : the extent of the page's meaningful content<ul><li>$this->pagedim[$this->page]['ArtBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['ArtBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['ArtBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['ArtBox']['ury'] = upper-right y coordinate in points</li></ul></li></ul>
	 * @param $pagenum (int) page number (empty = current page)
	 * @return array of page dimensions.
	 * @author Nicola Asuni
	 * @public
	 * @since 4.5.027 (2009-03-16)
	 */
	public function getPageDimensions($pagenum='') {
		if (empty($pagenum)) {
			$pagenum = $this->page;
		}
		return $this->pagedim[$pagenum];
	}

	/**
	 * Returns the page width in units.
	 * @param $pagenum (int) page number (empty = current page)
	 * @return int page width.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 * @see getPageDimensions()
	 */
	public function getPageWidth($pagenum='') {
		if (empty($pagenum)) {
			return $this->w;
		}
		return $this->pagedim[$pagenum]['w'];
	}

	/**
	 * Returns the page height in units.
	 * @param $pagenum (int) page number (empty = current page)
	 * @return int page height.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 * @see getPageDimensions()
	 */
	public function getPageHeight($pagenum='') {
		if (empty($pagenum)) {
			return $this->h;
		}
		return $this->pagedim[$pagenum]['h'];
	}

	/**
	 * Returns the page break margin.
	 * @param $pagenum (int) page number (empty = current page)
	 * @return int page break margin.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 * @see getPageDimensions()
	 */
	public function getBreakMargin($pagenum='') {
		if (empty($pagenum)) {
			return $this->bMargin;
		}
		return $this->pagedim[$pagenum]['bm'];
	}

	/**
	 * Returns the scale factor (number of points in user unit).
	 * @return int scale factor.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 */
	public function getScaleFactor() {
		return $this->k;
	}

	/**
	 * Defines the left, top and right margins.
	 * @param $left (float) Left margin.
	 * @param $top (float) Top margin.
	 * @param $right (float) Right margin. Default value is the left one.
	 * @param $keepmargins (boolean) if true overwrites the default page margins
	 * @public
	 * @since 1.0
	 * @see SetLeftMargin(), SetTopMargin(), SetRightMargin(), SetAutoPageBreak()
	 */
	public function SetMargins($left, $top, $right=-1, $keepmargins=false) {
		//Set left, top and right margins
		$this->lMargin = $left;
		$this->tMargin = $top;
		if ($right == -1) {
			$right = $left;
		}
		$this->rMargin = $right;
		if ($keepmargins) {
			// overwrite original values
			$this->original_lMargin = $this->lMargin;
			$this->original_rMargin = $this->rMargin;
		}
	}

	/**
	 * Defines the left margin. The method can be called before creating the first page. If the current abscissa gets out of page, it is brought back to the margin.
	 * @param $margin (float) The margin.
	 * @public
	 * @since 1.4
	 * @see SetTopMargin(), SetRightMargin(), SetAutoPageBreak(), SetMargins()
	 */
	public function SetLeftMargin($margin) {
		//Set left margin
		$this->lMargin = $margin;
		if (($this->page > 0) AND ($this->x < $margin)) {
			$this->x = $margin;
		}
	}

	/**
	 * Defines the top margin. The method can be called before creating the first page.
	 * @param $margin (float) The margin.
	 * @public
	 * @since 1.5
	 * @see SetLeftMargin(), SetRightMargin(), SetAutoPageBreak(), SetMargins()
	 */
	public function SetTopMargin($margin) {
		//Set top margin
		$this->tMargin = $margin;
		if (($this->page > 0) AND ($this->y < $margin)) {
			$this->y = $margin;
		}
	}

	/**
	 * Defines the right margin. The method can be called before creating the first page.
	 * @param $margin (float) The margin.
	 * @public
	 * @since 1.5
	 * @see SetLeftMargin(), SetTopMargin(), SetAutoPageBreak(), SetMargins()
	 */
	public function SetRightMargin($margin) {
		$this->rMargin = $margin;
		if (($this->page > 0) AND ($this->x > ($this->w - $margin))) {
			$this->x = $this->w - $margin;
		}
	}

	/**
	 * Set the same internal Cell padding for top, right, bottom, left-
	 * @param $pad (float) internal padding.
	 * @public
	 * @since 2.1.000 (2008-01-09)
	 * @see getCellPaddings(), setCellPaddings()
	 */
	public function SetCellPadding($pad) {
		if ($pad >= 0) {
			$this->cell_padding['L'] = $pad;
			$this->cell_padding['T'] = $pad;
			$this->cell_padding['R'] = $pad;
			$this->cell_padding['B'] = $pad;
		}
	}

	/**
	 * Set the internal Cell paddings.
	 * @param $left (float) left padding
	 * @param $top (float) top padding
	 * @param $right (float) right padding
	 * @param $bottom (float) bottom padding
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see getCellPaddings(), SetCellPadding()
	 */
	public function setCellPaddings($left='', $top='', $right='', $bottom='') {
		if (($left !== '') AND ($left >= 0)) {
			$this->cell_padding['L'] = $left;
		}
		if (($top !== '') AND ($top >= 0)) {
			$this->cell_padding['T'] = $top;
		}
		if (($right !== '') AND ($right >= 0)) {
			$this->cell_padding['R'] = $right;
		}
		if (($bottom !== '') AND ($bottom >= 0)) {
			$this->cell_padding['B'] = $bottom;
		}
	}

	/**
	 * Get the internal Cell padding array.
	 * @return array of padding values
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see setCellPaddings(), SetCellPadding()
	 */
	public function getCellPaddings() {
		return $this->cell_padding;
	}

	/**
	 * Set the internal Cell margins.
	 * @param $left (float) left margin
	 * @param $top (float) top margin
	 * @param $right (float) right margin
	 * @param $bottom (float) bottom margin
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see getCellMargins()
	 */
	public function setCellMargins($left='', $top='', $right='', $bottom='') {
		if (($left !== '') AND ($left >= 0)) {
			$this->cell_margin['L'] = $left;
		}
		if (($top !== '') AND ($top >= 0)) {
			$this->cell_margin['T'] = $top;
		}
		if (($right !== '') AND ($right >= 0)) {
			$this->cell_margin['R'] = $right;
		}
		if (($bottom !== '') AND ($bottom >= 0)) {
			$this->cell_margin['B'] = $bottom;
		}
	}

	/**
	 * Get the internal Cell margin array.
	 * @return array of margin values
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see setCellMargins()
	 */
	public function getCellMargins() {
		return $this->cell_margin;
	}

	/**
	 * Adjust the internal Cell padding array to take account of the line width.
	 * @param $brd (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @return array of adjustments
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 */
	protected function adjustCellPadding($brd=0) {
		if (empty($brd)) {
			return;
		}
		if (is_string($brd)) {
			// convert string to array
			$slen = strlen($brd);
			$newbrd = array();
			for ($i = 0; $i < $slen; ++$i) {
				$newbrd[$brd[$i]] = true;
			}
			$brd = $newbrd;
		} elseif (($brd === 1) OR ($brd === true) OR (is_numeric($brd) AND (intval($brd) > 0))) {
			$brd = array('LRTB' => true);
		}
		if (!is_array($brd)) {
			return;
		}
		// store current cell padding
		$cp = $this->cell_padding;
		// select border mode
		if (isset($brd['mode'])) {
			$mode = $brd['mode'];
			unset($brd['mode']);
		} else {
			$mode = 'normal';
		}
		// process borders
		foreach ($brd as $border => $style) {
			$line_width = $this->LineWidth;
			if (is_array($style) AND isset($style['width'])) {
				// get border width
				$line_width = $style['width'];
			}
			$adj = 0; // line width inside the cell
			switch ($mode) {
				case 'ext': {
					$adj = 0;
					break;
				}
				case 'int': {
					$adj = $line_width;
					break;
				}
				case 'normal':
				default: {
					$adj = ($line_width / 2);
					break;
				}
			}
			// correct internal cell padding if required to avoid overlap between text and lines
			if ((strpos($border,'T') !== false) AND ($this->cell_padding['T'] < $adj)) {
				$this->cell_padding['T'] = $adj;
			}
			if ((strpos($border,'R') !== false) AND ($this->cell_padding['R'] < $adj)) {
				$this->cell_padding['R'] = $adj;
			}
			if ((strpos($border,'B') !== false) AND ($this->cell_padding['B'] < $adj)) {
				$this->cell_padding['B'] = $adj;
			}
			if ((strpos($border,'L') !== false) AND ($this->cell_padding['L'] < $adj)) {
				$this->cell_padding['L'] = $adj;
			}
		}
		return array('T' => ($this->cell_padding['T'] - $cp['T']), 'R' => ($this->cell_padding['R'] - $cp['R']), 'B' => ($this->cell_padding['B'] - $cp['B']), 'L' => ($this->cell_padding['L'] - $cp['L']));
	}

	/**
	 * Enables or disables the automatic page breaking mode. When enabling, the second parameter is the distance from the bottom of the page that defines the triggering limit. By default, the mode is on and the margin is 2 cm.
	 * @param $auto (boolean) Boolean indicating if mode should be on or off.
	 * @param $margin (float) Distance from the bottom of the page.
	 * @public
	 * @since 1.0
	 * @see Cell(), MultiCell(), AcceptPageBreak()
	 */
	public function SetAutoPageBreak($auto, $margin=0) {
		$this->AutoPageBreak = $auto ? true : false;
		$this->bMargin = $margin;
		$this->PageBreakTrigger = $this->h - $margin;
	}

	/**
	 * Return the auto-page-break mode (true or false).
	 * @return boolean auto-page-break mode
	 * @public
	 * @since 5.9.088
	 */
	public function getAutoPageBreak() {
		return $this->AutoPageBreak;
	}

	/**
	 * Defines the way the document is to be displayed by the viewer.
	 * @param $zoom (mixed) The zoom to use. It can be one of the following string values or a number indicating the zooming factor to use. <ul><li>fullpage: displays the entire page on screen </li><li>fullwidth: uses maximum width of window</li><li>real: uses real size (equivalent to 100% zoom)</li><li>default: uses viewer default mode</li></ul>
	 * @param $layout (string) The page layout. Possible values are:<ul><li>SinglePage Display one page at a time</li><li>OneColumn Display the pages in one column</li><li>TwoColumnLeft Display the pages in two columns, with odd-numbered pages on the left</li><li>TwoColumnRight Display the pages in two columns, with odd-numbered pages on the right</li><li>TwoPageLeft (PDF 1.5) Display the pages two at a time, with odd-numbered pages on the left</li><li>TwoPageRight (PDF 1.5) Display the pages two at a time, with odd-numbered pages on the right</li></ul>
	 * @param $mode (string) A name object specifying how the document should be displayed when opened:<ul><li>UseNone Neither document outline nor thumbnail images visible</li><li>UseOutlines Document outline visible</li><li>UseThumbs Thumbnail images visible</li><li>FullScreen Full-screen mode, with no menu bar, window controls, or any other window visible</li><li>UseOC (PDF 1.5) Optional content group panel visible</li><li>UseAttachments (PDF 1.6) Attachments panel visible</li></ul>
	 * @public
	 * @since 1.2
	 */
	public function SetDisplayMode($zoom, $layout='SinglePage', $mode='UseNone') {
		if (($zoom == 'fullpage') OR ($zoom == 'fullwidth') OR ($zoom == 'real') OR ($zoom == 'default') OR (!is_string($zoom))) {
			$this->ZoomMode = $zoom;
		} else {
			$this->Error('Incorrect zoom display mode: '.$zoom);
		}
		$this->LayoutMode = TCPDF_STATIC::getPageLayoutMode($layout);
		$this->PageMode = TCPDF_STATIC::getPageMode($mode);
	}

	/**
	 * Activates or deactivates page compression. When activated, the internal representation of each page is compressed, which leads to a compression ratio of about 2 for the resulting document. Compression is on by default.
	 * Note: the Zlib extension is required for this feature. If not present, compression will be turned off.
	 * @param $compress (boolean) Boolean indicating if compression must be enabled.
	 * @public
	 * @since 1.4
	 */
	public function SetCompression($compress=true) {
		if (function_exists('gzcompress')) {
			$this->compress = $compress ? true : false;
		} else {
			$this->compress = false;
		}
	}

	/**
	 * Set flag to force sRGB_IEC61966-2.1 black scaled ICC color profile for the whole document.
	 * @param $mode (boolean) If true force sRGB output intent.
	 * @public
	 * @since 5.9.121 (2011-09-28)
	 */
	public function setSRGBmode($mode=false) {
		$this->force_srgb = $mode ? true : false;
	}

	/**
	 * Turn on/off Unicode mode for document information dictionary (meta tags).
	 * This has effect only when unicode mode is set to false.
	 * @param $unicode (boolean) if true set the meta information in Unicode
	 * @since 5.9.027 (2010-12-01)
	 * @public
	 */
	public function SetDocInfoUnicode($unicode=true) {
		$this->docinfounicode = $unicode ? true : false;
	}

	/**
	 * Defines the title of the document.
	 * @param $title (string) The title.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetKeywords(), SetSubject()
	 */
	public function SetTitle($title) {
		$this->title = $title;
	}

	/**
	 * Defines the subject of the document.
	 * @param $subject (string) The subject.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetKeywords(), SetTitle()
	 */
	public function SetSubject($subject) {
		$this->subject = $subject;
	}

	/**
	 * Defines the author of the document.
	 * @param $author (string) The name of the author.
	 * @public
	 * @since 1.2
	 * @see SetCreator(), SetKeywords(), SetSubject(), SetTitle()
	 */
	public function SetAuthor($author) {
		$this->author = $author;
	}

	/**
	 * Associates keywords with the document, generally in the form 'keyword1 keyword2 ...'.
	 * @param $keywords (string) The list of keywords.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetSubject(), SetTitle()
	 */
	public function SetKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
	 * Defines the creator of the document. This is typically the name of the application that generates the PDF.
	 * @param $creator (string) The name of the creator.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetKeywords(), SetSubject(), SetTitle()
	 */
	public function SetCreator($creator) {
		$this->creator = $creator;
	}

	/**
	 * Throw an exception or print an error message and die if the K_TCPDF_PARSER_THROW_EXCEPTION_ERROR constant is set to true.
	 * @param $msg (string) The error message
	 * @public
	 * @since 1.0
	 */
	public function Error($msg) {
		// unset all class variables
		$this->_destroy(true);
		if (defined('K_TCPDF_THROW_EXCEPTION_ERROR') AND !K_TCPDF_THROW_EXCEPTION_ERROR) {
			die('<strong>TCPDF ERROR: </strong>'.$msg);
		} else {
			throw new Exception('TCPDF ERROR: '.$msg);
		}
	}

	/**
	 * This method begins the generation of the PDF document.
	 * It is not necessary to call it explicitly because AddPage() does it automatically.
	 * Note: no page is created by this method
	 * @public
	 * @since 1.0
	 * @see AddPage(), Close()
	 */
	public function Open() {
		$this->state = 1;
	}

	/**
	 * Terminates the PDF document.
	 * It is not necessary to call this method explicitly because Output() does it automatically.
	 * If the document contains no page, AddPage() is called to prevent from getting an invalid document.
	 * @public
	 * @since 1.0
	 * @see Open(), Output()
	 */
	public function Close() {
		if ($this->state == 3) {
			return;
		}
		if ($this->page == 0) {
			$this->AddPage();
		}
		$this->endLayer();
		if ($this->tcpdflink) {
			// save current graphic settings
			$gvars = $this->getGraphicVars();
			$this->setEqualColumns();
			$this->lastpage(true);
			$this->SetAutoPageBreak(false);
			$this->x = 0;
			$this->y = $this->h - (1 / $this->k);
			$this->lMargin = 0;
			$this->_out('q');
			$font = defined('PDF_FONT_NAME_MAIN')?PDF_FONT_NAME_MAIN:'helvetica';
			$this->SetFont($font, '', 1);
			$this->setTextRenderingMode(0, false, false);
			$msg = "\x50\x6f\x77\x65\x72\x65\x64\x20\x62\x79\x20\x54\x43\x50\x44\x46\x20\x28\x77\x77\x77\x2e\x74\x63\x70\x64\x66\x2e\x6f\x72\x67\x29";
			$lnk = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x74\x63\x70\x64\x66\x2e\x6f\x72\x67";
			$this->Cell(0, 0, $msg, 0, 0, 'L', 0, $lnk, 0, false, 'D', 'B');
			$this->_out('Q');
			// restore graphic settings
			$this->setGraphicVars($gvars);
		}
		// close page
		$this->endPage();
		// close document
		$this->_enddoc();
		// unset all class variables (except critical ones)
		$this->_destroy(false);
	}

	/**
	 * Move pointer at the specified document page and update page dimensions.
	 * @param $pnum (int) page number (1 ... numpages)
	 * @param $resetmargins (boolean) if true reset left, right, top margins and Y position.
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see getPage(), lastpage(), getNumPages()
	 */
	public function setPage($pnum, $resetmargins=false) {
		if (($pnum == $this->page) AND ($this->state == 2)) {
			return;
		}
		if (($pnum > 0) AND ($pnum <= $this->numpages)) {
			$this->state = 2;
			// save current graphic settings
			//$gvars = $this->getGraphicVars();
			$oldpage = $this->page;
			$this->page = $pnum;
			$this->wPt = $this->pagedim[$this->page]['w'];
			$this->hPt = $this->pagedim[$this->page]['h'];
			$this->w = $this->pagedim[$this->page]['wk'];
			$this->h = $this->pagedim[$this->page]['hk'];
			$this->tMargin = $this->pagedim[$this->page]['tm'];
			$this->bMargin = $this->pagedim[$this->page]['bm'];
			$this->original_lMargin = $this->pagedim[$this->page]['olm'];
			$this->original_rMargin = $this->pagedim[$this->page]['orm'];
			$this->AutoPageBreak = $this->pagedim[$this->page]['pb'];
			$this->CurOrientation = $this->pagedim[$this->page]['or'];
			$this->SetAutoPageBreak($this->AutoPageBreak, $this->bMargin);
			// restore graphic settings
			//$this->setGraphicVars($gvars);
			if ($resetmargins) {
				$this->lMargin = $this->pagedim[$this->page]['olm'];
				$this->rMargin = $this->pagedim[$this->page]['orm'];
				$this->SetY($this->tMargin);
			} else {
				// account for booklet mode
				if ($this->pagedim[$this->page]['olm'] != $this->pagedim[$oldpage]['olm']) {
					$deltam = $this->pagedim[$this->page]['olm'] - $this->pagedim[$this->page]['orm'];
					$this->lMargin += $deltam;
					$this->rMargin -= $deltam;
				}
			}
		} else {
			$this->Error('Wrong page number on setPage() function: '.$pnum);
		}
	}

	/**
	 * Reset pointer to the last document page.
	 * @param $resetmargins (boolean) if true reset left, right, top margins and Y position.
	 * @public
	 * @since 2.0.000 (2008-01-04)
	 * @see setPage(), getPage(), getNumPages()
	 */
	public function lastPage($resetmargins=false) {
		$this->setPage($this->getNumPages(), $resetmargins);
	}

	/**
	 * Get current document page number.
	 * @return int page number
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see setPage(), lastpage(), getNumPages()
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * Get the total number of insered pages.
	 * @return int number of pages
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see setPage(), getPage(), lastpage()
	 */
	public function getNumPages() {
		return $this->numpages;
	}

	/**
	 * Adds a new TOC (Table Of Content) page to the document.
	 * @param $orientation (string) page orientation.
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param $keepmargins (boolean) if true overwrites the default page margins with the current margins
	 * @public
	 * @since 5.0.001 (2010-05-06)
	 * @see AddPage(), startPage(), endPage(), endTOCPage()
	 */
	public function addTOCPage($orientation='', $format='', $keepmargins=false) {
		$this->AddPage($orientation, $format, $keepmargins, true);
	}

	/**
	 * Terminate the current TOC (Table Of Content) page
	 * @public
	 * @since 5.0.001 (2010-05-06)
	 * @see AddPage(), startPage(), endPage(), addTOCPage()
	 */
	public function endTOCPage() {
		$this->endPage(true);
	}

	/**
	 * Adds a new page to the document. If a page is already present, the Footer() method is called first to output the footer (if enabled). Then the page is added, the current position set to the top-left corner according to the left and top margins (or top-right if in RTL mode), and Header() is called to display the header (if enabled).
	 * The origin of the coordinate system is at the top-left corner (or top-right for RTL) and increasing ordinates go downwards.
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or PORTRAIT (default)</li><li>L or LANDSCAPE</li></ul>
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param $keepmargins (boolean) if true overwrites the default page margins with the current margins
	 * @param $tocpage (boolean) if true set the tocpage state to true (the added page will be used to display Table Of Content).
	 * @public
	 * @since 1.0
	 * @see startPage(), endPage(), addTOCPage(), endTOCPage(), getPageSizeFromFormat(), setPageFormat()
	 */
	public function AddPage($orientation='', $format='', $keepmargins=false, $tocpage=false) {
		if ($this->inxobj) {
			// we are inside an XObject template
			return;
		}
		if (!isset($this->original_lMargin) OR $keepmargins) {
			$this->original_lMargin = $this->lMargin;
		}
		if (!isset($this->original_rMargin) OR $keepmargins) {
			$this->original_rMargin = $this->rMargin;
		}
		// terminate previous page
		$this->endPage();
		// start new page
		$this->startPage($orientation, $format, $tocpage);
	}

	/**
	 * Terminate the current page
	 * @param $tocpage (boolean) if true set the tocpage state to false (end the page used to display Table Of Content).
	 * @public
	 * @since 4.2.010 (2008-11-14)
	 * @see AddPage(), startPage(), addTOCPage(), endTOCPage()
	 */
	public function endPage($tocpage=false) {
		// check if page is already closed
		if (($this->page == 0) OR ($this->numpages > $this->page) OR (!$this->pageopen[$this->page])) {
			return;
		}
		// print page footer
		$this->setFooter();
		// close page
		$this->_endpage();
		// mark page as closed
		$this->pageopen[$this->page] = false;
		if ($tocpage) {
			$this->tocpage = false;
		}
	}

	/**
	 * Starts a new page to the document. The page must be closed using the endPage() function.
	 * The origin of the coordinate system is at the top-left corner and increasing ordinates go downwards.
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or PORTRAIT (default)</li><li>L or LANDSCAPE</li></ul>
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param $tocpage (boolean) if true the page is designated to contain the Table-Of-Content.
	 * @since 4.2.010 (2008-11-14)
	 * @see AddPage(), endPage(), addTOCPage(), endTOCPage(), getPageSizeFromFormat(), setPageFormat()
	 * @public
	 */
	public function startPage($orientation='', $format='', $tocpage=false) {
		if ($tocpage) {
			$this->tocpage = true;
		}
		// move page numbers of documents to be attached
		if ($this->tocpage) {
			// move reference to unexistent pages (used for page attachments)
			// adjust outlines
			$tmpoutlines = $this->outlines;
			foreach ($tmpoutlines as $key => $outline) {
				if ($outline['p'] > $this->numpages) {
					$this->outlines[$key]['p'] = ($outline['p'] + 1);
				}
			}
			// adjust dests
			$tmpdests = $this->dests;
			foreach ($tmpdests as $key => $dest) {
				if ($dest['p'] > $this->numpages) {
					$this->dests[$key]['p'] = ($dest['p'] + 1);
				}
			}
			// adjust links
			$tmplinks = $this->links;
			foreach ($tmplinks as $key => $link) {
				if ($link[0] > $this->numpages) {
					$this->links[$key][0] = ($link[0] + 1);
				}
			}
		}
		if ($this->numpages > $this->page) {
			// this page has been already added
			$this->setPage($this->page + 1);
			$this->SetY($this->tMargin);
			return;
		}
		// start a new page
		if ($this->state == 0) {
			$this->Open();
		}
		++$this->numpages;
		$this->swapMargins($this->booklet);
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// start new page
		$this->_beginpage($orientation, $format);
		// mark page as open
		$this->pageopen[$this->page] = true;
		// restore graphic settings
		$this->setGraphicVars($gvars);
		// mark this point
		$this->setPageMark();
		// print page header
		$this->setHeader();
		// restore graphic settings
		$this->setGraphicVars($gvars);
		// mark this point
		$this->setPageMark();
		// print table header (if any)
		$this->setTableHeader();
		// set mark for empty page check
		$this->emptypagemrk[$this->page]= $this->pagelen[$this->page];
	}

	/**
	 * Set start-writing mark on current page stream used to put borders and fills.
	 * Borders and fills are always created after content and inserted on the position marked by this method.
	 * This function must be called after calling Image() function for a background image.
	 * Background images must be always inserted before calling Multicell() or WriteHTMLCell() or WriteHTML() functions.
	 * @public
	 * @since 4.0.016 (2008-07-30)
	 */
	public function setPageMark() {
		$this->intmrk[$this->page] = $this->pagelen[$this->page];
		$this->bordermrk[$this->page] = $this->intmrk[$this->page];
		$this->setContentMark();
	}

	/**
	 * Set start-writing mark on selected page.
	 * Borders and fills are always created after content and inserted on the position marked by this method.
	 * @param $page (int) page number (default is the current page)
	 * @protected
	 * @since 4.6.021 (2009-07-20)
	 */
	protected function setContentMark($page=0) {
		if ($page <= 0) {
			$page = $this->page;
		}
		if (isset($this->footerlen[$page])) {
			$this->cntmrk[$page] = $this->pagelen[$page] - $this->footerlen[$page];
		} else {
			$this->cntmrk[$page] = $this->pagelen[$page];
		}
	}

	/**
	 * Set header data.
	 * @param $ln (string) header image logo
	 * @param $lw (string) header image logo width in mm
	 * @param $ht (string) string to print as title on document header
	 * @param $hs (string) string to print on document header
	 * @param $tc (array) RGB array color for text.
	 * @param $lc (array) RGB array color for line.
	 * @public
	 */
	public function setHeaderData($ln='', $lw=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0)) {
		$this->header_logo = $ln;
		$this->header_logo_width = $lw;
		$this->header_title = $ht;
		$this->header_string = $hs;
		$this->header_text_color = $tc;
		$this->header_line_color = $lc;
	}

	/**
	 * Set footer data.
	 * @param $tc (array) RGB array color for text.
	 * @param $lc (array) RGB array color for line.
	 * @public
	 */
	public function setFooterData($tc=array(0,0,0), $lc=array(0,0,0)) {
		$this->footer_text_color = $tc;
		$this->footer_line_color = $lc;
	}

	/**
	 * Returns header data:
	 * <ul><li>$ret['logo'] = logo image</li><li>$ret['logo_width'] = width of the image logo in user units</li><li>$ret['title'] = header title</li><li>$ret['string'] = header description string</li></ul>
	 * @return array()
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getHeaderData() {
		$ret = array();
		$ret['logo'] = $this->header_logo;
		$ret['logo_width'] = $this->header_logo_width;
		$ret['title'] = $this->header_title;
		$ret['string'] = $this->header_string;
		$ret['text_color'] = $this->header_text_color;
		$ret['line_color'] = $this->header_line_color;
		return $ret;
	}

	/**
	 * Set header margin.
	 * (minimum distance between header and top page margin)
	 * @param $hm (int) distance in user units
	 * @public
	 */
	public function setHeaderMargin($hm=10) {
		$this->header_margin = $hm;
	}

	/**
	 * Returns header margin in user units.
	 * @return float
	 * @since 4.0.012 (2008-07-24)
	 * @public
	 */
	public function getHeaderMargin() {
		return $this->header_margin;
	}

	/**
	 * Set footer margin.
	 * (minimum distance between footer and bottom page margin)
	 * @param $fm (int) distance in user units
	 * @public
	 */
	public function setFooterMargin($fm=10) {
		$this->footer_margin = $fm;
	}

	/**
	 * Returns footer margin in user units.
	 * @return float
	 * @since 4.0.012 (2008-07-24)
	 * @public
	 */
	public function getFooterMargin() {
		return $this->footer_margin;
	}
	/**
	 * Set a flag to print page header.
	 * @param $val (boolean) set to true to print the page header (default), false otherwise.
	 * @public
	 */
	public function setPrintHeader($val=true) {
		$this->print_header = $val ? true : false;
	}

	/**
	 * Set a flag to print page footer.
	 * @param $val (boolean) set to true to print the page footer (default), false otherwise.
	 * @public
	 */
	public function setPrintFooter($val=true) {
		$this->print_footer = $val ? true : false;
	}

	/**
	 * Return the right-bottom (or left-bottom for RTL) corner X coordinate of last inserted image
	 * @return float
	 * @public
	 */
	public function getImageRBX() {
		return $this->img_rb_x;
	}

	/**
	 * Return the right-bottom (or left-bottom for RTL) corner Y coordinate of last inserted image
	 * @return float
	 * @public
	 */
	public function getImageRBY() {
		return $this->img_rb_y;
	}

	/**
	 * Reset the xobject template used by Header() method.
	 * @public
	 */
	public function resetHeaderTemplate() {
		$this->header_xobjid = -1;
	}

	/**
	 * Set a flag to automatically reset the xobject template used by Header() method at each page.
	 * @param $val (boolean) set to true to reset Header xobject template at each page, false otherwise.
	 * @public
	 */
	public function setHeaderTemplateAutoreset($val=true) {
		$this->header_xobj_autoreset = $val ? true : false;
	}

	/**
	 * This method is used to render the page header.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * @public
	 */
	public function Header() {
		if ($this->header_xobjid < 0) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();
			$this->y = $this->header_margin;
			if ($this->rtl) {
				$this->x = $this->w - $this->original_rMargin;
			} else {
				$this->x = $this->original_lMargin;
			}
			if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
				$imgtype = TCPDF_IMAGES::getImageFileType(K_PATH_IMAGES.$headerdata['logo']);
				if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
					$this->ImageEps(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} elseif ($imgtype == 'svg') {
					$this->ImageSVG(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} else {
					$this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				}
				$imgy = $this->getImageRBY();
			} else {
				$imgy = $this->y;
			}
			$cell_height = round(($this->cell_height_ratio * $headerfont[2]) / $this->k, 2);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
			} else {
				$header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
			}
			$cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1);
			$this->SetTextColorArray($this->header_text_color);
			// header title
			$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
			$this->SetX($header_x);
			$this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);
			// header string
			$this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
			$this->SetX($header_x);
			$this->MultiCell($cw, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
			// print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
			$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
			$this->endTemplate();
		}
		// print header template
		$x = 0;
		$dx = 0;
		if (!$this->header_xobj_autoreset AND $this->booklet AND (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}
		$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = -1;
		}
	}

	/**
	 * This method is used to render the page footer.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * @public
	 */
	public function Footer() {
		$cur_y = $this->y;
		$this->SetTextColorArray($this->footer_text_color);
		//set style for cell border
		$line_width = (0.85 / $this->k);
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
		//print document barcode
		$barcode = $this->getBarcode();
		if (!empty($barcode)) {
			$this->Ln($line_width);
			$barcode_width = round(($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
			$style = array(
				'position' => $this->rtl?'R':'L',
				'align' => $this->rtl?'R':'L',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'padding' => 0,
				'fgcolor' => array(0,0,0),
				'bgcolor' => false,
				'text' => false
			);
			$this->write1DBarcode($barcode, 'C128', '', $cur_y + $line_width, '', (($this->footer_margin / 3) - $line_width), 0.3, $style, '');
		}
		$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		if (empty($this->pagegroups)) {
			$pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}
		$this->SetY($cur_y);
		//Print page number
		if ($this->getRTL()) {
			$this->SetX($this->original_rMargin);
			$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
		} else {
			$this->SetX($this->original_lMargin);
			$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 'T', 0, 'R');
		}
	}

	/**
	 * This method is used to render the page header.
	 * @protected
	 * @since 4.0.012 (2008-07-24)
	 */
	protected function setHeader() {
		if (!$this->print_header OR ($this->state != 2)) {
			return;
		}
		$this->InHeader = true;
		$this->setGraphicVars($this->default_graphic_vars);
		$temp_thead = $this->thead;
		$temp_theadMargins = $this->theadMargins;
		$lasth = $this->lasth;
		$this->_out('q');
		$this->rMargin = $this->original_rMargin;
		$this->lMargin = $this->original_lMargin;
		$this->SetCellPadding(0);
		//set current position
		if ($this->rtl) {
			$this->SetXY($this->original_rMargin, $this->header_margin);
		} else {
			$this->SetXY($this->original_lMargin, $this->header_margin);
		}
		$this->SetFont($this->header_font[0], $this->header_font[1], $this->header_font[2]);
		$this->Header();
		//restore position
		if ($this->rtl) {
			$this->SetXY($this->original_rMargin, $this->tMargin);
		} else {
			$this->SetXY($this->original_lMargin, $this->tMargin);
		}
		$this->_out('Q');
		$this->lasth = $lasth;
		$this->thead = $temp_thead;
		$this->theadMargins = $temp_theadMargins;
		$this->newline = false;
		$this->InHeader = false;
	}

	/**
	 * This method is used to render the page footer.
	 * @protected
	 * @since 4.0.012 (2008-07-24)
	 */
	protected function setFooter() {
		if ($this->state != 2) {
			return;
		}
		$this->InFooter = true;
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// mark this point
		$this->footerpos[$this->page] = $this->pagelen[$this->page];
		$this->_out("\n");
		if ($this->print_footer) {
			$this->setGraphicVars($this->default_graphic_vars);
			$this->current_column = 0;
			$this->num_columns = 1;
			$temp_thead = $this->thead;
			$temp_theadMargins = $this->theadMargins;
			$lasth = $this->lasth;
			$this->_out('q');
			$this->rMargin = $this->original_rMargin;
			$this->lMargin = $this->original_lMargin;
			$this->SetCellPadding(0);
			//set current position
			$footer_y = $this->h - $this->footer_margin;
			if ($this->rtl) {
				$this->SetXY($this->original_rMargin, $footer_y);
			} else {
				$this->SetXY($this->original_lMargin, $footer_y);
			}
			$this->SetFont($this->footer_font[0], $this->footer_font[1], $this->footer_font[2]);
			$this->Footer();
			//restore position
			if ($this->rtl) {
				$this->SetXY($this->original_rMargin, $this->tMargin);
			} else {
				$this->SetXY($this->original_lMargin, $this->tMargin);
			}
			$this->_out('Q');
			$this->lasth = $lasth;
			$this->thead = $temp_thead;
			$this->theadMargins = $temp_theadMargins;
		}
		// restore graphic settings
		$this->setGraphicVars($gvars);
		$this->current_column = $gvars['current_column'];
		$this->num_columns = $gvars['num_columns'];
		// calculate footer length
		$this->footerlen[$this->page] = $this->pagelen[$this->page] - $this->footerpos[$this->page] + 1;
		$this->InFooter = false;
	}

	/**
	 * Check if we are on the page body (excluding page header and footer).
	 * @return true if we are not in page header nor in page footer, false otherwise.
	 * @protected
	 * @since 5.9.091 (2011-06-15)
	 */
	protected function inPageBody() {
		return (($this->InHeader === false) AND ($this->InFooter === false));
	}

	/**
	 * This method is used to render the table header on new page (if any).
	 * @protected
	 * @since 4.5.030 (2009-03-25)
	 */
	protected function setTableHeader() {
		if ($this->num_columns > 1) {
			// multi column mode
			return;
		}
		if (isset($this->theadMargins['top'])) {
			// restore the original top-margin
			$this->tMargin = $this->theadMargins['top'];
			$this->pagedim[$this->page]['tm'] = $this->tMargin;
			$this->y = $this->tMargin;
		}
		if (!TCPDF_STATIC::empty_string($this->thead) AND (!$this->inthead)) {
			// set margins
			$prev_lMargin = $this->lMargin;
			$prev_rMargin = $this->rMargin;
			$prev_cell_padding = $this->cell_padding;
			$this->lMargin = $this->theadMargins['lmargin'] + ($this->pagedim[$this->page]['olm'] - $this->pagedim[$this->theadMargins['page']]['olm']);
			$this->rMargin = $this->theadMargins['rmargin'] + ($this->pagedim[$this->page]['orm'] - $this->pagedim[$this->theadMargins['page']]['orm']);
			$this->cell_padding = $this->theadMargins['cell_padding'];
			if ($this->rtl) {
				$this->x = $this->w - $this->rMargin;
			} else {
				$this->x = $this->lMargin;
			}
			// account for special "cell" mode
			if ($this->theadMargins['cell']) {
				if ($this->rtl) {
					$this->x -= $this->cell_padding['R'];
				} else {
					$this->x += $this->cell_padding['L'];
				}
			}
			// print table header
			$this->writeHTML($this->thead, false, false, false, false, '');
			// set new top margin to skip the table headers
			if (!isset($this->theadMargins['top'])) {
				$this->theadMargins['top'] = $this->tMargin;
			}
			// store end of header position
			if (!isset($this->columns[0]['th'])) {
				$this->columns[0]['th'] = array();
			}
			$this->columns[0]['th']['\''.$this->page.'\''] = $this->y;
			$this->tMargin = $this->y;
			$this->pagedim[$this->page]['tm'] = $this->tMargin;
			$this->lasth = 0;
			$this->lMargin = $prev_lMargin;
			$this->rMargin = $prev_rMargin;
			$this->cell_padding = $prev_cell_padding;
		}
	}

	/**
	 * Returns the current page number.
	 * @return int page number
	 * @public
	 * @since 1.0
	 * @see getAliasNbPages()
	 */
	public function PageNo() {
		return $this->page;
	}

	/**
	 * Returns the array of spot colors.
	 * @return (array) Spot colors array.
	 * @public
	 * @since 6.0.038 (2013-09-30)
	 */
	public function getAllSpotColors() {
		return $this->spot_colors;
	}

	/**
	 * Defines a new spot color.
	 * It can be expressed in RGB components or gray scale.
	 * The method can be called before the first page is created and the value is retained from page to page.
	 * @param $name (string) Full name of the spot color.
	 * @param $c (float) Cyan color for CMYK. Value between 0 and 100.
	 * @param $m (float) Magenta color for CMYK. Value between 0 and 100.
	 * @param $y (float) Yellow color for CMYK. Value between 0 and 100.
	 * @param $k (float) Key (Black) color for CMYK. Value between 0 and 100.
	 * @public
	 * @since 4.0.024 (2008-09-12)
	 * @see SetDrawSpotColor(), SetFillSpotColor(), SetTextSpotColor()
	 */
	public function AddSpotColor($name, $c, $m, $y, $k) {
		if (!isset($this->spot_colors[$name])) {
			$i = (1 + count($this->spot_colors));
			$this->spot_colors[$name] = array('C' => $c, 'M' => $m, 'Y' => $y, 'K' => $k, 'name' => $name, 'i' => $i);
		}
	}

	/**
	 * Set the spot color for the specified type ('draw', 'fill', 'text').
	 * @param $type (string) Type of object affected by this color: ('draw', 'fill', 'text').
	 * @param $name (string) Name of the spot color.
	 * @param $tint (float) Intensity of the color (from 0 to 100 ; 100 = full intensity by default).
	 * @return (string) PDF color command.
	 * @public
	 * @since 5.9.125 (2011-10-03)
	 */
	public function setSpotColor($type, $name, $tint=100) {
		$spotcolor = TCPDF_COLORS::getSpotColor($name, $this->spot_colors);
		if ($spotcolor === false) {
			$this->Error('Undefined spot color: '.$name.', you must add it using the AddSpotColor() method.');
		}
		$tint = (max(0, min(100, $tint)) / 100);
		$pdfcolor = sprintf('/CS%d ', $this->spot_colors[$name]['i']);
		switch ($type) {
			case 'draw': {
				$pdfcolor .= sprintf('CS %F SCN', $tint);
				$this->DrawColor = $pdfcolor;
				$this->strokecolor = $spotcolor;
				break;
			}
			case 'fill': {
				$pdfcolor .= sprintf('cs %F scn', $tint);
				$this->FillColor = $pdfcolor;
				$this->bgcolor = $spotcolor;
				break;
			}
			case 'text': {
				$pdfcolor .= sprintf('cs %F scn', $tint);
				$this->TextColor = $pdfcolor;
				$this->fgcolor = $spotcolor;
				break;
			}
		}
		$this->ColorFlag = ($this->FillColor != $this->TextColor);
		if ($this->state == 2) {
			$this->_out($pdfcolor);
		}
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['spot_colors'][$name] = $this->spot_colors[$name];
		}
		return $pdfcolor;
	}

	/**
	 * Defines the spot color used for all drawing operations (lines, rectangles and cell borders).
	 * @param $name (string) Name of the spot color.
	 * @param $tint (float) Intensity of the color (from 0 to 100 ; 100 = full intensity by default).
	 * @public
	 * @since 4.0.024 (2008-09-12)
	 * @see AddSpotColor(), SetFillSpotColor(), SetTextSpotColor()
	 */
	public function SetDrawSpotColor($name, $tint=100) {
		$this->setSpotColor('draw', $name, $tint);
	}

	/**
	 * Defines the spot color used for all filling operations (filled rectangles and cell backgrounds).
	 * @param $name (string) Name of the spot color.
	 * @param $tint (float) Intensity of the color (from 0 to 100 ; 100 = full intensity by default).
	 * @public
	 * @since 4.0.024 (2008-09-12)
	 * @see AddSpotColor(), SetDrawSpotColor(), SetTextSpotColor()
	 */
	public function SetFillSpotColor($name, $tint=100) {
		$this->setSpotColor('fill', $name, $tint);
	}

	/**
	 * Defines the spot color used for text.
	 * @param $name (string) Name of the spot color.
	 * @param $tint (int) Intensity of the color (from 0 to 100 ; 100 = full intensity by default).
	 * @public
	 * @since 4.0.024 (2008-09-12)
	 * @see AddSpotColor(), SetDrawSpotColor(), SetFillSpotColor()
	 */
	public function SetTextSpotColor($name, $tint=100) {
		$this->setSpotColor('text', $name, $tint);
	}

	/**
	 * Set the color array for the specified type ('draw', 'fill', 'text').
	 * It can be expressed in RGB, CMYK or GRAY SCALE components.
	 * The method can be called before the first page is created and the value is retained from page to page.
	 * @param $type (string) Type of object affected by this color: ('draw', 'fill', 'text').
	 * @param $color (array) Array of colors (1=gray, 3=RGB, 4=CMYK or 5=spotcolor=CMYK+name values).
	 * @param $ret (boolean) If true do not send the PDF command.
	 * @return (string) The PDF command or empty string.
	 * @public
	 * @since 3.1.000 (2008-06-11)
	 */
	public function setColorArray($type, $color, $ret=false) {
		if (is_array($color)) {
			$color = array_values($color);
			// component: grey, RGB red or CMYK cyan
			$c = isset($color[0]) ? $color[0] : -1;
			// component: RGB green or CMYK magenta
			$m = isset($color[1]) ? $color[1] : -1;
			// component: RGB blue or CMYK yellow
			$y = isset($color[2]) ? $color[2] : -1;
			// component: CMYK black
			$k = isset($color[3]) ? $color[3] : -1;
			// color name
			$name = isset($color[4]) ? $color[4] : '';
			if ($c >= 0) {
				return $this->setColor($type, $c, $m, $y, $k, $ret, $name);
			}
		}
		return '';
	}

	/**
	 * Defines the color used for all drawing operations (lines, rectangles and cell borders).
	 * It can be expressed in RGB, CMYK or GRAY SCALE components.
	 * The method can be called before the first page is created and the value is retained from page to page.
	 * @param $color (array) Array of colors (1, 3 or 4 values).
	 * @param $ret (boolean) If true do not send the PDF command.
	 * @return string the PDF command
	 * @public
	 * @since 3.1.000 (2008-06-11)
	 * @see SetDrawColor()
	 */
	public function SetDrawColorArray($color, $ret=false) {
		return $this->setColorArray('draw', $color, $ret);
	}

	/**
	 * Defines the color used for all filling operations (filled rectangles and cell backgrounds).
	 * It can be expressed in RGB, CMYK or GRAY SCALE components.
	 * The method can be called before the first page is created and the value is retained from page to page.
	 * @param $color (array) Array of colors (1, 3 or 4 values).
	 * @param $ret (boolean) If true do not send the PDF command.
	 * @public
	 * @since 3.1.000 (2008-6-11)
	 * @see SetFillColor()
	 */
	public function SetFillColorArray($color, $ret=false) {
		return $this->setColorArray('fill', $color, $ret);
	}

	/**
	 * Defines the color used for text. It can be expressed in RGB components or gray scale.
	 * The method can be called before the first page is created and the value is retained from page to page.
	 * @param $color (array) Array of colors (1, 3 or 4 values).
	 * @param $ret (boolean) If true do not send the PDF command.
	 * @public
	 * @since 3.1.000 (2008-6-11)
	 * @see SetFillColor()
	 */
	public function SetTextColorArray($color, $ret=false) {
		return $this->setColorArray('text', $color, $ret);
	}

	/**
	 * Defines the color used by the specified type ('draw', 'fill', 'text').
	 * @param $type (string) Type of object affected by this color: ('draw', 'fill', 'text').
	 * @param $col1 (float) GRAY level for single color, or Red color for RGB (0-255), or CYAN color for CMYK (0-100).
	 * @param $col2 (float) GREEN color for RGB (0-255), or MAGENTA color for CMYK (0-100).
	 * @param $col3 (float) BLUE color for RGB (0-255), or YELLOW color for CMYK (0-100).
	 * @param $col4 (float) KEY (BLACK) color for CMYK (0-100).
	 * @param $ret (boolean) If true do not send the command.
	 * @param $name (string) spot color name (if any)
	 * @return (string) The PDF command or empty string.
	 * @public
	 * @since 5.9.125 (2011-10-03)
	 */
	public function setColor($type, $col1=0, $col2=-1, $col3=-1, $col4=-1, $ret=false, $name='') {
		// set default values
		if (!is_numeric($col1)) {
			$col1 = 0;
		}
		if (!is_numeric($col2)) {
			$col2 = -1;
		}
		if (!is_numeric($col3)) {
			$col3 = -1;
		}
		if (!is_numeric($col4)) {
			$col4 = -1;
		}
		// set color by case
		$suffix = '';
		if (($col2 == -1) AND ($col3 == -1) AND ($col4 == -1)) {
			// Grey scale
			$col1 = max(0, min(255, $col1));
			$intcolor = array('G' => $col1);
			$pdfcolor = sprintf('%F ', ($col1 / 255));
			$suffix = 'g';
		} elseif ($col4 == -1) {
			// RGB
			$col1 = max(0, min(255, $col1));
			$col2 = max(0, min(255, $col2));
			$col3 = max(0, min(255, $col3));
			$intcolor = array('R' => $col1, 'G' => $col2, 'B' => $col3);
			$pdfcolor = sprintf('%F %F %F ', ($col1 / 255), ($col2 / 255), ($col3 / 255));
			$suffix = 'rg';
		} else {
			$col1 = max(0, min(100, $col1));
			$col2 = max(0, min(100, $col2));
			$col3 = max(0, min(100, $col3));
			$col4 = max(0, min(100, $col4));
			if (empty($name)) {
				// CMYK
				$intcolor = array('C' => $col1, 'M' => $col2, 'Y' => $col3, 'K' => $col4);
				$pdfcolor = sprintf('%F %F %F %F ', ($col1 / 100), ($col2 / 100), ($col3 / 100), ($col4 / 100));
				$suffix = 'k';
			} else {
				// SPOT COLOR
				$intcolor = array('C' => $col1, 'M' => $col2, 'Y' => $col3, 'K' => $col4, 'name' => $name);
				$this->AddSpotColor($name, $col1, $col2, $col3, $col4);
				$pdfcolor = $this->setSpotColor($type, $name, 100);
			}
		}
		switch ($type) {
			case 'draw': {
				$pdfcolor .= strtoupper($suffix);
				$this->DrawColor = $pdfcolor;
				$this->strokecolor = $intcolor;
				break;
			}
			case 'fill': {
				$pdfcolor .= $suffix;
				$this->FillColor = $pdfcolor;
				$this->bgcolor = $intcolor;
				break;
			}
			case 'text': {
				$pdfcolor .= $suffix;
				$this->TextColor = $pdfcolor;
				$this->fgcolor = $intcolor;
				break;
			}
		}
		$this->ColorFlag = ($this->FillColor != $this->TextColor);
		if (($type != 'text') AND ($this->state == 2)) {
			if (!$ret) {
				$this->_out($pdfcolor);
			}
			return $pdfcolor;
		}
		return '';
	}

	/**
	 * Defines the color used for all drawing operations (lines, rectangles and cell borders). It can be expressed in RGB components or gray scale. The method can be called before the first page is created and the value is retained from page to page.
	 * @param $col1 (float) GRAY level for single color, or Red color for RGB (0-255), or CYAN color for CMYK (0-100).
	 * @param $col2 (float) GREEN color for RGB (0-255), or MAGENTA color for CMYK (0-100).
	 * @param $col3 (float) BLUE color for RGB (0-255), or YELLOW color for CMYK (0-100).
	 * @param $col4 (float) KEY (BLACK) color for CMYK (0-100).
	 * @param $ret (boolean) If true do not send the command.
	 * @param $name (string) spot color name (if any)
	 * @return string the PDF command
	 * @public
	 * @since 1.3
	 * @see SetDrawColorArray(), SetFillColor(), SetTextColor(), Line(), Rect(), Cell(), MultiCell()
	 */
	public function SetDrawColor($col1=0, $col2=-1, $col3=-1, $col4=-1, $ret=false, $name='') {
		return $this->setColor('draw', $col1, $col2, $col3, $col4, $ret, $name);
	}

	/**
	 * Defines the color used for all filling operations (filled rectangles and cell backgrounds). It can be expressed in RGB components or gray scale. The method can be called before the first page is created and the value is retained from page to page.
	 * @param $col1 (float) GRAY level for single color, or Red color for RGB (0-255), or CYAN color for CMYK (0-100).
	 * @param $col2 (float) GREEN color for RGB (0-255), or MAGENTA color for CMYK (0-100).
	 * @param $col3 (float) BLUE color for RGB (0-255), or YELLOW color for CMYK (0-100).
	 * @param $col4 (float) KEY (BLACK) color for CMYK (0-100).
	 * @param $ret (boolean) If true do not send the command.
	 * @param $name (string) Spot color name (if any).
	 * @return (string) The PDF command.
	 * @public
	 * @since 1.3
	 * @see SetFillColorArray(), SetDrawColor(), SetTextColor(), Rect(), Cell(), MultiCell()
	 */
	public function SetFillColor($col1=0, $col2=-1, $col3=-1, $col4=-1, $ret=false, $name='') {
		return $this->setColor('fill', $col1, $col2, $col3, $col4, $ret, $name);
	}

	/**
	 * Defines the color used for text. It can be expressed in RGB components or gray scale. The method can be called before the first page is created and the value is retained from page to page.
	 * @param $col1 (float) GRAY level for single color, or Red color for RGB (0-255), or CYAN color for CMYK (0-100).
	 * @param $col2 (float) GREEN color for RGB (0-255), or MAGENTA color for CMYK (0-100).
	 * @param $col3 (float) BLUE color for RGB (0-255), or YELLOW color for CMYK (0-100).
	 * @param $col4 (float) KEY (BLACK) color for CMYK (0-100).
	 * @param $ret (boolean) If true do not send the command.
	 * @param $name (string) Spot color name (if any).
	 * @return (string) Empty string.
	 * @public
	 * @since 1.3
	 * @see SetTextColorArray(), SetDrawColor(), SetFillColor(), Text(), Cell(), MultiCell()
	 */
	public function SetTextColor($col1=0, $col2=-1, $col3=-1, $col4=-1, $ret=false, $name='') {
		return $this->setColor('text', $col1, $col2, $col3, $col4, $ret, $name);
	}

	/**
	 * Returns the length of a string in user unit. A font must be selected.<br>
	 * @param $s (string) The string whose length is to be computed
	 * @param $fontname (string) Family font. It can be either a name defined by AddFont() or one of the standard families. It is also possible to pass an empty string, in that case, the current family is retained.
	 * @param $fontstyle (string) Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line-through</li><li>O: overline</li></ul> or any combination. The default value is regular.
	 * @param $fontsize (float) Font size in points. The default value is the current size.
	 * @param $getarray (boolean) if true returns an array of characters widths, if false returns the total length.
	 * @return mixed int total string length or array of characted widths
	 * @author Nicola Asuni
	 * @public
	 * @since 1.2
	 */
	public function GetStringWidth($s, $fontname='', $fontstyle='', $fontsize=0, $getarray=false) {
		return $this->GetArrStringWidth(TCPDF_FONTS::utf8Bidi(TCPDF_FONTS::UTF8StringToArray($s, $this->isunicode, $this->CurrentFont), $s, $this->tmprtl, $this->isunicode, $this->CurrentFont), $fontname, $fontstyle, $fontsize, $getarray);
	}

	/**
	 * Returns the string length of an array of chars in user unit or an array of characters widths. A font must be selected.<br>
	 * @param $sa (string) The array of chars whose total length is to be computed
	 * @param $fontname (string) Family font. It can be either a name defined by AddFont() or one of the standard families. It is also possible to pass an empty string, in that case, the current family is retained.
	 * @param $fontstyle (string) Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line through</li><li>O: overline</li></ul> or any combination. The default value is regular.
	 * @param $fontsize (float) Font size in points. The default value is the current size.
	 * @param $getarray (boolean) if true returns an array of characters widths, if false returns the total length.
	 * @return mixed int total string length or array of characted widths
	 * @author Nicola Asuni
	 * @public
	 * @since 2.4.000 (2008-03-06)
	 */
	public function GetArrStringWidth($sa, $fontname='', $fontstyle='', $fontsize=0, $getarray=false) {
		// store current values
		if (!TCPDF_STATIC::empty_string($fontname)) {
			$prev_FontFamily = $this->FontFamily;
			$prev_FontStyle = $this->FontStyle;
			$prev_FontSizePt = $this->FontSizePt;
			$this->SetFont($fontname, $fontstyle, $fontsize, '', 'default', false);
		}
		// convert UTF-8 array to Latin1 if required
		if ($this->isunicode AND (!$this->isUnicodeFont())) {
			$sa = TCPDF_FONTS::UTF8ArrToLatin1Arr($sa);
		}
		$w = 0; // total width
		$wa = array(); // array of characters widths
		foreach ($sa as $ck => $char) {
			// character width
			$cw = $this->GetCharWidth($char, isset($sa[($ck + 1)]));
			$wa[] = $cw;
			$w += $cw;
		}
		// restore previous values
		if (!TCPDF_STATIC::empty_string($fontname)) {
			$this->SetFont($prev_FontFamily, $prev_FontStyle, $prev_FontSizePt, '', 'default', false);
		}
		if ($getarray) {
			return $wa;
		}
		return $w;
	}

	/**
	 * Returns the length of the char in user unit for the current font considering current stretching and spacing (tracking).
	 * @param $char (int) The char code whose length is to be returned
	 * @param $notlast (boolean) If false ignore the font-spacing.
	 * @return float char width
	 * @author Nicola Asuni
	 * @public
	 * @since 2.4.000 (2008-03-06)
	 */
	public function GetCharWidth($char, $notlast=true) {
		// get raw width
		$chw = $this->getRawCharWidth($char);
		if (($this->font_spacing < 0) OR (($this->font_spacing > 0) AND $notlast)) {
			// increase/decrease font spacing
			$chw += $this->font_spacing;
		}
		if ($this->font_stretching != 100) {
			// fixed stretching mode
			$chw *= ($this->font_stretching / 100);
		}
		return $chw;
	}

	/**
	 * Returns the length of the char in user unit for the current font.
	 * @param $char (int) The char code whose length is to be returned
	 * @return float char width
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-28)
	 */
	public function getRawCharWidth($char) {
		if ($char == 173) {
			// SHY character will not be printed
			return (0);
		}
		if (isset($this->CurrentFont['cw'][$char])) {
			$w = $this->CurrentFont['cw'][$char];
		} elseif (isset($this->CurrentFont['dw'])) {
			// default width
			$w = $this->CurrentFont['dw'];
		} elseif (isset($this->CurrentFont['cw'][32])) {
			// default width
			$w = $this->CurrentFont['cw'][32];
		} else {
			$w = 600;
		}
		return $this->getAbsFontMeasure($w);
	}

	/**
	 * Returns the numbero of characters in a string.
	 * @param $s (string) The input string.
	 * @return int number of characters
	 * @public
	 * @since 2.0.0001 (2008-01-07)
	 */
	public function GetNumChars($s) {
		if ($this->isUnicodeFont()) {
			return count(TCPDF_FONTS::UTF8StringToArray($s, $this->isunicode, $this->CurrentFont));
		}
		return strlen($s);
	}

	/**
	 * Fill the list of available fonts ($this->fontlist).
	 * @protected
	 * @since 4.0.013 (2008-07-28)
	 */
	protected function getFontsList() {
		if (($fontsdir = opendir(TCPDF_FONTS::_getfontpath())) !== false) {
			while (($file = readdir($fontsdir)) !== false) {
				if (substr($file, -4) == '.php') {
					array_push($this->fontlist, strtolower(basename($file, '.php')));
				}
			}
			closedir($fontsdir);
		}
	}

	/**
	 * Returns the unicode caracter specified by the value
	 * @param $c (int) UTF-8 value
	 * @return Returns the specified character.
	 * @since 2.3.000 (2008-03-05)
	 * @public
	 * @deprecated
	 */
	public function unichr($c) {
		return TCPDF_FONTS::unichr($c, $this->isunicode);
	}

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
	 * @return (string) TCPDF font name.
	 * @author Nicola Asuni
	 * @since 5.9.123 (2010-09-30)
	 * @public
	 * @deprecated
	 */
	public function addTTFfont($fontfile, $fonttype='', $enc='', $flags=32, $outpath='', $platid=3, $encid=1, $addcbbox=false) {
		return TCPDF_FONTS::addTTFfont($fontfile, $fonttype, $enc, $flags, $outpath, $platid, $encid, $addcbbox);
	}

	/**
	 * Imports a TrueType, Type1, core, or CID0 font and makes it available.
	 * It is necessary to generate a font definition file first (read /fonts/utils/README.TXT).
	 * The definition file (and the font file itself when embedding) must be present either in the current directory or in the one indicated by K_PATH_FONTS if the constant is defined. If it could not be found, the error "Could not include font definition file" is generated.
	 * @param $family (string) Font family. The name can be chosen arbitrarily. If it is a standard family name, it will override the corresponding font.
	 * @param $style (string) Font style. Possible values are (case insensitive):<ul><li>empty string: regular (default)</li><li>B: bold</li><li>I: italic</li><li>BI or IB: bold italic</li></ul>
	 * @param $fontfile (string) The font definition file. By default, the name is built from the family and style, in lower case with no spaces.
	 * @return array containing the font data, or false in case of error.
	 * @param $subset (mixed) if true embedd only a subset of the font (stores only the information related to the used characters); if false embedd full font; if 'default' uses the default value set using setFontSubsetting(). This option is valid only for TrueTypeUnicode fonts. If you want to enable users to change the document, set this parameter to false. If you subset the font, the person who receives your PDF would need to have your same font in order to make changes to your PDF. The file size of the PDF would also be smaller because you are embedding only part of a font.
	 * @public
	 * @since 1.5
	 * @see SetFont(), setFontSubsetting()
	 */
	public function AddFont($family, $style='', $fontfile='', $subset='default') {
		if ($subset === 'default') {
			$subset = $this->font_subsetting;
		}
		if ($this->pdfa_mode) {
			$subset = false;
		}
		if (TCPDF_STATIC::empty_string($family)) {
			if (!TCPDF_STATIC::empty_string($this->FontFamily)) {
				$family = $this->FontFamily;
			} else {
				$this->Error('Empty font family');
			}
		}
		// move embedded styles on $style
		if (substr($family, -1) == 'I') {
			$style .= 'I';
			$family = substr($family, 0, -1);
		}
		if (substr($family, -1) == 'B') {
			$style .= 'B';
			$family = substr($family, 0, -1);
		}
		// normalize family name
		$family = strtolower($family);
		if ((!$this->isunicode) AND ($family == 'arial')) {
			$family = 'helvetica';
		}
		if (($family == 'symbol') OR ($family == 'zapfdingbats')) {
			$style = '';
		}
		if ($this->pdfa_mode AND (isset($this->CoreFonts[$family]))) {
			// all fonts must be embedded
			$family = 'pdfa'.$family;
		}
		$tempstyle = strtoupper($style);
		$style = '';
		// underline
		if (strpos($tempstyle, 'U') !== false) {
			$this->underline = true;
		} else {
			$this->underline = false;
		}
		// line-through (deleted)
		if (strpos($tempstyle, 'D') !== false) {
			$this->linethrough = true;
		} else {
			$this->linethrough = false;
		}
		// overline
		if (strpos($tempstyle, 'O') !== false) {
			$this->overline = true;
		} else {
			$this->overline = false;
		}
		// bold
		if (strpos($tempstyle, 'B') !== false) {
			$style .= 'B';
		}
		// oblique
		if (strpos($tempstyle, 'I') !== false) {
			$style .= 'I';
		}
		$bistyle = $style;
		$fontkey = $family.$style;
		$font_style = $style.($this->underline ? 'U' : '').($this->linethrough ? 'D' : '').($this->overline ? 'O' : '');
		$fontdata = array('fontkey' => $fontkey, 'family' => $family, 'style' => $font_style);
		// check if the font has been already added
		$fb = $this->getFontBuffer($fontkey);
		if ($fb !== false) {
			if ($this->inxobj) {
				// we are inside an XObject template
				$this->xobjects[$this->xobjid]['fonts'][$fontkey] = $fb['i'];
			}
			return $fontdata;
		}
		// get specified font directory (if any)
		$fontdir = false;
		if (!TCPDF_STATIC::empty_string($fontfile)) {
			$fontdir = dirname($fontfile);
			if (TCPDF_STATIC::empty_string($fontdir) OR ($fontdir == '.')) {
				$fontdir = '';
			} else {
				$fontdir .= '/';
			}
		}
		// true when the font style variation is missing
		$missing_style = false;
		// search and include font file
		if (TCPDF_STATIC::empty_string($fontfile) OR (!@file_exists($fontfile))) {
			// build a standard filenames for specified font
			$tmp_fontfile = str_replace(' ', '', $family).strtolower($style).'.php';
			$fontfile = TCPDF_FONTS::getFontFullPath($tmp_fontfile, $fontdir);
			if (TCPDF_STATIC::empty_string($fontfile)) {
				$missing_style = true;
				// try to remove the style part
				$tmp_fontfile = str_replace(' ', '', $family).'.php';
				$fontfile = TCPDF_FONTS::getFontFullPath($tmp_fontfile, $fontdir);
			}
		}
		// include font file
		if (!TCPDF_STATIC::empty_string($fontfile) AND (@file_exists($fontfile))) {
			include($fontfile);
		} else {
			$this->Error('Could not include font definition file: '.$family.'');
		}
		// check font parameters
		if ((!isset($type)) OR (!isset($cw))) {
			$this->Error('The font definition file has a bad format: '.$fontfile.'');
		}
		// SET default parameters
		if (!isset($file) OR TCPDF_STATIC::empty_string($file)) {
			$file = '';
		}
		if (!isset($enc) OR TCPDF_STATIC::empty_string($enc)) {
			$enc = '';
		}
		if (!isset($cidinfo) OR TCPDF_STATIC::empty_string($cidinfo)) {
			$cidinfo = array('Registry'=>'Adobe', 'Ordering'=>'Identity', 'Supplement'=>0);
			$cidinfo['uni2cid'] = array();
		}
		if (!isset($ctg) OR TCPDF_STATIC::empty_string($ctg)) {
			$ctg = '';
		}
		if (!isset($desc) OR TCPDF_STATIC::empty_string($desc)) {
			$desc = array();
		}
		if (!isset($up) OR TCPDF_STATIC::empty_string($up)) {
			$up = -100;
		}
		if (!isset($ut) OR TCPDF_STATIC::empty_string($ut)) {
			$ut = 50;
		}
		if (!isset($cw) OR TCPDF_STATIC::empty_string($cw)) {
			$cw = array();
		}
		if (!isset($dw) OR TCPDF_STATIC::empty_string($dw)) {
			// set default width
			if (isset($desc['MissingWidth']) AND ($desc['MissingWidth'] > 0)) {
				$dw = $desc['MissingWidth'];
			} elseif (isset($cw[32])) {
				$dw = $cw[32];
			} else {
				$dw = 600;
			}
		}
		++$this->numfonts;
		if ($type == 'core') {
			$name = $this->CoreFonts[$fontkey];
			$subset = false;
		} elseif (($type == 'TrueType') OR ($type == 'Type1')) {
			$subset = false;
		} elseif ($type == 'TrueTypeUnicode') {
			$enc = 'Identity-H';
		} elseif ($type == 'cidfont0') {
			if ($this->pdfa_mode) {
				$this->Error('All fonts must be embedded in PDF/A mode!');
			}
		} else {
			$this->Error('Unknow font type: '.$type.'');
		}
		// set name if unset
		if (!isset($name) OR empty($name)) {
			$name = $fontkey;
		}
		// create artificial font style variations if missing (only works with non-embedded fonts)
		if (($type != 'core') AND $missing_style) {
			// style variations
			$styles = array('' => '', 'B' => ',Bold', 'I' => ',Italic', 'BI' => ',BoldItalic');
			$name .= $styles[$bistyle];
			// artificial bold
			if (strpos($bistyle, 'B') !== false) {
				if (isset($desc['StemV'])) {
					// from normal to bold
					$desc['StemV'] = round($desc['StemV'] * 1.75);
				} else {
					// bold
					$desc['StemV'] = 123;
				}
			}
			// artificial italic
			if (strpos($bistyle, 'I') !== false) {
				if (isset($desc['ItalicAngle'])) {
					$desc['ItalicAngle'] -= 11;
				} else {
					$desc['ItalicAngle'] = -11;
				}
				if (isset($desc['Flags'])) {
					$desc['Flags'] |= 64; //bit 7
				} else {
					$desc['Flags'] = 64;
				}
			}
		}
		// check if the array of characters bounding boxes is defined
		if (!isset($cbbox)) {
			$cbbox = array();
		}
		// initialize subsetchars
		$subsetchars = array_fill(0, 255, true);
		$this->setFontBuffer($fontkey, array('fontkey' => $fontkey, 'i' => $this->numfonts, 'type' => $type, 'name' => $name, 'desc' => $desc, 'up' => $up, 'ut' => $ut, 'cw' => $cw, 'cbbox' => $cbbox, 'dw' => $dw, 'enc' => $enc, 'cidinfo' => $cidinfo, 'file' => $file, 'ctg' => $ctg, 'subset' => $subset, 'subsetchars' => $subsetchars));
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['fonts'][$fontkey] = $this->numfonts;
		}
		if (isset($diff) AND (!empty($diff))) {
			//Search existing encodings
			$d = 0;
			$nb = count($this->diffs);
			for ($i=1; $i <= $nb; ++$i) {
				if ($this->diffs[$i] == $diff) {
					$d = $i;
					break;
				}
			}
			if ($d == 0) {
				$d = $nb + 1;
				$this->diffs[$d] = $diff;
			}
			$this->setFontSubBuffer($fontkey, 'diff', $d);
		}
		if (!TCPDF_STATIC::empty_string($file)) {
			if (!isset($this->FontFiles[$file])) {
				if ((strcasecmp($type,'TrueType') == 0) OR (strcasecmp($type, 'TrueTypeUnicode') == 0)) {
					$this->FontFiles[$file] = array('length1' => $originalsize, 'fontdir' => $fontdir, 'subset' => $subset, 'fontkeys' => array($fontkey));
				} elseif ($type != 'core') {
					$this->FontFiles[$file] = array('length1' => $size1, 'length2' => $size2, 'fontdir' => $fontdir, 'subset' => $subset, 'fontkeys' => array($fontkey));
				}
			} else {
				// update fontkeys that are sharing this font file
				$this->FontFiles[$file]['subset'] = ($this->FontFiles[$file]['subset'] AND $subset);
				if (!in_array($fontkey, $this->FontFiles[$file]['fontkeys'])) {
					$this->FontFiles[$file]['fontkeys'][] = $fontkey;
				}
			}
		}
		return $fontdata;
	}

	/**
	 * Sets the font used to print character strings.
	 * The font can be either a standard one or a font added via the AddFont() method. Standard fonts use Windows encoding cp1252 (Western Europe).
	 * The method can be called before the first page is created and the font is retained from page to page.
	 * If you just wish to change the current font size, it is simpler to call SetFontSize().
	 * Note: for the standard fonts, the font metric files must be accessible. There are three possibilities for this:<ul><li>They are in the current directory (the one where the running script lies)</li><li>They are in one of the directories defined by the include_path parameter</li><li>They are in the directory defined by the K_PATH_FONTS constant</li></ul><br />
	 * @param $family (string) Family font. It can be either a name defined by AddFont() or one of the standard Type1 families (case insensitive):<ul><li>times (Times-Roman)</li><li>timesb (Times-Bold)</li><li>timesi (Times-Italic)</li><li>timesbi (Times-BoldItalic)</li><li>helvetica (Helvetica)</li><li>helveticab (Helvetica-Bold)</li><li>helveticai (Helvetica-Oblique)</li><li>helveticabi (Helvetica-BoldOblique)</li><li>courier (Courier)</li><li>courierb (Courier-Bold)</li><li>courieri (Courier-Oblique)</li><li>courierbi (Courier-BoldOblique)</li><li>symbol (Symbol)</li><li>zapfdingbats (ZapfDingbats)</li></ul> It is also possible to pass an empty string. In that case, the current family is retained.
	 * @param $style (string) Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line through</li><li>O: overline</li></ul> or any combination. The default value is regular. Bold and italic styles do not apply to Symbol and ZapfDingbats basic fonts or other fonts when not defined.
	 * @param $size (float) Font size in points. The default value is the current size. If no size has been specified since the beginning of the document, the value taken is 12
	 * @param $fontfile (string) The font definition file. By default, the name is built from the family and style, in lower case with no spaces.
	 * @param $subset (mixed) if true embedd only a subset of the font (stores only the information related to the used characters); if false embedd full font; if 'default' uses the default value set using setFontSubsetting(). This option is valid only for TrueTypeUnicode fonts. If you want to enable users to change the document, set this parameter to false. If you subset the font, the person who receives your PDF would need to have your same font in order to make changes to your PDF. The file size of the PDF would also be smaller because you are embedding only part of a font.
	 * @param $out (boolean) if true output the font size command, otherwise only set the font properties.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.0
	 * @see AddFont(), SetFontSize()
	 */
	public function SetFont($family, $style='', $size=null, $fontfile='', $subset='default', $out=true) {
		//Select a font; size given in points
		if ($size === null) {
			$size = $this->FontSizePt;
		}
		if ($size < 0) {
			$size = 0;
		}
		// try to add font (if not already added)
		$fontdata = $this->AddFont($family, $style, $fontfile, $subset);
		$this->FontFamily = $fontdata['family'];
		$this->FontStyle = $fontdata['style'];
		if (isset($this->CurrentFont['fontkey']) AND isset($this->CurrentFont['subsetchars'])) {
			// save subset chars of the previous font
			$this->setFontSubBuffer($this->CurrentFont['fontkey'], 'subsetchars', $this->CurrentFont['subsetchars']);
		}
		$this->CurrentFont = $this->getFontBuffer($fontdata['fontkey']);
		$this->SetFontSize($size, $out);
	}

	/**
	 * Defines the size of the current font.
	 * @param $size (float) The font size in points.
	 * @param $out (boolean) if true output the font size command, otherwise only set the font properties.
	 * @public
	 * @since 1.0
	 * @see SetFont()
	 */
	public function SetFontSize($size, $out=true) {
		// font size in points
		$this->FontSizePt = $size;
		// font size in user units
		$this->FontSize = $size / $this->k;
		// calculate some font metrics
		if (isset($this->CurrentFont['desc']['FontBBox'])) {
			$bbox = explode(' ', substr($this->CurrentFont['desc']['FontBBox'], 1, -1));
			$font_height = ((intval($bbox[3]) - intval($bbox[1])) * $size / 1000);
		} else {
			$font_height = $size * 1.219;
		}
		if (isset($this->CurrentFont['desc']['Ascent']) AND ($this->CurrentFont['desc']['Ascent'] > 0)) {
			$font_ascent = ($this->CurrentFont['desc']['Ascent'] * $size / 1000);
		}
		if (isset($this->CurrentFont['desc']['Descent']) AND ($this->CurrentFont['desc']['Descent'] <= 0)) {
			$font_descent = (- $this->CurrentFont['desc']['Descent'] * $size / 1000);
		}
		if (!isset($font_ascent) AND !isset($font_descent)) {
			// core font
			$font_ascent = 0.76 * $font_height;
			$font_descent = $font_height - $font_ascent;
		} elseif (!isset($font_descent)) {
			$font_descent = $font_height - $font_ascent;
		} elseif (!isset($font_ascent)) {
			$font_ascent = $font_height - $font_descent;
		}
		$this->FontAscent = ($font_ascent / $this->k);
		$this->FontDescent = ($font_descent / $this->k);
		if ($out AND ($this->page > 0) AND (isset($this->CurrentFont['i'])) AND ($this->state == 2)) {
			$this->_out(sprintf('BT /F%d %F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
		}
	}

	/**
	 * Returns the bounding box of the current font in user units.
	 * @return array
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getFontBBox() {
		$fbbox = array();
		if (isset($this->CurrentFont['desc']['FontBBox'])) {
			$tmpbbox = explode(' ', substr($this->CurrentFont['desc']['FontBBox'], 1, -1));
			$fbbox = array_map(array($this,'getAbsFontMeasure'), $tmpbbox);
		} else {
			// Find max width
			if (isset($this->CurrentFont['desc']['MaxWidth'])) {
				$maxw = $this->getAbsFontMeasure(intval($this->CurrentFont['desc']['MaxWidth']));
			} else {
				$maxw = 0;
				if (isset($this->CurrentFont['desc']['MissingWidth'])) {
					$maxw = max($maxw, $this->CurrentFont['desc']['MissingWidth']);
				}
				if (isset($this->CurrentFont['desc']['AvgWidth'])) {
					$maxw = max($maxw, $this->CurrentFont['desc']['AvgWidth']);
				}
				if (isset($this->CurrentFont['dw'])) {
					$maxw = max($maxw, $this->CurrentFont['dw']);
				}
				foreach ($this->CurrentFont['cw'] as $char => $w) {
					$maxw = max($maxw, $w);
				}
				if ($maxw == 0) {
					$maxw = 600;
				}
				$maxw = $this->getAbsFontMeasure($maxw);
			}
			$fbbox = array(0, (0 - $this->FontDescent), $maxw, $this->FontAscent);
		}
		return $fbbox;
	}

	/**
	 * Convert a relative font measure into absolute value.
	 * @param $s (int) Font measure.
	 * @return float Absolute measure.
	 * @since 5.9.186 (2012-09-13)
	 */
	public function getAbsFontMeasure($s) {
		return ($s * $this->FontSize / 1000);
	}

	/**
	 * Returns the glyph bounding box of the specified character in the current font in user units.
	 * @param $char (int) Input character code.
	 * @return mixed array(xMin, yMin, xMax, yMax) or FALSE if not defined.
	 * @since 5.9.186 (2012-09-13)
	 */
	public function getCharBBox($char) {
		if (isset($this->CurrentFont['cbbox'][$char])) {
			return array_map(array($this,'getAbsFontMeasure'), $this->CurrentFont['cbbox'][intval($char)]);
		}
		return false;
	}

	/**
	 * Return the font descent value
	 * @param $font (string) font name
	 * @param $style (string) font style
	 * @param $size (float) The size (in points)
	 * @return int font descent
	 * @public
	 * @author Nicola Asuni
	 * @since 4.9.003 (2010-03-30)
	 */
	public function getFontDescent($font, $style='', $size=0) {
		$fontdata = $this->AddFont($font, $style);
		$fontinfo = $this->getFontBuffer($fontdata['fontkey']);
		if (isset($fontinfo['desc']['Descent']) AND ($fontinfo['desc']['Descent'] <= 0)) {
			$descent = (- $fontinfo['desc']['Descent'] * $size / 1000);
		} else {
			$descent = (1.219 * 0.24 * $size);
		}
		return ($descent / $this->k);
	}

	/**
	 * Return the font ascent value.
	 * @param $font (string) font name
	 * @param $style (string) font style
	 * @param $size (float) The size (in points)
	 * @return int font ascent
	 * @public
	 * @author Nicola Asuni
	 * @since 4.9.003 (2010-03-30)
	 */
	public function getFontAscent($font, $style='', $size=0) {
		$fontdata = $this->AddFont($font, $style);
		$fontinfo = $this->getFontBuffer($fontdata['fontkey']);
		if (isset($fontinfo['desc']['Ascent']) AND ($fontinfo['desc']['Ascent'] > 0)) {
			$ascent = ($fontinfo['desc']['Ascent'] * $size / 1000);
		} else {
			$ascent = 1.219 * 0.76 * $size;
		}
		return ($ascent / $this->k);
	}

	/**
	 * Return true in the character is present in the specified font.
	 * @param $char (mixed) Character to check (integer value or string)
	 * @param $font (string) Font name (family name).
	 * @param $style (string) Font style.
	 * @return (boolean) true if the char is defined, false otherwise.
	 * @public
	 * @since 5.9.153 (2012-03-28)
	 */
	public function isCharDefined($char, $font='', $style='') {
		if (is_string($char)) {
			// get character code
			$char = TCPDF_FONTS::UTF8StringToArray($char, $this->isunicode, $this->CurrentFont);
			$char = $char[0];
		}
		if (TCPDF_STATIC::empty_string($font)) {
			if (TCPDF_STATIC::empty_string($style)) {
				return (isset($this->CurrentFont['cw'][intval($char)]));
			}
			$font = $this->FontFamily;
		}
		$fontdata = $this->AddFont($font, $style);
		$fontinfo = $this->getFontBuffer($fontdata['fontkey']);
		return (isset($fontinfo['cw'][intval($char)]));
	}

	/**
	 * Replace missing font characters on selected font with specified substitutions.
	 * @param $text (string) Text to process.
	 * @param $font (string) Font name (family name).
	 * @param $style (string) Font style.
	 * @param $subs (array) Array of possible character substitutions. The key is the character to check (integer value) and the value is a single intege value or an array of possible substitutes.
	 * @return (string) Processed text.
	 * @public
	 * @since 5.9.153 (2012-03-28)
	 */
	public function replaceMissingChars($text, $font='', $style='', $subs=array()) {
		if (empty($subs)) {
			return $text;
		}
		if (TCPDF_STATIC::empty_string($font)) {
			$font = $this->FontFamily;
		}
		$fontdata = $this->AddFont($font, $style);
		$fontinfo = $this->getFontBuffer($fontdata['fontkey']);
		$uniarr = TCPDF_FONTS::UTF8StringToArray($text, $this->isunicode, $this->CurrentFont);
		foreach ($uniarr as $k => $chr) {
			if (!isset($fontinfo['cw'][$chr])) {
				// this character is missing on the selected font
				if (isset($subs[$chr])) {
					// we have available substitutions
					if (is_array($subs[$chr])) {
						foreach($subs[$chr] as $s) {
							if (isset($fontinfo['cw'][$s])) {
								$uniarr[$k] = $s;
								break;
							}
						}
					} elseif (isset($fontinfo['cw'][$subs[$chr]])) {
						$uniarr[$k] = $subs[$chr];
					}
				}
			}
		}
		return TCPDF_FONTS::UniArrSubString(TCPDF_FONTS::UTF8ArrayToUniArray($uniarr, $this->isunicode));
	}

	/**
	 * Defines the default monospaced font.
	 * @param $font (string) Font name.
	 * @public
	 * @since 4.5.025
	 */
	public function SetDefaultMonospacedFont($font) {
		$this->default_monospaced_font = $font;
	}

	/**
	 * Creates a new internal link and returns its identifier. An internal link is a clickable area which directs to another place within the document.<br />
	 * The identifier can then be passed to Cell(), Write(), Image() or Link(). The destination is defined with SetLink().
	 * @public
	 * @since 1.5
	 * @see Cell(), Write(), Image(), Link(), SetLink()
	 */
	public function AddLink() {
		//Create a new internal link
		$n = count($this->links) + 1;
		$this->links[$n] = array(0, 0);
		return $n;
	}

	/**
	 * Defines the page and position a link points to.
	 * @param $link (int) The link identifier returned by AddLink()
	 * @param $y (float) Ordinate of target position; -1 indicates the current position. The default value is 0 (top of page)
	 * @param $page (int) Number of target page; -1 indicates the current page. This is the default value
	 * @public
	 * @since 1.5
	 * @see AddLink()
	 */
	public function SetLink($link, $y=0, $page=-1) {
		if ($y == -1) {
			$y = $this->y;
		}
		if ($page == -1) {
			$page = $this->page;
		}
		$this->links[$link] = array($page, $y);
	}

	/**
	 * Puts a link on a rectangular area of the page.
	 * Text or image links are generally put via Cell(), Write() or Image(), but this method can be useful for instance to define a clickable area inside an image.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $w (float) Width of the rectangle
	 * @param $h (float) Height of the rectangle
	 * @param $link (mixed) URL or identifier returned by AddLink()
	 * @param $spaces (int) number of spaces on the text to link
	 * @public
	 * @since 1.5
	 * @see AddLink(), Annotation(), Cell(), Write(), Image()
	 */
	public function Link($x, $y, $w, $h, $link, $spaces=0) {
		$this->Annotation($x, $y, $w, $h, $link, array('Subtype'=>'Link'), $spaces);
	}

	/**
	 * Puts a markup annotation on a rectangular area of the page.
	 * !!!!THE ANNOTATION SUPPORT IS NOT YET FULLY IMPLEMENTED !!!!
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $w (float) Width of the rectangle
	 * @param $h (float) Height of the rectangle
	 * @param $text (string) annotation text or alternate content
	 * @param $opt (array) array of options (see section 8.4 of PDF reference 1.7).
	 * @param $spaces (int) number of spaces on the text to link
	 * @public
	 * @since 4.0.018 (2008-08-06)
	 */
	public function Annotation($x, $y, $w, $h, $text, $opt=array('Subtype'=>'Text'), $spaces=0) {
		if ($this->inxobj) {
			// store parameters for later use on template
			$this->xobjects[$this->xobjid]['annotations'][] = array('x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'text' => $text, 'opt' => $opt, 'spaces' => $spaces);
			return;
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		// recalculate coordinates to account for graphic transformations
		if (isset($this->transfmatrix) AND !empty($this->transfmatrix)) {
			for ($i=$this->transfmatrix_key; $i > 0; --$i) {
				$maxid = count($this->transfmatrix[$i]) - 1;
				for ($j=$maxid; $j >= 0; --$j) {
					$ctm = $this->transfmatrix[$i][$j];
					if (isset($ctm['a'])) {
						$x = $x * $this->k;
						$y = ($this->h - $y) * $this->k;
						$w = $w * $this->k;
						$h = $h * $this->k;
						// top left
						$xt = $x;
						$yt = $y;
						$x1 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y1 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// top right
						$xt = $x + $w;
						$yt = $y;
						$x2 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y2 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// bottom left
						$xt = $x;
						$yt = $y - $h;
						$x3 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y3 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// bottom right
						$xt = $x + $w;
						$yt = $y - $h;
						$x4 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y4 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// new coordinates (rectangle area)
						$x = min($x1, $x2, $x3, $x4);
						$y = max($y1, $y2, $y3, $y4);
						$w = (max($x1, $x2, $x3, $x4) - $x) / $this->k;
						$h = ($y - min($y1, $y2, $y3, $y4)) / $this->k;
						$x = $x / $this->k;
						$y = $this->h - ($y / $this->k);
					}
				}
			}
		}
		if ($this->page <= 0) {
			$page = 1;
		} else {
			$page = $this->page;
		}
		if (!isset($this->PageAnnots[$page])) {
			$this->PageAnnots[$page] = array();
		}
		$this->PageAnnots[$page][] = array('n' => ++$this->n, 'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'txt' => $text, 'opt' => $opt, 'numspaces' => $spaces);
		if (!$this->pdfa_mode) {
			if ((($opt['Subtype'] == 'FileAttachment') OR ($opt['Subtype'] == 'Sound')) AND (!TCPDF_STATIC::empty_string($opt['FS']))
				AND (@file_exists($opt['FS']) OR TCPDF_STATIC::isValidURL($opt['FS']))
				AND (!isset($this->embeddedfiles[basename($opt['FS'])]))) {
				$this->embeddedfiles[basename($opt['FS'])] = array('f' => ++$this->n, 'n' => ++$this->n, 'file' => $opt['FS']);
			}
		}
		// Add widgets annotation's icons
		if (isset($opt['mk']['i']) AND @file_exists($opt['mk']['i'])) {
			$this->Image($opt['mk']['i'], '', '', 10, 10, '', '', '', false, 300, '', false, false, 0, false, true);
		}
		if (isset($opt['mk']['ri']) AND @file_exists($opt['mk']['ri'])) {
			$this->Image($opt['mk']['ri'], '', '', 0, 0, '', '', '', false, 300, '', false, false, 0, false, true);
		}
		if (isset($opt['mk']['ix']) AND @file_exists($opt['mk']['ix'])) {
			$this->Image($opt['mk']['ix'], '', '', 0, 0, '', '', '', false, 300, '', false, false, 0, false, true);
		}
	}

	/**
	 * Embedd the attached files.
	 * @since 4.4.000 (2008-12-07)
	 * @protected
	 * @see Annotation()
	 */
	protected function _putEmbeddedFiles() {
		if ($this->pdfa_mode) {
			// embedded files are not allowed in PDF/A mode
			return;
		}
		reset($this->embeddedfiles);
		foreach ($this->embeddedfiles as $filename => $filedata) {
			$data = TCPDF_STATIC::fileGetContents($filedata['file']);
			if ($data !== FALSE) {
				$rawsize = strlen($data);
				if ($rawsize > 0) {
					// update name tree
					$this->efnames[$filename] = $filedata['f'].' 0 R';
					// embedded file specification object
					$out = $this->_getobj($filedata['f'])."\n";
					$out .= '<</Type /Filespec /F '.$this->_datastring($filename, $filedata['f']).' /EF <</F '.$filedata['n'].' 0 R>> >>';
					$out .= "\n".'endobj';
					$this->_out($out);
					// embedded file object
					$filter = '';
					if ($this->compress) {
						$data = gzcompress($data);
						$filter = ' /Filter /FlateDecode';
					}
					$stream = $this->_getrawstream($data, $filedata['n']);
					$out = $this->_getobj($filedata['n'])."\n";
					$out .= '<< /Type /EmbeddedFile'.$filter.' /Length '.strlen($stream).' /Params <</Size '.$rawsize.'>> >>';
					$out .= ' stream'."\n".$stream."\n".'endstream';
					$out .= "\n".'endobj';
					$this->_out($out);
				}
			}
		}
	}

	/**
	 * Prints a text cell at the specified position.
	 * This method allows to place a string precisely on the page.
	 * @param $x (float) Abscissa of the cell origin
	 * @param $y (float) Ordinate of the cell origin
	 * @param $txt (string) String to print
	 * @param $fstroke (int) outline size in user units (false = disable)
	 * @param $fclip (boolean) if true activate clipping mode (you must call StartTransform() before this function and StopTransform() to stop the clipping tranformation).
	 * @param $ffill (boolean) if true fills the text
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $ln (int) Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right (or left for RTL languages)</li><li>1: to the beginning of the next line</li><li>2: below</li></ul>Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
	 * @param $align (string) Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align (default value)</li><li>C: center</li><li>R: right align</li><li>J: justify</li></ul>
	 * @param $fill (boolean) Indicates if the cell background must be painted (true) or transparent (false).
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $stretch (int) font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param $ignore_min_height (boolean) if true ignore automatic minimum height value.
	 * @param $calign (string) cell vertical alignment relative to the specified Y value. Possible values are:<ul><li>T : cell top</li><li>A : font top</li><li>L : font baseline</li><li>D : font bottom</li><li>B : cell bottom</li></ul>
	 * @param $valign (string) text vertical alignment inside the cell. Possible values are:<ul><li>T : top</li><li>C : center</li><li>B : bottom</li></ul>
	 * @param $rtloff (boolean) if true uses the page top-left corner as origin of axis for $x and $y initial position.
	 * @public
	 * @since 1.0
	 * @see Cell(), Write(), MultiCell(), WriteHTML(), WriteHTMLCell()
	 */
	public function Text($x, $y, $txt, $fstroke=false, $fclip=false, $ffill=true, $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M', $rtloff=false) {
		$textrendermode = $this->textrendermode;
		$textstrokewidth = $this->textstrokewidth;
		$this->setTextRenderingMode($fstroke, $ffill, $fclip);
		$this->SetXY($x, $y, $rtloff);
		$this->Cell(0, 0, $txt, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign);
		// restore previous rendering mode
		$this->textrendermode = $textrendermode;
		$this->textstrokewidth = $textstrokewidth;
	}

	/**
	 * Whenever a page break condition is met, the method is called, and the break is issued or not depending on the returned value.
	 * The default implementation returns a value according to the mode selected by SetAutoPageBreak().<br />
	 * This method is called automatically and should not be called directly by the application.
	 * @return boolean
	 * @public
	 * @since 1.4
	 * @see SetAutoPageBreak()
	 */
	public function AcceptPageBreak() {
		if ($this->num_columns > 1) {
			// multi column mode
			if ($this->current_column < ($this->num_columns - 1)) {
				// go to next column
				$this->selectColumn($this->current_column + 1);
			} elseif ($this->AutoPageBreak) {
				// add a new page
				$this->AddPage();
				// set first column
				$this->selectColumn(0);
			}
			// avoid page breaking from checkPageBreak()
			return false;
		}
		return $this->AutoPageBreak;
	}

	/**
	 * Add page if needed.
	 * @param $h (float) Cell height. Default value: 0.
	 * @param $y (mixed) starting y position, leave empty for current position.
	 * @param $addpage (boolean) if true add a page, otherwise only return the true/false state
	 * @return boolean true in case of page break, false otherwise.
	 * @since 3.2.000 (2008-07-01)
	 * @protected
	 */
	protected function checkPageBreak($h=0, $y='', $addpage=true) {
		if (TCPDF_STATIC::empty_string($y)) {
			$y = $this->y;
		}
		$current_page = $this->page;
		if ((($y + $h) > $this->PageBreakTrigger) AND ($this->inPageBody()) AND ($this->AcceptPageBreak())) {
			if ($addpage) {
				//Automatic page break
				$x = $this->x;
				$this->AddPage($this->CurOrientation);
				$this->y = $this->tMargin;
				$oldpage = $this->page - 1;
				if ($this->rtl) {
					if ($this->pagedim[$this->page]['orm'] != $this->pagedim[$oldpage]['orm']) {
						$this->x = $x - ($this->pagedim[$this->page]['orm'] - $this->pagedim[$oldpage]['orm']);
					} else {
						$this->x = $x;
					}
				} else {
					if ($this->pagedim[$this->page]['olm'] != $this->pagedim[$oldpage]['olm']) {
						$this->x = $x + ($this->pagedim[$this->page]['olm'] - $this->pagedim[$oldpage]['olm']);
					} else {
						$this->x = $x;
					}
				}
			}
			return true;
		}
		if ($current_page != $this->page) {
			// account for columns mode
			return true;
		}
		return false;
	}

	/**
	 * Prints a cell (rectangular area) with optional borders, background color and character string. The upper-left corner of the cell corresponds to the current position. The text can be aligned or centered. After the call, the current position moves to the right or to the next line. It is possible to put a link on the text.<br />
	 * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
	 * @param $w (float) Cell width. If 0, the cell extends up to the right margin.
	 * @param $h (float) Cell height. Default value: 0.
	 * @param $txt (string) String to print. Default value: empty string.
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $ln (int) Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right (or left for RTL languages)</li><li>1: to the beginning of the next line</li><li>2: below</li></ul> Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
	 * @param $align (string) Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align (default value)</li><li>C: center</li><li>R: right align</li><li>J: justify</li></ul>
	 * @param $fill (boolean) Indicates if the cell background must be painted (true) or transparent (false).
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $stretch (int) font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param $ignore_min_height (boolean) if true ignore automatic minimum height value.
	 * @param $calign (string) cell vertical alignment relative to the specified Y value. Possible values are:<ul><li>T : cell top</li><li>C : center</li><li>B : cell bottom</li><li>A : font top</li><li>L : font baseline</li><li>D : font bottom</li></ul>
	 * @param $valign (string) text vertical alignment inside the cell. Possible values are:<ul><li>T : top</li><li>C : center</li><li>B : bottom</li></ul>
	 * @public
	 * @since 1.0
	 * @see SetFont(), SetDrawColor(), SetFillColor(), SetTextColor(), SetLineWidth(), AddLink(), Ln(), MultiCell(), Write(), SetAutoPageBreak()
	 */
	public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M') {
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		$this->adjustCellPadding($border);
		if (!$ignore_min_height) {
			$min_cell_height = ($this->FontSize * $this->cell_height_ratio) + $this->cell_padding['T'] + $this->cell_padding['B'];
			if ($h < $min_cell_height) {
				$h = $min_cell_height;
			}
		}
		$this->checkPageBreak($h + $this->cell_margin['T'] + $this->cell_margin['B']);
		// apply text shadow if enabled
		if ($this->txtshadow['enabled']) {
			// save data
			$x = $this->x;
			$y = $this->y;
			$bc = $this->bgcolor;
			$fc = $this->fgcolor;
			$sc = $this->strokecolor;
			$alpha = $this->alpha;
			// print shadow
			$this->x += $this->txtshadow['depth_w'];
			$this->y += $this->txtshadow['depth_h'];
			$this->SetFillColorArray($this->txtshadow['color']);
			$this->SetTextColorArray($this->txtshadow['color']);
			$this->SetDrawColorArray($this->txtshadow['color']);
			if ($this->txtshadow['opacity'] != $alpha['CA']) {
				$this->setAlpha($this->txtshadow['opacity'], $this->txtshadow['blend_mode']);
			}
			if ($this->state == 2) {
				$this->_out($this->getCellCode($w, $h, $txt, $border, $ln, $align, $fill, $link, $stretch, true, $calign, $valign));
			}
			//restore data
			$this->x = $x;
			$this->y = $y;
			$this->SetFillColorArray($bc);
			$this->SetTextColorArray($fc);
			$this->SetDrawColorArray($sc);
			if ($this->txtshadow['opacity'] != $alpha['CA']) {
				$this->setAlpha($alpha['CA'], $alpha['BM'], $alpha['ca'], $alpha['AIS']);
			}
		}
		if ($this->state == 2) {
			$this->_out($this->getCellCode($w, $h, $txt, $border, $ln, $align, $fill, $link, $stretch, true, $calign, $valign));
		}
		$this->cell_padding = $prev_cell_padding;
		$this->cell_margin = $prev_cell_margin;
	}

	/**
	 * Returns the PDF string code to print a cell (rectangular area) with optional borders, background color and character string. The upper-left corner of the cell corresponds to the current position. The text can be aligned or centered. After the call, the current position moves to the right or to the next line. It is possible to put a link on the text.<br />
	 * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
	 * @param $w (float) Cell width. If 0, the cell extends up to the right margin.
	 * @param $h (float) Cell height. Default value: 0.
	 * @param $txt (string) String to print. Default value: empty string.
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $ln (int) Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right (or left for RTL languages)</li><li>1: to the beginning of the next line</li><li>2: below</li></ul>Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
	 * @param $align (string) Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align (default value)</li><li>C: center</li><li>R: right align</li><li>J: justify</li></ul>
	 * @param $fill (boolean) Indicates if the cell background must be painted (true) or transparent (false).
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $stretch (int) font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param $ignore_min_height (boolean) if true ignore automatic minimum height value.
	 * @param $calign (string) cell vertical alignment relative to the specified Y value. Possible values are:<ul><li>T : cell top</li><li>C : center</li><li>B : cell bottom</li><li>A : font top</li><li>L : font baseline</li><li>D : font bottom</li></ul>
	 * @param $valign (string) text vertical alignment inside the cell. Possible values are:<ul><li>T : top</li><li>M : middle</li><li>B : bottom</li></ul>
	 * @return string containing cell code
	 * @protected
	 * @since 1.0
	 * @see Cell()
	 */
	protected function getCellCode($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M') {
		// replace 'NO-BREAK SPACE' (U+00A0) character with a simple space
		$txt = str_replace(TCPDF_FONTS::unichr(160, $this->isunicode), ' ', $txt);
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		$txt = TCPDF_STATIC::removeSHY($txt, $this->isunicode);
		$rs = ''; //string to be returned
		$this->adjustCellPadding($border);
		if (!$ignore_min_height) {
			$min_cell_height = ($this->FontSize * $this->cell_height_ratio) + $this->cell_padding['T'] + $this->cell_padding['B'];
			if ($h < $min_cell_height) {
				$h = $min_cell_height;
			}
		}
		$k = $this->k;
		// check page for no-write regions and adapt page margins if necessary
		list($this->x, $this->y) = $this->checkPageRegions($h, $this->x, $this->y);
		if ($this->rtl) {
			$x = $this->x - $this->cell_margin['R'];
		} else {
			$x = $this->x + $this->cell_margin['L'];
		}
		$y = $this->y + $this->cell_margin['T'];
		$prev_font_stretching = $this->font_stretching;
		$prev_font_spacing = $this->font_spacing;
		// cell vertical alignment
		switch ($calign) {
			case 'A': {
				// font top
				switch ($valign) {
					case 'T': {
						// top
						$y -= $this->cell_padding['T'];
						break;
					}
					case 'B': {
						// bottom
						$y -= ($h - $this->cell_padding['B'] - $this->FontAscent - $this->FontDescent);
						break;
					}
					default:
					case 'C':
					case 'M': {
						// center
						$y -= (($h - $this->FontAscent - $this->FontDescent) / 2);
						break;
					}
				}
				break;
			}
			case 'L': {
				// font baseline
				switch ($valign) {
					case 'T': {
						// top
						$y -= ($this->cell_padding['T'] + $this->FontAscent);
						break;
					}
					case 'B': {
						// bottom
						$y -= ($h - $this->cell_padding['B'] - $this->FontDescent);
						break;
					}
					default:
					case 'C':
					case 'M': {
						// center
						$y -= (($h + $this->FontAscent - $this->FontDescent) / 2);
						break;
					}
				}
				break;
			}
			case 'D': {
				// font bottom
				switch ($valign) {
					case 'T': {
						// top
						$y -= ($this->cell_padding['T'] + $this->FontAscent + $this->FontDescent);
						break;
					}
					case 'B': {
						// bottom
						$y -= ($h - $this->cell_padding['B']);
						break;
					}
					default:
					case 'C':
					case 'M': {
						// center
						$y -= (($h + $this->FontAscent + $this->FontDescent) / 2);
						break;
					}
				}
				break;
			}
			case 'B': {
				// cell bottom
				$y -= $h;
				break;
			}
			case 'C':
			case 'M': {
				// cell center
				$y -= ($h / 2);
				break;
			}
			default:
			case 'T': {
				// cell top
				break;
			}
		}
		// text vertical alignment
		switch ($valign) {
			case 'T': {
				// top
				$yt = $y + $this->cell_padding['T'];
				break;
			}
			case 'B': {
				// bottom
				$yt = $y + $h - $this->cell_padding['B'] - $this->FontAscent - $this->FontDescent;
				break;
			}
			default:
			case 'C':
			case 'M': {
				// center
				$yt = $y + (($h - $this->FontAscent - $this->FontDescent) / 2);
				break;
			}
		}
		$basefonty = $yt + $this->FontAscent;
		if (TCPDF_STATIC::empty_string($w) OR ($w <= 0)) {
			if ($this->rtl) {
				$w = $x - $this->lMargin;
			} else {
				$w = $this->w - $this->rMargin - $x;
			}
		}
		$s = '';
		// fill and borders
		if (is_string($border) AND (strlen($border) == 4)) {
			// full border
			$border = 1;
		}
		if ($fill OR ($border == 1)) {
			if ($fill) {
				$op = ($border == 1) ? 'B' : 'f';
			} else {
				$op = 'S';
			}
			if ($this->rtl) {
				$xk = (($x - $w) * $k);
			} else {
				$xk = ($x * $k);
			}
			$s .= sprintf('%F %F %F %F re %s ', $xk, (($this->h - $y) * $k), ($w * $k), (-$h * $k), $op);
		}
		// draw borders
		$s .= $this->getCellBorder($x, $y, $w, $h, $border);
		if ($txt != '') {
			$txt2 = $txt;
			if ($this->isunicode) {
				if (($this->CurrentFont['type'] == 'core') OR ($this->CurrentFont['type'] == 'TrueType') OR ($this->CurrentFont['type'] == 'Type1')) {
					$txt2 = TCPDF_FONTS::UTF8ToLatin1($txt2, $this->isunicode, $this->CurrentFont);
				} else {
					$unicode = TCPDF_FONTS::UTF8StringToArray($txt, $this->isunicode, $this->CurrentFont); // array of UTF-8 unicode values
					$unicode = TCPDF_FONTS::utf8Bidi($unicode, '', $this->tmprtl, $this->isunicode, $this->CurrentFont);
					// replace thai chars (if any)
					if (defined('K_THAI_TOPCHARS') AND (K_THAI_TOPCHARS == true)) {
						// number of chars
						$numchars = count($unicode);
						// po pla, for far, for fan
						$longtail = array(0x0e1b, 0x0e1d, 0x0e1f);
						// do chada, to patak
						$lowtail = array(0x0e0e, 0x0e0f);
						// mai hun arkad, sara i, sara ii, sara ue, sara uee
						$upvowel = array(0x0e31, 0x0e34, 0x0e35, 0x0e36, 0x0e37);
						// mai ek, mai tho, mai tri, mai chattawa, karan
						$tonemark = array(0x0e48, 0x0e49, 0x0e4a, 0x0e4b, 0x0e4c);
						// sara u, sara uu, pinthu
						$lowvowel = array(0x0e38, 0x0e39, 0x0e3a);
						$output = array();
						for ($i = 0; $i < $numchars; $i++) {
							if (($unicode[$i] >= 0x0e00) && ($unicode[$i] <= 0x0e5b)) {
								$ch0 = $unicode[$i];
								$ch1 = ($i > 0) ? $unicode[($i - 1)] : 0;
								$ch2 = ($i > 1) ? $unicode[($i - 2)] : 0;
								$chn = ($i < ($numchars - 1)) ? $unicode[($i + 1)] : 0;
								if (in_array($ch0, $tonemark)) {
									if ($chn == 0x0e33) {
										// sara um
										if (in_array($ch1, $longtail)) {
											// tonemark at upper left
											$output[] = $this->replaceChar($ch0, (0xf713 + $ch0 - 0x0e48));
										} else {
											// tonemark at upper right (normal position)
											$output[] = $ch0;
										}
									} elseif (in_array($ch1, $longtail) OR (in_array($ch2, $longtail) AND in_array($ch1, $lowvowel))) {
										// tonemark at lower left
										$output[] = $this->replaceChar($ch0, (0xf705 + $ch0 - 0x0e48));
									} elseif (in_array($ch1, $upvowel)) {
										if (in_array($ch2, $longtail)) {
											// tonemark at upper left
											$output[] = $this->replaceChar($ch0, (0xf713 + $ch0 - 0x0e48));
										} else {
											// tonemark at upper right (normal position)
											$output[] = $ch0;
										}
									} else {
										// tonemark at lower right
										$output[] = $this->replaceChar($ch0, (0xf70a + $ch0 - 0x0e48));
									}
								} elseif (($ch0 == 0x0e33) AND (in_array($ch1, $longtail) OR (in_array($ch2, $longtail) AND in_array($ch1, $tonemark)))) {
									// add lower left nikhahit and sara aa
									if ($this->isCharDefined(0xf711) AND $this->isCharDefined(0x0e32)) {
										$output[] = 0xf711;
										$this->CurrentFont['subsetchars'][0xf711] = true;
										$output[] = 0x0e32;
										$this->CurrentFont['subsetchars'][0x0e32] = true;
									} else {
										$output[] = $ch0;
									}
								} elseif (in_array($ch1, $longtail)) {
									if ($ch0 == 0x0e31) {
										// lower left mai hun arkad
										$output[] = $this->replaceChar($ch0, 0xf710);
									} elseif (in_array($ch0, $upvowel)) {
										// lower left
										$output[] = $this->replaceChar($ch0, (0xf701 + $ch0 - 0x0e34));
									} elseif ($ch0 == 0x0e47) {
										// lower left mai tai koo
										$output[] = $this->replaceChar($ch0, 0xf712);
									} else {
										// normal character
										$output[] = $ch0;
									}
								} elseif (in_array($ch1, $lowtail) AND in_array($ch0, $lowvowel)) {
									// lower vowel
									$output[] = $this->replaceChar($ch0, (0xf718 + $ch0 - 0x0e38));
								} elseif (($ch0 == 0x0e0d) AND in_array($chn, $lowvowel)) {
									// yo ying without lower part
									$output[] = $this->replaceChar($ch0, 0xf70f);
								} elseif (($ch0 == 0x0e10) AND in_array($chn, $lowvowel)) {
									// tho santan without lower part
									$output[] = $this->replaceChar($ch0, 0xf700);
								} else {
									$output[] = $ch0;
								}
							} else {
								// non-thai character
								$output[] = $unicode[$i];
							}
						}
						$unicode = $output;
						// update font subsetchars
						$this->setFontSubBuffer($this->CurrentFont['fontkey'], 'subsetchars', $this->CurrentFont['subsetchars']);
					} // end of K_THAI_TOPCHARS
					$txt2 = TCPDF_FONTS::arrUTF8ToUTF16BE($unicode, false);
				}
			}
			$txt2 = TCPDF_STATIC::_escape($txt2);
			// get current text width (considering general font stretching and spacing)
			$txwidth = $this->GetStringWidth($txt);
			$width = $txwidth;
			// check for stretch mode
			if ($stretch > 0) {
				// calculate ratio between cell width and text width
				if ($width <= 0) {
					$ratio = 1;
				} else {
					$ratio = (($w - $this->cell_padding['L'] - $this->cell_padding['R']) / $width);
				}
				// check if stretching is required
				if (($ratio < 1) OR (($ratio > 1) AND (($stretch % 2) == 0))) {
					// the text will be stretched to fit cell width
					if ($stretch > 2) {
						// set new character spacing
						$this->font_spacing += ($w - $this->cell_padding['L'] - $this->cell_padding['R'] - $width) / (max(($this->GetNumChars($txt) - 1), 1) * ($this->font_stretching / 100));
					} else {
						// set new horizontal stretching
						$this->font_stretching *= $ratio;
					}
					// recalculate text width (the text fills the entire cell)
					$width = $w - $this->cell_padding['L'] - $this->cell_padding['R'];
					// reset alignment
					$align = '';
				}
			}
			if ($this->font_stretching != 100) {
				// apply font stretching
				$rs .= sprintf('BT %F Tz ET ', $this->font_stretching);
			}
			if ($this->font_spacing != 0) {
				// increase/decrease font spacing
				$rs .= sprintf('BT %F Tc ET ', ($this->font_spacing * $this->k));
			}
			if ($this->ColorFlag AND ($this->textrendermode < 4)) {
				$s .= 'q '.$this->TextColor.' ';
			}
			// rendering mode
			$s .= sprintf('BT %d Tr %F w ET ', $this->textrendermode, ($this->textstrokewidth * $this->k));
			// count number of spaces
			$ns = substr_count($txt, chr(32));
			// Justification
			$spacewidth = 0;
			if (($align == 'J') AND ($ns > 0)) {
				if ($this->isUnicodeFont()) {
					// get string width without spaces
					$width = $this->GetStringWidth(str_replace(' ', '', $txt));
					// calculate average space width
					$spacewidth = -1000 * ($w - $width - $this->cell_padding['L'] - $this->cell_padding['R']) / ($ns?$ns:1) / ($this->FontSize?$this->FontSize:1);
					if ($this->font_stretching != 100) {
						// word spacing is affected by stretching
						$spacewidth /= ($this->font_stretching / 100);
					}
					// set word position to be used with TJ operator
					$txt2 = str_replace(chr(0).chr(32), ') '.sprintf('%F', $spacewidth).' (', $txt2);
					$unicode_justification = true;
				} else {
					// get string width
					$width = $txwidth;
					// new space width
					$spacewidth = (($w - $width - $this->cell_padding['L'] - $this->cell_padding['R']) / ($ns?$ns:1)) * $this->k;
					if ($this->font_stretching != 100) {
						// word spacing (Tw) is affected by stretching
						$spacewidth /= ($this->font_stretching / 100);
					}
					// set word spacing
					$rs .= sprintf('BT %F Tw ET ', $spacewidth);
				}
				$width = $w - $this->cell_padding['L'] - $this->cell_padding['R'];
			}
			// replace carriage return characters
			$txt2 = str_replace("\r", ' ', $txt2);
			switch ($align) {
				case 'C': {
					$dx = ($w - $width) / 2;
					break;
				}
				case 'R': {
					if ($this->rtl) {
						$dx = $this->cell_padding['R'];
					} else {
						$dx = $w - $width - $this->cell_padding['R'];
					}
					break;
				}
				case 'L': {
					if ($this->rtl) {
						$dx = $w - $width - $this->cell_padding['L'];
					} else {
						$dx = $this->cell_padding['L'];
					}
					break;
				}
				case 'J':
				default: {
					if ($this->rtl) {
						$dx = $this->cell_padding['R'];
					} else {
						$dx = $this->cell_padding['L'];
					}
					break;
				}
			}
			if ($this->rtl) {
				$xdx = $x - $dx - $width;
			} else {
				$xdx = $x + $dx;
			}
			$xdk = $xdx * $k;
			// print text
			$s .= sprintf('BT %F %F Td [(%s)] TJ ET', $xdk, (($this->h - $basefonty) * $k), $txt2);
			if (isset($uniblock)) {
				// print overlapping characters as separate string
				$xshift = 0; // horizontal shift
				$ty = (($this->h - $basefonty + (0.2 * $this->FontSize)) * $k);
				$spw = (($w - $txwidth - $this->cell_padding['L'] - $this->cell_padding['R']) / ($ns?$ns:1));
				foreach ($uniblock as $uk => $uniarr) {
					if (($uk % 2) == 0) {
						// x space to skip
						if ($spacewidth != 0) {
							// justification shift
							$xshift += (count(array_keys($uniarr, 32)) * $spw);
						}
						$xshift += $this->GetArrStringWidth($uniarr); // + shift justification
					} else {
						// character to print
						$topchr = TCPDF_FONTS::arrUTF8ToUTF16BE($uniarr, false);
						$topchr = TCPDF_STATIC::_escape($topchr);
						$s .= sprintf(' BT %F %F Td [(%s)] TJ ET', ($xdk + ($xshift * $k)), $ty, $topchr);
					}
				}
			}
			if ($this->underline) {
				$s .= ' '.$this->_dounderlinew($xdx, $basefonty, $width);
			}
			if ($this->linethrough) {
				$s .= ' '.$this->_dolinethroughw($xdx, $basefonty, $width);
			}
			if ($this->overline) {
				$s .= ' '.$this->_dooverlinew($xdx, $basefonty, $width);
			}
			if ($this->ColorFlag AND ($this->textrendermode < 4)) {
				$s .= ' Q';
			}
			if ($link) {
				$this->Link($xdx, $yt, $width, ($this->FontAscent + $this->FontDescent), $link, $ns);
			}
		}
		// output cell
		if ($s) {
			// output cell
			$rs .= $s;
			if ($this->font_spacing != 0) {
				// reset font spacing mode
				$rs .= ' BT 0 Tc ET';
			}
			if ($this->font_stretching != 100) {
				// reset font stretching mode
				$rs .= ' BT 100 Tz ET';
			}
		}
		// reset word spacing
		if (!$this->isUnicodeFont() AND ($align == 'J')) {
			$rs .= ' BT 0 Tw ET';
		}
		// reset stretching and spacing
		$this->font_stretching = $prev_font_stretching;
		$this->font_spacing = $prev_font_spacing;
		$this->lasth = $h;
		if ($ln > 0) {
			//Go to the beginning of the next line
			$this->y = $y + $h + $this->cell_margin['B'];
			if ($ln == 1) {
				if ($this->rtl) {
					$this->x = $this->w - $this->rMargin;
				} else {
					$this->x = $this->lMargin;
				}
			}
		} else {
			// go left or right by case
			if ($this->rtl) {
				$this->x = $x - $w - $this->cell_margin['L'];
			} else {
				$this->x = $x + $w + $this->cell_margin['R'];
			}
		}
		$gstyles = ''.$this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor.' '.$this->FillColor."\n";
		$rs = $gstyles.$rs;
		$this->cell_padding = $prev_cell_padding;
		$this->cell_margin = $prev_cell_margin;
		return $rs;
	}

	/**
	 * Replace a char if is defined on the current font.
	 * @param $oldchar (int) Integer code (unicode) of the character to replace.
	 * @param $newchar (int) Integer code (unicode) of the new character.
	 * @return int the replaced char or the old char in case the new char i not defined
	 * @protected
	 * @since 5.9.167 (2012-06-22)
	 */
	protected function replaceChar($oldchar, $newchar) {
		if ($this->isCharDefined($newchar)) {
			// add the new char on the subset list
			$this->CurrentFont['subsetchars'][$newchar] = true;
			// return the new character
			return $newchar;
		}
		// return the old char
		return $oldchar;
	}

	/**
	 * Returns the code to draw the cell border
	 * @param $x (float) X coordinate.
	 * @param $y (float) Y coordinate.
	 * @param $w (float) Cell width.
	 * @param $h (float) Cell height.
	 * @param $brd (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @return string containing cell border code
	 * @protected
	 * @see SetLineStyle()
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCellBorder($x, $y, $w, $h, $brd) {
		$s = ''; // string to be returned
		if (empty($brd)) {
			return $s;
		}
		if ($brd == 1) {
			$brd = array('LRTB' => true);
		}
		// calculate coordinates for border
		$k = $this->k;
		if ($this->rtl) {
			$xeL = ($x - $w) * $k;
			$xeR = $x * $k;
		} else {
			$xeL = $x * $k;
			$xeR = ($x + $w) * $k;
		}
		$yeL = (($this->h - ($y + $h)) * $k);
		$yeT = (($this->h - $y) * $k);
		$xeT = $xeL;
		$xeB = $xeR;
		$yeR = $yeT;
		$yeB = $yeL;
		if (is_string($brd)) {
			// convert string to array
			$slen = strlen($brd);
			$newbrd = array();
			for ($i = 0; $i < $slen; ++$i) {
				$newbrd[$brd[$i]] = array('cap' => 'square', 'join' => 'miter');
			}
			$brd = $newbrd;
		}
		if (isset($brd['mode'])) {
			$mode = $brd['mode'];
			unset($brd['mode']);
		} else {
			$mode = 'normal';
		}
		foreach ($brd as $border => $style) {
			if (is_array($style) AND !empty($style)) {
				// apply border style
				$prev_style = $this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor.' ';
				$s .= $this->SetLineStyle($style, true)."\n";
			}
			switch ($mode) {
				case 'ext': {
					$off = (($this->LineWidth / 2) * $k);
					$xL = $xeL - $off;
					$xR = $xeR + $off;
					$yT = $yeT + $off;
					$yL = $yeL - $off;
					$xT = $xL;
					$xB = $xR;
					$yR = $yT;
					$yB = $yL;
					$w += $this->LineWidth;
					$h += $this->LineWidth;
					break;
				}
				case 'int': {
					$off = ($this->LineWidth / 2) * $k;
					$xL = $xeL + $off;
					$xR = $xeR - $off;
					$yT = $yeT - $off;
					$yL = $yeL + $off;
					$xT = $xL;
					$xB = $xR;
					$yR = $yT;
					$yB = $yL;
					$w -= $this->LineWidth;
					$h -= $this->LineWidth;
					break;
				}
				case 'normal':
				default: {
					$xL = $xeL;
					$xT = $xeT;
					$xB = $xeB;
					$xR = $xeR;
					$yL = $yeL;
					$yT = $yeT;
					$yB = $yeB;
					$yR = $yeR;
					break;
				}
			}
			// draw borders by case
			if (strlen($border) == 4) {
				$s .= sprintf('%F %F %F %F re S ', $xT, $yT, ($w * $k), (-$h * $k));
			} elseif (strlen($border) == 3) {
				if (strpos($border,'B') === false) { // LTR
					$s .= sprintf('%F %F m ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= 'S ';
				} elseif (strpos($border,'L') === false) { // TRB
					$s .= sprintf('%F %F m ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= 'S ';
				} elseif (strpos($border,'T') === false) { // RBL
					$s .= sprintf('%F %F m ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= 'S ';
				} elseif (strpos($border,'R') === false) { // BLT
					$s .= sprintf('%F %F m ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= 'S ';
				}
			} elseif (strlen($border) == 2) {
				if ((strpos($border,'L') !== false) AND (strpos($border,'T') !== false)) { // LT
					$s .= sprintf('%F %F m ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= 'S ';
				} elseif ((strpos($border,'T') !== false) AND (strpos($border,'R') !== false)) { // TR
					$s .= sprintf('%F %F m ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= 'S ';
				} elseif ((strpos($border,'R') !== false) AND (strpos($border,'B') !== false)) { // RB
					$s .= sprintf('%F %F m ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= 'S ';
				} elseif ((strpos($border,'B') !== false) AND (strpos($border,'L') !== false)) { // BL
					$s .= sprintf('%F %F m ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= 'S ';
				} elseif ((strpos($border,'L') !== false) AND (strpos($border,'R') !== false)) { // LR
					$s .= sprintf('%F %F m ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= 'S ';
					$s .= sprintf('%F %F m ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= 'S ';
				} elseif ((strpos($border,'T') !== false) AND (strpos($border,'B') !== false)) { // TB
					$s .= sprintf('%F %F m ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= 'S ';
					$s .= sprintf('%F %F m ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= 'S ';
				}
			} else { // strlen($border) == 1
				if (strpos($border,'L') !== false) { // L
					$s .= sprintf('%F %F m ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= 'S ';
				} elseif (strpos($border,'T') !== false) { // T
					$s .= sprintf('%F %F m ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= 'S ';
				} elseif (strpos($border,'R') !== false) { // R
					$s .= sprintf('%F %F m ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= 'S ';
				} elseif (strpos($border,'B') !== false) { // B
					$s .= sprintf('%F %F m ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= 'S ';
				}
			}
			if (is_array($style) AND !empty($style)) {
				// reset border style to previous value
				$s .= "\n".$this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor."\n";
			}
		}
		return $s;
	}

	/**
	 * This method allows printing text with line breaks.
	 * They can be automatic (as soon as the text reaches the right border of the cell) or explicit (via the \n character). As many cells as necessary are output, one below the other.<br />
	 * Text can be aligned, centered or justified. The cell block can be framed and the background painted.
	 * @param $w (float) Width of cells. If 0, they extend up to the right margin of the page.
	 * @param $h (float) Cell minimum height. The cell extends automatically if needed.
	 * @param $txt (string) String to print
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $align (string) Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align</li><li>C: center</li><li>R: right align</li><li>J: justification (default value when $ishtml=false)</li></ul>
	 * @param $fill (boolean) Indicates if the cell background must be painted (true) or transparent (false).
	 * @param $ln (int) Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right</li><li>1: to the beginning of the next line [DEFAULT]</li><li>2: below</li></ul>
	 * @param $x (float) x position in user units
	 * @param $y (float) y position in user units
	 * @param $reseth (boolean) if true reset the last cell height (default true).
	 * @param $stretch (int) font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param $ishtml (boolean) INTERNAL USE ONLY -- set to true if $txt is HTML content (default = false). Never set this parameter to true, use instead writeHTMLCell() or writeHTML() methods.
	 * @param $autopadding (boolean) if true, uses internal padding and automatically adjust it to account for line width.
	 * @param $maxh (float) maximum height. It should be >= $h and less then remaining space to the bottom of the page, or 0 for disable this feature. This feature works only when $ishtml=false.
	 * @param $valign (string) Vertical alignment of text (requires $maxh = $h > 0). Possible values are:<ul><li>T: TOP</li><li>M: middle</li><li>B: bottom</li></ul>. This feature works only when $ishtml=false and the cell must fit in a single page.
	 * @param $fitcell (boolean) if true attempt to fit all the text within the cell by reducing the font size (do not work in HTML mode).
	 * @return int Return the number of cells or 1 for html mode.
	 * @public
	 * @since 1.3
	 * @see SetFont(), SetDrawColor(), SetFillColor(), SetTextColor(), SetLineWidth(), Cell(), Write(), SetAutoPageBreak()
	 */
	public function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false) {
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		// adjust internal padding
		$this->adjustCellPadding($border);
		$mc_padding = $this->cell_padding;
		$mc_margin = $this->cell_margin;
		$this->cell_padding['T'] = 0;
		$this->cell_padding['B'] = 0;
		$this->setCellMargins(0, 0, 0, 0);
		if (TCPDF_STATIC::empty_string($this->lasth) OR $reseth) {
			// reset row height
			$this->resetLastH();
		}
		if (!TCPDF_STATIC::empty_string($y)) {
			$this->SetY($y);
		} else {
			$y = $this->GetY();
		}
		$resth = 0;
		if (($h > 0) AND $this->inPageBody() AND (($y + $h + $mc_margin['T'] + $mc_margin['B']) > $this->PageBreakTrigger)) {
			// spit cell in more pages/columns
			$newh = ($this->PageBreakTrigger - $y);
			$resth = ($h - $newh); // cell to be printed on the next page/column
			$h = $newh;
		}
		// get current page number
		$startpage = $this->page;
		// get current column
		$startcolumn = $this->current_column;
		if (!TCPDF_STATIC::empty_string($x)) {
			$this->SetX($x);
		} else {
			$x = $this->GetX();
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions(0, $x, $y);
		// apply margins
		$oy = $y + $mc_margin['T'];
		if ($this->rtl) {
			$ox = ($this->w - $x - $mc_margin['R']);
		} else {
			$ox = ($x + $mc_margin['L']);
		}
		$this->x = $ox;
		$this->y = $oy;
		// set width
		if (TCPDF_STATIC::empty_string($w) OR ($w <= 0)) {
			if ($this->rtl) {
				$w = ($this->x - $this->lMargin - $mc_margin['L']);
			} else {
				$w = ($this->w - $this->x - $this->rMargin - $mc_margin['R']);
			}
		}
		// store original margin values
		$lMargin = $this->lMargin;
		$rMargin = $this->rMargin;
		if ($this->rtl) {
			$this->rMargin = ($this->w - $this->x);
			$this->lMargin = ($this->x - $w);
		} else {
			$this->lMargin = ($this->x);
			$this->rMargin = ($this->w - $this->x - $w);
		}
		$this->clMargin = $this->lMargin;
		$this->crMargin = $this->rMargin;
		if ($autopadding) {
			// add top padding
			$this->y += $mc_padding['T'];
		}
		if ($ishtml) { // ******* Write HTML text
			$this->writeHTML($txt, true, false, $reseth, true, $align);
			$nl = 1;
		} else { // ******* Write simple text
			$prev_FontSizePt = $this->FontSizePt;
			// vertical alignment
			if ($maxh > 0) {
				// get text height
				$text_height = $this->getStringHeight($w, $txt, $reseth, $autopadding, $mc_padding, $border);
				if ($fitcell) {
					// try to reduce font size to fit text on cell (use a quick search algorithm)
					$fmin = 1;
					$fmax = $this->FontSizePt;
					$prev_text_height = $text_height;
					$maxit = 100; // max number of iterations
					while ($maxit > 0) {
						$fmid = (($fmax + $fmin) / 2);
						$this->SetFontSize($fmid, false);
						$this->resetLastH();
						$text_height = $this->getStringHeight($w, $txt, $reseth, $autopadding, $mc_padding, $border);
						if (($text_height == $maxh) OR (($text_height < $maxh) AND ($fmin >= ($fmax - 0.01)))) {
							break;
						} elseif ($text_height < $maxh) {
							$fmin = $fmid;
						} else {
							$fmax = $fmid;
						}
						--$maxit;
					}
					$this->SetFontSize($this->FontSizePt);
				}
				if ($text_height < $maxh) {
					if ($valign == 'M') {
						// text vertically centered
						$this->y += (($maxh - $text_height) / 2);
					} elseif ($valign == 'B') {
						// text vertically aligned on bottom
						$this->y += ($maxh - $text_height);
					}
				}
			}
			$nl = $this->Write($this->lasth, $txt, '', 0, $align, true, $stretch, false, true, $maxh, 0, $mc_margin);
			if ($fitcell) {
				// restore font size
				$this->SetFontSize($prev_FontSizePt);
			}
		}
		if ($autopadding) {
			// add bottom padding
			$this->y += $mc_padding['B'];
		}
		// Get end-of-text Y position
		$currentY = $this->y;
		// get latest page number
		$endpage = $this->page;
		if ($resth > 0) {
			$skip = ($endpage - $startpage);
			$tmpresth = $resth;
			while ($tmpresth > 0) {
				if ($skip <= 0) {
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$this->checkPageBreak($this->PageBreakTrigger + 1);
				}
				if ($this->num_columns > 1) {
					$tmpresth -= ($this->h - $this->y - $this->bMargin);
				} else {
					$tmpresth -= ($this->h - $this->tMargin - $this->bMargin);
				}
				--$skip;
			}
			$currentY = $this->y;
			$endpage = $this->page;
		}
		// get latest column
		$endcolumn = $this->current_column;
		if ($this->num_columns == 0) {
			$this->num_columns = 1;
		}
		// disable page regions check
		$check_page_regions = $this->check_page_regions;
		$this->check_page_regions = false;
		// get border modes
		$border_start = TCPDF_STATIC::getBorderMode($border, $position='start', $this->opencell);
		$border_end = TCPDF_STATIC::getBorderMode($border, $position='end', $this->opencell);
		$border_middle = TCPDF_STATIC::getBorderMode($border, $position='middle', $this->opencell);
		// design borders around HTML cells.
		for ($page = $startpage; $page <= $endpage; ++$page) { // for each page
			$ccode = '';
			$this->setPage($page);
			if ($this->num_columns < 2) {
				// single-column mode
				$this->SetX($x);
				$this->y = $this->tMargin;
			}
			// account for margin changes
			if ($page > $startpage) {
				if (($this->rtl) AND ($this->pagedim[$page]['orm'] != $this->pagedim[$startpage]['orm'])) {
					$this->x -= ($this->pagedim[$page]['orm'] - $this->pagedim[$startpage]['orm']);
				} elseif ((!$this->rtl) AND ($this->pagedim[$page]['olm'] != $this->pagedim[$startpage]['olm'])) {
					$this->x += ($this->pagedim[$page]['olm'] - $this->pagedim[$startpage]['olm']);
				}
			}
			if ($startpage == $endpage) {
				// single page
				for ($column = $startcolumn; $column <= $endcolumn; ++$column) { // for each column
					$this->selectColumn($column);
					if ($this->rtl) {
						$this->x -= $mc_margin['R'];
					} else {
						$this->x += $mc_margin['L'];
					}
					if ($startcolumn == $endcolumn) { // single column
						$cborder = $border;
						$h = max($h, ($currentY - $oy));
						$this->y = $oy;
					} elseif ($column == $startcolumn) { // first column
						$cborder = $border_start;
						$this->y = $oy;
						$h = $this->h - $this->y - $this->bMargin;
					} elseif ($column == $endcolumn) { // end column
						$cborder = $border_end;
						$h = $currentY - $this->y;
						if ($resth > $h) {
							$h = $resth;
						}
					} else { // middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
						$resth -= $h;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} elseif ($page == $startpage) { // first page
				for ($column = $startcolumn; $column < $this->num_columns; ++$column) { // for each column
					$this->selectColumn($column);
					if ($this->rtl) {
						$this->x -= $mc_margin['R'];
					} else {
						$this->x += $mc_margin['L'];
					}
					if ($column == $startcolumn) { // first column
						$cborder = $border_start;
						$this->y = $oy;
						$h = $this->h - $this->y - $this->bMargin;
					} else { // middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
						$resth -= $h;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} elseif ($page == $endpage) { // last page
				for ($column = 0; $column <= $endcolumn; ++$column) { // for each column
					$this->selectColumn($column);
					if ($this->rtl) {
						$this->x -= $mc_margin['R'];
					} else {
						$this->x += $mc_margin['L'];
					}
					if ($column == $endcolumn) {
						// end column
						$cborder = $border_end;
						$h = $currentY - $this->y;
						if ($resth > $h) {
							$h = $resth;
						}
					} else {
						// middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
						$resth -= $h;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} else { // middle page
				for ($column = 0; $column < $this->num_columns; ++$column) { // for each column
					$this->selectColumn($column);
					if ($this->rtl) {
						$this->x -= $mc_margin['R'];
					} else {
						$this->x += $mc_margin['L'];
					}
					$cborder = $border_middle;
					$h = $this->h - $this->y - $this->bMargin;
					$resth -= $h;
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			}
			if ($cborder OR $fill) {
				$offsetlen = strlen($ccode);
				// draw border and fill
				if ($this->inxobj) {
					// we are inside an XObject template
					if (end($this->xobjects[$this->xobjid]['transfmrk']) !== false) {
						$pagemarkkey = key($this->xobjects[$this->xobjid]['transfmrk']);
						$pagemark = $this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey];
						$this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey] += $offsetlen;
					} else {
						$pagemark = $this->xobjects[$this->xobjid]['intmrk'];
						$this->xobjects[$this->xobjid]['intmrk'] += $offsetlen;
					}
					$pagebuff = $this->xobjects[$this->xobjid]['outdata'];
					$pstart = substr($pagebuff, 0, $pagemark);
					$pend = substr($pagebuff, $pagemark);
					$this->xobjects[$this->xobjid]['outdata'] = $pstart.$ccode.$pend;
				} else {
					if (end($this->transfmrk[$this->page]) !== false) {
						$pagemarkkey = key($this->transfmrk[$this->page]);
						$pagemark = $this->transfmrk[$this->page][$pagemarkkey];
						$this->transfmrk[$this->page][$pagemarkkey] += $offsetlen;
					} elseif ($this->InFooter) {
						$pagemark = $this->footerpos[$this->page];
						$this->footerpos[$this->page] += $offsetlen;
					} else {
						$pagemark = $this->intmrk[$this->page];
						$this->intmrk[$this->page] += $offsetlen;
					}
					$pagebuff = $this->getPageBuffer($this->page);
					$pstart = substr($pagebuff, 0, $pagemark);
					$pend = substr($pagebuff, $pagemark);
					$this->setPageBuffer($this->page, $pstart.$ccode.$pend);
				}
			}
		} // end for each page
		// restore page regions check
		$this->check_page_regions = $check_page_regions;
		// Get end-of-cell Y position
		$currentY = $this->GetY();
		// restore previous values
		if ($this->num_columns > 1) {
			$this->selectColumn();
		} else {
			// restore original margins
			$this->lMargin = $lMargin;
			$this->rMargin = $rMargin;
			if ($this->page > $startpage) {
				// check for margin variations between pages (i.e. booklet mode)
				$dl = ($this->pagedim[$this->page]['olm'] - $this->pagedim[$startpage]['olm']);
				$dr = ($this->pagedim[$this->page]['orm'] - $this->pagedim[$startpage]['orm']);
				if (($dl != 0) OR ($dr != 0)) {
					$this->lMargin += $dl;
					$this->rMargin += $dr;
				}
			}
		}
		if ($ln > 0) {
			//Go to the beginning of the next line
			$this->SetY($currentY + $mc_margin['B']);
			if ($ln == 2) {
				$this->SetX($x + $w + $mc_margin['L'] + $mc_margin['R']);
			}
		} else {
			// go left or right by case
			$this->setPage($startpage);
			$this->y = $y;
			$this->SetX($x + $w + $mc_margin['L'] + $mc_margin['R']);
		}
		$this->setContentMark();
		$this->cell_padding = $prev_cell_padding;
		$this->cell_margin = $prev_cell_margin;
		$this->clMargin = $this->lMargin;
		$this->crMargin = $this->rMargin;
		return $nl;
	}

	/**
	 * This method return the estimated number of lines for print a simple text string using Multicell() method.
	 * @param $txt (string) String for calculating his height
	 * @param $w (float) Width of cells. If 0, they extend up to the right margin of the page.
	 * @param $reseth (boolean) if true reset the last cell height (default false).
	 * @param $autopadding (boolean) if true, uses internal padding and automatically adjust it to account for line width (default true).
	 * @param $cellpadding (float) Internal cell padding, if empty uses default cell padding.
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @return float Return the minimal height needed for multicell method for printing the $txt param.
	 * @author Alexander Escalona Fern\E1ndez, Nicola Asuni
	 * @public
	 * @since 4.5.011
	 */
	public function getNumLines($txt, $w=0, $reseth=false, $autopadding=true, $cellpadding='', $border=0) {
		if ($txt === '') {
			// empty string
			return 1;
		}
		// adjust internal padding
		$prev_cell_padding = $this->cell_padding;
		$prev_lasth = $this->lasth;
		if (is_array($cellpadding)) {
			$this->cell_padding = $cellpadding;
		}
		$this->adjustCellPadding($border);
		if (TCPDF_STATIC::empty_string($w) OR ($w <= 0)) {
			if ($this->rtl) {
				$w = $this->x - $this->lMargin;
			} else {
				$w = $this->w - $this->rMargin - $this->x;
			}
		}
		$wmax = $w - $this->cell_padding['L'] - $this->cell_padding['R'];
		if ($reseth) {
			// reset row height
			$this->resetLastH();
		}
		$lines = 1;
		$sum = 0;
		$chars = TCPDF_FONTS::utf8Bidi(TCPDF_FONTS::UTF8StringToArray($txt, $this->isunicode, $this->CurrentFont), $txt, $this->tmprtl, $this->isunicode, $this->CurrentFont);
		$charsWidth = $this->GetArrStringWidth($chars, '', '', 0, true);
		$length = count($chars);
		$lastSeparator = -1;
		for ($i = 0; $i < $length; ++$i) {
			$charWidth = $charsWidth[$i];
			if (preg_match($this->re_spaces, TCPDF_FONTS::unichr($chars[$i], $this->isunicode))) {
				$lastSeparator = $i;
			}
			if ((($sum + $charWidth) > $wmax) OR ($chars[$i] == 10)) {
				++$lines;
				if ($chars[$i] == 10) {
					$lastSeparator = -1;
					$sum = 0;
				} elseif ($lastSeparator != -1) {
					$i = $lastSeparator;
					$lastSeparator = -1;
					$sum = 0;
				} else {
					$sum = $charWidth;
				}
			} else {
				$sum += $charWidth;
			}
		}
		if ($chars[($length - 1)] == 10) {
			--$lines;
		}
		$this->cell_padding = $prev_cell_padding;
		$this->lasth = $prev_lasth;
		return $lines;
	}

	/**
	 * This method return the estimated height needed for printing a simple text string using the Multicell() method.
	 * Generally, if you want to know the exact height for a block of content you can use the following alternative technique:
	 * @pre
	 *  // store current object
	 *  $pdf->startTransaction();
	 *  // store starting values
	 *  $start_y = $pdf->GetY();
	 *  $start_page = $pdf->getPage();
	 *  // call your printing functions with your parameters
	 *  // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 *  $pdf->MultiCell($w=0, $h=0, $txt, $border=1, $align='L', $fill=false, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0);
	 *  // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 *  // get the new Y
	 *  $end_y = $pdf->GetY();
	 *  $end_page = $pdf->getPage();
	 *  // calculate height
	 *  $height = 0;
	 *  if ($end_page == $start_page) {
	 *  	$height = $end_y - $start_y;
	 *  } else {
	 *  	for ($page=$start_page; $page <= $end_page; ++$page) {
	 *  		$this->setPage($page);
	 *  		if ($page == $start_page) {
	 *  			// first page
	 *  			$height = $this->h - $start_y - $this->bMargin;
	 *  		} elseif ($page == $end_page) {
	 *  			// last page
	 *  			$height = $end_y - $this->tMargin;
	 *  		} else {
	 *  			$height = $this->h - $this->tMargin - $this->bMargin;
	 *  		}
	 *  	}
	 *  }
	 *  // restore previous object
	 *  $pdf = $pdf->rollbackTransaction();
	 *
	 * @param $w (float) Width of cells. If 0, they extend up to the right margin of the page.
	 * @param $txt (string) String for calculating his height
	 * @param $reseth (boolean) if true reset the last cell height (default false).
	 * @param $autopadding (boolean) if true, uses internal padding and automatically adjust it to account for line width (default true).
	 * @param $cellpadding (float) Internal cell padding, if empty uses default cell padding.
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @return float Return the minimal height needed for multicell method for printing the $txt param.
	 * @author Nicola Asuni, Alexander Escalona Fern\E1ndez
	 * @public
	 */
	public function getStringHeight($w, $txt, $reseth=false, $autopadding=true, $cellpadding='', $border=0) {
		// adjust internal padding
		$prev_cell_padding = $this->cell_padding;
		$prev_lasth = $this->lasth;
		if (is_array($cellpadding)) {
			$this->cell_padding = $cellpadding;
		}
		$this->adjustCellPadding($border);
		$lines = $this->getNumLines($txt, $w, $reseth, $autopadding, $cellpadding, $border);
		$height = $lines * ($this->FontSize * $this->cell_height_ratio);
		if ($autopadding) {
			// add top and bottom padding
			$height += ($this->cell_padding['T'] + $this->cell_padding['B']);
		}
		$this->cell_padding = $prev_cell_padding;
		$this->lasth = $prev_lasth;
		return $height;
	}

	/**
	 * This method prints text from the current position.<br />
	 * @param $h (float) Line height
	 * @param $txt (string) String to print
	 * @param $link (mixed) URL or identifier returned by AddLink()
	 * @param $fill (boolean) Indicates if the cell background must be painted (true) or transparent (false).
	 * @param $align (string) Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align (default value)</li><li>C: center</li><li>R: right align</li><li>J: justify</li></ul>
	 * @param $ln (boolean) if true set cursor at the bottom of the line, otherwise set cursor at the top of the line.
	 * @param $stretch (int) font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param $firstline (boolean) if true prints only the first line and return the remaining string.
	 * @param $firstblock (boolean) if true the string is the starting of a line.
	 * @param $maxh (float) maximum height. It should be >= $h and less then remaining space to the bottom of the page, or 0 for disable this feature.
	 * @param $wadj (float) first line width will be reduced by this amount (used in HTML mode).
	 * @param $margin (array) margin array of the parent container
	 * @return mixed Return the number of cells or the remaining string if $firstline = true.
	 * @public
	 * @since 1.5
	 */
	public function Write($h, $txt, $link='', $fill=false, $align='', $ln=false, $stretch=0, $firstline=false, $firstblock=false, $maxh=0, $wadj=0, $margin='') {
		// check page for no-write regions and adapt page margins if necessary
		list($this->x, $this->y) = $this->checkPageRegions($h, $this->x, $this->y);
		if (strlen($txt) == 0) {
			// fix empty text
			$txt = ' ';
		}
		if ($margin === '') {
			// set default margins
			$margin = $this->cell_margin;
		}
		// remove carriage returns
		$s = str_replace("\r", '', $txt);
		// check if string contains arabic text
		if (preg_match(TCPDF_FONT_DATA::$uni_RE_PATTERN_ARABIC, $s)) {
			$arabic = true;
		} else {
			$arabic = false;
		}
		// check if string contains RTL text
		if ($arabic OR ($this->tmprtl == 'R') OR preg_match(TCPDF_FONT_DATA::$uni_RE_PATTERN_RTL, $s)) {
			$rtlmode = true;
		} else {
			$rtlmode = false;
		}
		// get a char width
		$chrwidth = $this->GetCharWidth(46); // dot character
		// get array of unicode values
		$chars = TCPDF_FONTS::UTF8StringToArray($s, $this->isunicode, $this->CurrentFont);
		// calculate maximum width for a single character on string
		$chrw = $this->GetArrStringWidth($chars, '', '', 0, true);
		array_walk($chrw, array($this, 'getRawCharWidth'));
		$maxchwidth = max($chrw);
		// get array of chars
		$uchars = TCPDF_FONTS::UTF8ArrayToUniArray($chars, $this->isunicode);
		// get the number of characters
		$nb = count($chars);
		// replacement for SHY character (minus symbol)
		$shy_replacement = 45;
		$shy_replacement_char = TCPDF_FONTS::unichr($shy_replacement, $this->isunicode);
		// widht for SHY replacement
		$shy_replacement_width = $this->GetCharWidth($shy_replacement);
		// max Y
		$maxy = $this->y + $maxh - $h - $this->cell_padding['T'] - $this->cell_padding['B'];
		// page width
		$pw = $w = $this->w - $this->lMargin - $this->rMargin;
		// calculate remaining line width ($w)
		if ($this->rtl) {
			$w = $this->x - $this->lMargin;
		} else {
			$w = $this->w - $this->rMargin - $this->x;
		}
		// max column width
		$wmax = ($w - $wadj);
		if (!$firstline) {
			$wmax -= ($this->cell_padding['L'] + $this->cell_padding['R']);
		}
		if ((!$firstline) AND (($chrwidth > $wmax) OR ($maxchwidth > $wmax))) {
			// the maximum width character do not fit on column
			return '';
		}
		// minimum row height
		$row_height = max($h, $this->FontSize * $this->cell_height_ratio);
		$start_page = $this->page;
		$i = 0; // character position
		$j = 0; // current starting position
		$sep = -1; // position of the last blank space
		$shy = false; // true if the last blank is a soft hypen (SHY)
		$l = 0; // current string length
		$nl = 0; //number of lines
		$linebreak = false;
		$pc = 0; // previous character
		// for each character
		while ($i < $nb) {
			if (($maxh > 0) AND ($this->y >= $maxy) ) {
				break;
			}
			//Get the current character
			$c = $chars[$i];
			if ($c == 10) { // 10 = "\n" = new line
				//Explicit line break
				if ($align == 'J') {
					if ($this->rtl) {
						$talign = 'R';
					} else {
						$talign = 'L';
					}
				} else {
					$talign = $align;
				}
				$tmpstr = TCPDF_FONTS::UniArrSubString($uchars, $j, $i);
				if ($firstline) {
					$startx = $this->x;
					$tmparr = array_slice($chars, $j, ($i - $j));
					if ($rtlmode) {
						$tmparr = TCPDF_FONTS::utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
					}
					$linew = $this->GetArrStringWidth($tmparr);
					unset($tmparr);
					if ($this->rtl) {
						$this->endlinex = $startx - $linew;
					} else {
						$this->endlinex = $startx + $linew;
					}
					$w = $linew;
					$tmpcellpadding = $this->cell_padding;
					if ($maxh == 0) {
						$this->SetCellPadding(0);
					}
				}
				if ($firstblock AND $this->isRTLTextDir()) {
					$tmpstr = $this->stringRightTrim($tmpstr);
				}
				// Skip newlines at the begining of a page or column
				if (!empty($tmpstr) OR ($this->y < ($this->PageBreakTrigger - $row_height))) {
					$this->Cell($w, $h, $tmpstr, 0, 1, $talign, $fill, $link, $stretch);
				}
				unset($tmpstr);
				if ($firstline) {
					$this->cell_padding = $tmpcellpadding;
					return (TCPDF_FONTS::UniArrSubString($uchars, $i));
				}
				++$nl;
				$j = $i + 1;
				$l = 0;
				$sep = -1;
				$shy = false;
				// account for margin changes
				if ((($this->y + $this->lasth) > $this->PageBreakTrigger) AND ($this->inPageBody())) {
					$this->AcceptPageBreak();
					if ($this->rtl) {
						$this->x -= $margin['R'];
					} else {
						$this->x += $margin['L'];
					}
					$this->lMargin += $margin['L'];
					$this->rMargin += $margin['R'];
				}
				$w = $this->getRemainingWidth();
				$wmax = ($w - $this->cell_padding['L'] - $this->cell_padding['R']);
			} else {
				// 160 is the non-breaking space.
				// 173 is SHY (Soft Hypen).
				// \p{Z} or \p{Separator}: any kind of Unicode whitespace or invisible separator.
				// \p{Lo} or \p{Other_Letter}: a Unicode letter or ideograph that does not have lowercase and uppercase variants.
				// \p{Lo} is needed because Chinese characters are packed next to each other without spaces in between.
				if (($c != 160)
					AND (($c == 173)
						OR preg_match($this->re_spaces, TCPDF_FONTS::unichr($c, $this->isunicode))
						OR (($c == 45)
							AND ($i < ($nb - 1))
							AND @preg_match('/[\p{L}]/'.$this->re_space['m'], TCPDF_FONTS::unichr($pc, $this->isunicode))
							AND @preg_match('/[\p{L}]/'.$this->re_space['m'], TCPDF_FONTS::unichr($chars[($i + 1)], $this->isunicode))
						)
					)
				) {
					// update last blank space position
					$sep = $i;
					// check if is a SHY
					if (($c == 173) OR ($c == 45)) {
						$shy = true;
						if ($pc == 45) {
							$tmp_shy_replacement_width = 0;
							$tmp_shy_replacement_char = '';
						} else {
							$tmp_shy_replacement_width = $shy_replacement_width;
							$tmp_shy_replacement_char = $shy_replacement_char;
						}
					} else {
						$shy = false;
					}
				}
				// update string length
				if ($this->isUnicodeFont() AND ($arabic)) {
					// with bidirectional algorithm some chars may be changed affecting the line length
					// *** very slow ***
					$l = $this->GetArrStringWidth(TCPDF_FONTS::utf8Bidi(array_slice($chars, $j, ($i - $j)), '', $this->tmprtl, $this->isunicode, $this->CurrentFont));
				} else {
					$l += $this->GetCharWidth($c);
				}
				if (($l > $wmax) OR (($c == 173) AND (($l + $tmp_shy_replacement_width) > $wmax)) ) {
					// we have reached the end of column
					if ($sep == -1) {
						// check if the line was already started
						if (($this->rtl AND ($this->x <= ($this->w - $this->rMargin - $this->cell_padding['R'] - $margin['R'] - $chrwidth)))
							OR ((!$this->rtl) AND ($this->x >= ($this->lMargin + $this->cell_padding['L'] + $margin['L'] + $chrwidth)))) {
							// print a void cell and go to next line
							$this->Cell($w, $h, '', 0, 1);
							$linebreak = true;
							if ($firstline) {
								return (TCPDF_FONTS::UniArrSubString($uchars, $j));
							}
						} else {
							// truncate the word because do not fit on column
							$tmpstr = TCPDF_FONTS::UniArrSubString($uchars, $j, $i);
							if ($firstline) {
								$startx = $this->x;
								$tmparr = array_slice($chars, $j, ($i - $j));
								if ($rtlmode) {
									$tmparr = TCPDF_FONTS::utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
								}
								$linew = $this->GetArrStringWidth($tmparr);
								unset($tmparr);
								if ($this->rtl) {
									$this->endlinex = $startx - $linew;
								} else {
									$this->endlinex = $startx + $linew;
								}
								$w = $linew;
								$tmpcellpadding = $this->cell_padding;
								if ($maxh == 0) {
									$this->SetCellPadding(0);
								}
							}
							if ($firstblock AND $this->isRTLTextDir()) {
								$tmpstr = $this->stringRightTrim($tmpstr);
							}
							$this->Cell($w, $h, $tmpstr, 0, 1, $align, $fill, $link, $stretch);
							unset($tmpstr);
							if ($firstline) {
								$this->cell_padding = $tmpcellpadding;
								return (TCPDF_FONTS::UniArrSubString($uchars, $i));
							}
							$j = $i;
							--$i;
						}
					} else {
						// word wrapping
						if ($this->rtl AND (!$firstblock) AND ($sep < $i)) {
							$endspace = 1;
						} else {
							$endspace = 0;
						}
						// check the length of the next string
						$strrest = TCPDF_FONTS::UniArrSubString($uchars, ($sep + $endspace));
						$nextstr = TCPDF_STATIC::pregSplit('/'.$this->re_space['p'].'/', $this->re_space['m'], $this->stringTrim($strrest));
						if (isset($nextstr[0]) AND ($this->GetStringWidth($nextstr[0]) > $pw)) {
							// truncate the word because do not fit on a full page width
							$tmpstr = TCPDF_FONTS::UniArrSubString($uchars, $j, $i);
							if ($firstline) {
								$startx = $this->x;
								$tmparr = array_slice($chars, $j, ($i - $j));
								if ($rtlmode) {
									$tmparr = TCPDF_FONTS::utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
								}
								$linew = $this->GetArrStringWidth($tmparr);
								unset($tmparr);
								if ($this->rtl) {
									$this->endlinex = ($startx - $linew);
								} else {
									$this->endlinex = ($startx + $linew);
								}
								$w = $linew;
								$tmpcellpadding = $this->cell_padding;
								if ($maxh == 0) {
									$this->SetCellPadding(0);
								}
							}
							if ($firstblock AND $this->isRTLTextDir()) {
								$tmpstr = $this->stringRightTrim($tmpstr);
							}
							$this->Cell($w, $h, $tmpstr, 0, 1, $align, $fill, $link, $stretch);
							unset($tmpstr);
							if ($firstline) {
								$this->cell_padding = $tmpcellpadding;
								return (TCPDF_FONTS::UniArrSubString($uchars, $i));
							}
							$j = $i;
							--$i;
						} else {
							// word wrapping
							if ($shy) {
								// add hypen (minus symbol) at the end of the line
								$shy_width = $tmp_shy_replacement_width;
								if ($this->rtl) {
									$shy_char_left = $tmp_shy_replacement_char;
									$shy_char_right = '';
								} else {
									$shy_char_left = '';
									$shy_char_right = $tmp_shy_replacement_char;
								}
							} else {
								$shy_width = 0;
								$shy_char_left = '';
								$shy_char_right = '';
							}
							$tmpstr = TCPDF_FONTS::UniArrSubString($uchars, $j, ($sep + $endspace));
							if ($firstline) {
								$startx = $this->x;
								$tmparr = array_slice($chars, $j, (($sep + $endspace) - $j));
								if ($rtlmode) {
									$tmparr = TCPDF_FONTS::utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
								}
								$linew = $this->GetArrStringWidth($tmparr);
								unset($tmparr);
								if ($this->rtl) {
									$this->endlinex = $startx - $linew - $shy_width;
								} else {
									$this->endlinex = $startx + $linew + $shy_width;
								}
								$w = $linew;
								$tmpcellpadding = $this->cell_padding;
								if ($maxh == 0) {
									$this->SetCellPadding(0);
								}
							}
							// print the line
							if ($firstblock AND $this->isRTLTextDir()) {
								$tmpstr = $this->stringRightTrim($tmpstr);
							}
							$this->Cell($w, $h, $shy_char_left.$tmpstr.$shy_char_right, 0, 1, $align, $fill, $link, $stretch);
							unset($tmpstr);
							if ($firstline) {
								if ($chars[$sep] == 45) {
									$endspace += 1;
								}
								// return the remaining text
								$this->cell_padding = $tmpcellpadding;
								return (TCPDF_FONTS::UniArrSubString($uchars, ($sep + $endspace)));
							}
							$i = $sep;
							$sep = -1;
							$shy = false;
							$j = ($i + 1);
						}
					}
					// account for margin changes
					if ((($this->y + $this->lasth) > $this->PageBreakTrigger) AND ($this->inPageBody())) {
						$this->AcceptPageBreak();
						if ($this->rtl) {
							$this->x -= $margin['R'];
						} else {
							$this->x += $margin['L'];
						}
						$this->lMargin += $margin['L'];
						$this->rMargin += $margin['R'];
					}
					$w = $this->getRemainingWidth();
					$wmax = $w - $this->cell_padding['L'] - $this->cell_padding['R'];
					if ($linebreak) {
						$linebreak = false;
					} else {
						++$nl;
						$l = 0;
					}
				}
			}
			// save last character
			$pc = $c;
			++$i;
		} // end while i < nb
		// print last substring (if any)
		if ($l > 0) {
			switch ($align) {
				case 'J':
				case 'C': {
					$w = $w;
					break;
				}
				case 'L': {
					if ($this->rtl) {
						$w = $w;
					} else {
						$w = $l;
					}
					break;
				}
				case 'R': {
					if ($this->rtl) {
						$w = $l;
					} else {
						$w = $w;
					}
					break;
				}
				default: {
					$w = $l;
					break;
				}
			}
			$tmpstr = TCPDF_FONTS::UniArrSubString($uchars, $j, $nb);
			if ($firstline) {
				$startx = $this->x;
				$tmparr = array_slice($chars, $j, ($nb - $j));
				if ($rtlmode) {
					$tmparr = TCPDF_FONTS::utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
				}
				$linew = $this->GetArrStringWidth($tmparr);
				unset($tmparr);
				if ($this->rtl) {
					$this->endlinex = $startx - $linew;
				} else {
					$this->endlinex = $startx + $linew;
				}
				$w = $linew;
				$tmpcellpadding = $this->cell_padding;
				if ($maxh == 0) {
					$this->SetCellPadding(0);
				}
			}
			if ($firstblock AND $this->isRTLTextDir()) {
				$tmpstr = $this->stringRightTrim($tmpstr);
			}
			$this->Cell($w, $h, $tmpstr, 0, $ln, $align, $fill, $link, $stretch);
			unset($tmpstr);
			if ($firstline) {
				$this->cell_padding = $tmpcellpadding;
				return (TCPDF_FONTS::UniArrSubString($uchars, $nb));
			}
			++$nl;
		}
		if ($firstline) {
			return '';
		}
		return $nl;
	}

	/**
	 * Returns the remaining width between the current position and margins.
	 * @return int Return the remaining width
	 * @protected
	 */
	protected function getRemainingWidth() {
		list($this->x, $this->y) = $this->checkPageRegions(0, $this->x, $this->y);
		if ($this->rtl) {
			return ($this->x - $this->lMargin);
		} else {
			return ($this->w - $this->rMargin - $this->x);
		}
	}

	/**
	 * Set the block dimensions accounting for page breaks and page/column fitting
	 * @param $w (float) width
	 * @param $h (float) height
	 * @param $x (float) X coordinate
	 * @param $y (float) Y coodiante
	 * @param $fitonpage (boolean) if true the block is resized to not exceed page dimensions.
	 * @return array($w, $h, $x, $y)
	 * @protected
	 * @since 5.5.009 (2010-07-05)
	 */
	protected function fitBlock($w, $h, $x, $y, $fitonpage=false) {
		if ($w <= 0) {
			// set maximum width
			$w = ($this->w - $this->lMargin - $this->rMargin);
			if ($w <= 0) {
				$w = 1;
			}
		}
		if ($h <= 0) {
			// set maximum height
			$h = ($this->PageBreakTrigger - $this->tMargin);
			if ($h <= 0) {
				$h = 1;
			}
		}
		// resize the block to be vertically contained on a single page or single column
		if ($fitonpage OR $this->AutoPageBreak) {
			$ratio_wh = ($w / $h);
			if ($h > ($this->PageBreakTrigger - $this->tMargin)) {
				$h = $this->PageBreakTrigger - $this->tMargin;
				$w = ($h * $ratio_wh);
			}
			// resize the block to be horizontally contained on a single page or single column
			if ($fitonpage) {
				$maxw = ($this->w - $this->lMargin - $this->rMargin);
				if ($w > $maxw) {
					$w = $maxw;
					$h = ($w / $ratio_wh);
				}
			}
		}
		// Check whether we need a new page or new column first as this does not fit
		$prev_x = $this->x;
		$prev_y = $this->y;
		if ($this->checkPageBreak($h, $y) OR ($this->y < $prev_y)) {
			$y = $this->y;
			if ($this->rtl) {
				$x += ($prev_x - $this->x);
			} else {
				$x += ($this->x - $prev_x);
			}
			$this->newline = true;
		}
		// resize the block to be contained on the remaining available page or column space
		if ($fitonpage) {
			$ratio_wh = ($w / $h);
			if (($y + $h) > $this->PageBreakTrigger) {
				$h = $this->PageBreakTrigger - $y;
				$w = ($h * $ratio_wh);
			}
			if ((!$this->rtl) AND (($x + $w) > ($this->w - $this->rMargin))) {
				$w = $this->w - $this->rMargin - $x;
				$h = ($w / $ratio_wh);
			} elseif (($this->rtl) AND (($x - $w) < ($this->lMargin))) {
				$w = $x - $this->lMargin;
				$h = ($w / $ratio_wh);
			}
		}
		return array($w, $h, $x, $y);
	}

	/**
	 * Puts an image in the page.
	 * The upper-left corner must be given.
	 * The dimensions can be specified in different ways:<ul>
	 * <li>explicit width and height (expressed in user unit)</li>
	 * <li>one explicit dimension, the other being calculated automatically in order to keep the original proportions</li>
	 * <li>no explicit dimension, in which case the image is put at 72 dpi</li></ul>
	 * Supported formats are JPEG and PNG images whitout GD library and all images supported by GD: GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM;
	 * The format can be specified explicitly or inferred from the file extension.<br />
	 * It is possible to put a link on the image.<br />
	 * Remark: if an image is used several times, only one copy will be embedded in the file.<br />
	 * @param $file (string) Name of the file containing the image or a '@' character followed by the image data string. To link an image without embedding it on the document, set an asterisk character before the URL (i.e.: '*http://www.example.com/image.jpg').
	 * @param $x (float) Abscissa of the upper-left corner (LTR) or upper-right corner (RTL).
	 * @param $y (float) Ordinate of the upper-left corner (LTR) or upper-right corner (RTL).
	 * @param $w (float) Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $h (float) Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $type (string) Image format. Possible values are (case insensitive): JPEG and PNG (whitout GD library) and all images supported by GD: GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM;. If not specified, the type is inferred from the file extension.
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $align (string) Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $resize (mixed) If true resize (reduce) the image to fit $w and $h (requires GD or ImageMagick library); if false do not resize; if 2 force resize in all cases (upscaling and downscaling).
	 * @param $dpi (int) dot-per-inch resolution used on resize
	 * @param $palign (string) Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $ismask (boolean) true if this image is a mask, false otherwise
	 * @param $imgmask (mixed) image object returned by this function or false
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $fitbox (mixed) If not false scale image dimensions proportionally to fit within the ($w, $h) box. $fitbox can be true or a 2 characters string indicating the image alignment inside the box. The first character indicate the horizontal alignment (L = left, C = center, R = right) the second character indicate the vertical algnment (T = top, M = middle, B = bottom).
	 * @param $hidden (boolean) If true do not display the image.
	 * @param $fitonpage (boolean) If true the image is resized to not exceed page dimensions.
	 * @param $alt (boolean) If true the image will be added as alternative and not directly printed (the ID of the image will be returned).
	 * @param $altimgs (array) Array of alternate images IDs. Each alternative image must be an array with two values: an integer representing the image ID (the value returned by the Image method) and a boolean value to indicate if the image is the default for printing.
	 * @return image information
	 * @public
	 * @since 1.1
	 */
	public function Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array()) {
		if ($this->state != 2) {
			return;
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$exurl = ''; // external streams
		$imsize = FALSE;
		// check if we are passing an image as file or string
		if ($file[0] === '@') {
			// image from string
			$imgdata = substr($file, 1);
		} else { // image file
			if ($file{0} === '*') {
				// image as external stream
				$file = substr($file, 1);
				$exurl = $file;
			}
			// check if is local file
			if (!@file_exists($file)) {
				// encode spaces on filename (file is probably an URL)
				$file = str_replace(' ', '%20', $file);
			}
			if (@file_exists($file)) {
				// get image dimensions
				$imsize = @getimagesize($file);
			}
			if ($imsize === FALSE) {
				$imgdata = TCPDF_STATIC::fileGetContents($file);
			}
		}
		if (isset($imgdata) AND ($imgdata !== FALSE) AND (strpos($file, '__tcpdf_img') === FALSE)) {
			// copy image to cache
			$original_file = $file;
			$file = TCPDF_STATIC::getObjFilename('img');
			$fp = fopen($file, 'w');
			fwrite($fp, $imgdata);
			fclose($fp);
			unset($imgdata);
			$imsize = @getimagesize($file);
			if ($imsize === FALSE) {
				unlink($file);
				$file = $original_file;
			} else {
				$this->cached_files[] = $file;
			}
		}
		if ($imsize === FALSE) {
			if (($w > 0) AND ($h > 0)) {
				// get measures from specified data
				$pw = $this->getHTMLUnitToUnits($w, 0, $this->pdfunit, true) * $this->imgscale * $this->k;
				$ph = $this->getHTMLUnitToUnits($h, 0, $this->pdfunit, true) * $this->imgscale * $this->k;
				$imsize = array($pw, $ph);
			} else {
				$this->Error('[Image] Unable to get the size of the image: '.$file);
			}
		}
		// file hash
		$filehash = md5($this->file_id.$file);
		// get original image width and height in pixels
		list($pixw, $pixh) = $imsize;
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			// convert image size to document unit
			$w = $this->pixelsToUnits($pixw);
			$h = $this->pixelsToUnits($pixh);
		} elseif ($w <= 0) {
			$w = $h * $pixw / $pixh;
		} elseif ($h <= 0) {
			$h = $w * $pixh / $pixw;
		} elseif (($fitbox !== false) AND ($w > 0) AND ($h > 0)) {
			if (strlen($fitbox) !== 2) {
				// set default alignment
				$fitbox = '--';
			}
			// scale image dimensions proportionally to fit within the ($w, $h) box
			if ((($w * $pixh) / ($h * $pixw)) < 1) {
				// store current height
				$oldh = $h;
				// calculate new height
				$h = $w * $pixh / $pixw;
				// height difference
				$hdiff = ($oldh - $h);
				// vertical alignment
				switch (strtoupper($fitbox{1})) {
					case 'T': {
						break;
					}
					case 'M': {
						$y += ($hdiff / 2);
						break;
					}
					case 'B': {
						$y += $hdiff;
						break;
					}
				}
			} else {
				// store current width
				$oldw = $w;
				// calculate new width
				$w = $h * $pixw / $pixh;
				// width difference
				$wdiff = ($oldw - $w);
				// horizontal alignment
				switch (strtoupper($fitbox{0})) {
					case 'L': {
						if ($this->rtl) {
							$x -= $wdiff;
						}
						break;
					}
					case 'C': {
						if ($this->rtl) {
							$x -= ($wdiff / 2);
						} else {
							$x += ($wdiff / 2);
						}
						break;
					}
					case 'R': {
						if (!$this->rtl) {
							$x += $wdiff;
						}
						break;
					}
				}
			}
		}
		// fit the image on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		// calculate new minimum dimensions in pixels
		$neww = round($w * $this->k * $dpi / $this->dpi);
		$newh = round($h * $this->k * $dpi / $this->dpi);
		// check if resize is necessary (resize is used only to reduce the image)
		$newsize = ($neww * $newh);
		$pixsize = ($pixw * $pixh);
		if (intval($resize) == 2) {
			$resize = true;
		} elseif ($newsize >= $pixsize) {
			$resize = false;
		}
		// check if image has been already added on document
		$newimage = true;
		if (in_array($file, $this->imagekeys)) {
			$newimage = false;
			// get existing image data
			$info = $this->getImageBuffer($file);
			if (substr($file, 0, -34) != K_PATH_CACHE.'msk') {
				// check if the newer image is larger
				$oldsize = ($info['w'] * $info['h']);
				if ((($oldsize < $newsize) AND ($resize)) OR (($oldsize < $pixsize) AND (!$resize))) {
					$newimage = true;
				}
			}
		} elseif (($ismask === false) AND ($imgmask === false) AND (strpos($file, '__tcpdf_imgmask_') === FALSE)) {
			// create temp image file (without alpha channel)
			$tempfile_plain = K_PATH_CACHE.'__tcpdf_imgmask_plain_'.$filehash;
			// create temp alpha file
			$tempfile_alpha = K_PATH_CACHE.'__tcpdf_imgmask_alpha_'.$filehash;
			// check for cached images
			if (in_array($tempfile_plain, $this->imagekeys)) {
				// get existing image data
				$info = $this->getImageBuffer($tempfile_plain);
				// check if the newer image is larger
				$oldsize = ($info['w'] * $info['h']);
				if ((($oldsize < $newsize) AND ($resize)) OR (($oldsize < $pixsize) AND (!$resize))) {
					$newimage = true;
				} else {
					$newimage = false;
					// embed mask image
					$imgmask = $this->Image($tempfile_alpha, $x, $y, $w, $h, 'PNG', '', '', $resize, $dpi, '', true, false);
					// embed image, masked with previously embedded mask
					return $this->Image($tempfile_plain, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, false, $imgmask);
				}
			}
		}
		if ($newimage) {
			//First use of image, get info
			$type = strtolower($type);
			if ($type == '') {
				$type = TCPDF_IMAGES::getImageFileType($file, $imsize);
			} elseif ($type == 'jpg') {
				$type = 'jpeg';
			}
			$mqr = TCPDF_STATIC::get_mqr();
			TCPDF_STATIC::set_mqr(false);
			// Specific image handlers (defined on TCPDF_IMAGES CLASS)
			$mtd = '_parse'.$type;
			// GD image handler function
			$gdfunction = 'imagecreatefrom'.$type;
			$info = false;
			if ((method_exists('TCPDF_IMAGES', $mtd)) AND (!($resize AND (function_exists($gdfunction) OR extension_loaded('imagick'))))) {
				// TCPDF image functions
				$info = TCPDF_IMAGES::$mtd($file);
				if (($ismask === false) AND ($imgmask === false) AND (strpos($file, '__tcpdf_imgmask_') === FALSE)
					AND (($info === 'pngalpha') OR (isset($info['trns']) AND !empty($info['trns'])))) {
					return $this->ImagePngAlpha($file, $x, $y, $pixw, $pixh, $w, $h, 'PNG', $link, $align, $resize, $dpi, $palign, $filehash);
				}
			}
			if (($info === false) AND function_exists($gdfunction)) {
				try {
					// GD library
					$img = @$gdfunction($file);
					if ($img !== false) {
						if ($resize) {
							$imgr = imagecreatetruecolor($neww, $newh);
							if (($type == 'gif') OR ($type == 'png')) {
								$imgr = TCPDF_IMAGES::setGDImageTransparency($imgr, $img);
							}
							imagecopyresampled($imgr, $img, 0, 0, 0, 0, $neww, $newh, $pixw, $pixh);
							if (($type == 'gif') OR ($type == 'png')) {
								$info = TCPDF_IMAGES::_toPNG($imgr);
							} else {
								$info = TCPDF_IMAGES::_toJPEG($imgr, $this->jpeg_quality);
							}
						} else {
							if (($type == 'gif') OR ($type == 'png')) {
								$info = TCPDF_IMAGES::_toPNG($img);
							} else {
								$info = TCPDF_IMAGES::_toJPEG($img, $this->jpeg_quality);
							}
						}
					}
				} catch(Exception $e) {
					$info = false;
				}
			}
			if (($info === false) AND extension_loaded('imagick')) {
				try {
					// ImageMagick library
					$img = new Imagick();
					if ($type == 'SVG') {
						// get SVG file content
						$svgimg = TCPDF_STATIC::fileGetContents($file);
						if ($svgimg !== FALSE) {
							// get width and height
							$regs = array();
							if (preg_match('/<svg([^\>]*)>/si', $svgimg, $regs)) {
								$svgtag = $regs[1];
								$tmp = array();
								if (preg_match('/[\s]+width[\s]*=[\s]*"([^"]*)"/si', $svgtag, $tmp)) {
									$ow = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
									$owu = sprintf('%F', ($ow * $dpi / 72)).$this->pdfunit;
									$svgtag = preg_replace('/[\s]+width[\s]*=[\s]*"[^"]*"/si', ' width="'.$owu.'"', $svgtag, 1);
								} else {
									$ow = $w;
								}
								$tmp = array();
								if (preg_match('/[\s]+height[\s]*=[\s]*"([^"]*)"/si', $svgtag, $tmp)) {
									$oh = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
									$ohu = sprintf('%F', ($oh * $dpi / 72)).$this->pdfunit;
									$svgtag = preg_replace('/[\s]+height[\s]*=[\s]*"[^"]*"/si', ' height="'.$ohu.'"', $svgtag, 1);
								} else {
									$oh = $h;
								}
								$tmp = array();
								if (!preg_match('/[\s]+viewBox[\s]*=[\s]*"[\s]*([0-9\.]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]*"/si', $svgtag, $tmp)) {
									$vbw = ($ow * $this->imgscale * $this->k);
									$vbh = ($oh * $this->imgscale * $this->k);
									$vbox = sprintf(' viewBox="0 0 %F %F" ', $vbw, $vbh);
									$svgtag = $vbox.$svgtag;
								}
								$svgimg = preg_replace('/<svg([^\>]*)>/si', '<svg'.$svgtag.'>', $svgimg, 1);
							}
							$img->readImageBlob($svgimg);
						}
					} else {
						$img->readImage($file);
					}
					if ($resize) {
						$img->resizeImage($neww, $newh, 10, 1, false);
					}
					$img->setCompressionQuality($this->jpeg_quality);
					$img->setImageFormat('jpeg');
					$tempname = TCPDF_STATIC::getObjFilename('img');
					$img->writeImage($tempname);
					$info = TCPDF_IMAGES::_parsejpeg($tempname);
					unlink($tempname);
					$img->destroy();
				} catch(Exception $e) {
					$info = false;
				}
			}
			if ($info === false) {
				// unable to process image
				return;
			}
			TCPDF_STATIC::set_mqr($mqr);
            if(is_array($info)) {
                if ($ismask) {
                    // force grayscale
                    $info['cs'] = 'DeviceGray';
                }
                if ($imgmask !== false) {
                    $info['masked'] = $imgmask;
                }
                if (!empty($exurl)) {
                    $info['exurl'] = $exurl;
                }
                // array of alternative images
                $info['altimgs'] = $altimgs;
                // add image to document
                $info['i'] = $this->setImageBuffer($file, $info);
            }
		}
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x - $w;
			}
			$this->img_rb_x = $ximg;
		} else {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x;
			}
			$this->img_rb_x = $ximg + $w;
		}
		if ($ismask OR $hidden) {
			// image is not displayed
			return $info['i'];
		}
		$xkimg = $ximg * $this->k;
		if (!$alt) {
			// only non-alternative immages will be set
			$this->_out(sprintf('q %F 0 0 %F %F %F cm /I%u Do Q', ($w * $this->k), ($h * $this->k), $xkimg, (($this->h - ($y + $h)) * $this->k), @$info['i']));
		}
		if (!empty($border)) {
			$bx = $this->x;
			$by = $this->y;
			$this->x = $ximg;
			if ($this->rtl) {
				$this->x += $w;
			}
			$this->y = $y;
			$this->Cell($w, $h, '', $border, 0, '', 0, '', 0, true);
			$this->x = $bx;
			$this->y = $by;
		}
		if ($link) {
			$this->Link($ximg, $y, $w, $h, $link, 0);
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T': {
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M': {
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B': {
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N': {
				$this->SetY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['images'][] = $info['i'];
		}
		return $info['i'];
	}

	/**
	 * Extract info from a PNG image with alpha channel using the Imagick or GD library.
	 * @param $file (string) Name of the file containing the image.
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $wpx (float) Original width of the image in pixels.
	 * @param $hpx (float) original height of the image in pixels.
	 * @param $w (float) Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $h (float) Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $type (string) Image format. Possible values are (case insensitive): JPEG and PNG (whitout GD library) and all images supported by GD: GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM;. If not specified, the type is inferred from the file extension.
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $align (string) Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $resize (boolean) If true resize (reduce) the image to fit $w and $h (requires GD library).
	 * @param $dpi (int) dot-per-inch resolution used on resize
	 * @param $palign (string) Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $filehash (string) File hash used to build unique file names.
	 * @author Nicola Asuni
	 * @protected
	 * @since 4.3.007 (2008-12-04)
	 * @see Image()
	 */
	protected function ImagePngAlpha($file, $x, $y, $wpx, $hpx, $w, $h, $type, $link, $align, $resize, $dpi, $palign, $filehash='') {
		// create temp images
		if (empty($filehash)) {
			$filehash = md5($this->file_id.$file);
		}
		// create temp image file (without alpha channel)
		$tempfile_plain = K_PATH_CACHE.'__tcpdf_imgmask_plain_'.$filehash;
		// create temp alpha file
		$tempfile_alpha = K_PATH_CACHE.'__tcpdf_imgmask_alpha_'.$filehash;
		$parsed = false;
		$parse_error = '';
		// ImageMagick extension
		if (($parsed === false) AND extension_loaded('imagick')) {
			try {
				// ImageMagick library
				$img = new Imagick();
				$img->readImage($file);
				// clone image object
				$imga = TCPDF_STATIC::objclone($img);
				// extract alpha channel
				if (method_exists($img, 'setImageAlphaChannel') AND defined('Imagick::ALPHACHANNEL_EXTRACT')) {
					$img->setImageAlphaChannel(Imagick::ALPHACHANNEL_EXTRACT);
				} else {
					$img->separateImageChannel(8); // 8 = (imagick::CHANNEL_ALPHA | imagick::CHANNEL_OPACITY | imagick::CHANNEL_MATTE);
					$img->negateImage(true);
				}
				$img->setImageFormat('png');
				$img->writeImage($tempfile_alpha);
				// remove alpha channel
				if (method_exists($imga, 'setImageMatte')) {
					$imga->setImageMatte(false);
				} else {
					$imga->separateImageChannel(39); // 39 = (imagick::CHANNEL_ALL & ~(imagick::CHANNEL_ALPHA | imagick::CHANNEL_OPACITY | imagick::CHANNEL_MATTE));
				}
				$imga->setImageFormat('png');
				$imga->writeImage($tempfile_plain);
				$parsed = true;
			} catch (Exception $e) {
				// Imagemagick fails, try with GD
				$parse_error = 'Imagick library error: '.$e->getMessage();
			}
		}
		// GD extension
		if (($parsed === false) AND function_exists('imagecreatefrompng')) {
			try {
				// generate images
				$img = imagecreatefrompng($file);
				$imgalpha = imagecreate($wpx, $hpx);
				// generate gray scale palette (0 -> 255)
				for ($c = 0; $c < 256; ++$c) {
					ImageColorAllocate($imgalpha, $c, $c, $c);
				}
				// extract alpha channel
				for ($xpx = 0; $xpx < $wpx; ++$xpx) {
					for ($ypx = 0; $ypx < $hpx; ++$ypx) {
						$color = imagecolorat($img, $xpx, $ypx);
						// get and correct gamma color
						$alpha = $this->getGDgamma($img, $color);
						imagesetpixel($imgalpha, $xpx, $ypx, $alpha);
					}
				}
				imagepng($imgalpha, $tempfile_alpha);
				imagedestroy($imgalpha);
				// extract image without alpha channel
				$imgplain = imagecreatetruecolor($wpx, $hpx);
				imagecopy($imgplain, $img, 0, 0, 0, 0, $wpx, $hpx);
				imagepng($imgplain, $tempfile_plain);
				imagedestroy($imgplain);
				$parsed = true;
			} catch (Exception $e) {
				// GD fails
				$parse_error = 'GD library error: '.$e->getMessage();
			}
		}
		if ($parsed === false) {
			if (empty($parse_error)) {
				$this->Error('TCPDF requires the Imagick or GD extension to handle PNG images with alpha channel.');
			} else {
				$this->Error($parse_error);
			}
		}
		// embed mask image
		$imgmask = $this->Image($tempfile_alpha, $x, $y, $w, $h, 'PNG', '', '', $resize, $dpi, '', true, false);
		// embed image, masked with previously embedded mask
		$this->Image($tempfile_plain, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, false, $imgmask);
		// remove temp files
		unlink($tempfile_alpha);
		unlink($tempfile_plain);
	}

	/**
	 * Get the GD-corrected PNG gamma value from alpha color
	 * @param $img (int) GD image Resource ID.
	 * @param $c (int) alpha color
	 * @protected
	 * @since 4.3.007 (2008-12-04)
	 */
	protected function getGDgamma($img, $c) {
		if (!isset($this->gdgammacache['#'.$c])) {
			$colors = imagecolorsforindex($img, $c);
			// GD alpha is only 7 bit (0 -> 127)
			$this->gdgammacache['#'.$c] = (((127 - $colors['alpha']) / 127) * 255);
			// correct gamma
			$this->gdgammacache['#'.$c] = (pow(($this->gdgammacache['#'.$c] / 255), 2.2) * 255);
			// store the latest values on cache to improve performances
			if (count($this->gdgammacache) > 8) {
				// remove one element from the cache array
				array_shift($this->gdgammacache);
			}
		}
		return $this->gdgammacache['#'.$c];
	}

	/**
	 * Performs a line break.
	 * The current abscissa goes back to the left margin and the ordinate increases by the amount passed in parameter.
	 * @param $h (float) The height of the break. By default, the value equals the height of the last printed cell.
	 * @param $cell (boolean) if true add the current left (or right o for RTL) padding to the X coordinate
	 * @public
	 * @since 1.0
	 * @see Cell()
	 */
	public function Ln($h='', $cell=false) {
		if (($this->num_columns > 1) AND ($this->y == $this->columns[$this->current_column]['y']) AND isset($this->columns[$this->current_column]['x']) AND ($this->x == $this->columns[$this->current_column]['x'])) {
			// revove vertical space from the top of the column
			return;
		}
		if ($cell) {
			if ($this->rtl) {
				$cellpadding = $this->cell_padding['R'];
			} else {
				$cellpadding = $this->cell_padding['L'];
			}
		} else {
			$cellpadding = 0;
		}
		if ($this->rtl) {
			$this->x = $this->w - $this->rMargin - $cellpadding;
		} else {
			$this->x = $this->lMargin + $cellpadding;
		}
		if (is_string($h)) {
			$this->y += $this->lasth;
		} else {
			$this->y += $h;
		}
		$this->newline = true;
	}

	/**
	 * Returns the relative X value of current position.
	 * The value is relative to the left border for LTR languages and to the right border for RTL languages.
	 * @return float
	 * @public
	 * @since 1.2
	 * @see SetX(), GetY(), SetY()
	 */
	public function GetX() {
		//Get x position
		if ($this->rtl) {
			return ($this->w - $this->x);
		} else {
			return $this->x;
		}
	}

	/**
	 * Returns the absolute X value of current position.
	 * @return float
	 * @public
	 * @since 1.2
	 * @see SetX(), GetY(), SetY()
	 */
	public function GetAbsX() {
		return $this->x;
	}

	/**
	 * Returns the ordinate of the current position.
	 * @return float
	 * @public
	 * @since 1.0
	 * @see SetY(), GetX(), SetX()
	 */
	public function GetY() {
		return $this->y;
	}

	/**
	 * Defines the abscissa of the current position.
	 * If the passed value is negative, it is relative to the right of the page (or left if language is RTL).
	 * @param $x (float) The value of the abscissa in user units.
	 * @param $rtloff (boolean) if true always uses the page top-left corner as origin of axis.
	 * @public
	 * @since 1.2
	 * @see GetX(), GetY(), SetY(), SetXY()
	 */
	public function SetX($x, $rtloff=false) {
		$x = floatval($x);
		if (!$rtloff AND $this->rtl) {
			if ($x >= 0) {
				$this->x = $this->w - $x;
			} else {
				$this->x = abs($x);
			}
		} else {
			if ($x >= 0) {
				$this->x = $x;
			} else {
				$this->x = $this->w + $x;
			}
		}
		if ($this->x < 0) {
			$this->x = 0;
		}
		if ($this->x > $this->w) {
			$this->x = $this->w;
		}
	}

	/**
	 * Moves the current abscissa back to the left margin and sets the ordinate.
	 * If the passed value is negative, it is relative to the bottom of the page.
	 * @param $y (float) The value of the ordinate in user units.
	 * @param $resetx (bool) if true (default) reset the X position.
	 * @param $rtloff (boolean) if true always uses the page top-left corner as origin of axis.
	 * @public
	 * @since 1.0
	 * @see GetX(), GetY(), SetY(), SetXY()
	 */
	public function SetY($y, $resetx=true, $rtloff=false) {
		$y = floatval($y);
		if ($resetx) {
			//reset x
			if (!$rtloff AND $this->rtl) {
				$this->x = $this->w - $this->rMargin;
			} else {
				$this->x = $this->lMargin;
			}
		}
		if ($y >= 0) {
			$this->y = $y;
		} else {
			$this->y = $this->h + $y;
		}
		if ($this->y < 0) {
			$this->y = 0;
		}
		if ($this->y > $this->h) {
			$this->y = $this->h;
		}
	}

	/**
	 * Defines the abscissa and ordinate of the current position.
	 * If the passed values are negative, they are relative respectively to the right and bottom of the page.
	 * @param $x (float) The value of the abscissa.
	 * @param $y (float) The value of the ordinate.
	 * @param $rtloff (boolean) if true always uses the page top-left corner as origin of axis.
	 * @public
	 * @since 1.2
	 * @see SetX(), SetY()
	 */
	public function SetXY($x, $y, $rtloff=false) {
		$this->SetY($y, false, $rtloff);
		$this->SetX($x, $rtloff);
	}

	/**
	 * Set the absolute X coordinate of the current pointer.
	 * @param $x (float) The value of the abscissa in user units.
	 * @public
	 * @since 5.9.186 (2012-09-13)
	 * @see setAbsX(), setAbsY(), SetAbsXY()
	 */
	public function SetAbsX($x) {
		$this->x = floatval($x);
	}

	/**
	 * Set the absolute Y coordinate of the current pointer.
	 * @param $y (float) (float) The value of the ordinate in user units.
	 * @public
	 * @since 5.9.186 (2012-09-13)
	 * @see setAbsX(), setAbsY(), SetAbsXY()
	 */
	public function SetAbsY($y) {
		$this->y = floatval($y);
	}

	/**
	 * Set the absolute X and Y coordinates of the current pointer.
	 * @param $x (float) The value of the abscissa in user units.
	 * @param $y (float) (float) The value of the ordinate in user units.
	 * @public
	 * @since 5.9.186 (2012-09-13)
	 * @see setAbsX(), setAbsY(), SetAbsXY()
	 */
	public function SetAbsXY($x, $y) {
		$this->SetAbsX($x);
		$this->SetAbsY($y);
	}

	/**
	 * Send the document to a given destination: string, local file or browser.
	 * In the last case, the plug-in may be used (if present) or a download ("Save as" dialog box) may be forced.<br />
	 * The method first calls Close() if necessary to terminate the document.
	 * @param $name (string) The name of the file when saved. Note that special characters are removed and blanks characters are replaced with the underscore character.
	 * @param $dest (string) Destination where to send the document. It can take one of the following values:<ul><li>I: send the file inline to the browser (default). The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.</li><li>D: send to the browser and force a file download with the name given by name.</li><li>F: save to a local server file with the name given by name.</li><li>S: return the document as a string (name is ignored).</li><li>FI: equivalent to F + I option</li><li>FD: equivalent to F + D option</li><li>E: return the document as base64 mime multi-part email attachment (RFC 2045)</li></ul>
	 * @public
	 * @since 1.0
	 * @see Close()
	 */
	public function Output($name='doc.pdf', $dest='I') {
		//Output PDF to some destination
		//Finish document if necessary
		if ($this->state < 3) {
			$this->Close();
		}
		//Normalize parameters
		if (is_bool($dest)) {
			$dest = $dest ? 'D' : 'F';
		}
		$dest = strtoupper($dest);
		if ($dest{0} != 'F') {
			$name = preg_replace('/[\s]+/', '_', $name);
			$name = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $name);
		}
		if ($this->sign) {
			// *** apply digital signature to the document ***
			// get the document content
			$pdfdoc = $this->getBuffer();
			// remove last newline
			$pdfdoc = substr($pdfdoc, 0, -1);
			// Remove the original buffer
			if (isset($this->diskcache) AND $this->diskcache) {
				// remove buffer file from cache
				unlink($this->buffer);
			}
			unset($this->buffer);
			// remove filler space
			$byterange_string_len = strlen(TCPDF_STATIC::$byterange_string);
			// define the ByteRange
			$byte_range = array();
			$byte_range[0] = 0;
			$byte_range[1] = strpos($pdfdoc, TCPDF_STATIC::$byterange_string) + $byterange_string_len + 10;
			$byte_range[2] = $byte_range[1] + $this->signature_max_length + 2;
			$byte_range[3] = strlen($pdfdoc) - $byte_range[2];
			$pdfdoc = substr($pdfdoc, 0, $byte_range[1]).substr($pdfdoc, $byte_range[2]);
			// replace the ByteRange
			$byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2], $byte_range[3]);
			$byterange .= str_repeat(' ', ($byterange_string_len - strlen($byterange)));
			$pdfdoc = str_replace(TCPDF_STATIC::$byterange_string, $byterange, $pdfdoc);
			// write the document to a temporary folder
			$tempdoc = TCPDF_STATIC::getObjFilename('doc');
			$f = fopen($tempdoc, 'wb');
			if (!$f) {
				$this->Error('Unable to create temporary file: '.$tempdoc);
			}
			$pdfdoc_length = strlen($pdfdoc);
			fwrite($f, $pdfdoc, $pdfdoc_length);
			fclose($f);
			// get digital signature via openssl library
			$tempsign = TCPDF_STATIC::getObjFilename('sig');
			if (empty($this->signature_data['extracerts'])) {
				openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED);
			} else {
				openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED, $this->signature_data['extracerts']);
			}
			unlink($tempdoc);
			// read signature
			$signature = file_get_contents($tempsign);
			unlink($tempsign);
			// extract signature
			$signature = substr($signature, $pdfdoc_length);
			$signature = substr($signature, (strpos($signature, "%%EOF\n\n------") + 13));
			$tmparr = explode("\n\n", $signature);
			$signature = $tmparr[1];
			unset($tmparr);
			// decode signature
			$signature = base64_decode(trim($signature));
			// convert signature to hex
			$signature = current(unpack('H*', $signature));
			$signature = str_pad($signature, $this->signature_max_length, '0');
			// disable disk caching
			$this->diskcache = false;
			// Add signature to the document
			$this->buffer = substr($pdfdoc, 0, $byte_range[1]).'<'.$signature.'>'.substr($pdfdoc, $byte_range[1]);
			$this->bufferlen = strlen($this->buffer);
		}
		switch($dest) {
			case 'I': {
				// Send PDF to the standard output
				if (ob_get_contents()) {
					$this->Error('Some data has already been output, can\'t send PDF file');
				}
				if (php_sapi_name() != 'cli') {
					// send output to a browser
					header('Content-Type: application/pdf');
					if (headers_sent()) {
						$this->Error('Some data has already been output to browser, can\'t send PDF file');
					}
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					header('Content-Disposition: inline; filename="'.basename($name).'"');
					TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
				} else {
					echo $this->getBuffer();
				}
				break;
			}
			case 'D': {
				// download PDF as file
				if (ob_get_contents()) {
					$this->Error('Some data has already been output, can\'t send PDF file');
				}
				header('Content-Description: File Transfer');
				if (headers_sent()) {
					$this->Error('Some data has already been output to browser, can\'t send PDF file');
				}
				header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
				//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
				header('Pragma: public');
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
				// force download dialog
				if (strpos(php_sapi_name(), 'cgi') === false) {
					header('Content-Type: application/force-download');
					header('Content-Type: application/octet-stream', false);
					header('Content-Type: application/download', false);
					header('Content-Type: application/pdf', false);
				} else {
					header('Content-Type: application/pdf');
				}
				// use the Content-Disposition header to supply a recommended filename
				header('Content-Disposition: attachment; filename="'.basename($name).'"');
				header('Content-Transfer-Encoding: binary');
				TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
				break;
			}
			case 'F':
			case 'FI':
			case 'FD': {
				// save PDF to a local file
				if ($this->diskcache) {
					copy($this->buffer, $name);
				} else {
					$f = fopen($name, 'wb');
					if (!$f) {
						$this->Error('Unable to create output file: '.$name);
					}
					fwrite($f, $this->getBuffer(), $this->bufferlen);
					fclose($f);
				}
				if ($dest == 'FI') {
					// send headers to browser
					header('Content-Type: application/pdf');
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					header('Content-Disposition: inline; filename="'.basename($name).'"');
					TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
				} elseif ($dest == 'FD') {
					// send headers to browser
					if (ob_get_contents()) {
						$this->Error('Some data has already been output, can\'t send PDF file');
					}
					header('Content-Description: File Transfer');
					if (headers_sent()) {
						$this->Error('Some data has already been output to browser, can\'t send PDF file');
					}
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					// force download dialog
					if (strpos(php_sapi_name(), 'cgi') === false) {
						header('Content-Type: application/force-download');
						header('Content-Type: application/octet-stream', false);
						header('Content-Type: application/download', false);
						header('Content-Type: application/pdf', false);
					} else {
						header('Content-Type: application/pdf');
					}
					// use the Content-Disposition header to supply a recommended filename
					header('Content-Disposition: attachment; filename="'.basename($name).'"');
					header('Content-Transfer-Encoding: binary');
					TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
				}
				break;
			}
			case 'E': {
				// return PDF as base64 mime multi-part email attachment (RFC 2045)
				$retval = 'Content-Type: application/pdf;'."\r\n";
				$retval .= ' name="'.$name.'"'."\r\n";
				$retval .= 'Content-Transfer-Encoding: base64'."\r\n";
				$retval .= 'Content-Disposition: attachment;'."\r\n";
				$retval .= ' filename="'.$name.'"'."\r\n\r\n";
				$retval .= chunk_split(base64_encode($this->getBuffer()), 76, "\r\n");
				return $retval;
			}
			case 'S': {
				// returns PDF as a string
				return $this->getBuffer();
			}
			default: {
				$this->Error('Incorrect output destination: '.$dest);
			}
		}
		return '';
	}

	/**
	 * Unset all class variables except the following critical variables.
	 * @param $destroyall (boolean) if true destroys all class variables, otherwise preserves critical variables.
	 * @param $preserve_objcopy (boolean) if true preserves the objcopy variable
	 * @public
	 * @since 4.5.016 (2009-02-24)
	 */
	public function _destroy($destroyall=false, $preserve_objcopy=false) {
		if ($destroyall AND isset($this->diskcache) AND $this->diskcache AND (!$preserve_objcopy) AND (!TCPDF_STATIC::empty_string($this->buffer))) {
			// remove buffer file from cache
			unlink($this->buffer);
		}
		if ($destroyall AND isset($this->cached_files) AND !empty($this->cached_files)) {
			// remove cached files
			foreach ($this->cached_files as $cachefile) {
				if (is_file($cachefile)) {
					unlink($cachefile);
				}
			}
			unset($this->cached_files);
		}
		foreach (array_keys(get_object_vars($this)) as $val) {
			if ($destroyall OR (
				($val != 'internal_encoding')
				AND ($val != 'state')
				AND ($val != 'bufferlen')
				AND ($val != 'buffer')
				AND ($val != 'diskcache')
				AND ($val != 'cached_files')
				AND ($val != 'sign')
				AND ($val != 'signature_data')
				AND ($val != 'signature_max_length')
				AND ($val != 'byterange_string')
				)) {
				if ((!$preserve_objcopy OR ($val != 'objcopy')) AND isset($this->$val)) {
					unset($this->$val);
				}
			}
		}
	}

	/**
	 * Check for locale-related bug
	 * @protected
	 */
	protected function _dochecks() {
		//Check for locale-related bug
		if (1.1 == 1) {
			$this->Error('Don\'t alter the locale before including class file');
		}
		//Check for decimal separator
		if (sprintf('%.1F', 1.0) != '1.0') {
			setlocale(LC_NUMERIC, 'C');
		}
	}

	/**
	 * Return an array containing variations for the basic page number alias.
	 * @param $a (string) Base alias.
	 * @return array of page number aliases
	 * @protected
	 */
	protected function getInternalPageNumberAliases($a= '') {
		$alias = array();
		// build array of Unicode + ASCII variants (the order is important)
		$alias = array('u' => array(), 'a' => array());
		$u = '{'.$a.'}';
		$alias['u'][] = TCPDF_STATIC::_escape($u);
		if ($this->isunicode) {
			$alias['u'][] = TCPDF_STATIC::_escape(TCPDF_FONTS::UTF8ToLatin1($u, $this->isunicode, $this->CurrentFont));
			$alias['u'][] = TCPDF_STATIC::_escape(TCPDF_FONTS::utf8StrRev($u, false, $this->tmprtl, $this->isunicode, $this->CurrentFont));
			$alias['a'][] = TCPDF_STATIC::_escape(TCPDF_FONTS::UTF8ToLatin1($a, $this->isunicode, $this->CurrentFont));
			$alias['a'][] = TCPDF_STATIC::_escape(TCPDF_FONTS::utf8StrRev($a, false, $this->tmprtl, $this->isunicode, $this->CurrentFont));
		}
		$alias['a'][] = TCPDF_STATIC::_escape($a);
		return $alias;
	}

	/**
	 * Return an array containing all internal page aliases.
	 * @return array of page number aliases
	 * @protected
	 */
	protected function getAllInternalPageNumberAliases() {
		$basic_alias = array(TCPDF_STATIC::$alias_tot_pages, TCPDF_STATIC::$alias_num_page, TCPDF_STATIC::$alias_group_tot_pages, TCPDF_STATIC::$alias_group_num_page, TCPDF_STATIC::$alias_right_shift);
		$pnalias = array();
		foreach($basic_alias as $k => $a) {
			$pnalias[$k] = $this->getInternalPageNumberAliases($a);
		}
		return $pnalias;
	}

	/**
	 * Replace right shift page number aliases with spaces to correct right alignment.
	 * This works perfectly only when using monospaced fonts.
	 * @param $page (string) Page content.
	 * @param $aliases (array) Array of page aliases.
	 * @param $diff (int) initial difference to add.
	 * @return replaced page content.
	 * @protected
	 */
	protected function replaceRightShiftPageNumAliases($page, $aliases, $diff) {
		foreach ($aliases as $type => $alias) {
			foreach ($alias as $a) {
				// find position of compensation factor
				$startnum = (strpos($a, ':') + 1);
				$a = substr($a, 0, $startnum);
				if (($pos = strpos($page, $a)) !== false) {
					// end of alias
					$endnum = strpos($page, '}', $pos);
					// string to be replaced
					$aa = substr($page, $pos, ($endnum - $pos + 1));
					// get compensation factor
					$ratio = substr($page, ($pos + $startnum), ($endnum - $pos - $startnum));
					$ratio = preg_replace('/[^0-9\.]/', '', $ratio);
					$ratio = floatval($ratio);
					if ($type == 'u') {
						$chrdiff = floor(($diff + 12) * $ratio);
						$shift = str_repeat(' ', $chrdiff);
						$shift = TCPDF_FONTS::UTF8ToUTF16BE($shift, false, $this->isunicode, $this->CurrentFont);
					} else {
						$chrdiff = floor(($diff + 11) * $ratio);
						$shift = str_repeat(' ', $chrdiff);
					}
					$page = str_replace($aa, $shift, $page);
				}
			}
		}
		return $page;
	}

	/**
	 * Set page boxes to be included on page descriptions.
	 * @param $boxes (array) Array of page boxes to set on document: ('MediaBox', 'CropBox', 'BleedBox', 'TrimBox', 'ArtBox').
	 * @protected
	 */
	protected function setPageBoxTypes($boxes) {
		$this->page_boxes = array();
		foreach ($boxes as $box) {
			if (in_array($box, TCPDF_STATIC::$pageboxes)) {
				$this->page_boxes[] = $box;
			}
		}
	}

	/**
	 * Output pages (and replace page number aliases).
	 * @protected
	 */
	protected function _putpages() {
		$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
		// get internal aliases for page numbers
		$pnalias = $this->getAllInternalPageNumberAliases();
		$num_pages = $this->numpages;
		$ptpa = TCPDF_STATIC::formatPageNumber(($this->starting_page_number + $num_pages - 1));
		$ptpu = TCPDF_FONTS::UTF8ToUTF16BE($ptpa, false, $this->isunicode, $this->CurrentFont);
		$ptp_num_chars = $this->GetNumChars($ptpa);
		$pagegroupnum = 0;
		$groupnum = 0;
		$ptgu = 1;
		$ptga = 1;
		$ptg_num_chars = 1;
		for ($n = 1; $n <= $num_pages; ++$n) {
			// get current page
			$temppage = $this->getPageBuffer($n);
			$pagelen = strlen($temppage);
			// set replacements for total pages number
			$pnpa = TCPDF_STATIC::formatPageNumber(($this->starting_page_number + $n - 1));
			$pnpu = TCPDF_FONTS::UTF8ToUTF16BE($pnpa, false, $this->isunicode, $this->CurrentFont);
			$pnp_num_chars = $this->GetNumChars($pnpa);
			$pdiff = 0; // difference used for right shift alignment of page numbers
			$gdiff = 0; // difference used for right shift alignment of page group numbers
			if (!empty($this->pagegroups)) {
				if (isset($this->newpagegroup[$n])) {
					$pagegroupnum = 0;
					++$groupnum;
					$ptga = TCPDF_STATIC::formatPageNumber($this->pagegroups[$groupnum]);
					$ptgu = TCPDF_FONTS::UTF8ToUTF16BE($ptga, false, $this->isunicode, $this->CurrentFont);
					$ptg_num_chars = $this->GetNumChars($ptga);
				}
				++$pagegroupnum;
				$pnga = TCPDF_STATIC::formatPageNumber($pagegroupnum);
				$pngu = TCPDF_FONTS::UTF8ToUTF16BE($pnga, false, $this->isunicode, $this->CurrentFont);
				$png_num_chars = $this->GetNumChars($pnga);
				// replace page numbers
				$replace = array();
				$replace[] = array($ptgu, $ptg_num_chars, 9, $pnalias[2]['u']);
				$replace[] = array($ptga, $ptg_num_chars, 7, $pnalias[2]['a']);
				$replace[] = array($pngu, $png_num_chars, 9, $pnalias[3]['u']);
				$replace[] = array($pnga, $png_num_chars, 7, $pnalias[3]['a']);
				list($temppage, $gdiff) = TCPDF_STATIC::replacePageNumAliases($temppage, $replace, $gdiff);
			}
			// replace page numbers
			$replace = array();
			$replace[] = array($ptpu, $ptp_num_chars, 9, $pnalias[0]['u']);
			$replace[] = array($ptpa, $ptp_num_chars, 7, $pnalias[0]['a']);
			$replace[] = array($pnpu, $pnp_num_chars, 9, $pnalias[1]['u']);
			$replace[] = array($pnpa, $pnp_num_chars, 7, $pnalias[1]['a']);
			list($temppage, $pdiff) = TCPDF_STATIC::replacePageNumAliases($temppage, $replace, $pdiff);
			// replace right shift alias
			$temppage = $this->replaceRightShiftPageNumAliases($temppage, $pnalias[4], max($pdiff, $gdiff));
			// replace EPS marker
			$temppage = str_replace($this->epsmarker, '', $temppage);
			//Page
			$this->page_obj_id[$n] = $this->_newobj();
			$out = '<<';
			$out .= ' /Type /Page';
			$out .= ' /Parent 1 0 R';
			$out .= ' /LastModified '.$this->_datestring(0, $this->doc_modification_timestamp);
			$out .= ' /Resources 2 0 R';
			foreach ($this->page_boxes as $box) {
				$out .= ' /'.$box;
				$out .= sprintf(' [%F %F %F %F]', $this->pagedim[$n][$box]['llx'], $this->pagedim[$n][$box]['lly'], $this->pagedim[$n][$box]['urx'], $this->pagedim[$n][$box]['ury']);
			}
			if (isset($this->pagedim[$n]['BoxColorInfo']) AND !empty($this->pagedim[$n]['BoxColorInfo'])) {
				$out .= ' /BoxColorInfo <<';
				foreach ($this->page_boxes as $box) {
					if (isset($this->pagedim[$n]['BoxColorInfo'][$box])) {
						$out .= ' /'.$box.' <<';
						if (isset($this->pagedim[$n]['BoxColorInfo'][$box]['C'])) {
							$color = $this->pagedim[$n]['BoxColorInfo'][$box]['C'];
							$out .= ' /C [';
							$out .= sprintf(' %F %F %F', ($color[0] / 255), ($color[1] / 255), ($color[2] / 255));
							$out .= ' ]';
						}
						if (isset($this->pagedim[$n]['BoxColorInfo'][$box]['W'])) {
							$out .= ' /W '.($this->pagedim[$n]['BoxColorInfo'][$box]['W'] * $this->k);
						}
						if (isset($this->pagedim[$n]['BoxColorInfo'][$box]['S'])) {
							$out .= ' /S /'.$this->pagedim[$n]['BoxColorInfo'][$box]['S'];
						}
						if (isset($this->pagedim[$n]['BoxColorInfo'][$box]['D'])) {
							$dashes = $this->pagedim[$n]['BoxColorInfo'][$box]['D'];
							$out .= ' /D [';
							foreach ($dashes as $dash) {
								$out .= sprintf(' %F', ($dash * $this->k));
							}
							$out .= ' ]';
						}
						$out .= ' >>';
					}
				}
				$out .= ' >>';
			}
			$out .= ' /Contents '.($this->n + 1).' 0 R';
			$out .= ' /Rotate '.$this->pagedim[$n]['Rotate'];
			if (!$this->pdfa_mode) {
				$out .= ' /Group << /Type /Group /S /Transparency /CS /DeviceRGB >>';
			}
			if (isset($this->pagedim[$n]['trans']) AND !empty($this->pagedim[$n]['trans'])) {
				// page transitions
				if (isset($this->pagedim[$n]['trans']['Dur'])) {
					$out .= ' /Dur '.$this->pagedim[$n]['trans']['Dur'];
				}
				$out .= ' /Trans <<';
				$out .= ' /Type /Trans';
				if (isset($this->pagedim[$n]['trans']['S'])) {
					$out .= ' /S /'.$this->pagedim[$n]['trans']['S'];
				}
				if (isset($this->pagedim[$n]['trans']['D'])) {
					$out .= ' /D '.$this->pagedim[$n]['trans']['D'];
				}
				if (isset($this->pagedim[$n]['trans']['Dm'])) {
					$out .= ' /Dm /'.$this->pagedim[$n]['trans']['Dm'];
				}
				if (isset($this->pagedim[$n]['trans']['M'])) {
					$out .= ' /M /'.$this->pagedim[$n]['trans']['M'];
				}
				if (isset($this->pagedim[$n]['trans']['Di'])) {
					$out .= ' /Di '.$this->pagedim[$n]['trans']['Di'];
				}
				if (isset($this->pagedim[$n]['trans']['SS'])) {
					$out .= ' /SS '.$this->pagedim[$n]['trans']['SS'];
				}
				if (isset($this->pagedim[$n]['trans']['B'])) {
					$out .= ' /B '.$this->pagedim[$n]['trans']['B'];
				}
				$out .= ' >>';
			}
			$out .= $this->_getannotsrefs($n);
			$out .= ' /PZ '.$this->pagedim[$n]['PZ'];
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
			//Page content
			$p = ($this->compress) ? gzcompress($temppage) : $temppage;
			$this->_newobj();
			$p = $this->_getrawstream($p);
			$this->_out('<<'.$filter.'/Length '.strlen($p).'>> stream'."\n".$p."\n".'endstream'."\n".'endobj');
			if ($this->diskcache) {
				// remove temporary files
				unlink($this->pages[$n]);
			}
		}
		//Pages root
		$out = $this->_getobj(1)."\n";
		$out .= '<< /Type /Pages /Kids [';
		foreach($this->page_obj_id as $page_obj) {
			$out .= ' '.$page_obj.' 0 R';
		}
		$out .= ' ] /Count '.$num_pages.' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}

	/**
	 * Output references to page annotations
	 * @param $n (int) page number
	 * @protected
	 * @author Nicola Asuni
	 * @since 4.7.000 (2008-08-29)
	 * @deprecated
	 */
	protected function _putannotsrefs($n) {
		$this->_out($this->_getannotsrefs($n));
	}

	/**
	 * Get references to page annotations.
	 * @param $n (int) page number
	 * @return string
	 * @protected
	 * @author Nicola Asuni
	 * @since 5.0.010 (2010-05-17)
	 */
	protected function _getannotsrefs($n) {
		if (!(isset($this->PageAnnots[$n]) OR ($this->sign AND isset($this->signature_data['cert_type'])))) {
			return '';
		}
		$out = ' /Annots [';
		if (isset($this->PageAnnots[$n])) {
			foreach ($this->PageAnnots[$n] as $key => $val) {
				if (!in_array($val['n'], $this->radio_groups)) {
					$out .= ' '.$val['n'].' 0 R';
				}
			}
			// add radiobutton groups
			if (isset($this->radiobutton_groups[$n])) {
				foreach ($this->radiobutton_groups[$n] as $key => $data) {
					if (isset($data['n'])) {
						$out .= ' '.$data['n'].' 0 R';
					}
				}
			}
		}
		if ($this->sign AND ($n == $this->signature_appearance['page']) AND isset($this->signature_data['cert_type'])) {
			// set reference for signature object
			$out .= ' '.$this->sig_obj_id.' 0 R';
		}
		if (!empty($this->empty_signature_appearance)) {
			foreach ($this->empty_signature_appearance as $esa) {
				if ($esa['page'] == $n) {
					// set reference for empty signature objects
					$out .= ' '.$esa['objid'].' 0 R';
				}
			}
		}
		$out .= ' ]';
		return $out;
	}

	/**
	 * Output annotations objects for all pages.
	 * !!! THIS METHOD IS NOT YET COMPLETED !!!
	 * See section 12.5 of PDF 32000_2008 reference.
	 * @protected
	 * @author Nicola Asuni
	 * @since 4.0.018 (2008-08-06)
	 */
	protected function _putannotsobjs() {
		// reset object counter
		for ($n=1; $n <= $this->numpages; ++$n) {
			if (isset($this->PageAnnots[$n])) {
				// set page annotations
				foreach ($this->PageAnnots[$n] as $key => $pl) {
					$annot_obj_id = $this->PageAnnots[$n][$key]['n'];
					// create annotation object for grouping radiobuttons
					if (isset($this->radiobutton_groups[$n][$pl['txt']]) AND is_array($this->radiobutton_groups[$n][$pl['txt']])) {
						$radio_button_obj_id = $this->radiobutton_groups[$n][$pl['txt']]['n'];
						$annots = '<<';
						$annots .= ' /Type /Annot';
						$annots .= ' /Subtype /Widget';
						$annots .= ' /Rect [0 0 0 0]';
						if ($this->radiobutton_groups[$n][$pl['txt']]['#readonly#']) {
							// read only
							$annots .= ' /F 68';
							$annots .= ' /Ff 49153';
						} else {
							$annots .= ' /F 4'; // default print for PDF/A
							$annots .= ' /Ff 49152';
						}
						$annots .= ' /T '.$this->_datastring($pl['txt'], $radio_button_obj_id);
						if (isset($pl['opt']['tu']) AND is_string($pl['opt']['tu'])) {
							$annots .= ' /TU '.$this->_datastring($pl['opt']['tu'], $radio_button_obj_id);
						}
						$annots .= ' /FT /Btn';
						$annots .= ' /Kids [';
						$defval = '';
						foreach ($this->radiobutton_groups[$n][$pl['txt']] as $key => $data) {
							if (isset($data['kid'])) {
								$annots .= ' '.$data['kid'].' 0 R';
								if ($data['def'] !== 'Off') {
									$defval = $data['def'];
								}
							}
						}
						$annots .= ' ]';
						if (!empty($defval)) {
							$annots .= ' /V /'.$defval;
						}
						$annots .= ' >>';
						$this->_out($this->_getobj($radio_button_obj_id)."\n".$annots."\n".'endobj');
						$this->form_obj_id[] = $radio_button_obj_id;
						// store object id to be used on Parent entry of Kids
						$this->radiobutton_groups[$n][$pl['txt']] = $radio_button_obj_id;
					}
					$formfield = false;
					$pl['opt'] = array_change_key_case($pl['opt'], CASE_LOWER);
					$a = $pl['x'] * $this->k;
					$b = $this->pagedim[$n]['h'] - (($pl['y'] + $pl['h']) * $this->k);
					$c = $pl['w'] * $this->k;
					$d = $pl['h'] * $this->k;
					$rect = sprintf('%F %F %F %F', $a, $b, $a+$c, $b+$d);
					// create new annotation object
					$annots = '<</Type /Annot';
					$annots .= ' /Subtype /'.$pl['opt']['subtype'];
					$annots .= ' /Rect ['.$rect.']';
					$ft = array('Btn', 'Tx', 'Ch', 'Sig');
					if (isset($pl['opt']['ft']) AND in_array($pl['opt']['ft'], $ft)) {
						$annots .= ' /FT /'.$pl['opt']['ft'];
						$formfield = true;
					}
					$annots .= ' /Contents '.$this->_textstring($pl['txt'], $annot_obj_id);
					$annots .= ' /P '.$this->page_obj_id[$n].' 0 R';
					$annots .= ' /NM '.$this->_datastring(sprintf('%04u-%04u', $n, $key), $annot_obj_id);
					$annots .= ' /M '.$this->_datestring($annot_obj_id, $this->doc_modification_timestamp);
					if (isset($pl['opt']['f'])) {
						$fval = 0;
						if (is_array($pl['opt']['f'])) {
							foreach ($pl['opt']['f'] as $f) {
								switch (strtolower($f)) {
									case 'invisible': {
										$fval += 1 << 0;
										break;
									}
									case 'hidden': {
										$fval += 1 << 1;
										break;
									}
									case 'print': {
										$fval += 1 << 2;
										break;
									}
									case 'nozoom': {
										$fval += 1 << 3;
										break;
									}
									case 'norotate': {
										$fval += 1 << 4;
										break;
									}
									case 'noview': {
										$fval += 1 << 5;
										break;
									}
									case 'readonly': {
										$fval += 1 << 6;
										break;
									}
									case 'locked': {
										$fval += 1 << 8;
										break;
									}
									case 'togglenoview': {
										$fval += 1 << 9;
										break;
									}
									case 'lockedcontents': {
										$fval += 1 << 10;
										break;
									}
									default: {
										break;
									}
								}
							}
						} else {
							$fval = intval($pl['opt']['f']);
						}
					} else {
						$fval = 4;
					}
					if ($this->pdfa_mode) {
						// force print flag for PDF/A mode
						$fval |= 4;
					}
					$annots .= ' /F '.intval($fval);
					if (isset($pl['opt']['as']) AND is_string($pl['opt']['as'])) {
						$annots .= ' /AS /'.$pl['opt']['as'];
					}
					if (isset($pl['opt']['ap'])) {
						// appearance stream
						$annots .= ' /AP <<';
						if (is_array($pl['opt']['ap'])) {
							foreach ($pl['opt']['ap'] as $apmode => $apdef) {
								// $apmode can be: n = normal; r = rollover; d = down;
								$annots .= ' /'.strtoupper($apmode);
								if (is_array($apdef)) {
									$annots .= ' <<';
									foreach ($apdef as $apstate => $stream) {
										// reference to XObject that define the appearance for this mode-state
										$apsobjid = $this->_putAPXObject($c, $d, $stream);
										$annots .= ' /'.$apstate.' '.$apsobjid.' 0 R';
									}
									$annots .= ' >>';
								} else {
									// reference to XObject that define the appearance for this mode
									$apsobjid = $this->_putAPXObject($c, $d, $apdef);
									$annots .= ' '.$apsobjid.' 0 R';
								}
							}
						} else {
							$annots .= $pl['opt']['ap'];
						}
						$annots .= ' >>';
					}
					if (isset($pl['opt']['bs']) AND (is_array($pl['opt']['bs']))) {
						$annots .= ' /BS <<';
						$annots .= ' /Type /Border';
						if (isset($pl['opt']['bs']['w'])) {
							$annots .= ' /W '.intval($pl['opt']['bs']['w']);
						}
						$bstyles = array('S', 'D', 'B', 'I', 'U');
						if (isset($pl['opt']['bs']['s']) AND in_array($pl['opt']['bs']['s'], $bstyles)) {
							$annots .= ' /S /'.$pl['opt']['bs']['s'];
						}
						if (isset($pl['opt']['bs']['d']) AND (is_array($pl['opt']['bs']['d']))) {
							$annots .= ' /D [';
							foreach ($pl['opt']['bs']['d'] as $cord) {
								$annots .= ' '.intval($cord);
							}
							$annots .= ']';
						}
						$annots .= ' >>';
					} else {
						$annots .= ' /Border [';
						if (isset($pl['opt']['border']) AND (count($pl['opt']['border']) >= 3)) {
							$annots .= intval($pl['opt']['border'][0]).' ';
							$annots .= intval($pl['opt']['border'][1]).' ';
							$annots .= intval($pl['opt']['border'][2]);
							if (isset($pl['opt']['border'][3]) AND is_array($pl['opt']['border'][3])) {
								$annots .= ' [';
								foreach ($pl['opt']['border'][3] as $dash) {
									$annots .= intval($dash).' ';
								}
								$annots .= ']';
							}
						} else {
							$annots .= '0 0 0';
						}
						$annots .= ']';
					}
					if (isset($pl['opt']['be']) AND (is_array($pl['opt']['be']))) {
						$annots .= ' /BE <<';
						$bstyles = array('S', 'C');
						if (isset($pl['opt']['be']['s']) AND in_array($pl['opt']['be']['s'], $bstyles)) {
							$annots .= ' /S /'.$pl['opt']['bs']['s'];
						} else {
							$annots .= ' /S /S';
						}
						if (isset($pl['opt']['be']['i']) AND ($pl['opt']['be']['i'] >= 0) AND ($pl['opt']['be']['i'] <= 2)) {
							$annots .= ' /I '.sprintf(' %F', $pl['opt']['be']['i']);
						}
						$annots .= '>>';
					}
					if (isset($pl['opt']['c']) AND (is_array($pl['opt']['c'])) AND !empty($pl['opt']['c'])) {
						$annots .= ' /C '.TCPDF_COLORS::getColorStringFromArray($pl['opt']['c']);
					}
					//$annots .= ' /StructParent ';
					//$annots .= ' /OC ';
					$markups = array('text', 'freetext', 'line', 'square', 'circle', 'polygon', 'polyline', 'highlight', 'underline', 'squiggly', 'strikeout', 'stamp', 'caret', 'ink', 'fileattachment', 'sound');
					if (in_array(strtolower($pl['opt']['subtype']), $markups)) {
						// this is a markup type
						if (isset($pl['opt']['t']) AND is_string($pl['opt']['t'])) {
							$annots .= ' /T '.$this->_textstring($pl['opt']['t'], $annot_obj_id);
						}
						//$annots .= ' /Popup ';
						if (isset($pl['opt']['ca'])) {
							$annots .= ' /CA '.sprintf('%F', floatval($pl['opt']['ca']));
						}
						if (isset($pl['opt']['rc'])) {
							$annots .= ' /RC '.$this->_textstring($pl['opt']['rc'], $annot_obj_id);
						}
						$annots .= ' /CreationDate '.$this->_datestring($annot_obj_id, $this->doc_creation_timestamp);
						//$annots .= ' /IRT ';
						if (isset($pl['opt']['subj'])) {
							$annots .= ' /Subj '.$this->_textstring($pl['opt']['subj'], $annot_obj_id);
						}
						//$annots .= ' /RT ';
						//$annots .= ' /IT ';
						//$annots .= ' /ExData ';
					}
					$lineendings = array('Square', 'Circle', 'Diamond', 'OpenArrow', 'ClosedArrow', 'None', 'Butt', 'ROpenArrow', 'RClosedArrow', 'Slash');
					// Annotation types
					switch (strtolower($pl['opt']['subtype'])) {
						case 'text': {
							if (isset($pl['opt']['open'])) {
								$annots .= ' /Open '. (strtolower($pl['opt']['open']) == 'true' ? 'true' : 'false');
							}
							$iconsapp = array('Comment', 'Help', 'Insert', 'Key', 'NewParagraph', 'Note', 'Paragraph');
							if (isset($pl['opt']['name']) AND in_array($pl['opt']['name'], $iconsapp)) {
								$annots .= ' /Name /'.$pl['opt']['name'];
							} else {
								$annots .= ' /Name /Note';
							}
							$statemodels = array('Marked', 'Review');
							if (isset($pl['opt']['statemodel']) AND in_array($pl['opt']['statemodel'], $statemodels)) {
								$annots .= ' /StateModel /'.$pl['opt']['statemodel'];
							} else {
								$pl['opt']['statemodel'] = 'Marked';
								$annots .= ' /StateModel /'.$pl['opt']['statemodel'];
							}
							if ($pl['opt']['statemodel'] == 'Marked') {
								$states = array('Accepted', 'Unmarked');
							} else {
								$states = array('Accepted', 'Rejected', 'Cancelled', 'Completed', 'None');
							}
							if (isset($pl['opt']['state']) AND in_array($pl['opt']['state'], $states)) {
								$annots .= ' /State /'.$pl['opt']['state'];
							} else {
								if ($pl['opt']['statemodel'] == 'Marked') {
									$annots .= ' /State /Unmarked';
								} else {
									$annots .= ' /State /None';
								}
							}
							break;
						}
						case 'link': {
							if (is_string($pl['txt'])) {
								if ($pl['txt'][0] == '#') {
									// internal destination
									$annots .= ' /Dest /'.TCPDF_STATIC::encodeNameObject(substr($pl['txt'], 1));
								} elseif ($pl['txt'][0] == '%') {
									// embedded PDF file
									$filename = basename(substr($pl['txt'], 1));
									$annots .= ' /A << /S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '.($n - 1).' /A '.$this->embeddedfiles[$filename]['a'].' >> >>';
								} elseif ($pl['txt'][0] == '*') {
									// embedded generic file
									$filename = basename(substr($pl['txt'], 1));
									$jsa = 'var D=event.target.doc;var MyData=D.dataObjects;for (var i in MyData) if (MyData[i].path=="'.$filename.'") D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
									$annots .= ' /A << /S /JavaScript /JS '.$this->_textstring($jsa, $annot_obj_id).'>>';
								} else {
									// external URI link
									$annots .= ' /A <</S /URI /URI '.$this->_datastring($this->unhtmlentities($pl['txt']), $annot_obj_id).'>>';
								}
							} elseif (isset($this->links[$pl['txt']])) {
								// internal link ID
								$l = $this->links[$pl['txt']];
								if (isset($this->page_obj_id[($l[0])])) {
									$annots .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $this->page_obj_id[($l[0])], ($this->pagedim[$l[0]]['h'] - ($l[1] * $this->k)));
								}
							}
							$hmodes = array('N', 'I', 'O', 'P');
							if (isset($pl['opt']['h']) AND in_array($pl['opt']['h'], $hmodes)) {
								$annots .= ' /H /'.$pl['opt']['h'];
							} else {
								$annots .= ' /H /I';
							}
							//$annots .= ' /PA ';
							//$annots .= ' /Quadpoints ';
							break;
						}
						case 'freetext': {
							if (isset($pl['opt']['da']) AND !empty($pl['opt']['da'])) {
								$annots .= ' /DA ('.$pl['opt']['da'].')';
							}
							if (isset($pl['opt']['q']) AND ($pl['opt']['q'] >= 0) AND ($pl['opt']['q'] <= 2)) {
								$annots .= ' /Q '.intval($pl['opt']['q']);
							}
							if (isset($pl['opt']['rc'])) {
								$annots .= ' /RC '.$this->_textstring($pl['opt']['rc'], $annot_obj_id);
							}
							if (isset($pl['opt']['ds'])) {
								$annots .= ' /DS '.$this->_textstring($pl['opt']['ds'], $annot_obj_id);
							}
							if (isset($pl['opt']['cl']) AND is_array($pl['opt']['cl'])) {
								$annots .= ' /CL [';
								foreach ($pl['opt']['cl'] as $cl) {
									$annots .= sprintf('%F ', $cl * $this->k);
								}
								$annots .= ']';
							}
							$tfit = array('FreeText', 'FreeTextCallout', 'FreeTextTypeWriter');
							if (isset($pl['opt']['it']) AND in_array($pl['opt']['it'], $tfit)) {
								$annots .= ' /IT /'.$pl['opt']['it'];
							}
							if (isset($pl['opt']['rd']) AND is_array($pl['opt']['rd'])) {
								$l = $pl['opt']['rd'][0] * $this->k;
								$r = $pl['opt']['rd'][1] * $this->k;
								$t = $pl['opt']['rd'][2] * $this->k;
								$b = $pl['opt']['rd'][3] * $this->k;
								$annots .= ' /RD ['.sprintf('%F %F %F %F', $l, $r, $t, $b).']';
							}
							if (isset($pl['opt']['le']) AND in_array($pl['opt']['le'], $lineendings)) {
								$annots .= ' /LE /'.$pl['opt']['le'];
							}
							break;
						}
						case 'line': {
							break;
						}
						case 'square': {
							break;
						}
						case 'circle': {
							break;
						}
						case 'polygon': {
							break;
						}
						case 'polyline': {
							break;
						}
						case 'highlight': {
							break;
						}
						case 'underline': {
							break;
						}
						case 'squiggly': {
							break;
						}
						case 'strikeout': {
							break;
						}
						case 'stamp': {
							break;
						}
						case 'caret': {
							break;
						}
						case 'ink': {
							break;
						}
						case 'popup': {
							break;
						}
						case 'fileattachment': {
							if ($this->pdfa_mode) {
								// embedded files are not allowed in PDF/A mode
								break;
							}
							if (!isset($pl['opt']['fs'])) {
								break;
							}
							$filename = basename($pl['opt']['fs']);
							if (isset($this->embeddedfiles[$filename]['f'])) {
								$annots .= ' /FS '.$this->embeddedfiles[$filename]['f'].' 0 R';
								$iconsapp = array('Graph', 'Paperclip', 'PushPin', 'Tag');
								if (isset($pl['opt']['name']) AND in_array($pl['opt']['name'], $iconsapp)) {
									$annots .= ' /Name /'.$pl['opt']['name'];
								} else {
									$annots .= ' /Name /PushPin';
								}
								// index (zero-based) of the annotation in the Annots array of this page
								$this->embeddedfiles[$filename]['a'] = $key;
							}
							break;
						}
						case 'sound': {
							if (!isset($pl['opt']['fs'])) {
								break;
							}
							$filename = basename($pl['opt']['fs']);
							if (isset($this->embeddedfiles[$filename]['f'])) {
								// ... TO BE COMPLETED ...
								// /R /C /B /E /CO /CP
								$annots .= ' /Sound '.$this->embeddedfiles[$filename]['f'].' 0 R';
								$iconsapp = array('Speaker', 'Mic');
								if (isset($pl['opt']['name']) AND in_array($pl['opt']['name'], $iconsapp)) {
									$annots .= ' /Name /'.$pl['opt']['name'];
								} else {
									$annots .= ' /Name /Speaker';
								}
							}
							break;
						}
						case 'movie': {
							break;
						}
						case 'widget': {
							$hmode = array('N', 'I', 'O', 'P', 'T');
							if (isset($pl['opt']['h']) AND in_array($pl['opt']['h'], $hmode)) {
								$annots .= ' /H /'.$pl['opt']['h'];
							}
							if (isset($pl['opt']['mk']) AND (is_array($pl['opt']['mk'])) AND !empty($pl['opt']['mk'])) {
								$annots .= ' /MK <<';
								if (isset($pl['opt']['mk']['r'])) {
									$annots .= ' /R '.$pl['opt']['mk']['r'];
								}
								if (isset($pl['opt']['mk']['bc']) AND (is_array($pl['opt']['mk']['bc']))) {
									$annots .= ' /BC '.TCPDF_COLORS::getColorStringFromArray($pl['opt']['mk']['bc']);
								}
								if (isset($pl['opt']['mk']['bg']) AND (is_array($pl['opt']['mk']['bg']))) {
									$annots .= ' /BG '.TCPDF_COLORS::getColorStringFromArray($pl['opt']['mk']['bg']);
								}
								if (isset($pl['opt']['mk']['ca'])) {
									$annots .= ' /CA '.$pl['opt']['mk']['ca'];
								}
								if (isset($pl['opt']['mk']['rc'])) {
									$annots .= ' /RC '.$pl['opt']['mk']['rc'];
								}
								if (isset($pl['opt']['mk']['ac'])) {
									$annots .= ' /AC '.$pl['opt']['mk']['ac'];
								}
								if (isset($pl['opt']['mk']['i'])) {
									$info = $this->getImageBuffer($pl['opt']['mk']['i']);
									if ($info !== false) {
										$annots .= ' /I '.$info['n'].' 0 R';
									}
								}
								if (isset($pl['opt']['mk']['ri'])) {
									$info = $this->getImageBuffer($pl['opt']['mk']['ri']);
									if ($info !== false) {
										$annots .= ' /RI '.$info['n'].' 0 R';
									}
								}
								if (isset($pl['opt']['mk']['ix'])) {
									$info = $this->getImageBuffer($pl['opt']['mk']['ix']);
									if ($info !== false) {
										$annots .= ' /IX '.$info['n'].' 0 R';
									}
								}
								if (isset($pl['opt']['mk']['if']) AND (is_array($pl['opt']['mk']['if'])) AND !empty($pl['opt']['mk']['if'])) {
									$annots .= ' /IF <<';
									$if_sw = array('A', 'B', 'S', 'N');
									if (isset($pl['opt']['mk']['if']['sw']) AND in_array($pl['opt']['mk']['if']['sw'], $if_sw)) {
										$annots .= ' /SW /'.$pl['opt']['mk']['if']['sw'];
									}
									$if_s = array('A', 'P');
									if (isset($pl['opt']['mk']['if']['s']) AND in_array($pl['opt']['mk']['if']['s'], $if_s)) {
										$annots .= ' /S /'.$pl['opt']['mk']['if']['s'];
									}
									if (isset($pl['opt']['mk']['if']['a']) AND (is_array($pl['opt']['mk']['if']['a'])) AND !empty($pl['opt']['mk']['if']['a'])) {
										$annots .= sprintf(' /A [%F %F]', $pl['opt']['mk']['if']['a'][0], $pl['opt']['mk']['if']['a'][1]);
									}
									if (isset($pl['opt']['mk']['if']['fb']) AND ($pl['opt']['mk']['if']['fb'])) {
										$annots .= ' /FB true';
									}
									$annots .= '>>';
								}
								if (isset($pl['opt']['mk']['tp']) AND ($pl['opt']['mk']['tp'] >= 0) AND ($pl['opt']['mk']['tp'] <= 6)) {
									$annots .= ' /TP '.intval($pl['opt']['mk']['tp']);
								}
								$annots .= '>>';
							} // end MK
							// --- Entries for field dictionaries ---
							if (isset($this->radiobutton_groups[$n][$pl['txt']])) {
								// set parent
								$annots .= ' /Parent '.$this->radiobutton_groups[$n][$pl['txt']].' 0 R';
							}
							if (isset($pl['opt']['t']) AND is_string($pl['opt']['t'])) {
								$annots .= ' /T '.$this->_datastring($pl['opt']['t'], $annot_obj_id);
							}
							if (isset($pl['opt']['tu']) AND is_string($pl['opt']['tu'])) {
								$annots .= ' /TU '.$this->_datastring($pl['opt']['tu'], $annot_obj_id);
							}
							if (isset($pl['opt']['tm']) AND is_string($pl['opt']['tm'])) {
								$annots .= ' /TM '.$this->_datastring($pl['opt']['tm'], $annot_obj_id);
							}
							if (isset($pl['opt']['ff'])) {
								if (is_array($pl['opt']['ff'])) {
									// array of bit settings
									$flag = 0;
									foreach($pl['opt']['ff'] as $val) {
										$flag += 1 << ($val - 1);
									}
								} else {
									$flag = intval($pl['opt']['ff']);
								}
								$annots .= ' /Ff '.$flag;
							}
							if (isset($pl['opt']['maxlen'])) {
								$annots .= ' /MaxLen '.intval($pl['opt']['maxlen']);
							}
							if (isset($pl['opt']['v'])) {
								$annots .= ' /V';
								if (is_array($pl['opt']['v'])) {
									foreach ($pl['opt']['v'] AS $optval) {
										if (is_float($optval)) {
											$optval = sprintf('%F', $optval);
										}
										$annots .= ' '.$optval;
									}
								} else {
									$annots .= ' '.$this->_textstring($pl['opt']['v'], $annot_obj_id);
								}
							}
							if (isset($pl['opt']['dv'])) {
								$annots .= ' /DV';
								if (is_array($pl['opt']['dv'])) {
									foreach ($pl['opt']['dv'] AS $optval) {
										if (is_float($optval)) {
											$optval = sprintf('%F', $optval);
										}
										$annots .= ' '.$optval;
									}
								} else {
									$annots .= ' '.$this->_textstring($pl['opt']['dv'], $annot_obj_id);
								}
							}
							if (isset($pl['opt']['rv'])) {
								$annots .= ' /RV';
								if (is_array($pl['opt']['rv'])) {
									foreach ($pl['opt']['rv'] AS $optval) {
										if (is_float($optval)) {
											$optval = sprintf('%F', $optval);
										}
										$annots .= ' '.$optval;
									}
								} else {
									$annots .= ' '.$this->_textstring($pl['opt']['rv'], $annot_obj_id);
								}
							}
							if (isset($pl['opt']['a']) AND !empty($pl['opt']['a'])) {
								$annots .= ' /A << '.$pl['opt']['a'].' >>';
							}
							if (isset($pl['opt']['aa']) AND !empty($pl['opt']['aa'])) {
								$annots .= ' /AA << '.$pl['opt']['aa'].' >>';
							}
							if (isset($pl['opt']['da']) AND !empty($pl['opt']['da'])) {
								$annots .= ' /DA ('.$pl['opt']['da'].')';
							}
							if (isset($pl['opt']['q']) AND ($pl['opt']['q'] >= 0) AND ($pl['opt']['q'] <= 2)) {
								$annots .= ' /Q '.intval($pl['opt']['q']);
							}
							if (isset($pl['opt']['opt']) AND (is_array($pl['opt']['opt'])) AND !empty($pl['opt']['opt'])) {
								$annots .= ' /Opt [';
								foreach($pl['opt']['opt'] AS $copt) {
									if (is_array($copt)) {
										$annots .= ' ['.$this->_textstring($copt[0], $annot_obj_id).' '.$this->_textstring($copt[1], $annot_obj_id).']';
									} else {
										$annots .= ' '.$this->_textstring($copt, $annot_obj_id);
									}
								}
								$annots .= ']';
							}
							if (isset($pl['opt']['ti'])) {
								$annots .= ' /TI '.intval($pl['opt']['ti']);
							}
							if (isset($pl['opt']['i']) AND (is_array($pl['opt']['i'])) AND !empty($pl['opt']['i'])) {
								$annots .= ' /I [';
								foreach($pl['opt']['i'] AS $copt) {
									$annots .= intval($copt).' ';
								}
								$annots .= ']';
							}
							break;
						}
						case 'screen': {
							break;
						}
						case 'printermark': {
							break;
						}
						case 'trapnet': {
							break;
						}
						case 'watermark': {
							break;
						}
						case '3d': {
							break;
						}
						default: {
							break;
						}
					}
					$annots .= '>>';
					// create new annotation object
					$this->_out($this->_getobj($annot_obj_id)."\n".$annots."\n".'endobj');
					if ($formfield AND !isset($this->radiobutton_groups[$n][$pl['txt']])) {
						// store reference of form object
						$this->form_obj_id[] = $annot_obj_id;
					}
				}
			}
		} // end for each page
	}

	/**
	 * Put appearance streams XObject used to define annotation's appearance states.
	 * @param $w (int) annotation width
	 * @param $h (int) annotation height
	 * @param $stream (string) appearance stream
	 * @return int object ID
	 * @protected
	 * @since 4.8.001 (2009-09-09)
	 */
	protected function _putAPXObject($w=0, $h=0, $stream='') {
		$stream = trim($stream);
		$out = $this->_getobj()."\n";
		$this->xobjects['AX'.$this->n] = array('n' => $this->n);
		$out .= '<<';
		$out .= ' /Type /XObject';
		$out .= ' /Subtype /Form';
		$out .= ' /FormType 1';
		if ($this->compress) {
			$stream = gzcompress($stream);
			$out .= ' /Filter /FlateDecode';
		}
		$rect = sprintf('%F %F', $w, $h);
		$out .= ' /BBox [0 0 '.$rect.']';
		$out .= ' /Matrix [1 0 0 1 0 0]';
		$out .= ' /Resources 2 0 R';
		$stream = $this->_getrawstream($stream);
		$out .= ' /Length '.strlen($stream);
		$out .= ' >>';
		$out .= ' stream'."\n".$stream."\n".'endstream';
		$out .= "\n".'endobj';
		$this->_out($out);
		return $this->n;
	}

	/**
	 * Output fonts.
	 * @author Nicola Asuni
	 * @protected
	 */
	protected function _putfonts() {
		$nf = $this->n;
		foreach ($this->diffs as $diff) {
			//Encodings
			$this->_newobj();
			$this->_out('<< /Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.'] >>'."\n".'endobj');
		}
		$mqr = TCPDF_STATIC::get_mqr();
		TCPDF_STATIC::set_mqr(false);
		foreach ($this->FontFiles as $file => $info) {
			// search and get font file to embedd
			$fontfile = TCPDF_FONTS::getFontFullPath($file, $info['fontdir']);
			if (!TCPDF_STATIC::empty_string($fontfile)) {
				$font = file_get_contents($fontfile);
				$compressed = (substr($file, -2) == '.z');
				if ((!$compressed) AND (isset($info['length2']))) {
					$header = (ord($font{0}) == 128);
					if ($header) {
						// strip first binary header
						$font = substr($font, 6);
					}
					if ($header AND (ord($font[$info['length1']]) == 128)) {
						// strip second binary header
						$font = substr($font, 0, $info['length1']).substr($font, ($info['length1'] + 6));
					}
				} elseif ($info['subset'] AND ((!$compressed) OR ($compressed AND function_exists('gzcompress')))) {
					if ($compressed) {
						// uncompress font
						$font = gzuncompress($font);
					}
					// merge subset characters
					$subsetchars = array(); // used chars
					foreach ($info['fontkeys'] as $fontkey) {
						$fontinfo = $this->getFontBuffer($fontkey);
						$subsetchars += $fontinfo['subsetchars'];
					}
					// rebuild a font subset
					$font = TCPDF_FONTS::_getTrueTypeFontSubset($font, $subsetchars);
					// calculate new font length
					$info['length1'] = strlen($font);
					if ($compressed) {
						// recompress font
						$font = gzcompress($font);
					}
				}
				$this->_newobj();
				$this->FontFiles[$file]['n'] = $this->n;
				$stream = $this->_getrawstream($font);
				$out = '<< /Length '.strlen($stream);
				if ($compressed) {
					$out .= ' /Filter /FlateDecode';
				}
				$out .= ' /Length1 '.$info['length1'];
				if (isset($info['length2'])) {
					$out .= ' /Length2 '.$info['length2'].' /Length3 0';
				}
				$out .= ' >>';
				$out .= ' stream'."\n".$stream."\n".'endstream';
				$out .= "\n".'endobj';
				$this->_out($out);
			}
		}
		TCPDF_STATIC::set_mqr($mqr);
		foreach ($this->fontkeys as $k) {
			//Font objects
			$font = $this->getFontBuffer($k);
			$type = $font['type'];
			$name = $font['name'];
			if ($type == 'core') {
				// standard core font
				$out = $this->_getobj($this->font_obj_ids[$k])."\n";
				$out .= '<</Type /Font';
				$out .= ' /Subtype /Type1';
				$out .= ' /BaseFont /'.$name;
				$out .= ' /Name /F'.$font['i'];
				if ((strtolower($name) != 'symbol') AND (strtolower($name) != 'zapfdingbats')) {
					$out .= ' /Encoding /WinAnsiEncoding';
				}
				if ($k == 'helvetica') {
					// add default font for annotations
					$this->annotation_fonts[$k] = $font['i'];
				}
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
			} elseif (($type == 'Type1') OR ($type == 'TrueType')) {
				// additional Type1 or TrueType font
				$out = $this->_getobj($this->font_obj_ids[$k])."\n";
				$out .= '<</Type /Font';
				$out .= ' /Subtype /'.$type;
				$out .= ' /BaseFont /'.$name;
				$out .= ' /Name /F'.$font['i'];
				$out .= ' /FirstChar 32 /LastChar 255';
				$out .= ' /Widths '.($this->n + 1).' 0 R';
				$out .= ' /FontDescriptor '.($this->n + 2).' 0 R';
				if ($font['enc']) {
					if (isset($font['diff'])) {
						$out .= ' /Encoding '.($nf + $font['diff']).' 0 R';
					} else {
						$out .= ' /Encoding /WinAnsiEncoding';
					}
				}
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
				// Widths
				$this->_newobj();
				$s = '[';
				for ($i = 32; $i < 256; ++$i) {
					if (isset($font['cw'][$i])) {
						$s .= $font['cw'][$i].' ';
					} else {
						$s .= $font['dw'].' ';
					}
				}
				$s .= ']';
				$s .= "\n".'endobj';
				$this->_out($s);
				//Descriptor
				$this->_newobj();
				$s = '<</Type /FontDescriptor /FontName /'.$name;
				foreach ($font['desc'] as $fdk => $fdv) {
					if (is_float($fdv)) {
						$fdv = sprintf('%F', $fdv);
					}
					$s .= ' /'.$fdk.' '.$fdv.'';
				}
				if (!TCPDF_STATIC::empty_string($font['file'])) {
					$s .= ' /FontFile'.($type == 'Type1' ? '' : '2').' '.$this->FontFiles[$font['file']]['n'].' 0 R';
				}
				$s .= '>>';
				$s .= "\n".'endobj';
				$this->_out($s);
			} else {
				// additional types
				$mtd = '_put'.strtolower($type);
				if (!method_exists($this, $mtd)) {
					$this->Error('Unsupported font type: '.$type);
				}
				$this->$mtd($font);
			}
		}
	}

	/**
	 * Adds unicode fonts.<br>
	 * Based on PDF Reference 1.3 (section 5)
	 * @param $font (array) font data
	 * @protected
	 * @author Nicola Asuni
	 * @since 1.52.0.TC005 (2005-01-05)
	 */
	protected function _puttruetypeunicode($font) {
		$fontname = '';
		if ($font['subset']) {
			// change name for font subsetting
			$subtag = sprintf('%06u', $font['i']);
			$subtag = strtr($subtag, '0123456789', 'ABCDEFGHIJ');
			$fontname .= $subtag.'+';
		}
		$fontname .= $font['name'];
		// Type0 Font
		// A composite font composed of other fonts, organized hierarchically
		$out = $this->_getobj($this->font_obj_ids[$font['fontkey']])."\n";
		$out .= '<< /Type /Font';
		$out .= ' /Subtype /Type0';
		$out .= ' /BaseFont /'.$fontname;
		$out .= ' /Name /F'.$font['i'];
		$out .= ' /Encoding /'.$font['enc'];
		$out .= ' /ToUnicode '.($this->n + 1).' 0 R';
		$out .= ' /DescendantFonts ['.($this->n + 2).' 0 R]';
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		// ToUnicode map for Identity-H
		$stream = TCPDF_FONT_DATA::$uni_identity_h;
		// ToUnicode Object
		$this->_newobj();
		$stream = ($this->compress) ? gzcompress($stream) : $stream;
		$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
		$stream = $this->_getrawstream($stream);
		$this->_out('<<'.$filter.'/Length '.strlen($stream).'>> stream'."\n".$stream."\n".'endstream'."\n".'endobj');
		// CIDFontType2
		// A CIDFont whose glyph descriptions are based on TrueType font technology
		$oid = $this->_newobj();
		$out = '<< /Type /Font';
		$out .= ' /Subtype /CIDFontType2';
		$out .= ' /BaseFont /'.$fontname;
		// A dictionary containing entries that define the character collection of the CIDFont.
		$cidinfo = '/Registry '.$this->_datastring($font['cidinfo']['Registry'], $oid);
		$cidinfo .= ' /Ordering '.$this->_datastring($font['cidinfo']['Ordering'], $oid);
		$cidinfo .= ' /Supplement '.$font['cidinfo']['Supplement'];
		$out .= ' /CIDSystemInfo << '.$cidinfo.' >>';
		$out .= ' /FontDescriptor '.($this->n + 1).' 0 R';
		$out .= ' /DW '.$font['dw']; // default width
		$out .= "\n".TCPDF_FONTS::_putfontwidths($font, 0);
		if (isset($font['ctg']) AND (!TCPDF_STATIC::empty_string($font['ctg']))) {
			$out .= "\n".'/CIDToGIDMap '.($this->n + 2).' 0 R';
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		// Font descriptor
		// A font descriptor describing the CIDFont default metrics other than its glyph widths
		$this->_newobj();
		$out = '<< /Type /FontDescriptor';
		$out .= ' /FontName /'.$fontname;
		foreach ($font['desc'] as $key => $value) {
			if (is_float($value)) {
				$value = sprintf('%F', $value);
			}
			$out .= ' /'.$key.' '.$value;
		}
		$fontdir = false;
		if (!TCPDF_STATIC::empty_string($font['file'])) {
			// A stream containing a TrueType font
			$out .= ' /FontFile2 '.$this->FontFiles[$font['file']]['n'].' 0 R';
			$fontdir = $this->FontFiles[$font['file']]['fontdir'];
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		if (isset($font['ctg']) AND (!TCPDF_STATIC::empty_string($font['ctg']))) {
			$this->_newobj();
			// Embed CIDToGIDMap
			// A specification of the mapping from CIDs to glyph indices
			// search and get CTG font file to embedd
			$ctgfile = strtolower($font['ctg']);
			// search and get ctg font file to embedd
			$fontfile = TCPDF_FONTS::getFontFullPath($ctgfile, $fontdir);
			if (TCPDF_STATIC::empty_string($fontfile)) {
				$this->Error('Font file not found: '.$ctgfile);
			}
			$stream = $this->_getrawstream(file_get_contents($fontfile));
			$out = '<< /Length '.strlen($stream).'';
			if (substr($fontfile, -2) == '.z') { // check file extension
				// Decompresses data encoded using the public-domain
				// zlib/deflate compression method, reproducing the
				// original text or binary data
				$out .= ' /Filter /FlateDecode';
			}
			$out .= ' >>';
			$out .= ' stream'."\n".$stream."\n".'endstream';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
	}

	/**
	 * Output CID-0 fonts.
	 * A Type 0 CIDFont contains glyph descriptions based on the Adobe Type 1 font format
	 * @param $font (array) font data
	 * @protected
	 * @author Andrew Whitehead, Nicola Asuni, Yukihiro Nakadaira
	 * @since 3.2.000 (2008-06-23)
	 */
	protected function _putcidfont0($font) {
		$cidoffset = 0;
		if (!isset($font['cw'][1])) {
			$cidoffset = 31;
		}
		if (isset($font['cidinfo']['uni2cid'])) {
			// convert unicode to cid.
			$uni2cid = $font['cidinfo']['uni2cid'];
			$cw = array();
			foreach ($font['cw'] as $uni => $width) {
				if (isset($uni2cid[$uni])) {
					$cw[($uni2cid[$uni] + $cidoffset)] = $width;
				} elseif ($uni < 256) {
					$cw[$uni] = $width;
				} // else unknown character
			}
			$font = array_merge($font, array('cw' => $cw));
		}
		$name = $font['name'];
		$enc = $font['enc'];
		if ($enc) {
			$longname = $name.'-'.$enc;
		} else {
			$longname = $name;
		}
		$out = $this->_getobj($this->font_obj_ids[$font['fontkey']])."\n";
		$out .= '<</Type /Font';
		$out .= ' /Subtype /Type0';
		$out .= ' /BaseFont /'.$longname;
		$out .= ' /Name /F'.$font['i'];
		if ($enc) {
			$out .= ' /Encoding /'.$enc;
		}
		$out .= ' /DescendantFonts ['.($this->n + 1).' 0 R]';
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		$oid = $this->_newobj();
		$out = '<</Type /Font';
		$out .= ' /Subtype /CIDFontType0';
		$out .= ' /BaseFont /'.$name;
		$cidinfo = '/Registry '.$this->_datastring($font['cidinfo']['Registry'], $oid);
		$cidinfo .= ' /Ordering '.$this->_datastring($font['cidinfo']['Ordering'], $oid);
		$cidinfo .= ' /Supplement '.$font['cidinfo']['Supplement'];
		$out .= ' /CIDSystemInfo <<'.$cidinfo.'>>';
		$out .= ' /FontDescriptor '.($this->n + 1).' 0 R';
		$out .= ' /DW '.$font['dw'];
		$out .= "\n".TCPDF_FONTS::_putfontwidths($font, $cidoffset);
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		$this->_newobj();
		$s = '<</Type /FontDescriptor /FontName /'.$name;
		foreach ($font['desc'] as $k => $v) {
			if ($k != 'Style') {
				if (is_float($v)) {
					$v = sprintf('%F', $v);
				}
				$s .= ' /'.$k.' '.$v.'';
			}
		}
		$s .= '>>';
		$s .= "\n".'endobj';
		$this->_out($s);
	}

	/**
	 * Output images.
	 * @protected
	 */
	protected function _putimages() {
		$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
		foreach ($this->imagekeys as $file) {
			$info = $this->getImageBuffer($file);
			// set object for alternate images array
			if ((!$this->pdfa_mode) AND isset($info['altimgs']) AND !empty($info['altimgs'])) {
				$altoid = $this->_newobj();
				$out = '[';
				foreach ($info['altimgs'] as $altimage) {
					if (isset($this->xobjects['I'.$altimage[0]]['n'])) {
						$out .= ' << /Image '.$this->xobjects['I'.$altimage[0]]['n'].' 0 R';
						$out .= ' /DefaultForPrinting';
						if ($altimage[1] === true) {
							$out .= ' true';
						} else {
							$out .= ' false';
						}
						$out .= ' >>';
					}
				}
				$out .= ' ]';
				$out .= "\n".'endobj';
				$this->_out($out);
			}
			// set image object
			$oid = $this->_newobj();
			$this->xobjects['I'.$info['i']] = array('n' => $oid);
			$this->setImageSubBuffer($file, 'n', $this->n);
			$out = '<</Type /XObject';
			$out .= ' /Subtype /Image';
			$out .= ' /Width '.$info['w'];
			$out .= ' /Height '.$info['h'];
			if (array_key_exists('masked', $info)) {
				$out .= ' /SMask '.($this->n - 1).' 0 R';
			}
			// set color space
			$icc = false;
			if (isset($info['icc']) AND ($info['icc'] !== false)) {
				// ICC Colour Space
				$icc = true;
				$out .= ' /ColorSpace [/ICCBased '.($this->n + 1).' 0 R]';
			} elseif ($info['cs'] == 'Indexed') {
				// Indexed Colour Space
				$out .= ' /ColorSpace [/Indexed /DeviceRGB '.((strlen($info['pal']) / 3) - 1).' '.($this->n + 1).' 0 R]';
			} else {
				// Device Colour Space
				$out .= ' /ColorSpace /'.$info['cs'];
			}
			if ($info['cs'] == 'DeviceCMYK') {
				$out .= ' /Decode [1 0 1 0 1 0 1 0]';
			}
			$out .= ' /BitsPerComponent '.$info['bpc'];
			if (isset($altoid) AND ($altoid > 0)) {
				// reference to alternate images dictionary
				$out .= ' /Alternates '.$altoid.' 0 R';
			}
			if (isset($info['exurl']) AND !empty($info['exurl'])) {
				// external stream
				$out .= ' /Length 0';
				$out .= ' /F << /FS /URL /F '.$this->_datastring($info['exurl'], $oid).' >>';
				if (isset($info['f'])) {
					$out .= ' /FFilter /'.$info['f'];
				}
				$out .= ' >>';
				$out .= ' stream'."\n".'endstream';
			} else {
				if (isset($info['f'])) {
					$out .= ' /Filter /'.$info['f'];
				}
				if (isset($info['parms'])) {
					$out .= ' '.$info['parms'];
				}
				if (isset($info['trns']) AND is_array($info['trns'])) {
					$trns = '';
					$count_info = count($info['trns']);
					for ($i=0; $i < $count_info; ++$i) {
						$trns .= $info['trns'][$i].' '.$info['trns'][$i].' ';
					}
					$out .= ' /Mask ['.$trns.']';
				}
				$stream = $this->_getrawstream($info['data']);
				$out .= ' /Length '.strlen($stream).' >>';
				$out .= ' stream'."\n".$stream."\n".'endstream';
			}
			$out .= "\n".'endobj';
			$this->_out($out);
			if ($icc) {
				// ICC colour profile
				$this->_newobj();
				$icc = ($this->compress) ? gzcompress($info['icc']) : $info['icc'];
				$icc = $this->_getrawstream($icc);
				$this->_out('<</N '.$info['ch'].' /Alternate /'.$info['cs'].' '.$filter.'/Length '.strlen($icc).'>> stream'."\n".$icc."\n".'endstream'."\n".'endobj');
			} elseif ($info['cs'] == 'Indexed') {
				// colour palette
				$this->_newobj();
				$pal = ($this->compress) ? gzcompress($info['pal']) : $info['pal'];
				$pal = $this->_getrawstream($pal);
				$this->_out('<<'.$filter.'/Length '.strlen($pal).'>> stream'."\n".$pal."\n".'endstream'."\n".'endobj');
			}
		}
	}

	/**
	 * Output Form XObjects Templates.
	 * @author Nicola Asuni
	 * @since 5.8.017 (2010-08-24)
	 * @protected
	 * @see startTemplate(), endTemplate(), printTemplate()
	 */
	protected function _putxobjects() {
		foreach ($this->xobjects as $key => $data) {
			if (isset($data['outdata'])) {
				$stream = str_replace($this->epsmarker, '', trim($data['outdata']));
				$out = $this->_getobj($data['n'])."\n";
				$out .= '<<';
				$out .= ' /Type /XObject';
				$out .= ' /Subtype /Form';
				$out .= ' /FormType 1';
				if ($this->compress) {
					$stream = gzcompress($stream);
					$out .= ' /Filter /FlateDecode';
				}
				$out .= sprintf(' /BBox [%F %F %F %F]', ($data['x'] * $this->k), (-$data['y'] * $this->k), (($data['w'] + $data['x']) * $this->k), (($data['h'] - $data['y']) * $this->k));
				$out .= ' /Matrix [1 0 0 1 0 0]';
				$out .= ' /Resources <<';
				$out .= ' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';
				if (!$this->pdfa_mode) {
					// transparency
					if (isset($data['extgstates']) AND !empty($data['extgstates'])) {
						$out .= ' /ExtGState <<';
						foreach ($data['extgstates'] as $k => $extgstate) {
							if (isset($this->extgstates[$k]['name'])) {
								$out .= ' /'.$this->extgstates[$k]['name'];
							} else {
								$out .= ' /GS'.$k;
							}
							$out .= ' '.$this->extgstates[$k]['n'].' 0 R';
						}
						$out .= ' >>';
					}
					if (isset($data['gradients']) AND !empty($data['gradients'])) {
						$gp = '';
						$gs = '';
						foreach ($data['gradients'] as $id => $grad) {
							// gradient patterns
							$gp .= ' /p'.$id.' '.$this->gradients[$id]['pattern'].' 0 R';
							// gradient shadings
							$gs .= ' /Sh'.$id.' '.$this->gradients[$id]['id'].' 0 R';
						}
						$out .= ' /Pattern <<'.$gp.' >>';
						$out .= ' /Shading <<'.$gs.' >>';
					}
				}
				// spot colors
				if (isset($data['spot_colors']) AND !empty($data['spot_colors'])) {
					$out .= ' /ColorSpace <<';
					foreach ($data['spot_colors'] as $name => $color) {
						$out .= ' /CS'.$color['i'].' '.$this->spot_colors[$name]['n'].' 0 R';
					}
					$out .= ' >>';
				}
				// fonts
				if (!empty($data['fonts'])) {
					$out .= ' /Font <<';
					foreach ($data['fonts'] as $fontkey => $fontid) {
						$out .= ' /F'.$fontid.' '.$this->font_obj_ids[$fontkey].' 0 R';
					}
					$out .= ' >>';
				}
				// images or nested xobjects
				if (!empty($data['images']) OR !empty($data['xobjects'])) {
					$out .= ' /XObject <<';
					foreach ($data['images'] as $imgid) {
						$out .= ' /I'.$imgid.' '.$this->xobjects['I'.$imgid]['n'].' 0 R';
					}
					foreach ($data['xobjects'] as $sub_id => $sub_objid) {
						$out .= ' /'.$sub_id.' '.$sub_objid['n'].' 0 R';
					}
					$out .= ' >>';
				}
				$out .= ' >>'; //end resources
				if (isset($data['group']) AND ($data['group'] !== false)) {
					// set transparency group
					$out .= ' /Group << /Type /Group /S /Transparency';
					if (is_array($data['group'])) {
						if (isset($data['group']['CS']) AND !empty($data['group']['CS'])) {
							$out .= ' /CS /'.$data['group']['CS'];
						}
						if (isset($data['group']['I'])) {
							$out .= ' /I /'.($data['group']['I']===true?'true':'false');
						}
						if (isset($data['group']['K'])) {
							$out .= ' /K /'.($data['group']['K']===true?'true':'false');
						}
					}
					$out .= ' >>';
				}
				$stream = $this->_getrawstream($stream, $data['n']);
				$out .= ' /Length '.strlen($stream);
				$out .= ' >>';
				$out .= ' stream'."\n".$stream."\n".'endstream';
				$out .= "\n".'endobj';
				$this->_out($out);
			}
		}
	}

	/**
	 * Output Spot Colors Resources.
	 * @protected
	 * @since 4.0.024 (2008-09-12)
	 */
	protected function _putspotcolors() {
		foreach ($this->spot_colors as $name => $color) {
			$this->_newobj();
			$this->spot_colors[$name]['n'] = $this->n;
			$out = '[/Separation /'.str_replace(' ', '#20', $name);
			$out .= ' /DeviceCMYK <<';
			$out .= ' /Range [0 1 0 1 0 1 0 1] /C0 [0 0 0 0]';
			$out .= ' '.sprintf('/C1 [%F %F %F %F] ', ($color['C'] / 100), ($color['M'] / 100), ($color['Y'] / 100), ($color['K'] / 100));
			$out .= ' /FunctionType 2 /Domain [0 1] /N 1>>]';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
	}

	/**
	 * Return XObjects Dictionary.
	 * @return string XObjects dictionary
	 * @protected
	 * @since 5.8.014 (2010-08-23)
	 */
	protected function _getxobjectdict() {
		$out = '';
		foreach ($this->xobjects as $id => $objid) {
			$out .= ' /'.$id.' '.$objid['n'].' 0 R';
		}
		return $out;
	}

	/**
	 * Output Resources Dictionary.
	 * @protected
	 */
	protected function _putresourcedict() {
		$out = $this->_getobj(2)."\n";
		$out .= '<< /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';
		$out .= ' /Font <<';
		foreach ($this->fontkeys as $fontkey) {
			$font = $this->getFontBuffer($fontkey);
			$out .= ' /F'.$font['i'].' '.$font['n'].' 0 R';
		}
		$out .= ' >>';
		$out .= ' /XObject <<';
		$out .= $this->_getxobjectdict();
		$out .= ' >>';
		// layers
		if (!empty($this->pdflayers)) {
			$out .= ' /Properties <<';
			foreach ($this->pdflayers as $layer) {
				$out .= ' /'.$layer['layer'].' '.$layer['objid'].' 0 R';
			}
			$out .= ' >>';
		}
		if (!$this->pdfa_mode) {
			// transparency
			if (isset($this->extgstates) AND !empty($this->extgstates)) {
				$out .= ' /ExtGState <<';
				foreach ($this->extgstates as $k => $extgstate) {
					if (isset($extgstate['name'])) {
						$out .= ' /'.$extgstate['name'];
					} else {
						$out .= ' /GS'.$k;
					}
					$out .= ' '.$extgstate['n'].' 0 R';
				}
				$out .= ' >>';
			}
			if (isset($this->gradients) AND !empty($this->gradients)) {
				$gp = '';
				$gs = '';
				foreach ($this->gradients as $id => $grad) {
					// gradient patterns
					$gp .= ' /p'.$id.' '.$grad['pattern'].' 0 R';
					// gradient shadings
					$gs .= ' /Sh'.$id.' '.$grad['id'].' 0 R';
				}
				$out .= ' /Pattern <<'.$gp.' >>';
				$out .= ' /Shading <<'.$gs.' >>';
			}
		}
		// spot colors
		if (isset($this->spot_colors) AND !empty($this->spot_colors)) {
			$out .= ' /ColorSpace <<';
			foreach ($this->spot_colors as $color) {
				$out .= ' /CS'.$color['i'].' '.$color['n'].' 0 R';
			}
			$out .= ' >>';
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}

	/**
	 * Output Resources.
	 * @protected
	 */
	protected function _putresources() {
		$this->_putextgstates();
		$this->_putocg();
		$this->_putfonts();
		$this->_putimages();
		$this->_putspotcolors();
		$this->_putshaders();
		$this->_putxobjects();
		$this->_putresourcedict();
		$this->_putdests();
		$this->_putEmbeddedFiles();
		$this->_putannotsobjs();
		$this->_putjavascript();
		$this->_putbookmarks();
		$this->_putencryption();
	}

	/**
	 * Adds some Metadata information (Document Information Dictionary)
	 * (see Chapter 14.3.3 Document Information Dictionary of PDF32000_2008.pdf Reference)
	 * @return int object id
	 * @protected
	 */
	protected function _putinfo() {
		$oid = $this->_newobj();
		$out = '<<';
		// store current isunicode value
		$prev_isunicode = $this->isunicode;
		if ($this->docinfounicode) {
			$this->isunicode = true;
		}
		if (!TCPDF_STATIC::empty_string($this->title)) {
			// The document's title.
			$out .= ' /Title '.$this->_textstring($this->title, $oid);
		}
		if (!TCPDF_STATIC::empty_string($this->author)) {
			// The name of the person who created the document.
			$out .= ' /Author '.$this->_textstring($this->author, $oid);
		}
		if (!TCPDF_STATIC::empty_string($this->subject)) {
			// The subject of the document.
			$out .= ' /Subject '.$this->_textstring($this->subject, $oid);
		}
		if (!TCPDF_STATIC::empty_string($this->keywords)) {
			// Keywords associated with the document.
			$out .= ' /Keywords '.$this->_textstring($this->keywords, $oid);
		}
		if (!TCPDF_STATIC::empty_string($this->creator)) {
			// If the document was converted to PDF from another format, the name of the conforming product that created the original document from which it was converted.
			$out .= ' /Creator '.$this->_textstring($this->creator, $oid);
		}
		// restore previous isunicode value
		$this->isunicode = $prev_isunicode;
		// default producer
		$out .= ' /Producer '.$this->_textstring(TCPDF_STATIC::getTCPDFProducer(), $oid);
		// The date and time the document was created, in human-readable form
		$out .= ' /CreationDate '.$this->_datestring(0, $this->doc_creation_timestamp);
		// The date and time the document was most recently modified, in human-readable form
		$out .= ' /ModDate '.$this->_datestring(0, $this->doc_modification_timestamp);
		// A name object indicating whether the document has been modified to include trapping information
		$out .= ' /Trapped /False';
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		return $oid;
	}

	/**
	 * Set additional XMP data to be added on the default XMP data just before the end of "x:xmpmeta" tag.
	 * IMPORTANT: This data is added as-is without controls, so you have to validate your data before using this method!
	 * @param $xmp (string) Custom XMP data.
	 * @since 5.9.128 (2011-10-06)
	 * @public
	 */
	public function setExtraXMP($xmp) {
		$this->custom_xmp = $xmp;
	}

	/**
	 * Put XMP data object and return ID.
	 * @return (int) The object ID.
	 * @since 5.9.121 (2011-09-28)
	 * @protected
	 */
	protected function _putXMP() {
		$oid = $this->_newobj();
		// store current isunicode value
		$prev_isunicode = $this->isunicode;
		$this->isunicode = true;
		$prev_encrypted = $this->encrypted;
		$this->encrypted = false;
		// set XMP data
		$xmp = '<?xpacket begin="'.TCPDF_FONTS::unichr(0xfeff, $this->isunicode).'" id="W5M0MpCehiHzreSzNTczkc9d"?>'."\n";
		$xmp .= '<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 4.2.1-c043 52.372728, 2009/01/18-15:08:04">'."\n";
		$xmp .= "\t".'<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'."\n";
		$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
		$xmp .= "\t\t\t".'<dc:format>application/pdf</dc:format>'."\n";
		$xmp .= "\t\t\t".'<dc:title>'."\n";
		$xmp .= "\t\t\t\t".'<rdf:Alt>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li xml:lang="x-default">'.TCPDF_STATIC::_escapeXML($this->title).'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t".'</rdf:Alt>'."\n";
		$xmp .= "\t\t\t".'</dc:title>'."\n";
		$xmp .= "\t\t\t".'<dc:creator>'."\n";
		$xmp .= "\t\t\t\t".'<rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li>'.TCPDF_STATIC::_escapeXML($this->author).'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t".'</rdf:Seq>'."\n";
		$xmp .= "\t\t\t".'</dc:creator>'."\n";
		$xmp .= "\t\t\t".'<dc:description>'."\n";
		$xmp .= "\t\t\t\t".'<rdf:Alt>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li xml:lang="x-default">'.TCPDF_STATIC::_escapeXML($this->subject).'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t".'</rdf:Alt>'."\n";
		$xmp .= "\t\t\t".'</dc:description>'."\n";
		$xmp .= "\t\t\t".'<dc:subject>'."\n";
		$xmp .= "\t\t\t\t".'<rdf:Bag>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li>'.TCPDF_STATIC::_escapeXML($this->keywords).'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t".'</rdf:Bag>'."\n";
		$xmp .= "\t\t\t".'</dc:subject>'."\n";
		$xmp .= "\t\t".'</rdf:Description>'."\n";
		// convert doc creation date format
		$dcdate = TCPDF_STATIC::getFormattedDate($this->doc_creation_timestamp);
		$doccreationdate = substr($dcdate, 0, 4).'-'.substr($dcdate, 4, 2).'-'.substr($dcdate, 6, 2);
		$doccreationdate .= 'T'.substr($dcdate, 8, 2).':'.substr($dcdate, 10, 2).':'.substr($dcdate, 12, 2);
		$doccreationdate .= '+'.substr($dcdate, 15, 2).':'.substr($dcdate, 18, 2);
		$doccreationdate = TCPDF_STATIC::_escapeXML($doccreationdate);
		// convert doc modification date format
		$dmdate = TCPDF_STATIC::getFormattedDate($this->doc_modification_timestamp);
		$docmoddate = substr($dmdate, 0, 4).'-'.substr($dmdate, 4, 2).'-'.substr($dmdate, 6, 2);
		$docmoddate .= 'T'.substr($dmdate, 8, 2).':'.substr($dmdate, 10, 2).':'.substr($dmdate, 12, 2);
		$docmoddate .= '+'.substr($dmdate, 15, 2).':'.substr($dmdate, 18, 2);
		$docmoddate = TCPDF_STATIC::_escapeXML($docmoddate);
		$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:xmp="http://ns.adobe.com/xap/1.0/">'."\n";
		$xmp .= "\t\t\t".'<xmp:CreateDate>'.$doccreationdate.'</xmp:CreateDate>'."\n";
		$xmp .= "\t\t\t".'<xmp:CreatorTool>'.$this->creator.'</xmp:CreatorTool>'."\n";
		$xmp .= "\t\t\t".'<xmp:ModifyDate>'.$docmoddate.'</xmp:ModifyDate>'."\n";
		$xmp .= "\t\t\t".'<xmp:MetadataDate>'.$doccreationdate.'</xmp:MetadataDate>'."\n";
		$xmp .= "\t\t".'</rdf:Description>'."\n";
		$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">'."\n";
		$xmp .= "\t\t\t".'<pdf:Keywords>'.TCPDF_STATIC::_escapeXML($this->keywords).'</pdf:Keywords>'."\n";
		$xmp .= "\t\t\t".'<pdf:Producer>'.TCPDF_STATIC::_escapeXML(TCPDF_STATIC::getTCPDFProducer()).'</pdf:Producer>'."\n";
		$xmp .= "\t\t".'</rdf:Description>'."\n";
		$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:xmpMM="http://ns.adobe.com/xap/1.0/mm/">'."\n";
		$uuid = 'uuid:'.substr($this->file_id, 0, 8).'-'.substr($this->file_id, 8, 4).'-'.substr($this->file_id, 12, 4).'-'.substr($this->file_id, 16, 4).'-'.substr($this->file_id, 20, 12);
		$xmp .= "\t\t\t".'<xmpMM:DocumentID>'.$uuid.'</xmpMM:DocumentID>'."\n";
		$xmp .= "\t\t\t".'<xmpMM:InstanceID>'.$uuid.'</xmpMM:InstanceID>'."\n";
		$xmp .= "\t\t".'</rdf:Description>'."\n";
		if ($this->pdfa_mode) {
			$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdfaid="http://www.aiim.org/pdfa/ns/id/">'."\n";
			$xmp .= "\t\t\t".'<pdfaid:part>1</pdfaid:part>'."\n";
			$xmp .= "\t\t\t".'<pdfaid:conformance>B</pdfaid:conformance>'."\n";
			$xmp .= "\t\t".'</rdf:Description>'."\n";
		}
		// XMP extension schemas
		$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdfaExtension="http://www.aiim.org/pdfa/ns/extension/" xmlns:pdfaSchema="http://www.aiim.org/pdfa/ns/schema#" xmlns:pdfaProperty="http://www.aiim.org/pdfa/ns/property#">'."\n";
		$xmp .= "\t\t\t".'<pdfaExtension:schemas>'."\n";
		$xmp .= "\t\t\t\t".'<rdf:Bag>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://ns.adobe.com/pdf/1.3/</pdfaSchema:namespaceURI>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>pdf</pdfaSchema:prefix>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>Adobe PDF Schema</pdfaSchema:schema>'."\n";
		$xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://ns.adobe.com/xap/1.0/mm/</pdfaSchema:namespaceURI>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>xmpMM</pdfaSchema:prefix>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>XMP Media Management Schema</pdfaSchema:schema>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
		$xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>UUID based identifier for specific incarnation of a document</pdfaProperty:description>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>InstanceID</pdfaProperty:name>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>URI</pdfaProperty:valueType>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
		$xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://www.aiim.org/pdfa/ns/id/</pdfaSchema:namespaceURI>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>pdfaid</pdfaSchema:prefix>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>PDF/A ID Schema</pdfaSchema:schema>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
		$xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Part of PDF/A standard</pdfaProperty:description>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>part</pdfaProperty:name>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Integer</pdfaProperty:valueType>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Amendment of PDF/A standard</pdfaProperty:description>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>amd</pdfaProperty:name>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Text</pdfaProperty:valueType>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Conformance level of PDF/A standard</pdfaProperty:description>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>conformance</pdfaProperty:name>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Text</pdfaProperty:valueType>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
		$xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t".'</rdf:Bag>'."\n";
		$xmp .= "\t\t\t".'</pdfaExtension:schemas>'."\n";
		$xmp .= "\t\t".'</rdf:Description>'."\n";
		$xmp .= "\t".'</rdf:RDF>'."\n";
		$xmp .= $this->custom_xmp;
		$xmp .= '</x:xmpmeta>'."\n";
		$xmp .= '<?xpacket end="w"?>';
		$out = '<< /Type /Metadata /Subtype /XML /Length '.strlen($xmp).' >> stream'."\n".$xmp."\n".'endstream'."\n".'endobj';
		// restore previous isunicode value
		$this->isunicode = $prev_isunicode;
		$this->encrypted = $prev_encrypted;
		$this->_out($out);
		return $oid;
	}

	/**
	 * Output Catalog.
	 * @return int object id
	 * @protected
	 */
	protected function _putcatalog() {
		// put XMP
		$xmpobj = $this->_putXMP();
		// if required, add standard sRGB_IEC61966-2.1 blackscaled ICC colour profile
		if ($this->pdfa_mode OR $this->force_srgb) {
			$iccobj = $this->_newobj();
			$icc = file_get_contents(dirname(__FILE__).'/include/sRGB.icc');
			$filter = '';
			if ($this->compress) {
				$filter = ' /Filter /FlateDecode';
				$icc = gzcompress($icc);
			}
			$icc = $this->_getrawstream($icc);
			$this->_out('<</N 3 '.$filter.'/Length '.strlen($icc).'>> stream'."\n".$icc."\n".'endstream'."\n".'endobj');
		}
		// start catalog
		$oid = $this->_newobj();
		$out = '<< /Type /Catalog';
		$out .= ' /Version /'.$this->PDFVersion;
		//$out .= ' /Extensions <<>>';
		$out .= ' /Pages 1 0 R';
		//$out .= ' /PageLabels ' //...;
		$out .= ' /Names <<';
		if ((!$this->pdfa_mode) AND !empty($this->n_js)) {
			$out .= ' /JavaScript '.$this->n_js;
		}
		if (!empty($this->efnames)) {
			$out .= ' /EmbeddedFiles <</Names [';
			foreach ($this->efnames AS $fn => $fref) {
				$out .= ' '.$this->_datastring($fn).' '.$fref;
			}
			$out .= ' ]>>';
		}
		$out .= ' >>';
		if (!empty($this->dests)) {
			$out .= ' /Dests '.($this->n_dests).' 0 R';
		}
		$out .= $this->_putviewerpreferences();
		if (isset($this->LayoutMode) AND (!TCPDF_STATIC::empty_string($this->LayoutMode))) {
			$out .= ' /PageLayout /'.$this->LayoutMode;
		}
		if (isset($this->PageMode) AND (!TCPDF_STATIC::empty_string($this->PageMode))) {
			$out .= ' /PageMode /'.$this->PageMode;
		}
		if (count($this->outlines) > 0) {
			$out .= ' /Outlines '.$this->OutlineRoot.' 0 R';
			$out .= ' /PageMode /UseOutlines';
		}
		//$out .= ' /Threads []';
		if ($this->ZoomMode == 'fullpage') {
			$out .= ' /OpenAction ['.$this->page_obj_id[1].' 0 R /Fit]';
		} elseif ($this->ZoomMode == 'fullwidth') {
			$out .= ' /OpenAction ['.$this->page_obj_id[1].' 0 R /FitH null]';
		} elseif ($this->ZoomMode == 'real') {
			$out .= ' /OpenAction ['.$this->page_obj_id[1].' 0 R /XYZ null null 1]';
		} elseif (!is_string($this->ZoomMode)) {
			$out .= sprintf(' /OpenAction ['.$this->page_obj_id[1].' 0 R /XYZ null null %F]', ($this->ZoomMode / 100));
		}
		//$out .= ' /AA <<>>';
		//$out .= ' /URI <<>>';
		$out .= ' /Metadata '.$xmpobj.' 0 R';
		//$out .= ' /StructTreeRoot <<>>';
		//$out .= ' /MarkInfo <<>>';
		if (isset($this->l['a_meta_language'])) {
			$out .= ' /Lang '.$this->_textstring($this->l['a_meta_language'], $oid);
		}
		//$out .= ' /SpiderInfo <<>>';
		// set OutputIntent to sRGB IEC61966-2.1 if required
		if ($this->pdfa_mode OR $this->force_srgb) {
			$out .= ' /OutputIntents [<<';
			$out .= ' /Type /OutputIntent';
			$out .= ' /S /GTS_PDFA1';
			$out .= ' /OutputCondition '.$this->_textstring('sRGB IEC61966-2.1', $oid);
			$out .= ' /OutputConditionIdentifier '.$this->_textstring('sRGB IEC61966-2.1', $oid);
			$out .= ' /RegistryName '.$this->_textstring('http://www.color.org', $oid);
			$out .= ' /Info '.$this->_textstring('sRGB IEC61966-2.1', $oid);
			$out .= ' /DestOutputProfile '.$iccobj.' 0 R';
			$out .= ' >>]';
		}
		//$out .= ' /PieceInfo <<>>';
		if (!empty($this->pdflayers)) {
			$lyrobjs = '';
			$lyrobjs_print = '';
			$lyrobjs_view = '';
			foreach ($this->pdflayers as $layer) {
				$lyrobjs .= ' '.$layer['objid'].' 0 R';
				if ($layer['print']) {
					$lyrobjs_print .= ' '.$layer['objid'].' 0 R';
				}
				if ($layer['view']) {
					$lyrobjs_view .= ' '.$layer['objid'].' 0 R';
				}
			}
			$out .= ' /OCProperties << /OCGs ['.$lyrobjs.']';
			$out .= ' /D <<';
			$out .= ' /Name '.$this->_textstring('Layers', $oid);
			$out .= ' /Creator '.$this->_textstring('TCPDF', $oid);
			$out .= ' /BaseState /ON';
			$out .= ' /ON ['.$lyrobjs_print.']';
			$out .= ' /OFF ['.$lyrobjs_view.']';
			$out .= ' /Intent /View';
			$out .= ' /AS [';
			$out .= ' << /Event /Print /OCGs ['.$lyrobjs.'] /Category [/Print] >>';
			$out .= ' << /Event /View /OCGs ['.$lyrobjs.'] /Category [/View] >>';
			$out .= ' ]';
			$out .= ' /Order ['.$lyrobjs.']';
			$out .= ' /ListMode /AllPages';
			//$out .= ' /RBGroups ['..']';
			//$out .= ' /Locked ['..']';
			$out .= ' >>';
			$out .= ' >>';
		}
		// AcroForm
		if (!empty($this->form_obj_id)
			OR ($this->sign AND isset($this->signature_data['cert_type']))
			OR !empty($this->empty_signature_appearance)) {
			$out .= ' /AcroForm <<';
			$objrefs = '';
			if ($this->sign AND isset($this->signature_data['cert_type'])) {
				// set reference for signature object
				$objrefs .= $this->sig_obj_id.' 0 R';
			}
			if (!empty($this->empty_signature_appearance)) {
				foreach ($this->empty_signature_appearance as $esa) {
					// set reference for empty signature objects
					$objrefs .= ' '.$esa['objid'].' 0 R';
				}
			}
			if (!empty($this->form_obj_id)) {
				foreach($this->form_obj_id as $objid) {
					$objrefs .= ' '.$objid.' 0 R';
				}
			}
			$out .= ' /Fields ['.$objrefs.']';
			// It's better to turn off this value and set the appearance stream for each annotation (/AP) to avoid conflicts with signature fields.
			$out .= ' /NeedAppearances false';
			if ($this->sign AND isset($this->signature_data['cert_type'])) {
				if ($this->signature_data['cert_type'] > 0) {
					$out .= ' /SigFlags 3';
				} else {
					$out .= ' /SigFlags 1';
				}
			}
			//$out .= ' /CO ';
			if (isset($this->annotation_fonts) AND !empty($this->annotation_fonts)) {
				$out .= ' /DR <<';
				$out .= ' /Font <<';
				foreach ($this->annotation_fonts as $fontkey => $fontid) {
					$out .= ' /F'.$fontid.' '.$this->font_obj_ids[$fontkey].' 0 R';
				}
				$out .= ' >> >>';
			}
			$font = $this->getFontBuffer('helvetica');
			$out .= ' /DA (/F'.$font['i'].' 0 Tf 0 g)';
			$out .= ' /Q '.(($this->rtl)?'2':'0');
			//$out .= ' /XFA ';
			$out .= ' >>';
			// signatures
			if ($this->sign AND isset($this->signature_data['cert_type'])) {
				if ($this->signature_data['cert_type'] > 0) {
					$out .= ' /Perms << /DocMDP '.($this->sig_obj_id + 1).' 0 R >>';
				} else {
					$out .= ' /Perms << /UR3 '.($this->sig_obj_id + 1).' 0 R >>';
				}
			}
		}
		//$out .= ' /Legal <<>>';
		//$out .= ' /Requirements []';
		//$out .= ' /Collection <<>>';
		//$out .= ' /NeedsRendering true';
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		return $oid;
	}

	/**
	 * Output viewer preferences.
	 * @return string for viewer preferences
	 * @author Nicola asuni
	 * @since 3.1.000 (2008-06-09)
	 * @protected
	 */
	protected function _putviewerpreferences() {
		$vp = $this->viewer_preferences;
		$out = ' /ViewerPreferences <<';
		if ($this->rtl) {
			$out .= ' /Direction /R2L';
		} else {
			$out .= ' /Direction /L2R';
		}
		if (isset($vp['HideToolbar']) AND ($vp['HideToolbar'])) {
			$out .= ' /HideToolbar true';
		}
		if (isset($vp['HideMenubar']) AND ($vp['HideMenubar'])) {
			$out .= ' /HideMenubar true';
		}
		if (isset($vp['HideWindowUI']) AND ($vp['HideWindowUI'])) {
			$out .= ' /HideWindowUI true';
		}
		if (isset($vp['FitWindow']) AND ($vp['FitWindow'])) {
			$out .= ' /FitWindow true';
		}
		if (isset($vp['CenterWindow']) AND ($vp['CenterWindow'])) {
			$out .= ' /CenterWindow true';
		}
		if (isset($vp['DisplayDocTitle']) AND ($vp['DisplayDocTitle'])) {
			$out .= ' /DisplayDocTitle true';
		}
		if (isset($vp['NonFullScreenPageMode'])) {
			$out .= ' /NonFullScreenPageMode /'.$vp['NonFullScreenPageMode'];
		}
		if (isset($vp['ViewArea'])) {
			$out .= ' /ViewArea /'.$vp['ViewArea'];
		}
		if (isset($vp['ViewClip'])) {
			$out .= ' /ViewClip /'.$vp['ViewClip'];
		}
		if (isset($vp['PrintArea'])) {
			$out .= ' /PrintArea /'.$vp['PrintArea'];
		}
		if (isset($vp['PrintClip'])) {
			$out .= ' /PrintClip /'.$vp['PrintClip'];
		}
		if (isset($vp['PrintScaling'])) {
			$out .= ' /PrintScaling /'.$vp['PrintScaling'];
		}
		if (isset($vp['Duplex']) AND (!TCPDF_STATIC::empty_string($vp['Duplex']))) {
			$out .= ' /Duplex /'.$vp['Duplex'];
		}
		if (isset($vp['PickTrayByPDFSize'])) {
			if ($vp['PickTrayByPDFSize']) {
				$out .= ' /PickTrayByPDFSize true';
			} else {
				$out .= ' /PickTrayByPDFSize false';
			}
		}
		if (isset($vp['PrintPageRange'])) {
			$PrintPageRangeNum = '';
			foreach ($vp['PrintPageRange'] as $k => $v) {
				$PrintPageRangeNum .= ' '.($v - 1).'';
			}
			$out .= ' /PrintPageRange ['.substr($PrintPageRangeNum,1).']';
		}
		if (isset($vp['NumCopies'])) {
			$out .= ' /NumCopies '.intval($vp['NumCopies']);
		}
		$out .= ' >>';
		return $out;
	}

	/**
	 * Output PDF File Header (7.5.2).
	 * @protected
	 */
	protected function _putheader() {
		$this->_out('%PDF-'.$this->PDFVersion);
		$this->_out('%'.chr(0xe2).chr(0xe3).chr(0xcf).chr(0xd3));
	}

	/**
	 * Output end of document (EOF).
	 * @protected
	 */
	protected function _enddoc() {
		if (isset($this->CurrentFont['fontkey']) AND isset($this->CurrentFont['subsetchars'])) {
			// save subset chars of the previous font
			$this->setFontSubBuffer($this->CurrentFont['fontkey'], 'subsetchars', $this->CurrentFont['subsetchars']);
		}
		$this->state = 1;
		$this->_putheader();
		$this->_putpages();
		$this->_putresources();
		// empty signature fields
		if (!empty($this->empty_signature_appearance)) {
			foreach ($this->empty_signature_appearance as $key => $esa) {
				// widget annotation for empty signature
				$out = $this->_getobj($esa['objid'])."\n";
				$out .= '<< /Type /Annot';
				$out .= ' /Subtype /Widget';
				$out .= ' /Rect ['.$esa['rect'].']';
				$out .= ' /P '.$this->page_obj_id[($esa['page'])].' 0 R'; // link to signature appearance page
				$out .= ' /F 4';
				$out .= ' /FT /Sig';
				$signame = $esa['name'].sprintf(' [%03d]', ($key + 1));
				$out .= ' /T '.$this->_textstring($signame, $esa['objid']);
				$out .= ' /Ff 0';
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
			}
		}
		// Signature
		if ($this->sign AND isset($this->signature_data['cert_type'])) {
			// widget annotation for signature
			$out = $this->_getobj($this->sig_obj_id)."\n";
			$out .= '<< /Type /Annot';
			$out .= ' /Subtype /Widget';
			$out .= ' /Rect ['.$this->signature_appearance['rect'].']';
			$out .= ' /P '.$this->page_obj_id[($this->signature_appearance['page'])].' 0 R'; // link to signature appearance page
			$out .= ' /F 4';
			$out .= ' /FT /Sig';
			$out .= ' /T '.$this->_textstring($this->signature_appearance['name'], $this->sig_obj_id);
			$out .= ' /Ff 0';
			$out .= ' /V '.($this->sig_obj_id + 1).' 0 R';
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
			// signature
			$this->_putsignature();
		}
		// Info
		$objid_info = $this->_putinfo();
		// Catalog
		$objid_catalog = $this->_putcatalog();
		// Cross-ref
		$o = $this->bufferlen;
		// XREF section
		$this->_out('xref');
		$this->_out('0 '.($this->n + 1));
		$this->_out('0000000000 65535 f ');
		$freegen = ($this->n + 2);
		for ($i=1; $i <= $this->n; ++$i) {
			if (!isset($this->offsets[$i]) AND ($i > 1)) {
				$this->_out(sprintf('0000000000 %05d f ', $freegen));
				++$freegen;
			} else {
				$this->_out(sprintf('%010d 00000 n ', $this->offsets[$i]));
			}
		}
		// TRAILER
		$out = 'trailer'."\n";
		$out .= '<<';
		$out .= ' /Size '.($this->n + 1);
		$out .= ' /Root '.$objid_catalog.' 0 R';
		$out .= ' /Info '.$objid_info.' 0 R';
		if ($this->encrypted) {
			$out .= ' /Encrypt '.$this->encryptdata['objid'].' 0 R';
		}
		$out .= ' /ID [ <'.$this->file_id.'> <'.$this->file_id.'> ]';
		$out .= ' >>';
		$this->_out($out);
		$this->_out('startxref');
		$this->_out($o);
		$this->_out('%%EOF');
		$this->state = 3; // end-of-doc
		if ($this->diskcache) {
			// remove temporary files used for images
			foreach ($this->imagekeys as $key) {
				// remove temporary files
				unlink($this->images[$key]);
			}
			foreach ($this->fontkeys as $key) {
				// remove temporary files
				unlink($this->fonts[$key]);
			}
		}
	}

	/**
	 * Initialize a new page.
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or PORTRAIT (default)</li><li>L or LANDSCAPE</li></ul>
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @protected
	 * @see getPageSizeFromFormat(), setPageFormat()
	 */
	protected function _beginpage($orientation='', $format='') {
		++$this->page;
		$this->pageobjects[$this->page] = array();
		$this->setPageBuffer($this->page, '');
		// initialize array for graphics tranformation positions inside a page buffer
		$this->transfmrk[$this->page] = array();
		$this->state = 2;
		if (TCPDF_STATIC::empty_string($orientation)) {
			if (isset($this->CurOrientation)) {
				$orientation = $this->CurOrientation;
			} elseif ($this->fwPt > $this->fhPt) {
				// landscape
				$orientation = 'L';
			} else {
				// portrait
				$orientation = 'P';
			}
		}
		if (TCPDF_STATIC::empty_string($format)) {
			$this->pagedim[$this->page] = $this->pagedim[($this->page - 1)];
			$this->setPageOrientation($orientation);
		} else {
			$this->setPageFormat($format, $orientation);
		}
		if ($this->rtl) {
			$this->x = $this->w - $this->rMargin;
		} else {
			$this->x = $this->lMargin;
		}
		$this->y = $this->tMargin;
		if (isset($this->newpagegroup[$this->page])) {
			// start a new group
			$this->currpagegroup = $this->newpagegroup[$this->page];
			$this->pagegroups[$this->currpagegroup] = 1;
		} elseif (isset($this->currpagegroup) AND ($this->currpagegroup > 0)) {
			++$this->pagegroups[$this->currpagegroup];
		}
	}

	/**
	 * Mark end of page.
	 * @protected
	 */
	protected function _endpage() {
		$this->setVisibility('all');
		$this->state = 1;
	}

	/**
	 * Begin a new object and return the object number.
	 * @return int object number
	 * @protected
	 */
	protected function _newobj() {
		$this->_out($this->_getobj());
		return $this->n;
	}

	/**
	 * Return the starting object string for the selected object ID.
	 * @param $objid (int) Object ID (leave empty to get a new ID).
	 * @return string the starting object string
	 * @protected
	 * @since 5.8.009 (2010-08-20)
	 */
	protected function _getobj($objid='') {
		if ($objid === '') {
			++$this->n;
			$objid = $this->n;
		}
		$this->offsets[$objid] = $this->bufferlen;
		$this->pageobjects[$this->page][] = $objid;
		return $objid.' 0 obj';
	}

	/**
	 * Underline text.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $txt (string) text to underline
	 * @protected
	 */
	protected function _dounderline($x, $y, $txt) {
		$w = $this->GetStringWidth($txt);
		return $this->_dounderlinew($x, $y, $w);
	}

	/**
	 * Underline for rectangular text area.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $w (int) width to underline
	 * @protected
	 * @since 4.8.008 (2009-09-29)
	 */
	protected function _dounderlinew($x, $y, $w) {
		$linew = - $this->CurrentFont['ut'] / 1000 * $this->FontSizePt;
		return sprintf('%F %F %F %F re f', $x * $this->k, ((($this->h - $y) * $this->k) + $linew), $w * $this->k, $linew);
	}

	/**
	 * Line through text.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $txt (string) text to linethrough
	 * @protected
	 */
	protected function _dolinethrough($x, $y, $txt) {
		$w = $this->GetStringWidth($txt);
		return $this->_dolinethroughw($x, $y, $w);
	}

	/**
	 * Line through for rectangular text area.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $w (int) line length (width)
	 * @protected
	 * @since 4.9.008 (2009-09-29)
	 */
	protected function _dolinethroughw($x, $y, $w) {
		$linew = - $this->CurrentFont['ut'] / 1000 * $this->FontSizePt;
		return sprintf('%F %F %F %F re f', $x * $this->k, ((($this->h - $y) * $this->k) + $linew + ($this->FontSizePt / 3)), $w * $this->k, $linew);
	}

	/**
	 * Overline text.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $txt (string) text to overline
	 * @protected
	 * @since 4.9.015 (2010-04-19)
	 */
	protected function _dooverline($x, $y, $txt) {
		$w = $this->GetStringWidth($txt);
		return $this->_dooverlinew($x, $y, $w);
	}

	/**
	 * Overline for rectangular text area.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $w (int) width to overline
	 * @protected
	 * @since 4.9.015 (2010-04-19)
	 */
	protected function _dooverlinew($x, $y, $w) {
		$linew = - $this->CurrentFont['ut'] / 1000 * $this->FontSizePt;
		return sprintf('%F %F %F %F re f', $x * $this->k, (($this->h - $y + $this->FontAscent) * $this->k) - $linew, $w * $this->k, $linew);

	}

	/**
	 * Format a data string for meta information
	 * @param $s (string) data string to escape.
	 * @param $n (int) object ID
	 * @return string escaped string.
	 * @protected
	 */
	protected function _datastring($s, $n=0) {
		if ($n == 0) {
			$n = $this->n;
		}
		$s = $this->_encrypt_data($n, $s);
		return '('. TCPDF_STATIC::_escape($s).')';
	}

	/**
	 * Set the document creation timestamp
	 * @param $time (mixed) Document creation timestamp in seconds or date-time string.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function setDocCreationTimestamp($time) {
		if (is_string($time)) {
			$time = TCPDF_STATIC::getTimestamp($time);
		}
		$this->doc_creation_timestamp = intval($time);
	}

	/**
	 * Set the document modification timestamp
	 * @param $time (mixed) Document modification timestamp in seconds or date-time string.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function setDocModificationTimestamp($time) {
		if (is_string($time)) {
			$time = TCPDF_STATIC::getTimestamp($time);
		}
		$this->doc_modification_timestamp = intval($time);
	}

	/**
	 * Returns document creation timestamp in seconds.
	 * @return (int) Creation timestamp in seconds.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getDocCreationTimestamp() {
		return $this->doc_creation_timestamp;
	}

	/**
	 * Returns document modification timestamp in seconds.
	 * @return (int) Modfication timestamp in seconds.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getDocModificationTimestamp() {
		return $this->doc_modification_timestamp;
	}

	/**
	 * Returns a formatted date for meta information
	 * @param $n (int) Object ID.
	 * @param $timestamp (int) Timestamp to convert.
	 * @return string escaped date string.
	 * @protected
	 * @since 4.6.028 (2009-08-25)
	 */
	protected function _datestring($n=0, $timestamp=0) {
		if ((empty($timestamp)) OR ($timestamp < 0)) {
			$timestamp = $this->doc_creation_timestamp;
		}
		return $this->_datastring('D:'.TCPDF_STATIC::getFormattedDate($timestamp), $n);
	}

	/**
	 * Format a text string for meta information
	 * @param $s (string) string to escape.
	 * @param $n (int) object ID
	 * @return string escaped string.
	 * @protected
	 */
	protected function _textstring($s, $n=0) {
		if ($this->isunicode) {
			//Convert string to UTF-16BE
			$s = TCPDF_FONTS::UTF8ToUTF16BE($s, true, $this->isunicode, $this->CurrentFont);
		}
		return $this->_datastring($s, $n);
	}

	/**
	 * THIS METHOD IS DEPRECATED
	 * Format a text string
	 * @param $s (string) string to escape.
	 * @return string escaped string.
	 * @protected
	 * @deprecated
	 */
	protected function _escapetext($s) {
		if ($this->isunicode) {
			if (($this->CurrentFont['type'] == 'core') OR ($this->CurrentFont['type'] == 'TrueType') OR ($this->CurrentFont['type'] == 'Type1')) {
				$s = TCPDF_FONTS::UTF8ToLatin1($s, $this->isunicode, $this->CurrentFont);
			} else {
				//Convert string to UTF-16BE and reverse RTL language
				$s = TCPDF_FONTS::utf8StrRev($s, false, $this->tmprtl, $this->isunicode, $this->CurrentFont);
			}
		}
		return TCPDF_STATIC::_escape($s);
	}

	/**
	 * get raw output stream.
	 * @param $s (string) string to output.
	 * @param $n (int) object reference for encryption mode
	 * @protected
	 * @author Nicola Asuni
	 * @since 5.5.000 (2010-06-22)
	 */
	protected function _getrawstream($s, $n=0) {
		if ($n <= 0) {
			// default to current object
			$n = $this->n;
		}
		return $this->_encrypt_data($n, $s);
	}

	/**
	 * Format output stream (DEPRECATED).
	 * @param $s (string) string to output.
	 * @param $n (int) object reference for encryption mode
	 * @protected
	 * @deprecated
	 */
	protected function _getstream($s, $n=0) {
		return 'stream'."\n".$this->_getrawstream($s, $n)."\n".'endstream';
	}

	/**
	 * Output a stream (DEPRECATED).
	 * @param $s (string) string to output.
	 * @param $n (int) object reference for encryption mode
	 * @protected
	 * @deprecated
	 */
	protected function _putstream($s, $n=0) {
		$this->_out($this->_getstream($s, $n));
	}

	/**
	 * Output a string to the document.
	 * @param $s (string) string to output.
	 * @protected
	 */
	protected function _out($s) {
		if ($this->state == 2) {
			if ($this->inxobj) {
				// we are inside an XObject template
				$this->xobjects[$this->xobjid]['outdata'] .= $s."\n";
			} elseif ((!$this->InFooter) AND isset($this->footerlen[$this->page]) AND ($this->footerlen[$this->page] > 0)) {
				// puts data before page footer
				$pagebuff = $this->getPageBuffer($this->page);
				$page = substr($pagebuff, 0, -$this->footerlen[$this->page]);
				$footer = substr($pagebuff, -$this->footerlen[$this->page]);
				$this->setPageBuffer($this->page, $page.$s."\n".$footer);
				// update footer position
				$this->footerpos[$this->page] += strlen($s."\n");
			} else {
				// set page data
				$this->setPageBuffer($this->page, $s."\n", true);
			}
		} elseif ($this->state > 0) {
			// set general data
			$this->setBuffer($s."\n");
		}
	}

	/**
	 * Set header font.
	 * @param $font (array) font
	 * @public
	 * @since 1.1
	 */
	public function setHeaderFont($font) {
		$this->header_font = $font;
	}

	/**
	 * Get header font.
	 * @return array()
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getHeaderFont() {
		return $this->header_font;
	}

	/**
	 * Set footer font.
	 * @param $font (array) font
	 * @public
	 * @since 1.1
	 */
	public function setFooterFont($font) {
		$this->footer_font = $font;
	}

	/**
	 * Get Footer font.
	 * @return array()
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getFooterFont() {
		return $this->footer_font;
	}

	/**
	 * Set language array.
	 * @param $language (array)
	 * @public
	 * @since 1.1
	 */
	public function setLanguageArray($language) {
		$this->l = $language;
		if (isset($this->l['a_meta_dir'])) {
			$this->rtl = $this->l['a_meta_dir']=='rtl' ? true : false;
		} else {
			$this->rtl = false;
		}
	}

	/**
	 * Returns the PDF data.
	 * @public
	 */
	public function getPDFData() {
		if ($this->state < 3) {
			$this->Close();
		}
		return $this->buffer;
	}

	/**
	 * Output anchor link.
	 * @param $url (string) link URL or internal link (i.e.: &lt;a href="#23,4.5"&gt;link to page 23 at 4.5 Y position&lt;/a&gt;)
	 * @param $name (string) link name
	 * @param $fill (boolean) Indicates if the cell background must be painted (true) or transparent (false).
	 * @param $firstline (boolean) if true prints only the first line and return the remaining string.
	 * @param $color (array) array of RGB text color
	 * @param $style (string) font style (U, D, B, I)
	 * @param $firstblock (boolean) if true the string is the starting of a line.
	 * @return the number of cells used or the remaining text if $firstline = true;
	 * @public
	 */
	public function addHtmlLink($url, $name, $fill=false, $firstline=false, $color='', $style=-1, $firstblock=false) {
		if (isset($url[1]) AND ($url[0] == '#') AND is_numeric($url[1])) {
			// convert url to internal link
			$lnkdata = explode(',', $url);
			if (isset($lnkdata[0])) {
				$page = intval(substr($lnkdata[0], 1));
				if (empty($page) OR ($page <= 0)) {
					$page = $this->page;
				}
				if (isset($lnkdata[1]) AND (strlen($lnkdata[1]) > 0)) {
					$lnky = floatval($lnkdata[1]);
				} else {
					$lnky = 0;
				}
				$url = $this->AddLink();
				$this->SetLink($url, $lnky, $page);
			}
		}
		// store current settings
		$prevcolor = $this->fgcolor;
		$prevstyle = $this->FontStyle;
		if (empty($color)) {
			$this->SetTextColorArray($this->htmlLinkColorArray);
		} else {
			$this->SetTextColorArray($color);
		}
		if ($style == -1) {
			$this->SetFont('', $this->FontStyle.$this->htmlLinkFontStyle);
		} else {
			$this->SetFont('', $this->FontStyle.$style);
		}
		$ret = $this->Write($this->lasth, $name, $url, $fill, '', false, 0, $firstline, $firstblock, 0);
		// restore settings
		$this->SetFont('', $prevstyle);
		$this->SetTextColorArray($prevcolor);
		return $ret;
	}

	/**
	 * Converts pixels to User's Units.
	 * @param $px (int) pixels
	 * @return float value in user's unit
	 * @public
	 * @see setImageScale(), getImageScale()
	 */
	public function pixelsToUnits($px) {
		return ($px / ($this->imgscale * $this->k));
	}

	/**
	 * Reverse function for htmlentities.
	 * Convert entities in UTF-8.
	 * @param $text_to_convert (string) Text to convert.
	 * @return string converted text string
	 * @public
	 */
	public function unhtmlentities($text_to_convert) {
		return @html_entity_decode($text_to_convert, ENT_QUOTES, $this->encoding);
	}

	// ENCRYPTION METHODS ----------------------------------

	/**
	 * Compute encryption key depending on object number where the encrypted data is stored.
	 * This is used for all strings and streams without crypt filter specifier.
	 * @param $n (int) object number
	 * @return int object key
	 * @protected
	 * @author Nicola Asuni
	 * @since 2.0.000 (2008-01-02)
	 */
	protected function _objectkey($n) {
		$objkey = $this->encryptdata['key'].pack('VXxx', $n);
		if ($this->encryptdata['mode'] == 2) { // AES-128
			// AES padding
			$objkey .= "\x73\x41\x6C\x54"; // sAlT
		}
		$objkey = substr(TCPDF_STATIC::_md5_16($objkey), 0, (($this->encryptdata['Length'] / 8) + 5));
		$objkey = substr($objkey, 0, 16);
		return $objkey;
	}

	/**
	 * Encrypt the input string.
	 * @param $n (int) object number
	 * @param $s (string) data string to encrypt
	 * @return encrypted string
	 * @protected
	 * @author Nicola Asuni
	 * @since 5.0.005 (2010-05-11)
	 */
	protected function _encrypt_data($n, $s) {
		if (!$this->encrypted) {
			return $s;
		}
		switch ($this->encryptdata['mode']) {
			case 0:   // RC4-40
			case 1: { // RC4-128
				$s = TCPDF_STATIC::_RC4($this->_objectkey($n), $s, $this->last_enc_key, $this->last_enc_key_c);
				break;
			}
			case 2: { // AES-128
				$s = TCPDF_STATIC::_AES($this->_objectkey($n), $s);
				break;
			}
			case 3: { // AES-256
				$s = TCPDF_STATIC::_AES($this->encryptdata['key'], $s);
				break;
			}
		}
		return $s;
	}

	/**
	 * Put encryption on PDF document.
	 * @protected
	 * @author Nicola Asuni
	 * @since 2.0.000 (2008-01-02)
	 */
	protected function _putencryption() {
		if (!$this->encrypted) {
			return;
		}
		$this->encryptdata['objid'] = $this->_newobj();
		$out = '<<';
		if (!isset($this->encryptdata['Filter']) OR empty($this->encryptdata['Filter'])) {
			$this->encryptdata['Filter'] = 'Standard';
		}
		$out .= ' /Filter /'.$this->encryptdata['Filter'];
		if (isset($this->encryptdata['SubFilter']) AND !empty($this->encryptdata['SubFilter'])) {
			$out .= ' /SubFilter /'.$this->encryptdata['SubFilter'];
		}
		if (!isset($this->encryptdata['V']) OR empty($this->encryptdata['V'])) {
			$this->encryptdata['V'] = 1;
		}
		// V is a code specifying the algorithm to be used in encrypting and decrypting the document
		$out .= ' /V '.$this->encryptdata['V'];
		if (isset($this->encryptdata['Length']) AND !empty($this->encryptdata['Length'])) {
			// The length of the encryption key, in bits. The value shall be a multiple of 8, in the range 40 to 256
			$out .= ' /Length '.$this->encryptdata['Length'];
		} else {
			$out .= ' /Length 40';
		}
		if ($this->encryptdata['V'] >= 4) {
			if (!isset($this->encryptdata['StmF']) OR empty($this->encryptdata['StmF'])) {
				$this->encryptdata['StmF'] = 'Identity';
			}
			if (!isset($this->encryptdata['StrF']) OR empty($this->encryptdata['StrF'])) {
				// The name of the crypt filter that shall be used when decrypting all strings in the document.
				$this->encryptdata['StrF'] = 'Identity';
			}
			// A dictionary whose keys shall be crypt filter names and whose values shall be the corresponding crypt filter dictionaries.
			if (isset($this->encryptdata['CF']) AND !empty($this->encryptdata['CF'])) {
				$out .= ' /CF <<';
				$out .= ' /'.$this->encryptdata['StmF'].' <<';
				$out .= ' /Type /CryptFilter';
				if (isset($this->encryptdata['CF']['CFM']) AND !empty($this->encryptdata['CF']['CFM'])) {
					// The method used
					$out .= ' /CFM /'.$this->encryptdata['CF']['CFM'];
					if ($this->encryptdata['pubkey']) {
						$out .= ' /Recipients [';
						foreach ($this->encryptdata['Recipients'] as $rec) {
							$out .= ' <'.$rec.'>';
						}
						$out .= ' ]';
						if (isset($this->encryptdata['CF']['EncryptMetadata']) AND (!$this->encryptdata['CF']['EncryptMetadata'])) {
							$out .= ' /EncryptMetadata false';
						} else {
							$out .= ' /EncryptMetadata true';
						}
					}
				} else {
					$out .= ' /CFM /None';
				}
				if (isset($this->encryptdata['CF']['AuthEvent']) AND !empty($this->encryptdata['CF']['AuthEvent'])) {
					// The event to be used to trigger the authorization that is required to access encryption keys used by this filter.
					$out .= ' /AuthEvent /'.$this->encryptdata['CF']['AuthEvent'];
				} else {
					$out .= ' /AuthEvent /DocOpen';
				}
				if (isset($this->encryptdata['CF']['Length']) AND !empty($this->encryptdata['CF']['Length'])) {
					// The bit length of the encryption key.
					$out .= ' /Length '.$this->encryptdata['CF']['Length'];
				}
				$out .= ' >> >>';
			}
			// The name of the crypt filter that shall be used by default when decrypting streams.
			$out .= ' /StmF /'.$this->encryptdata['StmF'];
			// The name of the crypt filter that shall be used when decrypting all strings in the document.
			$out .= ' /StrF /'.$this->encryptdata['StrF'];
			if (isset($this->encryptdata['EFF']) AND !empty($this->encryptdata['EFF'])) {
				// The name of the crypt filter that shall be used when encrypting embedded file streams that do not have their own crypt filter specifier.
				$out .= ' /EFF /'.$this->encryptdata[''];
			}
		}
		// Additional encryption dictionary entries for the standard security handler
		if ($this->encryptdata['pubkey']) {
			if (($this->encryptdata['V'] < 4) AND isset($this->encryptdata['Recipients']) AND !empty($this->encryptdata['Recipients'])) {
				$out .= ' /Recipients [';
				foreach ($this->encryptdata['Recipients'] as $rec) {
					$out .= ' <'.$rec.'>';
				}
				$out .= ' ]';
			}
		} else {
			$out .= ' /R';
			if ($this->encryptdata['V'] == 5) { // AES-256
				$out .= ' 5';
				$out .= ' /OE ('.TCPDF_STATIC::_escape($this->encryptdata['OE']).')';
				$out .= ' /UE ('.TCPDF_STATIC::_escape($this->encryptdata['UE']).')';
				$out .= ' /Perms ('.TCPDF_STATIC::_escape($this->encryptdata['perms']).')';
			} elseif ($this->encryptdata['V'] == 4) { // AES-128
				$out .= ' 4';
			} elseif ($this->encryptdata['V'] < 2) { // RC-40
				$out .= ' 2';
			} else { // RC-128
				$out .= ' 3';
			}
			$out .= ' /O ('.TCPDF_STATIC::_escape($this->encryptdata['O']).')';
			$out .= ' /U ('.TCPDF_STATIC::_escape($this->encryptdata['U']).')';
			$out .= ' /P '.$this->encryptdata['P'];
			if (isset($this->encryptdata['EncryptMetadata']) AND (!$this->encryptdata['EncryptMetadata'])) {
				$out .= ' /EncryptMetadata false';
			} else {
				$out .= ' /EncryptMetadata true';
			}
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}

	/**
	 * Compute U value (used for encryption)
	 * @return string U value
	 * @protected
	 * @since 2.0.000 (2008-01-02)
	 * @author Nicola Asuni
	 */
	protected function _Uvalue() {
		if ($this->encryptdata['mode'] == 0) { // RC4-40
			return TCPDF_STATIC::_RC4($this->encryptdata['key'], TCPDF_STATIC::$enc_padding, $this->last_enc_key, $this->last_enc_key_c);
		} elseif ($this->encryptdata['mode'] < 3) { // RC4-128, AES-128
			$tmp = TCPDF_STATIC::_md5_16(TCPDF_STATIC::$enc_padding.$this->encryptdata['fileid']);
			$enc = TCPDF_STATIC::_RC4($this->encryptdata['key'], $tmp, $this->last_enc_key, $this->last_enc_key_c);
			$len = strlen($tmp);
			for ($i = 1; $i <= 19; ++$i) {
				$ek = '';
				for ($j = 0; $j < $len; ++$j) {
					$ek .= chr(ord($this->encryptdata['key'][$j]) ^ $i);
				}
				$enc = TCPDF_STATIC::_RC4($ek, $enc, $this->last_enc_key, $this->last_enc_key_c);
			}
			$enc .= str_repeat("\x00", 16);
			return substr($enc, 0, 32);
		} elseif ($this->encryptdata['mode'] == 3) { // AES-256
			$seed = TCPDF_STATIC::_md5_16(TCPDF_STATIC::getRandomSeed());
			// User Validation Salt
			$this->encryptdata['UVS'] = substr($seed, 0, 8);
			// User Key Salt
			$this->encryptdata['UKS'] = substr($seed, 8, 16);
			return hash('sha256', $this->encryptdata['user_password'].$this->encryptdata['UVS'], true).$this->encryptdata['UVS'].$this->encryptdata['UKS'];
		}
	}

	/**
	 * Compute UE value (used for encryption)
	 * @return string UE value
	 * @protected
	 * @since 5.9.006 (2010-10-19)
	 * @author Nicola Asuni
	 */
	protected function _UEvalue() {
		$hashkey = hash('sha256', $this->encryptdata['user_password'].$this->encryptdata['UKS'], true);
		$iv = str_repeat("\x00", mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
		return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $hashkey, $this->encryptdata['key'], MCRYPT_MODE_CBC, $iv);
	}

	/**
	 * Compute O value (used for encryption)
	 * @return string O value
	 * @protected
	 * @since 2.0.000 (2008-01-02)
	 * @author Nicola Asuni
	 */
	protected function _Ovalue() {
		if ($this->encryptdata['mode'] < 3) { // RC4-40, RC4-128, AES-128
			$tmp = TCPDF_STATIC::_md5_16($this->encryptdata['owner_password']);
			if ($this->encryptdata['mode'] > 0) {
				for ($i = 0; $i < 50; ++$i) {
					$tmp = TCPDF_STATIC::_md5_16($tmp);
				}
			}
			$owner_key = substr($tmp, 0, ($this->encryptdata['Length'] / 8));
			$enc = TCPDF_STATIC::_RC4($owner_key, $this->encryptdata['user_password'], $this->last_enc_key, $this->last_enc_key_c);
			if ($this->encryptdata['mode'] > 0) {
				$len = strlen($owner_key);
				for ($i = 1; $i <= 19; ++$i) {
					$ek = '';
					for ($j = 0; $j < $len; ++$j) {
						$ek .= chr(ord($owner_key[$j]) ^ $i);
					}
					$enc = TCPDF_STATIC::_RC4($ek, $enc, $this->last_enc_key, $this->last_enc_key_c);
				}
			}
			return $enc;
		} elseif ($this->encryptdata['mode'] == 3) { // AES-256
			$seed = TCPDF_STATIC::_md5_16(TCPDF_STATIC::getRandomSeed());
			// Owner Validation Salt
			$this->encryptdata['OVS'] = substr($seed, 0, 8);
			// Owner Key Salt
			$this->encryptdata['OKS'] = substr($seed, 8, 16);
			return hash('sha256', $this->encryptdata['owner_password'].$this->encryptdata['OVS'].$this->encryptdata['U'], true).$this->encryptdata['OVS'].$this->encryptdata['OKS'];
		}
	}

	/**
	 * Compute OE value (used for encryption)
	 * @return string OE value
	 * @protected
	 * @since 5.9.006 (2010-10-19)
	 * @author Nicola Asuni
	 */
	protected function _OEvalue() {
		$hashkey = hash('sha256', $this->encryptdata['owner_password'].$this->encryptdata['OKS'].$this->encryptdata['U'], true);
		$iv = str_repeat("\x00", mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
		return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $hashkey, $this->encryptdata['key'], MCRYPT_MODE_CBC, $iv);
	}

	/**
	 * Convert password for AES-256 encryption mode
	 * @param $password (string) password
	 * @return string password
	 * @protected
	 * @since 5.9.006 (2010-10-19)
	 * @author Nicola Asuni
	 */
	protected function _fixAES256Password($password) {
		$psw = ''; // password to be returned
		$psw_array = TCPDF_FONTS::utf8Bidi(TCPDF_FONTS::UTF8StringToArray($password, $this->isunicode, $this->CurrentFont), $password, $this->rtl, $this->isunicode, $this->CurrentFont);
		foreach ($psw_array as $c) {
			$psw .= TCPDF_FONTS::unichr($c, $this->isunicode);
		}
		return substr($psw, 0, 127);
	}

	/**
	 * Compute encryption key
	 * @protected
	 * @since 2.0.000 (2008-01-02)
	 * @author Nicola Asuni
	 */
	protected function _generateencryptionkey() {
		$keybytelen = ($this->encryptdata['Length'] / 8);
		if (!$this->encryptdata['pubkey']) { // standard mode
			if ($this->encryptdata['mode'] == 3) { // AES-256
				// generate 256 bit random key
				$this->encryptdata['key'] = substr(hash('sha256', TCPDF_STATIC::getRandomSeed(), true), 0, $keybytelen);
				// truncate passwords
				$this->encryptdata['user_password'] = $this->_fixAES256Password($this->encryptdata['user_password']);
				$this->encryptdata['owner_password'] = $this->_fixAES256Password($this->encryptdata['owner_password']);
				// Compute U value
				$this->encryptdata['U'] = $this->_Uvalue();
				// Compute UE value
				$this->encryptdata['UE'] = $this->_UEvalue();
				// Compute O value
				$this->encryptdata['O'] = $this->_Ovalue();
				// Compute OE value
				$this->encryptdata['OE'] = $this->_OEvalue();
				// Compute P value
				$this->encryptdata['P'] = $this->encryptdata['protection'];
				// Computing the encryption dictionary's Perms (permissions) value
				$perms = TCPDF_STATIC::getEncPermissionsString($this->encryptdata['protection']); // bytes 0-3
				$perms .= chr(255).chr(255).chr(255).chr(255); // bytes 4-7
				if (isset($this->encryptdata['CF']['EncryptMetadata']) AND (!$this->encryptdata['CF']['EncryptMetadata'])) { // byte 8
					$perms .= 'F';
				} else {
					$perms .= 'T';
				}
				$perms .= 'adb'; // bytes 9-11
				$perms .= 'nick'; // bytes 12-15
				$iv = str_repeat("\x00", mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB));
				$this->encryptdata['perms'] = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->encryptdata['key'], $perms, MCRYPT_MODE_ECB, $iv);
			} else { // RC4-40, RC4-128, AES-128
				// Pad passwords
				$this->encryptdata['user_password'] = substr($this->encryptdata['user_password'].TCPDF_STATIC::$enc_padding, 0, 32);
				$this->encryptdata['owner_password'] = substr($this->encryptdata['owner_password'].TCPDF_STATIC::$enc_padding, 0, 32);
				// Compute O value
				$this->encryptdata['O'] = $this->_Ovalue();
				// get default permissions (reverse byte order)
				$permissions = TCPDF_STATIC::getEncPermissionsString($this->encryptdata['protection']);
				// Compute encryption key
				$tmp = TCPDF_STATIC::_md5_16($this->encryptdata['user_password'].$this->encryptdata['O'].$permissions.$this->encryptdata['fileid']);
				if ($this->encryptdata['mode'] > 0) {
					for ($i = 0; $i < 50; ++$i) {
						$tmp = TCPDF_STATIC::_md5_16(substr($tmp, 0, $keybytelen));
					}
				}
				$this->encryptdata['key'] = substr($tmp, 0, $keybytelen);
				// Compute U value
				$this->encryptdata['U'] = $this->_Uvalue();
				// Compute P value
				$this->encryptdata['P'] = $this->encryptdata['protection'];
			}
		} else { // Public-Key mode
			// random 20-byte seed
			$seed = sha1(TCPDF_STATIC::getRandomSeed(), true);
			$recipient_bytes = '';
			foreach ($this->encryptdata['pubkeys'] as $pubkey) {
				// for each public certificate
				if (isset($pubkey['p'])) {
					$pkprotection = TCPDF_STATIC::getUserPermissionCode($pubkey['p'], $this->encryptdata['mode']);
				} else {
					$pkprotection = $this->encryptdata['protection'];
				}
				// get default permissions (reverse byte order)
				$pkpermissions = TCPDF_STATIC::getEncPermissionsString($pkprotection);
				// envelope data
				$envelope = $seed.$pkpermissions;
				// write the envelope data to a temporary file
				$tempkeyfile = TCPDF_STATIC::getObjFilename('key');
				$f = fopen($tempkeyfile, 'wb');
				if (!$f) {
					$this->Error('Unable to create temporary key file: '.$tempkeyfile);
				}
				$envelope_length = strlen($envelope);
				fwrite($f, $envelope, $envelope_length);
				fclose($f);
				$tempencfile = TCPDF_STATIC::getObjFilename('enc');
				if (!openssl_pkcs7_encrypt($tempkeyfile, $tempencfile, $pubkey['c'], array(), PKCS7_BINARY | PKCS7_DETACHED)) {
					$this->Error('Unable to encrypt the file: '.$tempkeyfile);
				}
				unlink($tempkeyfile);
				// read encryption signature
				$signature = file_get_contents($tempencfile, false, null, $envelope_length);
				unlink($tempencfile);
				// extract signature
				$signature = substr($signature, strpos($signature, 'Content-Disposition'));
				$tmparr = explode("\n\n", $signature);
				$signature = trim($tmparr[1]);
				unset($tmparr);
				// decode signature
				$signature = base64_decode($signature);
				// convert signature to hex
				$hexsignature = current(unpack('H*', $signature));
				// store signature on recipients array
				$this->encryptdata['Recipients'][] = $hexsignature;
				// The bytes of each item in the Recipients array of PKCS#7 objects in the order in which they appear in the array
				$recipient_bytes .= $signature;
			}
			// calculate encryption key
			if ($this->encryptdata['mode'] == 3) { // AES-256
				$this->encryptdata['key'] = substr(hash('sha256', $seed.$recipient_bytes, true), 0, $keybytelen);
			} else { // RC4-40, RC4-128, AES-128
				$this->encryptdata['key'] = substr(sha1($seed.$recipient_bytes, true), 0, $keybytelen);
			}
		}
	}

	/**
	 * Set document protection
	 * Remark: the protection against modification is for people who have the full Acrobat product.
	 * If you don't set any password, the document will open as usual. If you set a user password, the PDF viewer will ask for it before displaying the document. The master password, if different from the user one, can be used to get full access.
	 * Note: protecting a document requires to encrypt it, which increases the processing time a lot. This can cause a PHP time-out in some cases, especially if the document contains images or fonts.
	 * @param $permissions (Array) the set of permissions (specify the ones you want to block):<ul><li>print : Print the document;</li><li>modify : Modify the contents of the document by operations other than those controlled by 'fill-forms', 'extract' and 'assemble';</li><li>copy : Copy or otherwise extract text and graphics from the document;</li><li>annot-forms : Add or modify text annotations, fill in interactive form fields, and, if 'modify' is also set, create or modify interactive form fields (including signature fields);</li><li>fill-forms : Fill in existing interactive form fields (including signature fields), even if 'annot-forms' is not specified;</li><li>extract : Extract text and graphics (in support of accessibility to users with disabilities or for other purposes);</li><li>assemble : Assemble the document (insert, rotate, or delete pages and create bookmarks or thumbnail images), even if 'modify' is not set;</li><li>print-high : Print the document to a representation from which a faithful digital copy of the PDF content could be generated. When this is not set, printing is limited to a low-level representation of the appearance, possibly of degraded quality.</li><li>owner : (inverted logic - only for public-key) when set permits change of encryption and enables all other permissions.</li></ul>
	 * @param $user_pass (String) user password. Empty by default.
	 * @param $owner_pass (String) owner password. If not specified, a random value is used.
	 * @param $mode (int) encryption strength: 0 = RC4 40 bit; 1 = RC4 128 bit; 2 = AES 128 bit; 3 = AES 256 bit.
	 * @param $pubkeys (String) array of recipients containing public-key certificates ('c') and permissions ('p'). For example: array(array('c' => 'file://../examples/data/cert/tcpdf.crt', 'p' => array('print')))
	 * @public
	 * @since 2.0.000 (2008-01-02)
	 * @author Nicola Asuni
	 */
	public function SetProtection($permissions=array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), $user_pass='', $owner_pass=null, $mode=0, $pubkeys=null) {
		if ($this->pdfa_mode) {
			// encryption is not allowed in PDF/A mode
			return;
		}
		$this->encryptdata['protection'] = TCPDF_STATIC::getUserPermissionCode($permissions, $mode);
		if (($pubkeys !== null) AND (is_array($pubkeys))) {
			// public-key mode
			$this->encryptdata['pubkeys'] = $pubkeys;
			if ($mode == 0) {
				// public-Key Security requires at least 128 bit
				$mode = 1;
			}
			if (!function_exists('openssl_pkcs7_encrypt')) {
				$this->Error('Public-Key Security requires openssl library.');
			}
			// Set Public-Key filter (availabe are: Entrust.PPKEF, Adobe.PPKLite, Adobe.PubSec)
			$this->encryptdata['pubkey'] = true;
			$this->encryptdata['Filter'] = 'Adobe.PubSec';
			$this->encryptdata['StmF'] = 'DefaultCryptFilter';
			$this->encryptdata['StrF'] = 'DefaultCryptFilter';
		} else {
			// standard mode (password mode)
			$this->encryptdata['pubkey'] = false;
			$this->encryptdata['Filter'] = 'Standard';
			$this->encryptdata['StmF'] = 'StdCF';
			$this->encryptdata['StrF'] = 'StdCF';
		}
		if ($mode > 1) { // AES
			if (!extension_loaded('mcrypt')) {
				$this->Error('AES encryption requires mcrypt library (http://www.php.net/manual/en/mcrypt.requirements.php).');
			}
			if (mcrypt_get_cipher_name(MCRYPT_RIJNDAEL_128) === false) {
				$this->Error('AES encryption requires MCRYPT_RIJNDAEL_128 cypher.');
			}
			if (($mode == 3) AND !function_exists('hash')) {
				// the Hash extension requires no external libraries and is enabled by default as of PHP 5.1.2.
				$this->Error('AES 256 encryption requires HASH Message Digest Framework (http://www.php.net/manual/en/book.hash.php).');
			}
		}
		if ($owner_pass === null) {
			$owner_pass = md5(TCPDF_STATIC::getRandomSeed());
		}
		$this->encryptdata['user_password'] = $user_pass;
		$this->encryptdata['owner_password'] = $owner_pass;
		$this->encryptdata['mode'] = $mode;
		switch ($mode) {
			case 0: { // RC4 40 bit
				$this->encryptdata['V'] = 1;
				$this->encryptdata['Length'] = 40;
				$this->encryptdata['CF']['CFM'] = 'V2';
				break;
			}
			case 1: { // RC4 128 bit
				$this->encryptdata['V'] = 2;
				$this->encryptdata['Length'] = 128;
				$this->encryptdata['CF']['CFM'] = 'V2';
				if ($this->encryptdata['pubkey']) {
					$this->encryptdata['SubFilter'] = 'adbe.pkcs7.s4';
					$this->encryptdata['Recipients'] = array();
				}
				break;
			}
			case 2: { // AES 128 bit
				$this->encryptdata['V'] = 4;
				$this->encryptdata['Length'] = 128;
				$this->encryptdata['CF']['CFM'] = 'AESV2';
				$this->encryptdata['CF']['Length'] = 128;
				if ($this->encryptdata['pubkey']) {
					$this->encryptdata['SubFilter'] = 'adbe.pkcs7.s5';
					$this->encryptdata['Recipients'] = array();
				}
				break;
			}
			case 3: { // AES 256 bit
				$this->encryptdata['V'] = 5;
				$this->encryptdata['Length'] = 256;
				$this->encryptdata['CF']['CFM'] = 'AESV3';
				$this->encryptdata['CF']['Length'] = 256;
				if ($this->encryptdata['pubkey']) {
					$this->encryptdata['SubFilter'] = 'adbe.pkcs7.s5';
					$this->encryptdata['Recipients'] = array();
				}
				break;
			}
		}
		$this->encrypted = true;
		$this->encryptdata['fileid'] = TCPDF_STATIC::convertHexStringToString($this->file_id);
		$this->_generateencryptionkey();
	}

	// END OF ENCRYPTION FUNCTIONS -------------------------

	// START TRANSFORMATIONS SECTION -----------------------

	/**
	 * Starts a 2D tranformation saving current graphic state.
	 * This function must be called before scaling, mirroring, translation, rotation and skewing.
	 * Use StartTransform() before, and StopTransform() after the transformations to restore the normal behavior.
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function StartTransform() {
		if ($this->state != 2) {
			return;
		}
		$this->_out('q');
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['transfmrk'][] = strlen($this->xobjects[$this->xobjid]['outdata']);
		} else {
			$this->transfmrk[$this->page][] = $this->pagelen[$this->page];
		}
		++$this->transfmatrix_key;
		$this->transfmatrix[$this->transfmatrix_key] = array();
	}

	/**
	 * Stops a 2D tranformation restoring previous graphic state.
	 * This function must be called after scaling, mirroring, translation, rotation and skewing.
	 * Use StartTransform() before, and StopTransform() after the transformations to restore the normal behavior.
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function StopTransform() {
		if ($this->state != 2) {
			return;
		}
		$this->_out('Q');
		if (isset($this->transfmatrix[$this->transfmatrix_key])) {
			array_pop($this->transfmatrix[$this->transfmatrix_key]);
			--$this->transfmatrix_key;
		}
		if ($this->inxobj) {
			// we are inside an XObject template
			array_pop($this->xobjects[$this->xobjid]['transfmrk']);
		} else {
			array_pop($this->transfmrk[$this->page]);
		}
	}
	/**
	 * Horizontal Scaling.
	 * @param $s_x (float) scaling factor for width as percent. 0 is not allowed.
	 * @param $x (int) abscissa of the scaling center. Default is current x position
	 * @param $y (int) ordinate of the scaling center. Default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function ScaleX($s_x, $x='', $y='') {
		$this->Scale($s_x, 100, $x, $y);
	}

	/**
	 * Vertical Scaling.
	 * @param $s_y (float) scaling factor for height as percent. 0 is not allowed.
	 * @param $x (int) abscissa of the scaling center. Default is current x position
	 * @param $y (int) ordinate of the scaling center. Default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function ScaleY($s_y, $x='', $y='') {
		$this->Scale(100, $s_y, $x, $y);
	}

	/**
	 * Vertical and horizontal proportional Scaling.
	 * @param $s (float) scaling factor for width and height as percent. 0 is not allowed.
	 * @param $x (int) abscissa of the scaling center. Default is current x position
	 * @param $y (int) ordinate of the scaling center. Default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function ScaleXY($s, $x='', $y='') {
		$this->Scale($s, $s, $x, $y);
	}

	/**
	 * Vertical and horizontal non-proportional Scaling.
	 * @param $s_x (float) scaling factor for width as percent. 0 is not allowed.
	 * @param $s_y (float) scaling factor for height as percent. 0 is not allowed.
	 * @param $x (int) abscissa of the scaling center. Default is current x position
	 * @param $y (int) ordinate of the scaling center. Default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function Scale($s_x, $s_y, $x='', $y='') {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		if (($s_x == 0) OR ($s_y == 0)) {
			$this->Error('Please do not use values equal to zero for scaling');
		}
		$y = ($this->h - $y) * $this->k;
		$x *= $this->k;
		//calculate elements of transformation matrix
		$s_x /= 100;
		$s_y /= 100;
		$tm = array();
		$tm[0] = $s_x;
		$tm[1] = 0;
		$tm[2] = 0;
		$tm[3] = $s_y;
		$tm[4] = $x * (1 - $s_x);
		$tm[5] = $y * (1 - $s_y);
		//scale the coordinate system
		$this->Transform($tm);
	}

	/**
	 * Horizontal Mirroring.
	 * @param $x (int) abscissa of the point. Default is current x position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function MirrorH($x='') {
		$this->Scale(-100, 100, $x);
	}

	/**
	 * Verical Mirroring.
	 * @param $y (int) ordinate of the point. Default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function MirrorV($y='') {
		$this->Scale(100, -100, '', $y);
	}

	/**
	 * Point reflection mirroring.
	 * @param $x (int) abscissa of the point. Default is current x position
	 * @param $y (int) ordinate of the point. Default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function MirrorP($x='',$y='') {
		$this->Scale(-100, -100, $x, $y);
	}

	/**
	 * Reflection against a straight line through point (x, y) with the gradient angle (angle).
	 * @param $angle (float) gradient angle of the straight line. Default is 0 (horizontal line).
	 * @param $x (int) abscissa of the point. Default is current x position
	 * @param $y (int) ordinate of the point. Default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function MirrorL($angle=0, $x='',$y='') {
		$this->Scale(-100, 100, $x, $y);
		$this->Rotate(-2*($angle-90), $x, $y);
	}

	/**
	 * Translate graphic object horizontally.
	 * @param $t_x (int) movement to the right (or left for RTL)
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function TranslateX($t_x) {
		$this->Translate($t_x, 0);
	}

	/**
	 * Translate graphic object vertically.
	 * @param $t_y (int) movement to the bottom
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function TranslateY($t_y) {
		$this->Translate(0, $t_y);
	}

	/**
	 * Translate graphic object horizontally and vertically.
	 * @param $t_x (int) movement to the right
	 * @param $t_y (int) movement to the bottom
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function Translate($t_x, $t_y) {
		//calculate elements of transformation matrix
		$tm = array();
		$tm[0] = 1;
		$tm[1] = 0;
		$tm[2] = 0;
		$tm[3] = 1;
		$tm[4] = $t_x * $this->k;
		$tm[5] = -$t_y * $this->k;
		//translate the coordinate system
		$this->Transform($tm);
	}

	/**
	 * Rotate object.
	 * @param $angle (float) angle in degrees for counter-clockwise rotation
	 * @param $x (int) abscissa of the rotation center. Default is current x position
	 * @param $y (int) ordinate of the rotation center. Default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function Rotate($angle, $x='', $y='') {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		$y = ($this->h - $y) * $this->k;
		$x *= $this->k;
		//calculate elements of transformation matrix
		$tm = array();
		$tm[0] = cos(deg2rad($angle));
		$tm[1] = sin(deg2rad($angle));
		$tm[2] = -$tm[1];
		$tm[3] = $tm[0];
		$tm[4] = $x + ($tm[1] * $y) - ($tm[0] * $x);
		$tm[5] = $y - ($tm[0] * $y) - ($tm[1] * $x);
		//rotate the coordinate system around ($x,$y)
		$this->Transform($tm);
	}

	/**
	 * Skew horizontally.
	 * @param $angle_x (float) angle in degrees between -90 (skew to the left) and 90 (skew to the right)
	 * @param $x (int) abscissa of the skewing center. default is current x position
	 * @param $y (int) ordinate of the skewing center. default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function SkewX($angle_x, $x='', $y='') {
		$this->Skew($angle_x, 0, $x, $y);
	}

	/**
	 * Skew vertically.
	 * @param $angle_y (float) angle in degrees between -90 (skew to the bottom) and 90 (skew to the top)
	 * @param $x (int) abscissa of the skewing center. default is current x position
	 * @param $y (int) ordinate of the skewing center. default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function SkewY($angle_y, $x='', $y='') {
		$this->Skew(0, $angle_y, $x, $y);
	}

	/**
	 * Skew.
	 * @param $angle_x (float) angle in degrees between -90 (skew to the left) and 90 (skew to the right)
	 * @param $angle_y (float) angle in degrees between -90 (skew to the bottom) and 90 (skew to the top)
	 * @param $x (int) abscissa of the skewing center. default is current x position
	 * @param $y (int) ordinate of the skewing center. default is current y position
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	public function Skew($angle_x, $angle_y, $x='', $y='') {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		if (($angle_x <= -90) OR ($angle_x >= 90) OR ($angle_y <= -90) OR ($angle_y >= 90)) {
			$this->Error('Please use values between -90 and +90 degrees for Skewing.');
		}
		$x *= $this->k;
		$y = ($this->h - $y) * $this->k;
		//calculate elements of transformation matrix
		$tm = array();
		$tm[0] = 1;
		$tm[1] = tan(deg2rad($angle_y));
		$tm[2] = tan(deg2rad($angle_x));
		$tm[3] = 1;
		$tm[4] = -$tm[2] * $y;
		$tm[5] = -$tm[1] * $x;
		//skew the coordinate system
		$this->Transform($tm);
	}

	/**
	 * Apply graphic transformations.
	 * @param $tm (array) transformation matrix
	 * @protected
	 * @since 2.1.000 (2008-01-07)
	 * @see StartTransform(), StopTransform()
	 */
	protected function Transform($tm) {
		if ($this->state != 2) {
			return;
		}
		$this->_out(sprintf('%F %F %F %F %F %F cm', $tm[0], $tm[1], $tm[2], $tm[3], $tm[4], $tm[5]));
		// add tranformation matrix
		$this->transfmatrix[$this->transfmatrix_key][] = array('a' => $tm[0], 'b' => $tm[1], 'c' => $tm[2], 'd' => $tm[3], 'e' => $tm[4], 'f' => $tm[5]);
		// update transformation mark
		if ($this->inxobj) {
			// we are inside an XObject template
			if (end($this->xobjects[$this->xobjid]['transfmrk']) !== false) {
				$key = key($this->xobjects[$this->xobjid]['transfmrk']);
				$this->xobjects[$this->xobjid]['transfmrk'][$key] = strlen($this->xobjects[$this->xobjid]['outdata']);
			}
		} elseif (end($this->transfmrk[$this->page]) !== false) {
			$key = key($this->transfmrk[$this->page]);
			$this->transfmrk[$this->page][$key] = $this->pagelen[$this->page];
		}
	}

	// END TRANSFORMATIONS SECTION -------------------------

	// START GRAPHIC FUNCTIONS SECTION ---------------------
	// The following section is based on the code provided by David Hernandez Sanz

	/**
	 * Defines the line width. By default, the value equals 0.2 mm. The method can be called before the first page is created and the value is retained from page to page.
	 * @param $width (float) The width.
	 * @public
	 * @since 1.0
	 * @see Line(), Rect(), Cell(), MultiCell()
	 */
	public function SetLineWidth($width) {
		//Set line width
		$this->LineWidth = $width;
		$this->linestyleWidth = sprintf('%F w', ($width * $this->k));
		if ($this->state == 2) {
			$this->_out($this->linestyleWidth);
		}
	}

	/**
	 * Returns the current the line width.
	 * @return int Line width
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see Line(), SetLineWidth()
	 */
	public function GetLineWidth() {
		return $this->LineWidth;
	}

	/**
	 * Set line style.
	 * @param $style (array) Line style. Array with keys among the following:
	 * <ul>
	 *	 <li>width (float): Width of the line in user units.</li>
	 *	 <li>cap (string): Type of cap to put on the line. Possible values are:
	 * butt, round, square. The difference between "square" and "butt" is that
	 * "square" projects a flat end past the end of the line.</li>
	 *	 <li>join (string): Type of join. Possible values are: miter, round,
	 * bevel.</li>
	 *	 <li>dash (mixed): Dash pattern. Is 0 (without dash) or string with
	 * series of length values, which are the lengths of the on and off dashes.
	 * For example: "2" represents 2 on, 2 off, 2 on, 2 off, ...; "2,1" is 2 on,
	 * 1 off, 2 on, 1 off, ...</li>
	 *	 <li>phase (integer): Modifier on the dash pattern which is used to shift
	 * the point at which the pattern starts.</li>
	 *	 <li>color (array): Draw color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName).</li>
	 * </ul>
	 * @param $ret (boolean) if true do not send the command.
	 * @return string the PDF command
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function SetLineStyle($style, $ret=false) {
		$s = ''; // string to be returned
		if (!is_array($style)) {
			return;
		}
		if (isset($style['width'])) {
			$this->LineWidth = $style['width'];
			$this->linestyleWidth = sprintf('%F w', ($style['width'] * $this->k));
			$s .= $this->linestyleWidth.' ';
		}
		if (isset($style['cap'])) {
			$ca = array('butt' => 0, 'round'=> 1, 'square' => 2);
			if (isset($ca[$style['cap']])) {
				$this->linestyleCap = $ca[$style['cap']].' J';
				$s .= $this->linestyleCap.' ';
			}
		}
		if (isset($style['join'])) {
			$ja = array('miter' => 0, 'round' => 1, 'bevel' => 2);
			if (isset($ja[$style['join']])) {
				$this->linestyleJoin = $ja[$style['join']].' j';
				$s .= $this->linestyleJoin.' ';
			}
		}
		if (isset($style['dash'])) {
			$dash_string = '';
			if ($style['dash']) {
				if (preg_match('/^.+,/', $style['dash']) > 0) {
					$tab = explode(',', $style['dash']);
				} else {
					$tab = array($style['dash']);
				}
				$dash_string = '';
				foreach ($tab as $i => $v) {
					if ($i) {
						$dash_string .= ' ';
					}
					$dash_string .= sprintf('%F', $v);
				}
			}
			if (!isset($style['phase']) OR !$style['dash']) {
				$style['phase'] = 0;
			}
			$this->linestyleDash = sprintf('[%s] %F d', $dash_string, $style['phase']);
			$s .= $this->linestyleDash.' ';
		}
		if (isset($style['color'])) {
			$s .= $this->SetDrawColorArray($style['color'], true).' ';
		}
		if (!$ret AND ($this->state == 2)) {
			$this->_out($s);
		}
		return $s;
	}

	/**
	 * Begin a new subpath by moving the current point to coordinates (x, y), omitting any connecting line segment.
	 * @param $x (float) Abscissa of point.
	 * @param $y (float) Ordinate of point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outPoint($x, $y) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F m', ($x * $this->k), (($this->h - $y) * $this->k)));
		}
	}

	/**
	 * Append a straight line segment from the current point to the point (x, y).
	 * The new current point shall be (x, y).
	 * @param $x (float) Abscissa of end point.
	 * @param $y (float) Ordinate of end point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outLine($x, $y) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F l', ($x * $this->k), (($this->h - $y) * $this->k)));
		}
	}

	/**
	 * Append a rectangle to the current path as a complete subpath, with lower-left corner (x, y) and dimensions widthand height in user space.
	 * @param $x (float) Abscissa of upper-left corner.
	 * @param $y (float) Ordinate of upper-left corner.
	 * @param $w (float) Width.
	 * @param $h (float) Height.
	 * @param $op (string) options
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outRect($x, $y, $w, $h, $op) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F re %s', ($x * $this->k), (($this->h - $y) * $this->k), ($w * $this->k), (-$h * $this->k), $op));
		}
	}

	/**
	 * Append a cubic B\E9zier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using (x1, y1) and (x2, y2) as the B\E9zier control points.
	 * The new current point shall be (x3, y3).
	 * @param $x1 (float) Abscissa of control point 1.
	 * @param $y1 (float) Ordinate of control point 1.
	 * @param $x2 (float) Abscissa of control point 2.
	 * @param $y2 (float) Ordinate of control point 2.
	 * @param $x3 (float) Abscissa of end point.
	 * @param $y3 (float) Ordinate of end point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outCurve($x1, $y1, $x2, $y2, $x3, $y3) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F %F %F c', ($x1 * $this->k), (($this->h - $y1) * $this->k), ($x2 * $this->k), (($this->h - $y2) * $this->k), ($x3 * $this->k), (($this->h - $y3) * $this->k)));
		}
	}

	/**
	 * Append a cubic B\E9zier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using the current point and (x2, y2) as the B\E9zier control points.
	 * The new current point shall be (x3, y3).
	 * @param $x2 (float) Abscissa of control point 2.
	 * @param $y2 (float) Ordinate of control point 2.
	 * @param $x3 (float) Abscissa of end point.
	 * @param $y3 (float) Ordinate of end point.
	 * @protected
	 * @since 4.9.019 (2010-04-26)
	 */
	protected function _outCurveV($x2, $y2, $x3, $y3) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F v', ($x2 * $this->k), (($this->h - $y2) * $this->k), ($x3 * $this->k), (($this->h - $y3) * $this->k)));
		}
	}

	/**
	 * Append a cubic B\E9zier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using (x1, y1) and (x3, y3) as the B\E9zier control points.
	 * The new current point shall be (x3, y3).
	 * @param $x1 (float) Abscissa of control point 1.
	 * @param $y1 (float) Ordinate of control point 1.
	 * @param $x3 (float) Abscissa of end point.
	 * @param $y3 (float) Ordinate of end point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outCurveY($x1, $y1, $x3, $y3) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F y', ($x1 * $this->k), (($this->h - $y1) * $this->k), ($x3 * $this->k), (($this->h - $y3) * $this->k)));
		}
	}

	/**
	 * Draws a line between two points.
	 * @param $x1 (float) Abscissa of first point.
	 * @param $y1 (float) Ordinate of first point.
	 * @param $x2 (float) Abscissa of second point.
	 * @param $y2 (float) Ordinate of second point.
	 * @param $style (array) Line style. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @public
	 * @since 1.0
	 * @see SetLineWidth(), SetDrawColor(), SetLineStyle()
	 */
	public function Line($x1, $y1, $x2, $y2, $style=array()) {
		if ($this->state != 2) {
			return;
		}
		if (is_array($style)) {
			$this->SetLineStyle($style);
		}
		$this->_outPoint($x1, $y1);
		$this->_outLine($x2, $y2);
		$this->_out('S');
	}

	/**
	 * Draws a rectangle.
	 * @param $x (float) Abscissa of upper-left corner.
	 * @param $y (float) Ordinate of upper-left corner.
	 * @param $w (float) Width.
	 * @param $h (float) Height.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $border_style (array) Border style of rectangle. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all borders. Array like for SetLineStyle().</li>
	 *	 <li>L, T, R, B or combinations: Line style of left, top, right or bottom border. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, the correspondent border is not drawn. Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @since 1.0
	 * @see SetLineStyle()
	 */
	public function Rect($x, $y, $w, $h, $style='', $border_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (empty($style)) {
			$style = 'S';
		}
		if (!(strpos($style, 'F') === false) AND !empty($fill_color)) {
			// set background color
			$this->SetFillColorArray($fill_color);
		}
		if (!empty($border_style)) {
			if (isset($border_style['all']) AND !empty($border_style['all'])) {
				//set global style for border
				$this->SetLineStyle($border_style['all']);
				$border_style = array();
			} else {
				// remove stroke operator from style
				$opnostroke = array('S' => '', 'D' => '', 's' => '', 'd' => '', 'B' => 'F', 'FD' => 'F', 'DF' => 'F', 'B*' => 'F*', 'F*D' => 'F*', 'DF*' => 'F*', 'b' => 'f', 'fd' => 'f', 'df' => 'f', 'b*' => 'f*', 'f*d' => 'f*', 'df*' => 'f*' );
				if (isset($opnostroke[$style])) {
					$style = $opnostroke[$style];
				}
			}
		}
		if (!empty($style)) {
			$op = TCPDF_STATIC::getPathPaintOperator($style);
			$this->_outRect($x, $y, $w, $h, $op);
		}
		if (!empty($border_style)) {
			$border_style2 = array();
			foreach ($border_style as $line => $value) {
				$length = strlen($line);
				for ($i = 0; $i < $length; ++$i) {
					$border_style2[$line[$i]] = $value;
				}
			}
			$border_style = $border_style2;
			if (isset($border_style['L']) AND $border_style['L']) {
				$this->Line($x, $y, $x, $y + $h, $border_style['L']);
			}
			if (isset($border_style['T']) AND $border_style['T']) {
				$this->Line($x, $y, $x + $w, $y, $border_style['T']);
			}
			if (isset($border_style['R']) AND $border_style['R']) {
				$this->Line($x + $w, $y, $x + $w, $y + $h, $border_style['R']);
			}
			if (isset($border_style['B']) AND $border_style['B']) {
				$this->Line($x, $y + $h, $x + $w, $y + $h, $border_style['B']);
			}
		}
	}

	/**
	 * Draws a Bezier curve.
	 * The Bezier curve is a tangent to the line between the control points at
	 * either end of the curve.
	 * @param $x0 (float) Abscissa of start point.
	 * @param $y0 (float) Ordinate of start point.
	 * @param $x1 (float) Abscissa of control point 1.
	 * @param $y1 (float) Ordinate of control point 1.
	 * @param $x2 (float) Abscissa of control point 2.
	 * @param $y2 (float) Ordinate of control point 2.
	 * @param $x3 (float) Abscissa of end point.
	 * @param $y3 (float) Ordinate of end point.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of curve. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @see SetLineStyle()
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Curve($x0, $y0, $x1, $y1, $x2, $y2, $x3, $y3, $style='', $line_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (!(false === strpos($style, 'F')) AND isset($fill_color)) {
			$this->SetFillColorArray($fill_color);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($line_style) {
			$this->SetLineStyle($line_style);
		}
		$this->_outPoint($x0, $y0);
		$this->_outCurve($x1, $y1, $x2, $y2, $x3, $y3);
		$this->_out($op);
	}

	/**
	 * Draws a poly-Bezier curve.
	 * Each Bezier curve segment is a tangent to the line between the control points at
	 * either end of the curve.
	 * @param $x0 (float) Abscissa of start point.
	 * @param $y0 (float) Ordinate of start point.
	 * @param $segments (float) An array of bezier descriptions. Format: array(x1, y1, x2, y2, x3, y3).
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of curve. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @see SetLineStyle()
	 * @since 3.0008 (2008-05-12)
	 */
	public function Polycurve($x0, $y0, $segments, $style='', $line_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (!(false === strpos($style, 'F')) AND isset($fill_color)) {
			$this->SetFillColorArray($fill_color);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		if ($line_style) {
			$this->SetLineStyle($line_style);
		}
		$this->_outPoint($x0, $y0);
		foreach ($segments as $segment) {
			list($x1, $y1, $x2, $y2, $x3, $y3) = $segment;
			$this->_outCurve($x1, $y1, $x2, $y2, $x3, $y3);
		}
		$this->_out($op);
	}

	/**
	 * Draws an ellipse.
	 * An ellipse is formed from n Bezier curves.
	 * @param $x0 (float) Abscissa of center point.
	 * @param $y0 (float) Ordinate of center point.
	 * @param $rx (float) Horizontal radius.
	 * @param $ry (float) Vertical radius (if ry = 0 then is a circle, see Circle()). Default value: 0.
	 * @param $angle: (float) Angle oriented (anti-clockwise). Default value: 0.
	 * @param $astart: (float) Angle start of draw line. Default value: 0.
	 * @param $afinish: (float) Angle finish of draw line. Default value: 360.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of ellipse. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @param $nc (integer) Number of curves used to draw a 90 degrees portion of ellipse.
	 * @author Nicola Asuni
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Ellipse($x0, $y0, $rx, $ry='', $angle=0, $astart=0, $afinish=360, $style='', $line_style=array(), $fill_color=array(), $nc=2) {
		if ($this->state != 2) {
			return;
		}
		if (TCPDF_STATIC::empty_string($ry) OR ($ry == 0)) {
			$ry = $rx;
		}
		if (!(false === strpos($style, 'F')) AND isset($fill_color)) {
			$this->SetFillColorArray($fill_color);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		if ($line_style) {
			$this->SetLineStyle($line_style);
		}
		$this->_outellipticalarc($x0, $y0, $rx, $ry, $angle, $astart, $afinish, false, $nc, true, true, false);
		$this->_out($op);
	}

	/**
	 * Append an elliptical arc to the current path.
	 * An ellipse is formed from n Bezier curves.
	 * @param $xc (float) Abscissa of center point.
	 * @param $yc (float) Ordinate of center point.
	 * @param $rx (float) Horizontal radius.
	 * @param $ry (float) Vertical radius (if ry = 0 then is a circle, see Circle()). Default value: 0.
	 * @param $xang: (float) Angle between the X-axis and the major axis of the ellipse. Default value: 0.
	 * @param $angs: (float) Angle start of draw line. Default value: 0.
	 * @param $angf: (float) Angle finish of draw line. Default value: 360.
	 * @param $pie (boolean) if true do not mark the border point (used to draw pie sectors).
	 * @param $nc (integer) Number of curves used to draw a 90 degrees portion of ellipse.
	 * @param $startpoint (boolean) if true output a starting point.
	 * @param $ccw (boolean) if true draws in counter-clockwise.
	 * @param $svg (boolean) if true the angles are in svg mode (already calculated).
	 * @return array bounding box coordinates (x min, y min, x max, y max)
	 * @author Nicola Asuni
	 * @protected
	 * @since 4.9.019 (2010-04-26)
	 */
	protected function _outellipticalarc($xc, $yc, $rx, $ry, $xang=0, $angs=0, $angf=360, $pie=false, $nc=2, $startpoint=true, $ccw=true, $svg=false) {
		if (($rx <= 0) OR ($ry < 0)) {
			return;
		}
		$k = $this->k;
		if ($nc < 2) {
			$nc = 2;
		}
		$xmin = 2147483647;
		$ymin = 2147483647;
		$xmax = 0;
		$ymax = 0;
		if ($pie) {
			// center of the arc
			$this->_outPoint($xc, $yc);
		}
		$xang = deg2rad((float) $xang);
		$angs = deg2rad((float) $angs);
		$angf = deg2rad((float) $angf);
		if ($svg) {
			$as = $angs;
			$af = $angf;
		} else {
			$as = atan2((sin($angs) / $ry), (cos($angs) / $rx));
			$af = atan2((sin($angf) / $ry), (cos($angf) / $rx));
		}
		if ($as < 0) {
			$as += (2 * M_PI);
		}
		if ($af < 0) {
			$af += (2 * M_PI);
		}
		if ($ccw AND ($as > $af)) {
			// reverse rotation
			$as -= (2 * M_PI);
		} elseif (!$ccw AND ($as < $af)) {
			// reverse rotation
			$af -= (2 * M_PI);
		}
		$total_angle = ($af - $as);
		if ($nc < 2) {
			$nc = 2;
		}
		// total arcs to draw
		$nc *= (2 * abs($total_angle) / M_PI);
		$nc = round($nc) + 1;
		// angle of each arc
		$arcang = ($total_angle / $nc);
		// center point in PDF coordinates
		$x0 = $xc;
		$y0 = ($this->h - $yc);
		// starting angle
		$ang = $as;
		$alpha = sin($arcang) * ((sqrt(4 + (3 * pow(tan(($arcang) / 2), 2))) - 1) / 3);
		$cos_xang = cos($xang);
		$sin_xang = sin($xang);
		$cos_ang = cos($ang);
		$sin_ang = sin($ang);
		// first arc point
		$px1 = $x0 + ($rx * $cos_xang * $cos_ang) - ($ry * $sin_xang * $sin_ang);
		$py1 = $y0 + ($rx * $sin_xang * $cos_ang) + ($ry * $cos_xang * $sin_ang);
		// first Bezier control point
		$qx1 = ($alpha * ((-$rx * $cos_xang * $sin_ang) - ($ry * $sin_xang * $cos_ang)));
		$qy1 = ($alpha * ((-$rx * $sin_xang * $sin_ang) + ($ry * $cos_xang * $cos_ang)));
		if ($pie) {
			// line from center to arc starting point
			$this->_outLine($px1, $this->h - $py1);
		} elseif ($startpoint) {
			// arc starting point
			$this->_outPoint($px1, $this->h - $py1);
		}
		// draw arcs
		for ($i = 1; $i <= $nc; ++$i) {
			// starting angle
			$ang = $as + ($i * $arcang);
			if ($i == $nc) {
				$ang = $af;
			}
			$cos_ang = cos($ang);
			$sin_ang = sin($ang);
			// second arc point
			$px2 = $x0 + ($rx * $cos_xang * $cos_ang) - ($ry * $sin_xang * $sin_ang);
			$py2 = $y0 + ($rx * $sin_xang * $cos_ang) + ($ry * $cos_xang * $sin_ang);
			// second Bezier control point
			$qx2 = ($alpha * ((-$rx * $cos_xang * $sin_ang) - ($ry * $sin_xang * $cos_ang)));
			$qy2 = ($alpha * ((-$rx * $sin_xang * $sin_ang) + ($ry * $cos_xang * $cos_ang)));
			// draw arc
			$cx1 = ($px1 + $qx1);
			$cy1 = ($this->h - ($py1 + $qy1));
			$cx2 = ($px2 - $qx2);
			$cy2 = ($this->h - ($py2 - $qy2));
			$cx3 = $px2;
			$cy3 = ($this->h - $py2);
			$this->_outCurve($cx1, $cy1, $cx2, $cy2, $cx3, $cy3);
			// get bounding box coordinates
			$xmin = min($xmin, $cx1, $cx2, $cx3);
			$ymin = min($ymin, $cy1, $cy2, $cy3);
			$xmax = max($xmax, $cx1, $cx2, $cx3);
			$ymax = max($ymax, $cy1, $cy2, $cy3);
			// move to next point
			$px1 = $px2;
			$py1 = $py2;
			$qx1 = $qx2;
			$qy1 = $qy2;
		}
		if ($pie) {
			$this->_outLine($xc, $yc);
			// get bounding box coordinates
			$xmin = min($xmin, $xc);
			$ymin = min($ymin, $yc);
			$xmax = max($xmax, $xc);
			$ymax = max($ymax, $yc);
		}
		return array($xmin, $ymin, $xmax, $ymax);
	}

	/**
	 * Draws a circle.
	 * A circle is formed from n Bezier curves.
	 * @param $x0 (float) Abscissa of center point.
	 * @param $y0 (float) Ordinate of center point.
	 * @param $r (float) Radius.
	 * @param $angstr: (float) Angle start of draw line. Default value: 0.
	 * @param $angend: (float) Angle finish of draw line. Default value: 360.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of circle. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(red, green, blue). Default value: default color (empty array).
	 * @param $nc (integer) Number of curves used to draw a 90 degrees portion of circle.
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Circle($x0, $y0, $r, $angstr=0, $angend=360, $style='', $line_style=array(), $fill_color=array(), $nc=2) {
		$this->Ellipse($x0, $y0, $r, $r, 0, $angstr, $angend, $style, $line_style, $fill_color, $nc);
	}

	/**
	 * Draws a polygonal line
	 * @param $p (array) Points 0 to ($np - 1). Array with values (x0, y0, x1, y1,..., x(np-1), y(np - 1))
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of polygon. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all lines. Array like for SetLineStyle().</li>
	 *	 <li>0 to ($np - 1): Line style of each line. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the line. Default value is default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @since 4.8.003 (2009-09-15)
	 * @public
	 */
	public function PolyLine($p, $style='', $line_style=array(), $fill_color=array()) {
		$this->Polygon($p, $style, $line_style, $fill_color, false);
	}

	/**
	 * Draws a polygon.
	 * @param $p (array) Points 0 to ($np - 1). Array with values (x0, y0, x1, y1,..., x(np-1), y(np - 1))
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of polygon. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all lines. Array like for SetLineStyle().</li>
	 *	 <li>0 to ($np - 1): Line style of each line. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the line. Default value is default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @param $closed (boolean) if true the polygon is closes, otherwise will remain open
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Polygon($p, $style='', $line_style=array(), $fill_color=array(), $closed=true) {
		if ($this->state != 2) {
			return;
		}
		$nc = count($p); // number of coordinates
		$np = $nc / 2; // number of points
		if ($closed) {
			// close polygon by adding the first 2 points at the end (one line)
			for ($i = 0; $i < 4; ++$i) {
				$p[$nc + $i] = $p[$i];
			}
			// copy style for the last added line
			if (isset($line_style[0])) {
				$line_style[$np] = $line_style[0];
			}
			$nc += 4;
		}
		if (!(false === strpos($style, 'F')) AND isset($fill_color)) {
			$this->SetFillColorArray($fill_color);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		$draw = true;
		if ($line_style) {
			if (isset($line_style['all'])) {
				$this->SetLineStyle($line_style['all']);
			} else {
				$draw = false;
				if ($op == 'B') {
					// draw fill
					$op = 'f';
					$this->_outPoint($p[0], $p[1]);
					for ($i = 2; $i < $nc; $i = $i + 2) {
						$this->_outLine($p[$i], $p[$i + 1]);
					}
					$this->_out($op);
				}
				// draw outline
				$this->_outPoint($p[0], $p[1]);
				for ($i = 2; $i < $nc; $i = $i + 2) {
					$line_num = ($i / 2) - 1;
					if (isset($line_style[$line_num])) {
						if ($line_style[$line_num] != 0) {
							if (is_array($line_style[$line_num])) {
								$this->_out('S');
								$this->SetLineStyle($line_style[$line_num]);
								$this->_outPoint($p[$i - 2], $p[$i - 1]);
								$this->_outLine($p[$i], $p[$i + 1]);
								$this->_out('S');
								$this->_outPoint($p[$i], $p[$i + 1]);
							} else {
								$this->_outLine($p[$i], $p[$i + 1]);
							}
						}
					} else {
						$this->_outLine($p[$i], $p[$i + 1]);
					}
				}
				$this->_out($op);
			}
		}
		if ($draw) {
			$this->_outPoint($p[0], $p[1]);
			for ($i = 2; $i < $nc; $i = $i + 2) {
				$this->_outLine($p[$i], $p[$i + 1]);
			}
			$this->_out($op);
		}
	}

	/**
	 * Draws a regular polygon.
	 * @param $x0 (float) Abscissa of center point.
	 * @param $y0 (float) Ordinate of center point.
	 * @param $r: (float) Radius of inscribed circle.
	 * @param $ns (integer) Number of sides.
	 * @param $angle (float) Angle oriented (anti-clockwise). Default value: 0.
	 * @param $draw_circle (boolean) Draw inscribed circle or not. Default value: false.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of polygon sides. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all sides. Array like for SetLineStyle().</li>
	 *	 <li>0 to ($ns - 1): Line style of each side. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the side. Default value is default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(red, green, blue). Default value: default color (empty array).
	 * @param $circle_style (string) Style of rendering of inscribed circle (if draws). Possible values are:
	 * <ul>
	 *	 <li>D or empty string: Draw (default).</li>
	 *	 <li>F: Fill.</li>
	 *	 <li>DF or FD: Draw and fill.</li>
	 *	 <li>CNZ: Clipping mode (using the even-odd rule to determine which regions lie inside the clipping path).</li>
	 *	 <li>CEO: Clipping mode (using the nonzero winding number rule to determine which regions lie inside the clipping path).</li>
	 * </ul>
	 * @param $circle_outLine_style (array) Line style of inscribed circle (if draws). Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $circle_fill_color (array) Fill color of inscribed circle (if draws). Format: array(red, green, blue). Default value: default color (empty array).
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function RegularPolygon($x0, $y0, $r, $ns, $angle=0, $draw_circle=false, $style='', $line_style=array(), $fill_color=array(), $circle_style='', $circle_outLine_style=array(), $circle_fill_color=array()) {
		if (3 > $ns) {
			$ns = 3;
		}
		if ($draw_circle) {
			$this->Circle($x0, $y0, $r, 0, 360, $circle_style, $circle_outLine_style, $circle_fill_color);
		}
		$p = array();
		for ($i = 0; $i < $ns; ++$i) {
			$a = $angle + ($i * 360 / $ns);
			$a_rad = deg2rad((float) $a);
			$p[] = $x0 + ($r * sin($a_rad));
			$p[] = $y0 + ($r * cos($a_rad));
		}
		$this->Polygon($p, $style, $line_style, $fill_color);
	}

	/**
	 * Draws a star polygon
	 * @param $x0 (float) Abscissa of center point.
	 * @param $y0 (float) Ordinate of center point.
	 * @param $r (float) Radius of inscribed circle.
	 * @param $nv (integer) Number of vertices.
	 * @param $ng (integer) Number of gap (if ($ng % $nv = 1) then is a regular polygon).
	 * @param $angle: (float) Angle oriented (anti-clockwise). Default value: 0.
	 * @param $draw_circle: (boolean) Draw inscribed circle or not. Default value is false.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of polygon sides. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all sides. Array like for
	 * SetLineStyle().</li>
	 *	 <li>0 to (n - 1): Line style of each side. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the side. Default value is default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(red, green, blue). Default value: default color (empty array).
	 * @param $circle_style (string) Style of rendering of inscribed circle (if draws). Possible values are:
	 * <ul>
	 *	 <li>D or empty string: Draw (default).</li>
	 *	 <li>F: Fill.</li>
	 *	 <li>DF or FD: Draw and fill.</li>
	 *	 <li>CNZ: Clipping mode (using the even-odd rule to determine which regions lie inside the clipping path).</li>
	 *	 <li>CEO: Clipping mode (using the nonzero winding number rule to determine which regions lie inside the clipping path).</li>
	 * </ul>
	 * @param $circle_outLine_style (array) Line style of inscribed circle (if draws). Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $circle_fill_color (array) Fill color of inscribed circle (if draws). Format: array(red, green, blue). Default value: default color (empty array).
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function StarPolygon($x0, $y0, $r, $nv, $ng, $angle=0, $draw_circle=false, $style='', $line_style=array(), $fill_color=array(), $circle_style='', $circle_outLine_style=array(), $circle_fill_color=array()) {
		if ($nv < 2) {
			$nv = 2;
		}
		if ($draw_circle) {
			$this->Circle($x0, $y0, $r, 0, 360, $circle_style, $circle_outLine_style, $circle_fill_color);
		}
		$p2 = array();
		$visited = array();
		for ($i = 0; $i < $nv; ++$i) {
			$a = $angle + ($i * 360 / $nv);
			$a_rad = deg2rad((float) $a);
			$p2[] = $x0 + ($r * sin($a_rad));
			$p2[] = $y0 + ($r * cos($a_rad));
			$visited[] = false;
		}
		$p = array();
		$i = 0;
		do {
			$p[] = $p2[$i * 2];
			$p[] = $p2[($i * 2) + 1];
			$visited[$i] = true;
			$i += $ng;
			$i %= $nv;
		} while (!$visited[$i]);
		$this->Polygon($p, $style, $line_style, $fill_color);
	}

	/**
	 * Draws a rounded rectangle.
	 * @param $x (float) Abscissa of upper-left corner.
	 * @param $y (float) Ordinate of upper-left corner.
	 * @param $w (float) Width.
	 * @param $h (float) Height.
	 * @param $r (float) the radius of the circle used to round off the corners of the rectangle.
	 * @param $round_corner (string) Draws rounded corner or not. String with a 0 (not rounded i-corner) or 1 (rounded i-corner) in i-position. Positions are, in order and begin to 0: top right, bottom right, bottom left and top left. Default value: all rounded corner ("1111").
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $border_style (array) Border style of rectangle. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function RoundedRect($x, $y, $w, $h, $r, $round_corner='1111', $style='', $border_style=array(), $fill_color=array()) {
		$this->RoundedRectXY($x, $y, $w, $h, $r, $r, $round_corner, $style, $border_style, $fill_color);
	}

	/**
	 * Draws a rounded rectangle.
	 * @param $x (float) Abscissa of upper-left corner.
	 * @param $y (float) Ordinate of upper-left corner.
	 * @param $w (float) Width.
	 * @param $h (float) Height.
	 * @param $rx (float) the x-axis radius of the ellipse used to round off the corners of the rectangle.
	 * @param $ry (float) the y-axis radius of the ellipse used to round off the corners of the rectangle.
	 * @param $round_corner (string) Draws rounded corner or not. String with a 0 (not rounded i-corner) or 1 (rounded i-corner) in i-position. Positions are, in order and begin to 0: top right, bottom right, bottom left and top left. Default value: all rounded corner ("1111").
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $border_style (array) Border style of rectangle. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @since 4.9.019 (2010-04-22)
	 */
	public function RoundedRectXY($x, $y, $w, $h, $rx, $ry, $round_corner='1111', $style='', $border_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (($round_corner == '0000') OR (($rx == $ry) AND ($rx == 0))) {
			// Not rounded
			$this->Rect($x, $y, $w, $h, $style, $border_style, $fill_color);
			return;
		}
		// Rounded
		if (!(false === strpos($style, 'F')) AND isset($fill_color)) {
			$this->SetFillColorArray($fill_color);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$border_style = array();
		}
		if ($border_style) {
			$this->SetLineStyle($border_style);
		}
		$MyArc = 4 / 3 * (sqrt(2) - 1);
		$this->_outPoint($x + $rx, $y);
		$xc = $x + $w - $rx;
		$yc = $y + $ry;
		$this->_outLine($xc, $y);
		if ($round_corner[0]) {
			$this->_outCurve($xc + ($rx * $MyArc), $yc - $ry, $xc + $rx, $yc - ($ry * $MyArc), $xc + $rx, $yc);
		} else {
			$this->_outLine($x + $w, $y);
		}
		$xc = $x + $w - $rx;
		$yc = $y + $h - $ry;
		$this->_outLine($x + $w, $yc);
		if ($round_corner[1]) {
			$this->_outCurve($xc + $rx, $yc + ($ry * $MyArc), $xc + ($rx * $MyArc), $yc + $ry, $xc, $yc + $ry);
		} else {
			$this->_outLine($x + $w, $y + $h);
		}
		$xc = $x + $rx;
		$yc = $y + $h - $ry;
		$this->_outLine($xc, $y + $h);
		if ($round_corner[2]) {
			$this->_outCurve($xc - ($rx * $MyArc), $yc + $ry, $xc - $rx, $yc + ($ry * $MyArc), $xc - $rx, $yc);
		} else {
			$this->_outLine($x, $y + $h);
		}
		$xc = $x + $rx;
		$yc = $y + $ry;
		$this->_outLine($x, $yc);
		if ($round_corner[3]) {
			$this->_outCurve($xc - $rx, $yc - ($ry * $MyArc), $xc - ($rx * $MyArc), $yc - $ry, $xc, $yc - $ry);
		} else {
			$this->_outLine($x, $y);
			$this->_outLine($x + $rx, $y);
		}
		$this->_out($op);
	}

	/**
	 * Draws a grahic arrow.
	 * @param $x0 (float) Abscissa of first point.
	 * @param $y0 (float) Ordinate of first point.
	 * @param $x1 (float) Abscissa of second point.
	 * @param $y1 (float) Ordinate of second point.
	 * @param $head_style (int) (0 = draw only arrowhead arms, 1 = draw closed arrowhead, but no fill, 2 = closed and filled arrowhead, 3 = filled arrowhead)
	 * @param $arm_size (float) length of arrowhead arms
	 * @param $arm_angle (int) angle between an arm and the shaft
	 * @author Piotr Galecki, Nicola Asuni, Andy Meier
	 * @since 4.6.018 (2009-07-10)
	 */
	public function Arrow($x0, $y0, $x1, $y1, $head_style=0, $arm_size=5, $arm_angle=15) {
		// getting arrow direction angle
		// 0 deg angle is when both arms go along X axis. angle grows clockwise.
		$dir_angle = atan2(($y0 - $y1), ($x0 - $x1));
		if ($dir_angle < 0) {
			$dir_angle += (2 * M_PI);
		}
		$arm_angle = deg2rad($arm_angle);
		$sx1 = $x1;
		$sy1 = $y1;
		if ($head_style > 0) {
			// calculate the stopping point for the arrow shaft
			$sx1 = $x1 + (($arm_size - $this->LineWidth) * cos($dir_angle));
			$sy1 = $y1 + (($arm_size - $this->LineWidth) * sin($dir_angle));
		}
		// main arrow line / shaft
		$this->Line($x0, $y0, $sx1, $sy1);
		// left arrowhead arm tip
		$x2L = $x1 + ($arm_size * cos($dir_angle + $arm_angle));
		$y2L = $y1 + ($arm_size * sin($dir_angle + $arm_angle));
		// right arrowhead arm tip
		$x2R = $x1 + ($arm_size * cos($dir_angle - $arm_angle));
		$y2R = $y1 + ($arm_size * sin($dir_angle - $arm_angle));
		$mode = 'D';
		$style = array();
		switch ($head_style) {
			case 0: {
				// draw only arrowhead arms
				$mode = 'D';
				$style = array(1, 1, 0);
				break;
			}
			case 1: {
				// draw closed arrowhead, but no fill
				$mode = 'D';
				break;
			}
			case 2: {
				// closed and filled arrowhead
				$mode = 'DF';
				break;
			}
			case 3: {
				// filled arrowhead
				$mode = 'F';
				break;
			}
		}
		$this->Polygon(array($x2L, $y2L, $x1, $y1, $x2R, $y2R), $mode, $style, array());
	}

	// END GRAPHIC FUNCTIONS SECTION -----------------------

	/**
	 * Add a Named Destination.
	 * NOTE: destination names are unique, so only last entry will be saved.
	 * @param $name (string) Destination name.
	 * @param $y (float) Y position in user units of the destiantion on the selected page (default = -1 = current position; 0 = page start;).
	 * @param $page (int) Target page number (leave empty for current page).
	 * @param $x (float) X position in user units of the destiantion on the selected page (default = -1 = current position;).
	 * @return (string) Stripped named destination identifier or false in case of error.
	 * @public
	 * @author Christian Deligant, Nicola Asuni
	 * @since 5.9.097 (2011-06-23)
	 */
	public function setDestination($name, $y=-1, $page='', $x=-1) {
		// remove unsupported characters
		$name = TCPDF_STATIC::encodeNameObject($name);
		if (TCPDF_STATIC::empty_string($name)) {
			return false;
		}
		if ($y == -1) {
			$y = $this->GetY();
		} elseif ($y < 0) {
			$y = 0;
		} elseif ($y > $this->h) {
			$y = $this->h;
		}
		if ($x == -1) {
			$x = $this->GetX();
		} elseif ($x < 0) {
			$x = 0;
		} elseif ($x > $this->w) {
			$x = $this->w;
		}
		if (empty($page)) {
			$page = $this->PageNo();
			if (empty($page)) {
				return;
			}
		}
		$this->dests[$name] = array('x' => $x, 'y' => $y, 'p' => $page);
		return $name;
	}

	/**
	 * Return the Named Destination array.
	 * @return (array) Named Destination array.
	 * @public
	 * @author Nicola Asuni
	 * @since 5.9.097 (2011-06-23)
	 */
	public function getDestination() {
		return $this->dests;
	}

	/**
	 * Insert Named Destinations.
	 * @protected
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 5.9.098 (2011-06-23)
	 */
	protected function _putdests() {
		if (empty($this->dests)) {
			return;
		}
		$this->n_dests = $this->_newobj();
		$out = ' <<';
		foreach($this->dests as $name => $o) {
			$out .= ' /'.$name.' '.sprintf('[%u 0 R /XYZ %F %F null]', $this->page_obj_id[($o['p'])], ($o['x'] * $this->k), ($this->pagedim[$o['p']]['h'] - ($o['y'] * $this->k)));
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}

	/**
	 * Adds a bookmark - alias for Bookmark().
	 * @param $txt (string) Bookmark description.
	 * @param $level (int) Bookmark level (minimum value is 0).
	 * @param $y (float) Y position in user units of the bookmark on the selected page (default = -1 = current position; 0 = page start;).
	 * @param $page (int) Target page number (leave empty for current page).
	 * @param $style (string) Font style: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param $color (array) RGB color array (values from 0 to 255).
	 * @param $x (float) X position in user units of the bookmark on the selected page (default = -1 = current position;).
	 * @param $link (mixed) URL, or numerical link ID, or named destination (# character followed by the destination name), or embedded file (* character followed by the file name).
	 * @public
	 */
	public function setBookmark($txt, $level=0, $y=-1, $page='', $style='', $color=array(0,0,0), $x=-1, $link='') {
		$this->Bookmark($txt, $level, $y, $page, $style, $color, $x, $link);
	}

	/**
	 * Adds a bookmark.
	 * @param $txt (string) Bookmark description.
	 * @param $level (int) Bookmark level (minimum value is 0).
	 * @param $y (float) Y position in user units of the bookmark on the selected page (default = -1 = current position; 0 = page start;).
	 * @param $page (int) Target page number (leave empty for current page).
	 * @param $style (string) Font style: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param $color (array) RGB color array (values from 0 to 255).
	 * @param $x (float) X position in user units of the bookmark on the selected page (default = -1 = current position;).
	 * @param $link (mixed) URL, or numerical link ID, or named destination (# character followed by the destination name), or embedded file (* character followed by the file name).
	 * @public
	 * @since 2.1.002 (2008-02-12)
	 */
	public function Bookmark($txt, $level=0, $y=-1, $page='', $style='', $color=array(0,0,0), $x=-1, $link='') {
		if ($level < 0) {
			$level = 0;
		}
		if (isset($this->outlines[0])) {
			$lastoutline = end($this->outlines);
			$maxlevel = $lastoutline['l'] + 1;
		} else {
			$maxlevel = 0;
		}
		if ($level > $maxlevel) {
			$level = $maxlevel;
		}
		if ($y == -1) {
			$y = $this->GetY();
		} elseif ($y < 0) {
			$y = 0;
		} elseif ($y > $this->h) {
			$y = $this->h;
		}
		if ($x == -1) {
			$x = $this->GetX();
		} elseif ($x < 0) {
			$x = 0;
		} elseif ($x > $this->w) {
			$x = $this->w;
		}
		if (empty($page)) {
			$page = $this->PageNo();
			if (empty($page)) {
				return;
			}
		}
		$this->outlines[] = array('t' => $txt, 'l' => $level, 'x' => $x, 'y' => $y, 'p' => $page, 's' => strtoupper($style), 'c' => $color, 'u' => $link);
	}

	/**
	 * Sort bookmarks for page and key.
	 * @protected
	 * @since 5.9.119 (2011-09-19)
	 */
	protected function sortBookmarks() {
		// get sorting columns
		$outline_p = array();
		$outline_y = array();
		foreach ($this->outlines as $key => $row) {
			$outline_p[$key] = $row['p'];
			$outline_k[$key] = $key;
		}
		// sort outlines by page and original position
		array_multisort($outline_p, SORT_NUMERIC, SORT_ASC, $outline_k, SORT_NUMERIC, SORT_ASC, $this->outlines);
	}

	/**
	 * Create a bookmark PDF string.
	 * @protected
	 * @author Olivier Plathey, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	protected function _putbookmarks() {
		$nb = count($this->outlines);
		if ($nb == 0) {
			return;
		}
		// sort bookmarks
		$this->sortBookmarks();
		$lru = array();
		$level = 0;
		foreach ($this->outlines as $i => $o) {
			if ($o['l'] > 0) {
				$parent = $lru[($o['l'] - 1)];
				//Set parent and last pointers
				$this->outlines[$i]['parent'] = $parent;
				$this->outlines[$parent]['last'] = $i;
				if ($o['l'] > $level) {
					//Level increasing: set first pointer
					$this->outlines[$parent]['first'] = $i;
				}
			} else {
				$this->outlines[$i]['parent'] = $nb;
			}
			if (($o['l'] <= $level) AND ($i > 0)) {
				//Set prev and next pointers
				$prev = $lru[$o['l']];
				$this->outlines[$prev]['next'] = $i;
				$this->outlines[$i]['prev'] = $prev;
			}
			$lru[$o['l']] = $i;
			$level = $o['l'];
		}
		//Outline items
		$n = $this->n + 1;
		$nltags = '/<br[\s]?\/>|<\/(blockquote|dd|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|p|pre|ul|tcpdf|table|tr|td)>/si';
		foreach ($this->outlines as $i => $o) {
			$oid = $this->_newobj();
			// covert HTML title to string
			$title = preg_replace($nltags, "\n", $o['t']);
			$title = preg_replace("/[\r]+/si", '', $title);
			$title = preg_replace("/[\n]+/si", "\n", $title);
			$title = strip_tags($title);
			$title = $this->stringTrim($title);
			$out = '<</Title '.$this->_textstring($title, $oid);
			$out .= ' /Parent '.($n + $o['parent']).' 0 R';
			if (isset($o['prev'])) {
				$out .= ' /Prev '.($n + $o['prev']).' 0 R';
			}
			if (isset($o['next'])) {
				$out .= ' /Next '.($n + $o['next']).' 0 R';
			}
			if (isset($o['first'])) {
				$out .= ' /First '.($n + $o['first']).' 0 R';
			}
			if (isset($o['last'])) {
				$out .= ' /Last '.($n + $o['last']).' 0 R';
			}
			if (isset($o['u']) AND !empty($o['u'])) {
				// link
				if (is_string($o['u'])) {
					if ($o['u'][0] == '#') {
						// internal destination
						$out .= ' /Dest /'.TCPDF_STATIC::encodeNameObject(substr($o['u'], 1));
					} elseif ($o['u'][0] == '%') {
						// embedded PDF file
						$filename = basename(substr($o['u'], 1));
						$out .= ' /A <</S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '.($o['p'] - 1).' /A '.$this->embeddedfiles[$filename]['a'].' >> >>';
					} elseif ($o['u'][0] == '*') {
						// embedded generic file
						$filename = basename(substr($o['u'], 1));
						$jsa = 'var D=event.target.doc;var MyData=D.dataObjects;for (var i in MyData) if (MyData[i].path=="'.$filename.'") D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
						$out .= ' /A <</S /JavaScript /JS '.$this->_textstring($jsa, $oid).'>>';
					} else {
						// external URI link
						$out .= ' /A <</S /URI /URI '.$this->_datastring($this->unhtmlentities($o['u']), $oid).'>>';
					}
				} elseif (isset($this->links[$o['u']])) {
					// internal link ID
					$l = $this->links[$o['u']];
					if (isset($this->page_obj_id[($l[0])])) {
						$out .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $this->page_obj_id[($l[0])], ($this->pagedim[$l[0]]['h'] - ($l[1] * $this->k)));
					}
				}
			} elseif (isset($this->page_obj_id[($o['p'])])) {
				// link to a page
				$out .= ' '.sprintf('/Dest [%u 0 R /XYZ %F %F null]', $this->page_obj_id[($o['p'])], ($o['x'] * $this->k), ($this->pagedim[$o['p']]['h'] - ($o['y'] * $this->k)));
			}
			// set font style
			$style = 0;
			if (!empty($o['s'])) {
				// bold
				if (strpos($o['s'], 'B') !== false) {
					$style |= 2;
				}
				// oblique
				if (strpos($o['s'], 'I') !== false) {
					$style |= 1;
				}
			}
			$out .= sprintf(' /F %d', $style);
			// set bookmark color
			if (isset($o['c']) AND is_array($o['c']) AND (count($o['c']) == 3)) {
				$color = array_values($o['c']);
				$out .= sprintf(' /C [%F %F %F]', ($color[0] / 255), ($color[1] / 255), ($color[2] / 255));
			} else {
				// black
				$out .= ' /C [0.0 0.0 0.0]';
			}
			$out .= ' /Count 0'; // normally closed item
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
		//Outline root
		$this->OutlineRoot = $this->_newobj();
		$this->_out('<< /Type /Outlines /First '.$n.' 0 R /Last '.($n + $lru[0]).' 0 R >>'."\n".'endobj');
	}

	// --- JAVASCRIPT ------------------------------------------------------

	/**
	 * Adds a javascript
	 * @param $script (string) Javascript code
	 * @public
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	public function IncludeJS($script) {
		$this->javascript .= $script;
	}

	/**
	 * Adds a javascript object and return object ID
	 * @param $script (string) Javascript code
	 * @param $onload (boolean) if true executes this object when opening the document
	 * @return int internal object ID
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function addJavascriptObject($script, $onload=false) {
		if ($this->pdfa_mode) {
			// javascript is not allowed in PDF/A mode
			return false;
		}
		++$this->n;
		$this->js_objects[$this->n] = array('n' => $this->n, 'js' => $script, 'onload' => $onload);
		return $this->n;
	}

	/**
	 * Create a javascript PDF string.
	 * @protected
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	protected function _putjavascript() {
		if ($this->pdfa_mode OR (empty($this->javascript) AND empty($this->js_objects))) {
			return;
		}
		if (strpos($this->javascript, 'this.addField') > 0) {
			if (!$this->ur['enabled']) {
				//$this->setUserRights();
			}
			// the following two lines are used to avoid form fields duplication after saving
			// The addField method only works when releasing user rights (UR3)
			$jsa = sprintf("ftcpdfdocsaved=this.addField('%s','%s',%d,[%F,%F,%F,%F]);", 'tcpdfdocsaved', 'text', 0, 0, 1, 0, 1);
			$jsb = "getField('tcpdfdocsaved').value='saved';";
			$this->javascript = $jsa."\n".$this->javascript."\n".$jsb;
		}
		// name tree for javascript
		$this->n_js = '<< /Names [';
		if (!empty($this->javascript)) {
			$this->n_js .= ' (EmbeddedJS) '.($this->n + 1).' 0 R';
		}
		if (!empty($this->js_objects)) {
			foreach ($this->js_objects as $key => $val) {
				if ($val['onload']) {
					$this->n_js .= ' (JS'.$key.') '.$key.' 0 R';
				}
			}
		}
		$this->n_js .= ' ] >>';
		// default Javascript object
		if (!empty($this->javascript)) {
			$obj_id = $this->_newobj();
			$out = '<< /S /JavaScript';
			$out .= ' /JS '.$this->_textstring($this->javascript, $obj_id);
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
		// additional Javascript objects
		if (!empty($this->js_objects)) {
			foreach ($this->js_objects as $key => $val) {
				$out = $this->_getobj($key)."\n".' << /S /JavaScript /JS '.$this->_textstring($val['js'], $key).' >>'."\n".'endobj';
				$this->_out($out);
			}
		}
	}

	/**
	 * Adds a javascript form field.
	 * @param $type (string) field type
	 * @param $name (string) field name
	 * @param $x (int) horizontal position
	 * @param $y (int) vertical position
	 * @param $w (int) width
	 * @param $h (int) height
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @protected
	 * @author Denis Van Nuffelen, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	protected function _addfield($type, $name, $x, $y, $w, $h, $prop) {
		if ($this->rtl) {
			$x = $x - $w;
		}
		// the followind avoid fields duplication after saving the document
		$this->javascript .= "if (getField('tcpdfdocsaved').value != 'saved') {";
		$k = $this->k;
		$this->javascript .= sprintf("f".$name."=this.addField('%s','%s',%u,[%F,%F,%F,%F]);", $name, $type, $this->PageNo()-1, $x*$k, ($this->h-$y)*$k+1, ($x+$w)*$k, ($this->h-$y-$h)*$k+1)."\n";
		$this->javascript .= 'f'.$name.'.textSize='.$this->FontSizePt.";\n";
		while (list($key, $val) = each($prop)) {
			if (strcmp(substr($key, -5), 'Color') == 0) {
				$val = TCPDF_COLORS::_JScolor($val);
			} else {
				$val = "'".$val."'";
			}
			$this->javascript .= 'f'.$name.'.'.$key.'='.$val.";\n";
		}
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
		$this->javascript .= '}';
	}

	// --- FORM FIELDS -----------------------------------------------------



	/**
	 * Set default properties for form fields.
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-06)
	 */
	public function setFormDefaultProp($prop=array()) {
		$this->default_form_prop = $prop;
	}

	/**
	 * Return the default properties for form fields.
	 * @return array $prop javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-06)
	 */
	public function getFormDefaultProp() {
		return $this->default_form_prop;
	}

	/**
	 * Creates a text field
	 * @param $name (string) field name
	 * @param $w (float) Width of the rectangle
	 * @param $h (float) Height of the rectangle
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) if true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function TextField($name, $w, $h, $prop=array(), $opt=array(), $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		if ($js) {
			$this->_addfield('text', $name, $x, $y, $w, $h, $prop);
			return;
		}
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		// set default appearance stream
		$this->annotation_fonts[$this->CurrentFont['fontkey']] = $this->CurrentFont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $this->CurrentFont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = '/Tx BMC q '.$fontstyle.' ';
		$text = '';
		if (isset($prop['value']) AND !empty($prop['value'])) {
			$text = $prop['value'];
		} elseif (isset($opt['v']) AND !empty($opt['v'])) {
			$text = $opt['v'];
		}
		$tmpid = $this->startTemplate($w, $h, false);
		$align = '';
		if (isset($popt['q'])) {
			switch ($popt['q']) {
				case 0: {
					$align = 'L';
					break;
				}
				case 1: {
					$align = 'C';
					break;
				}
				case 2: {
					$align = 'R';
					break;
				}
				default: {
					$align = '';
					break;
				}
			}
		}
		$this->MultiCell($w, $h, $text, 0, $align, false, 0, 0, 0, true, 0, false, true, 0, 'T', false);
		$this->endTemplate();
		--$this->n;
		$popt['ap']['n'] .= $this->xobjects[$tmpid]['outdata'];
		unset($this->xobjects[$tmpid]);
		$popt['ap']['n'] .= 'Q EMC';
		// merge options
		$opt = array_merge($popt, $opt);
		// remove some conflicting options
		unset($opt['bs']);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Tx';
		$opt['t'] = $name;
		// Additional annotation's parameters (check _putannotsobj() method):
		//$opt['f']
		//$opt['as']
		//$opt['bs']
		//$opt['be']
		//$opt['c']
		//$opt['border']
		//$opt['h']
		//$opt['mk'];
		//$opt['mk']['r']
		//$opt['mk']['bc'];
		//$opt['mk']['bg'];
		unset($opt['mk']['ca']);
		unset($opt['mk']['rc']);
		unset($opt['mk']['ac']);
		unset($opt['mk']['i']);
		unset($opt['mk']['ri']);
		unset($opt['mk']['ix']);
		unset($opt['mk']['if']);
		//$opt['mk']['if']['sw'];
		//$opt['mk']['if']['s'];
		//$opt['mk']['if']['a'];
		//$opt['mk']['if']['fb'];
		unset($opt['mk']['tp']);
		//$opt['tu']
		//$opt['tm']
		//$opt['ff']
		//$opt['v']
		//$opt['dv']
		//$opt['a']
		//$opt['aa']
		//$opt['q']
		$this->Annotation($x, $y, $w, $h, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	/**
	 * Creates a RadioButton field.
	 * @param $name (string) Field name.
	 * @param $w (int) Width of the radio button.
	 * @param $prop (array) Javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) Annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $onvalue (string) Value to be returned if selected.
	 * @param $checked (boolean) Define the initial state.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) If true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function RadioButton($name, $w, $prop=array(), $opt=array(), $onvalue='On', $checked=false, $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($w, $x, $y);
		if ($js) {
			$this->_addfield('radiobutton', $name, $x, $y, $w, $w, $prop);
			return;
		}
		if (TCPDF_STATIC::empty_string($onvalue)) {
			$onvalue = 'On';
		}
		if ($checked) {
			$defval = $onvalue;
		} else {
			$defval = 'Off';
		}
		// set font
		$font = 'zapfdingbats';
		if ($this->pdfa_mode) {
			// all fonts must be embedded
			$font = 'pdfa'.$font;
		}
		$this->AddFont($font);
		$tmpfont = $this->getFontBuffer($font);
		// set data for parent group
		if (!isset($this->radiobutton_groups[$this->page])) {
			$this->radiobutton_groups[$this->page] = array();
		}
		if (!isset($this->radiobutton_groups[$this->page][$name])) {
			$this->radiobutton_groups[$this->page][$name] = array();
			++$this->n;
			$this->radiobutton_groups[$this->page][$name]['n'] = $this->n;
			$this->radio_groups[] = $this->n;
		}
		$kid = ($this->n + 1);
		// save object ID to be added on Kids entry on parent object
		$this->radiobutton_groups[$this->page][$name][] = array('kid' => $kid, 'def' => $defval);
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		$prop['NoToggleToOff'] = 'true';
		$prop['Radio'] = 'true';
		$prop['borderStyle'] = 'inset';
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		// set additional default options
		$this->annotation_fonts[$tmpfont['fontkey']] = $tmpfont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $tmpfont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = array();
		$fx = ((($w - $this->getAbsFontMeasure($tmpfont['cw'][108])) / 2) * $this->k);
		$fy = (($w - ((($tmpfont['desc']['Ascent'] - $tmpfont['desc']['Descent']) * $this->FontSizePt / 1000) / $this->k)) * $this->k);
		$popt['ap']['n'][$onvalue] = sprintf('q %s BT /F%d %F Tf %F %F Td ('.chr(108).') Tj ET Q', $this->TextColor, $tmpfont['i'], $this->FontSizePt, $fx, $fy);
		$popt['ap']['n']['Off'] = sprintf('q %s BT /F%d %F Tf %F %F Td ('.chr(109).') Tj ET Q', $this->TextColor, $tmpfont['i'], $this->FontSizePt, $fx, $fy);
		if (!isset($popt['mk'])) {
			$popt['mk'] = array();
		}
		$popt['mk']['ca'] = '(l)';
		// merge options
		$opt = array_merge($popt, $opt);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Btn';
		if ($checked) {
			$opt['v'] = array('/'.$onvalue);
			$opt['as'] = $onvalue;
		} else {
			$opt['as'] = 'Off';
		}
		// store readonly flag
		if (!isset($this->radiobutton_groups[$this->page][$name]['#readonly#'])) {
			$this->radiobutton_groups[$this->page][$name]['#readonly#'] = false;
		}
		$this->radiobutton_groups[$this->page][$name]['#readonly#'] |= ($opt['f'] & 64);
		$this->Annotation($x, $y, $w, $w, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	/**
	 * Creates a List-box field
	 * @param $name (string) field name
	 * @param $w (int) width
	 * @param $h (int) height
	 * @param $values (array) array containing the list of values.
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) if true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function ListBox($name, $w, $h, $values, $prop=array(), $opt=array(), $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		if ($js) {
			$this->_addfield('listbox', $name, $x, $y, $w, $h, $prop);
			$s = '';
			foreach ($values as $value) {
				if (is_array($value)) {
					$s .= ',[\''.addslashes($value[1]).'\',\''.addslashes($value[0]).'\']';
				} else {
					$s .= ',[\''.addslashes($value).'\',\''.addslashes($value).'\']';
				}
			}
			$this->javascript .= 'f'.$name.'.setItems('.substr($s, 1).');'."\n";
			return;
		}
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		// set additional default values
		$this->annotation_fonts[$this->CurrentFont['fontkey']] = $this->CurrentFont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $this->CurrentFont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = '/Tx BMC q '.$fontstyle.' ';
		$text = '';
		foreach($values as $item) {
			if (is_array($item)) {
				$text .= $item[1]."\n";
			} else {
				$text .= $item."\n";
			}
		}
		$tmpid = $this->startTemplate($w, $h, false);
		$this->MultiCell($w, $h, $text, 0, '', false, 0, 0, 0, true, 0, false, true, 0, 'T', false);
		$this->endTemplate();
		--$this->n;
		$popt['ap']['n'] .= $this->xobjects[$tmpid]['outdata'];
		unset($this->xobjects[$tmpid]);
		$popt['ap']['n'] .= 'Q EMC';
		// merge options
		$opt = array_merge($popt, $opt);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Ch';
		$opt['t'] = $name;
		$opt['opt'] = $values;
		unset($opt['mk']['ca']);
		unset($opt['mk']['rc']);
		unset($opt['mk']['ac']);
		unset($opt['mk']['i']);
		unset($opt['mk']['ri']);
		unset($opt['mk']['ix']);
		unset($opt['mk']['if']);
		unset($opt['mk']['tp']);
		$this->Annotation($x, $y, $w, $h, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	/**
	 * Creates a Combo-box field
	 * @param $name (string) field name
	 * @param $w (int) width
	 * @param $h (int) height
	 * @param $values (array) array containing the list of values.
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) if true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function ComboBox($name, $w, $h, $values, $prop=array(), $opt=array(), $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		if ($js) {
			$this->_addfield('combobox', $name, $x, $y, $w, $h, $prop);
			$s = '';
			foreach ($values as $value) {
				if (is_array($value)) {
					$s .= ',[\''.addslashes($value[1]).'\',\''.addslashes($value[0]).'\']';
				} else {
					$s .= ',[\''.addslashes($value).'\',\''.addslashes($value).'\']';
				}
			}
			$this->javascript .= 'f'.$name.'.setItems('.substr($s, 1).');'."\n";
			return;
		}
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		$prop['Combo'] = true;
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		// set additional default options
		$this->annotation_fonts[$this->CurrentFont['fontkey']] = $this->CurrentFont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $this->CurrentFont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = '/Tx BMC q '.$fontstyle.' ';
		$text = '';
		foreach($values as $item) {
			if (is_array($item)) {
				$text .= $item[1]."\n";
			} else {
				$text .= $item."\n";
			}
		}
		$tmpid = $this->startTemplate($w, $h, false);
		$this->MultiCell($w, $h, $text, 0, '', false, 0, 0, 0, true, 0, false, true, 0, 'T', false);
		$this->endTemplate();
		--$this->n;
		$popt['ap']['n'] .= $this->xobjects[$tmpid]['outdata'];
		unset($this->xobjects[$tmpid]);
		$popt['ap']['n'] .= 'Q EMC';
		// merge options
		$opt = array_merge($popt, $opt);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Ch';
		$opt['t'] = $name;
		$opt['opt'] = $values;
		unset($opt['mk']['ca']);
		unset($opt['mk']['rc']);
		unset($opt['mk']['ac']);
		unset($opt['mk']['i']);
		unset($opt['mk']['ri']);
		unset($opt['mk']['ix']);
		unset($opt['mk']['if']);
		unset($opt['mk']['tp']);
		$this->Annotation($x, $y, $w, $h, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	/**
	 * Creates a CheckBox field
	 * @param $name (string) field name
	 * @param $w (int) width
	 * @param $checked (boolean) define the initial state.
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $onvalue (string) value to be returned if selected.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) if true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function CheckBox($name, $w, $checked=false, $prop=array(), $opt=array(), $onvalue='Yes', $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($w, $x, $y);
		if ($js) {
			$this->_addfield('checkbox', $name, $x, $y, $w, $w, $prop);
			return;
		}
		if (!isset($prop['value'])) {
			$prop['value'] = array('Yes');
		}
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		$prop['borderStyle'] = 'inset';
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		// set additional default options
		$font = 'zapfdingbats';
		if ($this->pdfa_mode) {
			// all fonts must be embedded
			$font = 'pdfa'.$font;
		}
		$this->AddFont($font);
		$tmpfont = $this->getFontBuffer($font);
		$this->annotation_fonts[$tmpfont['fontkey']] = $tmpfont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $tmpfont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = array();
		$fx = ((($w - $this->getAbsFontMeasure($tmpfont['cw'][110])) / 2) * $this->k);
		$fy = (($w - ((($tmpfont['desc']['Ascent'] - $tmpfont['desc']['Descent']) * $this->FontSizePt / 1000) / $this->k)) * $this->k);
		$popt['ap']['n']['Yes'] = sprintf('q %s BT /F%d %F Tf %F %F Td ('.chr(110).') Tj ET Q', $this->TextColor, $tmpfont['i'], $this->FontSizePt, $fx, $fy);
		$popt['ap']['n']['Off'] = sprintf('q %s BT /F%d %F Tf %F %F Td ('.chr(111).') Tj ET Q', $this->TextColor, $tmpfont['i'], $this->FontSizePt, $fx, $fy);
		// merge options
		$opt = array_merge($popt, $opt);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Btn';
		$opt['t'] = $name;
		if (TCPDF_STATIC::empty_string($onvalue)) {
			$onvalue = 'Yes';
		}
		$opt['opt'] = array($onvalue);
		if ($checked) {
			$opt['v'] = array('/Yes');
			$opt['as'] = 'Yes';
		} else {
			$opt['v'] = array('/Off');
			$opt['as'] = 'Off';
		}
		$this->Annotation($x, $y, $w, $w, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	/**
	 * Creates a button field
	 * @param $name (string) field name
	 * @param $w (int) width
	 * @param $h (int) height
	 * @param $caption (string) caption.
	 * @param $action (mixed) action triggered by pressing the button. Use a string to specify a javascript action. Use an array to specify a form action options as on section 12.7.5 of PDF32000_2008.
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) if true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function Button($name, $w, $h, $caption, $action, $prop=array(), $opt=array(), $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		if ($js) {
			$this->_addfield('button', $name, $this->x, $this->y, $w, $h, $prop);
			$this->javascript .= 'f'.$name.".buttonSetCaption('".addslashes($caption)."');\n";
			$this->javascript .= 'f'.$name.".setAction('MouseUp','".addslashes($action)."');\n";
			$this->javascript .= 'f'.$name.".highlight='push';\n";
			$this->javascript .= 'f'.$name.".print=false;\n";
			return;
		}
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		$prop['Pushbutton'] = 'true';
		$prop['highlight'] = 'push';
		$prop['display'] = 'display.noPrint';
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		$this->annotation_fonts[$this->CurrentFont['fontkey']] = $this->CurrentFont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $this->CurrentFont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = '/Tx BMC q '.$fontstyle.' ';
		$tmpid = $this->startTemplate($w, $h, false);
		$bw = (2 / $this->k); // border width
		$border = array(
			'L' => array('width' => $bw, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(231)),
			'R' => array('width' => $bw, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(51)),
			'T' => array('width' => $bw, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(231)),
			'B' => array('width' => $bw, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(51)));
		$this->SetFillColor(204);
		$this->Cell($w, $h, $caption, $border, 0, 'C', true, '', 1, false, 'T', 'M');
		$this->endTemplate();
		--$this->n;
		$popt['ap']['n'] .= $this->xobjects[$tmpid]['outdata'];
		unset($this->xobjects[$tmpid]);
		$popt['ap']['n'] .= 'Q EMC';
		// set additional default options
		if (!isset($popt['mk'])) {
			$popt['mk'] = array();
		}
		$ann_obj_id = ($this->n + 1);
		if (!empty($action) AND !is_array($action)) {
			$ann_obj_id = ($this->n + 2);
		}
		$popt['mk']['ca'] = $this->_textstring($caption, $ann_obj_id);
		$popt['mk']['rc'] = $this->_textstring($caption, $ann_obj_id);
		$popt['mk']['ac'] = $this->_textstring($caption, $ann_obj_id);
		// merge options
		$opt = array_merge($popt, $opt);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Btn';
		$opt['t'] = $caption;
		$opt['v'] = $name;
		if (!empty($action)) {
			if (is_array($action)) {
				// form action options as on section 12.7.5 of PDF32000_2008.
				$opt['aa'] = '/D <<';
				$bmode = array('SubmitForm', 'ResetForm', 'ImportData');
				foreach ($action AS $key => $val) {
					if (($key == 'S') AND in_array($val, $bmode)) {
						$opt['aa'] .= ' /S /'.$val;
					} elseif (($key == 'F') AND (!empty($val))) {
						$opt['aa'] .= ' /F '.$this->_datastring($val, $ann_obj_id);
					} elseif (($key == 'Fields') AND is_array($val) AND !empty($val)) {
						$opt['aa'] .= ' /Fields [';
						foreach ($val AS $field) {
							$opt['aa'] .= ' '.$this->_textstring($field, $ann_obj_id);
						}
						$opt['aa'] .= ']';
					} elseif (($key == 'Flags')) {
						$ff = 0;
						if (is_array($val)) {
							foreach ($val AS $flag) {
								switch ($flag) {
									case 'Include/Exclude': {
										$ff += 1 << 0;
										break;
									}
									case 'IncludeNoValueFields': {
										$ff += 1 << 1;
										break;
									}
									case 'ExportFormat': {
										$ff += 1 << 2;
										break;
									}
									case 'GetMethod': {
										$ff += 1 << 3;
										break;
									}
									case 'SubmitCoordinates': {
										$ff += 1 << 4;
										break;
									}
									case 'XFDF': {
										$ff += 1 << 5;
										break;
									}
									case 'IncludeAppendSaves': {
										$ff += 1 << 6;
										break;
									}
									case 'IncludeAnnotations': {
										$ff += 1 << 7;
										break;
									}
									case 'SubmitPDF': {
										$ff += 1 << 8;
										break;
									}
									case 'CanonicalFormat': {
										$ff += 1 << 9;
										break;
									}
									case 'ExclNonUserAnnots': {
										$ff += 1 << 10;
										break;
									}
									case 'ExclFKey': {
										$ff += 1 << 11;
										break;
									}
									case 'EmbedForm': {
										$ff += 1 << 13;
										break;
									}
								}
							}
						} else {
							$ff = intval($val);
						}
						$opt['aa'] .= ' /Flags '.$ff;
					}
				}
				$opt['aa'] .= ' >>';
			} else {
				// Javascript action or raw action command
				$js_obj_id = $this->addJavascriptObject($action);
				$opt['aa'] = '/D '.$js_obj_id.' 0 R';
			}
		}
		$this->Annotation($x, $y, $w, $h, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	// --- END FORMS FIELDS ------------------------------------------------

	/**
	 * Add certification signature (DocMDP or UR3)
	 * You can set only one signature type
	 * @protected
	 * @author Nicola Asuni
	 * @since 4.6.008 (2009-05-07)
	 */
	protected function _putsignature() {
		if ((!$this->sign) OR (!isset($this->signature_data['cert_type']))) {
			return;
		}
		$sigobjid = ($this->sig_obj_id + 1);
		$out = $this->_getobj($sigobjid)."\n";
		$out .= '<< /Type /Sig';
		$out .= ' /Filter /Adobe.PPKLite';
		$out .= ' /SubFilter /adbe.pkcs7.detached';
		$out .= ' '.TCPDF_STATIC::$byterange_string;
		$out .= ' /Contents<'.str_repeat('0', $this->signature_max_length).'>';
		$out .= ' /Reference ['; // array of signature reference dictionaries
		$out .= ' << /Type /SigRef';
		if ($this->signature_data['cert_type'] > 0) {
			$out .= ' /TransformMethod /DocMDP';
			$out .= ' /TransformParams <<';
			$out .= ' /Type /TransformParams';
			$out .= ' /P '.$this->signature_data['cert_type'];
			$out .= ' /V /1.2';
		} else {
			$out .= ' /TransformMethod /UR3';
			$out .= ' /TransformParams <<';
			$out .= ' /Type /TransformParams';
			$out .= ' /V /2.2';
			if (!TCPDF_STATIC::empty_string($this->ur['document'])) {
				$out .= ' /Document['.$this->ur['document'].']';
			}
			if (!TCPDF_STATIC::empty_string($this->ur['form'])) {
				$out .= ' /Form['.$this->ur['form'].']';
			}
			if (!TCPDF_STATIC::empty_string($this->ur['signature'])) {
				$out .= ' /Signature['.$this->ur['signature'].']';
			}
			if (!TCPDF_STATIC::empty_string($this->ur['annots'])) {
				$out .= ' /Annots['.$this->ur['annots'].']';
			}
			if (!TCPDF_STATIC::empty_string($this->ur['ef'])) {
				$out .= ' /EF['.$this->ur['ef'].']';
			}
			if (!TCPDF_STATIC::empty_string($this->ur['formex'])) {
				$out .= ' /FormEX['.$this->ur['formex'].']';
			}
		}
		$out .= ' >>'; // close TransformParams
		// optional digest data (values must be calculated and replaced later)
		//$out .= ' /Data ********** 0 R';
		//$out .= ' /DigestMethod/MD5';
		//$out .= ' /DigestLocation[********** 34]';
		//$out .= ' /DigestValue<********************************>';
		$out .= ' >>';
		$out .= ' ]'; // end of reference
		if (isset($this->signature_data['info']['Name']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['Name'])) {
			$out .= ' /Name '.$this->_textstring($this->signature_data['info']['Name'], $sigobjid);
		}
		if (isset($this->signature_data['info']['Location']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['Location'])) {
			$out .= ' /Location '.$this->_textstring($this->signature_data['info']['Location'], $sigobjid);
		}
		if (isset($this->signature_data['info']['Reason']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['Reason'])) {
			$out .= ' /Reason '.$this->_textstring($this->signature_data['info']['Reason'], $sigobjid);
		}
		if (isset($this->signature_data['info']['ContactInfo']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['ContactInfo'])) {
			$out .= ' /ContactInfo '.$this->_textstring($this->signature_data['info']['ContactInfo'], $sigobjid);
		}
		$out .= ' /M '.$this->_datestring($sigobjid, $this->doc_modification_timestamp);
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}

	/**
	 * Set User's Rights for PDF Reader
	 * WARNING: This is experimental and currently do not work.
	 * Check the PDF Reference 8.7.1 Transform Methods,
	 * Table 8.105 Entries in the UR transform parameters dictionary
	 * @param $enable (boolean) if true enable user's rights on PDF reader
	 * @param $document (string) Names specifying additional document-wide usage rights for the document. The only defined value is "/FullSave", which permits a user to save the document along with modified form and/or annotation data.
	 * @param $annots (string) Names specifying additional annotation-related usage rights for the document. Valid names in PDF 1.5 and later are /Create/Delete/Modify/Copy/Import/Export, which permit the user to perform the named operation on annotations.
	 * @param $form (string) Names specifying additional form-field-related usage rights for the document. Valid names are: /Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate
	 * @param $signature (string) Names specifying additional signature-related usage rights for the document. The only defined value is /Modify, which permits a user to apply a digital signature to an existing signature form field or clear a signed signature form field.
	 * @param $ef (string) Names specifying additional usage rights for named embedded files in the document. Valid names are /Create/Delete/Modify/Import, which permit the user to perform the named operation on named embedded files
	 Names specifying additional embedded-files-related usage rights for the document.
	 * @param $formex (string) Names specifying additional form-field-related usage rights. The only valid name is BarcodePlaintext, which permits text form field data to be encoded as a plaintext two-dimensional barcode.
	 * @public
	 * @author Nicola Asuni
	 * @since 2.9.000 (2008-03-26)
	 */
	public function setUserRights(
			$enable=true,
			$document='/FullSave',
			$annots='/Create/Delete/Modify/Copy/Import/Export',
			$form='/Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate',
			$signature='/Modify',
			$ef='/Create/Delete/Modify/Import',
			$formex='') {
		$this->ur['enabled'] = $enable;
		$this->ur['document'] = $document;
		$this->ur['annots'] = $annots;
		$this->ur['form'] = $form;
		$this->ur['signature'] = $signature;
		$this->ur['ef'] = $ef;
		$this->ur['formex'] = $formex;
		if (!$this->sign) {
			$this->setSignature('', '', '', '', 0, array());
		}
	}

	/**
	 * Enable document signature (requires the OpenSSL Library).
	 * The digital signature improve document authenticity and integrity and allows o enable extra features on Acrobat Reader.
	 * To create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
	 * To export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
	 * To convert pfx certificate to pem: openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
	 * @param $signing_cert (mixed) signing certificate (string or filename prefixed with 'file://')
	 * @param $private_key (mixed) private key (string or filename prefixed with 'file://')
	 * @param $private_key_password (string) password
	 * @param $extracerts (string) specifies the name of a file containing a bunch of extra certificates to include in the signature which can for example be used to help the recipient to verify the certificate that you used.
	 * @param $cert_type (int) The access permissions granted for this document. Valid values shall be: 1 = No changes to the document shall be permitted; any change to the document shall invalidate the signature; 2 = Permitted changes shall be filling in forms, instantiating page templates, and signing; other changes shall invalidate the signature; 3 = Permitted changes shall be the same as for 2, as well as annotation creation, deletion, and modification; other changes shall invalidate the signature.
	 * @param $info (array) array of option information: Name, Location, Reason, ContactInfo.
	 * @public
	 * @author Nicola Asuni
	 * @since 4.6.005 (2009-04-24)
	 */
	public function setSignature($signing_cert='', $private_key='', $private_key_password='', $extracerts='', $cert_type=2, $info=array()) {
		// to create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
		// to export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
		// to convert pfx certificate to pem: openssl
		//     OpenSSL> pkcs12 -in <cert.pfx> -out <cert.crt> -nodes
		$this->sign = true;
		++$this->n;
		$this->sig_obj_id = $this->n; // signature widget
		++$this->n; // signature object ($this->sig_obj_id + 1)
		$this->signature_data = array();
		if (strlen($signing_cert) == 0) {
			$this->Error('Please provide a certificate file and password!');
		}
		if (strlen($private_key) == 0) {
			$private_key = $signing_cert;
		}
		$this->signature_data['signcert'] = $signing_cert;
		$this->signature_data['privkey'] = $private_key;
		$this->signature_data['password'] = $private_key_password;
		$this->signature_data['extracerts'] = $extracerts;
		$this->signature_data['cert_type'] = $cert_type;
		$this->signature_data['info'] = $info;
	}

	/**
	 * Set the digital signature appearance (a cliccable rectangle area to get signature properties)
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the signature area.
	 * @param $h (float) Height of the signature area.
	 * @param $page (int) option page number (if < 0 the current page is used).
	 * @param $name (string) Name of the signature.
	 * @public
	 * @author Nicola Asuni
	 * @since 5.3.011 (2010-06-17)
	 */
	public function setSignatureAppearance($x=0, $y=0, $w=0, $h=0, $page=-1, $name='') {
		$this->signature_appearance = $this->getSignatureAppearanceArray($x, $y, $w, $h, $page, $name);
	}

	/**
	 * Add an empty digital signature appearance (a cliccable rectangle area to get signature properties)
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the signature area.
	 * @param $h (float) Height of the signature area.
	 * @param $page (int) option page number (if < 0 the current page is used).
	 * @param $name (string) Name of the signature.
	 * @public
	 * @author Nicola Asuni
	 * @since 5.9.101 (2011-07-06)
	 */
	public function addEmptySignatureAppearance($x=0, $y=0, $w=0, $h=0, $page=-1, $name='') {
		++$this->n;
		$this->empty_signature_appearance[] = array('objid' => $this->n) + $this->getSignatureAppearanceArray($x, $y, $w, $h, $page, $name);
	}

	/**
	 * Get the array that defines the signature appearance (page and rectangle coordinates).
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the signature area.
	 * @param $h (float) Height of the signature area.
	 * @param $page (int) option page number (if < 0 the current page is used).
	 * @param $name (string) Name of the signature.
	 * @return (array) Array defining page and rectangle coordinates of signature appearance.
	 * @protected
	 * @author Nicola Asuni
	 * @since 5.9.101 (2011-07-06)
	 */
	protected function getSignatureAppearanceArray($x=0, $y=0, $w=0, $h=0, $page=-1, $name='') {
		$sigapp = array();
		if (($page < 1) OR ($page > $this->numpages)) {
			$sigapp['page'] = $this->page;
		} else {
			$sigapp['page'] = intval($page);
		}
		if (empty($name)) {
			$sigapp['name'] = 'Signature';
		} else {
			$sigapp['name'] = $name;
		}
		$a = $x * $this->k;
		$b = $this->pagedim[($sigapp['page'])]['h'] - (($y + $h) * $this->k);
		$c = $w * $this->k;
		$d = $h * $this->k;
		$sigapp['rect'] = sprintf('%F %F %F %F', $a, $b, ($a + $c), ($b + $d));
		return $sigapp;
	}

	/**
	 * Create a new page group.
	 * NOTE: call this function before calling AddPage()
	 * @param $page (int) starting group page (leave empty for next page).
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function startPageGroup($page='') {
		if (empty($page)) {
			$page = $this->page + 1;
		}
		$this->newpagegroup[$page] = sizeof($this->newpagegroup) + 1;
	}

	/**
	 * Set the starting page number.
	 * @param $num (int) Starting page number.
	 * @since 5.9.093 (2011-06-16)
	 * @public
	 */
	public function setStartingPageNumber($num=1) {
		$this->starting_page_number = max(0, intval($num));
	}

	/**
	 * Returns the string alias used right align page numbers.
	 * If the current font is unicode type, the returned string wil contain an additional open curly brace.
	 * @return string
	 * @since 5.9.099 (2011-06-27)
	 * @public
	 */
	public function getAliasRightShift() {
		// calculate aproximatively the ratio between widths of aliases and replacements.
		$ref = '{'.TCPDF_STATIC::$alias_right_shift.'}{'.TCPDF_STATIC::$alias_tot_pages.'}{'.TCPDF_STATIC::$alias_num_page.'}';
		$rep = str_repeat(' ', $this->GetNumChars($ref));
		$wrep = $this->GetStringWidth($rep);
		if ($wrep > 0) {
			$wdiff = max(1, ($this->GetStringWidth($ref) / $wrep));
		} else {
			$wdiff = 1;
		}
		$sdiff = sprintf('%F', $wdiff);
		$alias = TCPDF_STATIC::$alias_right_shift.$sdiff.'}';
		if ($this->isUnicodeFont()) {
			$alias = '{'.$alias;
		}
		return $alias;
	}

	/**
	 * Returns the string alias used for the total number of pages.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the total number of pages in the document.
	 * @return string
	 * @since 4.0.018 (2008-08-08)
	 * @public
	 */
	public function getAliasNbPages() {
		if ($this->isUnicodeFont()) {
			return '{'.TCPDF_STATIC::$alias_tot_pages.'}';
		}
		return TCPDF_STATIC::$alias_tot_pages;
	}

	/**
	 * Returns the string alias used for the page number.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the page number.
	 * @return string
	 * @since 4.5.000 (2009-01-02)
	 * @public
	 */
	public function getAliasNumPage() {
		if ($this->isUnicodeFont()) {
			return '{'.TCPDF_STATIC::$alias_num_page.'}';
		}
		return TCPDF_STATIC::$alias_num_page;
	}

	/**
	 * Return the alias for the total number of pages in the current page group.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the total number of pages in this group.
	 * @return alias of the current page group
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function getPageGroupAlias() {
		if ($this->isUnicodeFont()) {
			return '{'.TCPDF_STATIC::$alias_group_tot_pages.'}';
		}
		return TCPDF_STATIC::$alias_group_tot_pages;
	}

	/**
	 * Return the alias for the page number on the current page group.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the page number (relative to the belonging group).
	 * @return alias of the current page group
	 * @public
	 * @since 4.5.000 (2009-01-02)
	 */
	public function getPageNumGroupAlias() {
		if ($this->isUnicodeFont()) {
			return '{'.TCPDF_STATIC::$alias_group_num_page.'}';
		}
		return TCPDF_STATIC::$alias_group_num_page;
	}

	/**
	 * Return the current page in the group.
	 * @return current page in the group
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function getGroupPageNo() {
		return $this->pagegroups[$this->currpagegroup];
	}

	/**
	 * Returns the current group page number formatted as a string.
	 * @public
	 * @since 4.3.003 (2008-11-18)
	 * @see PaneNo(), formatPageNumber()
	 */
	public function getGroupPageNoFormatted() {
		return TCPDF_STATIC::formatPageNumber($this->getGroupPageNo());
	}

	/**
	 * Returns the current page number formatted as a string.
	 * @public
	 * @since 4.2.005 (2008-11-06)
	 * @see PaneNo(), formatPageNumber()
	 */
	public function PageNoFormatted() {
		return TCPDF_STATIC::formatPageNumber($this->PageNo());
	}

	/**
	 * Put pdf layers.
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function _putocg() {
		if (empty($this->pdflayers)) {
			return;
		}
		foreach ($this->pdflayers as $key => $layer) {
			 $this->pdflayers[$key]['objid'] = $this->_newobj();
			 $out = '<< /Type /OCG';
			 $out .= ' /Name '.$this->_textstring($layer['name'], $this->pdflayers[$key]['objid']);
			 $out .= ' /Usage <<';
			 $out .= ' /Print <</PrintState /'.($layer['print']?'ON':'OFF').'>>';
			 $out .= ' /View <</ViewState /'.($layer['view']?'ON':'OFF').'>>';
			 $out .= ' >> >>';
			 $out .= "\n".'endobj';
			 $this->_out($out);
		}
	}

	/**
	 * Start a new pdf layer.
	 * @param $name (string) Layer name (only a-z letters and numbers). Leave empty for automatic name.
	 * @param $print (boolean) Set to true to print this layer.
	 * @param $view (boolean) Set to true to view this layer.
	 * @public
	 * @since 5.9.102 (2011-07-13)
	 */
	public function startLayer($name='', $print=true, $view=true) {
		if ($this->state != 2) {
			return;
		}
		$layer = sprintf('LYR%03d', (count($this->pdflayers) + 1));
		if (empty($name)) {
			$name = $layer;
		} else {
			$name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
		}
		$this->pdflayers[] = array('layer' => $layer, 'name' => $name, 'print' => $print, 'view' => $view);
		$this->openMarkedContent = true;
		$this->_out('/OC /'.$layer.' BDC');
	}

	/**
	 * End the current PDF layer.
	 * @public
	 * @since 5.9.102 (2011-07-13)
	 */
	public function endLayer() {
		if ($this->state != 2) {
			return;
		}
		if ($this->openMarkedContent) {
			// close existing open marked-content layer
			$this->_out('EMC');
			$this->openMarkedContent = false;
		}
	}

	/**
	 * Set the visibility of the successive elements.
	 * This can be useful, for instance, to put a background
	 * image or color that will show on screen but won't print.
	 * @param $v (string) visibility mode. Legal values are: all, print, screen or view.
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function setVisibility($v) {
		if ($this->state != 2) {
			return;
		}
		$this->endLayer();
		switch($v) {
			case 'print': {
				$this->startLayer('Print', true, false);
				break;
			}
			case 'view':
			case 'screen': {
				$this->startLayer('View', false, true);
				break;
			}
			case 'all': {
				$this->_out('');
				break;
			}
			default: {
				$this->Error('Incorrect visibility: '.$v);
				break;
			}
		}
	}

	/**
	 * Add transparency parameters to the current extgstate
	 * @param $parms (array) parameters
	 * @return the number of extgstates
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function addExtGState($parms) {
		if ($this->pdfa_mode) {
			// transparencies are not allowed in PDF/A mode
			return;
		}
		// check if this ExtGState already exist
		foreach ($this->extgstates as $i => $ext) {
			if ($ext['parms'] == $parms) {
				if ($this->inxobj) {
					// we are inside an XObject template
					$this->xobjects[$this->xobjid]['extgstates'][$i] = $ext;
				}
				// return reference to existing ExtGState
				return $i;
			}
		}
		$n = (count($this->extgstates) + 1);
		$this->extgstates[$n] = array('parms' => $parms);
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['extgstates'][$n] = $this->extgstates[$n];
		}
		return $n;
	}

	/**
	 * Add an extgstate
	 * @param $gs (array) extgstate
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function setExtGState($gs) {
		if ($this->pdfa_mode OR ($this->state != 2)) {
			// transparency is not allowed in PDF/A mode
			return;
		}
		$this->_out(sprintf('/GS%d gs', $gs));
	}

	/**
	 * Put extgstates for object transparency
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function _putextgstates() {
		foreach ($this->extgstates as $i => $ext) {
			$this->extgstates[$i]['n'] = $this->_newobj();
			$out = '<< /Type /ExtGState';
			foreach ($ext['parms'] as $k => $v) {
				if (is_float($v)) {
					$v = sprintf('%F', $v);
				} elseif ($v === true) {
					$v = 'true';
				} elseif ($v === false) {
					$v = 'false';
				}
				$out .= ' /'.$k.' '.$v;
			}
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
	}

	/**
	 * Set overprint mode for stroking (OP) and non-stroking (op) painting operations.
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @param $stroking (boolean) If true apply overprint for stroking operations.
	 * @param $nonstroking (boolean) If true apply overprint for painting operations other than stroking.
	 * @param $mode (integer) Overprint mode: (0 = each source colour component value replaces the value previously painted for the corresponding device colorant; 1 = a tint value of 0.0 for a source colour component shall leave the corresponding component of the previously painted colour unchanged).
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function setOverprint($stroking=true, $nonstroking='', $mode=0) {
		if ($this->state != 2) {
			return;
		}
		$stroking = $stroking ? true : false;
		if (TCPDF_STATIC::empty_string($nonstroking)) {
			// default value if not set
			$nonstroking = $stroking;
		} else {
			$nonstroking = $nonstroking ? true : false;
		}
		if (($mode != 0) AND ($mode != 1)) {
			$mode = 0;
		}
		$this->overprint = array('OP' => $stroking, 'op' => $nonstroking, 'OPM' => $mode);
		$gs = $this->addExtGState($this->overprint);
		$this->setExtGState($gs);
	}

	/**
	 * Get the overprint mode array (OP, op, OPM).
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @return array.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getOverprint() {
		return $this->overprint;
	}

	/**
	 * Set alpha for stroking (CA) and non-stroking (ca) operations.
	 * @param $stroking (float) Alpha value for stroking operations: real value from 0 (transparent) to 1 (opaque).
	 * @param $bm (string) blend mode, one of the following: Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
	 * @param $nonstroking (float) Alpha value for non-stroking operations: real value from 0 (transparent) to 1 (opaque).
	 * @param $ais (boolean)
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function setAlpha($stroking=1, $bm='Normal', $nonstroking='', $ais=false) {
		if ($this->pdfa_mode) {
			// transparency is not allowed in PDF/A mode
			return;
		}
		$stroking = floatval($stroking);
		if (TCPDF_STATIC::empty_string($nonstroking)) {
			// default value if not set
			$nonstroking = $stroking;
		} else {
			$nonstroking = floatval($nonstroking);
		}
		if ($bm[0] == '/') {
			// remove trailing slash
			$bm = substr($bm, 1);
		}
		if (!in_array($bm, array('Normal', 'Multiply', 'Screen', 'Overlay', 'Darken', 'Lighten', 'ColorDodge', 'ColorBurn', 'HardLight', 'SoftLight', 'Difference', 'Exclusion', 'Hue', 'Saturation', 'Color', 'Luminosity'))) {
			$bm = 'Normal';
		}
		$ais = $ais ? true : false;
		$this->alpha = array('CA' => $stroking, 'ca' => $nonstroking, 'BM' => '/'.$bm, 'AIS' => $ais);
		$gs = $this->addExtGState($this->alpha);
		$this->setExtGState($gs);
	}

	/**
	 * Get the alpha mode array (CA, ca, BM, AIS).
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @return array.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getAlpha() {
		return $this->alpha;
	}

	/**
	 * Set the default JPEG compression quality (1-100)
	 * @param $quality (int) JPEG quality, integer between 1 and 100
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function setJPEGQuality($quality) {
		if (($quality < 1) OR ($quality > 100)) {
			$quality = 75;
		}
		$this->jpeg_quality = intval($quality);
	}

	/**
	 * Set the default number of columns in a row for HTML tables.
	 * @param $cols (int) number of columns
	 * @public
	 * @since 3.0.014 (2008-06-04)
	 */
	public function setDefaultTableColumns($cols=4) {
		$this->default_table_columns = intval($cols);
	}

	/**
	 * Set the height of the cell (line height) respect the font height.
	 * @param $h (int) cell proportion respect font height (typical value = 1.25).
	 * @public
	 * @since 3.0.014 (2008-06-04)
	 */
	public function setCellHeightRatio($h) {
		$this->cell_height_ratio = $h;
	}

	/**
	 * return the height of cell repect font height.
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getCellHeightRatio() {
		return $this->cell_height_ratio;
	}

	/**
	 * Set the PDF version (check PDF reference for valid values).
	 * @param $version (string) PDF document version.
	 * @public
	 * @since 3.1.000 (2008-06-09)
	 */
	public function setPDFVersion($version='1.7') {
		if ($this->pdfa_mode) {
			// PDF/A mode
			$this->PDFVersion = '1.4';
		} else {
			$this->PDFVersion = $version;
		}
	}

	/**
	 * Set the viewer preferences dictionary controlling the way the document is to be presented on the screen or in print.
	 * (see Section 8.1 of PDF reference, "Viewer Preferences").
	 * <ul><li>HideToolbar boolean (Optional) A flag specifying whether to hide the viewer application's tool bars when the document is active. Default value: false.</li><li>HideMenubar boolean (Optional) A flag specifying whether to hide the viewer application's menu bar when the document is active. Default value: false.</li><li>HideWindowUI boolean (Optional) A flag specifying whether to hide user interface elements in the document's window (such as scroll bars and navigation controls), leaving only the document's contents displayed. Default value: false.</li><li>FitWindow boolean (Optional) A flag specifying whether to resize the document's window to fit the size of the first displayed page. Default value: false.</li><li>CenterWindow boolean (Optional) A flag specifying whether to position the document's window in the center of the screen. Default value: false.</li><li>DisplayDocTitle boolean (Optional; PDF 1.4) A flag specifying whether the window's title bar should display the document title taken from the Title entry of the document information dictionary (see Section 10.2.1, "Document Information Dictionary"). If false, the title bar should instead display the name of the PDF file containing the document. Default value: false.</li><li>NonFullScreenPageMode name (Optional) The document's page mode, specifying how to display the document on exiting full-screen mode:<ul><li>UseNone Neither document outline nor thumbnail images visible</li><li>UseOutlines Document outline visible</li><li>UseThumbs Thumbnail images visible</li><li>UseOC Optional content group panel visible</li></ul>This entry is meaningful only if the value of the PageMode entry in the catalog dictionary (see Section 3.6.1, "Document Catalog") is FullScreen; it is ignored otherwise. Default value: UseNone.</li><li>ViewArea name (Optional; PDF 1.4) The name of the page boundary representing the area of a page to be displayed when viewing the document on the screen. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>ViewClip name (Optional; PDF 1.4) The name of the page boundary to which the contents of a page are to be clipped when viewing the document on the screen. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>PrintArea name (Optional; PDF 1.4) The name of the page boundary representing the area of a page to be rendered when printing the document. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>PrintClip name (Optional; PDF 1.4) The name of the page boundary to which the contents of a page are to be clipped when printing the document. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>PrintScaling name (Optional; PDF 1.6) The page scaling option to be selected when a print dialog is displayed for this document. Valid values are: <ul><li>None, which indicates that the print dialog should reflect no page scaling</li><li>AppDefault (default), which indicates that applications should use the current print scaling</li></ul></li><li>Duplex name (Optional; PDF 1.7) The paper handling option to use when printing the file from the print dialog. The following values are valid:<ul><li>Simplex - Print single-sided</li><li>DuplexFlipShortEdge - Duplex and flip on the short edge of the sheet</li><li>DuplexFlipLongEdge - Duplex and flip on the long edge of the sheet</li></ul>Default value: none</li><li>PickTrayByPDFSize boolean (Optional; PDF 1.7) A flag specifying whether the PDF page size is used to select the input paper tray. This setting influences only the preset values used to populate the print dialog presented by a PDF viewer application. If PickTrayByPDFSize is true, the check box in the print dialog associated with input paper tray is checked. Note: This setting has no effect on Mac OS systems, which do not provide the ability to pick the input tray by size.</li><li>PrintPageRange array (Optional; PDF 1.7) The page numbers used to initialize the print dialog box when the file is printed. The first page of the PDF file is denoted by 1. Each pair consists of the first and last pages in the sub-range. An odd number of integers causes this entry to be ignored. Negative numbers cause the entire array to be ignored. Default value: as defined by PDF viewer application</li><li>NumCopies integer (Optional; PDF 1.7) The number of copies to be printed when the print dialog is opened for this file. Supported values are the integers 2 through 5. Values outside this range are ignored. Default value: as defined by PDF viewer application, but typically 1</li></ul>
	 * @param $preferences (array) array of options.
	 * @author Nicola Asuni
	 * @public
	 * @since 3.1.000 (2008-06-09)
	 */
	public function setViewerPreferences($preferences) {
		$this->viewer_preferences = $preferences;
	}

	/**
	 * Paints color transition registration bars
	 * @param $x (float) abscissa of the top left corner of the rectangle.
	 * @param $y (float) ordinate of the top left corner of the rectangle.
	 * @param $w (float) width of the rectangle.
	 * @param $h (float) height of the rectangle.
	 * @param $transition (boolean) if true prints tcolor transitions to white.
	 * @param $vertical (boolean) if true prints bar vertically.
	 * @param $colors (string) colors to print separated by comma. Valid values are: A,W,R,G,B,C,M,Y,K,RGB,CMYK,ALL,ALLSPOT,<SPOT_COLOR_NAME>. Where: A = grayscale black, W = grayscale white, R = RGB red, G RGB green, B RGB blue, C = CMYK cyan, M = CMYK magenta, Y = CMYK yellow, K = CMYK key/black, RGB = RGB registration color, CMYK = CMYK registration color, ALL = Spot registration color, ALLSPOT = print all defined spot colors, <SPOT_COLOR_NAME> = name of the spot color to print.
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function colorRegistrationBar($x, $y, $w, $h, $transition=true, $vertical=false, $colors='A,R,G,B,C,M,Y,K') {
		if (strpos($colors, 'ALLSPOT') !== false) {
			// expand spot colors
			$spot_colors = '';
			foreach ($this->spot_colors as $spot_color_name => $v) {
				$spot_colors .= ','.$spot_color_name;
			}
			if (!empty($spot_colors)) {
				$spot_colors = substr($spot_colors, 1);
				$colors = str_replace('ALLSPOT', $spot_colors, $colors);
			} else {
				$colors = str_replace('ALLSPOT', 'NONE', $colors);
			}
		}
		$bars = explode(',', $colors);
		$numbars = count($bars); // number of bars to print
		if ($numbars <= 0) {
			return;
		}
		// set bar measures
		if ($vertical) {
			$coords = array(0, 0, 0, 1);
			$wb = $w / $numbars; // bar width
			$hb = $h; // bar height
			$xd = $wb; // delta x
			$yd = 0; // delta y
		} else {
			$coords = array(1, 0, 0, 0);
			$wb = $w; // bar width
			$hb = $h / $numbars; // bar height
			$xd = 0; // delta x
			$yd = $hb; // delta y
		}
		$xb = $x;
		$yb = $y;
		foreach ($bars as $col) {
			switch ($col) {
				// set transition colors
				case 'A': { // BLACK (GRAYSCALE)
					$col_a = array(255);
					$col_b = array(0);
					break;
				}
				case 'W': { // WHITE (GRAYSCALE)
					$col_a = array(0);
					$col_b = array(255);
					break;
				}
				case 'R': { // RED (RGB)
					$col_a = array(255,255,255);
					$col_b = array(255,0,0);
					break;
				}
				case 'G': { // GREEN (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,255,0);
					break;
				}
				case 'B': { // BLUE (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,0,255);
					break;
				}
				case 'C': { // CYAN (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(100,0,0,0);
					break;
				}
				case 'M': { // MAGENTA (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,100,0,0);
					break;
				}
				case 'Y': { // YELLOW (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,0,100,0);
					break;
				}
				case 'K': { // KEY - BLACK (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,0,0,100);
					break;
				}
				case 'RGB': { // BLACK REGISTRATION (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,0,0);
					break;
				}
				case 'CMYK': { // BLACK REGISTRATION (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(100,100,100,100);
					break;
				}
				case 'ALL': { // SPOT COLOR REGISTRATION
					$col_a = array(0,0,0,0,'None');
					$col_b = array(100,100,100,100,'All');
					break;
				}
				case 'NONE': { // SKIP THIS COLOR
					$col_a = array(0,0,0,0,'None');
					$col_b = array(0,0,0,0,'None');
					break;
				}
				default: { // SPECIFIC SPOT COLOR NAME
					$col_a = array(0,0,0,0,'None');
					$col_b = TCPDF_COLORS::getSpotColor($col, $this->spot_colors);
					if ($col_b === false) {
						// in case of error defaults to the registration color
						$col_b = array(100,100,100,100,'All');
					}
					break;
				}
			}
			if ($col != 'NONE') {
				if ($transition) {
					// color gradient
					$this->LinearGradient($xb, $yb, $wb, $hb, $col_a, $col_b, $coords);
				} else {
					$this->SetFillColorArray($col_b);
					// colored rectangle
					$this->Rect($xb, $yb, $wb, $hb, 'F', array());
				}
				$xb += $xd;
				$yb += $yd;
			}
		}
	}

	/**
	 * Paints crop marks.
	 * @param $x (float) abscissa of the crop mark center.
	 * @param $y (float) ordinate of the crop mark center.
	 * @param $w (float) width of the crop mark.
	 * @param $h (float) height of the crop mark.
	 * @param $type (string) type of crop mark, one symbol per type separated by comma: T = TOP, F = BOTTOM, L = LEFT, R = RIGHT, TL = A = TOP-LEFT, TR = B = TOP-RIGHT, BL = C = BOTTOM-LEFT, BR = D = BOTTOM-RIGHT.
	 * @param $color (array) crop mark color (default spot registration color).
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function cropMark($x, $y, $w, $h, $type='T,R,B,L', $color=array(100,100,100,100,'All')) {
		$this->SetLineStyle(array('width' => (0.5 / $this->k), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $color));
		$type = strtoupper($type);
		$type = preg_replace('/[^A-Z\-\,]*/', '', $type);
		// split type in single components
		$type = str_replace('-', ',', $type);
		$type = str_replace('TL', 'T,L', $type);
		$type = str_replace('TR', 'T,R', $type);
		$type = str_replace('BL', 'F,L', $type);
		$type = str_replace('BR', 'F,R', $type);
		$type = str_replace('A', 'T,L', $type);
		$type = str_replace('B', 'T,R', $type);
		$type = str_replace('T,RO', 'BO', $type);
		$type = str_replace('C', 'F,L', $type);
		$type = str_replace('D', 'F,R', $type);
		$crops = explode(',', strtoupper($type));
		// remove duplicates
		$crops = array_unique($crops);
		$dw = ($w / 4); // horizontal space to leave before the intersection point
		$dh = ($h / 4); // vertical space to leave before the intersection point
		foreach ($crops as $crop) {
			switch ($crop) {
				case 'T':
				case 'TOP': {
					$x1 = $x;
					$y1 = ($y - $h);
					$x2 = $x;
					$y2 = ($y - $dh);
					break;
				}
				case 'F':
				case 'BOTTOM': {
					$x1 = $x;
					$y1 = ($y + $dh);
					$x2 = $x;
					$y2 = ($y + $h);
					break;
				}
				case 'L':
				case 'LEFT': {
					$x1 = ($x - $w);
					$y1 = $y;
					$x2 = ($x - $dw);
					$y2 = $y;
					break;
				}
				case 'R':
				case 'RIGHT': {
					$x1 = ($x + $dw);
					$y1 = $y;
					$x2 = ($x + $w);
					$y2 = $y;
					break;
				}
			}
			$this->Line($x1, $y1, $x2, $y2);
		}
	}

	/**
	 * Paints a registration mark
	 * @param $x (float) abscissa of the registration mark center.
	 * @param $y (float) ordinate of the registration mark center.
	 * @param $r (float) radius of the crop mark.
	 * @param $double (boolean) if true print two concentric crop marks.
	 * @param $cola (array) crop mark color (default spot registration color 'All').
	 * @param $colb (array) second crop mark color (default spot registration color 'None').
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function registrationMark($x, $y, $r, $double=false, $cola=array(100,100,100,100,'All'), $colb=array(0,0,0,0,'None')) {
		$line_style = array('width' => max((0.5 / $this->k),($r / 30)), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $cola);
		$this->SetFillColorArray($cola);
		$this->PieSector($x, $y, $r, 90, 180, 'F');
		$this->PieSector($x, $y, $r, 270, 360, 'F');
		$this->Circle($x, $y, $r, 0, 360, 'C', $line_style, array(), 8);
		if ($double) {
			$ri = $r * 0.5;
			$this->SetFillColorArray($colb);
			$this->PieSector($x, $y, $ri, 90, 180, 'F');
			$this->PieSector($x, $y, $ri, 270, 360, 'F');
			$this->SetFillColorArray($cola);
			$this->PieSector($x, $y, $ri, 0, 90, 'F');
			$this->PieSector($x, $y, $ri, 180, 270, 'F');
			$this->Circle($x, $y, $ri, 0, 360, 'C', $line_style, array(), 8);
		}
	}

	/**
	 * Paints a CMYK registration mark
	 * @param $x (float) abscissa of the registration mark center.
	 * @param $y (float) ordinate of the registration mark center.
	 * @param $r (float) radius of the crop mark.
	 * @author Nicola Asuni
	 * @since 6.0.038 (2013-09-30)
	 * @public
	 */
	public function registrationMarkCMYK($x, $y, $r) {
		// line width
		$lw = max((0.5 / $this->k),($r / 8));
		// internal radius
		$ri = ($r * 0.6);
		// external radius
		$re = ($r * 1.3);
		// Cyan
		$this->SetFillColorArray(array(100,0,0,0));
		$this->PieSector($x, $y, $ri, 270, 360, 'F');
		// Magenta
		$this->SetFillColorArray(array(0,100,0,0));
		$this->PieSector($x, $y, $ri, 0, 90, 'F');
		// Yellow
		$this->SetFillColorArray(array(0,0,100,0));
		$this->PieSector($x, $y, $ri, 90, 180, 'F');
		// Key - black
		$this->SetFillColorArray(array(0,0,0,100));
		$this->PieSector($x, $y, $ri, 180, 270, 'F');
		// registration color
		$line_style = array('width' => $lw, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(100,100,100,100,'All'));
		$this->SetFillColorArray(array(100,100,100,100,'All'));
		// external circle
		$this->Circle($x, $y, $r, 0, 360, 'C', $line_style, array(), 8);
		// cross lines
		$this->Line($x, ($y - $re), $x, ($y - $ri));
		$this->Line($x, ($y + $ri), $x, ($y + $re));
		$this->Line(($x - $re), $y, ($x - $ri), $y);
		$this->Line(($x + $ri), $y, ($x + $re), $y);
	}

	/**
	 * Paints a linear colour gradient.
	 * @param $x (float) abscissa of the top left corner of the rectangle.
	 * @param $y (float) ordinate of the top left corner of the rectangle.
	 * @param $w (float) width of the rectangle.
	 * @param $h (float) height of the rectangle.
	 * @param $col1 (array) first color (Grayscale, RGB or CMYK components).
	 * @param $col2 (array) second color (Grayscale, RGB or CMYK components).
	 * @param $coords (array) array of the form (x1, y1, x2, y2) which defines the gradient vector (see linear_gradient_coords.jpg). The default value is from left to right (x1=0, y1=0, x2=1, y2=0).
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function LinearGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0,0,1,0)) {
		$this->Clip($x, $y, $w, $h);
		$this->Gradient(2, $coords, array(array('color' => $col1, 'offset' => 0, 'exponent' => 1), array('color' => $col2, 'offset' => 1, 'exponent' => 1)), array(), false);
	}

	/**
	 * Paints a radial colour gradient.
	 * @param $x (float) abscissa of the top left corner of the rectangle.
	 * @param $y (float) ordinate of the top left corner of the rectangle.
	 * @param $w (float) width of the rectangle.
	 * @param $h (float) height of the rectangle.
	 * @param $col1 (array) first color (Grayscale, RGB or CMYK components).
	 * @param $col2 (array) second color (Grayscale, RGB or CMYK components).
	 * @param $coords (array) array of the form (fx, fy, cx, cy, r) where (fx, fy) is the starting point of the gradient with color1, (cx, cy) is the center of the circle with color2, and r is the radius of the circle (see radial_gradient_coords.jpg). (fx, fy) should be inside the circle, otherwise some areas will not be defined.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function RadialGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0.5,0.5,0.5,0.5,1)) {
		$this->Clip($x, $y, $w, $h);
		$this->Gradient(3, $coords, array(array('color' => $col1, 'offset' => 0, 'exponent' => 1), array('color' => $col2, 'offset' => 1, 'exponent' => 1)), array(), false);
	}

	/**
	 * Paints a coons patch mesh.
	 * @param $x (float) abscissa of the top left corner of the rectangle.
	 * @param $y (float) ordinate of the top left corner of the rectangle.
	 * @param $w (float) width of the rectangle.
	 * @param $h (float) height of the rectangle.
	 * @param $col1 (array) first color (lower left corner) (RGB components).
	 * @param $col2 (array) second color (lower right corner) (RGB components).
	 * @param $col3 (array) third color (upper right corner) (RGB components).
	 * @param $col4 (array) fourth color (upper left corner) (RGB components).
	 * @param $coords (array) <ul><li>for one patch mesh: array(float x1, float y1, .... float x12, float y12): 12 pairs of coordinates (normally from 0 to 1) which specify the Bezier control points that define the patch. First pair is the lower left edge point, next is its right control point (control point 2). Then the other points are defined in the order: control point 1, edge point, control point 2 going counter-clockwise around the patch. Last (x12, y12) is the first edge point's left control point (control point 1).</li><li>for two or more patch meshes: array[number of patches]: arrays with the following keys for each patch: f: where to put that patch (0 = first patch, 1, 2, 3 = right, top and left of precedent patch - I didn't figure this out completely - just try and error ;-) points: 12 pairs of coordinates of the Bezier control points as above for the first patch, 8 pairs of coordinates for the following patches, ignoring the coordinates already defined by the precedent patch (I also didn't figure out the order of these - also: try and see what's happening) colors: must be 4 colors for the first patch, 2 colors for the following patches</li></ul>
	 * @param $coords_min (array) minimum value used by the coordinates. If a coordinate's value is smaller than this it will be cut to coords_min. default: 0
	 * @param $coords_max (array) maximum value used by the coordinates. If a coordinate's value is greater than this it will be cut to coords_max. default: 1
	 * @param $antialias (boolean) A flag indicating whether to filter the shading function to prevent aliasing artifacts.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function CoonsPatchMesh($x, $y, $w, $h, $col1=array(), $col2=array(), $col3=array(), $col4=array(), $coords=array(0.00,0.0,0.33,0.00,0.67,0.00,1.00,0.00,1.00,0.33,1.00,0.67,1.00,1.00,0.67,1.00,0.33,1.00,0.00,1.00,0.00,0.67,0.00,0.33), $coords_min=0, $coords_max=1, $antialias=false) {
		if ($this->pdfa_mode OR ($this->state != 2)) {
			return;
		}
		$this->Clip($x, $y, $w, $h);
		$n = count($this->gradients) + 1;
		$this->gradients[$n] = array();
		$this->gradients[$n]['type'] = 6; //coons patch mesh
		$this->gradients[$n]['coords'] = array();
		$this->gradients[$n]['antialias'] = $antialias;
		$this->gradients[$n]['colors'] = array();
		$this->gradients[$n]['transparency'] = false;
		//check the coords array if it is the simple array or the multi patch array
		if (!isset($coords[0]['f'])) {
			//simple array -> convert to multi patch array
			if (!isset($col1[1])) {
				$col1[1] = $col1[2] = $col1[0];
			}
			if (!isset($col2[1])) {
				$col2[1] = $col2[2] = $col2[0];
			}
			if (!isset($col3[1])) {
				$col3[1] = $col3[2] = $col3[0];
			}
			if (!isset($col4[1])) {
				$col4[1] = $col4[2] = $col4[0];
			}
			$patch_array[0]['f'] = 0;
			$patch_array[0]['points'] = $coords;
			$patch_array[0]['colors'][0]['r'] = $col1[0];
			$patch_array[0]['colors'][0]['g'] = $col1[1];
			$patch_array[0]['colors'][0]['b'] = $col1[2];
			$patch_array[0]['colors'][1]['r'] = $col2[0];
			$patch_array[0]['colors'][1]['g'] = $col2[1];
			$patch_array[0]['colors'][1]['b'] = $col2[2];
			$patch_array[0]['colors'][2]['r'] = $col3[0];
			$patch_array[0]['colors'][2]['g'] = $col3[1];
			$patch_array[0]['colors'][2]['b'] = $col3[2];
			$patch_array[0]['colors'][3]['r'] = $col4[0];
			$patch_array[0]['colors'][3]['g'] = $col4[1];
			$patch_array[0]['colors'][3]['b'] = $col4[2];
		} else {
			//multi patch array
			$patch_array = $coords;
		}
		$bpcd = 65535; //16 bits per coordinate
		//build the data stream
		$this->gradients[$n]['stream'] = '';
		$count_patch = count($patch_array);
		for ($i=0; $i < $count_patch; ++$i) {
			$this->gradients[$n]['stream'] .= chr($patch_array[$i]['f']); //start with the edge flag as 8 bit
			$count_points = count($patch_array[$i]['points']);
			for ($j=0; $j < $count_points; ++$j) {
				//each point as 16 bit
				$patch_array[$i]['points'][$j] = (($patch_array[$i]['points'][$j] - $coords_min) / ($coords_max - $coords_min)) * $bpcd;
				if ($patch_array[$i]['points'][$j] < 0) {
					$patch_array[$i]['points'][$j] = 0;
				}
				if ($patch_array[$i]['points'][$j] > $bpcd) {
					$patch_array[$i]['points'][$j] = $bpcd;
				}
				$this->gradients[$n]['stream'] .= chr(floor($patch_array[$i]['points'][$j] / 256));
				$this->gradients[$n]['stream'] .= chr(floor($patch_array[$i]['points'][$j] % 256));
			}
			$count_cols = count($patch_array[$i]['colors']);
			for ($j=0; $j < $count_cols; ++$j) {
				//each color component as 8 bit
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['r']);
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['g']);
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['b']);
			}
		}
		//paint the gradient
		$this->_out('/Sh'.$n.' sh');
		//restore previous Graphic State
		$this->_out('Q');
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['gradients'][$n] = $this->gradients[$n];
		}
	}

	/**
	 * Set a rectangular clipping area.
	 * @param $x (float) abscissa of the top left corner of the rectangle (or top right corner for RTL mode).
	 * @param $y (float) ordinate of the top left corner of the rectangle.
	 * @param $w (float) width of the rectangle.
	 * @param $h (float) height of the rectangle.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @protected
	 */
	protected function Clip($x, $y, $w, $h) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rtl) {
			$x = $this->w - $x - $w;
		}
		//save current Graphic State
		$s = 'q';
		//set clipping area
		$s .= sprintf(' %F %F %F %F re W n', $x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k);
		//set up transformation matrix for gradient
		$s .= sprintf(' %F 0 0 %F %F %F cm', $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k);
		$this->_out($s);
	}

	/**
	 * Output gradient.
	 * @param $type (int) type of gradient (1 Function-based shading; 2 Axial shading; 3 Radial shading; 4 Free-form Gouraud-shaded triangle mesh; 5 Lattice-form Gouraud-shaded triangle mesh; 6 Coons patch mesh; 7 Tensor-product patch mesh). (Not all types are currently supported)
	 * @param $coords (array) array of coordinates.
	 * @param $stops (array) array gradient color components: color = array of GRAY, RGB or CMYK color components; offset = (0 to 1) represents a location along the gradient vector; exponent = exponent of the exponential interpolation function (default = 1).
	 * @param $background (array) An array of colour components appropriate to the colour space, specifying a single background colour value.
	 * @param $antialias (boolean) A flag indicating whether to filter the shading function to prevent aliasing artifacts.
	 * @author Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function Gradient($type, $coords, $stops, $background=array(), $antialias=false) {
		if ($this->pdfa_mode OR ($this->state != 2)) {
			return;
		}
		$n = count($this->gradients) + 1;
		$this->gradients[$n] = array();
		$this->gradients[$n]['type'] = $type;
		$this->gradients[$n]['coords'] = $coords;
		$this->gradients[$n]['antialias'] = $antialias;
		$this->gradients[$n]['colors'] = array();
		$this->gradients[$n]['transparency'] = false;
		// color space
		$numcolspace = count($stops[0]['color']);
		$bcolor = array_values($background);
		switch($numcolspace) {
			case 5:   // SPOT
			case 4: { // CMYK
				$this->gradients[$n]['colspace'] = 'DeviceCMYK';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F %F %F %F', $bcolor[0]/100, $bcolor[1]/100, $bcolor[2]/100, $bcolor[3]/100);
				}
				break;
			}
			case 3: { // RGB
				$this->gradients[$n]['colspace'] = 'DeviceRGB';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F %F %F', $bcolor[0]/255, $bcolor[1]/255, $bcolor[2]/255);
				}
				break;
			}
			case 1: { // GRAY SCALE
				$this->gradients[$n]['colspace'] = 'DeviceGray';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F', $bcolor[0]/255);
				}
				break;
			}
		}
		$num_stops = count($stops);
		$last_stop_id = $num_stops - 1;
		foreach ($stops as $key => $stop) {
			$this->gradients[$n]['colors'][$key] = array();
			// offset represents a location along the gradient vector
			if (isset($stop['offset'])) {
				$this->gradients[$n]['colors'][$key]['offset'] = $stop['offset'];
			} else {
				if ($key == 0) {
					$this->gradients[$n]['colors'][$key]['offset'] = 0;
				} elseif ($key == $last_stop_id) {
					$this->gradients[$n]['colors'][$key]['offset'] = 1;
				} else {
					$offsetstep = (1 - $this->gradients[$n]['colors'][($key - 1)]['offset']) / ($num_stops - $key);
					$this->gradients[$n]['colors'][$key]['offset'] = $this->gradients[$n]['colors'][($key - 1)]['offset'] + $offsetstep;
				}
			}
			if (isset($stop['opacity'])) {
				$this->gradients[$n]['colors'][$key]['opacity'] = $stop['opacity'];
				if ((!$this->pdfa_mode) AND ($stop['opacity'] < 1)) {
					$this->gradients[$n]['transparency'] = true;
				}
			} else {
				$this->gradients[$n]['colors'][$key]['opacity'] = 1;
			}
			// exponent for the exponential interpolation function
			if (isset($stop['exponent'])) {
				$this->gradients[$n]['colors'][$key]['exponent'] = $stop['exponent'];
			} else {
				$this->gradients[$n]['colors'][$key]['exponent'] = 1;
			}
			// set colors
			$color = array_values($stop['color']);
			switch($numcolspace) {
				case 5:   // SPOT
				case 4: { // CMYK
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F %F %F %F', $color[0]/100, $color[1]/100, $color[2]/100, $color[3]/100);
					break;
				}
				case 3: { // RGB
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F %F %F', $color[0]/255, $color[1]/255, $color[2]/255);
					break;
				}
				case 1: { // GRAY SCALE
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F', $color[0]/255);
					break;
				}
			}
		}
		if ($this->gradients[$n]['transparency']) {
			// paint luminosity gradient
			$this->_out('/TGS'.$n.' gs');
		}
		//paint the gradient
		$this->_out('/Sh'.$n.' sh');
		//restore previous Graphic State
		$this->_out('Q');
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['gradients'][$n] = $this->gradients[$n];
		}
	}

	/**
	 * Output gradient shaders.
	 * @author Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @protected
	 */
	function _putshaders() {
		if ($this->pdfa_mode) {
			return;
		}
		$idt = count($this->gradients); //index for transparency gradients
		foreach ($this->gradients as $id => $grad) {
			if (($grad['type'] == 2) OR ($grad['type'] == 3)) {
				$fc = $this->_newobj();
				$out = '<<';
				$out .= ' /FunctionType 3';
				$out .= ' /Domain [0 1]';
				$functions = '';
				$bounds = '';
				$encode = '';
				$i = 1;
				$num_cols = count($grad['colors']);
				$lastcols = $num_cols - 1;
				for ($i = 1; $i < $num_cols; ++$i) {
					$functions .= ($fc + $i).' 0 R ';
					if ($i < $lastcols) {
						$bounds .= sprintf('%F ', $grad['colors'][$i]['offset']);
					}
					$encode .= '0 1 ';
				}
				$out .= ' /Functions ['.trim($functions).']';
				$out .= ' /Bounds ['.trim($bounds).']';
				$out .= ' /Encode ['.trim($encode).']';
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
				for ($i = 1; $i < $num_cols; ++$i) {
					$this->_newobj();
					$out = '<<';
					$out .= ' /FunctionType 2';
					$out .= ' /Domain [0 1]';
					$out .= ' /C0 ['.$grad['colors'][($i - 1)]['color'].']';
					$out .= ' /C1 ['.$grad['colors'][$i]['color'].']';
					$out .= ' /N '.$grad['colors'][$i]['exponent'];
					$out .= ' >>';
					$out .= "\n".'endobj';
					$this->_out($out);
				}
				// set transparency fuctions
				if ($grad['transparency']) {
					$ft = $this->_newobj();
					$out = '<<';
					$out .= ' /FunctionType 3';
					$out .= ' /Domain [0 1]';
					$functions = '';
					$i = 1;
					$num_cols = count($grad['colors']);
					for ($i = 1; $i < $num_cols; ++$i) {
						$functions .= ($ft + $i).' 0 R ';
					}
					$out .= ' /Functions ['.trim($functions).']';
					$out .= ' /Bounds ['.trim($bounds).']';
					$out .= ' /Encode ['.trim($encode).']';
					$out .= ' >>';
					$out .= "\n".'endobj';
					$this->_out($out);
					for ($i = 1; $i < $num_cols; ++$i) {
						$this->_newobj();
						$out = '<<';
						$out .= ' /FunctionType 2';
						$out .= ' /Domain [0 1]';
						$out .= ' /C0 ['.$grad['colors'][($i - 1)]['opacity'].']';
						$out .= ' /C1 ['.$grad['colors'][$i]['opacity'].']';
						$out .= ' /N '.$grad['colors'][$i]['exponent'];
						$out .= ' >>';
						$out .= "\n".'endobj';
						$this->_out($out);
					}
				}
			}
			// set shading object
			$this->_newobj();
			$out = '<< /ShadingType '.$grad['type'];
			if (isset($grad['colspace'])) {
				$out .= ' /ColorSpace /'.$grad['colspace'];
			} else {
				$out .= ' /ColorSpace /DeviceRGB';
			}
			if (isset($grad['background']) AND !empty($grad['background'])) {
				$out .= ' /Background ['.$grad['background'].']';
			}
			if (isset($grad['antialias']) AND ($grad['antialias'] === true)) {
				$out .= ' /AntiAlias true';
			}
			if ($grad['type'] == 2) {
				$out .= ' '.sprintf('/Coords [%F %F %F %F]', $grad['coords'][0], $grad['coords'][1], $grad['coords'][2], $grad['coords'][3]);
				$out .= ' /Domain [0 1]';
				$out .= ' /Function '.$fc.' 0 R';
				$out .= ' /Extend [true true]';
				$out .= ' >>';
			} elseif ($grad['type'] == 3) {
				//x0, y0, r0, x1, y1, r1
				//at this this time radius of inner circle is 0
				$out .= ' '.sprintf('/Coords [%F %F 0 %F %F %F]', $grad['coords'][0], $grad['coords'][1], $grad['coords'][2], $grad['coords'][3], $grad['coords'][4]);
				$out .= ' /Domain [0 1]';
				$out .= ' /Function '.$fc.' 0 R';
				$out .= ' /Extend [true true]';
				$out .= ' >>';
			} elseif ($grad['type'] == 6) {
				$out .= ' /BitsPerCoordinate 16';
				$out .= ' /BitsPerComponent 8';
				$out .= ' /Decode[0 1 0 1 0 1 0 1 0 1]';
				$out .= ' /BitsPerFlag 8';
				$stream = $this->_getrawstream($grad['stream']);
				$out .= ' /Length '.strlen($stream);
				$out .= ' >>';
				$out .= ' stream'."\n".$stream."\n".'endstream';
			}
			$out .= "\n".'endobj';
			$this->_out($out);
			if ($grad['transparency']) {
				$shading_transparency = preg_replace('/\/ColorSpace \/[^\s]+/si', '/ColorSpace /DeviceGray', $out);
				$shading_transparency = preg_replace('/\/Function [0-9]+ /si', '/Function '.$ft.' ', $shading_transparency);
			}
			$this->gradients[$id]['id'] = $this->n;
			// set pattern object
			$this->_newobj();
			$out = '<< /Type /Pattern /PatternType 2';
			$out .= ' /Shading '.$this->gradients[$id]['id'].' 0 R';
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
			$this->gradients[$id]['pattern'] = $this->n;
			// set shading and pattern for transparency mask
			if ($grad['transparency']) {
				// luminosity pattern
				$idgs = $id + $idt;
				$this->_newobj();
				$this->_out($shading_transparency);
				$this->gradients[$idgs]['id'] = $this->n;
				$this->_newobj();
				$out = '<< /Type /Pattern /PatternType 2';
				$out .= ' /Shading '.$this->gradients[$idgs]['id'].' 0 R';
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
				$this->gradients[$idgs]['pattern'] = $this->n;
				// luminosity XObject
				$oid = $this->_newobj();
				$this->xobjects['LX'.$oid] = array('n' => $oid);
				$filter = '';
				$stream = 'q /a0 gs /Pattern cs /p'.$idgs.' scn 0 0 '.$this->wPt.' '.$this->hPt.' re f Q';
				if ($this->compress) {
					$filter = ' /Filter /FlateDecode';
					$stream = gzcompress($stream);
				}
				$stream = $this->_getrawstream($stream);
				$out = '<< /Type /XObject /Subtype /Form /FormType 1'.$filter;
				$out .= ' /Length '.strlen($stream);
				$rect = sprintf('%F %F', $this->wPt, $this->hPt);
				$out .= ' /BBox [0 0 '.$rect.']';
				$out .= ' /Group << /Type /Group /S /Transparency /CS /DeviceGray >>';
				$out .= ' /Resources <<';
				$out .= ' /ExtGState << /a0 << /ca 1 /CA 1 >> >>';
				$out .= ' /Pattern << /p'.$idgs.' '.$this->gradients[$idgs]['pattern'].' 0 R >>';
				$out .= ' >>';
				$out .= ' >> ';
				$out .= ' stream'."\n".$stream."\n".'endstream';
				$out .= "\n".'endobj';
				$this->_out($out);
				// SMask
				$this->_newobj();
				$out = '<< /Type /Mask /S /Luminosity /G '.($this->n - 1).' 0 R >>'."\n".'endobj';
				$this->_out($out);
				// ExtGState
				$this->_newobj();
				$out = '<< /Type /ExtGState /SMask '.($this->n - 1).' 0 R /AIS false >>'."\n".'endobj';
				$this->_out($out);
				$this->extgstates[] = array('n' => $this->n, 'name' => 'TGS'.$id);
			}
		}
	}

	/**
	 * Draw the sector of a circle.
	 * It can be used for instance to render pie charts.
	 * @param $xc (float) abscissa of the center.
	 * @param $yc (float) ordinate of the center.
	 * @param $r (float) radius.
	 * @param $a (float) start angle (in degrees).
	 * @param $b (float) end angle (in degrees).
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $cw: (float) indicates whether to go clockwise (default: true).
	 * @param $o: (float) origin of angles (0 for 3 o'clock, 90 for noon, 180 for 9 o'clock, 270 for 6 o'clock). Default: 90.
	 * @author Maxime Delorme, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function PieSector($xc, $yc, $r, $a, $b, $style='FD', $cw=true, $o=90) {
		$this->PieSectorXY($xc, $yc, $r, $r, $a, $b, $style, $cw, $o);
	}

	/**
	 * Draw the sector of an ellipse.
	 * It can be used for instance to render pie charts.
	 * @param $xc (float) abscissa of the center.
	 * @param $yc (float) ordinate of the center.
	 * @param $rx (float) the x-axis radius.
	 * @param $ry (float) the y-axis radius.
	 * @param $a (float) start angle (in degrees).
	 * @param $b (float) end angle (in degrees).
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $cw: (float) indicates whether to go clockwise.
	 * @param $o: (float) origin of angles (0 for 3 o'clock, 90 for noon, 180 for 9 o'clock, 270 for 6 o'clock).
	 * @param $nc (integer) Number of curves used to draw a 90 degrees portion of arc.
	 * @author Maxime Delorme, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function PieSectorXY($xc, $yc, $rx, $ry, $a, $b, $style='FD', $cw=false, $o=0, $nc=2) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rtl) {
			$xc = ($this->w - $xc);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		if ($cw) {
			$d = $b;
			$b = (360 - $a + $o);
			$a = (360 - $d + $o);
		} else {
			$b += $o;
			$a += $o;
		}
		$this->_outellipticalarc($xc, $yc, $rx, $ry, 0, $a, $b, true, $nc);
		$this->_out($op);
	}

	/**
	 * Embed vector-based Adobe Illustrator (AI) or AI-compatible EPS files.
	 * NOTE: EPS is not yet fully implemented, use the setRasterizeVectorImages() method to enable/disable rasterization of vector images using ImageMagick library.
	 * Only vector drawing is supported, not text or bitmap.
	 * Although the script was successfully tested with various AI format versions, best results are probably achieved with files that were exported in the AI3 format (tested with Illustrator CS2, Freehand MX and Photoshop CS2).
	 * @param $file (string) Name of the file containing the image or a '@' character followed by the EPS/AI data string.
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $h (float) Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $useBoundingBox (boolean) specifies whether to position the bounding box (true) or the complete canvas (false) at location (x,y). Default value is true.
	 * @param $align (string) Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $palign (string) Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $fitonpage (boolean) if true the image is resized to not exceed page dimensions.
	 * @param $fixoutvals (boolean) if true remove values outside the bounding box.
	 * @author Valentin Schmidt, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function ImageEps($file, $x='', $y='', $w=0, $h=0, $link='', $useBoundingBox=true, $align='', $palign='', $border=0, $fitonpage=false, $fixoutvals=false) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rasterize_vector_images AND ($w > 0) AND ($h > 0)) {
			// convert EPS to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$k = $this->k;
		if ($file{0} === '@') { // image from string
			$data = substr($file, 1);
		} else { // EPS/AI file
			$data = TCPDF_STATIC::fileGetContents($file);
		}
		if ($data === FALSE) {
			$this->Error('EPS file not found: '.$file);
		}
		$regs = array();
		// EPS/AI compatibility check (only checks files created by Adobe Illustrator!)
		preg_match("/%%Creator:([^\r\n]+)/", $data, $regs); # find Creator
		if (count($regs) > 1) {
			$version_str = trim($regs[1]); # e.g. "Adobe Illustrator(R) 8.0"
			if (strpos($version_str, 'Adobe Illustrator') !== false) {
				$versexp = explode(' ', $version_str);
				$version = (float)array_pop($versexp);
				if ($version >= 9) {
					$this->Error('This version of Adobe Illustrator file is not supported: '.$file);
				}
			}
		}
		// strip binary bytes in front of PS-header
		$start = strpos($data, '%!PS-Adobe');
		if ($start > 0) {
			$data = substr($data, $start);
		}
		// find BoundingBox params
		preg_match("/%%BoundingBox:([^\r\n]+)/", $data, $regs);
		if (count($regs) > 1) {
			list($x1, $y1, $x2, $y2) = explode(' ', trim($regs[1]));
		} else {
			$this->Error('No BoundingBox found in EPS/AI file: '.$file);
		}
		$start = strpos($data, '%%EndSetup');
		if ($start === false) {
			$start = strpos($data, '%%EndProlog');
		}
		if ($start === false) {
			$start = strpos($data, '%%BoundingBox');
		}
		$data = substr($data, $start);
		$end = strpos($data, '%%PageTrailer');
		if ($end===false) {
			$end = strpos($data, 'showpage');
		}
		if ($end) {
			$data = substr($data, 0, $end);
		}
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			$w = ($x2 - $x1) / $k;
			$h = ($y2 - $y1) / $k;
		} elseif ($w <= 0) {
			$w = ($x2-$x1) / $k * ($h / (($y2 - $y1) / $k));
		} elseif ($h <= 0) {
			$h = ($y2 - $y1) / $k * ($w / (($x2 - $x1) / $k));
		}
		// fit the image on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		if ($this->rasterize_vector_images) {
			// convert EPS to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
		}
		// set scaling factors
		$scale_x = $w / (($x2 - $x1) / $k);
		$scale_y = $h / (($y2 - $y1) / $k);
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x - $w;
			}
			$this->img_rb_x = $ximg;
		} else {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x;
			}
			$this->img_rb_x = $ximg + $w;
		}
		if ($useBoundingBox) {
			$dx = $ximg * $k - $x1;
			$dy = $y * $k - $y1;
		} else {
			$dx = $ximg * $k;
			$dy = $y * $k;
		}
		// save the current graphic state
		$this->_out('q'.$this->epsmarker);
		// translate
		$this->_out(sprintf('%F %F %F %F %F %F cm', 1, 0, 0, 1, $dx, $dy + ($this->hPt - (2 * $y * $k) - ($y2 - $y1))));
		// scale
		if (isset($scale_x)) {
			$this->_out(sprintf('%F %F %F %F %F %F cm', $scale_x, 0, 0, $scale_y, $x1 * (1 - $scale_x), $y2 * (1 - $scale_y)));
		}
		// handle pc/unix/mac line endings
		$lines = preg_split('/[\r\n]+/si', $data, -1, PREG_SPLIT_NO_EMPTY);
		$u=0;
		$cnt = count($lines);
		for ($i=0; $i < $cnt; ++$i) {
			$line = $lines[$i];
			if (($line == '') OR ($line{0} == '%')) {
				continue;
			}
			$len = strlen($line);
			// check for spot color names
			$color_name = '';
			if (strcasecmp('x', substr(trim($line), -1)) == 0) {
				if (preg_match('/\([^\)]*\)/', $line, $matches) > 0) {
					// extract spot color name
					$color_name = $matches[0];
					// remove color name from string
					$line = str_replace(' '.$color_name, '', $line);
					// remove pharentesis from color name
					$color_name = substr($color_name, 1, -1);
				}
			}
			$chunks = explode(' ', $line);
			$cmd = trim(array_pop($chunks));
			// RGB
			if (($cmd == 'Xa') OR ($cmd == 'XA')) {
				$b = array_pop($chunks);
				$g = array_pop($chunks);
				$r = array_pop($chunks);
				$this->_out(''.$r.' '.$g.' '.$b.' '.($cmd=='Xa'?'rg':'RG')); //substr($line, 0, -2).'rg' -> in EPS (AI8): c m y k r g b rg!
				continue;
			}
			$skip = false;
			if ($fixoutvals) {
				// check for values outside the bounding box
				switch ($cmd) {
					case 'm':
					case 'l':
					case 'L': {
						// skip values outside bounding box
						foreach ($chunks as $key => $val) {
							if ((($key % 2) == 0) AND (($val < $x1) OR ($val > $x2))) {
								$skip = true;
							} elseif ((($key % 2) != 0) AND (($val < $y1) OR ($val > $y2))) {
								$skip = true;
							}
						}
					}
				}
			}
			switch ($cmd) {
				case 'm':
				case 'l':
				case 'v':
				case 'y':
				case 'c':
				case 'k':
				case 'K':
				case 'g':
				case 'G':
				case 's':
				case 'S':
				case 'J':
				case 'j':
				case 'w':
				case 'M':
				case 'd':
				case 'n': {
					if ($skip) {
						break;
					}
					$this->_out($line);
					break;
				}
				case 'x': {// custom fill color
					if (empty($color_name)) {
						// CMYK color
						list($col_c, $col_m, $col_y, $col_k) = $chunks;
						$this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' k');
					} else {
						// Spot Color (CMYK + tint)
						list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
						$this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
						$color_cmd = sprintf('/CS%d cs %F scn', $this->spot_colors[$color_name]['i'], (1 - $col_t));
						$this->_out($color_cmd);
					}
					break;
				}
				case 'X': { // custom stroke color
					if (empty($color_name)) {
						// CMYK color
						list($col_c, $col_m, $col_y, $col_k) = $chunks;
						$this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' K');
					} else {
						// Spot Color (CMYK + tint)
						list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
						$this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
						$color_cmd = sprintf('/CS%d CS %F SCN', $this->spot_colors[$color_name]['i'], (1 - $col_t));
						$this->_out($color_cmd);
					}
					break;
				}
				case 'Y':
				case 'N':
				case 'V':
				case 'L':
				case 'C': {
					if ($skip) {
						break;
					}
					$line[($len - 1)] = strtolower($cmd);
					$this->_out($line);
					break;
				}
				case 'b':
				case 'B': {
					$this->_out($cmd . '*');
					break;
				}
				case 'f':
				case 'F': {
					if ($u > 0) {
						$isU = false;
						$max = min(($i + 5), $cnt);
						for ($j = ($i + 1); $j < $max; ++$j) {
							$isU = ($isU OR (($lines[$j] == 'U') OR ($lines[$j] == '*U')));
						}
						if ($isU) {
							$this->_out('f*');
						}
					} else {
						$this->_out('f*');
					}
					break;
				}
				case '*u': {
					++$u;
					break;
				}
				case '*U': {
					--$u;
					break;
				}
			}
		}
		// restore previous graphic state
		$this->_out($this->epsmarker.'Q');
		if (!empty($border)) {
			$bx = $this->x;
			$by = $this->y;
			$this->x = $ximg;
			if ($this->rtl) {
				$this->x += $w;
			}
			$this->y = $y;
			$this->Cell($w, $h, '', $border, 0, '', 0, '', 0, true);
			$this->x = $bx;
			$this->y = $by;
		}
		if ($link) {
			$this->Link($ximg, $y, $w, $h, $link, 0);
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->SetY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
	}

	/**
	 * Set document barcode.
	 * @param $bc (string) barcode
	 * @public
	 */
	public function setBarcode($bc='') {
		$this->barcode = $bc;
	}

	/**
	 * Get current barcode.
	 * @return string
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getBarcode() {
		return $this->barcode;
	}

	/**
	 * Print a Linear Barcode.
	 * @param $code (string) code to print
	 * @param $type (string) type of barcode (see tcpdf_barcodes_1d.php for supported formats).
	 * @param $x (int) x position in user units (empty string = current x position)
	 * @param $y (int) y position in user units (empty string = current y position)
	 * @param $w (int) width in user units (empty string = remaining page width)
	 * @param $h (int) height in user units (empty string = remaining page height)
	 * @param $xres (float) width of the smallest bar in user units (empty string = default value = 0.4mm)
	 * @param $style (array) array of options:<ul>
	 * <li>boolean $style['border'] if true prints a border</li>
	 * <li>int $style['padding'] padding to leave around the barcode in user units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['hpadding'] horizontal padding in user units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['vpadding'] vertical padding in user units (set to 'auto' for automatic padding)</li>
	 * <li>array $style['fgcolor'] color array for bars and text</li>
	 * <li>mixed $style['bgcolor'] color array for background (set to false for transparent)</li>
	 * <li>boolean $style['text'] if true prints text below the barcode</li>
	 * <li>string $style['label'] override default label</li>
	 * <li>string $style['font'] font name for text</li><li>int $style['fontsize'] font size for text</li>
	 * <li>int $style['stretchtext']: 0 = disabled; 1 = horizontal scaling only if necessary; 2 = forced horizontal scaling; 3 = character spacing only if necessary; 4 = forced character spacing.</li>
	 * <li>string $style['position'] horizontal position of the containing barcode cell on the page: L = left margin; C = center; R = right margin.</li>
	 * <li>string $style['align'] horizontal position of the barcode on the containing rectangle: L = left; C = center; R = right.</li>
	 * <li>string $style['stretch'] if true stretch the barcode to best fit the available width, otherwise uses $xres resolution for a single bar.</li>
	 * <li>string $style['fitwidth'] if true reduce the width to fit the barcode width + padding. When this option is enabled the 'stretch' option is automatically disabled.</li>
	 * <li>string $style['cellfitalign'] this option works only when 'fitwidth' is true and 'position' is unset or empty. Set the horizontal position of the containing barcode cell inside the specified rectangle: L = left; C = center; R = right.</li></ul>
	 * @param $align (string) Indicates the alignment of the pointer next to barcode insertion relative to barcode height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @author Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function write1DBarcode($code, $type, $x='', $y='', $w='', $h='', $xres='', $style='', $align='') {
		if (TCPDF_STATIC::empty_string(trim($code))) {
			return;
		}
		require_once(dirname(__FILE__).'/tcpdf_barcodes_1d.php');
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// create new barcode object
		$barcodeobj = new TCPDFBarcode($code, $type);
		$arrcode = $barcodeobj->getBarcodeArray();
		if (($arrcode === false) OR empty($arrcode) OR ($arrcode['maxw'] <= 0)) {
			$this->Error('Error in 1D barcode string');
		}
		if ($arrcode['maxh'] <= 0) {
			$arrcode['maxh'] = 1;
		}
		// set default values
		if (!isset($style['position'])) {
			$style['position'] = '';
		} elseif ($style['position'] == 'S') {
			// keep this for backward compatibility
			$style['position'] = '';
			$style['stretch'] = true;
		}
		if (!isset($style['fitwidth'])) {
			if (!isset($style['stretch'])) {
				$style['fitwidth'] = true;
			} else {
				$style['fitwidth'] = false;
			}
		}
		if ($style['fitwidth']) {
			// disable stretch
			$style['stretch'] = false;
		}
		if (!isset($style['stretch'])) {
			if (($w === '') OR ($w <= 0)) {
				$style['stretch'] = false;
			} else {
				$style['stretch'] = true;
			}
		}
		if (!isset($style['fgcolor'])) {
			$style['fgcolor'] = array(0,0,0); // default black
		}
		if (!isset($style['bgcolor'])) {
			$style['bgcolor'] = false; // default transparent
		}
		if (!isset($style['border'])) {
			$style['border'] = false;
		}
		$fontsize = 0;
		if (!isset($style['text'])) {
			$style['text'] = false;
		}
		if ($style['text'] AND isset($style['font'])) {
			if (isset($style['fontsize'])) {
				$fontsize = $style['fontsize'];
			}
			$this->SetFont($style['font'], '', $fontsize);
		}
		if (!isset($style['stretchtext'])) {
			$style['stretchtext'] = 4;
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		if (($w === '') OR ($w <= 0)) {
			if ($this->rtl) {
				$w = $x - $this->lMargin;
			} else {
				$w = $this->w - $this->rMargin - $x;
			}
		}
		// padding
		if (!isset($style['padding'])) {
			$padding = 0;
		} elseif ($style['padding'] === 'auto') {
			$padding = 10 * ($w / ($arrcode['maxw'] + 20));
		} else {
			$padding = floatval($style['padding']);
		}
		// horizontal padding
		if (!isset($style['hpadding'])) {
			$hpadding = $padding;
		} elseif ($style['hpadding'] === 'auto') {
			$hpadding = 10 * ($w / ($arrcode['maxw'] + 20));
		} else {
			$hpadding = floatval($style['hpadding']);
		}
		// vertical padding
		if (!isset($style['vpadding'])) {
			$vpadding = $padding;
		} elseif ($style['vpadding'] === 'auto') {
			$vpadding = ($hpadding / 2);
		} else {
			$vpadding = floatval($style['vpadding']);
		}
		// calculate xres (single bar width)
		$max_xres = ($w - (2 * $hpadding)) / $arrcode['maxw'];
		if ($style['stretch']) {
			$xres = $max_xres;
		} else {
			if (TCPDF_STATIC::empty_string($xres)) {
				$xres = (0.141 * $this->k); // default bar width = 0.4 mm
			}
			if ($xres > $max_xres) {
				// correct xres to fit on $w
				$xres = $max_xres;
			}
			if ((isset($style['padding']) AND ($style['padding'] === 'auto'))
				OR (isset($style['hpadding']) AND ($style['hpadding'] === 'auto'))) {
				$hpadding = 10 * $xres;
				if (isset($style['vpadding']) AND ($style['vpadding'] === 'auto')) {
					$vpadding = ($hpadding / 2);
				}
			}
		}
		if ($style['fitwidth']) {
			$wold = $w;
			$w = (($arrcode['maxw'] * $xres) + (2 * $hpadding));
			if (isset($style['cellfitalign'])) {
				switch ($style['cellfitalign']) {
					case 'L': {
						if ($this->rtl) {
							$x -= ($wold - $w);
						}
						break;
					}
					case 'R': {
						if (!$this->rtl) {
							$x += ($wold - $w);
						}
						break;
					}
					case 'C': {
						if ($this->rtl) {
							$x -= (($wold - $w) / 2);
						} else {
							$x += (($wold - $w) / 2);
						}
						break;
					}
					default : {
						break;
					}
				}
			}
		}
		$text_height = ($this->cell_height_ratio * $fontsize / $this->k);
		// height
		if (($h === '') OR ($h <= 0)) {
			// set default height
			$h = (($arrcode['maxw'] * $xres) / 3) + (2 * $vpadding) + $text_height;
		}
		$barh = $h - $text_height - (2 * $vpadding);
		if ($barh <=0) {
			// try to reduce font or padding to fit barcode on available height
			if ($text_height > $h) {
				$fontsize = (($h * $this->k) / (4 * $this->cell_height_ratio));
				$text_height = ($this->cell_height_ratio * $fontsize / $this->k);
				$this->SetFont($style['font'], '', $fontsize);
			}
			if ($vpadding > 0) {
				$vpadding = (($h - $text_height) / 4);
			}
			$barh = $h - $text_height - (2 * $vpadding);
		}
		// fit the barcode on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, false);
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($style['position'] == 'L') {
				$xpos = $this->lMargin;
			} elseif ($style['position'] == 'C') {
				$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($style['position'] == 'R') {
				$xpos = $this->w - $this->rMargin - $w;
			} else {
				$xpos = $x - $w;
			}
			$this->img_rb_x = $xpos;
		} else {
			if ($style['position'] == 'L') {
				$xpos = $this->lMargin;
			} elseif ($style['position'] == 'C') {
				$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($style['position'] == 'R') {
				$xpos = $this->w - $this->rMargin - $w;
			} else {
				$xpos = $x;
			}
			$this->img_rb_x = $xpos + $w;
		}
		$xpos_rect = $xpos;
		if (!isset($style['align'])) {
			$style['align'] = 'C';
		}
		switch ($style['align']) {
			case 'L': {
				$xpos = $xpos_rect + $hpadding;
				break;
			}
			case 'R': {
				$xpos = $xpos_rect + ($w - ($arrcode['maxw'] * $xres)) - $hpadding;
				break;
			}
			case 'C':
			default : {
				$xpos = $xpos_rect + (($w - ($arrcode['maxw'] * $xres)) / 2);
				break;
			}
		}
		$xpos_text = $xpos;
		// barcode is always printed in LTR direction
		$tempRTL = $this->rtl;
		$this->rtl = false;
		// print background color
		if ($style['bgcolor']) {
			$this->Rect($xpos_rect, $y, $w, $h, $style['border'] ? 'DF' : 'F', '', $style['bgcolor']);
		} elseif ($style['border']) {
			$this->Rect($xpos_rect, $y, $w, $h, 'D');
		}
		// set foreground color
		$this->SetDrawColorArray($style['fgcolor']);
		$this->SetTextColorArray($style['fgcolor']);
		// print bars
		foreach ($arrcode['bcode'] as $k => $v) {
			$bw = ($v['w'] * $xres);
			if ($v['t']) {
				// draw a vertical bar
				$ypos = $y + $vpadding + ($v['p'] * $barh / $arrcode['maxh']);
				$this->Rect($xpos, $ypos, $bw, ($v['h'] * $barh / $arrcode['maxh']), 'F', array(), $style['fgcolor']);
			}
			$xpos += $bw;
		}
		// print text
		if ($style['text']) {
			if (isset($style['label']) AND !TCPDF_STATIC::empty_string($style['label'])) {
				$label = $style['label'];
			} else {
				$label = $code;
			}
			$txtwidth = ($arrcode['maxw'] * $xres);
			if ($this->GetStringWidth($label) > $txtwidth) {
				$style['stretchtext'] = 2;
			}
			// print text
			$this->x = $xpos_text;
			$this->y = $y + $vpadding + $barh;
			$cellpadding = $this->cell_padding;
			$this->SetCellPadding(0);
			$this->Cell($txtwidth, '', $label, 0, 0, 'C', false, '', $style['stretchtext'], false, 'T', 'T');
			$this->cell_padding = $cellpadding;
		}
		// restore original direction
		$this->rtl = $tempRTL;
		// restore previous settings
		$this->setGraphicVars($gvars);
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h / 2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->SetY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
	}

	/**
	 * Print 2D Barcode.
	 * @param $code (string) code to print
	 * @param $type (string) type of barcode (see tcpdf_barcodes_2d.php for supported formats).
	 * @param $x (int) x position in user units
	 * @param $y (int) y position in user units
	 * @param $w (int) width in user units
	 * @param $h (int) height in user units
	 * @param $style (array) array of options:<ul>
	 * <li>boolean $style['border'] if true prints a border around the barcode</li>
	 * <li>int $style['padding'] padding to leave around the barcode in barcode units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['hpadding'] horizontal padding in barcode units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['vpadding'] vertical padding in barcode units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['module_width'] width of a single module in points</li>
	 * <li>int $style['module_height'] height of a single module in points</li>
	 * <li>array $style['fgcolor'] color array for bars and text</li>
	 * <li>mixed $style['bgcolor'] color array for background or false for transparent</li>
	 * <li>string $style['position'] barcode position on the page: L = left margin; C = center; R = right margin; S = stretch</li><li>$style['module_width'] width of a single module in points</li>
	 * <li>$style['module_height'] height of a single module in points</li></ul>
	 * @param $align (string) Indicates the alignment of the pointer next to barcode insertion relative to barcode height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $distort (boolean) if true distort the barcode to fit width and height, otherwise preserve aspect ratio
	 * @author Nicola Asuni
	 * @since 4.5.037 (2009-04-07)
	 * @public
	 */
	public function write2DBarcode($code, $type, $x='', $y='', $w='', $h='', $style='', $align='', $distort=false) {
		if (TCPDF_STATIC::empty_string(trim($code))) {
			return;
		}
		require_once(dirname(__FILE__).'/tcpdf_barcodes_2d.php');
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// create new barcode object
		$barcodeobj = new TCPDF2DBarcode($code, $type);
		$arrcode = $barcodeobj->getBarcodeArray();
		if (($arrcode === false) OR empty($arrcode) OR !isset($arrcode['num_rows']) OR ($arrcode['num_rows'] == 0) OR !isset($arrcode['num_cols']) OR ($arrcode['num_cols'] == 0)) {
			$this->Error('Error in 2D barcode string');
		}
		// set default values
		if (!isset($style['position'])) {
			$style['position'] = '';
		}
		if (!isset($style['fgcolor'])) {
			$style['fgcolor'] = array(0,0,0); // default black
		}
		if (!isset($style['bgcolor'])) {
			$style['bgcolor'] = false; // default transparent
		}
		if (!isset($style['border'])) {
			$style['border'] = false;
		}
		// padding
		if (!isset($style['padding'])) {
			$style['padding'] = 0;
		} elseif ($style['padding'] === 'auto') {
			$style['padding'] = 4;
		}
		if (!isset($style['hpadding'])) {
			$style['hpadding'] = $style['padding'];
		} elseif ($style['hpadding'] === 'auto') {
			$style['hpadding'] = 4;
		}
		if (!isset($style['vpadding'])) {
			$style['vpadding'] = $style['padding'];
		} elseif ($style['vpadding'] === 'auto') {
			$style['vpadding'] = 4;
		}
		$hpad = (2 * $style['hpadding']);
		$vpad = (2 * $style['vpadding']);
		// cell (module) dimension
		if (!isset($style['module_width'])) {
			$style['module_width'] = 1; // width of a single module in points
		}
		if (!isset($style['module_height'])) {
			$style['module_height'] = 1; // height of a single module in points
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		// number of barcode columns and rows
		$rows = $arrcode['num_rows'];
		$cols = $arrcode['num_cols'];
		if (($rows <= 0) || ($cols <= 0)){
			$this->Error('Error in 2D barcode string');
		}
		// module width and height
		$mw = $style['module_width'];
		$mh = $style['module_height'];
		if (($mw <= 0) OR ($mh <= 0)) {
			$this->Error('Error in 2D barcode string');
		}
		// get max dimensions
		if ($this->rtl) {
			$maxw = $x - $this->lMargin;
		} else {
			$maxw = $this->w - $this->rMargin - $x;
		}
		$maxh = ($this->h - $this->tMargin - $this->bMargin);
		$ratioHW = ((($rows * $mh) + $hpad) / (($cols * $mw) + $vpad));
		$ratioWH = ((($cols * $mw) + $vpad) / (($rows * $mh) + $hpad));
		if (!$distort) {
			if (($maxw * $ratioHW) > $maxh) {
				$maxw = $maxh * $ratioWH;
			}
			if (($maxh * $ratioWH) > $maxw) {
				$maxh = $maxw * $ratioHW;
			}
		}
		// set maximum dimesions
		if ($w > $maxw) {
			$w = $maxw;
		}
		if ($h > $maxh) {
			$h = $maxh;
		}
		// set dimensions
		if ((($w === '') OR ($w <= 0)) AND (($h === '') OR ($h <= 0))) {
			$w = ($cols + $hpad) * ($mw / $this->k);
			$h = ($rows + $vpad) * ($mh / $this->k);
		} elseif (($w === '') OR ($w <= 0)) {
			$w = $h * $ratioWH;
		} elseif (($h === '') OR ($h <= 0)) {
			$h = $w * $ratioHW;
		}
		// barcode size (excluding padding)
		$bw = ($w * $cols) / ($cols + $hpad);
		$bh = ($h * $rows) / ($rows + $vpad);
		// dimension of single barcode cell unit
		$cw = $bw / $cols;
		$ch = $bh / $rows;
		if (!$distort) {
			if (($cw / $ch) > ($mw / $mh)) {
				// correct horizontal distortion
				$cw = $ch * $mw / $mh;
				$bw = $cw * $cols;
				$style['hpadding'] = ($w - $bw) / (2 * $cw);
			} else {
				// correct vertical distortion
				$ch = $cw * $mh / $mw;
				$bh = $ch * $rows;
				$style['vpadding'] = ($h - $bh) / (2 * $ch);
			}
		}
		// fit the barcode on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, false);
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($style['position'] == 'L') {
				$xpos = $this->lMargin;
			} elseif ($style['position'] == 'C') {
				$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($style['position'] == 'R') {
				$xpos = $this->w - $this->rMargin - $w;
			} else {
				$xpos = $x - $w;
			}
			$this->img_rb_x = $xpos;
		} else {
			if ($style['position'] == 'L') {
				$xpos = $this->lMargin;
			} elseif ($style['position'] == 'C') {
				$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($style['position'] == 'R') {
				$xpos = $this->w - $this->rMargin - $w;
			} else {
				$xpos = $x;
			}
			$this->img_rb_x = $xpos + $w;
		}
		$xstart = $xpos + ($style['hpadding'] * $cw);
		$ystart = $y + ($style['vpadding'] * $ch);
		// barcode is always printed in LTR direction
		$tempRTL = $this->rtl;
		$this->rtl = false;
		// print background color
		if ($style['bgcolor']) {
			$this->Rect($xpos, $y, $w, $h, $style['border'] ? 'DF' : 'F', '', $style['bgcolor']);
		} elseif ($style['border']) {
			$this->Rect($xpos, $y, $w, $h, 'D');
		}
		// set foreground color
		$this->SetDrawColorArray($style['fgcolor']);
		// print barcode cells
		// for each row
		for ($r = 0; $r < $rows; ++$r) {
			$xr = $xstart;
			// for each column
			for ($c = 0; $c < $cols; ++$c) {
				if ($arrcode['bcode'][$r][$c] == 1) {
					// draw a single barcode cell
					$this->Rect($xr, $ystart, $cw, $ch, 'F', array(), $style['fgcolor']);
				}
				$xr += $cw;
			}
			$ystart += $ch;
		}
		// restore original direction
		$this->rtl = $tempRTL;
		// restore previous settings
		$this->setGraphicVars($gvars);
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->SetY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
	}

	/**
	 * Returns an array containing current margins:
	 * <ul>
			<li>$ret['left'] = left margin</li>
			<li>$ret['right'] = right margin</li>
			<li>$ret['top'] = top margin</li>
			<li>$ret['bottom'] = bottom margin</li>
			<li>$ret['header'] = header margin</li>
			<li>$ret['footer'] = footer margin</li>
			<li>$ret['cell'] = cell padding array</li>
			<li>$ret['padding_left'] = cell left padding</li>
			<li>$ret['padding_top'] = cell top padding</li>
			<li>$ret['padding_right'] = cell right padding</li>
			<li>$ret['padding_bottom'] = cell bottom padding</li>
	 * </ul>
	 * @return array containing all margins measures
	 * @public
	 * @since 3.2.000 (2008-06-23)
	 */
	public function getMargins() {
		$ret = array(
			'left' => $this->lMargin,
			'right' => $this->rMargin,
			'top' => $this->tMargin,
			'bottom' => $this->bMargin,
			'header' => $this->header_margin,
			'footer' => $this->footer_margin,
			'cell' => $this->cell_padding,
			'padding_left' => $this->cell_padding['L'],
			'padding_top' => $this->cell_padding['T'],
			'padding_right' => $this->cell_padding['R'],
			'padding_bottom' => $this->cell_padding['B']
		);
		return $ret;
	}

	/**
	 * Returns an array containing original margins:
	 * <ul>
			<li>$ret['left'] = left margin</li>
			<li>$ret['right'] = right margin</li>
	 * </ul>
	 * @return array containing all margins measures
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getOriginalMargins() {
		$ret = array(
			'left' => $this->original_lMargin,
			'right' => $this->original_rMargin
		);
		return $ret;
	}

	/**
	 * Returns the current font size.
	 * @return current font size
	 * @public
	 * @since 3.2.000 (2008-06-23)
	 */
	public function getFontSize() {
		return $this->FontSize;
	}

	/**
	 * Returns the current font size in points unit.
	 * @return current font size in points unit
	 * @public
	 * @since 3.2.000 (2008-06-23)
	 */
	public function getFontSizePt() {
		return $this->FontSizePt;
	}

	/**
	 * Returns the current font family name.
	 * @return string current font family name
	 * @public
	 * @since 4.3.008 (2008-12-05)
	 */
	public function getFontFamily() {
		return $this->FontFamily;
	}

	/**
	 * Returns the current font style.
	 * @return string current font style
	 * @public
	 * @since 4.3.008 (2008-12-05)
	 */
	public function getFontStyle() {
		return $this->FontStyle;
	}

	/**
	 * Cleanup HTML code (requires HTML Tidy library).
	 * @param $html (string) htmlcode to fix
	 * @param $default_css (string) CSS commands to add
	 * @param $tagvs (array) parameters for setHtmlVSpace method
	 * @param $tidy_options (array) options for tidy_parse_string function
	 * @return string XHTML code cleaned up
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.017 (2010-11-16)
	 * @see setHtmlVSpace()
	 */
	public function fixHTMLCode($html, $default_css='', $tagvs='', $tidy_options='') {
		return TCPDF_STATIC::fixHTMLCode($html, $default_css, $tagvs, $tidy_options, $this->tagvspaces);
	}

	/**
	 * Returns the border width from CSS property
	 * @param $width (string) border width
	 * @return int with in user units
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderWidth($width) {
		if ($width == 'thin') {
			$width = (2 / $this->k);
		} elseif ($width == 'medium') {
			$width = (4 / $this->k);
		} elseif ($width == 'thick') {
			$width = (6 / $this->k);
		} else {
			$width = $this->getHTMLUnitToUnits($width, 1, 'px', false);
		}
		return $width;
	}

	/**
	 * Returns the border dash style from CSS property
	 * @param $style (string) border style to convert
	 * @return int sash style (return -1 in case of none or hidden border)
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderDashStyle($style) {
		switch (strtolower($style)) {
			case 'none':
			case 'hidden': {
				$dash = -1;
				break;
			}
			case 'dotted': {
				$dash = 1;
				break;
			}
			case 'dashed': {
				$dash = 3;
				break;
			}
			case 'double':
			case 'groove':
			case 'ridge':
			case 'inset':
			case 'outset':
			case 'solid':
			default: {
				$dash = 0;
				break;
			}
		}
		return $dash;
	}

	/**
	 * Returns the border style array from CSS border properties
	 * @param $cssborder (string) border properties
	 * @return array containing border properties
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderStyle($cssborder) {
		$bprop = preg_split('/[\s]+/', trim($cssborder));
		$border = array(); // value to be returned
		switch (count($bprop)) {
			case 3: {
				$width = $bprop[0];
				$style = $bprop[1];
				$color = $bprop[2];
				break;
			}
			case 2: {
				$width = 'medium';
				$style = $bprop[0];
				$color = $bprop[1];
				break;
			}
			case 1: {
				$width = 'medium';
				$style = $bprop[0];
				$color = 'black';
				break;
			}
			default: {
				$width = 'medium';
				$style = 'solid';
				$color = 'black';
				break;
			}
		}
		if ($style == 'none') {
			return array();
		}
		$border['cap'] = 'square';
		$border['join'] = 'miter';
		$border['dash'] = $this->getCSSBorderDashStyle($style);
		if ($border['dash'] < 0) {
			return array();
		}
		$border['width'] = $this->getCSSBorderWidth($width);
		$border['color'] = TCPDF_COLORS::convertHTMLColorToDec($color, $this->spot_colors);
		return $border;
	}

	/**
	 * Get the internal Cell padding from CSS attribute.
	 * @param $csspadding (string) padding properties
	 * @param $width (float) width of the containing element
	 * @return array of cell paddings
	 * @public
	 * @since 5.9.000 (2010-10-04)
	 */
	public function getCSSPadding($csspadding, $width=0) {
		$padding = preg_split('/[\s]+/', trim($csspadding));
		$cell_padding = array(); // value to be returned
		switch (count($padding)) {
			case 4: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[2];
				$cell_padding['L'] = $padding[3];
				break;
			}
			case 3: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[2];
				$cell_padding['L'] = $padding[1];
				break;
			}
			case 2: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[0];
				$cell_padding['L'] = $padding[1];
				break;
			}
			case 1: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[0];
				$cell_padding['B'] = $padding[0];
				$cell_padding['L'] = $padding[0];
				break;
			}
			default: {
				return $this->cell_padding;
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$cell_padding['T'] = $this->getHTMLUnitToUnits($cell_padding['T'], $width, 'px', false);
		$cell_padding['R'] = $this->getHTMLUnitToUnits($cell_padding['R'], $width, 'px', false);
		$cell_padding['B'] = $this->getHTMLUnitToUnits($cell_padding['B'], $width, 'px', false);
		$cell_padding['L'] = $this->getHTMLUnitToUnits($cell_padding['L'], $width, 'px', false);
		return $cell_padding;
	}

	/**
	 * Get the internal Cell margin from CSS attribute.
	 * @param $cssmargin (string) margin properties
	 * @param $width (float) width of the containing element
	 * @return array of cell margins
	 * @public
	 * @since 5.9.000 (2010-10-04)
	 */
	public function getCSSMargin($cssmargin, $width=0) {
		$margin = preg_split('/[\s]+/', trim($cssmargin));
		$cell_margin = array(); // value to be returned
		switch (count($margin)) {
			case 4: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[2];
				$cell_margin['L'] = $margin[3];
				break;
			}
			case 3: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[2];
				$cell_margin['L'] = $margin[1];
				break;
			}
			case 2: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[0];
				$cell_margin['L'] = $margin[1];
				break;
			}
			case 1: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[0];
				$cell_margin['B'] = $margin[0];
				$cell_margin['L'] = $margin[0];
				break;
			}
			default: {
				return $this->cell_margin;
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$cell_margin['T'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['T']), $width, 'px', false);
		$cell_margin['R'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['R']), $width, 'px', false);
		$cell_margin['B'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['B']), $width, 'px', false);
		$cell_margin['L'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['L']), $width, 'px', false);
		return $cell_margin;
	}

	/**
	 * Get the border-spacing from CSS attribute.
	 * @param $cssbspace (string) border-spacing CSS properties
	 * @param $width (float) width of the containing element
	 * @return array of border spacings
	 * @public
	 * @since 5.9.010 (2010-10-27)
	 */
	public function getCSSBorderMargin($cssbspace, $width=0) {
		$space = preg_split('/[\s]+/', trim($cssbspace));
		$border_spacing = array(); // value to be returned
		switch (count($space)) {
			case 2: {
				$border_spacing['H'] = $space[0];
				$border_spacing['V'] = $space[1];
				break;
			}
			case 1: {
				$border_spacing['H'] = $space[0];
				$border_spacing['V'] = $space[0];
				break;
			}
			default: {
				return array('H' => 0, 'V' => 0);
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$border_spacing['H'] = $this->getHTMLUnitToUnits($border_spacing['H'], $width, 'px', false);
		$border_spacing['V'] = $this->getHTMLUnitToUnits($border_spacing['V'], $width, 'px', false);
		return $border_spacing;
	}

	/**
	 * Returns the letter-spacing value from CSS value
	 * @param $spacing (string) letter-spacing value
	 * @param $parent (float) font spacing (tracking) value of the parent element
	 * @return float quantity to increases or decreases the space between characters in a text.
	 * @protected
	 * @since 5.9.000 (2010-10-02)
	 */
	protected function getCSSFontSpacing($spacing, $parent=0) {
		$val = 0; // value to be returned
		$spacing = trim($spacing);
		switch ($spacing) {
			case 'normal': {
				$val = 0;
				break;
			}
			case 'inherit': {
				if ($parent == 'normal') {
					$val = 0;
				} else {
					$val = $parent;
				}
				break;
			}
			default: {
				$val = $this->getHTMLUnitToUnits($spacing, 0, 'px', false);
			}
		}
		return $val;
	}

	/**
	 * Returns the percentage of font stretching from CSS value
	 * @param $stretch (string) stretch mode
	 * @param $parent (float) stretch value of the parent element
	 * @return float font stretching percentage
	 * @protected
	 * @since 5.9.000 (2010-10-02)
	 */
	protected function getCSSFontStretching($stretch, $parent=100) {
		$val = 100; // value to be returned
		$stretch = trim($stretch);
		switch ($stretch) {
			case 'ultra-condensed': {
				$val = 40;
				break;
			}
			case 'extra-condensed': {
				$val = 55;
				break;
			}
			case 'condensed': {
				$val = 70;
				break;
			}
			case 'semi-condensed': {
				$val = 85;
				break;
			}
			case 'normal': {
				$val = 100;
				break;
			}
			case 'semi-expanded': {
				$val = 115;
				break;
			}
			case 'expanded': {
				$val = 130;
				break;
			}
			case 'extra-expanded': {
				$val = 145;
				break;
			}
			case 'ultra-expanded': {
				$val = 160;
				break;
			}
			case 'wider': {
				$val = ($parent + 10);
				break;
			}
			case 'narrower': {
				$val = ($parent - 10);
				break;
			}
			case 'inherit': {
				if ($parent == 'normal') {
					$val = 100;
				} else {
					$val = $parent;
				}
				break;
			}
			default: {
				$val = $this->getHTMLUnitToUnits($stretch, 100, '%', false);
			}
		}
		return $val;
	}

	/**
	 * Convert HTML string containing font size value to points
	 * @param $val (string) String containing font size value and unit.
	 * @param $refsize (float) Reference font size in points.
	 * @param $parent_size (float) Parent font size in points.
	 * @param $defaultunit (string) Default unit (can be one of the following: %, em, ex, px, in, mm, pc, pt).
	 * @return float value in points
	 * @public
	 */
	public function getHTMLFontUnits($val, $refsize=12, $parent_size=12, $defaultunit='pt') {
		$refsize = TCPDF_FONTS::getFontRefSize($refsize);
		$parent_size = TCPDF_FONTS::getFontRefSize($parent_size, $refsize);
		switch ($val) {
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
				$size = ($parent_size - 3);
				break;
			}
			case 'larger': {
				$size = ($parent_size + 3);
				break;
			}
			default: {
				$size = $this->getHTMLUnitToUnits($val, $parent_size, $defaultunit, true);
			}
		}
		return $size;
	}

	/**
	 * Returns the HTML DOM array.
	 * @param $html (string) html code
	 * @return array
	 * @protected
	 * @since 3.2.000 (2008-06-20)
	 */
	protected function getHtmlDomArray($html) {
		// array of CSS styles ( selector => properties).
		$css = array();
		// get CSS array defined at previous call
		$matches = array();
		if (preg_match_all('/<cssarray>([^\<]*)<\/cssarray>/isU', $html, $matches) > 0) {
			if (isset($matches[1][0])) {
				$css = array_merge($css, unserialize($this->unhtmlentities($matches[1][0])));
			}
			$html = preg_replace('/<cssarray>(.*?)<\/cssarray>/isU', '', $html);
		}
		// extract external CSS files
		$matches = array();
		if (preg_match_all('/<link([^\>]*)>/isU', $html, $matches) > 0) {
			foreach ($matches[1] as $key => $link) {
				$type = array();
				if (preg_match('/type[\s]*=[\s]*"text\/css"/', $link, $type)) {
					$type = array();
					preg_match('/media[\s]*=[\s]*"([^"]*)"/', $link, $type);
					// get 'all' and 'print' media, other media types are discarded
					// (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
					if (empty($type) OR (isset($type[1]) AND (($type[1] == 'all') OR ($type[1] == 'print')))) {
						$type = array();
						if (preg_match('/href[\s]*=[\s]*"([^"]*)"/', $link, $type) > 0) {
							// read CSS data file
							$cssdata = TCPDF_STATIC::fileGetContents(trim($type[1]));
							if (($cssdata !== FALSE) AND (strlen($cssdata) > 0)) {
								$css = array_merge($css, TCPDF_STATIC::extractCSSproperties($cssdata));
							}
						}
					}
				}
			}
		}
		// extract style tags
		$matches = array();
		if (preg_match_all('/<style([^\>]*)>([^\<]*)<\/style>/isU', $html, $matches) > 0) {
			foreach ($matches[1] as $key => $media) {
				$type = array();
				preg_match('/media[\s]*=[\s]*"([^"]*)"/', $media, $type);
				// get 'all' and 'print' media, other media types are discarded
				// (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
				if (empty($type) OR (isset($type[1]) AND (($type[1] == 'all') OR ($type[1] == 'print')))) {
					$cssdata = $matches[2][$key];
					$css = array_merge($css, TCPDF_STATIC::extractCSSproperties($cssdata));
				}
			}
		}
		// create a special tag to contain the CSS array (used for table content)
		$csstagarray = '<cssarray>'.htmlentities(serialize($css)).'</cssarray>';
		// remove head and style blocks
		$html = preg_replace('/<head([^\>]*)>(.*?)<\/head>/siU', '', $html);
		$html = preg_replace('/<style([^\>]*)>([^\<]*)<\/style>/isU', '', $html);
		// define block tags
		$blocktags = array('blockquote','br','dd','dl','div','dt','h1','h2','h3','h4','h5','h6','hr','li','ol','p','pre','ul','tcpdf','table','tr','td');
		// define self-closing tags
		$selfclosingtags = array('area','base','basefont','br','hr','input','img','link','meta');
		// remove all unsupported tags (the line below lists all supported tags)
		$html = strip_tags($html, '<marker/><a><b><blockquote><body><br><br/><dd><del><div><dl><dt><em><font><form><h1><h2><h3><h4><h5><h6><hr><hr/><i><img><input><label><li><ol><option><p><pre><s><select><small><span><strike><strong><sub><sup><table><tablehead><tcpdf><td><textarea><th><thead><tr><tt><u><ul>');
		//replace some blank characters
		$html = preg_replace('/<pre/', '<xre', $html); // preserve pre tag
		$html = preg_replace('/<(table|tr|td|th|tcpdf|blockquote|dd|div|dl|dt|form|h1|h2|h3|h4|h5|h6|br|hr|li|ol|ul|p)([^\>]*)>[\n\r\t]+/', '<\\1\\2>', $html);
		$html = preg_replace('@(\r\n|\r)@', "\n", $html);
		$repTable = array("\t" => ' ', "\0" => ' ', "\x0B" => ' ', "\\" => "\\\\");
		$html = strtr($html, $repTable);
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</pre>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 6));
			while (preg_match("'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si", $html_b)) {
				// preserve newlines on <pre> tag
				$html_b = preg_replace("'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si", "<xre\\1>\\2<br />\\3</pre>", $html_b);
			}
			while (preg_match("'<xre([^\>]*)>(.*?)".$this->re_space['p']."(.*?)</pre>'".$this->re_space['m'], $html_b)) {
				// preserve spaces on <pre> tag
				$html_b = preg_replace("'<xre([^\>]*)>(.*?)".$this->re_space['p']."(.*?)</pre>'".$this->re_space['m'], "<xre\\1>\\2&nbsp;\\3</pre>", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 6);
			$offset = strlen($html_a.$html_b);
		}
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</textarea>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 11));
			while (preg_match("'<textarea([^\>]*)>(.*?)\n(.*?)</textarea>'si", $html_b)) {
				// preserve newlines on <textarea> tag
				$html_b = preg_replace("'<textarea([^\>]*)>(.*?)\n(.*?)</textarea>'si", "<textarea\\1>\\2<TBR>\\3</textarea>", $html_b);
				$html_b = preg_replace("'<textarea([^\>]*)>(.*?)[\"](.*?)</textarea>'si", "<textarea\\1>\\2''\\3</textarea>", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 11);
			$offset = strlen($html_a.$html_b);
		}
		$html = preg_replace('/([\s]*)<option/si', '<option', $html);
		$html = preg_replace('/<\/option>([\s]*)/si', '</option>', $html);
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</option>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 9));
			while (preg_match("'<option([^\>]*)>(.*?)</option>'si", $html_b)) {
				$html_b = preg_replace("'<option([\s]+)value=\"([^\"]*)\"([^\>]*)>(.*?)</option>'si", "\\2#!TaB!#\\4#!NwL!#", $html_b);
				$html_b = preg_replace("'<option([^\>]*)>(.*?)</option>'si", "\\2#!NwL!#", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 9);
			$offset = strlen($html_a.$html_b);
		}
		if (preg_match("'</select'si", $html)) {
			$html = preg_replace("'<select([^\>]*)>'si", "<select\\1 opt=\"", $html);
			$html = preg_replace("'#!NwL!#</select>'si", "\" />", $html);
		}
		$html = str_replace("\n", ' ', $html);
		// restore textarea newlines
		$html = str_replace('<TBR>', "\n", $html);
		// remove extra spaces from code
		$html = preg_replace('/[\s]+<\/(table|tr|ul|ol|dl)>/', '</\\1>', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<\/(td|th|li|dt|dd)>/'.$this->re_space['m'], '</\\1>', $html);
		$html = preg_replace('/[\s]+<(tr|td|th|li|dt|dd)/', '<\\1', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<(ul|ol|dl|br)/'.$this->re_space['m'], '<\\1', $html);
		$html = preg_replace('/<\/(table|tr|td|th|blockquote|dd|dt|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|ul|p)>[\s]+</', '</\\1><', $html);
		$html = preg_replace('/<\/(td|th)>/', '<marker style="font-size:0"/></\\1>', $html);
		$html = preg_replace('/<\/table>([\s]*)<marker style="font-size:0"\/>/', '</table>', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<img/'.$this->re_space['m'], chr(32).'<img', $html);
		$html = preg_replace('/<img([^\>]*)>[\s]+([^\<])/xi', '<img\\1>&nbsp;\\2', $html);
		$html = preg_replace('/<img([^\>]*)>/xi', '<img\\1><span><marker style="font-size:0"/></span>', $html);
		$html = preg_replace('/<xre/', '<pre', $html); // restore pre tag
		$html = preg_replace('/<textarea([^\>]*)>([^\<]*)<\/textarea>/xi', '<textarea\\1 value="\\2" />', $html);
		$html = preg_replace('/<li([^\>]*)><\/li>/', '<li\\1>&nbsp;</li>', $html);
		$html = preg_replace('/<li([^\>]*)>'.$this->re_space['p'].'*<img/'.$this->re_space['m'], '<li\\1><font size="1">&nbsp;</font><img', $html);
		$html = preg_replace('/<([^\>\/]*)>[\s]/', '<\\1>&nbsp;', $html); // preserve some spaces
		$html = preg_replace('/[\s]<\/([^\>]*)>/', '&nbsp;</\\1>', $html); // preserve some spaces
		$html = preg_replace('/<su([bp])/', '<zws/><su\\1', $html); // fix sub/sup alignment
		$html = preg_replace('/<\/su([bp])>/', '</su\\1><zws/>', $html); // fix sub/sup alignment
		$html = preg_replace('/'.$this->re_space['p'].'+/'.$this->re_space['m'], chr(32), $html); // replace multiple spaces with a single space
		// trim string
		$html = $this->stringTrim($html);
		// fix br tag after li
		$html = preg_replace('/<li><br([^\>]*)>/', '<li> <br\\1>', $html);
		// fix first image tag alignment
		$html = preg_replace('/^<img/', '<span style="font-size:0"><br /></span> <img', $html, 1);
		// pattern for generic tag
		$tagpattern = '/(<[^>]+>)/';
		// explodes the string
		$a = preg_split($tagpattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		// count elements
		$maxel = count($a);
		$elkey = 0;
		$key = 0;
		// create an array of elements
		$dom = array();
		$dom[$key] = array();
		// set inheritable properties fot the first void element
		// possible inheritable properties are: azimuth, border-collapse, border-spacing, caption-side, color, cursor, direction, empty-cells, font, font-family, font-stretch, font-size, font-size-adjust, font-style, font-variant, font-weight, letter-spacing, line-height, list-style, list-style-image, list-style-position, list-style-type, orphans, page, page-break-inside, quotes, speak, speak-header, text-align, text-indent, text-transform, volume, white-space, widows, word-spacing
		$dom[$key]['tag'] = false;
		$dom[$key]['block'] = false;
		$dom[$key]['value'] = '';
		$dom[$key]['parent'] = 0;
		$dom[$key]['hide'] = false;
		$dom[$key]['fontname'] = $this->FontFamily;
		$dom[$key]['fontstyle'] = $this->FontStyle;
		$dom[$key]['fontsize'] = $this->FontSizePt;
		$dom[$key]['font-stretch'] = $this->font_stretching;
		$dom[$key]['letter-spacing'] = $this->font_spacing;
		$dom[$key]['stroke'] = $this->textstrokewidth;
		$dom[$key]['fill'] = (($this->textrendermode % 2) == 0);
		$dom[$key]['clip'] = ($this->textrendermode > 3);
		$dom[$key]['line-height'] = $this->cell_height_ratio;
		$dom[$key]['bgcolor'] = false;
		$dom[$key]['fgcolor'] = $this->fgcolor; // color
		$dom[$key]['strokecolor'] = $this->strokecolor;
		$dom[$key]['align'] = '';
		$dom[$key]['listtype'] = '';
		$dom[$key]['text-indent'] = 0;
		$dom[$key]['border'] = array();
		$dom[$key]['dir'] = $this->rtl?'rtl':'ltr';
		$thead = false; // true when we are inside the THEAD tag
		++$key;
		$level = array();
		array_push($level, 0); // root
		while ($elkey < $maxel) {
			$dom[$key] = array();
			$element = $a[$elkey];
			$dom[$key]['elkey'] = $elkey;
			if (preg_match($tagpattern, $element)) {
				// html tag
				$element = substr($element, 1, -1);
				// get tag name
				preg_match('/[\/]?([a-zA-Z0-9]*)/', $element, $tag);
				$tagname = strtolower($tag[1]);
				// check if we are inside a table header
				if ($tagname == 'thead') {
					if ($element{0} == '/') {
						$thead = false;
					} else {
						$thead = true;
					}
					++$elkey;
					continue;
				}
				$dom[$key]['tag'] = true;
				$dom[$key]['value'] = $tagname;
				if (in_array($dom[$key]['value'], $blocktags)) {
					$dom[$key]['block'] = true;
				} else {
					$dom[$key]['block'] = false;
				}
				if ($element{0} == '/') {
					// *** closing html tag
					$dom[$key]['opening'] = false;
					$dom[$key]['parent'] = end($level);
					array_pop($level);
					$dom[$key]['hide'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['hide'];
					$dom[$key]['fontname'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontname'];
					$dom[$key]['fontstyle'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontstyle'];
					$dom[$key]['fontsize'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontsize'];
					$dom[$key]['font-stretch'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['font-stretch'];
					$dom[$key]['letter-spacing'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['letter-spacing'];
					$dom[$key]['stroke'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['stroke'];
					$dom[$key]['fill'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fill'];
					$dom[$key]['clip'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['clip'];
					$dom[$key]['line-height'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['line-height'];
					$dom[$key]['bgcolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['bgcolor'];
					$dom[$key]['fgcolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fgcolor'];
					$dom[$key]['strokecolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['strokecolor'];
					$dom[$key]['align'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['align'];
					$dom[$key]['dir'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['dir'];
					if (isset($dom[($dom[($dom[$key]['parent'])]['parent'])]['listtype'])) {
						$dom[$key]['listtype'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['listtype'];
					}
					// set the number of columns in table tag
					if (($dom[$key]['value'] == 'tr') AND (!isset($dom[($dom[($dom[$key]['parent'])]['parent'])]['cols']))) {
						$dom[($dom[($dom[$key]['parent'])]['parent'])]['cols'] = $dom[($dom[$key]['parent'])]['cols'];
					}
					if (($dom[$key]['value'] == 'td') OR ($dom[$key]['value'] == 'th')) {
						$dom[($dom[$key]['parent'])]['content'] = $csstagarray;
						for ($i = ($dom[$key]['parent'] + 1); $i < $key; ++$i) {
							$dom[($dom[$key]['parent'])]['content'] .= $a[$dom[$i]['elkey']];
						}
						$key = $i;
						// mark nested tables
						$dom[($dom[$key]['parent'])]['content'] = str_replace('<table', '<table nested="true"', $dom[($dom[$key]['parent'])]['content']);
						// remove thead sections from nested tables
						$dom[($dom[$key]['parent'])]['content'] = str_replace('<thead>', '', $dom[($dom[$key]['parent'])]['content']);
						$dom[($dom[$key]['parent'])]['content'] = str_replace('</thead>', '', $dom[($dom[$key]['parent'])]['content']);
					}
					// store header rows on a new table
					if (($dom[$key]['value'] == 'tr') AND ($dom[($dom[$key]['parent'])]['thead'] === true)) {
						if (TCPDF_STATIC::empty_string($dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'])) {
							$dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'] = $csstagarray.$a[$dom[($dom[($dom[$key]['parent'])]['parent'])]['elkey']];
						}
						for ($i = $dom[$key]['parent']; $i <= $key; ++$i) {
							$dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'] .= $a[$dom[$i]['elkey']];
						}
						if (!isset($dom[($dom[$key]['parent'])]['attribute'])) {
							$dom[($dom[$key]['parent'])]['attribute'] = array();
						}
						// header elements must be always contained in a single page
						$dom[($dom[$key]['parent'])]['attribute']['nobr'] = 'true';
					}
					if (($dom[$key]['value'] == 'table') AND (!TCPDF_STATIC::empty_string($dom[($dom[$key]['parent'])]['thead']))) {
						// remove the nobr attributes from the table header
						$dom[($dom[$key]['parent'])]['thead'] = str_replace(' nobr="true"', '', $dom[($dom[$key]['parent'])]['thead']);
						$dom[($dom[$key]['parent'])]['thead'] .= '</tablehead>';
					}
				} else {
					// *** opening or self-closing html tag
					$dom[$key]['opening'] = true;
					$dom[$key]['parent'] = end($level);
					if ((substr($element, -1, 1) == '/') OR (in_array($dom[$key]['value'], $selfclosingtags))) {
						// self-closing tag
						$dom[$key]['self'] = true;
					} else {
						// opening tag
						array_push($level, $key);
						$dom[$key]['self'] = false;
					}
					// copy some values from parent
					$parentkey = 0;
					if ($key > 0) {
						$parentkey = $dom[$key]['parent'];
						$dom[$key]['hide'] = $dom[$parentkey]['hide'];
						$dom[$key]['fontname'] = $dom[$parentkey]['fontname'];
						$dom[$key]['fontstyle'] = $dom[$parentkey]['fontstyle'];
						$dom[$key]['fontsize'] = $dom[$parentkey]['fontsize'];
						$dom[$key]['font-stretch'] = $dom[$parentkey]['font-stretch'];
						$dom[$key]['letter-spacing'] = $dom[$parentkey]['letter-spacing'];
						$dom[$key]['stroke'] = $dom[$parentkey]['stroke'];
						$dom[$key]['fill'] = $dom[$parentkey]['fill'];
						$dom[$key]['clip'] = $dom[$parentkey]['clip'];
						$dom[$key]['line-height'] = $dom[$parentkey]['line-height'];
						$dom[$key]['bgcolor'] = $dom[$parentkey]['bgcolor'];
						$dom[$key]['fgcolor'] = $dom[$parentkey]['fgcolor'];
						$dom[$key]['strokecolor'] = $dom[$parentkey]['strokecolor'];
						$dom[$key]['align'] = $dom[$parentkey]['align'];
						$dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
						$dom[$key]['text-indent'] = $dom[$parentkey]['text-indent'];
						$dom[$key]['border'] = array();
						$dom[$key]['dir'] = $dom[$parentkey]['dir'];
					}
					// get attributes
					preg_match_all('/([^=\s]*)[\s]*=[\s]*"([^"]*)"/', $element, $attr_array, PREG_PATTERN_ORDER);
					$dom[$key]['attribute'] = array(); // reset attribute array
					while (list($id, $name) = each($attr_array[1])) {
						$dom[$key]['attribute'][strtolower($name)] = $attr_array[2][$id];
					}
					if (!empty($css)) {
						// merge CSS style to current style
						list($dom[$key]['csssel'], $dom[$key]['cssdata']) = TCPDF_STATIC::getCSSdataArray($dom, $key, $css);
						$dom[$key]['attribute']['style'] = TCPDF_STATIC::getTagStyleFromCSSarray($dom[$key]['cssdata']);
					}
					// split style attributes
					if (isset($dom[$key]['attribute']['style']) AND !empty($dom[$key]['attribute']['style'])) {
						// get style attributes
						preg_match_all('/([^;:\s]*):([^;]*)/', $dom[$key]['attribute']['style'], $style_array, PREG_PATTERN_ORDER);
						$dom[$key]['style'] = array(); // reset style attribute array
						while (list($id, $name) = each($style_array[1])) {
							// in case of duplicate attribute the last replace the previous
							$dom[$key]['style'][strtolower($name)] = trim($style_array[2][$id]);
						}
						// --- get some style attributes ---
						// text direction
						if (isset($dom[$key]['style']['direction'])) {
							$dom[$key]['dir'] = $dom[$key]['style']['direction'];
						}
						// display
						if (isset($dom[$key]['style']['display'])) {
							$dom[$key]['hide'] = (trim(strtolower($dom[$key]['style']['display'])) == 'none');
						}
						// font family
						if (isset($dom[$key]['style']['font-family'])) {
							$dom[$key]['fontname'] = $this->getFontFamilyName($dom[$key]['style']['font-family']);
						}
						// list-style-type
						if (isset($dom[$key]['style']['list-style-type'])) {
							$dom[$key]['listtype'] = trim(strtolower($dom[$key]['style']['list-style-type']));
							if ($dom[$key]['listtype'] == 'inherit') {
								$dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
							}
						}
						// text-indent
						if (isset($dom[$key]['style']['text-indent'])) {
							$dom[$key]['text-indent'] = $this->getHTMLUnitToUnits($dom[$key]['style']['text-indent']);
							if ($dom[$key]['text-indent'] == 'inherit') {
								$dom[$key]['text-indent'] = $dom[$parentkey]['text-indent'];
							}
						}
						// font size
						if (isset($dom[$key]['style']['font-size'])) {
							$fsize = trim($dom[$key]['style']['font-size']);
							$dom[$key]['fontsize'] = $this->getHTMLFontUnits($fsize, $dom[0]['fontsize'], $dom[$parentkey]['fontsize'], 'pt');
						}
						// font-stretch
						if (isset($dom[$key]['style']['font-stretch'])) {
							$dom[$key]['font-stretch'] = $this->getCSSFontStretching($dom[$key]['style']['font-stretch'], $dom[$parentkey]['font-stretch']);
						}
						// letter-spacing
						if (isset($dom[$key]['style']['letter-spacing'])) {
							$dom[$key]['letter-spacing'] = $this->getCSSFontSpacing($dom[$key]['style']['letter-spacing'], $dom[$parentkey]['letter-spacing']);
						}
						// line-height
						if (isset($dom[$key]['style']['line-height'])) {
							$lineheight = trim($dom[$key]['style']['line-height']);
							switch ($lineheight) {
								// A normal line height. This is default
								case 'normal': {
									$dom[$key]['line-height'] = $dom[0]['line-height'];
									break;
								}
								default: {
									if (is_numeric($lineheight)) {
										$lineheight = $lineheight * 100;
									}
									$dom[$key]['line-height'] = $this->getHTMLUnitToUnits($lineheight, 1, '%', true);
								}
							}
						}
						// font style
						if (isset($dom[$key]['style']['font-weight'])) {
							if (strtolower($dom[$key]['style']['font-weight']{0}) == 'n') {
								if (strpos($dom[$key]['fontstyle'], 'B') !== false) {
									$dom[$key]['fontstyle'] = str_replace('B', '', $dom[$key]['fontstyle']);
								}
							} elseif (strtolower($dom[$key]['style']['font-weight']{0}) == 'b') {
								$dom[$key]['fontstyle'] .= 'B';
							}
						}
						if (isset($dom[$key]['style']['font-style']) AND (strtolower($dom[$key]['style']['font-style']{0}) == 'i')) {
							$dom[$key]['fontstyle'] .= 'I';
						}
						// font color
						if (isset($dom[$key]['style']['color']) AND (!TCPDF_STATIC::empty_string($dom[$key]['style']['color']))) {
							$dom[$key]['fgcolor'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['style']['color'], $this->spot_colors);
						} elseif ($dom[$key]['value'] == 'a') {
							$dom[$key]['fgcolor'] = $this->htmlLinkColorArray;
						}
						// background color
						if (isset($dom[$key]['style']['background-color']) AND (!TCPDF_STATIC::empty_string($dom[$key]['style']['background-color']))) {
							$dom[$key]['bgcolor'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['style']['background-color'], $this->spot_colors);
						}
						// text-decoration
						if (isset($dom[$key]['style']['text-decoration'])) {
							$decors = explode(' ', strtolower($dom[$key]['style']['text-decoration']));
							foreach ($decors as $dec) {
								$dec = trim($dec);
								if (!TCPDF_STATIC::empty_string($dec)) {
									if ($dec{0} == 'u') {
										// underline
										$dom[$key]['fontstyle'] .= 'U';
									} elseif ($dec{0} == 'l') {
										// line-through
										$dom[$key]['fontstyle'] .= 'D';
									} elseif ($dec{0} == 'o') {
										// overline
										$dom[$key]['fontstyle'] .= 'O';
									}
								}
							}
						} elseif ($dom[$key]['value'] == 'a') {
							$dom[$key]['fontstyle'] = $this->htmlLinkFontStyle;
						}
						// check for width attribute
						if (isset($dom[$key]['style']['width'])) {
							$dom[$key]['width'] = $dom[$key]['style']['width'];
						}
						// check for height attribute
						if (isset($dom[$key]['style']['height'])) {
							$dom[$key]['height'] = $dom[$key]['style']['height'];
						}
						// check for text alignment
						if (isset($dom[$key]['style']['text-align'])) {
							$dom[$key]['align'] = strtoupper($dom[$key]['style']['text-align']{0});
						}
						// check for CSS border properties
						if (isset($dom[$key]['style']['border'])) {
							$borderstyle = $this->getCSSBorderStyle($dom[$key]['style']['border']);
							if (!empty($borderstyle)) {
								$dom[$key]['border']['LTRB'] = $borderstyle;
							}
						}
						if (isset($dom[$key]['style']['border-color'])) {
							$brd_colors = preg_split('/[\s]+/', trim($dom[$key]['style']['border-color']));
							if (isset($brd_colors[3])) {
								$dom[$key]['border']['L']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[3], $this->spot_colors);
							}
							if (isset($brd_colors[1])) {
								$dom[$key]['border']['R']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[1], $this->spot_colors);
							}
							if (isset($brd_colors[0])) {
								$dom[$key]['border']['T']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[0], $this->spot_colors);
							}
							if (isset($brd_colors[2])) {
								$dom[$key]['border']['B']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[2], $this->spot_colors);
							}
						}
						if (isset($dom[$key]['style']['border-width'])) {
							$brd_widths = preg_split('/[\s]+/', trim($dom[$key]['style']['border-width']));
							if (isset($brd_widths[3])) {
								$dom[$key]['border']['L']['width'] = $this->getCSSBorderWidth($brd_widths[3]);
							}
							if (isset($brd_widths[1])) {
								$dom[$key]['border']['R']['width'] = $this->getCSSBorderWidth($brd_widths[1]);
							}
							if (isset($brd_widths[0])) {
								$dom[$key]['border']['T']['width'] = $this->getCSSBorderWidth($brd_widths[0]);
							}
							if (isset($brd_widths[2])) {
								$dom[$key]['border']['B']['width'] = $this->getCSSBorderWidth($brd_widths[2]);
							}
						}
						if (isset($dom[$key]['style']['border-style'])) {
							$brd_styles = preg_split('/[\s]+/', trim($dom[$key]['style']['border-style']));
							if (isset($brd_styles[3]) AND ($brd_styles[3]!='none')) {
								$dom[$key]['border']['L']['cap'] = 'square';
								$dom[$key]['border']['L']['join'] = 'miter';
								$dom[$key]['border']['L']['dash'] = $this->getCSSBorderDashStyle($brd_styles[3]);
								if ($dom[$key]['border']['L']['dash'] < 0) {
									$dom[$key]['border']['L'] = array();
								}
							}
							if (isset($brd_styles[1])) {
								$dom[$key]['border']['R']['cap'] = 'square';
								$dom[$key]['border']['R']['join'] = 'miter';
								$dom[$key]['border']['R']['dash'] = $this->getCSSBorderDashStyle($brd_styles[1]);
								if ($dom[$key]['border']['R']['dash'] < 0) {
									$dom[$key]['border']['R'] = array();
								}
							}
							if (isset($brd_styles[0])) {
								$dom[$key]['border']['T']['cap'] = 'square';
								$dom[$key]['border']['T']['join'] = 'miter';
								$dom[$key]['border']['T']['dash'] = $this->getCSSBorderDashStyle($brd_styles[0]);
								if ($dom[$key]['border']['T']['dash'] < 0) {
									$dom[$key]['border']['T'] = array();
								}
							}
							if (isset($brd_styles[2])) {
								$dom[$key]['border']['B']['cap'] = 'square';
								$dom[$key]['border']['B']['join'] = 'miter';
								$dom[$key]['border']['B']['dash'] = $this->getCSSBorderDashStyle($brd_styles[2]);
								if ($dom[$key]['border']['B']['dash'] < 0) {
									$dom[$key]['border']['B'] = array();
								}
							}
						}
						$cellside = array('L' => 'left', 'R' => 'right', 'T' => 'top', 'B' => 'bottom');
						foreach ($cellside as $bsk => $bsv) {
							if (isset($dom[$key]['style']['border-'.$bsv])) {
								$borderstyle = $this->getCSSBorderStyle($dom[$key]['style']['border-'.$bsv]);
								if (!empty($borderstyle)) {
									$dom[$key]['border'][$bsk] = $borderstyle;
								}
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-color'])) {
								$dom[$key]['border'][$bsk]['color'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['style']['border-'.$bsv.'-color'], $this->spot_colors);
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-width'])) {
								$dom[$key]['border'][$bsk]['width'] = $this->getCSSBorderWidth($dom[$key]['style']['border-'.$bsv.'-width']);
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-style'])) {
								$dom[$key]['border'][$bsk]['dash'] = $this->getCSSBorderDashStyle($dom[$key]['style']['border-'.$bsv.'-style']);
								if ($dom[$key]['border'][$bsk]['dash'] < 0) {
									$dom[$key]['border'][$bsk] = array();
								}
							}
						}
						// check for CSS padding properties
						if (isset($dom[$key]['style']['padding'])) {
							$dom[$key]['padding'] = $this->getCSSPadding($dom[$key]['style']['padding']);
						} else {
							$dom[$key]['padding'] = $this->cell_padding;
						}
						foreach ($cellside as $psk => $psv) {
							if (isset($dom[$key]['style']['padding-'.$psv])) {
								$dom[$key]['padding'][$psk] = $this->getHTMLUnitToUnits($dom[$key]['style']['padding-'.$psv], 0, 'px', false);
							}
						}
						// check for CSS margin properties
						if (isset($dom[$key]['style']['margin'])) {
							$dom[$key]['margin'] = $this->getCSSMargin($dom[$key]['style']['margin']);
						} else {
							$dom[$key]['margin'] = $this->cell_margin;
						}
						foreach ($cellside as $psk => $psv) {
							if (isset($dom[$key]['style']['margin-'.$psv])) {
								$dom[$key]['margin'][$psk] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $dom[$key]['style']['margin-'.$psv]), 0, 'px', false);
							}
						}
						// check for CSS border-spacing properties
						if (isset($dom[$key]['style']['border-spacing'])) {
							$dom[$key]['border-spacing'] = $this->getCSSBorderMargin($dom[$key]['style']['border-spacing']);
						}
						// page-break-inside
						if (isset($dom[$key]['style']['page-break-inside']) AND ($dom[$key]['style']['page-break-inside'] == 'avoid')) {
							$dom[$key]['attribute']['nobr'] = 'true';
						}
						// page-break-before
						if (isset($dom[$key]['style']['page-break-before'])) {
							if ($dom[$key]['style']['page-break-before'] == 'always') {
								$dom[$key]['attribute']['pagebreak'] = 'true';
							} elseif ($dom[$key]['style']['page-break-before'] == 'left') {
								$dom[$key]['attribute']['pagebreak'] = 'left';
							} elseif ($dom[$key]['style']['page-break-before'] == 'right') {
								$dom[$key]['attribute']['pagebreak'] = 'right';
							}
						}
						// page-break-after
						if (isset($dom[$key]['style']['page-break-after'])) {
							if ($dom[$key]['style']['page-break-after'] == 'always') {
								$dom[$key]['attribute']['pagebreakafter'] = 'true';
							} elseif ($dom[$key]['style']['page-break-after'] == 'left') {
								$dom[$key]['attribute']['pagebreakafter'] = 'left';
							} elseif ($dom[$key]['style']['page-break-after'] == 'right') {
								$dom[$key]['attribute']['pagebreakafter'] = 'right';
							}
						}
					}
					if (isset($dom[$key]['attribute']['display'])) {
						$dom[$key]['hide'] = (trim(strtolower($dom[$key]['attribute']['display'])) == 'none');
					}
					if (isset($dom[$key]['attribute']['border']) AND ($dom[$key]['attribute']['border'] != 0)) {
						$borderstyle = $this->getCSSBorderStyle($dom[$key]['attribute']['border'].' solid black');
						if (!empty($borderstyle)) {
							$dom[$key]['border']['LTRB'] = $borderstyle;
						}
					}
					// check for font tag
					if ($dom[$key]['value'] == 'font') {
						// font family
						if (isset($dom[$key]['attribute']['face'])) {
							$dom[$key]['fontname'] = $this->getFontFamilyName($dom[$key]['attribute']['face']);
						}
						// font size
						if (isset($dom[$key]['attribute']['size'])) {
							if ($key > 0) {
								if ($dom[$key]['attribute']['size']{0} == '+') {
									$dom[$key]['fontsize'] = $dom[($dom[$key]['parent'])]['fontsize'] + intval(substr($dom[$key]['attribute']['size'], 1));
								} elseif ($dom[$key]['attribute']['size']{0} == '-') {
									$dom[$key]['fontsize'] = $dom[($dom[$key]['parent'])]['fontsize'] - intval(substr($dom[$key]['attribute']['size'], 1));
								} else {
									$dom[$key]['fontsize'] = intval($dom[$key]['attribute']['size']);
								}
							} else {
								$dom[$key]['fontsize'] = intval($dom[$key]['attribute']['size']);
							}
						}
					}
					// force natural alignment for lists
					if ((($dom[$key]['value'] == 'ul') OR ($dom[$key]['value'] == 'ol') OR ($dom[$key]['value'] == 'dl'))
						AND (!isset($dom[$key]['align']) OR TCPDF_STATIC::empty_string($dom[$key]['align']) OR ($dom[$key]['align'] != 'J'))) {
						if ($this->rtl) {
							$dom[$key]['align'] = 'R';
						} else {
							$dom[$key]['align'] = 'L';
						}
					}
					if (($dom[$key]['value'] == 'small') OR ($dom[$key]['value'] == 'sup') OR ($dom[$key]['value'] == 'sub')) {
						if (!isset($dom[$key]['attribute']['size']) AND !isset($dom[$key]['style']['font-size'])) {
							$dom[$key]['fontsize'] = $dom[$key]['fontsize'] * K_SMALL_RATIO;
						}
					}
					if (($dom[$key]['value'] == 'strong') OR ($dom[$key]['value'] == 'b')) {
						$dom[$key]['fontstyle'] .= 'B';
					}
					if (($dom[$key]['value'] == 'em') OR ($dom[$key]['value'] == 'i')) {
						$dom[$key]['fontstyle'] .= 'I';
					}
					if ($dom[$key]['value'] == 'u') {
						$dom[$key]['fontstyle'] .= 'U';
					}
					if (($dom[$key]['value'] == 'del') OR ($dom[$key]['value'] == 's') OR ($dom[$key]['value'] == 'strike')) {
						$dom[$key]['fontstyle'] .= 'D';
					}
					if (!isset($dom[$key]['style']['text-decoration']) AND ($dom[$key]['value'] == 'a')) {
						$dom[$key]['fontstyle'] = $this->htmlLinkFontStyle;
					}
					if (($dom[$key]['value'] == 'pre') OR ($dom[$key]['value'] == 'tt')) {
						$dom[$key]['fontname'] = $this->default_monospaced_font;
					}
					if (($dom[$key]['value']{0} == 'h') AND (intval($dom[$key]['value']{1}) > 0) AND (intval($dom[$key]['value']{1}) < 7)) {
						// headings h1, h2, h3, h4, h5, h6
						if (!isset($dom[$key]['attribute']['size']) AND !isset($dom[$key]['style']['font-size'])) {
							$headsize = (4 - intval($dom[$key]['value']{1})) * 2;
							$dom[$key]['fontsize'] = $dom[0]['fontsize'] + $headsize;
						}
						if (!isset($dom[$key]['style']['font-weight'])) {
							$dom[$key]['fontstyle'] .= 'B';
						}
					}
					if (($dom[$key]['value'] == 'table')) {
						$dom[$key]['rows'] = 0; // number of rows
						$dom[$key]['trids'] = array(); // IDs of TR elements
						$dom[$key]['thead'] = ''; // table header rows
					}
					if (($dom[$key]['value'] == 'tr')) {
						$dom[$key]['cols'] = 0;
						if ($thead) {
							$dom[$key]['thead'] = true;
							// rows on thead block are printed as a separate table
						} else {
							$dom[$key]['thead'] = false;
							// store the number of rows on table element
							++$dom[($dom[$key]['parent'])]['rows'];
							// store the TR elements IDs on table element
							array_push($dom[($dom[$key]['parent'])]['trids'], $key);
						}
					}
					if (($dom[$key]['value'] == 'th') OR ($dom[$key]['value'] == 'td')) {
						if (isset($dom[$key]['attribute']['colspan'])) {
							$colspan = intval($dom[$key]['attribute']['colspan']);
						} else {
							$colspan = 1;
						}
						$dom[$key]['attribute']['colspan'] = $colspan;
						$dom[($dom[$key]['parent'])]['cols'] += $colspan;
					}
					// text direction
					if (isset($dom[$key]['attribute']['dir'])) {
						$dom[$key]['dir'] = $dom[$key]['attribute']['dir'];
					}
					// set foreground color attribute
					if (isset($dom[$key]['attribute']['color']) AND (!TCPDF_STATIC::empty_string($dom[$key]['attribute']['color']))) {
						$dom[$key]['fgcolor'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['attribute']['color'], $this->spot_colors);
					} elseif (!isset($dom[$key]['style']['color']) AND ($dom[$key]['value'] == 'a')) {
						$dom[$key]['fgcolor'] = $this->htmlLinkColorArray;
					}
					// set background color attribute
					if (isset($dom[$key]['attribute']['bgcolor']) AND (!TCPDF_STATIC::empty_string($dom[$key]['attribute']['bgcolor']))) {
						$dom[$key]['bgcolor'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['attribute']['bgcolor'], $this->spot_colors);
					}
					// set stroke color attribute
					if (isset($dom[$key]['attribute']['strokecolor']) AND (!TCPDF_STATIC::empty_string($dom[$key]['attribute']['strokecolor']))) {
						$dom[$key]['strokecolor'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['attribute']['strokecolor'], $this->spot_colors);
					}
					// check for width attribute
					if (isset($dom[$key]['attribute']['width'])) {
						$dom[$key]['width'] = $dom[$key]['attribute']['width'];
					}
					// check for height attribute
					if (isset($dom[$key]['attribute']['height'])) {
						$dom[$key]['height'] = $dom[$key]['attribute']['height'];
					}
					// check for text alignment
					if (isset($dom[$key]['attribute']['align']) AND (!TCPDF_STATIC::empty_string($dom[$key]['attribute']['align'])) AND ($dom[$key]['value'] !== 'img')) {
						$dom[$key]['align'] = strtoupper($dom[$key]['attribute']['align']{0});
					}
					// check for text rendering mode (the following attributes do not exist in HTML)
					if (isset($dom[$key]['attribute']['stroke'])) {
						// font stroke width
						$dom[$key]['stroke'] = $this->getHTMLUnitToUnits($dom[$key]['attribute']['stroke'], $dom[$key]['fontsize'], 'pt', true);
					}
					if (isset($dom[$key]['attribute']['fill'])) {
						// font fill
						if ($dom[$key]['attribute']['fill'] == 'true') {
							$dom[$key]['fill'] = true;
						} else {
							$dom[$key]['fill'] = false;
						}
					}
					if (isset($dom[$key]['attribute']['clip'])) {
						// clipping mode
						if ($dom[$key]['attribute']['clip'] == 'true') {
							$dom[$key]['clip'] = true;
						} else {
							$dom[$key]['clip'] = false;
						}
					}
				} // end opening tag
			} else {
				// text
				$dom[$key]['tag'] = false;
				$dom[$key]['block'] = false;
				//$element = str_replace('&nbsp;', TCPDF_FONTS::unichr(160, $this->isunicode), $element);
				$dom[$key]['value'] = stripslashes($this->unhtmlentities($element));
				$dom[$key]['parent'] = end($level);
				$dom[$key]['dir'] = $dom[$dom[$key]['parent']]['dir'];
			}
			++$elkey;
			++$key;
		}
		return $dom;
	}

	/**
	 * Returns the string used to find spaces
	 * @return string
	 * @protected
	 * @author Nicola Asuni
	 * @since 4.8.024 (2010-01-15)
	 */
	protected function getSpaceString() {
		$spacestr = chr(32);
		if ($this->isUnicodeFont()) {
			$spacestr = chr(0).chr(32);
		}
		return $spacestr;
	}

	/**
	 * Serialize an array of parameters to be used with TCPDF tag in HTML code.
	 * @param $pararray (array) parameters array
	 * @return sting containing serialized data
	 * @since 4.9.006 (2010-04-02)
	 * @public
	 * @deprecated
	 */
	public function serializeTCPDFtagParameters($pararray) {
		return TCPDF_STATIC::serializeTCPDFtagParameters($pararray);
	}

	/**
	 * Prints a cell (rectangular area) with optional borders, background color and html text string.
	 * The upper-left corner of the cell corresponds to the current position. After the call, the current position moves to the right or to the next line.<br />
	 * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
	 * IMPORTANT: The HTML must be well formatted - try to clean-up it using an application like HTML-Tidy before submitting.
	 * Supported tags are: a, b, blockquote, br, dd, del, div, dl, dt, em, font, h1, h2, h3, h4, h5, h6, hr, i, img, li, ol, p, pre, small, span, strong, sub, sup, table, tcpdf, td, th, thead, tr, tt, u, ul
	 * NOTE: all the HTML attributes must be enclosed in double-quote.
	 * @param $w (float) Cell width. If 0, the cell extends up to the right margin.
	 * @param $h (float) Cell minimum height. The cell extends automatically if needed.
	 * @param $x (float) upper-left corner X coordinate
	 * @param $y (float) upper-left corner Y coordinate
	 * @param $html (string) html text to print. Default value: empty string.
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $ln (int) Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right (or left for RTL language)</li><li>1: to the beginning of the next line</li><li>2: below</li></ul>
Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
	 * @param $fill (boolean) Indicates if the cell background must be painted (true) or transparent (false).
	 * @param $reseth (boolean) if true reset the last cell height (default true).
	 * @param $align (string) Allows to center or align the text. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $autopadding (boolean) if true, uses internal padding and automatically adjust it to account for line width.
	 * @see Multicell(), writeHTML()
	 * @public
	 */
	public function writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=false, $reseth=true, $align='', $autopadding=true) {
		return $this->MultiCell($w, $h, $html, $border, $align, $fill, $ln, $x, $y, $reseth, 0, true, $autopadding, 0, 'T', false);
	}

	/**
	 * Allows to preserve some HTML formatting (limited support).<br />
	 * IMPORTANT: The HTML must be well formatted - try to clean-up it using an application like HTML-Tidy before submitting.
	 * Supported tags are: a, b, blockquote, br, dd, del, div, dl, dt, em, font, h1, h2, h3, h4, h5, h6, hr, i, img, li, ol, p, pre, small, span, strong, sub, sup, table, tcpdf, td, th, thead, tr, tt, u, ul
	 * NOTE: all the HTML attributes must be enclosed in double-quote.
	 * @param $html (string) text to display
	 * @param $ln (boolean) if true add a new line after text (default = true)
	 * @param $fill (boolean) Indicates if the background must be painted (true) or transparent (false).
	 * @param $reseth (boolean) if true reset the last cell height (default false).
	 * @param $cell (boolean) if true add the current left (or right for RTL) padding to each Write (default false).
	 * @param $align (string) Allows to center or align the text. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @public
	 */
	public function writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='') {
		$gvars = $this->getGraphicVars();
		// store current values
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		$prevPage = $this->page;
		$prevlMargin = $this->lMargin;
		$prevrMargin = $this->rMargin;
		$curfontname = $this->FontFamily;
		$curfontstyle = $this->FontStyle;
		$curfontsize = $this->FontSizePt;
		$curfontascent = $this->getFontAscent($curfontname, $curfontstyle, $curfontsize);
		$curfontdescent = $this->getFontDescent($curfontname, $curfontstyle, $curfontsize);
		$curfontstretcing = $this->font_stretching;
		$curfonttracking = $this->font_spacing;
		$this->newline = true;
		$newline = true;
		$startlinepage = $this->page;
		$minstartliney = $this->y;
		$maxbottomliney = 0;
		$startlinex = $this->x;
		$startliney = $this->y;
		$yshift = 0;
		$loop = 0;
		$curpos = 0;
		$this_method_vars = array();
		$undo = false;
		$fontaligned = false;
		$reverse_dir = false; // true when the text direction is reversed
		$this->premode = false;
		if ($this->inxobj) {
			// we are inside an XObject template
			$pask = count($this->xobjects[$this->xobjid]['annotations']);
		} elseif (isset($this->PageAnnots[$this->page])) {
			$pask = count($this->PageAnnots[$this->page]);
		} else {
			$pask = 0;
		}
		if ($this->inxobj) {
			// we are inside an XObject template
			$startlinepos = strlen($this->xobjects[$this->xobjid]['outdata']);
		} elseif (!$this->InFooter) {
			if (isset($this->footerlen[$this->page])) {
				$this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
			} else {
				$this->footerpos[$this->page] = $this->pagelen[$this->page];
			}
			$startlinepos = $this->footerpos[$this->page];
		} else {
			// we are inside the footer
			$startlinepos = $this->pagelen[$this->page];
		}
		$lalign = $align;
		$plalign = $align;
		if ($this->rtl) {
			$w = $this->x - $this->lMargin;
		} else {
			$w = $this->w - $this->rMargin - $this->x;
		}
		$w -= ($this->cell_padding['L'] + $this->cell_padding['R']);
		if ($cell) {
			if ($this->rtl) {
				$this->x -= $this->cell_padding['R'];
				$this->lMargin += $this->cell_padding['R'];
			} else {
				$this->x += $this->cell_padding['L'];
				$this->rMargin += $this->cell_padding['L'];
			}
		}
		if ($this->customlistindent >= 0) {
			$this->listindent = $this->customlistindent;
		} else {
			$this->listindent = $this->GetStringWidth('000000');
		}
		$this->listindentlevel = 0;
		// save previous states
		$prev_cell_height_ratio = $this->cell_height_ratio;
		$prev_listnum = $this->listnum;
		$prev_listordered = $this->listordered;
		$prev_listcount = $this->listcount;
		$prev_lispacer = $this->lispacer;
		$this->listnum = 0;
		$this->listordered = array();
		$this->listcount = array();
		$this->lispacer = '';
		if ((TCPDF_STATIC::empty_string($this->lasth)) OR ($reseth)) {
			// reset row height
			$this->resetLastH();
		}
		$dom = $this->getHtmlDomArray($html);
		$maxel = count($dom);
		$key = 0;
		while ($key < $maxel) {
			if ($dom[$key]['tag'] AND $dom[$key]['opening'] AND $dom[$key]['hide']) {
				// store the node key
				$hidden_node_key = $key;
				if ($dom[$key]['self']) {
					// skip just this self-closing tag
					++$key;
				} else {
					// skip this and all children tags
					while (($key < $maxel) AND (!$dom[$key]['tag'] OR $dom[$key]['opening'] OR ($dom[$key]['parent'] != $hidden_node_key))) {
						// skip hidden objects
						++$key;
					}
					++$key;
				}
			}
			if ($dom[$key]['tag'] AND isset($dom[$key]['attribute']['pagebreak'])) {
				// check for pagebreak
				if (($dom[$key]['attribute']['pagebreak'] == 'true') OR ($dom[$key]['attribute']['pagebreak'] == 'left') OR ($dom[$key]['attribute']['pagebreak'] == 'right')) {
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$this->checkPageBreak($this->PageBreakTrigger + 1);
					$this->htmlvspace = ($this->PageBreakTrigger + 1);
				}
				if ((($dom[$key]['attribute']['pagebreak'] == 'left') AND (((!$this->rtl) AND (($this->page % 2) == 0)) OR (($this->rtl) AND (($this->page % 2) != 0))))
					OR (($dom[$key]['attribute']['pagebreak'] == 'right') AND (((!$this->rtl) AND (($this->page % 2) != 0)) OR (($this->rtl) AND (($this->page % 2) == 0))))) {
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$this->checkPageBreak($this->PageBreakTrigger + 1);
					$this->htmlvspace = ($this->PageBreakTrigger + 1);
				}
			}
			if ($dom[$key]['tag'] AND $dom[$key]['opening'] AND isset($dom[$key]['attribute']['nobr']) AND ($dom[$key]['attribute']['nobr'] == 'true')) {
				if (isset($dom[($dom[$key]['parent'])]['attribute']['nobr']) AND ($dom[($dom[$key]['parent'])]['attribute']['nobr'] == 'true')) {
					$dom[$key]['attribute']['nobr'] = false;
				} else {
					// store current object
					$this->startTransaction();
					// save this method vars
					$this_method_vars['html'] = $html;
					$this_method_vars['ln'] = $ln;
					$this_method_vars['fill'] = $fill;
					$this_method_vars['reseth'] = $reseth;
					$this_method_vars['cell'] = $cell;
					$this_method_vars['align'] = $align;
					$this_method_vars['gvars'] = $gvars;
					$this_method_vars['prevPage'] = $prevPage;
					$this_method_vars['prev_cell_margin'] = $prev_cell_margin;
					$this_method_vars['prev_cell_padding'] = $prev_cell_padding;
					$this_method_vars['prevlMargin'] = $prevlMargin;
					$this_method_vars['prevrMargin'] = $prevrMargin;
					$this_method_vars['curfontname'] = $curfontname;
					$this_method_vars['curfontstyle'] = $curfontstyle;
					$this_method_vars['curfontsize'] = $curfontsize;
					$this_method_vars['curfontascent'] = $curfontascent;
					$this_method_vars['curfontdescent'] = $curfontdescent;
					$this_method_vars['curfontstretcing'] = $curfontstretcing;
					$this_method_vars['curfonttracking'] = $curfonttracking;
					$this_method_vars['minstartliney'] = $minstartliney;
					$this_method_vars['maxbottomliney'] = $maxbottomliney;
					$this_method_vars['yshift'] = $yshift;
					$this_method_vars['startlinepage'] = $startlinepage;
					$this_method_vars['startlinepos'] = $startlinepos;
					$this_method_vars['startlinex'] = $startlinex;
					$this_method_vars['startliney'] = $startliney;
					$this_method_vars['newline'] = $newline;
					$this_method_vars['loop'] = $loop;
					$this_method_vars['curpos'] = $curpos;
					$this_method_vars['pask'] = $pask;
					$this_method_vars['lalign'] = $lalign;
					$this_method_vars['plalign'] = $plalign;
					$this_method_vars['w'] = $w;
					$this_method_vars['prev_cell_height_ratio'] = $prev_cell_height_ratio;
					$this_method_vars['prev_listnum'] = $prev_listnum;
					$this_method_vars['prev_listordered'] = $prev_listordered;
					$this_method_vars['prev_listcount'] = $prev_listcount;
					$this_method_vars['prev_lispacer'] = $prev_lispacer;
					$this_method_vars['fontaligned'] = $fontaligned;
					$this_method_vars['key'] = $key;
					$this_method_vars['dom'] = $dom;
				}
			}
			// print THEAD block
			if (($dom[$key]['value'] == 'tr') AND isset($dom[$key]['thead']) AND $dom[$key]['thead']) {
				if (isset($dom[$key]['parent']) AND isset($dom[$dom[$key]['parent']]['thead']) AND !TCPDF_STATIC::empty_string($dom[$dom[$key]['parent']]['thead'])) {
					$this->inthead = true;
					// print table header (thead)
					$this->writeHTML($this->thead, false, false, false, false, '');
					// check if we are on a new page or on a new column
					if (($this->y < $this->start_transaction_y) OR ($this->checkPageBreak($this->lasth, '', false))) {
						// we are on a new page or on a new column and the total object height is less than the available vertical space.
						// restore previous object
						$this->rollbackTransaction(true);
						// restore previous values
						foreach ($this_method_vars as $vkey => $vval) {
							$$vkey = $vval;
						}
						// disable table header
						$tmp_thead = $this->thead;
						$this->thead = '';
						// add a page (or trig AcceptPageBreak() for multicolumn mode)
						$pre_y = $this->y;
						if ((!$this->checkPageBreak($this->PageBreakTrigger + 1)) AND ($this->y < $pre_y)) {
							// fix for multicolumn mode
							$startliney = $this->y;
						}
						$this->start_transaction_page = $this->page;
						$this->start_transaction_y = $this->y;
						// restore table header
						$this->thead = $tmp_thead;
						// fix table border properties
						if (isset($dom[$dom[$key]['parent']]['attribute']['cellspacing'])) {
							$tmp_cellspacing = $this->getHTMLUnitToUnits($dom[$dom[$key]['parent']]['attribute']['cellspacing'], 1, 'px');
						} elseif (isset($dom[$dom[$key]['parent']]['border-spacing'])) {
							$tmp_cellspacing = $dom[$dom[$key]['parent']]['border-spacing']['V'];
						} else {
							$tmp_cellspacing = 0;
						}
						$dom[$dom[$key]['parent']]['borderposition']['page'] = $this->page;
						$dom[$dom[$key]['parent']]['borderposition']['column'] = $this->current_column;
						$dom[$dom[$key]['parent']]['borderposition']['y'] = $this->y + $tmp_cellspacing;
						$xoffset = ($this->x - $dom[$dom[$key]['parent']]['borderposition']['x']);
						$dom[$dom[$key]['parent']]['borderposition']['x'] += $xoffset;
						$dom[$dom[$key]['parent']]['borderposition']['xmax'] += $xoffset;
						// print table header (thead)
						$this->writeHTML($this->thead, false, false, false, false, '');
					}
				}
				// move $key index forward to skip THEAD block
				while ( ($key < $maxel) AND (!(
					($dom[$key]['tag'] AND $dom[$key]['opening'] AND ($dom[$key]['value'] == 'tr') AND (!isset($dom[$key]['thead']) OR !$dom[$key]['thead']))
					OR ($dom[$key]['tag'] AND (!$dom[$key]['opening']) AND ($dom[$key]['value'] == 'table'))) )) {
					++$key;
				}
			}
			if ($dom[$key]['tag'] OR ($key == 0)) {
				if ((($dom[$key]['value'] == 'table') OR ($dom[$key]['value'] == 'tr')) AND (isset($dom[$key]['align']))) {
					$dom[$key]['align'] = ($this->rtl) ? 'R' : 'L';
				}
				// vertically align image in line
				if ((!$this->newline) AND ($dom[$key]['value'] == 'img') AND (isset($dom[$key]['height'])) AND ($dom[$key]['height'] > 0)) {
					// get image height
					$imgh = $this->getHTMLUnitToUnits($dom[$key]['height'], $this->lasth, 'px');
					$autolinebreak = false;
					if (isset($dom[$key]['width']) AND ($dom[$key]['width'] > 0)) {
						$imgw = $this->getHTMLUnitToUnits($dom[$key]['width'], 1, 'px', false);
						if (($imgw <= ($this->w - $this->lMargin - $this->rMargin - $this->cell_padding['L'] - $this->cell_padding['R']))
							AND ((($this->rtl) AND (($this->x - $imgw) < ($this->lMargin + $this->cell_padding['L'])))
							OR ((!$this->rtl) AND (($this->x + $imgw) > ($this->w - $this->rMargin - $this->cell_padding['R']))))) {
							// add automatic line break
							$autolinebreak = true;
							$this->Ln('', $cell);
							if ((!$dom[($key-1)]['tag']) AND ($dom[($key-1)]['value'] == ' ')) {
								// go back to evaluate this line break
								--$key;
							}
						}
					}
					if (!$autolinebreak) {
						if ($this->inPageBody()) {
							$pre_y = $this->y;
							// check for page break
							if ((!$this->checkPageBreak($imgh)) AND ($this->y < $pre_y)) {
								// fix for multicolumn mode
								$startliney = $this->y;
							}
						}
						if ($this->page > $startlinepage) {
							// fix line splitted over two pages
							if (isset($this->footerlen[$startlinepage])) {
								$curpos = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
							}
							// line to be moved one page forward
							$pagebuff = $this->getPageBuffer($startlinepage);
							$linebeg = substr($pagebuff, $startlinepos, ($curpos - $startlinepos));
							$tstart = substr($pagebuff, 0, $startlinepos);
							$tend = substr($this->getPageBuffer($startlinepage), $curpos);
							// remove line from previous page
							$this->setPageBuffer($startlinepage, $tstart.''.$tend);
							$pagebuff = $this->getPageBuffer($this->page);
							$tstart = substr($pagebuff, 0, $this->cntmrk[$this->page]);
							$tend = substr($pagebuff, $this->cntmrk[$this->page]);
							// add line start to current page
							$yshift = ($minstartliney - $this->y);
							if ($fontaligned) {
								$yshift += ($curfontsize / $this->k);
							}
							$try = sprintf('1 0 0 1 0 %F cm', ($yshift * $this->k));
							$this->setPageBuffer($this->page, $tstart."\nq\n".$try."\n".$linebeg."\nQ\n".$tend);
							// shift the annotations and links
							if (isset($this->PageAnnots[$this->page])) {
								$next_pask = count($this->PageAnnots[$this->page]);
							} else {
								$next_pask = 0;
							}
							if (isset($this->PageAnnots[$startlinepage])) {
								foreach ($this->PageAnnots[$startlinepage] as $pak => $pac) {
									if ($pak >= $pask) {
										$this->PageAnnots[$this->page][] = $pac;
										unset($this->PageAnnots[$startlinepage][$pak]);
										$npak = count($this->PageAnnots[$this->page]) - 1;
										$this->PageAnnots[$this->page][$npak]['y'] -= $yshift;
									}
								}
							}
							$pask = $next_pask;
							$startlinepos = $this->cntmrk[$this->page];
							$startlinepage = $this->page;
							$startliney = $this->y;
							$this->newline = false;
						}
						$this->y += ((($curfontsize * $this->cell_height_ratio / $this->k) + $curfontascent - $curfontdescent) / 2) - $imgh;
						$minstartliney = min($this->y, $minstartliney);
						$maxbottomliney = ($startliney + ($this->FontSize * $this->cell_height_ratio));
					}
				} elseif (isset($dom[$key]['fontname']) OR isset($dom[$key]['fontstyle']) OR isset($dom[$key]['fontsize']) OR isset($dom[$key]['line-height'])) {
					// account for different font size
					$pfontname = $curfontname;
					$pfontstyle = $curfontstyle;
					$pfontsize = $curfontsize;
					$fontname = (isset($dom[$key]['fontname']) ? $dom[$key]['fontname'] : $curfontname);
					$fontstyle = (isset($dom[$key]['fontstyle']) ? $dom[$key]['fontstyle'] : $curfontstyle);
					$fontsize = (isset($dom[$key]['fontsize']) ? $dom[$key]['fontsize'] : $curfontsize);
					$fontascent = $this->getFontAscent($fontname, $fontstyle, $fontsize);
					$fontdescent = $this->getFontDescent($fontname, $fontstyle, $fontsize);
					if (($fontname != $curfontname) OR ($fontstyle != $curfontstyle) OR ($fontsize != $curfontsize)
						OR ($this->cell_height_ratio != $dom[$key]['line-height'])
						OR ($dom[$key]['tag'] AND $dom[$key]['opening'] AND ($dom[$key]['value'] == 'li')) ) {
						if (($key < ($maxel - 1)) AND (
								($dom[$key]['tag'] AND $dom[$key]['opening'] AND ($dom[$key]['value'] == 'li'))
								OR ($this->cell_height_ratio != $dom[$key]['line-height'])
								OR (!$this->newline AND is_numeric($fontsize) AND is_numeric($curfontsize) AND ($fontsize >= 0) AND ($curfontsize >= 0) AND ($fontsize != $curfontsize))
							)) {
							if ($this->page > $startlinepage) {
								// fix lines splitted over two pages
								if (isset($this->footerlen[$startlinepage])) {
									$curpos = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
								}
								// line to be moved one page forward
								$pagebuff = $this->getPageBuffer($startlinepage);
								$linebeg = substr($pagebuff, $startlinepos, ($curpos - $startlinepos));
								$tstart = substr($pagebuff, 0, $startlinepos);
								$tend = substr($this->getPageBuffer($startlinepage), $curpos);
								// remove line start from previous page
								$this->setPageBuffer($startlinepage, $tstart.''.$tend);
								$pagebuff = $this->getPageBuffer($this->page);
								$tstart = substr($pagebuff, 0, $this->cntmrk[$this->page]);
								$tend = substr($pagebuff, $this->cntmrk[$this->page]);
								// add line start to current page
								$yshift = ($minstartliney - $this->y);
								$try = sprintf('1 0 0 1 0 %F cm', ($yshift * $this->k));
								$this->setPageBuffer($this->page, $tstart."\nq\n".$try."\n".$linebeg."\nQ\n".$tend);
								// shift the annotations and links
								if (isset($this->PageAnnots[$this->page])) {
									$next_pask = count($this->PageAnnots[$this->page]);
								} else {
									$next_pask = 0;
								}
								if (isset($this->PageAnnots[$startlinepage])) {
									foreach ($this->PageAnnots[$startlinepage] as $pak => $pac) {
										if ($pak >= $pask) {
											$this->PageAnnots[$this->page][] = $pac;
											unset($this->PageAnnots[$startlinepage][$pak]);
											$npak = count($this->PageAnnots[$this->page]) - 1;
											$this->PageAnnots[$this->page][$npak]['y'] -= $yshift;
										}
									}
								}
								$pask = $next_pask;
								$startlinepos = $this->cntmrk[$this->page];
								$startlinepage = $this->page;
								$startliney = $this->y;
							}
							if (!isset($dom[$key]['line-height'])) {
								$dom[$key]['line-height'] = $this->cell_height_ratio;
							}
							if (!$dom[$key]['block']) {
								if (!(isset($dom[($key + 1)]) AND $dom[($key + 1)]['tag'] AND (!$dom[($key + 1)]['opening']) AND ($dom[($key + 1)]['value'] != 'li') AND $dom[$key]['tag'] AND (!$dom[$key]['opening']))) {
									$this->y += (((($curfontsize * $this->cell_height_ratio) - ($fontsize * $dom[$key]['line-height'])) / $this->k) + $curfontascent - $fontascent - $curfontdescent + $fontdescent) / 2;
								}
								if (($dom[$key]['value'] != 'sup') AND ($dom[$key]['value'] != 'sub')) {
									$current_line_align_data = array($key, $minstartliney, $maxbottomliney);
									if (isset($line_align_data) AND (($line_align_data[0] == ($key - 1)) OR (($line_align_data[0] == ($key - 2)) AND (isset($dom[($key - 1)])) AND (preg_match('/^([\s]+)$/', $dom[($key - 1)]['value']) > 0)))) {
										$minstartliney = min($this->y, $line_align_data[1]);
										$maxbottomliney = max(($this->y + (($fontsize * $this->cell_height_ratio) / $this->k)), $line_align_data[2]);
									} else {
										$minstartliney = min($this->y, $minstartliney);
										$maxbottomliney = max(($this->y + (($fontsize * $this->cell_height_ratio) / $this->k)), $maxbottomliney);
									}
									$line_align_data = $current_line_align_data;
								}
							}
							$this->cell_height_ratio = $dom[$key]['line-height'];
							$fontaligned = true;
						}
						$this->SetFont($fontname, $fontstyle, $fontsize);
						// reset row height
						$this->resetLastH();
						$curfontname = $fontname;
						$curfontstyle = $fontstyle;
						$curfontsize = $fontsize;
						$curfontascent = $fontascent;
						$curfontdescent = $fontdescent;
					}
				}
				// set text rendering mode
				$textstroke = isset($dom[$key]['stroke']) ? $dom[$key]['stroke'] : $this->textstrokewidth;
				$textfill = isset($dom[$key]['fill']) ? $dom[$key]['fill'] : (($this->textrendermode % 2) == 0);
				$textclip = isset($dom[$key]['clip']) ? $dom[$key]['clip'] : ($this->textrendermode > 3);
				$this->setTextRenderingMode($textstroke, $textfill, $textclip);
				if (isset($dom[$key]['font-stretch']) AND ($dom[$key]['font-stretch'] !== false)) {
					$this->setFontStretching($dom[$key]['font-stretch']);
				}
				if (isset($dom[$key]['letter-spacing']) AND ($dom[$key]['letter-spacing'] !== false)) {
					$this->setFontSpacing($dom[$key]['letter-spacing']);
				}
				if (($plalign == 'J') AND $dom[$key]['block']) {
					$plalign = '';
				}
				// get current position on page buffer
				$curpos = $this->pagelen[$startlinepage];
				if (isset($dom[$key]['bgcolor']) AND ($dom[$key]['bgcolor'] !== false)) {
					$this->SetFillColorArray($dom[$key]['bgcolor']);
					$wfill = true;
				} else {
					$wfill = $fill | false;
				}
				if (isset($dom[$key]['fgcolor']) AND ($dom[$key]['fgcolor'] !== false)) {
					$this->SetTextColorArray($dom[$key]['fgcolor']);
				}
				if (isset($dom[$key]['strokecolor']) AND ($dom[$key]['strokecolor'] !== false)) {
					$this->SetDrawColorArray($dom[$key]['strokecolor']);
				}
				if (isset($dom[$key]['align'])) {
					$lalign = $dom[$key]['align'];
				}
				if (TCPDF_STATIC::empty_string($lalign)) {
					$lalign = $align;
				}
			}
			// align lines
			if ($this->newline AND (strlen($dom[$key]['value']) > 0) AND ($dom[$key]['value'] != 'td') AND ($dom[$key]['value'] != 'th')) {
				$newline = true;
				$fontaligned = false;
				// we are at the beginning of a new line
				if (isset($startlinex)) {
					$yshift = ($minstartliney - $startliney);
					if (($yshift > 0) OR ($this->page > $startlinepage)) {
						$yshift = 0;
					}
					$t_x = 0;
					// the last line must be shifted to be aligned as requested
					$linew = abs($this->endlinex - $startlinex);
					if ($this->inxobj) {
						// we are inside an XObject template
						$pstart = substr($this->xobjects[$this->xobjid]['outdata'], 0, $startlinepos);
						if (isset($opentagpos)) {
							$midpos = $opentagpos;
						} else {
							$midpos = 0;
						}
						if ($midpos > 0) {
							$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos, ($midpos - $startlinepos));
							$pend = substr($this->xobjects[$this->xobjid]['outdata'], $midpos);
						} else {
							$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos);
							$pend = '';
						}
					} else {
						$pstart = substr($this->getPageBuffer($startlinepage), 0, $startlinepos);
						if (isset($opentagpos) AND isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
							$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
							$midpos = min($opentagpos, $this->footerpos[$startlinepage]);
						} elseif (isset($opentagpos)) {
							$midpos = $opentagpos;
						} elseif (isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
							$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
							$midpos = $this->footerpos[$startlinepage];
						} else {
							$midpos = 0;
						}
						if ($midpos > 0) {
							$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos, ($midpos - $startlinepos));
							$pend = substr($this->getPageBuffer($startlinepage), $midpos);
						} else {
							$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos);
							$pend = '';
						}
					}
					if ((isset($plalign) AND ((($plalign == 'C') OR ($plalign == 'J') OR (($plalign == 'R') AND (!$this->rtl)) OR (($plalign == 'L') AND ($this->rtl)))))) {
						// calculate shifting amount
						$tw = $w;
						if (($plalign == 'J') AND $this->isRTLTextDir() AND ($this->num_columns > 1)) {
							$tw += $this->cell_padding['R'];
						}
						if ($this->lMargin != $prevlMargin) {
							$tw += ($prevlMargin - $this->lMargin);
						}
						if ($this->rMargin != $prevrMargin) {
							$tw += ($prevrMargin - $this->rMargin);
						}
						$one_space_width = $this->GetStringWidth(chr(32));
						$no = 0; // number of spaces on a line contained on a single block
						if ($this->isRTLTextDir()) { // RTL
							// remove left space if exist
							$pos1 = TCPDF_STATIC::revstrpos($pmid, '[(');
							if ($pos1 > 0) {
								$pos1 = intval($pos1);
								if ($this->isUnicodeFont()) {
									$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, '[('.chr(0).chr(32)));
									$spacelen = 2;
								} else {
									$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, '[('.chr(32)));
									$spacelen = 1;
								}
								if ($pos1 == $pos2) {
									$pmid = substr($pmid, 0, ($pos1 + 2)).substr($pmid, ($pos1 + 2 + $spacelen));
									if (substr($pmid, $pos1, 4) == '[()]') {
										$linew -= $one_space_width;
									} elseif ($pos1 == strpos($pmid, '[(')) {
										$no = 1;
									}
								}
							}
						} else { // LTR
							// remove right space if exist
							$pos1 = TCPDF_STATIC::revstrpos($pmid, ')]');
							if ($pos1 > 0) {
								$pos1 = intval($pos1);
								if ($this->isUnicodeFont()) {
									$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, chr(0).chr(32).')]')) + 2;
									$spacelen = 2;
								} else {
									$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, chr(32).')]')) + 1;
									$spacelen = 1;
								}
								if ($pos1 == $pos2) {
									$pmid = substr($pmid, 0, ($pos1 - $spacelen)).substr($pmid, $pos1);
									$linew -= $one_space_width;
								}
							}
						}
						$mdiff = ($tw - $linew);
						if ($plalign == 'C') {
							if ($this->rtl) {
								$t_x = -($mdiff / 2);
							} else {
								$t_x = ($mdiff / 2);
							}
						} elseif ($plalign == 'R') {
							// right alignment on LTR document
							$t_x = $mdiff;
						} elseif ($plalign == 'L') {
							// left alignment on RTL document
							$t_x = -$mdiff;
						} elseif (($plalign == 'J') AND ($plalign == $lalign)) {
							// Justification
							if ($this->isRTLTextDir()) {
								// align text on the left
								$t_x = -$mdiff;
							}
							$ns = 0; // number of spaces
							$pmidtemp = $pmid;
							// escape special characters
							$pmidtemp = preg_replace('/[\\\][\(]/x', '\\#!#OP#!#', $pmidtemp);
							$pmidtemp = preg_replace('/[\\\][\)]/x', '\\#!#CP#!#', $pmidtemp);
							// search spaces
							if (preg_match_all('/\[\(([^\)]*)\)\]/x', $pmidtemp, $lnstring, PREG_PATTERN_ORDER)) {
								$spacestr = $this->getSpaceString();
								$maxkk = count($lnstring[1]) - 1;
								for ($kk=0; $kk <= $maxkk; ++$kk) {
									// restore special characters
									$lnstring[1][$kk] = str_replace('#!#OP#!#', '(', $lnstring[1][$kk]);
									$lnstring[1][$kk] = str_replace('#!#CP#!#', ')', $lnstring[1][$kk]);
									// store number of spaces on the strings
									$lnstring[2][$kk] = substr_count($lnstring[1][$kk], $spacestr);
									// count total spaces on line
									$ns += $lnstring[2][$kk];
									$lnstring[3][$kk] = $ns;
								}
								if ($ns == 0) {
									$ns = 1;
								}
								// calculate additional space to add to each existing space
								$spacewidth = ($mdiff / ($ns - $no)) * $this->k;
								if ($this->FontSize <= 0) {
									$this->FontSize = 1;
								}
								$spacewidthu = -1000 * ($mdiff + (($ns + $no) * $one_space_width)) / $ns / $this->FontSize;
								if ($this->font_spacing != 0) {
									// fixed spacing mode
									$osw = -1000 * $this->font_spacing / $this->FontSize;
									$spacewidthu += $osw;
								}
								$nsmax = $ns;
								$ns = 0;
								reset($lnstring);
								$offset = 0;
								$strcount = 0;
								$prev_epsposbeg = 0;
								$textpos = 0;
								if ($this->isRTLTextDir()) {
									$textpos = $this->wPt;
								}
								global $spacew;
								while (preg_match('/([0-9\.\+\-]*)[\s](Td|cm|m|l|c|re)[\s]/x', $pmid, $strpiece, PREG_OFFSET_CAPTURE, $offset) == 1) {
									// check if we are inside a string section '[( ... )]'
									$stroffset = strpos($pmid, '[(', $offset);
									if (($stroffset !== false) AND ($stroffset <= $strpiece[2][1])) {
										// set offset to the end of string section
										$offset = strpos($pmid, ')]', $stroffset);
										while (($offset !== false) AND ($pmid[($offset - 1)] == '\\')) {
											$offset = strpos($pmid, ')]', ($offset + 1));
										}
										if ($offset === false) {
											$this->Error('HTML Justification: malformed PDF code.');
										}
										continue;
									}
									if ($this->isRTLTextDir()) {
										$spacew = ($spacewidth * ($nsmax - $ns));
									} else {
										$spacew = ($spacewidth * $ns);
									}
									$offset = $strpiece[2][1] + strlen($strpiece[2][0]);
									$epsposbeg = strpos($pmid, 'q'.$this->epsmarker, $offset);
									$epsposend = strpos($pmid, $this->epsmarker.'Q', $offset) + strlen($this->epsmarker.'Q');
									if ((($epsposbeg > 0) AND ($epsposend > 0) AND ($offset > $epsposbeg) AND ($offset < $epsposend))
										OR (($epsposbeg === false) AND ($epsposend > 0) AND ($offset < $epsposend))) {
										// shift EPS images
										$trx = sprintf('1 0 0 1 %F 0 cm', $spacew);
										$epsposbeg = strpos($pmid, 'q'.$this->epsmarker, ($prev_epsposbeg - 6));
										$pmid_b = substr($pmid, 0, $epsposbeg);
										$pmid_m = substr($pmid, $epsposbeg, ($epsposend - $epsposbeg));
										$pmid_e = substr($pmid, $epsposend);
										$pmid = $pmid_b."\nq\n".$trx."\n".$pmid_m."\nQ\n".$pmid_e;
										$offset = $epsposend;
										continue;

									}
									$prev_epsposbeg = $epsposbeg;
									$currentxpos = 0;
									// shift blocks of code
									switch ($strpiece[2][0]) {
										case 'Td':
										case 'cm':
										case 'm':
										case 'l': {
											// get current X position
											preg_match('/([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x', $pmid, $xmatches);
											if (!isset($xmatches[1])) {
												break;
											}
											$currentxpos = $xmatches[1];
											$textpos = $currentxpos;
											if (($strcount <= $maxkk) AND ($strpiece[2][0] == 'Td')) {
												$ns = $lnstring[3][$strcount];
												if ($this->isRTLTextDir()) {
													$spacew = ($spacewidth * ($nsmax - $ns));
												}
												++$strcount;
											}
											// justify block
											$pmid = preg_replace_callback('/([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x',
												create_function('$matches', 'global $spacew;
												$newx = sprintf("%F",(floatval($matches[1]) + $spacew));
												return "".$newx." ".$matches[2]." x*#!#*x".$matches[3].$matches[4];'), $pmid, 1);
											break;
										}
										case 're': {
											// justify block
											if (!TCPDF_STATIC::empty_string($this->lispacer)) {
												$this->lispacer = '';
												continue;
											}
											preg_match('/([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s](re)([\s]*)/x', $pmid, $xmatches);
											if (!isset($xmatches[1])) {
												break;
											}
											$currentxpos = $xmatches[1];
											global $x_diff, $w_diff;
											$x_diff = 0;
											$w_diff = 0;
											if ($this->isRTLTextDir()) { // RTL
												if ($currentxpos < $textpos) {
													$x_diff = ($spacewidth * ($nsmax - $lnstring[3][$strcount]));
													$w_diff = ($spacewidth * $lnstring[2][$strcount]);
												} else {
													if ($strcount > 0) {
														$x_diff = ($spacewidth * ($nsmax - $lnstring[3][($strcount - 1)]));
														$w_diff = ($spacewidth * $lnstring[2][($strcount - 1)]);
													}
												}
											} else { // LTR
												if ($currentxpos > $textpos) {
													if ($strcount > 0) {
														$x_diff = ($spacewidth * $lnstring[3][($strcount - 1)]);
													}
													$w_diff = ($spacewidth * $lnstring[2][$strcount]);
												} else {
													if ($strcount > 1) {
														$x_diff = ($spacewidth * $lnstring[3][($strcount - 2)]);
													}
													if ($strcount > 0) {
														$w_diff = ($spacewidth * $lnstring[2][($strcount - 1)]);
													}
												}
											}
											$pmid = preg_replace_callback('/('.$xmatches[1].')[\s]('.$xmatches[2].')[\s]('.$xmatches[3].')[\s]('.$strpiece[1][0].')[\s](re)([\s]*)/x',
												create_function('$matches', 'global $x_diff, $w_diff;
												$newx = sprintf("%F",(floatval($matches[1]) + $x_diff));
												$neww = sprintf("%F",(floatval($matches[3]) + $w_diff));
												return "".$newx." ".$matches[2]." ".$neww." ".$matches[4]." x*#!#*x".$matches[5].$matches[6];'), $pmid, 1);
											break;
										}
										case 'c': {
											// get current X position
											preg_match('/([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s](c)([\s]*)/x', $pmid, $xmatches);
											if (!isset($xmatches[1])) {
												break;
											}
											$currentxpos = $xmatches[1];
											// justify block
											$pmid = preg_replace_callback('/('.$xmatches[1].')[\s]('.$xmatches[2].')[\s]('.$xmatches[3].')[\s]('.$xmatches[4].')[\s]('.$xmatches[5].')[\s]('.$strpiece[1][0].')[\s](c)([\s]*)/x',
												create_function('$matches', 'global $spacew;
												$newx1 = sprintf("%F",(floatval($matches[1]) + $spacew));
												$newx2 = sprintf("%F",(floatval($matches[3]) + $spacew));
												$newx3 = sprintf("%F",(floatval($matches[5]) + $spacew));
												return "".$newx1." ".$matches[2]." ".$newx2." ".$matches[4]." ".$newx3." ".$matches[6]." x*#!#*x".$matches[7].$matches[8];'), $pmid, 1);
											break;
										}
									}
									// shift the annotations and links
									$cxpos = ($currentxpos / $this->k);
									$lmpos = ($this->lMargin + $this->cell_padding['L'] + $this->feps);
									if ($this->inxobj) {
										// we are inside an XObject template
										foreach ($this->xobjects[$this->xobjid]['annotations'] as $pak => $pac) {
											if (($pac['y'] >= $minstartliney) AND (($pac['x'] * $this->k) >= ($currentxpos - $this->feps)) AND (($pac['x'] * $this->k) <= ($currentxpos + $this->feps))) {
												if ($cxpos > $lmpos) {
													$this->xobjects[$this->xobjid]['annotations'][$pak]['x'] += ($spacew / $this->k);
													$this->xobjects[$this->xobjid]['annotations'][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												} else {
													$this->xobjects[$this->xobjid]['annotations'][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												}
												break;
											}
										}
									} elseif (isset($this->PageAnnots[$this->page])) {
										foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
											if (($pac['y'] >= $minstartliney) AND (($pac['x'] * $this->k) >= ($currentxpos - $this->feps)) AND (($pac['x'] * $this->k) <= ($currentxpos + $this->feps))) {
												if ($cxpos > $lmpos) {
													$this->PageAnnots[$this->page][$pak]['x'] += ($spacew / $this->k);
													$this->PageAnnots[$this->page][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												} else {
													$this->PageAnnots[$this->page][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												}
												break;
											}
										}
									}
								} // end of while
								// remove markers
								$pmid = str_replace('x*#!#*x', '', $pmid);
								if ($this->isUnicodeFont()) {
									// multibyte characters
									$spacew = $spacewidthu;
									if ($this->font_stretching != 100) {
										// word spacing is affected by stretching
										$spacew /= ($this->font_stretching / 100);
									}
									$pmidtemp = $pmid;
									// escape special characters
									$pmidtemp = preg_replace('/[\\\][\(]/x', '\\#!#OP#!#', $pmidtemp);
									$pmidtemp = preg_replace('/[\\\][\)]/x', '\\#!#CP#!#', $pmidtemp);
									$pmid = preg_replace_callback("/\[\(([^\)]*)\)\]/x",
												create_function('$matches', 'global $spacew;
												$matches[1] = str_replace("#!#OP#!#", "(", $matches[1]);
												$matches[1] = str_replace("#!#CP#!#", ")", $matches[1]);
												return "[(".str_replace(chr(0).chr(32), ") ".sprintf("%F", $spacew)." (", $matches[1]).")]";'), $pmidtemp);
									if ($this->inxobj) {
										// we are inside an XObject template
										$this->xobjects[$this->xobjid]['outdata'] = $pstart."\n".$pmid."\n".$pend;
									} else {
										$this->setPageBuffer($startlinepage, $pstart."\n".$pmid."\n".$pend);
									}
									$endlinepos = strlen($pstart."\n".$pmid."\n");
								} else {
									// non-unicode (single-byte characters)
									if ($this->font_stretching != 100) {
										// word spacing (Tw) is affected by stretching
										$spacewidth /= ($this->font_stretching / 100);
									}
									$rs = sprintf('%F Tw', $spacewidth);
									$pmid = preg_replace("/\[\(/x", $rs.' [(', $pmid);
									if ($this->inxobj) {
										// we are inside an XObject template
										$this->xobjects[$this->xobjid]['outdata'] = $pstart."\n".$pmid."\nBT 0 Tw ET\n".$pend;
									} else {
										$this->setPageBuffer($startlinepage, $pstart."\n".$pmid."\nBT 0 Tw ET\n".$pend);
									}
									$endlinepos = strlen($pstart."\n".$pmid."\nBT 0 Tw ET\n");
								}
							}
						} // end of J
					} // end if $startlinex
					if (($t_x != 0) OR ($yshift < 0)) {
						// shift the line
						$trx = sprintf('1 0 0 1 %F %F cm', ($t_x * $this->k), ($yshift * $this->k));
						$pstart .= "\nq\n".$trx."\n".$pmid."\nQ\n";
						$endlinepos = strlen($pstart);
						if ($this->inxobj) {
							// we are inside an XObject template
							$this->xobjects[$this->xobjid]['outdata'] = $pstart.$pend;
							foreach ($this->xobjects[$this->xobjid]['annotations'] as $pak => $pac) {
								if ($pak >= $pask) {
									$this->xobjects[$this->xobjid]['annotations'][$pak]['x'] += $t_x;
									$this->xobjects[$this->xobjid]['annotations'][$pak]['y'] -= $yshift;
								}
							}
						} else {
							$this->setPageBuffer($startlinepage, $pstart.$pend);
							// shift the annotations and links
							if (isset($this->PageAnnots[$this->page])) {
								foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
									if ($pak >= $pask) {
										$this->PageAnnots[$this->page][$pak]['x'] += $t_x;
										$this->PageAnnots[$this->page][$pak]['y'] -= $yshift;
									}
								}
							}
						}
						$this->y -= $yshift;
					}
				}
				$pbrk = $this->checkPageBreak($this->lasth);
				$this->newline = false;
				$startlinex = $this->x;
				$startliney = $this->y;
				if ($dom[$dom[$key]['parent']]['value'] == 'sup') {
					$startliney -= ((0.3 * $this->FontSizePt) / $this->k);
				} elseif ($dom[$dom[$key]['parent']]['value'] == 'sub') {
					$startliney -= (($this->FontSizePt / 0.7) / $this->k);
				} else {
					$minstartliney = $startliney;
					$maxbottomliney = ($this->y + (($fontsize * $this->cell_height_ratio) / $this->k));
				}
				$startlinepage = $this->page;
				if (isset($endlinepos) AND (!$pbrk)) {
					$startlinepos = $endlinepos;
				} else {
					if ($this->inxobj) {
						// we are inside an XObject template
						$startlinepos = strlen($this->xobjects[$this->xobjid]['outdata']);
					} elseif (!$this->InFooter) {
						if (isset($this->footerlen[$this->page])) {
							$this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
						} else {
							$this->footerpos[$this->page] = $this->pagelen[$this->page];
						}
						$startlinepos = $this->footerpos[$this->page];
					} else {
						$startlinepos = $this->pagelen[$this->page];
					}
				}
				unset($endlinepos);
				$plalign = $lalign;
				if (isset($this->PageAnnots[$this->page])) {
					$pask = count($this->PageAnnots[$this->page]);
				} else {
					$pask = 0;
				}
				if (!($dom[$key]['tag'] AND !$dom[$key]['opening'] AND ($dom[$key]['value'] == 'table')
					AND (isset($this->emptypagemrk[$this->page]))
					AND ($this->emptypagemrk[$this->page] == $this->pagelen[$this->page]))) {
					$this->SetFont($fontname, $fontstyle, $fontsize);
					if ($wfill) {
						$this->SetFillColorArray($this->bgcolor);
					}
				}
			} // end newline
			if (isset($opentagpos)) {
				unset($opentagpos);
			}
			if ($dom[$key]['tag']) {
				if ($dom[$key]['opening']) {
					// get text indentation (if any)
					if (isset($dom[$key]['text-indent']) AND $dom[$key]['block']) {
						$this->textindent = $dom[$key]['text-indent'];
						$this->newline = true;
					}
					// table
					if (($dom[$key]['value'] == 'table') AND isset($dom[$key]['cols']) AND ($dom[$key]['cols'] > 0)) {
						// available page width
						if ($this->rtl) {
							$wtmp = $this->x - $this->lMargin;
						} else {
							$wtmp = $this->w - $this->rMargin - $this->x;
						}
						// get cell spacing
						if (isset($dom[$key]['attribute']['cellspacing'])) {
							$clsp = $this->getHTMLUnitToUnits($dom[$key]['attribute']['cellspacing'], 1, 'px');
							$cellspacing = array('H' => $clsp, 'V' => $clsp);
						} elseif (isset($dom[$key]['border-spacing'])) {
							$cellspacing = $dom[$key]['border-spacing'];
						} else {
							$cellspacing = array('H' => 0, 'V' => 0);
						}
						// table width
						if (isset($dom[$key]['width'])) {
							$table_width = $this->getHTMLUnitToUnits($dom[$key]['width'], $wtmp, 'px');
						} else {
							$table_width = $wtmp;
						}
						$table_width -= (2 * $cellspacing['H']);
						if (!$this->inthead) {
							$this->y += $cellspacing['V'];
						}
						if ($this->rtl) {
							$cellspacingx = -$cellspacing['H'];
						} else {
							$cellspacingx = $cellspacing['H'];
						}
						// total table width without cellspaces
						$table_columns_width = ($table_width - ($cellspacing['H'] * ($dom[$key]['cols'] - 1)));
						// minimum column width
						$table_min_column_width = ($table_columns_width / $dom[$key]['cols']);
						// array of custom column widths
						$table_colwidths = array_fill(0, $dom[$key]['cols'], $table_min_column_width);
					}
					// table row
					if ($dom[$key]['value'] == 'tr') {
						// reset column counter
						$colid = 0;
					}
					// table cell
					if (($dom[$key]['value'] == 'td') OR ($dom[$key]['value'] == 'th')) {
						$trid = $dom[$key]['parent'];
						$table_el = $dom[$trid]['parent'];
						if (!isset($dom[$table_el]['cols'])) {
							$dom[$table_el]['cols'] = $dom[$trid]['cols'];
						}
						// store border info
						$tdborder = 0;
						if (isset($dom[$key]['border']) AND !empty($dom[$key]['border'])) {
							$tdborder = $dom[$key]['border'];
						}
						$colspan = intval($dom[$key]['attribute']['colspan']);
						if ($colspan <= 0) {
							$colspan = 1;
						}
						$old_cell_padding = $this->cell_padding;
						if (isset($dom[($dom[$trid]['parent'])]['attribute']['cellpadding'])) {
							$crclpd = $this->getHTMLUnitToUnits($dom[($dom[$trid]['parent'])]['attribute']['cellpadding'], 1, 'px');
							$current_cell_padding = array('L' => $crclpd, 'T' => $crclpd, 'R' => $crclpd, 'B' => $crclpd);
						} elseif (isset($dom[($dom[$trid]['parent'])]['padding'])) {
							$current_cell_padding = $dom[($dom[$trid]['parent'])]['padding'];
						} else {
							$current_cell_padding = array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0);
						}
						$this->cell_padding = $current_cell_padding;
						if (isset($dom[$key]['height'])) {
							// minimum cell height
							$cellh = $this->getHTMLUnitToUnits($dom[$key]['height'], 0, 'px');
						} else {
							$cellh = 0;
						}
						if (isset($dom[$key]['content'])) {
							$cell_content = stripslashes($dom[$key]['content']);
						} else {
							$cell_content = '&nbsp;';
						}
						$tagtype = $dom[$key]['value'];
						$parentid = $key;
						while (($key < $maxel) AND (!(($dom[$key]['tag']) AND (!$dom[$key]['opening']) AND ($dom[$key]['value'] == $tagtype) AND ($dom[$key]['parent'] == $parentid)))) {
							// move $key index forward
							++$key;
						}
						if (!isset($dom[$trid]['startpage'])) {
							$dom[$trid]['startpage'] = $this->page;
						} else {
							$this->setPage($dom[$trid]['startpage']);
						}
						if (!isset($dom[$trid]['startcolumn'])) {
							$dom[$trid]['startcolumn'] = $this->current_column;
						} elseif ($this->current_column != $dom[$trid]['startcolumn']) {
							$tmpx = $this->x;
							$this->selectColumn($dom[$trid]['startcolumn']);
							$this->x = $tmpx;
						}
						if (!isset($dom[$trid]['starty'])) {
							$dom[$trid]['starty'] = $this->y;
						} else {
							$this->y = $dom[$trid]['starty'];
						}
						if (!isset($dom[$trid]['startx'])) {
							$dom[$trid]['startx'] = $this->x;
							$this->x += $cellspacingx;
						} else {
							$this->x += ($cellspacingx / 2);
						}
						if (isset($dom[$parentid]['attribute']['rowspan'])) {
							$rowspan = intval($dom[$parentid]['attribute']['rowspan']);
						} else {
							$rowspan = 1;
						}
						// skip row-spanned cells started on the previous rows
						if (isset($dom[$table_el]['rowspans'])) {
							$rsk = 0;
							$rskmax = count($dom[$table_el]['rowspans']);
							while ($rsk < $rskmax) {
								$trwsp = $dom[$table_el]['rowspans'][$rsk];
								$rsstartx = $trwsp['startx'];
								$rsendx = $trwsp['endx'];
								// account for margin changes
								if ($trwsp['startpage'] < $this->page) {
									if (($this->rtl) AND ($this->pagedim[$this->page]['orm'] != $this->pagedim[$trwsp['startpage']]['orm'])) {
										$dl = ($this->pagedim[$this->page]['orm'] - $this->pagedim[$trwsp['startpage']]['orm']);
										$rsstartx -= $dl;
										$rsendx -= $dl;
									} elseif ((!$this->rtl) AND ($this->pagedim[$this->page]['olm'] != $this->pagedim[$trwsp['startpage']]['olm'])) {
										$dl = ($this->pagedim[$this->page]['olm'] - $this->pagedim[$trwsp['startpage']]['olm']);
										$rsstartx += $dl;
										$rsendx += $dl;
									}
								}
								if (($trwsp['rowspan'] > 0)
									AND ($rsstartx > ($this->x - $cellspacing['H'] - $current_cell_padding['L'] - $this->feps))
									AND ($rsstartx < ($this->x + $cellspacing['H'] + $current_cell_padding['R'] + $this->feps))
									AND (($trwsp['starty'] < ($this->y - $this->feps)) OR ($trwsp['startpage'] < $this->page) OR ($trwsp['startcolumn'] < $this->current_column))) {
									// set the starting X position of the current cell
									$this->x = $rsendx + $cellspacingx;
									// increment column indicator
									$colid += $trwsp['colspan'];
									if (($trwsp['rowspan'] == 1)
										AND (isset($dom[$trid]['endy']))
										AND (isset($dom[$trid]['endpage']))
										AND (isset($dom[$trid]['endcolumn']))
										AND ($trwsp['endpage'] == $dom[$trid]['endpage'])
										AND ($trwsp['endcolumn'] == $dom[$trid]['endcolumn'])) {
										// set ending Y position for row
										$dom[$table_el]['rowspans'][$rsk]['endy'] = max($dom[$trid]['endy'], $trwsp['endy']);
										$dom[$trid]['endy'] = $dom[$table_el]['rowspans'][$rsk]['endy'];
									}
									$rsk = 0;
								} else {
									++$rsk;
								}
							}
						}
						if (isset($dom[$parentid]['width'])) {
							// user specified width
							$cellw = $this->getHTMLUnitToUnits($dom[$parentid]['width'], $table_columns_width, 'px');
							$tmpcw = ($cellw / $colspan);
							for ($i = 0; $i < $colspan; ++$i) {
								$table_colwidths[($colid + $i)] = $tmpcw;
							}
						} else {
							// inherit column width
							$cellw = 0;
							for ($i = 0; $i < $colspan; ++$i) {
								$cellw += $table_colwidths[($colid + $i)];
							}
						}
						$cellw += (($colspan - 1) * $cellspacing['H']);
						// increment column indicator
						$colid += $colspan;
						// add rowspan information to table element
						if ($rowspan > 1) {
							$trsid = array_push($dom[$table_el]['rowspans'], array('trid' => $trid, 'rowspan' => $rowspan, 'mrowspan' => $rowspan, 'colspan' => $colspan, 'startpage' => $this->page, 'startcolumn' => $this->current_column, 'startx' => $this->x, 'starty' => $this->y));
						}
						$cellid = array_push($dom[$trid]['cellpos'], array('startx' => $this->x));
						if ($rowspan > 1) {
							$dom[$trid]['cellpos'][($cellid - 1)]['rowspanid'] = ($trsid - 1);
						}
						// push background colors
						if (isset($dom[$parentid]['bgcolor']) AND ($dom[$parentid]['bgcolor'] !== false)) {
							$dom[$trid]['cellpos'][($cellid - 1)]['bgcolor'] = $dom[$parentid]['bgcolor'];
						}
						// store border info
						if (isset($tdborder) AND !empty($tdborder)) {
							$dom[$trid]['cellpos'][($cellid - 1)]['border'] = $tdborder;
						}
						$prevLastH = $this->lasth;
						// store some info for multicolumn mode
						if ($this->rtl) {
							$this->colxshift['x'] = $this->w - $this->x - $this->rMargin;
						} else {
							$this->colxshift['x'] = $this->x - $this->lMargin;
						}
						$this->colxshift['s'] = $cellspacing;
						$this->colxshift['p'] = $current_cell_padding;
						// ****** write the cell content ******
						$this->MultiCell($cellw, $cellh, $cell_content, false, $lalign, false, 2, '', '', true, 0, true, true, 0, 'T', false);
						// restore some values
						$this->colxshift = array('x' => 0, 's' => array('H' => 0, 'V' => 0), 'p' => array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0));
						$this->lasth = $prevLastH;
						$this->cell_padding = $old_cell_padding;
						$dom[$trid]['cellpos'][($cellid - 1)]['endx'] = $this->x;
						// update the end of row position
						if ($rowspan <= 1) {
							if (isset($dom[$trid]['endy'])) {
								if (($this->page == $dom[$trid]['endpage']) AND ($this->current_column == $dom[$trid]['endcolumn'])) {
									$dom[$trid]['endy'] = max($this->y, $dom[$trid]['endy']);
								} elseif (($this->page > $dom[$trid]['endpage']) OR ($this->current_column > $dom[$trid]['endcolumn'])) {
									$dom[$trid]['endy'] = $this->y;
								}
							} else {
								$dom[$trid]['endy'] = $this->y;
							}
							if (isset($dom[$trid]['endpage'])) {
								$dom[$trid]['endpage'] = max($this->page, $dom[$trid]['endpage']);
							} else {
								$dom[$trid]['endpage'] = $this->page;
							}
							if (isset($dom[$trid]['endcolumn'])) {
								$dom[$trid]['endcolumn'] = max($this->current_column, $dom[$trid]['endcolumn']);
							} else {
								$dom[$trid]['endcolumn'] = $this->current_column;
							}
						} else {
							// account for row-spanned cells
							$dom[$table_el]['rowspans'][($trsid - 1)]['endx'] = $this->x;
							$dom[$table_el]['rowspans'][($trsid - 1)]['endy'] = $this->y;
							$dom[$table_el]['rowspans'][($trsid - 1)]['endpage'] = $this->page;
							$dom[$table_el]['rowspans'][($trsid - 1)]['endcolumn'] = $this->current_column;
						}
						if (isset($dom[$table_el]['rowspans'])) {
							// update endy and endpage on rowspanned cells
							foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
								if ($trwsp['rowspan'] > 0) {
									if (isset($dom[$trid]['endpage'])) {
										if (($trwsp['endpage'] == $dom[$trid]['endpage']) AND ($trwsp['endcolumn'] == $dom[$trid]['endcolumn'])) {
											$dom[$table_el]['rowspans'][$k]['endy'] = max($dom[$trid]['endy'], $trwsp['endy']);
										} elseif (($trwsp['endpage'] < $dom[$trid]['endpage']) OR ($trwsp['endcolumn'] < $dom[$trid]['endcolumn'])) {
											$dom[$table_el]['rowspans'][$k]['endy'] = $dom[$trid]['endy'];
											$dom[$table_el]['rowspans'][$k]['endpage'] = $dom[$trid]['endpage'];
											$dom[$table_el]['rowspans'][$k]['endcolumn'] = $dom[$trid]['endcolumn'];
										} else {
											$dom[$trid]['endy'] = $this->pagedim[$dom[$trid]['endpage']]['hk'] - $this->pagedim[$dom[$trid]['endpage']]['bm'];
										}
									}
								}
							}
						}
						$this->x += ($cellspacingx / 2);
					} else {
						// opening tag (or self-closing tag)
						if (!isset($opentagpos)) {
							if ($this->inxobj) {
								// we are inside an XObject template
								$opentagpos = strlen($this->xobjects[$this->xobjid]['outdata']);
							} elseif (!$this->InFooter) {
								if (isset($this->footerlen[$this->page])) {
									$this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
								} else {
									$this->footerpos[$this->page] = $this->pagelen[$this->page];
								}
								$opentagpos = $this->footerpos[$this->page];
							}
						}
						$dom = $this->openHTMLTagHandler($dom, $key, $cell);
					}
				} else { // closing tag
					$prev_numpages = $this->numpages;
					$old_bordermrk = $this->bordermrk[$this->page];
					$dom = $this->closeHTMLTagHandler($dom, $key, $cell, $maxbottomliney);
					if ($this->bordermrk[$this->page] > $old_bordermrk) {
						$startlinepos += ($this->bordermrk[$this->page] - $old_bordermrk);
					}
					if ($prev_numpages > $this->numpages) {
						$startlinepage = $this->page;
					}
				}
			} elseif (strlen($dom[$key]['value']) > 0) {
				// print list-item
				if (!TCPDF_STATIC::empty_string($this->lispacer) AND ($this->lispacer != '^')) {
					$this->SetFont($pfontname, $pfontstyle, $pfontsize);
					$this->resetLastH();
					$minstartliney = $this->y;
					$maxbottomliney = ($startliney + ($this->FontSize * $this->cell_height_ratio));
					if (is_numeric($pfontsize) AND ($pfontsize > 0)) {
						$this->putHtmlListBullet($this->listnum, $this->lispacer, $pfontsize);
					}
					$this->SetFont($curfontname, $curfontstyle, $curfontsize);
					$this->resetLastH();
					if (is_numeric($pfontsize) AND ($pfontsize > 0) AND is_numeric($curfontsize) AND ($curfontsize > 0) AND ($pfontsize != $curfontsize)) {
						$pfontascent = $this->getFontAscent($pfontname, $pfontstyle, $pfontsize);
						$pfontdescent = $this->getFontDescent($pfontname, $pfontstyle, $pfontsize);
						$this->y += ((($pfontsize - $curfontsize) * $this->cell_height_ratio / $this->k) + $pfontascent - $curfontascent - $pfontdescent + $curfontdescent) / 2;
						$minstartliney = min($this->y, $minstartliney);
						$maxbottomliney = max(($this->y + (($pfontsize * $this->cell_height_ratio) / $this->k)), $maxbottomliney);
					}
				}
				// text
				$this->htmlvspace = 0;
				if ((!$this->premode) AND $this->isRTLTextDir()) {
					// reverse spaces order
					$lsp = ''; // left spaces
					$rsp = ''; // right spaces
					if (preg_match('/^('.$this->re_space['p'].'+)/'.$this->re_space['m'], $dom[$key]['value'], $matches)) {
						$lsp = $matches[1];
					}
					if (preg_match('/('.$this->re_space['p'].'+)$/'.$this->re_space['m'], $dom[$key]['value'], $matches)) {
						$rsp = $matches[1];
					}
					$dom[$key]['value'] = $rsp.$this->stringTrim($dom[$key]['value']).$lsp;
				}
				if ($newline) {
					if (!$this->premode) {
						$prelen = strlen($dom[$key]['value']);
						if ($this->isRTLTextDir()) {
							// right trim except non-breaking space
							$dom[$key]['value'] = $this->stringRightTrim($dom[$key]['value']);
						} else {
							// left trim except non-breaking space
							$dom[$key]['value'] = $this->stringLeftTrim($dom[$key]['value']);
						}
						$postlen = strlen($dom[$key]['value']);
						if (($postlen == 0) AND ($prelen > 0)) {
							$dom[$key]['trimmed_space'] = true;
						}
					}
					$newline = false;
					$firstblock = true;
				} else {
					$firstblock = false;
					// replace empty multiple spaces string with a single space
					$dom[$key]['value'] = preg_replace('/^'.$this->re_space['p'].'+$/'.$this->re_space['m'], chr(32), $dom[$key]['value']);
				}
				$strrest = '';
				if ($this->rtl) {
					$this->x -= $this->textindent;
				} else {
					$this->x += $this->textindent;
				}
				if (!isset($dom[$key]['trimmed_space']) OR !$dom[$key]['trimmed_space']) {
					$strlinelen = $this->GetStringWidth($dom[$key]['value']);
					if (!empty($this->HREF) AND (isset($this->HREF['url']))) {
						// HTML <a> Link
						$hrefcolor = '';
						if (isset($dom[($dom[$key]['parent'])]['fgcolor']) AND ($dom[($dom[$key]['parent'])]['fgcolor'] !== false)) {
							$hrefcolor = $dom[($dom[$key]['parent'])]['fgcolor'];
						}
						$hrefstyle = -1;
						if (isset($dom[($dom[$key]['parent'])]['fontstyle']) AND ($dom[($dom[$key]['parent'])]['fontstyle'] !== false)) {
							$hrefstyle = $dom[($dom[$key]['parent'])]['fontstyle'];
						}
						$strrest = $this->addHtmlLink($this->HREF['url'], $dom[$key]['value'], $wfill, true, $hrefcolor, $hrefstyle, true);
					} else {
						$wadj = 0; // space to leave for block continuity
						if ($this->rtl) {
							$cwa = ($this->x - $this->lMargin);
						} else {
							$cwa = ($this->w - $this->rMargin - $this->x);
						}
						if (($strlinelen < $cwa) AND (isset($dom[($key + 1)])) AND ($dom[($key + 1)]['tag']) AND (!$dom[($key + 1)]['block'])) {
							// check the next text blocks for continuity
							$nkey = ($key + 1);
							$write_block = true;
							$same_textdir = true;
							$tmp_fontname = $this->FontFamily;
							$tmp_fontstyle = $this->FontStyle;
							$tmp_fontsize = $this->FontSizePt;
							while ($write_block AND isset($dom[$nkey])) {
								if ($dom[$nkey]['tag']) {
									if ($dom[$nkey]['block']) {
										// end of block
										$write_block = false;
									}
									$tmp_fontname = isset($dom[$nkey]['fontname']) ? $dom[$nkey]['fontname'] : $this->FontFamily;
									$tmp_fontstyle = isset($dom[$nkey]['fontstyle']) ? $dom[$nkey]['fontstyle'] : $this->FontStyle;
									$tmp_fontsize = isset($dom[$nkey]['fontsize']) ? $dom[$nkey]['fontsize'] : $this->FontSizePt;
									$same_textdir = ($dom[$nkey]['dir'] == $dom[$key]['dir']);
								} else {
									$nextstr = TCPDF_STATIC::pregSplit('/'.$this->re_space['p'].'+/', $this->re_space['m'], $dom[$nkey]['value']);
									if (isset($nextstr[0]) AND $same_textdir) {
										$wadj += $this->GetStringWidth($nextstr[0], $tmp_fontname, $tmp_fontstyle, $tmp_fontsize);
										if (isset($nextstr[1])) {
											$write_block = false;
										}
									}
								}
								++$nkey;
							}
						}
						if (($wadj > 0) AND (($strlinelen + $wadj) >= $cwa)) {
							$wadj = 0;
							$nextstr = TCPDF_STATIC::pregSplit('/'.$this->re_space['p'].'/', $this->re_space['m'], $dom[$key]['value']);
							$numblks = count($nextstr);
							if ($numblks > 1) {
								// try to split on blank spaces
								$wadj = ($cwa - $strlinelen + $this->GetStringWidth($nextstr[($numblks - 1)]));
							} else {
								// set the entire block on new line
								$wadj = $this->GetStringWidth($nextstr[0]);
							}
						}
						// check for reversed text direction
						if (($wadj > 0) AND (($this->rtl AND ($this->tmprtl === 'L')) OR (!$this->rtl AND ($this->tmprtl === 'R')))) {
							// LTR text on RTL direction or RTL text on LTR direction
							$reverse_dir = true;
							$this->rtl = !$this->rtl;
							$revshift = ($strlinelen + $wadj + 0.000001); // add little quantity for rounding problems
							if ($this->rtl) {
								$this->x += $revshift;
							} else {
								$this->x -= $revshift;
							}
							$xws = $this->x;
						}
						// ****** write only until the end of the line and get the rest ******
						$strrest = $this->Write($this->lasth, $dom[$key]['value'], '', $wfill, '', false, 0, true, $firstblock, 0, $wadj);
						// restore default direction
						if ($reverse_dir AND ($wadj == 0)) {
							$this->x = $xws;
							$this->rtl = !$this->rtl;
							$reverse_dir = false;
						}
					}
				}
				$this->textindent = 0;
				if (strlen($strrest) > 0) {
					// store the remaining string on the previous $key position
					$this->newline = true;
					if ($strrest == $dom[$key]['value']) {
						// used to avoid infinite loop
						++$loop;
					} else {
						$loop = 0;
					}
					$dom[$key]['value'] = $strrest;
					if ($cell) {
						if ($this->rtl) {
							$this->x -= $this->cell_padding['R'];
						} else {
							$this->x += $this->cell_padding['L'];
						}
					}
					if ($loop < 3) {
						--$key;
					}
				} else {
					$loop = 0;
					// add the positive font spacing of the last character (if any)
					 if ($this->font_spacing > 0) {
					 	if ($this->rtl) {
							$this->x -= $this->font_spacing;
						} else {
							$this->x += $this->font_spacing;
						}
					}
				}
			}
			++$key;
			if (isset($dom[$key]['tag']) AND $dom[$key]['tag'] AND (!isset($dom[$key]['opening']) OR !$dom[$key]['opening']) AND isset($dom[($dom[$key]['parent'])]['attribute']['nobr']) AND ($dom[($dom[$key]['parent'])]['attribute']['nobr'] == 'true')) {
				// check if we are on a new page or on a new column
				if ((!$undo) AND (($this->y < $this->start_transaction_y) OR (($dom[$key]['value'] == 'tr') AND ($dom[($dom[$key]['parent'])]['endy'] < $this->start_transaction_y)))) {
					// we are on a new page or on a new column and the total object height is less than the available vertical space.
					// restore previous object
					$this->rollbackTransaction(true);
					// restore previous values
					foreach ($this_method_vars as $vkey => $vval) {
						$$vkey = $vval;
					}
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$pre_y = $this->y;
					if ((!$this->checkPageBreak($this->PageBreakTrigger + 1)) AND ($this->y < $pre_y)) {
						$startliney = $this->y;
					}
					$undo = true; // avoid infinite loop
				} else {
					$undo = false;
				}
			}
		} // end for each $key
		// align the last line
		if (isset($startlinex)) {
			$yshift = ($minstartliney - $startliney);
			if (($yshift > 0) OR ($this->page > $startlinepage)) {
				$yshift = 0;
			}
			$t_x = 0;
			// the last line must be shifted to be aligned as requested
			$linew = abs($this->endlinex - $startlinex);
			if ($this->inxobj) {
				// we are inside an XObject template
				$pstart = substr($this->xobjects[$this->xobjid]['outdata'], 0, $startlinepos);
				if (isset($opentagpos)) {
					$midpos = $opentagpos;
				} else {
					$midpos = 0;
				}
				if ($midpos > 0) {
					$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos, ($midpos - $startlinepos));
					$pend = substr($this->xobjects[$this->xobjid]['outdata'], $midpos);
				} else {
					$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos);
					$pend = '';
				}
			} else {
				$pstart = substr($this->getPageBuffer($startlinepage), 0, $startlinepos);
				if (isset($opentagpos) AND isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
					$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
					$midpos = min($opentagpos, $this->footerpos[$startlinepage]);
				} elseif (isset($opentagpos)) {
					$midpos = $opentagpos;
				} elseif (isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
					$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
					$midpos = $this->footerpos[$startlinepage];
				} else {
					$midpos = 0;
				}
				if ($midpos > 0) {
					$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos, ($midpos - $startlinepos));
					$pend = substr($this->getPageBuffer($startlinepage), $midpos);
				} else {
					$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos);
					$pend = '';
				}
			}
			if ((isset($plalign) AND ((($plalign == 'C') OR (($plalign == 'R') AND (!$this->rtl)) OR (($plalign == 'L') AND ($this->rtl)))))) {
				// calculate shifting amount
				$tw = $w;
				if ($this->lMargin != $prevlMargin) {
					$tw += ($prevlMargin - $this->lMargin);
				}
				if ($this->rMargin != $prevrMargin) {
					$tw += ($prevrMargin - $this->rMargin);
				}
				$one_space_width = $this->GetStringWidth(chr(32));
				$no = 0; // number of spaces on a line contained on a single block
				if ($this->isRTLTextDir()) { // RTL
					// remove left space if exist
					$pos1 = TCPDF_STATIC::revstrpos($pmid, '[(');
					if ($pos1 > 0) {
						$pos1 = intval($pos1);
						if ($this->isUnicodeFont()) {
							$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, '[('.chr(0).chr(32)));
							$spacelen = 2;
						} else {
							$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, '[('.chr(32)));
							$spacelen = 1;
						}
						if ($pos1 == $pos2) {
							$pmid = substr($pmid, 0, ($pos1 + 2)).substr($pmid, ($pos1 + 2 + $spacelen));
							if (substr($pmid, $pos1, 4) == '[()]') {
								$linew -= $one_space_width;
							} elseif ($pos1 == strpos($pmid, '[(')) {
								$no = 1;
							}
						}
					}
				} else { // LTR
					// remove right space if exist
					$pos1 = TCPDF_STATIC::revstrpos($pmid, ')]');
					if ($pos1 > 0) {
						$pos1 = intval($pos1);
						if ($this->isUnicodeFont()) {
							$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, chr(0).chr(32).')]')) + 2;
							$spacelen = 2;
						} else {
							$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, chr(32).')]')) + 1;
							$spacelen = 1;
						}
						if ($pos1 == $pos2) {
							$pmid = substr($pmid, 0, ($pos1 - $spacelen)).substr($pmid, $pos1);
							$linew -= $one_space_width;
						}
					}
				}
				$mdiff = ($tw - $linew);
				if ($plalign == 'C') {
					if ($this->rtl) {
						$t_x = -($mdiff / 2);
					} else {
						$t_x = ($mdiff / 2);
					}
				} elseif ($plalign == 'R') {
					// right alignment on LTR document
					$t_x = $mdiff;
				} elseif ($plalign == 'L') {
					// left alignment on RTL document
					$t_x = -$mdiff;
				}
			} // end if startlinex
			if (($t_x != 0) OR ($yshift < 0)) {
				// shift the line
				$trx = sprintf('1 0 0 1 %F %F cm', ($t_x * $this->k), ($yshift * $this->k));
				$pstart .= "\nq\n".$trx."\n".$pmid."\nQ\n";
				$endlinepos = strlen($pstart);
				if ($this->inxobj) {
					// we are inside an XObject template
					$this->xobjects[$this->xobjid]['outdata'] = $pstart.$pend;
					foreach ($this->xobjects[$this->xobjid]['annotations'] as $pak => $pac) {
						if ($pak >= $pask) {
							$this->xobjects[$this->xobjid]['annotations'][$pak]['x'] += $t_x;
							$this->xobjects[$this->xobjid]['annotations'][$pak]['y'] -= $yshift;
						}
					}
				} else {
					$this->setPageBuffer($startlinepage, $pstart.$pend);
					// shift the annotations and links
					if (isset($this->PageAnnots[$this->page])) {
						foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
							if ($pak >= $pask) {
								$this->PageAnnots[$this->page][$pak]['x'] += $t_x;
								$this->PageAnnots[$this->page][$pak]['y'] -= $yshift;
							}
						}
					}
				}
				$this->y -= $yshift;
				$yshift = 0;
			}
		}
		// restore previous values
		$this->setGraphicVars($gvars);
		if ($this->num_columns > 1) {
			$this->selectColumn();
		} elseif ($this->page > $prevPage) {
			$this->lMargin = $this->pagedim[$this->page]['olm'];
			$this->rMargin = $this->pagedim[$this->page]['orm'];
		}
		// restore previous list state
		$this->cell_height_ratio = $prev_cell_height_ratio;
		$this->listnum = $prev_listnum;
		$this->listordered = $prev_listordered;
		$this->listcount = $prev_listcount;
		$this->lispacer = $prev_lispacer;
		if ($ln AND (!($cell AND ($dom[$key-1]['value'] == 'table')))) {
			$this->Ln($this->lasth);
			if ($this->y < $maxbottomliney) {
				$this->y = $maxbottomliney;
			}
		}
		unset($dom);
	}

	/**
	 * Process opening tags.
	 * @param $dom (array) html dom array
	 * @param $key (int) current element id
	 * @param $cell (boolean) if true add the default left (or right if RTL) padding to each new line (default false).
	 * @return $dom array
	 * @protected
	 */
	protected function openHTMLTagHandler($dom, $key, $cell) {
		$tag = $dom[$key];
		$parent = $dom[($dom[$key]['parent'])];
		$firsttag = ($key == 1);
		// check for text direction attribute
		if (isset($tag['dir'])) {
			$this->setTempRTL($tag['dir']);
		} else {
			$this->tmprtl = false;
		}
		if ($tag['block']) {
			$hbz = 0; // distance from y to line bottom
			$hb = 0; // vertical space between block tags
			// calculate vertical space for block tags
			if (isset($this->tagvspaces[$tag['value']][0]['h']) AND ($this->tagvspaces[$tag['value']][0]['h'] >= 0)) {
				$cur_h = $this->tagvspaces[$tag['value']][0]['h'];
			} elseif (isset($tag['fontsize'])) {
				$cur_h = ($tag['fontsize'] / $this->k) * $this->cell_height_ratio;
			} else {
				$cur_h = $this->FontSize * $this->cell_height_ratio;
			}
			if (isset($this->tagvspaces[$tag['value']][0]['n'])) {
				$n = $this->tagvspaces[$tag['value']][0]['n'];
			} elseif (preg_match('/[h][0-9]/', $tag['value']) > 0) {
				$n = 0.6;
			} else {
				$n = 1;
			}
			if ((!isset($this->tagvspaces[$tag['value']])) AND (in_array($tag['value'], array('div', 'dt', 'dd', 'li', 'br')))) {
				$hb = 0;
			} else {
				$hb = ($n * $cur_h);
			}
			if (($this->htmlvspace <= 0) AND ($n > 0)) {
				if (isset($parent['fontsize'])) {
					$hbz = (($parent['fontsize'] / $this->k) * $this->cell_height_ratio);
				} else {
					$hbz = $this->FontSize * $this->cell_height_ratio;
				}
			}
			if (isset($dom[($key - 1)]) AND ($dom[($key - 1)]['value'] == 'table')) {
				// fix vertical space after table
				$hbz = 0;
			}
		}
		// Opening tag
		switch($tag['value']) {
			case 'table': {
				$cp = 0;
				$cs = 0;
				$dom[$key]['rowspans'] = array();
				if (!isset($dom[$key]['attribute']['nested']) OR ($dom[$key]['attribute']['nested'] != 'true')) {
					$this->htmlvspace = 0;
					// set table header
					if (!TCPDF_STATIC::empty_string($dom[$key]['thead'])) {
						// set table header
						$this->thead = $dom[$key]['thead'];
						if (!isset($this->theadMargins) OR (empty($this->theadMargins))) {
							$this->theadMargins = array();
							$this->theadMargins['cell_padding'] = $this->cell_padding;
							$this->theadMargins['lmargin'] = $this->lMargin;
							$this->theadMargins['rmargin'] = $this->rMargin;
							$this->theadMargins['page'] = $this->page;
							$this->theadMargins['cell'] = $cell;
						}
					}
				}
				// store current margins and page
				$dom[$key]['old_cell_padding'] = $this->cell_padding;
				if (isset($tag['attribute']['cellpadding'])) {
					$pad = $this->getHTMLUnitToUnits($tag['attribute']['cellpadding'], 1, 'px');
					$this->SetCellPadding($pad);
				} elseif (isset($tag['padding'])) {
					$this->cell_padding = $tag['padding'];
				}
				if (isset($tag['attribute']['cellspacing'])) {
					$cs = $this->getHTMLUnitToUnits($tag['attribute']['cellspacing'], 1, 'px');
				} elseif (isset($tag['border-spacing'])) {
					$cs = $tag['border-spacing']['V'];
				}
				$prev_y = $this->y;
				if ($this->checkPageBreak(((2 * $cp) + (2 * $cs) + $this->lasth), '', false) OR ($this->y < $prev_y)) {
					$this->inthead = true;
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$this->checkPageBreak($this->PageBreakTrigger + 1);
				}
				break;
			}
			case 'tr': {
				// array of columns positions
				$dom[$key]['cellpos'] = array();
				break;
			}
			case 'hr': {
				if ((isset($tag['height'])) AND ($tag['height'] != '')) {
					$hrHeight = $this->getHTMLUnitToUnits($tag['height'], 1, 'px');
				} else {
					$hrHeight = $this->GetLineWidth();
				}
				$this->addHTMLVertSpace($hbz, ($hrHeight / 2), $cell, $firsttag);
				$x = $this->GetX();
				$y = $this->GetY();
				$wtmp = $this->w - $this->lMargin - $this->rMargin;
				if ($cell) {
					$wtmp -= ($this->cell_padding['L'] + $this->cell_padding['R']);
				}
				if ((isset($tag['width'])) AND ($tag['width'] != '')) {
					$hrWidth = $this->getHTMLUnitToUnits($tag['width'], $wtmp, 'px');
				} else {
					$hrWidth = $wtmp;
				}
				$prevlinewidth = $this->GetLineWidth();
				$this->SetLineWidth($hrHeight);
				$this->Line($x, $y, $x + $hrWidth, $y);
				$this->SetLineWidth($prevlinewidth);
				$this->addHTMLVertSpace(($hrHeight / 2), 0, $cell, !isset($dom[($key + 1)]));
				break;
			}
			case 'a': {
				if (array_key_exists('href', $tag['attribute'])) {
					$this->HREF['url'] = $tag['attribute']['href'];
				}
				break;
			}
			case 'img': {
				if (isset($tag['attribute']['src'])) {
					if ($tag['attribute']['src']{0} === '@') {
						// data stream
						$tag['attribute']['src'] = '@'.base64_decode(substr($tag['attribute']['src'], 1));
						$type = '';
					} else {
						// get image type
						$type = TCPDF_IMAGES::getImageFileType($tag['attribute']['src']);
					}
					if (!isset($tag['width'])) {
						$tag['width'] = 0;
					}
					if (!isset($tag['height'])) {
						$tag['height'] = 0;
					}
					//if (!isset($tag['attribute']['align'])) {
						// the only alignment supported is "bottom"
						// further development is required for other modes.
						$tag['attribute']['align'] = 'bottom';
					//}
					switch($tag['attribute']['align']) {
						case 'top': {
							$align = 'T';
							break;
						}
						case 'middle': {
							$align = 'M';
							break;
						}
						case 'bottom': {
							$align = 'B';
							break;
						}
						default: {
							$align = 'B';
							break;
						}
					}
					$prevy = $this->y;
					$xpos = $this->x;
					$imglink = '';
					if (isset($this->HREF['url']) AND !TCPDF_STATIC::empty_string($this->HREF['url'])) {
						$imglink = $this->HREF['url'];
						if ($imglink{0} == '#') {
							// convert url to internal link
							$lnkdata = explode(',', $imglink);
							if (isset($lnkdata[0])) {
								$page = intval(substr($lnkdata[0], 1));
								if (empty($page) OR ($page <= 0)) {
									$page = $this->page;
								}
								if (isset($lnkdata[1]) AND (strlen($lnkdata[1]) > 0)) {
									$lnky = floatval($lnkdata[1]);
								} else {
									$lnky = 0;
								}
								$imglink = $this->AddLink();
								$this->SetLink($imglink, $lnky, $page);
							}
						}
					}
					$border = 0;
					if (isset($tag['border']) AND !empty($tag['border'])) {
						// currently only support 1 (frame) or a combination of 'LTRB'
						$border = $tag['border'];
					}
					$iw = '';
					if (isset($tag['width'])) {
						$iw = $this->getHTMLUnitToUnits($tag['width'], 1, 'px', false);
					}
					$ih = '';
					if (isset($tag['height'])) {
						$ih = $this->getHTMLUnitToUnits($tag['height'], 1, 'px', false);
					}
					if (($type == 'eps') OR ($type == 'ai')) {
						$this->ImageEps($tag['attribute']['src'], $xpos, $this->y, $iw, $ih, $imglink, true, $align, '', $border, true);
					} elseif ($type == 'svg') {
						$this->ImageSVG($tag['attribute']['src'], $xpos, $this->y, $iw, $ih, $imglink, $align, '', $border, true);
					} else {
						$this->Image($tag['attribute']['src'], $xpos, $this->y, $iw, $ih, '', $imglink, $align, false, 300, '', false, false, $border, false, false, true);
					}
					switch($align) {
						case 'T': {
							$this->y = $prevy;
							break;
						}
						case 'M': {
							$this->y = (($this->img_rb_y + $prevy - ($tag['fontsize'] / $this->k)) / 2) ;
							break;
						}
						case 'B': {
							$this->y = $this->img_rb_y - ($tag['fontsize'] / $this->k);
							break;
						}
					}
				}
				break;
			}
			case 'dl': {
				++$this->listnum;
				if ($this->listnum == 1) {
					$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				} else {
					$this->addHTMLVertSpace(0, 0, $cell, $firsttag);
				}
				break;
			}
			case 'dt': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'dd': {
				if ($this->rtl) {
					$this->rMargin += $this->listindent;
				} else {
					$this->lMargin += $this->listindent;
				}
				++$this->listindentlevel;
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'ul':
			case 'ol': {
				++$this->listnum;
				if ($tag['value'] == 'ol') {
					$this->listordered[$this->listnum] = true;
				} else {
					$this->listordered[$this->listnum] = false;
				}
				if (isset($tag['attribute']['start'])) {
					$this->listcount[$this->listnum] = intval($tag['attribute']['start']) - 1;
				} else {
					$this->listcount[$this->listnum] = 0;
				}
				if ($this->rtl) {
					$this->rMargin += $this->listindent;
					$this->x -= $this->listindent;
				} else {
					$this->lMargin += $this->listindent;
					$this->x += $this->listindent;
				}
				++$this->listindentlevel;
				if ($this->listnum == 1) {
					if ($key > 1) {
						$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
					}
				} else {
					$this->addHTMLVertSpace(0, 0, $cell, $firsttag);
				}
				break;
			}
			case 'li': {
				if ($key > 2) {
					$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				}
				if ($this->listordered[$this->listnum]) {
					// ordered item
					if (isset($parent['attribute']['type']) AND !TCPDF_STATIC::empty_string($parent['attribute']['type'])) {
						$this->lispacer = $parent['attribute']['type'];
					} elseif (isset($parent['listtype']) AND !TCPDF_STATIC::empty_string($parent['listtype'])) {
						$this->lispacer = $parent['listtype'];
					} elseif (isset($this->lisymbol) AND !TCPDF_STATIC::empty_string($this->lisymbol)) {
						$this->lispacer = $this->lisymbol;
					} else {
						$this->lispacer = '#';
					}
					++$this->listcount[$this->listnum];
					if (isset($tag['attribute']['value'])) {
						$this->listcount[$this->listnum] = intval($tag['attribute']['value']);
					}
				} else {
					// unordered item
					if (isset($parent['attribute']['type']) AND !TCPDF_STATIC::empty_string($parent['attribute']['type'])) {
						$this->lispacer = $parent['attribute']['type'];
					} elseif (isset($parent['listtype']) AND !TCPDF_STATIC::empty_string($parent['listtype'])) {
						$this->lispacer = $parent['listtype'];
					} elseif (isset($this->lisymbol) AND !TCPDF_STATIC::empty_string($this->lisymbol)) {
						$this->lispacer = $this->lisymbol;
					} else {
						$this->lispacer = '!';
					}
				}
				break;
			}
			case 'blockquote': {
				if ($this->rtl) {
					$this->rMargin += $this->listindent;
				} else {
					$this->lMargin += $this->listindent;
				}
				++$this->listindentlevel;
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'br': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'div': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'p': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'pre': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				$this->premode = true;
				break;
			}
			case 'sup': {
				$this->SetXY($this->GetX(), $this->GetY() - ((0.7 * $this->FontSizePt) / $this->k));
				break;
			}
			case 'sub': {
				$this->SetXY($this->GetX(), $this->GetY() + ((0.3 * $this->FontSizePt) / $this->k));
				break;
			}
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			// Form fields (since 4.8.000 - 2009-09-07)
			case 'form': {
				if (isset($tag['attribute']['action'])) {
					$this->form_action = $tag['attribute']['action'];
				} else {
					$this->Error('Please explicitly set action attribute path!');
				}
				if (isset($tag['attribute']['enctype'])) {
					$this->form_enctype = $tag['attribute']['enctype'];
				} else {
					$this->form_enctype = 'application/x-www-form-urlencoded';
				}
				if (isset($tag['attribute']['method'])) {
					$this->form_mode = $tag['attribute']['method'];
				} else {
					$this->form_mode = 'post';
				}
				break;
			}
			case 'input': {
				if (isset($tag['attribute']['name']) AND !TCPDF_STATIC::empty_string($tag['attribute']['name'])) {
					$name = $tag['attribute']['name'];
				} else {
					break;
				}
				$prop = array();
				$opt = array();
				if (isset($tag['attribute']['readonly']) AND !TCPDF_STATIC::empty_string($tag['attribute']['readonly'])) {
					$prop['readonly'] = true;
				}
				if (isset($tag['attribute']['value']) AND !TCPDF_STATIC::empty_string($tag['attribute']['value'])) {
					$value = $tag['attribute']['value'];
				}
				if (isset($tag['attribute']['maxlength']) AND !TCPDF_STATIC::empty_string($tag['attribute']['maxlength'])) {
					$opt['maxlen'] = intval($tag['attribute']['maxlength']);
				}
				$h = $this->FontSize * $this->cell_height_ratio;
				if (isset($tag['attribute']['size']) AND !TCPDF_STATIC::empty_string($tag['attribute']['size'])) {
					$w = intval($tag['attribute']['size']) * $this->GetStringWidth(chr(32)) * 2;
				} else {
					$w = $h;
				}
				if (isset($tag['attribute']['checked']) AND (($tag['attribute']['checked'] == 'checked') OR ($tag['attribute']['checked'] == 'true'))) {
					$checked = true;
				} else {
					$checked = false;
				}
				if (isset($tag['align'])) {
					switch ($tag['align']) {
						case 'C': {
							$opt['q'] = 1;
							break;
						}
						case 'R': {
							$opt['q'] = 2;
							break;
						}
						case 'L':
						default: {
							break;
						}
					}
				}
				switch ($tag['attribute']['type']) {
					case 'text': {
						if (isset($value)) {
							$opt['v'] = $value;
						}
						$this->TextField($name, $w, $h, $prop, $opt, '', '', false);
						break;
					}
					case 'password': {
						if (isset($value)) {
							$opt['v'] = $value;
						}
						$prop['password'] = 'true';
						$this->TextField($name, $w, $h, $prop, $opt, '', '', false);
						break;
					}
					case 'checkbox': {
						if (!isset($value)) {
							break;
						}
						$this->CheckBox($name, $w, $checked, $prop, $opt, $value, '', '', false);
						break;
					}
					case 'radio': {
						if (!isset($value)) {
							break;
						}
						$this->RadioButton($name, $w, $prop, $opt, $value, $checked, '', '', false);
						break;
					}
					case 'submit': {
						if (!isset($value)) {
							$value = 'submit';
						}
						$w = $this->GetStringWidth($value) * 1.5;
						$h *= 1.6;
						$prop = array('lineWidth'=>1, 'borderStyle'=>'beveled', 'fillColor'=>array(196, 196, 196), 'strokeColor'=>array(255, 255, 255));
						$action = array();
						$action['S'] = 'SubmitForm';
						$action['F'] = $this->form_action;
						if ($this->form_enctype != 'FDF') {
							$action['Flags'] = array('ExportFormat');
						}
						if ($this->form_mode == 'get') {
							$action['Flags'] = array('GetMethod');
						}
						$this->Button($name, $w, $h, $value, $action, $prop, $opt, '', '', false);
						break;
					}
					case 'reset': {
						if (!isset($value)) {
							$value = 'reset';
						}
						$w = $this->GetStringWidth($value) * 1.5;
						$h *= 1.6;
						$prop = array('lineWidth'=>1, 'borderStyle'=>'beveled', 'fillColor'=>array(196, 196, 196), 'strokeColor'=>array(255, 255, 255));
						$this->Button($name, $w, $h, $value, array('S'=>'ResetForm'), $prop, $opt, '', '', false);
						break;
					}
					case 'file': {
						$prop['fileSelect'] = 'true';
						$this->TextField($name, $w, $h, $prop, $opt, '', '', false);
						if (!isset($value)) {
							$value = '*';
						}
						$w = $this->GetStringWidth($value) * 2;
						$h *= 1.2;
						$prop = array('lineWidth'=>1, 'borderStyle'=>'beveled', 'fillColor'=>array(196, 196, 196), 'strokeColor'=>array(255, 255, 255));
						$jsaction = 'var f=this.getField(\''.$name.'\'); f.browseForFileToSubmit();';
						$this->Button('FB_'.$name, $w, $h, $value, $jsaction, $prop, $opt, '', '', false);
						break;
					}
					case 'hidden': {
						if (isset($value)) {
							$opt['v'] = $value;
						}
						$opt['f'] = array('invisible', 'hidden');
						$this->TextField($name, 0, 0, $prop, $opt, '', '', false);
						break;
					}
					case 'image': {
						// THIS TYPE MUST BE FIXED
						if (isset($tag['attribute']['src']) AND !TCPDF_STATIC::empty_string($tag['attribute']['src'])) {
							$img = $tag['attribute']['src'];
						} else {
							break;
						}
						$value = 'img';
						//$opt['mk'] = array('i'=>$img, 'tp'=>1, 'if'=>array('sw'=>'A', 's'=>'A', 'fb'=>false));
						if (isset($tag['attribute']['onclick']) AND !empty($tag['attribute']['onclick'])) {
							$jsaction = $tag['attribute']['onclick'];
						} else {
							$jsaction = '';
						}
						$this->Button($name, $w, $h, $value, $jsaction, $prop, $opt, '', '', false);
						break;
					}
					case 'button': {
						if (!isset($value)) {
							$value = ' ';
						}
						$w = $this->GetStringWidth($value) * 1.5;
						$h *= 1.6;
						$prop = array('lineWidth'=>1, 'borderStyle'=>'beveled', 'fillColor'=>array(196, 196, 196), 'strokeColor'=>array(255, 255, 255));
						if (isset($tag['attribute']['onclick']) AND !empty($tag['attribute']['onclick'])) {
							$jsaction = $tag['attribute']['onclick'];
						} else {
							$jsaction = '';
						}
						$this->Button($name, $w, $h, $value, $jsaction, $prop, $opt, '', '', false);
						break;
					}
				}
				break;
			}
			case 'textarea': {
				$prop = array();
				$opt = array();
				if (isset($tag['attribute']['readonly']) AND !TCPDF_STATIC::empty_string($tag['attribute']['readonly'])) {
					$prop['readonly'] = true;
				}
				if (isset($tag['attribute']['name']) AND !TCPDF_STATIC::empty_string($tag['attribute']['name'])) {
					$name = $tag['attribute']['name'];
				} else {
					break;
				}
				if (isset($tag['attribute']['value']) AND !TCPDF_STATIC::empty_string($tag['attribute']['value'])) {
					$opt['v'] = $tag['attribute']['value'];
				}
				if (isset($tag['attribute']['cols']) AND !TCPDF_STATIC::empty_string($tag['attribute']['cols'])) {
					$w = intval($tag['attribute']['cols']) * $this->GetStringWidth(chr(32)) * 2;
				} else {
					$w = 40;
				}
				if (isset($tag['attribute']['rows']) AND !TCPDF_STATIC::empty_string($tag['attribute']['rows'])) {
					$h = intval($tag['attribute']['rows']) * $this->FontSize * $this->cell_height_ratio;
				} else {
					$h = 10;
				}
				$prop['multiline'] = 'true';
				$this->TextField($name, $w, $h, $prop, $opt, '', '', false);
				break;
			}
			case 'select': {
				$h = $this->FontSize * $this->cell_height_ratio;
				if (isset($tag['attribute']['size']) AND !TCPDF_STATIC::empty_string($tag['attribute']['size'])) {
					$h *= ($tag['attribute']['size'] + 1);
				}
				$prop = array();
				$opt = array();
				if (isset($tag['attribute']['name']) AND !TCPDF_STATIC::empty_string($tag['attribute']['name'])) {
					$name = $tag['attribute']['name'];
				} else {
					break;
				}
				$w = 0;
				if (isset($tag['attribute']['opt']) AND !TCPDF_STATIC::empty_string($tag['attribute']['opt'])) {
					$options = explode('#!NwL!#', $tag['attribute']['opt']);
					$values = array();
					foreach ($options as $val) {
						if (strpos($val, '#!TaB!#') !== false) {
							$opts = explode('#!TaB!#', $val);
							$values[] = $opts;
							$w = max($w, $this->GetStringWidth($opts[1]));
						} else {
							$values[] = $val;
							$w = max($w, $this->GetStringWidth($val));
						}
					}
				} else {
					break;
				}
				$w *= 2;
				if (isset($tag['attribute']['multiple']) AND ($tag['attribute']['multiple']='multiple')) {
					$prop['multipleSelection'] = 'true';
					$this->ListBox($name, $w, $h, $values, $prop, $opt, '', '', false);
				} else {
					$this->ComboBox($name, $w, $h, $values, $prop, $opt, '', '', false);
				}
				break;
			}
			case 'tcpdf': {
				if (defined('K_TCPDF_CALLS_IN_HTML') AND (K_TCPDF_CALLS_IN_HTML === true)) {
					// Special tag used to call TCPDF methods
					if (isset($tag['attribute']['method'])) {
						$tcpdf_method = $tag['attribute']['method'];
						if (method_exists($this, $tcpdf_method)) {
							if (isset($tag['attribute']['params']) AND (!empty($tag['attribute']['params']))) {
								$params = unserialize(urldecode($tag['attribute']['params']));
								call_user_func_array(array($this, $tcpdf_method), $params);
							} else {
								$this->$tcpdf_method();
							}
							$this->newline = true;
						}
					}
				}
				break;
			}
			default: {
				break;
			}
		}
		// define tags that support borders and background colors
		$bordertags = array('blockquote','br','dd','dl','div','dt','h1','h2','h3','h4','h5','h6','hr','li','ol','p','pre','ul','tcpdf','table');
		if (in_array($tag['value'], $bordertags)) {
			// set border
			$dom[$key]['borderposition'] = $this->getBorderStartPosition();
		}
		if ($dom[$key]['self'] AND isset($dom[$key]['attribute']['pagebreakafter'])) {
			$pba = $dom[$key]['attribute']['pagebreakafter'];
			// check for pagebreak
			if (($pba == 'true') OR ($pba == 'left') OR ($pba == 'right')) {
				// add a page (or trig AcceptPageBreak() for multicolumn mode)
				$this->checkPageBreak($this->PageBreakTrigger + 1);
			}
			if ((($pba == 'left') AND (((!$this->rtl) AND (($this->page % 2) == 0)) OR (($this->rtl) AND (($this->page % 2) != 0))))
				OR (($pba == 'right') AND (((!$this->rtl) AND (($this->page % 2) != 0)) OR (($this->rtl) AND (($this->page % 2) == 0))))) {
				// add a page (or trig AcceptPageBreak() for multicolumn mode)
				$this->checkPageBreak($this->PageBreakTrigger + 1);
			}
		}
		return $dom;
	}

	/**
	 * Process closing tags.
	 * @param $dom (array) html dom array
	 * @param $key (int) current element id
	 * @param $cell (boolean) if true add the default left (or right if RTL) padding to each new line (default false).
	 * @param $maxbottomliney (int) maximum y value of current line
	 * @return $dom array
	 * @protected
	 */
	protected function closeHTMLTagHandler($dom, $key, $cell, $maxbottomliney=0) {
		$tag = $dom[$key];
		$parent = $dom[($dom[$key]['parent'])];
		$lasttag = ((!isset($dom[($key + 1)])) OR ((!isset($dom[($key + 2)])) AND ($dom[($key + 1)]['value'] == 'marker')));
		$in_table_head = false;
		// maximum x position (used to draw borders)
		if ($this->rtl) {
			$xmax = $this->w;
		} else {
			$xmax = 0;
		}
		if ($tag['block']) {
			$hbz = 0; // distance from y to line bottom
			$hb = 0; // vertical space between block tags
			// calculate vertical space for block tags
			if (isset($this->tagvspaces[$tag['value']][1]['h']) AND ($this->tagvspaces[$tag['value']][1]['h'] >= 0)) {
				$pre_h = $this->tagvspaces[$tag['value']][1]['h'];
			} elseif (isset($parent['fontsize'])) {
				$pre_h = (($parent['fontsize'] / $this->k) * $this->cell_height_ratio);
			} else {
				$pre_h = $this->FontSize * $this->cell_height_ratio;
			}
			if (isset($this->tagvspaces[$tag['value']][1]['n'])) {
				$n = $this->tagvspaces[$tag['value']][1]['n'];
			} elseif (preg_match('/[h][0-9]/', $tag['value']) > 0) {
				$n = 0.6;
			} else {
				$n = 1;
			}
			if ((!isset($this->tagvspaces[$tag['value']])) AND ($tag['value'] == 'div')) {
				$hb = 0;
			} else {
				$hb = ($n * $pre_h);
			}
			if ($maxbottomliney > $this->PageBreakTrigger) {
				$hbz = ($this->FontSize * $this->cell_height_ratio);
			} elseif ($this->y < $maxbottomliney) {
				$hbz = ($maxbottomliney - $this->y);
			}
		}
		// Closing tag
		switch($tag['value']) {
			case 'tr': {
				$table_el = $dom[($dom[$key]['parent'])]['parent'];
				if (!isset($parent['endy'])) {
					$dom[($dom[$key]['parent'])]['endy'] = $this->y;
					$parent['endy'] = $this->y;
				}
				if (!isset($parent['endpage'])) {
					$dom[($dom[$key]['parent'])]['endpage'] = $this->page;
					$parent['endpage'] = $this->page;
				}
				if (!isset($parent['endcolumn'])) {
					$dom[($dom[$key]['parent'])]['endcolumn'] = $this->current_column;
					$parent['endcolumn'] = $this->current_column;
				}
				// update row-spanned cells
				if (isset($dom[$table_el]['rowspans'])) {
					foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
						$dom[$table_el]['rowspans'][$k]['rowspan'] -= 1;
						if ($dom[$table_el]['rowspans'][$k]['rowspan'] == 0) {
							if (($dom[$table_el]['rowspans'][$k]['endpage'] == $parent['endpage']) AND ($dom[$table_el]['rowspans'][$k]['endcolumn'] == $parent['endcolumn'])) {
								$dom[($dom[$key]['parent'])]['endy'] = max($dom[$table_el]['rowspans'][$k]['endy'], $parent['endy']);
							} elseif (($dom[$table_el]['rowspans'][$k]['endpage'] > $parent['endpage']) OR ($dom[$table_el]['rowspans'][$k]['endcolumn'] > $parent['endcolumn'])) {
								$dom[($dom[$key]['parent'])]['endy'] = $dom[$table_el]['rowspans'][$k]['endy'];
								$dom[($dom[$key]['parent'])]['endpage'] = $dom[$table_el]['rowspans'][$k]['endpage'];
								$dom[($dom[$key]['parent'])]['endcolumn'] = $dom[$table_el]['rowspans'][$k]['endcolumn'];
							}
						}
					}
					// report new endy and endpage to the rowspanned cells
					foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
						if ($dom[$table_el]['rowspans'][$k]['rowspan'] == 0) {
							$dom[$table_el]['rowspans'][$k]['endpage'] = max($dom[$table_el]['rowspans'][$k]['endpage'], $dom[($dom[$key]['parent'])]['endpage']);
							$dom[($dom[$key]['parent'])]['endpage'] = $dom[$table_el]['rowspans'][$k]['endpage'];
							$dom[$table_el]['rowspans'][$k]['endcolumn'] = max($dom[$table_el]['rowspans'][$k]['endcolumn'], $dom[($dom[$key]['parent'])]['endcolumn']);
							$dom[($dom[$key]['parent'])]['endcolumn'] = $dom[$table_el]['rowspans'][$k]['endcolumn'];
							$dom[$table_el]['rowspans'][$k]['endy'] = max($dom[$table_el]['rowspans'][$k]['endy'], $dom[($dom[$key]['parent'])]['endy']);
							$dom[($dom[$key]['parent'])]['endy'] = $dom[$table_el]['rowspans'][$k]['endy'];
						}
					}
					// update remaining rowspanned cells
					foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
						if ($dom[$table_el]['rowspans'][$k]['rowspan'] == 0) {
							$dom[$table_el]['rowspans'][$k]['endpage'] = $dom[($dom[$key]['parent'])]['endpage'];
							$dom[$table_el]['rowspans'][$k]['endcolumn'] = $dom[($dom[$key]['parent'])]['endcolumn'];
							$dom[$table_el]['rowspans'][$k]['endy'] = $dom[($dom[$key]['parent'])]['endy'];
						}
					}
				}
				$this->setPage($dom[($dom[$key]['parent'])]['endpage']);
				if ($this->num_columns > 1) {
					$this->selectColumn($dom[($dom[$key]['parent'])]['endcolumn']);
				}
				$this->y = $dom[($dom[$key]['parent'])]['endy'];
				if (isset($dom[$table_el]['attribute']['cellspacing'])) {
					$this->y += $this->getHTMLUnitToUnits($dom[$table_el]['attribute']['cellspacing'], 1, 'px');
				} elseif (isset($dom[$table_el]['border-spacing'])) {
					$this->y += $dom[$table_el]['border-spacing']['V'];
				}
				$this->Ln(0, $cell);
				if ($this->current_column == $parent['startcolumn']) {
					$this->x = $parent['startx'];
				}
				// account for booklet mode
				if ($this->page > $parent['startpage']) {
					if (($this->rtl) AND ($this->pagedim[$this->page]['orm'] != $this->pagedim[$parent['startpage']]['orm'])) {
						$this->x -= ($this->pagedim[$this->page]['orm'] - $this->pagedim[$parent['startpage']]['orm']);
					} elseif ((!$this->rtl) AND ($this->pagedim[$this->page]['olm'] != $this->pagedim[$parent['startpage']]['olm'])) {
						$this->x += ($this->pagedim[$this->page]['olm'] - $this->pagedim[$parent['startpage']]['olm']);
					}
				}
				break;
			}
			case 'tablehead':
				// closing tag used for the thead part
				$in_table_head = true;
				$this->inthead = false;
			case 'table': {
				$table_el = $parent;
				// set default border
				if (isset($table_el['attribute']['border']) AND ($table_el['attribute']['border'] > 0)) {
					// set default border
					$border = array('LTRB' => array('width' => $this->getCSSBorderWidth($table_el['attribute']['border']), 'cap'=>'square', 'join'=>'miter', 'dash'=> 0, 'color'=>array(0,0,0)));
				} else {
					$border = 0;
				}
				$default_border = $border;
				// fix bottom line alignment of last line before page break
				foreach ($dom[($dom[$key]['parent'])]['trids'] as $j => $trkey) {
					// update row-spanned cells
					if (isset($dom[($dom[$key]['parent'])]['rowspans'])) {
						foreach ($dom[($dom[$key]['parent'])]['rowspans'] as $k => $trwsp) {
							if (isset($prevtrkey) AND ($trwsp['trid'] == $prevtrkey) AND ($trwsp['mrowspan'] > 0)) {
								$dom[($dom[$key]['parent'])]['rowspans'][$k]['trid'] = $trkey;
							}
							if ($dom[($dom[$key]['parent'])]['rowspans'][$k]['trid'] == $trkey) {
								$dom[($dom[$key]['parent'])]['rowspans'][$k]['mrowspan'] -= 1;
							}
						}
					}
					if (isset($prevtrkey) AND ($dom[$trkey]['startpage'] > $dom[$prevtrkey]['endpage'])) {
						$pgendy = $this->pagedim[$dom[$prevtrkey]['endpage']]['hk'] - $this->pagedim[$dom[$prevtrkey]['endpage']]['bm'];
						$dom[$prevtrkey]['endy'] = $pgendy;
						// update row-spanned cells
						if (isset($dom[($dom[$key]['parent'])]['rowspans'])) {
							foreach ($dom[($dom[$key]['parent'])]['rowspans'] as $k => $trwsp) {
								if (($trwsp['trid'] == $trkey) AND ($trwsp['mrowspan'] > 1) AND ($trwsp['endpage'] == $dom[$prevtrkey]['endpage'])) {
									$dom[($dom[$key]['parent'])]['rowspans'][$k]['endy'] = $pgendy;
									$dom[($dom[$key]['parent'])]['rowspans'][$k]['mrowspan'] = -1;
								}
							}
						}
					}
					$prevtrkey = $trkey;
					$table_el = $dom[($dom[$key]['parent'])];
				}
				// for each row
				if (count($table_el['trids']) > 0) {
					unset($xmax);
				}
				foreach ($table_el['trids'] as $j => $trkey) {
					$parent = $dom[$trkey];
					if (!isset($xmax)) {
						$xmax = $parent['cellpos'][(count($parent['cellpos']) - 1)]['endx'];
					}
					// for each cell on the row
					foreach ($parent['cellpos'] as $k => $cellpos) {
						if (isset($cellpos['rowspanid']) AND ($cellpos['rowspanid'] >= 0)) {
							$cellpos['startx'] = $table_el['rowspans'][($cellpos['rowspanid'])]['startx'];
							$cellpos['endx'] = $table_el['rowspans'][($cellpos['rowspanid'])]['endx'];
							$endy = $table_el['rowspans'][($cellpos['rowspanid'])]['endy'];
							$startpage = $table_el['rowspans'][($cellpos['rowspanid'])]['startpage'];
							$endpage = $table_el['rowspans'][($cellpos['rowspanid'])]['endpage'];
							$startcolumn = $table_el['rowspans'][($cellpos['rowspanid'])]['startcolumn'];
							$endcolumn = $table_el['rowspans'][($cellpos['rowspanid'])]['endcolumn'];
						} else {
							$endy = $parent['endy'];
							$startpage = $parent['startpage'];
							$endpage = $parent['endpage'];
							$startcolumn = $parent['startcolumn'];
							$endcolumn = $parent['endcolumn'];
						}
						if ($this->num_columns == 0) {
							$this->num_columns = 1;
						}
						if (isset($cellpos['border'])) {
							$border = $cellpos['border'];
						}
						if (isset($cellpos['bgcolor']) AND ($cellpos['bgcolor']) !== false) {
							$this->SetFillColorArray($cellpos['bgcolor']);
							$fill = true;
						} else {
							$fill = false;
						}
						$x = $cellpos['startx'];
						$y = $parent['starty'];
						$starty = $y;
						$w = abs($cellpos['endx'] - $cellpos['startx']);
						// get border modes
						$border_start = TCPDF_STATIC::getBorderMode($border, $position='start', $this->opencell);
						$border_end = TCPDF_STATIC::getBorderMode($border, $position='end', $this->opencell);
						$border_middle = TCPDF_STATIC::getBorderMode($border, $position='middle', $this->opencell);
						// design borders around HTML cells.
						for ($page = $startpage; $page <= $endpage; ++$page) { // for each page
							$ccode = '';
							$this->setPage($page);
							if ($this->num_columns < 2) {
								// single-column mode
								$this->x = $x;
								$this->y = $this->tMargin;
							}
							// account for margin changes
							if ($page > $startpage) {
								if (($this->rtl) AND ($this->pagedim[$page]['orm'] != $this->pagedim[$startpage]['orm'])) {
									$this->x -= ($this->pagedim[$page]['orm'] - $this->pagedim[$startpage]['orm']);
								} elseif ((!$this->rtl) AND ($this->pagedim[$page]['olm'] != $this->pagedim[$startpage]['olm'])) {
									$this->x += ($this->pagedim[$page]['olm'] - $this->pagedim[$startpage]['olm']);
								}
							}
							if ($startpage == $endpage) { // single page
								$deltacol = 0;
								$deltath = 0;
								for ($column = $startcolumn; $column <= $endcolumn; ++$column) { // for each column
									$this->selectColumn($column);
									if ($startcolumn == $endcolumn) { // single column
										$cborder = $border;
										$h = $endy - $parent['starty'];
										$this->y = $y;
										$this->x = $x;
									} elseif ($column == $startcolumn) { // first column
										$cborder = $border_start;
										$this->y = $starty;
										$this->x = $x;
										$h = $this->h - $this->y - $this->bMargin;
										if ($this->rtl) {
											$deltacol = $this->x + $this->rMargin - $this->w;
										} else {
											$deltacol = $this->x - $this->lMargin;
										}
									} elseif ($column == $endcolumn) { // end column
										$cborder = $border_end;
										if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
											$this->y = $this->columns[$column]['th']['\''.$page.'\''];
										}
										$this->x += $deltacol;
										$h = $endy - $this->y;
									} else { // middle column
										$cborder = $border_middle;
										if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
											$this->y = $this->columns[$column]['th']['\''.$page.'\''];
										}
										$this->x += $deltacol;
										$h = $this->h - $this->y - $this->bMargin;
									}
									$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
								} // end for each column
							} elseif ($page == $startpage) { // first page
								$deltacol = 0;
								$deltath = 0;
								for ($column = $startcolumn; $column < $this->num_columns; ++$column) { // for each column
									$this->selectColumn($column);
									if ($column == $startcolumn) { // first column
										$cborder = $border_start;
										$this->y = $starty;
										$this->x = $x;
										$h = $this->h - $this->y - $this->bMargin;
										if ($this->rtl) {
											$deltacol = $this->x + $this->rMargin - $this->w;
										} else {
											$deltacol = $this->x - $this->lMargin;
										}
									} else { // middle column
										$cborder = $border_middle;
										if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
											$this->y = $this->columns[$column]['th']['\''.$page.'\''];
										}
										$this->x += $deltacol;
										$h = $this->h - $this->y - $this->bMargin;
									}
									$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
								} // end for each column
							} elseif ($page == $endpage) { // last page
								$deltacol = 0;
								$deltath = 0;
								for ($column = 0; $column <= $endcolumn; ++$column) { // for each column
									$this->selectColumn($column);
									if ($column == $endcolumn) { // end column
										$cborder = $border_end;
										if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
											$this->y = $this->columns[$column]['th']['\''.$page.'\''];
										}
										$this->x += $deltacol;
										$h = $endy - $this->y;
									} else { // middle column
										$cborder = $border_middle;
										if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
											$this->y = $this->columns[$column]['th']['\''.$page.'\''];
										}
										$this->x += $deltacol;
										$h = $this->h - $this->y - $this->bMargin;
									}
									$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
								} // end for each column
							} else { // middle page
								$deltacol = 0;
								$deltath = 0;
								for ($column = 0; $column < $this->num_columns; ++$column) { // for each column
									$this->selectColumn($column);
									$cborder = $border_middle;
									if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
										$this->y = $this->columns[$column]['th']['\''.$page.'\''];
									}
									$this->x += $deltacol;
									$h = $this->h - $this->y - $this->bMargin;
									$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
								} // end for each column
							}
							if ($cborder OR $fill) {
								$offsetlen = strlen($ccode);
								// draw border and fill
								if ($this->inxobj) {
									// we are inside an XObject template
									if (end($this->xobjects[$this->xobjid]['transfmrk']) !== false) {
										$pagemarkkey = key($this->xobjects[$this->xobjid]['transfmrk']);
										$pagemark = $this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey];
										$this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey] += $offsetlen;
									} else {
										$pagemark = $this->xobjects[$this->xobjid]['intmrk'];
										$this->xobjects[$this->xobjid]['intmrk'] += $offsetlen;
									}
									$pagebuff = $this->xobjects[$this->xobjid]['outdata'];
									$pstart = substr($pagebuff, 0, $pagemark);
									$pend = substr($pagebuff, $pagemark);
									$this->xobjects[$this->xobjid]['outdata'] = $pstart.$ccode.$pend;
								} else {
									// draw border and fill
									if (end($this->transfmrk[$this->page]) !== false) {
										$pagemarkkey = key($this->transfmrk[$this->page]);
										$pagemark = $this->transfmrk[$this->page][$pagemarkkey];
									} elseif ($this->InFooter) {
										$pagemark = $this->footerpos[$this->page];
									} else {
										$pagemark = $this->intmrk[$this->page];
									}
									$pagebuff = $this->getPageBuffer($this->page);
									$pstart = substr($pagebuff, 0, $pagemark);
									$pend = substr($pagebuff, $pagemark);
									$this->setPageBuffer($this->page, $pstart.$ccode.$pend);
								}
							}
						} // end for each page
						// restore default border
						$border = $default_border;
					} // end for each cell on the row
					if (isset($table_el['attribute']['cellspacing'])) {
						$this->y += $this->getHTMLUnitToUnits($table_el['attribute']['cellspacing'], 1, 'px');
					} elseif (isset($table_el['border-spacing'])) {
						$this->y += $table_el['border-spacing']['V'];
					}
					$this->Ln(0, $cell);
					$this->x = $parent['startx'];
					if ($endpage > $startpage) {
						if (($this->rtl) AND ($this->pagedim[$endpage]['orm'] != $this->pagedim[$startpage]['orm'])) {
							$this->x += ($this->pagedim[$endpage]['orm'] - $this->pagedim[$startpage]['orm']);
						} elseif ((!$this->rtl) AND ($this->pagedim[$endpage]['olm'] != $this->pagedim[$startpage]['olm'])) {
							$this->x += ($this->pagedim[$endpage]['olm'] - $this->pagedim[$startpage]['olm']);
						}
					}
				}
				if (!$in_table_head) { // we are not inside a thead section
					$this->cell_padding = $table_el['old_cell_padding'];
					// reset row height
					$this->resetLastH();
					if (($this->page == ($this->numpages - 1)) AND ($this->pageopen[$this->numpages])) {
						$plendiff = ($this->pagelen[$this->numpages] - $this->emptypagemrk[$this->numpages]);
						if (($plendiff > 0) AND ($plendiff < 60)) {
							$pagediff = substr($this->getPageBuffer($this->numpages), $this->emptypagemrk[$this->numpages], $plendiff);
							if (substr($pagediff, 0, 5) == 'BT /F') {
								// the difference is only a font setting
								$plendiff = 0;
							}
						}
						if ($plendiff == 0) {
							// remove last blank page
							$this->deletePage($this->numpages);
						}
					}
					if (isset($this->theadMargins['top'])) {
						// restore top margin
						$this->tMargin = $this->theadMargins['top'];
					}
					if (!isset($table_el['attribute']['nested']) OR ($table_el['attribute']['nested'] != 'true')) {
						// reset main table header
						$this->thead = '';
						$this->theadMargins = array();
						$this->pagedim[$this->page]['tm'] = $this->tMargin;
					}
				}
				$parent = $table_el;
				break;
			}
			case 'a': {
				$this->HREF = '';
				break;
			}
			case 'sup': {
				$this->SetXY($this->GetX(), $this->GetY() + ((0.7 * $parent['fontsize']) / $this->k));
				break;
			}
			case 'sub': {
				$this->SetXY($this->GetX(), $this->GetY() - ((0.3 * $parent['fontsize']) / $this->k));
				break;
			}
			case 'div': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				break;
			}
			case 'blockquote': {
				if ($this->rtl) {
					$this->rMargin -= $this->listindent;
				} else {
					$this->lMargin -= $this->listindent;
				}
				--$this->listindentlevel;
				$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				break;
			}
			case 'p': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				break;
			}
			case 'pre': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				$this->premode = false;
				break;
			}
			case 'dl': {
				--$this->listnum;
				if ($this->listnum <= 0) {
					$this->listnum = 0;
					$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				} else {
					$this->addHTMLVertSpace(0, 0, $cell, false, $lasttag);
				}
				$this->resetLastH();
				break;
			}
			case 'dt': {
				$this->lispacer = '';
				$this->addHTMLVertSpace(0, 0, $cell, false, $lasttag);
				break;
			}
			case 'dd': {
				$this->lispacer = '';
				if ($this->rtl) {
					$this->rMargin -= $this->listindent;
				} else {
					$this->lMargin -= $this->listindent;
				}
				--$this->listindentlevel;
				$this->addHTMLVertSpace(0, 0, $cell, false, $lasttag);
				break;
			}
			case 'ul':
			case 'ol': {
				--$this->listnum;
				$this->lispacer = '';
				if ($this->rtl) {
					$this->rMargin -= $this->listindent;
				} else {
					$this->lMargin -= $this->listindent;
				}
				--$this->listindentlevel;
				if ($this->listnum <= 0) {
					$this->listnum = 0;
					$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				} else {
					$this->addHTMLVertSpace(0, 0, $cell, false, $lasttag);
				}
				$this->resetLastH();
				break;
			}
			case 'li': {
				$this->lispacer = '';
				$this->addHTMLVertSpace(0, 0, $cell, false, $lasttag);
				break;
			}
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				break;
			}
			// Form fields (since 4.8.000 - 2009-09-07)
			case 'form': {
				$this->form_action = '';
				$this->form_enctype = 'application/x-www-form-urlencoded';
				break;
			}
			default : {
				break;
			}
		}
		// draw border and background (if any)
		$this->drawHTMLTagBorder($parent, $xmax);
		if (isset($dom[($dom[$key]['parent'])]['attribute']['pagebreakafter'])) {
			$pba = $dom[($dom[$key]['parent'])]['attribute']['pagebreakafter'];
			// check for pagebreak
			if (($pba == 'true') OR ($pba == 'left') OR ($pba == 'right')) {
				// add a page (or trig AcceptPageBreak() for multicolumn mode)
				$this->checkPageBreak($this->PageBreakTrigger + 1);
			}
			if ((($pba == 'left') AND (((!$this->rtl) AND (($this->page % 2) == 0)) OR (($this->rtl) AND (($this->page % 2) != 0))))
				OR (($pba == 'right') AND (((!$this->rtl) AND (($this->page % 2) != 0)) OR (($this->rtl) AND (($this->page % 2) == 0))))) {
				// add a page (or trig AcceptPageBreak() for multicolumn mode)
				$this->checkPageBreak($this->PageBreakTrigger + 1);
			}
		}
		$this->tmprtl = false;
		return $dom;
	}

	/**
	 * Add vertical spaces if needed.
	 * @param $hbz (string) Distance between current y and line bottom.
	 * @param $hb (string) The height of the break.
	 * @param $cell (boolean) if true add the default left (or right if RTL) padding to each new line (default false).
	 * @param $firsttag (boolean) set to true when the tag is the first.
	 * @param $lasttag (boolean) set to true when the tag is the last.
	 * @protected
	 */
	protected function addHTMLVertSpace($hbz=0, $hb=0, $cell=false, $firsttag=false, $lasttag=false) {
		if ($firsttag) {
			$this->Ln(0, $cell);
			$this->htmlvspace = 0;
			return;
		}
		if ($lasttag) {
			$this->Ln($hbz, $cell);
			$this->htmlvspace = 0;
			return;
		}
		if ($hb < $this->htmlvspace) {
			$hd = 0;
		} else {
			$hd = $hb - $this->htmlvspace;
			$this->htmlvspace = $hb;
		}
		$this->Ln(($hbz + $hd), $cell);
	}

	/**
	 * Return the starting coordinates to draw an html border
	 * @return array containing top-left border coordinates
	 * @protected
	 * @since 5.7.000 (2010-08-03)
	 */
	protected function getBorderStartPosition() {
		if ($this->rtl) {
			$xmax = $this->lMargin;
		} else {
			$xmax = $this->w - $this->rMargin;
		}
		return array('page' => $this->page, 'column' => $this->current_column, 'x' => $this->x, 'y' => $this->y, 'xmax' => $xmax);
	}

	/**
	 * Draw an HTML block border and fill
	 * @param $tag (array) array of tag properties.
	 * @param $xmax (int) end X coordinate for border.
	 * @protected
	 * @since 5.7.000 (2010-08-03)
	 */
	protected function drawHTMLTagBorder($tag, $xmax) {
		if (!isset($tag['borderposition'])) {
			// nothing to draw
			return;
		}
		$prev_x = $this->x;
		$prev_y = $this->y;
		$prev_lasth = $this->lasth;
		$border = 0;
		$fill = false;
		$this->lasth = 0;
		if (isset($tag['border']) AND !empty($tag['border'])) {
			// get border style
			$border = $tag['border'];
			if (!TCPDF_STATIC::empty_string($this->thead) AND (!$this->inthead)) {
				// border for table header
				$border = TCPDF_STATIC::getBorderMode($border, $position='middle', $this->opencell);
			}
		}
		if (isset($tag['bgcolor']) AND ($tag['bgcolor'] !== false)) {
			// get background color
			$old_bgcolor = $this->bgcolor;
			$this->SetFillColorArray($tag['bgcolor']);
			$fill = true;
		}
		if (!$border AND !$fill) {
			// nothing to draw
			return;
		}
		if (isset($tag['attribute']['cellspacing'])) {
			$clsp = $this->getHTMLUnitToUnits($tag['attribute']['cellspacing'], 1, 'px');
			$cellspacing = array('H' => $clsp, 'V' => $clsp);
		} elseif (isset($tag['border-spacing'])) {
			$cellspacing = $tag['border-spacing'];
		} else {
			$cellspacing = array('H' => 0, 'V' => 0);
		}
		if (($tag['value'] != 'table') AND (is_array($border)) AND (!empty($border))) {
			// draw the border externally respect the sqare edge.
			$border['mode'] = 'ext';
		}
		if ($this->rtl) {
			if ($xmax >= $tag['borderposition']['x']) {
				$xmax = $tag['borderposition']['xmax'];
			}
			$w = ($tag['borderposition']['x'] - $xmax);
		} else {
			if ($xmax <= $tag['borderposition']['x']) {
				$xmax = $tag['borderposition']['xmax'];
			}
			$w = ($xmax - $tag['borderposition']['x']);
		}
		if ($w <= 0) {
			return;
		}
		$w += $cellspacing['H'];
		$startpage = $tag['borderposition']['page'];
		$startcolumn = $tag['borderposition']['column'];
		$x = $tag['borderposition']['x'];
		$y = $tag['borderposition']['y'];
		$endpage = $this->page;
		$starty = $tag['borderposition']['y'] - $cellspacing['V'];
		$currentY = $this->y;
		$this->x = $x;
		// get latest column
		$endcolumn = $this->current_column;
		if ($this->num_columns == 0) {
			$this->num_columns = 1;
		}
		// get border modes
		$border_start = TCPDF_STATIC::getBorderMode($border, $position='start', $this->opencell);
		$border_end = TCPDF_STATIC::getBorderMode($border, $position='end', $this->opencell);
		$border_middle = TCPDF_STATIC::getBorderMode($border, $position='middle', $this->opencell);
		// temporary disable page regions
		$temp_page_regions = $this->page_regions;
		$this->page_regions = array();
		// design borders around HTML cells.
		for ($page = $startpage; $page <= $endpage; ++$page) { // for each page
			$ccode = '';
			$this->setPage($page);
			if ($this->num_columns < 2) {
				// single-column mode
				$this->x = $x;
				$this->y = $this->tMargin;
			}
			// account for margin changes
			if ($page > $startpage) {
				if (($this->rtl) AND ($this->pagedim[$page]['orm'] != $this->pagedim[$startpage]['orm'])) {
					$this->x -= ($this->pagedim[$page]['orm'] - $this->pagedim[$startpage]['orm']);
				} elseif ((!$this->rtl) AND ($this->pagedim[$page]['olm'] != $this->pagedim[$startpage]['olm'])) {
					$this->x += ($this->pagedim[$page]['olm'] - $this->pagedim[$startpage]['olm']);
				}
			}
			if ($startpage == $endpage) {
				// single page
				for ($column = $startcolumn; $column <= $endcolumn; ++$column) { // for each column
					$this->selectColumn($column);
					if ($startcolumn == $endcolumn) { // single column
						$cborder = $border;
						$h = ($currentY - $y) + $cellspacing['V'];
						$this->y = $starty;
					} elseif ($column == $startcolumn) { // first column
						$cborder = $border_start;
						$this->y = $starty;
						$h = $this->h - $this->y - $this->bMargin;
					} elseif ($column == $endcolumn) { // end column
						$cborder = $border_end;
						$h = $currentY - $this->y;
					} else { // middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} elseif ($page == $startpage) { // first page
				for ($column = $startcolumn; $column < $this->num_columns; ++$column) { // for each column
					$this->selectColumn($column);
					if ($column == $startcolumn) { // first column
						$cborder = $border_start;
						$this->y = $starty;
						$h = $this->h - $this->y - $this->bMargin;
					} else { // middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} elseif ($page == $endpage) { // last page
				for ($column = 0; $column <= $endcolumn; ++$column) { // for each column
					$this->selectColumn($column);
					if ($column == $endcolumn) {
						// end column
						$cborder = $border_end;
						$h = $currentY - $this->y;
					} else {
						// middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} else { // middle page
				for ($column = 0; $column < $this->num_columns; ++$column) { // for each column
					$this->selectColumn($column);
					$cborder = $border_middle;
					$h = $this->h - $this->y - $this->bMargin;
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			}
			if ($cborder OR $fill) {
				$offsetlen = strlen($ccode);
				// draw border and fill
				if ($this->inxobj) {
					// we are inside an XObject template
					if (end($this->xobjects[$this->xobjid]['transfmrk']) !== false) {
						$pagemarkkey = key($this->xobjects[$this->xobjid]['transfmrk']);
						$pagemark = $this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey];
						$this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey] += $offsetlen;
					} else {
						$pagemark = $this->xobjects[$this->xobjid]['intmrk'];
						$this->xobjects[$this->xobjid]['intmrk'] += $offsetlen;
					}
					$pagebuff = $this->xobjects[$this->xobjid]['outdata'];
					$pstart = substr($pagebuff, 0, $pagemark);
					$pend = substr($pagebuff, $pagemark);
					$this->xobjects[$this->xobjid]['outdata'] = $pstart.$ccode.$pend;
				} else {
					if (end($this->transfmrk[$this->page]) !== false) {
						$pagemarkkey = key($this->transfmrk[$this->page]);
						$pagemark = $this->transfmrk[$this->page][$pagemarkkey];
					} elseif ($this->InFooter) {
						$pagemark = $this->footerpos[$this->page];
					} else {
						$pagemark = $this->intmrk[$this->page];
					}
					$pagebuff = $this->getPageBuffer($this->page);
					$pstart = substr($pagebuff, 0, $pagemark);
					$pend = substr($pagebuff, $pagemark);
					$this->setPageBuffer($this->page, $pstart.$ccode.$pend);
					$this->bordermrk[$this->page] += $offsetlen;
					$this->cntmrk[$this->page] += $offsetlen;
				}
			}
		} // end for each page
		// restore page regions
		$this->page_regions = $temp_page_regions;
		if (isset($old_bgcolor)) {
			// restore background color
			$this->SetFillColorArray($old_bgcolor);
		}
		// restore pointer position
		$this->x = $prev_x;
		$this->y = $prev_y;
		$this->lasth = $prev_lasth;
	}

	/**
	 * Set the default bullet to be used as LI bullet symbol
	 * @param $symbol (string) character or string to be used (legal values are: '' = automatic, '!' = auto bullet, '#' = auto numbering, 'disc', 'disc', 'circle', 'square', '1', 'decimal', 'decimal-leading-zero', 'i', 'lower-roman', 'I', 'upper-roman', 'a', 'lower-alpha', 'lower-latin', 'A', 'upper-alpha', 'upper-latin', 'lower-greek', 'img|type|width|height|image.ext')
	 * @public
	 * @since 4.0.028 (2008-09-26)
	 */
	public function setLIsymbol($symbol='!') {
		// check for custom image symbol
		if (substr($symbol, 0, 4) == 'img|') {
			$this->lisymbol = $symbol;
			return;
		}
		$symbol = strtolower($symbol);
		$valid_symbols = array('!', '#', 'disc', 'circle', 'square', '1', 'decimal', 'decimal-leading-zero', 'i', 'lower-roman', 'I', 'upper-roman', 'a', 'lower-alpha', 'lower-latin', 'A', 'upper-alpha', 'upper-latin', 'lower-greek');
		if (in_array($symbol, $valid_symbols)) {
			$this->lisymbol = $symbol;
		} else {
			$this->lisymbol = '';
		}
	}

	/**
	 * Set the booklet mode for double-sided pages.
	 * @param $booklet (boolean) true set the booklet mode on, false otherwise.
	 * @param $inner (float) Inner page margin.
	 * @param $outer (float) Outer page margin.
	 * @public
	 * @since 4.2.000 (2008-10-29)
	 */
	public function SetBooklet($booklet=true, $inner=-1, $outer=-1) {
		$this->booklet = $booklet;
		if ($inner >= 0) {
			$this->lMargin = $inner;
		}
		if ($outer >= 0) {
			$this->rMargin = $outer;
		}
	}

	/**
	 * Swap the left and right margins.
	 * @param $reverse (boolean) if true swap left and right margins.
	 * @protected
	 * @since 4.2.000 (2008-10-29)
	 */
	protected function swapMargins($reverse=true) {
		if ($reverse) {
			// swap left and right margins
			$mtemp = $this->original_lMargin;
			$this->original_lMargin = $this->original_rMargin;
			$this->original_rMargin = $mtemp;
			$deltam = $this->original_lMargin - $this->original_rMargin;
			$this->lMargin += $deltam;
			$this->rMargin -= $deltam;
		}
	}

	/**
	 * Set the vertical spaces for HTML tags.
	 * The array must have the following structure (example):
	 * $tagvs = array('h1' => array(0 => array('h' => '', 'n' => 2), 1 => array('h' => 1.3, 'n' => 1)));
	 * The first array level contains the tag names,
	 * the second level contains 0 for opening tags or 1 for closing tags,
	 * the third level contains the vertical space unit (h) and the number spaces to add (n).
	 * If the h parameter is not specified, default values are used.
	 * @param $tagvs (array) array of tags and relative vertical spaces.
	 * @public
	 * @since 4.2.001 (2008-10-30)
	 */
	public function setHtmlVSpace($tagvs) {
		$this->tagvspaces = $tagvs;
	}

	/**
	 * Set custom width for list indentation.
	 * @param $width (float) width of the indentation. Use negative value to disable it.
	 * @public
	 * @since 4.2.007 (2008-11-12)
	 */
	public function setListIndentWidth($width) {
		return $this->customlistindent = floatval($width);
	}

	/**
	 * Set the top/bottom cell sides to be open or closed when the cell cross the page.
	 * @param $isopen (boolean) if true keeps the top/bottom border open for the cell sides that cross the page.
	 * @public
	 * @since 4.2.010 (2008-11-14)
	 */
	public function setOpenCell($isopen) {
		$this->opencell = $isopen;
	}

	/**
	 * Set the color and font style for HTML links.
	 * @param $color (array) RGB array of colors
	 * @param $fontstyle (string) additional font styles to add
	 * @public
	 * @since 4.4.003 (2008-12-09)
	 */
	public function setHtmlLinksStyle($color=array(0,0,255), $fontstyle='U') {
		$this->htmlLinkColorArray = $color;
		$this->htmlLinkFontStyle = $fontstyle;
	}

	/**
	 * Convert HTML string containing value and unit of measure to user's units or points.
	 * @param $htmlval (string) String containing values and unit.
	 * @param $refsize (string) Reference value in points.
	 * @param $defaultunit (string) Default unit (can be one of the following: %, em, ex, px, in, mm, pc, pt).
	 * @param $points (boolean) If true returns points, otherwise returns value in user's units.
	 * @return float value in user's unit or point if $points=true
	 * @public
	 * @since 4.4.004 (2008-12-10)
	 */
	public function getHTMLUnitToUnits($htmlval, $refsize=1, $defaultunit='px', $points=false) {
		$supportedunits = array('%', 'em', 'ex', 'px', 'in', 'cm', 'mm', 'pc', 'pt');
		$retval = 0;
		$value = 0;
		$unit = 'px';
		if ($points) {
			$k = 1;
		} else {
			$k = $this->k;
		}
		if (in_array($defaultunit, $supportedunits)) {
			$unit = $defaultunit;
		}
		if (is_numeric($htmlval)) {
			$value = floatval($htmlval);
		} elseif (preg_match('/([0-9\.\-\+]+)/', $htmlval, $mnum)) {
			$value = floatval($mnum[1]);
			if (preg_match('/([a-z%]+)/', $htmlval, $munit)) {
				if (in_array($munit[1], $supportedunits)) {
					$unit = $munit[1];
				}
			}
		}
		switch ($unit) {
			// percentage
			case '%': {
				$retval = (($value * $refsize) / 100);
				break;
			}
			// relative-size
			case 'em': {
				$retval = ($value * $refsize);
				break;
			}
			// height of lower case 'x' (about half the font-size)
			case 'ex': {
				$retval = ($value * ($refsize / 2));
				break;
			}
			// absolute-size
			case 'in': {
				$retval = (($value * $this->dpi) / $k);
				break;
			}
			// centimeters
			case 'cm': {
				$retval = (($value / 2.54 * $this->dpi) / $k);
				break;
			}
			// millimeters
			case 'mm': {
				$retval = (($value / 25.4 * $this->dpi) / $k);
				break;
			}
			// one pica is 12 points
			case 'pc': {
				$retval = (($value * 12) / $k);
				break;
			}
			// points
			case 'pt': {
				$retval = ($value / $k);
				break;
			}
			// pixels
			case 'px': {
				$retval = $this->pixelsToUnits($value);
				if ($points) {
					$retval *= $this->k;
				}
				break;
			}
		}
		return $retval;
	}

	/**
	 * Output an HTML list bullet or ordered item symbol
	 * @param $listdepth (int) list nesting level
	 * @param $listtype (string) type of list
	 * @param $size (float) current font size
	 * @protected
	 * @since 4.4.004 (2008-12-10)
	 */
	protected function putHtmlListBullet($listdepth, $listtype='', $size=10) {
		if ($this->state != 2) {
			return;
		}
		$size /= $this->k;
		$fill = '';
		$bgcolor = $this->bgcolor;
		$color = $this->fgcolor;
		$strokecolor = $this->strokecolor;
		$width = 0;
		$textitem = '';
		$tmpx = $this->x;
		$lspace = $this->GetStringWidth('  ');
		if ($listtype == '^') {
			// special symbol used for avoid justification of rect bullet
			$this->lispacer = '';
			return;
		} elseif ($listtype == '!') {
			// set default list type for unordered list
			$deftypes = array('disc', 'circle', 'square');
			$listtype = $deftypes[($listdepth - 1) % 3];
		} elseif ($listtype == '#') {
			// set default list type for ordered list
			$listtype = 'decimal';
		} elseif (substr($listtype, 0, 4) == 'img|') {
			// custom image type ('img|type|width|height|image.ext')
			$img = explode('|', $listtype);
			$listtype = 'img';
		}
		switch ($listtype) {
			// unordered types
			case 'none': {
				break;
			}
			case 'disc': {
				$r = $size / 6;
				$lspace += (2 * $r);
				if ($this->rtl) {
					$this->x += $lspace;
				} else {
					$this->x -= $lspace;
				}
				$this->Circle(($this->x + $r), ($this->y + ($this->lasth / 2)), $r, 0, 360, 'F', array(), $color, 8);
				break;
			}
			case 'circle': {
				$r = $size / 6;
				$lspace += (2 * $r);
				if ($this->rtl) {
					$this->x += $lspace;
				} else {
					$this->x -= $lspace;
				}
				$prev_line_style = $this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor;
				$new_line_style = array('width' => ($r / 3), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 0, 'color'=>$color);
				$this->Circle(($this->x + $r), ($this->y + ($this->lasth / 2)), ($r * (1 - (1/6))), 0, 360, 'D', $new_line_style, array(), 8);
				$this->_out($prev_line_style); // restore line settings
				break;
			}
			case 'square': {
				$l = $size / 3;
				$lspace += $l;
				if ($this->rtl) {;
					$this->x += $lspace;
				} else {
					$this->x -= $lspace;
				}
				$this->Rect($this->x, ($this->y + (($this->lasth - $l) / 2)), $l, $l, 'F', array(), $color);
				break;
			}
			case 'img': {
				// 1=>type, 2=>width, 3=>height, 4=>image.ext
				$lspace += $img[2];
				if ($this->rtl) {;
					$this->x += $lspace;
				} else {
					$this->x -= $lspace;
				}
				$imgtype = strtolower($img[1]);
				$prev_y = $this->y;
				switch ($imgtype) {
					case 'svg': {
						$this->ImageSVG($img[4], $this->x, ($this->y + (($this->lasth - $img[3]) / 2)), $img[2], $img[3], '', 'T', '', 0, false);
						break;
					}
					case 'ai':
					case 'eps': {
						$this->ImageEps($img[4], $this->x, ($this->y + (($this->lasth - $img[3]) / 2)), $img[2], $img[3], '', true, 'T', '', 0, false);
						break;
					}
					default: {
						$this->Image($img[4], $this->x, ($this->y + (($this->lasth - $img[3]) / 2)), $img[2], $img[3], $img[1], '', 'T', false, 300, '', false, false, 0, false, false, false);
						break;
					}
				}
				$this->y = $prev_y;
				break;
			}
			// ordered types
			// $this->listcount[$this->listnum];
			// $textitem
			case '1':
			case 'decimal': {
				$textitem = $this->listcount[$this->listnum];
				break;
			}
			case 'decimal-leading-zero': {
				$textitem = sprintf('%02d', $this->listcount[$this->listnum]);
				break;
			}
			case 'i':
			case 'lower-roman': {
				$textitem = strtolower(TCPDF_STATIC::intToRoman($this->listcount[$this->listnum]));
				break;
			}
			case 'I':
			case 'upper-roman': {
				$textitem = TCPDF_STATIC::intToRoman($this->listcount[$this->listnum]);
				break;
			}
			case 'a':
			case 'lower-alpha':
			case 'lower-latin': {
				$textitem = chr(97 + $this->listcount[$this->listnum] - 1);
				break;
			}
			case 'A':
			case 'upper-alpha':
			case 'upper-latin': {
				$textitem = chr(65 + $this->listcount[$this->listnum] - 1);
				break;
			}
			case 'lower-greek': {
				$textitem = TCPDF_FONTS::unichr((945 + $this->listcount[$this->listnum] - 1), $this->isunicode);
				break;
			}
			/*
			// Types to be implemented (special handling)
			case 'hebrew': {
				break;
			}
			case 'armenian': {
				break;
			}
			case 'georgian': {
				break;
			}
			case 'cjk-ideographic': {
				break;
			}
			case 'hiragana': {
				break;
			}
			case 'katakana': {
				break;
			}
			case 'hiragana-iroha': {
				break;
			}
			case 'katakana-iroha': {
				break;
			}
			*/
			default: {
				$textitem = $this->listcount[$this->listnum];
			}
		}
		if (!TCPDF_STATIC::empty_string($textitem)) {
			// Check whether we need a new page or new column
			$prev_y = $this->y;
			$h = ($this->FontSize * $this->cell_height_ratio) + $this->cell_padding['T'] + $this->cell_padding['B'];
			if ($this->checkPageBreak($h) OR ($this->y < $prev_y)) {
				$tmpx = $this->x;
			}
			// print ordered item
			if ($this->rtl) {
				$textitem = '.'.$textitem;
			} else {
				$textitem = $textitem.'.';
			}
			$lspace += $this->GetStringWidth($textitem);
			if ($this->rtl) {
				$this->x += $lspace;
			} else {
				$this->x -= $lspace;
			}
			$this->Write($this->lasth, $textitem, '', false, '', false, 0, false);
		}
		$this->x = $tmpx;
		$this->lispacer = '^';
		// restore colors
		$this->SetFillColorArray($bgcolor);
		$this->SetDrawColorArray($strokecolor);
		$this->SettextColorArray($color);
	}

	/**
	 * Returns current graphic variables as array.
	 * @return array of graphic variables
	 * @protected
	 * @since 4.2.010 (2008-11-14)
	 */
	protected function getGraphicVars() {
		$grapvars = array(
			'FontFamily' => $this->FontFamily,
			'FontStyle' => $this->FontStyle,
			'FontSizePt' => $this->FontSizePt,
			'rMargin' => $this->rMargin,
			'lMargin' => $this->lMargin,
			'cell_padding' => $this->cell_padding,
			'cell_margin' => $this->cell_margin,
			'LineWidth' => $this->LineWidth,
			'linestyleWidth' => $this->linestyleWidth,
			'linestyleCap' => $this->linestyleCap,
			'linestyleJoin' => $this->linestyleJoin,
			'linestyleDash' => $this->linestyleDash,
			'textrendermode' => $this->textrendermode,
			'textstrokewidth' => $this->textstrokewidth,
			'DrawColor' => $this->DrawColor,
			'FillColor' => $this->FillColor,
			'TextColor' => $this->TextColor,
			'ColorFlag' => $this->ColorFlag,
			'bgcolor' => $this->bgcolor,
			'fgcolor' => $this->fgcolor,
			'htmlvspace' => $this->htmlvspace,
			'listindent' => $this->listindent,
			'listindentlevel' => $this->listindentlevel,
			'listnum' => $this->listnum,
			'listordered' => $this->listordered,
			'listcount' => $this->listcount,
			'lispacer' => $this->lispacer,
			'cell_height_ratio' => $this->cell_height_ratio,
			'font_stretching' => $this->font_stretching,
			'font_spacing' => $this->font_spacing,
			'alpha' => $this->alpha,
			// extended
			'lasth' => $this->lasth,
			'tMargin' => $this->tMargin,
			'bMargin' => $this->bMargin,
			'AutoPageBreak' => $this->AutoPageBreak,
			'PageBreakTrigger' => $this->PageBreakTrigger,
			'x' => $this->x,
			'y' => $this->y,
			'w' => $this->w,
			'h' => $this->h,
			'wPt' => $this->wPt,
			'hPt' => $this->hPt,
			'fwPt' => $this->fwPt,
			'fhPt' => $this->fhPt,
			'page' => $this->page,
			'current_column' => $this->current_column,
			'num_columns' => $this->num_columns
			);
		return $grapvars;
	}

	/**
	 * Set graphic variables.
	 * @param $gvars (array) array of graphic variablesto restore
	 * @param $extended (boolean) if true restore extended graphic variables
	 * @protected
	 * @since 4.2.010 (2008-11-14)
	 */
	protected function setGraphicVars($gvars, $extended=false) {
		if ($this->state != 2) {
			 return;
		}
		$this->FontFamily = $gvars['FontFamily'];
		$this->FontStyle = $gvars['FontStyle'];
		$this->FontSizePt = $gvars['FontSizePt'];
		$this->rMargin = $gvars['rMargin'];
		$this->lMargin = $gvars['lMargin'];
		$this->cell_padding = $gvars['cell_padding'];
		$this->cell_margin = $gvars['cell_margin'];
		$this->LineWidth = $gvars['LineWidth'];
		$this->linestyleWidth = $gvars['linestyleWidth'];
		$this->linestyleCap = $gvars['linestyleCap'];
		$this->linestyleJoin = $gvars['linestyleJoin'];
		$this->linestyleDash = $gvars['linestyleDash'];
		$this->textrendermode = $gvars['textrendermode'];
		$this->textstrokewidth = $gvars['textstrokewidth'];
		$this->DrawColor = $gvars['DrawColor'];
		$this->FillColor = $gvars['FillColor'];
		$this->TextColor = $gvars['TextColor'];
		$this->ColorFlag = $gvars['ColorFlag'];
		$this->bgcolor = $gvars['bgcolor'];
		$this->fgcolor = $gvars['fgcolor'];
		$this->htmlvspace = $gvars['htmlvspace'];
		$this->listindent = $gvars['listindent'];
		$this->listindentlevel = $gvars['listindentlevel'];
		$this->listnum = $gvars['listnum'];
		$this->listordered = $gvars['listordered'];
		$this->listcount = $gvars['listcount'];
		$this->lispacer = $gvars['lispacer'];
		$this->cell_height_ratio = $gvars['cell_height_ratio'];
		$this->font_stretching = $gvars['font_stretching'];
		$this->font_spacing = $gvars['font_spacing'];
		$this->alpha = $gvars['alpha'];
		if ($extended) {
			// restore extended values
			$this->lasth = $gvars['lasth'];
			$this->tMargin = $gvars['tMargin'];
			$this->bMargin = $gvars['bMargin'];
			$this->AutoPageBreak = $gvars['AutoPageBreak'];
			$this->PageBreakTrigger = $gvars['PageBreakTrigger'];
			$this->x = $gvars['x'];
			$this->y = $gvars['y'];
			$this->w = $gvars['w'];
			$this->h = $gvars['h'];
			$this->wPt = $gvars['wPt'];
			$this->hPt = $gvars['hPt'];
			$this->fwPt = $gvars['fwPt'];
			$this->fhPt = $gvars['fhPt'];
			$this->page = $gvars['page'];
			$this->current_column = $gvars['current_column'];
			$this->num_columns = $gvars['num_columns'];
		}
		$this->_out(''.$this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor.' '.$this->FillColor.'');
		if (!TCPDF_STATIC::empty_string($this->FontFamily)) {
			$this->SetFont($this->FontFamily, $this->FontStyle, $this->FontSizePt);
		}
	}

	/**
	 * Writes data to a temporary file on filesystem.
	 * @param $filename (string) file name
	 * @param $data (mixed) data to write on file
	 * @param $append (boolean) if true append data, false replace.
	 * @since 4.5.000 (2008-12-31)
	 * @protected
	 */
	protected function writeDiskCache($filename, $data, $append=false) {
		if ($append) {
			$fmode = 'ab+';
		} else {
			$fmode = 'wb+';
		}
		$f = @fopen($filename, $fmode);
		if (!$f) {
			$this->Error('Unable to write cache file: '.$filename);
		} else {
			fwrite($f, $data);
			fclose($f);
		}
		// update file length (needed for transactions)
		if (!isset($this->cache_file_length['_'.$filename])) {
			$this->cache_file_length['_'.$filename] = strlen($data);
		} else {
			$this->cache_file_length['_'.$filename] += strlen($data);
		}
	}

	/**
	 * Read data from a temporary file on filesystem.
	 * @param $filename (string) file name
	 * @return mixed retrieved data
	 * @since 4.5.000 (2008-12-31)
	 * @protected
	 */
	protected function readDiskCache($filename) {
		return file_get_contents($filename);
	}

	/**
	 * Set buffer content (always append data).
	 * @param $data (string) data
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function setBuffer($data) {
		$this->bufferlen += strlen($data);
		if ($this->diskcache) {
			if (!isset($this->buffer) OR TCPDF_STATIC::empty_string($this->buffer)) {
				$this->buffer = TCPDF_STATIC::getObjFilename('buf');
			}
			$this->writeDiskCache($this->buffer, $data, true);
		} else {
			$this->buffer .= $data;
		}
	}

	/**
	 * Replace the buffer content
	 * @param $data (string) data
	 * @protected
	 * @since 5.5.000 (2010-06-22)
	 */
	protected function replaceBuffer($data) {
		$this->bufferlen = strlen($data);
		if ($this->diskcache) {
			if (!isset($this->buffer) OR TCPDF_STATIC::empty_string($this->buffer)) {
				$this->buffer = TCPDF_STATIC::getObjFilename('buf');
			}
			$this->writeDiskCache($this->buffer, $data, false);
		} else {
			$this->buffer = $data;
		}
	}

	/**
	 * Get buffer content.
	 * @return string buffer content
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function getBuffer() {
		if ($this->diskcache) {
			return $this->readDiskCache($this->buffer);
		} else {
			return $this->buffer;
		}
	}

	/**
	 * Set page buffer content.
	 * @param $page (int) page number
	 * @param $data (string) page data
	 * @param $append (boolean) if true append data, false replace.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function setPageBuffer($page, $data, $append=false) {
		if ($this->diskcache) {
			if (!isset($this->pages[$page])) {
				$this->pages[$page] = TCPDF_STATIC::getObjFilename('page');
			}
			$this->writeDiskCache($this->pages[$page], $data, $append);
		} else {
			if ($append) {
				$this->pages[$page] .= $data;
			} else {
				$this->pages[$page] = $data;
			}
		}
		if ($append AND isset($this->pagelen[$page])) {
			$this->pagelen[$page] += strlen($data);
		} else {
			$this->pagelen[$page] = strlen($data);
		}
	}

	/**
	 * Get page buffer content.
	 * @param $page (int) page number
	 * @return string page buffer content or false in case of error
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function getPageBuffer($page) {
		if ($this->diskcache) {
			return $this->readDiskCache($this->pages[$page]);
		} elseif (isset($this->pages[$page])) {
			return $this->pages[$page];
		}
		return false;
	}

	/**
	 * Set image buffer content.
	 * @param $image (string) image key
	 * @param $data (array) image data
	 * @return int image index number
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function setImageBuffer($image, $data) {
		if (($data['i'] = array_search($image, $this->imagekeys)) === FALSE) {
			$this->imagekeys[$this->numimages] = $image;
			$data['i'] = $this->numimages;
			++$this->numimages;
		}
		if ($this->diskcache) {
			if (!isset($this->images[$image])) {
				$this->images[$image] = TCPDF_STATIC::getObjFilename('img');
			}
			$this->writeDiskCache($this->images[$image], serialize($data));
		} else {
			$this->images[$image] = $data;
		}
		return $data['i'];
	}

	/**
	 * Set image buffer content for a specified sub-key.
	 * @param $image (string) image key
	 * @param $key (string) image sub-key
	 * @param $data (array) image data
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function setImageSubBuffer($image, $key, $data) {
		if (!isset($this->images[$image])) {
			$this->setImageBuffer($image, array());
		}
		if ($this->diskcache) {
			$tmpimg = $this->getImageBuffer($image);
			$tmpimg[$key] = $data;
			$this->writeDiskCache($this->images[$image], serialize($tmpimg));
		} else {
			$this->images[$image][$key] = $data;
		}
	}

	/**
	 * Get image buffer content.
	 * @param $image (string) image key
	 * @return string image buffer content or false in case of error
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function getImageBuffer($image) {
		if ($this->diskcache AND isset($this->images[$image])) {
			return unserialize($this->readDiskCache($this->images[$image]));
		} elseif (isset($this->images[$image])) {
			return $this->images[$image];
		}
		return false;
	}

	/**
	 * Set font buffer content.
	 * @param $font (string) font key
	 * @param $data (array) font data
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function setFontBuffer($font, $data) {
		if ($this->diskcache) {
			if (!isset($this->fonts[$font])) {
				$this->fonts[$font] = TCPDF_STATIC::getObjFilename('font');
			}
			$this->writeDiskCache($this->fonts[$font], serialize($data));
		} else {
			$this->fonts[$font] = $data;
		}
		if (!in_array($font, $this->fontkeys)) {
			$this->fontkeys[] = $font;
			// store object ID for current font
			++$this->n;
			$this->font_obj_ids[$font] = $this->n;
			$this->setFontSubBuffer($font, 'n', $this->n);
		}
	}

	/**
	 * Set font buffer content.
	 * @param $font (string) font key
	 * @param $key (string) font sub-key
	 * @param $data (array) font data
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function setFontSubBuffer($font, $key, $data) {
		if (!isset($this->fonts[$font])) {
			$this->setFontBuffer($font, array());
		}
		if ($this->diskcache) {
			$tmpfont = $this->getFontBuffer($font);
			$tmpfont[$key] = $data;
			$this->writeDiskCache($this->fonts[$font], serialize($tmpfont));
		} else {
			$this->fonts[$font][$key] = $data;
		}
	}

	/**
	 * Get font buffer content.
	 * @param $font (string) font key
	 * @return string font buffer content or false in case of error
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function getFontBuffer($font) {
		if ($this->diskcache AND isset($this->fonts[$font])) {
			return unserialize($this->readDiskCache($this->fonts[$font]));
		} elseif (isset($this->fonts[$font])) {
			return $this->fonts[$font];
		}
		return false;
	}

	/**
	 * Move a page to a previous position.
	 * @param $frompage (int) number of the source page
	 * @param $topage (int) number of the destination page (must be less than $frompage)
	 * @return true in case of success, false in case of error.
	 * @public
	 * @since 4.5.000 (2009-01-02)
	 */
	public function movePage($frompage, $topage) {
		if (($frompage > $this->numpages) OR ($frompage <= $topage)) {
			return false;
		}
		if ($frompage == $this->page) {
			// close the page before moving it
			$this->endPage();
		}
		// move all page-related states
		$tmppage = $this->getPageBuffer($frompage);
		$tmppagedim = $this->pagedim[$frompage];
		$tmppagelen = $this->pagelen[$frompage];
		$tmpintmrk = $this->intmrk[$frompage];
		$tmpbordermrk = $this->bordermrk[$frompage];
		$tmpcntmrk = $this->cntmrk[$frompage];
		$tmppageobjects = $this->pageobjects[$frompage];
		if (isset($this->footerpos[$frompage])) {
			$tmpfooterpos = $this->footerpos[$frompage];
		}
		if (isset($this->footerlen[$frompage])) {
			$tmpfooterlen = $this->footerlen[$frompage];
		}
		if (isset($this->transfmrk[$frompage])) {
			$tmptransfmrk = $this->transfmrk[$frompage];
		}
		if (isset($this->PageAnnots[$frompage])) {
			$tmpannots = $this->PageAnnots[$frompage];
		}
		if (isset($this->newpagegroup) AND !empty($this->newpagegroup)) {
			for ($i = $frompage; $i > $topage; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $frompage)) {
					--$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
			for ($i = $topage; $i > 0; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $topage)) {
					++$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
		}
		for ($i = $frompage; $i > $topage; --$i) {
			$j = $i - 1;
			// shift pages down
			$this->setPageBuffer($i, $this->getPageBuffer($j));
			$this->pagedim[$i] = $this->pagedim[$j];
			$this->pagelen[$i] = $this->pagelen[$j];
			$this->intmrk[$i] = $this->intmrk[$j];
			$this->bordermrk[$i] = $this->bordermrk[$j];
			$this->cntmrk[$i] = $this->cntmrk[$j];
			$this->pageobjects[$i] = $this->pageobjects[$j];
			if (isset($this->footerpos[$j])) {
				$this->footerpos[$i] = $this->footerpos[$j];
			} elseif (isset($this->footerpos[$i])) {
				unset($this->footerpos[$i]);
			}
			if (isset($this->footerlen[$j])) {
				$this->footerlen[$i] = $this->footerlen[$j];
			} elseif (isset($this->footerlen[$i])) {
				unset($this->footerlen[$i]);
			}
			if (isset($this->transfmrk[$j])) {
				$this->transfmrk[$i] = $this->transfmrk[$j];
			} elseif (isset($this->transfmrk[$i])) {
				unset($this->transfmrk[$i]);
			}
			if (isset($this->PageAnnots[$j])) {
				$this->PageAnnots[$i] = $this->PageAnnots[$j];
			} elseif (isset($this->PageAnnots[$i])) {
				unset($this->PageAnnots[$i]);
			}
			if (isset($this->newpagegroup[$j])) {
				$this->newpagegroup[$i] = $this->newpagegroup[$j];
				unset($this->newpagegroup[$j]);
			}
			if ($this->currpagegroup == $j) {
				$this->currpagegroup = $i;
			}
		}
		$this->setPageBuffer($topage, $tmppage);
		$this->pagedim[$topage] = $tmppagedim;
		$this->pagelen[$topage] = $tmppagelen;
		$this->intmrk[$topage] = $tmpintmrk;
		$this->bordermrk[$topage] = $tmpbordermrk;
		$this->cntmrk[$topage] = $tmpcntmrk;
		$this->pageobjects[$topage] = $tmppageobjects;
		if (isset($tmpfooterpos)) {
			$this->footerpos[$topage] = $tmpfooterpos;
		} elseif (isset($this->footerpos[$topage])) {
			unset($this->footerpos[$topage]);
		}
		if (isset($tmpfooterlen)) {
			$this->footerlen[$topage] = $tmpfooterlen;
		} elseif (isset($this->footerlen[$topage])) {
			unset($this->footerlen[$topage]);
		}
		if (isset($tmptransfmrk)) {
			$this->transfmrk[$topage] = $tmptransfmrk;
		} elseif (isset($this->transfmrk[$topage])) {
			unset($this->transfmrk[$topage]);
		}
		if (isset($tmpannots)) {
			$this->PageAnnots[$topage] = $tmpannots;
		} elseif (isset($this->PageAnnots[$topage])) {
			unset($this->PageAnnots[$topage]);
		}
		// adjust outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if (($outline['p'] >= $topage) AND ($outline['p'] < $frompage)) {
				$this->outlines[$key]['p'] = ($outline['p'] + 1);
			} elseif ($outline['p'] == $frompage) {
				$this->outlines[$key]['p'] = $topage;
			}
		}
		// adjust dests
		$tmpdests = $this->dests;
		foreach ($tmpdests as $key => $dest) {
			if (($dest['p'] >= $topage) AND ($dest['p'] < $frompage)) {
				$this->dests[$key]['p'] = ($dest['p'] + 1);
			} elseif ($dest['p'] == $frompage) {
				$this->dests[$key]['p'] = $topage;
			}
		}
		// adjust links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if (($link[0] >= $topage) AND ($link[0] < $frompage)) {
				$this->links[$key][0] = ($link[0] + 1);
			} elseif ($link[0] == $frompage) {
				$this->links[$key][0] = $topage;
			}
		}
		// adjust javascript
		$tmpjavascript = $this->javascript;
		global $jfrompage, $jtopage;
		$jfrompage = $frompage;
		$jtopage = $topage;
		$this->javascript = preg_replace_callback('/this\.addField\(\'([^\']*)\',\'([^\']*)\',([0-9]+)/',
			create_function('$matches', 'global $jfrompage, $jtopage;
			$pagenum = intval($matches[3]) + 1;
			if (($pagenum >= $jtopage) AND ($pagenum < $jfrompage)) {
				$newpage = ($pagenum + 1);
			} elseif ($pagenum == $jfrompage) {
				$newpage = $jtopage;
			} else {
				$newpage = $pagenum;
			}
			--$newpage;
			return "this.addField(\'".$matches[1]."\',\'".$matches[2]."\',".$newpage."";'), $tmpjavascript);
		// return to last page
		$this->lastPage(true);
		return true;
	}

	/**
	 * Remove the specified page.
	 * @param $page (int) page to remove
	 * @return true in case of success, false in case of error.
	 * @public
	 * @since 4.6.004 (2009-04-23)
	 */
	public function deletePage($page) {
		if (($page < 1) OR ($page > $this->numpages)) {
			return false;
		}
		// delete current page
		unset($this->pages[$page]);
		unset($this->pagedim[$page]);
		unset($this->pagelen[$page]);
		unset($this->intmrk[$page]);
		unset($this->bordermrk[$page]);
		unset($this->cntmrk[$page]);
		foreach ($this->pageobjects[$page] as $oid) {
			if (isset($this->offsets[$oid])){
				unset($this->offsets[$oid]);
			}
		}
		unset($this->pageobjects[$page]);
		if (isset($this->footerpos[$page])) {
			unset($this->footerpos[$page]);
		}
		if (isset($this->footerlen[$page])) {
			unset($this->footerlen[$page]);
		}
		if (isset($this->transfmrk[$page])) {
			unset($this->transfmrk[$page]);
		}
		if (isset($this->PageAnnots[$page])) {
			unset($this->PageAnnots[$page]);
		}
		if (isset($this->newpagegroup) AND !empty($this->newpagegroup)) {
			for ($i = $page; $i > 0; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $page)) {
					--$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
		}
		if (isset($this->pageopen[$page])) {
			unset($this->pageopen[$page]);
		}
		if ($page < $this->numpages) {
			// update remaining pages
			for ($i = $page; $i < $this->numpages; ++$i) {
				$j = $i + 1;
				// shift pages
				$this->setPageBuffer($i, $this->getPageBuffer($j));
				$this->pagedim[$i] = $this->pagedim[$j];
				$this->pagelen[$i] = $this->pagelen[$j];
				$this->intmrk[$i] = $this->intmrk[$j];
				$this->bordermrk[$i] = $this->bordermrk[$j];
				$this->cntmrk[$i] = $this->cntmrk[$j];
				$this->pageobjects[$i] = $this->pageobjects[$j];
				if (isset($this->footerpos[$j])) {
					$this->footerpos[$i] = $this->footerpos[$j];
				} elseif (isset($this->footerpos[$i])) {
					unset($this->footerpos[$i]);
				}
				if (isset($this->footerlen[$j])) {
					$this->footerlen[$i] = $this->footerlen[$j];
				} elseif (isset($this->footerlen[$i])) {
					unset($this->footerlen[$i]);
				}
				if (isset($this->transfmrk[$j])) {
					$this->transfmrk[$i] = $this->transfmrk[$j];
				} elseif (isset($this->transfmrk[$i])) {
					unset($this->transfmrk[$i]);
				}
				if (isset($this->PageAnnots[$j])) {
					$this->PageAnnots[$i] = $this->PageAnnots[$j];
				} elseif (isset($this->PageAnnots[$i])) {
					unset($this->PageAnnots[$i]);
				}
				if (isset($this->newpagegroup[$j])) {
					$this->newpagegroup[$i] = $this->newpagegroup[$j];
					unset($this->newpagegroup[$j]);
				}
				if ($this->currpagegroup == $j) {
					$this->currpagegroup = $i;
				}
				if (isset($this->pageopen[$j])) {
					$this->pageopen[$i] = $this->pageopen[$j];
				} elseif (isset($this->pageopen[$i])) {
					unset($this->pageopen[$i]);
				}
			}
			// remove last page
			unset($this->pages[$this->numpages]);
			unset($this->pagedim[$this->numpages]);
			unset($this->pagelen[$this->numpages]);
			unset($this->intmrk[$this->numpages]);
			unset($this->bordermrk[$this->numpages]);
			unset($this->cntmrk[$this->numpages]);
			foreach ($this->pageobjects[$this->numpages] as $oid) {
				if (isset($this->offsets[$oid])){
					unset($this->offsets[$oid]);
				}
			}
			unset($this->pageobjects[$this->numpages]);
			if (isset($this->footerpos[$this->numpages])) {
				unset($this->footerpos[$this->numpages]);
			}
			if (isset($this->footerlen[$this->numpages])) {
				unset($this->footerlen[$this->numpages]);
			}
			if (isset($this->transfmrk[$this->numpages])) {
				unset($this->transfmrk[$this->numpages]);
			}
			if (isset($this->PageAnnots[$this->numpages])) {
				unset($this->PageAnnots[$this->numpages]);
			}
			if (isset($this->newpagegroup[$this->numpages])) {
				unset($this->newpagegroup[$this->numpages]);
			}
			if ($this->currpagegroup == $this->numpages) {
				$this->currpagegroup = ($this->numpages - 1);
			}
			if (isset($this->pagegroups[$this->numpages])) {
				unset($this->pagegroups[$this->numpages]);
			}
			if (isset($this->pageopen[$this->numpages])) {
				unset($this->pageopen[$this->numpages]);
			}
		}
		--$this->numpages;
		$this->page = $this->numpages;
		// adjust outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if ($outline['p'] > $page) {
				$this->outlines[$key]['p'] = $outline['p'] - 1;
			} elseif ($outline['p'] == $page) {
				unset($this->outlines[$key]);
			}
		}
		// adjust dests
		$tmpdests = $this->dests;
		foreach ($tmpdests as $key => $dest) {
			if ($dest['p'] > $page) {
				$this->dests[$key]['p'] = $dest['p'] - 1;
			} elseif ($dest['p'] == $page) {
				unset($this->dests[$key]);
			}
		}
		// adjust links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if ($link[0] > $page) {
				$this->links[$key][0] = $link[0] - 1;
			} elseif ($link[0] == $page) {
				unset($this->links[$key]);
			}
		}
		// adjust javascript
		$tmpjavascript = $this->javascript;
		global $jpage;
		$jpage = $page;
		$this->javascript = preg_replace_callback('/this\.addField\(\'([^\']*)\',\'([^\']*)\',([0-9]+)/',
			create_function('$matches', 'global $jpage;
			$pagenum = intval($matches[3]) + 1;
			if ($pagenum >= $jpage) {
				$newpage = ($pagenum - 1);
			} elseif ($pagenum == $jpage) {
				$newpage = 1;
			} else {
				$newpage = $pagenum;
			}
			--$newpage;
			return "this.addField(\'".$matches[1]."\',\'".$matches[2]."\',".$newpage."";'), $tmpjavascript);
		// return to last page
		if ($this->numpages > 0) {
			$this->lastPage(true);
		}
		return true;
	}

	/**
	 * Clone the specified page to a new page.
	 * @param $page (int) number of page to copy (0 = current page)
	 * @return true in case of success, false in case of error.
	 * @public
	 * @since 4.9.015 (2010-04-20)
	 */
	public function copyPage($page=0) {
		if ($page == 0) {
			// default value
			$page = $this->page;
		}
		if (($page < 1) OR ($page > $this->numpages)) {
			return false;
		}
		// close the last page
		$this->endPage();
		// copy all page-related states
		++$this->numpages;
		$this->page = $this->numpages;
		$this->setPageBuffer($this->page, $this->getPageBuffer($page));
		$this->pagedim[$this->page] = $this->pagedim[$page];
		$this->pagelen[$this->page] = $this->pagelen[$page];
		$this->intmrk[$this->page] = $this->intmrk[$page];
		$this->bordermrk[$this->page] = $this->bordermrk[$page];
		$this->cntmrk[$this->page] = $this->cntmrk[$page];
		$this->pageobjects[$this->page] = $this->pageobjects[$page];
		$this->pageopen[$this->page] = false;
		if (isset($this->footerpos[$page])) {
			$this->footerpos[$this->page] = $this->footerpos[$page];
		}
		if (isset($this->footerlen[$page])) {
			$this->footerlen[$this->page] = $this->footerlen[$page];
		}
		if (isset($this->transfmrk[$page])) {
			$this->transfmrk[$this->page] = $this->transfmrk[$page];
		}
		if (isset($this->PageAnnots[$page])) {
			$this->PageAnnots[$this->page] = $this->PageAnnots[$page];
		}
		if (isset($this->newpagegroup[$page])) {
			// start a new group
			$this->newpagegroup[$this->page] = sizeof($this->newpagegroup) + 1;
			$this->currpagegroup = $this->newpagegroup[$this->page];
			$this->pagegroups[$this->currpagegroup] = 1;
		} elseif (isset($this->currpagegroup) AND ($this->currpagegroup > 0)) {
			++$this->pagegroups[$this->currpagegroup];
		}
		// copy outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if ($outline['p'] == $page) {
				$this->outlines[] = array('t' => $outline['t'], 'l' => $outline['l'], 'x' => $outline['x'], 'y' => $outline['y'], 'p' => $this->page, 's' => $outline['s'], 'c' => $outline['c']);
			}
		}
		// copy links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if ($link[0] == $page) {
				$this->links[] = array($this->page, $link[1]);
			}
		}
		// return to last page
		$this->lastPage(true);
		return true;
	}

	/**
	 * Output a Table of Content Index (TOC).
	 * This method must be called after all Bookmarks were set.
	 * Before calling this method you have to open the page using the addTOCPage() method.
	 * After calling this method you have to call endTOCPage() to close the TOC page.
	 * You can override this method to achieve different styles.
	 * @param $page (int) page number where this TOC should be inserted (leave empty for current page).
	 * @param $numbersfont (string) set the font for page numbers (please use monospaced font for better alignment).
	 * @param $filler (string) string used to fill the space between text and page number.
	 * @param $toc_name (string) name to use for TOC bookmark.
	 * @param $style (string) Font style for title: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param $color (array) RGB color array for bookmark title (values from 0 to 255).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.5.000 (2009-01-02)
	 * @see addTOCPage(), endTOCPage(), addHTMLTOC()
	 */
	public function addTOC($page='', $numbersfont='', $filler='.', $toc_name='TOC', $style='', $color=array(0,0,0)) {
		$fontsize = $this->FontSizePt;
		$fontfamily = $this->FontFamily;
		$fontstyle = $this->FontStyle;
		$w = $this->w - $this->lMargin - $this->rMargin;
		$spacer = $this->GetStringWidth(chr(32)) * 4;
		$lmargin = $this->lMargin;
		$rmargin = $this->rMargin;
		$x_start = $this->GetX();
		$page_first = $this->page;
		$current_page = $this->page;
		$page_fill_start = false;
		$page_fill_end = false;
		$current_column = $this->current_column;
		if (TCPDF_STATIC::empty_string($numbersfont)) {
			$numbersfont = $this->default_monospaced_font;
		}
		if (TCPDF_STATIC::empty_string($filler)) {
			$filler = ' ';
		}
		if (TCPDF_STATIC::empty_string($page)) {
			$gap = ' ';
		} else {
			$gap = '';
			if ($page < 1) {
				$page = 1;
			}
		}
		$this->SetFont($numbersfont, $fontstyle, $fontsize);
		$numwidth = $this->GetStringWidth('00000');
		$maxpage = 0; //used for pages on attached documents
		foreach ($this->outlines as $key => $outline) {
			// check for extra pages (used for attachments)
			if (($this->page > $page_first) AND ($outline['p'] >= $this->numpages)) {
				$outline['p'] += ($this->page - $page_first);
			}
			if ($this->rtl) {
				$aligntext = 'R';
				$alignnum = 'L';
			} else {
				$aligntext = 'L';
				$alignnum = 'R';
			}
			if ($outline['l'] == 0) {
				$this->SetFont($fontfamily, $outline['s'].'B', $fontsize);
			} else {
				$this->SetFont($fontfamily, $outline['s'], $fontsize - $outline['l']);
			}
			$this->SetTextColorArray($outline['c']);
			// check for page break
			$this->checkPageBreak((2 * $this->FontSize * $this->cell_height_ratio));
			// set margins and X position
			if (($this->page == $current_page) AND ($this->current_column == $current_column)) {
				$this->lMargin = $lmargin;
				$this->rMargin = $rmargin;
			} else {
				if ($this->current_column != $current_column) {
					if ($this->rtl) {
						$x_start = $this->w - $this->columns[$this->current_column]['x'];
					} else {
						$x_start = $this->columns[$this->current_column]['x'];
					}
				}
				$lmargin = $this->lMargin;
				$rmargin = $this->rMargin;
				$current_page = $this->page;
				$current_column = $this->current_column;
			}
			$this->SetX($x_start);
			$indent = ($spacer * $outline['l']);
			if ($this->rtl) {
				$this->x -= $indent;
				$this->rMargin = $this->w - $this->x;
			} else {
				$this->x += $indent;
				$this->lMargin = $this->x;
			}
			$link = $this->AddLink();
			$this->SetLink($link, $outline['y'], $outline['p']);
			// write the text
			if ($this->rtl) {
				$txt = ' '.$outline['t'];
			} else {
				$txt = $outline['t'].' ';
			}
			$this->Write(0, $txt, $link, false, $aligntext, false, 0, false, false, 0, $numwidth, '');
			if ($this->rtl) {
				$tw = $this->x - $this->lMargin;
			} else {
				$tw = $this->w - $this->rMargin - $this->x;
			}
			$this->SetFont($numbersfont, $fontstyle, $fontsize);
			if (TCPDF_STATIC::empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if ($this->isUnicodeFont()) {
					$pagenum = '{'.$pagenum.'}';
				}
				$maxpage = max($maxpage, $outline['p']);
			}
			$fw = ($tw - $this->GetStringWidth($pagenum.$filler));
			$wfiller = $this->GetStringWidth($filler);
			if ($wfiller > 0) {
				$numfills = floor($fw / $wfiller);
			} else {
				$numfills = 0;
			}
			if ($numfills > 0) {
				$rowfill = str_repeat($filler, $numfills);
			} else {
				$rowfill = '';
			}
			if ($this->rtl) {
				$pagenum = $pagenum.$gap.$rowfill;
			} else {
				$pagenum = $rowfill.$gap.$pagenum;
			}
			// write the number
			$this->Cell($tw, 0, $pagenum, 0, 1, $alignnum, 0, $link, 0);
		}
		$page_last = $this->getPage();
		$numpages = ($page_last - $page_first + 1);
		// account for booklet mode
		if ($this->booklet) {
			// check if a blank page is required before TOC
			$page_fill_start = ((($page_first % 2) == 0) XOR (($page % 2) == 0));
			$page_fill_end = (!((($numpages % 2) == 0) XOR ($page_fill_start)));
			if ($page_fill_start) {
				// add a page at the end (to be moved before TOC)
				$this->addPage();
				++$page_last;
				++$numpages;
			}
			if ($page_fill_end) {
				// add a page at the end
				$this->addPage();
				++$page_last;
				++$numpages;
			}
		}
		$maxpage = max($maxpage, $page_last);
		if (!TCPDF_STATIC::empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);
				for ($n = 1; $n <= $maxpage; ++$n) {
					// update page numbers
					$a = '{#'.$n.'}';
					// get page number aliases
					$pnalias = $this->getInternalPageNumberAliases($a);
					// calculate replacement number
					if (($n >= $page) AND ($n <= $this->numpages)) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}
					$na = TCPDF_STATIC::formatTOCPageNumber(($this->starting_page_number + $np - 1));
					$nu = TCPDF_FONTS::UTF8ToUTF16BE($na, false, $this->isunicode, $this->CurrentFont);
					// replace aliases with numbers
					foreach ($pnalias['u'] as $u) {
						$sfill = str_repeat($filler, max(0, (strlen($u) - strlen($nu.' '))));
						if ($this->rtl) {
							$nr = $nu.TCPDF_FONTS::UTF8ToUTF16BE(' '.$sfill, false, $this->isunicode, $this->CurrentFont);
						} else {
							$nr = TCPDF_FONTS::UTF8ToUTF16BE($sfill.' ', false, $this->isunicode, $this->CurrentFont).$nu;
						}
						$temppage = str_replace($u, $nr, $temppage);
					}
					foreach ($pnalias['a'] as $a) {
						$sfill = str_repeat($filler, max(0, (strlen($a) - strlen($na.' '))));
						if ($this->rtl) {
							$nr = $na.' '.$sfill;
						} else {
							$nr = $sfill.' '.$na;
						}
						$temppage = str_replace($a, $nr, $temppage);
					}
				}
				// save changes
				$this->setPageBuffer($p, $temppage);
			}
			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first, $style, $color);
			if ($page_fill_start) {
				$this->movePage($page_last, $page_first);
			}
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
	}

	/**
	 * Output a Table Of Content Index (TOC) using HTML templates.
	 * This method must be called after all Bookmarks were set.
	 * Before calling this method you have to open the page using the addTOCPage() method.
	 * After calling this method you have to call endTOCPage() to close the TOC page.
	 * @param $page (int) page number where this TOC should be inserted (leave empty for current page).
	 * @param $toc_name (string) name to use for TOC bookmark.
	 * @param $templates (array) array of html templates. Use: "#TOC_DESCRIPTION#" for bookmark title, "#TOC_PAGE_NUMBER#" for page number.
	 * @param $correct_align (boolean) if true correct the number alignment (numbers must be in monospaced font like courier and right aligned on LTR, or left aligned on RTL)
	 * @param $style (string) Font style for title: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param $color (array) RGB color array for title (values from 0 to 255).
	 * @public
	 * @author Nicola Asuni
	 * @since 5.0.001 (2010-05-06)
	 * @see addTOCPage(), endTOCPage(), addTOC()
	 */
	public function addHTMLTOC($page='', $toc_name='TOC', $templates=array(), $correct_align=true, $style='', $color=array(0,0,0)) {
		$filler = ' ';
		$prev_htmlLinkColorArray = $this->htmlLinkColorArray;
		$prev_htmlLinkFontStyle = $this->htmlLinkFontStyle;
		// set new style for link
		$this->htmlLinkColorArray = array();
		$this->htmlLinkFontStyle = '';
		$page_first = $this->getPage();
		$page_fill_start = false;
		$page_fill_end = false;
		// get the font type used for numbers in each template
		$current_font = $this->FontFamily;
		foreach ($templates as $level => $html) {
			$dom = $this->getHtmlDomArray($html);
			foreach ($dom as $key => $value) {
				if ($value['value'] == '#TOC_PAGE_NUMBER#') {
					$this->SetFont($dom[($key - 1)]['fontname']);
					$templates['F'.$level] = $this->isUnicodeFont();
				}
			}
		}
		$this->SetFont($current_font);
		$maxpage = 0; //used for pages on attached documents
		foreach ($this->outlines as $key => $outline) {
			// get HTML template
			$row = $templates[$outline['l']];
			if (TCPDF_STATIC::empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if ($templates['F'.$outline['l']]) {
					$pagenum = '{'.$pagenum.'}';
				}
				$maxpage = max($maxpage, $outline['p']);
			}
			// replace templates with current values
			$row = str_replace('#TOC_DESCRIPTION#', $outline['t'], $row);
			$row = str_replace('#TOC_PAGE_NUMBER#', $pagenum, $row);
			// add link to page
			$row = '<a href="#'.$outline['p'].','.$outline['y'].'">'.$row.'</a>';
			// write bookmark entry
			$this->writeHTML($row, false, false, true, false, '');
		}
		// restore link styles
		$this->htmlLinkColorArray = $prev_htmlLinkColorArray;
		$this->htmlLinkFontStyle = $prev_htmlLinkFontStyle;
		// move TOC page and replace numbers
		$page_last = $this->getPage();
		$numpages = ($page_last - $page_first + 1);
		// account for booklet mode
		if ($this->booklet) {
			// check if a blank page is required before TOC
			$page_fill_start = ((($page_first % 2) == 0) XOR (($page % 2) == 0));
			$page_fill_end = (!((($numpages % 2) == 0) XOR ($page_fill_start)));
			if ($page_fill_start) {
				// add a page at the end (to be moved before TOC)
				$this->addPage();
				++$page_last;
				++$numpages;
			}
			if ($page_fill_end) {
				// add a page at the end
				$this->addPage();
				++$page_last;
				++$numpages;
			}
		}
		$maxpage = max($maxpage, $page_last);
		if (!TCPDF_STATIC::empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);
				for ($n = 1; $n <= $maxpage; ++$n) {
					// update page numbers
					$a = '{#'.$n.'}';
					// get page number aliases
					$pnalias = $this->getInternalPageNumberAliases($a);
					// calculate replacement number
					if ($n >= $page) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}
					$na = TCPDF_STATIC::formatTOCPageNumber(($this->starting_page_number + $np - 1));
					$nu = TCPDF_FONTS::UTF8ToUTF16BE($na, false, $this->isunicode, $this->CurrentFont);
					// replace aliases with numbers
					foreach ($pnalias['u'] as $u) {
						if ($correct_align) {
							$sfill = str_repeat($filler, (strlen($u) - strlen($nu.' ')));
							if ($this->rtl) {
								$nr = $nu.TCPDF_FONTS::UTF8ToUTF16BE(' '.$sfill, false, $this->isunicode, $this->CurrentFont);
							} else {
								$nr = TCPDF_FONTS::UTF8ToUTF16BE($sfill.' ', false, $this->isunicode, $this->CurrentFont).$nu;
							}
						} else {
							$nr = $nu;
						}
						$temppage = str_replace($u, $nr, $temppage);
					}
					foreach ($pnalias['a'] as $a) {
						if ($correct_align) {
							$sfill = str_repeat($filler, (strlen($a) - strlen($na.' ')));
							if ($this->rtl) {
								$nr = $na.' '.$sfill;
							} else {
								$nr = $sfill.' '.$na;
							}
						} else {
							$nr = $na;
						}
						$temppage = str_replace($a, $nr, $temppage);
					}
				}
				// save changes
				$this->setPageBuffer($p, $temppage);
			}
			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first, $style, $color);
			if ($page_fill_start) {
				$this->movePage($page_last, $page_first);
			}
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
	}

	/**
	 * Stores a copy of the current TCPDF object used for undo operation.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function startTransaction() {
		if (isset($this->objcopy)) {
			// remove previous copy
			$this->commitTransaction();
		}
		// record current page number and Y position
		$this->start_transaction_page = $this->page;
		$this->start_transaction_y = $this->y;
		// clone current object
		$this->objcopy = TCPDF_STATIC::objclone($this);
	}

	/**
	 * Delete the copy of the current TCPDF object used for undo operation.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function commitTransaction() {
		if (isset($this->objcopy)) {
			$this->objcopy->_destroy(true, true);
			unset($this->objcopy);
		}
	}

	/**
	 * This method allows to undo the latest transaction by returning the latest saved TCPDF object with startTransaction().
	 * @param $self (boolean) if true restores current class object to previous state without the need of reassignment via the returned value.
	 * @return TCPDF object.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function rollbackTransaction($self=false) {
		if (isset($this->objcopy)) {
			if (isset($this->objcopy->diskcache) AND $this->objcopy->diskcache) {
				// truncate files to previous values
				foreach ($this->objcopy->cache_file_length as $file => $length) {
					$file = substr($file, 1);
					$handle = fopen($file, 'r+');
					ftruncate($handle, $length);
				}
			}
			$this->_destroy(true, true);
			if ($self) {
				$objvars = get_object_vars($this->objcopy);
				foreach ($objvars as $key => $value) {
					$this->$key = $value;
				}
			}
			return $this->objcopy;
		}
		return $this;
	}

	// --- MULTI COLUMNS METHODS -----------------------

	/**
	 * Set multiple columns of the same size
	 * @param $numcols (int) number of columns (set to zero to disable columns mode)
	 * @param $width (int) column width
	 * @param $y (int) column starting Y position (leave empty for current Y position)
	 * @public
	 * @since 4.9.001 (2010-03-28)
	 */
	public function setEqualColumns($numcols=0, $width=0, $y='') {
		$this->columns = array();
		if ($numcols < 2) {
			$numcols = 0;
			$this->columns = array();
		} else {
			// maximum column width
			$maxwidth = ($this->w - $this->original_lMargin - $this->original_rMargin) / $numcols;
			if (($width == 0) OR ($width > $maxwidth)) {
				$width = $maxwidth;
			}
			if (TCPDF_STATIC::empty_string($y)) {
				$y = $this->y;
			}
			// space between columns
			$space = (($this->w - $this->original_lMargin - $this->original_rMargin - ($numcols * $width)) / ($numcols - 1));
			// fill the columns array (with, space, starting Y position)
			for ($i = 0; $i < $numcols; ++$i) {
				$this->columns[$i] = array('w' => $width, 's' => $space, 'y' => $y);
			}
		}
		$this->num_columns = $numcols;
		$this->current_column = 0;
		$this->column_start_page = $this->page;
		$this->selectColumn(0);
	}

	/**
	 * Remove columns and reset page margins.
	 * @public
	 * @since 5.9.072 (2011-04-26)
	 */
	public function resetColumns() {
		$this->lMargin = $this->original_lMargin;
		$this->rMargin = $this->original_rMargin;
		$this->setEqualColumns();
	}

	/**
	 * Set columns array.
	 * Each column is represented by an array of arrays with the following keys: (w = width, s = space between columns, y = column top position).
	 * @param $columns (array)
	 * @public
	 * @since 4.9.001 (2010-03-28)
	 */
	public function setColumnsArray($columns) {
		$this->columns = $columns;
		$this->num_columns = count($columns);
		$this->current_column = 0;
		$this->column_start_page = $this->page;
		$this->selectColumn(0);
	}

	/**
	 * Set position at a given column
	 * @param $col (int) column number (from 0 to getNumberOfColumns()-1); empty string = current column.
	 * @public
	 * @since 4.9.001 (2010-03-28)
	 */
	public function selectColumn($col='') {
		if (is_string($col)) {
			$col = $this->current_column;
		} elseif ($col >= $this->num_columns) {
			$col = 0;
		}
		$xshift = array('x' => 0, 's' => array('H' => 0, 'V' => 0), 'p' => array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0));
		$enable_thead = false;
		if ($this->num_columns > 1) {
			if ($col != $this->current_column) {
				// move Y pointer at the top of the column
				if ($this->column_start_page == $this->page) {
					$this->y = $this->columns[$col]['y'];
				} else {
					$this->y = $this->tMargin;
				}
				// Avoid to write table headers more than once
				if (($this->page > $this->maxselcol['page']) OR (($this->page == $this->maxselcol['page']) AND ($col > $this->maxselcol['column']))) {
					$enable_thead = true;
					$this->maxselcol['page'] = $this->page;
					$this->maxselcol['column'] = $col;
				}
			}
			$xshift = $this->colxshift;
			// set X position of the current column by case
			$listindent = ($this->listindentlevel * $this->listindent);
			// calculate column X position
			$colpos = 0;
			for ($i = 0; $i < $col; ++$i) {
				$colpos += ($this->columns[$i]['w'] + $this->columns[$i]['s']);
			}
			if ($this->rtl) {
				$x = $this->w - $this->original_rMargin - $colpos;
				$this->rMargin = ($this->w - $x + $listindent);
				$this->lMargin = ($x - $this->columns[$col]['w']);
				$this->x = $x - $listindent;
			} else {
				$x = $this->original_lMargin + $colpos;
				$this->lMargin = ($x + $listindent);
				$this->rMargin = ($this->w - $x - $this->columns[$col]['w']);
				$this->x = $x + $listindent;
			}
			$this->columns[$col]['x'] = $x;
		}
		$this->current_column = $col;
		// fix for HTML mode
		$this->newline = true;
		// print HTML table header (if any)
		if ((!TCPDF_STATIC::empty_string($this->thead)) AND (!$this->inthead)) {
			if ($enable_thead) {
				// print table header
				$this->writeHTML($this->thead, false, false, false, false, '');
				$this->y += $xshift['s']['V'];
				// store end of header position
				if (!isset($this->columns[$col]['th'])) {
					$this->columns[$col]['th'] = array();
				}
				$this->columns[$col]['th']['\''.$this->page.'\''] = $this->y;
				$this->lasth = 0;
			} elseif (isset($this->columns[$col]['th']['\''.$this->page.'\''])) {
				$this->y = $this->columns[$col]['th']['\''.$this->page.'\''];
			}
		}
		// account for an html table cell over multiple columns
		if ($this->rtl) {
			$this->rMargin += $xshift['x'];
			$this->x -= ($xshift['x'] + $xshift['p']['R']);
		} else {
			$this->lMargin += $xshift['x'];
			$this->x += $xshift['x'] + $xshift['p']['L'];
		}
	}

	/**
	 * Return the current column number
	 * @return int current column number
	 * @public
	 * @since 5.5.011 (2010-07-08)
	 */
	public function getColumn() {
		return $this->current_column;
	}

	/**
	 * Return the current number of columns.
	 * @return int number of columns
	 * @public
	 * @since 5.8.018 (2010-08-25)
	 */
	public function getNumberOfColumns() {
		return $this->num_columns;
	}

	/**
	 * Set Text rendering mode.
	 * @param $stroke (int) outline size in user units (0 = disable).
	 * @param $fill (boolean) if true fills the text (default).
	 * @param $clip (boolean) if true activate clipping mode
	 * @public
	 * @since 4.9.008 (2009-04-02)
	 */
	public function setTextRenderingMode($stroke=0, $fill=true, $clip=false) {
		// Ref.: PDF 32000-1:2008 - 9.3.6 Text Rendering Mode
		// convert text rendering parameters
		if ($stroke < 0) {
			$stroke = 0;
		}
		if ($fill === true) {
			if ($stroke > 0) {
				if ($clip === true) {
					// Fill, then stroke text and add to path for clipping
					$textrendermode = 6;
				} else {
					// Fill, then stroke text
					$textrendermode = 2;
				}
				$textstrokewidth = $stroke;
			} else {
				if ($clip === true) {
					// Fill text and add to path for clipping
					$textrendermode = 4;
				} else {
					// Fill text
					$textrendermode = 0;
				}
			}
		} else {
			if ($stroke > 0) {
				if ($clip === true) {
					// Stroke text and add to path for clipping
					$textrendermode = 5;
				} else {
					// Stroke text
					$textrendermode = 1;
				}
				$textstrokewidth = $stroke;
			} else {
				if ($clip === true) {
					// Add text to path for clipping
					$textrendermode = 7;
				} else {
					// Neither fill nor stroke text (invisible)
					$textrendermode = 3;
				}
			}
		}
		$this->textrendermode = $textrendermode;
		$this->textstrokewidth = $stroke;
	}

	/**
	 * Set parameters for drop shadow effect for text.
	 * @param $params (array) Array of parameters: enabled (boolean) set to true to enable shadow; depth_w (float) shadow width in user units; depth_h (float) shadow height in user units; color (array) shadow color or false to use the stroke color; opacity (float) Alpha value: real value from 0 (transparent) to 1 (opaque); blend_mode (string) blend mode, one of the following: Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity.
	 * @since 5.9.174 (2012-07-25)
	 * @public
	*/
	public function setTextShadow($params=array('enabled'=>false, 'depth_w'=>0, 'depth_h'=>0, 'color'=>false, 'opacity'=>1, 'blend_mode'=>'Normal')) {
		if (isset($params['enabled'])) {
			$this->txtshadow['enabled'] = $params['enabled']?true:false;
		} else {
			$this->txtshadow['enabled'] = false;
		}
		if (isset($params['depth_w'])) {
			$this->txtshadow['depth_w'] = floatval($params['depth_w']);
		} else {
			$this->txtshadow['depth_w'] = 0;
		}
		if (isset($params['depth_h'])) {
			$this->txtshadow['depth_h'] = floatval($params['depth_h']);
		} else {
			$this->txtshadow['depth_h'] = 0;
		}
		if (isset($params['color']) AND ($params['color'] !== false) AND is_array($params['color'])) {
			$this->txtshadow['color'] = $params['color'];
		} else {
			$this->txtshadow['color'] = $this->strokecolor;
		}
		if (isset($params['opacity'])) {
			$this->txtshadow['opacity'] = min(1, max(0, floatval($params['opacity'])));
		} else {
			$this->txtshadow['opacity'] = 1;
		}
		if (isset($params['blend_mode']) AND in_array($params['blend_mode'], array('Normal', 'Multiply', 'Screen', 'Overlay', 'Darken', 'Lighten', 'ColorDodge', 'ColorBurn', 'HardLight', 'SoftLight', 'Difference', 'Exclusion', 'Hue', 'Saturation', 'Color', 'Luminosity'))) {
			$this->txtshadow['blend_mode'] = $params['blend_mode'];
		} else {
			$this->txtshadow['blend_mode'] = 'Normal';
		}
		if ((($this->txtshadow['depth_w'] == 0) AND ($this->txtshadow['depth_h'] == 0)) OR ($this->txtshadow['opacity'] == 0)) {
			$this->txtshadow['enabled'] = false;
		}
	}

	/**
	 * Return the text shadow parameters array.
	 * @return Array of parameters.
	 * @since 5.9.174 (2012-07-25)
	 * @public
	 */
	public function getTextShadow() {
		return $this->txtshadow;
	}

	/**
	 * Returns an array of chars containing soft hyphens.
	 * @param $word (array) array of chars
	 * @param $patterns (array) Array of hypenation patterns.
	 * @param $dictionary (array) Array of words to be returned without applying the hyphenation algoritm.
	 * @param $leftmin (int) Minimum number of character to leave on the left of the word without applying the hyphens.
	 * @param $rightmin (int) Minimum number of character to leave on the right of the word without applying the hyphens.
	 * @param $charmin (int) Minimum word length to apply the hyphenation algoritm.
	 * @param $charmax (int) Maximum length of broken piece of word.
	 * @return array text with soft hyphens
	 * @author Nicola Asuni
	 * @since 4.9.012 (2010-04-12)
	 * @protected
	 */
	protected function hyphenateWord($word, $patterns, $dictionary=array(), $leftmin=1, $rightmin=2, $charmin=1, $charmax=8) {
		$hyphenword = array(); // hyphens positions
		$numchars = count($word);
		if ($numchars <= $charmin) {
			return $word;
		}
		$word_string = TCPDF_FONTS::UTF8ArrSubString($word, '', '', $this->isunicode);
		// some words will be returned as-is
		$pattern = '/^([a-zA-Z0-9_\.\-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
		if (preg_match($pattern, $word_string) > 0) {
			// email
			return $word;
		}
		$pattern = '/(([a-zA-Z0-9\-]+\.)?)((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
		if (preg_match($pattern, $word_string) > 0) {
			// URL
			return $word;
		}
		if (isset($dictionary[$word_string])) {
			return TCPDF_FONTS::UTF8StringToArray($dictionary[$word_string], $this->isunicode, $this->CurrentFont);
		}
		// suround word with '_' characters
		$tmpword = array_merge(array(95), $word, array(95));
		$tmpnumchars = $numchars + 2;
		$maxpos = $tmpnumchars - $charmin;
		for ($pos = 0; $pos < $maxpos; ++$pos) {
			$imax = min(($tmpnumchars - $pos), $charmax);
			for ($i = $charmin; $i <= $imax; ++$i) {
				$subword = strtolower(TCPDF_FONTS::UTF8ArrSubString($tmpword, $pos, ($pos + $i), $this->isunicode));
				if (isset($patterns[$subword])) {
					$pattern = TCPDF_FONTS::UTF8StringToArray($patterns[$subword], $this->isunicode, $this->CurrentFont);
					$pattern_length = count($pattern);
					$digits = 1;
					for ($j = 0; $j < $pattern_length; ++$j) {
						// check if $pattern[$j] is a number
						if (($pattern[$j] >= 48) AND ($pattern[$j] <= 57)) {
							if ($j == 0) {
								$zero = $pos - 1;
							} else {
								$zero = $pos + $j - $digits;
							}
							if (!isset($hyphenword[$zero]) OR ($hyphenword[$zero] != $pattern[$j])) {
								$hyphenword[$zero] = TCPDF_FONTS::unichr($pattern[$j], $this->isunicode);
							}
							++$digits;
						}
					}
				}
			}
		}
		$inserted = 0;
		$maxpos = $numchars - $rightmin;
		for ($i = $leftmin; $i <= $maxpos; ++$i) {
			if (isset($hyphenword[$i]) AND (($hyphenword[$i] % 2) != 0)) {
				// 173 = soft hyphen character
				array_splice($word, $i + $inserted, 0, 173);
				++$inserted;
			}
		}
		return $word;
	}

	/**
	 * Returns text with soft hyphens.
	 * @param $text (string) text to process
	 * @param $patterns (mixed) Array of hypenation patterns or a TEX file containing hypenation patterns. TEX patterns can be downloaded from http://www.ctan.org/tex-archive/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	 * @param $dictionary (array) Array of words to be returned without applying the hyphenation algoritm.
	 * @param $leftmin (int) Minimum number of character to leave on the left of the word without applying the hyphens.
	 * @param $rightmin (int) Minimum number of character to leave on the right of the word without applying the hyphens.
	 * @param $charmin (int) Minimum word length to apply the hyphenation algoritm.
	 * @param $charmax (int) Maximum length of broken piece of word.
	 * @return array text with soft hyphens
	 * @author Nicola Asuni
	 * @since 4.9.012 (2010-04-12)
	 * @public
	 */
	public function hyphenateText($text, $patterns, $dictionary=array(), $leftmin=1, $rightmin=2, $charmin=1, $charmax=8) {
		$text = $this->unhtmlentities($text);
		$word = array(); // last word
		$txtarr = array(); // text to be returned
		$intag = false; // true if we are inside an HTML tag
		if (!is_array($patterns)) {
			$patterns = TCPDF_STATIC::getHyphenPatternsFromTEX($patterns);
		}
		// get array of characters
		$unichars = TCPDF_FONTS::UTF8StringToArray($text, $this->isunicode, $this->CurrentFont);
		// for each char
		foreach ($unichars as $char) {
			if ((!$intag) AND TCPDF_FONT_DATA::$uni_type[$char] == 'L') {
				// letter character
				$word[] = $char;
			} else {
				// other type of character
				if (!TCPDF_STATIC::empty_string($word)) {
					// hypenate the word
					$txtarr = array_merge($txtarr, $this->hyphenateWord($word, $patterns, $dictionary, $leftmin, $rightmin, $charmin, $charmax));
					$word = array();
				}
				$txtarr[] = $char;
				if (chr($char) == '<') {
					// we are inside an HTML tag
					$intag = true;
				} elseif ($intag AND (chr($char) == '>')) {
					// end of HTML tag
					$intag = false;
				}
			}
		}
		if (!TCPDF_STATIC::empty_string($word)) {
			// hypenate the word
			$txtarr = array_merge($txtarr, $this->hyphenateWord($word, $patterns, $dictionary, $leftmin, $rightmin, $charmin, $charmax));
		}
		// convert char array to string and return
		return TCPDF_FONTS::UTF8ArrSubString($txtarr, '', '', $this->isunicode);
	}

	/**
	 * Enable/disable rasterization of vector images using ImageMagick library.
	 * @param $mode (boolean) if true enable rasterization, false otherwise.
	 * @public
	 * @since 5.0.000 (2010-04-27)
	 */
	public function setRasterizeVectorImages($mode) {
		$this->rasterize_vector_images = $mode;
	}

	/**
	 * Enable or disable default option for font subsetting.
	 * @param $enable (boolean) if true enable font subsetting by default.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.3.002 (2010-06-07)
	 */
	public function setFontSubsetting($enable=true) {
		if ($this->pdfa_mode) {
			$this->font_subsetting = false;
		} else {
			$this->font_subsetting = $enable ? true : false;
		}
	}

	/**
	 * Return the default option for font subsetting.
	 * @return boolean default font subsetting state.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.3.002 (2010-06-07)
	 */
	public function getFontSubsetting() {
		return $this->font_subsetting;
	}

	/**
	 * Left trim the input string
	 * @param $str (string) string to trim
	 * @param $replace (string) string that replace spaces.
	 * @return left trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringLeftTrim($str, $replace='') {
		return preg_replace('/^'.$this->re_space['p'].'+/'.$this->re_space['m'], $replace, $str);
	}

	/**
	 * Right trim the input string
	 * @param $str (string) string to trim
	 * @param $replace (string) string that replace spaces.
	 * @return right trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringRightTrim($str, $replace='') {
		return preg_replace('/'.$this->re_space['p'].'+$/'.$this->re_space['m'], $replace, $str);
	}

	/**
	 * Trim the input string
	 * @param $str (string) string to trim
	 * @param $replace (string) string that replace spaces.
	 * @return trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringTrim($str, $replace='') {
		$str = $this->stringLeftTrim($str, $replace);
		$str = $this->stringRightTrim($str, $replace);
		return $str;
	}

	/**
	 * Return true if the current font is unicode type.
	 * @return true for unicode font, false otherwise.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.002 (2010-08-14)
	 */
	public function isUnicodeFont() {
		return (($this->CurrentFont['type'] == 'TrueTypeUnicode') OR ($this->CurrentFont['type'] == 'cidfont0'));
	}

	/**
	 * Return normalized font name
	 * @param $fontfamily (string) property string containing font family names
	 * @return string normalized font name
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.004 (2010-08-17)
	 */
	public function getFontFamilyName($fontfamily) {
		// remove spaces and symbols
		$fontfamily = preg_replace('/[^a-z0-9_\,]/', '', strtolower($fontfamily));
		// extract all font names
		$fontslist = preg_split('/[,]/', $fontfamily);
		// find first valid font name
		foreach ($fontslist as $font) {
			// replace font variations
			$font = preg_replace('/italic$/', 'I', $font);
			$font = preg_replace('/oblique$/', 'I', $font);
			$font = preg_replace('/bold([I]?)$/', 'B\\1', $font);
			// replace common family names and core fonts
			$pattern = array();
			$replacement = array();
			$pattern[] = '/^serif|^cursive|^fantasy|^timesnewroman/';
			$replacement[] = 'times';
			$pattern[] = '/^sansserif/';
			$replacement[] = 'helvetica';
			$pattern[] = '/^monospace/';
			$replacement[] = 'courier';
			$font = preg_replace($pattern, $replacement, $font);
			if (in_array(strtolower($font), $this->fontlist) OR in_array($font, $this->fontkeys)) {
				return $font;
			}
		}
		// return current font as default
		return $this->CurrentFont['fontkey'];
	}

	/**
	 * Start a new XObject Template.
	 * An XObject Template is a PDF block that is a self-contained description of any sequence of graphics objects (including path objects, text objects, and sampled images).
	 * An XObject Template may be painted multiple times, either on several pages or at several locations on the same page and produces the same results each time, subject only to the graphics state at the time it is invoked.
	 * Note: X,Y coordinates will be reset to 0,0.
	 * @param $w (int) Template width in user units (empty string or zero = page width less margins).
	 * @param $h (int) Template height in user units (empty string or zero = page height less margins).
	 * @param $group (mixed) Set transparency group. Can be a boolean value or an array specifying optional parameters: 'CS' (solour space name), 'I' (boolean flag to indicate isolated group) and 'K' (boolean flag to indicate knockout group).
	 * @return int the XObject Template ID in case of success or false in case of error.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.017 (2010-08-24)
	 * @see endTemplate(), printTemplate()
	 */
	public function startTemplate($w=0, $h=0, $group=false) {
		if ($this->inxobj) {
			// we are already inside an XObject template
			return false;
		}
		$this->inxobj = true;
		++$this->n;
		// XObject ID
		$this->xobjid = 'XT'.$this->n;
		// object ID
		$this->xobjects[$this->xobjid] = array('n' => $this->n);
		// store current graphic state
		$this->xobjects[$this->xobjid]['gvars'] = $this->getGraphicVars();
		// initialize data
		$this->xobjects[$this->xobjid]['intmrk'] = 0;
		$this->xobjects[$this->xobjid]['transfmrk'] = array();
		$this->xobjects[$this->xobjid]['outdata'] = '';
		$this->xobjects[$this->xobjid]['xobjects'] = array();
		$this->xobjects[$this->xobjid]['images'] = array();
		$this->xobjects[$this->xobjid]['fonts'] = array();
		$this->xobjects[$this->xobjid]['annotations'] = array();
		$this->xobjects[$this->xobjid]['extgstates'] = array();
		$this->xobjects[$this->xobjid]['gradients'] = array();
		$this->xobjects[$this->xobjid]['spot_colors'] = array();
		// set new environment
		$this->num_columns = 1;
		$this->current_column = 0;
		$this->SetAutoPageBreak(false);
		if (($w === '') OR ($w <= 0)) {
			$w = $this->w - $this->lMargin - $this->rMargin;
		}
		if (($h === '') OR ($h <= 0)) {
			$h = $this->h - $this->tMargin - $this->bMargin;
		}
		$this->xobjects[$this->xobjid]['x'] = 0;
		$this->xobjects[$this->xobjid]['y'] = 0;
		$this->xobjects[$this->xobjid]['w'] = $w;
		$this->xobjects[$this->xobjid]['h'] = $h;
		$this->w = $w;
		$this->h = $h;
		$this->wPt = $this->w * $this->k;
		$this->hPt = $this->h * $this->k;
		$this->fwPt = $this->wPt;
		$this->fhPt = $this->hPt;
		$this->x = 0;
		$this->y = 0;
		$this->lMargin = 0;
		$this->rMargin = 0;
		$this->tMargin = 0;
		$this->bMargin = 0;
		// set group mode
		$this->xobjects[$this->xobjid]['group'] = $group;
		return $this->xobjid;
	}

	/**
	 * End the current XObject Template started with startTemplate() and restore the previous graphic state.
	 * An XObject Template is a PDF block that is a self-contained description of any sequence of graphics objects (including path objects, text objects, and sampled images).
	 * An XObject Template may be painted multiple times, either on several pages or at several locations on the same page and produces the same results each time, subject only to the graphics state at the time it is invoked.
	 * @return int the XObject Template ID in case of success or false in case of error.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.017 (2010-08-24)
	 * @see startTemplate(), printTemplate()
	 */
	public function endTemplate() {
		if (!$this->inxobj) {
			// we are not inside a template
			return false;
		}
		$this->inxobj = false;
		// restore previous graphic state
		$this->setGraphicVars($this->xobjects[$this->xobjid]['gvars'], true);
		return $this->xobjid;
	}

	/**
	 * Print an XObject Template.
	 * You can print an XObject Template inside the currently opened Template.
	 * An XObject Template is a PDF block that is a self-contained description of any sequence of graphics objects (including path objects, text objects, and sampled images).
	 * An XObject Template may be painted multiple times, either on several pages or at several locations on the same page and produces the same results each time, subject only to the graphics state at the time it is invoked.
	 * @param $id (string) The ID of XObject Template to print.
	 * @param $x (int) X position in user units (empty string = current x position)
	 * @param $y (int) Y position in user units (empty string = current y position)
	 * @param $w (int) Width in user units (zero = remaining page width)
	 * @param $h (int) Height in user units (zero = remaining page height)
	 * @param $align (string) Indicates the alignment of the pointer next to template insertion relative to template height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $palign (string) Allows to center or align the template on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $fitonpage (boolean) If true the template is resized to not exceed page dimensions.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.017 (2010-08-24)
	 * @see startTemplate(), endTemplate()
	 */
	public function printTemplate($id, $x='', $y='', $w=0, $h=0, $align='', $palign='', $fitonpage=false) {
		if ($this->state != 2) {
			 return;
		}
		if (!isset($this->xobjects[$id])) {
			$this->Error('The XObject Template \''.$id.'\' doesn\'t exist!');
		}
		if ($this->inxobj) {
			if ($id == $this->xobjid) {
				// close current template
				$this->endTemplate();
			} else {
				// use the template as resource for the template currently opened
				$this->xobjects[$this->xobjid]['xobjects'][$id] = $this->xobjects[$id];
			}
		}
		// set default values
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$ow = $this->xobjects[$id]['w'];
		if ($ow <= 0) {
			$ow = 1;
		}
		$oh = $this->xobjects[$id]['h'];
		if ($oh <= 0) {
			$oh = 1;
		}
		// calculate template width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			$w = $ow;
			$h = $oh;
		} elseif ($w <= 0) {
			$w = $h * $ow / $oh;
		} elseif ($h <= 0) {
			$h = $w * $oh / $ow;
		}
		// fit the template on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		// set page alignment
		$rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$xt = $this->lMargin;
			} elseif ($palign == 'C') {
				$xt = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$xt = $this->w - $this->rMargin - $w;
			} else {
				$xt = $x - $w;
			}
			$rb_x = $xt;
		} else {
			if ($palign == 'L') {
				$xt = $this->lMargin;
			} elseif ($palign == 'C') {
				$xt = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$xt = $this->w - $this->rMargin - $w;
			} else {
				$xt = $x;
			}
			$rb_x = $xt + $w;
		}
		// print XObject Template + Transformation matrix
		$this->StartTransform();
		// translate and scale
		$sx = ($w / $ow);
		$sy = ($h / $oh);
		$tm = array();
		$tm[0] = $sx;
		$tm[1] = 0;
		$tm[2] = 0;
		$tm[3] = $sy;
		$tm[4] = $xt * $this->k;
		$tm[5] = ($this->h - $h - $y) * $this->k;
		$this->Transform($tm);
		// set object
		$this->_out('/'.$id.' Do');
		$this->StopTransform();
		// add annotations
		if (!empty($this->xobjects[$id]['annotations'])) {
			foreach ($this->xobjects[$id]['annotations'] as $annot) {
				// transform original coordinates
				$coordlt = TCPDF_STATIC::getTransformationMatrixProduct($tm, array(1, 0, 0, 1, ($annot['x'] * $this->k), (-$annot['y'] * $this->k)));
				$ax = ($coordlt[4] / $this->k);
				$ay = ($this->h - $h - ($coordlt[5] / $this->k));
				$coordrb = TCPDF_STATIC::getTransformationMatrixProduct($tm, array(1, 0, 0, 1, (($annot['x'] + $annot['w']) * $this->k), ((-$annot['y'] - $annot['h']) * $this->k)));
				$aw = ($coordrb[4] / $this->k) - $ax;
				$ah = ($this->h - $h - ($coordrb[5] / $this->k)) - $ay;
				$this->Annotation($ax, $ay, $aw, $ah, $annot['text'], $annot['opt'], $annot['spaces']);
			}
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T': {
				$this->y = $y;
				$this->x = $rb_x;
				break;
			}
			case 'M': {
				$this->y = $y + round($h/2);
				$this->x = $rb_x;
				break;
			}
			case 'B': {
				$this->y = $rb_y;
				$this->x = $rb_x;
				break;
			}
			case 'N': {
				$this->SetY($rb_y);
				break;
			}
			default:{
				break;
			}
		}
	}

	/**
	 * Set the percentage of character stretching.
	 * @param $perc (int) percentage of stretching (100 = no stretching)
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function setFontStretching($perc=100) {
		$this->font_stretching = $perc;
	}

	/**
	 * Get the percentage of character stretching.
	 * @return float stretching value
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function getFontStretching() {
		return $this->font_stretching;
	}

	/**
	 * Set the amount to increase or decrease the space between characters in a text.
	 * @param $spacing (float) amount to increase or decrease the space between characters in a text (0 = default spacing)
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function setFontSpacing($spacing=0) {
		$this->font_spacing = $spacing;
	}

	/**
	 * Get the amount to increase or decrease the space between characters in a text.
	 * @return int font spacing (tracking) value
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function getFontSpacing() {
		return $this->font_spacing;
	}

	/**
	 * Return an array of no-write page regions
	 * @return array of no-write page regions
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see setPageRegions(), addPageRegion()
	 */
	public function getPageRegions() {
		return $this->page_regions;
	}

	/**
	 * Set no-write regions on page.
	 * A no-write region is a portion of the page with a rectangular or trapezium shape that will not be covered when writing text or html code.
	 * A region is always aligned on the left or right side of the page ad is defined using a vertical segment.
	 * You can set multiple regions for the same page.
	 * @param $regions (array) array of no-write regions. For each region you can define an array as follow: ('page' => page number or empy for current page, 'xt' => X top, 'yt' => Y top, 'xb' => X bottom, 'yb' => Y bottom, 'side' => page side 'L' = left or 'R' = right). Omit this parameter to remove all regions.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see addPageRegion(), getPageRegions()
	 */
	public function setPageRegions($regions=array()) {
		// empty current regions array
		$this->page_regions = array();
		// add regions
		foreach ($regions as $data) {
			$this->addPageRegion($data);
		}
	}

	/**
	 * Add a single no-write region on selected page.
	 * A no-write region is a portion of the page with a rectangular or trapezium shape that will not be covered when writing text or html code.
	 * A region is always aligned on the left or right side of the page ad is defined using a vertical segment.
	 * You can set multiple regions for the same page.
	 * @param $region (array) array of a single no-write region array: ('page' => page number or empy for current page, 'xt' => X top, 'yt' => Y top, 'xb' => X bottom, 'yb' => Y bottom, 'side' => page side 'L' = left or 'R' = right).
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see setPageRegions(), getPageRegions()
	 */
	public function addPageRegion($region) {
		if (!isset($region['page']) OR empty($region['page'])) {
			$region['page'] = $this->page;
		}
		if (isset($region['xt']) AND isset($region['xb']) AND ($region['xt'] > 0) AND ($region['xb'] > 0)
			AND isset($region['yt'])  AND isset($region['yb']) AND ($region['yt'] >= 0) AND ($region['yt'] < $region['yb'])
			AND isset($region['side']) AND (($region['side'] == 'L') OR ($region['side'] == 'R'))) {
			$this->page_regions[] = $region;
		}
	}

	/**
	 * Remove a single no-write region.
	 * @param $key (int) region key
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see setPageRegions(), getPageRegions()
	 */
	public function removePageRegion($key) {
		if (isset($this->page_regions[$key])) {
			unset($this->page_regions[$key]);
		}
	}

	/**
	 * Check page for no-write regions and adapt current coordinates and page margins if necessary.
	 * A no-write region is a portion of the page with a rectangular or trapezium shape that will not be covered when writing text or html code.
	 * A region is always aligned on the left or right side of the page ad is defined using a vertical segment.
	 * @param $h (float) height of the text/image/object to print in user units
	 * @param $x (float) current X coordinate in user units
	 * @param $y (float) current Y coordinate in user units
	 * @return array($x, $y)
	 * @author Nicola Asuni
	 * @protected
	 * @since 5.9.003 (2010-10-13)
	 */
	protected function checkPageRegions($h, $x, $y) {
		// set default values
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		if (!$this->check_page_regions OR empty($this->page_regions)) {
			// no page regions defined
			return array($x, $y);
		}
		if (empty($h)) {
			$h = ($this->FontSize * $this->cell_height_ratio) + $this->cell_padding['T'] + $this->cell_padding['B'];
		}
		// check for page break
		if ($this->checkPageBreak($h, $y)) {
			// the content will be printed on a new page
			$x = $this->x;
			$y = $this->y;
		}
		if ($this->num_columns > 1) {
			if ($this->rtl) {
				$this->lMargin = ($this->columns[$this->current_column]['x'] - $this->columns[$this->current_column]['w']);
			} else {
				$this->rMargin = ($this->w - $this->columns[$this->current_column]['x'] - $this->columns[$this->current_column]['w']);
			}
		} else {
			if ($this->rtl) {
				$this->lMargin = max($this->clMargin, $this->original_lMargin);
			} else {
				$this->rMargin = max($this->crMargin, $this->original_rMargin);
			}
		}
		// adjust coordinates and page margins
		foreach ($this->page_regions as $regid => $regdata) {
			if ($regdata['page'] == $this->page) {
				// check region boundaries
				if (($y > ($regdata['yt'] - $h)) AND ($y <= $regdata['yb'])) {
					// Y is inside the region
					$minv = ($regdata['xb'] - $regdata['xt']) / ($regdata['yb'] - $regdata['yt']); // inverse of angular coefficient
					$yt = max($y, $regdata['yt']);
					$yb = min(($yt + $h), $regdata['yb']);
					$xt = (($yt - $regdata['yt']) * $minv) + $regdata['xt'];
					$xb = (($yb - $regdata['yt']) * $minv) + $regdata['xt'];
					if ($regdata['side'] == 'L') { // left side
						$new_margin = max($xt, $xb);
						if ($this->lMargin < $new_margin) {
							if ($this->rtl) {
								// adjust left page margin
								$this->lMargin = max(0, $new_margin);
							}
							if ($x < $new_margin) {
								// adjust x position
								$x = $new_margin;
								if ($new_margin > ($this->w - $this->rMargin)) {
									// adjust y position
									$y = $regdata['yb'] - $h;
								}
							}
						}
					} elseif ($regdata['side'] == 'R') { // right side
						$new_margin = min($xt, $xb);
						if (($this->w - $this->rMargin) > $new_margin) {
							if (!$this->rtl) {
								// adjust right page margin
								$this->rMargin = max(0, ($this->w - $new_margin));
							}
							if ($x > $new_margin) {
								// adjust x position
								$x = $new_margin;
								if ($new_margin > $this->lMargin) {
									// adjust y position
									$y = $regdata['yb'] - $h;
								}
							}
						}
					}
				}
			}
		}
		return array($x, $y);
	}

	// --- SVG METHODS ---------------------------------------------------------

	/**
	 * Embedd a Scalable Vector Graphics (SVG) image.
	 * NOTE: SVG standard is not yet fully implemented, use the setRasterizeVectorImages() method to enable/disable rasterization of vector images using ImageMagick library.
	 * @param $file (string) Name of the SVG file or a '@' character followed by the SVG data string.
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $h (float) Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $align (string) Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul> If the alignment is an empty string, then the pointer will be restored on the starting SVG position.
	 * @param $palign (string) Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $fitonpage (boolean) if true the image is resized to not exceed page dimensions.
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @public
	 */
	public function ImageSVG($file, $x='', $y='', $w=0, $h=0, $link='', $align='', $palign='', $border=0, $fitonpage=false) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rasterize_vector_images AND ($w > 0) AND ($h > 0)) {
			// convert SVG to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'SVG', $link, $align, true, 300, $palign, false, false, $border, false, false, false);
		}
		if ($file{0} === '@') { // image from string
			$this->svgdir = '';
			$svgdata = substr($file, 1);
		} else { // SVG file
			$this->svgdir = dirname($file);
			$svgdata = TCPDF_STATIC::fileGetContents($file);
		}
		if ($svgdata === FALSE) {
			$this->Error('SVG file not found: '.$file);
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$k = $this->k;
		$ox = 0;
		$oy = 0;
		$ow = $w;
		$oh = $h;
		$aspect_ratio_align = 'xMidYMid';
		$aspect_ratio_ms = 'meet';
		$regs = array();
		// get original image width and height
		preg_match('/<svg([^\>]*)>/si', $svgdata, $regs);
		if (isset($regs[1]) AND !empty($regs[1])) {
			$tmp = array();
			if (preg_match('/[\s]+x[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$ox = $this->getHTMLUnitToUnits($tmp[1], 0, $this->svgunit, false);
			}
			$tmp = array();
			if (preg_match('/[\s]+y[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$oy = $this->getHTMLUnitToUnits($tmp[1], 0, $this->svgunit, false);
			}
			$tmp = array();
			if (preg_match('/[\s]+width[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$ow = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
			}
			$tmp = array();
			if (preg_match('/[\s]+height[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$oh = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
			}
			$tmp = array();
			$view_box = array();
			if (preg_match('/[\s]+viewBox[\s]*=[\s]*"[\s]*([0-9\.\-]+)[\s]+([0-9\.\-]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]*"/si', $regs[1], $tmp)) {
				if (count($tmp) == 5) {
					array_shift($tmp);
					foreach ($tmp as $key => $val) {
						$view_box[$key] = $this->getHTMLUnitToUnits($val, 0, $this->svgunit, false);
					}
					$ox = $view_box[0];
					$oy = $view_box[1];
				}
				// get aspect ratio
				$tmp = array();
				if (preg_match('/[\s]+preserveAspectRatio[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
					$aspect_ratio = preg_split('/[\s]+/si', $tmp[1]);
					switch (count($aspect_ratio)) {
						case 3: {
							$aspect_ratio_align = $aspect_ratio[1];
							$aspect_ratio_ms = $aspect_ratio[2];
							break;
						}
						case 2: {
							$aspect_ratio_align = $aspect_ratio[0];
							$aspect_ratio_ms = $aspect_ratio[1];
							break;
						}
						case 1: {
							$aspect_ratio_align = $aspect_ratio[0];
							$aspect_ratio_ms = 'meet';
							break;
						}
					}
				}
			}
		}
		if ($ow <= 0) {
			$ow = 1;
		}
		if ($oh <= 0) {
			$oh = 1;
		}
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			// convert image size to document unit
			$w = $ow;
			$h = $oh;
		} elseif ($w <= 0) {
			$w = $h * $ow / $oh;
		} elseif ($h <= 0) {
			$h = $w * $oh / $ow;
		}
		// fit the image on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		if ($this->rasterize_vector_images) {
			// convert SVG to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'SVG', $link, $align, true, 300, $palign, false, false, $border, false, false, false);
		}
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x - $w;
			}
			$this->img_rb_x = $ximg;
		} else {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x;
			}
			$this->img_rb_x = $ximg + $w;
		}
		// store current graphic vars
		$gvars = $this->getGraphicVars();
		// store SVG position and scale factors
		$svgoffset_x = ($ximg - $ox) * $this->k;
		$svgoffset_y = -($y - $oy) * $this->k;
		if (isset($view_box[2]) AND ($view_box[2] > 0) AND ($view_box[3] > 0)) {
			$ow = $view_box[2];
			$oh = $view_box[3];
		} else {
			if ($ow <= 0) {
				$ow = $w;
			}
			if ($oh <= 0) {
				$oh = $h;
			}
		}
		$svgscale_x = $w / $ow;
		$svgscale_y = $h / $oh;
		// scaling and alignment
		if ($aspect_ratio_align != 'none') {
			// store current scaling values
			$svgscale_old_x = $svgscale_x;
			$svgscale_old_y = $svgscale_y;
			// force uniform scaling
			if ($aspect_ratio_ms == 'slice') {
				// the entire viewport is covered by the viewBox
				if ($svgscale_x > $svgscale_y) {
					$svgscale_y = $svgscale_x;
				} elseif ($svgscale_x < $svgscale_y) {
					$svgscale_x = $svgscale_y;
				}
			} else { // meet
				// the entire viewBox is visible within the viewport
				if ($svgscale_x < $svgscale_y) {
					$svgscale_y = $svgscale_x;
				} elseif ($svgscale_x > $svgscale_y) {
					$svgscale_x = $svgscale_y;
				}
			}
			// correct X alignment
			switch (substr($aspect_ratio_align, 1, 3)) {
				case 'Min': {
					// do nothing
					break;
				}
				case 'Max': {
					$svgoffset_x += (($w * $this->k) - ($ow * $this->k * $svgscale_x));
					break;
				}
				default:
				case 'Mid': {
					$svgoffset_x += ((($w * $this->k) - ($ow * $this->k * $svgscale_x)) / 2);
					break;
				}
			}
			// correct Y alignment
			switch (substr($aspect_ratio_align, 5)) {
				case 'Min': {
					// do nothing
					break;
				}
				case 'Max': {
					$svgoffset_y -= (($h * $this->k) - ($oh * $this->k * $svgscale_y));
					break;
				}
				default:
				case 'Mid': {
					$svgoffset_y -= ((($h * $this->k) - ($oh * $this->k * $svgscale_y)) / 2);
					break;
				}
			}
		}
		// store current page break mode
		$page_break_mode = $this->AutoPageBreak;
		$page_break_margin = $this->getBreakMargin();
		$cell_padding = $this->cell_padding;
		$this->SetCellPadding(0);
		$this->SetAutoPageBreak(false);
		// save the current graphic state
		$this->_out('q'.$this->epsmarker);
		// set initial clipping mask
		$this->Rect($x, $y, $w, $h, 'CNZ', array(), array());
		// scale and translate
		$e = $ox * $this->k * (1 - $svgscale_x);
		$f = ($this->h - $oy) * $this->k * (1 - $svgscale_y);
		$this->_out(sprintf('%F %F %F %F %F %F cm', $svgscale_x, 0, 0, $svgscale_y, ($e + $svgoffset_x), ($f + $svgoffset_y)));
		// creates a new XML parser to be used by the other XML functions
		$this->parser = xml_parser_create('UTF-8');
		// the following function allows to use parser inside object
		xml_set_object($this->parser, $this);
		// disable case-folding for this XML parser
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
		// sets the element handler functions for the XML parser
		xml_set_element_handler($this->parser, 'startSVGElementHandler', 'endSVGElementHandler');
		// sets the character data handler function for the XML parser
		xml_set_character_data_handler($this->parser, 'segSVGContentHandler');
		// start parsing an XML document
		if (!xml_parse($this->parser, $svgdata)) {
			$error_message = sprintf('SVG Error: %s at line %d', xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser));
			$this->Error($error_message);
		}
		// free this XML parser
		xml_parser_free($this->parser);
		// restore previous graphic state
		$this->_out($this->epsmarker.'Q');
		// restore graphic vars
		$this->setGraphicVars($gvars);
		$this->lasth = $gvars['lasth'];
		if (!empty($border)) {
			$bx = $this->x;
			$by = $this->y;
			$this->x = $ximg;
			if ($this->rtl) {
				$this->x += $w;
			}
			$this->y = $y;
			$this->Cell($w, $h, '', $border, 0, '', 0, '', 0, true);
			$this->x = $bx;
			$this->y = $by;
		}
		if ($link) {
			$this->Link($ximg, $y, $w, $h, $link, 0);
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->SetY($this->img_rb_y);
				break;
			}
			default:{
				// restore pointer to starting position
				$this->x = $gvars['x'];
				$this->y = $gvars['y'];
				$this->page = $gvars['page'];
				$this->current_column = $gvars['current_column'];
				$this->tMargin = $gvars['tMargin'];
				$this->bMargin = $gvars['bMargin'];
				$this->w = $gvars['w'];
				$this->h = $gvars['h'];
				$this->wPt = $gvars['wPt'];
				$this->hPt = $gvars['hPt'];
				$this->fwPt = $gvars['fwPt'];
				$this->fhPt = $gvars['fhPt'];
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
		// restore page break
		$this->SetAutoPageBreak($page_break_mode, $page_break_margin);
		$this->cell_padding = $cell_padding;
	}

	/**
	 * Convert SVG transformation matrix to PDF.
	 * @param $tm (array) original SVG transformation matrix
	 * @return array transformation matrix
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected function convertSVGtMatrix($tm) {
		$a = $tm[0];
		$b = -$tm[1];
		$c = -$tm[2];
		$d = $tm[3];
		$e = $this->getHTMLUnitToUnits($tm[4], 1, $this->svgunit, false) * $this->k;
		$f = -$this->getHTMLUnitToUnits($tm[5], 1, $this->svgunit, false) * $this->k;
		$x = 0;
		$y = $this->h * $this->k;
		$e = ($x * (1 - $a)) - ($y * $c) + $e;
		$f = ($y * (1 - $d)) - ($x * $b) + $f;
		return array($a, $b, $c, $d, $e, $f);
	}

	/**
	 * Apply SVG graphic transformation matrix.
	 * @param $tm (array) original SVG transformation matrix
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected function SVGTransform($tm) {
		$this->Transform($this->convertSVGtMatrix($tm));
	}

	/**
	 * Apply the requested SVG styles (*** TO BE COMPLETED ***)
	 * @param $svgstyle (array) array of SVG styles to apply
	 * @param $prevsvgstyle (array) array of previous SVG style
	 * @param $x (int) X origin of the bounding box
	 * @param $y (int) Y origin of the bounding box
	 * @param $w (int) width of the bounding box
	 * @param $h (int) height of the bounding box
	 * @param $clip_function (string) clip function
	 * @param $clip_params (array) array of parameters for clipping function
	 * @return object style
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @protected
	 */
	protected function setSVGStyles($svgstyle, $prevsvgstyle, $x=0, $y=0, $w=1, $h=1, $clip_function='', $clip_params=array()) {
		if ($this->state != 2) {
			 return;
		}
		$objstyle = '';
		$minlen = (0.01 / $this->k); // minimum acceptable length (3 point)
		if (!isset($svgstyle['opacity'])) {
			return $objstyle;
		}
		// clip-path
		$regs = array();
		if (preg_match('/url\([\s]*\#([^\)]*)\)/si', $svgstyle['clip-path'], $regs)) {
			$clip_path = $this->svgclippaths[$regs[1]];
			foreach ($clip_path as $cp) {
				$this->startSVGElementHandler('clip-path', $cp['name'], $cp['attribs'], $cp['tm']);
			}
		}
		// opacity
		if ($svgstyle['opacity'] != 1) {
			$this->setAlpha($svgstyle['opacity'], 'Normal', $svgstyle['opacity'], false);
		}
		// color
		$fill_color = TCPDF_COLORS::convertHTMLColorToDec($svgstyle['color'], $this->spot_colors);
		$this->SetFillColorArray($fill_color);
		// text color
		$text_color = TCPDF_COLORS::convertHTMLColorToDec($svgstyle['text-color'], $this->spot_colors);
		$this->SetTextColorArray($text_color);
		// clip
		if (preg_match('/rect\(([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)\)/si', $svgstyle['clip'], $regs)) {
			$top = (isset($regs[1])?$this->getHTMLUnitToUnits($regs[1], 0, $this->svgunit, false):0);
			$right = (isset($regs[2])?$this->getHTMLUnitToUnits($regs[2], 0, $this->svgunit, false):0);
			$bottom = (isset($regs[3])?$this->getHTMLUnitToUnits($regs[3], 0, $this->svgunit, false):0);
			$left = (isset($regs[4])?$this->getHTMLUnitToUnits($regs[4], 0, $this->svgunit, false):0);
			$cx = $x + $left;
			$cy = $y + $top;
			$cw = $w - $left - $right;
			$ch = $h - $top - $bottom;
			if ($svgstyle['clip-rule'] == 'evenodd') {
				$clip_rule = 'CNZ';
			} else {
				$clip_rule = 'CEO';
			}
			$this->Rect($cx, $cy, $cw, $ch, $clip_rule, array(), array());
		}
		// fill
		$regs = array();
		if (preg_match('/url\([\s]*\#([^\)]*)\)/si', $svgstyle['fill'], $regs)) {
			// gradient
			$gradient = $this->svggradients[$regs[1]];
			if (isset($gradient['xref'])) {
				// reference to another gradient definition
				$newgradient = $this->svggradients[$gradient['xref']];
				$newgradient['coords'] = $gradient['coords'];
				$newgradient['mode'] = $gradient['mode'];
				$newgradient['gradientUnits'] = $gradient['gradientUnits'];
				if (isset($gradient['gradientTransform'])) {
					$newgradient['gradientTransform'] = $gradient['gradientTransform'];
				}
				$gradient = $newgradient;
			}
			//save current Graphic State
			$this->_out('q');
			//set clipping area
			if (!empty($clip_function) AND method_exists($this, $clip_function)) {
				$bbox = call_user_func_array(array($this, $clip_function), $clip_params);
				if (is_array($bbox) AND (count($bbox) == 4)) {
					list($x, $y, $w, $h) = $bbox;
				}
			}
			if ($gradient['mode'] == 'measure') {
				if (isset($gradient['gradientTransform']) AND !empty($gradient['gradientTransform'])) {
					$gtm = $gradient['gradientTransform'];
					// apply transformation matrix
					$xa = ($gtm[0] * $gradient['coords'][0]) + ($gtm[2] * $gradient['coords'][1]) + $gtm[4];
					$ya = ($gtm[1] * $gradient['coords'][0]) + ($gtm[3] * $gradient['coords'][1]) + $gtm[5];
					$xb = ($gtm[0] * $gradient['coords'][2]) + ($gtm[2] * $gradient['coords'][3]) + $gtm[4];
					$yb = ($gtm[1] * $gradient['coords'][2]) + ($gtm[3] * $gradient['coords'][3]) + $gtm[5];
					if (isset($gradient['coords'][4])) {
						$gradient['coords'][4] = sqrt(pow(($gtm[0] * $gradient['coords'][4]), 2) + pow(($gtm[1] * $gradient['coords'][4]), 2));
					}
					$gradient['coords'][0] = $xa;
					$gradient['coords'][1] = $ya;
					$gradient['coords'][2] = $xb;
					$gradient['coords'][3] = $yb;
				}
				// convert SVG coordinates to user units
				$gradient['coords'][0] = $this->getHTMLUnitToUnits($gradient['coords'][0], 0, $this->svgunit, false);
				$gradient['coords'][1] = $this->getHTMLUnitToUnits($gradient['coords'][1], 0, $this->svgunit, false);
				$gradient['coords'][2] = $this->getHTMLUnitToUnits($gradient['coords'][2], 0, $this->svgunit, false);
				$gradient['coords'][3] = $this->getHTMLUnitToUnits($gradient['coords'][3], 0, $this->svgunit, false);
				if (isset($gradient['coords'][4])) {
					$gradient['coords'][4] = $this->getHTMLUnitToUnits($gradient['coords'][4], 0, $this->svgunit, false);
				}
				if ($w <= $minlen) {
					$w = $minlen;
				}
				if ($h <= $minlen) {
					$h = $minlen;
				}
				// shift units
				if ($gradient['gradientUnits'] == 'objectBoundingBox') {
					// convert to SVG coordinate system
					$gradient['coords'][0] += $x;
					$gradient['coords'][1] += $y;
					$gradient['coords'][2] += $x;
					$gradient['coords'][3] += $y;
				}
				// calculate percentages
				$gradient['coords'][0] = (($gradient['coords'][0] - $x) / $w);
				$gradient['coords'][1] = (($gradient['coords'][1] - $y) / $h);
				$gradient['coords'][2] = (($gradient['coords'][2] - $x) / $w);
				$gradient['coords'][3] = (($gradient['coords'][3] - $y) / $h);
				if (isset($gradient['coords'][4])) {
					$gradient['coords'][4] /= $w;
				}
			} elseif ($gradient['mode'] == 'percentage') {
				foreach($gradient['coords'] as $key => $val) {
					$gradient['coords'][$key] = (intval($val) / 100);
					if ($val < 0) {
						$gradient['coords'][$key] = 0;
					} elseif ($val > 1) {
						$gradient['coords'][$key] = 1;
					}
				}
			}
			if (($gradient['type'] == 2) AND ($gradient['coords'][0] == $gradient['coords'][2]) AND ($gradient['coords'][1] == $gradient['coords'][3])) {
				// single color (no shading)
				$gradient['coords'][0] = 1;
				$gradient['coords'][1] = 0;
				$gradient['coords'][2] = 0.999;
				$gradient['coords'][3] = 0;
			}
			// swap Y coordinates
			$tmp = $gradient['coords'][1];
			$gradient['coords'][1] = $gradient['coords'][3];
			$gradient['coords'][3] = $tmp;
			// set transformation map for gradient
			if ($gradient['type'] == 3) {
				// circular gradient
				$cy = $this->h - $y - ($gradient['coords'][1] * ($w + $h));
				$this->_out(sprintf('%F 0 0 %F %F %F cm', ($w * $this->k), ($w * $this->k), ($x * $this->k), ($cy * $this->k)));
			} else {
				$this->_out(sprintf('%F 0 0 %F %F %F cm', ($w * $this->k), ($h * $this->k), ($x * $this->k), (($this->h - ($y + $h)) * $this->k)));
			}
			if (count($gradient['stops']) > 1) {
				$this->Gradient($gradient['type'], $gradient['coords'], $gradient['stops'], array(), false);
			}
		} elseif ($svgstyle['fill'] != 'none') {
			$fill_color = TCPDF_COLORS::convertHTMLColorToDec($svgstyle['fill'], $this->spot_colors);
			if ($svgstyle['fill-opacity'] != 1) {
				$this->setAlpha($this->alpha['CA'], 'Normal', $svgstyle['fill-opacity'], false);
			}
			$this->SetFillColorArray($fill_color);
			if ($svgstyle['fill-rule'] == 'evenodd') {
				$objstyle .= 'F*';
			} else {
				$objstyle .= 'F';
			}
		}
		// stroke
		if ($svgstyle['stroke'] != 'none') {
			if ($svgstyle['stroke-opacity'] != 1) {
				$this->setAlpha($svgstyle['stroke-opacity'], 'Normal', $this->alpha['ca'], false);
			}
			$stroke_style = array(
				'color' => TCPDF_COLORS::convertHTMLColorToDec($svgstyle['stroke'], $this->spot_colors),
				'width' => $this->getHTMLUnitToUnits($svgstyle['stroke-width'], 0, $this->svgunit, false),
				'cap' => $svgstyle['stroke-linecap'],
				'join' => $svgstyle['stroke-linejoin']
				);
			if (isset($svgstyle['stroke-dasharray']) AND !empty($svgstyle['stroke-dasharray']) AND ($svgstyle['stroke-dasharray'] != 'none')) {
				$stroke_style['dash'] = $svgstyle['stroke-dasharray'];
			}
			$this->SetLineStyle($stroke_style);
			$objstyle .= 'D';
		}
		// font
		$regs = array();
		if (!empty($svgstyle['font'])) {
			if (preg_match('/font-family[\s]*:[\s]*([^\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_family = $this->getFontFamilyName($regs[1]);
			} else {
				$font_family = $svgstyle['font-family'];
			}
			if (preg_match('/font-size[\s]*:[\s]*([^\s\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_size = trim($regs[1]);
			} else {
				$font_size = $svgstyle['font-size'];
			}
			if (preg_match('/font-style[\s]*:[\s]*([^\s\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_style = trim($regs[1]);
			} else {
				$font_style = $svgstyle['font-style'];
			}
			if (preg_match('/font-weight[\s]*:[\s]*([^\s\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_weight = trim($regs[1]);
			} else {
				$font_weight = $svgstyle['font-weight'];
			}
			if (preg_match('/font-stretch[\s]*:[\s]*([^\s\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_stretch = trim($regs[1]);
			} else {
				$font_stretch = $svgstyle['font-stretch'];
			}
			if (preg_match('/letter-spacing[\s]*:[\s]*([^\s\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_spacing = trim($regs[1]);
			} else {
				$font_spacing = $svgstyle['letter-spacing'];
			}
		} else {
			$font_family = $this->getFontFamilyName($svgstyle['font-family']);
			$font_size = $svgstyle['font-size'];
			$font_style = $svgstyle['font-style'];
			$font_weight = $svgstyle['font-weight'];
			$font_stretch = $svgstyle['font-stretch'];
			$font_spacing = $svgstyle['letter-spacing'];
		}
		$font_size = $this->getHTMLFontUnits($font_size, $this->svgstyles[0]['font-size'], $prevsvgstyle['font-size'], $this->svgunit);
		$font_stretch = $this->getCSSFontStretching($font_stretch, $svgstyle['font-stretch']);
		$font_spacing = $this->getCSSFontSpacing($font_spacing, $svgstyle['letter-spacing']);
		switch ($font_style) {
			case 'italic': {
				$font_style = 'I';
				break;
			}
			case 'oblique': {
				$font_style = 'I';
				break;
			}
			default:
			case 'normal': {
				$font_style = '';
				break;
			}
		}
		switch ($font_weight) {
			case 'bold':
			case 'bolder': {
				$font_style .= 'B';
				break;
			}
		}
		switch ($svgstyle['text-decoration']) {
			case 'underline': {
				$font_style .= 'U';
				break;
			}
			case 'overline': {
				$font_style .= 'O';
				break;
			}
			case 'line-through': {
				$font_style .= 'D';
				break;
			}
			default:
			case 'none': {
				break;
			}
		}
		$this->SetFont($font_family, $font_style, $font_size);
		$this->setFontStretching($font_stretch);
		$this->setFontSpacing($font_spacing);
		return $objstyle;
	}

	/**
	 * Draws an SVG path
	 * @param $d (string) attribute d of the path SVG element
	 * @param $style (string) Style of rendering. Possible values are:
	 * <ul>
	 *	 <li>D or empty string: Draw (default).</li>
	 *	 <li>F: Fill.</li>
	 *	 <li>F*: Fill using the even-odd rule to determine which regions lie inside the clipping path.</li>
	 *	 <li>DF or FD: Draw and fill.</li>
	 *	 <li>DF* or FD*: Draw and fill using the even-odd rule to determine which regions lie inside the clipping path.</li>
	 *	 <li>CNZ: Clipping mode (using the even-odd rule to determine which regions lie inside the clipping path).</li>
	 *	 <li>CEO: Clipping mode (using the nonzero winding number rule to determine which regions lie inside the clipping path).</li>
	 * </ul>
	 * @return array of container box measures (x, y, w, h)
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @protected
	 */
	protected function SVGPath($d, $style='') {
		if ($this->state != 2) {
			 return;
		}
		// set fill/stroke style
		$op = TCPDF_STATIC::getPathPaintOperator($style, '');
		if (empty($op)) {
			return;
		}
		$paths = array();
		$d = preg_replace('/([0-9ACHLMQSTVZ])([\-\+])/si', '\\1 \\2', $d);
		preg_match_all('/([ACHLMQSTVZ])[\s]*([^ACHLMQSTVZ\"]*)/si', $d, $paths, PREG_SET_ORDER);
		$x = 0;
		$y = 0;
		$x1 = 0;
		$y1 = 0;
		$x2 = 0;
		$y2 = 0;
		$xmin = 2147483647;
		$xmax = 0;
		$ymin = 2147483647;
		$ymax = 0;
		$relcoord = false;
		$minlen = (0.01 / $this->k); // minimum acceptable length (3 point)
		$firstcmd = true; // used to print first point
		// draw curve pieces
		foreach ($paths as $key => $val) {
			// get curve type
			$cmd = trim($val[1]);
			if (strtolower($cmd) == $cmd) {
				// use relative coordinated instead of absolute
				$relcoord = true;
				$xoffset = $x;
				$yoffset = $y;
			} else {
				$relcoord = false;
				$xoffset = 0;
				$yoffset = 0;
			}
			$params = array();
			if (isset($val[2])) {
				// get curve parameters
				$rawparams = preg_split('/([\,\s]+)/si', trim($val[2]));
				$params = array();
				foreach ($rawparams as $ck => $cp) {
					$params[$ck] = $this->getHTMLUnitToUnits($cp, 0, $this->svgunit, false);
					if (abs($params[$ck]) < $minlen) {
						// aproximate little values to zero
						$params[$ck] = 0;
					}
				}
			}
			// store current origin point
			$x0 = $x;
			$y0 = $y;
			switch (strtoupper($cmd)) {
				case 'M': { // moveto
					foreach ($params as $ck => $cp) {
						if (($ck % 2) == 0) {
							$x = $cp + $xoffset;
						} else {
							$y = $cp + $yoffset;
							if ($firstcmd OR (abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
								if ($ck == 1) {
									$this->_outPoint($x, $y);
									$firstcmd = false;
								} else {
									$this->_outLine($x, $y);
								}
								$x0 = $x;
								$y0 = $y;
							}
							$xmin = min($xmin, $x);
							$ymin = min($ymin, $y);
							$xmax = max($xmax, $x);
							$ymax = max($ymax, $y);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'L': { // lineto
					foreach ($params as $ck => $cp) {
						if (($ck % 2) == 0) {
							$x = $cp + $xoffset;
						} else {
							$y = $cp + $yoffset;
							if ((abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
								$this->_outLine($x, $y);
								$x0 = $x;
								$y0 = $y;
							}
							$xmin = min($xmin, $x);
							$ymin = min($ymin, $y);
							$xmax = max($xmax, $x);
							$ymax = max($ymax, $y);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'H': { // horizontal lineto
					foreach ($params as $ck => $cp) {
						$x = $cp + $xoffset;
						if ((abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
							$this->_outLine($x, $y);
							$x0 = $x;
							$y0 = $y;
						}
						$xmin = min($xmin, $x);
						$xmax = max($xmax, $x);
						if ($relcoord) {
							$xoffset = $x;
						}
					}
					break;
				}
				case 'V': { // vertical lineto
					foreach ($params as $ck => $cp) {
						$y = $cp + $yoffset;
						if ((abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
							$this->_outLine($x, $y);
							$x0 = $x;
							$y0 = $y;
						}
						$ymin = min($ymin, $y);
						$ymax = max($ymax, $y);
						if ($relcoord) {
							$yoffset = $y;
						}
					}
					break;
				}
				case 'C': { // curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 6) == 0) {
							$x1 = $params[($ck - 5)] + $xoffset;
							$y1 = $params[($ck - 4)] + $yoffset;
							$x2 = $params[($ck - 3)] + $xoffset;
							$y2 = $params[($ck - 2)] + $yoffset;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$this->_outCurve($x1, $y1, $x2, $y2, $x, $y);
							$xmin = min($xmin, $x, $x1, $x2);
							$ymin = min($ymin, $y, $y1, $y2);
							$xmax = max($xmax, $x, $x1, $x2);
							$ymax = max($ymax, $y, $y1, $y2);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'S': { // shorthand/smooth curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 4) == 0) {
							if (($key > 0) AND ((strtoupper($paths[($key - 1)][1]) == 'C') OR (strtoupper($paths[($key - 1)][1]) == 'S'))) {
								$x1 = (2 * $x) - $x2;
								$y1 = (2 * $y) - $y2;
							} else {
								$x1 = $x;
								$y1 = $y;
							}
							$x2 = $params[($ck - 3)] + $xoffset;
							$y2 = $params[($ck - 2)] + $yoffset;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$this->_outCurve($x1, $y1, $x2, $y2, $x, $y);
							$xmin = min($xmin, $x, $x1, $x2);
							$ymin = min($ymin, $y, $y1, $y2);
							$xmax = max($xmax, $x, $x1, $x2);
							$ymax = max($ymax, $y, $y1, $y2);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'Q': { // quadratic B\E9zier curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 4) == 0) {
							// convert quadratic points to cubic points
							$x1 = $params[($ck - 3)] + $xoffset;
							$y1 = $params[($ck - 2)] + $yoffset;
							$xa = ($x + (2 * $x1)) / 3;
							$ya = ($y + (2 * $y1)) / 3;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$xb = ($x + (2 * $x1)) / 3;
							$yb = ($y + (2 * $y1)) / 3;
							$this->_outCurve($xa, $ya, $xb, $yb, $x, $y);
							$xmin = min($xmin, $x, $xa, $xb);
							$ymin = min($ymin, $y, $ya, $yb);
							$xmax = max($xmax, $x, $xa, $xb);
							$ymax = max($ymax, $y, $ya, $yb);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'T': { // shorthand/smooth quadratic B\E9zier curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if (($ck % 2) != 0) {
							if (($key > 0) AND ((strtoupper($paths[($key - 1)][1]) == 'Q') OR (strtoupper($paths[($key - 1)][1]) == 'T'))) {
								$x1 = (2 * $x) - $x1;
								$y1 = (2 * $y) - $y1;
							} else {
								$x1 = $x;
								$y1 = $y;
							}
							// convert quadratic points to cubic points
							$xa = ($x + (2 * $x1)) / 3;
							$ya = ($y + (2 * $y1)) / 3;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$xb = ($x + (2 * $x1)) / 3;
							$yb = ($y + (2 * $y1)) / 3;
							$this->_outCurve($xa, $ya, $xb, $yb, $x, $y);
							$xmin = min($xmin, $x, $xa, $xb);
							$ymin = min($ymin, $y, $ya, $yb);
							$xmax = max($xmax, $x, $xa, $xb);
							$ymax = max($ymax, $y, $ya, $yb);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'A': { // elliptical arc
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 7) == 0) {
							$x0 = $x;
							$y0 = $y;
							$rx = abs($params[($ck - 6)]);
							$ry = abs($params[($ck - 5)]);
							$ang = -$rawparams[($ck - 4)];
							$angle = deg2rad($ang);
							$fa = $rawparams[($ck - 3)]; // large-arc-flag
							$fs = $rawparams[($ck - 2)]; // sweep-flag
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[$ck] + $yoffset;
							if ((abs($x0 - $x) < $minlen) AND (abs($y0 - $y) < $minlen)) {
								// endpoints are almost identical
								$xmin = min($xmin, $x);
								$ymin = min($ymin, $y);
								$xmax = max($xmax, $x);
								$ymax = max($ymax, $y);
							} else {
								$cos_ang = cos($angle);
								$sin_ang = sin($angle);
								$a = (($x0 - $x) / 2);
								$b = (($y0 - $y) / 2);
								$xa = ($a * $cos_ang) - ($b * $sin_ang);
								$ya = ($a * $sin_ang) + ($b * $cos_ang);
								$rx2 = $rx * $rx;
								$ry2 = $ry * $ry;
								$xa2 = $xa * $xa;
								$ya2 = $ya * $ya;
								$delta = ($xa2 / $rx2) + ($ya2 / $ry2);
								if ($delta > 1) {
									$rx *= sqrt($delta);
									$ry *= sqrt($delta);
									$rx2 = $rx * $rx;
									$ry2 = $ry * $ry;
								}
								$numerator = (($rx2 * $ry2) - ($rx2 * $ya2) - ($ry2 * $xa2));
								if ($numerator < 0) {
									$root = 0;
								} else {
									$root = sqrt($numerator / (($rx2 * $ya2) + ($ry2 * $xa2)));
								}
								if ($fa == $fs){
									$root *= -1;
								}
								$cax = $root * (($rx * $ya) / $ry);
								$cay = -$root * (($ry * $xa) / $rx);
								// coordinates of ellipse center
								$cx = ($cax * $cos_ang) - ($cay * $sin_ang) + (($x0 + $x) / 2);
								$cy = ($cax * $sin_ang) + ($cay * $cos_ang) + (($y0 + $y) / 2);
								// get angles
								$angs = TCPDF_STATIC::getVectorsAngle(1, 0, (($xa - $cax) / $rx), (($cay - $ya) / $ry));
								$dang = TCPDF_STATIC::getVectorsAngle((($xa - $cax) / $rx), (($ya - $cay) / $ry), ((-$xa - $cax) / $rx), ((-$ya - $cay) / $ry));
								if (($fs == 0) AND ($dang > 0)) {
									$dang -= (2 * M_PI);
								} elseif (($fs == 1) AND ($dang < 0)) {
									$dang += (2 * M_PI);
								}
								$angf = $angs - $dang;
								if ((($fs == 0) AND ($angs > $angf)) OR (($fs == 1) AND ($angs < $angf))) {
									// reverse angles
									$tmp = $angs;
									$angs = $angf;
									$angf = $tmp;
								}
								$angs = round(rad2deg($angs), 6);
								$angf = round(rad2deg($angf), 6);
								// covent angles to positive values
								if (($angs < 0) AND ($angf < 0)) {
									$angs += 360;
									$angf += 360;
								}
								$pie = false;
								if (($key == 0) AND (isset($paths[($key + 1)][1])) AND (trim($paths[($key + 1)][1]) == 'z')) {
									$pie = true;
								}
								list($axmin, $aymin, $axmax, $aymax) = $this->_outellipticalarc($cx, $cy, $rx, $ry, $ang, $angs, $angf, $pie, 2, false, ($fs == 0), true);
								$xmin = min($xmin, $x, $axmin);
								$ymin = min($ymin, $y, $aymin);
								$xmax = max($xmax, $x, $axmax);
								$ymax = max($ymax, $y, $aymax);
							}
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'Z': {
					$this->_out('h');
					break;
				}
			}
			$firstcmd = false;
		} // end foreach
		if (!empty($op)) {
			$this->_out($op);
		}
		return array($xmin, $ymin, ($xmax - $xmin), ($ymax - $ymin));
	}

	/**
	 * Sets the opening SVG element handler function for the XML parser. (*** TO BE COMPLETED ***)
	 * @param $parser (resource) The first parameter, parser, is a reference to the XML parser calling the handler.
	 * @param $name (string) The second parameter, name, contains the name of the element for which this handler is called. If case-folding is in effect for this parser, the element name will be in uppercase letters.
	 * @param $attribs (array) The third parameter, attribs, contains an associative array with the element's attributes (if any). The keys of this array are the attribute names, the values are the attribute values. Attribute names are case-folded on the same criteria as element names. Attribute values are not case-folded. The original order of the attributes can be retrieved by walking through attribs the normal way, using each(). The first key in the array was the first attribute, and so on.
	 * @param $ctm (array) tranformation matrix for clipping mode (starting transformation matrix).
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @protected
	 */
	protected function startSVGElementHandler($parser, $name, $attribs, $ctm=array()) {
		// check if we are in clip mode
		if ($this->svgclipmode) {
			$this->svgclippaths[$this->svgclipid][] = array('name' => $name, 'attribs' => $attribs, 'tm' => $this->svgcliptm[$this->svgclipid]);
			return;
		}
		if ($this->svgdefsmode AND !in_array($name, array('clipPath', 'linearGradient', 'radialGradient', 'stop'))) {
			if (!isset($attribs['id'])) {
				$attribs['id'] = 'DF_'.(count($this->svgdefs) + 1);
			}
			$this->svgdefs[$attribs['id']] = array('name' => $name, 'attribs' => $attribs);
			return;
		}
		$clipping = false;
		if ($parser == 'clip-path') {
			// set clipping mode
			$clipping = true;
		}
		// get styling properties
		$prev_svgstyle = $this->svgstyles[(count($this->svgstyles) - 1)]; // previous style
		$svgstyle = $this->svgstyles[0]; // set default style
		if ($clipping AND !isset($attribs['fill']) AND (!isset($attribs['style']) OR (!preg_match('/[;\"\s]{1}fill[\s]*:[\s]*([^;\"]*)/si', $attribs['style'], $attrval)))) {
			// default fill attribute for clipping
			$attribs['fill'] = 'none';
		}
		if (isset($attribs['style']) AND !TCPDF_STATIC::empty_string($attribs['style'])) {
			// fix style for regular expression
			$attribs['style'] = ';'.$attribs['style'];
		}
		foreach ($prev_svgstyle as $key => $val) {
			if (in_array($key, TCPDF_IMAGES::$svginheritprop)) {
				// inherit previous value
				$svgstyle[$key] = $val;
			}
			if (isset($attribs[$key]) AND !TCPDF_STATIC::empty_string($attribs[$key])) {
				// specific attribute settings
				if ($attribs[$key] == 'inherit') {
					$svgstyle[$key] = $val;
				} else {
					$svgstyle[$key] = $attribs[$key];
				}
			} elseif (isset($attribs['style']) AND !TCPDF_STATIC::empty_string($attribs['style'])) {
				// CSS style syntax
				$attrval = array();
				if (preg_match('/[;\"\s]{1}'.$key.'[\s]*:[\s]*([^;\"]*)/si', $attribs['style'], $attrval) AND isset($attrval[1])) {
					if ($attrval[1] == 'inherit') {
						$svgstyle[$key] = $val;
					} else {
						$svgstyle[$key] = $attrval[1];
					}
				}
			}
		}
		// transformation matrix
		if (!empty($ctm)) {
			$tm = $ctm;
		} else {
			$tm = array(1,0,0,1,0,0);
		}
		if (isset($attribs['transform']) AND !empty($attribs['transform'])) {
			$tm = TCPDF_STATIC::getTransformationMatrixProduct($tm, TCPDF_STATIC::getSVGTransformMatrix($attribs['transform']));
		}
		$svgstyle['transfmatrix'] = $tm;
		$invisible = false;
		if (($svgstyle['visibility'] == 'hidden') OR ($svgstyle['visibility'] == 'collapse') OR ($svgstyle['display'] == 'none')) {
			// the current graphics element is invisible (nothing is painted)
			$invisible = true;
		}
		// process tag
		switch($name) {
			case 'defs': {
				$this->svgdefsmode = true;
				break;
			}
			// clipPath
			case 'clipPath': {
				if ($invisible) {
					break;
				}
				$this->svgclipmode = true;
				if (!isset($attribs['id'])) {
					$attribs['id'] = 'CP_'.(count($this->svgcliptm) + 1);
				}
				$this->svgclipid = $attribs['id'];
				$this->svgclippaths[$this->svgclipid] = array();
				$this->svgcliptm[$this->svgclipid] = $tm;
				break;
			}
			case 'svg': {
				// start of SVG object
				break;
			}
			case 'g': {
				// group together related graphics elements
				array_push($this->svgstyles, $svgstyle);
				$this->StartTransform();
				$this->SVGTransform($tm);
				$this->setSVGStyles($svgstyle, $prev_svgstyle);
				break;
			}
			case 'linearGradient': {
				if ($this->pdfa_mode) {
					break;
				}
				if (!isset($attribs['id'])) {
					$attribs['id'] = 'GR_'.(count($this->svggradients) + 1);
				}
				$this->svggradientid = $attribs['id'];
				$this->svggradients[$this->svggradientid] = array();
				$this->svggradients[$this->svggradientid]['type'] = 2;
				$this->svggradients[$this->svggradientid]['stops'] = array();
				if (isset($attribs['gradientUnits'])) {
					$this->svggradients[$this->svggradientid]['gradientUnits'] = $attribs['gradientUnits'];
				} else {
					$this->svggradients[$this->svggradientid]['gradientUnits'] = 'objectBoundingBox';
				}
				//$attribs['spreadMethod']
				if (((!isset($attribs['x1'])) AND (!isset($attribs['y1'])) AND (!isset($attribs['x2'])) AND (!isset($attribs['y2'])))
					OR ((isset($attribs['x1']) AND (substr($attribs['x1'], -1) == '%'))
						OR (isset($attribs['y1']) AND (substr($attribs['y1'], -1) == '%'))
						OR (isset($attribs['x2']) AND (substr($attribs['x2'], -1) == '%'))
						OR (isset($attribs['y2']) AND (substr($attribs['y2'], -1) == '%')))) {
					$this->svggradients[$this->svggradientid]['mode'] = 'percentage';
				} else {
					$this->svggradients[$this->svggradientid]['mode'] = 'measure';
				}
				$x1 = (isset($attribs['x1'])?$attribs['x1']:'0');
				$y1 = (isset($attribs['y1'])?$attribs['y1']:'0');
				$x2 = (isset($attribs['x2'])?$attribs['x2']:'100');
				$y2 = (isset($attribs['y2'])?$attribs['y2']:'0');
				if (isset($attribs['gradientTransform'])) {
					$this->svggradients[$this->svggradientid]['gradientTransform'] = TCPDF_STATIC::getSVGTransformMatrix($attribs['gradientTransform']);
				}
				$this->svggradients[$this->svggradientid]['coords'] = array($x1, $y1, $x2, $y2);
				if (isset($attribs['xlink:href']) AND !empty($attribs['xlink:href'])) {
					// gradient is defined on another place
					$this->svggradients[$this->svggradientid]['xref'] = substr($attribs['xlink:href'], 1);
				}
				break;
			}
			case 'radialGradient': {
				if ($this->pdfa_mode) {
					break;
				}
				if (!isset($attribs['id'])) {
					$attribs['id'] = 'GR_'.(count($this->svggradients) + 1);
				}
				$this->svggradientid = $attribs['id'];
				$this->svggradients[$this->svggradientid] = array();
				$this->svggradients[$this->svggradientid]['type'] = 3;
				$this->svggradients[$this->svggradientid]['stops'] = array();
				if (isset($attribs['gradientUnits'])) {
					$this->svggradients[$this->svggradientid]['gradientUnits'] = $attribs['gradientUnits'];
				} else {
					$this->svggradients[$this->svggradientid]['gradientUnits'] = 'objectBoundingBox';
				}
				//$attribs['spreadMethod']
				if (((!isset($attribs['cx'])) AND (!isset($attribs['cy'])))
					OR ((isset($attribs['cx']) AND (substr($attribs['cx'], -1) == '%'))
						OR (isset($attribs['cy']) AND (substr($attribs['cy'], -1) == '%')) )) {
					$this->svggradients[$this->svggradientid]['mode'] = 'percentage';
				} else {
					$this->svggradients[$this->svggradientid]['mode'] = 'measure';
				}
				$cx = (isset($attribs['cx']) ? $attribs['cx'] : 0.5);
				$cy = (isset($attribs['cy']) ? $attribs['cy'] : 0.5);
				$fx = (isset($attribs['fx']) ? $attribs['fx'] : $cx);
				$fy = (isset($attribs['fy']) ? $attribs['fy'] : $cy);
				$r = (isset($attribs['r']) ? $attribs['r'] : 0.5);
				if (isset($attribs['gradientTransform'])) {
					$this->svggradients[$this->svggradientid]['gradientTransform'] = TCPDF_STATIC::getSVGTransformMatrix($attribs['gradientTransform']);
				}
				$this->svggradients[$this->svggradientid]['coords'] = array($cx, $cy, $fx, $fy, $r);
				if (isset($attribs['xlink:href']) AND !empty($attribs['xlink:href'])) {
					// gradient is defined on another place
					$this->svggradients[$this->svggradientid]['xref'] = substr($attribs['xlink:href'], 1);
				}
				break;
			}
			case 'stop': {
				// gradient stops
				if (substr($attribs['offset'], -1) == '%') {
					$offset = floatval(substr($attribs['offset'], -1)) / 100;
				} else {
					$offset = floatval($attribs['offset']);
					if ($offset > 1) {
						$offset /= 100;
					}
				}
				$stop_color = isset($svgstyle['stop-color'])?TCPDF_COLORS::convertHTMLColorToDec($svgstyle['stop-color'], $this->spot_colors):'black';
				$opacity = isset($svgstyle['stop-opacity'])?$svgstyle['stop-opacity']:1;
				$this->svggradients[$this->svggradientid]['stops'][] = array('offset' => $offset, 'color' => $stop_color, 'opacity' => $opacity);
				break;
			}
			// paths
			case 'path': {
				if ($invisible) {
					break;
				}
				if (isset($attribs['d'])) {
					$d = trim($attribs['d']);
					if (!empty($d)) {
						$x = (isset($attribs['x'])?$attribs['x']:0);
						$y = (isset($attribs['y'])?$attribs['y']:0);
						$w = (isset($attribs['width'])?$attribs['width']:1);
						$h = (isset($attribs['height'])?$attribs['height']:1);
						$tm = TCPDF_STATIC::getTransformationMatrixProduct($tm, array($w, 0, 0, $h, $x, $y));
						if ($clipping) {
							$this->SVGTransform($tm);
							$this->SVGPath($d, 'CNZ');
						} else {
							$this->StartTransform();
							$this->SVGTransform($tm);
							$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'SVGPath', array($d, 'CNZ'));
							if (!empty($obstyle)) {
								$this->SVGPath($d, $obstyle);
							}
							$this->StopTransform();
						}
					}
				}
				break;
			}
			// shapes
			case 'rect': {
				if ($invisible) {
					break;
				}
				$x = (isset($attribs['x'])?$this->getHTMLUnitToUnits($attribs['x'], 0, $this->svgunit, false):0);
				$y = (isset($attribs['y'])?$this->getHTMLUnitToUnits($attribs['y'], 0, $this->svgunit, false):0);
				$w = (isset($attribs['width'])?$this->getHTMLUnitToUnits($attribs['width'], 0, $this->svgunit, false):0);
				$h = (isset($attribs['height'])?$this->getHTMLUnitToUnits($attribs['height'], 0, $this->svgunit, false):0);
				$rx = (isset($attribs['rx'])?$this->getHTMLUnitToUnits($attribs['rx'], 0, $this->svgunit, false):0);
				$ry = (isset($attribs['ry'])?$this->getHTMLUnitToUnits($attribs['ry'], 0, $this->svgunit, false):$rx);
				if ($clipping) {
					$this->SVGTransform($tm);
					$this->RoundedRectXY($x, $y, $w, $h, $rx, $ry, '1111', 'CNZ', array(), array());
				} else {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'RoundedRectXY', array($x, $y, $w, $h, $rx, $ry, '1111', 'CNZ'));
					if (!empty($obstyle)) {
						$this->RoundedRectXY($x, $y, $w, $h, $rx, $ry, '1111', $obstyle, array(), array());
					}
					$this->StopTransform();
				}
				break;
			}
			case 'circle': {
				if ($invisible) {
					break;
				}
				$r = (isset($attribs['r']) ? $this->getHTMLUnitToUnits($attribs['r'], 0, $this->svgunit, false) : 0);
				$cx = (isset($attribs['cx']) ? $this->getHTMLUnitToUnits($attribs['cx'], 0, $this->svgunit, false) : (isset($attribs['x']) ? $this->getHTMLUnitToUnits($attribs['x'], 0, $this->svgunit, false) : 0));
				$cy = (isset($attribs['cy']) ? $this->getHTMLUnitToUnits($attribs['cy'], 0, $this->svgunit, false) : (isset($attribs['y']) ? $this->getHTMLUnitToUnits($attribs['y'], 0, $this->svgunit, false) : 0));
				$x = ($cx - $r);
				$y = ($cy - $r);
				$w = (2 * $r);
				$h = $w;
				if ($clipping) {
					$this->SVGTransform($tm);
					$this->Circle($cx, $cy, $r, 0, 360, 'CNZ', array(), array(), 8);
				} else {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'Circle', array($cx, $cy, $r, 0, 360, 'CNZ'));
					if (!empty($obstyle)) {
						$this->Circle($cx, $cy, $r, 0, 360, $obstyle, array(), array(), 8);
					}
					$this->StopTransform();
				}
				break;
			}
			case 'ellipse': {
				if ($invisible) {
					break;
				}
				$rx = (isset($attribs['rx']) ? $this->getHTMLUnitToUnits($attribs['rx'], 0, $this->svgunit, false) : 0);
				$ry = (isset($attribs['ry']) ? $this->getHTMLUnitToUnits($attribs['ry'], 0, $this->svgunit, false) : 0);
				$cx = (isset($attribs['cx']) ? $this->getHTMLUnitToUnits($attribs['cx'], 0, $this->svgunit, false) : (isset($attribs['x']) ? $this->getHTMLUnitToUnits($attribs['x'], 0, $this->svgunit, false) : 0));
				$cy = (isset($attribs['cy']) ? $this->getHTMLUnitToUnits($attribs['cy'], 0, $this->svgunit, false) : (isset($attribs['y']) ? $this->getHTMLUnitToUnits($attribs['y'], 0, $this->svgunit, false) : 0));
				$x = ($cx - $rx);
				$y = ($cy - $ry);
				$w = (2 * $rx);
				$h = (2 * $ry);
				if ($clipping) {
					$this->SVGTransform($tm);
					$this->Ellipse($cx, $cy, $rx, $ry, 0, 0, 360, 'CNZ', array(), array(), 8);
				} else {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'Ellipse', array($cx, $cy, $rx, $ry, 0, 0, 360, 'CNZ'));
					if (!empty($obstyle)) {
						$this->Ellipse($cx, $cy, $rx, $ry, 0, 0, 360, $obstyle, array(), array(), 8);
					}
					$this->StopTransform();
				}
				break;
			}
			case 'line': {
				if ($invisible) {
					break;
				}
				$x1 = (isset($attribs['x1'])?$this->getHTMLUnitToUnits($attribs['x1'], 0, $this->svgunit, false):0);
				$y1 = (isset($attribs['y1'])?$this->getHTMLUnitToUnits($attribs['y1'], 0, $this->svgunit, false):0);
				$x2 = (isset($attribs['x2'])?$this->getHTMLUnitToUnits($attribs['x2'], 0, $this->svgunit, false):0);
				$y2 = (isset($attribs['y2'])?$this->getHTMLUnitToUnits($attribs['y2'], 0, $this->svgunit, false):0);
				$x = $x1;
				$y = $y1;
				$w = abs($x2 - $x1);
				$h = abs($y2 - $y1);
				if (!$clipping) {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'Line', array($x1, $y1, $x2, $y2));
					$this->Line($x1, $y1, $x2, $y2);
					$this->StopTransform();
				}
				break;
			}
			case 'polyline':
			case 'polygon': {
				if ($invisible) {
					break;
				}
				$points = (isset($attribs['points'])?$attribs['points']:'0 0');
				$points = trim($points);
				// note that point may use a complex syntax not covered here
				$points = preg_split('/[\,\s]+/si', $points);
				if (count($points) < 4) {
					break;
				}
				$p = array();
				$xmin = 2147483647;
				$xmax = 0;
				$ymin = 2147483647;
				$ymax = 0;
				foreach ($points as $key => $val) {
					$p[$key] = $this->getHTMLUnitToUnits($val, 0, $this->svgunit, false);
					if (($key % 2) == 0) {
						// X coordinate
						$xmin = min($xmin, $p[$key]);
						$xmax = max($xmax, $p[$key]);
					} else {
						// Y coordinate
						$ymin = min($ymin, $p[$key]);
						$ymax = max($ymax, $p[$key]);
					}
				}
				$x = $xmin;
				$y = $ymin;
				$w = ($xmax - $xmin);
				$h = ($ymax - $ymin);
				if ($name == 'polyline') {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'PolyLine', array($p, 'CNZ'));
					if (!empty($obstyle)) {
						$this->PolyLine($p, $obstyle, array(), array());
					}
					$this->StopTransform();
				} else { // polygon
					if ($clipping) {
						$this->SVGTransform($tm);
						$this->Polygon($p, 'CNZ', array(), array(), true);
					} else {
						$this->StartTransform();
						$this->SVGTransform($tm);
						$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'Polygon', array($p, 'CNZ'));
						if (!empty($obstyle)) {
							$this->Polygon($p, $obstyle, array(), array(), true);
						}
						$this->StopTransform();
					}
				}
				break;
			}
			// image
			case 'image': {
				if ($invisible) {
					break;
				}
				if (!isset($attribs['xlink:href']) OR empty($attribs['xlink:href'])) {
					break;
				}
				$x = (isset($attribs['x'])?$this->getHTMLUnitToUnits($attribs['x'], 0, $this->svgunit, false):0);
				$y = (isset($attribs['y'])?$this->getHTMLUnitToUnits($attribs['y'], 0, $this->svgunit, false):0);
				$w = (isset($attribs['width'])?$this->getHTMLUnitToUnits($attribs['width'], 0, $this->svgunit, false):0);
				$h = (isset($attribs['height'])?$this->getHTMLUnitToUnits($attribs['height'], 0, $this->svgunit, false):0);
				$img = $attribs['xlink:href'];
				if (!$clipping) {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h);
					if (preg_match('/^data:image\/[^;]+;base64,/', $img, $m) > 0) {
						// embedded image encoded as base64
						$img = '@'.base64_decode(substr($img, strlen($m[0])));
					} else {
						// fix image path
						if (!TCPDF_STATIC::empty_string($this->svgdir) AND (($img{0} == '.') OR (basename($img) == $img))) {
							// replace relative path with full server path
							$img = $this->svgdir.'/'.$img;
						}
						if (($img[0] == '/') AND !empty($_SERVER['DOCUMENT_ROOT']) AND ($_SERVER['DOCUMENT_ROOT'] != '/')) {
							$findroot = strpos($img, $_SERVER['DOCUMENT_ROOT']);
							if (($findroot === false) OR ($findroot > 1)) {
								if (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') {
									$img = substr($_SERVER['DOCUMENT_ROOT'], 0, -1).$img;
								} else {
									$img = $_SERVER['DOCUMENT_ROOT'].$img;
								}
							}
						}
						$img = urldecode($img);
						$testscrtype = @parse_url($img);
						if (!isset($testscrtype['query']) OR empty($testscrtype['query'])) {
							// convert URL to server path
							$img = str_replace(K_PATH_URL, K_PATH_MAIN, $img);
						}
					}
					// get image type
					$imgtype = TCPDF_IMAGES::getImageFileType($img);
					if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
						$this->ImageEps($img, $x, $y, $w, $h);
					} elseif ($imgtype == 'svg') {
						$this->ImageSVG($img, $x, $y, $w, $h);
					} else {
						$this->Image($img, $x, $y, $w, $h);
					}
					$this->StopTransform();
				}
				break;
			}
			// text
			case 'text':
			case 'tspan': {
				// only basic support - advanced features must be implemented
				$this->svgtextmode['invisible'] = $invisible;
				if ($invisible) {
					break;
				}
				array_push($this->svgstyles, $svgstyle);
				if (isset($attribs['x'])) {
					$x = $this->getHTMLUnitToUnits($attribs['x'], 0, $this->svgunit, false);
				} elseif ($name == 'tspan') {
					$x = $this->x;
				} else {
					$x = 0;
				}
				if (isset($attribs['dx'])) {
					$x += $this->getHTMLUnitToUnits($attribs['dx'], 0, $this->svgunit, false);
				}
				if (isset($attribs['y'])) {
					$y = $this->getHTMLUnitToUnits($attribs['y'], 0, $this->svgunit, false);
				} elseif ($name == 'tspan') {
					$y = $this->y;
				} else {
					$y = 0;
				}
				if (isset($attribs['dy'])) {
					$y += $this->getHTMLUnitToUnits($attribs['dy'], 0, $this->svgunit, false);
				}
				$svgstyle['text-color'] = $svgstyle['fill'];
				$this->svgtext = '';
				if (isset($svgstyle['text-anchor'])) {
					$this->svgtextmode['text-anchor'] = $svgstyle['text-anchor'];
				} else {
					$this->svgtextmode['text-anchor'] = 'start';
				}
				if (isset($svgstyle['direction'])) {
					if ($svgstyle['direction'] == 'rtl') {
						$this->svgtextmode['rtl'] = true;
					} else {
						$this->svgtextmode['rtl'] = false;
					}
				} else {
					$this->svgtextmode['rtl'] = false;
				}
				if (isset($svgstyle['stroke']) AND ($svgstyle['stroke'] != 'none') AND isset($svgstyle['stroke-width']) AND ($svgstyle['stroke-width'] > 0)) {
					$this->svgtextmode['stroke'] = $this->getHTMLUnitToUnits($svgstyle['stroke-width'], 0, $this->svgunit, false);
				} else {
					$this->svgtextmode['stroke'] = false;
				}
				$this->StartTransform();
				$this->SVGTransform($tm);
				$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, 1, 1);
				$this->x = $x;
				$this->y = $y;
				break;
			}
			// use
			case 'use': {
				if (isset($attribs['xlink:href']) AND !empty($attribs['xlink:href'])) {
					$svgdefid = substr($attribs['xlink:href'], 1);
					if (isset($this->svgdefs[$svgdefid])) {
						$use = $this->svgdefs[$svgdefid];
						if (isset($attribs['xlink:href'])) {
							unset($attribs['xlink:href']);
						}
						if (isset($attribs['id'])) {
							unset($attribs['id']);
						}
						if (isset($use['attribs']['x']) AND isset($attribs['x'])) {
							$attribs['x'] += $use['attribs']['x'];
						}
						if (isset($use['attribs']['y']) AND isset($attribs['y'])) {
							$attribs['y'] += $use['attribs']['y'];
						}
						$attribs = array_merge($use['attribs'], $attribs);
						$this->startSVGElementHandler($parser, $use['name'], $attribs);
					}
				}
				break;
			}
			default: {
				break;
			}
		} // end of switch
	}

	/**
	 * Sets the closing SVG element handler function for the XML parser.
	 * @param $parser (resource) The first parameter, parser, is a reference to the XML parser calling the handler.
	 * @param $name (string) The second parameter, name, contains the name of the element for which this handler is called. If case-folding is in effect for this parser, the element name will be in uppercase letters.
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @protected
	 */
	protected function endSVGElementHandler($parser, $name) {
		switch($name) {
			case 'defs': {
				$this->svgdefsmode = false;
				break;
			}
			// clipPath
			case 'clipPath': {
				$this->svgclipmode = false;
				break;
			}
			case 'g': {
				// ungroup: remove last style from array
				array_pop($this->svgstyles);
				$this->StopTransform();
				break;
			}
			case 'text':
			case 'tspan': {
				if ($this->svgtextmode['invisible']) {
					// This implementation must be fixed to following the rule:
					// If the 'visibility' property is set to hidden on a 'tspan', 'tref' or 'altGlyph' element, then the text is invisible but still takes up space in text layout calculations.
					break;
				}
				// print text
				$text = $this->svgtext;
				//$text = $this->stringTrim($text);
				$textlen = $this->GetStringWidth($text);
				if ($this->svgtextmode['text-anchor'] != 'start') {
					// check if string is RTL text
					if ($this->svgtextmode['text-anchor'] == 'end') {
						if ($this->svgtextmode['rtl']) {
							$this->x += $textlen;
						} else {
							$this->x -= $textlen;
						}
					} elseif ($this->svgtextmode['text-anchor'] == 'middle') {
						if ($this->svgtextmode['rtl']) {
							$this->x += ($textlen / 2);
						} else {
							$this->x -= ($textlen / 2);
						}
					}
				}
				$textrendermode = $this->textrendermode;
				$textstrokewidth = $this->textstrokewidth;
				$this->setTextRenderingMode($this->svgtextmode['stroke'], true, false);
				if ($name == 'text') {
					// store current coordinates
					$tmpx = $this->x;
					$tmpy = $this->y;
				}
				$this->Cell($textlen, 0, $text, 0, 0, '', false, '', 0, false, 'L', 'T');
				if ($name == 'text') {
					// restore coordinates
					$this->x = $tmpx;
					$this->y = $tmpy;
				}
				// restore previous rendering mode
				$this->textrendermode = $textrendermode;
				$this->textstrokewidth = $textstrokewidth;
				$this->svgtext = '';
				$this->StopTransform();
				array_pop($this->svgstyles);
				break;
			}
			default: {
				break;
			}
		}
	}

	/**
	 * Sets the character data handler function for the XML parser.
	 * @param $parser (resource) The first parameter, parser, is a reference to the XML parser calling the handler.
	 * @param $data (string) The second parameter, data, contains the character data as a string.
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @protected
	 */
	protected function segSVGContentHandler($parser, $data) {
		$this->svgtext .= $data;
	}

	// --- END SVG METHODS -----------------------------------------------------

} // END OF TCPDF CLASS

//============================================================+
// END OF FILE
//============================================================+
