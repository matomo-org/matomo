<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IntranetMeasurable;

use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Setting;

class MeasurableSettings extends \Piwik\Plugins\WebsiteMeasurable\MeasurableSettings
{
    /** @var Setting */
    public $trustvisitorcookies;

    protected function shouldShowSettingsForType($type)
    {
        return $type === Type::ID;
    }

    protected function init()
    {
        $this->trustvisitorcookies = $this->makeTrustVisitorCookies();
        $pluginNameBackup = $this->pluginName;
        // workaround to make it possible to save website properties, otherwise the values will be discarded
        $this->pluginName = 'WebsiteMeasurable';
        parent::init();
        $this->pluginName = $pluginNameBackup;
    }

    private function makeTrustVisitorCookies()
    {
        return $this->makeSetting('trust_visitors_cookies', $default = true, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('IntranetMeasurable_TrustVisitorCookies');
            $field->inlineHelp = Piwik::translate('IntranetMeasurable_TrustVisitorCookiesHelp', array('<a rel="noreferrer noopener" href="https://matomo.org/faq/how-to/faq_175/">', '</a>'));
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });
    }
}
