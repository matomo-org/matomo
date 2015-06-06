<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker;

use Piwik\Application\Application;
use Piwik\Application\Environment;
use Piwik\SettingsServer;
use Piwik\Tracker;

/**
 * Application entry point for Piwik tracking. Can be used to track one or more requests.
 *
 * Since this is an application, it will create and configure a new Piwik environment, so construction will
 * be slow. Additionally, creating applications will take up more memory than alternatives. Thus,
 * using Application classes in places other than Piwik entry points should be avoided.
 */
class TrackerApplication extends Application
{
    public function __construct()
    {
        SettingsServer::setIsTrackerApiRequest(); // TODO: shouldn't affect global state in a class, but not sure how to change this yet

        parent::__construct(new Environment('tracker'));

        Tracker::loadTrackerEnvironment(); // TODO: should remove this eventually
    }

    /**
     * @param array|RequestSet|null $requests
     * @return string
     * @throws \Exception
     */
    public function track($requests = null)
    {
        $container = $this->getEnvironment()->getContainer();

        /** @var RequestSet $requestSet */
        $requestSet = null;
        if (empty($requests)) {
            $requestSet = $container->make('Piwik\Tracker\RequestSet');
        } else if ($requests instanceof RequestSet) {
            $requestSet = $requests;
        } else if (is_array($requests)) {
            $requestSet = $container->make('Piwik\Tracker\RequestSet');
            $requestSet->setRequests($requests);
        } else {
            throw new \InvalidArgumentException("Invalid argument '\$requests' supplied to TrackerApplication::track().");
        }

        /** @var Tracker $tracker */
        $tracker = $this->getEnvironment()->getContainer()->get('Piwik\Tracker');
        $handler = Handler\Factory::make(); // TODO: create from DI

        return $tracker->main($handler, $requestSet);
    }
}