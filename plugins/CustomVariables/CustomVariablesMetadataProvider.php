<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables;

/**
 * Provides information about installed custom variable slots, such as,
 * the number of usable custom variables.
 */
class CustomVariablesMetadataProvider
{
    /**
     * @var Model[]
     */
    private $logTableModels;

    public function __construct(array $logTableModels)
    {
        $this->logTableModels = $logTableModels;
    }

    public function getNumUsableCustomVariables()
    {
        $minCustomVar = null;

        foreach ($this->logTableModels as $model) {
            $highestIndex = $model->getHighestCustomVarIndex();

            if (!isset($minCustomVar)) {
                $minCustomVar = $highestIndex;
            }

            if ($highestIndex < $minCustomVar) {
                $minCustomVar = $highestIndex;
            }
        }

        if (!isset($minCustomVar)) {
            $minCustomVar = 0;
        }

        return $minCustomVar;
    }
}
