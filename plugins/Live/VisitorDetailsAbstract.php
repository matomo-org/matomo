<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Piwik\DataTable;

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

    /**
     * Makes it possible to extend the visitor details returned from API
     *
     * @param $visitor
     */
    public function extendVisitorDetails(&$visitor)
    {
    }

    /**
     * Makes it possible to enrich the action set for a single visit
     *
     * @param $actions
     * @param $visitorDetails
     */
    public function provideActionsForVisit(&$actions, $visitorDetails)
    {
    }

    /**
     * Makes it possible to enrich the action set for multiple visits identified by given visit ids
     *
     * action set to enrich needs to have the following structure
     *
     * $actions = array (
     *     'idvisit' => array ( list of actions for this visit id ),
     *     'idvisit' => array ( list of actions for this visit id ),
     *     ...
     * )
     *
     * @param array $actions   action set to enrich
     * @param array $visitIds  list of visit ids
     */
    public function provideActionsForVisitIds(&$actions, $visitIds)
    {
    }

    /**
     * Allows filtering the provided actions
     *
     * Example:
     *
     * public function filterActions(&$actions, $visitorDetailsArray) {
     *     foreach ($actions as $idx => $action) {
     *         if ($action['type'] == Action::TYPE_CONTENT) {
     *             unset($actions[$idx]);
     *             continue;
     *         }
     *     }
     * }
     *
     * @param $actions
     * @param $visitorDetailsArray
     */
    public function filterActions(&$actions, $visitorDetailsArray)
    {
    }

    /**
     * Allows extended each action with additional information
     *
     * @param $action
     * @param $nextAction
     * @param $visitorDetails
     */
    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
    }

    /**
     * Called when rendering a single Action
     *
     * @param $action
     * @param $previousAction
     * @param $visitorDetails
     */
    public function renderAction($action, $previousAction, $visitorDetails)
    {
    }

    /**
     * Called for rendering the tooltip on actions
     *
     * @param $action
     * @param $visitInfo
     */
    public function renderActionTooltip($action, $visitInfo)
    {
    }

    /**
     * Called when rendering the Icons in visitor log
     *
     * @param $visitorDetails
     */
    public function renderIcons($visitorDetails)
    {
    }

    /**
     * Called when rendering the visitor details in visitor log
     *
     * @param $visitorDetails
     */
    public function renderVisitorDetails($visitorDetails)
    {
    }

    /**
     * Allows manipulating the visitor profile properties
     * Will be called when visitor profile is initialized
     *
     * @param DataTable $visits
     * @param array $profile
     * @return void
     */
    public function initProfile($visits, &$profile)
    {
    }

    /**
     * Allows manipulating the visitor profile properties based on included actions
     * Will be called for every action within the profile
     *
     * @param DataTable\Row $visit
     * @param array $profile
     * @return void
     */
    public function handleProfileVisit($visit, &$profile)
    {
    }

    /**
     * Allows manipulating the visitor profile properties based on included actions
     * Will be called for every action within the profile
     *
     * @param array $action
     * @param array $profile
     * @return void
     */
    public function handleProfileAction($action, &$profile)
    {
    }

    /**
     * Will be called after iterating over all actions
     *
     * @param DataTable $visits
     * @param array $profile
     * @return void
     */
    public function finalizeProfile($visits, &$profile)
    {
    }
}