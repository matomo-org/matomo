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
    private const CLOUD_DPA_URL = 'https://matomo.org/matomo-cloud-dpa/';

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
        $description = Piwik::translate(
            'General_ComplianceCNILGranularDescription2',
            array_merge(self::getFaqLinkParameters(), [self::getGranularStatusLegend()])
        );

        // the DPA only covers Matomo Cloud, so on-premise instances must not be pointed at it
        if (static::getPluginManagerInstance()->isPluginActivated('Cloud')) {
            $description .= '<br/><br/>' . Piwik::translate('General_ComplianceCNILCloudDpa', [
                self::getLinkOpeningTag(self::CLOUD_DPA_URL),
                '</a>',
            ]);
        }

        return $description;
    }

    /**
     * Both link placeholder pairs of the CNIL descriptions point at the consent exemption FAQ.
     *
     * @return array<string>
     */
    private static function getFaqLinkParameters(): array
    {
        $openingTag = self::getLinkOpeningTag(self::CONSENT_EXEMPTION_FAQ_URL);

        return [$openingTag, '</a>', $openingTag, '</a>'];
    }

    private static function getLinkOpeningTag(string $url): string
    {
        return '<a href="' .
            Url::addCampaignParametersToMatomoLink(
                $url,
                null,
                null,
                'App.PrivacyManager.compliance'
            ) .
            '" target="_blank" rel="noreferrer noopener">';
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
                        self::getLinkOpeningTag('https://matomo.org/faq/general/faq_20000/'),
                        '</a>',
                    ]),
                'impact' => Piwik::translate('General_ComplianceCNILUnknownSettingOptOutImpact'),
            ],
        ];
    }
}
