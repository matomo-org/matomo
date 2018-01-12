<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Db\Adapter;
use Piwik\SettingsServer;
use Piwik\Translation\Translator;

/**
 * Check supported DB adapters are available.
 */
class DbAdapterCheck implements Diagnostic
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
        $results = array();
        $results[] = $this->checkPdo();
        $results = array_merge($results, $this->checkDbAdapters());

        return $results;
    }

    private function checkPdo()
    {
        $label = 'PDO ' . $this->translator->translate('Installation_Extension');

        if (extension_loaded('PDO')) {
            $status = DiagnosticResult::STATUS_OK;
        } else {
            $status = DiagnosticResult::STATUS_WARNING;
        }

        return DiagnosticResult::singleResult($label, $status);
    }

    private function checkDbAdapters()
    {
        $results = array();
        $adapters = Adapter::getAdapters();

        foreach ($adapters as $adapter => $port) {
            $label = $adapter . ' ' . $this->translator->translate('Installation_Extension');

            $results[] = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK);
        }

        if (empty($adapters)) {
            $label = $this->translator->translate('Installation_SystemCheckDatabaseExtensions');
            $comment = $this->translator->translate('Installation_SystemCheckDatabaseHelp');

            $result = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_ERROR, $comment);
            $result->setLongErrorMessage($this->getLongErrorMessage());

            $results[] = $result;
        }

        return $results;
    }

    private function getLongErrorMessage()
    {
        $message = '<p>';

        if (SettingsServer::isWindows()) {
            $message .= $this->translator->translate(
                'Installation_SystemCheckWinPdoAndMysqliHelp',
                array('<br /><br /><code>extension=php_mysqli.dll</code><br /><code>extension=php_pdo.dll</code><br /><code>extension=php_pdo_mysql.dll</code><br />')
            );
        } else {
            $message .= $this->translator->translate(
                'Installation_SystemCheckPdoAndMysqliHelp',
                array(
                    '<br /><br /><code>--with-mysqli</code><br /><code>--with-pdo-mysql</code><br /><br />',
                    '<br /><br /><code>extension=mysqli.so</code><br /><code>extension=pdo.so</code><br /><code>extension=pdo_mysql.so</code><br />'
                )
            );
        }

        $message .= $this->translator->translate('Installation_RestartWebServer') . '<br/><br/>';
        $message .= $this->translator->translate('Installation_SystemCheckPhpPdoAndMysqli', array(
            '<a style="color:red" href="http://php.net/pdo">',
            '</a>',
            '<a style="color:red" href="http://php.net/mysqli">',
            '</a>',
        ));
        $message .= '</p>';

        return $message;
    }
}
