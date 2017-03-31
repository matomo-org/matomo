<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider;

use Piwik\Piwik;
use Piwik\Plugins\Live\VisitorDetailsAbstract;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['providerName'] = $this->getProviderName();
        $visitor['providerUrl']  = $this->getProviderUrl();
    }

    protected function getProvider()
    {
        if (isset($this->details['location_provider'])) {
            return $this->details['location_provider'];
        }
        return Piwik::translate('General_Unknown');
    }

    protected function getProviderName()
    {
        return getPrettyProviderName($this->getProvider());
    }

    protected function getProviderUrl()
    {
        return getHostnameUrl(@$this->details['location_provider']);
    }
}