<?php
use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\FrontController;
use Piwik\IP;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp\Pecl;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp\Php;

require_once './cli-script-bootstrap.php';

ini_set("memory_limit", "512M");

$query = "SELECT count(*) FROM " . Common::prefixTable('log_visit');
$count = Db::fetchOne($query);

// when script run via browser, check for Super User & output html page to do conversion via AJAX
if (!Common::isPhpCliMode()) {
    try {
        Piwik::checkUserHasSuperUserAccess();
    } catch (Exception $e) {
        Log::error('[error] You must be logged in as Super User to run this script. Please login in to Piwik and refresh this page.');
        exit;
    }
    // the 'start' query param will be supplied by the AJAX requests, so if it's not there, the
    // user is viewing the page in the browser.
    if (Common::getRequestVar('start', false) === false) {
        // output HTML page that runs update via AJAX
        ?>
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
        <html>
        <head>
            <script type="text/javascript" src="../../libs/jquery/dist/jquery.min.js"></script>
            <script type="text/javascript">
                (function ($) {
                    var count = <?php echo $count; ?>;
                    var doIteration = function (start) {
                        if (start >= count) {
                            return;
                        }

                        var end = Math.min(start + 100, count);
                        $.ajax({
                            type: 'POST',
                            url: 'geoipUpdateRows.php',
                            data: {
                                start: start,
                                end: end
                            },
                            async: true,
                            error: function (xhr, status, error) {
                                $('body')
                                    .append(xhr.responseText)
                                    .append('<div style="color:red"><strong>An error occured!</strong></div>');
                            },
                            success: function (response) {
                                doIteration(end);
                                $('body').append(response);
                                var body = $('body')[0];
                                body.scrollTop = body.scrollHeight;
                            }
                        });
                    };

                    doIteration(0);
                }(jQuery));
            </script>
        </head>
        <body>
        </body>
        </html>
        <?php
        exit;
    } else {
        $start = Common::getRequestVar('start', 0, 'int');
        $end = min($count, Common::getRequestVar('end', $count, 'int'));
        $limit = $end - $start;
    }
} else // command line
{
    $start = 0;
    $end = $count;
    $limit = 1000;
}

function geoipUpdateError($message)
{
    Log::error($message);
    Common::sendHeader('HTTP/1.1 500 Internal Server Error', $replace = true, $responseCode = 500);
    exit;
}

// only display notes if on command line (where start will == 0 for that part of script) or on
// first AJAX call by browser
$displayNotes = $start == 0;

// try getting the pecl location provider
$provider = new Pecl();
if (!$provider->isAvailable()) {
    if ($displayNotes) {
        Log::info("[note] The GeoIP PECL extension is not installed.");
    }

    $provider = null;
} else {
    $workingOrError = $provider->isWorking();
    if ($workingOrError !== true) {
        if ($displayNotes) {
            Log::info("[note] The GeoIP PECL extension is broken: $workingOrError");
        }
        if (Common::isPhpCliMode()) {
            Log::info("[note] Make sure your command line PHP is configured to use the PECL extension.");
        }
        $provider = null;
    }
}

// use php api if pecl extension cannot be used
if (is_null($provider)) {
    if ($displayNotes) {
        Log::info("[note] Falling back to PHP API. This may become too slow for you. If so, you can read this link on how to install the PECL extension: http://piwik.org/faq/how-to/#faq_164");
    }

    $provider = new Php();
    if (!$provider->isAvailable()) {
        if ($displayNotes) {
            Log::info("[note] The GeoIP PHP API is not available. This means you do not have a GeoIP location database in your ./misc directory. The database must be named either GeoIP.dat or GeoIPCity.dat based on the type of database it is.");
        }
        $provider = null;
    } else {
        $workingOrError = $provider->isWorking();
        if ($workingOrError !== true) {
            if ($displayNotes) {
                Log::info("[note] The GeoIP PHP API is broken: $workingOrError");
            }
            $provider = null;
        }
    }
}

if (is_null($provider)) {
    geoipUpdateError("\n[error] There is no location provider that can be used with this script. Only the GeoIP PECL module or the GeoIP PHP API can be used at present. Please install and configure one of these first.");
}

$info = $provider->getInfo();
if ($displayNotes) {
    Log::info("[note] Found working provider: {$info['id']}");
}

// perform update
$logVisitFieldsToUpdate = array('location_country'   => LocationProvider::COUNTRY_CODE_KEY,
                                'location_region'    => LocationProvider::REGION_CODE_KEY,
                                'location_city'      => LocationProvider::CITY_NAME_KEY,
                                'location_latitude'  => LocationProvider::LATITUDE_KEY,
                                'location_longitude' => LocationProvider::LONGITUDE_KEY);

if ($displayNotes) {
    Log::info("\n$count rows to process in " . Common::prefixTable('log_visit')
        . " and " . Common::prefixTable('log_conversion') . "...");
}
flush();
for (; $start < $end; $start += $limit) {
    $rows = Db::fetchAll("SELECT idvisit, location_ip, " . implode(',', array_keys($logVisitFieldsToUpdate)) . "
						FROM " . Common::prefixTable('log_visit') . "
						LIMIT $start, $limit");
    if (!count($rows)) {
        continue;
    }

    foreach ($rows as $row) {
        $fieldsToSet = array();
        foreach ($logVisitFieldsToUpdate as $field => $ignore) {
            if (empty($fieldsToSet[$field])) {
                $fieldsToSet[] = $field;
            }
        }

        // skip if it already has a location
        if (empty($fieldsToSet)) {
            continue;
        }

        $ip = IP::N2P($row['location_ip']);
        $location = $provider->getLocation(array('ip' => $ip));

        if (!empty($location[LocationProvider::COUNTRY_CODE_KEY])) {
            $location[LocationProvider::COUNTRY_CODE_KEY] =
                strtolower($location[LocationProvider::COUNTRY_CODE_KEY]);
        }
        $row['location_country'] = strtolower($row['location_country']);

        $columnsToSet = array();
        $bind = array();
        foreach ($logVisitFieldsToUpdate as $column => $locationKey) {
            if (!empty($location[$locationKey])
                && $location[$locationKey] != $row[$column]
            ) {
                $columnsToSet[] = $column . ' = ?';
                $bind[] = $location[$locationKey];
            }
        }

        if (empty($columnsToSet)) {
            continue;
        }

        $bind[] = $row['idvisit'];

        // update log_visit
        $sql = "UPDATE " . Common::prefixTable('log_visit') . "
				   SET " . implode(', ', $columnsToSet) . "
				 WHERE idvisit = ?";
        Db::query($sql, $bind);

        // update log_conversion
        $sql = "UPDATE " . Common::prefixTable('log_conversion') . "
				   SET " . implode(', ', $columnsToSet) . "
				 WHERE idvisit = ?";
        Db::query($sql, $bind);
    }
    Log::info(round($start * 100 / $count) . "% done...");
    flush();
}
if ($start >= $count) {
    Log::info("100% done!");
    Log::info("");
    Log::info("[note] Now that you've geolocated your old visits, you need to force your reports to be re-processed. See this FAQ entry: http://piwik.org/faq/how-to/#faq_59");
}