<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CustomVariables\tests\Fixtures;

use Piwik\Plugins\CustomVariables\Model;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one site with two goals and tracks two visits with custom variables.
 */
class VisitWithManyCustomVariables extends Fixture
{
    public $dateTime = '2010-01-03 11:22:33';
    public $idSite = 1;
    public $idGoal1 = 1;
    public $visitorId = '61e8cc2d51fea26d';
    private $numCustomVars = 8;

    public function setUp()
    {
        $this->setUpCustomVars();
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown()
    {
        // empty
    }

    private function setUpCustomVars()
    {
        foreach (Model::getScopes() as $scope) {
            $model = new Model($scope);
            $model->addCustomVariable();
            $model->addCustomVariable();
            $model->addCustomVariable();
        }
    }

    private function setUpWebsitesAndGoals()
    {
        // tests run in UTC, the Tracker in UTC
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }

        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            API::getInstance()->addGoal($this->idSite, 'triggered js', 'manually', '', '');
        }
    }

    private function trackVisits()
    {
        $idGoal = $this->idGoal1;

        $visitorA = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $visitorA->setUrl('http://localhost');
        // Used to test actual referrer + keyword position in Live!
        $visitorA->setUrlReferrer(urldecode('http://www.google.com/url?sa=t&source=web&cd=1&ved=0CB4QFjAA&url=http%3A%2F%2Fpiwik.org%2F&rct=j&q=this%20keyword%20should%20be%20ranked&ei=V8WfTePkKKLfiALrpZWGAw&usg=AFQjCNF_MGJRqKPvaKuUokHtZ3VvNG9ALw&sig2=BvKAdCtNixsmfNWXjsNyMw'));

        // no campaign, but a search engine to attribute the goal conversion to
        $attribution = array(
            '',
            '',
            1302306504,
            'http://www.google.com/search?q=piwik&ie=utf-8&oe=utf-8&aq=t&rls=org.mozilla:en-GB:official&client=firefox-a'
        );
        $visitorA->setAttributionInfo(json_encode($attribution));

        for ($index = 1; $index <= $this->numCustomVars; $index++) {
            $visitorA->setCustomVariable($index, 'Name_VISIT_' . $index, 'Val_VISIT' . $index, 'visit');
            $visitorA->setCustomVariable($index, 'Name_PAGE_' . $index, 'Val_PAGE' . $index, 'page');
        }

        self::checkResponse($visitorA->doTrackPageView('Profile page'));
        self::checkResponse($visitorA->doTrackGoal($idGoal));
    }
}
