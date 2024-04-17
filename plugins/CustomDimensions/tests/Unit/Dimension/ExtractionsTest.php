<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Unit\Dimension;

use Piwik\Plugins\CustomDimensions\Dimension\Extractions;

/**
 * @group CustomDimensions
 * @group ExtractionsTest
 * @group Extractions
 * @group Plugins
 */
class ExtractionsTest extends \PHPUnit\Framework\TestCase
{
    public function test_check_shouldFailWhenExtractionsIsNotAnArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("extractions has to be an array");

        $this->buildExtractions('')->check();
    }

    public function test_check_shouldFailWhenExtractionsDoesNotContainArrays()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Each extraction within extractions has to be an array");

        $this->buildExtractions(array('5'))->check();
    }

    /**
     * @dataProvider getInvalidExtraction
     */
    public function test_check_shouldFailWhenExtractionsDoesNotContainValidExtraction($extraction)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Each extraction within extractions must have a key "dimension" and "pattern" only');

        $this->buildExtractions(array($extraction))->check();
    }

    public function getInvalidExtraction()
    {
        return array(
            array(array()),
            array(array('dimension' => 'url')),
            array(array('pattern' => 'index(.+).html')),
            array(array('dimension' => 'url', 'anything' => 'invalid')),
            array(array('dimension' => 'url', 'pattern' => 'index(.+).html', 'anything' => 'invalid')),
        );
    }

    public function test_check_shouldAlsoCheckExtractionAndFailIfValueIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invald dimension 'invalId' used in an extraction. Available dimensions are: url, urlparam, action_name");

        $extraction1 = array('dimension' => 'url', 'pattern' => 'index(.+).html');
        $extraction2 = array('dimension' => 'invalId', 'pattern' => 'index');
        $this->buildExtractions(array($extraction1, $extraction2))->check();
    }

    public function test_check_shouldNotFailWhenExtractionsDefinitionIsValid()
    {
        $extraction1 = array('dimension' => 'url', 'pattern' => 'index(.+).html');
        $extraction2 = array('dimension' => 'urlparam', 'pattern' => 'index');
        $ex = $this->buildExtractions(array($extraction1, $extraction2));
        $ex->check();

        self::assertInstanceOf(Extractions::class, $ex);
    }

    private function buildExtractions($extractions)
    {
        return new Extractions($extractions);
    }
}
