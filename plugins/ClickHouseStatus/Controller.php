<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ClickHouseStatus;

use Piwik\ClickHouse\ClickHouse;
use Piwik\Piwik;
use Piwik\View;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public function index()
    {
        Piwik::checkUserHasSuperUserAccess();

        $view = new View('@ClickHouseStatus/index');
        $this->setBasicVariablesView($view);

        $view->connected = false;
        $view->roundTrip = '';
        $view->serverVersion = '';
        $view->error = '';

        try {
            $client = ClickHouse::getClient();
            $view->serverVersion = (string) $client->select('SELECT version() AS v')->fetchOne('v');

            // Same shape as the CI smoke fixture (tests/ClickHouse/SmokeTest.php): prove
            // DDL + INSERT + SELECT work end to end through the configured client.
            $client->write('DROP TABLE IF EXISTS ui_smoke');
            $client->write('CREATE TABLE ui_smoke (id UInt32, v String) ENGINE = MergeTree ORDER BY id');
            $client->write("INSERT INTO ui_smoke VALUES (1, 'ok')");
            $view->roundTrip = (string) $client->select('SELECT v FROM ui_smoke WHERE id = 1')->fetchOne('v');

            $view->connected = ($view->roundTrip === 'ok');
        } catch (\Exception $e) {
            $view->error = $e->getMessage();
        }

        return $view->render();
    }
}
