<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access\Role;

use Piwik\Access\Role;
use Piwik\Piwik;

class Write extends Role
{
    public const ID = 'write';

    public function getName(): string
    {
        return Piwik::translate('UsersManager_PrivWrite');
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function getDescription(): string
    {
        return Piwik::translate('UsersManager_PrivWriteDescription');
    }

    public function getHelpUrl(): string
    {
        return 'https://matomo.org/faq/general/faq_26910';
    }

}
