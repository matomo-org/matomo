<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\Providers;

use Piwik\Singleton;
use Piwik\Tracker\Request;

abstract class AgentAbstract extends Singleton
{
    /**
     * Returns internal provider id
     */
    abstract public function getId(): string;

    /**
     * Returns whether the current tracker request can be associated with the AI agent provider.
     */
    abstract public function isDetectedForTrackerRequest(Request $trackerRequest): bool;
}
