<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\Unit\Processor;

use Piwik\Plugins\Monolog\Processor\TokenProcessor;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Processor\TokenProcessor
 */
class TokenProcessorTest extends \PHPUnit\Framework\TestCase
{
    public function testItShouldRemoveToken()
    {
        $result = $this->process(array(
            'message' => '&token_auth=9b1cefc915ff6180071fb7dcd13ec5a4&trigger=archivephp',
        ));

        $this->assertEquals('&token_auth=removed&trigger=archivephp', $result['message']);
    }

    /**
     * @test
     */
    public function testItShouldRemoveMultipleTokens()
    {
        $result = $this->process(array(
            'message' => 'First token_auth=9b1cefc915ff6180071fb7dcd13ec5a4 and second token_auth=abec834efc915ff61801fb7dcd13ec',
        ));

        $this->assertEquals('First token_auth=removed and second token_auth=removed', $result['message']);
    }

    public function testItShouldNotAffectOtherStrings()
    {
        $result = $this->process(array(
            'message' => 'Please check your token_auth.',
        ));

        $this->assertEquals('Please check your token_auth.', $result['message']);
    }

    private function process($record)
    {
        $processor = new TokenProcessor();
        return $processor($record);
    }
}
