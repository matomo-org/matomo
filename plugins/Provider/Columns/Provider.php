<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider\Columns;

use Piwik\Common;
use Piwik\Network\IP;
use Piwik\Network\IPUtils;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\Provider\Provider as ProviderPlugin;

class Provider extends VisitDimension
{
    protected $columnName = 'location_provider';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('provider');
        $segment->setCategory('Visit Location');
        $segment->setName('Provider_ColumnProvider');
        $segment->setAcceptedValues('comcast.net, proxad.net, etc.');
        $this->addSegment($segment);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        // Adding &dp=1 will disable the provider plugin, this is an "unofficial" parameter used to speed up log importer
        $disableProvider = $request->getParam('dp');

        if (!empty($disableProvider)) {
            return false;
        }

        // if provider info has already been set, abort
        $locationValue = $visitor->getVisitorColumn('location_provider');
        if (!empty($locationValue)) {
            return false;
        }

        $ip = $visitor->getVisitorColumn('location_ip');

        $privacyConfig = new PrivacyManagerConfig();
        if (!$privacyConfig->useAnonymizedIpForVisitEnrichment) {
            $ip = $request->getIp();
        }

        $ip = IPUtils::binaryToStringIP($ip);

        // In case the IP was anonymized, we should not continue since the DNS reverse lookup will fail and this will slow down tracking
        if (substr($ip, -2, 2) == '.0') {
            Common::printDebug("IP Was anonymized so we skip the Provider DNS reverse lookup...");
            return false;
        }

        $hostname = $this->getHost($ip);
        $hostnameExtension = ProviderPlugin::getCleanHostname($hostname);

        // add the provider value in the table log_visit
        $locationProvider = substr($hostnameExtension, 0, 100);

        return $locationProvider;
    }

    public function getRequiredVisitFields()
    {
        return array('location_ip');
    }

    /**
     * Returns the hostname given the IP address string
     *
     * @param string $ipStr IP Address
     * @return string hostname (or human-readable IP address)
     */
    private function getHost($ipStr)
    {
        $ip = IP::fromStringIP($ipStr);

        $host = $ip->getHostname();
        $host = ($host === null ? $ipStr : $host);

        return trim(strtolower($host));
    }

    public function getName()
    {
        return Piwik::translate('Provider_ColumnProvider');
    }
}