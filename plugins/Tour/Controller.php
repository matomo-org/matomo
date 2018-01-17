<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour;

use Piwik\Common;
use Piwik\Plugins\Tour\Engagement\Steps;

class Controller extends \Piwik\Plugin\Controller
{

    /**
     * @var Steps
     */
    private $steps;

    public function __construct(Steps $parts)
    {
        $this->steps = $parts;
        parent::__construct();
    }

    public function skipstep()
    {
        $key = Common::getRequestVar('key', '', 'string');

        foreach ($this->steps->getSteps() as $step) {
            if ($step['key'] === $key) {
                // we make sure to change it only if it is a valid key
                Steps::skipStep($key);
            }
        }

        return true;
    }
}
