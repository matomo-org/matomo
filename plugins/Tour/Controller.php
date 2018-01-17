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
use Piwik\Plugins\Tour\Engagement\BasePart;
use Piwik\Plugins\Tour\Engagement\Parts;

class Controller extends \Piwik\Plugin\Controller
{

    /**
     * @var Parts
     */
    private $parts;

    public function __construct(Parts $parts)
    {
        $this->parts = $parts;
        parent::__construct();
    }

    public function skipstep()
    {
        $key = Common::getRequestVar('key', '', 'string');

        foreach ($this->parts->getAllParts() as $part) {
            foreach ($part->getSteps() as $step) {
                if ($step['key'] === $key) {
                    // we make sure to change it only if it is a valid key
                    BasePart::skipStep($key);
                }
            }
        }

        return true;
    }
}
