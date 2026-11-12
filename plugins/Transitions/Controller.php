<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\Transitions\PreprocessedPages\RebuildWindow;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
use Piwik\Request;
use Piwik\Site;

class Controller extends ControllerAdmin
{
    /**
     * Admin page for the pages of a website whose Transitions report is pre-processed. The list itself is loaded and
     * changed through the API; the page only gets the website's limit and rebuild window.
     */
    public function manage(): string
    {
        $idSite = Request::fromRequest()->getIntegerParameter('idSite');
        Piwik::checkUserHasAdminAccess($idSite);
        new Site($idSite);

        return $this->renderTemplate('manage', [
            'maxPages' => StaticContainer::get(Service::class)->getMaxPages($idSite),
            'rearchiveMonths' => RebuildWindow::getMonthsProcessed(),
        ]);
    }
}
