<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Common;
use Piwik\Metrics\Formatter;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class ReferrerType extends Base
{
    protected $columnName = 'referer_type';
    protected $columnType = 'TINYINT(1) UNSIGNED NULL';
    protected $type = self::TYPE_ENUM;
    protected $segmentName = 'referrerType';
    protected $nameSingular = 'Referrers_Type';
    protected $namePlural = 'Referrers_ReferrerTypes';
    protected $sqlFilterValue = 'Piwik\Plugins\Referrers\getReferrerTypeFromShortName';
    protected $acceptValues = 'direct, search, website, campaign';
    protected $category = 'Referrers_Referrers';

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\Referrers\getReferrerTypeLabel($value);
    }

    public function getEnumColumnValues()
    {
        return array(
            Common::REFERRER_TYPE_DIRECT_ENTRY   => 'direct',
            Common::REFERRER_TYPE_WEBSITE        => 'website',
            Common::REFERRER_TYPE_SEARCH_ENGINE  => 'search',
            Common::REFERRER_TYPE_SOCIAL_NETWORK => 'social',
            Common::REFERRER_TYPE_CAMPAIGN       => 'campaign',
        );
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $information = $this->getReferrerInformationFromRequest($request, $visitor);

        return $information['referer_type'];
    }

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $information = $this->getReferrerInformationFromRequest($request, $visitor);
        if ($this->isCurrentReferrerDirectEntry($visitor)
            && $information['referer_type'] != Common::REFERRER_TYPE_DIRECT_ENTRY
        ) {
            return $information['referer_type'];
        }

        return false;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return $this->getValueForRecordGoal($request, $visitor);
    }
}
