<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;
use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Updater\Migration\Db as DbMigration;

/**
 * Executes a given SQL query.
 *
 * @see Factory::sql()
 * @ignore
 */
class Sql extends DbMigration
{

    /**
     * @var string
     */
    protected $sql;

    /**
     * @var false|int|array
     */
    private $errorCodesToIgnore;

    /**
     * Sql constructor.
     *
     * @param string $sql
     * @param int|int[] $errorCodesToIgnore  If no error should be ignored use an empty array.
     */
    public function __construct($sql, $errorCodesToIgnore)
    {
        if (!is_array($errorCodesToIgnore)) {
            $errorCodesToIgnore = array($errorCodesToIgnore);
        }

        $this->sql = $sql;
        $globalErrorCodesToIgnore = Config::getInstance()->database['ignore_error_codes'] ?? [];
        $this->errorCodesToIgnore = array_merge($errorCodesToIgnore, (is_array($globalErrorCodesToIgnore) ? $globalErrorCodesToIgnore : []));
    }

    public function shouldIgnoreError($exception)
    {
        if (empty($this->errorCodesToIgnore)) {
            return false;
        }

        foreach ($this->errorCodesToIgnore as $code) {
            if (Db::get()->isErrNo($exception, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @internal
     * @param int $errorCode
     * @return $this
     */
    public function addErrorCodeToIgnore($errorCode)
    {
        $this->errorCodesToIgnore[] = $errorCode;

        return $this;
    }

    /**
     * @internal
     */
    public function getErrorCodesToIgnore()
    {
        return $this->errorCodesToIgnore;
    }

    public function __toString()
    {
        $sql = $this->sql;

        if (!empty($sql) && !Common::stringEndsWith($sql, ';')) {
            $sql .= ';';
        }

        return $sql;
    }

    public function exec()
    {
        if (!empty($this->sql)) {
            Db::exec($this->sql);
        }
    }
}
