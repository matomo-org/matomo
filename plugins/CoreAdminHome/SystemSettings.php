<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Controller as CoreAdminController;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $corsDomains;

    /** @var Setting */
    public $trustedHostnames;

    protected function init()
    {
        $this->title = Piwik::translate('CoreAdminHome_CorsDomains');

        $isWritable = Piwik::hasUserSuperUserAccess() && CoreAdminController::isGeneralSettingsAdminEnabled();
        $this->corsDomains = $this->createCorsDomains();
        $this->corsDomains->setIsWritableByCurrentUser($isWritable);

    }


    private function createCorsDomains()
    {
        return $this->makeSettingManagedInConfigOnly('General', 'cors_domains', $default = [], FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->uiControl = FieldConfig::UI_CONTROL_MULTI_TUPLE;
            $field1 = new FieldConfig\MultiPair(Piwik::translate('Overlay_Domain'), 'domain', FieldConfig::UI_CONTROL_TEXT);
            $field->uiControlAttributes['field1'] = $field1->toArray();
            $field->transform = function($values) {
                $corsDomains = [];
                foreach ($values as $value) {
                    if (!empty($value['domain'])) {
                        $corsDomains[] = $value['domain'];
                    }
                }
                return $corsDomains;
            };
            $field->prepareValue = function($value) {
                $domains = [];
                foreach ($value as $domain) {
                    $domains[] = ['domain' => $domain];
                }
                return $domains;
            };
            $field->inlineHelp = Piwik::translate('CoreAdminHome_CorsDomainsHelp');
        });
    }
}
