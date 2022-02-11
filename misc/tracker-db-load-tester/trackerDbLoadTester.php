<?php

require_once './trackerDbQueryGenerator.php';

/**
 * Tracker db load tester
 *
 * Utility to simulate the database load of tracking request queries
 *
 */

$usage = <<<USAGE
Usage: php trackerLoadTester.php -d=[DB NAME] -h=[DB HOST] -u=[DB USER] -p=[DB PASSWORD] {-r=[REQUEST LIMIT {-P=[DB PORT]} {-v=[VERBOSITY]}
    Example: php trackerLoadTester.php -d=mydb -h=127.0.0.1 -u=root -p=123 -P=3306
    -d    Database name, if 'random' then a randomly named database will automatically be created and used    
    -t    Database type, 'mysql' or 'tidb', used to adjust schema created with -d=random, defaults to 'mysql'
    -h    Database hostname, defaults to 'localhost'
    -u    Database username, defaults to 'root''
    -p    Database password, defaults to none
    -P    Database port, defaults to 3306
    -r    Tracking requests limit, will insert this many tracking requests then exit, runs indefinitely if omitted
    -v    Verbosity of output [0 = quiet, 3 = show everything]

USAGE;

#region Get DB connection parameters from command line
$dbName = null;
$dbCreate = false;
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbPort = 3306;
$dbType = 'mysql';
$verbosity = 0;
$requests = -1;
$cleanUp = false;

foreach ($argv as $arg) {

    if ($arg == '--cleanup') {
        $cleanUp = true;
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
    }
}

if ($cleanUp) {
    try {
        $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;charset=UTF8", $dbUser, $dbPass);
        if ($verbosity > 0) {
            echo "Cleaning up test databases...";
        }
        $dbs = query($prepareCache, $pdo, ['sql' => "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME LIKE 'tracker_db_test_%'", 'bind' => []]);
        $dropped = 0;
        foreach ($dbs as $db) {
            echo "Dropping database ".$db->SCHEMA_NAME."\n";
            $pdo->exec("DROP DATABASE ".$db->SCHEMA_NAME.";");
        }
        die("Dropped ".$dropped." test databases");

    } catch (PDOException $e) {
        echo $e->getMessage()."\n";
        die();
    }
}

if ($dbName === null || $dbHost === null || $dbUser === null || $dbPass === null || $dbPort === null) {
    die($usage);
}

if ($verbosity > 1) {
    echo "Host: $dbHost Type: $dbType User: '$dbUser' Password: '$dbPass' Port: $dbPort Request Limit: ".($requests === -1 ? 'unlimited' : $requests)."\n";
}
#endregion

#region Connect to db

$dsn = "mysql:host=$dbHost;port=$dbPort;charset=UTF8";

if (!$dbCreate) {
    $dsn .= ";dbname=$dbName";
    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass);
        if ($verbosity > 0) {
            echo "Connected to the $dbName database...\n";
        }
    } catch (PDOException $e) {
        echo $e->getMessage()."\n";
        die();
    }
} else {

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass);
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

while ($requestCount < $requests || $requests < 0) {

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
    $findVisitorQuery = $queryGenerator->getCheckIfNewVisitorQuery($idvisitor);
    $visitorRows = query($prepareCache, $pdo, $findVisitorQuery);

    if (count($visitorRows) == 0) {

        if ($verbosity == 3) {
            echo "New visitor, doing insert...\n";
        }

        // Insert new visit
        $insertVisitorQuery = $queryGenerator->getInsertVisitorQuery($idvisitor, $idaction);
        $visitorRows = query($prepareCache, $pdo, $insertVisitorQuery);
        $idvisit = $pdo->lastInsertId();

    } else {
        $idvisit = $visitorRows[0]->idvisit;

         if ($verbosity == 3) {
            echo "Existing visitor, updating...\n";
        }

        // Update visit
        $updateVisitQuery = $queryGenerator->getUpdateVisitQuery($idvisit, $visitorRows[0]->visit_first_action_time);
        query($prepareCache, $pdo, $updateVisitQuery);

    }

    if ($verbosity == 3) {
        echo "idvisit is $idvisit\n";
    }

    // Insert the action link
    if ($idvisit && $idaction) {

        if ($verbosity == 3) {
            echo "Inserting action link...\n";
        }
        $insertActionLinkQuery = $queryGenerator->getInsertActionLinkQuery($idvisitor, $idvisit, $idaction);
        query($prepareCache, $pdo, $insertActionLinkQuery);
    }

    $requestCount++;
    if ($requestCount % 1000 === 0 && $verbosity === 1) {
        echo ".";
    }
    if ($requestCount % 10000 === 0 && $verbosity > 1) {
        echo "..".$requestCount;
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
