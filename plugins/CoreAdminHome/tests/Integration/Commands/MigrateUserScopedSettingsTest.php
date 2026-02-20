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
use Piwik\Settings\Storage\UserScopedSettingsStore;
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
        $store = new UserScopedSettingsStore();
        $activeUser = 'activeMigrUser';
        $deletedUser = 'deletedMigrUser';

        $usersModel->addUser($activeUser, 'hashed-password', 'active@example.com', '2020-01-01 00:00:00');
        $usersModel->addUser($deletedUser, 'hashed-password', 'deleted@example.com', '2020-01-01 00:00:00');
        $usersModel->deleteUser($deletedUser);

        Option::set($activeUser . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION, '{"PhoneNumbers":{"123":{"verified":true}}}');
        Option::set($deletedUser . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION, '{"PhoneNumbers":{"456":{"verified":true}}}');

        Option::set('Feedback.nextFeedbackReminder.' . $activeUser, '2030-01-01');
        Option::set('Feedback.nextFeedbackReminder.' . $deletedUser, '2030-02-01');

        Option::set('ProfessionalServices.DismissedWidget.ActiveWidget.' . $activeUser, 12345);
        Option::set('ProfessionalServices.DismissedWidget.DeletedWidget.' . $deletedUser, 67890);

        Option::set($activeUser . '_defaultReport', '7');
        Option::set($deletedUser . '_defaultReport', '8');

        $result = MigrateUserScopedSettings::migrate();

        $this->assertSame(1, $result['mobileMessaging']);
        $this->assertSame(1, $result['feedback']);
        $this->assertSame(1, $result['professionalServices']);
        $this->assertSame(1, $result['usersManagerPreferences']);

        $this->assertSame(['PhoneNumbers' => ['123' => ['verified' => true]]], $store->getAll('MobileMessaging', $activeUser));
        $this->assertSame([], $store->getAll('MobileMessaging', $deletedUser));

        $this->assertSame('2030-01-01', $store->get('Feedback', $activeUser, 'nextFeedbackReminder', false));
        $this->assertFalse($store->get('Feedback', $deletedUser, 'nextFeedbackReminder', false));

        $this->assertSame(['ActiveWidget' => 12345], $store->get('ProfessionalServices', $activeUser, 'dismissedWidgets', []));
        $this->assertSame([], $store->get('ProfessionalServices', $deletedUser, 'dismissedWidgets', []));

        $this->assertSame('7', $store->get('UsersManager', $activeUser, 'defaultReport', false));
        $this->assertFalse($store->get('UsersManager', $deletedUser, 'defaultReport', false));

        $this->assertFalse(Option::get($activeUser . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION));
        $this->assertFalse(Option::get($deletedUser . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION));
        $this->assertFalse(Option::get('Feedback.nextFeedbackReminder.' . $activeUser));
        $this->assertFalse(Option::get('Feedback.nextFeedbackReminder.' . $deletedUser));
        $this->assertFalse(Option::get('ProfessionalServices.DismissedWidget.ActiveWidget.' . $activeUser));
        $this->assertFalse(Option::get('ProfessionalServices.DismissedWidget.DeletedWidget.' . $deletedUser));
        $this->assertFalse(Option::get($activeUser . '_defaultReport'));
        $this->assertFalse(Option::get($deletedUser . '_defaultReport'));
    }
}
