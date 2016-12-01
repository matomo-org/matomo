<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Marketplace\Plugins;

use Piwik\Cache;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\Plugins;
use Piwik\Translation\Translator;
use Piwik\Url;

/**
 *
 */
class InvalidLicenses
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Cache\Eager
     */
    private $cache;

    /**
     * @var array
     */
    private $activatedPluginNames = array();

    private $plugins;

    private $cacheKey = 'Marketplace_ExpiredPlugins';

    public function __construct(Client $client, Cache\Eager $cache, Translator $translator, Plugins $plugins)
    {
        $this->client = $client;
        $this->translator = $translator;
        $this->pluginManager = Plugin\Manager::getInstance();
        $this->cache = $cache;
        $this->plugins = $plugins;
    }

    public function getPluginNamesOfInvalidLicenses()
    {
        // it is very important this is cached, otherwise performance may decrease a lot. Eager cache is currently
        // cached for 12 hours. In case we lower ttl for eager cache it might be worth considering to change to another
        // cache
        if ($this->cache->contains($this->cacheKey)) {
            $expiredPlugins = $this->cache->fetch($this->cacheKey);
        } else {
            $expiredPlugins = $this->getPluginNamesToExpireInCaseLicenseIsInvalid();
            $this->cache->save($this->cacheKey, $expiredPlugins);
        }

        return $expiredPlugins;
    }

    public function clearCache()
    {
        $this->cache->delete($this->cacheKey);
    }

    public function getMessageExceededLicenses()
    {
        $plugins = $this->getPluginNamesOfInvalidLicenses();

        if (empty($plugins['exceeded'])) {
            return;
        }

        $plugins = '<strong>' . implode('</strong>, <strong>', $plugins['exceeded']) . '</strong>';
        $loginUrl = $this->getLoginLink();
        $loginUrlEnd = '';
        if (!empty($loginUrl)) {
            $loginUrlEnd = '</a>';
        }

        $message = $this->translator->translate('Marketplace_LicenseExceededDescription', array($plugins, '<br/>', "<strong>" . $loginUrl, $loginUrlEnd . "</strong>"));

        if (Piwik::hasUserSuperUserAccess()) {
            $message .= ' ' . $this->getSubscritionSummaryMessage();
        }

        return $message;
    }

    public function getMessageNoLicense()
    {
        $plugins = $this->getPluginNamesOfInvalidLicenses();

        if (empty($plugins['noLicense'])) {
            return;
        }

        $plugins = '<strong>' . implode('</strong>, <strong>', $plugins['noLicense']) . '</strong>';
        $loginUrl = $this->getLoginLink();
        $loginUrlEnd = '';
        if (!empty($loginUrl)) {
            $loginUrlEnd = '</a>';
        }

        $message = $this->translator->translate('Marketplace_LicenseMissingDescription', array($plugins, '<br/>', "<strong>" . $loginUrl, $loginUrlEnd. "</strong>"));

        if (Piwik::hasUserSuperUserAccess()) {
            $message .= ' ' . $this->getSubscritionSummaryMessage();
        }

        return $message;
    }

    public function getMessageExpiredLicenses()
    {
        $plugins = $this->getPluginNamesOfInvalidLicenses();

        if (empty($plugins['expired'])) {
            return;
        }

        $plugins = '<strong>' . implode('</strong>, <strong>', $plugins['expired']) . '</strong>';
        $loginUrl = $this->getLoginLink();
        $loginUrlEnd = '';
        if (!empty($loginUrl)) {
            $loginUrlEnd = '</a>';
        }

        $message = $this->translator->translate('Marketplace_LicenseExpiredDescription', array($plugins, '<br/>', "<strong>" . $loginUrl, $loginUrlEnd . "</strong>"));

        if (Piwik::hasUserSuperUserAccess()) {
            $message .= ' ' . $this->getSubscritionSummaryMessage();
        }

        return $message;
    }

    private function getLoginLink()
    {
        $info = $this->client->getInfo();

        if (empty($info['loginUrl'])) {
            return '';
        }

        return '<a href="' . $info['loginUrl'] . '" target="_blank" rel="noreferrer">';
    }

    private function getSubscritionSummaryMessage()
    {
        $url = Url::getCurrentQueryStringWithParametersModified(array(
            'module' => 'Marketplace', 'action' => 'subscriptionOverview'
        ));

        $link = '<a href="' . $url . '">';

        return "<br/>" .  $this->translator->translate('Marketplace_ViewSubscriptionsSummary', array($link, '</a>'));
    }

    private function getPluginNamesToExpireInCaseLicenseIsInvalid()
    {
        $pluginNames = array(
            'exceeded' => array(),
            'expired' => array(),
            'noLicense' => array()
        );

        try {
            $paidPlugins = $this->plugins->getAllPaidPlugins();
        } catch (\Exception $e) {
            return $pluginNames;
        }

        if (!empty($paidPlugins)) {
            foreach ($paidPlugins as $plugin) {
                if (!empty($plugin['isFree'])) {
                    continue;
                }
                $pluginName = $plugin['name'];
                if ($this->isPluginActivated($pluginName)) {
                    if (empty($plugin['consumer']['license'])) {
                        $pluginNames['noLicense'][] = $pluginName;
                    } elseif (!empty($plugin['consumer']['license']['isExceeded'])) {
                        $pluginNames['exceeded'][] = $pluginName;
                    } elseif (isset($plugin['consumer']['license']['isValid'])
                           && empty($plugin['consumer']['license']['isValid'])) {
                        $pluginNames['expired'][] = $pluginName;
                    }
                }
            }
        }

        return $pluginNames;
    }

    /**
     * for tests only
     * @param array $pluginNames
     * @internal
     * @ignore
     */
    public function setActivatedPluginNames($pluginNames)
    {
        $this->activatedPluginNames = $pluginNames;
    }

    protected function isPluginInstalled($pluginName)
    {
        if (in_array($pluginName, $this->activatedPluginNames)) {
            return true;
        }

        return $this->pluginManager->isPluginInstalled($pluginName);
    }

    protected function isPluginActivated($pluginName)
    {
        if (in_array($pluginName, $this->activatedPluginNames)) {
            return true;
        }

        return $this->pluginManager->isPluginActivated($pluginName);
    }

}
