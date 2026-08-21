<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy;

use Piwik\Piwik;
use Piwik\Url;

class CnilPolicy extends CompliancePolicy
{
    private const CONSENT_EXEMPTION_FAQ_URL = 'https://matomo.org/faq/how-to/how-do-i-configure-matomo-without-tracking-consent-for-french-visitors-cnil-exemption/';

    public static function getName(): string
    {
        return 'cnil_v1';
    }

    public static function generateDescription(): string
    {
        return Piwik::translate('General_ComplianceCNILDescription', self::getFaqLinkParameters());
    }

    protected static function generateGranularDescription(): string
    {
        return Piwik::translate('General_ComplianceCNILGranularDescription', self::getFaqLinkParameters());
    }

    /**
     * Both link placeholder pairs of the CNIL descriptions point at the consent exemption FAQ.
     *
     * @return array<string>
     */
    private static function getFaqLinkParameters(): array
    {
        $openingTag = '<a href="' .
            Url::addCampaignParametersToMatomoLink(
                self::CONSENT_EXEMPTION_FAQ_URL,
                null,
                null,
                'App.PrivacyManager.compliance'
            ) .
            '" target="_blank" rel="noreferrer noopener">';

        return [$openingTag, '</a>', $openingTag, '</a>'];
    }

    protected static function generateWarnings(): string
    {
        return Piwik::translate('General_ComplianceCNILWarning');
    }

    public static function getTitle(): string
    {
        return Piwik::translate('General_ComplianceCNILTitle');
    }

    public static function getUnknownSettings(): array
    {
        return [
            [
                'id' => 'optOut',
                'title' => Piwik::translate('General_ComplianceCNILUnknownSettingOptOutTitle'),
                'note' =>
                    Piwik::translate('General_ComplianceCNILUnknownSettingOptOutNotes', [
                        '<a href="' .
                        Url::addCampaignParametersToMatomoLink(
                            'https://matomo.org/faq/general/faq_20000/',
                            null,
                            null,
                            'App.PrivacyManager.compliance'
                        ) .
                        '" target="_blank" rel="noreferrer noopener">',
                        '</a>',
                    ]),
                'impact' => Piwik::translate('General_ComplianceCNILUnknownSettingOptOutImpact'),
            ],
        ];
    }
}
