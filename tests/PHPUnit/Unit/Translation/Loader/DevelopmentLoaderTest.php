<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Translation\Loader\DevelopmentLoader;

/**
 * @group Translation
 */
class DevelopmentLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array<string, array<string, string>>
     */
    private $translations = array(
        'General' => array(
            'translationId' => 'Hello',
            'withPlaceholder' => 'Items: %1$d',
            'withHtml' => '<strong>Save</strong>',
            'withEntity' => 'Fish &amp; Chips',
            'withPercentLiteral' => '100% free',
            'withStringPlaceholderAdjacentText' => '%sBy clicking',
            'withMultipleStringPlaceholders' => '%1$s from %2$s to %3$s',
            'withIntPlaceholder' => '%d results',
            'withNumberedIntPlaceholder' => '%2$d of %1$d',
            'withFloatPrecisionPlaceholder' => 'Rate: %.2f%% complete',
            'withPaddedIntPlaceholder' => '%04d items',
            'withPercentAndPositionalPlaceholders' => '%1$s%% from %2$s to %3$s',
            'withSignedPositionalFloatPlaceholder' => 'Delta: %1$+08.2f units',
        ),
        'Intl' => array(
            'OriginalLanguageName' => 'English',
        ),
    );

    public function testShouldReturnPseudoLocalizedTranslationsIfDevelopmentLanguage(): void
    {
        $wrappedLoader = $this->getMockForAbstractClass('Piwik\Translation\Loader\LoaderInterface');
        $loader = new DevelopmentLoader($wrappedLoader);

        $wrappedLoader->expects($this->once())
            ->method('load')
            ->with('en', array('directory'))
            ->willReturn($this->translations);

        $translations = $loader->load(DevelopmentLoader::LANGUAGE_ID, array('directory'));

        $expected = array(
            'General' => array(
                'translationId' => '[Ħḗŀŀǿ]',
                'withPlaceholder' => '[Īŧḗḿş: %1$d]',
                'withHtml' => '[<strong>Şȧṽḗ</strong>]',
                'withEntity' => '[Ƒīşħ &amp; Ƈħīƥş]',
                'withPercentLiteral' => '[100% ƒřḗḗ]',
                'withStringPlaceholderAdjacentText' => '[%sƁẏ ƈŀīƈķīƞɠ]',
                'withMultipleStringPlaceholders' => '[%1$s ƒřǿḿ %2$s ŧǿ %3$s]',
                'withIntPlaceholder' => '[%d řḗşŭŀŧş]',
                'withNumberedIntPlaceholder' => '[%2$d ǿƒ %1$d]',
                'withFloatPrecisionPlaceholder' => '[Řȧŧḗ: %.2f%% ƈǿḿƥŀḗŧḗ]',
                'withPaddedIntPlaceholder' => '[%04d īŧḗḿş]',
                'withPercentAndPositionalPlaceholders' => '[%1$s%% ƒřǿḿ %2$s ŧǿ %3$s]',
                'withSignedPositionalFloatPlaceholder' => '[Ḓḗŀŧȧ: %1$+08.2f ŭƞīŧş]',
            ),
            'Intl' => array(
                'OriginalLanguageName' => 'English',
            ),
        );

        $this->assertEquals($expected, $translations);
    }

    public function testShouldUseDecoratedLoaderIfNotDevelopmentLanguage(): void
    {
        $wrappedLoader = $this->getMockForAbstractClass('Piwik\Translation\Loader\LoaderInterface');
        $loader = new DevelopmentLoader($wrappedLoader);

        $wrappedLoader->expects($this->once())
            ->method('load')
            ->with('fr', array('directory'))
            ->willReturn($this->translations);

        $translations = $loader->load('fr', array('directory'));

        $this->assertEquals($this->translations, $translations);
    }
}
