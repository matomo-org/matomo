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
     * TODO
     */
    private $name;

    /**
     * TODO
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * TODO
     */
    public function increment()
    {
        $this->advance(1);
    }

    /**
     * TODO
     */
    public function decrement()
    {
        $this->advance(-1);
    }

    /**
     * TODO
     */
    public function advance($n)
    {
        $optionName = $this->getOptionName();
        Db::query("UPDATE " . Common::prefixTable('option') . "
                      SET option_value = option_value + ?
                    WHERE option_name = ?", array($n, $optionName));
    }

    /**
     * TODO
     */
    private function getOptionName()
    {
        return self::OPTION_NAME_PREFIX . $this->name;
    }

    /**
     * TODO
     */
    public static function deleteLike($like)
    {
        Option::deleteLike(self::OPTION_NAME_PREFIX . $like);
    }
}