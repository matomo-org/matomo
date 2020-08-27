<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager\UIAssetFetcher;

use Piwik\AssetManager\UIAssetFetcher;

class StaticUIAssetFetcher extends UIAssetFetcher
{
    /**
     * @var string[]
     */
    private $priorityOrder;

    public function __construct($fileLocations, $priorityOrder, $theme)
    {
        parent::__construct(array(), $theme);

        $this->fileLocations = $fileLocations;
        $this->priorityOrder = $priorityOrder;
    }

    protected function retrieveFileLocations()
    {
    }

    protected function getPriorityOrder()
    {
        return $this->priorityOrder;
    }
}
