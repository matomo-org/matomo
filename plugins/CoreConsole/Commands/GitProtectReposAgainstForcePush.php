<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Development;
use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Piwik\SettingsPiwik;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class GitProtectReposAgainstForcePush extends ConsoleCommand
{
    public function isEnabled()
    {
        return Development::isEnabled() && SettingsPiwik::isGitDeployment();
    }

    protected function configure()
    {
        $this->setName('git:protect-repos-against-force-push');
        $this->setDescription('Protect all repositories on Github against force push. Requires to have admin/owner permission.');

        $this->addArgument('username', InputArgument::REQUIRED, 'Github Username');
        $this->addArgument('key', InputArgument::REQUIRED, 'Github API key (github personal api token)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $page = 1;
        $repos = $this->getAllReposByPage($input, $output, $page);
        while(!empty($repos)) {
            foreach($repos as $repo) {
                $full_name = $repo->full_name;
                if(!$repo->permissions->admin) {
                    $output->writeln("Skipped $full_name - not an admin...");
                    continue;
                }
                $this->doProtectBranchAgainstForcePush($input, $output, $full_name);
                $this->writeSuccessMessage($output, array("Protected $full_name against force push"));
            }
            $repos = $this->getAllReposByPage($input, $output, ++$page);
        }
    }

    protected function getAllReposByPage(InputInterface $input, OutputInterface $output, $page)
    {
        $url = "https://api.github.com/user/repos?per_page=100&page=" . $page ;

        $repos = $this->sendGithubApiRequest($input, $url);

        $this->writeSuccessMessage($output, array("Found " . count($repos) . " repos..."));
        return $repos;
    }

    /**
     * @param InputInterface $input
     * @param $url
     * @return bool|mixed|string
     * @throws \Exception
     */
    protected function sendGithubApiRequest(InputInterface $input, $url)
    {
        $username = $input->getArgument('username');
        $key = $input->getArgument('key');

        $repos = Http::sendHttpRequest($url,
            $timeout = 10,
            $userAgent = null,
            $destinationPath = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'GET',
            $httpUsername = $username,
            $httpPassword = $key);

        $repos = json_decode($repos);
        return $repos;
    }

    /**
     * Cannot use the Http class to send this request as Http class doesn't allow to send custom headers
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $full_name
     */
    private function doProtectBranchAgainstForcePush(InputInterface $input, OutputInterface $output, $full_name)
    {
        $username = $input->getArgument('username');
        $key = $input->getArgument('key');

        $command = <<<EOF
        curl -silent -u$username:$key "https://api.github.com/repos/$full_name/branches/master" \
    -XPATCH \
    -H "Accept: application/vnd.github.loki-preview" \
    -d '{
    "protection": {
      "enabled": true
    }
  }' 2>&1 1> /dev/null
EOF;
        shell_exec($command);

    }
}
