<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\Validators;

use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\Exception;

class Email extends BaseValidator
{

    private $checkUnique;

    public function __construct($checkUnique = false)
    {
        $this->checkUnique = $checkUnique;
    }

    public function validate($value)
    {
        if ($this->isValueBare($value)) {
            return;
        }

        if (!Piwik::isValidEmailString($value)) {
            throw new Exception(Piwik::translate('General_ValidatorErrorNotEmailLike', array($value)));
        }

        if ($this->checkUnique) {
            $this->isUnique($value);
        }

    }

    /**
     * check if email already exist in database
     * @param $email
     * @throws \Exception
     */
    private function isUnique($email)
    {
        if (APIUsersManager::getInstance()->userExists($email)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionLoginExistsAsEmail', $email));
        }

        if (APIUsersManager::getInstance()->userEmailExists($email)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionEmailExists', $email));
        }
    }

}
