<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\Dimension;

use \Exception;

class CaseSensitive
{
    private $caseSensitive;

    public function __construct($caseSensitive)
    {
        $this->caseSensitive = $caseSensitive;
    }

    public function check()
    {
        if (!is_bool($this->caseSensitive) && !in_array($this->caseSensitive, array('0', '1', 0, 1), true)) {
            $caseSensitive = $this->caseSensitive;
            throw new Exception("Invalid value '$caseSensitive' for 'caseSensitive' specified. Allowed values: '0' or '1'");
        }
    }

}