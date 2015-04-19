<?php

namespace Piwik\Tracker\Visit;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Tracker\Request;

/**
 * Filters out tracking requests issued by spammers.
 */
class SpamFilter
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

        foreach($spammers as $spammerHost) {
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

        $userFile = StaticContainer::get('path.tmp') . '/spammers.txt';
        if (file_exists($userFile)) {
            $this->spammerList = file($userFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (!is_array($this->spammerList)) {
                throw new \Exception(sprintf('The file %s does not contain a JSON array', $userFile));
            }
        } else {
            // TODO
            $this->spammerList = array(
                '4webmasters.org',
                '7makemoneyonline.com',
            );
        }

        return $this->spammerList;
    }
}
