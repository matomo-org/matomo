<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Scheduler;

use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;

class ScheduledTaskLock extends Lock
{
    public function __construct(LockBackend $backend)
    {
        parent::__construct($backend, 'ScheduledTask', 3600);
    }
}
