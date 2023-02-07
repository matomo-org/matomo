<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\Dimension;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CustomDimensions\Tracker\CustomDimensionsRequestProcessor;

/**
 * We do not put this one in columns directory of the plugin since we do not want to have it automatically detected.
 * We create instances of it dynamically when needed instead.
 */
class CustomVisitDimension extends VisitDimension
{
    protected $actualName;
    protected $idDimension;

    public function __construct($column, $name, $idDimension)
    {
        $this->columnName = $column;
        $this->actualName = $name;
        $this->nameSingular = $name;
        $this->idDimension = $idDimension;
        $this->segmentName = CustomDimensionsRequestProcessor::buildCustomDimensionTrackingApiName($idDimension);
    }

    /**
     * The name of the dimension which will be visible for instance in the UI of a related report and in the mobile app.
     * @return string
     */
    public function getName()
    {
        return $this->actualName;
    }

    public function getId()
    {
        return 'CustomDimension.CustomDimension' . $this->idDimension;
    }
}