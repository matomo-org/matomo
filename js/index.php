<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

$file = "../piwik.js";

/*
 * Conditional GET
 */
if (file_exists($file)) {
	$modifiedSince = '';
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		$modifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
	}
	$lastModified = gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT';

	// strip any trailing data appended to header
	if (false !== ($semicolon = strpos($modifiedSince, ';'))) {
		$modifiedSince = substr($modifiedSince, 0, $semicolon);
	}

	if ($modifiedSince == $lastModified) {
		header('HTTP/1.1 304 Not Modified');
	} else {
		header('Last-Modified: ' . $lastModified);
		header('Content-Length: ' . filesize($file));
		header('Content-Type: application/x-javascript');

		if (!readfile($file)) {
			header ("HTTP/1.0 505 Internal server error");
		}
	}
} else {
	header ("HTTP/1.0 404 Not Found");
}
exit;
