<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\tests\Unit;

use Piwik\Plugins\AIProviders\AIUsageAction;
use PHPUnit\Framework\TestCase;

/**
 * @group AIProviders
 * @group Plugins
 */
class AIUsageActionTest extends TestCase
{
    /**
     * @dataProvider getUnsafeActionUrls
     */
    public function testAnActionRefusesAUrlThatIsNotRelativeOrHttp(string $url): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AIUsageAction('Buy more', $url);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function getUnsafeActionUrls(): iterable
    {
        yield 'javascript' => ['javascript:alert(1)'];
        yield 'uppercase javascript' => ['JavaScript:alert(1)'];
        yield 'data' => ['data:text/html,<script>alert(1)</script>'];
        yield 'leading space' => [' javascript:alert(1)'];
        yield 'leading tab' => ["\tjavascript:alert(1)"];
        yield 'embedded tab' => ["java\tscript:alert(1)"];
        yield 'empty' => [''];
        yield 'mailto' => ['mailto:billing@example.org'];
        yield 'protocol-relative' => ['//evil.example/credits'];
        yield 'backslashes' => ['\\\\evil.example/credits'];
        yield 'slash and backslash' => ['/\\evil.example/credits'];
        yield 'tab between slashes' => ["/\t/evil.example/credits"];
        yield 'http without host' => ['https:///credits'];
        yield 'http with backslashes' => ['https:\\\\evil.example/credits'];
    }

    public function testAnActionRefusesALongSchemeUrlWithoutPcreJit(): void
    {
        $jit = ini_get('pcre.jit');
        ini_set('pcre.jit', '0');

        try {
            $this->expectException(\InvalidArgumentException::class);

            new AIUsageAction('Buy more', 'javascript:alert(1);' . str_repeat('A', 1100000));
        } finally {
            ini_set('pcre.jit', (string) $jit);
        }
    }

    /**
     * @dataProvider getBlankLabels
     */
    public function testAnActionRefusesABlankLabel(string $label): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AIUsageAction($label, 'index.php?module=Billing');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function getBlankLabels(): iterable
    {
        yield 'empty' => [''];
        yield 'spaces' => ['   '];
        yield 'tab and newline' => ["\t\n"];
    }

    public function testAnActionAcceptsRelativeAndHttpUrls(): void
    {
        $this->assertSame('index.php?module=Billing', (new AIUsageAction('Buy', 'index.php?module=Billing'))->getUrl());
        $this->assertSame('https://shop.example/credits', (new AIUsageAction('Buy', 'https://shop.example/credits'))->getUrl());
        $this->assertSame('/index.php?module=Billing', (new AIUsageAction('Buy', '/index.php?module=Billing'))->getUrl());
        $this->assertSame(
            'index.php?module=Billing&returnUrl=https://shop.example/',
            (new AIUsageAction('Buy', 'index.php?module=Billing&returnUrl=https://shop.example/'))->getUrl()
        );
    }
}
