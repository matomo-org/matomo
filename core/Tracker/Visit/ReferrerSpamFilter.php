<?php

namespace Piwik\Tracker\Visit;

use Piwik\Common;
use Piwik\Tracker\Request;

/**
 * Filters out tracking requests issued by spammers.
 */
class ReferrerSpamFilter
{
    /**
     * @var string[]
     */
    private $spammerList;

    /**
     * Check if the request is from a known spammer host.
     *
     * @param Request $request
     * @return bool
     */
    public function isSpam(Request $request)
    {
        $spammers = $this->loadSpammerList();

        $referrerUrl = $request->getParam('urlref');

        foreach ($spammers as $spammerHost) {
            if (strpos($referrerUrl, $spammerHost) !== false) {
                Common::printDebug('Referrer URL is a known spam: ' . $spammerHost);
                return true;
            }
        }

        return false;
    }

    private function loadSpammerList()
    {
        if ($this->spammerList !== null) {
            return $this->spammerList;
        }

        $file = PIWIK_INCLUDE_PATH . '/vendor/piwik/referrer-spam-blacklist/spammers.txt';
        $this->spammerList = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return $this->spammerList;
    }
}
