<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker\Visit;

use Piwik\Cache;
use Piwik\Common;
use Piwik\Option;
use Piwik\Tracker\Request;

/**
 * Filters out tracking requests issued by spammers.
 */
class ReferrerSpamFilter
{
    const OPTION_STORAGE_NAME = 'referrer_spam_blacklist';
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
        $spammers = $this->getSpammerListFromCache();

        $referrerUrl = $request->getParam('urlref');

        foreach ($spammers as $spammerHost) {
            if (stripos($referrerUrl, $spammerHost) !== false) {
                Common::printDebug('Referrer URL is a known spam: ' . $spammerHost);
                return true;
            }
        }

        return false;
    }

    private function getSpammerListFromCache()
    {
        $cache = Cache::getEagerCache();
        $cacheId = 'ReferrerSpamFilter-' . self::OPTION_STORAGE_NAME;

        if ($cache->contains($cacheId)) {
            $list = $cache->fetch($cacheId);
        } else {
            $list = $this->loadSpammerList();
            $cache->save($cacheId, $list);
        }

        if(!is_array($list)) {
            Common::printDebug('Warning: could not read list of spammers from cache.');
            return array();
        }
        return $list;
    }

    private function loadSpammerList()
    {
        if ($this->spammerList !== null) {
            return $this->spammerList;
        }

        // Read first from the auto-updated list in database
        $list = Option::get(self::OPTION_STORAGE_NAME);

        if ($list) {
            $this->spammerList = Common::safe_unserialize($list);
        } else {
            // Fallback to reading the bundled list
            $file = PIWIK_VENDOR_PATH . '/matomo/referrer-spam-list/spammers.txt';
            $this->spammerList = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }

        return $this->spammerList;
    }
}
