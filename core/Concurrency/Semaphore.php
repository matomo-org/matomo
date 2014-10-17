<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Concurrency;

use Piwik\Common;
use Piwik\Option;
use Piwik\Db;

/**
 * MySQL based semaphore implementation.
 */
class Semaphore
{
    const OPTION_NAME_PREFIX = 'Semaphore.';

    /**
     * The name of this semaphore.
     *
     * @var string
     */
    private $name;

    /**
     * Constructor.
     *
     * @param string $name The name of the semaphore.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Atomically adds one to the semaphore value in the DB.
     */
    public function increment()
    {
        $this->advance(1);
    }

    /**
     * Atomically subtracts one to the semaphore value in the DB.
     */
    public function decrement()
    {
        $this->advance(-1);
    }

    /**
     * Atomically adds a number to the semahpore value in the DB.
     *
     * @param int $n The value to add. Can be negative.
     */
    public function advance($n)
    {
        $optionName = $this->getOptionName();
        Db::query("UPDATE `" . Common::prefixTable('option') . "`
                      SET option_value = option_value + ?
                    WHERE option_name = ?", array($n, $optionName));
    }

    /**
     * Returns the value in the DB of the semaphore. It is possible for the value to change
     * immediately after this method returns.
     *
     * @return int
     */
    public function get()
    {
        $optionName = $this->getOptionName();
        Option::clearCachedOption($optionName);
        return Option::get($optionName);
    }

    private function getOptionName()
    {
        return self::OPTION_NAME_PREFIX . $this->name;
    }

    /**
     * Deletes all semaphores with a name like `$like`.
     *
     * @param string $like A parameter to be supplied to the SQL `LIKE` operator.
     */
    public static function deleteLike($like)
    {
        Option::deleteLike(self::OPTION_NAME_PREFIX . $like);
    }
}