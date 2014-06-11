<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary\Reports;

use Piwik\Piwik;

class Get extends \Piwik\Plugin\Report
{
    protected function init()
    {
        parent::init();
        $this->category      = 'VisitsSummary_VisitsSummary';
        $this->name          = Piwik::translate('VisitsSummary_VisitsSummary');
        $this->documentation = ''; // TODO
        $this->metrics       = array('0', '1', '2', '3', '4', 'avg_time_on_site', 'max_actions');
        $this->order = 1;
    }
}
