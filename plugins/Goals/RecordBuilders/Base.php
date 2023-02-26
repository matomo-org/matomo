<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Plugin\Manager;
use Piwik\Site;
use Piwik\Tracker\GoalManager;

abstract class Base extends RecordBuilder
{
    protected function getSiteId(ArchiveProcessor $archiveProcessor)
    {
        return $archiveProcessor->getParams()->getSite()->getId();
    }

    protected function usesEcommerce($idSite)
    {
        return Manager::getInstance()->isPluginActivated('Ecommerce')
            && Site::isEcommerceEnabledFor($idSite);
    }

    protected function getEcommerceIdGoals()
    {
        return array(GoalManager::IDGOAL_CART, GoalManager::IDGOAL_ORDER);
    }
}