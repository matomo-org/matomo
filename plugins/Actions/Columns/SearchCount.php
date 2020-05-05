<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Actions\Actions\ActionSiteSearch;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class SearchCount extends ActionDimension
{
    protected $type = self::TYPE_TEXT;
    protected $nameSingular = 'Actions_SiteSearchKeywordCount';
    protected $namePlural = 'Actions_SiteSearchKeywordCounts';
    protected $columnName = 'search_count';
    protected $segmentName = 'siteSearchCount';
    protected $columnType = 'INTEGER(10) UNSIGNED NULL';

    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        if ($action instanceof ActionSiteSearch) {
            return $action->getSearchCount();
        }

        return parent::onNewAction($request, $visitor, $action);
    }
}