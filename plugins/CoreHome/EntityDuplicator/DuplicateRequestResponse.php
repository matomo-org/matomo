<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\EntityDuplicator;

/**
 *
 */
class DuplicateRequestResponse
{
    /**
     * @var array
     */
    private $initialState;

    /**
     * @var bool
     */
    protected $isDuplicationSuccessful;

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
    public function isDuplicationSuccessful(): bool
    {
        return $this->isDuplicationSuccessful ?? false;
    }

    /**
     * @param bool $isDuplicationSuccessful
     * @return void
     */
    public function setIsDuplicationSuccessful(bool $isDuplicationSuccessful): void
    {
        $this->isDuplicationSuccessful = $isDuplicationSuccessful;
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

    /**
     * Checks which property values have changed from the initial state and only includes them in the JSON string.
     *
     * @return string JSON of the response object
     * @throws \Exception If none of the properties have been set
     */
    public function getJsonResponse(): string
    {
        $responseArray = [];
        $currentState = $this->getCurrentState();
        foreach ($this->initialState as $propertyName => $value) {
            if ($currentState[$propertyName] !== $value) {
                $responseArray[$propertyName] = $currentState[$propertyName];
            }
        }

        if (count($responseArray) === 0) {
            throw new \Exception('No duplicate request response properties were set.');
        }

        return json_encode($responseArray);
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
