<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Authorization;

/**
 * Exception thrown when a user doesn't have sufficient access to a resource.
 *
 * TODO Should we rename it to "AccessDeniedException"?
 */
class NoAccessException extends \Piwik\NoAccessException
{
}
 