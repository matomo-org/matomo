<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Piwik;
use Piwik\Plugin\ActionDimension;
use Piwik\Plugin\VisitDimension;
use Piwik\Tracker\Request;
use Piwik\Tracker\TableLogAction;

class DownloadUrl extends ActionDimension
{
    public function getName()
    {
        return Piwik::translate('Actions_ColumnDownloadURL');
    }
/*
    public function shouldHandleAction(Request $request)
    {
        $download = $request->getParam('download');

        return !empty($download);
    }

    public function getValue(Request $request)
    {
        $url = $request->getParam('download');

        $ids = TableLogAction::loadIdsAction(array('idaction_url' => array($url, $this->getActionId())));

        if (empty($ids['idaction_url'])) {
            return false;
        }

        return (int) $ids['idaction_url'];
    }

    public function getActionId()
    {
        return 3;
    }*/
}
