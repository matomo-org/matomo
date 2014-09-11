<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

/**
 * Interface implemented by classes that track visit information for the Tracker.
 *
 */
interface VisitInterface
{
    /**
     * Stores the object describing the current tracking request.
     *
     * @param Request $request
     * @return void
     */
    public function setRequest(Request $request);

    /**
     * Tracks a visit.
     *
     * @return void
     */
    public function handle();
}
