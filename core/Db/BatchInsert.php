<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Log;
use Piwik\SettingsServer;

class BatchInsert
{
    /**
     * Performs a batch insert into a specific table by iterating through the data
     *
     * NOTE: you should use tableInsertBatch() which will fallback to this function if LOAD DATA INFILE not available
     *
     * @param string $tableName PREFIXED table name! you must call Common::prefixTable() before passing the table name
     * @param array $fields array of unquoted field names
     * @param array $values array of data to be inserted
     * @param bool $ignoreWhenDuplicate Ignore new rows that contain unique key values that duplicate old rows
     */
    public static function tableInsertBatchIterate($tableName, $fields, $values, $ignoreWhenDuplicate = true)
    {
        $fieldList = '(' . join(',', $fields) . ')';
        $ignore    = $ignoreWhenDuplicate ? 'IGNORE' : '';

        foreach ($values as $row) {
            $query = "INSERT $ignore INTO " . $tableName . "
					  $fieldList
					  VALUES (" . Common::getSqlStringFieldsArray($row) . ")";
            Db::query($query, $row);
        }
    }

    /**
     * Performs a batch insert into a specific table using either LOAD DATA INFILE or plain INSERTs,
     * as a fallback. On MySQL, LOAD DATA INFILE is 20x faster than a series of plain INSERTs.
     *
     * @param string $tableName PREFIXED table name! you must call Common::prefixTable() before passing the table name
     * @param array $fields array of unquoted field names
     * @param array $values array of data to be inserted
     * @param bool $throwException Whether to throw an exception that was caught while trying
     *                                LOAD DATA INFILE, or not.
     * @throws Exception
     * @return bool  True if the bulk LOAD was used, false if we fallback to plain INSERTs
     */
    public static function tableInsertBatch($tableName, $fields, $values, $throwException = false)
    {
        $filePath = StaticContainer::get('path.tmp') . '/assets/' . $tableName . '-' . Common::generateUniqId() . '.csv';

        $loadDataInfileEnabled = Config::getInstance()->General['enable_load_data_infile'];

        if ($loadDataInfileEnabled
            && Db::get()->hasBulkLoader()) {
            try {
                $fileSpec = array(
                    'delim'            => "\t",
                    'quote'            => '"', // chr(34)
                    'escape'           => '\\\\', // chr(92)
                    'escapespecial_cb' => function ($str) {
                            return str_replace(array(chr(92), chr(34)), array(chr(92) . chr(92), chr(92) . chr(34)), $str);
                        },
                    'eol'              => "\r\n",
                    'null'             => 'NULL',
                );

                // hack for charset mismatch
                if (!DbHelper::isDatabaseConnectionUTF8() && !isset(Config::getInstance()->database['charset'])) {
                    $fileSpec['charset'] = 'latin1';
                }

                self::createCSVFile($filePath, $fileSpec, $values);

                if (!is_readable($filePath)) {
                    throw new Exception("File $filePath could not be read.");
                }

                $rc = self::createTableFromCSVFile($tableName, $fields, $filePath, $fileSpec);
                if ($rc) {
                    unlink($filePath);
                    return true;
                }
            } catch (Exception $e) {
                if ($throwException) {
                    throw $e;
                }
            }
        }

        // if all else fails, fallback to a series of INSERTs
        if(file_exists($filePath)){
            @unlink($filePath);
        }
        
        self::tableInsertBatchIterate($tableName, $fields, $values);
        return false;
    }

    /**
     * Batch insert into table from CSV (or other delimited) file.
     *
     * @param string $tableName Name of table
     * @param array $fields Field names
     * @param string $filePath Path name of a file.
     * @param array $fileSpec File specifications (delimiter, line terminator, etc)
     *
     * @throws Exception
     * @return bool  True if successful; false otherwise
     */
    public static function createTableFromCSVFile($tableName, $fields, $filePath, $fileSpec)
    {
        // Chroot environment: prefix the path with the absolute chroot path
        $chrootPath = Config::getInstance()->General['absolute_chroot_path'];
        if (!empty($chrootPath)) {
            $filePath = $chrootPath . $filePath;
        }

        // On Windows, MySQL expects forward slashes as directory separators
        if (SettingsServer::isWindows()) {
            $filePath = str_replace('\\', '/', $filePath);
        }

        $query = "
				'$filePath'
			REPLACE
			INTO TABLE
				`" . $tableName . "`";

        if (isset($fileSpec['charset'])) {
            $query .= ' CHARACTER SET ' . $fileSpec['charset'];
        }

        $fieldList = '(' . join(',', $fields) . ')';

        $query .= "
			FIELDS TERMINATED BY
				'" . $fileSpec['delim'] . "'
			ENCLOSED BY
				'" . $fileSpec['quote'] . "'
		";
        if (isset($fileSpec['escape'])) {
            $query .= " ESCAPED BY '" . $fileSpec['escape'] . "'";
        }
        $query .= "
			LINES TERMINATED BY
				'" . $fileSpec['eol'] . "'
			$fieldList
		";

        /*
         * First attempt: assume web server and MySQL server are on the same machine;
         * this requires that the db user have the FILE privilege; however, since this is
         * a global privilege, it may not be granted due to security concerns
         */
        $keywords = array('');

        /*
         * Second attempt: using the LOCAL keyword means the client reads the file and sends it to the server;
         * the LOCAL keyword may trigger a known PHP PDO\MYSQL bug when MySQL not built with --enable-local-infile
         * @see http://bugs.php.net/bug.php?id=54158
         */
        $openBaseDir = ini_get('open_basedir');
        $safeMode    = ini_get('safe_mode');

        if (empty($openBaseDir) && empty($safeMode)) {
            // php 5.x - LOAD DATA LOCAL INFILE is disabled if open_basedir restrictions or safe_mode enabled
            $keywords[] = 'LOCAL ';
        }

        $exceptions = array();
        foreach ($keywords as $keyword) {
            $queryStart = 'LOAD DATA ' . $keyword . 'INFILE ';
            $sql = $queryStart . $query;
            try {
                $result = @Db::exec($sql);
                if (empty($result) || $result < 0) {
                    continue;
                }

                return true;
            } catch (Exception $e) {
                $code = $e->getCode();
                $message = $e->getMessage() . ($code ? "[$code]" : '');
                $exceptions[] = "\n  Try #" . (count($exceptions) + 1) . ': ' . $queryStart . ": " . $message;
            }
        }

        if (count($exceptions)) {
            $message = "LOAD DATA INFILE failed... Error was: " . implode(",", $exceptions);
            Log::info($message);
            throw new Exception($message);
        }

        return false;
    }

    /**
     * Create CSV (or other delimited) files
     *
     * @param string $filePath filename to create
     * @param array $fileSpec File specifications (delimiter, line terminator, etc)
     * @param array $rows Array of array corresponding to rows of values
     * @throws Exception  if unable to create or write to file
     */
    protected static function createCSVFile($filePath, $fileSpec, $rows)
    {
        // Set up CSV delimiters, quotes, etc
        $delim = $fileSpec['delim'];
        $quote = $fileSpec['quote'];
        $eol   = $fileSpec['eol'];
        $null  = $fileSpec['null'];
        $escapespecial_cb = $fileSpec['escapespecial_cb'];

        $fp = @fopen($filePath, 'wb');
        if (!$fp) {
            throw new Exception('Error creating the tmp file ' . $filePath . ', please check that the webserver has write permission to write this file.');
        }

        foreach ($rows as $row) {
            $output = '';
            foreach ($row as $value) {
                if (!isset($value) || is_null($value) || $value === false) {
                    $output .= $null . $delim;
                } else {
                    $output .= $quote . $escapespecial_cb($value) . $quote . $delim;
                }
            }

            // Replace delim with eol
            $output = substr_replace($output, $eol, -1);

            $ret = fwrite($fp, $output);
            if (!$ret) {
                fclose($fp);
                throw new Exception('Error writing to the tmp file ' . $filePath);
            }
        }

        fclose($fp);

        @chmod($filePath, 0777);
    }
}
