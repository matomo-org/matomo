<?php
// matomo.php test harness

if (!defined('PIWIK_DOCUMENT_ROOT')) {
	define('PIWIK_DOCUMENT_ROOT', dirname(__FILE__) . '/../..');
}

define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);


require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

$environment = new \Piwik\Application\Environment(null);
$environment->init();
$dbConfig = Piwik\Config::getInstance()->database_tests;
$dbConfig['dbname'] = 'tracker_tests';

try {
	Piwik\Db::createDatabaseObject($dbConfig);
} catch (Exception $e) {
	$dbInfosConnectOnly = $dbConfig;
	$dbInfosConnectOnly['dbname'] = null;
	Piwik\Db::createDatabaseObject($dbInfosConnectOnly);
	Piwik\DbHelper::createDatabase($dbConfig['dbname']);
	Piwik\Db::createDatabaseObject($dbConfig);
}

$db = Piwik\Db::get();

function sendWebBug() {
	$trans_gif_64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
	header("Content-type: image/gif");
	print(base64_decode($trans_gif_64));
}

function isPost()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

if (!Piwik\Db::hasDatabaseObject()) {
	sendWebBug();
	exit;
}

function getNextRequestId($db, $token)
{
    $requests = $db->fetchAll("SELECT uri FROM requests WHERE token = \"$token\"");

    if (empty($requests)) {
        return 1;
    }

    return count($requests) + 1;
}

try {
	$db->query( 'CREATE TABLE IF NOT EXISTS `requests` (requestid TEXT, token TEXT, ip TEXT, ts TEXT, uri TEXT, referer TEXT, ua TEXT) DEFAULT CHARSET=utf8' );
} catch (Exception $e) {
	header("HTTP/1.0 500 Internal Server Error");
	exit;
}

function logRequest($db, $uri, $data) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $ts = $_SERVER['REQUEST_TIME'];

//		$uri = htmlspecialchars($uri);

    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $ua = $_SERVER['HTTP_USER_AGENT'];

    $token = isset($data['token']) ? $data['token'] : '';

    $id = getNextRequestId($db, $token);

    $query = $db->query(
        "INSERT INTO requests (requestid, token, ip, ts, uri, referer, ua) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$id, $token, $ip, $ts, $uri, $referrer, $ua]
    );

    return $query;
}

if (isset($_GET['requests'])) {
	$token = htmlentities($_GET['requests'], ENT_COMPAT | ENT_HTML401, 'UTF-8');
	$ua = $_SERVER['HTTP_USER_AGENT'];

	echo "<html><head><title>$token</title></head><body>\n";

	$result = @$db->fetchAll("SELECT uri FROM requests WHERE token = \"$token\" AND ua = \"$ua\" ORDER BY ts ASC, requestid ASC");
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
        $data     = json_decode($_REQUEST['data'], true);

        if (!empty($requests) && isPost()) {
            $query = true;
            foreach ($requests['requests'] as $request) {
                if (empty($data) && preg_match('/data=%7B%22token%22%3A%22([a-z0-9A-Z]*?)%22%7D/', $request, $matches)) {
                    // safari and opera
                    $data = array('token' => $matches[1]);
                }

                $query = $query && logRequest($db, $uri . $request, $data);
            }
        } else {

            if (isPost()) {
                $uri .= '?' . file_get_contents('php://input');
            }

            $query = logRequest($db, $uri, $data);
        }

		if (!$query) {
			header("HTTP/1.0 500 Internal Server Error");
		} else {
			sendWebBug();
		}
	}
}
