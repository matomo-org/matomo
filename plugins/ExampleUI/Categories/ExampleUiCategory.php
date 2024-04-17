<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ExampleUI\Categories;

use Piwik\Category\Category;

class ExampleUiCategory extends Category
{
    protected $id = 'ExampleUI_UiFramework';
    protected $order = 90;
}
