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
 * Check the enabled PHP functions.
 */
class PhpFunctionsCheck implements Diagnostic
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
        $label = $this->translator->translate('Installation_SystemCheckFunctions');

        $result = new DiagnosticResult($label);

        foreach ($this->getRequiredFunctions() as $function) {
            if (! self::functionExists($function)) {
                $status = DiagnosticResult::STATUS_ERROR;
                $comment = sprintf(
                    '%s <br/><br/><em>%s</em><br/><em>%s</em><br/>',
                    $function,
                    $this->getHelpMessage($function),
                    $this->translator->translate('Installation_RestartWebServer')
                );
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
    private function getRequiredFunctions()
    {
        return array(
            'debug_backtrace',
            'create_function',
            'eval',
            'gzcompress',
            'gzuncompress',
            'pack',
        );
    }

    /**
     * Tests if a function exists. Also handles the case where a function is disabled via Suhosin.
     *
     * @param string $function
     * @return bool
     */
    public static function functionExists($function)
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

    private function getHelpMessage($missingFunction)
    {
        $messages = array(
            'debug_backtrace' => 'Installation_SystemCheckDebugBacktraceHelp',
            'create_function' => 'Installation_SystemCheckCreateFunctionHelp',
            'eval'            => 'Installation_SystemCheckEvalHelp',
            'gzcompress'      => 'Installation_SystemCheckGzcompressHelp',
            'gzuncompress'    => 'Installation_SystemCheckGzuncompressHelp',
            'pack'            => 'Installation_SystemCheckPackHelp',
        );

        return $this->translator->translate($messages[$missingFunction]);
    }
}
