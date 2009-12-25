<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

$file = '../piwik.js';

if (file_exists($file)) {
	// conditional GET
	$modifiedSince = '';
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		$modifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
	}
	$lastModified = gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT';

	// optional compression
	$compressed = false;
	$encoding = '';
	if (extension_loaded('zlib') && function_exists('file_get_contents') && function_exists('file_put_contents') && isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
		$acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
		if (preg_match('/(?:^|, ?)(deflate)(?:,|$)/', $acceptEncoding, $matches)) {
			$encoding = $matches[1];
		} else if (preg_match('/(?:^|, ?)((x-)?gzip)(?:,|$)/', $acceptEncoding, $matches)) {
			$encoding = $matches[1];
		}

		if (!empty($encoding)) {
			$filegz = '../piwik.js.' . $encoding;

			if(!file_exists($filegz) || (filemtime($file) > filemtime($filegz))) {
				$data = file_get_contents($file);

				if ($encoding == 'deflate') {
					$data = gzcompress($data, 9);
				} else if ($encoding == 'gzip' || $encoding == 'x-gzip') {
					$data = gzencode($data, 9);
				}

				file_put_contents($filegz, $data);
				$compressed = true;
				$file = $filegz;
			}
		}
	}

	// strip any trailing data appended to header
	if (false !== ($semicolon = strpos($modifiedSince, ';'))) {
		$modifiedSince = substr($modifiedSince, 0, $semicolon);
	}

	if ($modifiedSince == $lastModified) {
		header('HTTP/1.1 304 Not Modified');
	} else {
		header('Last-Modified: ' . $lastModified);
		header('Content-Length: ' . filesize($file));
		header('Content-Type: application/x-javascript; charset=UTF-8');

		if ($compressed) {
			header('Content-Encoding: ' . $encoding);
		}

		if (!readfile($file)) {
			header ('HTTP/1.0 505 Internal server error');
		}
	}
} else {
	header ('HTTP/1.0 404 Not Found');
}
exit;
