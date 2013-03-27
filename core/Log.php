<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 *
 * @package Piwik
 * @subpackage Piwik_Log
 * @see Zend_Log, libs/Zend/Log.php
 * @link http://framework.zend.com/manual/en/zend.log.html
 */
abstract class Piwik_Log extends Zend_Log
{
    protected $logToDatabaseTableName = null;
    protected $logToDatabaseColumnMapping = null;
    protected $logToFileFilename = null;
    protected $fileFormatter = null;
    protected $screenFormatter = null;
    protected $currentRequestKey;

    function __construct($logToFileFilename,
                         $fileFormatter,
                         $screenFormatter,
                         $logToDatabaseTableName,
                         $logToDatabaseColumnMapping)
    {
        parent::__construct();

        $this->currentRequestKey = substr(Piwik_Common::generateUniqId(), 0, 8);

        $log_dir = Piwik_Config::getInstance()->log['logger_file_path'];
        if ($log_dir[0] != '/' && $log_dir[0] != DIRECTORY_SEPARATOR) {
            $log_dir = PIWIK_USER_PATH . '/' . $log_dir;
        }
        $this->logToFileFilename = $log_dir . '/' . $logToFileFilename;

        $this->fileFormatter = $fileFormatter;
        $this->screenFormatter = $screenFormatter;
        $this->logToDatabaseTableName = Piwik_Common::prefixTable($logToDatabaseTableName);
        $this->logToDatabaseColumnMapping = $logToDatabaseColumnMapping;
    }

    function addWriteToFile()
    {
        Piwik_Common::mkdir(dirname($this->logToFileFilename));
        $writerFile = new Zend_Log_Writer_Stream($this->logToFileFilename);
        $writerFile->setFormatter($this->fileFormatter);
        $this->addWriter($writerFile);
    }

    function addWriteToNull()
    {
        $this->addWriter(new Zend_Log_Writer_Null);
    }

    function addWriteToDatabase()
    {
        $writerDb = new Zend_Log_Writer_Db(
            Zend_Registry::get('db'),
            $this->logToDatabaseTableName,
            $this->logToDatabaseColumnMapping);

        $this->addWriter($writerDb);
    }

    function addWriteToScreen()
    {
        $writerScreen = new Zend_Log_Writer_Stream('php://output');
        $writerScreen->setFormatter($this->screenFormatter);
        $this->addWriter($writerScreen);
    }

    public function getWritersCount()
    {
        return count($this->_writers);
    }

    /**
     * Log an event
     * @param string $event
     * @param int $priority
     * @param null $extras
     * @throws Zend_Log_Exception
     * @return void
     */
    public function log($event, $priority, $extras = null)
    {
        // sanity checks
        if (empty($this->_writers)) {
            throw new Zend_Log_Exception('No writers were added');
        }

        $event['timestamp'] = date('Y-m-d H:i:s');
        $event['requestKey'] = $this->currentRequestKey;
        // pack into event required by filters and writers
        $event = array_merge($event, $this->_extras);

        // one message must stay on one line
        if (isset($event['message'])) {
            $event['message'] = str_replace(array(PHP_EOL, "\n"), " ", $event['message']);
        }

        // Truncate the backtrace which can be too long to display in the browser
        if (!empty($event['backtrace'])) {
            $maxSizeOutputBytes = 1024 * 1024; // no more than 1M output please
            $truncateBacktraceLineAfter = 1000;
            $maxLines = ceil($maxSizeOutputBytes / $truncateBacktraceLineAfter);
            $bt = explode("\n", $event['backtrace']);
            foreach ($bt as $count => &$line) {
                if (strlen($line) > $truncateBacktraceLineAfter) {
                    $line = substr($line, 0, $truncateBacktraceLineAfter) . '...';
                }
                if ($count > $maxLines) {
                    $line .= "\nTruncated error message.";
                    break;
                }
            }
            $event['backtrace'] = implode("\n", $bt);
        }
        // abort if rejected by the global filters
        foreach ($this->_filters as $filter) {
            if (!$filter->accept($event)) {
                return;
            }
        }

        // send to each writer
        foreach ($this->_writers as $writer) {
            $writer->write($event);
        }
    }

}

/**
 *
 * @package Piwik
 * @subpackage Piwik_Log
 */
class Piwik_Log_Formatter_FileFormatter implements Zend_Log_Formatter_Interface
{
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        foreach ($event as &$value) {
            $value = str_replace("\n", '\n', $value);
            $value = '"' . $value . '"';
        }
        $ts = $event['timestamp'];
        unset($event['timestamp']);
        return $ts . ' ' . implode(" ", $event) . "\n";
    }
}

/**
 *
 * @package Piwik
 * @subpackage Piwik_Log
 */
class Piwik_Log_Formatter_ScreenFormatter implements Zend_Log_Formatter_Interface
{
    function formatEvent($event)
    {
        // no injection in error messages, backtrace when displayed on screen
        return array_map(array('Piwik_Common', 'sanitizeInputValue'), $event);
    }

    function format($string)
    {
        return self::getFormattedString($string);
    }

    static public function getFormattedString($string)
    {
        if (!Piwik_Common::isPhpCliMode()) {
            @header('Content-Type: text/html; charset=utf-8');
        }
        return $string;
    }
}
