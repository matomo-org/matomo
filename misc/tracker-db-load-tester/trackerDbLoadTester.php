<?php

require_once './trackerDbQueryGenerator.php';

/**
 * Tracker db load tester
 *
 * Utility to simulate the database load of tracking request queries
 *
 */

$usage = <<<USAGE
Usage: php trackerDbLoadTester.php -d=[DB NAME] -h=[DB HOST] -u=[DB USER] -p=[DB PASSWORD] {-r=[REQUEST LIMIT {-P=[DB PORT]} {-v=[VERBOSITY]}
    Example: php trackerDbLoadTester.php -d=mydb -h=127.0.0.1 -u=root -p=123 -P=3306
    -d          Database name, if 'random' then a randomly named database will automatically be created and used,
                if 'sequential' then use randomly choose sequentially named databases for each hit in the format
                matomo_test_db_xxx use with -smin and -smax options   
    -t          Database type, 'mysql' or 'tidb', used to adjust schema created with -d=random, defaults to 'mysql'
    -h          Database hostname, defaults to 'localhost', multiple hosts can be specified and will be chosen randomly
    -u          Database username, defaults to 'root''
    -p          Database password, defaults to none
    -P          Database port, defaults to 3306
    -r          Tracking requests limit, will insert this many tracking requests then exit, runs indefinitely if omitted
    -v          Verbosity of output [0 = quiet, 3 = show everything]
    -T          Throttle the number of requests per second to this value
    -b          Basic test, do a very basic insert test instead of using tracker data 1=insert k/v, 2=select/insert
    -c          Create a new random database and tracking data schema only then exit
    -n          Percent of logged actions which will trigger a goal conversion, defaults to zero. Goals ids are 1..10    
    -m          Create x multiple headless test processes using the supplied parameters
    -ds         Start date in UTC for random visit/action date range, yyyy-mm-dd,hh:mm:ss
    -de         End date for random visit/action date, must be paired with -ds, if omitted then current date is used   
    --cleanup   Delete all randomly named test databases
    -rs         Create visits for random sites starting at this siteid
    -re         Create visits for random sites ending at this siteid
    -ac         Action count, create this many random page views per visit, default to 1 if ommitted.
    -smin       If using sequential database names then use this as the minimum sequential value, eg. -smin=1
    -smax       If using sequential database names then use this as the minimum sequential value, eg. -smax=100
    -nc         Start a new PDO connection for every request
    -rtq        For every tracking request also query x random application tables, eg. -rtq=2
    

USAGE;

#region Get parameters from command line
$dbName = null;
$dbCreate = false;
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbPort = 3306;
$dbType = 'mysql';
$verbosity = 1;
$requests = -1;
$cleanUp = false;
$throttle = -1;
$basicTest = 0;
$multipleProcesses = 0;
$dbCreateOnly = false;
$randomDateStart = null;
$randomDateEnd = null;
$conversionPercent = 0;
$randomSiteStart = 0;
$randomSiteEnd = 0;
$newConnection = false;
$sequentialMin = 1;
$sequentialMax = 5000;
$randomTableQuery = 0;
$randomDb = false;
$usePrepareCache = true;
$tmpHack = false;
$actionCount = 1;

// List of tables to read from if randomTableQuery is used
$tables = [
'access', 'archive_invalidations', 'brute_force_log', 'changes', 'custom_dimensions', 'goal', 'locks', 'log_conversion_item',
'log_profiling', 'logger_message', 'option', 'plugin_setting', 'privacy_logdata_anonymizations', 'report', 'report_subscriptions',
'segment', 'sequence', 'session', 'site', 'site_setting', 'site_url', 'tracking_failure', 'twofactor_recovery_code', 'user',
'user_dashboard', 'user_language', 'user_token_auth', 'archive_blob_2019_05', 'archive_blob_2019_08', 'archive_blob_2019_09',
'archive_blob_2019_10', 'archive_blob_2019_09', 'archive_blob_2019_10', 'archive_blob_2019_11', 'archive_blob_2019_12',
'archive_blob_2020_01', 'archive_blob_2020_02', 'archive_blob_2020_03', 'archive_blob_2020_04', 'archive_blob_2020_05',
'archive_blob_2020_06', 'archive_blob_2020_07', 'archive_blob_2020_08', 'archive_blob_2020_09', 'archive_blob_2020_10',
'archive_blob_2020_11', 'archive_blob_2020_12', 'archive_blob_2021_01', 'archive_blob_2021_02', 'archive_blob_2021_03',
'archive_blob_2021_04', 'archive_blob_2021_05', 'archive_blob_2021_06', 'archive_blob_2021_07', 'archive_blob_2021_08',
'archive_blob_2021_09', 'archive_blob_2021_10', 'archive_blob_2021_11', 'archive_blob_2021_12', 'archive_blob_2022_01',
'archive_blob_2022_02', 'archive_blob_2022_03', 'archive_blob_2022_04', 'archive_blob_2022_05', 'archive_numeric_2019_05',
'archive_numeric_2019_06', 'archive_numeric_2019_07', 'archive_numeric_2019_08', 'archive_numeric_2019_09',
'archive_numeric_2019_10', 'archive_numeric_2019_11', 'archive_numeric_2019_12', 'archive_numeric_2020_01',
'archive_numeric_2020_02', 'archive_numeric_2020_03', 'archive_numeric_2020_04', 'archive_numeric_2020_05',
'archive_numeric_2020_06', 'archive_numeric_2020_07', 'archive_numeric_2020_08', 'archive_numeric_2020_09',
'archive_numeric_2020_10', 'archive_numeric_2020_11', 'archive_numeric_2020_12', 'archive_numeric_2021_01',
'archive_numeric_2021_02', 'archive_numeric_2021_03', 'archive_numeric_2021_04', 'archive_numeric_2021_05',
'archive_numeric_2021_06', 'archive_numeric_2021_07', 'archive_numeric_2021_08', 'archive_numeric_2021_09',
'archive_numeric_2021_10', 'archive_numeric_2021_11', 'archive_numeric_2021_12', 'archive_numeric_2022_01',
'archive_numeric_2022_02', 'archive_numeric_2022_03', 'archive_numeric_2022_04', 'archive_numeric_2022_05'
];

foreach ($argv as $arg) {

    if ($arg == '--cleanup') {
        $cleanUp = true;
        continue;
    }

    if ($arg == '-c') {
        $dbCreateOnly = true;
        continue;
    }

    if ($arg == '-nc') {
        $newConnection = true;
        continue;
    }

    if ($arg == '-tmphack') {
        $tmpHack = true;
        continue;
    }

    $kv = explode('=', $arg);
    if (count($kv) != 2) {
        continue;
    }
    switch ($kv[0]) {
        case '-d':
            if ($kv[1] === 'random') {
                $dbName = "tracker_db_test_".bin2hex(random_bytes(10));
                $dbCreate = true;
                $randomDb = true;
            } else {
                $dbName = $kv[1];
            }
            break;
        case '-t':
            $dbType = $kv[1];
            break;
        case '-h':
            $dbHost = $kv[1];
            break;
        case '-u':
            $dbUser = $kv[1];
            break;
        case '-n':
            $conversionPercent = $kv[1];
            break;
        case '-p':
            $dbPass = $kv[1];
            break;
        case '-P':
            $dbPort = $kv[1];
            break;
        case '-r':
            $requests = $kv[1];
            break;
        case '-v':
            $verbosity = $kv[1];
            break;
        case '-T':
            $throttle = $kv[1];
            break;
        case '-b':
            $basicTest = $kv[1];
            break;
        case '-m':
            $multipleProcesses = $kv[1];
            break;
        case '-ds':
            if (strlen($kv[1]) !== 19 || strpos($kv[1],',') === false) {
                die("Invalid date passed to -ds option: please use format yyyy-mm-dd,hh:mm:ss\n");
            }
            $randomDateStart = strtotime(str_replace(',', ' ', $kv[1]));
            break;
        case '-de':
            if (strlen($kv[1]) !== 19 || strpos($kv[1],',') === false) {
                die("Invalid date passed to -de option: please use format yyyy-mm-dd,hh:mm:ss\n");
            }
            $randomDateEnd = strtotime(str_replace(',', ' ', $kv[1]));
            break;
        case '-rs':
            $randomSiteStart = $kv[1];
            break;
        case '-re':
            $randomSiteEnd = $kv[1];
            break;
        case '-ac':
            $actionCount = $kv[1];
            break;
        case '-smin':
            $sequentialMin = $kv[1];
            break;
        case '-smax':
            $sequentialMax = $kv[1];
            break;
        case '-rtq':
            $randomTableQuery = $kv[1];
            break;
    }
}

if ($dbName === null || $dbHost === null || $dbUser === null || $dbPass === null || $dbPort === null) {
    die($usage);
}

if ($randomDateStart && !$randomDateEnd) {
    $randomDateEnd = time();
}

#endregion

#region Spawn multiple processes

if ($multipleProcesses) {

    $argString = "";
    $paramSmin = null;
    $paramSmax = null;
    $divideSequential = false;
    $dbSeqPerThread = 0;

    foreach ($argv as $arg) {
        if ($arg == 'trackerDbLoadTester.php') {
            continue;
        }
        $kv = explode('=', $arg);
        if (count($kv) == 2 && $kv[0] == '-m') {
            continue;
        }
        if (count($kv) == 2 && $kv[0] == '-smin') {
            $paramSmin = $kv[1];
            continue;
        }
        if (count($kv) == 2 && $kv[0] == '-smax') {
            $paramSmax = $kv[1];
            continue;
        }
        if (count($kv) == 2 && $kv[0] == '-d' && $kv[1] == 'sequential') {
            $divideSequential= true;
        }
        $argString .= ' '.$arg;
    }

    // Divide out the sequential database range between threads
    if ($paramSmin && $paramSmax) {
        $dbCount = ($paramSmax - ($paramSmin == 1 ? 0 : $paramSmin));
        $dbSeqPerThread = ceil($dbCount / $multipleProcesses);
        echo $dbSeqPerThread;
    }

    $cmd = "/usr/bin/php ".__FILE__."".$argString;
    echo "Spawning ".$multipleProcesses." test processes with command:\n\n";
    echo $cmd."\n\n";


    for ($i = 0; $i < $multipleProcesses; $i++) {
        $tcmd = $cmd;
        if ($divideSequential && $dbSeqPerThread) {

            $divideMin = ($sequentialMin + ($dbSeqPerThread * $i));
            $divideMax = (($divideMin + $dbSeqPerThread) - 1);
            if ($i === ($multipleProcesses - 1) && $divideMax < $sequentialMax) {
                $divideMax = $sequentialMax;
            }

            $tcmd .= ' -smin='.$divideMin.' -smax='.$divideMax;
        }
        echo $tcmd."\n";
        exec("nohup ".$tcmd." > /dev/null 2>&1 & echo $!");
        usleep(500000);
        //echo ".";
    }

    echo "Done:\n";
    exec('ps ax | grep /usr/bin/php | grep trackerDbLoadTester | grep -v ps', $output);
    foreach ($output as $line) {
        echo $line."\n";
    }
    echo "\n";
    echo "killall /usr/bin/php to terminate\n";
    die();
}

#endregion

#region Create DSN
if (strpos($dbHost, ',') !== false) {
    $hosts = explode(',', $dbHost);
    $dbHost = $hosts[array_rand($hosts)];
}

if ($verbosity > 1) {
    echo "Host: $dbHost Type: $dbType User: '$dbUser' Password: '$dbPass' Port: $dbPort Request Limit: ".($requests === -1 ? 'unlimited' : $requests)."\n";
}

$dsn = "mysql:host=$dbHost;port=$dbPort;charset=UTF8";
#endregion

#region Do clean-up action and die
if ($cleanUp) {
    try {
        $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;charset=UTF8", $dbUser, $dbPass);
        if ($verbosity > 0) {
            echo "Cleaning up test databases...\n";
        }
        $dbs = query($prepareCache, $pdo, ['sql' => "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME LIKE 'tracker_db_test_%'", 'bind' => []]);
        $dropped = 0;
        foreach ($dbs as $db) {
            echo "Dropping database ".$db->SCHEMA_NAME."\n";
            $pdo->exec("DROP DATABASE ".$db->SCHEMA_NAME.";");
            $dropped++;
        }
        die("Dropped ".$dropped." test databases\n\n");

    } catch (PDOException $e) {
        echo $e->getMessage()."\n";
        die();
    }
}
#endregion

#region Do basic test instead - to compare vs tracker queries
if ($basicTest) {

    try {
        $dbName = "tracker_db_test_basic_".bin2hex(random_bytes(10));
        $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_PERSISTENT => true]);
        if ($verbosity > 0) {
            echo "Connected to the database server...\n";
        }
        $pdo->query("CREATE DATABASE `$dbName`;                
                GRANT ALL ON `$dbName`.* TO '$dbUser'@'localhost';
                FLUSH PRIVILEGES;");
        $pdo->query("USE $dbName");
        if ($verbosity > 1) {
            echo "Using database '$dbName'...\n";
        }

        $schemaSql = /** @lang Text */ "
            CREATE TABLE basic_test
            (
	            idaction bigint PRIMARY KEY AUTO_RANDOM(3),
	            name varchar(255) null	            
            );
            CREATE INDEX index_name on basic_test (name);
        ";

        if ($dbType !== 'tidb') {
            $schemaSql = str_replace('bigint PRIMARY KEY AUTO_RANDOM(3)', 'int UNSIGNED AUTO_INCREMENT PRIMARY KEY', $schemaSql);
        }
        $pdo->query($schemaSql);
        if ($verbosity > 0) {
            echo "Created new database '$dbName'...\n";
        }

        $requestCount = 0;
        $lastCount = 0;
        $lastTimeSample = microtime(true);
        while ($requestCount < $requests || $requests < 0) {

            $requestCount++;
            $lastCount++;

            $rand = bin2hex(random_bytes(2));

            if ($basicTest == 2) {
                /** @noinspection SqlResolve */
                $query = [
                    'sql'  => "SELECT name FROM basic_test WHERE name = :name",
                    'bind' => [':name' => $rand]
                ];
                query($prepareCache, $pdo, $query);
            }

            /** @noinspection SqlResolve */
            $query = [
                'sql'  => "INSERT IGNORE INTO basic_test (name) VALUES (:name)",
                'bind' => [':name' => $rand]
            ];
            query($prepareCache, $pdo, $query);

            if ($verbosity > 0) {
                if ((microtime(true) - $lastTimeSample) > 1) {
                    $lastTimeSample = microtime(true);
                    echo "\033[70D";
                    echo str_pad($lastCount, 10, ' ', STR_PAD_LEFT)." ";
                    echo " Inserts per second  ";
                    echo str_pad(number_format($requestCount, 0), 20, ' ', STR_PAD_LEFT)." ";
                    echo " Total Inserts ";

                    $lastCount = 0;
                }
            }
        }

    } catch (PDOException $e) {
        echo $e->getMessage();
    }

}
#endregion

#region Setup schema for tracker data test when using 'random'
if ($dbName !== 'sequential') {
    if (!$dbCreate) {
        try {
            $pdo = new PDO($dsn.";dbname=$dbName", $dbUser, $dbPass, [PDO::ATTR_PERSISTENT => true]);
            if ($verbosity > 0) {
                echo "Connected to the $dbName database...\n";
            }
        } catch (PDOException $e) {
            echo $e->getMessage()."\n";
            die();
        }
    } else {

        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_PERSISTENT => true]);
            if ($verbosity > 0) {
                echo "Connected to the database server...\n";
            }
            $pdo->query("CREATE DATABASE `$dbName`;                
                GRANT ALL ON `$dbName`.* TO '$dbUser'@'localhost';
                FLUSH PRIVILEGES;");
            if ($verbosity > 0) {
                echo "Created new database '$dbName'...\n";
            }
            $pdo->query("USE $dbName");
            if ($verbosity > 1) {
                echo "Using database '$dbName'...\n";
            }
            $schemaSql = file_get_contents('./schema.sql');
            if ($dbType !== 'tidb') {
                $schemaSql = str_replace('bigint PRIMARY KEY AUTO_RANDOM(3)', 'int UNSIGNED AUTO_INCREMENT PRIMARY KEY', $schemaSql);
            }

            $pdo->exec($schemaSql);
            if ($verbosity > 1) {
                echo "Created schema.\n";
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        if ($dbCreateOnly) {
            die("Create database only option is set, exiting now\n");
        }

    }
} else {
    $usePrepareCache = false;
    $pdo = new PDO($dsn.";dbname=matomo_test_db_".rand($sequentialMin, $sequentialMax), $dbUser, $dbPass);
}

if (!isset($pdo)) {
    die('DB connection invalid');
}

#endregion

#region Generate and execute database queries

$prepareCache = [];
$requestCount = 0;
$queryGenerator = new TrackerDbQueryGenerator();

if ($verbosity > 0) {
    echo "Generating requests.";
    if ($verbosity > 1) {
        echo "..\n";
    }
}

$lastTimeSample = microtime(true);
$lastCount = 0;
if ($throttle > 0) {
    $throttle = round($throttle / 2);
}
$throttleIntervalCount = 0;
$throttleLastTimeSample = microtime(true);
$seqNo = 0;
$oldSeqNo = 0;
while ($requestCount < $requests || $requests < 0) {

    // Create a new connection if option set, and/or choose random db for sequential mode
    if ($dbName === 'sequential') {
        $oldSeqNo = $seqNo;
        $seqNo = rand($sequentialMin, $sequentialMax);
        $seqDbName = 'matomo_test_db_'.$seqNo;
        if ($verbosity == 3) {
            echo "-------------------\n";
            echo "Chose sequential database ".$seqDbName."\n";
        }
        if ($newConnection) {
            $pdo = new PDO($dsn.";dbname=$seqDbName", $dbUser, $dbPass);
        } else {
            if ($seqNo != $oldSeqNo) {
                $pdo->query("USE $seqDbName");
            }
        }
    } else {
        if ($newConnection) {
            //$pdo->query('KILL CONNECTION_ID()');
            //$pdo = null;
            $pdo = new PDO($dsn.";dbname=$dbName", $dbUser, $dbPass);
        }
    }

    if ($randomDateStart) {
        $timestampUTC = rand($randomDateStart, $randomDateEnd);
    } else {
        $timestampUTC = time();
    }

    $site = 1;
    if ($randomSiteStart != 0 && $randomSiteEnd != 0) {
        $site = rand($randomSiteStart, $randomSiteEnd);
    }

    // Create actions for the visit
    $idactions = [];
    $actionUrl = '';
    for ($i = 0; $i != $actionCount; $i++) {

        // Get random action, 50% chance of being new until pool is full, then always an existing action
        $actionUrl = $queryGenerator->getRandomActionURL();

        // Check if the action exists in the db, create new action if not
        $findActionQuery = $queryGenerator->getCheckActionExistsQuery($actionUrl);
        $actionRows = query($prepareCache, $pdo, $findActionQuery, true, $usePrepareCache);
        if (count($actionRows) === 0) {
            if ($verbosity === 3) {
                echo "New action, doing insert...\n";
            }

            // Insert new action
            $insertActionQuery = $queryGenerator->getInsertActionQuery($actionUrl);
            $visitorRows = query($prepareCache, $pdo, $insertActionQuery, false, $usePrepareCache);
            $idactions[] = $pdo->lastInsertId();
        } else {
            $idactions[] = $actionRows[0]->idaction;
        }

        // Hacky workaround for int(11) idaction foreign keys on TiDB cloud test
        if ($tmpHack && $dbName === 'sequential' && $seqNo > 250) {
            $idactions[] = rand(1, 1000000);
        }

    }

    // Get random visitor id (10% chance of a returning visitor id)
    $idvisitor = $queryGenerator->getVisitor(10);
    if ($verbosity == 3) {
        echo "Got idvisitor '".bin2hex($idvisitor)."' \n";
    }

    // Check if visit exists in db, create new visit if not
    $findVisitorQuery = $queryGenerator->getCheckIfNewVisitorQuery($idvisitor, $site);
    $visitorRows = query($prepareCache, $pdo, $findVisitorQuery, true, $usePrepareCache);

    if (count($visitorRows) == 0) {

        if ($verbosity == 3) {
            echo "New visitor, doing insert...";
        }

        // Insert new visit
        $insertVisitorQuery = $queryGenerator->getInsertVisitorQuery($idvisitor, reset($idactions), $timestampUTC, $site);
        $visitorRows = query($prepareCache, $pdo, $insertVisitorQuery, false, $usePrepareCache);
        $idvisit = $pdo->lastInsertId();
        if ($verbosity == 3) {
            echo $idvisit."\n";
        }
    } else {
        $idvisit = $visitorRows[0]->idvisit;

        // Update random visit time to always be after an existing visit's first action time
        $visitFirstTime = strtotime($visitorRows[0]->visit_first_action_time);
        $timestampUTC = rand($visitFirstTime, $randomDateEnd);

        if ($verbosity == 3) {
            echo "Existing visitor, updating...\n";
        }

        // Update visit
        $updateVisitQuery = $queryGenerator->getUpdateVisitQuery($idvisit, $visitorRows[0]->visit_first_action_time, $timestampUTC, $site);
        query($prepareCache, $pdo, $updateVisitQuery, false, $usePrepareCache);

    }

    if ($verbosity == 3) {
        echo "idvisit is $idvisit\n";
    }

    // Insert the action link(s)
    $idlinkva = null;
    foreach($idactions as $idaction) {
        if ($idvisit && $idaction) {

            if ($verbosity == 3) {
                echo "Inserting action link...\n";
            }
            $insertActionLinkQuery = $queryGenerator->getInsertActionLinkQuery($idvisitor, $idvisit, $idaction, $timestampUTC, $site);
            query($prepareCache, $pdo, $insertActionLinkQuery, false, $usePrepareCache);
            $idlinkva = $pdo->lastInsertId();
        }
    }

    // Insert conversion (conversion will always use the last idlinkva if there are multiple actions
    if ($idlinkva && $conversionPercent > 0 && (rand(0, 100) < $conversionPercent)) {

        $idgoal = array_rand([1,2,3,4,5,6,7,8,9,10]);

        if ($verbosity == 3) {
            echo "Inserting conversion...\n";
        }

        $insertConversionQuery = $queryGenerator->getInsertConversionQuery($idvisitor, $idvisit, end($idactions), $actionUrl, $timestampUTC,
            $idlinkva, $idgoal, $site);
        query($prepareCache, $pdo, $insertConversionQuery, false, $usePrepareCache);

    }

    // Optionally do a random table query
    if ($randomTableQuery) {
        for ($i = 0; $i < $randomTableQuery; $i++) {
            $table = $tables[array_rand($tables)];
            if ($verbosity == 3) {
               echo "Doing random table query on table '$table'...\n";
            }
            query($prepareCache, $pdo, ['sql' => 'SELECT COUNT(*) FROM `'.$table.'`', 'bind' => []],true, $usePrepareCache);
        }
    }

    $requestCount++;
    $lastCount++;
    $throttleIntervalCount++;

    if ($throttle > 0 && $throttleIntervalCount >= $throttle) {
        $newThrottleTimeSample = microtime(true);
        $throttleTime = ($newThrottleTimeSample - $throttleLastTimeSample) * 1000;
        if ($throttleTime < 1000) {
            // Throttle limit has been reached before the second is up, so sleep the rest
            $sleepTime = 1000 - $throttleTime;
            usleep(($sleepTime * 1000));
        }
        $throttleLastTimeSample = $newThrottleTimeSample;
        $throttleIntervalCount = 0;
    }

    if ($verbosity < 3) {
        if ((microtime(true) - $lastTimeSample) > 1) {
            $lastTimeSample = microtime(true);
            echo "\033[70D";
            echo str_pad($lastCount, 10, ' ', STR_PAD_LEFT)." ";
            echo " Requests per second  ";
            echo str_pad(number_format($requestCount, 0), 20, ' ', STR_PAD_LEFT)." ";
            echo " Total requests ";

            $lastCount = 0;
        }
    }

}

echo "\ndone\n";

#endregion

#region Query wrapper to cache prepares
function query(&$prepareCache, $pdo, $q, $result = false, $usePrepareCache = true)
{

    if ($usePrepareCache && isset($prepareCache[$q['sql']])) {
        if (!is_array($q['bind'])) {
            $q['bind'] = array($q['bind']);
        }
        $stmt = $prepareCache[$q['sql']];
    } else {
        $stmt = $pdo->prepare($q['sql']);
        $prepareCache[$q['sql']] = $stmt;
    }

    $stmt->execute($q['bind']);
    if ($result) {
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

}
#endregion
