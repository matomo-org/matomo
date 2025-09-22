<?php

namespace Piwik\Policy;

use Piwik\Piwik;

class CnilPolicy extends CompliancePolicy
{
    public static function getName(): string
    {
        return 'cnil_v1';
    }

    public static function getDescription(): string
    {
        return Piwik::translate('General_ComplianceCNILDescription');
    }

    public static function getTitle(): string
    {
        return Piwik::translate('General_ComplianceCNILTitle');
    }

    protected static function getMinimumRequiredPlugins(): array
    {
        return [
            'PrivacyManager',
            'Live',
            'WebsiteMeasurable',
        ];
    }
}
