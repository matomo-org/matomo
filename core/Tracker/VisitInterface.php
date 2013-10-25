<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Tracker;

/**
 * @package Piwik
 * @subpackage Tracker
 * @api
 */
interface VisitInterface
{
    /**
     * @param Request $request
     * @return void
     */
    public function setRequest(Request $request);

    /**
     * @return void
     */
    public function handle();
}