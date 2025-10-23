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

    public static function getUnknownSettings(): array
    {
        return [
            [
                'title' => Piwik::translate('General_ComplianceCNILUnknownSettingOptOutTitle'),
                'note' => Piwik::translate('General_ComplianceCNILUnknownSettingOptOutNotes'),
            ],
        ];
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
