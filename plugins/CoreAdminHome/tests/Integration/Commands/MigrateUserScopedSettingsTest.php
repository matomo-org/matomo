<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Option;
use Piwik\Plugins\CoreAdminHome\Commands\MigrateUserScopedSettings;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Settings\Storage\Factory;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_Integration
 */
class MigrateUserScopedSettingsTest extends IntegrationTestCase
{
    public function testMigrateMovesKnownUsersToPluginSettingsAndPurgesLegacyOptions(): void
    {
        $usersModel = new UsersModel();
        $store = new UserScopedSettingsAccessManager();
        $activeUser = 'activeMigrUser';
        $activeUserWithDot = 'active.migr.user';
        $deletedUser = 'deletedMigrUser';

        $usersModel->addUser($activeUser, 'hashed-password', 'active@example.com', '2020-01-01 00:00:00');
        $usersModel->addUser($activeUserWithDot, 'hashed-password', 'active.dot@example.com', '2020-01-01 00:00:00');
        $usersModel->addUser($deletedUser, 'hashed-password', 'deleted@example.com', '2020-01-01 00:00:00');
        $usersModel->deleteUser($deletedUser);

        Option::set($activeUser . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION, '{"PhoneNumbers":{"123":{"verified":true}}}');
        Option::set($activeUserWithDot . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION, '{"PhoneNumbers":{"789":{"verified":true}}}');
        Option::set($deletedUser . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION, '{"PhoneNumbers":{"456":{"verified":true}}}');
        Option::set(MobileMessaging::USER_SETTINGS_POSTFIX_OPTION, '{"Provider":"StubbedProvider"}');

        Option::set('Feedback.nextFeedbackReminder.' . $activeUser, '2030-01-01');
        Option::set('Feedback.nextFeedbackReminder.' . $activeUserWithDot, '2030-01-15');
        Option::set('Feedback.nextFeedbackReminder.' . $deletedUser, '2030-02-01');

        Option::set('ProfessionalServices.DismissedWidget.ActiveWidget.' . $activeUser, 12345);
        Option::set('ProfessionalServices.DismissedWidget.DotWidget.' . $activeUserWithDot, 24680);
        Option::set('ProfessionalServices.DismissedWidget.DeletedWidget.' . $deletedUser, 67890);

        Option::set($activeUser . '_defaultReport', '7');
        Option::set($activeUserWithDot . '_defaultReport', '9');
        Option::set($deletedUser . '_defaultReport', '8');
        Option::set($activeUser . '_isLDAPUser', '1');
        Option::set($activeUserWithDot . '_isLDAPUser', '1');
        Option::set($deletedUser . '_isLDAPUser', '1');

        $result = MigrateUserScopedSettings::migrate();

        $this->assertSame(3, $result['mobileMessaging']);
        $this->assertSame(2, $result['feedback']);
        $this->assertSame(2, $result['professionalServices']);
        $this->assertSame(4, $result['usersManagerPreferences']);

        $this->assertSame(['PhoneNumbers' => ['123' => ['verified' => true]]], $store->getAll('MobileMessaging', $activeUser));
        $this->assertSame(['PhoneNumbers' => ['789' => ['verified' => true]]], $store->getAll('MobileMessaging', $activeUserWithDot));
        $this->assertSame([], $store->getAll('MobileMessaging', $deletedUser));
        $globalSettings = (new Factory())->getPluginStorage('MobileMessaging', '')->getBackend()->load();
        $this->assertSame(['Provider' => 'StubbedProvider'], $globalSettings);

        $this->assertSame('2030-01-01', $store->get('Feedback', $activeUser, 'nextFeedbackReminder', false));
        $this->assertSame('2030-01-15', $store->get('Feedback', $activeUserWithDot, 'nextFeedbackReminder', false));
        $this->assertFalse($store->get('Feedback', $deletedUser, 'nextFeedbackReminder', false));

        $this->assertSame(['ActiveWidget' => 12345], $store->get('ProfessionalServices', $activeUser, 'dismissedWidgets', []));
        $this->assertSame(['DotWidget' => 24680], $store->get('ProfessionalServices', $activeUserWithDot, 'dismissedWidgets', []));
        $this->assertSame([], $store->get('ProfessionalServices', $deletedUser, 'dismissedWidgets', []));

        $this->assertSame('7', $store->get('UsersManager', $activeUser, 'defaultReport', false));
        $this->assertSame('1', $store->get('UsersManager', $activeUser, 'isLDAPUser', false));
        $this->assertSame('9', $store->get('UsersManager', $activeUserWithDot, 'defaultReport', false));
        $this->assertSame('1', $store->get('UsersManager', $activeUserWithDot, 'isLDAPUser', false));
        $this->assertFalse($store->get('UsersManager', $deletedUser, 'defaultReport', false));
        $this->assertFalse($store->get('UsersManager', $deletedUser, 'isLDAPUser', false));

        $this->assertFalse(Option::get($activeUser . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION));
        $this->assertFalse(Option::get($activeUserWithDot . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION));
        $this->assertFalse(Option::get($deletedUser . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION));
        $this->assertFalse(Option::get(MobileMessaging::USER_SETTINGS_POSTFIX_OPTION));
        $this->assertFalse(Option::get('Feedback.nextFeedbackReminder.' . $activeUser));
        $this->assertFalse(Option::get('Feedback.nextFeedbackReminder.' . $activeUserWithDot));
        $this->assertFalse(Option::get('Feedback.nextFeedbackReminder.' . $deletedUser));
        $this->assertFalse(Option::get('ProfessionalServices.DismissedWidget.ActiveWidget.' . $activeUser));
        $this->assertFalse(Option::get('ProfessionalServices.DismissedWidget.DotWidget.' . $activeUserWithDot));
        $this->assertFalse(Option::get('ProfessionalServices.DismissedWidget.DeletedWidget.' . $deletedUser));
        $this->assertFalse(Option::get($activeUser . '_defaultReport'));
        $this->assertFalse(Option::get($activeUserWithDot . '_defaultReport'));
        $this->assertFalse(Option::get($deletedUser . '_defaultReport'));
        $this->assertSame('1', Option::get($activeUser . '_isLDAPUser'));
        $this->assertSame('1', Option::get($activeUserWithDot . '_isLDAPUser'));
        $this->assertFalse(Option::get($deletedUser . '_isLDAPUser'));
    }
}
