<?php

/**
 * Step 1: Require the Slim PHP 5 Framework
 *
 * If using the default file layout, the `Slim/` directory
 * will already be on your include path. If you move the `Slim/`
 * directory elsewhere, ensure that it is added to your include path
 * or update this file path as needed.
 */
require 'Slim/Slim.php';

/**
 * Step 2: Instantiate the Slim application
 *
 * Here we instantiate the Slim application with its default settings.
 * However, we could also pass a key-value array of settings.
 * Refer to the online documentation for available settings.
 */
$app = new Slim();

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, and `Slim::delete`
 * is an anonymous function. If you are using PHP < 5.3, the
 * second argument should be any variable that returns `true` for
 * `is_callable()`. An example GET route for PHP < 5.3 is:
 *
 * $app = new Slim();
 * $app->get('/hello/:name', 'myFunction');
 * function myFunction($name) { echo "Hello, $name"; }
 *
 * The routes below work with PHP >= 5.3.
 */

// connect to geoip db
ini_set('display_errors', 'on');
error_reporting(E_ALL);

$cfg = parse_ini_file('../../..//config/config.ini.php', true);

$geoip_db = new PDO(
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
    $query = $col.' >= "'.$startDate.'" AND '.$col.' <= "'.$endDate.'" ';
    return $query;
}

$app->get('/piwik/:idsite/geoip/:country/cities', function($idSite, $country) use ($app, $piwik_db) {
    $res = $piwik_db->query('SELECT location_geoip_city as city, location_geoip_latitude as latitude, location_geoip_longitude as longitude, COUNT(*) as nb_visits FROM piwik_log_visit WHERE idSite = '.intval($idSite).' AND location_geoip_country = "'.strtolower($country).'" AND '.date_query($app).' GROUP BY location_geoip_city ORDER BY nb_visits DESC LIMIT 300', PDO::FETCH_ASSOC);
    $out = array();
    foreach ($res as $row) {
        $out[] = $row;
    }
    jsonify($out, $app);
});

$app->get('/piwik/:idsite/geoip/:country/regions', function($idSite, $country) use ($app, $piwik_db) {
    $res = $piwik_db->query('SELECT location_geoip_region as code, COUNT(*) as nb_visits FROM piwik_log_visit WHERE idSite = '.intval($idSite).' AND location_geoip_country = "'.strtolower($country).'" AND '.date_query($app).' GROUP BY location_geoip_region;', PDO::FETCH_ASSOC);
    $out = array();
    foreach ($res as $row) {
        $out[] = $row;
    }
    jsonify($out, $app);
});


/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This is responsible for executing
 * the Slim application using the settings and routes defined above.
 */
$app->run();
