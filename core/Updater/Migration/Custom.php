<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration;

use Piwik\Updater\Migration;

/**
 * Create a custom migration that can execute any callback.
 *
 * @api
 */
class Custom extends Migration
{
    private $callback;
    private $toString;
    private $args;

    public function __construct($callback, $toString, array $args = [])
    {
        $this->callback = $callback;
        $this->toString = $toString;
        $this->args = $args;
    }

    public function exec()
    {
        call_user_func_array($this->callback, $this->args);
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
