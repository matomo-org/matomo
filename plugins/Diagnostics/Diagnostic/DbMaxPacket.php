<?php

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;
use Piwik\Metrics\Formatter;

/**
 * Check if Piwik is connected with database through ssl.
 */
class DbMaxPacket implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    const MIN_VALUE_MAX_PACKET_MB = 64;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        if (!SettingsPiwik::isMatomoInstalled()) {
            return array(); // only possible to perform check once we have DB connection
        }

        $maxPacketBytes = Db::fetchRow("SHOW VARIABLES LIKE 'max_allowed_packet'");

        $status = DiagnosticResult::STATUS_OK;
        $label = $this->translator->translate('Diagnostics_MysqlMaxPacketSize');
        $comment = '';

        $minSize = self::MIN_VALUE_MAX_PACKET_MB * 1000 * 1000; // not using 1024 just in case... this amount be good enough
        if (!empty($maxPacketBytes['Value']) && $maxPacketBytes['Value'] < $minSize) {
            $status = DiagnosticResult::STATUS_WARNING;
            $formatter = new Formatter\Html();
            $pretty = $formatter->getPrettySizeFromBytes($maxPacketBytes['Value'], 'M', $precision = 1);
            $configured = str_replace(array(' M', '&nbsp;M'), 'MB', $pretty);
            $comment = Piwik::translate('Diagnostics_MysqlMaxPacketSizeWarning', array('64MB', $configured));
        }

        return array(DiagnosticResult::singleResult($label, $status, $comment));
    }
}
