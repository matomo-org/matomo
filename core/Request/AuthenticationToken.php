<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Request;

use Piwik\API\Request as ApiRequest;
use Piwik\Http\BadRequestException;
use Piwik\Piwik;
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
        $this->detectToken();

        return $this->wasTokenProvidedSecurely;
    }

    public function isSessionToken(): bool
    {
        $this->detectToken();

        return $this->isSessionToken;
    }

    private function detectToken(): void
    {
        $this->validateNoConflictingAuthParameters();
        $this->initTokenFromHeader() || $this->initTokenFromJsonRequestBody() || $this->initTokenFromPostRequest() || $this->initTokenFromGetRequest();
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
        $getTokenAuth = $get->getStringParameter('token_auth', '');
        if (!empty($getTokenAuth)) {
            $tokenAuthBySource['get'] = $getTokenAuth;
        }

        if (array_key_exists('force_api_session', $_GET)) {
            $forceApiSessionBySource['get'] = $get->getBoolParameter('force_api_session', false);
        }

        $this->throwIfValuesConflict($tokenAuthBySource);
        $this->throwIfValuesConflict($forceApiSessionBySource);
    }

    private function shouldSkipConflictingAuthValidation(): bool
    {
        return ApiRequest::isRootRequestApiRequest() && !ApiRequest::isCurrentApiRequestTheRootApiRequest();
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
