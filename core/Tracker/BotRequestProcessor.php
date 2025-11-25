<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tracker;

abstract class BotRequestProcessor
{
    /**
     * This is the first method called when processing a bot request.
     *
     * Derived classes can use this method to manipulate a bot request before the request
     * is handled. Plugins could change the URL, add custom variables, etc.
     *
     * @param Request $request
     */
    public function manipulateRequest(Request $request): void
    {
        // empty
    }

    /**
     * This method is called last.
     *
     * Derived classes should use this method to insert log data.
     *
     * @param Request $request
     * @return bool return true if the processor handled the request, this will automatically trigger archive invalidation
     */
    public function handleRequest(Request $request): bool
    {
        return false;
    }
}
