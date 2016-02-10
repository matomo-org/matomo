<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Mail;

class MailTest extends \PHPUnit_Framework_TestCase
{

    public function getEmailFilenames()
    {
        return array(
            array('January 3 – 9, 2010', 'January 3 - 9, 2010'),
            array('Report <The><< ’s Coves - week January 18 – 24, 2016', 'Report <The><< \'s Coves - week January 18 - 24, 2016'),
        );
    }

    /**
     * @dataProvider getEmailFilenames
     */
    public function test_EmailFilenamesAreSanitised($raw, $expected)
    {
        $mail = new Mail;
        $this->assertEquals($expected, $mail->sanitiseString($raw));
    }
}
