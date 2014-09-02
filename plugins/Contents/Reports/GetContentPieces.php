<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugins\Contents\Columns\ContentPiece;
use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetContentPieces extends Base
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('Contents_ContentPiece');
        $this->dimension     = null;
        // TODO $this->documentation = Piwik::translate('ContentsDocumentation');
        $this->dimension     = new ContentPiece();
        $this->order         = 36;
        $this->actionToLoadSubTables = 'getContentPieces';

        $this->widgetTitle = 'Contents_ContentPiece';

        $this->metrics = array('nb_impressions', 'nb_interactions');
        $this->processedMetrics = array('interaction_rate');
    }
}
