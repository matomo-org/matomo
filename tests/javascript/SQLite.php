<?php
/*!
 * Piwik - free/libre analytics platform
 *
 * SQLite shim
 *
 * @link http://piwik.org
 * @license http://www.opensource.org/licenses/bsd-license.php Simplified BSD
 */
if (class_exists('SQLite3')) {
    class SQLite extends SQLite3
    {
        public function __construct($filename)
        {
            parent::__construct($filename);

            // for backward compatibility
            if (version_compare(PHP_VERSION, '5.3.3') > 0) {
                $this->busyTimeout(60000);
            }
        }

        public function query_array($sql)
        {
            $result = parent::query($sql);

            $rows = array();
            while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $res;
            }
            return $rows;
        }
    }
} elseif (extension_loaded('sqlite')) {
    class SQLite
    {
        private $handle;

        public function __construct($filename)
        {
            $this->handle = sqlite_open($filename);
        }

        public function query_array($sql)
        {
            return sqlite_array_query($this->handle, $sql);
        }

        public function exec($sql)
        {
            return sqlite_exec($this->handle, $sql);
        }

        public function changes()
        {
            return sqlite_changes($this->handle);
        }

        public function close()
        {
            sqlite_close($this->handle);
            unset($this->handle);
        }
    }
}
