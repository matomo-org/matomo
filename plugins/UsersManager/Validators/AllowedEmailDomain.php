<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\Validators;

use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\SystemSettings;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\Exception;

class AllowedEmailDomain extends BaseValidator
{
    /**
     * @var SystemSettings
     */
    private $settings;

    public function __construct(SystemSettings $settings)
    {
        $this->settings = $settings;
    }

    public function getDomainFromEmail($email): string
    {
        if (!is_string($email) || mb_strpos($email, '@') === false) {
            return '';
        }
        return mb_strtolower(trim(mb_substr($email, mb_strrpos($email, '@') + 1)));
    }

    public function doesEmailEndWithAValidDomain($email, array $domains): bool
    {
        $domains = array_map('mb_strtolower', array_filter($domains));
        $domain = $this->getDomainFromEmail($email);

        return in_array($domain, $domains, true);
    }

    public function getEmailDomainsInUse(): array
    {
        $users = Request::processRequest('UsersManager.getUsers');
        $domains = [];
        foreach ($users as $user) {
            $domains[] = AllowedEmailDomain::getDomainFromEmail($user['email']);
        }
        return array_values(array_unique($domains));
    }

    public function validate($value)
    {
        $domains = $this->settings->allowedEmailDomains->getValue();

        if (empty($domains) || !is_array($domains)) {
            return; // all domains allowed as none are configured
        }

        if (!$this->doesEmailEndWithAValidDomain($value, $domains)) {
            $message = Piwik::translate('UsersManager_ErrorEmailDomainNotAllowed', [$value, implode(', ', $domains)]);
            throw new Exception($message);
        }
    }

}
