<?php

namespace Piwik\Plugins\JsTrackerInstallCheck\NonceOption;

use Piwik\Common;
use Piwik\Date;
use Piwik\Option;
use Piwik\SettingsPiwik;

class JsTrackerInstallCheckOption
{
    const OPTION_NAME_PREFIX = 'JsTrackerInstallCheck_';
    const MAX_NONCE_AGE_SECONDS = 30;
    const NONCE_DATA_TIME = 'time';
    const NONCE_DATA_URL = 'url';
    const NONCE_DATA_IS_SUCCESS = 'isSuccessful';

    /**
     * Look up a specific nonce for a site. If none exists, an empty array is returned.
     *
     * @param int $idSite
     * @param string $nonce A MD5 hash to uniquely identify an installation test request
     * @return array The data associated with a specific nonce for a site.
     * E.g. ['time' => 1692920000, 'url' => 'https://some-test-site.local', 'isSuccessful' => true]
     */
    public function lookUpNonce(int $idSite, string $nonce): array
    {
        $nonceMap = $this->getNonceMap($idSite);

        return $nonceMap[$nonce] ?? [];
    }

    /**
     * Find the nonce for a specific site and URL. If none exists, an empty array is returned.
     *
     * @param int $idSite
     * @param string $url
     * @return array Collection containing the nonce and it's associated data.
     * E.g. ['some_nonce' => ['time' => 1692920000, 'url' => 'https://some-test-site.local', 'isSuccessful' => true]]
     */
    public function getNonceForSiteAndUrl(int $idSite, string $url): array
    {
        if (empty($url)) {
            return [];
        }

        return $this->getCurrentNonceMap($idSite, $url);
    }

    /**
     * Get the current list of nonces for a site, excluding expired ones. Optionally filter by URL. There should only be
     * one nonce per URL.
     *
     * @param int $idSite
     * @param string $url Optionally filter the results to only be the nonce associated with the provided URL
     * @return array Associative array where the nonces are the keys and the value is an array with the nonce data.
     * E.g. ['some_nonce' => ['time' => 1692920000, 'url' => 'https://some-test-site.local', 'isSuccessful' => true]]
     */
    public function getCurrentNonceMap(int $idSite, string $url = ''): array
    {
        $filteredMap = [];
        $nonceMap = $this->getNonceMap($idSite);
        $url = trim($url);
        foreach ($nonceMap as $nonce => $checkData) {
            // If the nonce is more than the max allowed age, don't include it in the collection.
            if (!empty($checkData[self::NONCE_DATA_TIME]) && Date::getNowTimestamp() - $checkData[self::NONCE_DATA_TIME] > self::MAX_NONCE_AGE_SECONDS) {
                continue;
            }

            // If the optional URL argument was provided, only include nonces for that URL
            if (!empty($url) && !empty($checkData[self::NONCE_DATA_URL]) && $checkData[self::NONCE_DATA_URL] !== $url) {
                continue;
            }

            $filteredMap[$nonce] = $checkData;
        }

        return $filteredMap;
    }

    /**
     * Get the decoded array version of the JSON stored in the option table to track installation checks. Note that this
     * won't filter out expired nonces like getCurrentNonceMap, so this should only be used when looking for past test
     * results.
     *
     * @param int $idSite
     * @return array Collection of nonces used for a specific site and their associated data.
     * E.g. ['some_nonce' => ['time' => 1692920000, 'url' => 'https://some-test-site.local', 'isSuccessful' => true]]
     */
    public function getNonceMap(int $idSite): array
    {
        $nonceOptionString = $this->getNonceOption($idSite);
        if (empty($nonceOptionString)) {
            return [];
        }

        $nonceOptionArray = json_decode($nonceOptionString, true);
        // If the option couldn't be decoded or is in the old format, let's ignore it
        if (empty($nonceOptionArray) || key_exists('nonce', $nonceOptionArray)) {
            return [];
        }

        return $nonceOptionArray;
    }

    /**
     * Update a nonce to indicate that the test was successful.
     *
     * @param int $idSite
     * @param string $nonce
     * @return bool Indicates whether the update was successful. The main reason it might fail is if the nonce isn't found
     */
    public function markNonceAsSuccessFul(int $idSite, string $nonce): bool
    {
        $nonceMap = $this->getCurrentNonceMap($idSite);
        if (empty($nonceMap[$nonce])) {
            return false;
        }

        $nonceMap[$nonce][self::NONCE_DATA_IS_SUCCESS] = true;
        $this->setNonceOption($idSite, $nonceMap);

        return true;
    }

    /**
     * Create a new nonce for the site/URL combination. This checks if a
     *
     * @param int $idSite
     * @param string $url
     * @return string
     */
    public function createNewNonce(int $idSite, string $url): string
    {
        $url = trim($url);
        $nonceMap = $this->getCurrentNonceMap($idSite);
        // If the nonce already exists and isn't expired, reuse it
        $existingNonce = $this->searchNonceMapForUrl($nonceMap, $url);
        if (!empty($existingNonce) && Date::getNowTimestamp() - $nonceMap[$existingNonce]['time'] < self::MAX_NONCE_AGE_SECONDS) {
            // Only update the time as the other test might have already succeeded, and we don't want to overwrite that
            $this->updateNonceTime($idSite, $existingNonce, Date::getNowTimestamp());
            return $existingNonce;
        }

        $nonceString = md5(SettingsPiwik::getSalt() . time() . Common::generateUniqId() . $url);
        $nonceMap[$nonceString] = [
            'time' => Date::getNowTimestamp(),
            'url' => $url,
            'isSuccessful' => false,
        ];
        $this->setNonceOption($idSite, $nonceMap);

        return $nonceString;
    }

    /**
     * Get the string JSON stored in the option table to track installation checks.
     *
     * @param int $idSite
     * @return string JSON list of nonces and the data associated with each
     */
    protected function getNonceOption(int $idSite): string
    {
        return Option::get(self::OPTION_NAME_PREFIX . $idSite) ?: '';
    }

    /**
     * Update the string JSON stored in the option table to track installation checks.
     *
     * @param int $idSite
     * @param array $nonceMap
     * @return void
     */
    protected function setNonceOption(int $idSite, array $nonceMap): void
    {
        Option::set(self::OPTION_NAME_PREFIX . $idSite, json_encode($nonceMap));
    }

    /**
     * Update the time associated with a specific nonce. This is mainly for when a nonce already exists for the
     * site and requested URL. This allows us to bump the time so that we can reuse the nonce for the second test.
     *
     * @param int $idSite
     * @param string $nonce
     * @param int $time
     * @return bool
     */
    protected function updateNonceTime(int $idSite, string $nonce, int $time): bool
    {
        $nonceMap = $this->getCurrentNonceMap($idSite);
        if (empty($nonceMap[$nonce])) {
            return false;
        }

        $nonceMap[$nonce][self::NONCE_DATA_TIME] = $time;
        $this->setNonceOption($idSite, $nonceMap);

        return true;
    }

    /**
     * @param array $nonceMap
     * @param string $url
     * @return string
     */
    protected function searchNonceMapForUrl(array $nonceMap, string $url): string
    {
        foreach ($nonceMap as $nonce => $checkData) {
            // If the URL matches, return the nonce
            if (!empty($url) && !empty($checkData[self::NONCE_DATA_URL]) && $checkData[self::NONCE_DATA_URL] === $url) {
                return $nonce;
            }
        }

        return '';
    }
}