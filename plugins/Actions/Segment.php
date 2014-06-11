<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\API\Request;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Db;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Site;

/**
 * Actions plugin
 *
 * Reports about the page views, the outlinks and downloads.
 *
 */
class Segment extends \Piwik\Plugin\Segment
{
    protected  function init()
    {
        parent::init();

        $this->setCategory('General_Actions');
        $this->setSqlFilter('\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment');
    }
}

