<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals;

use Piwik\Common;
use Piwik\Db;

class Model
{
    private static $rawPrefix = 'goal';
    private $table;

    public function __construct()
    {
        $this->table = Common::prefixTable(self::$rawPrefix);
    }

    private function getNextIdGoal($idSite)
    {
        $db     = $this->getDb();
        $idGoal = $db->fetchOne("SELECT max(idgoal) + 1 FROM " . $this->table . "
                                 WHERE idsite = ?", $idSite);

        if (empty($idGoal)) {
            $idGoal = 1;
        }

        return $idGoal;
    }

    public function createGoalForSite($idSite, $goal)
    {
        $db     = $this->getDb();
        $goalId = $this->getNextIdGoal($idSite);

        $goal['idgoal'] = $goalId;
        $goal['idsite'] = $idSite;

        $db->insert($this->table, $goal);

        return $goalId;
    }

    public function updateGoal($idSite, $idGoal, $goal)
    {
        $idSite = (int) $idSite;
        $idGoal = (int) $idGoal;

        $db = $this->getDb();
        $db->update($this->table, $goal, "idsite = '$idSite' AND idgoal = '$idGoal'");
    }

    // actually this should be in a log_conversion model
    public function deleteGoalConversions($idSite, $idGoal)
    {
        $table = Common::prefixTable("log_conversion");

        Db::deleteAllRows($table, "WHERE idgoal = ? AND idsite = ?", "idvisit", 100000, array($idGoal, $idSite));
    }

    public function getActiveGoals($idSite)
    {
        $idSite = array_map('intval', $idSite);
        $goals  = Db::fetchAll("SELECT * FROM " . $this->table . "
                                WHERE idsite IN (" . implode(", ", $idSite) . ")
                                      AND deleted = 0");

        return $goals;
    }

    public function deleteGoalsForSite($idSite)
    {
        Db::query("DELETE FROM " . $this->table . " WHERE idsite = ? ", array($idSite));
    }

    public function deleteGoal($idSite, $idGoal)
    {
        $query = "UPDATE " . $this->table . " SET deleted = 1
                  WHERE idsite = ? AND idgoal = ?";
        $bind  = array($idSite, $idGoal);

        Db::query($query, $bind);
    }

    private function getDb()
    {
        return Db::get();
    }
}
