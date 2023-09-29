<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Changes\Model as ChangesModel;
use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

class CreateChanges extends Fixture
{

    private $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createSuperUser();
        if (!self::siteCreated($idSite = $this->idSite)) {
            self::createWebsite('2021-01-01');
        }
        $this->trackVisits();
        $this->createChanges();
    }

    private function createChanges()
    {

        $changes = [
            [
             'version' => '4.6.0b5',
             'title' => 'New feature x added',
             'description' => 'Now you can do a with b like this',
             'link_name' => 'For more information go here',
             'link' => 'https://www.matomo.org',
            ],
            [
             'version' => '4.5.0',
             'title' => 'New feature y added',
             'description' => 'Now you can do c with d like this',
            ],
            [
             'version' => '4.4.0',
             'title' => 'New feature z added',
             'description' => 'Now you can do e with f like this',
             'link_name' => 'For more information go here',
             'link' => 'https://www.matomo.org',
            ],
        ];

        $changes = array_reverse($changes);
        $changesModel = new ChangesModel(); // Intentionally not using the FakeChangesModel, we want these changes added
        foreach ($changes as $change) {
            $changesModel->addChange('CoreHome', $change);
        }

    }

    private function trackVisits()
    {
        $dateTime = Date::today()->toString();
        $t = self::getTracker($this->idSite, $dateTime, $defaultInit = true);

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('0'));
    }

}
