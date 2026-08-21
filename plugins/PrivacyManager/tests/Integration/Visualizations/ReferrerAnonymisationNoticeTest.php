<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration\Visualizations;

use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugin\Visualization;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\PrivacyManager\ReferrerAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

/**
 * @group PrivacyManager
 */
class ReferrerAnonymisationNoticeTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2024-01-01 00:00:00');
        Fixture::createWebsite('2024-01-01 00:00:00');

        $_GET['idSite'] = '1';
        $_GET['date']   = '2024-01-01';
        $_GET['period'] = 'day';
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['idsite'], $_GET['date'], $_GET['period']);

        parent::tearDown();
    }

    public function testNoticeUsesSiteSpecificConfiguration(): void
    {
        $this->setAnonymisation(ReferrerAnonymizer::EXCLUDE_ALL, 2);

        $_GET['idSite'] = '2';

        $view = $this->buildReferrersView();
        (new PrivacyManager())->onConfigureVisualisation($view);

        self::assertStringContainsString(
            $this->notice(ReferrerAnonymizer::EXCLUDE_ALL),
            (string) $view->config->show_footer_message
        );
    }

    public function testNoticeIgnoresUnrelatedRequestParameters(): void
    {
        $this->setAnonymisation(ReferrerAnonymizer::EXCLUDE_ALL, 2);

        $_GET['idSite'] = '1';
        $_GET['idsite'] = '2';

        $view = $this->buildReferrersView();
        (new PrivacyManager())->onConfigureVisualisation($view);

        self::assertStringNotContainsString(
            $this->notice(ReferrerAnonymizer::EXCLUDE_ALL),
            (string) $view->config->show_footer_message
        );
        self::assertSame('', (string) $view->config->show_footer_message);
    }

    public function testNoticeFallsBackToGlobalConfiguration(): void
    {
        $this->setAnonymisation(ReferrerAnonymizer::EXCLUDE_QUERY, null);

        $view = $this->buildReferrersView();
        (new PrivacyManager())->onConfigureVisualisation($view);

        self::assertStringContainsString(
            $this->notice(ReferrerAnonymizer::EXCLUDE_QUERY),
            (string) $view->config->show_footer_message
        );
    }

    public function testNoticeNotShownWhenAnonymisationDisabled(): void
    {
        $view = $this->buildReferrersView();
        (new PrivacyManager())->onConfigureVisualisation($view);

        self::assertSame('', (string) $view->config->show_footer_message);
    }

    public function testNoticeHandlesMultiSiteRequests(): void
    {
        $this->setAnonymisation(ReferrerAnonymizer::EXCLUDE_PATH, null);

        $view = $this->buildReferrersView();

        $_GET['idSite'] = 'all';

        (new PrivacyManager())->onConfigureVisualisation($view);

        self::assertStringContainsString(
            $this->notice(ReferrerAnonymizer::EXCLUDE_PATH),
            (string) $view->config->show_footer_message
        );
    }

    private function setAnonymisation(string $option, ?int $idSite): void
    {
        $config = new PrivacyManagerConfig($idSite);
        $config->anonymizeReferrer = $option;
    }

    private function notice(string $option): string
    {
        return Piwik::translate(
            'PrivacyManager_InfoSomeReferrerInfoMayBeAnonymized',
            ReferrerAnonymizer::getAvailableAnonymizationOptions()[$option]
        );
    }

    private function buildReferrersView(): Visualization
    {
        /** @var Visualization $view */
        $view = ViewDataTableFactory::build(
            'table',
            'Referrers.getWebsites',
            $controllerAction = 'Referrers.getWebsites',
            $forceDefault = false,
            $loadViewDataTableParametersForUser = false
        );
        $view->setDataTable(new DataTable());

        // guards against a future change to the factory silently turning every case below into a no-op
        self::assertSame('Referrers', $view->requestConfig->getApiModuleToRequest());

        return $view;
    }
}
