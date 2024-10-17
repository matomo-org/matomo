<?php

namespace Piwik\Plugins\UsersManager\Repository;

use Piwik\Auth\Password;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\CoreAdminHome\Emails\UserCreatedEmail;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Emails\UserInviteEmail;
use Piwik\Plugins\UsersManager\LastSeenTimeLogger;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UserAccessFilter;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Plugins\UsersManager\Validators\AllowedEmailDomain;
use Piwik\Plugins\UsersManager\Validators\Email;
use Piwik\Plugins\UsersManager\Validators\Login;
use Piwik\Site;
use Piwik\Validators\BaseValidator;

class UserRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var UserAccessFilter
     */
    protected $filter;

    /**
     * @var Password
     */
    protected $password;

    /**
     * @var AllowedEmailDomain
     */
    protected $allowedEmailDomain;

    /**
     * @var ?bool
     */
    private $twoFaPluginActivated = null;

    public function __construct(Model $model, UserAccessFilter $filter, Password $password, AllowedEmailDomain $allowedEmailDomain)
    {
        $this->model = $model;
        $this->filter = $filter;
        $this->password = $password;
        $this->allowedEmailDomain = $allowedEmailDomain;
    }

    /**
     * @param string $userLogin
     * @param string $email
     * @param int    $initialIdSite
     * @param string $password
     * @param bool   $isPasswordHashed
     * @throws \Exception
     */
    public function create(
        string $userLogin,
        string $email,
        ?int $initialIdSite = null,
        string $password = '',
        bool $isPasswordHashed = false
    ): void {


        if (!Piwik::hasUserSuperUserAccess()) {
            // check if the user has admin access to the site
            Piwik::checkUserHasAdminAccess($initialIdSite);
        }

        BaseValidator::check(Piwik::translate('General_Username'), $userLogin, [new Login(true)]);
        BaseValidator::check(Piwik::translate('Installation_Email'), $email, [new Email(true), $this->allowedEmailDomain]);

        if (!empty($password)) {
            if (!$isPasswordHashed) {
                $passwordTransformed = UsersManager::getPasswordHash($password);
            } else {
                $passwordTransformed = $password;
            }
            $password = $this->password->hash($passwordTransformed);
        }

        $this->model->addUser($userLogin, $password, $email, Date::now()->getDatetime());

        if ($initialIdSite) {
            API::getInstance()->setUserAccess($userLogin, 'view', $initialIdSite);
        }

        $this->sendUserCreationNotification($userLogin);
    }

    public function inviteUser(string $userLogin, string $email, ?int $initialIdSite = null, $expiryInDays = null): void
    {
        $this->create($userLogin, $email, $initialIdSite);
        $this->model->updateUserFields($userLogin, ['invited_by' => Piwik::getCurrentUserLogin()]);
        $user = $this->model->getUser($userLogin);
        $generatedToken = $this->model->generateRandomInviteToken();
        $this->model->attachInviteToken($userLogin, $generatedToken, $expiryInDays);
        $this->sendInvitationEmail($user, $generatedToken, $expiryInDays);
    }

    public function reInviteUser(string $userLogin, int $expiryInDays): void
    {
        $user = $this->model->getUser($userLogin);
        $generatedToken = $this->model->generateRandomInviteToken();
        $this->model->attachInviteToken($userLogin, $generatedToken, $expiryInDays);
        $this->sendInvitationEmail($user, $generatedToken, $expiryInDays);
    }

    public function generateInviteToken(string $userLogin, int $expiryInDays): string
    {
        $generatedToken = $this->model->generateRandomInviteToken();
        $this->model->attachInviteLinkToken($userLogin, $generatedToken, $expiryInDays);
        return $generatedToken;
    }

    protected function sendUserCreationNotification(string $createdUserLogin): void
    {
        if (Piwik::getCurrentUserLogin() !== 'anonymous') {
            $mail = StaticContainer::getContainer()->make(UserCreatedEmail::class, [
                'login' => Piwik::getCurrentUserLogin(),
                'emailAddress' => Piwik::getCurrentUserEmail(),
                'userLogin' => $createdUserLogin,
            ]);
            $mail->safeSend();
        }
    }

    protected function sendInvitationEmail(array $user, string $inviteToken, int $expiryInDays): void
    {
        $site = $this->model->getSitesAccessFromUser($user['login']);

        if (isset($site[0])) {
            $siteName = Site::getNameFor($site[0]['site']);
        } else {
            $siteName = "Default Site";
        }

        $email = StaticContainer::getContainer()->make(UserInviteEmail::class, [
            'currentUser'  => Piwik::getCurrentUserLogin(),
            'invitedUser'  => $user,
            'siteName'     => $siteName,
            'token'        => $inviteToken,
            'expiryInDays' => $expiryInDays
        ]);
        $email->safeSend();
    }

    /**
     * @param array $user
     * @return array
     * @throws \Exception
     */
    public function enrichUser(array $user): array
    {
        if (empty($user)) {
            return $user;
        }

        unset($user['token_auth']);
        unset($user['password']);
        unset($user['ts_password_modified']);
        unset($user['idchange_last_viewed']);
        unset($user['ts_changes_shown']);
        unset($user['invite_token']);
        unset($user['invite_link_token']);

        if ($lastSeen = LastSeenTimeLogger::getLastSeenTimeForUser($user['login'])) {
            $user['last_seen'] = Date::getDatetimeFromTimestamp($lastSeen);
        }

        $user['invite_status'] = 'active';

        if (!empty($user['invite_expired_at'])) {
            $inviteExpireAt = Date::factory($user['invite_expired_at']);
            // if token expired
            if (Date::now()->isLater($inviteExpireAt)) {
                $user['invite_status'] = 'expired';
            }
            // if token not expired
            if (Date::now()->isEarlier($inviteExpireAt)) {
                $dayLeft = floor(Date::secondsToDays($inviteExpireAt->getTimestamp() - Date::now()->getTimestamp()));
                $user['invite_status'] = $dayLeft;
            }
        }

        if (Piwik::hasUserSuperUserAccess()) {
            $user['uses_2fa'] = !empty($user['twofactor_secret']) && $this->isTwoFactorAuthPluginEnabled();
            unset($user['twofactor_secret']);
            return $user;
        }

        $newUser = ['login' => $user['login']];

        if ($user['login'] === Piwik::getCurrentUserLogin() || !empty($user['superuser_access'])) {
            $newUser['email'] = $user['email'];
        }

        if (isset($user['role'])) {
            $newUser['role'] = $user['role'] == 'superuser' ? 'admin' : $user['role'];
        }
        if (isset($user['capabilities'])) {
            $newUser['capabilities'] = $user['capabilities'];
        }

        if (isset($user['superuser_access'])) {
            $newUser['superuser_access'] = $user['superuser_access'];
        }

        if (isset($user['last_seen'])) {
            $newUser['last_seen'] = $user['last_seen'];
        }
        $newUser['invite_status'] = $user['invite_status'];
        if (isset($user['invited_by'])) {
            $newUser['invited_by'] = $user['invited_by'];
        }

        return $newUser;
    }

    /**
     * @param array $users
     * @return mixed
     * @throws \Exception
     */
    public function enrichUsers(array $users): array
    {
        if (!empty($users)) {
            foreach ($users as $index => $user) {
                $users[$index] = $this->enrichUser($user);
            }
        }
        return $users;
    }

    /**
     * @param array $users
     * @return mixed
     */
    public function enrichUsersWithLastSeen(array $users): array
    {
        $formatter = new Formatter();

        $lastSeenTimes = LastSeenTimeLogger::getLastSeenTimesForAllUsers();
        foreach ($users as &$user) {
            $login = $user['login'];
            if (isset($lastSeenTimes[$login])) {
                $user['last_seen'] = $formatter->getPrettyTimeFromSeconds(time() - $lastSeenTimes[$login]);
            }
        }
        return $users;
    }

    private function isTwoFactorAuthPluginEnabled(): bool
    {
        if (!isset($this->twoFaPluginActivated)) {
            $this->twoFaPluginActivated = Plugin\Manager::getInstance()->isPluginActivated('TwoFactorAuth');
        }
        return $this->twoFaPluginActivated;
    }
}
