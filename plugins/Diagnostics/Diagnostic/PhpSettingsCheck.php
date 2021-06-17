<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        $label = $this->translator->translate('Installation_SystemCheckSettings');

        $result = new DiagnosticResult($label);
        
        foreach ($this->getRequiredSettings() as $setting) {
            if (!$setting->check()) {
                $status = $setting->getErrorResult();
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
     * @return RequiredPhpSetting[]
     */
    private function getRequiredSettings()
    {
        $requiredSettings[] = new RequiredPhpSetting('session.auto_start', 0);
    
        $maxExecutionTime = new RequiredPhpSetting('max_execution_time', 0);
        $maxExecutionTime->addRequiredValue(-1, '=');
        $maxExecutionTime->addRequiredValue(30, '>=');
        $maxExecutionTime->setErrorResult(DiagnosticResult::STATUS_WARNING);
        $requiredSettings[] = $maxExecutionTime;

        return $requiredSettings;
    }
}
