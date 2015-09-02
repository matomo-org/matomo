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
 * Check the PHP functions that are not required but recommended.
 */
class RecommendedFunctionsCheck implements Diagnostic
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
        $label = $this->translator->translate('Installation_SystemCheckOtherFunctions');

        $result = new DiagnosticResult($label);

        foreach ($this->getRecommendedFunctions() as $function) {
            if (! PhpFunctionsCheck::functionExists($function)) {
                $status = DiagnosticResult::STATUS_WARNING;
                $comment = $function . '<br/>' . $this->getHelpMessage($function);
            } else {
                $status = DiagnosticResult::STATUS_OK;
                $comment = $function;
            }

            $result->addItem(new DiagnosticResultItem($status, $comment));
        }

        return array($result);
    }

    /**
     * @return string[]
     */
    private function getRecommendedFunctions()
    {
        return array(
            'shell_exec',
            'set_time_limit',
            'mail',
            'parse_ini_file',
            'glob',
            'gzopen',
        );
    }

    private function getHelpMessage($function)
    {
        $messages = array(
            'shell_exec'     => 'Installation_SystemCheckFunctionHelp',
            'set_time_limit' => 'Installation_SystemCheckTimeLimitHelp',
            'mail'           => 'Installation_SystemCheckMailHelp',
            'parse_ini_file' => 'Installation_SystemCheckParseIniFileHelp',
            'glob'           => 'Installation_SystemCheckGlobHelp',
            'gzopen'         => 'Installation_SystemCheckZlibHelp',
        );

        return $this->translator->translate($messages[$function]);
    }
}
