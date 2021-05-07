<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Mail;

use Piwik\Mail;
use Piwik\Piwik;

abstract class EmailNotification
{
    protected $message;
    protected $subject;
    
    public function send()
    {
        $mail = new Mail();
        $mail->setDefaultFromPiwik();
        $mail->addTo(Piwik::getCurrentUserEmail());
        $mail->setSubject($this->subject);
        $mail->setBodyText($this->message);
        $mail->send();
    }
}