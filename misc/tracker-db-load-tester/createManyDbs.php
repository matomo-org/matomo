<?php

// Create x schemas with y simple tables in each, then issue select queries against each table in turn (keep alive)
// This is to test the ability of the database server to keep a large number of active tables in memory

$usage = <<<USAGE
Usage: php createManyDbs.php -h=[DB HOST] -u=[DB USER] -p=[DB PASSWORD] {-r=[REQUEST LIMIT {-P=[DB PORT]} {-v=[VERBOSITY]}
    Example: php trackerDbLoadTester.php -d=mydb -h=127.0.0.1 -u=root -p=123 -P=3306
    -h          Database hostname
    -u          Database username, defaults to 'root'
    -p          Database password, defaults to none
    -P          Database port, defaults to 3306   
    -c          Number of databases to create
    -t          Number of tables to create for each database
    --cleanup   Delete all randomly named test databases
    --no-create Don't create any database or tables, just do the keep alive

USAGE;

#region Get parameters from command line
$dbHost = null;
$dbUser = 'root';
$dbPass = '';
$dbPort = 3306;
$dbCount = 5000;
$tablesPerDbCount = 100;
$cleanUp = false;
$noCreate = false;

foreach ($argv as $arg) {

    if ($arg == '--cleanup') {
        $cleanUp = true;
        continue;
    }

    if ($arg == '--no-create') {
        $noCreate = true;
        continue;
    }

    $kv = explode('=', $arg);
    if (count($kv) != 2) {
        continue;
    }
    switch ($kv[0]) {
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
        case '-c':
            $dbCount = $kv[1];
            break;
        case '-t':
            $tablesPerDbCount = $kv[1];
            break;
    }
}

if ($dbHost === null || $dbUser === null || $dbPass === null || $dbPort === null) {
    die($usage);
}

$dsn = "mysql:host=$dbHost;port=$dbPort;charset=UTF8";
$pdo = new PDO($dsn, $dbUser, $dbPass);
#endregion

#region Do clean-up action and die
if ($cleanUp) {
    try {
        echo "Cleaning up test databases...\n";

        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME LIKE 'create_db_test_%'");
        $stmt->execute([]);
        $dbs = $stmt->fetchAll(PDO::FETCH_OBJ);

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

#region Create schemas
if (!$noCreate) {


    echo "Creating databases";
    for ($dbc = 1; $dbc < $dbCount + 1; $dbc++) {

        $dbName = 'create_db_test_'.$dbc;

        $pdo->query("CREATE DATABASE `$dbName`;");
        $pdo->query("USE $dbName");
        echo ".";

        for ($tc = 1; $tc < $tablesPerDbCount + 1; $tc++) {

            $pdo->query("
        CREATE TABLE table_$tc (
            name VARCHAR(255),
            val VARCHAR(255)
        );");

        }
    }
    echo " done!\n";
}
#endgregion

#region Keepalive

$keepAlives = 1;
while (true) {
    echo "\nKeep Alive run ".$keepAlives."\n";

    for ($dbc = 1; $dbc < $dbCount + 1; $dbc++) {

        $pdo->query("USE create_db_test_$dbc;");
        echo ".";

        for ($tc = 1; $tc < $tablesPerDbCount+1; $tc++) {
            $pdo->query("SELECT COUNT(*) FROM table_$tc;");
        }

    }
    $keepAlives++;
}
#endregion
