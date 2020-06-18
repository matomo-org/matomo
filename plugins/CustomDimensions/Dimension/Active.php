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

class Active
{
    private $active;

    public function __construct($active)
    {
        $this->active = $active;
    }

    public function check()
    {
        if (!is_bool($this->active) && !in_array($this->active, array('0', '1', 0, 1), true)) {
            $active = $this->active;
            throw new Exception("Invalid value '$active' for 'active' specified. Allowed values: '0' or '1'");
        }
    }

}