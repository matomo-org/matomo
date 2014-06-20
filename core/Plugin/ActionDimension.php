<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Tracker\Visitor;
use Piwik\Translate;

/**
 * @api
 * @since 2.5.0
 */
abstract class ActionDimension
{
    protected $name;

    protected $fieldName = '';
    protected $fieldType = '';

    protected $segments = array();

    /**
     * Cached instances of sorted visit dimension entries
     * @var array
     */
    private static $cachedInstances = array();

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {

    }

    public function install()
    {
        if (empty($this->fieldName) || empty($this->fieldType)) {
            return;
        }

        try {
            $sql = "ALTER TABLE `" . Common::prefixTable("log_link_visit_action") . "` ADD `$this->fieldName` $this->fieldType";
            Db::exec($sql);

        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, '1060')) {
                throw $e;
            }
        }
    }

    public function uninstall()
    {
        if (empty($this->fieldName) || empty($this->fieldType)) {
            return;
        }

        try {
            $sql = "ALTER TABLE `" . Common::prefixTable("log_link_visit_action") . "` DROP COLUMN `$this->fieldName`";
            Db::exec($sql);
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, '1091')) {
                throw $e;
            }
        }
    }


    public function shouldHandle()
    {
        return false;
    }

    /**
     * @return string|int
     * @throws \Exception in case not implemented
     */
    public function getActionId()
    {
        throw new \Exception('You need to overwrite the getActionId method in case you implement the onLookupAction method in class: ' . get_class($this));
    }

    protected function addSegment(Segment $segment)
    {
        $sqlSegment = $segment->getSqlSegment();
        if (!empty($this->fieldName) && empty($sqlSegment)) {
            $segment->setSqlSegment('log_link_visit_action.' . $this->fieldName);
        }

        $segment->setType(Segment::TYPE_DIMENSION);

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

    abstract public function getName();

    /** @return \Piwik\Plugin\ActionDimension[] */
    public static function getAllDimensions()
    {
        $pluginManager = PluginManager::getInstance();
        $cacheKey      = md5(implode('', $pluginManager->getLoadedPluginsName()) . Translate::getLanguageLoaded());

        if (!array_key_exists($cacheKey, self::$cachedInstances)) {

            $plugins   = $pluginManager->getLoadedPlugins();
            $instances = array();

            foreach ($plugins as $plugin) {
                foreach (self::getDimensions($plugin) as $instance) {
                    $instances[] = $instance;
                }
            }

            self::$cachedInstances[$cacheKey] = $instances;
        }

        return self::$cachedInstances[$cacheKey];
    }

    /** @return \Piwik\Plugin\ActionDimension[] */
    public static function getDimensions(\Piwik\Plugin $plugin)
    {
        $dimensions = $plugin->findMultipleComponents('Columns', '\\Piwik\\Plugin\\ActionDimension');
        $instances  = array();

        foreach ($dimensions as $dimension) {
            $instances[] = new $dimension();
        }

        return $instances;
    }

    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        return false;
    }

}
