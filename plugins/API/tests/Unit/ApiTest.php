<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\API\tests\Unit;

use Piwik\Plugins\API\API;
use ReflectionClass;

/**
 * @group Plugin
 * @group API
 */
class ApiTest extends \PHPUnit_Framework_TestCase
{

    public function getSegments()
    {
        $someSegments = [];
        
        $someSegments['one'] = [
            [
                [
                    'type' => 'dimension',
                    'category' => 'Visit',
                    'name' => 'User ID',
                    'segment' => 'userId'
                ],
                [
                    'type' => 'dimension',
                    'category' => 'Actions',
                    'name' => 'Entry Page title',
                    'segment' => 'entryPageTitle'
                ],
                [
                    'type' => 'dimension',
                    'category' => 'Visit',
                    'name' => 'Visit Ecommerce status at the end of the visit',
                    'segment' => 'visitEcommerceStatus'
                ],
                [
                    'type' => 'dimension',
                    'category' => 'Custom Variables',
                    'name' => 'Custom Variable value (scope visit)',
                    'segment' => 'customVariableValue'
                ],
                
                [
                    'type' => 'dimension',
                    'category' => 'Actions',
                    'name' => 'Action Type',
                    'segment' => 'actionType'
                ],
                [
                    'type' => 'dimension',
                    'category' => 'Actions',
                    'name' => 'Entry Page URL',
                    'segment' => 'entryPageUrl'
                ],
                [
                    'type' => 'dimension',
                    'category' => 'Actions',
                    'name' => 'Exit Page Title',
                    'segment' => 'exitPageTitle'
                ]
            ]
        ];
        
        return $someSegments;
    }

    /**
     * @dataProvider getSegments
     */
    public function testSortSegments($segments)
    {
        var_dump($segments);
        
        $class = new ReflectionClass('Piwik\Plugins\API\API');
        $method = $class->getMethod('sortSegments');
        $method->setAccessible(true);
        
        $obj = new API();
        $result = $method->invokeArgs($obj, [
            $segments
        ]);
        
        var_dump($result);
        exit();
    }
}