<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\Emails;

use Piwik\Plugins\Marketplace\Emails\PluginRequestDecidedEmail;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class PluginRequestDecidedEmailTest extends IntegrationTestCase
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

    public function testADeclinedRequestShowsTheSuperUsersNoteEscaped(): void
    {
        $email = new PluginRequestDecidedEmail(
            'some.user',
            'some.user@example.org',
            'PremiumPlugin',
            'Premium Plugin',
            RequestHistory::DECISION_DECLINED,
            "Not this year <b>sorry</b>\nAsk again in January"
        );
        $body = $email->getBodyHtml();

        self::assertSame('Your request for the Premium Plugin plugin was declined', $email->getSubject());
        self::assertStringContainsString('Your request for the Premium Plugin plugin was declined.', $body);
        self::assertStringContainsString('The Super User added this message:', $body);
        self::assertStringContainsString('Not this year &lt;b&gt;sorry&lt;/b&gt;<br />', $body);
        self::assertStringContainsString('index.php?module=UsersManager&action=userSettings', html_entity_decode($body));
        self::assertStringContainsString('>Unsubscribe</a> from emails about your plugin requests in your personal settings.', $body);
    }

    public function testADeclinedRequestWithoutANoteShowsNoMessage(): void
    {
        $email = new PluginRequestDecidedEmail('some.user', 'some.user@example.org', 'PremiumPlugin', 'Premium Plugin', RequestHistory::DECISION_DECLINED);

        self::assertStringNotContainsString('The Super User added this message:', $email->getBodyHtml());
    }

    public function testAnApprovedRequestSaysSo(): void
    {
        $email = new PluginRequestDecidedEmail('some.user', 'some.user@example.org', 'PremiumPlugin', 'Premium Plugin', RequestHistory::DECISION_APPROVED, 'Ignored');
        $body = $email->getBodyHtml();

        self::assertSame('Your request for the Premium Plugin plugin was approved', $email->getSubject());
        self::assertStringContainsString('Good news: your request for the Premium Plugin plugin was approved.', $body);
        self::assertStringNotContainsString('Ignored', $body);
    }
}
