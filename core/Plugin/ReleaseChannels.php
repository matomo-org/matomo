<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\UpdateCheck\ReleaseChannel;

/**
 * Get release channels that are defined by plugins.
 */
class ReleaseChannels
{
    /**
     * @var Manager
     */
    private $pluginManager;

    public function __construct(Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * @return ReleaseChannel[]
     */
    public function getAllReleaseChannels()
    {
        $classNames = $this->pluginManager->findMultipleComponents('ReleaseChannel', 'Piwik\\UpdateCheck\\ReleaseChannel');
        $channels = array();

        foreach ($classNames as $className) {
            $channels[] = StaticContainer::get($className);
        }

        usort($channels, function (ReleaseChannel $a, ReleaseChannel $b) {
            if ($a->getOrder() === $b->getOrder()) {
                return 0;
            }

            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        return $channels;
    }

    /**
     * @return ReleaseChannel
     */
    public function getActiveReleaseChannel()
    {
        $channel = Config::getInstance()->General['release_channel'];
        $channel = $this->factory($channel);

        if (!empty($channel)) {
            return $channel;
        }

        $channels = $this->getAllReleaseChannels();

        // we default to the one with lowest id
        return reset($channels);
    }

    /**
     * Sets the given release channel in config but does not save id. $config->forceSave() still needs to be called
     * @param string $channel
     */
    public function setActiveReleaseChannelId($channel)
    {
        $general = Config::getInstance()->General;
        $general['release_channel'] = $channel;
        Config::getInstance()->General = $general;
    }

    public function isValidReleaseChannelId($releaseChannelId)
    {
        $channel = $this->factory($releaseChannelId);

        return !empty($channel);
    }

    /**
     * @param string $releaseChannelId
     * @return ReleaseChannel
     */
    private function factory($releaseChannelId)
    {
        $releaseChannelId = strtolower($releaseChannelId);

        foreach ($this->getAllReleaseChannels() as $releaseChannel) {
            if ($releaseChannelId === strtolower($releaseChannel->getId())) {
                return $releaseChannel;
            }
        }
    }
}