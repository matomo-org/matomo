<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Aws;
use Aws\Ec2\Ec2Client;

class Tags
{
    /**
     * @var Ec2Client
     */
    private $ec2Client;

    public function __construct(Ec2Client $client)
    {
        $this->ec2Client = $client;
    }

    public function assignTagsToInstances($instanceIds, $testSuite)
    {
        $tags = array($this->buildTag('Name', 'PiwikTesting'));

        if (!empty($testSuite)) {
            $tags[] = $this->buildTag('TestSuite', $testSuite);
        }

        $this->ec2Client->createTags(array('Resources' => $instanceIds, 'Tags' => $tags));
    }

    private function buildTag($name, $value)
    {
        return array(
            'Key'   => $name,
            'Value' => $value,
        );
    }
}