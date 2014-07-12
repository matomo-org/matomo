<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Reports;

use Piwik\Piwik;

class Get extends Base
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('General_Actions') . ' - ' . Piwik::translate('General_MainMetrics');
        $this->documentation = ''; // TODO
        $this->order = 1;
        $this->metrics  = array(
            'nb_pageviews',
            'nb_uniq_pageviews',
            'nb_downloads',
            'nb_uniq_downloads',
            'nb_outlinks',
            'nb_uniq_outlinks',
            'nb_searches',
            'nb_keywords',
            'avg_time_generation'
        );
    }
}
