<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

if(!empty($_SERVER['QUERY_STRING'])) {
	include '../piwik.php';
	exit;
}

$file = '../piwik.js';

if (file_exists($file) && function_exists('readfile')) {
	// conditional GET
	$modifiedSince = '';
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		$modifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
	}
	$lastModified = gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT';

	// optional compression
	$compressed = false;
	$encoding = '';
	if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
		$acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
		if (extension_loaded('zlib') && function_exists('file_get_contents') && function_exists('file_put_contents')) {
			if (preg_match('/(?:^|, ?)(deflate)(?:,|$)/', $acceptEncoding, $matches)) {
				$encoding = 'deflate';
				$filegz = '../piwik.js.deflate';
			} else if (preg_match('/(?:^|, ?)((x-)?gzip)(?:,|$)/', $acceptEncoding, $matches)) {
				$encoding = $matches[1];
				$filegz = '../piwik.js.gz';
			}

			if (!empty($encoding)) {
				// compress-on-demand and use cache
				if(!file_exists($filegz) || (filemtime($file) > filemtime($filegz))) {
					$data = file_get_contents($file);

					if ($encoding == 'deflate') {
						$data = gzcompress($data, 9);
					} else if ($encoding == 'gzip' || $encoding == 'x-gzip') {
						$data = gzencode($data, 9);
					}

					file_put_contents($filegz, $data);
					$file = $filegz;
				}

				$compressed = true;
				$file = $filegz;
			}
		} else {
			// manually compressed
			$filegz = '../piwik.js.gz';
			if (preg_match('/(?:^|, ?)((x-)?gzip)(?:,|$)/', $acceptEncoding, $matches) && file_exists($filegz) && (filemtime($file) < filemtime($filegz))) {
				$encoding = $matches[1];
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
