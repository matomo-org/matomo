<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\Settings;

use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Interfaces\MeasurableSettingInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\SystemSettingInterface;
use Piwik\Settings\Interfaces\Traits\Getters\MeasurableGetterTrait;
use Piwik\Settings\Interfaces\Traits\Getters\SystemGetterTrait;

/**
 * Whether the aggregated real-time reports may be shown while the detailed visits log is disabled.
 *
 * This is intentionally a plain system + measurable setting. The CNIL compliance wiring that forces
 * and locks it is added separately, so this class does not implement PolicyComparisonInterface.
 *
 * @implements MeasurableSettingInterface<bool>
 * @implements SettingValueInterface<bool>
 * @implements SystemSettingInterface<bool>
 */
class AggregatedRealtimeReportsEnabled implements MeasurableSettingInterface, SettingValueInterface, SystemSettingInterface
{
    /**
     * @use MeasurableGetterTrait<bool>
     */
    use MeasurableGetterTrait;

    /**
     * @use SystemGetterTrait<bool>
     */
    use SystemGetterTrait;

    /**
     * @var bool
     */
    private $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    protected static function getMeasurableName(): string
    {
        return 'enable_aggregated_realtime_reports';
    }

    protected static function getMeasurableDefaultValue()
    {
        return false;
    }

    protected static function getMeasurableType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    protected static function getSystemName(): string
    {
        return 'enable_aggregated_realtime_reports';
    }

    protected static function getSystemDefaultValue()
    {
        return false;
    }

    protected static function getSystemType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    public static function getTitle(): string
    {
        return Piwik::translate('Live_EnableAggregatedRealtimeReports');
    }

    public static function getInlineHelp(): string
    {
        return Piwik::translate('Live_EnableAggregatedRealtimeReportsDescription');
    }

    public static function getInstance(?int $idSite = null): self
    {
        $enabled = self::getSystemValue();

        if (!$enabled && $idSite !== null) {
            $enabled = self::getMeasurableValue($idSite);
        }

        return new self((bool) $enabled);
    }
}
