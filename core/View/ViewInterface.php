<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\View;

/**
 * Rendering interface for all "view" types.
 *
 * @api
 */
interface ViewInterface
{
    /**
     * Returns data.
     *
     * @return string Serialized data, eg, (image, array, html...).
     */
    public function render();
}
