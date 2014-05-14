<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\Db;
use Piwik\Piwik;

/**
 * Reusable fixture. Loads a SQL dump into the DB.
 */
class Piwik_Test_Fixture_SqlDump extends Fixture
{
    public $date = '2012-09-03';
    public $dateTime = '2012-09-03';
    public $period = 'day';
    public $idSite = 'all';
    public $tablesPrefix = 'piwik_';
    public $dumpUrl = "http://piwik-team.s3.amazonaws.com/generated-logs-one-day.sql.gz";

    public function setUp()
    {
        // drop all tables
        Db::dropAllTables();

        // download data dump if url supplied
        if (is_file($this->dumpUrl)) {
            $dumpPath = $this->dumpUrl;
        } else {
            $dumpPath = PIWIK_INCLUDE_PATH . '/tmp/logdump.sql.gz';
            $bufferSize = 1024 * 1024;

            $dump = fopen($this->dumpUrl, 'rb');
            $outfile = fopen($dumpPath, 'wb');
            $bytesRead = 0;
            while (!feof($dump)) {
                fwrite($outfile, fread($dump, $bufferSize), $bufferSize);
                $bytesRead += $bufferSize;
            }
            fclose($dump);
            fclose($outfile);

            if ($bytesRead <= 40 * 1024 * 1024) { // sanity check
                throw new Exception("Could not download sql dump!");
            }
        }

        // unzip the dump
        if (substr($dumpPath, -3) === ".gz") {
            $deflatedDumpPath = PIWIK_INCLUDE_PATH . '/tmp/logdump.sql'; // TODO: should depend on name of URL
            exec("gunzip -c \"" . $dumpPath . "\" > \"$deflatedDumpPath\"", $output, $return);
            if ($return !== 0) {
                throw new Exception("gunzip failed: " . implode("\n", $output));
            }
        } else {
            $deflatedDumpPath = $dumpPath;
        }

        // load the data into the correct database
        $user = Config::getInstance()->database['username'];
        $password = Config::getInstance()->database['password'];
        Config::getInstance()->database['tables_prefix'] = $this->tablesPrefix;

        $cmd = "mysql -u \"$user\" \"--password=$password\" {$this->dbName} < \"" . $deflatedDumpPath . "\" 2>&1";
        exec($cmd, $output, $return);
        if ($return !== 0) {
            throw new Exception("Failed to load sql dump: " . implode("\n", $output));
        }

        // make sure archiving will be called
        Rules::setBrowserTriggerArchiving(true);

        // reload access
        Piwik::setUserHasSuperUserAccess();

        $this->getTestEnvironment()->configOverride = array(
            'database' => array(
                'tables_prefix' => $this->tablesPrefix
            )
        );
        $this->getTestEnvironment()->save();
    }

    public function tearDown()
    {
        // empty
    }
}