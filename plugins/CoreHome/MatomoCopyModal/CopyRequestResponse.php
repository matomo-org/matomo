<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\MatomoCopyModal;

/**
 *
 */
class CopyRequestResponse
{
    /**
     * @var array
     */
    private $initialState;

    /**
     * @var bool
     */
    protected $isCopySuccessful;

    /**
     * @var string
     */
    protected $successMessage;

    /**
     * @var array
     */
    protected $responseData;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @var int
     */
    protected $errorCode;

    /**
     * Get an instance of the object and store it's initial state for comparison later
     */
    public function __construct()
    {
        // Save the initial state of the object
        $this->initialState = $this->getCurrentState();
    }

    /**
     * @return bool
     */
    public function isCopySuccessful(): bool
    {
        return $this->isCopySuccessful ?? false;
    }

    /**
     * @param bool $isCopySuccessful
     * @return void
     */
    public function setIsCopySuccessful(bool $isCopySuccessful): void
    {
        $this->isCopySuccessful = $isCopySuccessful;
    }

    /**
     * @return string
     */
    public function getSuccessMessage(): string
    {
        return $this->successMessage ?? '';
    }

    /**
     * @param string $successMessage
     * @return void
     */
    public function setSuccessMessage(string $successMessage): void
    {
        $this->successMessage = $successMessage;
    }

    /**
     * @return array
     */
    public function getResponseData(): array
    {
        return $this->responseData ?? [];
    }

    /**
     * @param array $responseData
     * @return void
     */
    public function setResponseData(array $responseData): void
    {
        $this->responseData = $responseData;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage ?? '';
    }

    /**
     * @param string $errorMessage
     * @return void
     */
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode ?? 0;
    }

    /**
     * @param int $errorCode
     * @return void
     */
    public function setErrorCode(int $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return bool
     */
    public function hasResponseBeenModified(): bool
    {
        return $this->initialState !== $this->getCurrentState();
    }

    public function getJsonResponse(): string
    {
        return json_encode([
            'isCopySuccessful' => $this->isCopySuccessful(),
            'successMessage' => $this->getSuccessMessage(),
            'responseData' => $this->getResponseData(),
            'errorMessage' => $this->getErrorMessage(),
            'errorCode' => $this->getErrorCode(),
        ]);
    }

    /**
     * @return array
     */
    private function getCurrentState(): array
    {
        // Get an array of all the property values
        $state = get_object_vars($this);
        // Exclude the state property
        unset($state['initialState']);

        return $state;
    }
}