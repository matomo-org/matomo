<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals;

use Piwik\Columns\Dimension;
use Piwik\Columns\Discriminator;

class GoalDimension extends Dimension
{
    protected $type = self::TYPE_TEXT;
    private $goal;
    private $id;

    public function __construct($goal, $column, $name)
    {
        $this->goal = $goal;
        $this->category = 'Goals_Goals';
        $this->dbTableName = 'log_conversion';
        $this->columnName = $column;
        $this->nameSingular = $name;

        $this->id = 'Goals.Goal' . ucfirst($column) . $goal['idgoal'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_conversion', 'idgoal', $this->goal['idgoal']);
    }

}