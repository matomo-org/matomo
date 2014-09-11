<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

interface VisitorInterface
{
    /**
     * @return array
     */
    public function getAllVisitorDetails();

    /**
     * @return string|bool
     */
    public function getVisitorId();
}
