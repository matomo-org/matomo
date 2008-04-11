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

if ('@DATA-DIR@' != '@'.'DATA-DIR@')
{
    include_once('PhpDocumentor/HTML_TreeMenu-1.1.2/TreeMenu.php');
} else {
    include_once(dirname(realpath(__FILE__))."/../../HTML_TreeMenu-1.1.2/TreeMenu.php");
}

/**
 *	Allows png's with alpha transparency to be displayed in IE 6
 *	@param string $src path to the source image
 *	@param int $wid width on the image [optional]
 *	@param int $hgt height on the image [optional]
 *	@param string $alt hover text for the image [optional]
 */
function showImage( $src, $wid='', $hgt='', $alt='' ) {
	if (strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0' ) !== false) {
		return "<div style=\"height:{$hgt}px; width:{$wid}px; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$src', sizingMethod='scale');\" ></div>";
	} else {
		return "<img src=\"$src\" width=\"$wid\" height=\"$hgt\" alt=\"$alt\" border=\"0\" />";
	}
}

/**
 *	Returns a select box based on an key,value array where selected is based on key
 *	@param array $arr array of the key-text pairs
 *	@param string $select_name The name of the select box
 *	@param string $select_attribs Additional attributes to insert into the html select tag
 *	@param string $selected The key value of the selected eleme
 */
function htmlArraySelect( &$arr, $select_name, $select_attribs, $selected ) {
	GLOBAL $AppUI;
	reset( $arr );
	$s = "\n<select name=\"$select_name\" $select_attribs>";
	foreach ($arr as $k => $v ) {
		$s .= "\n\t<option value=\"".$k."\"".($k == $selected ? " selected=\"selected\"" : '').">" . $v . "</option>";
	}
	$s .= "\n</select>\n";
	return $s;
}

function getDir($path,&$node) {
	global $pd;
	if (!$dir = opendir($path)) return;
	
	$node = new HTML_TreeNode(array('text' => basename(realpath($path)), 'link' => "", 'icon' => 'folder.gif'));
	while (($file = readdir($dir)) !== false)
	{
		if ($file != '.' && $file != '..')
		{
			if (is_dir("$path$pd$file") && !is_link("$path$pd$file"))
			{
				$entry[] = "$path$pd$file";
			}
		}
	}
	closedir($dir);
	for($i = 0; $i < count($entry); $i++)
	{
		$node->addItem(new HTML_TreeNode(array('text'=>basename(realpath($entry[$i])), 'link' => "javascript:setHelp('".addslashes(realpath($entry[$i]))."');", 'icon' => 'folder.gif')));
	}
}

function recurseDir($path, &$node) {
	global $pd;
	if (!$dir = opendir($path)) {
		return false;
	}
	$anode = new HTML_TreeNode(array('text' => basename($path), 'link' => "javascript:setHelpVal('".$path."');", 'icon' => 'folder.gif'));
	$result = addslashes(realpath(stripslashes($path).$pd.".."));
	if (!$node) $anode->addItem(new DirNode('..',"javascript:setHelp('".$result."');",'folder.gif'),'..');
	while (($file = readdir($dir)) !== false) {
		if ($file != '.' && $file != '..') {
			if (is_dir("$path$pd$file")) {
				recurseDir("$path$pd$file",$anode);
			}
		}
	}
	rewinddir($dir);//
	while (false){//($file = readdir($dir)) !== false) {
		if ($file != '.' && $file != '..') {
			if (is_file("$path$pd$file")) {
				$anode->addItem(new DirNode($file,"javascript:setHelpVal('$path$pd$file');",'branchtop.gif',"$path$pd$file"));
			}
		}
	}
	if (!$node) $node = $anode;
	else
	$node->addItem($anode);
	closedir($dir);
}

function switchDirTree($path, &$node)
{
	global $pd;

	// initialize recursion simulation values
	// array format: path => &parent in $node itemlist
	$parent = array();
	$parent_indexes = array();
	$parenti = 1;
	
	$node = new DirNode(basename($path),"javascript:setHelpVal('".$path."');",'folder.gif',$path);
	$result = addslashes(realpath($path.$pd.".."));
	$node->addItem(new DirNode('..',"javascript:setHelp('".$result."');",'folder.gif','..'));
	$rnode = &$node;
	$parent[realpath($path)] = false;
	$recur = 0;
	do
	{
		if ($recur++ > 120) return;
		if (!$dir = @opendir($path)) {
			// no child files or directories
//                    echo "$path no child files or directories return to ";
			$rnode = &$parent[realpath($path)];
			$path = $rnode->path;
			if (isset($parent_indexes[realpath($path)])) $parenti = $parent_indexes[realpath($path)];
//                    echo "$path parenti $parenti<br>";
		}
//                fancy_debug($path,$parent_indexes);
//                vdump_par($parent);
		if (!isset($parent_indexes[realpath($path)]))
		{
			$file = readdir($dir);
			while ($file !== false) {
				if ($file != '.' && $file != '..') {
					if (@is_dir(realpath("$path$pd$file"))) {
						if (!isset($parent_indexes[realpath($path)])) $parent_indexes[realpath($path)] = true;
						$parent[realpath("$path$pd$file")] = &$rnode;
//                                echo "<br>adding new ".addslashes(realpath($path.$pd.$file))." to $path<br>";
						$rnode->addItem(new DirNode(addslashes(realpath("$path$pd$file")),"javascript:setHelpVal('".addslashes(realpath($path.$pd.$file))."');",'folder.gif',addslashes(realpath($path.$pd.$file))));
					}
				}
				$file = readdir($dir);
			}
		}
		// go down the tree if possible
		if (isset($parent_indexes[realpath($path)]))
		{
			if ($parenti + 1 > (count($rnode->items)))
			{
				// no more children, go back up to parent
//                        echo "$path no more children, go back up to parent ";
				$rnode = &$parent[realpath($path)];
				$path = $rnode->path;
				if (isset($parent_indexes[realpath($path)])) $parenti = $parent_indexes[realpath($path)];
//                        echo $path." parenti $parenti<br>";
			} else
			{
				// go to next child
//                        echo "$path go to next child ";
				$parent_indexes[realpath($path)] = $parenti+1;
//                        debug("set parent ".$rnode->items[$parenti]->path." = ".$rnode->path.'<br>');
				$parent[realpath($rnode->items[$parenti]->path)] = &$rnode;
				$rnode = &$rnode->items[$parenti];
				$path = $rnode->path;
//                        echo "$path<br>";
				$parenti = 0;
			}
		} else
		{
			// no children, go back up the tree to the next child
//                    echo "$path no children, go back up to parent ";
			$rnode = &$parent[realpath($path)];
			$path = $rnode->path;
			if (isset($parent_indexes[realpath($path)])) $parenti = $parent_indexes[realpath($path)];
//                    echo "$path parenti $parenti<br>";
		}
		@closedir($dir);
	} while ($path && (($parenti < (count($rnode->items))) || ($parent[realpath($path)] !== false)));
}

function vdump_par($tree)
{
	foreach($tree as $key => $val)
	{
		if ($val === false)
		debug($key.' -> false<br>');
		else
		debug($key.' -> ' .$val->path.'<br>');
	}
	debug('<br>');
}
?>