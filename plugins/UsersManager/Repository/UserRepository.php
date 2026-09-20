<?php

namespace Piwik\Plugins\UsersManager\Repository;

use Piwik\Auth\Password;
use Piwik\Concurrency\Lock;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\CoreAdminHome\Emails\UserCreatedEmail;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Emails\UserInviteEmail;
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
     * Creates an account.
     *
     * @return string The registration date the account was given, which identifies the row this call
     *                inserted to a caller that has more to write to it.
     *
     * @throws \Exception
     */
    public function create(
        string $userLogin,
        string $email,
        ?int $initialIdSite = null,
        #[\SensitiveParameter]
        string $password = '',
        bool $isPasswordHashed = false
    ): string {


        if (!Piwik::hasUserSuperUserAccess()) {
            // check if the user has admin access to the site
            Piwik::checkUserHasAdminAccess($initialIdSite);
        }

        $dateRegistered = Date::now()->getDatetime();

        // Serialise the uniqueness validation and the insert so two concurrent requests cannot both pass the
        // checks and then persist records whose login and email overlap.
        $lock = StaticContainer::getContainer()->make(Lock::class, ['namespace' => 'UsersManager']);
        $lock->execute('createUser', function () use ($userLogin, $email, $password, $isPasswordHashed, $dateRegistered) {
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

            $this->model->addUser($userLogin, $password, $email, $dateRegistered);
        });

        if ($initialIdSite) {
            API::getInstance()->setUserAccess($userLogin, 'view', $initialIdSite);
        }

        $this->sendUserCreationNotification($userLogin);

        return $dateRegistered;
    }

    public function inviteUser(string $userLogin, string $email, ?int $initialIdSite = null, $expiryInDays = null): void
    {
        $dateRegistered = $this->create($userLogin, $email, $initialIdSite);
        $generatedToken = $this->model->generateRandomInviteToken();

        // Record the inviter and attach the invitation in one statement, pinned to the account this call
        // inserted, so neither can land on a login that was freed and taken again in the meantime.
        if (
            !$this->model->attachInviteToken(
                $userLogin,
                $generatedToken,
                $expiryInDays,
                Piwik::getCurrentUserLogin(),
                $dateRegistered
            )
        ) {
            throw new \Exception(Piwik::translate('UsersManager_ExceptionUserDoesNotExist', $userLogin));
        }

        $this->sendInvitationEmail(['login' => $userLogin, 'email' => $email], $generatedToken, $expiryInDays);
    }

    /**
     * Issues a fresh invitation to a pending user, optionally moving them to a new address.
     *
     * The expected values have to come from the read the caller gated on, so the write can only land on
     * that same pending invitation. A password belonging to the same call goes in on that same write.
     *
     * @return bool Whether the invitation was reissued. False means the account is no longer the pending
     *              user the caller read, in which case nothing was written and no mail is sent.
     */
    public function reInviteUser(
        string $userLogin,
        string $expectedInviteToken,
        string $expectedEmail,
        int $expiryInDays,
        ?string $newEmail = null,
        #[\SensitiveParameter]
        ?string $hashedPassword = null
    ): bool {
        $email = $newEmail ?? $expectedEmail;
        $generatedToken = $this->model->generateRandomInviteToken();

        if (
            !$this->model->reissueInviteTokenForPendingUser(
                $userLogin,
                $generatedToken,
                $expectedInviteToken,
                $expectedEmail,
                $email,
                $expiryInDays,
                $hashedPassword
            )
        ) {
            return false;
        }

        // notify the address the invitation now belongs to, not the one it was read with
        $this->sendInvitationEmail(['login' => $userLogin, 'email' => $email], $generatedToken, $expiryInDays);

        return true;
    }

    /**
     * Replaces the activation token for an unchanged, unexpired pending user
     * without changing the invitation expiry.
     */
    public function refreshInviteToken(
        string $userLogin,
        string $expectedInviteToken,
        string $expectedEmail
    ): ?string {
        $generatedToken = $this->model->generateRandomInviteToken();

        if (
            !$this->model->replaceInviteTokenForPendingUser(
                $userLogin,
                $generatedToken,
                $expectedInviteToken,
                $expectedEmail
            )
        ) {
            return null;
        }

        return $generatedToken;
    }

    /**
     * Issues a copy-and-paste invitation link for an unchanged pending user.
     *
     * @return string|null Null when the account is no longer the pending user that was read.
     */
    public function generateInviteToken(string $userLogin, string $expectedInviteToken, int $expiryInDays): ?string
    {
        $generatedToken = $this->model->generateRandomInviteToken();

        if (
            !$this->model->attachInviteLinkToken(
                $userLogin,
                $generatedToken,
                $expectedInviteToken,
                $expiryInDays
            )
        ) {
            return null;
        }

        return $generatedToken;
    }

    protected function sendUserCreationNotification(string $createdUserLogin): void
    {
        if (Piwik::getCurrentUserLogin() !== 'anonymous' && Piwik::getCurrentUserEmail() !== '') {
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
            'expiryInDays' => $expiryInDays,
        ]);
        $email->safeSend();
    }

    /**
     * @param array $user
     * @return array
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
        unset($user['ts_inactivity_notified']);

        if (isset($user['ts_last_seen'])) {
            $formatter = new Formatter();
            $user['last_seen'] = $user['ts_last_seen'];
            $user['last_seen_ago'] = $formatter->getPrettyTimeFromSeconds(
                time() - Date::factory($user['ts_last_seen'])->getTimestamp()
            );
        }
        unset($user['ts_last_seen']);

        $user['invite_status'] = 'active';

        if (!empty($user['invite_expired_at'])) {
            try {
                $inviteExpireAt = Date::factory($user['invite_expired_at']);
            } catch (\Exception $e) {
                // invite_expired_at is not a valid, in-range date (e.g. corrupted by a bug in the past);
                // treat the invite as expired instead of letting the exception break the whole user list
                $inviteExpireAt = null;
                $user['invite_status'] = 'expired';
            }

            if ($inviteExpireAt) {
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
     * @return array
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

    private function isTwoFactorAuthPluginEnabled(): bool
    {
        if (!isset($this->twoFaPluginActivated)) {
            $this->twoFaPluginActivated = Plugin\Manager::getInstance()->isPluginActivated('TwoFactorAuth');
        }
        return $this->twoFaPluginActivated;
    }
}
