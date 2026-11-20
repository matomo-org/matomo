<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleAPI;

/**
 * Magic Object
 */
class MagicObject
{
    public string $great = 'formidable';

    protected string $wonderful = 'magnifique';

    public function incredible(): string
    {
        return 'Incroyable';
    }
}
