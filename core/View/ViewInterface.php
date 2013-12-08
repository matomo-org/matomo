<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\View;

/**
 * Rendering interface for all "view" types.
 *
 * @package Piwik
 * @api
 */
interface ViewInterface
{
    /**
     * Returns data.
     *
     * @return string Serialized data, eg, (image, array, html...).
     */
    function render();
}