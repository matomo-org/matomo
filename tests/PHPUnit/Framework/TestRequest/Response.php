<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestRequest;

use Piwik\API\Request;
use PHPUnit\Framework\Assert as Asserts;
use Exception;
use Piwik\Tests\Framework\Fixture;

/**
 * Utility class used to obtain and process API responses for API tests.
 * @since 2.8.0
 */
class Response
{
    private $processedResponseText;

    private $params;

    private $requestUrl;

    public function __construct($apiResponse, $params, $requestUrl, $normalize = true)
    {
        $this->params = $params;
        $this->requestUrl = $requestUrl;

        $apiResponse = (string) $apiResponse;
        $this->processedResponseText = $normalize ? $this->normalizeApiResponse($apiResponse) : $apiResponse;
    }

    public function getResponseText()
    {
        return $this->processedResponseText;
    }

    public function save($path)
    {
        file_put_contents($path, $this->processedResponseText);
    }

    public static function loadFromFile($path, $params, $requestUrl)
    {
        $contents = @file_get_contents($path);

        if (empty($contents)) {
            throw new Exception("$path does not exist");
        }

        return new Response($contents, $params, $requestUrl);
    }

    public static function loadFromApi($params, $requestUrl, $normalize = true)
    {
        $requestParams = Request::getRequestArrayFromString($requestUrl);

        $testRequest = new Request($requestParams, []);

        // set the request as root request
        Request::setIsRootRequestApiRequest(Request::getMethodIfApiRequest($requestParams));

        // Cast as string is important. For example when calling
        // with format=original, objects or php arrays can be returned.
        $response = (string) $testRequest->process();

        return new Response($response, $params, $requestUrl, $normalize);
    }

    public static function assertEquals(Response $expected, Response $actual, $message = false)
    {
        $expectedText = $expected->getResponseText();
        $actualText = $actual->getResponseText();

        $expectedText = preg_replace('/[^\x09-\x0d\x1b\x20-\xff]/', '', $expectedText);
        $actualText = preg_replace('/[^\x09-\x0d\x1b\x20-\xff]/', '', $actualText);

        if ($expected->requestUrl['format'] == 'xml') {
            Asserts::assertXmlStringEqualsXmlString($expectedText, $actualText, $message);
            return;
        }

        // check content size to get quick feedback and avoid lengthy diff
        $checkSizeFirst = array('pdf', 'csv', 'html');
        if (
            !empty($expected->requestUrl['reportFormat'])
            && in_array($expected->requestUrl['reportFormat'], $checkSizeFirst)
        ) {
            Asserts::assertEquals(strlen($expectedText), strlen($actualText), $message);
        }

        Asserts::assertEquals($expectedText, $actualText, $message);
    }

    private function normalizeApiResponse($apiResponse)
    {
        $apiResponse = $this->removeSubtableIdsFromXml($apiResponse);
        $apiResponse = $this->removePageViewIds($apiResponse);

        if ($this->shouldDeleteLiveIds()) {
            $apiResponse = $this->removeAllIdsFromXml($apiResponse);
        }

        if ($this->shouldDeleteLiveDates()) {
            $apiResponse = $this->removeAllLiveDatesFromXml($apiResponse);
        } elseif ($this->requestHasNonDeterministicDate()) {
            // If date=lastN the <prettyDate> element will change each day, we remove XML element before comparison

            if ($this->requestUrl['method'] == 'API.getProcessedReport') {
                $apiResponse = $this->removeXmlElement($apiResponse, 'prettyDate');
            }

            $apiResponse = $this->removeXmlElement($apiResponse, 'visitServerHour');

            $regex = "/date=[-0-9,%Ca-z]+/"; // need to remove %2C which is encoded ,
            $apiResponse = preg_replace($regex, 'date=', $apiResponse);
        }

        $apiResponse = $this->normalizePdfContent($apiResponse);
        $apiResponse = $this->removeXmlFields($apiResponse);
        $apiResponse = $this->removeTodaysDate($apiResponse);
        $apiResponse = $this->normalizeDecimalFields($apiResponse);
        $apiResponse = $this->normalizeNumbersOnSqlite($apiResponse);
        $apiResponse = $this->normalizeEncodingPhp533($apiResponse);
        $apiResponse = $this->normalizeSpaces($apiResponse);
        $apiResponse = $this->replacePiwikUrl($apiResponse);

        return $apiResponse;
    }

    private function removeTodaysDate($apiResponse)
    {
        $result = preg_replace('/' . date('Y-m-d') . ' [0-9]{2}:[0-9]{2}:[0-9]{2}/', 'today-date-removed-in-tests', $apiResponse);
        $result = str_replace(date('Y-m-d'), 'today-date-removed-in-tests', $result);
        return $result;
    }

    private function normalizeEncodingPhp533($apiResponse)
    {
        return str_replace('&amp;#039;', "'", $apiResponse);
    }

    private function removePageViewIds($apiResponse)
    {
        $toRemove = array(
            'idpageview',
        );

        return $this->removeXmlFields($apiResponse, $toRemove);
    }

    private function removeAllIdsFromXml($apiResponse)
    {
        $toRemove = array(
            'visitorId',
            'nextVisitorId',
            'previousVisitorId',
            'idvisitor',
        );

        return $this->removeXmlFields($apiResponse, $toRemove);
    }

    private function removeAllLiveDatesFromXml($apiResponse)
    {
        $toRemove = array(
            'serverDate',
            'firstActionTimestamp',
            'lastActionTimestamp',
            'lastActionDateTime',
            'serverTimestamp',
            'serverTimePretty',
            'daysAgo',
            'serverDatePretty',
            'serverDatePrettyFirstAction',
            'serverTimePrettyFirstAction',
            'goalTimePretty',
            'serverTimePretty',
            'visitServerHour',
            'timestamp',
            'date',
            'prettyDate',
            'serverDateTimePrettyFirstAction',
        );
        return $this->removeXmlFields($apiResponse, $toRemove);
    }

    /**
     * Removes content from PDF binary the content that changes with the datetime or other random Ids
     */
    private function normalizePdfContent($response)
    {
        // normalize date markups and document ID in pdf files :
        // - /LastModified (D:20120820204023+00'00')
        // - /CreationDate (D:20120820202226+00'00')
        // - /ModDate (D:20120820202226+00'00')
        // - /M (D:20120820202226+00'00')
        // - /ID [ <0f5cc387dc28c0e13e682197f485fe65> <0f5cc387dc28c0e13e682197f485fe65> ]
        $response = preg_replace('/\(D:[0-9]{14}/', '(D:19700101000000', $response);
        $response = preg_replace('/\/ID \[ <.*> ]/', '', $response);
        $response = preg_replace('/\/id:\[ <.*> ]/', '', $response);

        $response = $this->removeXmlElement($response, "xmp:CreateDate");
        $response = $this->removeXmlElement($response, "xmp:ModifyDate");
        $response = $this->removeXmlElement($response, "xmp:MetadataDate");
        $response = $this->removeXmlElement($response, "xmpMM:DocumentID");
        $response = $this->removeXmlElement($response, "xmpMM:InstanceID");
        return $response;
    }

    private function removeXmlFields($input, $fieldsToRemove = false)
    {
        if ($fieldsToRemove === false) {
            $fieldsToRemove = @$this->params['xmlFieldsToRemove'];
        }

        if (!is_array($fieldsToRemove)) {
            $fieldsToRemove = array();
        }

        foreach ($fieldsToRemove as $xml) {
            $input = $this->removeXmlElement($input, $xml);
        }
        return $input;
    }

    private function removeXmlElement($input, $xmlElement, $testNotSmallAfter = true)
    {
        // Only raise error if there was some data before
        $testNotSmallAfter = strlen($input) > 100 && $testNotSmallAfter;

        $oldInput = $input;
        $input = preg_replace('/(<' . $xmlElement . '>.+?<\/' . $xmlElement . '>)/s', '', $input);
        $input = str_replace('<' . $xmlElement . ' />', '', $input);

        // check we didn't delete the whole string
        if ($testNotSmallAfter && $input != $oldInput) {
            Asserts::assertTrue(strlen($input) > 100, "Removing element $xmlElement from request " . http_build_query($this->requestUrl) . " resulted in a too small value:\n$input");
        }
        return $input;
    }

    private function requestHasNonDeterministicDate()
    {
        if (empty($this->requestUrl['date'])) {
            return false;
        }

        $dateTime = $this->requestUrl['date'];
        return str_contains($dateTime, 'last')
            || str_contains($dateTime, 'today')
            || str_contains($dateTime, 'now');
    }

    private function shouldDeleteLiveIds()
    {
        return empty($this->params['keepLiveIds']);
    }

    private function shouldDeleteLiveDates()
    {
        return empty($this->params['keepLiveDates'])
            && ($this->requestUrl['method'] == 'Live.getLastVisits'
                || $this->requestUrl['method'] == 'Live.getLastVisitsDetails'
                || $this->requestUrl['method'] == 'Live.getVisitorProfile');
    }

    private function normalizeDecimalFields($response)
    {
        // Do not test for TRUNCATE(SUM()) returning .00 on mysqli since this is not working
        // http://bugs.php.net/bug.php?id=54508
        $response = str_replace('.000000</l', '</l', $response); //lat/long
        $response = str_replace('.00</revenue>', '</revenue>', $response);

        // eg. <totalEcommerceRevenue>0.00</totalEcommerceRevenue>
        $response = str_replace('.00</t', '</t', $response);

        return $response;
    }

    /**
     * MySQL pads a DECIMAL to its column's scale (0.0620) and returns some sums as strings ("260");
     * SQLite returns the shortest number (0.062, 260). On SQLite, both sides are compared by value: a
     * decimal loses its trailing zeros, and in JSON a quoted number loses its quotes.
     */
    /**
     * Fields whose values are names or identifiers, compared as they are even when they look like numbers.
     */
    private const SQLITE_UNNORMALIZED_FIELDS = [
        'label', 'sku', 'idorder', 'orderId', 'name', 'title', 'url', 'segment', 'userId', 'visitorId', 'fingerprint',
    ];

    /**
     * On SQLite, API values compare by value: MySQL returns some sums as strings ("260") and pads decimals to their
     * column's scale (12.50), SQLite returns numbers (260, 12.5). Values of the fields above are left as they are.
     */
    private function normalizeNumbersOnSqlite($apiResponse)
    {
        if (!Fixture::isSqlite()) {
            return $apiResponse;
        }

        $number = '-?(?:0|[1-9]\d*)(?:\.\d+)?';
        $isNormalized = function (?string $field) {
            return !in_array($field, self::SQLITE_UNNORMALIZED_FIELDS, true);
        };
        $trimZeros = function ($value) {
            return strpos($value, '.') === false ? $value : rtrim(rtrim($value, '0'), '.');
        };

        if (str_starts_with((string) ($this->requestUrl['format'] ?? ''), 'json')) {
            // "field":"260" and array elements ["260"] become numbers; "field":12.50 becomes 12.5
            return preg_replace_callback(
                '/(?:"((?:[^"\\\\]|\\\\.)*)":|(?<=[\[,]))("?)(' . $number . ')\2(?=[,}\]])/',
                function ($m) use ($isNormalized, $trimZeros) {
                    // no key (null): an array element; an empty key stays a key
                    $prefix = $m[1] !== null ? '"' . $m[1] . '":' : '';
                    return $isNormalized($m[1]) ? $prefix . $trimZeros($m[3]) : $m[0];
                },
                $apiResponse,
                -1,
                $count,
                PREG_UNMATCHED_AS_NULL
            );
        }

        // XML elements: <revenue>12.50</revenue> becomes <revenue>12.5</revenue>
        $apiResponse = preg_replace_callback(
            '/<([\w:-]+)>(' . $number . ')<\/\1>/',
            function ($m) use ($isNormalized, $trimZeros) {
                return $isNormalized($m[1]) ? '<' . $m[1] . '>' . $trimZeros($m[2]) . '</' . $m[1] . '>' : $m[0];
            },
            $apiResponse
        );

        $format = (string) ($this->requestUrl['format'] ?? '');
        if ($format !== 'csv' && $format !== 'tsv') {
            return $apiResponse;
        }

        // CSV/TSV: the header names each column, so labels and identifiers are kept wherever they are
        $delimiter = $format === 'tsv' ? "\t" : ',';
        $lines = preg_split('/(\r?\n)/', $apiResponse, -1, PREG_SPLIT_DELIM_CAPTURE);
        $header = explode($delimiter, preg_replace('/^\xEF\xBB\xBF/', '', (string) $lines[0]));
        for ($i = 2; $i < count($lines); $i += 2) {
            if (strpos($lines[$i], '"') !== false) {
                // a quoted field may hold the delimiter: left as it is
                continue;
            }
            $fields = explode($delimiter, $lines[$i]);
            foreach ($fields as $index => $field) {
                if (preg_match('/^' . $number . '$/', $field) && $isNormalized($header[$index] ?? null)) {
                    $fields[$index] = $trimZeros($field);
                }
            }
            $lines[$i] = implode($delimiter, $fields);
        }

        return implode('', $lines);
    }

    private function normalizeSpaces($apiResponse)
    {
        if (str_starts_with($this->requestUrl['format'], 'json')) {
            $apiResponse = str_replace('&nbsp;', '\u00a0', $apiResponse);
        }

        return $apiResponse;
    }

    private function removeSubtableIdsFromXml($apiResponse)
    {
        return $this->removeXmlFields($apiResponse, array('idsubdatatable_in_db'));
    }

    /**
     * To allow tests to pass no matter what port Piwik is on, we replace the test URL w/ another
     * one in the response. We don't remove the URL outright, because then we would not be able
     * to detect regressions where the root URL went missing.
     *
     * @param $apiResponse
     * @return mixed
     * @throws Exception
     */
    private function replacePiwikUrl($apiResponse)
    {
        $rootUrl = Fixture::getRootUrl();
        $rootUrlRel = str_replace(array('http://', 'https://'), '//', $rootUrl);

        $apiResponse = str_replace($rootUrl, "http://example.com/piwik/", $apiResponse);
        $apiResponse = str_replace($rootUrlRel, "//example.com/piwik/", $apiResponse);
        return $apiResponse;
    }
}
