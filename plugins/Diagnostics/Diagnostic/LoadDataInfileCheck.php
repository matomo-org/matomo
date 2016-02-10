<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Translation\Translator;

/**
 * Check if Piwik can use LOAD DATA INFILE.
 */
class LoadDataInfileCheck implements Diagnostic
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
        $isPiwikInstalling = !Config::getInstance()->existsLocalConfig();
        if ($isPiwikInstalling) {
            // Skip the diagnostic if Piwik is being installed
            return array();
        }

        $label = $this->translator->translate('Installation_DatabaseAbilities');

        $optionTable = Common::prefixTable('option');
        $testOptionNames = array('test_system_check1', 'test_system_check2');

        $loadDataInfile = false;
        $errorMessage = null;
        try {
            $loadDataInfile = Db\BatchInsert::tableInsertBatch(
                $optionTable,
                array('option_name', 'option_value'),
                array(
                    array($testOptionNames[0], '1'),
                    array($testOptionNames[1], '2'),
                ),
                $throwException = true,
                $charset = 'latin1'
            );
        } catch (\Exception $ex) {
            $errorMessage = str_replace("\n", "<br/>", $ex->getMessage());
        }

        // delete the temporary rows that were created
        Db::exec("DELETE FROM `$optionTable` WHERE option_name IN ('" . implode("','", $testOptionNames) . "')");

        if ($loadDataInfile) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, 'LOAD DATA INFILE'));
        }

        $comment = sprintf(
            'LOAD DATA INFILE<br/>%s<br/>%s',
            $this->translator->translate('Installation_LoadDataInfileUnavailableHelp', array(
                'LOAD DATA INFILE',
                'FILE',
            )),
            $this->translator->translate('Installation_LoadDataInfileRecommended')
        );

        if ($errorMessage) {
            $comment .= sprintf(
                '<br/><strong>%s:</strong> %s<br/>%s',
                $this->translator->translate('General_Error'),
                $errorMessage,
                'Troubleshooting: <a target="_blank" href="?module=Proxy&action=redirect&url=http://piwik.org/faq/troubleshooting/%23faq_194">FAQ on piwik.org</a>'
            );
        }

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
