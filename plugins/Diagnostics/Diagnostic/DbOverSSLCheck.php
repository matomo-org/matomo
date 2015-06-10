<?php

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Translation\Translator;

/**
 * Check if Piwik is connected with database through ssl.
 * TODO: translation
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

        //$label = $this->translator->translate('Installation_DatabaseAbilities'); 
        $label = "Database SSL connection:";

        $cipher = Db::fetchAll("show status like 'Ssl_cipher'");
        if(!empty($cipher[0]['Value'])) {
             return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, 'Cipher: ' . $cipher[0]['Value']));
        }

        //no cipher, not working
        $comment = 'use_ssl is set to true but ssl connection is not working<br />';

        // test ssl support
        $ssl_support = Db::fetchAll("SHOW VARIABLES LIKE 'have_ssl'");
        switch ($ssl_support[0]['Value']) {
            case 'YES': 
                $comment .= "Database server is supporting SSL connections, please check your configuration in config/config.ini.php <br /> 
                    Consider using REQUIRE SSL for your piwik database user";
                break;
            case 'DISABLED':
                $comment .= "SSL support in your database server is disabled";
                break;
            case 'NO':
                $comment .= "Database server is not compiled with SSL support";
                break;
        }

        $comment .= '<br />Piwik <a target="_blank" href="?module=Proxy&action=redirect&url=http://piwik.org/faq/"> FAQ on piwik.org</a>'; // TODO: change link to piwik FAQ how to set up ssl connection

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
