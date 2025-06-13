<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Request;

use Piwik\Request;
use Piwik\SettingsServer;

/**
 * Main class to handle actions related to auth tokens.
 */
class AuthenticationToken
{
    protected $authToken = '';
    protected $wasTokenProvidedSecurely = false;
    protected $isSessionToken = false;

    /**
     * @param array|null $request
     * @return string
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
        $this->initTokenFromHeader() || $this->initTokenFromJsonRequestBody() || $this->initTokenFromPostRequest() || $this->initTokenFromGetRequest();
    }

    private function initTokenFromHeader(): bool
    {
        if (!empty($_SERVER['HTTP_AUTHORIZATION']) && strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0) {
            $this->authToken = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
            $this->wasTokenProvidedSecurely = true;
            return true;
        }

        return false;
    }

    private function initTokenFromJsonRequestBody(): bool
    {
        // Token in JSON request body is only support for tracking requests
        if (!SettingsServer::isTrackerApiRequest()) {
            return false;
        }

        $requestBody = file_get_contents('php://input');
        if (!empty($requestBody) && strpos($requestBody, '{') === 0) {
            $jsonContent = json_decode($requestBody, true);

            if (!empty($jsonContent['token_auth']) && is_string($jsonContent['token_auth'])) {
                $this->authToken = $jsonContent['token_auth'];
                $this->wasTokenProvidedSecurely = true;
                return true;
            }
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
}
