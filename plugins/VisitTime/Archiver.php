<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\VisitTime;

class Archiver extends \Piwik\Plugin\Archiver
{
    const SERVER_TIME_RECORD_NAME = 'VisitTime_serverTime';
    const LOCAL_TIME_RECORD_NAME = 'VisitTime_localTime';
}
