<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\Dimension;

use \Exception;
use Piwik\API\Request;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;

class Index
{
    public function getNextIndex($idSite, $scope)
    {
        $indexes = $this->getTracking($scope)->getInstalledIndexes();

        $configs = Request::processRequest('CustomDimensions.getConfiguredCustomDimensionsHavingScope', [
            'idSite' => $idSite,
            'scope' => $scope,
        ]);
        foreach ($configs as $config) {
            $key = array_search($config['index'], $indexes);
            if ($key !== false) {
                unset($indexes[$key]);
            }
        }

        if (empty($indexes)) {
            throw new Exception("All Custom Dimensions for website $idSite in scope '$scope' are already used.");
        }

        $index = array_shift($indexes);

        return $index;
    }

    private function getTracking($scope)
    {
        return new LogTable($scope);
    }

    private function getConfiguration()
    {
        return new Configuration();
    }
}