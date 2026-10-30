<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\Emails;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Email\ContentGenerator;
use Piwik\Plugins\Marketplace\Emails\RequestTrialNotificationEmail;
use Piwik\Plugins\Marketplace\PluginTrial\UnsubscribeToken;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\View;
use Piwik\View\HtmlReportEmailHeaderView;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class RequestTrialNotificationEmailTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testBodyLinksToTheMarketplaceUnsubscribePageForTheRecipient()
    {
        $email = new RequestTrialNotificationEmail('some.user', 'some.user@example.org', 'PremiumPlugin', 'Premium Plugin', 'abc123');
        $body = html_entity_decode($email->getBodyHtml());

        self::assertStringContainsString(
            'index.php?module=Marketplace&action=unsubscribeTrialRequestEmails&login=some.user&token=abc123',
            $body
        );
        self::assertStringContainsString('To unsubscribe from plugin request email notifications, please follow this link:', $body);
        self::assertStringNotContainsString('To unsubscribe from this report', $body);
    }

    public function testBodySignsTheUnsubscribeLinkWhenNoTokenIsPassed()
    {
        (new UsersModel())->addUser('some.user', 'password', 'some.user@example.org', Date::now()->getDatetime());
        $email = new RequestTrialNotificationEmail('some.user', 'some.user@example.org', 'PremiumPlugin', 'Premium Plugin');

        $token = StaticContainer::get(UnsubscribeToken::class)->generate('some.user');
        self::assertNotNull($token);
        self::assertStringContainsString('login=some.user&token=' . $token, html_entity_decode($email->getBodyHtml()));
    }

    public function testBodyLeavesOutTheUnsubscribeLineWhenTheUserDoesNotExist()
    {
        $email = new RequestTrialNotificationEmail('missing.user', 'missing.user@example.org', 'PremiumPlugin', 'Premium Plugin');
        $body = html_entity_decode($email->getBodyHtml());

        self::assertStringNotContainsString('unsubscribeTrialRequestEmails', $body);
        self::assertStringNotContainsString('To unsubscribe from plugin request email notifications', $body);
    }

    public function testScheduledReportFooterKeepsTheReportWording()
    {
        $footer = new View('@CoreHome/ReportRenderer/_htmlReportFooter.twig');
        HtmlReportEmailHeaderView::assignCommonParameters($footer);
        $footer->unsubscribeLink = 'https://example.org/unsubscribe';

        self::assertStringContainsString('To unsubscribe from this report please follow this link:', $footer->render());
    }

    public function testDefaultBodyHasNoUnsubscribeLine()
    {
        $body = StaticContainer::get(ContentGenerator::class)->generateHtmlContent('body');

        self::assertStringNotContainsString('unsubscribe', strtolower($body));
    }
}
