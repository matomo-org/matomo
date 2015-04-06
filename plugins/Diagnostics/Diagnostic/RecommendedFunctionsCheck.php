<?php

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
            if (! $this->functionExists($function)) {
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
            'set_time_limit' => 'Installation_SystemCheckTimeLimitHelp',
            'mail'           => 'Installation_SystemCheckMailHelp',
            'parse_ini_file' => 'Installation_SystemCheckParseIniFileHelp',
            'glob'           => 'Installation_SystemCheckGlobHelp',
            'gzopen'         => 'Installation_SystemCheckZlibHelp',
        );

        return $this->translator->translate($messages[$function]);
    }

    /**
     * Tests if a function exists. Also handles the case where a function is disabled via Suhosin.
     *
     * @param string $function
     * @return bool
     */
    private function functionExists($function)
    {
        // eval() is a language construct
        if ($function == 'eval') {
            // does not check suhosin.executor.eval.whitelist (or blacklist)
            if (extension_loaded('suhosin')) {
                return @ini_get("suhosin.executor.disable_eval") != "1";
            }
            return true;
        }

        $exists = function_exists($function);

        if (extension_loaded('suhosin')) {
            $blacklist = @ini_get("suhosin.executor.func.blacklist");
            if (!empty($blacklist)) {
                $blacklistFunctions = array_map('strtolower', array_map('trim', explode(',', $blacklist)));
                return $exists && !in_array($function, $blacklistFunctions);
            }
        }

        return $exists;
    }
}
