<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ExampleAPI;

use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Version;

/**
 * The ExampleAPI is useful to developers building a custom Matomo plugin.
 *
 * Please see the <a href='https://github.com/piwik/piwik/blob/master/plugins/ExampleAPI/API.php' rel='noreferrer' target='_blank'>source code in in the file plugins/ExampleAPI/API.php</a> for more documentation.
 * @method static \Piwik\Plugins\ExampleAPI\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Defines if the parameters passed to the public API methods in this class will be automatically sanitized or not.
     * When setting this value to false, please ensure to handle all input parameters with care.
     * Especially parameters provided in an unspecific type, as string or array might contain harmful content.
     * If those values are persisted and displayed in the UI somewhere, ensure to sanitize/escape them for output.
     *
     * @var bool
     */
    protected $autoSanitizeInputParams = true;

    /**
     * Get Matomo version
     * @return string
     */
    public function getMatomoVersion(): string
    {
        Piwik::checkUserHasSomeViewAccess();
        return Version::VERSION;
    }

    /**
     * Get Answer to Life
     * @return int
     */
    public function getAnswerToLife(): int
    {
        return 42;
    }

    /**
     * Returns a custom object.
     * API format conversion will fail for this custom object.
     * If used internally, the data structure can be returned untouched by using
     * the API parameter 'format=original'
     *
     * @return MagicObject Will return a standard Matomo error when called from the Web APIs
     */
    public function getObject(): MagicObject
    {
        return new MagicObject();
    }

    /**
     * Sums two floats and returns the result.
     * The parameters are set automatically from the GET request
     * when the API function is called. You can also use default values
     * as shown in this example.
     *
     * @param float $a
     * @param float $b
     * @return float
     */
    public function getSum(float $a = 0, float $b = 0): float
    {
        return $a + $b;
    }

    /**
     * Returns null value
     *
     * @return null
     */
    public function getNull()
    {
        return null;
    }

    /**
     * Get array of descriptive text
     * When called from the Web API, you see that simple arrays like this one
     * are automatically converted in the various formats (xml, csv, etc.)
     *
     * @return array
     */
    public function getDescriptionArray(): array
    {
        return array('piwik', 'free/libre', 'web analytics', 'free', 'Strong message: Свободный Тибет');
    }

    /**
     * Returns a custom data table.
     * This data table will be converted to all available formats
     * when requested in the API request.
     *
     * @return DataTable
     */
    public function getCompetitionDatatable(): DataTable
    {
        $dataTable = new DataTable();

        $row1 = new Row();
        $row1->setColumns(array('name' => 'piwik', 'license' => 'GPL'));

        // Rows Metadata is useful to store non stats data for example (logos, urls, etc.)
        // When printed out, they are simply merged with columns
        $row1->setMetadata('logo', 'logo.png');
        $dataTable->addRow($row1);

        $dataTable->addRowFromSimpleArray(array('name' => 'google analytics', 'license' => 'commercial'));

        return $dataTable;
    }

    /**
     * Get more information on the Answer to Life...
     *
     * @return string
     */
    public function getMoreInformationAnswerToLife(): string
    {
        return "Check http://en.wikipedia.org/wiki/The_Answer_to_Life,_the_Universe,_and_Everything";
    }

    /**
     * Returns a Multidimensional Array
     * Only supported in JSON
     *
     * @return array
     */
    public function getMultiArray(): array
    {
        $return = array(
            'Limitation'       => array(
                "Multi dimensional arrays is only supported by format=JSON",
                "Known limitation"
            ),
            'Second Dimension' => array(true, false, 1, 0, 152, 'test', array(42 => 'end')),
        );
        return $return;
    }
}
