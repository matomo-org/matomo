<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\Unit\Reports;

use Piwik\Config;
use Piwik\Plugins\Actions\Reports\GetEntryPageTitles;
use Piwik\Plugins\Actions\Reports\GetPageTitles;
use Piwik\Plugins\Actions\Reports\GetPageUrls;

class BaseTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRecursiveLabelSeparatorUsesTitleDelimiterForPageTitleReports()
    {
        $this->withActionDelimiters('', ' :: ', '/', function () {
            $this->assertSame(' :: ', (new GetPageTitles())->getRecursiveLabelSeparator());
            $this->assertSame(' :: ', (new GetEntryPageTitles())->getRecursiveLabelSeparator());
        });
    }

    public function testGetRecursiveLabelSeparatorUsesLegacyDelimiterWhenConfigured()
    {
        $this->withActionDelimiters(' > ', ' :: ', '/', function () {
            $this->assertSame(' > ', (new GetPageTitles())->getRecursiveLabelSeparator());
            $this->assertSame(' > ', (new GetPageUrls())->getRecursiveLabelSeparator());
        });
    }

    private function withActionDelimiters($legacyDelimiter, $titleDelimiter, $urlDelimiter, callable $callback)
    {
        $config = Config::getInstance();

        $keys = [
            'action_category_delimiter',
            'action_title_category_delimiter',
            'action_url_category_delimiter',
        ];

        $previousValues = [];
        foreach ($keys as $key) {
            $hadValue = array_key_exists($key, $config->General);
            $previousValues[$key] = [
                'hadValue' => $hadValue,
                'value' => $hadValue ? $config->General[$key] : null,
            ];
        }

        $config->General['action_category_delimiter'] = $legacyDelimiter;
        $config->General['action_title_category_delimiter'] = $titleDelimiter;
        $config->General['action_url_category_delimiter'] = $urlDelimiter;

        try {
            $callback();
        } finally {
            foreach ($previousValues as $key => $previousValue) {
                if ($previousValue['hadValue']) {
                    $config->General[$key] = $previousValue['value'];
                } else {
                    unset($config->General[$key]);
                }
            }
        }
    }
}
