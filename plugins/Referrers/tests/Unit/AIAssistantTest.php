<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Unit;

use Piwik\Plugins\Referrers\AIAssistant;

/**
 * @group AIAssistant
 * @group Plugins
 */
class AIAssistantTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        // inject definitions to avoid database usage
        $yml = file_get_contents(PIWIK_PATH_TEST_TO_ROOT . AIAssistant::DEFINITION_FILE);
        AIAssistant::getInstance()->loadYmlData($yml);
        parent::setUpBeforeClass();
    }

    public function isAIAssistantUrlTestData(): iterable
    {
        yield 'chatgpt.com with correct assumed assistant name' => [
            'https://chatgpt.com',
            'ChatGPT',
            true,
        ];

        yield 'chatgpt.com with incorrect assumed assistant name' => [
            'https://chatgpt.com',
            'Copilot',
            false,
        ];

        yield 'chatgpt.com without assumed assistant name' => [
            'https://chatgpt.com',
            null,
            true,
        ];

        yield 'copilot.microsoft.com with correct assumed assistant name' => [
            'https://copilot.microsoft.com',
            'Copilot',
            true,
        ];
    }

    /**
     * @dataProvider isAIAssistantUrlTestData
     */
    public function testIsAIAssistantUrl($url, $assumedSocial, $expected)
    {
        $this->assertEquals($expected, AIAssistant::getInstance()->isAIAssistantUrl($url, $assumedSocial));
    }

    public function getAIAssistantFromDomainTestData(): iterable
    {
        yield 'ChatGPT url is correctly detected' => [
            'https://chatgpt.com',
            'ChatGPT',
        ];

        yield 'Grok url with path is correctly detected' => [
            'https://x.com/i/grok',
            'Grok',
        ];

        yield 'ChatGPT url with path is correctly detected' => [
            'http://chatgpt.com/i/16564-4345-5sdff-333',
            'ChatGPT',
        ];

        yield 'ChatGPT subdomain url is correctly detected' => [
            'https://custom.chatgpt.com/i/16564-4345-5sdff-333',
            'ChatGPT',
        ];

        yield 'ChatGPT look-a-url is detected as Unknown' => [
            'https://chatgpt.com.custom.org/i/16564-4345-5sdff-333',
            \Piwik\Piwik::translate('General_Unknown'),
        ];

        yield 'Unknown url is detected as Unknown' => [
            'https://xwayn.com',
            \Piwik\Piwik::translate('General_Unknown'),
        ];
    }

    /**
     * @dataProvider getAIAssistantFromDomainTestData
     */
    public function testGetSocialNetworkFromDomain($url, $expected)
    {
        $this->assertEquals($expected, AIAssistant::getInstance()->getAIAssistantFromDomain($url));
    }

    public function getLogoFromUrlTestData()
    {
        return array(
            array('https://chatgpt.com', 'chatgpt.com.png'),
            array('www.chatgpt.com', 'chatgpt.com.png',),
            array('grok.com', 'grok.com.png',),
        );
    }

    /**
     * @dataProvider getLogoFromUrlTestData
     */
    public function testGetLogoFromUrl($url, $expected)
    {
        self::assertStringContainsString($expected, AIAssistant::getInstance()->getLogoFromUrl($url));
    }
}
