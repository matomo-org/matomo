<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Feedback;

use Piwik\Piwik;
use Piwik\Option;

class FeedbackReminder
{
    public $userLogin;
    public $option;

    public function __construct()
    {
        $this->userLogin = Piwik::getCurrentUserLogin();
        $this->option = 'Feedback.nextFeedbackReminder';
    }

    public function getUserOption()
    {
        return Option::get("{$this->option}.{$this->userLogin}");
    }

    public function setUserOption($value)
    {
        Option::set("{$this->option}.{$this->userLogin}", $value);
    }
}
