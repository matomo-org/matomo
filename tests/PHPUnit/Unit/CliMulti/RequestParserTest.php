<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace PHPUnit\Unit\CliMulti;

use PHPUnit\Framework\TestCase;
use Piwik\CliMulti\RequestParser;

class RequestParserTest extends TestCase
{
    public function test_getInProgressCommands_parsesAndFiltersCorrectly()
    {
        $psOutput = <<<END
USER               PID  %CPU %MEM      VSZ    RSS   TT  STAT STARTED      TIME COMMAND
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ls
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ./console climulti:request module=API&trigger=archivephp&idSite=1&date=2015-02-03&period=day
anotheruser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ls

theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ./console --matomo-domain=domain climulti:request --some-param module=API&trigger=archivephp&idSite=2&date=2015-02-03&period=week
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ls

theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ./console --matomo-domain=domain climulti:request module=Actions&action=doSomething

theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 php console --matomo-domain=domain climulti:request --some-param module=API&trigger=archivephp&idSite=2&date=2015-03-03&period=week
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 grep climulti:request whatever
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 /usr/bin/php console --some-arg climulti:request --some-param module=Funnels&trigger=archivephp&idSite=2&date=2015-02-03&period=week --some-other-param anotherarg


END;

        $requestParser = $this->getMockRequestParser(true, $psOutput);

        $actual = $requestParser->getInProgressCommands();
        $expected = [
            [
                'module' => 'API',
                'trigger' => 'archivephp',
                'idSite' => '1',
                'date' => '2015-02-03',
                'period' => 'day',
            ],
            [
                'module' => 'API',
                'trigger' => 'archivephp',
                'idSite' => '2',
                'date' => '2015-02-03',
                'period' => 'week',
            ],
            [
                'module' => 'Actions',
                'action' => 'doSomething',
            ],
            [
                'module' => 'API',
                'trigger' => 'archivephp',
                'idSite' => '2',
                'date' => '2015-03-03',
                'period' => 'week',
            ],
            [
                'module' => 'Funnels',
                'trigger' => 'archivephp',
                'idSite' => '2',
                'date' => '2015-02-03',
                'period' => 'week',
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_getInProgressCommands_returnsNothingIfNotSupportingAsync()
    {
        $psOutput = <<<END
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ./console climulti:request module=API&trigger=archivephp&idSite=1&date=2015-02-03&period=day
anotheruser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ls
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ./console --matomo-domain=domain climulti:request --some-param module=API&trigger=archivephp&idSite=2&date=2015-02-03&period=week
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ls
END;

        $requestParser = $this->getMockRequestParser(false, $psOutput);

        $actual = $requestParser->getInProgressCommands();
        $expected = [];

        $this->assertEquals($expected, $actual);
    }

    public function test_getInProgressArchivingCommands_returnsOnlyArchivingJobs()
    {
        $psOutput = <<<END
USER               PID  %CPU %MEM      VSZ    RSS   TT  STAT STARTED      TIME COMMAND
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ls
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ./console climulti:request module=API&trigger=archivephp&idSite=1&date=2015-02-03&period=day
anotheruser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ls

theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ./console --matomo-domain=domain climulti:request --some-param module=API&trigger=archivephp&idSite=2&date=2015-02-03&period=week
theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ls

theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 ./console --matomo-domain=domain climulti:request module=Actions&action=doSomething

theuser     3310   7.3  8.5 11469072 1434212   ??  S    17Apr20 1652:35.90 php console --matomo-domain=domain climulti:request --some-param module=API&trigger=archivephp&idSite=2&date=2015-03-03&period=week

END;

        $requestParser = $this->getMockRequestParser(true, $psOutput);

        $actual = $requestParser->getInProgressArchivingCommands();
        $expected = [
            [
                'module' => 'API',
                'trigger' => 'archivephp',
                'idSite' => '1',
                'date' => '2015-02-03',
                'period' => 'day',
            ],
            [
                'module' => 'API',
                'trigger' => 'archivephp',
                'idSite' => '2',
                'date' => '2015-02-03',
                'period' => 'week',
            ],
            [
                'module' => 'API',
                'trigger' => 'archivephp',
                'idSite' => '2',
                'date' => '2015-03-03',
                'period' => 'week',
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    private function getMockRequestParser($supportsAsync, $psOutput)
    {
        $mock = new class($supportsAsync, $psOutput) extends RequestParser {
            public function __construct($supportsAsync, $psOutput)
            {
                parent::__construct($supportsAsync);
                $this->psOutput = $psOutput;
            }

            protected function invokePs()
            {
                return $this->psOutput;
            }
        };
        return $mock;
    }
}