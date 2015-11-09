<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables\tests\Mock;

use Piwik\Plugins\CustomVariables\Model;

/**
 * Mock CustomVariablesMetadataProvider used during tests when it is necessary to get
 * the custom variable count w/o database access.
 */
class CustomVariablesMetadataProvider extends \Piwik\Plugins\CustomVariables\CustomVariablesMetadataProvider
{
    public function getNumUsableCustomVariables()
    {
        return Model::DEFAULT_CUSTOM_VAR_COUNT;
    }
}
