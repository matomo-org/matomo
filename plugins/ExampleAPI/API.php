<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleAPI;

use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Version;

/**
 * The ExampleAPI is useful to developers building a custom Piwik plugin.
 *
 * Please see the <a href='https://github.com/piwik/piwik/blob/master/plugins/ExampleAPI/API.php' target='_blank'>source code in in the file plugins/ExampleAPI/API.php</a> for more documentation.
 * @method static \Piwik\Plugins\ExampleAPI\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Get Piwik version
     * @return string
     */
    public function getPiwikVersion()
    {
        Piwik::checkUserHasSomeViewAccess();
        return Version::VERSION;
    }

    /**
     * Get Answer to Life
     * @return integer
     */
    public function getAnswerToLife()
    {
        return 42;
    }

    /**
     * Returns a custom object.
     * API format conversion will fail for this custom object.
     * If used internally, the data structure can be returned untouched by using
     * the API parameter 'format=original'
     *
     * @return MagicObject Will return a standard Piwik error when called from the Web APIs
     */
    public function getObject()
    {
        return new MagicObject();
    }

    /**
     * Sums two floats and returns the result.
     * The paramaters are set automatically from the GET request
     * when the API function is called. You can also use default values
     * as shown in this example.
     *
     * @param float|int $a
     * @param float|int $b
     * @return float
     */
    public function getSum($a = 0, $b = 0)
    {
        return (float)($a + $b);
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
    public function getDescriptionArray()
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
    public function getCompetitionDatatable()
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
    public function getMoreInformationAnswerToLife()
    {
        return "Check http://en.wikipedia.org/wiki/The_Answer_to_Life,_the_Universe,_and_Everything";
    }

    /**
     * Returns a Multidimensional Array
     * Only supported in JSON
     *
     * @return array
     */
    public function getMultiArray()
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

/**
 * Magic Object
 *
 */
class MagicObject
{
    function Incredible()
    {
        return 'Incroyable';
    }

    protected $wonderful = 'magnifique';
    public $great = 'formidable';
}
