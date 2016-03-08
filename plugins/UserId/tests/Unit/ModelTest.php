<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId\tests\Unit;
use Piwik\Plugins\UserId\Model;

/**
 * Unit test for the model
 *
 * @group UserId
 * @group ModelTest
 * @group Plugins
 */
class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function test_getTotalUsersNumber()
    {
        $dbMock = $this->getMockBuilder('Piwik\Db\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array('fetchOne'))
            ->getMock();

        $model = new Model($dbMock);

        $dbMock->expects($this->exactly(3))
            ->method('fetchOne')
            ->withConsecutive(
                array(
                    $this->equalTo(
                "SELECT COUNT(*)
            FROM {$model->getUserIdsTable()}
            WHERE idsite = ? AND user_id != ''"
                    ),
                    $this->equalTo(array(1))
                ),
                array(
                    $this->equalTo(
                        "SELECT COUNT(*)
            FROM {$model->getUserIdsTable()}
            WHERE idsite = ? AND user_id != '' AND user_id LIKE ?"
                    ),
                    $this->equalTo(array(1, '%user@example.com%'))
                ),
                array(
                    $this->equalTo(
                        "SELECT COUNT(*)
            FROM {$model->getUserIdsTable()}
            WHERE idsite = ? AND user_id != '' AND user_id LIKE ?"
                    ),
                    $this->equalTo(array(1, '%test\_user\%example.com%'))
                )
            );

        // simple count
        $model->getTotalUsersNumber(1);
        // search by user ID
        $model->getTotalUsersNumber(1, 'user@example.com');
        // search by user ID that contains MySQL LIKE wildcard symbols
        $model->getTotalUsersNumber(1, 'test_user%example.com');
    }

    public function test_getSiteUserIds()
    {
        $dbMock = $this->getMockBuilder('Piwik\Db\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array('fetchAll'))
            ->getMock();

        $model = new Model($dbMock);

        $dbMock->expects($this->exactly(7))
            ->method('fetchAll')
            ->withConsecutive(
                array(
                    $this->equalTo(
                    "SELECT user_id, first_visit_time, last_visit_time, total_visits, idvisitor, total_actions,
              total_searches, total_events
            FROM {$model->getUserIdsTable()}
            WHERE idsite = ? AND user_id != ''
            ORDER BY user_id * 1 asc, user_id asc
            LIMIT 0, 10"
                    ),
                    $this->equalTo(array(1))
                ),
                array(
                    $this->equalTo(
                        "SELECT user_id, first_visit_time, last_visit_time, total_visits, idvisitor, total_actions,
              total_searches, total_events
            FROM {$model->getUserIdsTable()}
            WHERE idsite = ? AND user_id != ''
            ORDER BY user_id * 1 desc, user_id desc
            LIMIT 50, 25"
                    ),
                    $this->equalTo(array(1))
                ),
                array(
                    $this->equalTo(
                        "SELECT user_id, first_visit_time, last_visit_time, total_visits, idvisitor, total_actions,
              total_searches, total_events
            FROM {$model->getUserIdsTable()}
            WHERE idsite = ? AND user_id != ''
            ORDER BY user_id * 1 asc, user_id asc
            LIMIT 50, 25"
                    ),
                    $this->equalTo(array(1))
                ),
                array(
                    $this->equalTo(
                        "SELECT user_id, first_visit_time, last_visit_time, total_visits, idvisitor, total_actions,
              total_searches, total_events
            FROM {$model->getUserIdsTable()}
            WHERE idsite = ? AND user_id != ''
            ORDER BY user_id * 1 asc, user_id asc
            LIMIT 0, 0"
                    ),
                    $this->equalTo(array(1))
                ),
                array(
                    $this->equalTo(
                        "SELECT user_id, first_visit_time, last_visit_time, total_visits, idvisitor, total_actions,
              total_searches, total_events
            FROM {$model->getUserIdsTable()}
            WHERE idsite = ? AND user_id != ''
            ORDER BY first_visit_time asc
            LIMIT 0, 10"
                    ),
                    $this->equalTo(array(1))
                ),
                array(
                    $this->equalTo(
                        "SELECT user_id, first_visit_time, last_visit_time, total_visits, idvisitor, total_actions,
              total_searches, total_events
            FROM {$model->getUserIdsTable()}
            WHERE idsite = ? AND user_id != '' AND user_id LIKE ?
            ORDER BY user_id * 1 asc, user_id asc
            LIMIT 0, 10"
                    ),
                    $this->equalTo(array(1, '%user@example.com%'))
                ),
                array(
                    $this->equalTo(
                        "SELECT user_id, first_visit_time, last_visit_time, total_visits, idvisitor, total_actions,
              total_searches, total_events
            FROM {$model->getUserIdsTable()}
            WHERE idsite = ? AND user_id != '' AND user_id LIKE ?
            ORDER BY user_id * 1 asc, user_id asc
            LIMIT 0, 10"
                    ),
                    $this->equalTo(array(1, '%test\_user\%example.com%'))
                )
            );

        // simplest call
        $model->getSiteUserIds(1, 0, 10);
        // supply descendant sorting
        $model->getSiteUserIds(1, 50, 25, 'desc');
        // sorting direction is not asc or desc
        $model->getSiteUserIds(1, 50, 25, 'unsupported_sorting');
        // offset and limit values are not integers
        $model->getSiteUserIds(1, '" hack you', "' hack you");
        // sorting column is not default user_id
        $model->getSiteUserIds(1, 0, 10, 'asc', 'first_visit_time');
        // search by user ID
        $model->getSiteUserIds(1, 0, 10, 'asc', 'user_id', 'user@example.com');
        // search by user ID that contains MySQL LIKE wildcard symbols
        $model->getSiteUserIds(1, 0, 10, 'asc', 'user_id', 'test_user%example.com');
    }

    public function test_indexNewVisitsToUserIdsTable()
    {
        $dbMock = $this->getMockBuilder('Piwik\Db\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array('query'))
            ->getMock();

        $model = new Model($dbMock);

        $dbMock->expects($this->exactly(1))
            ->method('query')
            ->withConsecutive(
                array(
                    $this->equalTo(
                        "INSERT INTO {$model->getUserIdsTable()}
              (user_id, idsite, last_visit_id, first_visit_time, last_visit_time, total_visits, idvisitor, total_actions, total_searches, total_events)
            VALUES ('simple@user.id', 1, 12345, '2014-01-01 01:23:45', '2015-12-13 23:12:21', 9, X'0b81a0fa7f886b23', 2, 3, 0),('simple\\'use\\\"r.id', 1, 12345, '2014-01-01 01:23:45', '2015-12-13 23:12:21', 9, X'0b81a0fa7f886b23', 2, 3, 0)
            ON DUPLICATE KEY UPDATE last_visit_id = values(last_visit_id),
              last_visit_time = values(last_visit_time),
              total_visits = total_visits + values(total_visits),
              total_actions = total_actions + values(total_actions),
              total_searches = total_searches + values(total_searches),
              total_events = total_events + values(total_events)"
                    ),
                    $this->equalTo(array()),
                    $this->equalTo(false)
                )
            );

        $model->indexNewVisitsToUserIdsTable(array(
            array(
                'user_id' => 'simple@user.id',
                'idsite' => '1',
                'idvisitor' => hex2bin('0b81a0fa7f886b23'),
                'last_visit_id' => '12345',
                'first_visit_time' => '2014-01-01 01:23:45',
                'last_visit_time' => '2015-12-13 23:12:21',
                'total_visits' => '9',
                'total_actions' => '2',
                'total_searches' => '3',
                'total_events' => '0'
            ),
            array(
                'user_id' => 'simple\'use"r.id',
                'idsite' => '1',
                'idvisitor' => hex2bin('0b81a0fa7f886b23'),
                'last_visit_id' => '12345',
                'first_visit_time' => '2014-01-01 01:23:45',
                'last_visit_time' => '2015-12-13 23:12:21',
                'total_visits' => '9',
                'total_actions' => '2',
                'total_searches' => '3',
                'total_events' => '0'
            ),
        ));
    }
}
