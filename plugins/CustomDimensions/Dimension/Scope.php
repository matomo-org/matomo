<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\Dimension;

use Piwik\Plugins\CustomDimensions\CustomDimensions;

class Scope
{
    private $scope;

    public function __construct($scope)
    {
        $this->scope = $scope;
    }

    public function check()
    {
        $scopes = CustomDimensions::getScopes();

        if (empty($this->scope) || !in_array($this->scope, $scopes, true)) {
            $scopes = implode(', ', $scopes);
            $scope  = $this->scope;

            throw new \Exception("Invalid value '$scope' for 'scope' specified. Available scopes are: $scopes");
        }
    }
}