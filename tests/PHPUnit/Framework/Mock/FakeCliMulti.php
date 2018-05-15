<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework\Mock;

use Piwik\Archiver\Request;
use Piwik\CliMulti;

class FakeCliMulti extends CliMulti
{
    public static $specifiedResults = null;

    public function request(array $piwikUrls)
    {
        if (empty(FakeCliMulti::$specifiedResults)) {
            return parent::request($piwikUrls);
        }

        $results = array();
        foreach ($piwikUrls as $url) {
            if ($url instanceof Request) {
                $url->start();

                $url = (string)$url;
            }

            $results[] = $this->getSpecifiedResult($url);
        }
        return $results;
    }

    private function getSpecifiedResult($url)
    {
        foreach (FakeCliMulti::$specifiedResults as $pattern => $result) {
            if (substr($pattern, 0, 1) == '/'
                && substr($pattern, strlen($pattern) - 1, 1) == '/'
            ) {
                $isMatch = preg_match($pattern, $url);
            } else {
                $isMatch = $pattern == $url;
            }

            if (!$isMatch) {
                continue;
            }

            if (is_callable($result)) {
                return $result($url);
            } else {
                return $result;
            }
        }
        return null;
    }
}