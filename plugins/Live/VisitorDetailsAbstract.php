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

/**
 * Class VisitorDetailsAbstract
 *
 * This class can be implemented in a plugin to extend the Live reports, visitor log and profile
 *
 * @api
 */
abstract class VisitorDetailsAbstract
{
    protected $details = array();

    /**
     * Set details of current visit
     *
     * @param array $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * Makes it possible to extend the visitor details returned from API
     *
     * **Example**
     *
     *    public function extendVisitorDetails(&$visitor) {
     *        $crmData = Model::getCRMData($visitor['userid']);
     *
     *        foreach ($crmData as $prop => $value) {
     *            $visitor[$prop] = $value;
     *        }
     *    }
     *
     * @param array $visitor
     * @return void
     */
    public function extendVisitorDetails(&$visitor)
    {
    }

    /**
     * Makes it possible to enrich the action set for a single visit
     *
     * **Example**
     *
     *    public function provideActionsForVisit(&$actions, $visitorDetails) {
     *        $adviews = Model::getAdviews($visitorDetails['visitid']);
     *        $actions += $adviews;
     *    }
     *
     * @param array $actions List of action to manipulate
     * @param array $visitorDetails
     * @return void
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
     * **Example**
     *
     *    public function provideActionsForVisitIds(&$actions, $visitIds) {
     *        $adviews = Model::getAdviewsByVisitIds($visitIds);
     *        foreach ($adviews as $idVisit => $adView) {
     *            $actions[$idVisit][] = $adView;
     *        }
     *    }
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
     * **Example:**
     *
     *     public function filterActions(&$actions, $visitorDetailsArray) {
     *         foreach ($actions as $idx => $action) {
     *             if ($action['type'] == 'customaction') {
     *                 unset($actions[$idx]);
     *                 continue;
     *             }
     *         }
     *     }
     *
     * @param array $actions
     * @param array $visitorDetailsArray
     */
    public function filterActions(&$actions, $visitorDetailsArray)
    {
    }

    /**
     * Allows extended each action with additional information
     *
     * @param array $action
     * @param array $nextAction
     * @param array $visitorDetails
     */
    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
    }

    /**
     * Called when rendering a single Action
     *
     * @param array $action
     * @param array $previousAction
     * @param array $visitorDetails
     * @return string
     */
    public function renderAction($action, $previousAction, $visitorDetails)
    {
    }

    /**
     * Called for rendering the tooltip on actions
     * returned array needs to look like this:
     * array (
     *          20,   // order id
     *          'rendered html content'
     * )
     *
     * @param array $action
     * @param array $visitInfo
     * @return array
     */
    public function renderActionTooltip($action, $visitInfo)
    {
        return [];
    }

    /**
     * Called when rendering the Icons in visitor log
     *
     * @param array $visitorDetails
     * @return string
     */
    public function renderIcons($visitorDetails)
    {
    }

    /**
     * Called when rendering the visitor details in visitor log
     * returned array needs to look like this:
     * array (
     *          20,   // order id
     *          'rendered html content'
     * )
     *
     * **Example**
     *    public function renderVisitorDetails($visitorDetails) {
     *        $view            = new View('@MyPlugin/_visitorDetails.twig');
     *        $view->visitInfo = $visitorDetails;
     *        return $view->render();
     *    }
     *
     * @param array $visitorDetails
     * @return array
     */
    public function renderVisitorDetails($visitorDetails)
    {
        return array();
    }

    /**
     * Allows manipulating the visitor profile properties
     * Will be called when visitor profile is initialized
     *
     * **Example**
     *
     *    public function initProfile($visit, &$profile) {
     *        // initialize properties that will be filled based on visits or actions
     *        $profile['totalActions']         = 0;
     *        $profile['totalActionsOfMyType'] = 0;
     *    }
     *
     * @param DataTable $visits
     * @param array $profile
     * @return void
     */
    public function initProfile($visits, &$profile)
    {
    }

    /**
     * Allows manipulating the visitor profile properties based on included visits
     * Will be called for every action within the profile
     *
     * **Example**
     *
     *    public function handleProfileVisit($visit, &$profile) {
     *        $profile['totalActions'] += $visit->getColumn('actions');
     *    }
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
     * **Example**
     *
     *    public function handleProfileAction($action, &$profile)
     *    {
     *        if ($action['type'] != 'myactiontype') {
     *            return;
     *        }
     *
     *        $profile['totalActionsOfMyType']++;
     *    }
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
     * Can be used to set profile information that requires data that was set while iterating over visits & actions
     *
     * **Example**
     *
     *    public function finalizeProfile($visits, &$profile) {
     *        $profile['isPowerUser'] = false;
     *
     *        if ($profile['totalActionsOfMyType'] > 20) {
     *            $profile['isPowerUser'] = true;
     *        }
     *    }
     *
     * @param DataTable $visits
     * @param array $profile
     * @return void
     */
    public function finalizeProfile($visits, &$profile)
    {
    }
}