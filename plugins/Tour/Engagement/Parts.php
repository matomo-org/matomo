<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

class Parts
{
    /**
     * @var Part1
     */
    private $part1;

    public function __construct(Part1 $part1)
    {
        $this->part1 = $part1;
    }

    public function getCurrentPart()
    {
        $parts = $this->getAllParts();

        foreach ($parts as $part) {
            foreach ($part->getSteps() as $step) {
                if (!$step['done'] && !$step['skipped']) {
                    // not finished step, we will work on this part.
                    return $part;
                }
            }
        }
    }

    /**
     * @return BasePart[]
     */
    public function getAllParts()
    {
        return array(
            $this->part1
        );
    }

}