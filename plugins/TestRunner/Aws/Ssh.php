<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Aws;
use Symfony\Component\Console\Output\OutputInterface;
use Crypt_RSA;
use Net_SSH2;

class Ssh extends Net_SSH2
{
    /**
     * @var OutputInterface
     */
    private $output;

    public static function connectToAws($host, $pemFile)
    {
        $key = new Crypt_RSA();
        $key->loadKey(file_get_contents($pemFile));

        $ssh = new Ssh($host);

        if (!$ssh->login('ubuntu', $key)) {
            $error = error_get_last();
            throw new \RuntimeException("Login to $host using $pemFile failed: " . $error['message']);
        }

        return $ssh;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function exec($command, $callback = null)
    {
        $command = 'cd www/piwik && ' . $command;
        $output  = $this->output;

        $output->writeln("Executing <comment>$command</comment>");

        return parent::exec($command, function($tempOutput) use ($output) {
            if ($output) {
                $output->write($tempOutput);
            }
        });
    }
}
