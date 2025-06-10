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
class CopyRequest
{
    /**
     * @var int
     */
    protected $idSite;

    /**
     * @var int[]
     */
    protected $idDestinationSites;

    /**
     * @var string
     */
    protected $entityTypeName;

    /**
     * @var array
     */
    protected $requestData;

    /**
     * @param int $idSite
     * @param string $entityTypeName
     * @param int[] $idDestinationSites Optional collection of idSites to copy the entity to. Default is idSite.
     * @param array $requestData Optional array of extra request data specific to the entity being copied.
     */
    public function __construct(int $idSite, string $entityTypeName, array $idDestinationSites = [], array $requestData = [])
    {
        $this->idSite = $idSite;
        $this->entityTypeName = $entityTypeName;
        $this->idDestinationSites = $idDestinationSites ?: [$this->idSite];
        $this->requestData = $requestData;
    }

    /**
     * @return int
     */
    public function getIdSite(): int
    {
        return $this->idSite;
    }

    /**
     * @param int $idSite
     * @return void
     */
    public function setIdSite(int $idSite): void
    {
        $this->idSite = $idSite;
    }

    /**
     * @return int[]
     */
    public function getIdDestinationSites(): array
    {
        return $this->idDestinationSites;
    }

    /**
     * @param array $idDestinationSites
     * @return void
     */
    public function setIdDestinationSites(array $idDestinationSites): void
    {
        $this->idDestinationSites = $idDestinationSites;
    }

    /**
     * @return string
     */
    public function getEntityTypeName(): string
    {
        return $this->entityTypeName;
    }

    /**
     * @param string $entityTypeName
     * @return void
     */
    public function setEntityTypeName(string $entityTypeName): void
    {
        $this->entityTypeName = $entityTypeName;
    }

    /**
     * @return array
     */
    public function getRequestData(): array
    {
        return $this->requestData;
    }

    /**
     * @param array $requestData
     * @return void
     */
    public function setRequestData(array $requestData): void
    {
        $this->requestData = $requestData;
    }
}
