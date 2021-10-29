<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Mail;

/**
 * This class exists so that scheduled report emails can
 * be identified by plugins that listen to Mail events.
 */
class ScheduledReportEmail extends Mail
{

}