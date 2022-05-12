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
    -d          Database name, if 'random' then a randomly named database will automatically be created and used    
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

foreach ($argv as $arg) {

    if ($arg == '--cleanup') {
        $cleanUp = true;
        continue;
    }

    if ($arg == '-c') {
        $dbCreateOnly = true;
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
    foreach ($argv as $arg) {
        if ($arg == 'trackerDbLoadTester.php') {
            continue;
        }
        $kv = explode('=', $arg);
        if (count($kv) == 2 && $kv[0] == '-m') {
            continue;
        }
        $argString .= ' '.$arg;
    }

    $cmd = "/usr/bin/php ".__FILE__."".$argString;
    echo "Spawning ".$multipleProcesses." test processes with command:\n\n";
    echo $cmd."\n\n";

    for ($i = 0; $i < $multipleProcesses; $i++) {
        exec("nohup ".$cmd." > /dev/null 2>&1 & echo $!");
        usleep(500000);
        echo ".";
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

#region Setup schema for tracker data test
if (!$dbCreate) {
    $dsn .= ";dbname=$dbName";
    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_PERSISTENT => true]);
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
while ($requestCount < $requests || $requests < 0) {

    if ($randomDateStart) {
        $timestampUTC = rand($randomDateStart, $randomDateEnd);
    } else {
        $timestampUTC = time();
    }

    $site = 1;
    if ($randomSiteStart != 0 && $randomSiteEnd != 0) {
        $site = rand($randomSiteStart, $randomSiteEnd);
    }

    // Get random action, 50% chance of being new until pool is full, then always an existing action
    $actionUrl = $queryGenerator->getRandomActionURL();

    // Check if the action exists in the db, create new action if not
    $findActionQuery = $queryGenerator->getCheckActionExistsQuery($actionUrl);
    $actionRows = query($prepareCache, $pdo, $findActionQuery);
    if (count($actionRows) === 0) {
        if ($verbosity === 3) {
            echo "New action, doing insert...\n";
        }

        // Insert new action
        $insertActionQuery = $queryGenerator->getInsertActionQuery($actionUrl);
        $visitorRows = query($prepareCache, $pdo, $insertActionQuery);
        $idaction = $pdo->lastInsertId();
    } else {
        $idaction = $actionRows[0]->idaction;
    }

    // Get random visitor id (10% chance of a returning visitor id)
    $idvisitor = $queryGenerator->getVisitor(10);
    if ($verbosity == 3) {
        echo "Got idvisitor '".bin2hex($idvisitor)."' \n";
    }

    // Check if visit exists in db, create new visit if not
    $findVisitorQuery = $queryGenerator->getCheckIfNewVisitorQuery($idvisitor, $site);
    $visitorRows = query($prepareCache, $pdo, $findVisitorQuery);

    if (count($visitorRows) == 0) {

        if ($verbosity == 3) {
            echo "New visitor, doing insert...\n";
        }

        // Insert new visit
        $insertVisitorQuery = $queryGenerator->getInsertVisitorQuery($idvisitor, $idaction, $timestampUTC, $site);
        $visitorRows = query($prepareCache, $pdo, $insertVisitorQuery);
        $idvisit = $pdo->lastInsertId();

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
        query($prepareCache, $pdo, $updateVisitQuery);

    }

    if ($verbosity == 3) {
        echo "idvisit is $idvisit\n";
    }

    // Insert the action link
    $idlinkva = null;
    if ($idvisit && $idaction) {

        if ($verbosity == 3) {
            echo "Inserting action link...\n";
        }
        $insertActionLinkQuery = $queryGenerator->getInsertActionLinkQuery($idvisitor, $idvisit, $idaction, $timestampUTC, $site);
        query($prepareCache, $pdo, $insertActionLinkQuery);
        $idlinkva = $pdo->lastInsertId();
    }

    // Insert conversion
    if ($idlinkva && $conversionPercent > 0 && (rand(0, 100) < $conversionPercent)) {

        $idgoal = array_rand([1,2,3,4,5,6,7,8,9,10]);

        if ($verbosity == 3) {
            echo "Inserting conversion...\n";
        }

        $insertConversionQuery = $queryGenerator->getInsertConversionQuery($idvisitor, $idvisit, $idaction, $actionUrl, $timestampUTC,
            $idlinkva, $idgoal, $site);
        query($prepareCache, $pdo, $insertConversionQuery);

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

    if ((microtime(true) - $lastTimeSample) > 1) {
        $lastTimeSample = microtime(true);
        echo "\033[70D";
        echo str_pad($lastCount, 10, ' ', STR_PAD_LEFT) . " ";
        echo " Requests per second  ";
        echo str_pad(number_format($requestCount,0), 20, ' ', STR_PAD_LEFT) . " ";
        echo " Total requests ";

        $lastCount = 0;
    }

}

echo "\ndone\n";

#endregion

#region Query wrapper to cache prepares
function query(&$prepareCache, $pdo, $q)
{
    if (isset($prepareCache[$q['sql']])) {
        if (!is_array($q['bind'])) {
            $q['bind'] = array($q['bind']);
        }
        $stmt = $prepareCache[$q['sql']];
    } else {
        $stmt = $pdo->prepare($q['sql']);
        $prepareCache[$q['sql']] = $stmt;
    }

    $stmt->execute($q['bind']);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
#endregion
