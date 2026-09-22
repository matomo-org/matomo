<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Request;

use Piwik\API\Request as ApiRequest;
use Piwik\Date;
use Piwik\Exception\AuthenticationFailedException;
use Piwik\Http\BadRequestException;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Request;
use Piwik\SettingsServer;

/**
 * Main class to handle actions related to auth tokens.
 */
class AuthenticationToken
{
    /** @var string */
    protected $authToken = '';
    /** @var bool */
    protected $wasTokenProvidedSecurely = false;
    /** @var bool */
    protected $isSessionToken = false;
    /** @var bool */
    protected $isConflictingAuthValidationDone = false;
    /** @var bool */
    protected $isJsonRequestBodyTokenLoaded = false;
    /** @var string|null */
    protected $jsonRequestBodyTokenAuth = null;
    /**
     * Per-(token, secure-state) metadata cache, spanning one authentication: survives detectToken() resets
     * and is cleared by {@see clearTokenMetadataCache()}.
     *
     * @var array<string, array<string,mixed>|null>
     */
    private $tokenMetadataCache = [];

    /**
     * @param array<string, mixed>|null $request
     */
    public function getAuthToken(?array $request = null): string
    {
        $this->detectToken();

        if ($request !== null) {
            return (new Request($request))->getStringParameter('token_auth', '');
        }
        return $this->authToken;
    }

    /**
     * Returns true if a token_auth parameter was supplied via a secure mechanism and is not present as a URL parameter
     *
     * @return bool True if token was supplied in a secure way
     */
    public function wasTokenAuthProvidedSecurely(): bool
    {
        // The guard is not enforced here: this grants nothing, and it runs from failure handlers where
        // throwing would replace the authentication error being reported. Every path that can establish a
        // session calls isSessionToken() or getAuthToken() first, so the guard still runs before one exists.
        $this->detectToken($enforceScopedTokenSessionGuard = false);

        return $this->wasTokenProvidedSecurely;
    }

    public function isSessionToken(): bool
    {
        $this->detectToken();

        return $this->isSessionToken;
    }

    private function detectToken(bool $enforceScopedTokenSessionGuard = true): void
    {
        $this->resetDetectedTokenState();
        $this->validateNoConflictingAuthParameters();
        $this->initTokenFromHeader() || $this->initTokenFromJsonRequestBody() || $this->initTokenFromPostRequest() || $this->initTokenFromGetRequest();

        if ($enforceScopedTokenSessionGuard) {
            $this->throwIfScopedTokenUsesApiSession();
        }
    }

    private function resetDetectedTokenState(): void
    {
        // Without this, a later call where no source matches inherits stale flags from an earlier detection.
        $this->authToken = '';
        $this->wasTokenProvidedSecurely = false;
        $this->isSessionToken = false;
        // $isConflictingAuthValidationDone and $tokenMetadataCache are deliberately kept: the former latches
        // once per instance, the latter is cleared per authentication by clearTokenMetadataCache().
    }

    private function validateNoConflictingAuthParameters(): void
    {
        if ($this->isConflictingAuthValidationDone || $this->shouldSkipConflictingAuthValidation()) {
            return;
        }

        $this->isConflictingAuthValidationDone = true;

        $tokenAuthBySource = [];
        $forceApiSessionBySource = [];

        $headerTokenAuth = $this->getTokenAuthFromHeader();
        if (!empty($headerTokenAuth)) {
            $tokenAuthBySource['header'] = $headerTokenAuth;
        }

        $jsonTokenAuth = $this->getTokenAuthFromJsonRequestBody();
        if (!empty($jsonTokenAuth)) {
            $tokenAuthBySource['json'] = $jsonTokenAuth;
        }

        $post = Request::fromPost();
        $postTokenAuth = $post->getStringParameter('token_auth', '');
        if (!empty($postTokenAuth)) {
            $tokenAuthBySource['post'] = $postTokenAuth;
        }

        if (array_key_exists('force_api_session', $_POST)) {
            $forceApiSessionBySource['post'] = $post->getBoolParameter('force_api_session', false);
        }

        $get = Request::fromGet();
        if (!$this->isNavigationOnlyEndpoint()) {
            $getTokenAuth = $get->getStringParameter('token_auth', '');
            if (!empty($getTokenAuth)) {
                $tokenAuthBySource['get'] = $getTokenAuth;
            }

            if (array_key_exists('force_api_session', $_GET)) {
                $forceApiSessionBySource['get'] = $get->getBoolParameter('force_api_session', false);
            }
        }

        $this->throwIfValuesConflict($tokenAuthBySource);
        $this->throwIfValuesConflict($forceApiSessionBySource);
    }

    private function shouldSkipConflictingAuthValidation(): bool
    {
        // Base this on the actual API call nesting rather than request-scoped cache state, which is
        // not a reliable signal for this decision.
        return ApiRequest::isCurrentApiRequestNestedInAnotherApiRequest();
    }

    /**
     * @param array<string, bool|string> $valuesBySource
     */
    private function throwIfValuesConflict(array $valuesBySource): void
    {
        if (count($valuesBySource) < 2) {
            return;
        }

        $firstValue = array_shift($valuesBySource);
        foreach ($valuesBySource as $value) {
            if ($value !== $firstValue) {
                throw new BadRequestException(Piwik::translate('General_ConflictingAuthenticationParametersProvided'));
            }
        }
    }

    private function initTokenFromHeader(): bool
    {
        $tokenAuth = $this->getTokenAuthFromHeader();

        if ($tokenAuth !== null) {
            $this->authToken = $tokenAuth;
            $this->wasTokenProvidedSecurely = true;
            return true;
        }

        return false;
    }

    private function initTokenFromJsonRequestBody(): bool
    {
        $tokenAuth = $this->getTokenAuthFromJsonRequestBody();
        if (!empty($tokenAuth)) {
            $this->authToken = $tokenAuth;
            $this->wasTokenProvidedSecurely = true;
            return true;
        }

        return false;
    }

    private function initTokenFromPostRequest(): bool
    {
        $request = Request::fromPost();
        $tokenAuth = $request->getStringParameter('token_auth', '');

        if ($tokenAuth !== '') {
            $this->authToken = $tokenAuth;
            $this->wasTokenProvidedSecurely = true;
            $this->isSessionToken = $request->getBoolParameter('force_api_session', false);
            return true;
        }

        return false;
    }

    private function initTokenFromGetRequest(): bool
    {
        if ($this->isNavigationOnlyEndpoint()) {
            return false;
        }

        $request = Request::fromGet();
        $tokenAuth = $request->getStringParameter('token_auth', '');

        if ($tokenAuth !== '') {
            $this->authToken = $tokenAuth;
            $this->wasTokenProvidedSecurely = false;
            $this->isSessionToken = $request->getBoolParameter('force_api_session', false);
            return true;
        }

        return false;
    }

    /**
     * Some endpoints exist only as browser navigations, not as API entry points. They are reached
     * via a top-level GET and hand off to another page, so GET credentials are not part of their
     * request contract and must not be consumed as authentication.
     *
     * Keep this list extremely small. Only add an endpoint here once it has been independently
     * confirmed that the endpoint never needs URL-borne auth and never performs writes.
     */
    private function isNavigationOnlyEndpoint(): bool
    {
        if (SettingsServer::isTrackerApiRequest()) {
            return false;
        }

        $get = Request::fromGet();
        $module = $get->getStringParameter('module', '');
        $action = $get->getStringParameter('action', '');

        return $module === 'Overlay' && $action === 'startOverlaySession';
    }

    /**
     * Returns true when the token metadata for the given token was already fetched by throwIfScopedTokenUsesApiSession().
     */
    public function isTokenMetadataPreloadedFor(
        #[\SensitiveParameter]
        ?string $tokenAuth
    ): bool {
        if ($tokenAuth === null || $this->authToken !== $tokenAuth) {
            return false;
        }
        return $this->hasFreshCacheEntry($this->getTokenMetadataCacheKey());
    }

    /**
     * Returns the cached token metadata fetched during the scoped-token session check, or null if not preloaded.
     *
     * @return array<string,mixed>|null
     */
    public function getPreloadedTokenMetadata(): ?array
    {
        $cacheKey = $this->getTokenMetadataCacheKey();
        if (!$this->hasFreshCacheEntry($cacheKey)) {
            return null;
        }
        return $this->tokenMetadataCache[$cacheKey];
    }

    /**
     * Returns true when token metadata was cached for this exact (token, secure-state) pair. Lets callers
     * distinguish a cached null (token not found) from "not in cache".
     */
    public function hasCachedTokenMetadata(
        #[\SensitiveParameter]
        ?string $tokenAuth,
        bool $isTokenProvidedSecurely
    ): bool {
        if (
            $tokenAuth === null
            || $tokenAuth !== $this->authToken
            || $isTokenProvidedSecurely !== $this->wasTokenProvidedSecurely
        ) {
            return false;
        }
        return $this->hasFreshCacheEntry($this->getTokenMetadataCacheKey());
    }

    /**
     * Expiry is re-evaluated on every read because the `date_expired` predicate only applies when the row is
     * actually queried, so a token expiring mid-authentication would otherwise keep authenticating. A cached
     * "not found" stays usable: it can only ever deny.
     */
    private function hasFreshCacheEntry(string $cacheKey): bool
    {
        if (!array_key_exists($cacheKey, $this->tokenMetadataCache)) {
            return false;
        }

        $metadata = $this->tokenMetadataCache[$cacheKey];
        if ($metadata === null || empty($metadata['date_expired'])) {
            return true;
        }

        if (Date::factory($metadata['date_expired'])->isLater(Date::now())) {
            return true;
        }

        unset($this->tokenMetadataCache[$cacheKey]);
        return false;
    }

    /**
     * Returns cached token metadata for this exact (token, secure-state) pair, or null when missing or when
     * the cache holds a null entry (token not found / expired). Pair with hasCachedTokenMetadata() to disambiguate.
     *
     * @return array<string,mixed>|null
     */
    public function getCachedTokenMetadata(
        #[\SensitiveParameter]
        ?string $tokenAuth,
        bool $isTokenProvidedSecurely
    ): ?array {
        if (!$this->hasCachedTokenMetadata($tokenAuth, $isTokenProvidedSecurely)) {
            return null;
        }
        return $this->tokenMetadataCache[$this->getTokenMetadataCacheKey()];
    }

    /**
     * Caches only the request's own token at its actual transport security; any other (token, secure-state)
     * pair is dropped, so nothing leaks across security contexts or sub-request tokens.
     *
     * @param array<string,mixed>|null $metadata Token row to cache, or null when the token was not found.
     */
    public function cacheTokenMetadata(
        #[\SensitiveParameter]
        ?string $tokenAuth,
        bool $isTokenProvidedSecurely,
        ?array $metadata
    ): void {
        if (
            $tokenAuth === null
            || $tokenAuth !== $this->authToken
            || $isTokenProvidedSecurely !== $this->wasTokenProvidedSecurely
        ) {
            return;
        }
        $this->tokenMetadataCache[$this->getTokenMetadataCacheKey()] = $metadata;
    }

    /**
     * Drops every cached token row, so the next authentication reads user_token_auth again and sees a
     * revoked or re-scoped token.
     *
     * @internal Intended only for use by Access when it begins authenticating a request.
     */
    public function clearTokenMetadataCache(): void
    {
        $this->tokenMetadataCache = [];
    }

    protected function getUsersModel(): UsersModel
    {
        return new UsersModel();
    }

    private function throwIfScopedTokenUsesApiSession(): void
    {
        if (!$this->isSessionToken || $this->authToken === '') {
            return;
        }

        $cacheKey = $this->getTokenMetadataCacheKey();
        if (!$this->hasFreshCacheEntry($cacheKey)) {
            $this->tokenMetadataCache[$cacheKey] = $this->getUsersModel()->getTokenMetadataByTokenAuthWithSecurityState(
                $this->authToken,
                $this->wasTokenProvidedSecurely
            );
        }

        $accessLevel = $this->tokenMetadataCache[$cacheKey]['access_level'] ?? null;
        if (empty($accessLevel) || $accessLevel === 'superuser') {
            return;
        }

        throw new AuthenticationFailedException(
            Piwik::translate('General_ScopedTokenCannotUseApiSession')
        );
    }

    private function getTokenMetadataCacheKey(): string
    {
        return ($this->wasTokenProvidedSecurely ? '1|' : '0|') . $this->authToken;
    }

    private function getTokenAuthFromHeader(): ?string
    {
        if (!empty($_SERVER['HTTP_AUTHORIZATION']) && strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0) {
            return substr($_SERVER['HTTP_AUTHORIZATION'], 7);
        }

        return null;
    }

    private function getTokenAuthFromJsonRequestBody(): ?string
    {
        if ($this->isJsonRequestBodyTokenLoaded) {
            return $this->jsonRequestBodyTokenAuth;
        }

        $this->isJsonRequestBodyTokenLoaded = true;
        $this->jsonRequestBodyTokenAuth = null;

        // Token in JSON request body is only supported for tracking requests
        if (!SettingsServer::isTrackerApiRequest()) {
            return null;
        }

        $requestBody = file_get_contents('php://input');
        if (!empty($requestBody) && strpos($requestBody, '{') === 0) {
            $jsonContent = json_decode($requestBody, true);

            if (is_array($jsonContent) && !empty($jsonContent['token_auth']) && is_string($jsonContent['token_auth'])) {
                $this->jsonRequestBodyTokenAuth = $jsonContent['token_auth'];
            }
        }

        return $this->jsonRequestBodyTokenAuth;
    }
}
