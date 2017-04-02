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

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function extendVisitorDetails(&$visitor)
    {
    }

    public function filterActions(&$actions)
    {
    }

    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
    }

    public function renderAction($action)
    {
    }

    public function renderIcons()
    {
    }

    public function renderVisitorDetails()
    {
    }
}