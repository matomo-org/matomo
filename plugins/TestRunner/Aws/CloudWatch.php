<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Aws;

use Aws\CloudWatch\CloudWatchClient;
use Aws\CloudWatch\Enum\ComparisonOperator;
use Aws\CloudWatch\Enum\Statistic;
use Aws\CloudWatch\Enum\Unit;

class CloudWatch
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CloudWatchClient
     */
    private $client;

    public function __construct(Config $awsConfig)
    {
        $this->config = $awsConfig;
        $this->client = $this->getCloudWatchClient();
    }

    public function hasAssignedAlarms($instanceIds)
    {
        $result = $this->client->describeAlarmsForMetric(array(
            'MetricName' => 'CPUUtilization',
            'Namespace'  => $this->getNamespace(),
            'Dimensions' => $this->getDimensions($instanceIds)
        ));

        $metricAlarms = $result->getPath('MetricAlarms');

        return !empty($metricAlarms);
    }

    public function terminateInstanceIfIdleForTooLong($instanceIds)
    {
        $this->client->putMetricAlarm(array(
            'AlarmName' => 'TerminateInstanceBecauseIdle',
            'AlarmDescription' => 'Terminate instances if CPU is on average < 10% for 5 minutes in a row 8 times consecutively',
            'ActionsEnabled' => true,
            'OKActions' => array(),
            'AlarmActions' => $this->getAlarmActions(),
            'InsufficientDataActions' => array(),
            'MetricName' => 'CPUUtilization',
            'Namespace' => $this->getNamespace(),
            'Statistic' => Statistic::AVERAGE,
            'Dimensions' => $this->getDimensions($instanceIds),
            'Period' => 300,
            'Unit' => Unit::PERCENT,
            'EvaluationPeriods' => 8,
            'Threshold' => 10,
            'ComparisonOperator' => ComparisonOperator::LESS_THAN_THRESHOLD,
        ));

        $this->client->putMetricAlarm(array(
            'AlarmName' => 'TerminateInstanceIfStatusCheckFails',
            'AlarmDescription' => 'Terminate instances in case two status check fail within one minute',
            'ActionsEnabled' => true,
            'OKActions' => array(),
            'AlarmActions' => $this->getAlarmActions(),
            'InsufficientDataActions' => array(),
            'MetricName' => 'StatusCheckFailed',
            'Namespace' => $this->getNamespace(),
            'Statistic' => Statistic::AVERAGE,
            'Dimensions' => $this->getDimensions($instanceIds),
            'Period' => 60,
            'Unit' => Unit::PERCENT,
            'EvaluationPeriods' => 2,
            'Threshold' => 1,
            'ComparisonOperator' => ComparisonOperator::GREATER_THAN_OR_EQUAL_TO_THRESHOLD,
        ));
    }

    private function getCloudWatchClient()
    {
        return CloudWatchClient::factory($this->getConnectionOptions());
    }

    private function getConnectionOptions()
    {
        return array(
            'key'    => $this->config->getAccessKey(),
            'secret' => $this->config->getSecretKey(),
            'region' => $this->config->getRegion()
        );
    }

    private function getDimensions($instanceIds)
    {
        $dimensions = array();

        foreach ($instanceIds as $instanceId) {
            $dimensions[] = array(
                'Name'  => 'InstanceId',
                'Value' => $instanceId,
            );
        }

        return $dimensions;
    }

    private function getNamespace()
    {
        return 'AWS/EC2';
    }

    private function getAlarmActions()
    {
        return array(
            'arn:aws:automate:' . $this->config->getRegion() . ':ec2:terminate',
            'arn:aws:sns:' . $this->config->getRegion() . ':682510200394:TerminateInstanceBecauseIdle'
        );
    }

}
