<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

/**
 * The bundles the Marketplace sells, ordered by what they include.
 *
 * A consumer who already holds a bundle needs no promotion for it, nor for any bundle
 * below it, which is what the tiers are for: Team is the smallest, Enterprise the
 * largest, and a promotion is only worth showing when the tier held is lower than the
 * tier promoted.
 *
 * The Marketplace names its bundles like any other product, and a bundle is never
 * "installed" the way a plugin is - holding one is a property of the license, not of the
 * plugins directory.
 */
final class PremiumBundle
{
    /**
     * The Team bundle is a later addition than the other two, which is why it appears
     * nowhere else in Matomo: only `BusinessBundle` and `EnterpriseBundle` are named in
     * `Marketplace\Plugins::getCurrentLicenseFor()`. A legacy account is not offered it,
     * and {@see PremiumEntitlements::isBundleOffered()} is what keeps the promotion away
     * from those accounts.
     *
     * @internal The exact product name is still **unconfirmed**; everything here keys off
     *           this constant, so correcting it is a one line change.
     */
    public const TEAM = 'TeamBundle';

    public const BUSINESS = 'BusinessBundle';

    public const ENTERPRISE = 'EnterpriseBundle';

    /**
     * Ascending order of value. The numbers are only ever compared with each other, so
     * their absolute values carry no meaning.
     */
    private const TIERS = [
        self::TEAM => 1,
        self::BUSINESS => 2,
        self::ENTERPRISE => 3,
    ];

    /**
     * The tier of the given product, or 0 when it is not a bundle at all.
     */
    public static function getTier(string $productName): int
    {
        return self::TIERS[$productName] ?? 0;
    }

    /**
     * @return string[] every bundle product name
     */
    public static function getAllNames(): array
    {
        return array_keys(self::TIERS);
    }
}
