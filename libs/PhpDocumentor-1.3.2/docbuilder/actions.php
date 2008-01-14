<?php
/**
 * phpDocumentor :: docBuilder Web Interface
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
 */
$path = dirname(__FILE__);
if ('@WEB-DIR@' != '@'.'WEB-DIR@')
{
    include_once( "@WEB-DIR@/PhpDocumentor/docbuilder/includes/utilities.php" );
} else {
    include_once( "$path/includes/utilities.php" );
}

$filename = '';
if (isset($_GET) && isset($_GET['fileName'])) {
	$filename = $_GET['fileName'];
}
$filename = realpath($filename);
$pd = DIRECTORY_SEPARATOR;
$test = ($pd == '/') ? '/' : 'C:\\';
if (empty($filename) || ($filename == $test)) {
	$filename = ($pd == '/') ? '/' : 'C:\\';
	$node = false;
	getDir($filename,$node);
}

?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>docBuilder - phpDocumentor web interface</title>
	<meta name="Generator" content="EditPlus">
	<meta name="Author" content="Andrew Eddie">
	<meta name="Description" content="Blank page">
	<style type="text/css">
		body, td, th, select, input {
			font-family: verdana,san-serif;
			font-size: 8pt;
		}
		.button {
			border: solid 1px #000000;
		}
		.text {
			border: solid 1px #000000;
		}
	</style>
	<script type="text/javascript" language="Javascript">
	function setFile( name ) {
		document.actionFrm.fileName.value = name;
	}
	</script>
</head>
<body text="#000000" bgcolor="#0099cc">
<table cellspacing="0" cellpadding="2" border="0" width="100%">
<form name="actionFrm">
<tr>
	<td>Working Directory</td>
	<td>
		<input type="text" name="fileName" value="<?php print $filename;?>" size="60" class="text" />
	</td>
	<td>
		<input type="button" name="" value="..." title="change directory" class="button" onclick="window.open('file_dialog.php?filename='+document.actionFrm.fileName.value,'file_dialog','height=300px,width=600px,resizable=yes,scrollbars=yes');" />
	</td>
	<td align="right" width="100%">
		<input type="button" value="create" title="create documentation" class="button" onclick="parent.DataFrame.document.dataForm.target = 'OutputFrame'; parent.DataFrame.document.dataForm.submit();" /><br />
		<input type="button" value="create (new window)" title="create docs (new window)" class="button" onclick="parent.DataFrame.document.dataForm.target = 'newFrame';parent.DataFrame.document.dataForm.submit();" />
	</td>
	<td>&nbsp;</td>
</tr>
</form>

</body>
</html>
