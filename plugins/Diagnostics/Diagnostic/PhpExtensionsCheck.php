<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Translation\Translator;

/**
 * Check the PHP extensions.
 */
class PhpExtensionsCheck implements Diagnostic
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
        $label = $this->translator->translate('Installation_SystemCheckExtensions');

        $result = new DiagnosticResult($label);
        $longErrorMessage = '';

        $requiredExtensions = $this->getRequiredExtensions();

        foreach ($requiredExtensions as $extension) {
            if (! extension_loaded($extension)) {
                $status = DiagnosticResult::STATUS_ERROR;
                $comment = $extension . ': ' . $this->translator->translate('Installation_RestartWebServer');
                $longErrorMessage .= '<p>' . $this->getHelpMessage($extension) . '</p>';
            } else {
                $status = DiagnosticResult::STATUS_OK;
                $comment = $extension;
            }

            $result->addItem(new DiagnosticResultItem($status, $comment));
        }

        $result->setLongErrorMessage($longErrorMessage);

        return array($result);
    }

    /**
     * @return string[]
     */
    private function getRequiredExtensions()
    {
        $requiredExtensions = array(
            'zlib',
            'SPL',
            'iconv',
            'json',
            'mbstring',
            'Reflection',
        );

        return $requiredExtensions;
    }

    private function getHelpMessage($missingExtension)
    {
        $messages = array(
            'zlib'       => 'Installation_SystemCheckZlibHelp',
            'SPL'        => 'Installation_SystemCheckSplHelp',
            'iconv'      => 'Installation_SystemCheckIconvHelp',
            'json'       => 'Installation_SystemCheckWarnJsonHelp',
            'mbstring'   => 'Installation_SystemCheckMbstringHelp',
            'Reflection' => 'Required extension that is built in PHP, see http://www.php.net/manual/en/book.reflection.php',
        );

        return $this->translator->translate($messages[$missingExtension]);
    }
}
