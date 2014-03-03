<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\DataTable\Filter;

use Piwik\DataTable;

class Average extends DataTable\BaseFilter
{
    private $divisor;

    public function __construct($table, $columnToRead, $divisor)
    {
        $this->columnToRead = $columnToRead;
        $this->divisor = $divisor;
    }

    public function filter($table)
    {
        if (!$this->divisor) {
            return;
        }

        foreach ($table->getRows() as $key => $row) {

            $value = $row->getColumn($this->columnToRead);

            $row->setColumn($this->columnToRead, round($value / $this->divisor));
        }
    }
}