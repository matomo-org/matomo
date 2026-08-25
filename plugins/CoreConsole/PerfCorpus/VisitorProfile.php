<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

/**
 * Everything about a visitor that stays the same for their whole life: who they are, where they
 * are, and what they browse with.
 *
 * Recomputed from the visitor ordinal wherever it is needed rather than stored, which is what
 * lets any worker generate any visitor without reading anything but its own spool file.
 *
 * These are the columns the current generator leaves empty - browser, engine, OS, device brand
 * and model are empty strings, geo is a bare country with no region, city or coordinates, and
 * custom dimensions and user_id are never written at all. That is not a cosmetic gap: those are
 * the columns LogAggregator groups by, so leaving them empty removes most of the archiving cost
 * the benchmark is supposed to measure.
 */
class VisitorProfile
{
    public string $idVisitorHex;
    public string $configIdHex;
    public string $ipHex;
    public ?string $userId;
    public string $country;
    public string $region;
    public string $city;
    public string $latitude;
    public string $longitude;
    public string $browserName;
    public string $browserEngine;
    public string $browserVersion;
    public string $os;
    public string $osVersion;
    public int $deviceType;
    public int $clientType;
    public ?string $deviceBrand;
    public ?string $deviceModel;
    public string $resolution;
    public string $language;
    public array $customDimensions;
    public int $localtimeOffsetSeconds;

    private static ?array $countryCdf = null;
    private static ?array $browserCdf = null;
    private static ?array $deviceCdf = null;

    /**
     * @param int $ordinal the visitor's global ordinal; the only input besides the run seed
     */
    public static function build(int $seed, int $ordinal, Profile $profile): self
    {
        $rng = Rng::forStream($seed, Rng::S_VISITOR_CONTENT, $ordinal);
        $self = new self();

        // Unique by construction - no lookup, and no collision worth worrying about: 8 random
        // bytes over a few hundred million visitors.
        $self->idVisitorHex = substr(hash('sha256', $seed . ':v:' . $ordinal), 0, 16);

        self::$countryCdf = self::$countryCdf ?? Vocabulary::toCdf(Vocabulary::COUNTRIES, 1);
        self::$browserCdf = self::$browserCdf ?? Vocabulary::toCdf(Vocabulary::BROWSERS, 2);
        self::$deviceCdf = self::$deviceCdf ?? Vocabulary::toCdf(Vocabulary::DEVICE_TYPES, 1);

        $countryIndex = $rng->pickFromCdf(self::$countryCdf);
        $self->country = Vocabulary::COUNTRIES[$countryIndex][0];

        // A city pool sized so location_city has realistic cardinality without a geo database.
        // The first stem is Berlin, and the "city == Berlin" segment targets exactly it.
        $cityIndex = $rng->nextInt(0, Profile::CITY_POOL_SIZE - 1);
        $self->city = Vocabulary::city($cityIndex);
        $self->region = sprintf('%02d', $cityIndex % 40);
        $self->latitude = sprintf('%.6f', 35.0 + ($cityIndex % 1000) / 1000.0 * 25.0);
        $self->longitude = sprintf('%.6f', -9.0 + ($cityIndex % 997) / 997.0 * 40.0);
        $self->localtimeOffsetSeconds = (($cityIndex % 5) - 1) * 3600;

        $browserIndex = $rng->pickFromCdf(self::$browserCdf);
        $self->browserName = Vocabulary::BROWSERS[$browserIndex][0];
        $self->browserEngine = Vocabulary::BROWSERS[$browserIndex][1];
        $self->browserVersion = $rng->nextInt(96, 141) . '.0';

        $deviceIndex = $rng->pickFromCdf(self::$deviceCdf);
        $self->deviceType = Vocabulary::DEVICE_TYPES[$deviceIndex][0];
        // Matomo's config_client_type: 1 desktop, 2 smartphone/tablet, 3 other.
        $self->clientType = 0 === $self->deviceType ? 1 : (in_array($self->deviceType, [1, 2], true) ? 2 : 3);

        if (0 === $self->deviceType) {
            $self->os = Vocabulary::DESKTOP_OS[$rng->nextInt(0, count(Vocabulary::DESKTOP_OS) - 1)];
            $self->deviceBrand = null;
            $self->deviceModel = null;
        } else {
            $self->os = Vocabulary::MOBILE_OS[$rng->nextInt(0, count(Vocabulary::MOBILE_OS) - 1)];
            $brandIndex = $rng->nextInt(0, count(Vocabulary::MOBILE_BRANDS) - 1);
            // Apple ships iOS and nobody else does; an inconsistent pair would be visible in the
            // Devices report and would look like a bug in Matomo rather than in the data.
            if ('IOS' === $self->os) {
                $brandIndex = 0;
            } elseif (0 === $brandIndex) {
                $brandIndex = 1;
            }
            $self->deviceBrand = Vocabulary::MOBILE_BRANDS[$brandIndex];
            $self->deviceModel = sprintf('Model %s%d', chr(65 + ($ordinal % 26)), $ordinal % 40);
        }

        $self->osVersion = $rng->nextInt(10, 18) . '.' . $rng->nextInt(0, 6);
        $self->resolution = Vocabulary::RESOLUTIONS[$rng->nextInt(0, count(Vocabulary::RESOLUTIONS) - 1)];
        $self->language = Vocabulary::LANGUAGES[$rng->nextInt(0, count(Vocabulary::LANGUAGES) - 1)];

        $self->userId = $rng->nextBool(Profile::USER_ID_SHARE) ? 'user_' . $ordinal : null;
        $self->ipHex = self::buildIp($rng);

        // config_id is a fingerprint hash in production, so two visitors with the same setup
        // collide. Hashing the fingerprint here reproduces that instead of making it unique.
        $self->configIdHex = substr(hash('sha256', implode('|', [
            $self->browserName,
            $self->browserVersion,
            $self->os,
            $self->osVersion,
            $self->resolution,
            $self->language,
            $self->deviceType,
        ])), 0, 16);

        $self->customDimensions = self::buildCustomDimensions($rng, $ordinal);

        return $self;
    }

    /**
     * VARBINARY(16): four bytes for IPv4, sixteen for IPv6, as Matomo stores them.
     */
    private static function buildIp(Rng $rng): string
    {
        if ($rng->nextBool(0.90)) {
            return sprintf(
                '%02x%02x%02x%02x',
                $rng->nextInt(1, 223),
                $rng->nextInt(0, 255),
                $rng->nextInt(0, 255),
                $rng->nextInt(0, 254)
            );
        }

        $hex = '2a00';
        for ($i = 0; $i < 6; $i++) {
            $hex .= sprintf('%04x', $rng->nextInt(0, 65535));
        }

        return $hex;
    }

    /**
     * Five visit-scope custom dimensions with deliberately different cardinalities, so segments
     * on them span the whole selectivity range from "a fifth of all visits" down to a needle.
     */
    private static function buildCustomDimensions(Rng $rng, int $ordinal): array
    {
        return [
            1 => 'tier-' . $rng->pickFromCdf([0.20, 0.45, 0.68, 0.86, 1.00]),
            2 => 'segment-' . $rng->nextInt(0, 99),
            3 => 'cohort-' . $rng->nextInt(0, 9999),
            4 => 'account-' . $rng->nextInt(0, 999999),
            5 => $rng->nextBool(0.20) ? 'plan-' . $rng->nextInt(0, 999) : null,
        ];
    }
}
