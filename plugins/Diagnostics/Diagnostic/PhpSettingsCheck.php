<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Translation\Translator;

/**
 * Check some PHP INI settings.
 */
class PhpSettingsCheck implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;
    
    /**
     * @var array[]
     */
    private $requiredSettings = array();

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        $label = $this->translator->translate('Installation_SystemCheckSettings');

        $result = new DiagnosticResult($label);
        
        /**
         * @var PhpSettingsCheckService $setting
         */
        foreach ($this->getRequiredSettings() as $setting) {
            if (!$setting->check()) {
                $status = DiagnosticResult::STATUS_ERROR;
                $comment = sprintf(
                    '%s <br/><br/><em>%s</em><br/><em>%s</em><br/>',
                    $setting,
                    $this->translator->translate('Installation_SystemCheckPhpSetting', array($setting)),
                    $this->translator->translate('Installation_RestartWebServer')
                );
            } else {
                $status = DiagnosticResult::STATUS_OK;
                $comment = $setting;
            }

            $result->addItem(new DiagnosticResultItem($status, $comment));
        }

        return array($result);
    }

    /**
     * @return array[]
     */
    private function getRequiredSettings()
    {
        $this->addRequiredSetting(new PhpSettingsCheckService('session.auto_start', 0));
        
        $maxExecutionTime = new PhpSettingsCheckService('max_execution_time', 0);
        $maxExecutionTime->addRequiredValue(30, '>=');
        $this->addRequiredSetting($maxExecutionTime);

        if ($this->isPhpVersionAtLeast56() && ! defined("HHVM_VERSION") && !$this->isPhpVersionAtLeast70()) {
            // always_populate_raw_post_data must be -1
            // removed in PHP 7
            $this->addRequiredSetting(new PhpSettingsCheckService('always_populate_raw_post_data', -1));
        }
        
        return $this->requiredSettings;
    }

    private function isPhpVersionAtLeast56()
    {
        return version_compare(PHP_VERSION, '5.6', '>=');
    }

    private function isPhpVersionAtLeast70()
    {
        return version_compare(PHP_VERSION, '7.0.0-dev', '>=');
    }
    
    private function addRequiredSetting(PhpSettingsCheckService $checkRequiredValues){
        $this->requiredSettings[] = $checkRequiredValues;
        
        return $this;
    }
}
