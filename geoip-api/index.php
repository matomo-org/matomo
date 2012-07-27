<?php

require 'Slim/Slim.php';

$app = new Slim();

// connect to piwik db
ini_set('display_errors', 'on');
error_reporting(E_ALL);

$cfg = parse_ini_file('../../../config/config.ini.php', true);
$pdo = 'mysql:host=' . $cfg['database']['host'] . ';dbname=' . $cfg['database']['dbname'];
$piwik_db = new PDO(
	$pdo, $cfg['database']['username'], $cfg['database']['password']
);

function jsonify($res, $app) {
   if ($app->request()->get('callback')) {
      print $app->request()->get('callback') . '(' . json_encode($res) . ');';
   } else {
      print json_encode($res);
   }
}

function date_query($app) {
    $startDate = $app->request()->get('startDate');
    $endDate = $app->request()->get('endDate');
    $col = 'visit_first_action_time';
    $query = $col.' >= "'.$startDate.'" AND '.$col.' <= "'.$endDate.' 23:59:59" ';
    return $query;
}

$app->get('/:country/cities', function($country) use ($app, $piwik_db) {
    $idSite = $app->request()->get('idSite');
    $res = $piwik_db->query('SELECT location_geoip_city as city, location_geoip_latitude as latitude, location_geoip_longitude as longitude, COUNT(*) as nb_visits FROM piwik_log_visit WHERE idSite = '.intval($idSite).' AND location_geoip_country = "'.strtolower($country).'" AND '.date_query($app).' GROUP BY location_geoip_city ORDER BY nb_visits DESC LIMIT 300', PDO::FETCH_ASSOC);
    $out = array();
    foreach ($res as $row) {
        $out[] = $row;
    }
    jsonify($out, $app);
});

$app->get('/:country/regions', function($country) use ($app, $piwik_db) {
    $idSite = $app->request()->get('idSite');
    $res = $piwik_db->query('SELECT location_geoip_region as code, COUNT(*) as nb_visits FROM piwik_log_visit WHERE idSite = '.intval($idSite).' AND location_geoip_country = "'.strtolower($country).'" AND '.date_query($app).' GROUP BY location_geoip_region;', PDO::FETCH_ASSOC);
    $out = array();
    foreach ($res as $row) {
        $out[] = $row;
    }
    jsonify($out, $app);
});

$app->run();
