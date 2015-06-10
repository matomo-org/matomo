<?php

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Translation\Translator;

/**
 * Check if Piwik is connected with database through ssl.
 * TODO: link to piwik FAQ into comment
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
        $use_ssl = Config::getInstance()->database['use_ssl'];
        if (!$use_ssl) {
            return array();
        }

        $label = $this->translator->translate('Installation_SystemCheckDatabaseSSL');

        $cipher = Db::fetchAll("show status like 'Ssl_cipher'");
        if(!empty($cipher[0]['Value'])) {
             return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, $this->translator->translate('Installation_SystemCheckDatabaseSSLCipher') . ': ' . $cipher[0]['Value']));
        }

        //no cipher, not working
        $comment = sprintf($this->translator->translate('Installation_SystemCheckDatabaseSSLNotWorking'), "use_ssl", "true");

        // test ssl support
        $ssl_support = Db::fetchAll("SHOW VARIABLES LIKE 'have_ssl'");
        if(!empty($ssl_support[0]['Value'])) {
            switch ($ssl_support[0]['Value']) {
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

        $comment .= '<br /><a target="_blank" href="?module=Proxy&action=redirect&url=http://piwik.org/faq/"> FAQ on piwik.org</a>'; // TODO: change link to piwik FAQ how to set up ssl connection

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
