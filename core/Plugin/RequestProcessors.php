<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\Tracker\BotRequestProcessor;
use Piwik\Tracker\RequestProcessor;

class RequestProcessors
{
    /**
     * @return RequestProcessor[]
     */
    public function getRequestProcessors(): array
    {
        $manager    = Manager::getInstance();
        $processors = $manager->findMultipleComponents('Tracker', 'Piwik\\Tracker\\RequestProcessor');

        $instances = array();
        foreach ($processors as $processor) {
            $instances[] = StaticContainer::get($processor);
        }

        return $instances;
    }

    /**
     * @return BotRequestProcessor[]
     */
    public function getBotRequestProcessors(): array
    {
        $manager    = Manager::getInstance();
        $processors = $manager->findMultipleComponents('Tracker', 'Piwik\\Tracker\\BotRequestProcessor');

        $instances = [];
        foreach ($processors as $processor) {
            $instances[] = StaticContainer::get($processor);
        }

        return $instances;
    }
}
