<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugin\ReleaseChannels;
use Piwik\Plugins\CoreUpdater\ReleaseChannel;
use Piwik\Version;

class Environment
{
    public const OPTION_MARKETPLACE_UNIQUE_ID = 'Marketplace.unique_id';

    /**
     * @var ReleaseChannel
     */
    private $releaseChannel;

    private $usersCache = null;
    private $websitesCache = null;
    private $mySqlCache = null;
    private $piwikVersion = null;

    public function __construct(ReleaseChannels $releaseChannels)
    {
        $this->releaseChannel = $releaseChannels->getActiveReleaseChannel();
    }

    public function setPiwikVersion($piwikVersion)
    {
        $this->piwikVersion = $piwikVersion;
    }

    public function getNumUsers()
    {
        if (!isset($this->usersCache)) {
            $this->usersCache = (int)Db::get()->fetchOne('SELECT count(login) FROM `' . Common::prefixTable('user') . '` WHERE login <> "anonymous" ');
        }

        return $this->usersCache;
    }

    public function getNumWebsites()
    {
        if (!isset($this->websitesCache)) {
            $this->websitesCache = (int)Db::get()->fetchOne('SELECT count(idsite) FROM `' . Common::prefixTable('site') . '`');
        }

        return $this->websitesCache;
    }

    public function getPhpVersion()
    {
        return PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
    }

    public function getPiwikVersion()
    {
        if (!empty($this->piwikVersion)) {
            return $this->piwikVersion;
        }

        return Version::VERSION;
    }

    public function doesPreferStable()
    {
        if (!empty($this->releaseChannel)) {
            return $this->releaseChannel->doesPreferStable();
        }

        return true;
    }

    public function getReleaseChannel()
    {
        if (!empty($this->releaseChannel)) {
            return $this->releaseChannel->getId();
        }
    }

    /**
     * Returns a unique, stable and anonymous identifier for this Matomo installation.
     *
     * @return string
     */
    public function getUniqueId()
    {
        $uniqueId = (string) Option::get(self::OPTION_MARKETPLACE_UNIQUE_ID);

        if (empty($uniqueId)) {
            $uniqueId = hash(
                'sha256',
                Common::generateUniqId() . Common::getRandomString(40)
            );
            Option::set(Environment::OPTION_MARKETPLACE_UNIQUE_ID, $uniqueId);
        }

        return $uniqueId;
    }

    public function getMySQLVersion()
    {
        if (isset($this->mySqlCache)) {
            return $this->mySqlCache;
        }

        $this->mySqlCache = '';

        $db = Db::get();
        if (method_exists($db, 'getServerVersion')) {
            $this->mySqlCache = $db->getServerVersion();
        }

        return $this->mySqlCache;
    }
}
