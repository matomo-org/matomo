<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\DbHelper;
use Piwik\Filechecks;
use Piwik\Translation\Translator;

/**
 * Check the permissions for some directories.
 */
class WriteAccessCheck implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * Path to the temp directory.
     * @var string
     */
    private $tmpPath;

    /**
     * @param Translator $translator
     * @param string $tmpPath Path to the temp directory.
     */
    public function __construct(Translator $translator, $tmpPath)
    {
        $this->translator = $translator;
        $this->tmpPath = $tmpPath;
    }

    public function execute()
    {
        $label = $this->translator->translate('Installation_SystemCheckWriteDirs');

        $result = new DiagnosticResult($label);

        $directories = Filechecks::checkDirectoriesWritable($this->getDirectories());

        $error = false;
        foreach ($directories as $directory => $isWritable) {
            if ($isWritable) {
                $status = DiagnosticResult::STATUS_OK;
            } else {
                $status = DiagnosticResult::STATUS_ERROR;
                $error = true;
            }

            $result->addItem(new DiagnosticResultItem($status, $directory));
        }

        if ($error) {
            $longErrorMessage = $this->translator->translate('Installation_SystemCheckWriteDirsHelp');
            $longErrorMessage .= '<ul>';
            foreach ($directories as $directory => $isWritable) {
                if (! $isWritable) {
                    $longErrorMessage .= sprintf('<li><pre>chmod a+w %s</pre></li>', $directory);
                }
            }
            $longErrorMessage .= '</ul>';
            $result->setLongErrorMessage($longErrorMessage);
        }

        return array($result);
    }

    /**
     * @return string[]
     */
    private function getDirectories()
    {
        $directoriesToCheck = array(
            $this->tmpPath,
            $this->tmpPath . '/assets/',
            $this->tmpPath . '/cache/',
            $this->tmpPath . '/climulti/',
            $this->tmpPath . '/latest/',
            $this->tmpPath . '/logs/',
            $this->tmpPath . '/sessions/',
            $this->tmpPath . '/tcpdf/',
            $this->tmpPath . '/templates_c/',
        );

        if (! DbHelper::isInstalled()) {
            // at install, need /config to be writable (so we can create config.ini.php)
            $directoriesToCheck[] = '/config/';
        }

        return $directoriesToCheck;
    }
}
