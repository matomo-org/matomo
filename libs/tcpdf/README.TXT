TCPDF - README
============================================================

I WISH TO IMPROVE AND EXPAND TCPDF BUT I NEED YOUR SUPPORT.
PLEASE MAKE A DONATION:
http://sourceforge.net/donate/index.php?group_id=128076

------------------------------------------------------------

Name: TCPDF
Version: 6.0.044
Release date: 2013-11-10
Author:	Nicola Asuni

Copyright (c) 2002-2013:
	Nicola Asuni
	Tecnick.com LTD
	www.tecnick.com

URLs:
	http://www.tcpdf.org
	http://www.sourceforge.net/projects/tcpdf

Description:
	TCPDF is a PHP class for generating PDF files on-the-fly without requiring external extensions.
	This library includes also a class to extract data from existing PDF documents and
	classes to generate 1D and 2D barcodes in various formats.

Main Features:
    * no external libraries are required for the basic functions;
    * all standard page formats, custom page formats, custom margins and units of measure;
    * UTF-8 Unicode and Right-To-Left languages;
    * TrueTypeUnicode, OpenTypeUnicode v1, TrueType, OpenType v1, Type1 and CID-0 fonts;
    * font subsetting;
    * methods to publish some XHTML + CSS code, Javascript and Forms;
    * images, graphic (geometric figures) and transformation methods;
    * supports JPEG, PNG and SVG images natively, all images supported by GD (GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM) and all images supported via ImagMagick (http:  www.imagemagick.org/www/formats.html)
    * 1D and 2D barcodes: CODE 39, ANSI MH10.8M-1983, USD-3, 3 of 9, CODE 93, USS-93, Standard 2 of 5, Interleaved 2 of 5, CODE 128 A/B/C, 2 and 5 Digits UPC-Based Extention, EAN 8, EAN 13, UPC-A, UPC-E, MSI, POSTNET, PLANET, RMS4CC (Royal Mail 4-state Customer Code), CBC (Customer Bar Code), KIX (Klant index - Customer index), Intelligent Mail Barcode, Onecode, USPS-B-3200, CODABAR, CODE 11, PHARMACODE, PHARMACODE TWO-TRACKS, Datamatrix, QR-Code, PDF417;
    * JPEG and PNG ICC profiles, Grayscale, RGB, CMYK, Spot Colors and Transparencies;
    * automatic page header and footer management;
    * document encryption up to 256 bit and digital signature certifications;
    * transactions to UNDO commands;
    * PDF annotations, including links, text and file attachments;
    * text rendering modes (fill, stroke and clipping);
    * multiple columns mode;
    * no-write page regions;
    * bookmarks, named destinations and table of content;
    * text hyphenation;
    * text stretching and spacing (tracking);
    * automatic page break, line break and text alignments including justification;
    * automatic page numbering and page groups;
    * move and delete pages;
    * page compression (requires php-zlib extension);
    * XOBject Templates;
	* Layers and object visibility.
	* PDF/A-1b support.

Installation (full instructions on http:  www.tcpdf.org):
	1. copy the folder on your Web server
	2. set your installation path and other parameters on the config/tcpdf_config.php
	3. call the examples/example_001.php page with your browser to see an example

Source Code Documentation:
	http://www.tcpdf.org

Additional Documentation:
	http://www.tcpdf.org

License:
	Copyright (C) 2002-2013  Nicola Asuni - Tecnick.com LTD

	TCPDF is free software: you can redistribute it and/or modify it
	under the terms of the GNU Lesser General Public License as
	published by the Free Software Foundation, either version 3 of the
	License, or (at your option) any later version.

	TCPDF is distributed in the hope that it will be useful, but
	WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU Lesser General Public License for more details.

	You should have received a copy of the License
	along with TCPDF. If not, see
	<http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT>.

	See LICENSE.TXT file for more information.

Third party fonts:

	This library may include third party font files released with different licenses.
	
	All the PHP files on the fonts directory are subject to the general TCPDF license (GNU-LGPLv3),
	they do not contain any binary data but just a description of the general properties of a particular font.
	These files can be also generated on the fly using the font utilities and TCPDF methods.
	
	All the original binary TTF font files have been renamed for compatibility with TCPDF and compressed using the gzcompress PHP function that uses the ZLIB data format (.z files).
	
	The binary files (.z) that begins with the prefix "free" have been extracted from the GNU FreeFont collection (GNU-GPLv3).
	The binary files (.z) that begins with the prefix "pdfa" have been derived from the GNU FreeFont, so they are subject to the same license.
	For the details of Copyright, License and other information, please check the files inside the directory fonts/freefont-20120503
	Link : http://www.gnu.org/software/freefont/
	
	The binary files (.z) that begins with the prefix "dejavu" have been extracted from the DejaVu fonts 2.33 (Bitstream) collection.
	For the details of Copyright, License and other information, please check the files inside the directory fonts/dejavu-fonts-ttf-2.33
	Link : http://dejavu-fonts.org
	
	The binary files (.z) that begins with the prefix "ae" have been extracted from the Arabeyes.org collection (GNU-GPLv2).
	Link : http://projects.arabeyes.org/


============================================================
