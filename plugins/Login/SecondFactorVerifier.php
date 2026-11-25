<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Login;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Exception\NoPrivilegesException;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\Login\Security\BruteForceDetection;
use Piwik\Request;
use Piwik\Session\SessionNamespace;
use Piwik\Url;

/**
 * Requires the current user to confirm a sensitive action with their second factor, e.g. a two-factor
 * authentication code.
 *
 * Unlike a password confirmation done through {@link PasswordVerifier}, a second-factor confirmation is not
 * valid for a period of time: each confirmation is used up by the first request using it, is only valid for the
 * action it was requested for, and only reaches the browser that entered the code. Users without a second factor
 * are not asked for one.
 *
 * Plugins providing a second factor answer the `Login.userRequiresSecondFactorConfirmation` and
 * `Login.verifySecondFactor` events. Failed codes count towards the brute force detection.
 *
 * Usage in a controller action, typically after a password confirmation:
 *
 *     $params = ['module' => 'MyPlugin', 'action' => 'mySensitiveAction'];
 *     if (!$secondFactorVerifier->requireSecondFactorConfirmed($params)) {
 *         return;
 *     }
 *     // perform the sensitive action
 *
 * @api
 */
class SecondFactorVerifier
{
    /**
     * Query parameter identifying a pending request on the confirmation page.
     */
    public const REQUEST_ID_PARAMETER = 'secondFactorRequest';

    /**
     * Query parameter carrying a confirmation back to the action.
     */
    public const CONFIRMATION_TOKEN_PARAMETER = 'secondFactorToken';

    /**
     * Minutes the user has to confirm a request.
     */
    public const REQUEST_VALID_FOR_MINUTES = 30;

    /**
     * Minutes a confirmation stays usable; it only needs to cover the redirect back to the action.
     */
    public const CONFIRMATION_VALID_FOR_MINUTES = 5;

    private const MAX_ENTRIES = 10;
    private const REQUESTS = 'secondFactorRequests';
    private const CONFIRMATIONS = 'secondFactorConfirmations';

    private ?Date $now = null;

    private bool $enableRedirect = true;

    /**
     * @ignore tests only
     */
    public function setDisableRedirect(): void
    {
        $this->enableRedirect = false;
    }

    /**
     * @ignore tests only
     */
    public function setNow(Date $now): void
    {
        $this->now = $now;
    }

    /**
     * Returns whether the given user has a second factor that has to be confirmed.
     */
    public function isSecondFactorRequired(string $login): bool
    {
        $requiresSecondFactor = false;

        /**
         * Triggered to check if a user has to confirm sensitive actions with a second factor.
         *
         * Plugins providing a second factor, such as two-factor authentication, set `$requiresSecondFactor`
         * to `true` for users who have it configured. They also need to verify the codes in
         * `Login.verifySecondFactor`.
         *
         * **Example**
         *
         *     public function userRequiresSecondFactorConfirmation(bool &$requiresSecondFactor, string $login): void
         *     {
         *         if ($this->hasDeviceConfigured($login)) {
         *             $requiresSecondFactor = true;
         *         }
         *     }
         *
         * @param bool &$requiresSecondFactor Whether a second factor confirmation is required
         * @param string $login Login of the user
         */
        Piwik::postEvent('Login.userRequiresSecondFactorConfirmation', [&$requiresSecondFactor, $login]);

        /** @var mixed $requiresSecondFactor set by reference in the event; any truthy value requires it */
        return (bool) $requiresSecondFactor;
    }

    /**
     * Returns `true` if the current request carries a confirmation of the user's second factor for the given
     * action, or if the user has no second factor. The confirmation is used up by this call, so the action has
     * to perform the sensitive operation right away.
     *
     * Otherwise, the user is redirected to the confirmation page, which redirects back to the action once
     * confirmed. Returns `false` if redirects are disabled.
     *
     * @param array<string, string|int> $redirectParams The query parameters of the action, including `module`
     *                                                  and `action`. Values are URL encoded when redirecting.
     */
    public function requireSecondFactorConfirmed(array $redirectParams): bool
    {
        $login = Piwik::getCurrentUserLogin();

        if (!$this->isSecondFactorRequired($login)) {
            return true;
        }

        $token = Request::fromRequest()->getStringParameter(self::CONFIRMATION_TOKEN_PARAMETER, '');
        if ($token !== '' && $this->consumeConfirmation($token, $login, $redirectParams)) {
            return true;
        }

        $requestId = $this->requestConfirmation($redirectParams);

        if ($this->enableRedirect) {
            Piwik::redirectToModule($this->getConfirmationModule($redirectParams), 'confirmSecondFactor', [
                self::REQUEST_ID_PARAMETER => $requestId,
            ]);
        }

        return false;
    }

    /**
     * Records that the current user needs to confirm the given action and returns the id of that request,
     * which the confirmation page needs. Prefer {@link requireSecondFactorConfirmed()}.
     *
     * @param array<string, string|int> $redirectParams
     */
    public function requestConfirmation(array $redirectParams): string
    {
        $requestId = $this->generateRandomId();

        $requests = $this->getEntries(self::REQUESTS, self::REQUEST_VALID_FOR_MINUTES);
        $requests[$requestId] = [
            'login' => Piwik::getCurrentUserLogin(),
            'params' => $redirectParams,
            'time' => $this->getNow()->getTimestamp(),
        ];
        $this->setEntries(self::REQUESTS, $requests, self::REQUEST_VALID_FOR_MINUTES);

        return $requestId;
    }

    /**
     * Returns whether the given confirmation request is pending for the current user.
     */
    public function hasConfirmationBeenRequested(string $requestId): bool
    {
        return $this->getPendingRequest($requestId) !== null;
    }

    /**
     * Verifies the given code for the current user and the given confirmation request. If it is valid, returns
     * the URL of the action to redirect to. That URL carries a one-time token which lets the action proceed once.
     * Returns `null` if the code is not valid or the request is not pending.
     *
     * Failed attempts count towards the brute force detection.
     */
    public function confirmSecondFactor(
        string $requestId,
        #[\SensitiveParameter]
        string $code
    ): ?string {
        $request = $this->getPendingRequest($requestId);

        if ($request === null || !$this->verifyCode($request['login'], $code)) {
            return null;
        }

        $requests = $this->getEntries(self::REQUESTS, self::REQUEST_VALID_FOR_MINUTES);
        unset($requests[$requestId]);
        $this->setEntries(self::REQUESTS, $requests, self::REQUEST_VALID_FOR_MINUTES);

        $params = $request['params'];
        $params[self::CONFIRMATION_TOKEN_PARAMETER] = $this->storeConfirmation($request['login'], $params);
        $params = array_map(static fn ($value): string => rawurlencode((string) $value), $params);

        return 'index.php' . Url::getCurrentQueryStringWithParametersModified($params);
    }

    private function verifyCode(
        string $login,
        #[\SensitiveParameter]
        string $code
    ): bool {
        // not through the Login plugin's events, which are not posted while it is deactivated
        $bruteForce = StaticContainer::get(BruteForceDetection::class);
        $isBruteForceDetectionEnabled = $bruteForce->isEnabled();
        $ip = IP::getIpFromHeader();

        if ($isBruteForceDetectionEnabled && !$bruteForce->isAllowedToLogin($ip)) {
            throw new NoPrivilegesException(Piwik::translate('Login_LoginNotAllowedBecauseBlocked'), 403);
        }

        if ($isBruteForceDetectionEnabled && $bruteForce->isUserLoginBlocked($login)) {
            throw new NoPrivilegesException(Piwik::translate('Login_LoginNotAllowedBecauseUserLoginBlocked'), 403);
        }

        $isValid = false;

        /**
         * Triggered to verify a code the user entered to confirm a sensitive action with their second factor.
         *
         * Plugins that require a second factor in `Login.userRequiresSecondFactorConfirmation` set `$isValid`
         * to `true` if the code is valid for the user. A code should only be accepted once.
         *
         * **Example**
         *
         *     public function verifySecondFactor(bool &$isValid, string $login, string $code): void
         *     {
         *         if (!$isValid && $this->hasDeviceConfigured($login)) {
         *             $isValid = $this->isValidCodeNotUsedBefore($login, $code);
         *         }
         *     }
         *
         * @param bool &$isValid Whether the code is valid
         * @param string $login Login of the user
         * @param string $code The code entered by the user
         */
        Piwik::postEvent('Login.verifySecondFactor', [&$isValid, $login, $code]);

        /** @var mixed $isValid set by reference in the event; only true accepts the code */
        $isAccepted = $isValid === true;

        if (!$isAccepted && $isBruteForceDetectionEnabled) {
            $bruteForce->addFailedAttempt($ip, $login);
        }

        return $isAccepted;
    }

    /**
     * @param array<string, string|int> $params
     */
    private function storeConfirmation(string $login, array $params): string
    {
        $token = $this->generateRandomId();

        $confirmations = $this->getEntries(self::CONFIRMATIONS, self::CONFIRMATION_VALID_FOR_MINUTES);
        $confirmations[$token] = [
            'login' => $login,
            'module' => (string) ($params['module'] ?? ''),
            'action' => (string) ($params['action'] ?? ''),
            'time' => $this->getNow()->getTimestamp(),
        ];
        $this->setEntries(self::CONFIRMATIONS, $confirmations, self::CONFIRMATION_VALID_FOR_MINUTES);

        return $token;
    }

    /**
     * @param array<string, string|int> $redirectParams
     */
    private function consumeConfirmation(string $token, string $login, array $redirectParams): bool
    {
        $confirmations = $this->getEntries(self::CONFIRMATIONS, self::CONFIRMATION_VALID_FOR_MINUTES);
        $confirmation = $confirmations[$token] ?? null;

        if (
            $confirmation === null
            || ($confirmation['login'] ?? null) !== $login
            || ($confirmation['module'] ?? null) !== (string) ($redirectParams['module'] ?? '')
            || ($confirmation['action'] ?? null) !== (string) ($redirectParams['action'] ?? '')
        ) {
            return false;
        }

        unset($confirmations[$token]);
        $this->setEntries(self::CONFIRMATIONS, $confirmations, self::CONFIRMATION_VALID_FOR_MINUTES);

        return true;
    }

    /**
     * @return array{login: string, params: array<string, string|int>}|null
     */
    private function getPendingRequest(string $requestId): ?array
    {
        $request = $this->getEntries(self::REQUESTS, self::REQUEST_VALID_FOR_MINUTES)[$requestId] ?? null;
        $login = $request['login'] ?? null;
        $params = $request['params'] ?? null;

        if (!is_string($login) || $login !== Piwik::getCurrentUserLogin() || !is_array($params)) {
            return null;
        }

        /** @var array<string, string|int> $params only written by requestConfirmation() */
        return ['login' => $login, 'params' => $params];
    }

    /**
     * Returns the module showing the confirmation page: the login plugin in use if its controller provides it, as
     * controllers extending the Login controller do, otherwise Login. If Login is not activated either, the plugin
     * of the action can show the page itself, as TwoFactorAuth does.
     *
     * @param array<string, string|int> $redirectParams
     */
    private function getConfirmationModule(array $redirectParams): string
    {
        $loginPlugin = (string) Piwik::getLoginPluginName();
        if ($this->isPluginActivated($loginPlugin) && $this->providesConfirmationPage($loginPlugin)) {
            return $loginPlugin;
        }

        if ($this->isPluginActivated('Login')) {
            return 'Login';
        }

        $actionPlugin = (string) ($redirectParams['module'] ?? '');
        if ($this->providesConfirmationPage($actionPlugin)) {
            return $actionPlugin;
        }

        return 'Login';
    }

    private function providesConfirmationPage(string $plugin): bool
    {
        if (!preg_match('/^[A-Za-z0-9]+$/', $plugin)) {
            return false;
        }

        $controllerClass = 'Piwik\\Plugins\\' . $plugin . '\\Controller';

        return class_exists($controllerClass) && method_exists($controllerClass, 'confirmSecondFactor');
    }

    /**
     * @ignore tests only
     */
    protected function isPluginActivated(string $plugin): bool
    {
        return PluginManager::getInstance()->isPluginActivated($plugin);
    }

    /**
     * Returns the unexpired entries stored under the given session key.
     *
     * @return array<array<mixed>>
     */
    private function getEntries(string $key, int $validForMinutes): array
    {
        $entries = $this->getSession()->$key;

        if (!is_array($entries)) {
            return [];
        }

        $oldestValidTime = $this->getNow()->getTimestamp() - $validForMinutes * 60;

        return array_filter(
            $entries,
            static fn (mixed $entry): bool => is_array($entry)
                && is_int($entry['time'] ?? null)
                && $entry['time'] >= $oldestValidTime
        );
    }

    /**
     * Stores the newest entries under the given session key, which expires with them.
     *
     * @param array<array<mixed>> $entries
     */
    private function setEntries(string $key, array $entries, int $validForMinutes): void
    {
        $session = $this->getSession();
        $session->$key = array_slice($entries, -self::MAX_ENTRIES, null, true);
        $session->setExpirationSeconds($validForMinutes * 60, $key);
    }

    private function generateRandomId(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function getNow(): Date
    {
        return $this->now ?? Date::now();
    }

    private function getSession(): SessionNamespace
    {
        return new SessionNamespace('LoginSecondFactor');
    }
}
