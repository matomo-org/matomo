<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\DataTable\Filter;
use Piwik\DataTable\BaseFilter;

class Limit extends BaseFilter
{
    private $limitIncreaser;
    private $limitDecreaser;
    private $columnToRead;

    public function __construct($table, $columnToRead, $limitIncreaser, $limitDecreaser)
    {
        $this->columnToRead   = $columnToRead;
        $this->limitIncreaser = (int) $limitIncreaser;
        $this->limitDecreaser = (int) $limitDecreaser;
    }

    public function filter($table)
    {
        $countIncreaser = 0;
        $countDecreaser = 0;

        foreach ($table->getRows() as $key => $row) {

            if ($row->getColumn($this->columnToRead) >= 0) {

                $countIncreaser++;

                if ($countIncreaser > $this->limitIncreaser) {
                    $table->deleteRow($key);
                }

            } else {
                $countDecreaser++;

                if ($countDecreaser > $this->limitDecreaser) {
                    $table->deleteRow($key);
                }

            }
        }
    }
}