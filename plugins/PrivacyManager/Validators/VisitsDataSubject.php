<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\Validators;

use Piwik\Validators\BaseValidator;
use Piwik\Validators\Exception;

class VisitsDataSubject extends BaseValidator
{
    public function validate($visits)
    {
        if (empty($visits) || !is_array($visits)) {
            throw new Exception('No list of visits given');
        }

        foreach ($visits as $index => $visit) {
            if (empty($visit['idsite'])) {
                throw new Exception('No idsite key set for visit at index ' . $index);
            }
            if (empty($visit['idvisit'])) {
                throw new Exception('No idvisit key set for visit at index ' . $index);
            }
        }
    }

}
