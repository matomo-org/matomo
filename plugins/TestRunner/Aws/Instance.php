<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Aws;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Aws\Ec2\Ec2Client;

class Instance
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Ec2Client
     */
    private $client;

    private $testSuite;

    private $useOneInstancePerTestSuite = false;

    public function __construct(Config $config, $testSuite)
    {
        $this->config    = $config;
        $this->testSuite = $testSuite;
        $this->client    = $this->createEc2Client();
    }

    public function enableUseOneInstancePerTestSuite()
    {
        $this->useOneInstancePerTestSuite = true;
    }

    public function findExisting()
    {
        $filters = array(
            array('Name' => 'image-id', 'Values' => array($this->config->getAmi())),
            array('Name' => 'key-name', 'Values' => array($this->config->getKeyName())),
            array('Name' => 'instance-state-name', 'Values' => array('running')),
        );

        if (!empty($this->testSuite) && $this->useOneInstancePerTestSuite) {
            $filters[] = array('Name' => 'tag:TestSuite', 'Values' => array($this->testSuite));
        }

        $instances = $this->client->describeInstances(array('Filters' => $filters));

        $reservations = $instances->getPath('Reservations');

        if (!empty($reservations)) {
            $host = $this->getHostFromDescribedInstances($instances);

            return $host;
        }
    }

    public function terminate($instanceIds)
    {
        $this->client->terminateInstances(array(
            'InstanceIds' => $instanceIds
        ));

        $this->client->waitUntilInstanceTerminated(array(
            'InstanceIds' => $instanceIds
        ));
    }

    public function launch()
    {
        $result = $this->client->runInstances(array(
            'ImageId' => $this->config->getAmi(),
            'MinCount' => 1,
            'MaxCount' => 1,
            'InstanceType' => $this->config->getInstanceType(),
            'KeyName' => $this->config->getKeyName(),
            'SecurityGroups' => $this->config->getSecurityGroups(),
            'InstanceInitiatedShutdownBehavior' => 'terminate'
        ));

        $instanceIds = $result->getPath('Instances/*/InstanceId');

        return $instanceIds;
    }

    public function setup($instanceIds)
    {
        $this->client->waitUntilInstanceRunning(array(
            'InstanceIds' => $instanceIds,
        ));

        $awsCloudWatch = new CloudWatch($this->config);
        $awsCloudWatch->terminateInstanceIfIdleForTooLong($instanceIds);

        $awsTags = new Tags($this->client);
        $awsTags->assignTagsToInstances($instanceIds, $this->testSuite);

        $instances = $this->client->describeInstances(array(
            'InstanceIds' => $instanceIds,
        ));

        $host = $this->getHostFromDescribedInstances($instances);

        return $host;
    }

    /**
     * @param \Guzzle\Service\Resource\Model $resources
     * @return mixed
     */
    private function getHostFromDescribedInstances($resources)
    {
        $instances = $resources->getPath('Reservations/*/Instances');

        $instanceToUse = null;

        foreach ($instances as $index => $instance) {
            foreach ($instance['Tags'] as $tag) {
                if (!empty($this->testSuite)
                    && $tag['Key'] === 'TestSuite'
                    && $tag['Value'] === $this->testSuite) {

                    $instanceToUse = $instance;
                }
            }
        }

        if (empty($instanceToUse)) {
            $instanceToUse = array_shift($instances);
        }

        $host = $instanceToUse['PublicDnsName'];

        return $host;
    }

    private function createEc2Client()
    {
        return Ec2Client::factory($this->getConnectionOptions());
    }

    private function getConnectionOptions()
    {
        return array(
            'key'    => $this->config->getAccessKey(),
            'secret' => $this->config->getSecretKey(),
            'region' => $this->config->getRegion()
        );
    }
}