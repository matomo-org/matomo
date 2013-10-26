<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

/**
 * @package Piwik
 * @subpackage Notification
 * @api
 */
class Notification
{
    const CONTEXT_SUCCESS = 'success';
    const CONTEXT_ERROR   = 'error';
    const CONTEXT_INFO    = 'info';
    const CONTEXT_WARNING = 'warning';

    const PRIORITY_MIN    = 100;
    const PRIORITY_LOW    = 50;
    const PRIORITY_HIGH   = 25;
    const PRIORITY_MAX    = 1;

    const FLAG_NO_CLEAR   = 1;
    const FLAG_TOAST      = 2;
    const FLAG_PERSISTENT = 4;
    const FLAG_TRANSIENT  = 8;

    public $title;
    public $message;
    public $icon;
    public $context  = self::CONTEXT_INFO;
    public $flags    = self::FLAG_PERSISTENT;
    public $priority = self::PRIORITY_LOW;

}