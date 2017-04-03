<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

abstract class VisitorDetailsAbstract
{
    protected $details = array();

    /**
     * Set details of current visit
     * @param $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function extendVisitorDetails(&$visitor)
    {
    }

    public function provideActions(&$actions, $visitorDetails)
    {
    }

    /**
     * Allows filtering the provided actions
     *
     * Example:
     *
     * public function filterActions(&$actions) {
     *     foreach ($actions as $idx => $action) {
     *         if ($action['type'] == Action::TYPE_CONTENT) {
     *             unset($actions[$idx]);
     *             continue;
     *         }
     *     }
     * }
     *
     * @param $actions
     */
    public function filterActions(&$actions)
    {
    }

    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
    }

    public function renderAction($action, $previousAction)
    {
    }

    public function renderIcons()
    {
    }

    public function renderVisitorDetails()
    {
    }
}