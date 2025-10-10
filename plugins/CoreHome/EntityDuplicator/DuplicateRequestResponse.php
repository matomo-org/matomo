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
 * The object for building a consistent response to the duplication of an entity.
 */
class DuplicateRequestResponse
{
    /**
     * @var array
     */
    private $initialState;

    /**
     * @var bool|null
     */
    protected $success;

    /**
     * @var string|null
     */
    protected $message;

    /**
     * @var array|null
     */
    protected $additionalData;

    /**
     * @var \Piwik\Plugins\ActivityLog\ActivityParamObject\EntityDuplicatedData|null Optional object defining the
     * structure of the data needed for the EntityDuplicated activity log. It will only be set if the class exists. If
     * set, the event to record the activity will be triggered when the getResponseArray method is called.
     *
     * @see self::setRequestDataForActivity()
     * @see self::getResponseArray()
     */
    protected $activityLogDataObject;

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
    public function isSuccess(): bool
    {
        return $this->success ?? false;
    }

    /**
     * @param bool $success
     * @return void
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message ?? '';
    }

    /**
     * @param string $message
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData ?? [];
    }

    /**
     * @param array $additionalData
     * @return void
     */
    public function setAdditionalData(array $additionalData): void
    {
        $this->additionalData = $additionalData;
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
        return json_encode($this->getResponseArray());
    }

    /**
     * Checks which property values have changed from the initial state and only includes them in the array.
     *
     * @return array response object properties
     * @throws \Exception If none of the properties have been set
     */
    public function getResponseArray(): array
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

        // If the flag is set to post the even and the request was successful, post the event for the duplication
        if ($this->activityLogDataObject !== null && method_exists($this->activityLogDataObject, 'postActivityEvent')) {
            $this->activityLogDataObject->postActivityEvent();
        }

        return $responseArray;
    }

    /**
     * @return array
     */
    private function getCurrentState(): array
    {
        // Get an array of all the property values
        $state = get_object_vars($this);
        // Exclude the state property and activityLogDataObject
        unset($state['initialState']);
        unset($state['activityLogDataObject']);

        return $state;
    }

    /**
     * If the correct class exists, instantiate it with the provided arguments so that it can post the event to record
     * the activity log when the response array is built.
     *
     * @param string $entityTypeTranslation See {@see \Piwik\Plugins\ActivityLog\ActivityParamObject\EntityDuplicatedData::$entityTypeTranslation}
     * @param string $entityName See {@see \Piwik\Plugins\ActivityLog\ActivityParamObject\EntityDuplicatedData::$entityName}
     * @param int|null $idEntity See {@see \Piwik\Plugins\ActivityLog\ActivityParamObject\EntityDuplicatedData::$idEntity}
     * @param int|null $idSite See {@see \Piwik\Plugins\ActivityLog\ActivityParamObject\EntityDuplicatedData::$idSite}
     * @param array|null $idDestinationSites See {@see \Piwik\Plugins\ActivityLog\ActivityParamObject\EntityDuplicatedData::$idDestinationSites}
     * @param array|null $additionalData See {@see \Piwik\Plugins\ActivityLog\ActivityParamObject\EntityDuplicatedData::$additionalData}
     *
     * @return void
     */
    public function setRequestDataForActivity(string $entityTypeTranslation, string $entityName, ?int $idEntity = null, ?int $idSite = null, ?array $idDestinationSites = null, ?array $additionalData = null): void
    {
        if (!class_exists(\Piwik\Plugins\ActivityLog\ActivityParamObject\EntityDuplicatedData::class)) {
            return;
        }

        $this->activityLogDataObject = new \Piwik\Plugins\ActivityLog\ActivityParamObject\EntityDuplicatedData(
            $entityTypeTranslation,
            $entityName,
            $idEntity,
            $idSite,
            $idDestinationSites,
            $additionalData
        );
    }
}
