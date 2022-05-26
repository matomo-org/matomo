<?php

// Create x sequentially named schemas using a schema definition sql file

$usage = <<<USAGE
Usage: php createManyDbsTidb.php -f=schema.sql -h=[DB HOST] -u=[DB USER] -p=[DB PASSWORD] {-P=[DB PORT]}
    Example: php createManyDbsTidb.php -h=127.0.0.1 -u=root -p=123 -P=4000
    -h          Database hostname
    -u          Database username, defaults to 'root'
    -p          Database password, defaults to none
    -P          Database port, defaults to 4000
    -f          Schema file   
    -c          Number of schemas to create
    -r          Resume from sequential schema -r=200
    --cleanup   Drop all test schemata

USAGE;

#region Get parameters from command line
$dbHost = null;
$dbUser = 'root';
$dbPass = '';
$dbPort = 4000;
$dbCount = 5000;
$resume = 0;
$schemaFile = null;
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
            $resume = $kv[1];
            break;
        case '-c':
            $dbCount = $kv[1];
            break;
        case '-f':
            $schemaFile = $kv[1];
            break;
    }
}

if ($dbHost === null || $dbUser === null || $dbPass === null || $dbPort === null || $schemaFile === null) {
    die($usage);
}

$dsn = "mysql:host=$dbHost;port=$dbPort;charset=UTF8";
$pdo = new PDO($dsn, $dbUser, $dbPass);
#endregion

#region Do clean-up action and die
if ($cleanUp) {
    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass);
        echo "Cleaning up test databases...\n";

        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME LIKE 'matomo_test_db_%'");
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

echo "Creating databases";
for ($dbc = 1; $dbc < ($dbCount + 1 + $resume); $dbc++) {

    $dbName = 'matomo_test_db_'.$dbc;

    $pdo->query("CREATE DATABASE `$dbName`;");
    $pdo->query("USE $dbName");

    try {

        $schemaSql = file_get_contents($schemaFile);
        $pdo->exec($schemaSql);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    echo ".";
    if ($dbc % 100 === 0) {
        echo $dbc."\n";
    }

}
echo " done!\n";

#endregion
