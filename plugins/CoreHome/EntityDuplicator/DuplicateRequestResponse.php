<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\EntityDuplicator;

use Piwik\Piwik;

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
     * @var array Optional array containing the data required for the event to be posted on success. If set, the event
     *   will be triggered when the getResponseArray method is called.
     *
     * @see self::setRequestDataForEvent()
     * @see self::getResponseArray()
     */
    protected $eventDataToPost;

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

        // If the flag is set to post the event and the request was successful, post the event for the duplication
        if ($this->success && $this->eventDataToPost !== null) {
            Piwik::postEvent('EntityDuplicator.DuplicationSuccessful', $this->eventDataToPost);
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
        // Exclude the state property and eventDataToPost
        unset($state['initialState']);
        unset($state['eventDataToPost']);

        return $state;
    }

    /**
     * Set the arguments to be used while posting the event when the response array is built. This is used by plugins
     *   which use this class while generating the response to a duplication request.
     *
     * @param string $entityTypeTranslation Translation key for the name of the type of entity. E.g. Goals_Goal,
     *   Heatmaps_Heatmap, etc.
     * @param string $entityName The name of the entity being copied. E.g. 'Goal that does thing' or'Home page heatmap'.
     * @param int|null $idEntity The ID of the entity being copied. E.g. 2 or 900. It's optional since some entities
     *   might only have a string identifier which should be provided as the entityName.
     * @param int|null $idSite ID of the source site. It's optional in case the entity being copied is not site scoped,
     *   like a system-wide configuration.
     * @param array|null $idDestinationSites IDs of the destination sites. This is optional for the same reason as
     *   idSite but also since it doesn't need to be provided if the only destination site is the source site (idSite).
     * @param array|null $additionalData Optional array of additional data relating to the entity being copied.
     *
     * @return void
     */
    public function setRequestDataForEvent(
        string $entityTypeTranslation,
        string $entityName,
        ?int $idEntity = null,
        ?int $idSite = null,
        ?array $idDestinationSites = null,
        ?array $additionalData = null
    ): void {
        $this->eventDataToPost = [
            $entityTypeTranslation,
            $entityName,
            $idEntity,
            $idSite,
            $idDestinationSites,
            $additionalData,
        ];
    }
}
