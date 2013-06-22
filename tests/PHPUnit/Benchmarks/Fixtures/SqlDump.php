<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Reusable fixture. Loads a ~1GB SQL dump into the DB.
 */
class Piwik_Test_Fixture_SqlDump
{
    public static $dumpUrl = "http://piwik-team.s3.amazonaws.com/generated-logs-one-day.sql.gz";

    public $date = '2012-09-03';
    public $period = 'day';
    public $idSite = 'all';
    public $tablesPrefix = 'piwik_';

    public function setUp()
    {
        $dumpPath = PIWIK_INCLUDE_PATH . '/tmp/logdump.sql.gz';
        $deflatedDumpPath = PIWIK_INCLUDE_PATH . '/tmp/logdump.sql';
        $bufferSize = 1024 * 1024;

        // drop all tables
        Piwik::dropTables();

        // download data dump
        $dump = fopen(self::$dumpUrl, 'rb');
        $outfile = fopen($dumpPath, 'wb');
        $bytesRead = 0;
        while (!feof($dump)) {
            fwrite($outfile, fread($dump, $bufferSize), $bufferSize);
            $bytesRead += $bufferSize;
        }
        fclose($dump);
        fclose($outfile);

        if ($bytesRead <= 40 * 1024 * 1024) // sanity check
        {
            throw new Exception("Could not download sql dump!");
        }

        // unzip the dump
        exec("gunzip -c \"" . $dumpPath . "\" > \"$deflatedDumpPath\"", $output, $return);
        if ($return !== 0) {
            throw new Exception("gunzip failed: " . implode("\n", $output));
        }

        // load the data into the correct database
        $user = Piwik_Config::getInstance()->database['username'];
        $password = Piwik_Config::getInstance()->database['password'];
        $dbName = Piwik_Config::getInstance()->database['dbname'];
        Piwik_Config::getInstance()->database['tables_prefix'] = 'piwik_';
        Piwik_Common::$cachedTablePrefix = null;

        exec("mysql -u \"$user\" \"--password=$password\" $dbName < \"" . $deflatedDumpPath . "\" 2>&1", $output, $return);
        if ($return !== 0) {
            throw new Exception("Failed to load sql dump: " . implode("\n", $output));
        }

        // make sure archiving will be called
        Piwik_ArchiveProcessor_Rules::setBrowserTriggerArchiving(true);
    }
}
