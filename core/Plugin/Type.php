<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Piwik;

class Type
{
    protected $type = '';
    protected $name = '';
    protected $namePlural = '';
    protected $management = array(
        'name'         => '',
        'urls'         => '',
        'customFields' => array()
    );
    protected $reports = array(
        'disable' => array(),
        'enable'  => array(),
        'rename'  => array()
    );
    protected $metrics = array();

    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return Type
     */
    public static function factory($type)
    {
        $availableTypes = Manager::getInstance()->findComponents('Type', '\\Piwik\\Plugin\\Type');

        foreach ($availableTypes as $availableType) {
            /** @var Type $instance */
            $instance = new $availableType;

            if ($instance->getType() === $type) {
                return $availableType;
            }
        }
    }

    /**
     * @param  Report[] $reports
     * @return bool
     */
    public function filterReports($reports)
    {
        foreach ($reports as $index => $report) {
            $method = $report->getModule() . '.' . $report->getAction();
            if ($this->matchesReport($method, $this->reports['disable']) &&
                !$this->matchesReport($method, $this->reports['enable'])) {
                unset($reports[$index]);
            }
        }

        return $reports;
    }

    /**
     * @param  Report[] $reports
     * @return bool
     */
    public function renameReports($reports)
    {
        foreach ($this->reports['rename'] as $reportMethod => $name) {
            foreach ($reports as $index => $report) {
                $method = $report->getModule() . '.' . $report->getAction();

                if ($method === $reportMethod) {
                    $report->setName(Piwik::translate($name));
                    // here we actually need to apply a decorator (kinda) as all report instances are cached and we do not
                    // want to change the original report object
                    break;
                }
            }
        }
    }

    private function matchesReport($apiMethod, $rules)
    {
        foreach ($rules as $rule) {
            $rule = '/^' . str_replace('*', '(.*)', $rule) . '(.*)$/';
            if ($apiMethod === $rule || preg_match($rule, $apiMethod)) {
                return true;
            }
        }

        return false;
    }
}