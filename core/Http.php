<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Server-side http client to retrieve content from remote servers, and optionally save to a local file.
 * Used to check for the latest Piwik version and download updates.
 *
 * @package Piwik
 */
class Piwik_Http
{
	/**
	 * Get "best" available transport method for sendHttpRequest() calls.
	 *
	 * @return string
	 */
	static public function getTransportMethod()
	{
		$method = 'curl';
		if(!function_exists('curl_init'))
		{
			$method = 'fopen';
			if(@ini_get('allow_url_fopen') != '1')
			{
				$method = 'socket';
				if(!function_exists('fsockopen'))
				{
					return null;
				}
			}
		}
		return $method;
	}

	/**
	 * Sends http request ensuring the request will fail before $timeout seconds
	 *
	 * If no $destinationPath is specified, the trimmed response (without header) is returned as a string.
	 * If a $destinationPath is specified, the response (without header) is saved to a file.
	 *
	 * @param string $aUrl
	 * @param int $timeout
	 * @param string $userAgent
	 * @param string $destinationPath
	 * @param int $followDepth
	 * @param bool $acceptLanguage
	 * @throws Exception
	 * @return bool true (or string) on success; false on HTTP response error code (1xx or 4xx)
	 */
	static public function sendHttpRequest($aUrl, $timeout, $userAgent = null, $destinationPath = null, $followDepth = 0, $acceptLanguage = false)
	{
		// create output file
		$file = null;
		if($destinationPath)
		{
			// Ensure destination directory exists 
			Piwik_Common::mkdir(dirname($destinationPath));
			if (($file = @fopen($destinationPath, 'wb')) === false || !is_resource($file))
			{
				throw new Exception('Error while creating the file: ' . $destinationPath);
			}
		}

		$acceptLanguage = $acceptLanguage ? 'Accept-Language: '.$acceptLanguage : '';
		return self::sendHttpRequestBy(self::getTransportMethod(), $aUrl, $timeout, $userAgent, $destinationPath, $file, $followDepth, $acceptLanguage); 			
	}

	/**
	 * Sends http request using the specified transport method
	 *
	 * @param string $method
	 * @param string $aUrl
	 * @param int $timeout
	 * @param string $userAgent
	 * @param string $destinationPath
	 * @param resource $file
	 * @param int $followDepth
	 * @param bool|string $acceptLanguage Accept-language header
	 * @param bool $acceptInvalidSslCertificate Only used with $method == 'curl'. If set to true (NOT recommended!) the SSL certificate will not be checked
	 * @throws Exception
	 * @return bool true (or string) on success; false on HTTP response error code (1xx or 4xx)
	 */
	static public function sendHttpRequestBy($method = 'socket', $aUrl, $timeout, $userAgent = null, $destinationPath = null, $file = null, $followDepth = 0, $acceptLanguage = false, $acceptInvalidSslCertificate = false)
	{
		if ($followDepth > 5)
		{
			throw new Exception('Too many redirects ('.$followDepth.')');
		}

		$contentLength = 0;
		$fileLength = 0;

		// Piwik services behave like a proxy, so we should act like one.
		$xff = 'X-Forwarded-For: '
			. (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] . ',' : '')
			. Piwik_IP::getIpFromHeader();
		$via = 'Via: '
			. (isset($_SERVER['HTTP_VIA']) && !empty($_SERVER['HTTP_VIA']) ? $_SERVER['HTTP_VIA'] . ', ' : '')
			. Piwik_Version::VERSION . ' Piwik'
			. ($userAgent ? " ($userAgent)" : '');
		$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Piwik/'.Piwik_Version::VERSION;

		// proxy configuration
		$proxyHost = Piwik_Config::getInstance()->proxy['host'];
		$proxyPort = Piwik_Config::getInstance()->proxy['port'];
		$proxyUser = Piwik_Config::getInstance()->proxy['username'];
		$proxyPassword = Piwik_Config::getInstance()->proxy['password'];

		if($method == 'socket')
		{
			// initialization
			$url = @parse_url($aUrl);
			if($url === false || !isset($url['scheme']))
			{
				throw new Exception('Malformed URL: '.$aUrl);
			}

			if($url['scheme'] != 'http')
			{
				throw new Exception('Invalid protocol/scheme: '.$url['scheme']);
			}
			$host = $url['host'];
			$port = isset($url['port)']) ? $url['port'] : 80;
			$path = isset($url['path']) ? $url['path'] : '/';
			if(isset($url['query']))
			{
				$path .= '?'.$url['query'];
			}
			$errno = null;
			$errstr = null;

			$proxyAuth = null;
			if(!empty($proxyHost) && !empty($proxyPort))
			{
				$connectHost = $proxyHost;
				$connectPort = $proxyPort;
				if(!empty($proxyUser) && !empty($proxyPassword))
				{
					$proxyAuth = 'Proxy-Authorization: Basic '.base64_encode("$proxyUser:$proxyPassword") ."\r\n";
				}
				$requestHeader = "GET $aUrl HTTP/1.1\r\n";
			}
			else
			{
				$connectHost = $host;
				$connectPort = $port;
				$requestHeader = "GET $path HTTP/1.0\r\n";
			}

			// connection attempt
			if (($fsock = @fsockopen($connectHost, $connectPort, $errno, $errstr, $timeout)) === false || !is_resource($fsock))
			{
				if(is_resource($file)) { @fclose($file); }
				throw new Exception("Error while connecting to: $host. Please try again later. $errstr");
			}

			// send HTTP request header
			$requestHeader .=
				"Host: $host".($port != 80 ? ':'.$port : '')."\r\n"
				.($proxyAuth ? $proxyAuth : '')
				.'User-Agent: '.$userAgent."\r\n"
				. ($acceptLanguage ? $acceptLanguage ."\r\n" : '') 
				.$xff."\r\n"
				.$via."\r\n"
				."Connection: close\r\n"
				."\r\n";
			fwrite($fsock, $requestHeader);

			$streamMetaData = array('timed_out' => false);
			@stream_set_blocking($fsock, true);

			if (function_exists('stream_set_timeout'))
			{
				@stream_set_timeout($fsock, $timeout);
			}
			elseif (function_exists('socket_set_timeout'))
			{
				@socket_set_timeout($fsock, $timeout);
			}

			// process header
			$status = null;
			$expectRedirect = false;

			while(!feof($fsock))
			{
				$line = fgets($fsock, 4096);

				$streamMetaData = @stream_get_meta_data($fsock);
				if($streamMetaData['timed_out'])
				{
					if(is_resource($file)) { @fclose($file); }
					@fclose($fsock);
					throw new Exception('Timed out waiting for server response');
				}

				// a blank line marks the end of the server response header
				if(rtrim($line, "\r\n") == '')
				{
					break;
				}

				// parse first line of server response header
				if(!$status)
				{
					// expect first line to be HTTP response status line, e.g., HTTP/1.1 200 OK
					if(!preg_match('~^HTTP/(\d\.\d)\s+(\d+)(\s*.*)?~', $line, $m))
					{
						if(is_resource($file)) { @fclose($file); }
						@fclose($fsock);
						throw new Exception('Expected server response code.  Got '.rtrim($line, "\r\n"));
					}

					$status = (integer) $m[2];

					// Informational 1xx or Client Error 4xx
					if ($status < 200 || $status >= 400)
					{
						if(is_resource($file)) { @fclose($file); }
						@fclose($fsock);
						return false;
					}

					continue;
				}

				// handle redirect
				if(preg_match('/^Location:\s*(.+)/', rtrim($line, "\r\n"), $m))
				{
					if(is_resource($file)) { @fclose($file); }
					@fclose($fsock);
					// Successful 2xx vs Redirect 3xx
					if($status < 300)
					{
						throw new Exception('Unexpected redirect to Location: '.rtrim($line).' for status code '.$status);
					}
					return self::sendHttpRequestBy($method, trim($m[1]), $timeout, $userAgent, $destinationPath, $file, $followDepth+1, $acceptLanguage);
				}

				// save expected content length for later verification
				if(preg_match('/^Content-Length:\s*(\d+)/', $line, $m))
				{
					$contentLength = (integer) $m[1];
				}
			}

			if(feof($fsock))
			{
				throw new Exception('Unexpected end of transmission');
			}

			// process content/body
			$response = '';

			while(!feof($fsock))
			{
				$line = fread($fsock, 8192);

				$streamMetaData = @stream_get_meta_data($fsock);
				if($streamMetaData['timed_out'])
				{
					if(is_resource($file)) { @fclose($file); }
					@fclose($fsock);
					throw new Exception('Timed out waiting for server response');
				}

				$fileLength += Piwik_Common::strlen($line);

				if(is_resource($file))
				{
					// save to file
					fwrite($file, $line);
				}
				else
				{
					// concatenate to response string
					$response .= $line;
				}
			}

			// determine success or failure
			@fclose(@$fsock);
		}
		else if($method == 'fopen')
		{
			$response = false;

			// we make sure the request takes less than a few seconds to fail
			// we create a stream_context (works in php >= 5.2.1)
			// we also set the socket_timeout (for php < 5.2.1)
			$default_socket_timeout = @ini_get('default_socket_timeout');
			@ini_set('default_socket_timeout', $timeout);

			$ctx = null;
			if(function_exists('stream_context_create')) {
				$stream_options = array(
					'http' => array(
						'header' => 'User-Agent: '.$userAgent."\r\n"
									.($acceptLanguage ? $acceptLanguage."\r\n" : '')
									.$xff."\r\n"
									.$via."\r\n",
						'max_redirects' => 5, // PHP 5.1.0
						'timeout' => $timeout, // PHP 5.2.1
					)
				);

				if(!empty($proxyHost) && !empty($proxyPort))
				{
					$stream_options['http']['proxy'] = 'tcp://'.$proxyHost.':'.$proxyPort;
					$stream_options['http']['request_fulluri'] = true; // required by squid proxy
					if(!empty($proxyUser) && !empty($proxyPassword))
					{
						$stream_options['http']['header'] .= 'Proxy-Authorization: Basic '.base64_encode("$proxyUser:$proxyPassword")."\r\n";
					}
				}

				$ctx = stream_context_create($stream_options);
			}

			// save to file
			if(is_resource($file))
			{
				$handle = fopen($aUrl, 'rb', false, $ctx);
				while(!feof($handle))
				{
					$response = fread($handle, 8192);
					$fileLength += Piwik_Common::strlen($response);
					fwrite($file, $response);
				}
				fclose($handle);
			}
			else
			{
				$response = @file_get_contents($aUrl, 0, $ctx);
				$fileLength = Piwik_Common::strlen($response);
			}

			// restore the socket_timeout value
			if(!empty($default_socket_timeout))
			{
				@ini_set('default_socket_timeout', $default_socket_timeout);
			}
		}
		else if($method == 'curl')
		{
			$ch = @curl_init();

			if(!empty($proxyHost) && !empty($proxyPort))
			{
				@curl_setopt($ch, CURLOPT_PROXY, $proxyHost.':'.$proxyPort);
				if(!empty($proxyUser) && !empty($proxyPassword))
				{
					// PROXYAUTH defaults to BASIC
					@curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUser.':'.$proxyPassword);
				}
			}

			$curl_options = array(
				// internal to ext/curl
				CURLOPT_BINARYTRANSFER => is_resource($file),

				// curl options (sorted oldest to newest)
				CURLOPT_URL => $aUrl,
				CURLOPT_USERAGENT => $userAgent,
				CURLOPT_HTTPHEADER => array(
					$xff,
					$via,
					$acceptLanguage
				),
				CURLOPT_HEADER => false,
				CURLOPT_CONNECTTIMEOUT => $timeout,
			);
			// Case archive.php is triggering archiving on https:// and the certificate is not valid
			if($acceptInvalidSslCertificate)
			{
				$curl_options += array(
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_SSL_VERIFYPEER => false, 
				);
			}
			
			@curl_setopt_array($ch, $curl_options);

			/*
			 * use local list of Certificate Authorities, if available
			 */
			if(file_exists(PIWIK_INCLUDE_PATH . '/core/DataFiles/cacert.pem'))
			{
				@curl_setopt($ch, CURLOPT_CAINFO, PIWIK_INCLUDE_PATH . '/core/DataFiles/cacert.pem');
			}

			/*
			 * as of php 5.2.0, CURLOPT_FOLLOWLOCATION can't be set if
			 * in safe_mode or open_basedir is set
			 */
			if((string)ini_get('safe_mode') == '' && ini_get('open_basedir') == '')
			{ 
				$curl_options = array(
					// curl options (sorted oldest to newest)
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_MAXREDIRS => 5, 
				);
				@curl_setopt_array($ch, $curl_options);
			}

			if(is_resource($file))
			{
				// write output directly to file
				@curl_setopt($ch, CURLOPT_FILE, $file);
			}
			else
			{
				// internal to ext/curl
				@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			}

			ob_start();
			$response = @curl_exec($ch);
			ob_end_clean();

			if($response === true)
			{
				$response = '';
			}
			else if($response === false)
			{
				$errstr = curl_error($ch);
				if($errstr != '')
				{
					throw new Exception('curl_exec: '.$errstr);
				}
				$response = '';
			}

			$contentLength = @curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
			$fileLength = is_resource($file) ? @curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD) : Piwik_Common::strlen($response);

			@curl_close($ch);
			unset($ch);
		}
		else
		{
			throw new Exception('Invalid request method: '.$method);
		}

		if(is_resource($file))
		{
			fflush($file);
			@fclose($file);

			$fileSize = filesize($destinationPath);
			if((($contentLength > 0) && ($fileLength != $contentLength)) || ($fileSize != $fileLength))
			{
				throw new Exception('File size error: '.$destinationPath.'; expected '.$contentLength.' bytes; received '.$fileLength.' bytes; saved '.$fileSize.' bytes to file');
			}
			return true;
		}

		if(($contentLength > 0) && ($fileLength != $contentLength))
		{
			throw new Exception('Content length error: expected '.$contentLength.' bytes; received '.$fileLength.' bytes');
		}
		return trim($response);
	}

	/**
	 * Fetch the file at $url in the destination $destinationPath
	 *
	 * @param string $url
	 * @param string $destinationPath
	 * @param int $tries
	 * @return true on success, throws Exception on failure
	 */
	static public function fetchRemoteFile($url, $destinationPath = null, $tries = 0)
	{
		@ignore_user_abort(true);
		Piwik::setMaxExecutionTime(0);
		return self::sendHttpRequest($url, 10, 'Update', $destinationPath, $tries);
	}
}
