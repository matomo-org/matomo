<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Validators;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;

class Email extends BaseValidator
{

    protected $email;

    public function validate($value)
    {
        if ($this->isValueBare($value)) {
            return;
        }

        if (!Piwik::isValidEmailString($value)) {
            throw new Exception(Piwik::translate('General_ValidatorErrorNotEmailLike', array($value)));
        }
        $this->email = $value;
        return $this;
    }

    public function isUniqueUserEmail($table = 'user')
    {
        $db = Db::get();
        $count = $db->fetchOne("SELECT count(*) FROM " . Common::prefixTable($table) . " WHERE email = ?",
          $this->email);
        if ($count != 0) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionEmailExists', $this->email));
        }
    }
}