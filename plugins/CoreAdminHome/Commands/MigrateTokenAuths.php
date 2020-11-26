<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\UsersManager\Model;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to selectively delete visits.
 */
class MigrateTokenAuths extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:matomo4-migrate-token-auth');
        $this->addArgument('login', InputArgument::REQUIRED, "User login");
        $this->setDescription('Only needed for the matomo 3 to matomo 4 migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arg = $input->getArgument('login');
        $userModel = new Model();
        foreach ($userModel->getUsers(array($arg)) as $user) {
            if (!empty($user['token_auth'])) {

                $sql = sprintf('INSERT INTO %s (`login`, `description`, `password`, `date_created`, `hash_algo`) VALUES(?,?,?,?)',
                                Common::prefixTable('user_token_auth'));
                $bind = [
                    $user['login'],
                    'Created by Matomo 4 migration',
                    $userModel->hashTokenAuth($user['token_auth']),
                    Date::now()->getDatetime(),
                    'sha512'
                ];

                try {
                    Db::query($sql, $bind);
                } catch (\Exception $e) {
                    if (Db::get()->isErrNo($e, \Piwik\Updater\Migration\Db::ERROR_CODE_DUPLICATE_ENTRY)) {
                        continue;
                    }
                    throw $e;
                }
            }
        }
    }

}
