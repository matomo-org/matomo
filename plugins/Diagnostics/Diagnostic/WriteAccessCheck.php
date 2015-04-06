<?php

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Container\StaticContainer;
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

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
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
        // TODO dependency injection
        $tmpPath = StaticContainer::get('path.tmp');

        $directoriesToCheck = array(
            $tmpPath,
            $tmpPath . '/assets/',
            $tmpPath . '/cache/',
            $tmpPath . '/climulti/',
            $tmpPath . '/latest/',
            $tmpPath . '/logs/',
            $tmpPath . '/sessions/',
            $tmpPath . '/tcpdf/',
            $tmpPath . '/templates_c/',
        );

        if (! DbHelper::isInstalled()) {
            // at install, need /config to be writable (so we can create config.ini.php)
            $directoriesToCheck[] = '/config/';
        }

        return $directoriesToCheck;
    }
}
