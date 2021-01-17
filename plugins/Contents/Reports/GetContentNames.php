<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents\Reports;

use Piwik\Piwik;
use Piwik\Plugins\Contents\Columns\ContentName;
use Piwik\Plugins\Contents\Columns\Metrics\InteractionRate;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetContentNames extends Base
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('Contents_ContentName');
        $this->documentation = Piwik::translate('Contents_ContentNameReportDocumentation');
        $this->dimension     = new ContentName();
        $this->order         = 35;
        $this->actionToLoadSubTables = 'getContentNames';

        $this->metrics = array('nb_impressions', 'nb_interactions');
        $this->processedMetrics = array(new InteractionRate());
    }
}
