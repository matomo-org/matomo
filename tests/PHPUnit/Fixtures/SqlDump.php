<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Access;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\Config\DatabaseConfig;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;
use Exception;
use Piwik\Tests\Framework\TestingEnvironmentVariables;

/**
 * Reusable fixture. Loads a SQL dump into the DB.
 */
class SqlDump extends Fixture
{
    public $date = '2012-09-03';
    public $dateTime = '2012-09-03';
    public $period = 'day';
    public $idSite = 'all';
    public $tablesPrefix = 'piwik_';
    public $dumpUrl = "";

    public function setUp(): void
    {
        // drop all tables
        Db::dropAllTables();

        // download data dump if url supplied
        if (is_file($this->dumpUrl)) {
            $dumpPath = $this->dumpUrl;
        } else {
            $dumpPath = PIWIK_INCLUDE_PATH . '/tmp/logdump.sql.gz';
            $bytesRead = $this->downloadDumpInPath($dumpPath);

            // sanity check
            if ($bytesRead <= 40 * 1024 * 1024) {
                $str = "Could not download sql dump! You can manually download %s into %s";
                throw new Exception(sprintf($str, $this->dumpUrl, $dumpPath));
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
        $host = Config::getInstance()->database['host'];
        Config::getInstance()->database['tables_prefix'] = $this->tablesPrefix;

        $defaultsFile = $this->makeMysqlDefaultsFile($user, $password);

        $cmd = "mysql --defaults-extra-file=\"$defaultsFile\" -h \"$host\" {$this->dbName} < \"" . $deflatedDumpPath . "\" 2>&1";

        if (DatabaseConfig::getConfigValue('schema') === 'Tidb') {
            // For TiDb we need to remove the default charset from the create table statements, otherwise it will use the default charset collation, which differs from database default collation
            $cmd = "sed 's/ DEFAULT CHARSET=utf8mb4//' \"$deflatedDumpPath\" | mysql --defaults-extra-file=\"$defaultsFile\" -h \"$host\" {$this->dbName} 2>&1";
        }

        exec($cmd, $output, $return);
        if ($return !== 0) {
            throw new Exception("Failed to load sql dump: " . implode("\n", $output));
        }

        Db::destroyDatabaseObject(); // recreate db connection so any cached table metadata in the connection is reset

        // make sure archiving will be called
        Rules::setBrowserTriggerArchiving(true);

        // reload access
        Access::getInstance()->reloadAccess();

        $testVars = new TestingEnvironmentVariables();
        $testVars->configOverride = array(
            'database' => array(
                'tables_prefix' => $this->tablesPrefix
            )
        );
        $testVars->save();
    }

    /**
     * @param $dumpPath
     * @return int
     */
    protected function downloadDumpInPath($dumpPath)
    {
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
        return $bytesRead;
    }

    private function makeMysqlDefaultsFile($user, $password)
    {
        $contents = "[client]
user=$user
password=$password\n";

        $path = PIWIK_INCLUDE_PATH . '/mysqldefaults.conf';
        file_put_contents($path, $contents);

        return $path;
    }
}
