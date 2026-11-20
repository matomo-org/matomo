<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\LanguagesManager\tests\Integration;

use Exception;
use Piwik\Access;
use Piwik\Http\BadRequestException;
use Piwik\Nonce;
use Piwik\Plugins\LanguagesManager\Controller;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Url;

/**
 * @group LanguagesManager
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    private const HASH = '#?idSite=1&period=day&date=today&category=General_Visitors';

    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];

        unset($_SERVER['HTTP_REFERER']);

        parent::tearDown();
    }

    public function testSaveLanguageSendsTheUserBackWithoutTheLanguageTheUrlPinned(): void
    {
        $target = $this->saveLanguageFrom($this->page() . '&language=fr' . self::HASH);

        self::assertStringNotContainsString('language=fr', $target);
        self::assertStringContainsString(self::HASH, $target);
    }

    public function testSaveLanguageLeavesAReferrerWithoutALanguageAlone(): void
    {
        $referrer = $this->page() . self::HASH;

        self::assertSame($referrer, $this->saveLanguageFrom($referrer));
    }

    public function testSaveLanguageLeavesNoEmptyQueryStringBehind(): void
    {
        $bare = Url::getCurrentUrlWithoutQueryString();

        self::assertSame($bare, $this->saveLanguageFrom($bare . '?language=fr'));
    }

    public function testSaveLanguageAcceptsTheParametersThroughTheFormPost(): void
    {
        $_SERVER['HTTP_REFERER'] = $this->page();
        $_POST['language'] = 'de';
        $_POST['nonce'] = Nonce::getNonce(LanguagesManager::LANGUAGE_SELECTION_NONCE);

        try {
            Access::doAsSuperUser(static function () {
                (new Controller())->saveLanguage();
            });
        } catch (Exception $e) {
            self::assertStringContainsString('redirect you to this URL: ' . $this->page(), $e->getMessage());
            return;
        }

        self::fail('saveLanguage did not redirect');
    }

    /**
     * @dataProvider getInvalidLanguages
     */
    public function testSaveLanguageRejectsAnInvalidLanguage(?string $language): void
    {
        if ($language !== null) {
            $_GET['language'] = $language;
        }
        $_GET['nonce'] = Nonce::getNonce(LanguagesManager::LANGUAGE_SELECTION_NONCE);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionCode(400);

        Access::doAsSuperUser(static function () {
            (new Controller())->saveLanguage();
        });
    }

    /**
     * @return array<string, array{string|null}>
     */
    public function getInvalidLanguages(): array
    {
        return [
            'missing' => [null],
            'empty' => [''],
            'not installed' => ['xx'],
            'path' => ['../en'],
            'markup' => ['<b>en</b>'],
        ];
    }

    /**
     * The nonce the selector carries is only accepted for a referrer on the Matomo host.
     */
    private function page(): string
    {
        return Url::getCurrentUrlWithoutQueryString()
            . '?module=CoreHome&action=index&idSite=1&period=day&date=today';
    }

    /**
     * The URL the action redirects to, which it reports through an exception on the CLI.
     */
    private function saveLanguageFrom(string $referrer): string
    {
        $_SERVER['HTTP_REFERER'] = $referrer;
        $_GET['language'] = 'de';
        $_GET['nonce'] = Nonce::getNonce(LanguagesManager::LANGUAGE_SELECTION_NONCE);

        try {
            Access::doAsSuperUser(static function () {
                (new Controller())->saveLanguage();
            });
        } catch (Exception $e) {
            preg_match('/redirect you to this URL: (\S+)/', $e->getMessage(), $matches);

            return $matches[1] ?? $e->getMessage();
        }

        self::fail('saveLanguage did not redirect');
    }
}
