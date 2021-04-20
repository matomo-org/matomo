<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserId\Reports;

use Piwik\Plugin\Report;

abstract class Base extends Report
{
    protected function init()
    {
        $this->categoryId = 'General_Visitors';
        $this->onlineGuideUrl = 'https://matomo.org/docs/user-id/';
    }
}
