<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Tests the class LabelFilter.
 * This is not possible as unit test, since it loads data from the archive.
 */
class Test_Piwik_Integration_LabelFilter extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $labelsToTest = array(
            // first level
            'shouldBeNoData'          => 'nonExistent',
            'dir'                     => '  dir   ',
            '0'                       => '/0',

            // TODO the label in the API output is ...&amp;#039;... why does it only work this way?
            'thisiscool'              => '/ééé&quot;&#039;... &lt;this is cool&gt;!',

            // second level
            'dirnonExistent'          => 'dir>nonExistent',
            'dirfilephpfoobarfoo2bar' => 'dir>' . urlencode('/file.php?foo=bar&foo2=bar'),

            // 4 levels
            'dir2sub0filephp'         => 'dir2>sub>0>' . urlencode('/file.php'),
        );

        $return = array();
        foreach ($labelsToTest as $suffix => $label) {
            $return[] = array('Actions.getPageUrls', array(
                'testSuffix'             => '_' . $suffix,
                'idSite'                 => $idSite,
                'date'                   => $dateTime,
                'otherRequestParameters' => array(
                    'label'    => urlencode($label),
                    'expanded' => 0
                )
            ));
        }

        $label = 'dir';
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix'             => '_' . $label . '_range',
            'idSite'                 => $idSite,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                'date'     => '2010-03-06,2010-03-08',
                'label'    => urlencode($label),
                'expanded' => 0
            )
        ));

        $return[] = array('Actions.getPageTitles', array(
            'testSuffix'             => '_titles',
            'idSite'                 => $idSite,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                // note: title has no blank prefixed here. in the report it has.
                'label'    => urlencode('incredible title! <>,;'),
                'expanded' => 0
            )
        ));

        $return[] = array('Actions.getPageTitles', array(
            'testSuffix'             => '_titlesRecursive',
            'idSite'                 => $idSite,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                'label'    => urlencode(
                    '   ' . // test trimming
                        urlencode('incredible parent title! <>,;') .
                        '>' .
                        urlencode('subtitle <>,;')),
                'expanded' => 0
            )
        ));

        $keyword = '&lt;&gt;&amp;\&quot;the pdo extension is required for this adapter but the extension is not loaded';
        $searchEngineTest = array(
            'testSuffix'             => '_keywords_html',
            'idSite'                 => $idSite,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                'label'    => urlencode('Google>' . urlencode($keyword)),
                'expanded' => 0
            )
        );
        $return[] = array('Referrers.getSearchEngines', $searchEngineTest);

        $searchEngineTest['otherRequestParameters']['label'] = urlencode('Google>' . urlencode(html_entity_decode($keyword)));
        $return[] = array('Referrers.getSearchEngines', $searchEngineTest);

        return $return;
    }

    public static function getOutputPrefix()
    {
        return 'LabelFilter';
    }
}

Test_Piwik_Integration_LabelFilter::$fixture = new Test_Piwik_Fixture_OneVisitSeveralPageViews();

