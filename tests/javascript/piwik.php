<?php
// piwik.php test harness

require_once(dirname(__FILE__).'/SQLite.php');

function sendWebBug() {
	$trans_gif_64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
	header("Content-type: image/gif");
	print(base64_decode($trans_gif_64));
}

function isPost()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

if (!file_exists("enable_sqlite")) {
	sendWebBug();
	exit;
}

if (!class_exists('SQLite')) {
	sendWebBug();
	exit;
}

$sqlite = new SQLite( 'unittest2.dbf' );
if (!$sqlite) {
	header("HTTP/1.0 500 Internal Server Error");
	exit;
}

function getNextRequestId($sqlite, $token)
{
    $requests = $sqlite->query_array("SELECT uri FROM requests WHERE token = \"$token\"");

    if (empty($requests)) {
        return 1;
    }

    return count($requests) + 1;
}

if (filesize(dirname(__FILE__).'/unittest2.dbf') == 0)
{
	try {
		$query = @$sqlite->exec( 'CREATE TABLE requests (requestid TEXT, token TEXT, ip TEXT, ts TEXT, uri TEXT, referer TEXT, ua TEXT)' );
	} catch (Exception $e) {
		header("HTTP/1.0 500 Internal Server Error");
		exit;
	}
}

function logRequest($sqlite, $uri, $data) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $ts = $_SERVER['REQUEST_TIME'];

//		$uri = htmlspecialchars($uri);

    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $ua = $_SERVER['HTTP_USER_AGENT'];

    $token = isset($data['token']) ? $data['token'] : '';

    $id = getNextRequestId($sqlite, $token);

    $query = $sqlite->exec("INSERT INTO requests (requestid, token, ip, ts, uri, referer, ua) VALUES (\"$id\", \"$token\", \"$ip\", \"$ts\", \"$uri\", \"$referrer\", \"$ua\")");

    return $query;
}

if (isset($_GET['requests'])) {
	$token = htmlentities($_GET['requests']);
	$ua = $_SERVER['HTTP_USER_AGENT'];

	echo "<html><head><title>$token</title></head><body>\n";

	sleep(5);

//	$result = $sqlite->query_array("SELECT uri FROM requests");
	$result = @$sqlite->query_array("SELECT uri FROM requests WHERE token = \"$token\" AND ua = \"$ua\" ORDER BY ts ASC, requestid ASC");
	if ($result !== false) {
		$nofRows = count($result);
		echo "<span>$nofRows</span>\n";

		foreach ($result as $entry) {
			echo "<span>". $entry['uri'] ."</span>\n";
		}
	}

	echo "</body></html>\n";
} else {

	if (!isset($_REQUEST['data'])) {
        header("HTTP/1.0 400 Bad Request");
	} else {

        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        $input    = file_get_contents("php://input");
        $requests = @json_decode($input, true);
        $data     = json_decode(get_magic_quotes_gpc() ? stripslashes($_REQUEST['data']) : $_REQUEST['data'], true);

        if (!empty($requests) && isPost()) {
            $query = true;
            foreach ($requests['requests'] as $request) {
                if (empty($data) && preg_match('/data=%7B%22token%22%3A%22([a-z0-9A-Z]*?)%22%7D/', $request, $matches)) {
                    // safari and opera
                    $data = array('token' => $matches[1]);
                }

                $query = $query && logRequest($sqlite, $uri . $request, $data);
            }
        } else {

            if (isPost()) {
                $uri .= '?' . file_get_contents('php://input');
            }

            $query = logRequest($sqlite, $uri, $data);
        }

		if (!$query) {
			header("HTTP/1.0 500 Internal Server Error");
		} else {
//			echo 'Number of rows modified: ', $sqlite->changes();
			sendWebBug();
		}
	}
}

$sqlite->close();
