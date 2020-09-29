<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomDimensions;

use Piwik\Columns\Dimension;
use Piwik\Plugins\CustomDimensions\Dao\AutoSuggest;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Plugins\CustomDimensions\Tracker\CustomDimensionsRequestProcessor;

class CustomDimension extends Dimension
{
    protected $type = self::TYPE_TEXT;

    private $id;

    public function getId()
    {
        return $this->id;
    }

    public function initCustomDimension($dimension)
    {
        $this->id = 'CustomDimension.CustomDimension' . $dimension['idcustomdimension'];
        $this->nameSingular = $dimension['name'];
        $this->columnName = LogTable::buildCustomDimensionColumnName($dimension);
        $this->segmentName = CustomDimensionsRequestProcessor::buildCustomDimensionTrackingApiName($dimension);

        if ($dimension['scope'] === CustomDimensions::SCOPE_ACTION) {
            $this->category = 'General_Actions';
            $this->dbTableName = 'log_link_visit_action';
            $this->suggestedValuesCallback = function ($idSite, $maxValuesToReturn) use ($dimension) {
                $autoSuggest = new AutoSuggest();
                return $autoSuggest->getMostUsedActionDimensionValues($dimension, $idSite, $maxValuesToReturn);
            };
        } elseif ($dimension['scope'] === CustomDimensions::SCOPE_VISIT) {
            $this->category = 'General_Visitors';
            $this->dbTableName = 'log_visit';
        }
    }

}