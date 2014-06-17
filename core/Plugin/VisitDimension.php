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
use Piwik\Plugin\Manager as PluginManager;

/**
 * @api
 * @since 2.4.0
 */
abstract class VisitDimension
{
    protected $name;

    protected $fieldName = '';
    protected $fieldType = '';

    protected $segments = array();

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {

    }

    public function hasImplementedEvent($method)
    {
        $reflectionObject = new \ReflectionObject($this);
        $declaringClass   = $reflectionObject->getMethod($method)->getDeclaringClass();

        return get_class() !== $declaringClass->name;
    }

    public function install()
    {
        if (empty($this->fieldName) || empty($this->fieldType)) {
            return;
        }

        try {
            if ($this->hasImplementedEvent('onNewVisit')
             || $this->hasImplementedEvent('onExistingVisit')
             || $this->hasImplementedEvent('onConvertedVisit') ) {
                $sql = "ALTER TABLE `" . Common::prefixTable("log_visit") . "` ADD `$this->fieldName` $this->fieldType;";
                Db::exec($sql);
            }

        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, '1060')) {
                throw $e;
            }
        }

        try {
            if ($this->hasImplementedEvent('onRecordGoal')) {
                $sql = "ALTER TABLE `" . Common::prefixTable("log_conversion") . "` ADD `$this->fieldName` $this->fieldType;";
                Db::exec($sql);
            }

        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, '1060')) {
                throw $e;
            }
        }
    }

    protected function addSegment(Segment $segment)
    {
        $sqlSegment = $segment->getSqlSegment();
        if (!empty($this->fieldName) && empty($sqlSegment)) {
            $segment->setSqlSegment('log_visit.' . $this->fieldName);
        }

        $type = $segment->getType();

        if (empty($type)) {
            $segment->setType(Segment::TYPE_DIMENSION);
        }

        $this->segments[] = $segment;
    }

    /**
     * @return Segment[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function onNewVisit()
    {
        return false;
    }

    public function onExistingVisit()
    {
        return false;
    }

    public function onConvertedVisit()
    {
        return false;
    }

    public function onRecordGoal()
    {
        return false;
    }

    abstract public function getName();

    /** @return \Piwik\Plugin\VisitDimension[] */
    public static function getAllDimensions()
    {
        $plugins   = PluginManager::getInstance()->getLoadedPlugins();
        $instances = array();
        foreach ($plugins as $plugin) {
            foreach (self::getDimensions($plugin) as $instance) {
                $instances[] = $instance;
            }
        }

        return $instances;
    }

    /** @return \Piwik\Plugin\VisitDimension[] */
    public static function getDimensions(\Piwik\Plugin $plugin)
    {
        $dimensions = $plugin->findMultipleComponents('Columns', '\\Piwik\\Plugin\\VisitDimension');
        $instances  = array();

        foreach ($dimensions as $dimension) {
            $instances[] = new $dimension();
        }

        return $instances;
    }

}
