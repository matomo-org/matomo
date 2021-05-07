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

class TwoFactorAuthDisabledEmailNotification extends EmailNotification
{
    protected $message = '2 fa disabled';
    protected $subject = 'security 2fa';
}