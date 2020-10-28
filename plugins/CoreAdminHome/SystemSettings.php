<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Columns\Dimension;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CoreAdminHome\Controller as CoreAdminController;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Tracker\Cache;

class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $corsDomains;

    /** @var Setting */
    public $trustedHostnames;

    /** @var Setting */
    public $disabledDimensions;

    protected function init()
    {
        $this->title = ' '; // intentionally left blank as it's hidden with css

        $isWritable = Piwik::hasUserSuperUserAccess() && CoreAdminController::isGeneralSettingsAdminEnabled();
        $this->trustedHostnames = $this->createTrustedHostnames();
        $this->trustedHostnames->setIsWritableByCurrentUser($isWritable);

        $isWritable = Piwik::hasUserSuperUserAccess();
        $this->corsDomains = $this->createCorsDomains();
        $this->corsDomains->setIsWritableByCurrentUser($isWritable);

        $this->disabledDimensions = $this->createDisabledDimensions();
        $this->disabledDimensions->setIsWritableByCurrentUser($isWritable);
    }


    private function createCorsDomains()
    {
        return $this->makeSettingManagedInConfigOnly('General', 'cors_domains', $default = [], FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->introduction = Piwik::translate('CoreAdminHome_CorsDomains');
            $field->uiControl = FieldConfig::UI_CONTROL_FIELD_ARRAY;
            $arrayField = new FieldConfig\ArrayField(Piwik::translate('Overlay_Domain'), FieldConfig::UI_CONTROL_TEXT);
            $field->uiControlAttributes['field'] = $arrayField->toArray();
            $field->inlineHelp = Piwik::translate('CoreAdminHome_CorsDomainsHelp');
            $field->transform = function($values) {
                return array_filter($values);
            };
        });
    }

    private function createTrustedHostnames()
    {
        return $this->makeSettingManagedInConfigOnly('General', 'trusted_hosts', $default = [], FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->introduction = Piwik::translate('CoreAdminHome_TrustedHostSettings');
            $field->uiControl = FieldConfig::UI_CONTROL_FIELD_ARRAY;
            $arrayField = new FieldConfig\ArrayField(Piwik::translate('CoreAdminHome_ValidPiwikHostname'), FieldConfig::UI_CONTROL_TEXT);
            $field->uiControlAttributes['field'] = $arrayField->toArray();
            $field->transform = function($values) {
                return array_filter($values);
            };
        });
    }

    private function createDisabledDimensions()
    {
        return $this->makeSetting('disabled_settings', $default = [], FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->introduction = 'Disabled Dimensions'; // TODO translate
            $field->inlineHelp = "Disable dimensions to avoid tracking this data no matter what is sent to the tracker. This can be useful in being compliant with various privacy regulations."; // TODO: translate
            $field->uiControl = FieldConfig::UI_CONTROL_MULTI_SELECT;
            $field->availableValues = $this->getAvailableDimensionsToDisable();
        });
    }

    public function save()
    {
        parent::save();
        Cache::deleteTrackerCache();
    }

    private function getAvailableDimensionsToDisable()
    {
        // TODO: translate types
        $dimensions = [];
        $this->addDimensions($dimensions, VisitDimension::getAllDimensions(), $type = 'Visit');
        $this->addDimensions($dimensions, ActionDimension::getAllDimensions(), $type = 'Action');
        $this->addDimensions($dimensions, ConversionDimension::getAllDimensions(), $type = 'Conversion');
        return $dimensions;
    }

    /**
     * @param string[] $dimensions
     * @param Dimension[] $allDimensions
     * @param $dimensionType
     */
    private function addDimensions(array &$dimensions, array $allDimensions, $dimensionType)
    {
        foreach ($allDimensions as $dimension) {
            $dimensions[$dimension->getId()] = ($dimension->getName() ?: get_class($dimension)) . ' (' . $dimensionType . ')';
        }
    }
}
