<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Emails\PluginRequestDecidedEmail;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\Service;
use Piwik\Plugins\Marketplace\UserSettings;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class DecisionEmailTest extends IntegrationTestCase
{
    /**
     * @var list<PluginRequestDecidedEmail>
     */
    private array $sentEmails = [];

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Config::getInstance()->FeatureFlags = ['PluginRequests_feature' => 'enabled'];

        $usersModel = new UsersModel();
        foreach (['alice', 'bob', 'carol'] as $login) {
            $usersModel->addUser($login, 'password', $login . '@example.org', Date::now()->getDatetime());
        }

        Piwik::addAction('Mail.send', function (Mail $mail) {
            if ($mail instanceof PluginRequestDecidedEmail) {
                $this->sentEmails[] = $mail;
            }
        });
    }

    public function testDecliningEmailsEachRequesterOnceWithTheNote(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');
        $this->requestAs('bob', 'PremiumPlugin');
        $this->requestAs('carol', 'OtherPlugin');

        $this->decideAsSuperUser('PremiumPlugin', RequestHistory::DECISION_DECLINED, 'Not this year');

        self::assertSame(['alice@example.org', 'bob@example.org'], $this->getRecipients());
        foreach ($this->sentEmails as $email) {
            self::assertSame('Marketplace_PluginRequestDeclinedEmailSubject', $email->getSubject());
            self::assertStringContainsString('Not this year', $email->getBodyHtml());
        }
    }

    public function testApprovingEmailsTheRequesters(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');

        $this->decideAsSuperUser('PremiumPlugin', RequestHistory::DECISION_APPROVED);

        self::assertSame(['alice@example.org'], $this->getRecipients());
        self::assertSame('Marketplace_PluginRequestApprovedEmailSubject', $this->sentEmails[0]->getSubject());
    }

    public function testActivatingAPluginEmailsItsRequestersAboutTheApproval(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');

        FakeAccess::clearAccess(true);
        (new Service())->cancelRequest('PremiumPlugin');

        self::assertSame(['alice@example.org'], $this->getRecipients());
        self::assertSame('Marketplace_PluginRequestApprovedEmailSubject', $this->sentEmails[0]->getSubject());
    }

    public function testRequestersWhoTurnedTheEmailsOffOrWereDeletedGetNone(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');
        $this->requestAs('bob', 'PremiumPlugin');
        $this->requestAs('carol', 'PremiumPlugin');
        StaticContainer::get(UserScopedSettingsAccessManager::class)
            ->set('Marketplace', 'bob', UserSettings::NOTIFY_ON_PLUGIN_REQUEST_DECISION, false);
        (new UsersModel())->deleteUserOnly('carol');

        $this->decideAsSuperUser('PremiumPlugin', RequestHistory::DECISION_DECLINED);

        self::assertSame(['alice@example.org'], $this->getRecipients());
    }

    public function testNoEmailIsSentWhileTheFeatureFlagIsOff(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');
        Config::getInstance()->FeatureFlags = ['PluginRequests_feature' => 'disabled'];

        FakeAccess::clearAccess(true);
        (new Service())->cancelRequest('PremiumPlugin');

        self::assertSame([], $this->sentEmails);
    }

    /**
     * @return string[]
     */
    private function getRecipients(): array
    {
        $recipients = [];
        foreach ($this->sentEmails as $email) {
            $recipients = array_merge($recipients, array_keys($email->getRecipients()));
        }
        sort($recipients);

        return $recipients;
    }

    private function requestAs(string $login, string $pluginName): void
    {
        FakeAccess::clearAccess(false);
        FakeAccess::$identity = $login;
        (new Service())->request($pluginName, $pluginName);
    }

    private function decideAsSuperUser(string $pluginName, string $decision, ?string $note = null): void
    {
        FakeAccess::clearAccess(true);
        FakeAccess::$identity = FakeAccess::$superUserLogin;
        (new Service())->decide($pluginName, $decision, $note);
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
