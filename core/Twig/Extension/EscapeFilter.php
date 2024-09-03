<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class EscapeFilter extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('e', '\Piwik\piwik_escape_filter', ['needs_environment' => true, 'is_safe_callback' => 'twig_escape_filter_is_safe']),
            new TwigFilter('escape', '\Piwik\piwik_escape_filter', ['needs_environment' => true, 'is_safe_callback' => 'twig_escape_filter_is_safe'])
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'escaper2';
    }
}
