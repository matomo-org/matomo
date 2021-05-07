<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Mail\EmailNotification;

use Piwik\Mail\EmailNotification;

class RecoveryCodesShowedEmailNotification extends EmailNotification
{
    protected $message = 'recovery codes showed';
    protected $subject = 'security 2fa';
}