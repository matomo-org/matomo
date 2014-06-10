<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tracker\Request;
use Piwik\Plugin\Manager as PluginManager;

/**
 * @api
 */
abstract class VisitDimension
{
    protected $name;

    protected $trackerParam   = '';
    protected $trackerType    = '';
    protected $trackerDefault = '';

    protected $fieldName = '';
    protected $fieldType = '';

    public function install()
    {
        try {
            $sql = "ALTER TABLE `" . Common::prefixTable("log_visit") . "` ADD `$this->fieldName` $this->fieldType";
            Db::exec($sql);

        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, '1060')) {
                throw $e;
            }
        }
    }

    public function onNewVisit(Request $request, $visit)
    {
        $params = array();
        // TODO $params = $request->getParams()
        $value = Common::getRequestVar($this->trackerParam, $this->trackerDefault, $this->trackerType, $params);
        return $value;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    abstract public function getName();

    /** @return \Piwik\Plugin\VisitDimension[] */
    public static function getAllDimensions()
    {
        $manager    = PluginManager::getInstance();
        $dimensions = $manager->findMultipleComponents('Dimensions', '\\Piwik\\Plugin\\VisitDimension');
        $instances  = array();

        foreach ($dimensions as $dimension) {
            $instances[] = new $dimension();
        }

        return $instances;
    }

    /** @return \Piwik\Plugin\VisitDimension[] */
    public static function getDimensions(\Piwik\Plugin $plugin)
    {
        $dimensions = $plugin->findMultipleComponents('Dimensions', '\\Piwik\\Plugin\\VisitDimension');
        $instances  = array();

        foreach ($dimensions as $dimension) {
            $instances[] = new $dimension();
        }

        return $instances;
    }

}
