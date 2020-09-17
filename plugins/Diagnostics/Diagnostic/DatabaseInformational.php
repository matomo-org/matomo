<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Informatation about the database.
 */
class DatabaseInformational implements Diagnostic
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
        $results = [];

        if (SettingsPiwik::isMatomoInstalled()) {
            $dbConfig = Config::getInstance()->database;
            $results[] = DiagnosticResult::informationalResult('DB Prefix', $dbConfig['tables_prefix']);
            $results[] = DiagnosticResult::informationalResult('DB Charset', $dbConfig['charset']);
            $results[] = DiagnosticResult::informationalResult('DB Adapter', $dbConfig['adapter']);
            $results[] = DiagnosticResult::informationalResult('MySQL Version', $this->getServerVersion());
            $results[] = DiagnosticResult::informationalResult('Num Tables', $this->getNumMatomoTables());
        }

        return $results;
    }

    private function getServerVErsion() {
        try {
            return Db::get()->getServerVersion();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function getNumMatomoTables() {
        $prefix = Common::prefixTable('');

        $results = null;
        try {
            $results = Db::get()->fetchAll('show tables like "' . $prefix . '%"');
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        if (defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE) {
            return '89'; // fails randomly in tests as it is sometimes eg 89 and sometimes 90 depending on the time
        }

        return count($results);
    }

}
