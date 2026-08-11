<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;

use Piwik\Common;
use Piwik\Http;

class Azure implements IpRangeProviderInterface
{
    public function getRanges(): array
    {
        $ranges = [];

        $downloadUrl = $this->getDownloadUrl();

        $azure = Http::sendHttpRequest($downloadUrl, 120);

        if (empty($azure)) {
            throw new \Exception('Failed to retrieve azure IP ranges');
        }
        $azure = json_decode($azure, true);
        if (empty($azure)) {
            throw new \Exception('Failed to retrieve azure IP ranges: ' . json_last_error_msg());
        }
        if (empty($azure['values'])) {
            throw new \Exception('Failed to retrieve azure IP range values');
        }

        $azureValues = $azure['values'];
        foreach ($azureValues as $azureValue) {
            if (!isset($azureValue['properties']['addressPrefixes'])) {
                throw new \Exception('Failed to get azure address prefixes');
            }
            foreach ($azureValue['properties']['addressPrefixes'] as $ip) {
                $ranges[] = $ip;
            }
        }

        return $ranges;
    }

    public function getDownloadUrl()
    {
        //  get info somehow for up to date from url https://www.microsoft.com/en-us/download/confirmation.aspx?id=56519

        // see also https://docs.microsoft.com/en-us/azure/virtual-network/service-tags-overview .
        // The api itself we don't really want to use. We'd need a subscriptionId. Unless we fetch it on a matomo server and make it available through a JSON there but then not sure if we are allowed to do that
        // or can we get it differently? Like do they have a fixed URL or so?
        // seems we could maybe assume the URL always stays the same except for the date part but then we'd need to check which date works (I've seen older URLs with same strucutre only date different)
        // might be easiest to fetch "details" page and then extract the URL from there?
        // should look like 'https://download.microsoft.com/download/7/1/D/71D86715-5596-4529-9B13-DA13A5DE5B63/ServiceTags_Public_20201207.json'

        $contentDownloadPage = Http::sendHttpRequest('https://www.microsoft.com/en-us/download/details.aspx?id=56519', 120);
        $prefixUrl = 'url":"';
        $prefixStrLen = mb_strlen($prefixUrl, 'UTF-8');
        $posStart = mb_strpos($contentDownloadPage, $prefixUrl . 'https://download.microsoft.com/download/', 0, 'UTF-8');
        $posEnd = mb_strpos($contentDownloadPage, '.json"', $posStart + $prefixStrLen, 'UTF-8'); // we don't want to match the " in url":"
        $contentDownloadPage = mb_substr(
            $contentDownloadPage,
            $posStart + $prefixStrLen,
            $posEnd - $posStart - $prefixStrLen,
            'UTF-8'
        );
        $downloadUrl = trim($contentDownloadPage, '="' . "'") . '.json';
        $downloadUrl = trim($downloadUrl);

        if (strpos($downloadUrl, 'http') !== 0) {
            throw new \Exception('Expected download URL for Azure IP ranges to start with HTTP but it does not. It is: ' . $downloadUrl);
        }

        if (!Common::stringEndsWith($downloadUrl, '.json')) {
            throw new \Exception('Expected download URL for Azure IP ranges to end with ".json" but it does not. It is: ' . $downloadUrl);
        }

        return $downloadUrl;
    }
}
