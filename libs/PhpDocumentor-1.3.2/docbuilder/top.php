<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
/**
 * phpDocumentor :: docBuilder Web Interface
 * 
 * Advanced Web Interface to phpDocumentor
 *
 * PHP versions 4 and 5
 *
 * Copyright (c) 2003-2006 Andrew Eddie, Greg Beaver
 * 
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package    phpDocumentor
 * @author     Andrew Eddie
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2003-2006 Andrew Eddie, Greg Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @filesource
 * @see phpdoc.php
 */
if ('@DATA-DIR@' != '@'.'DATA-DIR@')
{
    include_once( "PhpDocumentor/phpDocumentor/common.inc.php");
} else {
    $path = dirname(dirname(__FILE__));
    include_once( "$path/phpDocumentor/common.inc.php");
}
//
// +------------------------------------------------------------------------+
// | phpDocumentor :: docBuilder Web Interface                              |
// +------------------------------------------------------------------------+
// | Copyright (c) 2003 Andrew Eddie, Greg Beaver                           |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
?>
<html>
<head>
	<title>
		Form to submit to phpDocumentor v<?php print PHPDOCUMENTOR_VER; ?>
	</title>
	<style type="text/css">
		body, td, th {
			font-family: verdana,sans-serif;
			font-size: 10pt;
		}
		.title {
			font-size: 12pt;
		}
	</style>
</head>

<body bgcolor="#0099cc" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
	<td bgcolor="#0099cc" height="35" width="100" nowrap="nowrap">
		<img src="../poweredbyphpdoc.gif" alt="" width="88" height="31" border="0" alt="" />
	</td>
	<td bgcolor="#0099cc" width="100%">
		<span class="title"><strong>docBuilder</strong> :: phpDocumentor v<?php print PHPDOCUMENTOR_VER; ?> Web Interface</span>
	</td>
</tr>
</table>

</body>
</html>
