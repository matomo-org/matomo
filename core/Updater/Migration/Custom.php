<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Updater\Migration;

/**
 * Provides plugin migrations.
 *
 * @api
 */
class Custom extends Migration
{
    private $callback;
    private $toString;

    public function __construct($callback, $toString)
    {
        $this->callback = $callback;
        $this->toString = $toString;
    }

    public function exec()
    {
        call_user_func($this->callback);
    }

    public function __toString()
    {
        return $this->toString;
    }

    public function shouldIgnoreError($exception)
    {
        return false;
    }
}
