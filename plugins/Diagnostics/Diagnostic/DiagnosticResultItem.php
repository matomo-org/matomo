<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

/**
 * @api
 */
class DiagnosticResultItem
{
    /**
     * @var string
     */
    private $status;

    /**
     * Optional comment about the item.
     *
     * @var string
     */
    private $comment;

    public function __construct($status, $comment = '')
    {
        $this->status = $status;
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
