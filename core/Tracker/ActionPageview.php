<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Config;

/**
 * This class represents a page view, tracking URL, page title and generation time.
 *
 */
class ActionPageview extends Action
{
    protected $timeGeneration = false;

    public function __construct(Request $request)
    {
        parent::__construct(Action::TYPE_PAGE_URL, $request);

        $url = $request->getParam('url');
        $this->setActionUrl($url);

        $actionName = $request->getParam('action_name');
        $actionName = $this->cleanupActionName($actionName);
        $this->setActionName($actionName);

        $this->timeGeneration = $this->request->getPageGenerationTime();
    }

    protected function getActionsToLookup()
    {
        return array(
            'idaction_name' => array($this->getActionName(), Action::TYPE_PAGE_TITLE),
            'idaction_url'  => $this->getUrlAndType()
        );
    }

    public function getCustomFloatValue()
    {
        return $this->request->getPageGenerationTime();
    }

    public static function shouldHandle(Request $request)
    {
        return true;
    }

    private function cleanupActionName($actionName)
    {
        // get the delimiter, by default '/'; BC, we read the old action_category_delimiter first (see #1067)
        $actionCategoryDelimiter = $this->getActionCategoryDelimiter();

        // create an array of the categories delimited by the delimiter
        $split = explode($actionCategoryDelimiter, $actionName);
        $split = $this->trimEveryCategory($split);
        $split = $this->removeEmptyCategories($split);

        return $this->rebuildNameOfCleanedCategories($actionCategoryDelimiter, $split);
    }

    private function rebuildNameOfCleanedCategories($actionCategoryDelimiter, $split)
    {
        return implode($actionCategoryDelimiter, $split);
    }

    private function removeEmptyCategories($split)
    {
        return array_filter($split, 'strlen');
    }

    private function trimEveryCategory($split)
    {
        return array_map('trim', $split);
    }

    private function getActionCategoryDelimiter()
    {
        if (isset(Config::getInstance()->General['action_category_delimiter'])) {
            return Config::getInstance()->General['action_category_delimiter'];
        }

        return Config::getInstance()->General['action_url_category_delimiter'];
    }
}
