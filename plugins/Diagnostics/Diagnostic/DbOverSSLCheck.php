<?php

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Config;
use Piwik\Db;
use Piwik\Translation\Translator;

/**
 * Check if Piwik is connected with database through ssl.
 */
class DbOverSSLCheck implements Diagnostic
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
        $enable_ssl = Config::getInstance()->database['enable_ssl'];
        if (!$enable_ssl) {
            return array();
        }

        $label = $this->translator->translate('Installation_SystemCheckDatabaseSSL');

        $cipher = Db::fetchRow("show status like 'Ssl_cipher'");
        if(!empty($cipher['Value'])) {
             return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, $this->translator->translate('Installation_SystemCheckDatabaseSSLCipher') . ': ' . $cipher['Value']));
        }

        //no cipher, not working
        $comment = sprintf($this->translator->translate('Installation_SystemCheckDatabaseSSLNotWorking'), "enable_ssl") . "<br />";

        // test ssl support
        $ssl_support = Db::fetchRow("SHOW VARIABLES LIKE 'have_ssl'");
        if(!empty($ssl_support['Value'])) {
            switch ($ssl_support['Value']) {
                case 'YES':
                    $comment .= $this->translator->translate('Installation_SystemCheckDatabaseSSLOn');
                    break;
                case 'DISABLED':
                    $comment .= $this->translator->translate('Installation_SystemCheckDatabaseSSLDisabled');
                    break;
                case 'NO':
                    $comment .= $this->translator->translate('Installation_SystemCheckDatabaseSSLNo');
                    break;
            }
        }

        $comment .= '<br />' . '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/faq/"> FAQ on matomo.org</a>';

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
